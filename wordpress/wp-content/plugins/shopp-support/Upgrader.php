<?php
/**
 * Shopp_Upgrader class
 *
 * Provides foundational functionality specific to Shopp update
 * processing classes.
 *
 * Extensions derived from the WordPress WP_Upgrader & Plugin_Upgrader classes:
 * @see wp-admin/includes/class-wp-upgrader.php
 *
 * @copyright WordPress {@link http://codex.wordpress.org/Copyright_Holders}
 * @author Jonathan Davis
 * @since 1.1
 * @package shopp
 **/

class Shopp_Upgrader extends Plugin_Upgrader {

	public function download_package($package) {

		if ( ! preg_match('!^(http|https|ftp)://!i', $package) && file_exists($package) ) //Local file or remote?
			return $package; //must be a local file..

		if ( empty($package) )
			return new WP_Error('no_package', $this->strings['no_package']);

		$this->skin->feedback('downloading_package', $package);

		$key = ShoppSupportKey::code();
		$vars = array('VERSION', 'KEY', 'URL');
		$values = array(urlencode(ShoppVersion::release()), urlencode($key), urlencode(get_bloginfo('siteurl')));
		$package = str_replace($vars, $values, $package);

		$download_file = $this->download_url($package);

		if ( is_wp_error($download_file) )
			return new WP_Error('download_failed', $this->strings['download_failed'], $download_file->get_error_message());

		return $download_file;
	}

	public function download_url ( $url ) {
		//WARNING: The file is not automatically deleted, The script must unlink() the file.
		if ( ! $url )
			return new WP_Error('http_no_url', __('Invalid URL Provided'));

		$request = parse_url($url);
		parse_str($request['query'], $query);
		$tmpfname = wp_tempnam($query['update'].".zip");
		if ( ! $tmpfname )
			return new WP_Error('http_no_file', __('Could not create Temporary file'));

		$handle = @fopen($tmpfname, 'wb');
		if ( ! $handle )
			return new WP_Error('http_no_file', __('Could not create Temporary file'));

		$response = wp_remote_get($url, array('timeout' => 300));

		if ( is_wp_error($response) ) {
			fclose($handle);
			unlink($tmpfname);
			return $response;
		}

		if ( $response['response']['code'] != '200' ){
			fclose($handle);
			unlink($tmpfname);
			return new WP_Error('http_404', trim($response['response']['message']));
		}

		fwrite($handle, $response['body']);
		fclose($handle);

		return $tmpfname;
	}

	public function unpack_package($package, $delete_package = true, $clear_working = true) {
		global $wp_filesystem;

		$this->skin->feedback('unpack_package');

		$upgrade_folder = $wp_filesystem->wp_content_dir() . 'upgrade/';

		//Clean up contents of upgrade directory beforehand.
		if ($clear_working) {
			$upgrade_files = $wp_filesystem->dirlist($upgrade_folder);
			if ( !empty($upgrade_files) ) {
				foreach ( $upgrade_files as $file )
					$wp_filesystem->delete($upgrade_folder . $file['name'], true);
			}
		}

		//We need a working directory
		$working_dir = $upgrade_folder . basename($package, '.zip');

		// Clean up working directory
		if ( $wp_filesystem->is_dir($working_dir) )
			$wp_filesystem->delete($working_dir, true);

		// Unzip package to working directory
		$result = unzip_file($package, $working_dir); //TODO optimizations, Copy when Move/Rename would suffice?

		// Once extracted, delete the package if required.
		if ( $delete_package )
			unlink($package);

		if ( is_wp_error($result) ) {
			$wp_filesystem->delete($working_dir, true);
			return $result;
		}
		$this->working_dir = $working_dir;

		return $working_dir;
	}

}

/**
 * ShoppCore_Upgrader class
 *
 * Adds auto-update support for the core plugin.
 *
 * Extensions derived from the WordPress WP_Upgrader & Plugin_Upgrader classes:
 * @see wp-admin/includes/class-wp-upgrader.php
 *
 * @copyright WordPress {@link http://codex.wordpress.org/Copyright_Holders}
 *
 * @author Jonathan Davis
 * @since 1.1
 * @package shopp
 * @subpackage installation
 **/
class ShoppCore_Upgrader extends Shopp_Upgrader {

	public function upgrade_strings() {
		$this->strings['up_to_date'] = __('Shopp is at the latest version.', 'Shopp');
		$this->strings['no_package'] = __('Shopp upgrade package not available.', 'Shopp');
		$this->strings['downloading_package'] = sprintf(__('Downloading update from <span class="code">%s</span>.'), SHOPP_HOME);
		$this->strings['unpack_package'] = __('Unpacking the update.', 'Shopp');
		$this->strings['deactivate_plugin'] = __('Deactivating Shopp.', 'Shopp');
		$this->strings['remove_old'] = __('Removing the old version of Shopp.', 'Shopp');
		$this->strings['remove_old_failed'] = __('Could not remove the old Shopp.', 'Shopp');
		$this->strings['process_failed'] = __('Shopp upgrade Failed.', 'Shopp');
		$this->strings['process_success'] = __('Shopp upgraded successfully.', 'Shopp');
	}

	public function upgrade($plugin) {
		$this->init();
		$this->upgrade_strings();

		$current = shopp_setting('updates');
		if ( !isset( $current->response[ $plugin ] ) ) {
			$this->skin->set_result(false);
			$this->skin->error('up_to_date');
			$this->skin->after();
			return false;
		}

		// Get the URL to the zip file
		$r = $current->response[ $plugin ];

		add_filter('upgrader_pre_install', array(&$this, 'addons'), 10, 2);
		// add_filter('upgrader_pre_install', array(&$this, 'deactivate_plugin_before_upgrade'), 10, 2);
		add_filter('upgrader_clear_destination', array(&$this, 'delete_old_plugin'), 10, 4);

		// Turn on Shopp's maintenance mode
		shopp_set_setting('maintenance', 'on');

		$this->run(array(
					'package' => $r->package,
					'destination' => WP_PLUGIN_DIR,
					'clear_destination' => true,
					'clear_working' => true,
					'hook_extra' => array(
					'plugin' => $plugin
					)
				));

		// Cleanup our hooks, incase something else does a upgrade on this connection.
		remove_filter('upgrader_pre_install', array(&$this, 'addons'));
		// remove_filter('upgrader_pre_install', array(&$this, 'deactivate_plugin_before_upgrade'));
		remove_filter('upgrader_clear_destination', array(&$this, 'delete_old_plugin'));

		if ( ! $this->result || is_wp_error($this->result) )
			return $this->result;

		// Turn off Shopp's maintenance mode
		shopp_set_setting('maintenance', 'off');

		// Force refresh of plugin update information
		shopp_set_setting('updates', false);
	}

	public function addons ($return, $plugin) {
		$current = shopp_setting('updates');

		if ( !isset( $current->response[ $plugin['plugin'].'/addons' ] ) ) return $return;
		$addons = $current->response[ $plugin['plugin'].'/addons' ];

		if (count($addons) > 0) {
			$upgrader = new ShoppAddon_Upgrader( $this->skin );
			$upgrader->addon_core_updates($addons, $this->working_dir);
		}
		$this->init(); // Get the current skin controller back for the core upgrader
		$this->upgrade_strings(); // Reinstall our upgrade strings for core
		$this->skin->feedback('<h4>'.__('Finishing Shopp upgrade...', 'Shopp').'</h4>');
	}

}

/**
 * ShoppAddon_Upgrader class
 *
 * Adds auto-update support for individual Shopp add-ons.
 *
 * Extensions derived from the WordPress WP_Upgrader & Plugin_Upgrader classes:
 * @see wp-admin/includes/class-wp-upgrader.php
 *
 * @copyright WordPress {@link http://codex.wordpress.org/Copyright_Holders}
 *
 * @author Jonathan Davis
 * @since 1.1
 * @package shopp
 * @subpackage installation
 **/
class ShoppAddon_Upgrader extends Shopp_Upgrader {

	public $addon = false;
	public $destination = false;

	public function upgrade_strings () {
		$this->strings['up_to_date'] = __('The add-on is at the latest version.', 'Shopp');
		$this->strings['no_package'] = __('Upgrade package not available.');
		$this->strings['downloading_package'] = sprintf(__('Downloading update from <span class="code">%s</span>.'), SHOPP_HOME);
		$this->strings['unpack_package'] = __('Unpacking the update.');
		$this->strings['deactivate_plugin'] = __('Deactivating the add-on.', 'Shopp');
		$this->strings['remove_old'] = __('Removing the old version of the add-on.', 'Shopp');
		$this->strings['remove_old_failed'] = __('Could not remove the old add-on.', 'Shopp');
		$this->strings['process_failed'] = __('Add-on upgrade Failed.', 'Shopp');
		$this->strings['process_success'] = __('Add-on upgraded successfully.', 'Shopp');
		$this->strings['include_success'] = __('Add-on included successfully.', 'Shopp');
	}

	function install_strings() {
		$this->strings['no_package'] = __('Install package not available.');
		$this->strings['downloading_package'] = __('Downloading install package from <span class="code">%s</span>&#8230;');
		$this->strings['unpack_package'] = __('Unpacking the package&#8230;');
		$this->strings['installing_package'] = __('Installing the Shopp add-on&#8230;');
		$this->strings['process_failed'] = __('Shopp add-on install failed.');
		$this->strings['process_success'] = __('Shopp add-on installed successfully.');
	}

	public function install ($package) {

		$this->init();
		$this->install_strings();

		$this->run(array(
					'package' => $package,
					'destination' => SHOPP_ADDONS,
					'clear_destination' => false, //Do not overwrite files.
					'clear_working' => true,
					'hook_extra' => array()
					));

		// Force refresh of plugin update information
		shopp_set_setting('updates', false);

	}

	public function addon_core_updates ($addons, $working_core) {

		$this->init();
		$this->upgrade_strings();

		$current = shopp_setting('updates');

		add_filter('upgrader_destination_selection', array(&$this, 'destination_selector'), 10, 2);

		$all = count($addons);
		$i = 1;
		foreach ($addons as $addon) {

			// Get the URL to the zip file
			$this->addon = $addon->slug;

			$this->show_before = sprintf( '<h4>' . __('Updating addon %1$d of %2$d...') . '</h4>', $i++, $all );

			$this->run(array(
						'package' => $addon->package,
						'destination' => SHOPP_ADDONS,
						'clear_working' => false,
						'with_core' => true,
						'hook_extra' => array(
							'addon' => $addon
						)
			));
		}

		// Cleanup our hooks, in case something else does an upgrade on this connection.
		remove_filter('upgrader_destination_selection', array(&$this, 'destination_selector'));

		if ( ! $this->result || is_wp_error($this->result) )
			return $this->result;

	}

	public function upgrade ($addon, $type) {
		$this->init();
		$this->upgrade_strings();

		$current = shopp_setting('updates');
		if ( ! isset( $current->response[ SHOPP_PLUGINFILE.'/addons' ][ $addon ] ) ) {
			$this->skin->set_result(false);
			$this->skin->error('up_to_date');
			$this->skin->after();
			return false;
		}

		// Get the URL to the zip file
		$r = $current->response[ SHOPP_PLUGINFILE.'/addons' ][$addon];
		$this->addon = $r->slug;

		add_filter('upgrader_destination_selection', array(&$this, 'destination_selector'), 10, 2);

		$this->run(array(
			'package' => $r->package,
			'destination' => SHOPP_ADDONS,
			'clear_destination' => true,
			'clear_working' => true,
			'hook_extra' => array(
				'addon' => $addon
			)
		));

		// Cleanup our hooks, in case something else does an upgrade on this connection.
		remove_filter('upgrader_destination_selection', array(&$this, 'destination_selector'));

		if ( ! $this->result || is_wp_error($this->result) )
			return $this->result;

		// Force refresh of plugin update information
		shopp_set_setting('updates', false);
	}

	public function run ($options) {
		global $wp_filesystem;
		$defaults = array(
			'package' => '', //Please always pass this.
			'destination' => '', //And this
			'clear_destination' => false,
			'clear_working' => true,
			'is_multi' => false,
			'with_core' => false,
			'hook_extra' => array() //Pass any extra $hook_extra args here, this will be passed to any hooked filters.
		);

		$options = wp_parse_args($options, $defaults);
		extract($options);

		//Connect to the Filesystem first.
		$res = $this->fs_connect( array(WP_CONTENT_DIR, $destination) );
		if ( ! $res ) //Mainly for non-connected filesystem.
			return false;

		if ( is_wp_error($res) ) {
			$this->skin->error($res);
			return $res;
		}

		if ( !$with_core ) // call $this->header separately if running multiple times
			$this->skin->header();

		$this->skin->before();

		//Download the package (Note, This just returns the filename of the file if the package is a local file)
		$download = $this->download_package( $package );
		if ( is_wp_error($download) ) {
			$this->skin->error($download);
			return $download;
		}

		//Unzip's the file into a temporary directory
		$working_dir = $this->unpack_package( $download, true, ($with_core)?false:true );
		if ( is_wp_error($working_dir) ) {
			$this->skin->error($working_dir);
			return $working_dir;
		}

		// Determine the final destination
		$source_files = array_keys( $wp_filesystem->dirlist($working_dir) );
		if ( 1 == count($source_files)) {
			$this->destination = $source_files[0];
			if ($wp_filesystem->is_dir(trailingslashit($destination) . trailingslashit($source_files[0])))
				$destination = trailingslashit($destination) . trailingslashit($source_files[0]);
			// else $destination = trailingslashit($destination) . $source_files[0];
		}

		//With the given options, this installs it to the destination directory.
		$result = $this->install_package( array(
			'source' => $working_dir,
			'destination' => $destination,
			'clear_destination' => $clear_destination,
			'clear_working' => $clear_working,
			'hook_extra' => $hook_extra
		) );

		$this->skin->set_result($result);

		if ( is_wp_error($result) ) {
			$this->skin->error($result);
			$this->skin->feedback('process_failed');
		} else {
			// Install Suceeded
			if ($with_core) $this->skin->feedback('include_success');
			else $this->skin->feedback('process_success');
		}

		if ( !$with_core ) {
			$this->skin->after();
			$this->skin->footer();
		}

		return $result;
	}

	public function plugin_info () {
		if ( ! is_array($this->result) )
			return false;
		if ( empty($this->result['destination_name']) )
			return false;

		$plugin = get_plugins('/' . $this->result['destination_name']); //Ensure to pass with leading slash
		if ( empty($plugin) )
			return false;

		$pluginfiles = array_keys($plugin); //Assume the requested plugin is the first in the list

		return $this->result['destination_name'] . '/' . $pluginfiles[0];
	}

	public function install_package ($args = array()) {
		global $wp_filesystem;
		$defaults = array( 'source' => '', 'destination' => '', //Please always pass these
						'clear_destination' => false, 'clear_working' => false,
						'hook_extra' => array());

		$args = wp_parse_args($args, $defaults);
		extract($args);

		@set_time_limit( 300 );

		if ( empty($source) || empty($destination) )
			return new WP_Error('bad_request', $this->strings['bad_request']);

		$this->skin->feedback('installing_package');

		$res = apply_filters('upgrader_pre_install', true, $hook_extra);
		if ( is_wp_error($res) )
			return $res;

		//Retain the Original source and destinations
		$remote_source = $source;
		$local_destination = $destination;

		$source_isdir = true;
		$source_files = array_keys( $wp_filesystem->dirlist($remote_source) );
		$remote_destination = $wp_filesystem->find_folder($local_destination);

		//Locate which directory to copy to the new folder, This is based on the actual folder holding the files.
		if ( 1 == count($source_files) && $wp_filesystem->is_dir( trailingslashit($source) . $source_files[0] . '/') ) //Only one folder? Then we want its contents.
			$source = trailingslashit($source) . trailingslashit($source_files[0]);
		elseif ( count($source_files) == 0 )
				return new WP_Error('bad_package', $this->strings['bad_package']); //There are no files?
		else $source_isdir = false; //Its only a single file, The upgrader will use the foldername of this file as the destination folder. foldername is based on zip filename.

		//Hook ability to change the source file location..
		$source = apply_filters('upgrader_source_selection', $source, $remote_source, $this);
		if ( is_wp_error($source) )
			return $source;

		//Has the source location changed? If so, we need a new source_files list.
		if ( $source !== $remote_source )
			$source_files = array_keys( $wp_filesystem->dirlist($source) );

		//Protection against deleting files in any important base directories.
		if ((
			in_array( $destination, array(ABSPATH, WP_CONTENT_DIR, WP_PLUGIN_DIR, WP_CONTENT_DIR . '/themes', SHOPP_ADDONS, SHOPP_GATEWAYS, SHOPP_SHIPPING, SHOPP_STORAGE) ) ||
			in_array( basename($destination), array(basename(SHOPP_GATEWAYS), basename(SHOPP_SHIPPING), basename(SHOPP_STORAGE)) )
		) && $source_isdir) {
			$remote_destination = trailingslashit($remote_destination) . trailingslashit(basename($source));
			$destination = trailingslashit($destination) . trailingslashit(basename($source));
		}

		// Clear destination
		if ( $wp_filesystem->is_dir($remote_destination) && $source_isdir ) {
			if ( $clear_destination ) {
				//We're going to clear the destination if theres something there
				$this->skin->feedback('remove_old');
				$removed = $wp_filesystem->delete($remote_destination, true);
				$removed = apply_filters('upgrader_clear_destination', $removed, $local_destination, $remote_destination, $hook_extra);

				if ( is_wp_error($removed) )
					return $removed;
				else if ( ! $removed )
					return new WP_Error('remove_old_failed', $this->strings['remove_old_failed']);
			} else {
				//If we're not clearing the destination folder and something exists there allready, Bail.
				//But first check to see if there are actually any files in the folder.
				$_files = $wp_filesystem->dirlist($remote_destination);
				if ( ! empty($_files) ) {
					$wp_filesystem->delete($remote_source, true); //Clear out the source files.
					return new WP_Error('folder_exists', $this->strings['folder_exists'], $remote_destination );
				}
			}
		}

		// Create destination if needed
		if (!$wp_filesystem->exists($remote_destination) && $source_isdir) {
			if (!$wp_filesystem->mkdir($remote_destination, FS_CHMOD_DIR) )
				return new WP_Error('mkdir_failed', $this->strings['mkdir_failed'], $remote_destination);
		}

		// Copy new version of item into place.
		$result = copy_dir($source, $remote_destination);
		if ( is_wp_error($result) ) {
			if ( $clear_working )
				$wp_filesystem->delete($remote_source, true);
			return $result;
		}

		//Clear the Working folder?
		if ( $clear_working )
			$wp_filesystem->delete($remote_source, true);

		$destination_name = basename( str_replace($local_destination, '', $destination) );
		if ( '.' == $destination_name )
			$destination_name = '';

		$this->result = compact('local_source', 'source', 'source_name', 'source_files', 'destination', 'destination_name', 'local_destination', 'remote_destination', 'clear_destination', 'delete_source_dir');

		$res = apply_filters('upgrader_post_install', true, $hook_extra, $this->result);
		if ( is_wp_error($res) ) {
			$this->result = $res;
			return $res;
		}

		//Bombard the calling function will all the info which we've just used.
		return $this->result;
	}

	public function source_selector ($source, $remote_source) {
		global $wp_filesystem;

		$source_files = array_keys( $wp_filesystem->dirlist($source) );
		if (count($source_files) == 1) $source = trailingslashit($source).$source_files[0];

		return $source;
	}

	public function destination_selector ($destination, $remote_destination) {
		global $wp_filesystem;

		if (strpos(basename($destination), '.tmp') !== false)
			$destination = trailingslashit(dirname($destination));

		return $destination;
	}

}

/**
 * Shopp_Upgrader_Skin class
 *
 * Shopp-ifies the auto-upgrade process.
 *
 * Extensions derived from the WordPress Plugin_Upgrader_Skin class:
 * @see wp-admin/includes/class-wp-upgrader.php
 *
 * @copyright WordPress {@link http://codex.wordpress.org/Copyright_Holders}
 * @author Jonathan Davis
 * @since 1.1
 * @package shopp
 * @subpackage installation
 **/
class Shopp_Upgrader_Skin extends Plugin_Upgrader_Skin {

	/**
	 * Custom heading for Shopp
	 *
	 * @author Jonathan Davis
	 * @since 1.1
	 *
	 * @return void
	 **/
	public function header() {
		if ( $this->done_header )
			return;
		$this->done_header = true;
		echo '<div class="wrap shopp">';
		echo screen_icon();
		echo '<h2>' . $this->options['title'] . '</h2>';
	}

	/**
	 * Displays a return to plugins page button after installation
	 *
	 * @author Jonathan Davis
	 * @since 1.1
	 *
	 * @return void
	 **/
	public function after() {
		$this->feedback('<a href="' . admin_url('plugins.php') . '" title="' . esc_attr__('Return to Plugins page') . '" target="_parent" class="button-secondary">' . __('Return to Plugins page') . '</a>');
	}

} // END class Shopp_Upgrader_Skin