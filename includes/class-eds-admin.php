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
        
        // Add new category (hidden from menu, accessed via button)
        add_submenu_page(
            null, // null parent = hidden from menu but accessible via URL
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
        
        // Support & Donate
        add_submenu_page(
            'easy-categories',
            __('Support & Donate', 'easy-directory-system'),
            '<span style="color:#f18500;">❤️ ' . __('Support', 'easy-directory-system') . '</span>',
            'manage_options',
            'easy-categories-donate',
            array($this, 'render_donate_page')
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
    
    /**
     * Render donate page
     */
    public function render_donate_page() {
        require_once EDS_PLUGIN_DIR . 'templates/donate.php';
    }
    
    /**
     * Render footer with credits and donation
     */
    public static function render_footer() {
        ?>
        <div style="margin-top: 40px; padding: 20px; background: #f8f9fa; border-left: 4px solid #2271b1; border-radius: 4px;">
            <p style="margin: 0; font-size: 14px; color: #666;">
                <strong><?php _e('Created by Theodore Sfakianakis', 'easy-directory-system'); ?></strong> | 
                <a href="https://github.com/TheoSfak/easy-directory-system" target="_blank" style="color: #2271b1; text-decoration: none;"><?php _e('GitHub', 'easy-directory-system'); ?></a>
            </p>
            <p style="margin: 10px 0 0 0; font-size: 13px; color: #999;">
                ❤️ <?php _e('If you find this plugin helpful, please consider supporting its development:', 'easy-directory-system'); ?>
                <strong>PayPal:</strong> <a href="https://www.paypal.com/paypalme/TheodoreSfakianakis" target="_blank" style="color: #2271b1;">paypal.me/TheodoreSfakianakis</a> | 
                <strong>Revolut:</strong> <a href="https://revolut.me/theodocmx" target="_blank" style="color: #2271b1;">revolut.me/theodocmx</a>
            </p>
        </div>
        <?php
    }
}
