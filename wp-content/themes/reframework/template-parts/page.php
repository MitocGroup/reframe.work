<?php if(!is_page('stories') && !is_page('events')): ?>

<div class="effect-class effect-class-page">
    <img src="<?php echo $imagesPath . 'effect.png' ?>">
</div>
<div class="right-side display-flex-column">
<?php else: ?>
    <div class="right-side">
<?php endif; ?>

    <button id="gotoTop" title="Go to top" class="flipster_button" style="display: none"><i class="mdi mdi-arrow-up"></i></button>
    <?php if (!is_page('stories') && !is_page('events')) : ?>
        <div class="pageContainer right-section padding-content">
            <h1 class="title"><?php the_title(); ?></h1>
    <?php endif; ?>
    <?php wp_reset_query(); ?>
        <?php if (is_single()) : ?>
            <?php
            // POST CONTENT
            ?>
            <div class="data">
                <?php include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); ?>
                <?php
                if(is_plugin_active('Facebook Events Importer')){
                    $eventUri = get_fbe_field('fb_event_uri');
                }
                if ( $eventUri ) {
                    $eventStart = get_fbe_date('event_starts', 'M d, H:m A');
                    $eventEnd = get_fbe_date('event_ends', 'M d, H:m A');
                    $date = $eventStart . ' - ' . $eventEnd;
                } else {
                    $date = get_the_date('M d, H:m A');
                }
                echo $date;
                ?>
            </div>
            <?php
                if ( $eventUri ) {
                    $eventLocation = get_fbe_field('location');
                    echo '
                    <div>
                        <a class="location-prefix">Location:</a>
                        <a class="location-place">'.$eventLocation.'</a>
                    </div>';
                    echo '<a href="'.$eventUri.'" class="btn">Go to facebook</a>';
                }
            ?>
            <div class="content">
                <?php reframework_render_post_content(); ?>
            </div>
        <?php else : ?>
            <?php
            // PAGE CONTENT
            $contentClasses = ['content'];
            if(is_page('stories') || is_page('events')) {
                $contentClasses[] = 'fluidContent';
            }
            ?>
            <div <?php echo ' class="'.join(' ', $contentClasses).'"'; ?>>
                <?php reframework_render_post_content(); ?>
            </div>
        <?php endif; ?>
    <?php if (!is_page('stories') && !is_page('events')) : ?>
            </div>
            <?php getRightFooter(is_single()); ?>
    <?php endif; ?>
</div>