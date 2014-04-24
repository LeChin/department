<?php

// INIT
function flexslider_hg_setup_init()
{
	// LANGUAGE
	load_plugin_textdomain( 'flexslider_hg', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	// MAKE SURE POST THUMBNAILS ARE AVAILABLE
	if(!current_theme_supports('post-thumbnails')) { add_theme_support('post-thumbnails'); }

	// 'SLIDES' POST TYPE
	$slides_labels = array( 'name' => __( 'Slides', 'flexslider_hg' ), 'singular_name' => __( 'Slide', 'flexslider_hg' ), 'all_items' => __( 'All Slides', 'flexslider_hg' ), 'add_new' => __( 'Add New Slide', 'flexslider_hg' ), 'add_new_item' => __( 'Add New Slide', 'flexslider_hg' ), 'edit_item' => __( 'Edit Slide', 'flexslider_hg' ), 'new_item' => __( 'New Slide', 'flexslider_hg' ),'view_item' => __( 'View Slide', 'flexslider_hg' ),'search_items' => __( 'Search Slides', 'flexslider_hg' ),'not_found' => __( 'No Slide found', 'flexslider_hg' ), 'not_found_in_trash' => __( 'No Slide found in Trash', 'flexslider_hg' ), 'parent_item_colon' => '' );
	
	$slides_args = array(
		'labels'               => $slides_labels,
		'public'               => false,
		'publicly_queryable'   => true,
		'_builtin'             => false,
		'show_ui'              => true, 
		'query_var'            => true,
		'rewrite'              => apply_filters( 'flexslider_hg_post_type_rewite', array( "slug" => "slides" )),
		'capability_type'      => 'post',
		'capabilities' => array(
		        'publish_posts' 		=> 'publish_pages',
		        'edit_posts' 			=> 'publish_pages',
		        'edit_others_posts' 	=> 'publish_pages',
		        'delete_posts' 			=> 'publish_pages',
		        'delete_others_posts' 	=> 'publish_pages',
		        'read_private_posts' 	=> 'publish_pages',
		        'edit_post' 			=> 'publish_pages',
		        'delete_post' 			=> 'publish_pages',
		        'read_post' 			=> 'publish_pages',
		    ),
		'hierarchical'         => false,
		'menu_position'        => 26.6,
		'supports'             => array( 'title', 'thumbnail', 'excerpt', 'page-attributes' ),
		'taxonomies'           => array(),
		'has_archive'          => true,
		'show_in_nav_menus'    => false,
	);
	register_post_type( 'slides', $slides_args );
	
	
	// TESTIMONIAL ROTATOR CUSTOM POST TYPE
  	$sliders_labels = array( 'name' => __('Sliders', 'flexslider_hg'), 'singular_name' => __('Slider', 'flexslider_hg'), 'add_new' => __('Add New', 'flexslider_hg'), 'add_new_item' => __('Add New Slider', 'flexslider_hg'), 'edit_item' => __('Edit Slider', 'flexslider_hg'), 'new_item' => __('New Slider', 'flexslider_hg'), 'all_items' => __('All Sliders', 'flexslider_hg'), 'view_item' => __('View Slider', 'flexslider_hg'), 'search_items' => __('Search Sliders', 'flexslider_hg'), 'not_found' =>  __('No sliders found', 'flexslider_hg'), 'not_found_in_trash' => __('No sliders found in Trash', 'flexslider_hg'), 'parent_item_colon' => '', 'menu_name' => __('Sliders', 'v') );
						
	$sliders_args = array(
		'labels' 				=> $sliders_labels,
		'public' 				=> false,
		'publicly_queryable' 	=> false,
		'show_ui' 				=> true, 
		'show_in_menu' 			=> false, 
		'query_var' 			=> true,
		'rewrite' 				=> array( 'with_front' => false ),
		'capability_type' 		=> 'post',
		'capabilities' => array(
		        'publish_posts' 		=> 'publish_pages',
		        'edit_posts' 			=> 'publish_pages',
		        'edit_others_posts' 	=> 'publish_pages',
		        'delete_posts' 			=> 'publish_pages',
		        'delete_others_posts' 	=> 'publish_pages',
		        'read_private_posts' 	=> 'publish_pages',
		        'edit_post' 			=> 'publish_pages',
		        'delete_post' 			=> 'publish_pages',
		        'read_post' 			=> 'publish_pages',
		    ),
		'has_archive' 			=> false,
		'hierarchical' 			=> false,
		'menu_position' 		=> 26.7,
		'exclude_from_search' 	=> true,
		'supports' 				=> array( 'title' ),
		'show_in_menu'  		=> 'edit.php?post_type=slides',
	);
					
	register_post_type( 'sliders', $sliders_args );

}