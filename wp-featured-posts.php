<?php
/**
 * Plugin Name:       WP Featured Posts
 * Plugin URI:        https://wordpress.org/plugins/wp-featured-posts/
 * Description:       Set featured posts and sortable. Compatible with WPML.
 * Version:           1.0.0
 * Requires at least: 4.7
 * Requires PHP:      7.0
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
define('WPFP_VERSION', '1.0.0');

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

        new WPFP_Featured_Posts_Setting();

        $default = [
            'enable'     => 0,
            'post_types' => [],
        ];

        $this->options = get_option('wp_featured_posts_settings', $default);

        if ($this->options['enable'] && $this->options['post_types']) {
            foreach ($this->options['post_types'] as $post_type) {
                add_filter("manage_{$post_type}_posts_columns", [$this, 'set_custom_featured_columns'], 5);
                add_action("manage_{$post_type}_posts_custom_column", [$this, 'custom_column'], 10, 2);
            }
        }

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

            wp_enqueue_style('admin-featured-sorting', WPFP_PLUGIN_URL . '/assets/css/style.css', [], WPFP_VERSION);

            wp_enqueue_script('admin-featured-sorting', WPFP_PLUGIN_URL . '/assets/js/main.js', ['jquery', 'jquery-ui-sortable'], WPFP_VERSION, true);

            wp_localize_script('admin-featured-sorting', 'wtfp_admin_global',
                [
                    'site_url'  => site_url(),
                    'theme_url' => WPFP_PLUGIN_URL,
                    'ajax_url'  => admin_url('admin-ajax.php')
                ]
            );

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
            $post_type_title = $get_post_type[$post_type]->label;
            $featured_key = "{$post_type}_featured";
            $posts = self::get_posts($post_type, $featured_key);
            $featured_posts = self::get_featured_sorting($post_type, $featured_key);

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
                    'compare' => 'NOT EXISTS'
                ],
                [
                    'key'   => $key,
                    'value' => 0
                ]
            ]
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
                    'value' => '1'
                ]
            ]
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

            // Update
            $args = [
                'ID'         => $data['post_id'],
                'menu_order' => $featured_sorting
            ];
            $update = wp_update_post($args);
            update_post_meta($data['post_id'], $data['featured_key'], 1);

            if (WPFP_WPML::is_active()) {
                $post_type = $data['post_type'] ?? '';
                $trid = apply_filters('wpml_element_trid', NULL, $data['post_id'], 'post_' . $post_type);
                if ($trid) {
                    $ids = WPFP_WPML::wpml_get_post_ids($data['post_id']);
                    if ($ids) {
                        foreach ($ids as $lang => $id) {
                            if ($data['lang'] != $lang) {

                                $args = [
                                    'ID'         => $id,
                                    'menu_order' => $featured_sorting
                                ];
                                $update = wp_update_post($args);

                                update_post_meta($id, $data['featured_key'], 1);
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

        // Delete
        $delete = update_post_meta($data['post_id'], $data['featured_key'], '0');

        if (WPFP_WPML::is_active()) {
            $post_type = $data['post_type'] ?? '';
            $trid = apply_filters('wpml_element_trid', NULL, $data['post_id'], 'post_' . $post_type);
            if ($trid) {
                $ids = WPFP_WPML::wpml_get_post_ids($data['post_id']);
                if ($ids) {
                    foreach ($ids as $lang => $id) {
                        if ($data['lang'] != $lang) {
                            $delete = update_post_meta($id, $data['featured_key'], '0');
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

        if ($data['post_id'] && is_array($data['post_id'])) {

            $post_type = $data['post_type'] ?? '';

            foreach ($data['post_id'] as $key => $post_id) {

                update_post_meta($post_id, $data['featured_key'], '1');
                $args = [
                    'ID'         => $post_id,
                    'menu_order' => $key
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
                                        'menu_order' => $key
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
    protected function get_post_type($post_type) {
        return get_post_types(['name' => $post_type], 'objects');
    }

}

require WPFP_PATH . 'inc/wp-featured-posts-setting.php';
require WPFP_PATH . 'inc/wpml-functions.php';

WPFP_Featured_Posts::instance();
