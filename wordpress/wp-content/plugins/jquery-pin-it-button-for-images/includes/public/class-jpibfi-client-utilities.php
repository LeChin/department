<?php

class JPIBFI_Client_Utilities {

	//function gets the id of the image by searching for class with wp-image- prefix, otherwise returns empty string
	public static function get_post_id_from_image_classes( $class_attribute ) {
		$classes = preg_split( '/\s+/', $class_attribute, -1, PREG_SPLIT_NO_EMPTY );
		$prefix = 'wp-image-';

		for ($i = 0; $i < count( $classes ); $i++) {

			if ( $prefix === substr( $classes[ $i ], 0, strlen( $prefix ) ))
				return str_replace( $prefix, '',  $classes[ $i ] );
		}

		return '';
	}

	/* Get description for a given image */
	public static function get_image_description( $id, $src ) {

		$result = is_numeric( $id ) ? self::get_image_description_by_id( $id ) : '';

		//if description based on id wasn't found
		if ( '' === $result  ) {
			$id = self::fjarrett_get_attachment_id_by_url( $src );
			$result = is_numeric ( $id ) ? self::get_image_description_by_id( $id ) : '';
		}

		return $result;
	}

	/* Function searches for image based on $id and returns its description */
	static function get_image_description_by_id( $id ){

		$attachment = get_post( $id );
		return null == $attachment ? '' : $attachment->post_content;
	}

	/**
	 * Function copied from http://frankiejarrett.com/get-an-attachment-id-by-url-in-wordpress/
	 * Return an ID of an attachment by searching the database with the file URL.
	 *
	 * First checks to see if the $url is pointing to a file that exists in
	 * the wp-content directory. If so, then we search the database for a
	 * partial match consisting of the remaining path AFTER the wp-content
	 * directory. Finally, if a match is found the attachment ID will be
	 * returned.
	 *
	 * @return {int} $attachment
	 */
	static function fjarrett_get_attachment_id_by_url( $url ) {

		// Split the $url into two parts with the wp-content directory as the separator.
		$parse_url  = explode( parse_url( WP_CONTENT_URL, PHP_URL_PATH ), $url );

		// Get the host of the current site and the host of the $url, ignoring www.
		$this_host = str_ireplace( 'www.', '', parse_url( home_url(), PHP_URL_HOST ) );
		$file_host = str_ireplace( 'www.', '', parse_url( $url, PHP_URL_HOST ) );

		// Return nothing if there aren't any $url parts or if the current host and $url host do not match.
		if ( ! isset( $parse_url[1] ) || empty( $parse_url[1] ) || ( $this_host != $file_host ) )
			return;

		// Now we're going to quickly search the DB for any attachment GUID with a partial path match.
		// Example: /uploads/2013/05/test-image.jpg
		global $wpdb;

		$prefix     = $wpdb->prefix;
		$attachment = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM " . $prefix . "posts WHERE guid RLIKE %s;", $parse_url[1] ) );

		// Returns null if no attachment is found.
		return  $attachment ? $attachment[0] : null;
	}

	/* True if plugin should be added to the current post/page */
	public static function add_jpibfi() {
		global $post;
		global $jpibfi_selection_options;

		if ( is_front_page() )
			return isset( $jpibfi_selection_options['show_on_home'] ) && $jpibfi_selection_options['show_on_home'] == "1";
		else if ( is_single() )
			return isset( $jpibfi_selection_options['show_on_single'] ) && $jpibfi_selection_options['show_on_single'] == "1" ? self::add_plugin_to_post( $post->ID ) : false;
		else if ( is_page() )
			return isset( $jpibfi_selection_options['show_on_page'] ) && $jpibfi_selection_options['show_on_page'] == "1" ? self::add_plugin_to_post( $post->ID ) : false;
		else if ( is_category() || is_archive() || is_search() )
			return isset( $jpibfi_selection_options['show_on_category'] ) && $jpibfi_selection_options['show_on_category'] == "1";
		else if ( self::is_blog_page() )
			return isset( $jpibfi_selection_options['show_on_blog'] ) && $jpibfi_selection_options['show_on_blog'] == "1";
		return true;
	}

	/* Checks if the plugin wasn't deactivated in the given post/page */
	private static function add_plugin_to_post( $post_id ) {
		$post_meta = get_post_meta( $post_id, JPIBFI_METADATA, true );
		return empty( $post_meta )
		|| false == array_key_exists( 'jpibfi_disable_for_post', $post_meta )
		|| '1' != $post_meta['jpibfi_disable_for_post'];
	}

	/* function copied from https://gist.github.com/wesbos/1189639 */
	private static function is_blog_page() {
		global $post;

		$post_type = get_post_type( $post );

		return ( ( is_home() || is_archive() || is_single() ) && ( $post_type == 'post' )	);
	}
}