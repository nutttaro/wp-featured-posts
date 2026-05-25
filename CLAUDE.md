# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What This Plugin Does

WP Featured Posts lets admins mark any post type as "featured," reorder featured posts via drag-and-drop, and optionally pin them to the top of archive pages (sticky behavior for custom post types). It syncs featured state across WPML translations.

## Development

The plugin has two separate asset systems:

1. **Legacy jQuery assets** (`assets/js/`, `assets/css/`) — hand-written JS/CSS with pre-minified `.min.` copies. WordPress enqueues the `.min.` files in production.
2. **Gutenberg editor sidebar** (`src/editor/`) — React component built with `@wordpress/scripts` to `build/editor/`.

### Build Commands

```bash
npm run build        # Build Gutenberg sidebar (src/editor/ → build/editor/)
npm run start        # Watch mode for Gutenberg sidebar
npm run minify       # Minify legacy assets (main.js, setting.js, style.css → .min versions)
npm run minify:js    # Minify JS only (uses uglifyjs)
npm run minify:css   # Minify CSS only (uses clean-css-cli)
npm run build:all    # Build Gutenberg sidebar + minify legacy assets
```

**When editing legacy JS or CSS** in `assets/`: edit both the source file and its `.min.` counterpart, or run `npm run minify` after editing. The `.min.` files are what actually load.

**The `build/` directory is gitignored** — run `npm run build` after cloning for the Gutenberg sidebar to work.

**Testing**: no automated test suite. Test manually against a WordPress instance. The parent repo's CLAUDE.md has Docker setup instructions.

## Architecture

### Core Class: `WPFP_Featured_Posts` (singleton)

[wp-featured-posts.php](wp-featured-posts.php) — the main file. Handles:
- Admin UI registration (submenu pages under each enabled post type)
- Three AJAX endpoints: `save_featured_sorting`, `delete_featured_sorting`, `order_featured_sorting`
- Frontend query modification via `pre_get_posts` + `the_posts` filters (sticky behavior)
- Pin icon rendering on featured post titles via `the_title` filter (CSS via `wp_add_inline_style`)
- `[featured_posts]` shortcode (attributes: `post_type`, `limit`)
- REST API meta registration for block editor support (`register_meta_fields`)

### Settings: `WPFP_Featured_Posts_Setting`

[inc/wp-featured-posts-setting.php](inc/wp-featured-posts-setting.php) — WordPress Settings API integration. Registers the "Featured Posts" top-level admin menu and all settings fields. Options stored as a single serialized array under `wp_featured_posts_settings`.

### WPML Integration: `WPFP_WPML`

[inc/wp-featured-posts-wpml.php](inc/wp-featured-posts-wpml.php) — static helper that syncs featured/ordering state across WPML translations when adding, removing, or reordering featured posts.

### Gutenberg Sidebar

[src/editor/index.js](src/editor/index.js) — `PluginDocumentSettingPanel` (imported from `@wordpress/editor`) that adds a "Featured Post" toggle in the block editor sidebar. Reads enabled post types from `window.wpfpEditor.postTypes` (localized by PHP). Writes to `{post_type}_featured` post meta via the REST API.

### Template

[templates/featured-posts.php](templates/featured-posts.php) — the admin drag-and-drop sorting page. Rendered by `featured_callback()`. Expects variables set by the caller (`$title`, `$posts`, `$featured_posts`, `$post_type`, `$featured_key`, etc.).

### Legacy JavaScript

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
