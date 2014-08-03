<?php get_header(); ?>
<?php
$large_image =  wp_get_attachment_image_src( get_post_thumbnail_id(get_the_ID()), 'fullsize', false, '' ); 
$large_image = $large_image[0]; 
$video_input = get_post_meta($post->ID, 'themnific_video_embed', true);
$project_url = get_post_meta($post->ID, 'themnific_gatherings_url', true);
$project_description = get_post_meta($post->ID, 'themnific_project_description', true);
$attachments = get_children( array('post_parent' => get_the_ID(), 'post_type' => 'attachment', 'post_mime_type' => 'image') );
?>

<?php the_post(); ?>
    
<div class="container container_block">
    
    <div class="nav_item">
        
        <?php previous_post_link('%link', '<span class="previous_product">%title</span>') ?>
    
        
        <?php next_post_link('%link', '<span class="next_product">%title</span>') ?>
  
    </div>
    
    <h2 class="itemtitle"><?php the_title(); ?></h2>
    <h3 class="item_header"><?php echo $project_description; ?></h3>
    

    <div id="foliosidebar">
    
    
        
    </div>
    
    
    
    
    <div id="foliocontent">   
            
            <div class="entry entry_item">
             
        <?php the_content(); ?>
            
            </div>
  
     </div>
     
</div>
<div id="push_up"></div>
        
<?php get_footer(); ?>