<?php
namespace Rey;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Plugins {

	private static $_instance = null;

	private function __construct()
	{
		require_once REY_THEME_DIR . '/inc/libs/plugins-lib.php';

		add_action( 'wp_ajax_rey_setup_plugin', [$this, 'ajax__setup_plugin'] );
		add_action( 'admin_notices', [$this, 'check_plugins_notice']);
		add_filter( 'add_menu_classes', [ $this, 'menu_classes' ] );
		add_filter( 'upgrader_pre_download', [$this, 'pre_upgrader_filter'], 15, 3 );

	}

	public static function version_swap(){
		return version_compare(REY_THEME_VERSION, '2.6.0', '>=');
	}

	/**
	 * Refresh plugins list
	 * @called by Force check for updates
	 * @called after registration (and code stored)
	 * @called by Dashbox flush versions
	 * Does not run in multisite network admin.
	 *
	 * @return void
	 */
	public static function refresh(){
		$plugins_lib = new \Rey\Libs\Plugins();
		return $plugins_lib->refresh();
	}

	public static function delete_plugins_list(){
		delete_option( \Rey\Libs\Plugins::PLUGIN_LIST_OPTION );
	}

	/**
	 * Get the plugins list
	 *
	 * @param string $type
	 * @return array
	 */
	public static function get_plugins( $type = 'all' ){

		static $plugins;

		if( is_null($plugins) ){
			$plugins_lib = new \Rey\Libs\Plugins();
			$plugins = $plugins_lib->get_plugins();
		}

		if( $type === 'all' ) {
			return $plugins;
		}

		return array_filter($plugins, function($v, $k) use ($type){

			// Get REY plugins (self-hosted)
			if( $type === 'rey' ){
				$item = REY_THEME_NAME == $v['type'];
			}

			// Get required plugins
			else if ($type === 'required'){
				$item = true === $v['required'];
			}

			return $item;

		}, ARRAY_FILTER_USE_BOTH);
	}

	/**
	 * Get Rey's plugins marked as required.
	 *
	 * @since 1.0.0
	 */
	public static function get_required_plugins(){
		return self::get_plugins('required');
	}

	/**
	 * Get Rey's plugins marked as required.
	 *
	 * @since 1.0.0
	 */
	public static function get_rey_plugins(){
		return self::get_plugins('rey');
	}

	/**
	 * Install required plugins method for ajax calls
	 *
	 * @since 1.0.0
	 */
	public static function ajax_install_required_plugins()
	{
		$plugins_lib = new \Rey\Libs\Plugins();

		$slug = '';

		foreach ( self::get_required_plugins() as $required_plugin) {
			if( ! $required_plugin['active'] ){
				$slug = $required_plugin['slug'];
				break;
			}
		}

		if( ! $slug ){
			wp_send_json_success();
		}

		$install = $plugins_lib->install_activate_plugins( $slug );

		if( isset($install['error']) ){
			wp_send_json_error( $install['error'] );
		}

		wp_send_json_success($install);
	}

	/**
	 * Show notice if required plugins are not installed or inactive
	 *
	 * @since 1.0.0
	 */
	public function check_plugins_notice()
	{
		if( class_exists('ReyCore') ){
			return;
		} ?>

		<div class="reyAdm-notice notice notice-info --visible">
			<div class="__inner">
				<div class="__logo">
					<?php echo rey__get_svg_icon(['id'=>'logo']) ?>
				</div>
				<div class="__content">
					<h3><?php echo esc_html_x('One more step ..', 'Admin notice backend', 'rey') ?></h3>
					<p>
						<?php echo rey__wp_kses( sprintf( _x('%1$s requires <strong>%2$s</strong> plugin companion to be installed. That\'s because the theme is just a backbone and the Core is where the features are packend in and offloaded.', 'Admin notice backend', 'rey'),
							rey__get_props('theme_title'),
							rey__get_props('core_title')
						) ); ?>
					</p>
					<p>
						<?php
						printf(
							'<a href="%s" class="rey-genericBtn  button-primary --red" %s>%s</a>',
							esc_url(add_query_arg([ 'page' => \ReyTheme_Base::DASHBOARD_PAGE_ID ], admin_url('admin.php'))),
							\ReyTheme_Base::get_purchase_code() ? 'data-setup-plugin="rey-core"' : '',
							sprintf(esc_html_x('Activate %s', 'Admin notice backend', 'rey'), rey__get_props('core_title'))
						); ?>
					</p>
				</div>
			</div>
		</div>
		<?php
	}

	public function ajax__setup_plugin(){

		if ( ! check_ajax_referer( 'rey_fadm_nonce', 'security', false ) ) {
			wp_send_json_error( esc_html__('Invalid security nonce!', 'rey') );
		}

		if( ! ($slug = rey__clean($_REQUEST['item'])) ){
			return;
		}

		$plugins_lib = new \Rey\Libs\Plugins();
		$activated = $plugins_lib->install_activate_plugins([$slug]);

		if( isset($activated['error']) ){
			wp_send_json_error( $activated['error'] );
		}

		do_action('rey/plugin_setup', $activated[$slug]);

		wp_send_json_success(esc_html__('Reloading page..', 'rey'));
	}

	public function menu_classes( $menu ) {

		global $submenu;

		if ( is_multisite() && is_network_admin() ) {
			return $menu;
		}

		// Non admin role / custom wp menu.
		if ( empty( $submenu[ \ReyTheme_Base::DASHBOARD_PAGE_ID ] ) ) {
			return $menu;
		}

		// foreach ($menu as $k => $m) {
		// 	if( isset($m[2]) && \ReyTheme_Base::DASHBOARD_PAGE_ID === $m[2] ){
		// 		// add css class to "Rey Theme" menu item
		// 	}
		// }

		$has_wizard = ! empty(array_filter($submenu[ \ReyTheme_Base::DASHBOARD_PAGE_ID ], function($item){
			return isset($item[2]) && 'rey-setup-wizard' === $item[2];
		}));

		// Hack to add a link to sub menu.
		foreach ( $submenu[ \ReyTheme_Base::DASHBOARD_PAGE_ID ] as &$item ) {
			if ( strpos($item[2], REY_THEME_NAME) === 0 ) {

				$item[4] = apply_filters('rey/admin/submenu/classes', 'cls-' . esc_attr($item[2]), $item);

				if( $has_wizard ){
					if( 'rey-setup-wizard' === $item[2] ){
						$item[4] = ' cls-rey-first';
					}
				}
				else {
					if( \ReyTheme_Base::DASHBOARD_PAGE_ID === $item[2] ){
						$item[4] = ' cls-rey-first';
					}
				}

			}
		}

		return $menu;
	}

	public function pre_upgrader_filter($reply, $package, $upgrader){

		if( ! \ReyTheme_Base::get_purchase_code() ) {
			return $reply;
		}

		if( ! ( $rey_plugins = self::get_rey_plugins() ) ) {
			return $reply;
		}

		$sources = wp_list_pluck( $rey_plugins, 'source' );

		$plugin_slug = '';

		foreach ($sources as $key => $value) {
			if( $value === $package && isset($rey_plugins[$key]['slug']) && !empty($rey_plugins[$key]['slug']) && $rey_plugins[$key]['type'] === REY_THEME_NAME ){
				$plugin_slug = $rey_plugins[$key]['slug'];
			}
		}

		if( empty($plugin_slug) ){
			return $reply;
		}

		$upgrader->strings['downloading_package_url'] = esc_html__( 'Getting download link...', 'rey' );
		$upgrader->skin->feedback( 'downloading_package_url' );

		$download_link = \ReyTheme_API::getInstance()->get_download_url($plugin_slug);

		if ( ! $download_link ) {
			return new \WP_Error( 'no_credentials', esc_html__( 'Download link could not be retrieved', 'rey' ) );
		}

		$upgrader->strings['downloading_package'] = esc_html__( 'Downloading package...', 'rey' );
		$upgrader->skin->feedback( 'downloading_package' );

		$download_file = download_url( $download_link );

		if ( is_wp_error( $download_file ) && ! $download_file->get_error_data( 'softfail-filename' ) ) {
			return new \WP_Error( 'download_failed', $upgrader->strings['download_failed'], $download_file->get_error_message() );
		}

		return $download_file;
	}

	public static function getInstance()
	{
		if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}
}

Plugins::getInstance();
