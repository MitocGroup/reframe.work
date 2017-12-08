<div class="socialPanel right-section">
    <?php comments_template(); ?> 
    <div class="authorPanel">
        <a href="/about/personal/" class="authorPhoto">
            <?php echo get_avatar( get_the_author_meta('grigore997@gmail.com'), $size = '50'); ?>
        </a>
        <div class="authorName">
            <a href="/about/personal/">
                <?php echo get_author_name(); ?>
            </a>
        </div>
    </div>
    
    <div class="shareBox">
        <div class="media">
            <span>Share: </span>
            <a class="facebook" href="#"><i class="mdi mdi-facebook"> +246</i></a>
            <a class="twitter" href="#"><i class="mdi mdi-twitter"> +457</i></a>
            <a class="linkedin" href="#"><i class="mdi mdi-linkedin"> +56</i></a>
        </div>
        <?php do_shortcode('[TheChamp-Sharing]'); ?>
    </div>
</div>
