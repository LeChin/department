<?php

class JPIBFI_Admin {

	protected static $instance = null;

	private function __construct() {
		add_action( 'admin_menu', array( $this, 'print_admin_page_action') );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_meta_data' ) );
	}

	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function print_admin_page_action() {
		$page = add_submenu_page(
			'options-general.php',
			'jQuery Pin It Button For Images', 	// The value used to populate the browser's title bar when the menu page is active
			'jQuery Pin It Button For Images', // The text of the menu in the administrator's sidebar
			'administrator',					// What roles are able to access the menu
			'jpibfi_settings',				// The ID used to bind submenu items to this menu
			array( $this, 'print_admin_page' )				// The callback function used to render this menu
		);

		add_action( 'admin_print_styles-' . $page, array( $this, 'add_admin_site_scripts') );
	}

	/* Adds admin scripts */
	public function add_admin_site_scripts() {

		wp_register_style( 'jquery-pin-it-button-admin-style', JPIBFI_STYLE_URL . 'admin.css', array(), JPIBFI_VERSION . JPIBFI_VERSION_MINOR, 'all' );
		wp_enqueue_style( 'jquery-pin-it-button-admin-style' );

		wp_enqueue_script( 'jquery-pin-it-button-admin-script', JPIBFI_SCRIPT_URL . 'admin.js', array( 'jquery' ), JPIBFI_VERSION . JPIBFI_VERSION_MINOR, false );

		wp_register_script( 'angular', JPIBFI_SCRIPT_URL . 'angular.min.js' , '', '1.0.7', false );
		wp_enqueue_script( 'jquery-pin-it-button-admin-angular-script', JPIBFI_SCRIPT_URL . 'admin-angular.js', array( 'angular' ), JPIBFI_VERSION . JPIBFI_VERSION_MINOR, false );

		if ( function_exists( "wp_enqueue_media") ) {
			wp_enqueue_media();
			wp_enqueue_script( 'jpibfi-upload-new', JPIBFI_SCRIPT_URL . 'upload-button-new.js', array(), JPIBFI_VERSION . JPIBFI_VERSION_MINOR, false );
		} 	else {
			wp_enqueue_script( 'jpibfi-upload-old', JPIBFI_SCRIPT_URL . 'upload-button-old.js', array('thickbox', 'media-upload' ), JPIBFI_VERSION . JPIBFI_VERSION_MINOR, false );
		}
	}

	/* Prints admin page */
	public function print_admin_page() {

		//dictionary of tab names associated with settings to render names
		$settings_tabs = array(
			'selection_options' => array(
				'settings_name' => 'jpibfi_selection_options',
				'tab_label' => __( 'Selection', 'jpibfi' ),
				'support_link' => 'http://wordpress.org/support/plugin/jquery-pin-it-button-for-images',
				'review_link' => 'http://wordpress.org/support/view/plugin-reviews/jquery-pin-it-button-for-images'
			),
			'visual_options' => array(
				'settings_name' => 'jpibfi_visual_options',
				'tab_label' => __( 'Visual', 'jpibfi' ),
				'support_link' => 'http://wordpress.org/support/plugin/jquery-pin-it-button-for-images',
				'review_link' => 'http://wordpress.org/support/view/plugin-reviews/jquery-pin-it-button-for-images'
			),
			'advanced_options' => array(
				'settings_name' => 'jpibfi_advanced_options',
				'tab_label' => __('Advanced', 'jpibfi' ),
				'support_link' => 'http://wordpress.org/support/plugin/jquery-pin-it-button-for-images',
				'review_link' => 'http://wordpress.org/support/view/plugin-reviews/jquery-pin-it-button-for-images'
			)
		);

		$settings_tabs = apply_filters( 'jpibfi_settings_tabs', $settings_tabs);

		include_once( 'views/admin.php' );
		//cumbersome, but needed for error management to work properly in WP 3.3
		delete_option( JPIBFI_SELECTION_OPTIONS . '_errors' );
		delete_option( JPIBFI_VISUAL_OPTIONS . '_errors' );
	}

	/* Meta box for each post and page */
	public function add_meta_box() {
		//for posts
		add_meta_box(
			'jpibfi_settings_id', // this is HTML id of the box on edit screen
				'jQuery Pin It Button for Images - ' . __( 'Settings', 'jpibfi' ), // title of the box
			array( $this, 'print_meta_box' ), // function to be called to display the checkboxes, see the function below
			'post', // on which edit screen the box should appear
			'side', // part of page where the box should appear
			'default' // priority of the box
		);

		//for pages
		add_meta_box(
			'jpibfi_settings_id',
				'jQuery Pin It Button for Images - ' . __( 'Settings', 'jpibfi' ),
			array( $this, 'print_meta_box' ),
			'page',
			'side',
			'default'
		);
	}

	/* Displays the meta box */
	public function print_meta_box( $post, $metabox ) {

		wp_nonce_field( plugin_basename( __FILE__ ), 'jpibfi_nonce' );

		$post_meta = get_post_meta( $post->ID, JPIBFI_METADATA, true );
		$checked = isset( $post_meta ) && isset( $post_meta['jpibfi_disable_for_post'] ) && $post_meta['jpibfi_disable_for_post'] == '1';

		echo '<input type="checkbox" id="jpibfi_disable_for_post" name="jpibfi_disable_for_post" value="1"'	. checked( $checked, true, false ) . '>';
		echo '<label for="jpibfi_disable_for_post">' . __( 'Disable "Pin it" button for this post (works only on single pages/posts)', 'jpibfi' ) . '</label><br />';
	}

	public function save_meta_data( $post_id ) {

		//check user's permissions
		if ( ! current_user_can( 'edit_post', $post_id ) )
			return;
		// check if this isn't an auto save
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;
		// security check = updating possible only using post edit form
		if ( !isset( $_POST['jpibfi_nonce'] ) || ! wp_verify_nonce( $_POST['jpibfi_nonce'], plugin_basename( __FILE__ ) ) )
			return;

		$post_meta = array( 'jpibfi_disable_for_post' => '0' );
		// now store data in custom fields based on checkboxes selected
		$post_meta['jpibfi_disable_for_post'] =
				isset( $_POST['jpibfi_disable_for_post'] ) && $_POST['jpibfi_disable_for_post'] == '1' ? '1' : '0';

		if ( $post_meta['jpibfi_disable_for_post'] == '1' )
			update_post_meta( $post_id, JPIBFI_METADATA, $post_meta );
		else
			delete_post_meta( $post_id, JPIBFI_METADATA );
	}
}

add_action( 'plugins_loaded', array( 'JPIBFI_Admin', 'get_instance' ) );