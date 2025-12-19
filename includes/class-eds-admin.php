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
        add_action('admin_head', array($this, 'add_admin_styles'));
    }
    
    /**
     * Add custom admin styles
     */
    public function add_admin_styles() {
        // Only on our plugin pages
        $screen = get_current_screen();
        if (!$screen || strpos($screen->id, 'easy-categories') === false) {
            return;
        }
        ?>
        <style>
            /* Enhanced styling for Easy Categories */
            .eds-wrap {
                background: #f8f9fa;
                padding: 20px;
                border-radius: 8px;
            }
            
            .eds-form-section {
                background: #fff;
                border-radius: 8px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.08);
                transition: box-shadow 0.3s ease;
            }
            
            .eds-form-section:hover {
                box-shadow: 0 4px 12px rgba(0,0,0,0.12);
            }
            
            .eds-form-section h2 {
                color: #1e293b;
                font-weight: 600;
                border-bottom: 2px solid #e2e8f0;
            }
            
            .button-primary {
                background: linear-gradient(135deg, #2271b1 0%, #1e5a8e 100%) !important;
                border: none !important;
                box-shadow: 0 2px 4px rgba(34,113,177,0.3) !important;
                transition: all 0.3s ease !important;
            }
            
            .button-primary:hover {
                background: linear-gradient(135deg, #1e5a8e 0%, #16496d 100%) !important;
                box-shadow: 0 4px 8px rgba(34,113,177,0.4) !important;
                transform: translateY(-1px);
            }
            
            .button-secondary {
                background: #fff !important;
                border: 1px solid #cbd5e0 !important;
                transition: all 0.3s ease !important;
            }
            
            .button-secondary:hover {
                background: #f8f9fa !important;
                border-color: #2271b1 !important;
                color: #2271b1 !important;
                transform: translateY(-1px);
            }
            
            .eds-table-container {
                background: #fff;
                border-radius: 8px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.08);
                overflow: hidden;
            }
            
            .eds-table th {
                background: linear-gradient(to bottom, #f8f9fa 0%, #e9ecef 100%);
                font-weight: 600;
                color: #1e293b;
                text-transform: uppercase;
                font-size: 11px;
                letter-spacing: 0.5px;
                padding: 12px 8px !important;
            }
            
            .eds-table tr:hover {
                background: #f8fafc;
                transition: background 0.2s ease;
            }
            
            .eds-toggle input:checked + .slider {
                background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            }
            
            .wrap h1 {
                color: #1e293b;
                font-weight: 700;
            }
            
            .form-table th {
                color: #475569;
                font-weight: 600;
            }
            
            .form-table input[type="text"],
            .form-table textarea,
            .form-table select {
                border: 1px solid #cbd5e0;
                border-radius: 6px;
                transition: all 0.3s ease;
            }
            
            .form-table input[type="text"]:focus,
            .form-table textarea:focus,
            .form-table select:focus {
                border-color: #2271b1;
                box-shadow: 0 0 0 3px rgba(34,113,177,0.1);
                outline: none;
            }
            
            .notice {
                border-radius: 6px;
                border-left-width: 4px;
            }
            
            /* Submenu icon alignment */
            #adminmenu .wp-submenu a span.dashicons {
                vertical-align: text-top;
            }
        </style>
        <?php
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
            '<span class="dashicons dashicons-list-view" style="font-size: 16px; width: 16px; height: 16px; margin-right: 5px;"></span>' . __('All Categories', 'easy-directory-system'),
            'manage_categories',
            'easy-categories',
            array($this, 'render_categories_page')
        );
        
        // Add new category
        add_submenu_page(
            'easy-categories',
            __('Add New Category', 'easy-directory-system'),
            '<span class="dashicons dashicons-plus-alt" style="font-size: 16px; width: 16px; height: 16px; margin-right: 5px;"></span>' . __('Add New Category', 'easy-directory-system'),
            'manage_categories',
            'easy-categories-add',
            array($this, 'render_add_category_page')
        );
        
        // Menu Sync
        add_submenu_page(
            'easy-categories',
            __('Sync to Menu', 'easy-directory-system'),
            '<span class="dashicons dashicons-menu" style="font-size: 16px; width: 16px; height: 16px; margin-right: 5px;"></span>' . __('Sync to Menu', 'easy-directory-system'),
            'manage_options',
            'easy-categories-menu-sync',
            array($this, 'render_menu_sync_page')
        );
        
        // Settings
        add_submenu_page(
            'easy-categories',
            __('Settings', 'easy-directory-system'),
            '<span class="dashicons dashicons-admin-settings" style="font-size: 16px; width: 16px; height: 16px; margin-right: 5px;"></span>' . __('Settings', 'easy-directory-system'),
            'manage_options',
            'easy-categories-settings',
            array($this, 'render_settings_page')
        );
        
        // Import/Export
        add_submenu_page(
            'easy-categories',
            __('Import/Export', 'easy-directory-system'),
            '<span class="dashicons dashicons-database-import" style="font-size: 16px; width: 16px; height: 16px; margin-right: 5px;"></span>' . __('Import/Export', 'easy-directory-system'),
            'manage_options',
            'easy-categories-import-export',
            array($this, 'render_import_export_page')
        );
        
        // Demo Data
        add_submenu_page(
            'easy-categories',
            __('Demo Data', 'easy-directory-system'),
            '<span class="dashicons dashicons-download" style="font-size: 16px; width: 16px; height: 16px; margin-right: 5px; color:#10b981;"></span><span style="color:#10b981;">' . __('Demo Data', 'easy-directory-system') . '</span>',
            'manage_options',
            'easy-categories-demo',
            array($this, 'render_demo_page')
        );
        
        // How To
        add_submenu_page(
            'easy-categories',
            __('How To', 'easy-directory-system'),
            '<span class="dashicons dashicons-book-alt" style="font-size: 16px; width: 16px; height: 16px; margin-right: 5px; color:#2271b1;"></span><span style="color:#2271b1;">' . __('How To', 'easy-directory-system') . '</span>',
            'manage_categories',
            'easy-categories-howto',
            array($this, 'render_howto_page')
        );
        
        // Support & Donate
        add_submenu_page(
            'easy-categories',
            __('Support & Donate', 'easy-directory-system'),
            '<span class="dashicons dashicons-heart" style="font-size: 16px; width: 16px; height: 16px; margin-right: 5px; color:#f18500;"></span><span style="color:#f18500;">' . __('Support', 'easy-directory-system') . '</span>',
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
     * Render import/export page
     */
    public function render_import_export_page() {
        // Handle export BEFORE any output
        if (isset($_POST['eds_export']) && check_admin_referer('eds_export', 'eds_export_nonce')) {
            $this->handle_export();
        }
        
        require_once EDS_PLUGIN_DIR . 'templates/import-export.php';
    }
    
    /**
     * Handle category export
     */
    private function handle_export() {
        $taxonomy = sanitize_text_field($_POST['taxonomy']);
        $export_format = sanitize_text_field($_POST['export_format']);
        
        $categories = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
        ));
        
        $export_data = array();
        foreach ($categories as $category) {
            // Get extended data from database
            $extended_data = EDS_Database::get_category_data($category->term_id);
            
            // Get parent name if exists
            $parent_name = '';
            if ($category->parent) {
                $parent_term = get_term($category->parent, $taxonomy);
                if (!is_wp_error($parent_term)) {
                    $parent_name = $parent_term->name;
                }
            }
            
            $export_data[] = array(
                'term_id' => $category->term_id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'parent' => $category->parent,
                'parent_name' => $parent_name,
                'extended_data' => $extended_data,
                'is_enabled' => isset($extended_data->is_enabled) ? $extended_data->is_enabled : 1,
                'meta_title' => get_term_meta($category->term_id, 'eds_meta_title', true),
                'meta_description' => get_term_meta($category->term_id, 'eds_meta_description', true),
                'position' => isset($extended_data->position) ? $extended_data->position : '',
            );
        }
        
        if ($export_format === 'csv') {
            // CSV Export
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="easy-categories-' . $taxonomy . '-' . date('Y-m-d') . '.csv"');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            $output = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // CSV Headers
            fputcsv($output, array(
                'ID',
                'Name',
                'Slug',
                'Description',
                'Parent ID',
                'Parent Name',
                'Enabled',
                'Meta Title',
                'Meta Description',
                'Position'
            ));
            
            // CSV Data
            foreach ($export_data as $row) {
                fputcsv($output, array(
                    $row['term_id'],
                    $row['name'],
                    $row['slug'],
                    strip_tags($row['description']),
                    $row['parent'],
                    $row['parent_name'],
                    $row['is_enabled'] ? 'Yes' : 'No',
                    $row['meta_title'],
                    strip_tags($row['meta_description']),
                    $row['position']
                ));
            }
            
            fclose($output);
            exit;
        } else {
            // JSON Export
            header('Content-Type: application/json; charset=utf-8');
            header('Content-Disposition: attachment; filename="easy-categories-' . $taxonomy . '-' . date('Y-m-d') . '.json"');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            echo json_encode($export_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
    
    /**
     * Render demo page
     */
    public function render_demo_page() {
        require_once EDS_PLUGIN_DIR . 'templates/demo.php';
    }
    
    /**
     * Render how-to page
     */
    public function render_howto_page() {
        require_once EDS_PLUGIN_DIR . 'templates/howto.php';
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
