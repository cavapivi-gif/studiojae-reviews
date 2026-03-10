<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( class_exists('ReyTheme_API') && ! class_exists('ReyTheme_Rollback') ):

	class ReyTheme_Rollback extends ReyTheme_API
	{

		const THRESHOLD_VERSION = '2.0.0';

		const VERSION_TRANSIENT = 'rey_rollback_versions_';

		private $can_rollback_theme = true;

		public $items_data;

		public function __construct()
		{
			add_action( 'rey/init', [$this, 'init']);
		}

		function init(){

			$this->items_data = apply_filters('rey/dashboard/box/rollback/items_data', [
				'theme' => [
					'file' => 'rb_theme_versions.json',
					'current_version' => defined('REY_THEME_VERSION') ? REY_THEME_VERSION : false,
					'name' => rey__get_props('theme_title'),
					'slug' => 'rey',
					'basename' => 'rey',
				],
				'rey-core' => [
					'file' => 'rb_core_versions.json',
					'current_version' => defined('REY_CORE_VERSION') ? REY_CORE_VERSION : false,
					'name' => rey__get_props('core_title'),
					'slug' => 'rey-core',
					'basename' => 'rey-core/rey-core.php'
				]
			]);

			add_action( 'rey/dashboard/box/versions', [$this, 'theme_output'], 11);
			add_action( 'rey/dashboard/box/versions', [$this, 'core_output'], 21);
			add_filter( 'rey/admin_script_params', [$this, 'add_js_params']);
			add_action( 'wp_ajax_rey_rollback_version', [$this, 'ajax_rollback']);
			add_action( 'rey/rollback', [$this, 'clean_versions_transients']);
			add_action( 'rey/flush_cache_after_updates', [$this, 'clean_versions_transients']);

			if(
				isset($_REQUEST['page']) && rey__clean($_REQUEST['page']) === 'rey-dashboard' &&
				isset($_REQUEST['clear_versions']) && absint($_REQUEST['clear_versions']) === 1 &&
				current_user_can('administrator')
			 ){
				$this->clean_versions_transients();
			}

		}

		function clean( $var ){

			if( function_exists('rey__clean') ){
				return rey__clean($var);
			}

			else if( function_exists('reycore__clean') ){
				return reycore__clean($var);
			}

		}

		function add_js_params( $params )
		{
			$params = array_merge($params, [
				'ajax_rollback_nonce' => wp_create_nonce( 'rey_rollback_nonce' ),
				'rollback_strings'   => [
					'something_went_wrong' => esc_html_x( 'Something went wrong. Please refresh the page and try again!', 'Rollback texts', 'rey' ),
					'skipping_success' => esc_html_x( 'RELOADING PAGE..', 'Rollback texts', 'rey' ),
					'failed' => esc_html_x( 'FAILED!', 'Rollback texts', 'rey' ),
				],
			]);

			return $params;
		}

		function ajax_rollback(){

			if ( ! check_ajax_referer( 'rey_rollback_nonce', 'security', false ) ) {
				wp_send_json_error( esc_html__('Invalid security nonce!', 'rey') );
			}

			if ( ! current_user_can('administrator') ) {
				wp_send_json_error( esc_html__('Operation not allowed!', 'rey') );
			}

			if ( ! current_user_can('install_plugins') ) {
				wp_send_json_error( esc_html__('Operation not allowed!', 'rey') );
			}

			if( isset($_REQUEST['key']) && ($key = $this->clean($_REQUEST['key'])) && isset($this->items_data[$key]) ){

				if( isset($_REQUEST['version']) && ($version = $this->clean($_REQUEST['version'])) && version_compare( $version, '0.0.1', '>=' ) ){

					$upgrader = new \Rey\Upgrader([
						'slug'     => $this->items_data[$key]['slug'],
						'version'  => $version,
						'basename' => $this->items_data[$key]['basename'],
						'hook'     => 'rey/rollback',
					]);

					if( ! empty($upgrader->error) ){
						wp_send_json_error( $upgrader->error );
					}

					wp_send_json_success( $upgrader );
				}
			}
		}

		public function get_html( $key ){

			$data = $this->items_data[$key];

			if( ! ($versions = $this->get_versions( $data, $key )) ){
				return;
			}

			$options = '<option value="">- Select version -</option>';

			$message = '';

			foreach($versions as $version){

				$disabled_option = '';

				if( $key === 'theme' ){

					if( version_compare( $this->items_data['rey-core']['current_version'], self::THRESHOLD_VERSION, '>=' ) &&
						version_compare( $version, self::THRESHOLD_VERSION, '<' ) ){
						$disabled_option = ' disabled';
						$message = sprintf(esc_html_x('To downgrade %2$s to versions lower than %1$s, %3$s must be downgraded first.', 'Backend dashboard text', 'rey'), self::THRESHOLD_VERSION, rey__get_props('theme_title'), rey__get_props('core_title'));

					}
				}

				$options .= sprintf( '<option value="%1$s" %2$s>%1$s</option>', $version, $disabled_option );
			}


			printf(
				'<label class="__rb-title">%4$s</label><select>%1$s</select><button class="js-dashRollback rey-adminBtn rey-adminBtn-secondary --sm-padding --disabled" data-settings=\'%3$s\'>%2$s</button>',
				$options,
				esc_html_x('Rollback', 'Dashboard text', 'rey'),
				wp_json_encode([
					'key' => $key,
					'name' => $data['name'],
				]),
				esc_html_x('Select version to downgrade:', 'Dashboard text', 'rey')
			);

			echo '<p class="js-dashResponse __response">'.$message.'</p>';
		}

		function clean_versions_transients(){
			foreach( $this->items_data as $key => $item ){
				delete_transient(self::VERSION_TRANSIENT . $key);
			}
		}

		public function get_versions( $data, $key ){

			$transient_name = self::VERSION_TRANSIENT . $key;

			if( $transient = get_transient( $transient_name ) ){
				return $transient;
			}

			$get_versions = $this->file_remote_get( $data['file'] );

			// check for errors
			if ( is_wp_error( $get_versions ) ) {
				rey__log_error( 'err002', $get_versions );
				return;
			}

			if( isset($get_versions['success']) && !$get_versions['success'] ){
				return;
			}

			//check server version against current installed version
			if( !(isset($get_versions['data']) && $versions = array_reverse($get_versions['data'])) ){
				return;
			}

			$clean_versions = [];

			foreach ($versions as $version) {

				if( version_compare( $version, $data['current_version'], '>=' ) ){
					continue;
				}

				$clean_versions[] = $this->clean($version);
			}

			set_transient( $transient_name, $clean_versions, 12 * HOUR_IN_SECONDS );

			return $clean_versions;
		}

		function theme_output(){
			?>
			<tr class="__rollback --theme">
				<th>&nbsp;</th>
				<td><?php $this->get_html( 'theme' ); ?></td>
			</tr>
			<?php
		}

		function core_output(){

			if( ! defined('REY_CORE_VERSION') ){
				return;
			} ?>

			<tr class="__rollback --core">
				<th>&nbsp;</th>
				<td><?php $this->get_html( 'rey-core' ); ?></td>
			</tr>
			<?php
		}


	}

	new ReyTheme_Rollback;
endif;
