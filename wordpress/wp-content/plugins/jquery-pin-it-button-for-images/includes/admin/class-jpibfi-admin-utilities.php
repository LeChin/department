<?php

class JPIBFI_Admin_Utilities {

	/* Returns properly formatted description of a setting */
	public static function create_description( $desc ) {
		return '<p class="description">' . $desc . '</p>';
	}

	/* Returns error div if there are errors */
	public static function create_errors( $setting_name ) {
		$error = "";

		$errors_array = get_settings_errors( $setting_name );

		if ( count ( $errors_array ) > 0 ) {
			$error .= '<div class="jpibfi-error-message">';
			for( $i = 0; $i < count( $errors_array ); $i++ ){
				$error .= $errors_array[ $i ]['message'] . '</br>';
			}
			$error .= '</div>';
		}

		return $error;
	}

	/* Checks if value exists in array and if it is of certain value */
	public static function exists_and_equal_to( $array , $name, $expected_value = "1" ) {
		return isset( $array[ $name ] ) && $array[ $name ] == $expected_value;
	}

	/* Checks if given string contains class names separated by semicolons or is empty */
	public static function contains_css_class_names_or_empty( $str ) {
		if ( 0 == strlen( $str ) )
			return true;

		$names = explode( ';', $str );
		$only_class_names = true;

		for( $i = 0; $i < count( $names ) && $only_class_names; $i++ )
			$only_class_names = self::is_string_css_class_name( $names [ $i ] );

		return $only_class_names;
	}

	public static function is_string_css_class_name( $class_name ) {
		return 1 == preg_match( "/^-?[_a-zA-Z]+[_a-zA-Z0-9-]*$/", $class_name );
	}

} 