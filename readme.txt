=== WP Featured Posts ===
Contributors: nutttaro
Donate link: https://www.buymeacoffee.com/nutttaro
Tags: featured-posts, featured-post, feature-posts, feature-post
Requires at least: 4.7
Tested up to: 7.0
Stable tag: 1.2.1
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

WP Featured Posts is a plugin choose featured posts, sortable and sticky custom post type and compatible with WPML.

== Description ==

WP Featured Posts is a plugin choose featured posts, sortable and sticky custom post type.

Features:

* Easy for choose featured posts.
* Easy for ordering featured posts.
* Support custom post types.
* Sticky custom post types for sticky posts at first.
* The plugin is lightweight.
* Compatible with WPML.

== Installation ==
1. Upload `wp-featured-posts.zip` to the install plugin page
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to *Featured Posts* in the left-hand menu to start setting the plugin

== Frequently Asked Questions ==

= How to setting the plugin? =

Go to *Featured Posts* in the left-hand menu to start setting the plugin

= How to custom code for get featured posts? =

You can see example code in *Featured Posts* setting

== Screenshots ==

1. Screenshot of the menu page for Featured Posts Setting page.
1. Drag and Drop for order posts. It easy!
1. How to sticky posts work

== Changelog ==

= 1.2.1 =
* Compatibility: WordPress 7.0 support
* Fix: Featured post ordering on frontend now matches admin drag-and-drop order
* Fix: Toggling featured status in block editor sidebar now correctly updates sticky posts and sort order
* Fix: Updated Gutenberg sidebar to import PluginDocumentSettingPanel from @wordpress/editor (deprecated from @wordpress/edit-post)
* Fix: Replaced deprecated jQuery .submit() shorthand with .on('submit', ...) for jQuery 4.x forward compatibility
* Fix: Removed deprecated jQuery UI .disableSelection() call
* Enhancement: Replaced inline wp_head styles with wp_add_inline_style() for pin icon CSS
* Enhancement: Updated @wordpress/scripts to v32

= 1.2.0 =
* Feature: Added [featured_posts] shortcode with post_type and limit attributes
* Feature: Added Gutenberg block editor sidebar panel for toggling featured status
* Feature: Registered post meta with REST API support (show_in_rest)
* Feature: Added build toolchain with @wordpress/scripts for editor assets
* Fix: Featured column now works for all post types (was hardcoded to testimonials only)
* Fix: Fixed setting.js syntax error (missing closing bracket for document.ready)

= 1.1.1 =
* Fix: Resolved media uploader not working for custom pin image upload
* Fix: Improved JavaScript initialization with proper DOM ready handling
* Fix: Enhanced event delegation for better compatibility

= 1.1.0 =
* Security: Added ABSPATH checks to prevent direct file access
* Security: Improved output escaping in template files
* Enhancement: Added translator comments for better internationalization support
* Enhancement: Fixed wp_send_json_* function usage (removed incorrect echo statements)
* Enhancement: Modernized code to comply with WordPress coding standards
* Enhancement: Fixed deprecated FILTER_SANITIZE_STRING for PHP 8.1+ compatibility
* Enhancement: Fixed undefined variable $q in sticky posts logic
* Feature: Added pin icon display for featured posts with customizable settings
* Feature: Pin icon can be enabled/disabled per configuration
* Feature: Adjustable pin icon size (10-50px)
* Feature: Support for custom pin image upload
* Feature: Pin icon position can be filtered (before/after title)
* Fix: Featured posts now work correctly on category, tag, author, and date archive pages
* Compatibility: Tested up to WordPress 6.9

= 1.0.7 =
* Tested up to WordPress 6.1.1
* Add Tip me on Ko-fi

= 1.0.6 =
* Tested up to WordPress 5.8.1

= 1.0.5 =
* Add action 'after_setup_theme' for feature column on custom post type

= 1.0.4 =
* Fix bug setting options array

= 1.0.3 =
* Fix bug setting options
* Add fillter show delete button

= 1.0.2 =
* Add fillter title featured post
* Add fillter show select featured post

= 1.0.1 =
* Fix bug setting options

= 1.0.0 =
* Initial Release
