<?php
/**
 * Template for displaying search forms in Twenty Seventeen
 *
 * @package WordPress
 * @subpackage Reframework
 * @since 1.0
 * @version 1.0
 */

?>

<?php $unique_id = esc_attr(uniqid('search-form-')); ?>

<form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
	<label for="<?php echo $unique_id; ?>">
		<span class="screen-reader-text"><?php echo _x('Search for:', 'label', 'reframework'); ?></span>
	</label>
	<input type="search" id="<?php echo $unique_id; ?>" class="search-field" placeholder="<?php echo esc_attr_x('Search &hellip;', 'placeholder', 'reframework'); ?>" value="<?php echo get_search_query(); ?>" name="s" />
	<button type="submit" class="search-submit"><?php echo reframework_get_svg(array( 'icon' => 'search' )); ?><span class="screen-reader-text"><?php echo _x('Search', 'submit button', 'reframework'); ?></span></button>
</form>
