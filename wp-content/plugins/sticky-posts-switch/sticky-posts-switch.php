<?php
/*
 * Plugin Name: Sticky Posts - Switch
 * Description: This plugin adds a sticky post switch functionality to the admin list post/custom post type pages.
 * Version:     1.5
 * Author:      Markus Froehlich
 * Author URI:  mailto:markusfroehlich01@gmail.com
 * Requires at least: 4.0
 * Tested up to: 4.8
 * Text Domain: sticky-posts-switch
 * Domain Path: /languages/
 * License:     GPL v2 or later
 */

/**
 * Sticky Posts - Switch
 *
 * LICENSE
 * This file is part of Sticky Posts Switch.
 *
 * Sticky Posts is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * @package    Sticky Posts - Switch
 * @author     Markus Fröhlich <markusfroehlich01@gmail.com>
 * @copyright  Copyright 2017 Markus Fröhlich
 * @license    http://www.gnu.org/licenses/gpl.txt GPL 2.0
 * @link       https://wordpress.org/plugins/sticky-posts/
 * @since      0.2
 */

if(!defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!class_exists('wp_sticky_posts_switch') )
{
    /*
     * Main class of the plugin
     */
    class wp_sticky_posts_switch
    {
        /*
         * Datafields
         */
        private $settings;

        /*
         *  Constructor
         */
        public function __construct()
        {
            $this->includes();

            add_action('plugins_loaded', array($this, 'load_plugin_textdomain'));

            // Init settings page
            $this->settings = new wp_sticky_posts_switch_settings(__FILE__);

            if(is_admin())
            {
                $post_types = $this->settings->get_post_types();

                if(count($post_types) > 0)
                {
                    foreach($post_types as $post_type)
                    {
                        add_filter('manage_'.$post_type.'_posts_columns', array($this, 'manage_post_columns'), 1);
                        add_action('manage_'.$post_type.'_posts_custom_column', array($this, 'manage_posts_custom_column'), 10, 2);
                    }

                    // Handle the ajax request
                    add_action('wp_ajax_process_sticky_post', array($this, 'process_ajax_sticky_post'));

                    add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));

                    // Add bulk and quick edit elements to CPT
                    add_action('quick_edit_custom_box', array($this, 'quick_edit_sticky_post'), 10, 2 );
                    add_action('bulk_edit_custom_box', array($this, 'bulk_edit_sticky_post'), 10, 2 );

                    add_action('admin_footer', array($this, 'add_sticky_post_checkbox'));
                }
            }

            // Change the retrieved posts from the custom post type, to put the sticky posts on the top
            if(!$this->settings->get_ignore_retrieved_posts()) {
                add_filter('the_posts', array($this, 'handle_retrieved_posts'), 1);
            }

            add_filter('plugin_action_links_'.plugin_basename(__FILE__),  array($this, 'add_plugin_action_links'));
        }

        /**
         * Include external classes
         */
        private function includes()
        {
            require_once dirname(__FILE__).'/settings/class-settings.php';
        }

        /*
         * Enqueue the post admin columns scripts
         */
        public function enqueue_scripts($hook_suffix)
        {
            // Enqueue only on edit.php
			if($hook_suffix == 'edit.php' && in_array(get_query_var('post_type'), $this->settings->get_post_types()))
            {
                $plugin_data = get_plugin_data( __FILE__ );

                wp_enqueue_style('sticky-posts-style', plugin_dir_url(__FILE__).'assets/css/admin-sticky-posts.css', array(), $plugin_data['Version']);
                wp_enqueue_script('jquery-ajax-queue',plugin_dir_url(__FILE__).'assets/jquery/jquery.ajaxQueue.min.js', array('jquery'), '0.1.2', true);
				wp_enqueue_script('stick-posts-admin', plugin_dir_url(__FILE__).'assets/js/admin-sticky-posts.js', array('jquery'), $plugin_data['Version'], true);
                wp_enqueue_script('stick-posts-admin-quick-edit', plugin_dir_url(__FILE__).'assets/js/admin-quick-edit.js', array('jquery'), $plugin_data['Version'], true);

                $l10n = array(
                    'ajaxUrl'   => admin_url('admin-ajax.php'),
                    'action'    => 'process_sticky_post'
                );

                wp_localize_script('stick-posts-admin', 'stickyPostObject', $l10n);
			}
		}

        /*
         * Initialize the textdomain
         */
        public function load_plugin_textdomain()
        {
            load_plugin_textdomain('sticky-posts-switch', false, dirname(plugin_basename(__FILE__) ) . '/languages' );
        }

        /**
         * Manage custom columns for posts.
         * @param  array $columns
         * @return array
         */
        public function manage_post_columns($columns)
        {
            if(get_query_var('post_status') === 'trash') {
                return $columns;
            }

            // Add sticky post column
            $columns['sticky_post'] = '<span class="dashicons dashicons-sticky"></span>';

            $sort_order = $this->settings->get_sort_order();

            $i = 0;
            foreach($sort_order as $column)
            {
                if(!in_array($column, array_keys($columns))) {
                    unset($sort_order[$i]);
                }

                $i++;
            }

            // Reset sort array
            $sort_order = array_values($sort_order);

            // Reorder columns
            $columns = array_merge(array_flip($sort_order), $columns);

			return $columns;
        }

        /**
         * Output custom columns for posts.
         * @param string $column
         */
        public function manage_posts_custom_column($column, $post_id)
        {
            switch ($column)
            {
                case 'sticky_post':
                    $hyperlink_class = 'sticky-posts';
                    $hyperlink_style = '';
                    $icon_class = 'dashicons-star-empty';

                    $icon_color = $this->settings->get_icon_color();

                    if(!empty($icon_color)) {
                        $hyperlink_style = 'style="color: '.$icon_color.';"';
                    }

                    if(is_sticky($post_id))
                    {
                        $hyperlink_class .= ' active';
                        $icon_class = 'dashicons-star-filled';
                    }

                    printf('<a id="%s" title="%s" class="%s" %s href="javascript:void(0);" data-id="%d" data-nonce="%s"><span class="dashicons %s"></span></a>',
                        'stiky-post-'.$post_id,
                        __('Sticky Post'),
                        $hyperlink_class,
                        $hyperlink_style,
                        $post_id,
                        wp_create_nonce('sticky-post-nonce'),
                        $icon_class
                    );

                    break;
            }
        }

        /*
         * Add the sticky post checkbox to the custom post type quick edit
         */
        public function quick_edit_sticky_post($column_name, $post_type)
        {
            if($post_type !== 'post' && in_array($post_type, $this->settings->get_post_types()))
            {
                $post_type_object = get_post_type_object($post_type);

                if(current_user_can($post_type_object->cap->publish_posts) && current_user_can($post_type_object->cap->edit_others_posts))
                {
                    switch ( $column_name ) {
                        case 'sticky_post':
                            ?>
                            <fieldset class="inline-edit-col-right">
                                <div class="inline-edit-col">
                                    <div class="inline-edit-group wp-clearfix">
                                        <label class="alignleft">
                                            <input type="checkbox" name="sticky" value="sticky" />
                                            <span class="checkbox-title"><?php _e( 'Make this post sticky' ); ?></span>
                                        </label>
                                    </div>
                                </div>
                            </fieldset>
                            <?php
                            break;
                    }
                }
            }
        }

        /*
         * Add the sticky post select to the custom post type bulk edit
         */
        public function bulk_edit_sticky_post($column_name, $post_type)
        {
            if($post_type !== 'post' && in_array($post_type, $this->settings->get_post_types()))
            {
                $post_type_object = get_post_type_object($post_type);

                if(current_user_can($post_type_object->cap->publish_posts) && current_user_can($post_type_object->cap->edit_others_posts))
                {
                    switch ( $column_name )
                    {
                        case 'sticky_post':
                            ?>
                            <fieldset class="inline-edit-col-right">
                                <div class="inline-edit-col">
                                    <div class="inline-edit-group wp-clearfix">
                                        <label class="alignleft">
                                            <span class="title"><?php _e( 'Sticky' ); ?></span>
                                            <select name="sticky">
                                                <option value="-1"><?php _e( '&mdash; No Change &mdash;' ); ?></option>
                                                <option value="sticky"><?php _e( 'Sticky' ); ?></option>
                                                <option value="unsticky"><?php _e( 'Not Sticky' ); ?></option>
                                            </select>
                                        </label>
                                    </div>
                                </div>
                            </fieldset>
                            <?php
                            break;
                    }
                }
            }
        }

        /*
         * Implements the sticky checkbox on custom post type
         */
        public function add_sticky_post_checkbox()
        {
            global $pagenow;

            if('post.php' == $pagenow && isset($_GET['post']) && isset($_GET['action']) && $_GET['action'] == 'edit')
            {
                $post_id = absint($_GET['post']);

                if(get_post_type($post_id) !== 'post' && in_array(get_post_type($post_id), $this->settings->get_post_types()) && current_user_can( 'edit_others_posts' ))
                {
                    $checked = is_sticky( $post_id ) ? ' checked="checked"' : '';

                    $checkbox = '<input id="sticky" name="sticky" type="checkbox" value="sticky"'.$checked.' />';
                    $label = '<label for="sticky" class="selectit">'.__( 'Stick this post to the front page' ).'</label>';

                    $content = sprintf('<span id="sticky-span">%s %s <br /></span>', $checkbox, $label);

                    /*
                     * Add the sticky post checkbox with javascript
                     */
                    ?>
                    <script type="text/javascript">
                        (function($) {
                            if($('#post-visibility-select').length && $('label[for="visibility-radio-public"]').length) {
                                $('#post-visibility-select > label[for="visibility-radio-public"]+br').after('<?php echo $content; ?>');
                            }
                        })(jQuery);
                    </script>

                    <?php
                }
            }
        }

        /*
         * Handle the ajax request and set/unset the post sticky
         */
        public function process_ajax_sticky_post()
        {
            // Nonce security check
            if(!check_ajax_referer('sticky-post-nonce')) {
                wp_send_json_error('Nonce security check failed!');
            }

            $handle = $_POST['handle'];
            $post_id = absint($_POST['post_id']);
            $post_obj = get_post($post_id);
            $post_type_object = get_post_type_object($post_obj->post_type);

            // Check capabilities
            if (!current_user_can( $post_type_object->cap->edit_others_posts ) || !current_user_can( $post_type_object->cap->publish_posts ) ) {
                wp_send_json_error(__('Sorry, you are not allowed to edit this item.'));
            }

            wp_set_post_lock( $post_id );

            // Sticky posts are not available on password or private posts
            $sticky_available = true;
            if(post_password_required($post_obj) || $post_obj->post_status == 'private')
            {
                unstick_post( $post_id );
                $sticky_available = false;
            }
            else
            {
                // Set all translations from the post
                if($this->settings->get_handle_multilingual_posts())
                {
                    // Set all linked posts from multilingualpress
                    if($this->settings->multilingualpress_is_active()) {
                        $this->set_linked_multilingualpress_posts($post_id, $handle);
                    }

                    // Set all linked posts from WPML
                    if($this->settings->wpml_is_active()) {
                        $this->set_linked_wpml_posts($post_id, $handle);
                    }
                }
                else
                {
                    $this->set_the_post_sticky($post_id, $handle);
                }
            }

            // Get all post states
            ob_start();
            _post_states(get_post($post_id));
            $post_states = ob_get_clean();

            // Ajax output response
            wp_send_json_success(array(
                'sticky'    => is_sticky($post_id),
                'states'    => $post_states,
                'available' => $sticky_available
            ));
        }

        /*
         * Set all linked posts from multilingualpress
         */
        private function set_linked_multilingualpress_posts($post_id, $handle)
        {
            if(!function_exists('mlp_get_linked_elements')) {
                return false;
            }

            $linked_posts = mlp_get_linked_elements($post_id);

            foreach ($linked_posts as $linked_blog => $linked_post)
            {
                switch_to_blog( $linked_blog );

                $this->set_the_post_sticky($linked_post, $handle);

                restore_current_blog();
            }

            return true;
        }

        /*
         * Set all linked posts from WPML
         */
        private function set_linked_wpml_posts($post_id, $handle)
        {
            global $sitepress;

            $post_type = get_post_type($post_id);

            $trid = $sitepress->get_element_trid($post_id, 'post_'.$post_type);
            $translations = $sitepress->get_element_translations($trid ,'post_'.$post_type);

            remove_filter('pre_option_sticky_posts', array($sitepress, 'option_sticky_posts'));

            foreach($translations as $translation)
            {
                $translation_post_id = absint($translation->element_id);

                $this->set_the_post_sticky($translation_post_id, $handle);
            }
        }

        private function stick_post($post_id) {
            // If found any another sticky post, removes it
            $stickies = get_option( 'sticky_posts' );
            if ( $stickies ) {
                $args = [
                    'post_type'           => 'post',
                    'post__in'            => $stickies,
                    'posts_per_page'      => -1
                ];
                $the_query = new WP_Query($args);
                if ( $the_query->have_posts() ) { 
                    while ( $the_query->have_posts() ) { 
                        $the_query->the_post();
                        
                        $this->unstick_post(get_the_ID());
                    }    
                    wp_reset_postdata();    
                }
            }
            stick_post($post_id);
        }

        private function unstick_post($post_id) {
            unstick_post($post_id);
        }

        /*
         * Set the current post as sticky
         */
        private function set_the_post_sticky($post_id, $handle)
        {
            switch($handle)
            {
                case 'sticky':
                    $this->stick_post($post_id);
                    break;
                case 'unsticky':
                    $this->unstick_post($post_id);
                    break;
            }
        }

        /**
         * Change the retrieved posts from the custom post type, to put the sticky posts on the top
         * Filters the array of retrieved posts after they’ve been fetched and internally processed.
         *
         * Based on wp-includes/class-wp-query:2942
         *
         * @return array List of posts.
         */
        public function handle_retrieved_posts($posts)
        {
            global $wp_query;

            // Check if the query var "post_type" is set and the post type is choosen by the user
            if(!isset($wp_query->query_vars['post_type']) || empty($wp_query->query_vars['post_type']) || !in_array($wp_query->query_vars['post_type'], $this->settings->get_post_types())) {
                return $posts;
            }

            if($wp_query->query_vars['post_type'] == 'post' && $wp_query->is_home()) {
                return $posts;
            }

            $page = 1;
            if ( empty($wp_query->query_vars['nopaging']) && !$wp_query->is_singular )
            {
                $page = absint($wp_query->query_vars['paged']);

                if (!$page) {
                    $page = 1;
                }
            }

            $sticky_posts = get_option('sticky_posts');
            if ($page <= 1 && is_array($sticky_posts) && !empty($sticky_posts) && !$wp_query->query_vars['ignore_sticky_posts'] )
            {
                $num_posts = count($posts);
                $sticky_offset = 0;
                // Loop over posts and relocate stickies to the front.
                for ( $i = 0; $i < $num_posts; $i++ )
                {
                    if ( in_array($posts[$i]->ID, $sticky_posts) )
                    {
                        $sticky_post = $posts[$i];
                        // Remove sticky from current position
                        array_splice($posts, $i, 1);
                        // Move to front, after other stickies
                        array_splice($posts, $sticky_offset, 0, array($sticky_post));
                        // Increment the sticky offset. The next sticky will be placed at this offset.
                        $sticky_offset++;
                        // Remove post from sticky posts array
                        $offset = array_search($sticky_post->ID, $sticky_posts);
                        unset( $sticky_posts[$offset] );
                    }
                }

                // If any posts have been excluded specifically, Ignore those that are sticky.
                if ( !empty($sticky_posts) && !empty($q['post__not_in']) ) {
                    $sticky_posts = array_diff($sticky_posts, $q['post__not_in']);
                }

                // Fetch sticky posts that weren't in the query results
                if ( !empty($sticky_posts) )
                {
                    $stickies = get_posts( array(
                        'post__in'      => $sticky_posts,
                        'post_type'     => $wp_query->query_vars['post_type'],
                        'post_status'   => 'publish',
                        'nopaging'      => true
                    ) );

                    foreach ( $stickies as $sticky_post ) {
                        array_splice( $posts, $sticky_offset, 0, array( $sticky_post ) );
                        $sticky_offset++;
                    }
                }
            }

            return $posts;
        }

        /*
         * Applied to the list of links to display on the plugins page
         */
        public function add_plugin_action_links($links)
        {
            $new_links[] = '<a href="'.admin_url('admin.php?page='.$this->settings->menu_slug) . '">'.__('Settings').'</a>';

            return array_merge($links, $new_links);
        }
    }

    new wp_sticky_posts_switch;
}