<?php
error_reporting(0); // disable errors
/**
 * Reframe.work functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package WordPress
 * @subpackage Reframework
 * @since 1.0
 */

/**
 * Reframe.work only works in WordPress 4.7 or later.
 */
if (version_compare($GLOBALS['wp_version'], '4.7-alpha', '<')) {
    require get_template_directory() . '/inc/back-compat.php';
    return;
}
add_theme_support('post-thumbnails', array( 'post', 'page' ));

add_filter( 'content_save_pre', 'remove_buggy_nbsps', 99 );

function remove_buggy_nbsps( $content ) {

   return str_replace( '\xc2\xa0', ' ', $content); 

}

function get_slider_image_count() {
    $defaultCount = 10;
    $count = intval(get_option( 'carousel_image_count' ));
    if ( $count < 1 ) {
        return 0;
    }
    return $count;
}

function get_slider_categories() {
    return get_option( 'carousel_category' );
}

/**
* register the setting on the media settings page
*/
function media_setting() {
    register_setting(
    'media', // settings page
    'carousel_image_count', // option name
    'carouselIC_validation' ); // validation callback
    register_setting(
    'media', // settings page
    'carousel_category', // option name
    'carouselIP_validation' ); // validation callback
    add_settings_field(
    'CarouselIC', // id
    'Homepage Carousel', // setting title
    'render_carousel_panel', // display callback
    'media', // settings page
    'default' // settings section
    );
}
add_action( 'admin_init', 'media_setting');

// Validate user input
function carouselIC_validation( $input ) {
    if ( intval( $input) ) {
        return $input;
    }
    return false;
}

// Validate user input
function carouselIP_validation( $input ) {
    if ( !empty( $input) ) {
        return $input;
    }

    return false;
}

/**
* display the buttons and a preview
*/
function render_carousel_panel() {
    $value = get_slider_image_count();
    ?>
    <h4>Categories to show:</h4>
    <ul>
    <?php
    $carousel_category = get_slider_categories();
    $categories = get_categories( array(
        'orderby' => 'name',
        'order'   => 'ASC'
    ) );
    $categories_content = '';
    foreach ($categories as $category) {
        if (!empty($carousel_category)) {
            if (is_array($carousel_category) && in_array($category->term_id, $carousel_category)) { $checked = 'checked="checked"'; }
            else { $checked = ''; }
        } else { $checked = ''; }
        $categories_content .= '<li id="category-' . $category->term_id . '"><label class="selectit"><input type="checkbox" id="in-category-' . $category->term_id . '" name="carousel_category[]" value="' . $category->term_id . '" '.$checked.'> ' . $category->name . '</label></li>' . "\n";
    }
    echo $categories_content;
    ?>
    </ul>
    <label for="CarouselIC">Count to show: </label>
    <input id="CarouselIC" type="number" value="<?php echo esc_attr( $value ); ?>" name="carousel_image_count"/>
    <?php
}

function body_styles() {
    $imagesPath = get_template_directory_uri(). '/assets/images/';
    $styles = [];

    if(is_404()) {
        $styles[] = 'background-image:url('.$imagesPath.'not-found.jpg)';
        $styles[] = 'background-size:cover';
        $styles[] = 'background-repeat:no-repeat';
        $styles[] = 'height:100vh';
        $styles[] = 'background-position:50% 50%';
    }

    echo ' style="'.join(';', $styles).'"';
}

function reframeWorkScripts_OnLoad()
{
    wp_enqueue_script('slider-jquery', get_theme_file_uri('/assets/js/jquery.flipster.min.js'), array( 'jquery' ), '1.0', true);
}
// Add hook for admin <head></head>
add_action('admin_head', 'reframeWorkScripts_OnLoad');
// Add hook for front-end <head></head>
add_action('wp_head', 'reframeWorkScripts_OnLoad');

add_filter('nav_menu_css_class', 'special_nav_class', 10, 2);
function special_nav_class($classes, $item)
{
    if (in_array('current-menu-item', $classes)) {
        $classes[] = 'active ';
    }
    return $classes;
}

function reframe_include_style($styleName, $stylePath)
{
    wp_enqueue_style($styleName, $stylePath, array(), null, 'all');
    wp_enqueue_style($styleName);
}

function reframework_stylesheets()
{
    reframe_include_style('flipster-style', get_template_directory_uri().'/assets/css/jquery.flipster.min.css');
    reframe_include_style('footer-style', get_template_directory_uri().'/template-parts/footer/style.css');
    reframe_include_style('responsive-style', get_template_directory_uri().'/assets/css/responsive.css');
}
add_action('wp_enqueue_scripts', 'reframework_stylesheets');

function getRightFooter($INCLUDE_SOCIAL = true)
{
    include 'template-parts/footer/head.php';
    if ($INCLUDE_SOCIAL) {
        include 'template-parts/footer/social.php';
    }
    include 'template-parts/footer/bottom.php';
}

function get_featured_image()
{
    $imageUrl = get_the_post_thumbnail_url();
    if (empty($imageUrl) || is_home()) {
        if (wp_get_attachment_image_src(get_option('dfi_image_id'),'full')[0]) {
            $imageUrl = wp_get_attachment_image_src(get_option('dfi_image_id'),'full')[0];
        } else {
            $imageUrl = get_template_directory_uri()."/assets/images/default-bg.jpg";
        }
    }

    return $imageUrl;
}

function _renderBackground( $isRight = false, $haveLink = false )
{
    $onClick = "";
    $onClickClass = "";
    if ( $haveLink ) {
        $onClick='action="'.get_the_permalink().'"';
        $onClickClass = 'clickable';
    }

    return $isRight
        ? '<div class="bRight" style="filter:blur(25px);background: url('.get_featured_image(). ') repeat-x scroll -50vw top; background-size: cover;"></div>'
        : '<div '.$onClick.' class="bLeft '.$onClickClass.'" style="background: transparent url('.get_featured_image().') no-repeat scroll center center; background-size: cover;">';
}

function call_background_image($isRight = false)
{
    if(is_404()) {
        return;
    }
    $haveSticky = doActionWithStickyPost(_renderBackground);
    if (false === $haveSticky || $isRight) {
        $haveSticky = _renderBackground($isRight);
    }
    
    echo $haveSticky;
}

function _renderStickyLabels()
{
    return '
		<div class="stickyPostLabels">
			<h1><a class="stickyPostTitle" href="'.get_the_permalink().'" target="_self">'.get_the_title().'</a></h1>
			<h6><a class="stickyPostDate" href="'.get_the_permalink().'" target="_self">'.get_the_date("H:i d:m:Y").'</a></h6>
		</div>
		';
}

function call_sticky_labels()
{
    $haveSticky = '';
    if (is_page('stories')) {
        $haveSticky = doActionWithStickyPost(_renderStickyLabels);
    }
    
    echo $haveSticky;
}

function doActionWithStickyPost($myFunc=null)
{
    if (!is_page('stories') || is_home() || !$myFunc) {
        return false;
    }

    $stickies = get_option('sticky_posts');
    if ($stickies) {
        $args = [
            'post_type'                => 'post',
            'post__in'                => $stickies,
            'ignore_sticky_posts'    => 1,
            'orderby'                => 'date',
            'posts_per_page'        => 1
        ];
        $the_query = new WP_Query($args);
        if ($the_query->have_posts()) {
            while ($the_query->have_posts()) {
                $the_query->the_post();

                if ($myFunc==='_renderBackground') {
                    return $myFunc(false, true);
                }

                return $myFunc();
            }
            wp_reset_postdata();
        }
    }

    return false;
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function reframework_setup()
{
    /*
     * Make theme available for translation.
     * Translations can be filed at WordPress.org. See: https://translate.wordpress.org/projects/wp-themes/reframework
     * If you're building a theme based on Twenty Seventeen, use a find and replace
     * to change 'reframework' to the name of your theme in all the template files.
     */
    load_theme_textdomain('reframework');

    // Add default posts and comments RSS feed links to head.
    add_theme_support('automatic-feed-links');

    /*
     * Let WordPress manage the document title.
     * By adding theme support, we declare that this theme does not use a
     * hard-coded <title> tag in the document head, and expect WordPress to
     * provide it for us.
     */
    add_theme_support('title-tag');

    /*
     * Enable support for Post Thumbnails on posts and pages.
     *
     * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
     */
    add_theme_support('post-thumbnails');

    add_image_size('reframework-featured-image', 2000, 1200, true);

    add_image_size('reframework-thumbnail-avatar', 100, 100, true);

    // Set the default content width.
    $GLOBALS['content_width'] = 525;

    /*
     * Switch default core markup for search form, comment form, and comments
     * to output valid HTML5.
     */
    add_theme_support('html5', array(
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ));

    // Add theme support for Custom Logo.
    add_theme_support('custom-logo', array(
        'width'       => 250,
        'height'      => 250,
        'flex-width'  => true,
    ));

    // Add theme support for selective refresh for widgets.
    add_theme_support('customize-selective-refresh-widgets');

    /*
     * This theme styles the visual editor to resemble the theme style,
     * specifically font, colors, and column width.
     */
    add_editor_style(array( 'assets/css/editor-style.css', reframework_fonts_url() ));

    /**
     * Filters Reframe.work array of starter content.
     *
     * @since Reframe.work 1.1
     *
     * @param array $starter_content Array of starter content.
     */
    $starter_content = apply_filters('reframework_starter_content', '');

    add_theme_support('starter-content', $starter_content);
}
add_action('after_setup_theme', 'reframework_setup');

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function reframework_content_width()
{
    $content_width = $GLOBALS['content_width'];

    // Get layout.
    $page_layout = get_theme_mod('page_layout');

    // Check if layout is one column.
    if ('one-column' === $page_layout) {
        if (reframework_is_frontpage()) {
            $content_width = 644;
        } elseif (is_page()) {
            $content_width = 740;
        }
    }

    // Check if is single post and there is no sidebar.
    if (is_single() && ! is_active_sidebar('sidebar-1')) {
        $content_width = 740;
    }

    /**
     * Filter Reframe.work content width of the theme.
     *
     * @since Reframe.work 0.1
     *
     * @param int $content_width Content width in pixels.
     */
    $GLOBALS['content_width'] = apply_filters('reframework_content_width', $content_width);
}
add_action('template_redirect', 'reframework_content_width', 0);

/**
 * Register custom fonts.
 */
function reframework_fonts_url()
{
    $fonts_url = '';

    /*
     * Translators: If there are characters in your language that are not
     * supported by Libre Franklin, translate this to 'off'. Do not translate
     * into your own language.
     */
    $libre_franklin = _x('on', 'Libre Franklin font: on or off', 'reframework');

    if ('off' !== $libre_franklin) {
        $font_families = array();

        $font_families[] = 'Libre Franklin:300,300i,400,400i,600,600i,800,800i';

        $query_args = array(
            'family' => urlencode(implode('|', $font_families)),
            'subset' => urlencode('latin,latin-ext'),
        );

        $fonts_url = add_query_arg($query_args, 'https://fonts.googleapis.com/css');
    }

    return esc_url_raw($fonts_url);
}

/**
 * Add preconnect for Google Fonts.
 *
 * @since Reframe.work 0.1
 *
 * @param array  $urls           URLs to print for resource hints.
 * @param string $relation_type  The relation type the URLs are printed.
 * @return array $urls           URLs to print for resource hints.
 */
function reframework_resource_hints($urls, $relation_type)
{
    if (wp_style_is('reframework-fonts', 'queue') && 'preconnect' === $relation_type) {
        $urls[] = array(
            'href' => 'https://fonts.gstatic.com',
            'crossorigin',
        );
    }

    return $urls;
}
add_filter('wp_resource_hints', 'reframework_resource_hints', 10, 2);

function reframework_render_post_content() 
{
    $query = get_post(get_the_ID());
    $content = apply_filters('the_content', $query->post_content);
    echo $content;
}
function get_index_structure()
{
    ?>
    <?php if (is_home() && ! is_front_page()) : ?>
		<header class="page-header">
			<h1 class="page-title"><?php single_post_title(); ?></h1>
		</header>
	<?php else : ?>
	<header class="page-header">
	</header>
	<?php endif; ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">
		</main><!-- #main -->
	</div><!-- #primary -->
    <?
}

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function reframework_widgets_init()
{
    register_sidebar(array(
        'name'          => __('Right Side', 'reframework'),
        'id'            => 'right-side',
        'description'   => __('Right side of page.', 'reframework'),
        'before_widget' => '<div id="%1$s" class="widget %1$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ));

    register_sidebar(array(
        'name'          => __('Left Side', 'reframework'),
        'id'            => 'left-side',
        'description'   => __('Left side of page.', 'reframework'),
        'before_widget' => '<div id="%1$s" class="widget %1$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ));
}
add_action('widgets_init', 'reframework_widgets_init');

/**
 * Handles JavaScript detection.
 *
 * Adds a `js` class to the root `<html>` element when JavaScript is detected.
 *
 * @since Reframe.work 0.1
 */
function reframework_javascript_detection()
{
    echo "<script>(function(html){html.className = html.className.replace(/\bno-js\b/,'js')})(document.documentElement);</script>\n";
}
add_action('wp_head', 'reframework_javascript_detection', 0);

/**
 * Add a pingback url auto-discovery header for singularly identifiable articles.
 */
function reframework_pingback_header()
{
    if (is_singular() && pings_open()) {
        printf('<link rel="pingback" href="%s">' . "\n", get_bloginfo('pingback_url'));
    }
}
add_action('wp_head', 'reframework_pingback_header');

/**
 * Enqueue scripts and styles.
 */
function reframework_scripts()
{
    // Add custom fonts, used in the main stylesheet.
    wp_enqueue_style('reframework-fonts', reframework_fonts_url(), array(), null);

    // Theme stylesheet.
    wp_enqueue_style('reframework-style', get_stylesheet_uri());

    // Load the Internet Explorer 9 specific stylesheet, to fix display issues in the Customizer.
    if (is_customize_preview()) {
        wp_enqueue_style('reframework-ie9', get_theme_file_uri('/assets/css/ie9.css'), array( 'reframework-style' ), '1.0');
        wp_style_add_data('reframework-ie9', 'conditional', 'IE 9');
    }

    // Load the Internet Explorer 8 specific stylesheet.
    wp_enqueue_style('reframework-ie8', get_theme_file_uri('/assets/css/ie8.css'), array( 'reframework-style' ), '1.0');
    wp_style_add_data('reframework-ie8', 'conditional', 'lt IE 9');

    // Load the html5 shiv.
    wp_enqueue_script('html5', get_theme_file_uri('/assets/js/html5.js'), array(), '3.7.3');
    wp_script_add_data('html5', 'conditional', 'lt IE 9');

    wp_enqueue_script('reframework-skip-link-focus-fix', get_theme_file_uri('/assets/js/skip-link-focus-fix.js'), array(), '1.0', true);

    $reframework_l10n = array(
        'quote'          => reframework_get_svg(array( 'icon' => 'quote-right' )),
    );

    wp_enqueue_script('reframework-navigation', get_theme_file_uri('/assets/js/navigation.js'), array( 'jquery' ), '1.0', true);
    $reframework_l10n['expand']         = __('Expand child menu', 'reframework');
    $reframework_l10n['collapse']       = __('Collapse child menu', 'reframework');
    $reframework_l10n['icon']           = reframework_get_svg(array( 'icon' => 'angle-down', 'fallback' => true ));

    wp_enqueue_script('reframework-global', get_theme_file_uri('/assets/js/global.js'), array( 'jquery' ), '1.0', true);

    wp_localize_script('reframework-skip-link-focus-fix', 'reframeworkScreenReaderText', $reframework_l10n);

    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
}
add_action('wp_enqueue_scripts', 'reframework_scripts');

/**
 * Add custom image sizes attribute to enhance responsive image functionality
 * for content images.
 *
 * @since Reframe.work 0.1
 *
 * @param string $sizes A source size value for use in a 'sizes' attribute.
 * @param array  $size  Image size. Accepts an array of width and height
 *                      values in pixels (in that order).
 * @return string A source size value for use in a content image 'sizes' attribute.
 */
function reframework_content_image_sizes_attr($sizes, $size)
{
    $width = $size[0];

    if (740 <= $width) {
        $sizes = '(max-width: 706px) 89vw, (max-width: 767px) 82vw, 740px';
    }

    if (is_active_sidebar('sidebar-1') || is_archive() || is_search() || is_home() || is_page()) {
        if (! (is_page() && 'one-column' === get_theme_mod('page_options')) && 767 <= $width) {
            $sizes = '(max-width: 767px) 89vw, (max-width: 1000px) 54vw, (max-width: 1071px) 543px, 580px';
        }
    }

    return $sizes;
}
add_filter('wp_calculate_image_sizes', 'reframework_content_image_sizes_attr', 10, 2);

/**
 * Add custom image sizes attribute to enhance responsive image functionality
 * for post thumbnails.
 *
 * @since Reframe.work 0.1
 *
 * @param array $attr       Attributes for the image markup.
 * @param int   $attachment Image attachment ID.
 * @param array $size       Registered image size or flat array of height and width dimensions.
 * @return string A source size value for use in a post thumbnail 'sizes' attribute.
 */
function reframework_post_thumbnail_sizes_attr($attr, $attachment, $size)
{
    if (is_archive() || is_search() || is_home()) {
        $attr['sizes'] = '(max-width: 767px) 89vw, (max-width: 1000px) 54vw, (max-width: 1071px) 543px, 580px';
    } else {
        $attr['sizes'] = '100vw';
    }

    return $attr;
}
add_filter('wp_get_attachment_image_attributes', 'reframework_post_thumbnail_sizes_attr', 10, 3);

/**
 * Use front-page.php when Front page displays is set to a static page.
 *
 * @since Reframe.work 0.1
 *
 * @param string $template front-page.php.
 *
 * @return string The template to be used: blank if is_home() is true (defaults to index.php), else $template.
 */
function reframework_front_page_template($template)
{
    return is_home() ? '' : $template;
}
add_filter('frontpage_template', 'reframework_front_page_template');

add_action('get_header', 'remove_admin_login_header');
function remove_admin_login_header()
{
    remove_action('wp_head', '_admin_bar_bump_cb');
}

/**
 * Implement the Custom Header feature.
 */
require get_parent_theme_file_path('/inc/custom-header.php');

/**
 * Custom template tags for this theme.
 */
require get_parent_theme_file_path('/inc/template-tags.php');

/**
 * Additional features to allow styling of the templates.
 */
require get_parent_theme_file_path('/inc/template-functions.php');

/**
 * Customizer additions.
 */
require get_parent_theme_file_path('/inc/customizer.php');

/**
 * SVG icons functions and filters.
 */
require get_parent_theme_file_path('/inc/icon-functions.php');
