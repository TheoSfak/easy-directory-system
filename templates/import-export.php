<?php
/**
 * Import/Export Template
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Handle import
if (isset($_POST['eds_import']) && check_admin_referer('eds_import', 'eds_import_nonce')) {
    if (isset($_FILES['import_file']) && $_FILES['import_file']['error'] === UPLOAD_ERR_OK) {
        $file_content = file_get_contents($_FILES['import_file']['tmp_name']);
        $import_data = json_decode($file_content, true);
        
        if ($import_data && is_array($import_data)) {
            $taxonomy = sanitize_text_field($_POST['taxonomy']);
            $imported = 0;
            
            foreach ($import_data as $item) {
                $args = array(
                    'slug' => $item['slug'],
                    'description' => $item['description'],
                );
                
                if (!empty($item['parent'])) {
                    $args['parent'] = $item['parent'];
                }
                
                $result = wp_insert_term($item['name'], $taxonomy, $args);
                
                if (!is_wp_error($result)) {
                    $term_id = $result['term_id'];
                    
                    // Import extended data
                    if (isset($item['extended_data'])) {
                        foreach ($item['extended_data'] as $key => $value) {
                            update_term_meta($term_id, $key, $value);
                        }
                    }
                    
                    $imported++;
                }
            }
            
            echo '<div class="notice notice-success"><p>' . sprintf(__('Successfully imported %d categories!', 'easy-directory-system'), $imported) . '</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>' . __('Invalid import file format.', 'easy-directory-system') . '</p></div>';
        }
    }
}

// Get available taxonomies
$available_taxonomies = get_taxonomies(array('public' => true), 'objects');
$default_taxonomy = class_exists('WooCommerce') ? 'product_cat' : 'category';
?>

<div class="wrap">
    <h1><?php _e('Import / Export Categories', 'easy-directory-system'); ?></h1>
    
    <div class="eds-wrap">
        <!-- Export Section -->
        <div class="eds-form-section" style="border-left: 4px solid #2271b1;">
            <h2><span class="dashicons dashicons-database-export" style="margin-right: 10px;"></span><?php _e('Export Categories', 'easy-directory-system'); ?></h2>
            <p style="font-size: 14px; line-height: 1.7;">
                <?php _e('Export your categories to a JSON file. This will include all category data, extended metadata, SEO information, and settings.', 'easy-directory-system'); ?>
            </p>
            
            <form method="post" style="margin-top: 20px;">
                <?php wp_nonce_field('eds_export', 'eds_export_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label><?php _e('Select Taxonomy', 'easy-directory-system'); ?></label>
                        </th>
                        <td>
                            <select name="taxonomy" required style="min-width: 300px;">
                                <?php foreach ($available_taxonomies as $tax): ?>
                                    <option value="<?php echo esc_attr($tax->name); ?>" <?php selected($default_taxonomy, $tax->name); ?>>
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
                            <p class="description">
                                <?php _e('Choose which taxonomy to export.', 'easy-directory-system'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label><?php _e('Export Format', 'easy-directory-system'); ?></label>
                        </th>
                        <td>
                            <fieldset>
                                <label style="display: block; margin-bottom: 8px;">
                                    <input type="radio" name="export_format" value="json" checked>
                                    <strong>JSON</strong> - <?php _e('Complete data with all metadata (recommended for backup)', 'easy-directory-system'); ?>
                                </label>
                                <label style="display: block; margin-bottom: 8px;">
                                    <input type="radio" name="export_format" value="csv">
                                    <strong>CSV</strong> - <?php _e('Spreadsheet format, easy to edit in Excel (basic data only)', 'easy-directory-system'); ?>
                                </label>
                            </fieldset>
                            <p class="description">
                                <?php _e('JSON preserves all data and can be re-imported. CSV is for viewing/editing in spreadsheet software.', 'easy-directory-system'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" name="eds_export" class="button button-primary button-large">
                        <span class="dashicons dashicons-download" style="margin-top: 4px;"></span>
                        <?php _e('Export Categories', 'easy-directory-system'); ?>
                    </button>
                </p>
            </form>
        </div>
        
        <!-- Import Section -->
        <div class="eds-form-section" style="border-left: 4px solid #10b981; margin-top: 20px;">
            <h2><span class="dashicons dashicons-database-import" style="margin-right: 10px;"></span><?php _e('Import Categories', 'easy-directory-system'); ?></h2>
            <p style="font-size: 14px; line-height: 1.7;">
                <?php _e('Import categories from a previously exported JSON file. This will create new categories or update existing ones based on slugs.', 'easy-directory-system'); ?>
            </p>
            
            <div style="background: #fff3cd; padding: 15px; margin: 15px 0; border-radius: 4px; border: 1px solid #f0b849;">
                <p style="margin: 0; font-size: 13px;">
                    <strong>⚠️ <?php _e('Warning:', 'easy-directory-system'); ?></strong> 
                    <?php _e('Importing will create new categories. Make sure to backup your database before importing.', 'easy-directory-system'); ?>
                </p>
            </div>
            
            <form method="post" enctype="multipart/form-data" style="margin-top: 20px;">
                <?php wp_nonce_field('eds_import', 'eds_import_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label><?php _e('Select Taxonomy', 'easy-directory-system'); ?></label>
                        </th>
                        <td>
                            <select name="taxonomy" required style="min-width: 300px;">
                                <?php foreach ($available_taxonomies as $tax): ?>
                                    <option value="<?php echo esc_attr($tax->name); ?>" <?php selected($default_taxonomy, $tax->name); ?>>
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
                            <p class="description">
                                <?php _e('Choose which taxonomy to import into.', 'easy-directory-system'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label><?php _e('Import File', 'easy-directory-system'); ?></label>
                        </th>
                        <td>
                            <input typJSON format:', 'easy-directory-system'); ?></strong> <?php _e('Complete backup including names, slugs, descriptions, parent relationships, images, SEO data, and all custom metadata. Can be re-imported.', 'easy-directory-system'); ?></li>
                <li><strong><?php _e('CSV format:', 'easy-directory-system'); ?></strong> <?php _e('Spreadsheet-friendly format with basic data (ID, Name, Slug, Description, Parent, SEO). Great for Excel/Google Sheets editing. Cannot be re-imported.', 'easy-directory-system'); ?></li>
                <li><strong><?php _e('Compatibility:', 'easy-directory-system'); ?></strong> <?php _e('JSON ectory-system'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" name="eds_import" class="button button-primary button-large">
                        <span class="dashicons dashicons-upload" style="margin-top: 4px;"></span>
                        <?php _e('Import Categories', 'easy-directory-system'); ?>
                    </button>
                </p>
            </form>
        </div>
        
        <!-- Information Section -->
        <div class="eds-form-section" style="background: #f0f6fc; border: 2px solid #2271b1; margin-top: 20px;">
            <h2>💡 <?php _e('Important Information', 'easy-directory-system'); ?></h2>
            <ul style="font-size: 14px; line-height: 1.8;">
                <li><strong><?php _e('Export includes:', 'easy-directory-system'); ?></strong> <?php _e('Category names, slugs, descriptions, parent relationships, images, SEO data, and all custom metadata.', 'easy-directory-system'); ?></li>
                <li><strong><?php _e('File format:', 'easy-directory-system'); ?></strong> <?php _e('JSON (JavaScript Object Notation) - human-readable and editable.', 'easy-directory-system'); ?></li>
                <li><strong><?php _e('Compatibility:', 'easy-directory-system'); ?></strong> <?php _e('Export files are compatible between different WordPress sites using Easy Categories.', 'easy-directory-system'); ?></li>
                <li><strong><?php _e('Backup first:', 'easy-directory-system'); ?></strong> <?php _e('Always backup your database before importing data.', 'easy-directory-system'); ?></li>
                <li><strong><?php _e('Duplicate handling:', 'easy-directory-system'); ?></strong> <?php _e('If a category with the same slug exists, import will skip it to avoid duplicates.', 'easy-directory-system'); ?></li>
            </ul>
        </div>
    </div>
</div>
