<?php
/*
Template Name: Full Width Image Header
*/
?>
<?php get_header(); ?>
<?php 
$large_image =  wp_get_attachment_image_src( get_post_thumbnail_id(get_the_ID()), 'fullsize', false, '' ); 
$large_image = $large_image[0]; 
$project_description = get_post_meta($post->ID, 'themnific_project_description', true);
$src = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID),false, '' );
?> 

<div class="resmode-No section_template "
style="  <?php if($large_image) { ?>background-image:url(<?php echo $src[0] ?>);<?php } else {}?> ">

  <div class="container">
    
    
  </div>


</div>

<div class="hrlineB"></div>
    
    <div class="container" style="overflow:visible">

        <div class="heading">
        
            <h2><?php the_title(); ?></h2>

            <p><?php echo $project_description; ?></p>
            
        </div>
    
    
    	<div class="entryfull" style="overflow:visible">
            
            <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            
            <?php the_content(); ?>
            
            <?php wp_link_pages(array('before' => '<p><strong>Pages:</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>
            
            <?php endwhile; endif; ?>
            
       	</div>
        
    </div>
    
    <div style="clear: both;"></div>
   	<div id="push_up"></div>
    
<?php get_footer(); ?>