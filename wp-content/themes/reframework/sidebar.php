<?php $imagesPath = get_template_directory_uri(). '/assets/images/'; ?>
<div class="leftbar">

	<div class="menu_logo menu-visible">
		<i class="mdi mdi-menu"></i>
		<a href="/"><img id="menuIcon" class="logo" src="<?php echo $imagesPath . 'reframe-logo.svg' ?>" alt="Logo Reframe.Work"><a>
	</div>
<?php wp_nav_menu(); ?>
	<?php
    // Previous/next post navigation.
    if (is_single()) {
        $prevText = get_the_title(get_previous_post()->ID);
        $nextText = get_the_title(get_next_post()->ID);
        if ( Strlen($prevText)>15 ) {
            $prevClass="two-lines";
        }
        if ( Strlen($nextText)>15 ) {
            $nextClass="two-lines";
        }
        the_post_navigation(array(
            'next_text' => '<span class="'.$nextClass.' meta-nav meta-nav-left" aria-hidden="true">' . __('<i class="mdi mdi-arrow-left mdi-set btn-arrows"></i><span class="on-hover on-hover-left">'.get_the_title(get_next_post()->ID).'</span>', 'reframework') . '</span> ',
            'prev_text' => '<span class="'.$prevClass.' meta-nav meta-nav-right" aria-hidden="true">' . __('<i class="mdi mdi-arrow-right mdi-set btn-arrows"></i><span class="on-hover on-hover-right">'.get_the_title(get_previous_post()->ID).'</span>', 'reframework') . '</span> '
        ));
    }

    ?>
</div>
</div>

<?php call_background_image(true); ?>
<?php if (is_home()) : ?>
	<?php include('template-parts/home/home.php') ?>
<?php elseif(!is_404()): ?>
	<?php include('template-parts/page.php') ?>
<?php endif; ?>
</div>
<div>