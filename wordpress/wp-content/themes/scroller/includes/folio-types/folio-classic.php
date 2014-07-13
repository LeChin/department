           <?php
				$large_image =  wp_get_attachment_image_src( get_post_thumbnail_id(get_the_ID()), 'fullsize', false, '' ); 
				$large_image = $large_image[0]; 
				$another_image_1 = get_post_meta($post->ID, 'themnific_image_1_url', true);
				$video_input = get_post_meta($post->ID, 'themnific_video_url', true);
                $project_description = get_post_meta($post->ID, 'themnific_project_description', true);
            ?>
            
            <div class="item_full item_height1">
        
                <div class="imgwrap">
                
                        
                        <a href="<?php the_permalink(); ?>">
                                
                            <?php the_post_thumbnail('folio',array('title' => "")); ?>
                        
                        </a>
                        
                </div>	
                
                <div style="clear:both"></div>
    
                <div class="hover_box_info">
                    <h3><a href="<?php the_permalink(); ?>"><?php echo short_title('...', 8); ?></a></h3>
                    
                    <p>

                        <?php if($project_description) { echo themnific_excerpt( $project_description, '70'); 


                        } else {
                            
                            echo themnific_excerpt( get_the_excerpt(), '70');
                            
                         }?>

                     </p>
                    
                    <a class="portfolio-feature-link" href="<?php the_permalink(); ?>">See Item</a>
                </div>
    
            </div>