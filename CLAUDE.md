# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What This Plugin Does

WP Featured Posts lets admins mark any post type as "featured," reorder featured posts via drag-and-drop, and optionally pin them to the top of archive pages (sticky behavior for custom post types). It syncs featured state across WPML translations.

## Development

This is a classic WordPress plugin — no build toolchain, no npm. PHP files, vanilla jQuery, and hand-written CSS/JS with pre-minified `.min.` copies.

**When editing JS or CSS**: edit both the source file and its `.min.` counterpart in `assets/`, or the minified version is what actually loads in production. WordPress enqueues the `.min.` files.

**Testing**: no automated test suite exists. Test changes manually against a WordPress instance with the plugin activated. The parent repo's CLAUDE.md has Docker setup instructions.

## Architecture

### Core Class: `WPFP_Featured_Posts` (singleton)

[wp-featured-posts.php](wp-featured-posts.php) — the main file. Handles:
- Admin UI registration (submenu pages under each enabled post type)
- Three AJAX endpoints: `save_featured_sorting`, `delete_featured_sorting`, `order_featured_sorting`
- Frontend query modification via `pre_get_posts` + `the_posts` filters (sticky behavior)
- Pin icon rendering on featured post titles via `the_title` filter

### Settings: `WPFP_Featured_Posts_Setting`

[inc/wp-featured-posts-setting.php](inc/wp-featured-posts-setting.php) — WordPress Settings API integration. Registers the "Featured Posts" top-level admin menu and all settings fields. Options stored as a single serialized array under `wp_featured_posts_settings`.

### WPML Integration: `WPFP_WPML`

[inc/wp-featured-posts-wpml.php](inc/wp-featured-posts-wpml.php) — static helper that syncs featured/ordering state across WPML translations when adding, removing, or reordering featured posts.

### Template

[templates/featured-posts.php](templates/featured-posts.php) — the admin drag-and-drop sorting page. Rendered by `featured_callback()`. Expects variables set by the caller (`$title`, `$posts`, `$featured_posts`, `$post_type`, `$featured_key`, etc.).

### JavaScript

- [assets/js/main.js](assets/js/main.js) — featured post management UI (add/delete/reorder via AJAX + jQuery UI Sortable)
- [assets/js/setting.js](assets/js/setting.js) — settings page (toggle expand, media uploader for pin image)

Both depend on `jQuery`. `main.js` also depends on `jquery-ui-sortable`.

## Data Model

- **Option**: `wp_featured_posts_settings` — plugin config (enabled post types, sticky settings, pin icon config)
- **Post meta**: `{post_type}_featured` with value `'1'` marks a post as featured (e.g., `post_featured`, `page_featured`, `news_featured`)
- **`menu_order`**: used to store sort position of featured posts
- **`sticky_posts` option**: featured posts are added to WordPress's native sticky_posts array

## Key Filters and Actions

- `wpfp_add_featured_column_{$post_type}` — control whether the "Featured" column appears in the post list
- `wpfp_title_featured_{$post_type}` — customize the featured page title
- `wpfp_show_select_featured_{$post_type}` — hide the post selector dropdown
- `wpfp_allow_delete_featured_{$post_type}` — hide the delete button
- `wpfp_pin_icon_position` — `'before'` (default) or `'after'` the title

## Code Conventions

- PHP function/class prefix: `WPFP_` or `wpfp_`
- All user-facing strings use text domain `wp-featured-posts`
- Nonce actions: `save-featured-sorting`, `delete-featured-sorting`, `order-featured-sorting`
- AJAX actions match the method names: `save_featured_sorting`, `delete_featured_sorting`, `order_featured_sorting`

## SVN / WordPress.org

The `svn/` directory is the WordPress.org plugin repository checkout (trunk + tags). It mirrors the plugin source for releases but is gitignored. Don't modify files inside `svn/` directly — copy from root after changes are finalized.
