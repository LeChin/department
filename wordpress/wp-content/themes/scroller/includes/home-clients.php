<ul id="clientsbox">

    <?php $loop = new WP_Query( array( 'post_type' => 'client','posts_per_page' => 50) ); ?>
    <?php while ( $loop->have_posts() ) : $loop->the_post(); ?>
    <?php 
        $themnific_client_data = get_post_meta($post->ID, 'themnific_client_link', true);
    ?>
    
        <li class="clients">
            <h3><?php the_title(); ?></h3>
            
            <p><?php the_excerpt(); ?></p>

            <a title="<?php the_title(); ?>" href="<?php echo $themnific_client_data; ?>"><?php echo $themnific_client_data; ?></a>      
        </li>
    
    <?php endwhile; ?>

</ul>
<div style="clear: both;"></div>	