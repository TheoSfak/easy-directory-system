<?php
/**
 * Plugin Name: Easy Directory System
 * Plugin URI: https://github.com/TheoSfak/easy-directory-system
 * Description: Advanced category management system for WordPress with PrestaShop-style interface. Manage categories with SEO tools, WooCommerce synchronization, and WordPress menu integration.
 * Version: 1.0.6
 * Author: Theo Sfak
 * Author URI: https://github.com/TheoSfak
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: easy-directory-system
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('EDS_VERSION', '1.0.6');
define('EDS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EDS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('EDS_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Easy Directory System Class
 */
class Easy_Directory_System {
    
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
        $this->init_hooks();
        $this->load_dependencies();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Load plugin text domain
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        
        // Admin scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }
    
    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        // Load core files
        require_once EDS_PLUGIN_DIR . 'includes/class-eds-database.php';
        require_once EDS_PLUGIN_DIR . 'includes/class-eds-admin.php';
        require_once EDS_PLUGIN_DIR . 'includes/class-eds-category.php';
        require_once EDS_PLUGIN_DIR . 'includes/class-eds-woocommerce-sync.php';
        require_once EDS_PLUGIN_DIR . 'includes/class-eds-ajax.php';
        require_once EDS_PLUGIN_DIR . 'includes/class-eds-menu-sync.php';
        
        // Initialize components
        if (is_admin()) {
            EDS_Admin::get_instance();
            EDS_Ajax::get_instance();
        }
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create database tables
        EDS_Database::create_tables();
        
        // Set default options
        add_option('eds_version', EDS_VERSION);
        add_option('eds_settings', array(
            'default_redirection' => '301',
            'allowed_url_chars' => 'letters_numbers_underscores_hyphens',
            'sync_on_save' => false,
            'enable_multilingual' => true
        ));
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Load plugin text domain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'easy-directory-system',
            false,
            dirname(EDS_PLUGIN_BASENAME) . '/languages'
        );
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        // Only load on our plugin pages
        if (strpos($hook, 'easy-categories') === false) {
            return;
        }
        
        // Enqueue CSS
        wp_enqueue_style(
            'eds-admin-style',
            EDS_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            EDS_VERSION
        );
        
        // Enqueue JS
        wp_enqueue_script(
            'eds-admin-script',
            EDS_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery', 'jquery-ui-sortable'),
            EDS_VERSION . '.' . time(), // Force reload by adding timestamp
            true
        );
        
        // Get settings
        $eds_settings = get_option('eds_settings', array());
        $allowed_url_chars = isset($eds_settings['allowed_url_chars']) ? $eds_settings['allowed_url_chars'] : 'letters_numbers_underscores_hyphens';
        $sync_mode = isset($eds_settings['sync_mode']) ? $eds_settings['sync_mode'] : 'add_only';
        
        // Localize script
        wp_localize_script('eds-admin-script', 'edsAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('eds_ajax_nonce'),
            'settings' => array(
                'allowed_url_chars' => $allowed_url_chars,
                'sync_mode' => $sync_mode
            ),
            'strings' => array(
                'confirm_delete' => __('Are you sure you want to delete this category?', 'easy-directory-system'),
                'confirm_bulk_delete' => __('Are you sure you want to delete selected categories?', 'easy-directory-system'),
                'sync_success' => __('Categories synchronized successfully!', 'easy-directory-system'),
                'sync_error' => __('An error occurred during synchronization.', 'easy-directory-system')
            )
        ));
        
        // Enqueue media uploader
        wp_enqueue_media();
    }
}

/**
 * Initialize the plugin
 */
function eds_init() {
    return Easy_Directory_System::get_instance();
}

// Start the plugin
add_action('plugins_loaded', 'eds_init');
