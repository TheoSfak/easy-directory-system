<?php
/**
 * Demo Data Template
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Handle demo data installation
if (isset($_POST['eds_install_demo']) && check_admin_referer('eds_install_demo', 'eds_demo_nonce')) {
    $taxonomy = sanitize_text_field($_POST['taxonomy']);
    $demo_type = sanitize_text_field($_POST['demo_type']);
    
    $created = 0;
    
    // Demo data sets
    $demo_sets = array(
        'electronics' => array(
            array('name' => 'Electronics', 'slug' => 'electronics', 'parent' => 0, 'description' => 'Latest electronics including computers, phones, and tablets.'),
            array('name' => 'Computers', 'slug' => 'computers', 'parent' => 'electronics', 'description' => 'Desktop computers, laptops, and workstations.'),
            array('name' => 'Mobile Phones', 'slug' => 'mobile-phones', 'parent' => 'electronics', 'description' => 'Smartphones and mobile devices from top brands.'),
            array('name' => 'Tablets', 'slug' => 'tablets', 'parent' => 'electronics', 'description' => 'iPad, Android tablets, and e-readers.'),
            array('name' => 'Accessories', 'slug' => 'accessories', 'parent' => 'electronics', 'description' => 'Chargers, cases, cables and more.'),
            array('name' => 'Home Appliances', 'slug' => 'home-appliances', 'parent' => 0, 'description' => 'Kitchen and home appliances for modern living.'),
            array('name' => 'Fashion', 'slug' => 'fashion', 'parent' => 0, 'description' => 'Clothing, shoes, and accessories for men and women.'),
        ),
        'blog' => array(
            array('name' => 'Technology', 'slug' => 'technology', 'parent' => 0, 'description' => 'Tech news, reviews, and tutorials.'),
            array('name' => 'Programming', 'slug' => 'programming', 'parent' => 'technology', 'description' => 'Web development, coding tips, and best practices.'),
            array('name' => 'WordPress', 'slug' => 'wordpress', 'parent' => 'programming', 'description' => 'WordPress tutorials, plugins, and themes.'),
            array('name' => 'Lifestyle', 'slug' => 'lifestyle', 'parent' => 0, 'description' => 'Life tips, health, and personal development.'),
            array('name' => 'Travel', 'slug' => 'travel', 'parent' => 'lifestyle', 'description' => 'Travel guides, tips, and destination reviews.'),
            array('name' => 'Business', 'slug' => 'business', 'parent' => 0, 'description' => 'Business strategies, marketing, and entrepreneurship.'),
        ),
        'minimal' => array(
            array('name' => 'Category One', 'slug' => 'category-one', 'parent' => 0, 'description' => 'First sample category.'),
            array('name' => 'Category Two', 'slug' => 'category-two', 'parent' => 0, 'description' => 'Second sample category.'),
            array('name' => 'Subcategory A', 'slug' => 'subcategory-a', 'parent' => 'category-one', 'description' => 'First subcategory example.'),
        ),
    );
    
    $demo_data = isset($demo_sets[$demo_type]) ? $demo_sets[$demo_type] : array();
    $parent_map = array(); // Map slugs to term IDs
    
    foreach ($demo_data as $item) {
        $args = array(
            'slug' => $item['slug'],
            'description' => $item['description'],
        );
        
        // Handle parent
        if (!empty($item['parent']) && $item['parent'] !== 0) {
            if (isset($parent_map[$item['parent']])) {
                $args['parent'] = $parent_map[$item['parent']];
            }
        }
        
        $result = wp_insert_term($item['name'], $taxonomy, $args);
        
        if (!is_wp_error($result)) {
            $term_id = $result['term_id'];
            $parent_map[$item['slug']] = $term_id;
            
            // Add some demo metadata
            update_term_meta($term_id, 'eds_is_enabled', 1);
            update_term_meta($term_id, 'eds_position', $created);
            
            $created++;
        }
    }
    
    echo '<div class="notice notice-success"><p>' . sprintf(__('Successfully created %d demo categories!', 'easy-directory-system'), $created) . '</p></div>';
}

// Get available taxonomies
$available_taxonomies = get_taxonomies(array('public' => true), 'objects');
$default_taxonomy = class_exists('WooCommerce') ? 'product_cat' : 'category';
?>

<div class="wrap">
    <h1><?php _e('Demo Data', 'easy-directory-system'); ?></h1>
    
    <div class="eds-wrap">
        <!-- Introduction -->
        <div class="eds-form-section">
            <h2><?php _e('Install Demo Categories', 'easy-directory-system'); ?></h2>
            <p style="font-size: 15px; line-height: 1.7;">
                <?php _e('Quickly populate your site with sample categories to test features and see how Easy Categories works. Choose from different demo data sets below.', 'easy-directory-system'); ?>
            </p>
        </div>
        
        <!-- Demo Sets -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
            
            <!-- Electronics Demo -->
            <div class="eds-form-section" style="border-left: 4px solid #2271b1;">
                <h2>🛒 <?php _e('E-Commerce / Electronics', 'easy-directory-system'); ?></h2>
                <p style="font-size: 13px; color: #666;">
                    <?php _e('Perfect for online stores. Creates 7 categories including Electronics, Computers, Mobile Phones, and more.', 'easy-directory-system'); ?>
                </p>
                <ul style="font-size: 13px; margin: 15px 0;">
                    <li>Electronics</li>
                    <li>&nbsp;&nbsp;↳ Computers</li>
                    <li>&nbsp;&nbsp;↳ Mobile Phones</li>
                    <li>&nbsp;&nbsp;↳ Tablets</li>
                    <li>&nbsp;&nbsp;↳ Accessories</li>
                    <li>Home Appliances</li>
                    <li>Fashion</li>
                </ul>
                <form method="post">
                    <?php wp_nonce_field('eds_install_demo', 'eds_demo_nonce'); ?>
                    <input type="hidden" name="demo_type" value="electronics">
                    <select name="taxonomy" style="width: 100%; margin-bottom: 10px;">
                        <?php foreach ($available_taxonomies as $tax): ?>
                            <option value="<?php echo esc_attr($tax->name); ?>" <?php selected($default_taxonomy, $tax->name); ?>>
                                <?php echo esc_html($tax->labels->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" name="eds_install_demo" class="button button-primary" style="width: 100%;">
                        <span class="dashicons dashicons-download"></span>
                        <?php _e('Install E-Commerce Demo', 'easy-directory-system'); ?>
                    </button>
                </form>
            </div>
            
            <!-- Blog Demo -->
            <div class="eds-form-section" style="border-left: 4px solid #10b981;">
                <h2>📝 <?php _e('Blog / Magazine', 'easy-directory-system'); ?></h2>
                <p style="font-size: 13px; color: #666;">
                    <?php _e('Ideal for blogs and content sites. Creates 6 categories including Technology, Lifestyle, and Business.', 'easy-directory-system'); ?>
                </p>
                <ul style="font-size: 13px; margin: 15px 0;">
                    <li>Technology</li>
                    <li>&nbsp;&nbsp;↳ Programming</li>
                    <li>&nbsp;&nbsp;&nbsp;&nbsp;↳ WordPress</li>
                    <li>Lifestyle</li>
                    <li>&nbsp;&nbsp;↳ Travel</li>
                    <li>Business</li>
                </ul>
                <form method="post">
                    <?php wp_nonce_field('eds_install_demo', 'eds_demo_nonce'); ?>
                    <input type="hidden" name="demo_type" value="blog">
                    <select name="taxonomy" style="width: 100%; margin-bottom: 10px;">
                        <?php foreach ($available_taxonomies as $tax): ?>
                            <option value="<?php echo esc_attr($tax->name); ?>" <?php selected('category', $tax->name); ?>>
                                <?php echo esc_html($tax->labels->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" name="eds_install_demo" class="button button-primary" style="width: 100%;">
                        <span class="dashicons dashicons-download"></span>
                        <?php _e('Install Blog Demo', 'easy-directory-system'); ?>
                    </button>
                </form>
            </div>
            
            <!-- Minimal Demo -->
            <div class="eds-form-section" style="border-left: 4px solid #f59e0b;">
                <h2>⚡ <?php _e('Minimal / Testing', 'easy-directory-system'); ?></h2>
                <p style="font-size: 13px; color: #666;">
                    <?php _e('Quick test setup. Creates just 3 categories for basic testing and learning.', 'easy-directory-system'); ?>
                </p>
                <ul style="font-size: 13px; margin: 15px 0;">
                    <li>Category One</li>
                    <li>&nbsp;&nbsp;↳ Subcategory A</li>
                    <li>Category Two</li>
                    <li style="opacity: 0;">Placeholder</li>
                    <li style="opacity: 0;">Placeholder</li>
                </ul>
                <form method="post">
                    <?php wp_nonce_field('eds_install_demo', 'eds_demo_nonce'); ?>
                    <input type="hidden" name="demo_type" value="minimal">
                    <select name="taxonomy" style="width: 100%; margin-bottom: 10px;">
                        <?php foreach ($available_taxonomies as $tax): ?>
                            <option value="<?php echo esc_attr($tax->name); ?>" <?php selected($default_taxonomy, $tax->name); ?>>
                                <?php echo esc_html($tax->labels->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" name="eds_install_demo" class="button button-primary" style="width: 100%;">
                        <span class="dashicons dashicons-download"></span>
                        <?php _e('Install Minimal Demo', 'easy-directory-system'); ?>
                    </button>
                </form>
            </div>
            
        </div>
        
        <!-- Important Notes -->
        <div class="eds-form-section" style="background: #fff3cd; border: 1px solid #f0b849; margin-top: 20px;">
            <h2>⚠️ <?php _e('Important Notes', 'easy-directory-system'); ?></h2>
            <ul style="font-size: 14px; line-height: 1.8;">
                <li><strong><?php _e('Safe to install:', 'easy-directory-system'); ?></strong> <?php _e('Demo data will not delete or modify your existing categories.', 'easy-directory-system'); ?></li>
                <li><strong><?php _e('Easy removal:', 'easy-directory-system'); ?></strong> <?php _e('You can delete demo categories individually or use bulk delete from the All Categories page.', 'easy-directory-system'); ?></li>
                <li><strong><?php _e('Test features:', 'easy-directory-system'); ?></strong> <?php _e('Use demo data to test menu sync, SEO settings, and category management features.', 'easy-directory-system'); ?></li>
                <li><strong><?php _e('Production ready:', 'easy-directory-system'); ?></strong> <?php _e('Demo categories include proper slugs, descriptions, and are SEO-friendly.', 'easy-directory-system'); ?></li>
            </ul>
        </div>
        
        <!-- Next Steps -->
        <div class="eds-form-section" style="text-align: center; padding: 30px; margin-top: 20px;">
            <h2><?php _e('After Installing Demo Data', 'easy-directory-system'); ?></h2>
            <p style="font-size: 15px; margin: 20px 0;">
                <?php _e('Once demo categories are created, you can:', 'easy-directory-system'); ?>
            </p>
            <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; margin-top: 20px;">
                <a href="<?php echo admin_url('admin.php?page=easy-categories'); ?>" class="button button-primary button-large">
                    <span class="dashicons dashicons-list-view"></span>
                    <?php _e('View Categories', 'easy-directory-system'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=easy-categories-menu-sync'); ?>" class="button button-secondary button-large">
                    <span class="dashicons dashicons-menu"></span>
                    <?php _e('Sync to Menu', 'easy-directory-system'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=easy-categories-howto'); ?>" class="button button-secondary button-large">
                    <span class="dashicons dashicons-book-alt"></span>
                    <?php _e('Read How To Guide', 'easy-directory-system'); ?>
                </a>
            </div>
        </div>
    </div>
</div>
