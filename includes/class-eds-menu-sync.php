<?php
/**
 * Menu Sync Class - Creates and syncs WordPress menus from EDS categories
 */

class EDS_Menu_Sync {
    
    /**
     * Create or update WordPress menu from EDS categories
     */
    public static function sync_menu($taxonomy = 'product_cat', $menu_name = null) {
        if (!$menu_name) {
            $tax_obj = get_taxonomy($taxonomy);
            $menu_name = 'EDS - ' . $tax_obj->labels->name;
        }
        
        // Get or create menu
        $menu = wp_get_nav_menu_object($menu_name);
        if (!$menu) {
            $menu_id = wp_create_nav_menu($menu_name);
        } else {
            $menu_id = $menu->term_id;
            // Clear existing items
            $menu_items = wp_get_nav_menu_items($menu_id);
            if ($menu_items) {
                foreach ($menu_items as $item) {
                    wp_delete_post($item->ID, true);
                }
            }
        }
        
        if (is_wp_error($menu_id)) {
            return false;
        }
        
        // Get enabled categories sorted by position
        $categories = self::get_enabled_categories($taxonomy);
        
        // Build menu tree
        $position = 1;
        foreach ($categories as $category) {
            if ($category->parent == 0) {
                self::add_category_to_menu($menu_id, $category, 0, $position, $taxonomy);
                $position++;
            }
        }
        
        return $menu_id;
    }
    
    /**
     * Get enabled categories sorted by position
     */
    private static function get_enabled_categories($taxonomy) {
        $all_terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
            'orderby' => 'name'
        ));
        
        if (is_wp_error($all_terms)) {
            return array();
        }
        
        // Filter enabled and add position data
        $enabled_categories = array();
        foreach ($all_terms as $term) {
            $extended_data = EDS_Database::get_category_data($term->term_id);
            if ($extended_data && $extended_data->is_enabled) {
                $term->eds_position = $extended_data->position ? intval($extended_data->position) : 999;
                $enabled_categories[] = $term;
            }
        }
        
        // Sort by position
        usort($enabled_categories, function($a, $b) {
            return $a->eds_position - $b->eds_position;
        });
        
        return $enabled_categories;
    }
    
    /**
     * Add category and children to menu recursively
     */
    private static function add_category_to_menu($menu_id, $category, $parent_item_id, &$position, $taxonomy) {
        // Add this category
        $item_id = wp_update_nav_menu_item($menu_id, 0, array(
            'menu-item-title' => $category->name,
            'menu-item-object' => $taxonomy,
            'menu-item-object-id' => $category->term_id,
            'menu-item-type' => 'taxonomy',
            'menu-item-status' => 'publish',
            'menu-item-position' => $position,
            'menu-item-parent-id' => $parent_item_id
        ));
        
        if (is_wp_error($item_id)) {
            return;
        }
        
        // Get children
        $children = get_terms(array(
            'taxonomy' => $taxonomy,
            'parent' => $category->term_id,
            'hide_empty' => false,
            'orderby' => 'name'
        ));
        
        if (!is_wp_error($children) && !empty($children)) {
            // Sort children by position
            $children_sorted = array();
            foreach ($children as $child) {
                $child_extended = EDS_Database::get_category_data($child->term_id);
                if ($child_extended && $child_extended->is_enabled) {
                    $child->eds_position = $child_extended->position ? intval($child_extended->position) : 999;
                    $children_sorted[] = $child;
                }
            }
            
            usort($children_sorted, function($a, $b) {
                return $a->eds_position - $b->eds_position;
            });
            
            // Add children recursively
            foreach ($children_sorted as $child) {
                $position++;
                self::add_category_to_menu($menu_id, $child, $item_id, $position, $taxonomy);
            }
        }
    }
    
    /**
     * Get all taxonomies that have EDS categories
     */
    public static function get_synced_taxonomies() {
        global $wpdb;
        $table = $wpdb->prefix . 'eds_category_data';
        
        $results = $wpdb->get_results("
            SELECT DISTINCT tt.taxonomy
            FROM {$table} ecd
            INNER JOIN {$wpdb->term_taxonomy} tt ON ecd.term_id = tt.term_id
            WHERE ecd.is_enabled = 1
        ");
        
        $taxonomies = array();
        foreach ($results as $row) {
            $taxonomies[] = $row->taxonomy;
        }
        
        return $taxonomies;
    }
}
