<?php
/**
 * How To Template
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('How To Use Easy Categories', 'easy-directory-system'); ?></h1>
    
    <div class="eds-wrap">
        <!-- Introduction -->
        <div class="eds-form-section">
            <h2>📚 <?php _e('Getting Started with WooCommerce Categories', 'easy-directory-system'); ?></h2>
            <p style="font-size: 15px; line-height: 1.7;">
                <?php _e('This guide will walk you through setting up your first WooCommerce product directory using Easy Categories. Follow each step carefully to create a professional category structure.', 'easy-directory-system'); ?>
            </p>
        </div>
        
        <!-- Step 1 -->
        <div class="eds-form-section" style="border-left: 4px solid #2271b1;">
            <h2>🔧 <?php _e('Step 1: Configure Settings', 'easy-directory-system'); ?></h2>
            <ol style="font-size: 14px; line-height: 1.8;">
                <li><strong><?php _e('Go to:', 'easy-directory-system'); ?></strong> Easy Categories → Settings</li>
                <li><strong><?php _e('Choose URL Character Setting:', 'easy-directory-system'); ?></strong>
                    <ul style="margin-top: 10px;">
                        <li><strong><?php _e('For non-English shops:', 'easy-directory-system'); ?></strong> <?php _e('Select "Auto-convert Greek to Greeklish (Latin)" for Greek language support', 'easy-directory-system'); ?><br>
                            <em><?php _e('This converts Greek characters to Latin automatically (e.g., Greek text becomes Latin-friendly URLs)', 'easy-directory-system'); ?></em>
                        </li>
                        <li><strong><?php _e('For international shops:', 'easy-directory-system'); ?></strong> <?php _e('Select "Letters, Numbers, Underscores, Hyphens"', 'easy-directory-system'); ?></li>
                    </ul>
                </li>
                <li><strong><?php _e('Enable SEO Features:', 'easy-directory-system'); ?></strong> <?php _e('Check the box to enable meta titles and descriptions', 'easy-directory-system'); ?></li>
                <li><strong><?php _e('Click "Save Changes"', 'easy-directory-system'); ?></strong></li>
            </ol>
        </div>
        
        <!-- Step 2 -->
        <div class="eds-form-section" style="border-left: 4px solid #00a32a;">
            <h2>📁 <?php _e('Step 2: Create Parent Categories', 'easy-directory-system'); ?></h2>
            <p><strong><?php _e('Example: Electronics Shop Structure', 'easy-directory-system'); ?></strong></p>
            <ol style="font-size: 14px; line-height: 1.8;">
                <li><strong><?php _e('Go to:', 'easy-directory-system'); ?></strong> Easy Categories → Add New Category</li>
                <li><strong><?php _e('Make sure:', 'easy-directory-system'); ?></strong> <?php _e('Taxonomy dropdown shows "Product Categories (WooCommerce)"', 'easy-directory-system'); ?></li>
                <li><strong><?php _e('Create your main categories:', 'easy-directory-system'); ?></strong>
                    <div style="background: #f8f9fa; padding: 15px; margin: 15px 0; border-radius: 4px;">
                        <table style="width: 100%; font-size: 13px;">
                            <thead>
                                <tr style="background: #fff;">
                                    <th style="padding: 8px; text-align: left;"><?php _e('Category Name', 'easy-directory-system'); ?></th>
                                    <th style="padding: 8px; text-align: left;"><?php _e('Friendly URL', 'easy-directory-system'); ?></th>
                                    <th style="padding: 8px; text-align: left;"><?php _e('Parent', 'easy-directory-system'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="padding: 8px;">Electronics</td>
                                    <td style="padding: 8px;"><code>electronics</code></td>
                                    <td style="padding: 8px;">— <?php _e('None', 'easy-directory-system'); ?> —</td>
                                </tr>
                                <tr style="background: #fff;">
                                    <td style="padding: 8px;">Home Appliances</td>
                                    <td style="padding: 8px;"><code>home-appliances</code></td>
                                    <td style="padding: 8px;">— <?php _e('None', 'easy-directory-system'); ?> —</td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px;">Fashion</td>
                                    <td style="padding: 8px;"><code>fashion</code></td>
                                    <td style="padding: 8px;">— <?php _e('None', 'easy-directory-system'); ?> —</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </li>
                <li><strong><?php _e('Fill in SEO fields:', 'easy-directory-system'); ?></strong>
                    <ul style="margin-top: 10px;">
                        <li><strong><?php _e('Meta Title:', 'easy-directory-system'); ?></strong> <?php _e('"Electronics - Best Deals on Laptops, Phones & More"', 'easy-directory-system'); ?></li>
                        <li><strong><?php _e('Meta Description:', 'easy-directory-system'); ?></strong> <?php _e('"Shop the latest electronics including laptops, smartphones, tablets and accessories at the best prices."', 'easy-directory-system'); ?></li>
                    </ul>
                </li>
            </ol>
        </div>
        
        <!-- Step 3 -->
        <div class="eds-form-section" style="border-left: 4px solid #d63638;">
            <h2>🌳 <?php _e('Step 3: Create Subcategories', 'easy-directory-system'); ?></h2>
            <p><strong><?php _e('Example: Electronics Subcategories', 'easy-directory-system'); ?></strong></p>
            <ol style="font-size: 14px; line-height: 1.8;">
                <li><strong><?php _e('Go to:', 'easy-directory-system'); ?></strong> Easy Categories → Add New Category</li>
                <li><strong><?php _e('Create subcategories under "Electronics":', 'easy-directory-system'); ?></strong>
                    <div style="background: #f8f9fa; padding: 15px; margin: 15px 0; border-radius: 4px;">
                        <table style="width: 100%; font-size: 13px;">
                            <thead>
                                <tr style="background: #fff;">
                                    <th style="padding: 8px; text-align: left;"><?php _e('Category Name', 'easy-directory-system'); ?></th>
                                    <th style="padding: 8px; text-align: left;"><?php _e('Friendly URL', 'easy-directory-system'); ?></th>
                                    <th style="padding: 8px; text-align: left;"><?php _e('Parent Category', 'easy-directory-system'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="padding: 8px;">Computers</td>
                                    <td style="padding: 8px;"><code>computers</code></td>
                                    <td style="padding: 8px;">Electronics</td>
                                </tr>
                                <tr style="background: #fff;">
                                    <td style="padding: 8px;">Mobile Phones</td>
                                    <td style="padding: 8px;"><code>mobile-phones</code></td>
                                    <td style="padding: 8px;">Electronics</td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px;">Tablets</td>
                                    <td style="padding: 8px;"><code>tablets</code></td>
                                    <td style="padding: 8px;">Electronics</td>
                                </tr>
                                <tr style="background: #fff;">
                                    <td style="padding: 8px;">Accessories</td>
                                    <td style="padding: 8px;"><code>accessories</code></td>
                                    <td style="padding: 8px;">Electronics</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </li>
                <li><strong><?php _e('Important:', 'easy-directory-system'); ?></strong> <?php _e('Select the parent category from the dropdown before saving', 'easy-directory-system'); ?></li>
            </ol>
        </div>
        
        <!-- Step 4 -->
        <div class="eds-form-section" style="border-left: 4px solid #f0b849;">
            <h2>🔗 <?php _e('Step 4: Sync to WordPress Menu', 'easy-directory-system'); ?></h2>
            <ol style="font-size: 14px; line-height: 1.8;">
                <li><strong><?php _e('Go to:', 'easy-directory-system'); ?></strong> Easy Categories → Sync to Menu</li>
                <li><strong><?php _e('Select your menu:', 'easy-directory-system'); ?></strong> <?php _e('Choose "Main Menu" or "Primary Menu"', 'easy-directory-system'); ?></li>
                <li><strong><?php _e('Select taxonomy:', 'easy-directory-system'); ?></strong> Product Categories (WooCommerce)</li>
                <li><strong><?php _e('Click "Sync Categories to Menu"', 'easy-directory-system'); ?></strong></li>
                <li><strong><?php _e('Result:', 'easy-directory-system'); ?></strong> <?php _e('All enabled categories will appear in your menu with proper hierarchy', 'easy-directory-system'); ?></li>
            </ol>
            <div style="background: #fff3cd; padding: 15px; margin: 15px 0; border-radius: 4px; border: 1px solid #f0b849;">
                <p style="margin: 0; font-size: 13px;">
                    <strong>💡 <?php _e('Pro Tip:', 'easy-directory-system'); ?></strong> <?php _e('Only categories marked as "Enabled" will sync to your menu. You can toggle this in the All Categories list.', 'easy-directory-system'); ?>
                </p>
            </div>
        </div>
        
        <!-- Step 5 -->
        <div class="eds-form-section" style="border-left: 4px solid #7c3aed;">
            <h2>🎨 <?php _e('Step 5: Add Images & Descriptions', 'easy-directory-system'); ?></h2>
            <ol style="font-size: 14px; line-height: 1.8;">
                <li><strong><?php _e('Go to:', 'easy-directory-system'); ?></strong> Easy Categories → All Categories</li>
                <li><strong><?php _e('Click "Edit"', 'easy-directory-system'); ?></strong> <?php _e('on any category', 'easy-directory-system'); ?></li>
                <li><strong><?php _e('Upload images:', 'easy-directory-system'); ?></strong>
                    <ul style="margin-top: 10px;">
                        <li><strong><?php _e('Cover Image:', 'easy-directory-system'); ?></strong> <?php _e('Large banner (1200x400px recommended)', 'easy-directory-system'); ?></li>
                        <li><strong><?php _e('Thumbnail:', 'easy-directory-system'); ?></strong> <?php _e('Square icon (300x300px recommended)', 'easy-directory-system'); ?></li>
                    </ul>
                </li>
                <li><strong><?php _e('Add descriptions:', 'easy-directory-system'); ?></strong>
                    <ul style="margin-top: 10px;">
                        <li><strong><?php _e('Description:', 'easy-directory-system'); ?></strong> <?php _e('Shows at the top of category page', 'easy-directory-system'); ?></li>
                        <li><strong><?php _e('Additional Description:', 'easy-directory-system'); ?></strong> <?php _e('Shows after products (good for SEO)', 'easy-directory-system'); ?></li>
                    </ul>
                </li>
            </ol>
        </div>
        
        <!-- Complete Example -->
        <div class="eds-form-section" style="background: #f0f6fc; border: 2px solid #2271b1;">
            <h2>✅ <?php _e('Complete Example: Electronics Category', 'easy-directory-system'); ?></h2>
            <div style="font-size: 14px; line-height: 1.8;">
                <p><strong><?php _e('Category Name:', 'easy-directory-system'); ?></strong> Computers</p>
                <p><strong><?php _e('Friendly URL:', 'easy-directory-system'); ?></strong> <code>computers</code></p>
                <p><strong><?php _e('Parent:', 'easy-directory-system'); ?></strong> Electronics</p>
                <p><strong><?php _e('Meta Title:', 'easy-directory-system'); ?></strong> Computers & Laptops - Best Prices Online</p>
                <p><strong><?php _e('Meta Description:', 'easy-directory-system'); ?></strong> Buy computers, laptops and gaming PCs at the best prices. Free shipping on orders over $50.</p>
                <p><strong><?php _e('Description:', 'easy-directory-system'); ?></strong></p>
                <div style="background: #fff; padding: 15px; border-radius: 4px; margin: 10px 0;">
                    <?php _e('Discover our extensive collection of computers and laptops from leading brands. Whether you need a powerful gaming PC, a lightweight laptop for work, or a budget-friendly desktop computer, we have the perfect solution for you.', 'easy-directory-system'); ?>
                </div>
                <p><strong><?php _e('Additional Description:', 'easy-directory-system'); ?></strong></p>
                <div style="background: #fff; padding: 15px; border-radius: 4px; margin: 10px 0;">
                    <?php _e('All our computers come with warranty and technical support. Free shipping on orders over $50. Compare prices and specifications to find the best computer for your needs. Latest models from HP, Dell, Lenovo, ASUS and more.', 'easy-directory-system'); ?>
                </div>
                <p><strong><?php _e('Final URL:', 'easy-directory-system'); ?></strong> <code>https://yoursite.com/product-category/electronics/computers/</code></p>
            </div>
        </div>
        
        <!-- Tips & Best Practices -->
        <div class="eds-form-section">
            <h2>💡 <?php _e('Tips & Best Practices', 'easy-directory-system'); ?></h2>
            <ul style="font-size: 14px; line-height: 1.8;">
                <li><strong><?php _e('Use descriptive names:', 'easy-directory-system'); ?></strong> <?php _e('Make category names clear and easy to understand', 'easy-directory-system'); ?></li>
                <li><strong><?php _e('Keep URL slugs short:', 'easy-directory-system'); ?></strong> <?php _e('Use simple, memorable URLs without special characters', 'easy-directory-system'); ?></li>
                <li><strong><?php _e('Enable categories strategically:', 'easy-directory-system'); ?></strong> <?php _e('Only enable categories that should appear in menus', 'easy-directory-system'); ?></li>
                <li><strong><?php _e('Add SEO content:', 'easy-directory-system'); ?></strong> <?php _e('Use both descriptions for better Google rankings', 'easy-directory-system'); ?></li>
                <li><strong><?php _e('Use high-quality images:', 'easy-directory-system'); ?></strong> <?php _e('Professional photos improve user experience', 'easy-directory-system'); ?></li>
                <li><strong><?php _e('Organize hierarchically:', 'easy-directory-system'); ?></strong> <?php _e('Use parent-child structure: Electronics → Computers → Gaming PCs', 'easy-directory-system'); ?></li>
                <li><strong><?php _e('Test your URLs:', 'easy-directory-system'); ?></strong> <?php _e('Visit category pages to make sure Greeklish conversion works correctly', 'easy-directory-system'); ?></li>
            </ul>
        </div>
        
        <!-- Troubleshooting -->
        <div class="eds-form-section" style="background: #fcf3f3; border: 1px solid #d63638;">
            <h2>🔧 <?php _e('Troubleshooting', 'easy-directory-system'); ?></h2>
            <dl style="font-size: 14px; line-height: 1.8;">
                <dt style="font-weight: bold; margin-top: 15px;"><?php _e('Q: Greek characters are not converting to Greeklish', 'easy-directory-system'); ?></dt>
                <dd><?php _e('A: Make sure you selected "Auto-convert Greek to Greeklish" in Settings and saved. Clear browser cache.', 'easy-directory-system'); ?></dd>
                
                <dt style="font-weight: bold; margin-top: 15px;"><?php _e('Q: Categories not showing in menu', 'easy-directory-system'); ?></dt>
                <dd><?php _e('A: Check that categories are marked as "Enabled" in All Categories list. Re-sync the menu.', 'easy-directory-system'); ?></dd>
                
                <dt style="font-weight: bold; margin-top: 15px;"><?php _e('Q: Subcategories not appearing under parent', 'easy-directory-system'); ?></dt>
                <dd><?php _e('A: Edit the subcategory and make sure you selected the correct parent from the dropdown.', 'easy-directory-system'); ?></dd>
                
                <dt style="font-weight: bold; margin-top: 15px;"><?php _e('Q: SEO preview not updating', 'easy-directory-system'); ?></dt>
                <dd><?php _e('A: Make sure "Enable SEO Features" is checked in Settings. The preview updates in real-time as you type.', 'easy-directory-system'); ?></dd>
            </dl>
        </div>
        
        <!-- Need Help -->
        <div class="eds-form-section" style="text-align: center; padding: 30px;">
            <h2><?php _e('Need More Help?', 'easy-directory-system'); ?></h2>
            <p style="font-size: 15px; margin: 20px 0;">
                <?php _e('If you have questions or need assistance, feel free to reach out!', 'easy-directory-system'); ?>
            </p>
            <a href="https://github.com/TheoSfak/easy-directory-system/issues" target="_blank" class="button button-primary button-large">
                <?php _e('Report Issue on GitHub', 'easy-directory-system'); ?>
            </a>
            <a href="<?php echo admin_url('admin.php?page=easy-categories-donate'); ?>" class="button button-secondary button-large" style="margin-left: 10px;">
                ❤️ <?php _e('Support Development', 'easy-directory-system'); ?>
            </a>
        </div>
    </div>
</div>

<style>
.eds-form-section {
    background: #fff;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 4px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.eds-form-section h2 {
    margin-top: 0;
    padding-bottom: 10px;
    border-bottom: 2px solid #f0f0f0;
}
.eds-form-section ol,
.eds-form-section ul {
    margin-left: 20px;
}
.eds-form-section code {
    background: #f8f9fa;
    padding: 2px 6px;
    border-radius: 3px;
    font-family: monospace;
    color: #d63638;
}
</style>
