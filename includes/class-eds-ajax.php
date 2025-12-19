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
}
