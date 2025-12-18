<?php
/**
 * Menu Sync Template - Create WordPress menus from EDS categories
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete_menu' && isset($_GET['menu_id'])) {
    $menu_id = intval($_GET['menu_id']);
    $nonce = isset($_GET['_wpnonce']) ? $_GET['_wpnonce'] : '';
    
    if (wp_verify_nonce($nonce, 'eds_delete_menu_' . $menu_id)) {
        $result = wp_delete_nav_menu($menu_id);
        if ($result && !is_wp_error($result)) {
            echo '<div class="notice notice-success"><p>' . __('Menu deleted successfully!', 'easy-directory-system') . '</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>' . __('Failed to delete menu', 'easy-directory-system') . '</p></div>';
        }
    }
}

// Handle sync action
if (isset($_POST['eds_sync_menu_nonce']) && wp_verify_nonce($_POST['eds_sync_menu_nonce'], 'eds_sync_menu')) {
    $taxonomy = isset($_POST['taxonomy']) ? sanitize_text_field($_POST['taxonomy']) : 'product_cat';
    $menu_name = isset($_POST['menu_name']) ? sanitize_text_field($_POST['menu_name']) : '';
    
    $menu_id = EDS_Menu_Sync::sync_menu($taxonomy, $menu_name);
    
    if ($menu_id) {
        echo '<div class="notice notice-success"><p>' . __('Menu created/updated successfully!', 'easy-directory-system') . ' <a href="' . admin_url('nav-menus.php?action=edit&menu=' . $menu_id) . '">' . __('Edit Menu', 'easy-directory-system') . '</a></p></div>';
    } else {
        echo '<div class="notice notice-error"><p>' . __('Failed to create menu', 'easy-directory-system') . '</p></div>';
    }
}

// Get available taxonomies
$taxonomies = get_taxonomies(array('public' => true), 'objects');
?>

<div class="wrap">
    <h1><?php _e('Sync Categories to Menu', 'easy-directory-system'); ?></h1>
    
    <div class="eds-settings-intro" style="background: #fff; padding: 20px; margin: 20px 0; border-left: 4px solid #2271b1;">
        <h2><?php _e('Create WordPress Menus from EDS Categories', 'easy-directory-system'); ?></h2>
        <p><?php _e('This will create a real WordPress menu that you can assign in Appearance → Menus. Only enabled categories will be included, sorted by position.', 'easy-directory-system'); ?></p>
        
        <div style="background: #f8f9fa; padding: 15px; margin-top: 15px; border-left: 4px solid #00a32a;">
            <h3 style="margin-top: 0;"><?php _e('How to use:', 'easy-directory-system'); ?></h3>
            <ol style="margin-left: 20px;">
                <li><?php _e('Select the taxonomy (e.g., Product Categories)', 'easy-directory-system'); ?></li>
                <li><?php _e('Enter a menu name (e.g., "Shop Menu")', 'easy-directory-system'); ?></li>
                <li><?php _e('Click "Create/Update Menu"', 'easy-directory-system'); ?></li>
                <li><?php _e('Go to Appearance → Menus', 'easy-directory-system'); ?></li>
                <li><?php _e('Select your new menu and assign it to a location (e.g., "Main Menu")', 'easy-directory-system'); ?></li>
                <li><?php _e('Save and view your site!', 'easy-directory-system'); ?></li>
            </ol>
        </div>
    </div>
    
    <form method="post" class="eds-menu-sync-form">
        <?php wp_nonce_field('eds_sync_menu', 'eds_sync_menu_nonce'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="taxonomy"><?php _e('Taxonomy', 'easy-directory-system'); ?></label>
                </th>
                <td>
                    <select name="taxonomy" id="taxonomy" class="regular-text">
                        <?php foreach ($taxonomies as $tax): ?>
                            <option value="<?php echo esc_attr($tax->name); ?>" <?php selected('product_cat', $tax->name); ?>>
                                <?php 
                                echo esc_html($tax->labels->name);
                                if ($tax->name === 'category') {
                                    echo ' (Blog Posts)';
                                } elseif ($tax->name === 'product_cat') {
                                    echo ' (WooCommerce)';
                                }
                                ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php _e('Select which category type to create menu from', 'easy-directory-system'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="menu_name"><?php _e('Menu Name', 'easy-directory-system'); ?></label>
                </th>
                <td>
                    <input type="text" name="menu_name" id="menu_name" class="regular-text" 
                           placeholder="e.g., Shop Menu, Product Categories">
                    <p class="description"><?php _e('Leave empty to auto-generate name (e.g., "EDS - Product Categories")', 'easy-directory-system'); ?></p>
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <button type="submit" class="button button-primary button-large">
                <span class="dashicons dashicons-update" style="margin-top: 4px;"></span>
                <?php _e('Create/Update Menu', 'easy-directory-system'); ?>
            </button>
            
            <a href="<?php echo admin_url('nav-menus.php'); ?>" class="button button-large" style="margin-left: 10px;">
                <span class="dashicons dashicons-menu" style="margin-top: 4px;"></span>
                <?php _e('Go to WordPress Menus', 'easy-directory-system'); ?>
            </a>
        </p>
    </form>
    
    <div class="eds-existing-menus" style="margin-top: 40px;">
        <h2><?php _e('Existing WordPress Menus', 'easy-directory-system'); ?></h2>
        <?php
        $menus = wp_get_nav_menus();
        if (!empty($menus)):
        ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Menu Name', 'easy-directory-system'); ?></th>
                    <th><?php _e('Items', 'easy-directory-system'); ?></th>
                    <th><?php _e('Locations', 'easy-directory-system'); ?></th>
                    <th><?php _e('Actions', 'easy-directory-system'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $locations = get_nav_menu_locations();
                $location_names = get_registered_nav_menus();
                foreach ($menus as $menu): 
                    $items = wp_get_nav_menu_items($menu->term_id);
                    $item_count = $items ? count($items) : 0;
                    
                    // Find locations
                    $assigned_locations = array();
                    foreach ($locations as $loc => $menu_id) {
                        if ($menu_id == $menu->term_id) {
                            $assigned_locations[] = isset($location_names[$loc]) ? $location_names[$loc] : $loc;
                        }
                    }
                ?>
                <tr>
                    <td><strong><?php echo esc_html($menu->name); ?></strong></td>
                    <td><?php echo $item_count; ?> items</td>
                    <td>
                        <?php 
                        if (!empty($assigned_locations)) {
                            echo esc_html(implode(', ', $assigned_locations));
                        } else {
                            echo '<span style="color: #999;">' . __('Not assigned', 'easy-directory-system') . '</span>';
                        }
                        ?>
                    </td>
                    <td>
                        <a href="<?php echo admin_url('nav-menus.php?action=edit&menu=' . $menu->term_id); ?>" 
                           class="button button-small">
                            <?php _e('Edit', 'easy-directory-system'); ?>
                        </a>
                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=easy-categories-menu-sync&action=delete_menu&menu_id=' . $menu->term_id), 'eds_delete_menu_' . $menu->term_id); ?>" 
                           class="button button-small"
                           onclick="return confirm('<?php echo esc_js(__('Are you sure you want to delete this menu?', 'easy-directory-system')); ?>');"
                           style="color: #b32d2e;">
                            <?php _e('Delete', 'easy-directory-system'); ?>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p><?php _e('No menus created yet.', 'easy-directory-system'); ?></p>
        <?php endif; ?>
    </div>
</div>

<style>
.eds-menu-sync-form .button-large {
    height: auto;
    padding: 10px 20px;
    font-size: 14px;
}

.eds-menu-sync-form .dashicons {
    font-size: 18px;
    width: 18px;
    height: 18px;
}
</style>
