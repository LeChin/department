<?php

class JPIBFI_Client {

	protected static $instance = null;

	private function __construct() {
		$this->setup_constants();
		add_action( 'wp_enqueue_scripts', array( $this, 'add_plugin_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'add_plugin_styles' ) );
		add_action( 'wp_head', array( $this, 'print_header_style' ) );
		add_filter( "the_content", array( $this, 'prepare_the_content' ) );
		add_filter( "the_excerpt", array( $this, 'prepare_the_content' ) );
	}

	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	private function setup_constants() {
		if ( ! defined( 'JPIBFI_IMAGE_WIDTH' ) )
			define( 'JPIBFI_IMAGE_WIDTH', 65 );

		if ( ! defined( 'JPIBFI_IMAGE_HEIGHT' ) )
			define( 'JPIBFI_IMAGE_HEIGHT', 41 );
	}

	//Adds all necessary styles
	public function add_plugin_styles() {
		if ( ! ( JPIBFI_Client_Utilities::add_jpibfi() ) )
			return;

		wp_register_style( 'jquery-pin-it-button-style', JPIBFI_STYLE_URL . 'style.css', array(), JPIBFI_VERSION . JPIBFI_VERSION_MINOR, 'all' );
		wp_enqueue_style( 'jquery-pin-it-button-style' );
	}

	//Adds all necessary scripts
	public function add_plugin_scripts() {
		global $jpibfi_selection_options;
		global $jpibfi_visual_options;
		global $jpibfi_adanced_options;

		if ( ! ( JPIBFI_Client_Utilities::add_jpibfi() ) )
			return;
		//TODO minify
		wp_enqueue_script( 'jquery-pin-it-button-script', JPIBFI_SCRIPT_URL . 'script.min.js', array( 'jquery' ), JPIBFI_VERSION . JPIBFI_VERSION_MINOR, false );

		$use_custom_image = isset( $jpibfi_visual_options[ 'use_custom_image' ] ) && $jpibfi_visual_options[ 'use_custom_image' ] == "1";

		$parameters_array = array(
			'imageSelector' 		=> $jpibfi_selection_options['image_selector'],
			'disabledClasses' 	=> $jpibfi_selection_options['disabled_classes'],
			'enabledClasses' 	=> $jpibfi_selection_options['enabled_classes'],
			'descriptionOption' => $jpibfi_visual_options['description_option'],
			'usePostUrl' 			=> isset ( $jpibfi_visual_options['use_post_url'] ) ? $jpibfi_visual_options['use_post_url'] : '0',
			'minImageHeight'	=> $jpibfi_selection_options['min_image_height'],
			'minImageWidth'		=> $jpibfi_selection_options['min_image_width'],
			'siteTitle'				=> get_bloginfo( 'name', 'display' ),
			'mode'							=> $jpibfi_visual_options[ 'mode' ],
			'buttonPosition'		=> $jpibfi_visual_options[ 'button_position' ],
			'debug'							=> isset( $jpibfi_adanced_options[ 'debug'] ) ? $jpibfi_adanced_options[ 'debug'] : '0',
			'containerSelector' => $jpibfi_adanced_options[ 'container_selector'],
			'pinImageHeight' 	=> $use_custom_image ? $jpibfi_visual_options['custom_image_height'] : JPIBFI_IMAGE_HEIGHT,
			'pinImageWidth'		=> $use_custom_image ? $jpibfi_visual_options['custom_image_width'] : JPIBFI_IMAGE_WIDTH,
			'buttonMarginTop'	=> $jpibfi_visual_options[ 'button_margin_top' ],
			'buttonMarginBottom'	=> $jpibfi_visual_options[ 'button_margin_bottom' ],
			'buttonMarginLeft'=> $jpibfi_visual_options[ 'button_margin_left' ],
			'buttonMarginRight'	=> $jpibfi_visual_options[ 'button_margin_right' ]
		);

		wp_localize_script( 'jquery-pin-it-button-script', 'jpibfi_options', apply_filters( 'jpibfi_javascript_parameters', $parameters_array ) );
	}

	public function print_header_style() {
		global $jpibfi_visual_options;

		if ( ! ( JPIBFI_Client_Utilities::add_jpibfi() ) )
			return;

		$use_custom_image = isset( $jpibfi_visual_options[ 'use_custom_image' ] ) && $jpibfi_visual_options[ 'use_custom_image' ] == "1";

		$width  = $use_custom_image ? $jpibfi_visual_options['custom_image_width'] : JPIBFI_IMAGE_WIDTH;
		$height = $use_custom_image ? $jpibfi_visual_options['custom_image_height'] : JPIBFI_IMAGE_HEIGHT;

		if ( isset( $jpibfi_visual_options[ 'retina_friendly' ] ) && $jpibfi_visual_options[ 'retina_friendly' ] == '1' ){
			$width = floor( $width / 2 );
			$height = floor ( $height / 2 );
		}

		$url = $use_custom_image ? $jpibfi_visual_options['custom_image_url'] : JPIBFI_PLUGIN_URL . 'images/pinit-button.png';

		?>
		<!--[if lt IE 9]>
		<style type="text/css">
			.pinit-overlay {
				background-image: url( '<?php echo JPIBFI_PLUGIN_URL . 'images/transparency_0.png'; ?>' ) !important;
			}
		</style>
		<![endif]-->

		<style type="text/css">
			a.pinit-button {
				width: <?php echo $width; ?>px !important;
				height: <?php echo $height; ?>px !important;
				background: transparent url('<?php echo $url; ?>') no-repeat 0 0 !important;
				background-size: <?php echo $width; ?>px <?php echo $height; ?>px !important
			}

			a.pinit-button.pinit-top-left {
				<?php printf('margin: %dpx 0 0 %dpx', $jpibfi_visual_options['button_margin_top'], $jpibfi_visual_options['button_margin_left']); ?>
			}

			a.pinit-button.pinit-top-right {
				<?php printf('margin: %dpx %dpx 0 0', $jpibfi_visual_options['button_margin_top'], $jpibfi_visual_options['button_margin_right']); ?>
			}

			a.pinit-button.pinit-bottom-left {
				<?php printf('margin: 0 0 %dpx  %dpx', $jpibfi_visual_options['button_margin_bottom'], $jpibfi_visual_options['button_margin_left']); ?>
			}

			a.pinit-button.pinit-bottom-right {
				<?php printf('margin: 0 %dpx  %dpx 0', $jpibfi_visual_options['button_margin_right'], $jpibfi_visual_options['button_margin_bottom']); ?>
			}

			img.pinit-hover {
				opacity: <?php echo (1 - $jpibfi_visual_options['transparency_value']); ?> !important;
				filter:alpha(opacity=<?php echo (1 - $jpibfi_visual_options['transparency_value']) * 100; ?>) !important; /* For IE8 and earlier */
			}
		</style>
	<?php
	}

	/*
 * Adds a hidden field with url and and description of the pin that's used when user uses "Link to individual page"
 * Thanks go to brocheafoin, who added most of the code that handles creating description
 */
	public function prepare_the_content( $content ) {
		if ( ! JPIBFI_Client_Utilities::add_jpibfi() )
			return $content;

		global $post;
		global $jpibfi_visual_options;

		$add_attributes = false == is_singular() && isset( $jpibfi_visual_options[ 'use_post_url' ] ) && '1' == $jpibfi_visual_options[ 'use_post_url' ];

		$attributes_html = '';

		//if we need to add additional attributes to handle use_post_url setting
		if ( $add_attributes ){
			//if page description should be used as pin description and an excerpt for the post exists
			if ( has_excerpt( $post->ID ) && 2 == $jpibfi_visual_options[ 'description_option' ] )
				$description = wp_kses( $post->post_excerpt, array() );
			else
				$description = get_the_title($post->ID);

			$attributes_html .= 'data-jpibfi-url="' . get_permalink( $post->ID ) . '" ' ;
			$attributes_html .= 'data-jpibfi-description ="' . esc_attr( $description ) . '" ';
		}

		$input_html = '<input class="jpibfi" type="hidden" ' . $attributes_html . '>';
		$content = $input_html . $content;

		$add_image_descriptions = '5' == $jpibfi_visual_options[ 'description_option' ];

		//if we need to add data-jpibfi-description to each image
		if ( $add_image_descriptions ){
			$content = $this->add_description_attribute_to_images( $content );
		}

		return $content;
	}

	/* PRIVATE METHODS */

	/*
 * Adds data-jpibfi-description attribute to each image that is added through media library. The value is the "Description"  of the image from media library.
 * This piece of code uses a lot of code from the Photo Protect http://wordpress.org/plugins/photo-protect/ plugin
 */
	private function add_description_attribute_to_images( $content ) {

		$imgPattern = '/<img[^>]*>/i';
		$attrPattern = '/ ([\w]+)[ ]*=[ ]*([\"\'])(.*?)\2/i';

		preg_match_all($imgPattern, $content, $images, PREG_SET_ORDER);

		foreach ($images as $img) {

			preg_match_all($attrPattern, $img[0], $attributes, PREG_SET_ORDER);

			$newImg = '<img';
			$src = '';
			$id = '';

			foreach ($attributes as $att) {
				$full = $att[0];
				$name = $att[1];
				$value = $att[3];

				$newImg .= $full;

				if ('class' == $name ) {
					$id = JPIBFI_Client_Utilities::get_post_id_from_image_classes( $value );
				}	else if ( 'src' == $name ) {
					$src = $value;
				}
			}

			$description = JPIBFI_Client_Utilities::get_image_description( $id, $src );
			$newImg .= ' data-jpibfi-description="' . esc_attr( $description ) . '" />';
			$content = str_replace($img[0], $newImg, $content);
		}

		return $content;
	}
}

add_action( 'plugins_loaded', array( 'JPIBFI_Client', 'get_instance' ) );