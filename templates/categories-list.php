<?php
/**
 * Categories List Template
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get categories - default to product_cat if WooCommerce is active
$default_taxonomy = class_exists('WooCommerce') ? 'product_cat' : 'category';
$taxonomy = isset($_GET['taxonomy']) ? sanitize_text_field($_GET['taxonomy']) : $default_taxonomy;
$categories = EDS_Category::get_all_categories($taxonomy);
$stats = EDS_Category::get_statistics($taxonomy);

// Get available taxonomies for switcher
$available_taxonomies = get_taxonomies(array('public' => true), 'objects');
?>

<div class="wrap">
    <?php
    // Breadcrumb navigation
    $parent_id = isset($_GET['parent_id']) ? intval($_GET['parent_id']) : 0;
    ?>
    
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
        <div>
            <h1 style="display: inline-block; margin: 0;">
                <?php _e('Easy Categories', 'easy-directory-system'); ?>
                
                <!-- Taxonomy Switcher -->
                <select id="taxonomy-switcher" style="margin-left: 20px; height: 32px; vertical-align: middle; font-size: 13px;">
                    <?php foreach ($available_taxonomies as $tax): ?>
                        <option value="<?php echo esc_attr($tax->name); ?>" <?php selected($taxonomy, $tax->name); ?>>
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
                
                <script>
                jQuery(document).ready(function($) {
                    $('#taxonomy-switcher').on('change', function() {
                        var taxonomy = $(this).val();
                        var url = '<?php echo admin_url('admin.php?page=easy-categories'); ?>&taxonomy=' + taxonomy;
                        window.location.href = url;
                    });
                });
                </script>
            </h1>
            
            <?php if ($parent_id > 0): ?>
                <?php
                $parent_term = get_term($parent_id, $taxonomy);
                if ($parent_term && !is_wp_error($parent_term)):
                    // Build breadcrumb trail
                    $breadcrumb = array();
                    $current = $parent_term;
                    while ($current && !is_wp_error($current)) {
                        array_unshift($breadcrumb, $current);
                        if ($current->parent > 0) {
                            $current = get_term($current->parent, $taxonomy);
                        } else {
                            break;
                        }
                    }
                ?>
                <div style="font-size: 14px; font-weight: normal; margin-top: 10px; color: #666;">
                    <a href="<?php echo admin_url('admin.php?page=easy-categories&taxonomy=' . $taxonomy); ?>" style="text-decoration: none;">
                        <span class="dashicons dashicons-admin-home"></span> <?php _e('All Categories', 'easy-directory-system'); ?>
                    </a>
                    <?php foreach ($breadcrumb as $crumb): ?>
                        <span class="dashicons dashicons-arrow-right-alt2" style="font-size: 14px;"></span>
                        <?php if ($crumb->term_id == $parent_id): ?>
                            <strong style="color: #2271b1;"><?php echo esc_html($crumb->name); ?></strong>
                        <?php else: ?>
                            <a href="<?php echo admin_url('admin.php?page=easy-categories&parent_id=' . $crumb->term_id . '&taxonomy=' . $taxonomy); ?>" style="text-decoration: none;">
                                <?php echo esc_html($crumb->name); ?>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <?php if ($parent_id > 0): ?>
            <a href="<?php 
                $parent_term = get_term($parent_id, $taxonomy);
                if ($parent_term->parent > 0) {
                    echo admin_url('admin.php?page=easy-categories&parent_id=' . $parent_term->parent . '&taxonomy=' . $taxonomy);
                } else {
                    echo admin_url('admin.php?page=easy-categories&taxonomy=' . $taxonomy);
                }
            ?>" class="button button-secondary" style="height: 40px; line-height: 38px; padding: 0 20px;">
                <span class="dashicons dashicons-arrow-left-alt2" style="margin-top: 8px;"></span>
                <?php _e('Back', 'easy-directory-system'); ?>
            </a>
        <?php endif; ?>
    </div>
    
    <div class="eds-wrap">
        <!-- Statistics Dashboard -->
        <div class="eds-stats-grid">
            <div class="eds-stat-card">
                <h3><?php _e('Disabled Categories', 'easy-directory-system'); ?></h3>
                <div class="stat-value stat-disabled"><?php echo $stats['disabled']; ?></div>
            </div>
            
            <div class="eds-stat-card">
                <h3><?php _e('Empty Categories', 'easy-directory-system'); ?></h3>
                <div class="stat-value stat-empty"><?php echo $stats['empty']; ?></div>
            </div>
            
            <div class="eds-stat-card">
                <h3><?php _e('Top Category', 'easy-directory-system'); ?></h3>
                <div class="stat-value stat-top">
                    <?php echo $stats['top_category'] ? esc_html($stats['top_category']->name) : 'N/A'; ?>
                </div>
                <div class="stat-label">
                    <?php echo $stats['top_category'] ? $stats['top_category']->product_count . ' products' : ''; ?>
                </div>
            </div>
            
            <div class="eds-stat-card">
                <h3><?php _e('Average Products', 'easy-directory-system'); ?></h3>
                <div class="stat-value stat-average"><?php echo $stats['average_products']; ?></div>
                <button class="button eds-refresh-stats" style="margin-top: 10px;">
                    <span class="dashicons dashicons-update"></span>
                </button>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="eds-sync-buttons" style="margin-bottom: 20px;">
            <a href="<?php echo admin_url('admin.php?page=easy-categories-add&taxonomy=' . $taxonomy); ?>" class="button button-primary button-large" style="height: 36px; line-height: 36px; padding: 0 20px;">
                <span class="dashicons dashicons-plus" style="margin-top: 4px;"></span>
                <?php _e('Add New Category', 'easy-directory-system'); ?>
            </a>
            
            <button type="button" class="button button-secondary button-large eds-enable-all" style="height: 36px; margin-left: 10px;">
                <span class="dashicons dashicons-yes" style="margin-top: 4px;"></span>
                <?php _e('Enable All Categories', 'easy-directory-system'); ?>
            </button>
        </div>
        
        <!-- Categories Table -->
        <div class="eds-table-container">
            <table class="eds-table">
                <thead>
                    <tr>
                        <th style="width: 30px;"></th>
                        <th><input type="checkbox" id="select-all"></th>
                        <th><?php _e('ID', 'easy-directory-system'); ?></th>
                        <th><?php _e('Name', 'easy-directory-system'); ?></th>
                        <th><?php _e('Description', 'easy-directory-system'); ?></th>
                        <th><?php _e('Products', 'easy-directory-system'); ?></th>
                        <th><?php _e('Position', 'easy-directory-system'); ?></th>
                        <th><?php _e('Displayed', 'easy-directory-system'); ?></th>
                        <th><?php _e('Actions', 'easy-directory-system'); ?></th>
                    </tr>
                </thead>
                <tbody class="sortable">
                    <?php 
                    // Filter to show only parent categories (no parent) or specific parent if viewing subcategories
                    $parent_filter = isset($_GET['parent_id']) ? intval($_GET['parent_id']) : 0;
                    $filtered_categories = array_filter($categories, function($cat) use ($parent_filter) {
                        return $cat->parent == $parent_filter;
                    });
                    
                    // Sort by position
                    usort($filtered_categories, function($a, $b) {
                        $pos_a = $a->extended_data ? $a->extended_data->position : 0;
                        $pos_b = $b->extended_data ? $b->extended_data->position : 0;
                        return $pos_a - $pos_b;
                    });
                    
                    foreach ($filtered_categories as $category): 
                        // Count children
                        $children_count = count(get_terms(array(
                            'taxonomy' => $taxonomy,
                            'parent' => $category->term_id,
                            'hide_empty' => false
                        )));
                    ?>
                    <tr data-term-id="<?php echo $category->term_id; ?>">
                        <td class="drag-handle" style="cursor: move; text-align: center;">
                            <span class="dashicons dashicons-move"></span>
                        </td>
                        <td><input type="checkbox" name="category[]" value="<?php echo $category->term_id; ?>"></td>
                        <td><?php echo $category->term_id; ?></td>
                        <td>
                            <?php if ($category->extended_data): ?>
                                <?php if (!empty($category->extended_data->category_color)): ?>
                                    <span class="eds-category-color-badge" style="background-color: <?php echo esc_attr($category->extended_data->category_color); ?>;"></span>
                                <?php endif; ?>
                                <?php if (!empty($category->extended_data->category_icon)): ?>
                                    <span class="dashicons <?php echo esc_attr($category->extended_data->category_icon); ?>" style="color: <?php echo esc_attr($category->extended_data->category_color ?: '#3498db'); ?>;"></span>
                                <?php endif; ?>
                            <?php endif; ?>
                            <strong>
                                <?php if ($children_count > 0): ?>
                                    <a href="<?php echo admin_url('admin.php?page=easy-categories&parent_id=' . $category->term_id . '&taxonomy=' . $taxonomy); ?>" 
                                       style="text-decoration: none; color: #0073aa;">
                                        <?php echo esc_html($category->name); ?>
                                        <span class="dashicons dashicons-arrow-right-alt2" style="font-size: 16px; vertical-align: middle;"></span>
                                    </a>
                                <?php else: ?>
                                    <?php echo esc_html($category->name); ?>
                                <?php endif; ?>
                            </strong>
                            <?php if ($children_count > 0): ?>
                                <br><small style="color: #666;">
                                    <?php printf(_n('%d subcategory', '%d subcategories', $children_count, 'easy-directory-system'), $children_count); ?>
                                </small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html(wp_trim_words($category->description, 10)); ?></td>
                        <td><?php echo $category->product_count; ?></td>
                        <td><?php echo $category->extended_data ? $category->extended_data->position : 0; ?></td>
                        <td>
                            <label class="eds-toggle">
                                <input type="checkbox" 
                                       class="category-toggle"
                                       data-term-id="<?php echo $category->term_id; ?>"
                                       <?php checked($category->extended_data ? $category->extended_data->is_enabled : 0, 1); ?>>
                                <span class="eds-toggle-slider"></span>
                            </label>
                        </td>
                        <td style="white-space: nowrap;">
                            <a href="<?php echo admin_url('admin.php?page=easy-categories-add&action=edit&term_id=' . $category->term_id); ?>" 
                               class="eds-action-btn"
                               title="<?php _e('Edit', 'easy-directory-system'); ?>">
                                <span class="dashicons dashicons-edit"></span>
                            </a>
                            <a href="#" 
                               class="eds-action-btn eds-duplicate-category" 
                               data-term-id="<?php echo $category->term_id; ?>"
                               data-taxonomy="<?php echo $taxonomy; ?>"
                               style="background: #9b59b6;"
                               title="<?php _e('Duplicate', 'easy-directory-system'); ?>">
                                <span class="dashicons dashicons-admin-page"></span>
                            </a>
                            <?php if ($children_count > 0): ?>
                                <a href="<?php echo admin_url('admin.php?page=easy-categories&parent_id=' . $category->term_id . '&taxonomy=' . $taxonomy); ?>" 
                                   class="eds-action-btn"
                                   style="background: #6c757d;"
                                   title="<?php _e('View Subcategories', 'easy-directory-system'); ?>">
                                    <span class="dashicons dashicons-category"></span>
                                </a>
                            <?php endif; ?>
                            <a href="#" 
                               class="eds-action-btn eds-action-btn-view"
                               onclick="window.open('<?php echo get_term_link($category); ?>', '_blank'); return false;"
                               title="<?php _e('View', 'easy-directory-system'); ?>">
                                <span class="dashicons dashicons-visibility"></span>
                            </a>
                            <a href="#" 
                               class="eds-action-btn delete eds-delete-category" 
                               data-term-id="<?php echo $category->term_id; ?>"
                               data-taxonomy="<?php echo $taxonomy; ?>"
                               title="<?php _e('Delete', 'easy-directory-system'); ?>">
                                <span class="dashicons dashicons-trash"></span>
                            </a>
                            <a href="<?php echo admin_url('admin.php?page=easy-categories-add&parent_id=' . $category->term_id . '&taxonomy=' . $taxonomy); ?>" 
                               class="eds-action-btn"
                               style="background: #28a745;"
                               title="<?php _e('Add Subcategory', 'easy-directory-system'); ?>">
                                <span class="dashicons dashicons-plus-alt"></span>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($filtered_categories)): ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 40px;">
                            <?php _e('No categories found.', 'easy-directory-system'); ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php EDS_Admin::render_footer(); ?>
</div>

<input type="hidden" name="taxonomy" value="<?php echo esc_attr($taxonomy); ?>">
