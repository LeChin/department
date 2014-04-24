<?php
		$font_h1 = get_option('themnific_font_h1');	
		$font_h2 = get_option('themnific_font_h2');	
		$font_h2_home = get_option('themnific_font_h2_home');	
		$font_h3 = get_option('themnific_font_h3');	
		$font_h4 = get_option('themnific_font_h4');	
		$font_h5 = get_option('themnific_font_h5');	

		$font_text = get_option('themnific_font_text');	
		$font_nav = get_option('themnific_font_nav');	


echo '<link href="http://fonts.googleapis.com/css?family='

.str_replace(" ", "+",$font_h1 ["face"]).':'.$font_h1["style"].'|'
.str_replace(" ", "+",$font_h2 ["face"]).':'.$font_h2["style"].'|'
.str_replace(" ", "+",$font_h2_home ["face"]).':'.$font_h2_home["style"].'|'
.str_replace(" ", "+",$font_h3 ["face"]).':'.$font_h3["style"].'|'
.str_replace(" ", "+",$font_h4 ["face"]).':'.$font_h4["style"].'|'
.str_replace(" ", "+",$font_h5 ["face"]).':'.$font_h5["style"].'|'


.str_replace(" ", "+",$font_text ["face"]).':'.$font_text["style"].'|'
.str_replace(" ", "+",$font_nav ["face"]).':'.$font_nav["style"].'"

 rel="stylesheet" type="text/css">'."\n";


?>