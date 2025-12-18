<?php
/**
 * Menu Integration Settings Template
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eds_menu_nonce'])) {
    if (!wp_verify_nonce($_POST['eds_menu_nonce'], 'eds_save_menu_settings')) {
        wp_die(__('Security check failed', 'easy-directory-system'));
    }
    
    // Handle takeover toggles
    $menu_locations = get_registered_nav_menus();
    foreach ($menu_locations as $location => $description) {
        $takeover_key = 'takeover_' . $location;
        $taxonomy_key = 'taxonomy_' . $location;
        
        if (isset($_POST[$takeover_key]) && $_POST[$takeover_key] === '1') {
            // Enable takeover
            $taxonomy = isset($_POST[$taxonomy_key]) ? sanitize_text_field($_POST[$taxonomy_key]) : 'category';
            EDS_Menu_Integration::enable_takeover($location, $taxonomy);
        } else {
            // Disable takeover
            if (get_option('eds_menu_takeover_' . $location, false)) {
                EDS_Menu_Integration::disable_takeover($location);
            }
        }
    }
    
    echo '<div class="notice notice-success"><p>' . __('Menu settings saved successfully!', 'easy-directory-system') . '</p></div>';
}

// Get menu locations
$menu_locations = EDS_Menu_Integration::get_menu_locations();

// Get available taxonomies
$taxonomies = get_taxonomies(array('public' => true), 'objects');
?>

<div class="wrap">
    <h1><?php _e('Menu Integration', 'easy-directory-system'); ?></h1>
    
    <div class="eds-settings-intro" style="background: #fff; padding: 20px; margin: 20px 0; border-left: 4px solid #2271b1;">
        <h2><?php _e('Take Over WordPress Menus', 'easy-directory-system'); ?></h2>
        <p><?php _e('Replace WordPress menus with your EDS category structure. Original menus are safely stored and can be restored anytime.', 'easy-directory-system'); ?></p>
        <ul style="list-style: disc; margin-left: 20px;">
            <li><?php _e('Enable takeover for specific menu locations', 'easy-directory-system'); ?></li>
            <li><?php _e('Choose which taxonomy (categories) to display', 'easy-directory-system'); ?></li>
            <li><?php _e('Hierarchical structure preserved automatically', 'easy-directory-system'); ?></li>
            <li><?php _e('Only enabled categories are shown', 'easy-directory-system'); ?></li>
            <li><?php _e('Reset to restore original WordPress menus', 'easy-directory-system'); ?></li>
        </ul>
    </div>
    
    <form method="post" class="eds-menu-settings-form">
        <?php wp_nonce_field('eds_save_menu_settings', 'eds_menu_nonce'); ?>
        
        <div class="eds-settings-section">
            <h2><?php _e('Menu Locations', 'easy-directory-system'); ?></h2>
            
            <?php if (empty($menu_locations)): ?>
                <div class="notice notice-warning inline">
                    <p><?php _e('No menu locations registered by your theme.', 'easy-directory-system'); ?></p>
                </div>
            <?php else: ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width: 50px;"><?php _e('Enable', 'easy-directory-system'); ?></th>
                            <th><?php _e('Location', 'easy-directory-system'); ?></th>
                            <th><?php _e('Description', 'easy-directory-system'); ?></th>
                            <th><?php _e('Current Menu', 'easy-directory-system'); ?></th>
                            <th><?php _e('Taxonomy', 'easy-directory-system'); ?></th>
                            <th><?php _e('Status', 'easy-directory-system'); ?></th>
                            <th style="width: 120px;"><?php _e('Actions', 'easy-directory-system'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($menu_locations as $location_data): ?>
                            <?php 
                            $location = $location_data['location'];
                            $is_active = $location_data['takeover_enabled'];
                            $current_taxonomy = get_option('eds_menu_taxonomy_' . $location, 'category');
                            ?>
                            <tr>
                                <td>
                                    <label class="eds-toggle">
                                        <input type="checkbox" 
                                               name="takeover_<?php echo esc_attr($location); ?>" 
                                               value="1" 
                                               <?php checked($is_active, true); ?>>
                                        <span class="eds-toggle-slider"></span>
                                    </label>
                                </td>
                                <td>
                                    <strong><?php echo esc_html($location); ?></strong>
                                </td>
                                <td>
                                    <?php echo esc_html($location_data['name']); ?>
                                </td>
                                <td>
                                    <?php 
                                    if ($is_active && $location_data['original_menu']) {
                                        $original_menu = wp_get_nav_menu_object($location_data['original_menu']);
                                        echo $original_menu ? esc_html($original_menu->name) : __('None', 'easy-directory-system');
                                    } else {
                                        echo esc_html($location_data['current_menu']);
                                    }
                                    ?>
                                </td>
                                <td>
                                    <select name="taxonomy_<?php echo esc_attr($location); ?>" 
                                            style="width: 100%;"
                                            class="eds-taxonomy-select"
                                            <?php echo !$is_active ? 'disabled' : ''; ?>>
                                        <?php foreach ($taxonomies as $tax): ?>
                                            <option value="<?php echo esc_attr($tax->name); ?>" 
                                                    <?php selected($current_taxonomy, $tax->name); ?>>
                                                <?php 
                                                echo esc_html($tax->labels->name);
                                                // Add helpful hints
                                                if ($tax->name === 'category') {
                                                    echo ' (Blog Posts)';
                                                } elseif ($tax->name === 'product_cat') {
                                                    echo ' (WooCommerce)';
                                                }
                                                ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if ($is_active && $current_taxonomy === 'category'): ?>
                                        <p class="description" style="margin-top: 5px; color: #d63638;">
                                            ⚠️ <?php _e('Showing blog categories. For WooCommerce products, select "Product Categories".', 'easy-directory-system'); ?>
                                        </p>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($is_active): ?>
                                        <span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span>
                                        <span style="color: #46b450;"><?php _e('EDS Active', 'easy-directory-system'); ?></span>
                                    <?php else: ?>
                                        <span class="dashicons dashicons-minus" style="color: #999;"></span>
                                        <span style="color: #999;"><?php _e('WordPress Menu', 'easy-directory-system'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($is_active): ?>
                                        <a href="<?php echo admin_url('admin.php?page=easy-categories-menu&action=reset&location=' . urlencode($location) . '&_wpnonce=' . wp_create_nonce('eds_reset_menu_' . $location)); ?>" 
                                           class="button button-small"
                                           onclick="return confirm('<?php esc_attr_e('Are you sure you want to reset this menu to default?', 'easy-directory-system'); ?>');">
                                            <span class="dashicons dashicons-image-rotate" style="vertical-align: middle;"></span>
                                            <?php _e('Reset', 'easy-directory-system'); ?>
                                        </a>
                                    <?php else: ?>
                                        <span style="color: #999;">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <div class="eds-form-actions" style="margin-top: 20px;">
            <button type="submit" class="button button-primary button-large">
                <span class="dashicons dashicons-saved" style="vertical-align: middle;"></span>
                <?php _e('Save Menu Settings', 'easy-directory-system'); ?>
            </button>
            
            <a href="<?php echo admin_url('nav-menus.php'); ?>" class="button button-large" style="margin-left: 10px;">
                <span class="dashicons dashicons-menu" style="vertical-align: middle;"></span>
                <?php _e('WordPress Menus', 'easy-directory-system'); ?>
            </a>
        </div>
    </form>
    
    <div class="eds-settings-section" style="margin-top: 40px; background: #f8f9fa; padding: 20px; border-radius: 4px;">
        <h3><?php _e('How It Works', 'easy-directory-system'); ?></h3>
        <ol style="margin-left: 20px;">
            <li><?php _e('Enable takeover for a menu location using the toggle', 'easy-directory-system'); ?></li>
            <li><?php _e('Select which taxonomy (category type) to display', 'easy-directory-system'); ?></li>
            <li><?php _e('Save settings - your original menu is safely stored', 'easy-directory-system'); ?></li>
            <li><?php _e('EDS categories replace the menu automatically', 'easy-directory-system'); ?></li>
            <li><?php _e('Click Reset to restore the original WordPress menu', 'easy-directory-system'); ?></li>
        </ol>
        
        <p style="margin-top: 20px;">
            <strong><?php _e('Note:', 'easy-directory-system'); ?></strong>
            <?php _e('Only enabled categories with proper settings will appear in menus. Disabled categories are automatically hidden.', 'easy-directory-system'); ?>
        </p>
    </div>
</div>

<style>
.eds-settings-section {
    background: #fff;
    padding: 20px;
    margin-top: 20px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.eds-settings-section h2 {
    margin-top: 0;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
}

.eds-menu-settings-form select:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.eds-form-actions .button {
    height: auto;
    padding: 10px 20px;
    font-size: 14px;
}

.eds-form-actions .dashicons {
    font-size: 18px;
    width: 18px;
    height: 18px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Enable/disable taxonomy selector based on toggle
    $('.eds-toggle input[type="checkbox"]').on('change', function() {
        var $row = $(this).closest('tr');
        var $select = $row.find('select');
        
        if ($(this).is(':checked')) {
            $select.prop('disabled', false);
        } else {
            $select.prop('disabled', true);
        }
    });
});
</script>
