<?php
/**
 * Settings Template
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Handle table creation
if (isset($_POST['eds_create_tables']) && isset($_POST['eds_tables_nonce'])) {
    if (!wp_verify_nonce($_POST['eds_tables_nonce'], 'eds_create_tables')) {
        wp_die(__('Security check failed', 'easy-directory-system'));
    }
    
    EDS_Database::create_tables();
    echo '<div class="notice notice-success"><p>' . __('Database tables created successfully!', 'easy-directory-system') . '</p></div>';
}

// Handle settings save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eds_settings_nonce'])) {
    if (!wp_verify_nonce($_POST['eds_settings_nonce'], 'eds_save_settings')) {
        wp_die(__('Security check failed', 'easy-directory-system'));
    }
    
    $settings = array(
        'default_redirection' => sanitize_text_field($_POST['default_redirection']),
        'allowed_url_chars' => sanitize_text_field($_POST['allowed_url_chars']),
        'sync_on_save' => isset($_POST['sync_on_save']) ? 1 : 0,
        'enable_multilingual' => isset($_POST['enable_multilingual']) ? 1 : 0,
        'seo_enabled' => isset($_POST['seo_enabled']) ? 1 : 0,
        'auto_generate_meta' => isset($_POST['auto_generate_meta']) ? 1 : 0,
        'default_group_access' => isset($_POST['default_group_access']) ? $_POST['default_group_access'] : array('visitor', 'guest', 'customer')
    );
    
    update_option('eds_settings', $settings);
    
    echo '<div class="notice notice-success"><p>' . __('Settings saved successfully!', 'easy-directory-system') . '</p></div>';
}

// Get current settings
$settings = get_option('eds_settings', array(
    'default_redirection' => '301',
    'allowed_url_chars' => 'letters_numbers_underscores_hyphens',
    'sync_on_save' => false,
    'enable_multilingual' => true,
    'seo_enabled' => true,
    'auto_generate_meta' => false,
    'default_group_access' => array('visitor', 'guest', 'customer')
));
?>

<div class="wrap">
    <h1><?php _e('Easy Categories Settings', 'easy-directory-system'); ?></h1>
    
    <form method="post">
        <?php wp_nonce_field('eds_save_settings', 'eds_settings_nonce'); ?>
        
        <div class="eds-wrap">
            <!-- General Settings -->
            <div class="eds-form-section">
                <h2><?php _e('General Settings', 'easy-directory-system'); ?></h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label><?php _e('Default Redirection Type', 'easy-directory-system'); ?></label>
                        </th>
                        <td>
                            <select name="default_redirection" style="min-width: 300px;">
                                <option value="301" <?php selected($settings['default_redirection'], '301'); ?>>
                                    301 - <?php _e('Permanent Redirection', 'easy-directory-system'); ?>
                                </option>
                                <option value="302" <?php selected($settings['default_redirection'], '302'); ?>>
                                    302 - <?php _e('Temporary Redirection', 'easy-directory-system'); ?>
                                </option>
                                <option value="404" <?php selected($settings['default_redirection'], '404'); ?>>
                                    404 - <?php _e('Not Found', 'easy-directory-system'); ?>
                                </option>
                                <option value="410" <?php selected($settings['default_redirection'], '410'); ?>>
                                    410 - <?php _e('Gone', 'easy-directory-system'); ?>
                                </option>
                            </select>
                            <p class="description">
                                <?php _e('Default redirection behavior when a category is not displayed.', 'easy-directory-system'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label><?php _e('Allowed URL Characters', 'easy-directory-system'); ?></label>
                        </th>
                        <td>
                            <select name="allowed_url_chars" style="min-width: 300px;">
                                <option value="letters_numbers_underscores_hyphens" 
                                        <?php selected($settings['allowed_url_chars'], 'letters_numbers_underscores_hyphens'); ?>>
                                    <?php _e('Letters, Numbers, Underscores, Hyphens', 'easy-directory-system'); ?>
                                </option>
                                <option value="letters_numbers_hyphens" 
                                        <?php selected($settings['allowed_url_chars'], 'letters_numbers_hyphens'); ?>>
                                    <?php _e('Letters, Numbers, Hyphens', 'easy-directory-system'); ?>
                                </option>
                                <option value="letters_numbers" 
                                        <?php selected($settings['allowed_url_chars'], 'letters_numbers'); ?>>
                                    <?php _e('Letters, Numbers', 'easy-directory-system'); ?>
                                </option>
                            </select>
                            <p class="description">
                                <?php _e('Define which characters are allowed in category friendly URLs.', 'easy-directory-system'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label><?php _e('Enable Multilingual Support', 'easy-directory-system'); ?></label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" 
                                       name="enable_multilingual" 
                                       value="1" 
                                       <?php checked($settings['enable_multilingual'], 1); ?>>
                                <?php _e('Enable language tabs for category content', 'easy-directory-system'); ?>
                            </label>
                            <p class="description">
                                <?php _e('Allow translating category names, descriptions, and meta information.', 'easy-directory-system'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- WooCommerce Settings -->
            <?php if (class_exists('WooCommerce')): ?>
            <div class="eds-form-section">
                <h2><?php _e('WooCommerce Integration', 'easy-directory-system'); ?></h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label><?php _e('Auto-sync on Save', 'easy-directory-system'); ?></label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" 
                                       name="sync_on_save" 
                                       value="1" 
                                       <?php checked($settings['sync_on_save'], 1); ?>>
                                <?php _e('Automatically sync with WooCommerce when saving categories', 'easy-directory-system'); ?>
                            </label>
                            <p class="description">
                                <?php _e('When enabled, changes will be automatically synced to WooCommerce product categories.', 'easy-directory-system'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
            <?php endif; ?>
            
            <!-- SEO Settings -->
            <div class="eds-form-section">
                <h2><?php _e('SEO Settings', 'easy-directory-system'); ?></h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label><?php _e('Enable SEO Features', 'easy-directory-system'); ?></label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" 
                                       name="seo_enabled" 
                                       value="1" 
                                       <?php checked($settings['seo_enabled'], 1); ?>>
                                <?php _e('Enable meta titles and descriptions for categories', 'easy-directory-system'); ?>
                            </label>
                            <p class="description">
                                <?php _e('Show SEO fields in category forms and display live preview.', 'easy-directory-system'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label><?php _e('Auto-generate Meta Information', 'easy-directory-system'); ?></label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" 
                                       name="auto_generate_meta" 
                                       value="1" 
                                       <?php checked($settings['auto_generate_meta'], 1); ?>>
                                <?php _e('Automatically generate meta titles/descriptions from category name/description', 'easy-directory-system'); ?>
                            </label>
                            <p class="description">
                                <?php _e('If meta fields are empty, they will be auto-filled based on category content.', 'easy-directory-system'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Access Control Settings -->
            <div class="eds-form-section">
                <h2><?php _e('Default Group Access', 'easy-directory-system'); ?></h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label><?php _e('Default Groups', 'easy-directory-system'); ?></label>
                        </th>
                        <td>
                            <p>
                                <label>
                                    <input type="checkbox" 
                                           name="default_group_access[]" 
                                           value="visitor" 
                                           <?php checked(in_array('visitor', $settings['default_group_access']), true); ?>>
                                    <?php _e('Visitor', 'easy-directory-system'); ?>
                                </label>
                            </p>
                            <p>
                                <label>
                                    <input type="checkbox" 
                                           name="default_group_access[]" 
                                           value="guest" 
                                           <?php checked(in_array('guest', $settings['default_group_access']), true); ?>>
                                    <?php _e('Guest', 'easy-directory-system'); ?>
                                </label>
                            </p>
                            <p>
                                <label>
                                    <input type="checkbox" 
                                           name="default_group_access[]" 
                                           value="customer" 
                                           <?php checked(in_array('customer', $settings['default_group_access']), true); ?>>
                                    <?php _e('Customer', 'easy-directory-system'); ?>
                                </label>
                            </p>
                            <p class="description">
                                <?php _e('Select which user groups can access new categories by default.', 'easy-directory-system'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- System Information -->
            <div class="eds-form-section">
                <h2><?php _e('System Information', 'easy-directory-system'); ?></h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Plugin Version', 'easy-directory-system'); ?></th>
                        <td><?php echo EDS_VERSION; ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('WordPress Version', 'easy-directory-system'); ?></th>
                        <td><?php echo get_bloginfo('version'); ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('WooCommerce', 'easy-directory-system'); ?></th>
                        <td>
                            <?php if (class_exists('WooCommerce')): ?>
                                <span style="color: #28a745;">✓ <?php _e('Active', 'easy-directory-system'); ?></span>
                                (<?php echo defined('WC_VERSION') ? WC_VERSION : 'N/A'; ?>)
                            <?php else: ?>
                                <span style="color: #dc3545;">✗ <?php _e('Not Active', 'easy-directory-system'); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Database Tables', 'easy-directory-system'); ?></th>
                        <td>
                            <?php
                            global $wpdb;
                            $table1 = $wpdb->prefix . 'eds_category_data';
                            $table2 = $wpdb->prefix . 'eds_category_translations';
                            
                            $exists1 = $wpdb->get_var("SHOW TABLES LIKE '$table1'") == $table1;
                            $exists2 = $wpdb->get_var("SHOW TABLES LIKE '$table2'") == $table2;
                            
                            if ($exists1 && $exists2) {
                                echo '<span style="color: #28a745;">✓ ' . __('All tables exist', 'easy-directory-system') . '</span>';
                            } else {
                                echo '<span style="color: #dc3545;">✗ ' . __('Missing tables', 'easy-directory-system') . '</span>';
                                echo '<form method="post" style="display: inline; margin-left: 15px;">';
                                wp_nonce_field('eds_create_tables', 'eds_tables_nonce');
                                echo '<button type="submit" name="eds_create_tables" class="button button-secondary">';
                                echo __('Create Tables Now', 'easy-directory-system');
                                echo '</button>';
                                echo '</form>';
                            }
                            ?>
                            <br>
                            <small>
                                <?php _e('Tables:', 'easy-directory-system'); ?> 
                                <code><?php echo $table1; ?></code>
                                <?php echo $exists1 ? '<span style="color: #28a745;">✓</span>' : '<span style="color: #dc3545;">✗</span>'; ?>
                                , 
                                <code><?php echo $table2; ?></code>
                                <?php echo $exists2 ? '<span style="color: #28a745;">✓</span>' : '<span style="color: #dc3545;">✗</span>'; ?>
                            </small>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Save Button -->
            <p class="submit">
                <button type="submit" class="button button-primary button-large">
                    <?php _e('Save Settings', 'easy-directory-system'); ?>
                </button>
            </p>
        </div>
    </form>
</div>

<style>
.eds-form-section {
    background: #fff;
    padding: 20px;
    margin-bottom: 20px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.eds-form-section h2 {
    margin-top: 0;
    font-size: 18px;
    font-weight: 600;
    border-bottom: 2px solid #0073aa;
    padding-bottom: 10px;
    margin-bottom: 15px;
}

.form-table th {
    width: 200px;
    padding: 15px 10px 15px 0;
    font-weight: 600;
}

.form-table td {
    padding: 15px 10px;
}
</style>
