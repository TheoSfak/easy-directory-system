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
        
        // Menu Integration (old takeover method - keeping for backward compatibility)
        add_submenu_page(
            'easy-categories',
            __('Menu Takeover', 'easy-directory-system'),
            __('Menu Takeover', 'easy-directory-system'),
            'manage_options',
            'easy-categories-menu',
            array($this, 'render_menu_page')
        );
        
        // Menu Sync (new recommended method)
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
     * Render menu integration page
     */
    public function render_menu_page() {
        // Handle reset action
        if (isset($_GET['action']) && $_GET['action'] === 'reset' && isset($_GET['location'])) {
            $location = sanitize_text_field($_GET['location']);
            $nonce = isset($_GET['_wpnonce']) ? $_GET['_wpnonce'] : '';
            
            if (wp_verify_nonce($nonce, 'eds_reset_menu_' . $location)) {
                EDS_Menu_Integration::disable_takeover($location);
                wp_redirect(admin_url('admin.php?page=easy-categories-menu&reset=success'));
                exit;
            }
        }
        
        // Show success message
        if (isset($_GET['reset']) && $_GET['reset'] === 'success') {
            add_settings_error('eds_menu', 'eds_menu_reset', __('Menu successfully reset to default!', 'easy-directory-system'), 'success');
        }
        
        settings_errors('eds_menu');
        require_once EDS_PLUGIN_DIR . 'templates/menu-integration.php';
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
