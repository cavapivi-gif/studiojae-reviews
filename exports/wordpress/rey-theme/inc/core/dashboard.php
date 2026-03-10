<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists('ReyTheme_Dashboard') ):
	/**
	 * Manager for Rey theme's backend dashboard
	 *
	 * @since 1.0.0
	 */
	class ReyTheme_Dashboard extends ReyTheme_Base
	{
		const MANAGE_CAP = 'manage_options';

		public function __construct()
		{
			add_action( 'admin_menu', [ $this, 'admin_menu' ], 5 );

			$this->add_dashboxes();

			add_action( 'wp_ajax_rey_dashboard_register', [ $this, 'dashboard_register' ] );
			add_action( 'wp_ajax_rey_dashboard_deregister', [ $this, 'dashboard_deregister' ] );
			add_action( 'wp_ajax_rey_dashboard_install_plugins', [$this, 'dashboard_install_required_plugins'] );
			add_action( 'wp_ajax_rey_dashboard_newsletter_subscribe', [$this, 'dashboard_newsletter_subscribe'] );
			add_action( 'wp_ajax_rey_dashboard_install_child', [$this, 'dashboard_install_child'] );
			add_action( 'wp_ajax_rey_dashboard_migrate_opts_child', [$this, 'migrate_opts_child'] );

			add_filter('rey/admin_script_params', [$this, 'add_js_params']);

		}

		function add_dashboxes(){

			add_action( 'rey/dashboard/boxes', function() {

				if( ($excluded_dashboxes = rey__get_props('excluded_dashboxes')) && in_array('register', $excluded_dashboxes, true) ){
					return;
				}

				if( ! self::get_purchase_code() ){
					require_once REY_THEME_DIR . '/inc/core/admin/dashbox-registration.php';
				}
			});

			add_action( 'rey/dashboard/boxes', function() {

				if( ($excluded_dashboxes = rey__get_props('excluded_dashboxes')) && in_array('register', $excluded_dashboxes, true) ){
					return;
				}

				if( self::get_purchase_code() ){
					require_once REY_THEME_DIR . '/inc/core/admin/dashbox-registered.php';
				}
			});

			add_action( 'rey/dashboard/boxes', function() {

				if( ($excluded_dashboxes = rey__get_props('excluded_dashboxes')) && in_array('required_plugins', $excluded_dashboxes, true) ){
					return;
				}

				require_once REY_THEME_DIR . '/inc/core/admin/dashbox-required-plugins.php';
			});

			add_action( 'rey/dashboard/boxes', function(){

				if( ! rey__get_props('branding') ){
					return;
				}

				if( ($excluded_dashboxes = rey__get_props('excluded_dashboxes')) && in_array('newsletter', $excluded_dashboxes, true) ){
					return;
				}

				if( self::get_purchase_code() && ! $this->is_subscribed_to_newsletter() ){
					require_once REY_THEME_DIR . '/inc/core/admin/dashbox-newsletter.php';
				}
			});

			add_action( 'rey/dashboard/boxes', function(){

				if( ! rey__get_props('kb_links') ){
					return;
				}

				if( ($excluded_dashboxes = rey__get_props('excluded_dashboxes')) && in_array('help', $excluded_dashboxes, true) ){
					return;
				}

				require_once REY_THEME_DIR . '/inc/core/admin/dashbox-kb.php';
			});

			add_action( 'rey/dashboard/boxes', function() {

				if( ($excluded_dashboxes = rey__get_props('excluded_dashboxes')) && in_array('versions', $excluded_dashboxes, true) ){
					return;
				}

				require_once REY_THEME_DIR . '/inc/core/admin/dashbox-versions.php';
			});

			add_action( 'rey/dashboard/boxes', function() {

				if( ($excluded_dashboxes = rey__get_props('excluded_dashboxes')) && in_array('child_theme', $excluded_dashboxes, true) ){
					return;
				}

				require_once REY_THEME_DIR . '/inc/core/admin/dashbox-child-theme.php';
			});

			add_action( 'rey/dashboard/boxes', function(){

				if( ($excluded_dashboxes = rey__get_props('excluded_dashboxes')) && in_array('system_status', $excluded_dashboxes, true) ){
					return;
				}

				if( current_user_can( 'switch_themes' ) ){
					require_once REY_THEME_DIR . '/inc/core/admin/dashbox-system-status.php';
				}
			}, 150 );
		}

		/**
		 * Add admin script params
		 *
		 * @since 1.0.0
		 */
		function add_js_params( $params )
		{
			$params = array_merge($params, [
				'ajax_dashboard_nonce' => wp_create_nonce( 'rey_dashboard_nonce' ),
				'dashboard_url' => esc_url( add_query_arg( ['page' => self::DASHBOARD_PAGE_ID ], admin_url('admin.php')) ),
				'dashboard_strings'   => [
					'default_btn_text' => esc_html__( 'REGISTER', 'rey' ),
					'subscribe_default_btn_text' => esc_html__( 'SUBSCRIBE', 'rey' ),
					'installing_btn_text' => esc_html__( 'INSTALLING', 'rey' ),
					'default_install_btn_text' => esc_html__( 'INSTALL / ACTIVATE', 'rey' ),
					'install_btn_done' => esc_html__( 'DONE', 'rey' ),
					'installed_some' => esc_html__( 'Some plugins were installed/activated, but not all.', 'rey' ),
					'reloading_text' => esc_html__( 'RELOADING PAGE...', 'rey' ),
					'deregister_success' => esc_html__( 'Deregistered successfully! Reloading..', 'rey' ),
					'something_went_wrong' => esc_html__( 'Something went wrong. Please refresh the page and try again!', 'rey' ),
					'copying_settings' => esc_html__( 'COPYING SETTINGS..', 'rey' ),
				],
			]);

			return $params;
		}


		/**
		 * Create Rey's main menu
		 * @since 1.0.0
		 */
		public function admin_menu( $hook = '' )
		{

			add_menu_page(
				rey__get_props('theme_title'),
				rey__get_props('theme_title'),
				self::MANAGE_CAP,
				self::DASHBOARD_PAGE_ID,
				[ $this, 'admin_dashboard_page' ],
				rey__get_props('menu_icon')
			);

			add_submenu_page(
				self::DASHBOARD_PAGE_ID,
				esc_html__( 'Dashboard', 'rey' ),
				esc_html__( 'Dashboard', 'rey' ),
				self::MANAGE_CAP,
				self::DASHBOARD_PAGE_ID,
				[ $this, 'admin_dashboard_page' ]
			);

			do_action( 'rey/dashboard/admin_menu', $this );

		}

		/**
		 * Load home's admin page
		 *
		 * @since 1.0.0
		 */
		public function admin_dashboard_page()
		{
			require_once REY_THEME_DIR . '/inc/core/admin/pages-dashboard.php';
		}


		/**
		 * Dashboard Register Ajax Call
		 *
		 * @since 1.0.0
		 */
		public function dashboard_register(){

			if ( ! check_ajax_referer( 'rey_dashboard_nonce', 'security', false ) ) {
				wp_send_json_error( esc_html__('Invalid security nonce!', 'rey') );
			}

			if ( ! current_user_can('administrator') ) {
				wp_send_json_error( esc_html__('Operation not allowed!', 'rey') );
			}

			$this->register();
		}


		/**
		 * Setup dashboard plugin installation Ajax Call
		 *
		 * @since 1.0.0
		 */
		public function dashboard_install_required_plugins(){

			if ( ! check_ajax_referer( 'rey_dashboard_nonce', 'security', false ) ) {
				wp_send_json_error( esc_html__('Invalid security nonce!', 'rey') );
			}

			if ( ! current_user_can('install_plugins') ) {
				wp_send_json_error( esc_html__('Operation not allowed!', 'rey') );
			}

			if( isset($_GET['page']) && $_GET['page'] === self::DASHBOARD_PAGE_ID ){
				\Rey\Plugins::ajax_install_required_plugins();
			}
			else {
				wp_send_json_error( esc_html__('Submission URL not matching the page URL.', 'rey') );
			}
		}

		/**
		 * Dashboard deregister Ajax Call
		 *
		 * @since 1.0.0
		 */
		public function dashboard_deregister(){

			if ( ! check_ajax_referer( 'rey_dashboard_nonce', 'security', false ) ) {
				wp_send_json_error( esc_html__('Invalid security nonce!', 'rey') );
			}

			if ( ! current_user_can('administrator') ) {
				wp_send_json_error( esc_html__('Operation not allowed!', 'rey') );
			}

			$deregister = $this->deregister();

			// has status message
			if( ! empty($deregister['error']) ){
				wp_send_json_error( $deregister['error'] );
			}

			if( true === $deregister ){
				wp_send_json_success( '<strong>Successfully deregistered!</strong><br>Just a friendly reminder that our policy allows the use of a purchase code on only one website, and transferring a purchase code between multiple websites is strictly prohibited.' );
			}

			wp_send_json_error( esc_html__('Something went wrong. Please retry later.', 'rey') );
		}


		/**
		 * Dashboard subscribe to newsletter ajax call
		 *
		 * @since 1.0.0
		 */
		public function dashboard_newsletter_subscribe(){

			if ( ! check_ajax_referer( 'rey_dashboard_nonce', 'security', false ) ) {
				wp_send_json_error( esc_html__('Invalid security nonce!', 'rey') );
			}

			if( !isset($_POST['rey_email_address']) && !empty($_POST['rey_email_address']) ){
				wp_send_json_error( esc_html__( 'Please add an email address.', 'rey' ) );
			}

			// send subscribe request
			$request = $this->theme_api()->subscribe_newsletter( sanitize_email( $_POST['rey_email_address'] ) );

			// check for errors
			if ( is_wp_error( $request ) ) {
				wp_send_json_error( $request->get_error_message() );
			}

			// check if subscribed
			if ( isset($request['data']['subscribed']) ) {
				if( 1 === absint($request['data']['subscribed'])) {
					$this->set_subscribed_newsletter();
					wp_send_json_success();
				}
				else {
					wp_send_json_error( esc_html__( 'Something went wrong. Please try again!', 'rey' ) );
				}
			}
			return true;
		}

		/**
		 * Dashboard install child theme
		 *
		 * @since 1.6.9
		 */
		public function dashboard_install_child(){

			if ( ! check_ajax_referer( 'rey_dashboard_nonce', 'security', false ) ) {
				wp_send_json_error( esc_html__('Invalid security nonce!', 'rey') );
			}

			if( ReyTheme_Base::install_child_theme() ){

				$child_theme = self::get_child_theme();

				if ( $child_theme !== false ) {
					switch_theme( $child_theme->get_stylesheet() );
				}

				wp_send_json_success();
			}

			wp_send_json_error( esc_html__( 'Something went wrong. Please try again!', 'rey' ) );
		}

		/**
		 * Dashboard migrate parent to child theme
		 *
		 * @since 1.6.9
		 */
		public function migrate_opts_child(){

			if ( ! check_ajax_referer( 'rey_dashboard_nonce', 'security', false ) ) {
				wp_send_json_error( esc_html__('Invalid security nonce!', 'rey') );
			}

			$child_theme_name = REY_CORE_THEME_NAME . '-child';
			$child_theme_options = sprintf('theme_mods_%s', REY_THEME_NAME . '-child');
			$options_copied = 'rey__parent_to_child';

			if( ! get_option($options_copied) ){
				if( ($mods = get_option(sprintf('theme_mods_%s', REY_THEME_NAME))) && update_option($child_theme_options, $mods, false) ){
					update_option($options_copied, true, false);
					wp_send_json_success();
				}
			}

			wp_send_json_error( esc_html__( 'Something went wrong. Please try again!', 'rey' ) );
		}

	}

	new ReyTheme_Dashboard;
endif;
