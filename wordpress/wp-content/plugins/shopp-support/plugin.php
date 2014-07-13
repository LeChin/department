<?php
/**
 * Plugin Name: Shopp+Support
 * Plugin URI: http://shopplugin.com
 * Description: Enables expert support and one-click updates for Shopp and Shopp add-ons from shopplugin.com
 * Version: 1.0
 * Author: shopplugin.com
 * Author URI: http://shopplugin.com
 * Requires at least: 3.5
 * Tested up to: 3.5.2
 *
 *    Portions created by Ingenesis Limited are Copyright © 2008-2013 by Ingenesis Limited
 *    All rights reserved.
 *    This file is part of the Shopp+Support plugin.
 *
 **/

defined( 'WPINC' ) || header( 'HTTP/1.1 403' ) & exit; // Prevent direct access

ShoppSupportPlugin::activate();

class ShoppSupportPlugin {

	private static $object = false;
	private static $certificate = false;

	private function __construct () {}

	public static function activate () {
		self::object();
		require 'Core.php';
	}

	public static function object () {
		if ( ! self::$object instanceof self )
			self::$object = new self;
		return self::$object;
	}

	public static function certificate () {
		return file_get_contents(dirname(__FILE__) . '/certificate.pem');
	}

}