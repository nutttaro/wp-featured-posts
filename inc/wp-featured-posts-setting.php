<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

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
            wp_enqueue_media();
            wp_enqueue_script('admin-featured-sorting-setting', WPFP_PLUGIN_URL . '/assets/js/setting.min.js', ['jquery'], WPFP_VERSION, true);
            wp_enqueue_style('admin-featured-sorting-setting', WPFP_PLUGIN_URL . '/assets/css/settings.min.css', [], WPFP_VERSION);
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
            'sticky_post_type' => [],
            'pin_enable'       => 0,
            'pin_size'         => 16,
            'pin_image'        => '',
        ];

        // Set class property
        $this->options = get_option('wp_featured_posts_settings', $default);
        ?>
        <div class="wrap wpfp-settings-wrap">
            <h1><?php esc_html_e('Featured Posts Settings', 'wp-featured-posts'); ?></h1>
            <?php settings_errors(); ?>
            <form method="post" action="options.php">
                <?php settings_fields('wp_featured_posts_settings_group'); ?>

                <div class="wpfp-settings-grid">
                    <div class="wpfp-settings-main">

                        <div class="wpfp-card">
                            <div class="wpfp-card-header">
                                <h2><?php esc_html_e('General', 'wp-featured-posts'); ?></h2>
                            </div>
                            <div class="wpfp-card-body">
                                <table class="form-table" role="presentation">
                                    <?php $this->render_field(__('Enable', 'wp-featured-posts'), 'enable_field'); ?>
                                    <?php $this->render_field(__('Post Types', 'wp-featured-posts'), 'post_types_field'); ?>
                                </table>
                            </div>
                        </div>

                        <div class="wpfp-card">
                            <div class="wpfp-card-header">
                                <h2><?php esc_html_e('Sticky Behavior', 'wp-featured-posts'); ?></h2>
                                <p><?php esc_html_e('Pin featured posts to the top of archive pages, just like WordPress sticky posts.', 'wp-featured-posts'); ?></p>
                            </div>
                            <div class="wpfp-card-body">
                                <table class="form-table" role="presentation">
                                    <?php $this->render_field(__('Sticky Post Types', 'wp-featured-posts'), 'sticky_post_type_field'); ?>
                                </table>
                            </div>
                        </div>

                        <div class="wpfp-card">
                            <div class="wpfp-card-header">
                                <h2><?php esc_html_e('Pin Icon', 'wp-featured-posts'); ?></h2>
                                <p><?php esc_html_e('Display a small icon next to featured post titles on the frontend.', 'wp-featured-posts'); ?></p>
                            </div>
                            <div class="wpfp-card-body">
                                <table class="form-table" role="presentation">
                                    <?php $this->render_field(__('Enable Pin Icon', 'wp-featured-posts'), 'pin_enable_field'); ?>
                                    <?php $this->render_field(__('Icon Size', 'wp-featured-posts'), 'pin_size_field'); ?>
                                    <?php $this->render_field(__('Custom Image', 'wp-featured-posts'), 'pin_image_field'); ?>
                                </table>
                            </div>
                        </div>

                    </div>

                    <div class="wpfp-settings-sidebar">

                        <div class="wpfp-card">
                            <div class="wpfp-card-header">
                                <h2><?php esc_html_e('How to Use', 'wp-featured-posts'); ?></h2>
                            </div>
                            <div class="wpfp-card-body wpfp-how-to-use">
                                <h4><?php esc_html_e('Admin Panel', 'wp-featured-posts'); ?></h4>
                                <p><?php esc_html_e('Go to each enabled post type menu and click "Featured [Post Type]" to add, remove, and reorder featured posts.', 'wp-featured-posts'); ?></p>

                                <h4><?php esc_html_e('Block Editor', 'wp-featured-posts'); ?></h4>
                                <p><?php esc_html_e('Toggle "Mark as Featured" in the post editor sidebar panel.', 'wp-featured-posts'); ?></p>

                                <h4><?php esc_html_e('Shortcode', 'wp-featured-posts'); ?></h4>
                                <code>[featured_posts]</code>
                                <p class="wpfp-code-desc"><?php esc_html_e('Attributes:', 'wp-featured-posts'); ?> <code>post_type</code>, <code>limit</code></p>
                            </div>
                        </div>

                        <div class="wpfp-card">
                            <div class="wpfp-card-header">
                                <h2><?php esc_html_e('Query Example', 'wp-featured-posts'); ?></h2>
                            </div>
                            <div class="wpfp-card-body">
<pre class="wpfp-code-block">$args = [
  'post_type'  => 'post',
  'post_status'=> 'publish',
  'orderby'    => [
    'menu_order' => 'ASC',
    'date'       => 'DESC',
  ],
  'meta_query' => [[
    'key'   => 'post_featured',
    'value' => '1',
  ]],
];
$posts = get_posts($args);</pre>
                                <p class="wpfp-code-desc"><?php
                                    /* translators: %s: example meta key pattern */
                                    printf(esc_html__('Replace %s with your post type name.', 'wp-featured-posts'), '<code>post_featured</code>');
                                ?></p>
                            </div>
                        </div>

                    </div>
                </div>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render a single field row inside a form-table.
     *
     * @param string $label
     * @param string $method
     */
    private function render_field($label, $method)
    {
        echo '<tr>';
        echo '<th scope="row">' . esc_html($label) . '</th>';
        echo '<td>';
        $this->$method();
        echo '</td>';
        echo '</tr>';
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
            'pin_enable'       => 0,
            'pin_size'         => 16,
            'pin_image'        => '',
        ];

        $sanitized_input = array_merge($sanitized_input, $input);

        // Sanitize pin size
        if (isset($sanitized_input['pin_size'])) {
            $sanitized_input['pin_size'] = absint($sanitized_input['pin_size']);
            if ($sanitized_input['pin_size'] < 10) {
                $sanitized_input['pin_size'] = 10;
            }
            if ($sanitized_input['pin_size'] > 50) {
                $sanitized_input['pin_size'] = 50;
            }
        }

        // Sanitize pin image URL
        if (isset($sanitized_input['pin_image'])) {
            $sanitized_input['pin_image'] = esc_url_raw($sanitized_input['pin_image']);
        }

        return $sanitized_input;
    }

    public function enable_field()
    {
        echo '<label for="wp-featured-posts-enable"><input type="checkbox" id="wp-featured-posts-enable" name="wp_featured_posts_settings[enable]" value="1" ' . checked($this->options['enable'], 1, false) . '> ' . esc_html__('Enable featured posts functionality', 'wp-featured-posts') . '</label>';
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
                        '<p><label for="wp-featured-posts-%s"><input name="wp_featured_posts_settings[post_types][]" type="checkbox" id="wp-featured-posts-%s" value="%s" %s> %s</label></p>',
                        esc_attr($post_type->name), esc_attr($post_type->name), esc_attr($post_type->name), checked($is_checked, true, false), esc_html($post_type->label)
                    );
                }
            }
        }
        echo '<p class="description">' . esc_html__('Select which post types can have featured posts.', 'wp-featured-posts') . '</p>';
    }

    public function sticky_post_type_field()
    {
        $post_types = $this->options['post_types'] ?? [];
        $sticky_post_type = $this->options['sticky_post_type'] ?? [];

        $args = [
            'public' => true,
        ];

        $get_post_types = get_post_types($args, 'objects');

        $has_options = false;
        if ($get_post_types) {
            foreach ($get_post_types as $post_type) {
                if (!in_array($post_type->name, ['attachment']) && in_array($post_type->name, $post_types)) {
                    $has_options = true;
                    $is_checked = in_array($post_type->name, $sticky_post_type);
                    printf(
                        '<p><label for="wp-sticky-post-type-%s"><input name="wp_featured_posts_settings[sticky_post_type][]" type="checkbox" id="wp-sticky-post-type-%s" value="%s" %s> %s</label></p>',
                        esc_attr($post_type->name), esc_attr($post_type->name), esc_attr($post_type->name), checked($is_checked, true, false), esc_html($post_type->label)
                    );
                }
            }
        }

        if (!$has_options) {
            echo '<p class="description"><em>' . esc_html__('Enable post types above first.', 'wp-featured-posts') . '</em></p>';
        }
    }

    public function pin_enable_field()
    {
        $pin_enable = isset($this->options['pin_enable']) ? $this->options['pin_enable'] : 0;
        echo '<label for="wp-featured-posts-pin-enable"><input type="checkbox" id="wp-featured-posts-pin-enable" name="wp_featured_posts_settings[pin_enable]" value="1" ' . checked($pin_enable, 1, false) . '> ' . esc_html__('Show pin icon next to featured post titles', 'wp-featured-posts') . '</label>';
    }

    public function pin_size_field()
    {
        $pin_size = isset($this->options['pin_size']) ? $this->options['pin_size'] : 16;
        echo '<input type="number" id="wp-featured-posts-pin-size" name="wp_featured_posts_settings[pin_size]" value="' . esc_attr($pin_size) . '" min="10" max="50" step="1" style="width: 80px;"> <span class="description">' . esc_html__('px (10-50)', 'wp-featured-posts') . '</span>';
    }

    public function pin_image_field()
    {
        $pin_image = isset($this->options['pin_image']) ? $this->options['pin_image'] : '';
        ?>
        <div class="wpfp-pin-image-upload">
            <input type="hidden" id="wp-featured-posts-pin-image" name="wp_featured_posts_settings[pin_image]" value="<?php echo esc_attr($pin_image); ?>">
            <button type="button" class="button wpfp-upload-pin-image"><?php esc_html_e('Upload Image', 'wp-featured-posts'); ?></button>
            <button type="button" class="button wpfp-remove-pin-image" style="<?php echo empty($pin_image) ? 'display:none;' : ''; ?>"><?php esc_html_e('Remove', 'wp-featured-posts'); ?></button>
            <div class="wpfp-pin-preview" style="margin-top: 10px;<?php echo empty($pin_image) ? 'display:none;' : ''; ?>">
                <img src="<?php echo esc_url($pin_image); ?>" style="max-width: 100px; height: auto;">
            </div>
            <p class="description"><?php esc_html_e('Leave empty to use the default pin emoji. Recommended: 32x32px or larger.', 'wp-featured-posts'); ?></p>
        </div>
        <?php
    }
}
