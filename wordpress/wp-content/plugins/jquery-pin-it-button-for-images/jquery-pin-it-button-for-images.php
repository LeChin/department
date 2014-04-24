<?php
/*
Plugin Name: jQuery Pin It Button For Images
Plugin URI: http://mrsztuczkens.me/jpibfi/
Description: Highlights images on hover and adds a "Pin It" button over them for easy pinning.
Author: Marcin Skrzypiec
Version: 1.31
Author URI: http://mrsztuczkens.me/
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
if ( ! class_exists( 'jQuery_Pin_It_Button_For_Images' ) ) :

	/* Main JPIBFI Class */
	final class jQuery_Pin_It_Button_For_Images {
		/** Singleton *************************************************************/

		private static $instance;

		private function __construct() {
			$this->setup_constants();
			$this->includes();
			$this->load_textdomain();

			$jpibfi_plugin = plugin_basename( __FILE__ );
			add_filter( "plugin_action_links_$jpibfi_plugin", array( $this, 'plugin_settings_filter' ) );

			register_activation_hook( __FILE__, array( $this, 'update_plugin' ) );
			add_action( 'plugins_loaded', array( $this, 'update_plugin' ) );
		}

		public static function instance() {
			if ( null == self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		/**
		 * Setup plugin constants
		 */
		private function setup_constants() {

			/* VERSIONING */
			//plugin version
			if ( ! defined( 'JPIBFI_VERSION' ) )
				define( 'JPIBFI_VERSION', '1.31' );

			//used in versioning css and js files
			if ( ! defined( 'JPIBFI_VERSION_MINOR' ) )
				define( 'JPIBFI_VERSION_MINOR', 'a' );

			/* OPTIONS IN DATABASE */
			//metadata for each post
			if ( ! defined( 'JPIBFI_METADATA' ) )
				define( 'JPIBFI_METADATA', 'jpibfi_meta' );

			//used in error handling
			if ( ! defined( 'JPIBFI_UPDATE_OPTIONS' ) )
				define( 'JPIBFI_UPDATE_OPTIONS', 'jpibfi_update_options' );

			if ( ! defined( 'JPIBFI_SELECTION_OPTIONS' ) )
				define( 'JPIBFI_SELECTION_OPTIONS', 'jpibfi_selection_options' );

			if ( ! defined( 'JPIBFI_VISUAL_OPTIONS' ) )
				define( 'JPIBFI_VISUAL_OPTIONS', 'jpibfi_visual_options' );

			if ( ! defined( 'JPIBFI_ADVANCED_OPTIONS' ) )
				define( 'JPIBFI_ADVANCED_OPTIONS', 'jpibfi_advanced_options' );

			if ( ! defined( 'JPIBFI_VERSION_OPTION' ) )
				define( 'JPIBFI_VERSION_OPTION', 'jpibfi_version' );

			/* FOLDERS */
			// Plugin Folder Path
			if ( ! defined( 'JPIBFI_PLUGIN_DIR' ) )
				define( 'JPIBFI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

			// Plugin Folder URL
			if ( ! defined( 'JPIBFI_PLUGIN_URL' ) )
				define( 'JPIBFI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

			// Plugin Root File
			if ( ! defined( 'JPIBFI_PLUGIN_FILE' ) )
				define( 'JPIBFI_PLUGIN_FILE', __FILE__ );

			if ( ! defined( 'JPIBFI_STYLE_URL' ) )
				define( 'JPIBFI_STYLE_URL', JPIBFI_PLUGIN_URL . 'css/' );

			if ( ! defined( 'JPIBFI_SCRIPT_URL' ) )
				define( 'JPIBFI_SCRIPT_URL', JPIBFI_PLUGIN_URL . 'js/' );
		}

		/**
		 * Include required files
		 */
		private function includes() {
			global $jpibfi_selection_options;
			global $jpibfi_visual_options;
			global $jpibfi_adanced_options;

			$jpibfi_selection_options = get_option( JPIBFI_SELECTION_OPTIONS );
			$jpibfi_visual_options  = get_option( JPIBFI_VISUAL_OPTIONS );
			$jpibfi_adanced_options = get_option( JPIBFI_ADVANCED_OPTIONS );


			require_once JPIBFI_PLUGIN_DIR . 'includes/admin/class-jpibfi-admin-utilities.php';
			require_once JPIBFI_PLUGIN_DIR . 'includes/admin/class-jpibfi-selection-options.php';
			require_once JPIBFI_PLUGIN_DIR . 'includes/admin/class-jpibfi-advanced-options.php';
			require_once JPIBFI_PLUGIN_DIR . 'includes/admin/class-jpibfi-visual-options.php';
			require_once JPIBFI_PLUGIN_DIR . 'includes/admin/class-jpibfi-admin.php';
			require_once JPIBFI_PLUGIN_DIR . 'includes/public/class-jpibfi-client-utilities.php';
			require_once JPIBFI_PLUGIN_DIR . 'includes/public/class-jpibfi-client.php';


			if ( is_admin() ) {
				//TODO changes
			} else {

			}
		}

		public function load_textdomain() {
			load_plugin_textdomain( 'jpibfi', FALSE, dirname( plugin_basename( JPIBFI_PLUGIN_FILE ) ) . '/languages/' );
		}

		public function plugin_settings_filter( $links ) {
			$settings_link = '<a href="options-general.php?page=jpibfi_settings">' . __( 'Settings', 'jpibfi' ) . '</a>';
			array_unshift( $links, $settings_link );
			return $links;
		}

		/* Function updates DB if it detects new version of the plugin */
		public function update_plugin() {

			$version = get_option( JPIBFI_VERSION_OPTION );
			//if update is needed
			if ( false == $version || (float)$version < (float)JPIBFI_VERSION  || get_option( JPIBFI_UPDATE_OPTIONS ) ) {

				$option = get_option( JPIBFI_VISUAL_OPTIONS );
				self::update_option_fields( $option, JPIBFI_Visual_Options::default_visual_options(), JPIBFI_VISUAL_OPTIONS );

				$option = get_option( JPIBFI_SELECTION_OPTIONS );
				self::update_option_fields( $option, JPIBFI_Selection_Options::default_selection_options(), JPIBFI_SELECTION_OPTIONS );

				$option = get_option( JPIBFI_ADVANCED_OPTIONS );
				self::update_option_fields( $option, JPIBFI_ADVANCED_OPTIONS::default_advanced_options(), JPIBFI_ADVANCED_OPTIONS );

				//update the version of the plugin stored in option
				update_option( JPIBFI_VERSION_OPTION, JPIBFI_VERSION );
				//update not needed anymore
				update_option( JPIBFI_UPDATE_OPTIONS, false);
			}
		}

		/* Function makes sure that option has all needed fields by checking with defaults */
		public static function update_option_fields( $option, $default_option, $option_name ) {

			$new_option = array();

			if ( false == $option )
				$option = array();

			foreach ($default_option as $key => $value ) {
				if ( false == array_key_exists( $key, $option ) )
					$new_option [ $key ] = $value;
				else
					$new_option [ $key ] = $option [ $key ];
			}

			update_option( $option_name, $new_option );
		}
	}

endif; // End if class_exists check


function JPIBFI() {
	return jQuery_Pin_It_Button_For_Images::instance();
}

JPIBFI();
