<?php
/*
Plugin Name: HG Slider
Plugin URI: http://halgatewood.com/flexslider-hg
Description: A responsive image rotator plugin that easily creates WordPress slideshows.
Version: 2.0
Author: Hal Gatewood
Author URI: http://halgatewood.com
Text Domain: flexslider_hg
Domain Path: /languages

*/


/* SETUP */
add_action( 'plugins_loaded', 'flexslider_hg_setup' );
define( 'FLEXSLIDER_HG_URI', trailingslashit( plugin_dir_url( __FILE__ ) ) );
require_once dirname( __FILE__ ) . '/flexslider-hg-init.php';


// GET ROTATORS
function flexslider_hg_rotators()
{
	$rotators = array();
	$rotators['homepage'] 		= array( 'size' => 'large' );
	$rotators['attachments'] 	= array( 'size' => 'large', 'hide_slide_data' => true );
	
	// GET ROTATORS FROM sliders POST TYPE
	$sliders = get_posts( array( 'post_type' => 'sliders', 'posts_per_page' => -1 ) );
	if( $sliders )
	{
		foreach( $sliders as $slider )
		{
			// CUSTOM FIELDS
			$current_randomize				= (int) get_post_meta( $slider->ID, '_randomize', true );
			$current_slideshow_speed		= get_post_meta( $slider->ID, '_slideshow_speed', true );
			$current_animation_speed		= get_post_meta( $slider->ID, '_animation_speed', true );
			$current_animation				= get_post_meta( $slider->ID, '_animation', true );
			$current_animation_direction	= get_post_meta( $slider->ID, '_animation_direction', true );
			
			// CREATE NEW ARRAY IF NOT ALREADY, 
			// *** MANUALLY CREATING ROTATORS TAKES PRECEDENCE
			if( ! isset( $rotators[ $slider->post_name ] ) ) 
			{
				$rotators[ $slider->post_name ] = array();
			}
			
			// ADVANCED FLEXSLIDER OPTIONS
			$advanced_options = array();
			if( isset($rotators[ $slider->post_name ]['options']) )
			{
				$advanced_options = json_decode( $rotators[ $slider->post_name ]['options'], true );
			}
			
			if($current_randomize) 				{ $advanced_options['randomize'] = 'true'; }
			if($current_slideshow_speed) 		{ $advanced_options['slideshowSpeed'] = $current_slideshow_speed * 1000; }
			if($current_animation_speed) 		{ $advanced_options['animationSpeed'] = $current_animation_speed * 1000; }
			if($current_animation) 				{ $advanced_options['animation'] = $current_animation; }
			if($current_animation_direction) 	{ $advanced_options['direction'] = $current_animation_direction; }
			
			$rotators[ $slider->post_name ]['size'] 				= get_post_meta( $slider->ID, '_image_size', true );
			$rotators[ $slider->post_name ]['heading_tag'] 			= get_post_meta( $slider->ID, '_heading_tag', true );
			$rotators[ $slider->post_name ]['hide_slide_data'] 		= get_post_meta( $slider->ID, '_hide_slide_data', true );
			$rotators[ $slider->post_name ]['orderby']				= get_post_meta( $slider->ID, '_order_by', true );
			$rotators[ $slider->post_name ]['order']				= get_post_meta( $slider->ID, '_order', true );
			$rotators[ $slider->post_name ]['limit']				= (int) get_post_meta( $slider->ID, '_limit', true );
			$rotators[ $slider->post_name ]['corners']				= get_post_meta( $slider->ID, '_corners', true );
			$rotators[ $slider->post_name ]['style']				= get_post_meta( $slider->ID, '_style', true );

			$rotators[ $slider->post_name ]['options'] = json_encode($advanced_options);
		}
	}

	return apply_filters( 'flexslider_hg_rotators', $rotators );
}


// SETUP ACTIONS
function flexslider_hg_setup()
{
	add_action( 'init', 'flexslider_hg_setup_init' );
	add_action( 'admin_head', 'flexslider_hg_admin_icon' );	
	add_action( 'wp_enqueue_scripts', 'flexslider_wp_enqueue' );	

	add_action( 'add_meta_boxes', 'flexslider_hg_create_slide_metaboxes' );
	add_action( 'add_meta_boxes', 'flexslider_hg_create_slider_metaboxes' );
	add_action( 'save_post', 'flexslider_hg_save_meta', 1, 2 );
	
	add_filter( 'manage_edit-slides_columns', 'flexslider_hg_columns' );
	add_action( 'manage_slides_posts_custom_column', 'flexslider_hg_add_columns' );
	
	add_filter( 'manage_edit-sliders_columns', 'flexslider_hg_sliders_edit_columns');
	add_action( 'manage_sliders_posts_custom_column', 'flexslider_hg_sliders_sliders_add_columns' );	
	
	add_shortcode('flexslider', 'flexslider_hg_shortcode');
	add_shortcode('hgslider', 'flexslider_hg_shortcode');
	
	add_action( 'admin_menu', 'register_slides_submenu_page');
	
	add_filter( 'enter_title_here', 'register_hgslider_form_title' );
}


// SLIDERS SUBMENU PAGE
function register_slides_submenu_page()
{
	add_submenu_page( 'edit.php?post_type=slides', __('Add New Slider', 'flexslider_hg'), __('Add New Slider', 'flexslider_hg'), 'manage_options', 'post-new.php?post_type=sliders' ); 
}


// ON INSTALL FLUSH REWRITES FOR OUR NEW PERMALINKS
function hg_slider_activate() 
{
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'hg_slider_activate' );


// FRONTEND: heading
function flexslider_wp_enqueue()
{
	wp_enqueue_script( 'flexslider', FLEXSLIDER_HG_URI . 'js/jquery.flexslider-min.js', array( 'jquery' ) );
	wp_enqueue_style( 'flexslider', FLEXSLIDER_HG_URI . 'css/flexslider.css' );
}


// ADMIN: WIDGET ICONS
function flexslider_hg_admin_icon()
{
	echo '
		<style> 
			#adminmenu #menu-posts-slides div.wp-menu-image:before { content: "\f233"; }
		</style>
	';	
}


// CREATE AND RETURN PIPED ROTATOR IDS
function flexslider_hg_make_piped_string( $arr )
{
	return "|" . implode("|", (array) $arr) . "|";
}
function flexslider_hg_break_piped_string( $arr )
{
	return array_filter( explode("|", (string) $arr), 'strlen' );
}


// SHOW ROTATOR
function show_flexslider_rotator( $slug )
{
	// GET ALL ROTATORS
	$rotators = flexslider_hg_rotators();
	
	$rotator = $rotators[ $slug ];
	
	// SET IMAGE SIZE: size
	$image_size = isset( $rotator['size'] ) ? $rotators[ $slug ]['size'] : 'large';

	// HIDE SLIDE TEXT: hide_slide_data
	$hide_slide_data = (isset( $rotator['hide_slide_data'] ) AND $rotator['hide_slide_data']) ? true : false;
	
	// HEADING HTML ELEMENT: heading_tag
	$header_type = isset( $rotator['heading_tag'] ) ? $rotator['heading_tag'] : "h2";

	// ORDER BY PARAMS: orderby, order, limit
	$orderby = isset( $rotator['orderby'] ) ? $rotator['orderby'] : "menu_order";
	$order = isset( $rotator['order'] ) ? $rotator['order'] : "ASC";
	$limit = (isset( $rotator['limit'] ) AND $rotator['limit'] > 0) ? $rotator['limit'] : "-1";


	// STYLING
	$corner_css = (isset( $rotator['corners'] ) AND $rotator['corners'] == "square") ? "square" : "rounded";
	$styles_css = (isset( $rotator['style'] ) AND $rotator['style'] != "") ? $rotator['style'] : "default";


	// DEFAULT QUERY PARAMS
	$query_args = array( 'post_type' => 'slides', 'order' => $order, 'orderby' => $orderby, 'posts_per_page' => $limit );
	
	// IF ATTACHMENTS WE NEED THE POST PARENT
	if( $slug == "attachments" )
	{
		$query_args['post_type'] = 'attachment';
		$query_args['post_parent'] = get_the_ID();
		$query_args['post_status'] = 'inherit';
		$query_args['post_mime_type'] = 'image';
		unset( $query_args['meta_value'] );
		unset( $query_args['meta_key'] );
	}
	else
	{
		$query_args['meta_query'] = array( 'relation' => 'OR',
															array(
																'key' 		=> '_slider_id',
																'value' 	=> $slug
															),
															array(
																'key' 		=> '_slider_id',
																'value' 	=> '|' . $slug . '|',
																'compare'	=> 'LIKE'
															));
	}
	
	
	
	$rtn = "";
	
	query_posts( apply_filters( 'flexslider_hg_query_post_args', $query_args) );
	
	if ( have_posts() ) 
	{
		$rtn .= '<div id="flexslider_hg_' . $slug . '_wrapper" class="flexslider-hg-wrapper">';
		$rtn .= '<div id="flexslider_hg_' . $slug . '" class="flexslider_hg_' . $slug . ' flexslider flexslider-hg flexslider-hg-corners-' . $corner_css . ' flexslider-hg-style-' . $styles_css . '">';
		$rtn .= '<ul class="slides">';
		
		while ( have_posts() )
		{
			the_post();
		
			$url = esc_url( get_post_meta( get_the_ID(), "_slide_link_url", true ) );
			$a_tag_opening = '<a href="' . $url . '" title="' . the_title_attribute( array('echo' => false) ) . '" >';
			
			
			$rtn .= '<li>';
			$rtn .= '<div id="slide-' . get_the_ID() . '" class="slide">';
			
			if( $slug == "attachments" )
			{
				$rtn .= wp_get_attachment_image( get_the_ID(), $image_size );
			}
			else if ( has_post_thumbnail() )
			{
				if($url) { $rtn .= $a_tag_opening; }
				$rtn .= get_the_post_thumbnail( get_the_ID(), $image_size , array( 'class' => 'slide-thumbnail' ) );
				if($url) { $rtn .= '</a>'; }
			}
			
			if( !$hide_slide_data)
			{
				$rtn .= '<div class="slide-data">';
				
				$rtn .= '<' . $header_type . ' class="slide-title flexslider-hg-title">';
				
				if($url) { $rtn .= $a_tag_opening; }
				$rtn .= get_the_title();
				if($url) { $rtn .= '</a>'; }
				
				$rtn .= '</' . $header_type . '>';
				
				$rtn .= get_the_excerpt();
				$rtn .= '</div>';
			}
	
			$rtn .= '</div><!-- #slide-' . get_the_ID() . ' -->';
			$rtn .= '</li>';
		}

		$rtn .= '</ul>';
		$rtn .= '</div><!-- close: #flexslider_hg_' . $slug . ' -->';
		$rtn .= '</div><!-- close: #flexslider_hg_' . $slug . '_wrapper -->';
		
		
		// INIT THE ROTATOR
		$rtn .= '<script>';
		$rtn .= " jQuery('#flexslider_hg_{$slug}').flexslider( ";
			
		if(isset($rotators[ $slug ]['options']) AND $rotators[ $slug ]['options'] != "") 
		{ 
			$rtn .= $rotators[ $slug ]['options'];
		}
				
		$rtn .= " ); ";
		$rtn .= '</script>';		
	}
	wp_reset_postdata();
	wp_reset_query();
	
	return $rtn;
}


// ADMIN META BOX
function flexslider_hg_create_slide_metaboxes() 
{
    add_meta_box( 'flexslider_hg_metabox_1', __( 'Slide Settings', 'flexslider_hg' ), 'flexslider_hg_metabox_1', 'slides', 'normal', 'default' );
}
function flexslider_hg_metabox_1() 
{
	global $post;	
    $rotators = flexslider_hg_rotators();

	$slide_link_url 	= get_post_meta( $post->ID, '_slide_link_url', true );
	$slider_ids			= flexslider_hg_break_piped_string( get_post_meta( $post->ID, '_slider_id', true ) ); 
	
?>
	
	<p>
		<span class="description"><?php echo _e( 'The URL this slide should link to:', 'flexslider_hg' ); ?></span><br>
		<input type="text" style="width: 90%;" name="slide_link_url" value="<?php echo esc_attr( $slide_link_url ); ?>" />
	</p>

	<p>
		<?php if($rotators) { ?>
		
			<?php _e('Attach to:', 'flexslider_hg'); ?> &nbsp; 
			
			<?php foreach( $rotators as $rotator => $size) { if( $rotator == "attachments") continue; ?>
				<input type="checkbox" name="slider_id[]" <?php echo in_array($rotator, $slider_ids) ? " CHECKED" : ""; ?> value="<?php echo $rotator ?>"/> <?php echo $rotator ?> &nbsp; &nbsp;
			<?php } ?>

		<?php } ?>
	</p>
	
	<?php 
}


// SAVE THE EXTRA GOODS FROM THE SLIDE
function flexslider_hg_save_meta( $post_id, $post )
{
	global $post;
	 
	// SAVE SLIDES POST DATA
	if( isset( $_POST ) && isset( $post->ID ) && get_post_type( $post->ID ) == "slides" ) 
	{
		if ( isset( $_POST['slide_link_url'] ) ) 
		{
			update_post_meta( $post_id, '_slide_link_url', strip_tags( $_POST['slide_link_url'] ) );
		}
		
		if ( isset( $_POST['slider_id'] ) ) 
		{
			if( is_array($_POST['slider_id']))
			{
				update_post_meta( $post_id, '_slider_id', flexslider_hg_make_piped_string($_POST['slider_id']) );
			}
			else
			{
				update_post_meta( $post_id, '_slider_id', strip_tags( $_POST['slider_id'] ) ); 
			}
		}
		else
		{
			update_post_meta( $post_id, '_slider_id', '' ); 
		}
		return;
	}
	
	// SAVE SLIDERS POST DATA
	if( isset( $_POST ) && isset( $post->ID ) && get_post_type( $post->ID ) == "sliders" ) 
	{
		if( isset($_POST['image_size']) ) 			update_post_meta( $post_id, '_image_size', strip_tags( $_POST['image_size'] ) );
		if( isset($_POST['heading_tag']) ) 			update_post_meta( $post_id, '_heading_tag', strip_tags( $_POST['heading_tag'] ) );
		if( isset($_POST['order_by']) ) 			update_post_meta( $post_id, '_order_by', strip_tags( $_POST['order_by'] ) );
		if( isset($_POST['order']) ) 				update_post_meta( $post_id, '_order', strip_tags( $_POST['order'] ) );
		if( isset($_POST['limit']) ) 				update_post_meta( $post_id, '_limit', (int) $_POST['limit'] );
													update_post_meta( $post_id, '_hide_slide_data', isset( $_POST['hide_slide_data'] ) ? 1 : 0 );
													update_post_meta( $post_id, '_randomize', isset( $_POST['randomize'] ) ? 1 : 0 );
		if( isset($_POST['slideshow_speed']) ) 		update_post_meta( $post_id, '_slideshow_speed', strip_tags( $_POST['slideshow_speed'] ) );
		if( isset($_POST['animation_speed']) ) 		update_post_meta( $post_id, '_animation_speed', strip_tags( $_POST['animation_speed'] ) );
		if( isset($_POST['animation']) ) 			update_post_meta( $post_id, '_animation', strip_tags( $_POST['animation'] ) );
		if( isset($_POST['animation_direction']) ) 	update_post_meta( $post_id, '_animation_direction', strip_tags( $_POST['animation_direction'] ) );
		if( isset($_POST['style']) ) 				update_post_meta( $post_id, '_style', strip_tags( $_POST['style'] ) );
		if( isset($_POST['corners']) ) 				update_post_meta( $post_id, '_corners', strip_tags( $_POST['corners'] ) );
		return;
	}
}


// ADMIN COLUMNS
function flexslider_hg_columns( $columns ) 
{
	$columns = array(
		'cb'       => '<input type="checkbox" />',
		'image'    => __( 'Image', 'flexslider_hg' ),
		'title'    => __( 'Title', 'flexslider_hg' ),
		'ID'       => __( 'Sliders', 'flexslider_hg' ),
		'order'    => __( 'Order', 'flexslider_hg' ),
		'link'     => __( 'Link', 'flexslider_hg' ),
		'date'     => __( 'Date', 'flexslider_hg' )
	);

	return $columns;
}

function flexslider_hg_add_columns( $column )
{
	global $post;
	$edit_link = get_edit_post_link( $post->ID );
	
	$slider_ids = flexslider_hg_break_piped_string( get_post_meta( $post->ID, "_slider_id", true ) );
	$slider_title_array = array();
	foreach($slider_ids as $slider_id) { $slider_title_array[] = $slider_id; }

	if ( $column == 'image' ) 	echo '<a href="' . $edit_link . '" title="' . $post->post_title . '">' . get_the_post_thumbnail( $post->ID, array( 60, 60 ), array( 'title' => trim( strip_tags(  $post->post_title ) ) ) ) . '</a>';
	if ( $column == 'order' ) 	echo '<a href="' . $edit_link . '">' . $post->menu_order . '</a>';
	if ( $column == 'ID' ) 		echo implode(", ", $slider_title_array);
	if ( $column == 'link' ) 	echo '<a href="' . get_post_meta( $post->ID, "_slide_link_url", true ) . '" target="_blank" >' . get_post_meta( $post->ID, "_slide_link_url", true ) . '</a>';		
}


function flexslider_hg_sliders_edit_columns($columns)
{
	return array(
	    "cb" => "<input type=\"checkbox\" />",
	    "title" => __("Slider Title", 'flexslider_hg'),
	    "slider_shortcode" => __("Shortcode", 'flexslider_hg')
	);  
}

function flexslider_hg_sliders_sliders_add_columns( $column )
{
	global $post;
	$edit_link = get_edit_post_link( $post->ID );

	if ( $column == 'slider_shortcode' ) 	echo '[flexslider slug="'.  $post->post_name .'"]';
}


// SLIDERS ADMIN META BOX
function flexslider_hg_create_slider_metaboxes() 
{
    add_meta_box( 'flexslider_hg_sliders_metabox', __( 'Slider Settings', 'flexslider_hg' ), 'flexslider_hg_sliders_metabox', 'sliders', 'normal', 'default' );
}

function flexslider_hg_sliders_metabox() 
{
	global $post;
	$current_image_size				= get_post_meta( $post->ID, '_image_size', true );
	$current_heading_tag			= get_post_meta( $post->ID, '_heading_tag', true );
	$current_order_by				= get_post_meta( $post->ID, '_order_by', true );
	$current_order					= get_post_meta( $post->ID, '_order', true );
	$current_hide_slide_data		= get_post_meta( $post->ID, '_hide_slide_data', true );
	$current_limit					= (int) get_post_meta( $post->ID, '_limit', true );
	$current_randomize				= (int) get_post_meta( $post->ID, '_randomize', true );
	$current_slideshow_speed		= get_post_meta( $post->ID, '_slideshow_speed', true );
	$current_animation_speed		= get_post_meta( $post->ID, '_animation_speed', true );
	$current_animation				= get_post_meta( $post->ID, '_animation', true );
	$current_animation_direction	= get_post_meta( $post->ID, '_animation_direction', true );
	$current_style					= get_post_meta( $post->ID, '_style', true );
	$current_corners				= get_post_meta( $post->ID, '_corners', true );
	
	
	// DEFAULTS
	if( !$current_heading_tag ) $current_heading_tag = "h2";
	if( !$current_slideshow_speed ) $current_slideshow_speed = 7;
	if( !$current_animation_speed ) $current_animation_speed = 0.5;
	if( !$current_corners ) $current_corners = "rounded";
	if( !$current_style ) $current_style = "default";
	
	
	// DATA SETS
	$orderbys = array( 'date', 'id', 'author', 'title', 'name', 'modified', 'menu_order' );
	$image_sizes = get_intermediate_image_sizes();
	$slider_styles = array( 'default', 'slim', 'bottomheavy', 'crossed' ); 
?>

	
	<style>
	
		.hg_slider_column { width: 46%; margin: 0 2%; float: left; }
		.slider-styles a { width: 25%; float: left; display: block; outline: none;}
		.slider-styles img { width: 86%; height: auto; border: solid 3px #fff; transition: all 0.25s; }
		.slider-styles a.selected img {	border: solid 3px #96d78b; } 
		
		/* 680 */
		@media only screen and (max-width: 680px) {
			.hg_slider_column { width: 100%; margin: 0 0 20px 0; float: none; }
		}
	</style>
	
	<div style="border-bottom: solid 1px #ccc; padding: 10px 2%;">

		<input type="checkbox" name="hide_slide_data" value="1" <?php if($current_hide_slide_data) echo " CHECKED"; ?> onchange="if(this.checked) { jQuery('#heading-tag-p').slideUp(); } else { jQuery('#heading-tag-p').slideDown(); }" />
		&nbsp; <?php _e('Hide Title/Excerpt', 'flexslider_hg'); ?>

		&nbsp; &nbsp; &nbsp;

		<input type="checkbox" name="randomize" value="1" <?php if($current_randomize) echo " CHECKED"; ?> onchange="if(this.checked) { jQuery('#order-direction-p, #orderby-p').slideUp(); } else { jQuery('#order-direction-p, #orderby-p').slideDown(); }" />
		&nbsp; <?php _e('Randomize Slides', 'flexslider_hg'); ?>

	</div>	

	<div class="hg_slider_column">
	
		<p>
			<select name="image_size">
				<?php foreach($image_sizes as $image_size) { ?>
				<option value="<?php echo $image_size ?>" <?php if($image_size == $current_image_size) echo " SELECTED"; ?>><?php echo $image_size ?></option>
				<?php } ?>
			</select>
			<?php _e('Image Size', 'flexslider_hg'); ?>
		</p>
		
		<p id="orderby-p" <?php if($current_randomize) echo "style='display:none;'"; ?>>
			<select name="order_by">
				<?php foreach($orderbys as $orderby) { ?>
				<option value="<?php echo $orderby ?>" <?php if($orderby == $current_order_by) echo " SELECTED"; ?>><?php echo $orderby ?></option>
				<?php } ?>
			</select>
			<?php _e('Order By', 'flexslider_hg'); ?>
		</p>
		
		<p id="order-direction-p" <?php if($current_randomize) echo "style='display:none;'"; ?>>
			<select name="order">
				<option value="ASC" <?php if("ASC" == $current_order) echo " SELECTED"; ?>><?php _e('Ascending', 'flexslider_hg'); ?></option>
				<option value="DESC" <?php if("DESC" == $current_order) echo " SELECTED"; ?>><?php _e('Decending', 'flexslider_hg'); ?></option>
			</select>
			<?php _e('Order Direction', 'flexslider_hg'); ?>
		</p>
		
		<p>
			<select name="animation" id="animation-select" onchange="if(this.value == 'fade') { jQuery('#animation-direction-p').slideUp(); } else { jQuery('#animation-direction-p').slideDown(); }">
				<option value="fade" <?php if("fade" == $current_animation) echo " SELECTED"; ?>><?php _e('Fade', 'flexslider_hg'); ?></option>
				<option value="slide" <?php if("slide" == $current_animation) echo " SELECTED"; ?>><?php _e('Slide', 'flexslider_hg'); ?></option>
			</select>
			<?php _e('Animation', 'flexslider_hg'); ?>
		</p>
		
		<p id="animation-direction-p" <?php if($current_animation == "fade") echo "style='display:none;'"; ?>>
			<select name="animation_direction">
				<option value="horizontal" <?php if("horizontal" == $current_animation_direction) echo " SELECTED"; ?>><?php _e('Horizontal', 'flexslider_hg'); ?></option>
				<option value="vertical" <?php if("vertical" == $current_animation_direction) echo " SELECTED"; ?>><?php _e('Vertical', 'flexslider_hg'); ?></option>
			</select>
			<?php _e('Animation Direction', 'flexslider_hg'); ?>
		</p>
	
	</div>
	
	<div class="hg_slider_column">
	
		<p>
			<input type="text" style="width: 45px; text-align: center;" name="slideshow_speed" value="<?php echo esc_attr( $current_slideshow_speed ); ?>" />
			<?php _e('Slideshow Speed (in seconds)', 'flexslider_hg'); ?>
		</p>
		
		<p>
			<input type="text" style="width: 45px; text-align: center;" name="animation_speed" value="<?php echo esc_attr( $current_animation_speed ); ?>" />
			<?php _e('Animation Speed (in seconds)', 'flexslider_hg'); ?>
		</p>
		
		<p id="heading-tag-p" <?php if($current_hide_slide_data) echo "style='display:none;'"; ?>>
			<input type="text" style="width: 45px; text-align: center;" name="heading_tag" value="<?php echo esc_attr( $current_heading_tag ); ?>" />
			<?php _e('Heading Tag', 'flexslider_hg'); ?>
		</p>
		
		<p>
			<input type="text" style="width: 45px; text-align: center;" name="limit" value="<?php echo esc_attr( $current_limit ); ?>" />
			<?php _e('Max Number of Slides', 'flexslider_hg'); ?> <small>(<?php _e('0 for all slides', 'flexslider_hg'); ?>)</small>
		</p>
	
	</div>
	
	<div class="clear" style="padding: 0 2%;">
	
		<p style="border-top: solid 1px #ccc; border-bottom: solid 1px #ccc; padding: 10px 0;">
			<input type="radio" name="corners" value="rounded" <?php if($current_corners == "rounded") echo " CHECKED"; ?> />
			<?php _e('Rounded Corners', 'flexslider_hg'); ?>
			&nbsp; &nbsp;
			<input type="radio" name="corners" value="square" <?php if($current_corners == "square") echo " CHECKED"; ?> />
			<?php _e('Square Corners', 'flexslider_hg'); ?>
		</p>
	
		<p class="slider-styles">
			<?php foreach( $slider_styles as $slider_style ) { ?>
			<a id="flexslider-hg-icon-<?php echo $slider_style; ?>" data-slug="<?php echo $slider_style; ?>" href="javascript:;"<?php if($current_style == $slider_style) echo " class=\"selected\""; ?>><img src="<?php echo FLEXSLIDER_HG_URI; ?>images/icon-<?php echo $slider_style; ?>.gif" alt="<?php echo $slider_style . " " . __('Style', 'flexslider_hg'); ?>" /></a>
			<?php } ?>
		</p>

		<script>
			jQuery(document).ready(function($) {
			
				$(".slider-styles a").click(function() 
				{
					$(".slider-styles a").removeClass("selected");
					$(this).addClass("selected");
					$("#rotator_style").val( $(this).data('slug') );				
				});			
			});
		</script>
		
		<input id="rotator_style" type="text" name="style" value="<?php echo $current_style; ?>" />
		<br /><small><?php _e('or change this to your custom classname', 'flexslider_hg'); ?></small>
		
		<div class="clear"></div>

	</div>
	
	<?php
}

/* TITLE INPUT FOR SLIDERS */
function register_hgslider_form_title( $title )
{
     $screen = get_current_screen();
     if  ( $screen->post_type == 'sliders' ) 
     {
          return __('Enter Slider Name Here', 'flexslider_hg');
     }
}


// SHORTCODE
function flexslider_hg_shortcode($atts, $content = null)
{
	$slug = isset($atts['slug']) ? $atts['slug'] : "attachments";
	if(!$slug) { return apply_filters( 'flexslider_hg_empty_shortcode', "<p>Flexslider: Please include a 'slug' parameter. [flexslider slug=homepage]</p>" ); }
	return show_flexslider_rotator( $slug );
}


// SET SETTINGS LINK ON PLUGIN PAGE
function flexslider_hg_plugin_action_links( $links, $file ) 
{
	$donate_link 		= '<a href="https://halgatewood.com/donate" target="_blank">' . esc_html__( 'Donate', 'flexslider_hg' ) . '</a>';
	if ( $file == 'flexslider-hg/flexslider-hg.php' )
	{
		array_unshift( $links, $donate_link );
	}
	return $links;
}
add_filter( 'plugin_action_links', 'flexslider_hg_plugin_action_links', 10, 2 );