<?php get_header(); ?>
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>


<div class="container container_block">
 
            <div id="content">
        	<div <?php post_class(); ?>>
    
                    <div class="entry">
                    <?php the_content(); ?>
                        <?php wp_link_pages( array( 'before' => '<div class="page-link"><span>' . __( 'Pages:','themnific') . '</span>', 'after' => '</div>' ) ); ?>
                        <?php the_tags( '<p class="tagssingle">','',  '</p>'); ?>
                    </div>       
                        
				<div style="clear: both;"></div>

            </div>



	<?php endwhile; else: ?>

		<p><?php _e('Sorry, no posts matched your criteria','themnific');?>.</p>

	<?php endif; ?>

                <div style="clear: both;"></div>

        </div><!-- #homecontent -->
        
        </div>

<?php get_footer(); ?>