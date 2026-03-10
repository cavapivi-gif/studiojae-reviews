<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists('ReyTheme_Wizard') ):
	/**
	 * Adds a setup wizard after theme has been activated
	 *
	 * @since 1.0.0
	 */
	class ReyTheme_Wizard extends ReyTheme_Base
	{
		const WIZARD_PAGE_ID = REY_THEME_NAME . '-setup-wizard';
		const WIZARD_OPTION_ID = 'rey_finished_wizard';

		public function __construct()
		{
			add_action( 'rey/dashboard/admin_menu', [ $this, 'admin_menu' ] );
			add_action( 'after_switch_theme', [ $this, 'redirect_to_wizard' ] );
			add_action( 'wp_ajax_rey_register_purchase_code', [ $this, 'wizard_register_purchase_code' ] );
			add_action( 'wp_ajax_rey_wizard_install_plugins', [$this, 'wizard_install_required_plugins'] );
			add_action( 'wp_ajax_rey_wizard_enable_child_theme', [$this, 'wizard_enable_child_theme'] );
			add_action( 'wp_ajax_rey_wizard_get_required_plugins_markup', [$this, 'get_required_plugins_markup'] );
			add_action( 'wp_ajax_rey_wizard_skip', [$this, 'ajax_wizard_disable'] );
			add_action( 'wp_ajax_rey_get_importer_url', [$this, 'ajax__import_url'] );
			add_action( 'wp_ajax_rey_wizard_test_connection', [ $this, 'ajax__test_connection' ] );
			add_filter( 'rey/admin_script_params', [$this, 'add_js_params']);
		}

		/**
		 * Add admin script params
		 *
		 * @since 1.0.0
		 */
		function add_js_params( $params )
		{
			$params = array_merge($params, [
				'ajax_wizard_nonce' => wp_create_nonce( 'rey_wizard_nonce' ),
				'wizard_strings'   => [
					'registering_btn_text' => esc_html__( 'REGISTERING', 'rey' ),
					'default_btn_text' => esc_html__( 'NEXT', 'rey' ),
					'installing_btn_text' => esc_html__( 'INSTALLING', 'rey' ),
					'installed_some' => esc_html__( 'Some plugins were not installed. Please retry!', 'rey' ),
					'something_went_wrong' => esc_html__( 'Something went wrong. Please refresh the page and try again!', 'rey' ),
					'skipping_success' => esc_html__( 'REDIRECTING..', 'rey' ),
				],
			]);

			return $params;
		}


		/**
		 * Create Rey's main menu
		 * @since 1.0.0
		 */
		public function admin_menu()
		{
			if( ! $this->check_wizard() ){
				add_submenu_page(
					self::DASHBOARD_PAGE_ID,
					esc_html__( 'Setup Wizard', 'rey' ),
					esc_html__( 'Setup Wizard', 'rey' ),
					'switch_themes',
					self::WIZARD_PAGE_ID,
					[ $this, 'admin_page_wizard' ]
				);
			}
		}

		/**
		 * Load home's admin page
		 *
		 * @since 1.0.0
		 */
		public function admin_page_wizard()
		{
			require_once REY_THEME_DIR . '/inc/core/admin/pages-wizard.php';
		}

		/**
		 * Redirect to wizard after theme switch
		 *
		 * @since 1.0.0
		 */
		function redirect_to_wizard(){
			global $pagenow;

			if ( is_admin() && 'themes.php' === $pagenow && isset( $_GET['activated'] ) && ! $this->check_wizard() ) {
				wp_safe_redirect( add_query_arg( ['page' => self::WIZARD_PAGE_ID ], admin_url('admin.php')) );
				exit();
			}
		}

		/**
		 * Setup wizard purchase code registration
		 *
		 * @since 1.0.0
		 */
		public function wizard_register_purchase_code(){

			if ( ! check_ajax_referer( 'rey_wizard_nonce', 'security', false ) ) {
				wp_send_json_error( esc_html__('Invalid security nonce!', 'rey') );
			}

			if ( ! current_user_can('administrator') ) {
				wp_send_json_error( esc_html__('Operation not allowed!', 'rey') );
			}

			$this->register();
		}

		/**
		 * Setup wizard plugin installation Ajax Calls
		 *
		 * @since 1.0.0
		 */
		public function wizard_install_required_plugins(){

			if ( ! check_ajax_referer( 'rey_wizard_nonce', 'security', false ) ) {
				wp_send_json_error( esc_html__('Invalid security nonce!', 'rey') );
			}

			if ( ! current_user_can('install_plugins') ) {
				wp_send_json_error( esc_html__('Operation not allowed!', 'rey') );
			}

			if( isset($_GET['page']) && $_GET['page'] === self::WIZARD_PAGE_ID ){

				// check if child theme needs installation
				if( isset($_GET['child_theme']) && $_GET['child_theme'] === 'true' ){
					wp_send_json_success( ['child_theme' => ReyTheme_Base::install_child_theme()] );
				}

				\Rey\Plugins::ajax_install_required_plugins();
			}
			else {
				wp_send_json_error( esc_html__('Submission URL not matching the page URL.', 'rey') );
			}
		}

		/**
		 * Setup wizard child theme enable
		 *
		 * @since 1.0.0
		 */
		public function wizard_enable_child_theme(){

			if ( ! check_ajax_referer( 'rey_wizard_nonce', 'security', false ) ) {
				wp_send_json_error( esc_html__('Invalid security nonce!', 'rey') );
			}

			if ( ! current_user_can('administrator') ) {
				wp_send_json_error( esc_html__('Operation not allowed!', 'rey') );
			}

			$this->enable_child_theme();
		}

		/**
		 * Setup wizard child theme enable
		 *
		 * @since 1.0.0
		 */
		public function get_required_plugins_markup(){

			if ( ! check_ajax_referer( 'rey_wizard_nonce', 'security', false ) ) {
				wp_send_json_error( esc_html__('Invalid security nonce!', 'rey') );
			}

			if ( ! current_user_can('administrator') ) {
				wp_send_json_error( esc_html__('Operation not allowed!', 'rey') );
			}

			include __DIR__ . '/admin/tpl-required-plugins.php';

			wp_send_json_success( rey__kses_post_with_svg( $plugins_output ) );
		}

		/**
		 * Disable the wizard ajax call
		 *
		 * @since 1.0.0
		 */
		public function ajax_wizard_disable(){

			if ( ! check_ajax_referer( 'rey_wizard_nonce', 'security', false ) ) {
				wp_send_json_error( esc_html__('Invalid security nonce!', 'rey') );
			}

			if ( ! current_user_can('administrator') ) {
				wp_send_json_error( esc_html__('Operation not allowed!', 'rey') );
			}

			if( $this->disable_wizard() ){
				wp_send_json_success();
			}

			wp_send_json_error( esc_html__('Something went wrong. Please retry!', 'rey') );
		}

		/**
		 * Tests connection to Rey's API
		 *
		 * @return void
		 */
		public function ajax__test_connection(){

			if( ! (current_user_can('administrator') || current_user_can('install_plugins')) ){
				wp_send_json_error('Operation not allowed!');
			}

			$response = wp_safe_remote_get( \ReyTheme_API::$api_site_url );

			if ( is_wp_error( $response ) ) {
				wp_send_json_error( \ReyTheme_Base::get_failed_connection_message( $response->get_error_message() ) );
			}

			$code = wp_remote_retrieve_response_code( $response );

			if( 200 !== $code ){
				wp_send_json_error( \ReyTheme_Base::get_failed_connection_message($code) );
			}

			wp_send_json_success();
		}

		/**
		 * Disable wizard option
		 *
		 * @since 1.0.0
		 */
		public function disable_wizard(){
			return update_site_option( self::WIZARD_OPTION_ID, true );
		}

		/**
		 * Check wizard if active
		 *
		 * @since 1.0.0
		 */
		public function check_wizard() {

			// delete_site_option( self::WIZARD_OPTION_ID );

			if( ! rey__get_props('setup_wizard') ){
				return true;
			}

			return get_site_option( self::WIZARD_OPTION_ID, false );
		}

		public static function get_import_url(){
			return apply_filters('rey/importer_link', '');
		}

		public function ajax__import_url(){
			wp_send_json_success( self::get_import_url() );
		}

	}

	new ReyTheme_Wizard;

endif;
