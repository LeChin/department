<?php
/**
 * Core.php
 *
 * Provides core plugin functionality
 *
 * @author Jonathan Davis
 * @copyright Ingenesis Limited, November 2013
 * @license (@see shopp/license.txt)
 * @package shopp+support
 * @since 1.0
 **/

ShoppSupportCore::run();

class ShoppSupportCore {

	const SERVER = 'https://shopplugin.com/';
	const TESTURL = 'https://devstage.shopplugin.net/';

	private static $plugin;

	public static function run () {
		require 'Key.php';

		$plugin = self::plugin();

		// Plugin activation & deactivation
		register_deactivation_hook( $plugin, array(__CLASS__, 'deactivation') );
		register_activation_hook( $plugin, array(__CLASS__, 'activation') );

		add_action('shopp_init', array(__CLASS__, 'init'));

		add_action('shopp_init', array(__CLASS__, 'updates'));

		if ( self::activating() ) {
			$_GET['s'] = 'Shopp+'; // Set search to show this plugin only
			self::shoppd();
		}

	}

	public static function init () {

		if ( self::activating() )
			self::activate();
		else self::checkin();

	}

	public static function updates () {

		if ( ! class_exists('ShoppSupportUpdates', false) )
			require 'Updates.php';

		// Update checking
		$updates = array('load-plugins.php', 'load-update.php', 'load-update-core.php', 'wp_update_plugins', 'shopp_check_updates');
		foreach ( $updates as $action )
			add_action($action, array('ShoppSupportUpdates', 'check'));

		// Plugin updates notifications
		add_action('load-plugins.php', array('ShoppSupportUpdates', 'remove_wp_update_notices'));
		add_action('after_plugin_row_' . SHOPP_PLUGINFILE, array('ShoppSupportUpdates', 'status'), 10, 2);
		add_action('install_plugins_pre_plugin-information', array('ShoppSupportUpdates', 'changelog'));

		// Upgrades
		add_action('update-custom_shopp', array(__CLASS__, 'upgrade'));

		// Shopp Add-on Uploads
		add_action('install_plugins_upload', array(__CLASS__, 'install_plugins_adddon_upload'), 20);

	}

	public static function checkin () {
		if ( isset($_GET['action']) && 'error_scrape' != $_GET['action'] || isset($_GET['error']) ) return; // Skip on errors

		if ( ! ShoppSupportKey::activated() )
			add_action('admin_head-plugins.php', array(__CLASS__, 'unplug'));
	}

	public static function activating () {
		return ( isset($_REQUEST['activate']) && 'true' == $_REQUEST['activate'] && false !== get_transient('shopp_key_activating') );
	}

	/**
	 * After the plugin is activated
	 *
	 * @author Jonathan Davis
	 * @since 1.3
	 *
	 * @return void
	 **/
	public static function activate () {

		delete_transient('shopp_key_activating');
		unset($_GET['activate']); // Prevent activation message

		if ( ShoppSupportKey::activate() )
			return add_action('admin_notices', array(__CLASS__, 'success'));

		add_action('admin_notices', array(__CLASS__, 'error'));
		add_action('admin_head-plugins.php', array(__CLASS__, 'unplug'));
	}

	public static function plugin () {
		if ( isset(self::$plugin) ) return self::$plugin;

		global $plugin, $mu_plugin, $network_plugin;

		if ( isset($plugin) ) {
			$filepath = $plugin;
		} elseif ( isset($mu_plugin) ) {
			$filepath = $mu_plugin;
		} elseif ( isset($network_plugin) ) {
			$filepath = $network_plugin;
		}

		self::$plugin = basename(dirname($filepath)) . '/' . basename($filepath);
		return self::$plugin;
	}

	public static function activation () {
		set_transient('shopp_key_activating', true, 60);
	}

	public static function deactivation () {
		$deactivation = ShoppSupportKey::deactivate();
		delete_transient('shopp_activation');
	}

	public static function error ( $error = null ) {
		if ( empty($error) ) $error = ShoppSupportKey::error();

		echo '<div id="message" class="error">';
		if ( empty($error) ) _e('<h3>Activation Failed.</h3><p>A problem with the activation server response prevented activating this Shopp installation.</p>');
		else echo $error;
		echo '</div>';

	}

	public static function success () {
		echo '<div id="message" class="updated">';
		Shopp::_em('**Nice work!** Your support key has been successfully **activated** on %s. **Thank you!**', get_bloginfo('url'));
		echo '</div>';
	}

	public static function unplug () {
		delete_transient('shopp_key_activating');
		delete_transient('shopp_activation');

		$plugin = self::plugin();
		$active_plugins = get_option('active_plugins');

		if ( in_array($plugin, $active_plugins) ) {

			$key = array_search($plugin, $active_plugins);
			array_splice($active_plugins, $key, 1);
			update_option('active_plugins', $active_plugins);
		}

	}

	public static function shoppd () {
		$active_plugins = self::activeplugins();

	    foreach ( $active_plugins as $plugin )
			if ( false !== strpos($plugin, 'Shopp.php') )
				return;

		unset($_GET['activate']);
		add_action('admin_notices', array(__CLASS__, 'noshopp'));
		add_action('admin_head-plugins.php', array(__CLASS__, 'unplug'));
	}

	private static function activeplugins () {
		$active_plugins = (array) get_option( 'active_plugins', array() );
		$network_plugins = (array) get_site_option( 'active_sitewide_plugins', array() );

		return array_merge($active_plugins, $network_plugins);
	}

	public static function noshopp () {
		$error = __('<p><strong>Activation failed.</strong> Shopp is not active on this installation of WordPress. Please install Shopp from <a href="' . self::SERVER . '">' . self::SERVER . '</a> before activating your Shopp Support Key.</p>');
		self::error($error);
	}

	/**
	 * Communicates with the Shopp update service server
	 *
	 * @author Jonathan Davis
	 * @since 1.1
	 *
	 * @param array $request (optional) A list of request variables to send
	 * @param array $data (optional) A list of data variables to send
	 * @param array $options (optional)
	 * @return string The response from the server
	 **/
	public static function callhome ($request=array(), $data=array(), $options=array()) {
		$query = http_build_query(array_merge(array('ver'=>'1.2'), $request), '', '&');
		$data = http_build_query($data, '', '&');

		$defaults = array(
			'method' => 'POST',
			'timeout' => 20,
			'redirection' => 7,
			'httpversion' => '1.0',
			'user-agent' => SHOPP_GATEWAY_USERAGENT.'; '.get_bloginfo( 'url' ),
			'blocking' => true,
			'headers' => array(),
			'cookies' => array(),
			'body' => $data,
			'compress' => false,
			'decompress' => true,
			'sslverify' => false
		);
		$params = array_merge($defaults, $options);

		$testing = defined('SHOPP_SUPPORT_TESTING') && SHOPP_SUPPORT_TESTING;
		$server = $testing ? self::TESTURL : self::SERVER;
		$URL = "$server?$query";

		if ( $testing ) {
			error_log('CALLHOME REQUEST ------------------');
			error_log(debug_caller());
			error_log($URL);
		}

		$connection = new WP_Http();
		$result = $connection->request($URL, $params);

		if ( $testing ) {
			error_log(json_encode($result));
			error_log('-------------- END CALLHOME REQUEST');
		}

		extract($result);

		if ( is_wp_error($result) ) {
			$errors = array(); foreach ($result->errors as $errname => $msgs) $errors[] = join(' ', $msgs);
			$errors = join(' ', $errors);
			return $errors;
		} elseif ( empty($result) || !isset($result['response']) ) {
			return false;
		} else extract($result);

		if ( isset($response['code']) && 200 != $response['code'] )
			return array(strtok($body, ' '), strtok(''));

		return $body;

	}

	/**
	 * Perform updates for the core plugin and addons
	 *
	 * @author Jonathan Davis
	 * @since 1.1
	 *
	 * @return void
	 **/
	public function upgrade () {
		require 'Upgrader.php';

		global $parent_file, $submenu_file;

		$plugin = isset($_REQUEST['plugin']) ? trim($_REQUEST['plugin']) : '';
		$addon = isset($_REQUEST['addon']) ? trim($_REQUEST['addon']) : '';
		$type = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : '';
		$upload = isset($_REQUEST['upload']) ? trim($_REQUEST['upload']) : '';

		if ( ! current_user_can('update_plugins') )
			wp_die(__('You do not have sufficient permissions to update plugins for this blog.'));

		if ( ! class_exists('Plugin_Upgrader', false) )
			require ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		if (SHOPP_PLUGINFILE == $plugin) {
			// check_admin_referer('upgrade-plugin_' . $plugin);
			$title = __('Upgrade Shopp', 'Shopp');
			$parent_file = 'plugins.php';
			$submenu_file = 'plugins.php';
			require ABSPATH . 'wp-admin/admin-header.php';

			$nonce = 'upgrade-plugin_' . $plugin;
			$url = 'update.php?action=shopp&plugin=' . $plugin;

			$upgrader = new ShoppCore_Upgrader( new Shopp_Upgrader_Skin( compact('title', 'nonce', 'url', 'plugin') ) );
			$upgrader->upgrade($plugin);

			include ABSPATH . '/wp-admin/admin-footer.php';
		} elseif ( in_array($type, array('gateway', 'shipping', 'storage')) ) {
			// check_admin_referer('upgrade-shopp-addon_' . $plugin);
			$title = sprintf(__('Upgrade Shopp Add-on', 'Shopp'), 'Shopp');
			$parent_file = 'plugins.php';
			$submenu_file = 'plugins.php';
			require ABSPATH . 'wp-admin/admin-header.php';

			$nonce = 'upgrade-shopp-addon_' . $plugin;
			$url = 'update.php?action=shopp&addon=' . $addon . '&type=' . $type;

			$upgrader = new ShoppAddon_Upgrader( new Shopp_Upgrader_Skin( compact('title', 'nonce', 'url', 'addon') ) );
			$upgrader->upgrade($addon, $type);

			include ABSPATH . '/wp-admin/admin-footer.php';

		} elseif ( 'shopp-addon' == $upload ) {
			if ( ! current_user_can('install_plugins') )
				wp_die( __( 'You do not have sufficient permissions to install plugins on this site.' ) );

			check_admin_referer('plugin-upload');

			$file_upload = new File_Upload_Upgrader('pluginzip', 'package');

			$title = Shopp::__('Upload Shopp Add-on');
			$parent_file = 'plugins.php';
			$submenu_file = 'plugin-install.php';
			require_once ABSPATH . 'wp-admin/admin-header.php';

			$title = sprintf( Shopp::__('Installing Shopp Add-on from uploaded file: %s'), basename( $file_upload->filename ) );
			$nonce = 'plugin-upload';
			$url = add_query_arg(array('package' => $file_upload->id), 'update.php?action=shopp&upload=shopp-addon');
			$type = 'upload'; //Install plugin type, From Web or an Upload.

			$upgrader = new ShoppAddon_Upgrader( new Shopp_Upgrader_Skin( compact('type', 'title', 'nonce', 'url') ) );
			$result = $upgrader->install( $file_upload->package );

			if ( $result || is_wp_error($result) )
				$file_upload->cleanup();

			include ABSPATH . 'wp-admin/admin-footer.php';
		}

	}

	/**
	 * Upload from zip
	 *
	 * @param string $page
	 */
	public static function install_plugins_adddon_upload ( $page = 1 ) {
	?>
		<h4><?php _e('Install a Shopp Add-on in .zip format'); ?></h4>
		<p class="install-help"><?php _e('If you have a Shopp Add-on in a .zip format, you may install it by uploading it here.'); ?></p>
		<form method="post" enctype="multipart/form-data" class="wp-upload-form" action="<?php echo self_admin_url('update.php?action=shopp&upload=shopp-addon'); ?>">
			<?php wp_nonce_field( 'plugin-upload'); ?>
			<label class="screen-reader-text" for="pluginzip"><?php _e('Plugin zip file'); ?></label>
			<input type="file" id="pluginzip" name="pluginzip" />
			<?php submit_button( __( 'Install Shopp Add-on' ), 'button', 'install-plugin-submit', false ); ?>
		</form>
	<?php
	}


}