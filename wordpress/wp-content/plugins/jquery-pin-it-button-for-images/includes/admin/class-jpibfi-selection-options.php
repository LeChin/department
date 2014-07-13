<?php

class JPIBFI_Selection_Options {

	protected static $instance = null;

	protected $admin_selection_options = null;

	private function __construct() {
		add_action( 'admin_init', array( $this, 'initialize_selection_options' ) );
	}

	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/* Default values for selection options section */
	public static function default_selection_options() {
		$defaults = array(
			'image_selector'      => '.jpibfi_container img',
			'disabled_classes'    => 'nopin;wp-smiley',
			'enabled_classes'     => '',
			'min_image_height'		=> '0',
			'min_image_width'			=> '0',
			'show_on_home'     		=> '1',
			'show_on_single'   		=> '1',
			'show_on_page'    		=> '1',
			'show_on_category' 		=> '1',
			'show_on_blog'				=> '1'
		);

		return apply_filters('jpibfi_default_selection_options', $defaults);
	}

	/*
	 * Defines selection options section and adds all required fields
	 */
	public function initialize_selection_options() {

		// First, we register a section.
		add_settings_section(
			'selection_options_section',			// ID used to identify this section and with which to register options
			__( 'Selection', 'jpibfi' ),		// Title to be displayed on the administration page
			array( $this, 'selection_options_callback' ),	// Callback used to render the description of the section
			'jpibfi_selection_options'		// Page on which to add this section of options
		);

		//lThen add all necessary fields to the section
		add_settings_field(
			'image_selector',						// ID used to identify the field throughout the plugin
			__( 'Image selector', 'jpibfi' ),							// The label to the left of the option interface element
			array( $this, 'image_selector_callback'),	// The name of the function responsible for rendering the option interface
			'jpibfi_selection_options',	// The page on which this option will be displayed
			'selection_options_section',			// The name of the section to which this field belongs
			array(								// The array of arguments to pass to the callback. In this case, just a description.
				sprintf ( __( 'jQuery selector for all the images that should have the "Pin it" button. Set the value to %s if you want the "Pin it" button to appear only on images in content or %s to appear on all images on site (including sidebar, header and footer). If you know a thing or two about jQuery, you might use your own selector. %sClick here%s to read about jQuery selectors.', 'jpibfi' ),
					'<a href="#" class="jpibfi_selector_option">div.jpibfi_container img</a>',
					'<a href="#" class="jpibfi_selector_option">img</a>',
					'<a href="http://api.jquery.com/category/selectors/" target="_blank">',
					'</a>'
				)
			)
		);

		add_settings_field(
			'disabled_classes',
			__( 'Disabled classes', 'jpibfi' ),
			array( $this, 'disabled_classes_callback' ),
			'jpibfi_selection_options',
			'selection_options_section',
			array(
				__( 'Pictures with these CSS classes won\'t show the "Pin it" button. Please separate multiple classes with semicolons. Spaces are not accepted.', 'jpibfi' ),
			)
		);

		add_settings_field(
			'enabled_classes',
			__( 'Enabled classes', 'jpibfi' ),
			array( $this, 'enabled_classes_callback' ),
			'jpibfi_selection_options',
			'selection_options_section',
			array(
				__( 'Only pictures with these CSS classes will show the "Pin it" button. Please separate multiple classes with semicolons. If this field is empty, images with any (besides disabled ones) classes will show the Pin It button.', 'jpibfi' ),
			)
		);

		add_settings_field(
			'show_on_field',
			__( 'On which pages the "Pin it" button should be shown', 'jpibfi' ),
			array( $this, 'show_on_field_callback' ),
			'jpibfi_selection_options',
			'selection_options_section',
			array(
				__( 'Check on which pages you want the Pinterest button to show up.', 'jpibfi' ),
			)
		);

		add_settings_field(
			'min_image',
			__( 'Minimum resolution that should trigger the "Pin it" button to show up', 'jpibfi' ),
			array( $this, 'min_image_callback' ),
			'jpibfi_selection_options',
			'selection_options_section',
			array(
				__( 'If you\'d like the "Pin it" button to not show up on small images (e.g. social media icons), just set the appropriate values above. The default values cause the "Pin it" button to show on every eligible image.', 'jpibfi' ),
			)
		);

		register_setting(
			'jpibfi_selection_options',
			'jpibfi_selection_options',
			array( $this, 'sanitize_selection_options' )
		);
	}

	public function selection_options_callback() {
		echo '<p>' . __('Which images can be pinned', 'jpibfi') . '</p>';
	}

	public function image_selector_callback( $args ) {

		$options = $this->get_selection_options();

		$selector = esc_attr( $options['image_selector'] );

		echo '<input type="text" id="image_selector" name="jpibfi_selection_options[image_selector]" value="' . $selector . '"/>';
		echo JPIBFI_Admin_Utilities::create_description( $args[0] );
	}

	public function disabled_classes_callback( $args ){

		$options = $this->get_selection_options();
		$value = esc_attr( $options[ 'disabled_classes' ] );

		?>
		<input type="hidden" name="jpibfi_selection_options[disabled_classes]" value="{{ disabledClassesFormatted }}" ng-init="initDisabledClasses('<?php echo $value; ?>')">
		<span ng-hide="disabledClasses.length > 0">
		<?php echo JPIBFI_Admin_Utilities::create_description( __( 'No classes added.', 'jpibfi' ) ); ?>
	</span>
		<ul class="jpibfi-classes-list" ng-hide="disabledClasses.length == 0">
			<li ng-repeat="class in disabledClasses">
				<a ng-click="deleteDisabledClass(class)">X</a><span>{{ class }}</span>
			</li>
		</ul>
		<div>
			<div>
				<label for="disabledClass" ><?php _e( 'Class name', 'jpibfi' ); ?></label>
				<input id="disabledClass" type="text" ng-model="disabledClass">
				<button type="button" ng-click="addDisabledClass(disabledClass)"><?php _e( 'Add to list', 'jpibfi' ); ?></button>
			</div>
		</div>

		<?php

		echo JPIBFI_Admin_Utilities::create_description( $args[0] );
		echo JPIBFI_Admin_Utilities::create_errors( 'disabled_classes' );
	}

	public function enabled_classes_callback( $args ){

		$options = $this->get_selection_options();
		$value = esc_attr( $options[ 'enabled_classes' ] );

		?>
		<input type="hidden" name="jpibfi_selection_options[enabled_classes]" value="{{ enabledClassesFormatted }}" ng-init="initEnabledClasses('<?php echo $value; ?>')">
		<span ng-hide="enabledClasses.length > 0">
		<?php echo JPIBFI_Admin_Utilities::create_description( __( 'No classes added.', 'jpibfi' ) ); ?>
	</span>
		<ul class="jpibfi-classes-list" ng-hide="enabledClasses.length == 0">
			<li ng-repeat="class in enabledClasses">
				<a ng-click="deleteEnabledClass(class)">X</a><span>{{ class }}</span>
			</li>
		</ul>
		<div>
			<div>
				<label for="enabledClass" ><?php _e( 'Class name', 'jpibfi' ); ?></label>
				<input id="enabledClass" type="text" ng-model="enabledClass">
				<button type="button" ng-click="addEnabledClass(enabledClass)"><?php _e( 'Add to list', 'jpibfi' ); ?></button>
			</div>
		</div>

		<?php

		echo JPIBFI_Admin_Utilities::create_description( $args[0] );
		echo JPIBFI_Admin_Utilities::create_errors( 'enabled_classes' );
	}

	public function show_on_field_callback( $args ) {
		$options = $this->get_selection_options();

		$show_on_home = JPIBFI_Admin_Utilities::exists_and_equal_to( $options, 'show_on_home', '1' );
		$show_on_page = JPIBFI_Admin_Utilities::exists_and_equal_to( $options, 'show_on_page', '1' );
		$show_on_single = JPIBFI_Admin_Utilities::exists_and_equal_to( $options, 'show_on_single', '1' );
		$show_on_category = JPIBFI_Admin_Utilities::exists_and_equal_to( $options, 'show_on_category', '1' );
		$show_on_blog = JPIBFI_Admin_Utilities::exists_and_equal_to( $options, 'show_on_blog', '1' );
		?>

		<input type="checkbox" id="show_on_home" name="jpibfi_selection_options[show_on_home]" <?php checked( true, $show_on_home ); ?> value="1" />
		<label for="show_on_home"><?php _e( 'Home page', 'jpibfi' ); ?></label><br/>
		<input type="checkbox" id="show_on_page" name="jpibfi_selection_options[show_on_page]" <?php checked( true, $show_on_page ); ?> value="1" />
		<label for="show_on_page"><?php _e( 'Pages', 'jpibfi' ); ?></label><br />
		<input type="checkbox" id="show_on_single" name="jpibfi_selection_options[show_on_single]" <?php checked( true, $show_on_single ); ?> value="1" />
		<label for="show_on_single"><?php _e( 'Single posts', 'jpibfi' ); ?></label><br />
		<input type="checkbox" id="show_on_category"	name="jpibfi_selection_options[show_on_category]" <?php checked( true, $show_on_category ); ?> value="1" />
		<label for="show_on_category"><?php _e( 'Category and archive pages', 'jpibfi' ); ?></label><br />
		<input type="checkbox" id="show_on_blog"	name="jpibfi_selection_options[show_on_blog]" <?php checked( true, $show_on_blog ); ?> value="1" />
		<label for="show_on_blog"><?php _e( 'Blog pages', 'jpibfi' ); ?></label>

		<?php
		echo JPIBFI_Admin_Utilities::create_description( $args[0] );
	}

	public function min_image_callback( $args ) {
		$options = $this->get_selection_options();

		$min_image_height = $options[ 'min_image_height' ];
		$min_image_width = $options[ 'min_image_width' ];
		?>

		<p>
			<label for="min_image_height"><?php _e('Height', 'jpibfi'); ?></label>
			<input type="number" min="0" step="1" id="min_image_height" name="jpibfi_selection_options[min_image_height]" value="<?php echo $min_image_height; ?>"
						 class="small-text" /> px
			<?php echo JPIBFI_Admin_Utilities::create_errors( 'min_image_height' ); ?>
		</p>

		<p>
			<label for="min_image_width"><?php _e('Width', 'jpibfi'); ?></label>
			<input type="number" min="0" step="1" id="min_image_width" name="jpibfi_selection_options[min_image_width]" value="<?php echo $min_image_width; ?>"
						 class="small-text" /> px
			<?php echo JPIBFI_Admin_Utilities::create_errors( 'min_image_width' ); ?>
		</p>

		<?php

		echo JPIBFI_Admin_Utilities::create_description( $args[0] );
	}

	public function sanitize_selection_options( $input ) {
		global $jpibfi_selection_options;

		foreach( $input as $key => $value ) {
			switch($key) {
				case 'disabled_classes':
				case 'enabled_classes':
					if ( false == JPIBFI_Admin_Utilities::contains_css_class_names_or_empty( $input [ $key ] ) ) {

						$field = '';
						if ( 'disabled_classes' == $key )
							$field = __( 'Disabled classes', 'jpibfi' );
						else if ( 'enabled_classes' == $key )
							$field = __( 'Enabled classes', 'jpibfi' );

						add_settings_error(
							$key,
							esc_attr( 'settings_updated' ),
								$field . ' - ' . __('the given value doesn\'t meet the requirements. Please correct it and try again.', 'jpibfi')
						);
					}
					break;
				case 'min_image_height':
				case 'min_image_width':
					if ( !is_numeric( $value ) || $value < 0 ) {

						$field = '';
						if ( 'min_image_height' == $key )
							$field = __( 'Minimum image height', 'jpibfi' );
						else if ( 'min_image_width' == $key )
							$field = __( 'Minimum image width', 'jpibfi' );

						add_settings_error(
							$key,
							esc_attr( 'settings_updated' ),
								$field . ' - ' . sprintf ( __('value must be a number greater or equal to %d.', 'jpibfi'), '0' )
						);
					}
					break;
			}

		}

		$errors = get_settings_errors();

		if ( count( $errors ) > 0 ) {

			update_option( JPIBFI_SELECTION_OPTIONS . '_errors', $input );
			return $jpibfi_selection_options;

		} else {

			delete_option( JPIBFI_SELECTION_OPTIONS . '_errors' );
			return $input;

		}
	}

	private function get_selection_options() {
		global $jpibfi_selection_options;

		if ( null == $this->admin_selection_options ) {
			//cumbersome, but works in WP 3.3
			$options = get_option( JPIBFI_SELECTION_OPTIONS . '_errors' );
			$this->admin_selection_options = false == $options ? $jpibfi_selection_options : $options;
		}

		return $this->admin_selection_options;
	}
}

add_action( 'plugins_loaded', array( 'JPIBFI_Selection_Options', 'get_instance' ) );