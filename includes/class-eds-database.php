<?php
/**
 * Database operations for Easy Directory System
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class EDS_Database {
    
    /**
     * Create database tables
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'eds_category_data';
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            term_id bigint(20) NOT NULL,
            taxonomy varchar(32) NOT NULL DEFAULT 'category',
            cover_image_id bigint(20) DEFAULT NULL,
            thumbnail_image_id bigint(20) DEFAULT NULL,
            position int(11) DEFAULT 0,
            is_enabled tinyint(1) DEFAULT 1,
            redirection_type varchar(10) DEFAULT '301',
            redirection_target bigint(20) DEFAULT NULL,
            group_access text DEFAULT NULL,
            meta_data longtext DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY term_id (term_id),
            KEY taxonomy (taxonomy),
            KEY position (position)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $result1 = dbDelta($sql);
        
        // Create translations table for multilingual support
        $translations_table = $wpdb->prefix . 'eds_category_translations';
        
        $sql_translations = "CREATE TABLE IF NOT EXISTS $translations_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            term_id bigint(20) NOT NULL,
            language varchar(10) NOT NULL,
            meta_title varchar(255) DEFAULT NULL,
            meta_description text DEFAULT NULL,
            additional_description longtext DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY term_language (term_id, language),
            KEY term_id (term_id)
        ) $charset_collate;";
        
        $result2 = dbDelta($sql_translations);
        
        // Log table creation results
        error_log('EDS Database: Main table creation - ' . print_r($result1, true));
        error_log('EDS Database: Translations table creation - ' . print_r($result2, true));
        
        // Verify tables were created
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
        $trans_exists = $wpdb->get_var("SHOW TABLES LIKE '$translations_table'");
        
        if (!$table_exists || !$trans_exists) {
            throw new Exception('Failed to create database tables. Check database permissions.');
        }
        
        return true;
    }
    
    /**
     * Get category extended data
     */
    public static function get_category_data($term_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'eds_category_data';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE term_id = %d",
            $term_id
        ));
    }
    
    /**
     * Save category extended data
     */
    public static function save_category_data($term_id, $data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'eds_category_data';
        
        $existing = self::get_category_data($term_id);
        
        $data['term_id'] = $term_id;
        
        if ($existing) {
            $wpdb->update(
                $table_name,
                $data,
                array('term_id' => $term_id),
                null,
                array('%d')
            );
        } else {
            $wpdb->insert($table_name, $data);
        }
        
        return true;
    }
    
    /**
     * Get category translation
     */
    public static function get_translation($term_id, $language) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'eds_category_translations';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE term_id = %d AND language = %s",
            $term_id,
            $language
        ));
    }
    
    /**
     * Save category translation
     */
    public static function save_translation($term_id, $language, $data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'eds_category_translations';
        
        $existing = self::get_translation($term_id, $language);
        
        $data['term_id'] = $term_id;
        $data['language'] = $language;
        
        if ($existing) {
            $wpdb->update(
                $table_name,
                $data,
                array('term_id' => $term_id, 'language' => $language),
                null,
                array('%d', '%s')
            );
        } else {
            $wpdb->insert($table_name, $data);
        }
        
        return true;
    }
    
    /**
     * Delete category data
     */
    public static function delete_category_data($term_id) {
        global $wpdb;
        
        $wpdb->delete(
            $wpdb->prefix . 'eds_category_data',
            array('term_id' => $term_id),
            array('%d')
        );
        
        $wpdb->delete(
            $wpdb->prefix . 'eds_category_translations',
            array('term_id' => $term_id),
            array('%d')
        );
        
        return true;
    }
}
