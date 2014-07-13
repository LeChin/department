<?php

class JPIBFI_Advanced_Options {

	protected static $instance = null;

	protected $admin_advanced_options = null;

	private function __construct() {
		add_action( 'admin_init', array( $this, 'initialize_advanced_options' ) );
	}

	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public static function default_advanced_options() {
		$defaults = array(
			'debug'      => '0',
			'container_selector' => 'div'
		);

		return apply_filters( 'jpibfi_default_advanced_options', $defaults );
	}

	/* Defines selection options section and adds all required fields	 */
	public function initialize_advanced_options() {

		// First, we register a section.
		add_settings_section(
			'advanced_options_section',
			__( 'Advanced Settings', 'jpibfi' ),
			array( $this, 'advanced_options_callback' ),
			'jpibfi_advanced_options'
		);

		//lThen add all necessary fields to the section
		add_settings_field(
			'debug',
			__( 'Debug', 'jpibfi' ),
			array( $this, 'debug_callback' ),
			'jpibfi_advanced_options',
			'advanced_options_section',
			array(
				__( 'Use debug mode only if you are experiencing some issues with the plugin and you are reporting them to the developer of the plugin', 'jpibfi' ),
			)
		);

		add_settings_field(
			'container_selector',
			__( 'Container selector', 'jpibfi' ),
			array( $this, 'container_selector_callback' ),
			'jpibfi_advanced_options',
			'advanced_options_section',
			array(
				__( 'This is the selector used to find the container that holds the entire single post. It looks for an element that is a parent of the content of the post. Usually it\'s a div or article element. This setting is important to making "Use post url" visual setting work properly.', 'jpibfi' ),
			)
		);

		register_setting(
			'jpibfi_advanced_options',
			'jpibfi_advanced_options' //no sanitization needed for now
		);
	}

	public function advanced_options_callback() {
		echo '<p>' . __('Advanced settings', 'jpibfi') . '</p>';
	}

	public function debug_callback( $args ){
		$options = $this->get_advanced_options();
		$debug = JPIBFI_Admin_Utilities::exists_and_equal_to( $options, 'debug', '1' );

		echo '<input type="checkbox" id="debug" name="jpibfi_advanced_options[debug]" value="1" ' . checked( "1", $debug, false ) . '>';
		echo '<label for="debug">' . __( 'Enable debug mode', 'jpibfi' ) . '</label>';

		echo JPIBFI_Admin_Utilities::create_description( $args[0] );
	}

	public function container_selector_callback( $args ) {
		$options = $this->get_advanced_options();
		$container_selector = esc_attr( $options[ 'container_selector' ] );

		echo '<input type="text" id="image_selector" name="jpibfi_advanced_options[container_selector]" value="' . $container_selector . '"/>';
		echo JPIBFI_Admin_Utilities::create_description( $args[0] );
	}

	private function get_advanced_options() {
		global $jpibfi_adanced_options;

		if ( null == $this->admin_advanced_options ) {
			//cumbersome, but works in WP 3.3
			$options = get_option( JPIBFI_ADVANCED_OPTIONS . '_errors' );
			$this->admin_advanced_options = false == $options ? $jpibfi_adanced_options : $options;
		}

		return $this->admin_advanced_options;
	}
}

add_action( 'plugins_loaded', array( 'JPIBFI_Advanced_Options', 'get_instance' ) );