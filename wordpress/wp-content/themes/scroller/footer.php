<div id="footer" class="body3">

	<div class="container">

        <div id="copyright">
                
            <div class="fl">
                <i class="icon-search"></i><?php get_template_part('/includes/uni-searchformhead');?>
            </div>
        
        
            <div class="center_footer">
              <ul id="footer_pages">
                <li><?php echo do_shortcode("[link_popup id='220' link_text='Newsletter' name='Newsletter']"); ?></li>
                <li><a href="http://www.deptofdecoration.com/faq/" title="faq">FAQ</a></li>
                <li><a href="http://www.deptofdecoration.com/privacy/" title="privacy">Privacy Policy</a></li>
                <li><a href="http://www.deptofdecoration.com/press/" title="Press">Press</a></li>
              </ul>
            </div>
        
            <div class="fr">
                <a href="http://www.pinterest.com/deptofdecor/" target="_blank" title="pinterest" class="pinterest social_icon"></a>
                <a href="http://instagram.com/deptofdecoration" target="_blank" title="instagram" class="instagram social_icon"></a>
                <a href="https://twitter.com/weshapespace" target="_blank" title="twitter" class="twitter social_icon"></a>
                <a href="https://www.facebook.com/departmentofdecoration" target="_blank" title="facebook" class="facebook social_icon"></a>
                <a href="mailto:?subject=<?php urlencode(the_title()) ?>&body=<?php urlencode(the_permalink()) ?>">Send via Email</a>
            </div>
                  
        </div> 
    
	</div>
        
</div><!-- /#footer  -->
    
<?php themnific_foot(); ?>
<?php wp_footer(); ?>

</body>
</html>