<?php 
	$sliderLimitCount = get_slider_image_count();
	$args = array(
		'post_type' => 'post',
		'posts_per_page' => $sliderLimitCount,
		'order' => 'DESC',
		'ignore_sticky_posts' => 1,
		'post__not_in' => get_option( 'sticky_posts' ),
		'tax_query' => array(
			array(
				'taxonomy' => 'category',
				'field' => 'id',
				'terms' => get_slider_categories()
			)
		)
	);
	$query = new WP_Query($args);
?>

<div class="effect-class">
	<img src="<?php echo $imagesPath . 'effect.png' ?>">
</div>

<div class="right-side home-page">
	<div class="homeContainer all-content">
	<?php if( $sliderLimitCount>0 && $query->have_posts() && $query->post_count>1 ): ?>
		<h1 class="title">Top Stories</h1>
			<div class="carousel-box">
				<div class="carousel-container" style="display:none;">
					<ul class="carousel-list">
					<?php
                        while ($query->have_posts()) {
                            $query->the_post();
                            if ( has_post_thumbnail() ) {
                                ?>
								<li class="carousel-item" data-flip-title="<?php echo get_the_title(); ?>">
									<h2 class="title"><?php echo get_the_title(); ?></h2>
									<a href="<?php echo the_permalink(); ?>"><img src="<?php echo get_the_post_thumbnail_url(); ?>"/></a>
								</li>
							<?php

                            }
                        }
					?>     
					</ul>
				</div>  
			</div>
		<hr>
	<?php endif; ?>
		<h1 class="title">Recent Tweets</h1>
	<?php
		echo do_shortcode('[ap-twitter-feed]');
	?>
	</div>
</div>
