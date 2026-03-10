<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists('ReyTheme_Updates') ):
	/**
	 * Rey Theme & Rey plugins update manager.
	 *
	 * @since 1.0.0
	 */
	class ReyTheme_Updates
	{
		const OPTION_CLEANUP_NEEDED = 'rey_needs_cleanup';

		const REY_VERSION = 'rey_version';

		const VERSIONS_TRANSIENT = 'rey_versions_data';

		public function __construct()
		{
			$this->maybe_delete_transients();

			add_filter( 'pre_set_site_transient_update_themes', [ $this, 'transient_update_theme' ] );
			add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'transient_update_plugins' ] );
			add_action( 'delete_site_transient_update_plugins', [ $this, 'delete_transients' ] );
			add_action( 'upgrader_process_complete', [ $this, 'after_theme_core_update' ], 10, 2 );
			add_action( 'upgrader_overwrote_package', [ $this, 'after_theme_core_upgrader_overwrote_package' ], 10, 3 );
			add_action( 'admin_init', [$this, 'flush_rey_cache_after_updates'], 20);
			add_action( 'rey/flush_cache_after_updates', [$this, 'delete_cleanup_option']);
			add_action( 'rey/flush_cache_after_updates', [$this, 'recheck_theme_updates']);
			add_filter( 'plugins_api', [$this, 'get_changelog'], 10, 3);
			add_filter( 'update_theme_complete_actions', [$this, 'fix_activate_link_on_update_theme'], 20, 2);

			add_action('rey/dashboard/box/versions', [$this, 'add_theme_version_box'], 10);
			add_action('rey/dashboard/box/versions', [$this, 'version_box_separator'], 19);
			add_action('rey/dashboard/box/versions', [$this, 'add_core_version_box'], 20);

			add_filter( 'rey/admin_script_params', [$this, 'js_params']);
			add_filter( 'rey/file_remote_get/timestamp', [$this, 'ensure_timestamp_in_versions']);
			add_action( 'wp_ajax_rey_dashbox_do_update', [ $this, 'ajax__dashbox_run_update' ] );
			add_action( 'wp_ajax_rey_dashbox_versions_check_update', [ $this, 'ajax__dashbox_check_update' ] );
			add_action( 'wp_ajax_rey_dashbox_flush_versions', [ $this, 'ajax__dashbox_flush_versions' ] );
			add_action( 'wp_ajax_rey_needs_update', [ $this, 'ajax__needs_update' ] );

		}

		/**
		 * Refresh plugin list when flushing site transient updates
		 *
		 * @return void
		 */
		public function delete_transients() {
			\Rey\Plugins::delete_plugins_list();
		}

		private function maybe_delete_transients() {
			global $pagenow;
			if ( 'update-core.php' === $pagenow && isset( $_GET['force-check'] ) && 1 === absint($_GET['force-check']) ) {
				$this->delete_transients();
			}
		}

		/**
		 * After Rey theme or Core were updated.
		 *
		 * Adds a "need cache flush".
		 *
		 * @since 1.0.0
		 */
		public function after_theme_core_update( $upgrader_object, $options ) {

			$cleanup = false;

			if ( ! (isset($options['action']) && $options['action'] === 'update') ){
				return;
			}

			if (! isset($options['type'])){
				return;
			}

			if( $options['type'] === 'plugin' ){

				$plugins = isset($options['plugins']) && !empty($options['plugins']) ? $options['plugins'] : [];

				// coming from TGMPA
				if( isset($options['plugin']) && !empty($options['plugin']) ){
					$plugins = (array) $options['plugin'];
				}

				foreach($plugins as $plugin){

					if ( $plugin !== rey__get_core_path()){
						continue;
					}

					$this->after_core_update();

					$cleanup = true;
				}
			}

			elseif ( $options['type'] === 'theme' ){

				$themes = isset($options['themes']) && !empty($options['themes']) ? $options['themes'] : [];

				// coming from TGMPA
				if( isset($options['theme']) && !empty($options['theme']) ){
					$themes = (array) $options['theme'];
				}

				foreach( $themes as $theme ){

					if ( $theme !== REY_THEME_NAME ){
						continue;
					}

					$this->after_theme_update();

					$cleanup = true;

				}
			}

			if( $cleanup ){
				update_site_option(self::OPTION_CLEANUP_NEEDED, true);
			}

		}

		public function after_theme_core_upgrader_overwrote_package( $package, $new_data, $type ) {

			$cleanup = false;

			if (isset($type)){
				if( $type == 'plugin' && isset($new_data['Name']) && $new_data['Name'] === 'Rey Core' ){

					$this->after_core_update();

					$cleanup = true;
				}
				elseif ( $type == 'theme' && isset($new_data['Name']) && $new_data['Name'] === 'Rey' ){

					$this->after_theme_update();

					$cleanup = true;
				}
			}

			if( $cleanup ){
				do_action('rey/flush_cache_after_updates');
			}

		}

		public function after_theme_update(){

			/**
			 * Run action after the theme updates.
			 */
			do_action('rey/after_update/theme');

			/**
			 * Set the new version
			 */
			update_option(self::REY_VERSION, REY_THEME_VERSION);

		}

		public function after_core_update(){
			/**
			 * Run action after Core updates.
			 */
			do_action('rey/after_update/core');
		}

		/**
		 * Flush caches after Rey theme or Core updates.
		 *
		 * @since 1.0.0
		 */
		public function flush_rey_cache_after_updates()
		{
			if( get_site_option(self::OPTION_CLEANUP_NEEDED, false) )
			{
				do_action('rey/flush_cache_after_updates');
				rey__log_error( 'err013', esc_html__('Cache flush after update', 'rey') );
			}
		}

		public function delete_cleanup_option(){
			// delete the option
			return delete_site_option(self::OPTION_CLEANUP_NEEDED);
		}

		public function recheck_theme_updates()
		{
			global $wp_current_filter;

			$wp_current_filter[] = 'load-update-core.php';

			wp_clean_update_cache();
			wp_update_themes();

			array_pop($wp_current_filter);

			do_action('load-plugins.php');
		}

		/**
		 * Reflect Rey Theme updates in WordPress Updates section
		 *
		 * @since 1.0.0
		 */
		public function transient_update_theme( $transient ) {
			if( empty( $transient->checked[ REY_THEME_NAME ] ) ){
				return $transient;
			}

			if( !ReyTheme_Base::get_purchase_code() ) {
				return $transient;
			}

			$theme_data = ReyTheme_API::getInstance()->get_theme_version();

			// check for errors
			if ( is_wp_error( $theme_data ) || (isset($theme_data['success']) && !$theme_data['success']) ) {
				rey__log_error( 'err002', $theme_data );
				return $transient;
			}

			//check server version against current installed version
			if( isset($theme_data['data']['new_version']) && version_compare( REY_THEME_VERSION, $theme_data['data']['new_version'], '<' ) ){
				$transient->response[ REY_THEME_NAME ] = [
					'theme' => REY_THEME_NAME,
					'new_version' => esc_html( $theme_data['data']['new_version'] ),
					'url' => rey__get_theme_url(),
					'package' => ReyTheme_API::getInstance()->get_download_url()
				];
			}

			return $transient;
		}

		/**
		 * Reflect Rey plugin updates in WordPress Updates section
		 *
		 * @since 1.0.0
		 */
		public function transient_update_plugins( $transient ) {

			static $responses;

			if( is_null($responses) )
			{
				$responses = [];

				if( ReyTheme_Base::get_purchase_code() && $rey_plugins = \Rey\Plugins::get_rey_plugins() )
				{
					foreach( $rey_plugins as $plugin )
					{
						if( isset($plugin['file_path']) && !empty( $plugin['file_path'] ) )
						{
							$plugin_path = trailingslashit( WP_PLUGIN_DIR ) . $plugin['file_path'];

							if( is_readable( $plugin_path ) )
							{
								$plugin_data = get_plugin_data( $plugin_path );

								if(
									isset($plugin['version']) &&
									version_compare( $plugin_data['Version'], $plugin['version'], '<' ) )
								{
									$plugin_response = [
										'new_version' => esc_html( $plugin['version'] ),
										'url' => rey__get_theme_url(),
										'id' => $plugin['slug'],
										'slug' => $plugin['slug'],
										'plugin' => $plugin['file_path'],
										'package' => ReyTheme_API::getInstance()->get_download_url($plugin['slug']),
										// 'package' => '', // intentionally empty, is updated in `upgrader_package_options`
									];

									if( isset($plugin['icon']) ){
										$plugin_response['icons']['2x'] = $plugin['icon'];
										$plugin_response['icons']['1x'] = $plugin['icon'];
									}

									$responses[ $plugin['file_path'] ] = (object) $plugin_response;
								}
							}
						}
					}
				}
			}

			if( $responses ){
				foreach ( (array) $responses as $file_path => $response) {
					if( ! is_null($transient) && isset($transient->response) ){
						$transient->response[ $file_path ] = $response;
					}
				}
			}

			return $transient;
		}

		/**
		 * Add admin script params
		 *
		 * @since 1.0.0
		 */
		public function js_params( $params )
		{
			return array_merge($params, [ 'updates_nonce' => wp_create_nonce( 'rey_updates' ), 'state_notice' => get_option( 'rey_state_notice' ) ]);
		}

		/**
		 * Retrieves plugin changelog
		 *
		 * @since 1.0.3
		 */
		public function get_changelog($result, $action, $arg)
		{
			// only for 'plugin_information' action
			if( $action !== 'plugin_information' ) {
				return $result;
			}

			if(
				($plugins = \Rey\Plugins::get_rey_plugins()) &&
				array_key_exists($arg->slug, $plugins) && $plugins[$arg->slug]['type'] === 'rey'
			) {

				$content = get_site_transient('rey_changelog');

				if( !$content ){
					// JSON Page of Changelog
					$response = wp_remote_get( rey__support_url('wp-json/wp/v2/pages/372'), [
						'timeout' => 40,
						'body' => [],
					] );

					if ( is_wp_error( $response ) ) {
						return false;
					}

					$data = json_decode( wp_remote_retrieve_body( $response ), true );

					if ( empty( $data ) || ! is_array( $data ) ) {
						return false;
					}

					$response_code = wp_remote_retrieve_response_code( $response );

					if ( 200 !== (int) $response_code ) {
						return false;
					}

					if( isset($data['content']['rendered']) ){

						$content = $data['content']['rendered'];

						set_site_transient('rey_changelog', $content, DAY_IN_SECONDS);
					}
				}

				$res = (object) [
					'sections' => [
						'changelog' => $content,
					],
					'slug' => $arg->slug,
					'name' => $plugins[$arg->slug]['name']
				];

				return $res;
			}

			return false;
		}

		/**
		 * Will ensure a timestamp is added on the versions request
		 * to prevent caching
		 *
		 * @since 2.4.0
		 */
		function ensure_timestamp_in_versions( $status )
		{
			if( isset($_REQUEST['action']) && 'rey_dashbox_versions_check_update' === $_REQUEST['action'] ){
				return true;
			}
			return $status;
		}

		/**
		 * Checks for new updates
		 *
		 * @param array $adata
		 * @return mixed
		 */
		public function ajax__dashbox_check_update(){

			if ( ! wp_verify_nonce( $_REQUEST['security'], 'rey_updates' ) ) {
				wp_send_json_error('Operation not allowed!');
			}

			if( ! (current_user_can('administrator') || current_user_can('install_plugins')) ){
				wp_send_json_error('Operation not allowed!');
			}

			if ( ! ( isset($_REQUEST['slug']) && $slug = rey__clean($_REQUEST['slug']) ) ) {
				wp_send_json_error('Missing slug.');
			}

			$items = [
				'rey-core' => 'REY_CORE_VERSION',
				'rey' => 'REY_THEME_VERSION',
			];

			if( ! isset( $items[$slug] ) ){
				wp_send_json_error('Item not found.');
			}

			if( ! defined( $items[$slug] ) ){
				wp_send_json_error('Item inactive.');
			}

			if( false === ( $main_versions = get_transient(self::VERSIONS_TRANSIENT) ) ) {
				$main_versions = \ReyTheme_API::getInstance()->get_main_versions();
				set_transient(self::VERSIONS_TRANSIENT, $main_versions, HOUR_IN_SECONDS);
			}

			if( is_wp_error($main_versions) ){
				wp_send_json_error('Failed API request.');
			}

			if( ! (isset($main_versions['success']) && true === $main_versions['success']) ){
				wp_send_json_error('Failed API call.');
			}

			if( ! (isset($main_versions['data'][$slug]) && $version = $main_versions['data'][$slug]) ){
				wp_send_json_error('Version not found.');
			}

			// if no new update, just bail
			if( ! version_compare( $version, constant( $items[$slug] ), '>' ) ){
				wp_send_json_error();
			}

			// Check status code from transient and show

			$data = sprintf('<button class="rey-adminBtn rey-adminBtn-primary --sm-padding rey-updateItem" data-slug="%1$s" data-version="%3$s">%2$s <strong>v%3$s</strong></button>', $slug, esc_html__('Update to ', 'rey'), $version);

			wp_send_json_success($data);

		}

		/**
		 * Runs the update
		 *
		 * @return string
		 */
		public function ajax__dashbox_run_update(){

			if ( ! wp_verify_nonce( $_REQUEST['security'], 'rey_updates' ) ) {
				wp_send_json_error('Operation not allowed!');
			}

			if( ! (current_user_can('administrator') || current_user_can('install_plugins')) ){
				wp_send_json_error('Operation not allowed!');
			}

			if ( ! ( isset($_REQUEST['slug']) && $slug = rey__clean($_REQUEST['slug']) ) ) {
				wp_send_json_error('Missing slug.');
			}

			$items = [
				'rey-core' => [
					'basename' => 'rey-core/rey-core.php',
					'type' => 'core',
				],
				'rey' => [
					'basename' => 'rey',
					'type' => 'theme',
				],
			];

			if( ! isset( $items[$slug] ) ){
				wp_send_json_error('Item not found.');
			}

			if( ! class_exists('\Rey\Upgrader') ){
				require_once __DIR__ . '/upgrader.php';
			}

			$upgrader = new \Rey\Upgrader([
				'slug'     => $slug,
				'basename' => $items[$slug]['basename'],
				'hook'     => 'rey/after_update/' . $items[$slug]['type'],
			]);

			if( ! empty($upgrader->error) ){
				wp_send_json_error( $upgrader->error );
			}

			wp_send_json_success( $upgrader );

		}

		/**
		 * Purge updates cache.
		 * Triggered by the "Check for updates" button in Rey's Dasboard > Version Status box
		 *
		 * @return string
		 */
		public function ajax__dashbox_flush_versions(){

			if ( ! wp_verify_nonce( $_REQUEST['security'], 'rey_updates' ) ) {
				wp_send_json_error('Operation not allowed!');
			}

			if( ! (current_user_can('administrator') || current_user_can('install_plugins')) ){
				wp_send_json_error('Operation not allowed!');
			}

			delete_transient(self::VERSIONS_TRANSIENT);

			\Rey\Plugins::refresh();

			set_site_transient( 'update_plugins', (object)[] );
			set_site_transient( 'update_themes', (object)[] );

			wp_send_json_success();

		}

		public function version_box_separator(){
			?>
			<tr class="__border">
				<td colspan="2"><hr></td>
			</tr><?php
		}

		private function version_box_template($args = []){
			?><tr class="__version" data-slug="<?php echo esc_attr($args['slug']); ?>">
				<th width="160">
					<h4 class="__title"><?php echo wp_kses_post($args['title']); ?></h4>
				</th>
				<td class="__content-cell">
					<div class="__content">
						<span class="__version-no"><?php echo wp_kses_post($args['version']); ?></span>
					</div>
				</td>
			</tr><?php
		}

		public function add_theme_version_box(){

			$this->version_box_template([
				'slug' => 'rey',
				'title' => rey__get_props('theme_title'),
				'version' => REY_THEME_VERSION,
			]);
		}

		public function add_core_version_box(){

			if( ! defined('REY_CORE_VERSION') ){
				return;
			}

			$this->version_box_template([
				'slug' => 'rey-core',
				'title' => rey__get_props('core_title'),
				'version' => REY_CORE_VERSION,
			]);
		}

		/**
		 * Remove the activate button after updating theme (it's already active)
		 *
		 * @param array $update_actions
		 * @param string $theme
		 * @return array
		 */
		public function fix_activate_link_on_update_theme($update_actions, $theme){

			if ( REY_THEME_NAME === $theme && get_template_directory() !== get_stylesheet_directory()) {
				unset($update_actions['activate']);
			}

			return $update_actions;

		}

		public function ajax__needs_update(){

			if( ! current_user_can('update_plugins') ){
				wp_send_json_error();
			}

			$queue = [];

			if( $update_themes = get_site_transient('update_themes') ){
				if( isset($update_themes->response) && $response = $update_themes->response ){
					if( isset($response[REY_THEME_NAME]) ){
						$queue[] = 'theme';
					}
				}
			}

			if( $update_plugins = get_site_transient('update_plugins') ){
				if( isset($update_plugins->response) && $response = $update_plugins->response ){
					if( isset($response[sprintf('%1$s/%1$s.php', REY_THEME_CORE_SLUG)]) ){
						$queue[] = 'core';
					}
				}
			}

			$response = false;

			if( count($queue) === 1 ){
				$response = '<span class="update-plugins count-1"><span class="plugin-count" aria-hidden="true">1</span><span class="screen-reader-text">1 notification</span></span>';
			}

			wp_send_json_success( $response );

		}
	}

	new ReyTheme_Updates;

endif;
