<?php
/*-----------------------------------------------------------------------------------

CLASS INFORMATION

Description: Shortcode generator.
Author: Based on the work of the Shortcode Ninja plugin by VisualShortcodes.com.


-----------------------------------------------------------------------------------*/

class Themes_Shortcode_Generator {



	function Themes_Shortcode_Generator () {
	
		// Register the necessary actions on `admin_init`.
		add_action( 'admin_init', array( &$this, 'init' ) );
		
		// wp_ajax_... is only run for logged users.
		add_action( 'wp_ajax_tmnf_check_url_action', array( &$this, 'ajax_action_check_url' ) );
		
		// Shortcode testing functionality.
		//if ( ! function_exists( 'add_shortcode' ) ) return;
		//add_shortcode( 'testing',     array( &$this, 'shortcode_testing' ) );
	
	} // End Themes_Shortcode_Generator()

/*-----------------------------------------------------------------------------------
  init()
  
  * This guy runs the show. Rocket boosters... engage!
-----------------------------------------------------------------------------------*/
	function init() {
		global $pagenow;
		
		if ( ( current_user_can( 'edit_posts' ) || current_user_can( 'edit_pages' ) ) && get_user_option( 'rich_editing' ) == 'true' && ( in_array( $pagenow, array( 'post.php', 'post-new.php', 'page-new.php', 'page.php' ) ) ) )  {
		  	
		  	// Add the tinyMCE buttons and plugins.
			add_filter( 'mce_buttons', array( &$this, 'filter_mce_buttons' ) );
			add_filter( 'mce_external_plugins', array( &$this, 'filter_mce_external_plugins' ) );
			
			// Register the colourpicker JavaScript.
			wp_register_script( 'tmnf-colourpicker', esc_url( $this->framework_url() . 'js/colorpicker.js' ), array( 'jquery' ), '3.6', true ); // Loaded into the footer.
			wp_enqueue_script( 'tmnf-colourpicker' );
			
			// Register the colourpicker CSS.
			wp_register_style( 'tmnf-colourpicker', esc_url( $this->framework_url() . 'css/colorpicker.css' ) );
			wp_enqueue_style( 'tmnf-colourpicker' );
			
			// Register the custom CSS styles.
			wp_register_style( 'tmnf-shortcode-generator', esc_url( $this->framework_url() . 'css/shortcode-generator.css' ) );
			wp_enqueue_style( 'tmnf-shortcode-generator' );
			
		} // End IF Statement
	
	} // End init()

/*-----------------------------------------------------------------------------------
  filter_mce_buttons()
  
  * Add our new button to the tinyMCE editor.
-----------------------------------------------------------------------------------*/
	
	function filter_mce_buttons( $buttons ) {
		
		array_push( $buttons, '|', 'themnific_shortcodes_button' );
		
		return $buttons;
		
	} // End filter_mce_buttons()

/*-----------------------------------------------------------------------------------
  filter_mce_external_plugins()
  
  * Add functionality to the tinyMCE editor as an external plugin.
-----------------------------------------------------------------------------------*/
	
	function filter_mce_external_plugins( $plugins ) {
		
        $plugins['ThemnificThemesShortcodes'] = wp_nonce_url( esc_url( $this->framework_url() . 'js/shortcode-generator/editor_plugin.js' ));
        
        return $plugins;
        
	} // End filter_mce_external_plugins()
	
/*-----------------------------------------------------------------------------------
  Utility Functions
  
  * Helper functions for this class.
-----------------------------------------------------------------------------------*/

/*-----------------------------------------------------------------------------------
  framework_url()
  
  * Returns the full URL of the TMNFFramework, including trailing slash.
-----------------------------------------------------------------------------------*/

function framework_url() {
	
	return esc_url( trailingslashit( get_template_directory_uri() . '/' . basename( dirname( __FILE__ ) ) ) );

} // End framework_url()

/*-----------------------------------------------------------------------------------
  ajax_action_check_url()
  
  * Checks if a given url (via GET or POST) exists.
  * Returns JSON.
  *
  * NOTE: For users that are not logged in this is not called.
  * The client recieves <code>-1</code> in that case.
-----------------------------------------------------------------------------------*/

function ajax_action_check_url() {

	$hadError = true;

	$url = isset( $_REQUEST['url'] ) ? $_REQUEST['url'] : '';

	if ( strlen( $url ) > 0  && function_exists( 'get_headers' ) ) {
			
		$file_headers = @get_headers( $url );
		$exists       = $file_headers && $file_headers[0] != 'HTTP/1.1 404 Not Found';
		$hadError     = false;
	}

	echo '{ "exists": '. ($exists ? '1' : '0') . ($hadError ? ', "error" : 1 ' : '') . ' }';

	die();
	
} // End ajax_action_check_url()

/*-----------------------------------------------------------------------------------
  shortcode_testing()
  
  * Used for testing that the shortcodes are functioning.
-----------------------------------------------------------------------------------*/

function shortcode_testing( $atts, $content = null ) {
	
	if ($content === null) return '';
	
	return '<strong>Working: ' . $content . '</strong>' . "\n";
	
} // End shortcode_testing()

} // End Class

/*-----------------------------------------------------------------------------------
  INSTANTIATE CLASS
-----------------------------------------------------------------------------------*/

$tmnf_shortcode_generator = new Themes_Shortcode_Generator();
?>