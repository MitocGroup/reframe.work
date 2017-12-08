<?php
/*
 * The settings class from the plugin
 * Author: Markus Froehlich
 */
if(!defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!class_exists('wp_sticky_posts_switch_settings'))
{
    class wp_sticky_posts_switch_settings
    {
        /**
         * Datafields
         */
        private $options;
        private $option_name = 'sticky_posts_switch_options';
        private $group_name = 'sticky_post_settings_group';
        private $plugin_file;
        private $posts_columns;

        public $menu_slug = 'sticky-posts-switch';

        /*
         * Get all selected post types
         */
        public function get_post_types()
        {
            $post_types = array();

            if(isset($this->options['post_types']) && is_array($this->options['post_types'])){
                $post_types = $this->options['post_types'];
            }

            return $post_types;
        }

        /*
         * Get the icon color in hex code
         */
        public function get_icon_color()
        {
            $color = '';

            if(isset($this->options['icon_color']) && !empty($this->options['icon_color'])) {
                $color = $this->options['icon_color'];
            }

            return $color;
        }

        /*
         * Get the sort order
         */
        public function get_sort_order()
        {
            if(isset($this->options['sort_order']) && is_array($this->options['sort_order'])) {
                $sort_order = $this->options['sort_order'];
            } else {
                $sort_order = array_keys($this->posts_columns);
            }

            return $sort_order;
        }

        /*
         * Get the ignore retrieved posts checkbox value
         */
        public function get_ignore_retrieved_posts()
        {
            $ignore_retrieved_posts = false;

            if(isset($this->options['ignore_retrieved_posts']) && $this->options['ignore_retrieved_posts'] === 1) {
                $ignore_retrieved_posts = true;
            }

            return $ignore_retrieved_posts;
        }

        /*
         * Get the set multilingual posts checkbox value
         */
        public function get_handle_multilingual_posts()
        {
            $handle_multilingual_posts = false;

            if(isset($this->options['handle_multilingual_posts']) && $this->options['handle_multilingual_posts'] === 1) {
                $handle_multilingual_posts = true;
            }

            return $handle_multilingual_posts;
        }

        /*
         * Check if WPML is active
         */
        public function wpml_is_active()
        {
            return function_exists('icl_object_id');
        }

        /*
         * Check if MultilingualPress is active
         */
        public function multilingualpress_is_active()
        {
            $mlp_language_api = apply_filters( 'mlp_language_api', NULL );

            return is_a($mlp_language_api, 'Mlp_Language_Api_Interface');
        }

        /**
         *  Constructor
         */
        public function __construct($plugin_file)
        {
            $this->plugin_file = $plugin_file;
            $this->options = get_option($this->option_name);
            $this->posts_columns = array(
                'cb'            => array('<input type="checkbox" disabled>', true),
                'sticky_post'   => array(__('Sticky Post'), false),
                'title'         => array(__('Title'), true),
                'author'        => array(__('Author'), true),
                'categories'    => array(__('Categories'), true),
                'tags'          => array(__('Tags'), true),
                'comments'      => array(__('Comments'), true),
                'date'          => array(__('Date'), true)
            );

            register_activation_hook($this->plugin_file, array($this, 'set_default_settings'));

            add_action('admin_menu', array($this, 'add_settings_page'));
            add_action('admin_init', array($this, 'settings_init'));

            add_action('admin_enqueue_scripts', array($this, 'enqueue_setting_scripts'));
        }

        /*
         * Set the default settings on plugin activation
         */
        public function set_default_settings()
        {
            if(!$this->options)
            {
                // Allgemeine Settings
                $options['post_types'] = array('post');

                add_option($this->option_name, $options);
            }
        }

        /*
         * Enqueue the settings page scripts
         */
        function enqueue_setting_scripts( $hook_suffix )
        {
            if(is_admin() && $hook_suffix == 'settings_page_'.$this->menu_slug)
            {
                $plugin_data = get_plugin_data($this->plugin_file);

                // Add the color picker css file
                wp_enqueue_style('wp-color-picker');

                // Custom styles and scripts
                wp_enqueue_style('sticky-post-admin-settings', plugin_dir_url($this->plugin_file).'assets/css/admin-settings.css', array(), $plugin_data['Version']);
                wp_enqueue_script('sticky-post-admin-settings', plugin_dir_url($this->plugin_file).'assets/js/admin-settings.js', array('jquery', 'jquery-ui-sortable', 'wp-color-picker'), $plugin_data['Version']);
            }
        }

        /**
         * Add submenu to the options page
         */
        public function add_settings_page()
        {
            add_submenu_page(
                'options-general.php',
                __('Sticky Posts - Switch', 'sticky-posts-switch'),
                __('Sticky Posts - Switch', 'sticky-posts-switch'),
                'manage_options',
                $this->menu_slug,
                array($this, 'create_settings_page')
            );
        }

        /**
         * Options page callback
         */
        public function create_settings_page()
        {
            ?>
            <div class="wrap">
                <h1><?php echo __('Sticky Post Settings', 'sticky-posts-switch'); ?></h1>
                <form method="post" action="options.php">
                <?php
                    // This prints out all hidden setting fields
                    settings_fields($this->group_name);
                    do_settings_sections($this->group_name);
                    submit_button();
                ?>
                </form>
            </div>
            <?php
        }

        /**
         * Register and add settings
         */
        public function settings_init()
        {
            register_setting(
                $this->group_name,
                $this->option_name,
                array($this, 'sanitize_fields')
            );

            /*
             * General settings
             */
            add_settings_section(
                'display_setting_section',
                __('Display Options'),
                array($this, 'print_display_section_info'),
                $this->group_name
            );

            add_settings_field(
                'post_types',
                __('Post Types', 'sticky-posts-switch'),
                array($this, 'render_post_type_checkboxes'),
                $this->group_name,
                'display_setting_section'
            );

            add_settings_field(
                'icon_color',
                '<span class="dashicons dashicons-star-empty"></span> '.__('Color'),
                array($this, 'render_color_input_field'),
                $this->group_name,
                'display_setting_section'
            );

            add_settings_field(
                'sort_order',
                __('Columns').' '.__('Order'),
                array($this, 'render_sort_order_field'),
                $this->group_name,
                'display_setting_section'
            );

            /*
             * Custom post types settings
             */
            add_settings_section(
                'cpt_setting_section',
                __('Custom Post Types', 'sticky-posts-switch'),
                array($this, 'print_cpt_section_info'),
                $this->group_name
            );

            add_settings_field(
                'ignore_retrieved_posts',
                __('Posts selection', 'sticky-posts-switch'),
                array($this, 'render_checkbox_settings_field'),
                $this->group_name,
                'cpt_setting_section',
                array(
                    'id'    => 'ignore_retrieved_posts',
                    'label' => __('Dont change the order from the custom post types in the frontend.', 'sticky-posts-switch'),
                    'desc'  => __('Sticky posts is a WordPress feature only for posts, by default this plugin also prepare custom post types, to put the posts on the top.', 'sticky-posts-switch')
                )
            );

            /*
             * Multilingual settings
             */
            add_settings_section(
                'multilingual_setting_section',
                __('Multilingual settings', 'sticky-posts-switch'),
                array($this, 'print_multilingual_section_info'),
                $this->group_name
            );

            // Check if any multilingual plugin is active
            if($this->wpml_is_active() || $this->multilingualpress_is_active())
            {
                add_settings_field(
                    'handle_multilingual_posts',
                    __('Multilingual posts', 'sticky-posts-switch'),
                    array($this, 'render_checkbox_settings_field'),
                    $this->group_name,
                    'multilingual_setting_section',
                    array(
                        'id'    => 'handle_multilingual_posts',
                        'label' => __('Set all the translations sticky, when one post is set.', 'sticky-posts-switch')
                    )
                );
            }
        }

        /**
         * Print the Section text
         */
        public function print_display_section_info() {
            _e('Change the display options for the admin sticky post column.', 'sticky-posts-switch');
        }

        public function print_cpt_section_info() {
            _e('Handle the post selection of custom post types.', 'sticky-posts-switch');
        }

        /**
         * Print the Section text
         */
        public function print_multilingual_section_info()
        {
            if($this->wpml_is_active() || $this->multilingualpress_is_active())
            {
                $active_ml_plugins = array();

                if($this->wpml_is_active()) {
                    $active_ml_plugins[] = '<a href="https://wpml.org" target="_blank">WPML</a>';
                }

                if($this->multilingualpress_is_active()) {
                    $active_ml_plugins[] = '<a href="https://wordpress.org/plugins/multilingual-press" target="_blank">MultilingualPress</a>';
                }

                printf(__('Active multilingual plugin found: %s', 'sticky-posts-switch'), implode(',', $active_ml_plugins));
            }
            else
            {
                _e('There are no supported multilingual plugins active.', 'sticky-posts-switch');

                echo '<br>';

                printf(
                    __('Supported Plugins: %s, %s', 'sticky-posts-switch'),
                    '<a href="https://wpml.org" target="_blank">WPML</a>',
                    '<a href="https://wordpress.org/plugins/multilingual-press" target="_blank">MultilingualPress</a>'
                );
            }
        }

        /**
         * Get the options option array and print one of its values
         */
        public function render_post_type_checkboxes()
        {
            $post_types_objects = get_post_types(array('public' => true), 'objects');

            foreach($post_types_objects as $post_type)
            {
                $exclude = array('page', 'attachment');

                if(in_array($post_type->name, $exclude)) {
                    continue;
                }

                $checked = '';
                if($this->options && is_array($this->options['post_types']) && in_array($post_type->name, $this->options['post_types'])) {
                    $checked = ' checked=checked';
                }

                printf('<label style="display: block"><input type="checkbox" name="%s" value="%s" %s>%s</label>',
                    $this->option_name.'[post_types][]',
                    $post_type->name,
                    $checked,
                    $post_type->label
                );
            }
        }

        /*
         * Render the color picker
         */
        public function render_color_input_field()
        {
            $color = (isset( $this->options['icon_color'] ) ) ? $this->options['icon_color'] : '';

            printf('<input type="text" name="'.$this->option_name.'[icon_color]" value="%s" class="color-picker" >', $color);
        }

        /*
         * Renders the sort order fields
         */
        public function render_sort_order_field()
        {
            $posts_columns = $this->posts_columns;

            // Get Option and transform it to array
            $sort_order = (isset($this->options['sort_order']) && is_array($this->options['sort_order'])) ? $this->options['sort_order'] : array();

            // Reorder the columns
            if(count($sort_order) > 0) {
                $posts_columns = array_merge(array_flip($sort_order), $this->posts_columns);
            }

            echo '<input id="sticky-post-sort-order" name="'.$this->option_name.'[sort_order]" type="hidden" value="'.implode(',', array_keys($posts_columns)).'">';

            echo '<ul id="sticky-post-sortable" class="ui-sortable">';
            foreach($posts_columns as $key => $column)
            {
                $disabled = $column[1] ? ' ui-state-disabled' : '';

                echo '<li id="'.$key.'" class="ui-state-default'.$disabled.'"><span class="dashicons dashicons-sort"></span> '.$column[0].'</li>';
            }
            echo '</ul>';
        }

        /*
         * Render the checkbox settings field
         */
        public function render_checkbox_settings_field($args)
        {
            if(isset($args['id']) && !empty($args['id']))
            {
                $default_value = (isset($this->options[$args['id']]) && $this->options[$args['id']] === 1) ? true : false;

                // Create checkbox
                printf('<label><input type="checkbox" name="%s" value="1" %s> %s</label>',
                    $this->option_name.'['.$args['id'].']',
                    checked($default_value, true, false),
                    $args['label']
                );

                // Print description
                if(isset($args['desc']) && !empty($args['desc'])) {
                    printf('<p class="description">%s</p>', $args['desc']);
                }
            }
        }

        /**
         * Sanitize each setting field
         *
         * @param array $input
         */
        public function sanitize_fields($input)
        {
            $new_input = array();

            // Post Types setting field
            if(isset($input['post_types']) && is_array($input['post_types']) && count($input['post_types']) > 0) {
                $new_input['post_types'] = $input['post_types'];
            }

            // Icon Color setting field
            if(isset($input['icon_color']) && preg_match('/^#[a-f0-9]{6}$/i', $input['icon_color'])) {
                $new_input['icon_color'] = $input['icon_color'];
            }

            // Sort Order setting field
            if(isset($input['sort_order'])) {
                $new_input['sort_order'] = explode(',', $input['sort_order']);
            }

            // Ignore retrieved posts checkbox
            if(isset($input['ignore_retrieved_posts']) && ctype_digit($input['ignore_retrieved_posts']) && $input['ignore_retrieved_posts'] == 1) {
                $new_input['ignore_retrieved_posts'] = 1;
            }

            // Handle multilingual posts checkbox
            if(isset($input['handle_multilingual_posts']) && ctype_digit($input['handle_multilingual_posts']) && $input['handle_multilingual_posts'] == 1) {
                $new_input['handle_multilingual_posts'] = 1;
            }

            return $new_input;
        }
    }
}