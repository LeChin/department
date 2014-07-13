<?php
/**
 * Updates.php
 *
 * Handles update checks from shopplugin.com and WP notifications
 *
 * @copyright Ingenesis Limited, 2008-2013. All rights reserved.
 */

class ShoppSupportUpdates {

	/**
	 * Checks for available updates
	 *
	 * @author Jonathan Davis
	 * @since 1.1
	 *
	 * @return array List of available updates
	 **/
	public static function check () {

		global $pagenow;
		if ( is_admin()
			&& 'plugins.php' == $pagenow
			&& isset($_GET['action'])
			&& 'deactivate' == $_GET['action']) return array();

		$updates = new StdClass();
		if (function_exists('get_site_transient')) $plugin_updates = get_site_transient('update_plugins');
		else $plugin_updates = get_transient('update_plugins');

		switch ( current_filter() ) {
			case 'load-update-core.php': $timeout = 60; break; // 1 minute
			case 'load-plugins.php': // 1 hour
			case 'load-update.php': $timeout = 3600; break;
			default: $timeout = 43200; // 12 hours
		}

		$justchecked = isset( $plugin_updates->last_checked_shopp ) && $timeout > ( time() - $plugin_updates->last_checked_shopp );
		$changed = isset($plugin_updates->response[ SHOPP_PLUGINFILE ]);
		if ( $justchecked && ! $changed ) return;

		$Shopp = Shopp::object();
		$addons = array_merge(
			$Shopp->Gateways->checksums(),
			$Shopp->Shipping->checksums(),
			$Shopp->Storage->checksums()
		);

		$request = array('ShoppServerRequest' => 'update-check');
		/**
		 * Update checks collect environment details for faster support service only,
		 * none of it is linked to personally identifiable information.
		 **/
		$data = array(
			'core' => ShoppVersion::release(),
			'addons' => join("-", $addons),
			'site' => get_bloginfo('url'),

			// Details
			'wp' => get_bloginfo('version').(is_multisite()?' (multisite)':''),
			'mysql' => mysql_get_server_info(),
			'php' => phpversion(),
			'uploadmax' => ini_get('upload_max_filesize'),
			'postmax' => ini_get('post_max_size'),
			'memlimit' => ini_get('memory_limit'),
			'server' => $_SERVER['SERVER_SOFTWARE'],
			'agent' => $_SERVER['HTTP_USER_AGENT']

		);

		$response = ShoppSupportCore::callhome($request, $data);

		if ($response == '-1') return; // Bad response, bail
		$response = maybe_unserialize($response);
		unset($updates->response);

		if ( isset($response->key) && ! Shopp::str_true($response->key) ) ShoppSupportCore::unplug();

		if ( isset($response->addons) ) {
			$updates->response[ SHOPP_PLUGINFILE . '/addons' ] = $response->addons;
			unset($response->addons);
		}

		if ( isset($response->id) )
			$updates->response[ SHOPP_PLUGINFILE ] = $response;

		if (isset($updates->response)) {
			shopp_set_setting('updates', $updates);

			// Add Shopp to the WP plugin update notification count
			if ( isset($updates->response[ SHOPP_PLUGINFILE ]) )
				$plugin_updates->response[ SHOPP_PLUGINFILE ] = $updates->response[ SHOPP_PLUGINFILE ];

		} else unset($plugin_updates->response[ SHOPP_PLUGINFILE ]); // No updates, remove Shopp from the plugin update count

		$plugin_updates->last_checked_shopp = time();
		if ( function_exists('set_site_transient') ) set_site_transient('update_plugins', $plugin_updates);
		else set_transient('update_plugins', $plugin_updates);

		return $updates;
	}

	/**
	 * Loads the change log for an available update
	 *
	 * @author Jonathan Davis
	 * @since 1.1
	 *
	 * @return void
	 **/
	public static function changelog () {
		if ( 'shopp' != $_REQUEST['plugin'] ) return;

		$request = array('ShoppServerRequest' => 'changelog');

		if ( isset($_GET['core']) && ! empty($_GET['core']) )
			$request['core'] = $_GET['core'];

		if ( isset($_GET['addon']) && ! empty($_GET['addon']) )
			$request['addons'] = $_GET['addon'];

		$data = array();
		$response = ShoppSupport::callhome($request, $data);

		include SHOPP_ADMIN_PATH . '/help/changelog.php';
		exit;
	}

	/**
	 * Reports on the availability of new updates and the update key
	 *
	 * @author Jonathan Davis
	 * @since 1.1
	 *
	 * @return void
	 **/
	public static function status () {
		$updates = shopp_setting('updates');
		$activated = ShoppSupportKey::activated();
		$core = isset($updates->response[ SHOPP_PLUGINFILE ]) ? $updates->response[ SHOPP_PLUGINFILE ] : false;
		$addons = isset($updates->response[ SHOPP_PLUGINFILE . '/addons' ]) ? $updates->response[ SHOPP_PLUGINFILE . '/addons'] : false;

		$plugin_name = 'Shopp';
		$plugin_slug = strtolower($plugin_name);
		$store_url = ShoppSupport::STORE;
		$account_url = $store_url . 'account/';
		$style = '<style type="text/css">#shopp th, #shopp td { border-bottom: 0; }</style>';

		if ( ! empty($core)	// Core update available
				&& isset($core->new_version)	// New version info available
				&& version_compare($core->new_version, ShoppVersion::release(), '>') // New version is greater than current version
			) {
			$details_url = admin_url('plugin-install.php?tab=plugin-information&plugin=' . $plugin_slug . '&core=' . $core->new_version . '&TB_iframe=true&width=600&height=800');
			$update_url = wp_nonce_url('update.php?action=shopp&plugin=' . SHOPP_PLUGINFILE, 'upgrade-plugin_shopp');

			if ( ! $activated ) { // Key not active
				$update_url = $store_url;
				$message = Shopp::__(
					'There is a new version of %1$s available. %2$s View version %5$s details %4$s or %3$s purchase a %1$s key %4$s to get access to automatic updates and official support services.',
					$plugin_name, '<a href="' . $details_url . '" class="thickbox" title="' . esc_attr($plugin_name) . '">', '<a href="' . $update_url .'">', '</a>', $core->new_version
				);

				shopp_set_setting('updates', false);
			} else {
				$message = Shopp::__(
					'There is a new version of %1$s available. %2$s View version %5$s details %4$s or %3$s upgrade now%4$s.',
					$plugin_name, '<a href="'.$details_url.'" class="thickbox" title="'.esc_attr($plugin_name).'">', '<a href="'.$update_url.'">', '</a>', $core->new_version
				);
			}

			echo '<tr class="plugin-update-tr"><td colspan="3" class="plugin-update"><div class="update-message">'.$message.$style.'</div></td></tr>';

			return;
		}

		if ( ! $activated ) { // No update available, key not active
			$message = Shopp::__(
				'Please activate a valid %1$s support key for automatic updates and official support services. %2$s Download your %1$s support key %4$s or %3$s purchase a new key at the Shopp Store. %4$s',
				$plugin_name, '<a href="'.$account_url.'" target="_blank">', '<a href="'.$store_url.'" target="_blank">', '</a>'
			);

			echo '<tr class="plugin-update-tr"><td colspan="3" class="plugin-update"><div class="update-message">'.$message.$style.'</div></td></tr>';
			shopp_set_setting('updates', false);

			return;
		}

	    if ( $addons ) {
			// Addon update messages
			foreach ( $addons as $addon ) {
				$details_url = admin_url('plugin-install.php?tab=plugin-information&plugin=shopp&addon='.($addon->slug).'&TB_iframe=true&width=600&height=800');
				$update_url = wp_nonce_url('update.php?action=shopp&addon='.$addon->slug.'&type='.$addon->type, 'upgrade-shopp-addon_'.$addon->slug);
				$message = Shopp::__(
					'There is a new version of the %1$s add-on available. %2$s View version %5$s details %4$s or %3$s upgrade now%4$s.',
					esc_html($addon->name), '<a href="'.$details_url.'" class="thickbox" title="'.esc_attr($addon->name).'">', '<a href="'.esc_url($update_url).'">', '</a>', esc_html($addon->new_version)
				);

				echo '<tr class="plugin-update-tr"><td colspan="3" class="plugin-update"><div class="update-message">'.$message.$style.'</div></td></tr>';
			}
		}

	}

	public static function remove_wp_update_notices () {
		remove_action('after_plugin_row_' . SHOPP_PLUGINFILE, 'wp_plugin_update_row');
	}

}