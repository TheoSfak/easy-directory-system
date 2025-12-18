<?php
/**
 * Category Add/Edit Form Template
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get term ID if editing
$term_id = isset($_GET['term_id']) ? intval($_GET['term_id']) : 0;
$taxonomy = isset($_GET['taxonomy']) ? sanitize_text_field($_GET['taxonomy']) : 'category';
$is_edit = $term_id > 0;
$preset_parent_id = isset($_GET['parent_id']) ? intval($_GET['parent_id']) : 0;

// Get category data if editing
$category = null;
$extended_data = null;
if ($is_edit) {
    $category = get_term($term_id, $taxonomy);
    if (is_wp_error($category)) {
        wp_die(__('Category not found', 'easy-directory-system'));
    }
    $extended_data = EDS_Database::get_category_data($term_id);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eds_category_nonce'])) {
    if (!wp_verify_nonce($_POST['eds_category_nonce'], 'eds_save_category')) {
        wp_die(__('Security check failed', 'easy-directory-system'));
    }
    
    $name = sanitize_text_field($_POST['name']);
    $slug = sanitize_title($_POST['slug']);
    $description = wp_kses_post($_POST['description']);
    $parent = intval($_POST['parent']);
    
    $args = array(
        'description' => $description,
        'slug' => $slug,
        'parent' => $parent
    );
    
    if ($is_edit) {
        $result = wp_update_term($term_id, $taxonomy, $args);
    } else {
        $result = wp_insert_term($name, $taxonomy, $args);
    }
    
    if (is_wp_error($result)) {
        echo '<div class="notice notice-error"><p>' . $result->get_error_message() . '</p></div>';
    } else {
        $saved_term_id = is_array($result) ? $result['term_id'] : $term_id;
        
        // Save extended data
        $extended_data_args = array(
            'taxonomy' => $taxonomy,
            'cover_image_id' => isset($_POST['cover_image_id']) ? intval($_POST['cover_image_id']) : null,
            'thumbnail_image_id' => isset($_POST['thumbnail_image_id']) ? intval($_POST['thumbnail_image_id']) : null,
            'position' => isset($_POST['position']) ? intval($_POST['position']) : 0,
            'is_enabled' => isset($_POST['is_enabled']) ? 1 : 0,
            'redirection_type' => sanitize_text_field($_POST['redirection_type']),
            'redirection_target' => isset($_POST['redirection_target']) ? intval($_POST['redirection_target']) : null,
            'group_access' => isset($_POST['group_access']) ? json_encode($_POST['group_access']) : json_encode(array()),
            'meta_data' => json_encode(array(
                'meta_title' => sanitize_text_field($_POST['meta_title']),
                'meta_description' => sanitize_textarea_field($_POST['meta_description']),
                'additional_description' => wp_kses_post($_POST['additional_description'])
            ))
        );
        
        EDS_Database::save_category_data($saved_term_id, $extended_data_args);
        
        echo '<div class="notice notice-success"><p>' . __('Category saved successfully!', 'easy-directory-system') . '</p></div>';
        
        // Reload category data
        if (!$is_edit) {
            wp_redirect(admin_url('admin.php?page=easy-categories-add&action=edit&term_id=' . $saved_term_id));
            exit;
        } else {
            $category = get_term($saved_term_id, $taxonomy);
            $extended_data = EDS_Database::get_category_data($saved_term_id);
        }
    }
}

// Get all categories for parent dropdown
$all_categories = get_terms(array(
    'taxonomy' => $taxonomy,
    'hide_empty' => false,
    'exclude' => $term_id ? array($term_id) : array()
));

// Parse extended data
$meta_data = array();
if ($extended_data && $extended_data->meta_data) {
    $meta_data = json_decode($extended_data->meta_data, true);
}
$group_access = array();
if ($extended_data && $extended_data->group_access) {
    $group_access = json_decode($extended_data->group_access, true);
}
?>

<div class="wrap">
    <h1>
        <?php echo $is_edit ? __('Edit Category', 'easy-directory-system') : __('Add New Category', 'easy-directory-system'); ?>
        <?php if ($is_edit): ?>
            <a href="<?php echo admin_url('admin.php?page=easy-categories-add'); ?>" class="page-title-action">
                <?php _e('Add New', 'easy-directory-system'); ?>
            </a>
        <?php endif; ?>
    </h1>
    
    <form method="post" class="eds-category-form">
        <?php wp_nonce_field('eds_save_category', 'eds_category_nonce'); ?>
        
        <div class="eds-wrap">
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
                <!-- Left Column - Main Fields -->
                <div>
                    <!-- Basic Information -->
                    <div class="eds-form-section">
                        <h2><?php _e('Basic Information', 'easy-directory-system'); ?></h2>
                        
                        <div class="eds-form-row">
                            <label for="name">
                                <?php _e('Name', 'easy-directory-system'); ?> <span class="required">*</span>
                            </label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   value="<?php echo $is_edit ? esc_attr($category->name) : ''; ?>" 
                                   required>
                            <p class="description"><?php _e('Invalid characters: <>{}', 'easy-directory-system'); ?></p>
                        </div>
                        
                        <div class="eds-form-row">
                            <label for="slug">
                                <?php _e('Friendly URL', 'easy-directory-system'); ?> <span class="required">*</span>
                            </label>
                            <input type="text" 
                                   id="slug" 
                                   name="slug" 
                                   value="<?php echo $is_edit ? esc_attr($category->slug) : ''; ?>" 
                                   required>
                            <p class="description"><?php _e('Allowed characters: letters, numbers, underscores, hyphens', 'easy-directory-system'); ?></p>
                        </div>
                    </div>
                    
                    <!-- Descriptions -->
                    <div class="eds-form-section">
                        <h2><?php _e('Descriptions', 'easy-directory-system'); ?></h2>
                        
                        <div class="eds-form-row">
                            <label><?php _e('Description', 'easy-directory-system'); ?></label>
                            <?php 
                            wp_editor(
                                $is_edit ? $category->description : '', 
                                'description', 
                                array('textarea_rows' => 5, 'media_buttons' => false)
                            ); 
                            ?>
                        </div>
                        
                        <div class="eds-form-row">
                            <label><?php _e('Additional Description', 'easy-directory-system'); ?></label>
                            <?php 
                            $additional_desc = isset($meta_data['additional_description']) ? $meta_data['additional_description'] : '';
                            wp_editor(
                                $additional_desc, 
                                'additional_description', 
                                array('textarea_rows' => 8, 'media_buttons' => true)
                            ); 
                            ?>
                            <p class="description">
                                <?php _e('Text that is usually displayed after the product list on category page. Good for SEO content.', 'easy-directory-system'); ?>
                            </p>
                        </div>
                    </div>
                    
                    <!-- SEO Section with Preview -->
                    <div class="eds-form-section">
                        <h2><?php _e('SEO', 'easy-directory-system'); ?></h2>
                        
                        <div class="eds-form-row">
                            <label><?php _e('Meta Title', 'easy-directory-system'); ?></label>
                            <?php $meta_title = isset($meta_data['meta_title']) ? $meta_data['meta_title'] : ''; ?>
                            <input type="text" 
                                   name="meta_title" 
                                   value="<?php echo esc_attr($meta_title); ?>"
                                   maxlength="70"
                                   class="seo-meta-title">
                            <p class="description char-count">0 / 70 characters used (recommended)</p>
                        </div>
                        
                        <div class="eds-form-row">
                            <label><?php _e('Meta Description', 'easy-directory-system'); ?></label>
                            <?php $meta_desc = isset($meta_data['meta_description']) ? $meta_data['meta_description'] : ''; ?>
                            <textarea name="meta_description" 
                                      rows="3"
                                      maxlength="160"
                                      class="seo-meta-description"><?php echo esc_textarea($meta_desc); ?></textarea>
                            <p class="description char-count">0 / 160 characters used (recommended)</p>
                        </div>
                        
                        <div class="eds-seo-preview">
                            <h3><?php _e('SEO Preview', 'easy-directory-system'); ?></h3>
                            <div class="eds-seo-preview-url">https://example.com/category/<?php echo $is_edit ? $category->slug : 'category-slug'; ?></div>
                            <div class="eds-seo-preview-title"><?php echo $meta_title ?: ($is_edit ? $category->name : 'Category Title'); ?></div>
                            <div class="eds-seo-preview-desc"><?php echo $meta_desc ?: 'Category description will appear here...'; ?></div>
                        </div
                                <input type="checkbox" 
                                       name="is_enabled" 
                                       value="1" 
                                       <?php checked($extended_data ? $extended_data->is_enabled : 1, 1); ?>>
                                <?php _e('Enabled', 'easy-directory-system'); ?>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Parent Category -->
                    <div class="eds-form-section">
                        <h2><?php _e('Parent Category', 'easy-directory-system'); ?></h2>
                        <div class="eds-form-row">
                            <?php 
                            $selected_parent = $is_edit ? $category->parent : $preset_parent_id;
                            ?>
                            <select name="parent" id="parent_category" style="width: 100%;">
                                <option value="0"><?php _e('Home', 'easy-directory-system'); ?></option>
                                <?php foreach ($all_categories as $cat): ?>
                                    <option value="<?php echo $cat->term_id; ?>" 
                                            <?php selected($selected_parent, $cat->term_id); ?>>
                                        <?php echo str_repeat('—', count(get_ancestors($cat->term_id, $taxonomy))) . ' ' . esc_html($cat->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($preset_parent_id > 0): ?>
                                <p class="description" style="color: #28a745;">
                                    <span class="dashicons dashicons-info" style="font-size: 16px; vertical-align: middle;"></span>
                                    <?php 
                                    $preset_parent = get_term($preset_parent_id, $taxonomy);
                                    printf(
                                        __('Creating a subcategory under: <strong>%s</strong>', 'easy-directory-system'),
                                        $preset_parent ? esc_html($preset_parent->name) : ''
                                    );
                                    ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Position -->
                    <div class="eds-form-section">
                        <h2><?php _e('Position', 'easy-directory-system'); ?></h2>
                        <div class="eds-form-row">
                            <input type="number" 
                                   name="position" 
                                   value="<?php echo $extended_data ? $extended_data->position : 0; ?>" 
                                   min="0">
                        </div>
                    </div>
                    
                    <!-- Images -->
                    <div class="eds-form-section">
                        <h2><?php _e('Category Images', 'easy-directory-system'); ?></h2>
                        
                        <div class="eds-form-row">
                            <label><?php _e('Category Cover Image', 'easy-directory-system'); ?></label>
                            <button type="button" 
                                    class="button eds-upload-image" 
                                    data-input-id="cover_image_id" 
                                    data-preview-id="cover_image_preview">
                                <?php _e('Choose file(s)', 'easy-directory-system'); ?>
                            </button>
                            <input type="hidden" 
                                   id="cover_image_id" 
                                   name="cover_image_id" 
                                   value="<?php echo $extended_data ? $extended_data->cover_image_id : ''; ?>">
                            <div id="cover_image_preview" class="eds-image-preview">
                                <?php if ($extended_data && $extended_data->cover_image_id): ?>
                                    <?php echo wp_get_attachment_image($extended_data->cover_image_id, 'medium'); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="eds-form-row">
                            <label><?php _e('Category Thumbnail', 'easy-directory-system'); ?></label>
                            <button type="button" 
                                    class="button eds-upload-image" 
                                    data-input-id="thumbnail_image_id" 
                                    data-preview-id="thumbnail_image_preview">
                                <?php _e('Choose file(s)', 'easy-directory-system'); ?>
                            </button>
                            <input type="hidden" 
                                   id="thumbnail_image_id" 
                                   name="thumbnail_image_id" 
                                   value="<?php echo $extended_data ? $extended_data->thumbnail_image_id : ''; ?>">
                            <div id="thumbnail_image_preview" class="eds-image-preview">
                                <?php if ($extended_data && $extended_data->thumbnail_image_id): ?>
                                    <?php echo wp_get_attachment_image($extended_data->thumbnail_image_id, 'thumbnail'); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Redirection -->
                    <div class="eds-form-section">
                        <h2><?php _e('Redirection when not displayed', 'easy-directory-system'); ?></h2>
                        <div class="eds-form-row">
                            <select name="redirection_type" style="width: 100%;">
                                <option value="301" <?php selected($extended_data ? $extended_data->redirection_type : '301', '301'); ?>>
                                    301 - <?php _e('Permanent redirection', 'easy-directory-system'); ?>
                                </option>
                                <option value="302" <?php selected($extended_data ? $extended_data->redirection_type : '301', '302'); ?>>
                                    302 - <?php _e('Temporary redirection', 'easy-directory-system'); ?>
                                </option>
                                <option value="404" <?php selected($extended_data ? $extended_data->redirection_type : '301', '404'); ?>>
                                    404 - <?php _e('Not Found', 'easy-directory-system'); ?>
                                </option>
                                <option value="410" <?php selected($extended_data ? $extended_data->redirection_type : '301', '410'); ?>>
                                    410 - <?php _e('Gone', 'easy-directory-system'); ?>
                                </option>
                            </select>
                        </div>
                        
                        <div class="eds-form-row">
                            <label><?php _e('Target Category', 'easy-directory-system'); ?></label>
                            <select name="redirection_target" style="width: 100%;">
                                <option value=""><?php _e('Select category', 'easy-directory-system'); ?></option>
                                <?php foreach ($all_categories as $cat): ?>
                                    <option value="<?php echo $cat->term_id; ?>" 
                                            <?php selected($extended_data ? $extended_data->redirection_target : '', $cat->term_id); ?>>
                                        <?php echo esc_html($cat->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Group Access -->
                    <div class="eds-form-section">
                        <h2><?php _e('Group Access', 'easy-directory-system'); ?> <span class="required">*</span></h2>
                        <div class="eds-form-row">
                            <label>
                                <input type="checkbox" 
                                       name="group_access[]" 
                                       value="visitor" 
                                       <?php checked(in_array('visitor', $group_access) || empty($group_access), true); ?>>
                                <?php _e('Visitor', 'easy-directory-system'); ?>
                            </label>
                        </div>
                        <div class="eds-form-row">
                            <label>
                                <input type="checkbox" 
                                       name="group_access[]" 
                                       value="guest" 
                                       <?php checked(in_array('guest', $group_access) || empty($group_access), true); ?>>
                                <?php _e('Guest', 'easy-directory-system'); ?>
                            </label>
                        </div>
                        <div class="eds-form-row">
                            <label>
                                <input type="checkbox" 
                                       name="group_access[]" 
                                       value="customer" 
                                       <?php checked(in_array('customer', $group_access) || empty($group_access), true); ?>>
                                <?php _e('Customer', 'easy-directory-system'); ?>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="eds-form-section">
                        <button type="submit" class="button button-primary button-large" style="width: 100%;">
                            <?php echo $is_edit ? __('Update Category', 'easy-directory-system') : __('Create Category', 'easy-directory-system'); ?>
                        </button>
                    </div>
                </div>
            </div>
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
    font-size: 16px;
    font-weight: 600;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
    margin-bottom: 15px;
}

.eds-form-row {
    margin-bottom: 15px;
}

.eds-form-row:last-child {
    margin-bottom: 0;
}

.required {
    color: #dc3545;
}

.eds-lang-tab-seo {
    display: inline-block;
    padding: 10px 20px;
    cursor: pointer;
    border: 1px solid transparent;
    border-bottom: none;
    margin-bottom: -1px;
    background: #f8f9fa;
}

.eds-lang-tab-seo.active {
    background: #fff;
    border-color: #ddd;
    border-bottom-color: #fff;
}

.eds-lang-content-seo {
    display: none;
}

.eds-lang-content-seo.active {
    display: block;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Auto-generate slug from name
    $('#name').on('blur', function() {
        if ($('#slug').val() === '') {
            const slug = $(this).val().toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
            $('#slug').val(slug);
        }
    });
    
    // Update character counts on page load
    $('.seo-meta-title, .seo-meta-description').each(function() {
        const count = $(this).val().length;
        const max = $(this).attr('maxlength');
        $(this).next('.char-count').text(count + ' / ' + max + ' characters used (recommended)');
    });
    
    // Real-time SEO preview
    $('.seo-meta-title').on('input', function() {
        const title = $(this).val() || $('#name').val() || 'Category Title';
        $('.eds-seo-preview-title').text(title);
    });
    
    $('.seo-meta-description').on('input', function() {
        const desc = $(this).val() || 'Category description will appear here...';
        $('.eds-seo-preview-desc').text(desc.substring(0, 160) + (desc.length > 160 ? '...' : ''));
    });
});
</script>