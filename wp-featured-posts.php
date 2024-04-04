<?php
/**
 * Plugin Name:       WP Featured Posts
 * Plugin URI:        https://wordpress.org/plugins/wp-featured-posts/
 * Description:       Set featured posts, sortable and sticky custom post type. Compatible with WPML.
 * Version:           1.0.7
 * Requires at least: 4.7
 * Requires PHP:      7.4
 * Tested up to:      6.5
 * Author:            NuttTaro
 * Author URI:        https://nutttaro.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wp-featured-posts
 * Domain Path:       /languages
 */

// Define constants.
define('WPFP_PATH', plugin_dir_path(__FILE__));
define('WPFP_BASENAME', plugin_basename(__FILE__));
define('WPFP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WPFP_VERSION', '1.0.7');

/**
 * Class WPFP_Featured_Posts
 */
class WPFP_Featured_Posts
{

    /** @var null $instance */
    public static $instance = null;

    /**
     * Array of custom settings/options
     **/
    private $options;

    /**
     * Singleton method responsible for the instantiation of the object
     */
    static public function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * WPFP_Featured_Posts constructor.
     */
    public function __construct()
    {

        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts'], 99);
        add_action('admin_menu', [$this, 'add_submenu_page']);

        add_action('wp_ajax_save_featured_sorting', [$this, 'save_featured_sorting']);
        add_action('wp_ajax_delete_featured_sorting', [$this, 'delete_featured_sorting']);
        add_action('wp_ajax_order_featured_sorting', [$this, 'order_featured_sorting']);

        add_action('pre_get_posts', [$this, 'pre_get_posts']);
        add_filter('the_posts', [$this, 'the_posts'], 10, 2);

        new WPFP_Featured_Posts_Setting();

        $default = [
            'enable'           => 0,
            'post_types'       => [],
            'sticky_post_type' => [],
        ];

        $this->options = get_option('wp_featured_posts_settings', $default);

        self::set_default_options();

        add_action('after_setup_theme', [$this, 'after_setup_theme']);
    }

    /**
     * Enqueue Scripts
     *
     * @param $hook
     */
    public function admin_enqueue_scripts($hook)
    {
        $post_type = '';
        $current_screen = get_current_screen();
        if (isset($current_screen->post_type)) {
            $post_type = $current_screen->post_type ? $current_screen->post_type : 'post';
        }

        if ($this->options['enable'] && in_array($post_type, $this->options['post_types'])) {

            wp_enqueue_style('admin-featured-sorting', WPFP_PLUGIN_URL . '/assets/css/style.min.css', [], WPFP_VERSION);

            wp_enqueue_script('admin-featured-sorting', WPFP_PLUGIN_URL . '/assets/js/main.min.js', ['jquery', 'jquery-ui-sortable'], WPFP_VERSION, true);

            wp_localize_script('admin-featured-sorting', 'wtfp_admin_global',
                [
                    'site_url'  => site_url(),
                    'theme_url' => WPFP_PLUGIN_URL,
                    'ajax_url'  => admin_url('admin-ajax.php'),
                ]
            );

        }
    }

    /**
     * Add column to post type after setup theme
     */
    public function after_setup_theme()
    {
        if ($this->options['enable'] && $this->options['post_types']) {
            foreach ($this->options['post_types'] as $post_type) {
                $allow_featured_column = apply_filters("wpfp_add_featured_column_{$post_type}", true);
                if ($allow_featured_column) {
                    add_filter("manage_{$post_type}_posts_columns", [$this, 'set_custom_featured_columns'], 5);
                    add_action("manage_{$post_type}_posts_custom_column", [$this, 'custom_column'], 10, 2);
                }
            }
        }
    }

    /**
     * Add submenu to post type
     */
    public function add_submenu_page()
    {

        if ($this->options['enable'] && $this->options['post_types']) {
            foreach ($this->options['post_types'] as $post_type) {

                $get_post_type = self::get_post_type($post_type);

                $parent_slug = $post_type == 'post' ? 'edit.php' : "edit.php?post_type={$post_type}";
                $menu_slug = "featured-{$post_type}-page";

                add_submenu_page(
                    $parent_slug,
                    sprintf(__('Featured %s', 'wp-featured-posts'), $get_post_type[$post_type]->label),
                    sprintf(__('Featured %s', 'wp-featured-posts'), $get_post_type[$post_type]->label),
                    'manage_options',
                    $menu_slug,
                    [$this, 'featured_callback']
                );
            }
        }

    }

    /**
     * Render testimonials submenu
     */
    public function featured_callback()
    {
        $post_type = '';
        $current_screen = get_current_screen();
        if (isset($current_screen->post_type)) {
            $post_type = $current_screen->post_type ? $current_screen->post_type : 'post';
        }

        if ($this->options['enable'] && in_array($post_type, $this->options['post_types'])) {

            $get_post_type = self::get_post_type($post_type);

            $title = sprintf(__('Featured %s', 'wp-featured-posts'), $post_type);
            $title = apply_filters("wpfp_title_featured_{$post_type}", $title);
            $post_type_title = $get_post_type[$post_type]->label;
            $featured_key = "{$post_type}_featured";
            $posts = self::get_posts($post_type, $featured_key);
            $featured_posts = self::get_featured_sorting($post_type, $featured_key);

            $show_select_post = apply_filters("wpfp_show_select_featured_{$post_type}", true);
            $allow_delete = apply_filters("wpfp_allow_delete_featured_{$post_type}", true);

            require_once WPFP_PATH . 'templates/featured-posts.php';
        }
    }

    /**
     * Get all post type
     *
     * @param $post_type
     * @param $key
     * @return int[]|WP_Post[]
     */
    public function get_posts($post_type, $key)
    {
        $args = [
            'post_type'        => $post_type,
            'post_status'      => 'publish',
            'posts_per_page'   => -1,
            'order'            => 'DESC',
            'suppress_filters' => false,
            'meta_query'       => [
                'relation' => 'OR',
                [
                    'key'     => $key,
                    'compare' => 'NOT EXISTS',
                ],
                [
                    'key'   => $key,
                    'value' => 0,
                ],
            ],
        ];

        return get_posts($args);
    }

    /**
     * Get featured posts and order by asc
     *
     * @param $post_type
     * @param $key
     * @return int[]|WP_Post[]
     */
    public function get_featured_sorting($post_type, $key)
    {
        $args = [
            'post_type'        => $post_type,
            'post_status'      => 'publish',
            'posts_per_page'   => -1,
            'orderby'          => ['menu_order' => 'ASC', 'date' => 'DESC'],
            'suppress_filters' => false,
            'meta_query'       => [
                [
                    'key'   => $key,
                    'value' => '1',
                ],
            ],
        ];

        return get_posts($args);
    }

    /**
     * Ajax Add Feature Post
     */
    public function save_featured_sorting()
    {

        check_ajax_referer('save-featured-sorting');

        $data = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

        $data['_redirect'] = $data['_wp_http_referer'];

        $featured_sorting = absint($data['count_featured_sorting']) + 1;

        if ($featured_sorting) {

            $post_id = absint($data['post_id']);

            // Update
            $args = [
                'ID'         => $post_id,
                'menu_order' => $featured_sorting,
            ];
            $update = wp_update_post($args);
            update_post_meta($post_id, $data['featured_key'], 1);

            $sticky_posts = get_option('sticky_posts', []);
            $sticky_posts = array_merge($sticky_posts, [$post_id]);
            update_option('sticky_posts', $sticky_posts);

            if (WPFP_WPML::is_active()) {
                $post_type = $data['post_type'] ?? '';
                $trid = apply_filters('wpml_element_trid', NULL, $post_id, 'post_' . $post_type);
                if ($trid) {
                    $ids = WPFP_WPML::wpml_get_post_ids($post_id);
                    if ($ids) {
                        foreach ($ids as $lang => $id) {
                            if ($data['lang'] != $lang) {

                                $args = [
                                    'ID'         => $id,
                                    'menu_order' => $featured_sorting,
                                ];
                                $update = wp_update_post($args);

                                update_post_meta($id, $data['featured_key'], 1);

                                $sticky_posts = get_option('sticky_posts', []);
                                $sticky_posts = array_merge($sticky_posts, [$id]);
                                update_option('sticky_posts', $sticky_posts);
                            }
                        }
                    }
                }
            }

            if ($update) {
                $data['message'] = 'Added Featured ' . $data['post_type_title'];
                echo wp_send_json_success($data);
            } else {
                $data['message'] = '<strong>Error</strong> Cannot add featured ' . $data['post_type'];
                echo wp_send_json_error($data);
            }
        } else {
            $data['message'] = '<strong>Error</strong> Not found ' . $data['post_type'];
            echo wp_send_json_error($data);
        }

        wp_die();

    }

    /**
     * Delete Add Feature Post
     */
    public function delete_featured_sorting()
    {

        check_ajax_referer('delete-featured-sorting');

        $data = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

        if ($data['post_type'] == 'post') {
            $_redirect = 'edit.php?page=featured-' . $data['post_type'] . '-page';
        } else {
            $_redirect = 'edit.php?post_type=' . $data['post_type'] . '&page=featured-' . $data['post_type'] . '-page';
        }
        $data['_redirect'] = admin_url($_redirect);

        $post_id = absint($data['post_id']);

        // Delete
        $delete = delete_post_meta($post_id, $data['featured_key']);

        // Update
        $args = [
            'ID'         => $post_id,
            'menu_order' => 0,
        ];
        $update = wp_update_post($args);

        $sticky_posts = get_option('sticky_posts', []);
        if (($key = array_search($post_id, $sticky_posts)) !== false) {
            unset($sticky_posts[$key]);
        }
        update_option('sticky_posts', $sticky_posts);

        if (WPFP_WPML::is_active()) {
            $post_type = $data['post_type'] ?? '';
            $trid = apply_filters('wpml_element_trid', NULL, $post_id, 'post_' . $post_type);
            if ($trid) {
                $ids = WPFP_WPML::wpml_get_post_ids($post_id);
                if ($ids) {
                    foreach ($ids as $lang => $id) {
                        if ($data['lang'] != $lang) {
                            $delete = delete_post_meta($id, $data['featured_key']);

                            // Update
                            $args = [
                                'ID'         => $id,
                                'menu_order' => 0,
                            ];
                            $update = wp_update_post($args);

                            $sticky_posts = get_option('sticky_posts', []);
                            if (($key = array_search($id, $sticky_posts)) !== false) {
                                unset($sticky_posts[$key]);
                            }
                            update_option('sticky_posts', $sticky_posts);
                        }
                    }
                }
            }
        }

        if ($delete) {
            $data['message'] = 'Deleted Featured ' . $data['post_type_title'];
            echo wp_send_json_success($data);
        } else {
            $data['message'] = '<strong>Error</strong> Cannot delete featured ' . $data['post_type'];
            echo wp_send_json_error($data);
        }

        wp_die();

    }

    /**
     * Order Feature Post
     */
    public function order_featured_sorting()
    {

        check_ajax_referer('order-featured-sorting');

        $data = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

        $post_ids = $data['post_id'];

        if ($post_ids && is_array($post_ids)) {

            $post_type = $data['post_type'] ?? '';

            foreach ($post_ids as $key => $post_id) {

                update_post_meta($post_id, $data['featured_key'], '1');
                $args = [
                    'ID'         => $post_id,
                    'menu_order' => $key,
                ];
                $update = wp_update_post($args);

                if (WPFP_WPML::is_active()) {
                    $trid = apply_filters('wpml_element_trid', NULL, $post_id, 'post_' . $post_type);
                    if ($trid) {
                        $ids = WPFP_WPML::wpml_get_post_ids($post_id);
                        if ($ids) {
                            foreach ($ids as $lang => $id) {
                                if ($data['lang'] != $lang) {
                                    update_post_meta($id, $data['featured_key'], '1');
                                    $args = [
                                        'ID'         => $id,
                                        'menu_order' => $key,
                                    ];
                                    $update = wp_update_post($args);
                                }
                            }
                        }
                    }
                }

            }

            $data['message'] = 'Ordered Featured ' . $data['post_type_title'];
            echo wp_send_json_success($data);

        }

        wp_die();

    }

    /**
     * Add custon columns
     *
     * @param $columns
     * @return mixed
     */
    public function set_custom_featured_columns($columns)
    {
        $column_date = '';
        if (isset($columns['date'])) {
            $column_date = $columns['date'];
            unset($columns['date']);
        }

        $columns['featured'] = __('Featured', 'wp-featured-posts');

        if ($column_date) {
            $columns['date'] = $column_date;
        }

        return $columns;
    }

    /**
     * Display value in custom columns
     *
     * @param $column
     * @param $post_id
     */
    function custom_column($column, $post_id)
    {
        switch ($column) {

            case 'featured' :
                $featured = 0;
                $post_type = get_post_type($post_id);
                if ($post_type == 'testimonials') {
                    $featured = get_post_meta($post_id, "testimonial_featured", true);
                }

                if ($featured) {
                    echo __('Yes', 'wp-featured-posts');
                } else {
                    echo __('No', 'wp-featured-posts');
                }

                break;

        }
    }

    /**
     * Get post types
     *
     * @param $post_type
     * @return string[]|WP_Post_Type[]
     */
    protected function get_post_type($post_type)
    {
        return get_post_types(['name' => $post_type], 'objects');
    }

    /**
     * Set featured post to top
     *
     * @param $wp_query
     * @return mixed
     */
    public function pre_get_posts($wp_query)
    {
        if (!is_admin() && $wp_query->is_main_query()) {
            if ($this->options['enable'] && $this->options['sticky_post_type']) {

                foreach ($this->options['sticky_post_type'] as $sticky_post_type) {

                    if ($wp_query->is_home() && $sticky_post_type == 'post' || $wp_query->get('post_type') == $sticky_post_type) {

                        // Get original meta query
                        $meta_query = $wp_query->get('meta_query');

                        if (empty($meta_query)) {
                            $meta_query = [];
                        }

                        // Add our meta query to the original meta queries
                        $meta_query[] = [
                            'relation' => 'OR',
                            [
                                'key'     => "{$sticky_post_type}_featured",
                                'compare' => 'NOT EXISTS',
                            ],
                            [
                                'key'     => "{$sticky_post_type}_featured",
                                'compare' => 'EXISTS',
                            ],
                        ];
                        $wp_query->set('meta_query', $meta_query);

                        $wp_query->set('orderby', ['menu_order' => 'ASC', 'date' => 'DESC']);
                    }

                }

            }
        }

        return $wp_query;
    }

    /**
     * Make sticky post for custom post type.
     *
     * @param $posts
     * @param $wp_query
     * @return mixed
     */
    public function the_posts($posts, $wp_query)
    {
        $post_type = $wp_query->query_vars['post_type'];
        if (!is_admin() && $wp_query->is_main_query() && $this->options['enable'] && in_array($post_type, $this->options['sticky_post_type'])) {

            $page = 1;
            if (empty($wp_query->query_vars['nopaging']) && !$wp_query->is_singular) {
                $page = absint($wp_query->query_vars['paged']);
                if (empty($page)) {
                    $page = 1;
                }
            }

            /**
             * Sticky Posts
             * ref: wp-includes/class-wp-query.php
             * line 3131 to 3137
             */
            // Put sticky posts at the top of the posts array.
            $sticky_posts = get_option('sticky_posts');
            if ($page <= 1 && is_array($sticky_posts) && !empty($sticky_posts) && !$wp_query->query_vars['ignore_sticky_posts']) {
                $num_posts = count($posts);
                $sticky_offset = 0;
                // Loop over posts and relocate stickies to the front.
                for ($i = 0; $i < $num_posts; $i++) {
                    if (in_array($posts[$i]->ID, $sticky_posts, true)) {
                        $sticky_post = $posts[$i];
                        // Remove sticky from current position.
                        array_splice($posts, $i, 1);
                        // Move to front, after other stickies.
                        array_splice($posts, $sticky_offset, 0, [$sticky_post]);
                        // Increment the sticky offset. The next sticky will be placed at this offset.
                        $sticky_offset++;
                        // Remove post from sticky posts array.
                        $offset = array_search($sticky_post->ID, $sticky_posts, true);
                        unset($sticky_posts[$offset]);
                    }
                }

                // If any posts have been excluded specifically, Ignore those that are sticky.
                if (!empty($sticky_posts) && !empty($q['post__not_in'])) {
                    $sticky_posts = array_diff($sticky_posts, $q['post__not_in']);
                }

                // Fetch sticky posts that weren't in the query results.
                if (!empty($sticky_posts)) {

                    remove_filter('the_posts', [$this, 'the_posts'], 10);

                    $stickies = get_posts([
                        'post__in'    => $sticky_posts,
                        'post_type'   => $post_type,
                        'post_status' => 'publish',
                        'nopaging'    => true,
                    ]);

                    add_filter('the_posts', [$this, 'the_posts'], 10, 2);

                    foreach ($stickies as $sticky_post) {
                        array_splice($posts, $sticky_offset, 0, [$sticky_post]);
                        $sticky_offset++;
                    }
                }
            }
        }

        return $posts;
    }

    /**
     * Set default options
     */
    private function set_default_options()
    {
        $set_default_options = false;
        if (!isset($this->options['enable'])) {
            $this->options['enable'] = 0;
            $set_default_options = true;
        }
        if (!isset($this->options['post_types'])) {
            $this->options['post_types'] = [];
            $set_default_options = true;
        }
        if (!isset($this->options['sticky_post_type'])) {
            $this->options['sticky_post_type'] = [];
            $set_default_options = true;
        }

        if ($set_default_options) {
            update_option('wp_featured_posts_settings', $this->options);
        }
    }

}

require WPFP_PATH . 'inc/wp-featured-posts-setting.php';
require WPFP_PATH . 'inc/wp-featured-posts-wpml.php';

WPFP_Featured_Posts::instance();
