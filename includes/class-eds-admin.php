<?php
/**
 * Admin interface for Easy Directory System
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class EDS_Admin {
    
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
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Main menu page
        add_menu_page(
            __('Easy Categories', 'easy-directory-system'),
            __('Easy Categories', 'easy-directory-system'),
            'manage_categories',
            'easy-categories',
            array($this, 'render_categories_page'),
            'dashicons-category',
            25
        );
        
        // Categories list (same as main)
        add_submenu_page(
            'easy-categories',
            __('All Categories', 'easy-directory-system'),
            __('All Categories', 'easy-directory-system'),
            'manage_categories',
            'easy-categories',
            array($this, 'render_categories_page')
        );
        
        // Add new category
        add_submenu_page(
            'easy-categories',
            __('Add New Category', 'easy-directory-system'),
            __('Add New Category', 'easy-directory-system'),
            'manage_categories',
            'easy-categories-add',
            array($this, 'render_add_category_page')
        );
        
        // Menu Sync
        add_submenu_page(
            'easy-categories',
            __('Sync to Menu', 'easy-directory-system'),
            __('Sync to Menu', 'easy-directory-system'),
            'manage_options',
            'easy-categories-menu-sync',
            array($this, 'render_menu_sync_page')
        );
        
        // Settings
        add_submenu_page(
            'easy-categories',
            __('Settings', 'easy-directory-system'),
            __('Settings', 'easy-directory-system'),
            'manage_options',
            'easy-categories-settings',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Render categories list page
     */
    public function render_categories_page() {
        require_once EDS_PLUGIN_DIR . 'templates/categories-list.php';
    }
    
    /**
     * Render add/edit category page
     */
    public function render_add_category_page() {
        require_once EDS_PLUGIN_DIR . 'templates/category-form.php';
    }
    
    /**
     * Render menu sync page
     */
    public function render_menu_sync_page() {
        require_once EDS_PLUGIN_DIR . 'templates/menu-sync.php';
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        require_once EDS_PLUGIN_DIR . 'templates/settings.php';
    }
}
