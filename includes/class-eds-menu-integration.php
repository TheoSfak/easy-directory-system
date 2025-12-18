<?php
/**
 * Menu Integration Class
 * Handles WordPress menu takeover and restoration
 */

class EDS_Menu_Integration {
    
    /**
     * Initialize menu integration
     */
    public static function init() {
        // Hook into menu system with high priority
        add_filter('wp_nav_menu_args', array(__CLASS__, 'override_menu_args'), 999, 1);
        add_filter('wp_nav_menu_objects', array(__CLASS__, 'modify_menu_objects'), 999, 2);
    }
    
    /**
     * Get all registered menu locations
     */
    public static function get_menu_locations() {
        $locations = get_registered_nav_menus();
        $menu_locations = array();
        
        foreach ($locations as $location => $description) {
            $menu_locations[$location] = array(
                'name' => $description,
                'location' => $location,
                'current_menu' => self::get_assigned_menu($location),
                'takeover_enabled' => get_option('eds_menu_takeover_' . $location, false),
                'original_menu' => get_option('eds_original_menu_' . $location, '')
            );
        }
        
        return $menu_locations;
    }
    
    /**
     * Get currently assigned menu for a location
     */
    public static function get_assigned_menu($location) {
        $locations = get_nav_menu_locations();
        if (isset($locations[$location])) {
            $menu = wp_get_nav_menu_object($locations[$location]);
            return $menu ? $menu->name : __('None', 'easy-directory-system');
        }
        return __('None', 'easy-directory-system');
    }
    
    /**
     * Enable menu takeover for a location
     */
    public static function enable_takeover($location, $taxonomy = 'category') {
        // Store original menu assignment before takeover
        $locations = get_nav_menu_locations();
        if (isset($locations[$location])) {
            $original_menu_id = $locations[$location];
            update_option('eds_original_menu_' . $location, $original_menu_id);
        }
        
        // Store taxonomy to use
        update_option('eds_menu_taxonomy_' . $location, $taxonomy);
        
        // Enable takeover
        update_option('eds_menu_takeover_' . $location, true);
        
        return true;
    }
    
    /**
     * Disable menu takeover and restore original menu
     */
    public static function disable_takeover($location) {
        // Restore original menu
        $original_menu_id = get_option('eds_original_menu_' . $location);
        if ($original_menu_id) {
            $locations = get_nav_menu_locations();
            $locations[$location] = intval($original_menu_id);
            set_theme_mod('nav_menu_locations', $locations);
        }
        
        // Clean up options
        delete_option('eds_menu_takeover_' . $location);
        delete_option('eds_original_menu_' . $location);
        delete_option('eds_menu_taxonomy_' . $location);
        
        return true;
    }
    
    /**
     * Override menu args to force menu rendering even if no menu assigned
     */
    public static function override_menu_args($args) {
        $location = isset($args['theme_location']) ? $args['theme_location'] : '';
        
        // Only modify if this location has takeover enabled
        if ($location && get_option('eds_menu_takeover_' . $location, false)) {
            // Store the location so we can use it in modify_menu_objects
            $args['eds_takeover_location'] = $location;
            // Use a custom walker to ensure compatibility
            $args['items_wrap'] = '<ul id="%1$s" class="%2$s">%3$s</ul>';
        }
        
        return $args;
    }
    
    /**
     * Modify menu objects for locations with takeover
     */
    public static function modify_menu_objects($sorted_menu_items, $args) {
        // Check if this request is for a location with EDS takeover
        $location = isset($args->theme_location) ? $args->theme_location : '';
        
        // Only proceed if takeover is enabled for this specific location
        if (!$location || !get_option('eds_menu_takeover_' . $location, false)) {
            return $sorted_menu_items;
        }
        
        // Get taxonomy for this location
        $taxonomy = get_option('eds_menu_taxonomy_' . $location, 'category');
        
        // Build menu items from categories
        $eds_items = self::build_menu_items_from_categories($taxonomy, $location);
        
        // Debug: Log what's happening
        if (current_user_can('manage_options') && WP_DEBUG) {
            error_log('EDS Menu - Location: ' . $location . ', Taxonomy: ' . $taxonomy . ', EDS Items: ' . count($eds_items) . ', Original Items: ' . count($sorted_menu_items));
            if (!empty($eds_items)) {
                error_log('EDS Menu - First item: ' . $eds_items[0]->title);
            }
        }
        
        // Always replace with EDS items when takeover is enabled
        // Return empty array if no EDS categories (better than showing original menu)
        return $eds_items;
    }
    
    /**
     * Build menu items array from EDS categories
     */
    private static function build_menu_items_from_categories($taxonomy, $location) {
        global $wpdb;
        
        // Get parent categories - try with position first, fallback to name
        $categories = get_terms(array(
            'taxonomy' => $taxonomy,
            'parent' => 0,
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC'
        ));
        
        if (is_wp_error($categories) || empty($categories)) {
            return array();
        }
        
        // Sort by position if available
        $categories_with_position = array();
        foreach ($categories as $cat) {
            $extended_data = EDS_Database::get_category_data($cat->term_id);
            if ($extended_data) {
                $cat->eds_position = $extended_data->position ? intval($extended_data->position) : 999;
                $cat->eds_enabled = $extended_data->is_enabled;
            } else {
                $cat->eds_position = 999;
                $cat->eds_enabled = false;
            }
            $categories_with_position[] = $cat;
        }
        
        // Sort by position
        usort($categories_with_position, function($a, $b) {
            return $a->eds_position - $b->eds_position;
        });
        
        $menu_items = array();
        $menu_order = 1;
        
        foreach ($categories_with_position as $category) {
            // Check if category is enabled
            if (!$category->eds_enabled) {
                continue;
            }
            
            // Create menu item object
            $menu_item = self::create_menu_item_from_category($category, $taxonomy, 0, $menu_order);
            $menu_items[] = $menu_item;
            $menu_order++;
            
            // Get child categories
            $children = get_terms(array(
                'taxonomy' => $taxonomy,
                'parent' => $category->term_id,
                'hide_empty' => false,
                'orderby' => 'name',
                'order' => 'ASC'
            ));
            
            if (!is_wp_error($children) && !empty($children)) {
                // Sort children by position
                $children_with_position = array();
                foreach ($children as $child) {
                    $child_extended = EDS_Database::get_category_data($child->term_id);
                    if ($child_extended) {
                        $child->eds_position = $child_extended->position ? intval($child_extended->position) : 999;
                        $child->eds_enabled = $child_extended->is_enabled;
                    } else {
                        $child->eds_position = 999;
                        $child->eds_enabled = false;
                    }
                    $children_with_position[] = $child;
                }
                
                usort($children_with_position, function($a, $b) {
                    return $a->eds_position - $b->eds_position;
                });
                
                foreach ($children_with_position as $child) {
                    if ($child->eds_enabled) {
                        $child_item = self::create_menu_item_from_category($child, $taxonomy, $menu_item->ID, $menu_order);
                        $menu_items[] = $child_item;
                        $menu_order++;
                    }
                }
            }
        }
        
        return $menu_items;
    }
    
    /**
     * Create a menu item object from a category
     */
    private static function create_menu_item_from_category($category, $taxonomy, $parent_id, $order) {
        $menu_item = new stdClass();
        
        $menu_item->ID = $category->term_id * 1000 + $order; // Unique numeric ID
        $menu_item->db_id = $category->term_id * 1000 + $order;
        $menu_item->menu_item_parent = $parent_id;
        $menu_item->object_id = $category->term_id;
        $menu_item->object = $taxonomy;
        $menu_item->type = 'taxonomy';
        $menu_item->type_label = __('Category');
        $menu_item->title = $category->name;
        $menu_item->url = get_term_link($category->term_id, $taxonomy);
        $menu_item->target = '';
        $menu_item->attr_title = '';
        $menu_item->description = $category->description;
        $menu_item->classes = array('menu-item', 'menu-item-type-taxonomy', 'menu-item-object-' . $taxonomy, 'menu-item-' . $menu_item->ID);
        $menu_item->xfn = '';
        $menu_item->menu_order = $order;
        $menu_item->post_parent = $parent_id;
        $menu_item->post_type = 'nav_menu_item';
        
        // Check if current
        if (is_tax($taxonomy, $category->slug) || is_category($category->term_id)) {
            $menu_item->current = true;
            $menu_item->classes[] = 'current-menu-item';
            $menu_item->classes[] = 'current_page_item';
        } else {
            $menu_item->current = false;
        }
        
        $menu_item->current_item_ancestor = false;
        $menu_item->current_item_parent = false;
        
        // Flatsome specific properties
        $menu_item->filter = 'raw';
        $menu_item->_menu_item_type = 'taxonomy';
        $menu_item->_menu_item_object = $taxonomy;
        $menu_item->_menu_item_object_id = $category->term_id;
        
        return $menu_item;
    }
}
