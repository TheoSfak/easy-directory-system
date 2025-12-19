=== Easy Directory System ===
Contributors: irmaiden
Donate link: https://paypal.me/TheodoreSfakianakis
Tags: categories, directory, woocommerce, multilingual, seo
Requires at least: 5.8
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.7
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Advanced category management system with PrestaShop-style interface, multilingual support, and WooCommerce synchronization.

== Description ==

Easy Directory System provides a comprehensive solution for managing WordPress categories with an intuitive PrestaShop-style interface.

Features:
* PrestaShop-style category management interface
* WooCommerce category synchronization (bidirectional)
* Multilingual support for category metadata
* SEO tools (meta titles, descriptions, preview)
* Category images (cover and thumbnail)
* Position ordering with drag-and-drop
* Group access controls
* Redirection options (301, 302, 404, 410)
* Statistics dashboard
* Bulk actions
* AJAX-powered interface

== Installation ==

**From GitHub Releases (Recommended):**
1. Download the latest release ZIP from https://github.com/TheoSfak/easy-directory-system/releases
2. Go to WordPress Admin → Plugins → Add New → Upload Plugin
3. Choose the downloaded ZIP file and click Install Now
4. Activate the plugin

**From GitHub Source:**
1. Download ZIP from https://github.com/TheoSfak/easy-directory-system
2. Extract and rename folder from "easy-directory-system-main" to "easy-directory-system"
3. Upload to /wp-content/plugins/
4. Activate in WordPress Admin

**Manual Installation:**
1. Upload the plugin files to `/wp-content/plugins/easy-directory-system/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to 'Easy Categories' in the WordPress admin menu
4. Start managing your categories!

== Frequently Asked Questions ==

= How do I donate? =

You can support development via:
* PayPal: https://paypal.me/TheodoreSfakianakis
* Revolut: https://revolut.me/theodocmx

Your donations help us continue developing and maintaining this plugin!

== Changelog ==

= 1.0.7 =
* Added: Category Duplicator - clone categories with all metadata and settings
* Added: Scheduled Visibility - show/hide categories automatically based on date/time
* Added: Color Coding - assign custom colors to categories with WordPress color picker
* Added: Icon Picker - select from 300+ dashicons for category visual identification
* Enhanced: Database schema with category_color, category_icon, scheduled_from, scheduled_until columns
* Enhanced: Categories list now displays color badges and icons for quick identification
* Enhanced: WordPress Color Picker integration for seamless color selection
* Fixed: Frontend CSS enqueuing for mega menu overflow handling

= 1.0.6 =
* Added: Import/Export functionality with JSON and CSV format support
* Added: Demo Data installer with 3 preset category structures (E-Commerce, Blog, Minimal)
* Added: How To guide page with complete step-by-step tutorials
* Added: Beautiful dashicons for all sidebar menu items
* Improved: Modern UI with gradient buttons, hover effects, and smooth transitions
* Improved: Enhanced form styling with focus states and better typography
* Fixed: CSV export now generates proper format without HTML interference
* Fixed: Export handling moved before page rendering for clean file output

= 1.0.5 =
* Added: Greek to Greeklish auto-conversion for friendly URLs
* Added: Radio button interface for URL character settings
* Improved: Greek character support with real-time validation in slug field
* Improved: "Add New Category" moved to button inside All Categories page
* Improved: Dynamic description text showing allowed characters based on settings
* Fixed: Menu sync now includes all categories (with/without extended data)
* Fixed: Removed WooCommerce cross-taxonomy sync buttons
* Fixed: Page access issues after UI reorganization

= 1.0.0 =
* Initial release
* Core plugin structure
* Categories list view
* Database schema
* WooCommerce sync functionality
* AJAX operations
