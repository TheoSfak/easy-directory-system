<?php
/**
 * WooCommerce synchronization for Easy Directory System
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class EDS_WooCommerce_Sync {
    
    /**
     * Check if WooCommerce is active
     */
    public static function is_woocommerce_active() {
        return class_exists('WooCommerce');
    }
    
    /**
     * Sync WordPress categories to WooCommerce
     */
    public static function sync_to_woocommerce() {
        if (!self::is_woocommerce_active()) {
            return array('success' => false, 'message' => __('WooCommerce is not active', 'easy-directory-system'));
        }
        
        $wp_categories = get_terms(array(
            'taxonomy' => 'category',
            'hide_empty' => false
        ));
        
        $synced = 0;
        $errors = array();
        
        foreach ($wp_categories as $wp_cat) {
            try {
                $woo_cat = self::sync_single_category_internal($wp_cat, 'category', 'product_cat');
                if ($woo_cat) {
                    $synced++;
                }
            } catch (Exception $e) {
                $errors[] = sprintf(__('Error syncing %s: %s', 'easy-directory-system'), $wp_cat->name, $e->getMessage());
            }
        }
        
        return array(
            'success' => true,
            'synced' => $synced,
            'errors' => $errors,
            'message' => sprintf(__('Synced %d categories to WooCommerce', 'easy-directory-system'), $synced)
        );
    }
    
    /**
     * Get orphaned categories (exist in WordPress but not in WooCommerce)
     */
    public static function get_orphaned_categories() {
        if (!self::is_woocommerce_active()) {
            return array();
        }
        
        // Get all WooCommerce categories
        $woo_categories = get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
            'fields' => 'slugs'
        ));
        
        // Get all WordPress categories
        $wp_categories = get_terms(array(
            'taxonomy' => 'category',
            'hide_empty' => false
        ));
        
        $orphaned = array();
        foreach ($wp_categories as $wp_cat) {
            // Check if this category exists in WooCommerce
            if (!in_array($wp_cat->slug, $woo_categories)) {
                $orphaned[] = array(
                    'term_id' => $wp_cat->term_id,
                    'name' => $wp_cat->name,
                    'slug' => $wp_cat->slug
                );
            }
        }
        
        return $orphaned;
    }
    
    /**
     * Sync WooCommerce categories to WordPress
     */
    public static function sync_from_woocommerce($remove_orphaned = false) {
        if (!self::is_woocommerce_active()) {
            return array('success' => false, 'message' => __('WooCommerce is not active', 'easy-directory-system'));
        }
        
        $woo_categories = get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => false
        ));
        
        $synced = 0;
        $removed = 0;
        $errors = array();
        
        foreach ($woo_categories as $woo_cat) {
            try {
                $wp_cat = self::sync_single_category_internal($woo_cat, 'product_cat', 'category');
                if ($wp_cat) {
                    $synced++;
                }
            } catch (Exception $e) {
                $errors[] = sprintf(__('Error syncing %s: %s', 'easy-directory-system'), $woo_cat->name, $e->getMessage());
            }
        }
        
        // Remove orphaned categories if requested
        if ($remove_orphaned) {
            $orphaned = self::get_orphaned_categories();
            foreach ($orphaned as $orphan) {
                $result = wp_delete_term($orphan['term_id'], 'category');
                if (!is_wp_error($result)) {
                    $removed++;
                }
            }
        }
        
        // Build message based on what actually happened
        $message = sprintf(__('Synced %d categories from WooCommerce', 'easy-directory-system'), $synced);
        if ($remove_orphaned && $removed > 0) {
            $message .= sprintf(__(', removed %d orphaned categories', 'easy-directory-system'), $removed);
        } elseif ($remove_orphaned && $removed === 0) {
            $message .= __(', no orphaned categories found', 'easy-directory-system');
        }
        
        return array(
            'success' => true,
            'synced' => $synced,
            'removed' => $removed,
            'errors' => $errors,
            'message' => $message
        );
    }
    
    /**
     * Sync single category by term ID (for auto-sync on save)
     */
    public static function sync_single_category($term_id) {
        if (!self::is_woocommerce_active()) {
            return false;
        }
        
        // Get the term from product_cat taxonomy
        $term = get_term($term_id, 'product_cat');
        if (is_wp_error($term) || !$term) {
            return false;
        }
        
        // Sync to WordPress categories
        try {
            $result = self::sync_single_category_internal($term, 'product_cat', 'category');
            return !is_wp_error($result);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Sync single category (internal method)
     */
    private static function sync_single_category_internal($source_term, $source_taxonomy, $target_taxonomy) {
        // Get settings
        $settings = get_option('eds_settings', array(
            'sync_images' => 1,
            'sync_hierarchy' => 1
        ));
        
        // Check if category already exists in target taxonomy
        $existing = get_term_by('slug', $source_term->slug, $target_taxonomy);
        
        $args = array(
            'description' => $source_term->description,
            'slug' => $source_term->slug,
            'parent' => 0
        );
        
        // Handle parent relationship if sync_hierarchy is enabled
        if ($settings['sync_hierarchy'] && $source_term->parent > 0) {
            $source_parent = get_term($source_term->parent, $source_taxonomy);
            if ($source_parent && !is_wp_error($source_parent)) {
                $target_parent = get_term_by('slug', $source_parent->slug, $target_taxonomy);
                if ($target_parent) {
                    $args['parent'] = $target_parent->term_id;
                }
            }
        }
        
        if ($existing) {
            // Update existing term
            $result = wp_update_term($existing->term_id, $target_taxonomy, $args);
        } else {
            // Create new term
            $result = wp_insert_term($source_term->name, $target_taxonomy, $args);
        }
        
        if (is_wp_error($result)) {
            throw new Exception($result->get_error_message());
        }
        
        $term_id = is_array($result) ? $result['term_id'] : $result;
        
        // Copy extended data
        $source_data = EDS_Database::get_category_data($source_term->term_id);
        if ($source_data) {
            $data = array(
                'taxonomy' => $target_taxonomy,
                'position' => $source_data->position,
                'is_enabled' => $source_data->is_enabled,
                'redirection_type' => $source_data->redirection_type,
                'redirection_target' => $source_data->redirection_target,
                'group_access' => $source_data->group_access,
                'meta_data' => $source_data->meta_data
            );
            
            // Only sync images if setting is enabled
            if ($settings['sync_images']) {
                $data['cover_image_id'] = $source_data->cover_image_id;
                $data['thumbnail_image_id'] = $source_data->thumbnail_image_id;
            }
            
            EDS_Database::save_category_data($term_id, $data);
        }
        
        return $term_id;
    }
}
