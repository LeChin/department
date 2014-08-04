<?php get_header(); ?>
<?php
$large_image =  wp_get_attachment_image_src( get_post_thumbnail_id(get_the_ID()), 'fullsize', false, '' ); 
$large_image = $large_image[0]; 
$video_input = get_post_meta($post->ID, 'themnific_video_embed', true);
$project_url = get_post_meta($post->ID, 'themnific_gatherings_url', true);
$project_description = get_post_meta($post->ID, 'themnific_project_description', true);
$product_section = get_post_meta($post->ID, 'themnific_product_section', true);
$attachments = get_children( array('post_parent' => get_the_ID(), 'post_type' => 'attachment', 'post_mime_type' => 'image') );
?>

<?php the_post(); ?>
  
<div class="gatherings_header">
  <div class="nav_item">
    <?php previous_post_link('%link', '<span class="previous_product">%title</span>') ?>
    <?php next_post_link('%link', '<span class="next_product">%title</span>') ?>
  </div>
  <img class="aligncenter size-full wp-image-226" src="http://localhost:8888/wordpress/wp-content/uploads/2014/08/diamond.png" alt="diamond" width="35" height="34" class="alignnone size-full wp-image-72" />

  <h2><?php the_title(); ?></h2>
  <p class="-content"><?php echo $project_description; ?></p>
</div>

<div id="gatherings_content">
  <div class="entryfull">
    <?php the_content(); ?>
  </div>
</div>

<div id="gatherings_products">
  <h3>Department of Decoration Pieces</h3>
  <div id="products_list">
    <div class="-content">
      <?php echo $product_section; ?>
    </div>
  </div>
</div>

<div id="push_up"></div>
        
<?php get_footer(); ?>