jQuery(document).ready(function(){
/*global jQuery:false */
/*jshint devel:true, laxcomma:true, smarttabs:true */
"use strict";

	jQuery(function() {
		if (jQuery.browser.webkit) {
			jQuery(".slider_full img").css('position','relative');
			jQuery(".slider_full img").css('top','auto');
		}
	});
	
	
	// parallax
	jQuery('.section').parallax("50%", 0.05);
	jQuery('.section_template').parallax("50%", 0.6);
	
	jQuery(function() {	
		if (navigator.userAgent.indexOf('Safari') != -1 && navigator.userAgent.indexOf('Chrome') == -1){
				jQuery(".section").css('background-attachment','scroll');
				jQuery('.section').parallax("50%", 0.08);
		}
	});
	
	jQuery(function() {					
		if (jQuery.browser.webkit) {
				jQuery('.wpcf7-form-control').focus(function(){
					jQuery(".section").css('background-attachment','scroll'); 
				});
		}
	});


	  // change header - depends o scroll
			jQuery(window).scroll(function () {
				if (jQuery(this).scrollTop() > 600) {
					jQuery('#header h1').addClass('showme');
					jQuery('#nav').addClass('nav_classic');
				} else {
					jQuery('#header h1').removeClass('showme');
					jQuery('#nav').removeClass('nav_classic');
				}
			});
	

	// add header_scroll class when refresh
	  jQuery(function() {
	  
		  // grab the initial top offset of the navigation 
		  var sticky_navigation_offset_top = jQuery('body').offset().top;
		  
		  // our function that decides weather the navigation bar should have "fixed" css position or not.
		  var sticky_navigation = function(){
			  var scroll_top = jQuery(window).scrollTop() > 601; // our current vertical position from the top
			  
			  // if we've scrolled more than the navigation, change its position to fixed to stick to top, otherwise change it back to relative
			  if (scroll_top > sticky_navigation_offset_top) { 
					jQuery('#nav').addClass('nav_classic');
					jQuery('#header img').fadeIn();
			  }  
		  };
  
		  // run our function on load
		  sticky_navigation();
		  
		  // and run it again every time you scroll
		  jQuery(window).scroll(function() {
			   sticky_navigation();
		  });
		  
	  });




	// add nav current class on load
	jQuery(function() {
		jQuery('#nav li:first').addClass('current');
	});
	
	// initiate page scroller plugin	
	jQuery('.scroll').onePageNav({
		begin: function() {
		console.log('start');},
		end: function() {
		console.log('stop');},
		filter: ':not(.external a)'
	});


	// add spec class to nav
	jQuery("ul.sub-menu,ul.children").parents().addClass('scrollparent');




	// trigger + show menu on fire
	  jQuery(window).resize(function() {
	  /*If browser resized, check width again */
		  if (jQuery(window).width() < 639) {
			   jQuery('#navigation').addClass('hidenav');
			   jQuery('a#navtrigger').addClass('showtrig');
			  }
		  else {
			  jQuery('#navigation').removeClass('hidenav');
			  jQuery('a#navtrigger').removeClass('showtrig');}
	  });
	  
        jQuery('a#navtrigger').click(function(){ 
                jQuery('#navigation').toggleClass('shownav'); 
                jQuery('#sec-nav').toggleClass('shownav'); 
                jQuery(this).toggleClass('active'); 
                return false; 
        });

	// fading out/in slider stuff
	var fadeStart=100 // 100px scroll or less will equiv to 1 opacity
		,fadeUntil=500 // 500px scroll or more will equiv to 0 opacity
		,fading = jQuery('.stuff,#header_bottom,.section_template h2')
	;
	
	jQuery(window).bind('scroll', function(){
		var offset = jQuery(document).scrollTop()
			,opacity=0
		;
		if( offset<=fadeStart ){
			opacity=1;
		}else if( offset<=fadeUntil ){
			opacity=1-offset/fadeUntil;
		}
		fading.css('opacity',opacity);
	});


	/* wp gallery hover */	
			
	// jQuery('.item_full,.item_carousel,.item_slider').hover(function() {
	// 	jQuery(this).find('a.hoverstuff-link,a.hoverstuff-zoom')
	// 		.animate({opacity: '1'}, 100); 
	
	// 	} , function() {
	
	// 	jQuery(this).find('a.hoverstuff-link,a.hoverstuff-zoom')
	// 		.animate({opacity: '0'}, 400); 
	// });

	// jQuery('.item_full,.item_carousel,.format-image').hover(function() {
	// 	jQuery(this).find('img')
	// 		.animate({opacity: '.1'}, 100); 
	
	// 	} , function() {
	
	// 	jQuery(this).find('img')
	// 		.animate({opacity: '1'}, 400); 
	// });



	/* Tooltips */
	jQuery("body").prepend('<div class="tooltip rad"><p></p></div>');
	var tt = jQuery("div.tooltip");
	
	jQuery(".flickr_badge_image a img,ul.social-menu li a,.nav_item i").hover(function() {								
		var btn = jQuery(this);
		
		tt.children("p").text(btn.attr("title"));								
					
		var t = Math.floor(tt.outerWidth(true)/2),
			b = Math.floor(btn.outerWidth(true)/2),							
			y = btn.offset().top - 30,
			x = btn.offset().left - (t-b);
					
		tt.css({"top" : y+"px", "left" : x+"px", "display" : "block"});			
		   
	}, function() {		
		tt.hide();			
	});



	function lightbox() {
		// Apply PrettyPhoto to find the relation with our portfolio item
		jQuery("a[rel^='prettyPhoto']").prettyPhoto({
			// Parameters for PrettyPhoto styling
			animationSpeed:'fast',
			slideshow:5000,
			theme:'pp_default',
			show_title:false,
			overlay_gallery: false,
			social_tools: false
		});
	}
	
	if(jQuery().prettyPhoto) {
		lightbox();
	}


	// Begin Custom Code - N Barrett
	// sticky header

	function stickHeaderAndFooter() {
		var windowH = jQuery(window).height();
	  var stickToBot = windowH - jQuery('#header').outerHeight(true);
	  //outherHeight(true) will calculate with borders, paddings and margins.
	  jQuery('.home #header').css({'top': stickToBot + 'px'});

	  jQuery(window).scroll(function() {
	    var scrollVal = jQuery(this).scrollTop();
	    if ( scrollVal > stickToBot ) {
	      jQuery('.home #header').css({'position':'fixed','top' :'0px','bottom':'auto'});
	      jQuery('.home #footer').css({'position':'fixed','bottom' :'0px'});
	    } else {
	      jQuery('.home #header').css({'position':'absolute','top': stickToBot +'px'});
	      jQuery('.home #footer').css({'position':'relative'});
	    }
	  });
	}

	function stickHeaderAndFooterResize() {
		var windowH = jQuery(window).height();
	  var stickToBot = windowH - jQuery('#header').outerHeight(true);
	  //outherHeight(true) will calculate with borders, paddings and margins.
	  jQuery('.home #header').css({'top': stickToBot + 'px'});

	  jQuery(window).resize(function() {
	    var scrollVal = jQuery(this).scrollTop();
	    if ( scrollVal > stickToBot ) {
	      jQuery('.home #header').css({'position':'fixed','top' :'0px','bottom':'auto'});
	      jQuery('.home #footer').css({'position':'fixed','bottom' :'0px'});
	    } else {
	      jQuery('.home #header').css({'position':'absolute','top': stickToBot +'px'});
	      jQuery('.home #footer').css({'position':'relative'});
	    }
	  });
	}

  jQuery("#show_hide_arrow").click(function() {
  	jQuery(document.body).animate({
    	'scrollTop': jQuery('#collection').offset().top
		}, 2000);
  });

	stickHeaderAndFooter();

	jQuery(window).resize(function(){	
		stickHeaderAndFooter();
		stickHeaderAndFooterResize();
	});

	var timeout = null;

	jQuery(document).on('mousemove', function() {
    if (timeout !== null) {
        clearTimeout(timeout);
    }

    timeout = setTimeout(function() {
      timeout = null;
      console.log('Mouse idle for 3 sec');
      jQuery('#show_hide_arrow').fadeIn();
    }, 3000);
	});

	jQuery(document).on('ready', function() {
    jQuery('#show_hide_arrow').delay('5000').fadeIn();
		stickHeaderAndFooter();
	});
	
	jQuery(document).on('scroll', function(){
    jQuery('#show_hide_arrow').fadeOut();
	});

	// show/hide pin-it button
	
	jQuery('.soliloquy-item').on('mouseenter', function(){
    jQuery(this).find('.soliloquy-pinterest-share').addClass('active');
	});
	
	jQuery('.soliloquy-item').on('mouseleave', function(){
    jQuery(this).find('.soliloquy-pinterest-share').removeClass('active');
	});
});