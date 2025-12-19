<?php
/**
 * AJAX operations for Easy Directory System
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class EDS_Ajax {
    
    /**
     * Instance of this class
     */
    private static $instance = null;
    
    /**
     * Get instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        // AJAX actions
        add_action('wp_ajax_eds_toggle_category', array($this, 'toggle_category'));
        add_action('wp_ajax_eds_delete_category', array($this, 'delete_category'));
        add_action('wp_ajax_eds_sync_woocommerce', array($this, 'sync_woocommerce'));
        add_action('wp_ajax_eds_get_orphaned_categories', array($this, 'get_orphaned_categories'));
        add_action('wp_ajax_eds_update_position', array($this, 'update_position'));
        add_action('wp_ajax_eds_get_statistics', array($this, 'get_statistics'));
        add_action('wp_ajax_eds_enable_all_categories', array($this, 'enable_all_categories'));
        add_action('wp_ajax_eds_duplicate_category', array($this, 'duplicate_category'));
        add_action('wp_ajax_eds_regenerate_urls', array($this, 'regenerate_urls'));
    }
    
    /**
     * Verify nonce
     */
    private function verify_nonce() {
        if (!check_ajax_referer('eds_ajax_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => __('Security check failed', 'easy-directory-system')));
        }
    }
    
    /**
     * Toggle category enabled/disabled
     */
    public function toggle_category() {
        $this->verify_nonce();
        
        $term_id = isset($_POST['term_id']) ? intval($_POST['term_id']) : 0;
        $enabled = isset($_POST['enabled']) ? (bool)$_POST['enabled'] : false;
        
        if (!$term_id) {
            wp_send_json_error(array('message' => __('Invalid category', 'easy-directory-system')));
        }
        
        EDS_Database::save_category_data($term_id, array('is_enabled' => $enabled ? 1 : 0));
        
        wp_send_json_success(array(
            'message' => __('Category status updated', 'easy-directory-system'),
            'enabled' => $enabled
        ));
    }
    
    /**
     * Delete category
     */
    public function delete_category() {
        $this->verify_nonce();
        
        $term_id = isset($_POST['term_id']) ? intval($_POST['term_id']) : 0;
        $taxonomy = isset($_POST['taxonomy']) ? sanitize_text_field($_POST['taxonomy']) : 'category';
        
        if (!$term_id) {
            wp_send_json_error(array('message' => __('Invalid category', 'easy-directory-system')));
        }
        
        // Delete extended data
        EDS_Database::delete_category_data($term_id);
        
        // Delete term
        $result = wp_delete_term($term_id, $taxonomy);
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
        
        wp_send_json_success(array('message' => __('Category deleted', 'easy-directory-system')));
    }
    
    /**
     * Get orphaned categories
     */
    public function get_orphaned_categories() {
        $this->verify_nonce();
        
        $orphaned = EDS_WooCommerce_Sync::get_orphaned_categories();
        
        wp_send_json_success(array('orphaned' => $orphaned));
    }
    
    /**
     * Sync with WooCommerce
     */
    public function sync_woocommerce() {
        $this->verify_nonce();
        
        $direction = isset($_POST['direction']) ? sanitize_text_field($_POST['direction']) : 'to_woo';
        $remove_orphaned = isset($_POST['remove_orphaned']) ? (bool)$_POST['remove_orphaned'] : false;
        
        if ($direction === 'from_woo') {
            $result = EDS_WooCommerce_Sync::sync_from_woocommerce($remove_orphaned);
        } else {
            $result = EDS_WooCommerce_Sync::sync_to_woocommerce();
        }
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }
    
    /**
     * Update category position
     */
    public function update_position() {
        $this->verify_nonce();
        
        $positions = isset($_POST['positions']) ? $_POST['positions'] : array();
        
        foreach ($positions as $term_id => $position) {
            EDS_Database::save_category_data(intval($term_id), array('position' => intval($position)));
        }
        
        wp_send_json_success(array('message' => __('Positions updated', 'easy-directory-system')));
    }
    
    /**
     * Get category statistics
     */
    public function get_statistics() {
        $this->verify_nonce();
        
        $taxonomy = isset($_POST['taxonomy']) ? sanitize_text_field($_POST['taxonomy']) : 'category';
        $stats = EDS_Category::get_statistics($taxonomy);
        
        wp_send_json_success($stats);
    }
    
    /**
     * Enable all categories in taxonomy
     */
    public function enable_all_categories() {
        $this->verify_nonce();
        
        $taxonomy = isset($_POST['taxonomy']) ? sanitize_text_field($_POST['taxonomy']) : 'category';
        
        // Get ALL terms in the taxonomy without any filters
        $terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
            'number' => 0,  // Get all terms, no limit
            'orderby' => 'none',
            'suppress_filter' => true  // Bypass filters that might limit results
        ));
        
        if (is_wp_error($terms)) {
            wp_send_json_error(array('message' => $terms->get_error_message()));
        }
        
        $count = 0;
        
        // Enable all terms regardless of their hierarchy level
        foreach ($terms as $term) {
            EDS_Database::save_category_data($term->term_id, array('is_enabled' => 1));
            $count++;
        }
        
        wp_send_json_success(array(
            'message' => sprintf(__('Enabled %d categories', 'easy-directory-system'), $count),
            'count' => $count
        ));
    }
    
    /**
     * Duplicate category with all metadata
     */
    public function duplicate_category() {
        $this->verify_nonce();
        
        $term_id = isset($_POST['term_id']) ? intval($_POST['term_id']) : 0;
        $taxonomy = isset($_POST['taxonomy']) ? sanitize_text_field($_POST['taxonomy']) : 'category';
        
        if (!$term_id) {
            wp_send_json_error(array('message' => __('Invalid category ID', 'easy-directory-system')));
        }
        
        // Get original term
        $original_term = get_term($term_id, $taxonomy);
        if (is_wp_error($original_term) || !$original_term) {
            wp_send_json_error(array('message' => __('Category not found', 'easy-directory-system')));
        }
        
        // Create new term with (Copy) suffix
        $new_name = $original_term->name . ' ' . __('(Copy)', 'easy-directory-system');
        $new_slug = $original_term->slug . '-copy-' . time();
        
        $new_term = wp_insert_term(
            $new_name,
            $taxonomy,
            array(
                'description' => $original_term->description,
                'parent' => $original_term->parent,
                'slug' => $new_slug
            )
        );
        
        if (is_wp_error($new_term)) {
            wp_send_json_error(array('message' => $new_term->get_error_message()));
        }
        
        $new_term_id = $new_term['term_id'];
        
        // Copy extended data
        $extended_data = EDS_Database::get_category_data($term_id);
        if ($extended_data) {
            $copy_data = array(
                'is_enabled' => $extended_data->is_enabled,
                'position' => $extended_data->position,
                'cover_image' => $extended_data->cover_image,
                'thumbnail_image' => $extended_data->thumbnail_image,
                'meta_title' => $extended_data->meta_title,
                'meta_description' => $extended_data->meta_description,
                'additional_description' => $extended_data->additional_description,
                'group_access' => $extended_data->group_access,
                'redirection_type' => $extended_data->redirection_type,
                'redirection_target' => $extended_data->redirection_target
            );
            EDS_Database::save_category_data($new_term_id, $copy_data);
        }
        
        // Copy term meta
        $meta_keys = get_term_meta($term_id);
        foreach ($meta_keys as $key => $values) {
            foreach ($values as $value) {
                add_term_meta($new_term_id, $key, maybe_unserialize($value));
            }
        }
        
        wp_send_json_success(array(
            'message' => sprintf(__('Category duplicated successfully as "%s"', 'easy-directory-system'), $new_name),
            'new_term_id' => $new_term_id
        ));
    }
    
    /**
     * Regenerate all friendly URLs
     */
    public function regenerate_urls() {
        $this->verify_nonce();
        
        $taxonomy = isset($_POST['taxonomy']) ? sanitize_text_field($_POST['taxonomy']) : 'category';
        
        // Get all terms in the taxonomy
        $terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
            'number' => 0,
            'orderby' => 'none',
            'suppress_filter' => true
        ));
        
        if (is_wp_error($terms)) {
            wp_send_json_error(array('message' => $terms->get_error_message()));
        }
        
        $count = 0;
        $errors = array();
        $debug_info = array();
        
        // Get URL settings - default to Greeklish conversion for Greek sites
        $allowed_chars = get_option('eds_allowed_url_chars', 'letters_numbers_underscores_hyphens_greeklish');
        $debug_info[] = 'Setting: ' . $allowed_chars;
        
        foreach ($terms as $term) {
            // Generate new slug from term name
            $new_slug = $this->generate_slug($term->name, $allowed_chars, $taxonomy, $term->term_id);
            
            // Debug first 3 conversions
            if (count($debug_info) < 4) {
                $debug_info[] = sprintf('"%s" -> "%s" (was: "%s")', $term->name, $new_slug, $term->slug);
            }
            
            // Only update if slug changed
            if ($new_slug !== $term->slug) {
                // Force our custom slug using filter to bypass WordPress sanitization
                add_filter('pre_term_slug', function($slug) use ($new_slug) {
                    return $new_slug;
                }, 10, 1);
                
                $result = wp_update_term($term->term_id, $taxonomy, array(
                    'slug' => $new_slug,
                    'name' => $term->name  // Keep name unchanged
                ));
                
                // Remove filter after use
                remove_all_filters('pre_term_slug');
                
                if (is_wp_error($result)) {
                    $errors[] = sprintf(__('Failed to update "%s": %s', 'easy-directory-system'), $term->name, $result->get_error_message());
                } else {
                    $count++;
                }
            }
        }
        
        $message = sprintf(__('Regenerated %d friendly URLs', 'easy-directory-system'), $count);
        if (!empty($debug_info)) {
            $message .= '<br><br><strong>Debug:</strong><br>' . implode('<br>', $debug_info);
        }
        if (!empty($errors)) {
            $message .= '<br><br><strong>Errors:</strong><br>' . implode('<br>', $errors);
        }
        
        wp_send_json_success(array(
            'message' => $message,
            'count' => $count,
            'errors' => $errors,
            'debug' => $debug_info
        ));
    }
    
    /**
     * Generate slug from text based on settings
     */
    private function generate_slug($text, $allowed_chars, $taxonomy, $term_id = 0) {
        // Greek to Greeklish conversion map (handle before lowercasing)
        $greek_map = array(
            'Α'=>'A','Ά'=>'A','α'=>'a','ά'=>'a',
            'Β'=>'B','β'=>'b',
            'Γ'=>'G','γ'=>'g',
            'Δ'=>'D','δ'=>'d',
            'Ε'=>'E','Έ'=>'E','ε'=>'e','έ'=>'e',
            'Ζ'=>'Z','ζ'=>'z',
            'Η'=>'I','Ή'=>'I','η'=>'i','ή'=>'i',
            'Θ'=>'Th','θ'=>'th',
            'Ι'=>'I','Ί'=>'I','Ϊ'=>'I','ι'=>'i','ί'=>'i','ϊ'=>'i','ΐ'=>'i',
            'Κ'=>'K','κ'=>'k',
            'Λ'=>'L','λ'=>'l',
            'Μ'=>'M','μ'=>'m',
            'Ν'=>'N','ν'=>'n',
            'Ξ'=>'Ks','ξ'=>'ks',
            'Ο'=>'O','Ό'=>'O','ο'=>'o','ό'=>'o',
            'Π'=>'P','π'=>'p',
            'Ρ'=>'R','ρ'=>'r',
            'Σ'=>'S','σ'=>'s','ς'=>'s',
            'Τ'=>'T','τ'=>'t',
            'Υ'=>'Y','Ύ'=>'Y','Ϋ'=>'Y','υ'=>'y','ύ'=>'y','ϋ'=>'y','ΰ'=>'y',
            'Φ'=>'F','φ'=>'f',
            'Χ'=>'Ch','χ'=>'ch',
            'Ψ'=>'Ps','ψ'=>'ps',
            'Ω'=>'O','Ώ'=>'O','ω'=>'o','ώ'=>'o'
        );
        
        $slug = $text;
        
        // Apply character rules based on setting
        switch($allowed_chars) {
            case 'letters_numbers_underscores_hyphens_greeklish':
                // Convert Greek to Greeklish FIRST (before lowercasing)
                $slug = strtr($slug, $greek_map);
                // Then lowercase all characters
                $slug = function_exists('mb_strtolower') ? mb_strtolower($slug, 'UTF-8') : strtolower($slug);
                // Replace spaces and invalid characters with hyphens
                $slug = preg_replace('/\s+/', '-', $slug);  // Spaces to hyphens first
                $slug = preg_replace('/[^a-z0-9_\-]/', '-', $slug);  // Other invalid chars to hyphens
                break;
            case 'letters_numbers_underscores_hyphens_greek':
                // Keep Greek characters (lowercase first)
                $slug = function_exists('mb_strtolower') ? mb_strtolower($slug, 'UTF-8') : strtolower($slug);
                $slug = preg_replace('/[^a-z0-9_\-\x{0370}-\x{03ff}\x{1f00}-\x{1fff}]/u', '-', $slug);
                break;
            case 'letters_numbers_hyphens':
                $slug = function_exists('mb_strtolower') ? mb_strtolower($slug, 'UTF-8') : strtolower($slug);
                $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
                break;
            case 'letters_numbers':
                $slug = function_exists('mb_strtolower') ? mb_strtolower($slug, 'UTF-8') : strtolower($slug);
                $slug = preg_replace('/[^a-z0-9]/', '-', $slug);
                break;
            case 'letters_numbers_underscores_hyphens':
            default:
                $slug = function_exists('mb_strtolower') ? mb_strtolower($slug, 'UTF-8') : strtolower($slug);
                $slug = preg_replace('/[^a-z0-9_\-]/', '-', $slug);
                break;
        }
        
        // Clean up multiple hyphens
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        
        // Ensure uniqueness
        $original_slug = $slug;
        $counter = 1;
        while (term_exists($slug, $taxonomy) && get_term_by('slug', $slug, $taxonomy)->term_id !== $term_id) {
            $slug = $original_slug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
}
