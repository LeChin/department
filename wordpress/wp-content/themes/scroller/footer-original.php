<div id="footer" class="body3">

  <div class="container">

        <div id="copyright">
                
            <div class="fl">
            </div>
        
        
            <div class="fl">
            
        <?php if(get_option('themnific_footer_left') == 'true'){
                    
                    echo stripslashes(get_option('themnific_footer_left_text'));
                    
                } else { ?>
        
              <p>&copy; <?php echo date("Y"); ?> <?php bloginfo('name'); ?> | <?php bloginfo('description'); ?></p>
                    
                <?php } ?>
                    
            </div>
        
            <div class="fr">
            
        <?php if(get_option('themnific_footer_right') == 'true'){
                    
                    echo stripslashes(get_option('themnific_footer_right_text'));
                    
                } else { ?>
                
                    <p><?php _e('Powered by','themnific');?> <a href="http://www.wordpress.org">Wordpress</a>. <?php _e('Designed by','themnific');?> <a href="http://themnific.com">Themnific&trade;</a></p>
                    
                <?php } ?>
                
            </div>
                  
        </div> 
    
  </div>
        
</div><!-- /#footer  -->
    
<?php themnific_foot(); ?>
<?php wp_footer(); ?>

</body>
</html>