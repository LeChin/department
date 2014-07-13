<?php
/**
 * Key.php
 *
 * Provides support key functionality
 *
 * @copyright Ingenesis Limited, 2008-2013. All rights reserved.
 */

class ShoppSupportKey {

	private static $error;

	/**
	 * Provides the activation status
	 *
	 * @return boolean True if activated
	 **/
	public static function activated () {
		return ( 'on' === self::activation('status') );
	}

	/**
	 * Activates the key with the server
	 *
	 * @return boolean True if activated
	 **/
	public static function activate () {
		return ( 'on' === self::activation('activate') );
	}

	/**
	 * Deactivates the key
	 *
	 * @author Jonathan Davis
	 * @since 1.3
	 *
	 * @return void
	 **/
	public static function deactivate () {
		if ( self::request('deactivate') ) {
			delete_transient('shopp_activation');
			add_action('shopp_supportkey_deactivated');
			return true;
		}
		return false;
	}

	private static function activation ( $request = 'status' ) {

		$activation = get_transient('shopp_activation');
		if ( ! empty($activation) ) return $activation;

		if ( ! in_array($request, array('activate', 'status')) )
			$request = 'status';

		$activating = ShoppSupportCore::activating();
		if ( 'status' == $request && $activating ) return 'off';

		$status = 'off';
		if ( self::request($request) ) {
			$status = 'on';
			set_transient('shopp_activation', $status, 86400);
		} else delete_transient('shopp_activation');

		return $status;
	}


	/**
	 * Activates or deactivates a support key
	 *
	 * @author Jonathan Davis
	 * @since 1.2
	 *
	 * @return stdClass The server response
	 **/
	public static function request ( $action ) {
		$actions = array('status', 'deactivate', 'activate');
		if ( ! in_array($action, $actions) ) $action = reset($actions);
		$request = array( 'ShoppServerRequest' => $action, 'site' => get_bloginfo('url') );
		$response = ShoppSupportCore::callhome($request, array(ShoppSupportPlugin::certificate()));

		if ( 'OK' == $response ) return true;

		if ( is_array($response) )
			self::$error = $response; // scrape the error message from the server

		return false;
	}

	public static function error () {

		if ( ! is_array(self::$error) || empty(self::$error) ) return false;

		list($code, $message) = self::$error;

		if ( empty($message) ) return false;

		$header =  Shopp::_m('### Activation Failed');
		$customer_service = Shopp::_mi('Please contact [customer service](%s) for assistance.', ShoppSupport::SUPPORT);
		$buy_more = Shopp::_mi('%sBuy more sites for this key%s', '<a href="' . ShoppSupport::STORE . '" class="button button-primary">', '</a>');
		$renew = Shopp::_mi('%sRenew your support key%s', '<a href="' . ShoppSupport::STORE . '" class="button button-primary">', '</a>');

		switch ($code) {
			case 410: $cta = $buy_more; break;
			case 402: $cta = $renew; break;
			default: $cta = $customer_service;
		}

		return $header . ' ' . Shopp::_m("%s %s", esc_html($message), $cta);

		$errors = array(
			'-100' => Shopp::__('An unknown activation error occurred.' . $customer_service),			// Unknown activation problem
			'-101' => Shopp::_m('This Shopp key is not recognized by the activation server.') . $customer_service,
			'-102' => Shopp::_m('Shopp keys cannot be activated on invalid URLs.') . $customer_service,
			'-103' => Shopp::_m('The key provided could not be validated by the activation server.') . $customer_service,
			'-104' => Shopp::_m('This Shopp key has already been activated on the maximum number of sites allowed. %sBuy more sites for this key%s', '<a href="' . ShoppSupport::STORE . '" class="button button-primary">', '</a>'),
			'-200' => Shopp::_m('An unknown deactivation error occurred.') . $customer_service,
			'-201' => Shopp::_m('This Shopp key is not recognized by the activation server.'),
			'-202' => Shopp::_m('Shopp keys cannot be deactivated on invalid URLs.'),
			'-203' => Shopp::_m('The key provided could not be validated by the activation server.') . $customer_service
		);
		if ( isset($errors[ $status ]) ) return Shopp::_m('**Activation failed.** ') . $errors[ $status ].debug_caller();

		$httpstatus = abs($status);

		if ( 503 == $httpstatus )
			return Shopp::_m('**Activation failed.** The activation server is currently unavailable for maintenance. Please be patient and try activating your key again later.');
		elseif ( 400 >= $httpstatus  )
			return Shopp::_m('**Activation failed.** There is a communication problem between this installation and the activation server.') . $customer_service;
		elseif ( 500 >= $httpstatus && 600 > $httpstatus )
			return Shopp::_m('**Activation failed.** The activation server is experiencing temporary problems. We&apos;re sorry for the inconvenience! We are fixing the problem as quickly as possible.');

		return false;
	}

	/**
	 * Loads the key code
	 *
	 * @author Jonathan Davis
	 * @since 1.3
	 *
	 * @return key data
	 **/
	public static function code () {
		$certificate = ShoppSupportPlugin::certificate();
		if ( empty($certificate) ) die("Could not load Shopp Certified Key");
		$parsed = openssl_x509_parse($certificate);
		if ( ! $parsed || ! isset($parsed['extensions']['1.2.3.0.2008']) ) return false;
		return bin2hex($parsed['extensions']['1.2.3.0.2008']);
	}

	/**
	 * Returns the key binding format
	 *
	 * @author Jonathan Davis
	 * @since 1.2
	 *
	 * @param boolean $m (optional) True to mask the format string
	 * @return string The format for key binding
	 **/
	public static function keyformat ( $m = false ) {
		$f = array(0x69, 0x73, 0x2f, 0x48, 0x34, 0x30, 0x6b);
		if ( true === $m ) $f = array_diff($f, array(0x73, 0x2f, 0x6b));
		return join('', array_map('chr', $f));
	}

}