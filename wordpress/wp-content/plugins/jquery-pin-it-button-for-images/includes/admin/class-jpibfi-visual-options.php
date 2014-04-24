<?php

class JPIBFI_Visual_Options {
	protected static $instance = null;

	protected $admin_visual_options = null;

	private function __construct() {
		add_action( 'admin_init', array( $this, 'initialize_visual_options' ) );

	}

	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	private function get_visual_options() {
		global $jpibfi_visual_options;

		if ( null == $this->admin_visual_options ) {
			//cumbersome, but works in WP 3.3
			$options = get_option( JPIBFI_VISUAL_OPTIONS . '_errors' );
			$this->admin_visual_options = false == $options ? $jpibfi_visual_options : $options;
		}

		return $this->admin_visual_options;
	}

	/* Default values for visual options section */
	public static function default_visual_options() {

		$defaults = array(
			'transparency_value'  => '0.5',
			'description_option'  => '1',
			'use_custom_image'    => '0',
			'custom_image_url'    => '',
			'custom_image_height' => '0',
			'custom_image_width'  => '0',
			'use_post_url'        => '0',
			'button_position'			=> '0',
			'mode'								=> 'static',
			'button_margin_top'		=> '20',
			'button_margin_right'	=> '20',
			'button_margin_bottom'=> '20',
			'button_margin_left'	=> '20',
			'retina_friendly'     => '0'
		);

		return apply_filters( 'jpibfi_default_visual_options', $defaults );
	}

	/* Defines visual options section and defines all required fields */
	public function initialize_visual_options() {

		// First, we register a section.
		add_settings_section(
			'visual_options_section',			// ID used to identify this section and with which to register options
			__( 'Visual', 'jpibfi' ),		// Title to be displayed on the administration page
			array( $this, 'visual_options_callback' ),	// Callback used to render the description of the section
			'jpibfi_visual_options'		// Page on which to add this section of options
		);

		//Then add all necessary fields to the section
		add_settings_field(
			'mode',
			__( 'Mode', 'jpibfi' ),
			array( $this, 'mode_option_callback' ),
			'jpibfi_visual_options',
			'visual_options_section',
			array(
				__( 'Static mode adds a layer on the top of the image that restricts image download, but works on websites that protect images download. Dynamic mode doesn\'t add that layer and allows image download. If you\'re experiencing issues with static mode, try using dynamic mode.', 'jpibfi' ),
			)
		);

		add_settings_field(
			'description_option',
			__( 'Description source', 'jpibfi' ),
			array( $this, 'description_option_callback' ),
			'jpibfi_visual_options',
			'visual_options_section',
			array(
				__( 'From where the Pinterest message should be taken. Please note that "Image description" works properly only for images that were added to your Media Library.', 'jpibfi' ),
			)
		);

		add_settings_field(
			'use_post_url',
			__( 'Linked page', 'jpibfi' ),
			array( $this, 'use_post_url_callback' ),
			'jpibfi_visual_options',
			'visual_options_section',
			array(
				__( 'When checked, the link on Pinterest will always point to the individual page with the image and title of this individual page will be used if you\'ve selected Title as the description source, even when the image was pinned on an archive page, category page or homepage. If false, the link will point to the URL the user is currently on.', 'jpibfi' ),
			)
		);

		add_settings_field(
			'transparency_value',
			__( 'Transparency value', 'jpibfi' ),
			array( $this, 'transparency_value_callback' ),
			'jpibfi_visual_options',
			'visual_options_section',
			array(
				__( 'This setting sets the transparency of the image.', 'jpibfi' ),
			)
		);

		add_settings_field(
			'custom_pin_it_button',
			__( 'Custom "Pin It" button', 'jpibfi' ),
			array( $this, 'custom_pin_it_button_callback' ),
			'jpibfi_visual_options',
			'visual_options_section',
			array(
				__( 'Check the <b>Use custom image</b> checkbox, specify image\'s URL, height and width to use your own Pinterest button design. You can just upload an image using Wordpress media library if you wish.', 'jpibfi' ),
			)
		);

		add_settings_field(
			'pin_it_button_position',
			__( '"Pin it" button position', 'jpibfi' ),
			array( $this, 'pin_it_button_position_callback' ),
			'jpibfi_visual_options',
			'visual_options_section',
			array(
				__( 'Where the "Pin it" button should appear on the image.', 'jpibfi' ),
			)
		);

		add_settings_field(
			'pin_it_button_margins',
			__( '"Pin it" button margins', 'jpibfi' ),
			array( $this, 'pin_it_button_margins_callback' ),
			'jpibfi_visual_options',
			'visual_options_section',
			array(
				sprintf( __( 'Margins are used to adjust the position of the "Pin it" button, but not all margins are used on all button positions. Here is an example. If you\'re using the "%s" position, the button\'s position will be affected only by top and left margins. Bottom and right margins affect "%s" position, etc. The "%s" position does not use any margins at all.', 'jpibfi' ),
					__( 'Top left', 'jpibfi' ),
					__( 'Bottom right', 'jpibfi' ),
					__( 'Middle', 'jpibfi' )
				),
			)
		);

		add_settings_field(
			'retina_friendly',
			__( 'Retina friendly', 'jpibfi' ),
			array( $this, 'retina_friendly_callback' ),
			'jpibfi_visual_options',
			'visual_options_section',
			array(
				__( 'Please note that checking this option will result in rendering the "Pin it" button half of its normal size (if you use a 80x60 image, the button will be 40x30). When uploading a custom "Pin it" button (the default one is too small), please make sure both width and height are even numbers (i.e. divisible by two) when using this option.', 'jpibfi' ),
			)
		);

		register_setting(
			'jpibfi_visual_options',
			'jpibfi_visual_options',
			array( $this, 'sanitize_visual_options' )
		);

	}

	public function visual_options_callback() {
		echo '<p>' . __('How it should look like', 'jpibfi') . '</p>';
	}

	public function mode_option_callback( $args ) {
		$options = $this->get_visual_options();
		$mode = $options[ 'mode' ];

		?>

		<select id="mode" name="jpibfi_visual_options[mode]">
			<option value="static" <?php selected ( "static", $mode ); ?>><?php _e( 'Static', 'jpibfi' ); ?></option>
			<option value="dynamic" <?php selected ( "dynamic", $mode ); ?>><?php _e( 'Dynamic', 'jpibfi' ); ?></option>
		</select>

		<?php
		echo JPIBFI_Admin_Utilities::create_description( $args[0] );
	}

	public function description_option_callback( $args ) {
		$options = $this->get_visual_options();


		$description_option = $options[ 'description_option' ];
		?>

		<select id="description_option" name="jpibfi_visual_options[description_option]">
			<option value="1" <?php selected ( "1", $description_option ); ?>><?php _e( 'Page title', 'jpibfi' ); ?></option>
			<option value="2" <?php selected ( "2", $description_option ); ?>><?php _e( 'Page description', 'jpibfi' ); ?></option>
			<option value="3" <?php selected ( "3", $description_option ); ?>><?php _e( 'Picture title or (if title not available) alt attribute', 'jpibfi' ); ?></option>
			<option value="4" <?php selected ( "4", $description_option ); ?>><?php _e( 'Site title (Settings->General)', 'jpibfi' ); ?></option>
			<option value="5" <?php selected ( "5", $description_option ); ?>><?php _e( 'Image description', 'jpibfi' ); ?></option>
		</select>

		<?php
		echo JPIBFI_Admin_Utilities::create_description( $args[0] );
	}

	public function use_post_url_callback( $args ) {

		$options = $this->get_visual_options();
		$use_post_url = JPIBFI_Admin_Utilities::exists_and_equal_to( $options, 'use_post_url', '1' );

		echo '<input type="checkbox" id="use_post_url" name="jpibfi_visual_options[use_post_url]" value="1" ' . checked( true, $use_post_url, false ) . '>';
		echo '<label for="use_post_url">' . __( 'Always link to individual post page', 'jpibfi' ) . '</label>';

		echo JPIBFI_Admin_Utilities::create_description( $args[0] );
	}

	public function transparency_value_callback( $args ) {
		$options = $this->get_visual_options();

		$transparency_value = $options[ 'transparency_value' ];

		echo '<label for="transparency_value">' . sprintf ( __('Choose transparency (between %.02f and %.02f)', 'jpibfi'), '0.00', '1.00' ) . '</label><br/>';
		echo '<input type="number" min="0" max="1" step="0.01" id="transparency_value" name="jpibfi_visual_options[transparency_value]"' .
				'value="' . $transparency_value . '" class="small-text" >';
		echo JPIBFI_Admin_Utilities::create_description( $args[0] );
		echo JPIBFI_Admin_Utilities::create_errors( 'transparency_value' );
	}

	public function custom_pin_it_button_callback( $args ) {
		$options = $this->get_visual_options();

		$use_custom_image = JPIBFI_Admin_Utilities::exists_and_equal_to( $options, 'use_custom_image', '1' );
		$custom_image_url = $options[ 'custom_image_url' ];
		$custom_image_height = $options[ 'custom_image_height' ];
		$custom_image_width = $options[ 'custom_image_width' ];

		?>
		<p>
			<input type="checkbox" id="use_custom_image" name="jpibfi_visual_options[use_custom_image]" value="1" <?php checked( true, $use_custom_image ); ?> >
			<label class="chbox-label" for="use_custom_image"><?php _e( 'Use custom image', 'jpibfi' ); ?></label>
		</p>

		<button id=upload-image><?php _e( 'Upload an image using media library','jpibfi' ); ?></button><br />

		<p>
			<label for="custom_image_url"><?php _e( 'URL address of the image', 'jpibfi' ); ?></label>
			<input type="url" id="custom_image_url" name="jpibfi_visual_options[custom_image_url]" value="<?php echo $custom_image_url; ?>">
		</p>

		<p>
			<label for="custom_image_height"><?php _e( 'Height', 'jpibfi' ); ?></label>
			<input type="number" min="0" step="1" id="custom_image_height" name="jpibfi_visual_options[custom_image_height]" value="<?php echo $custom_image_height; ?>"
						 class="small-text" /> px
			<?php echo JPIBFI_Admin_Utilities::create_errors( 'custom_image_height' ); ?>
		</p>

		<p>
			<label for="custom_image_width"><?php _e( 'Width', 'jpibfi' ); ?></label>
			<input type="number" min="0" step="1" id="custom_image_width" name="jpibfi_visual_options[custom_image_width]" value="<?php echo $custom_image_width; ?>"
						 class="small-text"  /> px
			<?php echo JPIBFI_Admin_Utilities::create_errors( 'custom_image_width' ); ?>
		</p>

		<p>
			<b><?php _e( 'Custom Pin It button preview', 'jpibfi' ); ?></b><br/>
		<span id="custom_button_preview" style="width: <?php echo $custom_image_width; ?>px; height: <?php echo $custom_image_height; ?>px; background-image: url('<?php echo $custom_image_url; ?>');">
			Preview
		</span><br/>
			<button id="refresh_custom_button_preview"><?php _e( 'Refresh preview', 'jpibfi' ); ?></button>
		</p>

		<?php

		echo JPIBFI_Admin_Utilities::create_description( $args[0] );
	}

	public function pin_it_button_position_callback( $args ) {
		$options = $this->get_visual_options();

		$jpibfi_button_dropdown = array(
			__( 'Top left', 'jpibfi' ),
			__( 'Top right', 'jpibfi' ),
			__( 'Bottom left', 'jpibfi' ),
			__( 'Bottom right', 'jpibfi' ),
			__( 'Middle', 'jpibfi' )
		);

		$button_position = $options[ 'button_position' ];

		?>

		<select name="jpibfi_visual_options[button_position]" id="button_position">
			<?php for( $i = 0; $i < count( $jpibfi_button_dropdown ); $i++) { ?>
				<option value="<?php echo $i; ?>" <?php selected( $i, $button_position ); ?>><?php echo $jpibfi_button_dropdown[ $i ]; ?></option>
			<?php } ?>
		</select><br/>

		<?php
		echo JPIBFI_Admin_Utilities::create_description( $args[0] );
	}

	public function pin_it_button_margins_callback( $args ) {
		$options = $this->get_visual_options();
		?>
		<label for="button_margin_top"><?php _e('Top', 'jpibfi'); ?></label>
		<input type="number" min="-1000" max="1000" step="1" id="button_margin_top" name="jpibfi_visual_options[button_margin_top]" value="<?php echo $options[ 'button_margin_top' ]; ?>" class="small-text" >px<br/>
		<label for="button_margin_bottom"><?php _e('Bottom', 'jpibfi'); ?></label>
		<input type="number" min="-1000" max="1000" step="1" id="button_margin_bottom" name="jpibfi_visual_options[button_margin_bottom]" value="<?php echo $options[ 'button_margin_bottom' ]; ?>" class="small-text" >px<br/>
		<label for="button_margin_left"><?php _e('Left', 'jpibfi'); ?></label>
		<input type="number" min="-1000" max="1000" step="1" id="button_margin_left" name="jpibfi_visual_options[button_margin_left]" value="<?php echo $options[ 'button_margin_left' ]; ?>" class="small-text" >px<br/>
		<label for="button_margin_right"><?php _e('Right', 'jpibfi'); ?></label>
		<input type="number" min="-1000" max="1000" step="1" id="button_margin_right" name="jpibfi_visual_options[button_margin_right]" value="<?php echo $options[ 'button_margin_right' ]; ?>" class="small-text" >px<br/>

		<?php
		echo JPIBFI_Admin_Utilities::create_description( $args[0] );
	}

	public function retina_friendly_callback( $args ) {

		$options = $this->get_visual_options();
		$retina_friendly = JPIBFI_Admin_Utilities::exists_and_equal_to( $options, 'retina_friendly', '1' );

		echo '<input type="checkbox" id="retina_friendly" name="jpibfi_visual_options[retina_friendly]" value="1" ' . checked( true, $retina_friendly, false ) . '>';
		echo '<label for="retina_friendly">' . __( 'Optimize for high pixel density displays', 'jpibfi' ) . '</label>';

		echo JPIBFI_Admin_Utilities::create_description( $args[0] );
	}

	public function sanitize_visual_options( $input ) {
		global $jpibfi_visual_options;

		foreach( $input as $key => $value ) {

			switch($key) {
				case 'transparency_value':
					if ( !is_numeric( $input[ $key ] ) || ( $input[ $key ] < 0.0 ) || ( $input[ $key ] > 1.0 ) ) {

						add_settings_error(
							$key,
							esc_attr( 'settings_updated' ),
							sprintf( __('Transparency value must be a number between %.02d and %.02f.', 'jpibfi'), '0.00', '1.00' )
						);
					}
					break;
				case 'custom_image_height':
				case 'custom_image_width':
					$name = "";
					if ( 'custom_image_height' == $key )
						$name = __('Custom image height', 'jpibfi' );
					else if ( 'custom_image_width' == $key )
						$name = __('Custom image width', 'jpibfi' );

					if ( '' != $value && ( !is_numeric( $value ) || $value < 0 ) ) {
						add_settings_error(
							$key,
							esc_attr( 'settings_updated' ),
								$name . ' - ' . sprintf ( __('value must be a number greater or equal to %d.', 'jpibfi'), '0' )
						);
					}
					break;
			}
		}

		$errors = get_settings_errors();

		if ( count( $errors ) > 0 ) {

			update_option( JPIBFI_VISUAL_OPTIONS . '_errors', $input );
			return $jpibfi_visual_options;

		} else {

			delete_option( JPIBFI_VISUAL_OPTIONS . '_errors' );
			return $input;

		}
	}
}

add_action( 'plugins_loaded', array( 'JPIBFI_Visual_Options', 'get_instance' ) );