<div id="staff-wrap">
	<ul class="warpbox">
    
		<?php $loop = new WP_Query( array( 'post_type' => 'staff','posts_per_page' => 50) ); ?>
        <?php while ( $loop->have_posts() ) : $loop->the_post(); ?>
        <?php 
			$staff_position = get_post_meta($post->ID, 'themnific_staff_position', true);
			$staff_facebook = get_post_meta($post->ID, 'themnific_staff_facebook', true);
			$staff_twitter = get_post_meta($post->ID, 'themnific_staff_twitter', true);
			$staff_google = get_post_meta($post->ID, 'themnific_staff_google', true);
			$staff_linkedin = get_post_meta($post->ID, 'themnific_staff_linkedin', true);
			$staff_pinterest = get_post_meta($post->ID, 'themnific_staff_pinterest', true);
			$staff_website = get_post_meta($post->ID, 'themnific_staff_website', true);
		?>
        
            <li class="staff threecol">
                
        		<?php the_post_thumbnail('staff'); ?>
                
				<h3><?php the_title(  ); ?></h3>   
                
                <?php if($staff_position) {?>
                        <p class="meta"><?php echo ($staff_position); ?></p>
                <?php } else {}?>
                       
                <?php the_content(); ?>
                
                <ul class="staff_social">
                
                	<?php if($staff_facebook) {?><li><a class="rad mk-social-facebook" href="<?php echo ($staff_facebook); ?>"></a></li><?php } else {}?>
                	<?php if($staff_twitter) {?><li><a class="rad mk-social-twitter-alt" href="<?php echo ($staff_twitter); ?>"></a></li><?php } else {}?>
                	<?php if($staff_google) {?><li><a class="rad mk-social-googleplus" href="<?php echo ($staff_google); ?>"></a></li><?php } else {}?>
                	<?php if($staff_linkedin) {?><li><a class="rad mk-social-linkedin" href="<?php echo ($staff_linkedin); ?>"></a></li><?php } else {}?>
                	<?php if($staff_pinterest) {?><li><a class="rad mk-social-pinterest" href="<?php echo ($staff_pinterest); ?>"></a></li><?php } else {}?>
                	<?php if($staff_website) {?><li><a class="rad mk-social-wp" href="<?php echo ($staff_website); ?>"></a></li><?php } else {}?>
                
                </ul>
                
            </li>
        
        <?php endwhile; ?>
    
    </ul>
</div> 
<div style="clear: both;"></div>	