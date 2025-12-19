<?php
/**
 * Category operations for Easy Directory System
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class EDS_Category {
    
    /**
     * Get all categories with extended data
     */
    public static function get_all_categories($taxonomy = 'category', $args = array()) {
        $defaults = array(
            'taxonomy' => $taxonomy,
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => false,
            'number' => 0  // Get ALL terms without limit
        );
        
        $args = wp_parse_args($args, $defaults);
        $terms = get_terms($args);
        
        if (is_wp_error($terms)) {
            return array();
        }
        
        // Enhance with extended data
        foreach ($terms as &$term) {
            $extended = EDS_Database::get_category_data($term->term_id);
            $term->extended_data = $extended;
            $term->product_count = self::get_product_count($term->term_id, $taxonomy);
        }
        
        return $terms;
    }
    
    /**
     * Get category by ID
     */
    public static function get_category($term_id, $taxonomy = 'category') {
        $term = get_term($term_id, $taxonomy);
        
        if (is_wp_error($term)) {
            return null;
        }
        
        $term->extended_data = EDS_Database::get_category_data($term_id);
        $term->product_count = self::get_product_count($term_id, $taxonomy);
        
        return $term;
    }
    
    /**
     * Get product count for category
     */
    public static function get_product_count($term_id, $taxonomy = 'category') {
        $args = array(
            'post_type' => ($taxonomy === 'product_cat') ? 'product' : 'post',
            'tax_query' => array(
                array(
                    'taxonomy' => $taxonomy,
                    'field' => 'term_id',
                    'terms' => $term_id
                )
            ),
            'posts_per_page' => -1,
            'fields' => 'ids'
        );
        
        $query = new WP_Query($args);
        return $query->found_posts;
    }
    
    /**
     * Get category tree structure
     */
    public static function get_category_tree($taxonomy = 'category', $parent = 0) {
        $args = array(
            'taxonomy' => $taxonomy,
            'parent' => $parent,
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => false
        );
        
        $terms = get_terms($args);
        $tree = array();
        
        foreach ($terms as $term) {
            $term->children = self::get_category_tree($taxonomy, $term->term_id);
            $term->extended_data = EDS_Database::get_category_data($term->term_id);
            $tree[] = $term;
        }
        
        return $tree;
    }
    
    /**
     * Get category statistics
     */
    public static function get_statistics($taxonomy = 'category') {
        global $wpdb;
        
        $all_categories = self::get_all_categories($taxonomy);
        
        $stats = array(
            'total' => count($all_categories),
            'disabled' => 0,
            'empty' => 0,
            'top_category' => null,
            'average_products' => 0
        );
        
        $total_products = 0;
        $max_products = 0;
        
        foreach ($all_categories as $category) {
            // Count disabled
            if ($category->extended_data && !$category->extended_data->is_enabled) {
                $stats['disabled']++;
            }
            
            // Count empty
            if ($category->product_count == 0) {
                $stats['empty']++;
            }
            
            // Track top category
            if ($category->product_count > $max_products) {
                $max_products = $category->product_count;
                $stats['top_category'] = $category;
            }
            
            $total_products += $category->product_count;
        }
        
        // Calculate average
        if ($stats['total'] > 0) {
            $stats['average_products'] = round($total_products / $stats['total'], 2);
        }
        
        return $stats;
    }
}
