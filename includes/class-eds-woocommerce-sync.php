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
                $woo_cat = self::sync_single_category($wp_cat, 'category', 'product_cat');
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
     * Sync WooCommerce categories to WordPress
     */
    public static function sync_from_woocommerce() {
        if (!self::is_woocommerce_active()) {
            return array('success' => false, 'message' => __('WooCommerce is not active', 'easy-directory-system'));
        }
        
        $woo_categories = get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => false
        ));
        
        $synced = 0;
        $errors = array();
        
        foreach ($woo_categories as $woo_cat) {
            try {
                $wp_cat = self::sync_single_category($woo_cat, 'product_cat', 'category');
                if ($wp_cat) {
                    $synced++;
                }
            } catch (Exception $e) {
                $errors[] = sprintf(__('Error syncing %s: %s', 'easy-directory-system'), $woo_cat->name, $e->getMessage());
            }
        }
        
        return array(
            'success' => true,
            'synced' => $synced,
            'errors' => $errors,
            'message' => sprintf(__('Synced %d categories from WooCommerce', 'easy-directory-system'), $synced)
        );
    }
    
    /**
     * Sync single category
     */
    private static function sync_single_category($source_term, $source_taxonomy, $target_taxonomy) {
        // Check if category already exists in target taxonomy
        $existing = get_term_by('slug', $source_term->slug, $target_taxonomy);
        
        $args = array(
            'description' => $source_term->description,
            'slug' => $source_term->slug,
            'parent' => 0
        );
        
        // Handle parent relationship
        if ($source_term->parent > 0) {
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
                'cover_image_id' => $source_data->cover_image_id,
                'thumbnail_image_id' => $source_data->thumbnail_image_id,
                'position' => $source_data->position,
                'is_enabled' => $source_data->is_enabled,
                'redirection_type' => $source_data->redirection_type,
                'redirection_target' => $source_data->redirection_target,
                'group_access' => $source_data->group_access,
                'meta_data' => $source_data->meta_data
            );
            
            EDS_Database::save_category_data($term_id, $data);
        }
        
        return $term_id;
    }
}
