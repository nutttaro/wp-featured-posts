<?php

/**
 * Class WPFP_Featured_Posts_Setting
 */
class WPFP_Featured_Posts_Setting
{
    /**
     * Array of custom settings/options
     **/
    private $options;

    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts'], 99);
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'page_init']);
    }

    /**
     * Enqueue Scripts
     *
     * @param $hook
     */
    public function admin_enqueue_scripts($hook)
    {
        if ($hook === 'toplevel_page_wp-featured-posts-settings-page') {
            wp_enqueue_script('admin-featured-sorting-setting', WPFP_PLUGIN_URL . '/assets/js/setting.min.js', ['jquery'], WPFP_VERSION, true);
        }
    }

    /**
     * Add settings page
     * The page will appear in Admin menu
     */
    public function add_settings_page()
    {
        add_menu_page(
            __('Featured Posts Setting', 'wp-featured-posts'), // Page title
            __('Featured Posts', 'wp-featured-posts'), // Title
            'edit_pages', // Capability
            'wp-featured-posts-settings-page', // Url slug
            [$this, 'create_admin_page'], // Callback
            'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="26.667" height="26.667" viewBox="0 0 20 20" xmlns:v="https://vecta.io/nano"><style><![CDATA[.B{fill:rgb(99.215686%,74.901961%,0%)}.C{fill:rgb(59.215686%,62.352941%,93.72549%)}.D{fill:rgb(45.098039%,49.411765%,90.196078%)}]]></style><path d="M19.965 7.582c-.07-.234-.27-.387-.516-.4L12.94 6.75 10.527.68C10.445.47 10.223.363 10 .352c-.234 0-.47.105-.562.328L7.023 6.75l-6.47.422c-.246.023-.445.176-.527.4-.07.223 0 .48.188.633l4.98 4.168-1.617 6.328a.59.59 0 0 0 .223.609c.2.14.457.152.656.023L10 15.863l5.492 3.48c.21.13.47.117.668-.023.188-.14.28-.375.223-.61l-1.613-6.328 5.02-4.168a.57.57 0 0 0 .176-.633zm0 0" fill="rgb(100%,85.490196%,17.647059%)"/><path d="M19.79 8.215l-5.02 4.168 1.613 6.328c.06.234-.035.47-.223.61-.2.14-.457.152-.668.023L10 15.863V.352c.223.012.445.117.527.328l2.414 6.07 6.508.422c.246.023.445.176.516.4a.57.57 0 0 1-.176.633zm0 0" class="B"/><path d="M10.586 19.063a.58.58 0 0 1-.586.586.58.58 0 1 1 0-1.172.58.58 0 0 1 .586.586zm0 0" class="C"/><path d="M17.98 12.836a.59.59 0 0 0-.738.379c-.102.3.07.637.375.738s.64-.07.738-.38-.066-.637-.375-.738zm-2.816-9.082a.58.58 0 0 0-.816.129.58.58 0 0 0 .13.816.584.584 0 1 0 .688-.945zm0 0" class="D"/><path d="M5.652 3.883a.59.59 0 0 0-.82-.133.59.59 0 0 0-.13.82.59.59 0 0 0 .82.13.59.59 0 0 0 .13-.816zm-2.898 9.332a.58.58 0 0 0-.738-.375c-.308.102-.477.43-.375.738s.43.477.738.375.477-.43.375-.738zm0 0" class="C"/><path d="M10.586 19.063a.58.58 0 0 1-.586.586v-1.172a.58.58 0 0 1 .586.586zm0 0" class="D"/><path d="M13.95 9.54c-.07-.234-.28-.387-.516-.4L11.313 9l-.785-1.98c-.082-.21-.305-.316-.527-.328-.234-.012-.47.094-.562.328L8.652 9l-2.12.13c-.234.023-.445.176-.516.4-.082.223 0 .477.176.63l1.64 1.36-.527 2.05a.6.6 0 0 0 .223.621c.2.14.457.152.656.023L10 13.086l1.78 1.137c.2.13.457.117.656-.023a.6.6 0 0 0 .223-.621l-.527-2.05 1.63-1.36c.188-.152.258-.406.188-.63zm0 0" class="B"/><path d="M13.762 10.168l-1.63 1.36.527 2.05a.6.6 0 0 1-.223.621c-.2.14-.457.152-.656.023L10 13.086V6.69c.223.012.445.117.527.328L11.313 9l2.12.13c.234.023.445.176.516.4.07.223 0 .477-.187.63zm0 0" fill="rgb(100%,56.862745%,0%)"/></svg>')
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {

        $default = [
            'enable'           => 0,
            'post_types'       => [],
            'sticky_post_type' => []
        ];

        // Set class property
        $this->options = get_option('wp_featured_posts_settings', $default);
        ?>
        <div class="wrap">
            <h1><?php _e('Featured Posts Setting', 'wp-featured-posts'); ?></h1>
            <form method="post" action="options.php">
                <?php
                // This prints out all hidden setting fields
                settings_fields('wp_featured_posts_settings_group');
                do_settings_sections('wp-featured-posts-settings-page');
                submit_button();
                ?>
            </form>
        </div>
		<hr>
		<a href='https://ko-fi.com/J3J6HM43W' target='_blank'><img height='36' style='border:0px;height:36px;' src='<?php echo WPFP_PLUGIN_URL; ?>assets/images/kofi1.webp' border='0' alt='Buy Me a Coffee at ko-fi.com' /></a>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {
        register_setting(
            'wp_featured_posts_settings_group', // Option group
            'wp_featured_posts_settings', // Option name
            [$this, 'sanitize'] // Sanitize,
        );

        add_settings_section(
            'wp_featured_posts_settings_section', // ID
            '', // Title
            [$this, 'wp_featured_posts_settings_section'], // Callback
            'wp-featured-posts-settings-page' // Page
        );

        add_settings_field(
            'wp-featured-posts-enable', // ID
            __('Enable', 'wp-featured-posts'), // Title
            [$this, 'enable_field'], // Callback
            'wp-featured-posts-settings-page', // Page
            'wp_featured_posts_settings_section'
        );

        add_settings_field(
            'wp-featured-posts', // ID
            __('Post Types', 'wp-featured-posts'), // Title
            [$this, 'post_types_field'], // Callback
            'wp-featured-posts-settings-page', // Page
            'wp_featured_posts_settings_section'
        );

        add_settings_field(
            'wp-featured-posts-sticky', // ID
            __('Sticky Featured Posts', 'wp-featured-posts'), // Title
            [$this, 'sticky_post_type_field'], // Callback
            'wp-featured-posts-settings-page', // Page
            'wp_featured_posts_settings_section'
        );

        add_settings_section(
            'wp_featured_sticky_posts_settings_section', // ID
            '', // Title
            [$this, 'wp_featured_sticky_posts_settings_section'], // Callback
            'wp-featured-posts-settings-page' // Page
        );
    }

    /**
     * Sanitize POST data from custom settings form
     *
     * @param array $input Contains custom settings which are passed when saving the form
     * @return array
     */
    public function sanitize(array $input)
    {

        $sanitized_input = [
            'enable'           => 0,
            'post_types'       => [],
            'sticky_post_type' => [],
        ];

        $sanitized_input = array_merge($sanitized_input, $input);

        return $sanitized_input;
    }

    /**
     * Custom settings section text
     */
    public function wp_featured_posts_settings_section()
    {

    }

    public function enable_field()
    {
        echo '<label for="wp-featured-posts-enable"><input type="checkbox" id="wp-featured-posts-enable" name="wp_featured_posts_settings[enable]" value="1" ' . checked($this->options['enable'], 1, false) . ' > ' . __('Enable', 'wp-featured-posts') . '</label>';
    }

    public function post_types_field()
    {
        $post_types = $this->options['post_types'] ?? [];

        $args = [
            'public' => true,
        ];

        $get_post_types = get_post_types($args, 'objects');

        if ($get_post_types) {
            foreach ($get_post_types as $post_type) {
                if (!in_array($post_type->name, ['attachment'])) {
                    $is_checked = in_array($post_type->name, $post_types);
                    printf(
                        '<p><label for="wp-featured-posts-%s"><input name="wp_featured_posts_settings[post_types][]" type="checkbox" id="wp-featured-posts-%s" value="%s" %s> %s (%s)</label></p>',
                        $post_type->name, $post_type->name, $post_type->name, checked($is_checked, true, false), $post_type->label, $post_type->name
                    );
                }
            }
        }
    }

    public function sticky_post_type_field()
    {
        $post_types = $this->options['post_types'] ?? [];
        $sticky_post_type = $this->options['sticky_post_type'] ?? [];

        $args = [
            'public' => true,
        ];

        $get_post_types = get_post_types($args, 'objects');

        if ($get_post_types) {
            foreach ($get_post_types as $post_type) {
                if (!in_array($post_type->name, ['attachment']) && in_array($post_type->name, $post_types)) {
                    $is_checked = in_array($post_type->name, $sticky_post_type);
                    printf(
                        '<p><label for="wp-sticky-post-type-%s"><input name="wp_featured_posts_settings[sticky_post_type][]" type="checkbox" id="wp-sticky-post-type-%s" value="%s" %s> %s (%s)</label></p>',
                        $post_type->name, $post_type->name, $post_type->name, checked($is_checked, true, false), __('Enable Sticky for', 'wp-featured-posts') . ' ' . $post_type->label, $post_type->name
                    );
                }
            }
        }
    }


    public function wp_featured_sticky_posts_settings_section()
    {
        ?>
        <div class="how-to-query-featured-posts">
            <div class="title">
                <h3><?php _e('How to query featured posts', 'wp-featured-posts'); ?>
                    <a href="#" class="wpfp-toggle-expand" data-expand="#wpfp-code">
                        <small>
                            <span class="show"><?php _e('[Show]', 'wp-featured-posts'); ?></span>
                            <span class="hide" style="display: none;"><?php _e('[Hide]', 'wp-featured-posts'); ?></span>
                        </small>
                    </a>
                </h3>
            </div>
            <div id="wpfp-code" class="code" style="display: none;">
                <h5><?php _e('Example code for query featured posts', 'wp-featured-posts'); ?></h5>
<pre>
$args = [
    'post_type'        => 'post',
    'post_status'      => 'publish',
    'orderby'          => ['menu_order' => 'ASC', 'date' => 'DESC'],
    'meta_query'       => [
        [
            'key'   => 'post_featured', // {post_type_name}_featured example page_featured, news_featured etc..
            'value' => '1'
        ]
    ]
];
$posts = get_posts($args);
</pre>
            </div>
        </div>

        <?php
    }

}
