<?php
namespace Rey\Libs;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Plugins {

	/**
	 * Holds all registered plugins.
	 *
	 * @var array
	 */
	private $plugins = [];

	const PLUGIN_LIST_OPTION = 'rey_plugins_list';

	public function __construct() {
		$this->set_the_plugins();
	}

	private function set_the_plugins( $refresh = false ) {

		if( $refresh ){
			delete_option( self::PLUGIN_LIST_OPTION );
		}

		$this->plugins = get_option( self::PLUGIN_LIST_OPTION, [] );

		if( empty($this->plugins) )
		{
			if( \ReyTheme_Base::get_purchase_code() )
			{
				$request = \ReyTheme_API::getInstance()->get_plugins();

				if ( ! is_wp_error( $request ) )
				{
					if ( isset($request['data']) && is_array($request['data']) && !empty($request['data'])) {
						$this->plugins = rey__clean($request['data']);
					}
				}
			}

			if( is_array($this->plugins) && empty($this->plugins) ){
				$this->plugins = self::default_plugins_list();
			}

			update_option( self::PLUGIN_LIST_OPTION, $this->plugins, false );
		}

		// starting with 2.6.0 OCDI is removed
		if( \Rey\Plugins::version_swap() ){
			unset($this->plugins['one-click-demo-import']);
		}

		if( $this->plugins ){

			foreach ($this->plugins as $slug => $plugin_data) {

				// not installed
				// installed = inactive
				// active

				$status = false; // not installed

				if ( $this->is_plugin_active( $slug ) ) {
					$status = 'active';
				}
				else if ( $this->is_plugin_installed( $slug ) ) {
					$status = 'inactive';
				}

				$this->plugins[$slug]['status'] = $status;
				$this->plugins[$slug]['installed'] = $status !== false;
				$this->plugins[$slug]['active'] = $status === 'active';
			}

		}
	}

	public function get_plugins(){
		return $this->plugins;
	}

	public function refresh(){

		// refresh the plugins list
		$this->set_the_plugins(true);

		// Checks for available updates to plugins based on the latest versions hosted on WordPress.org.
		wp_update_plugins();

		return $this->plugins;
	}

	public static function default_plugins_list(){

		$plugins = [];

		$plugins_json_file = __DIR__ . '/plugins.json';

		if(	($wp_filesystem = rey__wp_filesystem()) && $wp_filesystem->is_file( $plugins_json_file ) ) {
			if( $json_raw = $wp_filesystem->get_contents( $plugins_json_file ) ){
				$plugins = json_decode($json_raw, true );
			}
		}

		return array_map('rey__clean', $plugins);
	}

	/**
	 * AJAX callback for installing a plugin.
	 * Has to contain the `slug` POST parameter.
	 */
	public function install_plugin_ajax() {

		// Check if user has the WP capability to install plugins.
		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json_error( esc_html__( 'Could not install the plugin. You don\'t have permission to install plugins.', 'rey' ) );
		}

		$slug = ! empty( $_POST['slug'] ) ? sanitize_key( wp_unslash( $_POST['slug'] ) ) : '';

		if ( empty( $slug ) ) {
			wp_send_json_error( esc_html__( 'Could not install the plugin. Plugin slug is missing.', 'rey' ) );
		}

		// Check if the plugin is already installed and activated.
		if ( $this->is_plugin_active( $slug ) ) {
			wp_send_json_success( esc_html__( 'Plugin is already installed and activated!', 'rey' ) );
		}

		// Activate the plugin if the plugin is already installed.
		if ( $this->is_plugin_installed( $slug ) ) {

			$activated = $this->activate_plugin( self::get_plugin_basename_from_slug( $slug ), $slug );

			if ( ! is_wp_error( $activated ) ) {
				wp_send_json_success( esc_html__( 'Plugin was already installed! We activated it for you.', 'rey' ) );
			} else {
				wp_send_json_error( $activated->get_error_message() );
			}
		}

		// Do not allow WordPress to search/download translations, as this will break JS output.
		remove_action( 'upgrader_process_complete', [ 'Language_Pack_Upgrader', 'async_upgrade' ], 20 );

		if( ! ($source = $this->get_download_url( $slug )) ){
			wp_send_json_error( esc_html__( 'Cannot generate download url.', 'rey' ) );
		}

		require_once __DIR__ . '/plugin-installer-skin.php';

		if ( ! class_exists( '\Plugin_Upgrader', false ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		}

		$upgrader = new \Plugin_Upgrader( new PluginInstallerSkin() );

		$upgrader->install( $source );

		// Flush the cache and return the newly installed plugin basename.
		wp_cache_flush();

		if ( $upgrader->plugin_info() ) {

			$activated = $this->activate_plugin( $upgrader->plugin_info(), $slug );

			if ( ! is_wp_error( $activated ) ) {
				wp_send_json_success(
					esc_html__( 'Plugin installed and activated succesfully.', 'rey' )
				);
			} else {
				wp_send_json_success( $activated->get_error_message() );
			}

		}

		wp_send_json_error( esc_html__( 'Could not install the plugin. WP Plugin installer could not retrieve plugin information.', 'rey' ) );
	}

	/**
	 * Direct plugin install, without AJAX responses.
	 *
	 * @param string $slug The registered plugin slug to install.
	 *
	 * @return bool
	 */
	public function install_plugin( $slug ) {

		if ( empty( $slug ) ) {
			error_log('Empty slug while installing plugin.');
			return false;
		}

		// Check if user has the WP capability to install plugins.
		if ( ! current_user_can( 'install_plugins' ) ) {
			error_log('No permissions to install plugin.');
			return false;
		}

		// Check if the plugin is already installed and activated.
		if ( $this->is_plugin_active( $slug ) ) {
			return true;
		}

		// Activate the plugin if the plugin is already installed.
		if ( $this->is_plugin_installed( $slug ) ) {
			$activated = $this->activate_plugin( self::get_plugin_basename_from_slug( $slug ), $slug );
			return ! is_wp_error( $activated );
		}

		ini_set('display_errors', '0');
		ini_set('error_reporting', E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

		// Do not allow WordPress to search/download translations
		remove_action( 'upgrader_process_complete', [ 'Language_Pack_Upgrader', 'async_upgrade' ], 20 );

		if( ! ($source = $this->get_download_url( $slug )) ){
			error_log(sprintf('Cannot download %s plugin.', $slug));
			return false;
		}

		require_once __DIR__ . '/plugin-installer-skin-silent.php';

		if ( ! class_exists( '\Plugin_Upgrader', false ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		}

		$upgrader = new \Plugin_Upgrader( new PluginInstallerSkinSilent() );

		try {
			$upgrader->install( $source );
		}
		catch ( \Exception $e ) {
			error_log($e->getMessage());
			return false;
		}

		// Flush the cache and return the newly installed plugin basename.
		wp_cache_flush();

		if ( $upgrader->plugin_info() ) {
			$activated = $this->activate_plugin( $upgrader->plugin_info(), $slug );
			if ( ! is_wp_error( $activated ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Activate a plugin.
	 *
	 * @param string $plugin_path The plugin file path.
	 * @param bool   $silent      Whether to suppress activation hooks.
	 *
	 * @return bool|WP_Error True if the plugin was activated, false if not, or a WP_Error object if there was an error.
	 */
	public function activate_plugin($plugin_path, $silent = false){

		if( ! $plugin_path ){
			return false;
		}

		if ( ! function_exists( 'activate_plugin' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		ini_set('display_errors', '0');
		ini_set('error_reporting', E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

		$activate = activate_plugin( $plugin_path, '', false, $silent );

		if( is_wp_error($activate) ){
			return $activate;
		}

		return is_null($activate);
	}

	public function activate_plugin_by_slug($slug, $silent = false){
		return $this->activate_plugin( self::get_plugin_basename_from_slug( $slug ), $silent );
	}

	public function deactivate_plugin($plugin_path){

		if( ! $plugin_path ){
			return false;
		}

		if ( ! function_exists( 'deactivate_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return deactivate_plugins( $plugin_path );
	}

	public function deactivate_plugin_by_slug($slug){
		return $this->deactivate_plugin( self::get_plugin_basename_from_slug( $slug ) );
	}

	public function install_activate_plugins( $plugins = [] ){

		if( ! current_user_can('install_plugins') ){
			return ['error' => esc_html__('You\'re not allowed to install plugins!', 'rey')];
		}

		$errors = [];
		$activated = [];

		foreach ( (array) $plugins as $slug) {

			if( ! isset($this->plugins[$slug]) ){
				$errors[$slug] = sprintf(esc_html__('Cannot find "%s" plugin.', 'rey'), $slug);
				continue;
			}

			// install
			if ( false === $this->plugins[$slug]['status'] ) {
				if( $this->install_plugin($slug) ){
					$activated[$slug] = $this->plugins[$slug];
				}
				else {
					$errors[$slug] = sprintf(esc_html__('Cannot install "%s" plugin.', 'rey'), $this->plugins[$slug]['name']);
				}
			}

			// activate
			else if ( 'inactive' === $this->plugins[$slug]['status'] ) {
				if( ! is_wp_error( $this->activate_plugin_by_slug($slug) ) ){
					$activated[$slug] = $this->plugins[$slug];
				}
				else {
					$errors[$slug] = sprintf(esc_html__('Cannot activate "%s" plugin.', 'rey'), $this->plugins[$slug]['name']);
				}
			}
		}

		if( ! empty($errors) ){
			return ['error' => implode(' ', $errors)];
		}

		return $activated;
	}

	/**
	 * Update plugins based on their slugs
	 *
	 * @param array $slugs
	 * @return false|array basename=>version
	 */
	public function upgrade_plugins_by_slug($slugs = []) {

		require_once __DIR__ . '/plugin-installer-skin-silent.php';

		if ( ! class_exists( '\Plugin_Upgrader', false ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		}

		$upgrader = new \Plugin_Upgrader( new PluginInstallerSkinSilent() );

		$plugins = [];

		foreach ($slugs as $slug) {
			if( $basename = self::get_plugin_basename_from_slug( $slug ) ){
				$plugins[] = $basename;
			}
		}

		if( ! $result = $upgrader->bulk_upgrade( $plugins ) ){
			return;
		}

		$plugins_versions = [];

		foreach ($result as $file_path => $value) {
			$plugin_data = get_file_data( trailingslashit(WP_PLUGIN_DIR) . $file_path, ['Version' => 'Version'], false);
			if( ! empty($plugin_data['Version']) ){
				$plugins_versions[$file_path] = $plugin_data['Version'];
			}
		}

		return $plugins_versions;
	}

	/**
	 * Get the data of a registered plugin via the slug.
	 *
	 * @param string $slug The plugin slug.
	 *
	 * @return array
	 */
	public function get_plugin_data( $slug ) {

		if( isset($this->plugins[$slug]) ){
			return $this->plugins[$slug];
		}

		return [];
	}

	public static function get_plugin_version( $data, $by = 'file_path' ){

		$installed_plugins = self::get_wp_plugins();

		if( 'slug' === $by ){
			$file_path = self::get_plugin_basename_from_slug($data);
		}

		else if( 'file_path' === $by ){
			$file_path = $data;
		}

		if ( ! empty( $installed_plugins[ $file_path  ]['Version'] ) ) {
			return $installed_plugins[ $file_path  ]['Version'];
		}

		return '';
	}

	public static function get_plugin_update_status( $plugin_data ){

		if ( ! current_user_can( 'update_plugins' ) ) {
			return;
		}

		static $update_plugins;

		if( is_null($update_plugins) ){
			$update_plugins = get_site_transient( 'update_plugins' );
		}

		if ( ! $update_plugins ) {
			return;
		}

		if ( ! isset($update_plugins->response) ) {
			return;
		}

		if ( ! isset($update_plugins->response[ $plugin_data['file_path'] ]) ) {
			return;
		}

		if( ! ($updatable_plugin = (array) $update_plugins->response[ $plugin_data['file_path'] ]) ){
			return;
		}

		if( ! isset($updatable_plugin['new_version']) ){
			return;
		}

		if( ! version_compare( $updatable_plugin['new_version'], $plugin_data['version'], '>' ) ){
			return;
		}

		return $updatable_plugin['new_version'];
	}

	/**
	 * Get the download URL for a plugin.
	 *
	 * @param  string $slug Plugin slug.
	 *
	 * @return string Plugin download URL.
	 */
	public function get_download_url( $slug ) {

		$plugin_data = $this->get_plugin_data( $slug );

		if ( ! empty( $plugin_data['download_url'] ) ) {
			$download_url = '';
			$plugin_data = \ReyTheme_API::getInstance()->get_plugin_data( $slug );
			if( ! is_wp_error($plugin_data) ) {
				if( isset($plugin_data['data']['download_url']) && ! empty($plugin_data['data']['download_url']) ) {
					$download_url = esc_url_raw( $plugin_data['data']['download_url'] );
				}
			}
			return $download_url;
		}

		return sprintf('https://downloads.wordpress.org/plugin/%s.latest-stable.zip', $slug);
	}

	/**
	 * Wrapper around the core WP get_plugins function, making sure it's actually available.
	 *
	 * @param string $plugin_folder Optional. Relative path to single plugin folder.
	 *
	 * @return array Array of installed plugins with plugin information.
	 */
	public static function get_wp_plugins() {

		static $plugins;

		if( is_null($plugins) ){

			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			$plugins = get_plugins();
		}

		return $plugins;
	}

	/**
	 * Helper function to extract the plugin file path from the
	 * plugin slug, if the plugin is installed.
	 *
	 * @param string $slug Plugin slug (typically folder name) as provided by the developer.
	 *
	 * @return string|bool Either plugin file path for plugin if installed, or false.
	 */
	protected static function get_plugin_basename_from_slug( $slug ) {

		$keys = array_keys( self::get_wp_plugins() );

		foreach ( $keys as $key ) {
			if ( preg_match( '/^' . $slug . '\//', $key ) ) {
				return $key;
			}
		}

		return false;
	}

	/**
	 * Check if a plugin is installed. Does not take must-use plugins into account.
	 *
	 * @param string $slug Plugin slug.
	 *
	 * @return bool True if installed, false otherwise.
	 */
	public function is_plugin_installed( $slug ) {
		return ( ! empty( self::get_plugin_basename_from_slug( $slug ) ) );
	}

	/**
	 * Check if a plugin is active.
	 *
	 * @param string $slug Plugin slug.
	 *
	 * @return bool True if active, false otherwise.
	 */
	public function is_plugin_active( $slug ) {

		$plugin_path = self::get_plugin_basename_from_slug( $slug );

		if ( empty( $plugin_path ) ) {
			return false;
		}

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return is_plugin_active( $plugin_path );
	}

}
