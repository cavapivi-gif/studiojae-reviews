<?php
namespace ReyCore\WooCommerce\Tags;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class LoginRegister
{
	public $modal_login_atts = [
		'enabled' => true,
		'forms' => true
	];

	public function __construct() {
		add_action( 'wp', [ $this, 'onwp' ] );
		add_action( 'reycore/ajax/register_actions', [ $this, 'register_actions' ] );
		add_action( 'reycore/woocommerce/account_panel', [$this, 'display_login_forms_account_panel'], 20);
		add_shortcode( 'rey_ajax_login_form', [$this, 'forms_shortcode']);
	}

	function onwp(){

		if( ! is_account_page() ){
			return;
		}

		add_action( 'woocommerce_account_content', [$this, 'wrap_account_content_start'], -1);
		add_action( 'woocommerce_account_content', [$this, 'wrap_account_content_end'], 1000);

		if( apply_filters('reycore/myaccount/form_row_styles', true) ){
			reycore_assets()->add_styles('rey-form-row');
		}

	}

	function add_shortcode_atts($params){
		return wp_parse_args($this->modal_login_atts, $params);
	}

	function forms_shortcode( $atts = [] ) {

		$this->modal_login_atts['login_register_redirect'] = isset($atts['redirect_type']) ? $atts['redirect_type'] : 'load_menu';
		$this->modal_login_atts['login_register_redirect_url'] = isset($atts['redirect_url']) ? $atts['redirect_url'] : '';
		$this->modal_login_atts['ajax_forms'] = isset($atts['ajax']) ? (absint($atts['ajax']) === 1) : true;

		add_filter('rey/header/account_params', [$this, 'add_shortcode_atts'], 20);
		$this->display_account_forms();
		remove_filter('rey/header/account_params', [$this, 'add_shortcode_atts'], 20);

	}

	function display_account_forms(){

		if( ! is_user_logged_in() ): ?>

			<div class="rey-accountForms --active" <?php reycore_wc__account_redirect_attrs() ?> data-account-tab="account">
				<?php

					reycore_assets()->add_styles(['rey-buttons', 'reycore-pass-visibility']);
					reycore_assets()->add_deferred_styles('rey-form-row');
					reycore_assets()->add_scripts('reycore-wc-header-account-forms');

					reycore__get_template_part('template-parts/woocommerce/form-login');
					reycore__get_template_part('template-parts/woocommerce/form-register');
					reycore__get_template_part('template-parts/woocommerce/form-lost-password');

					do_action( 'woocommerce_after_customer_login_form' ); ?>
			</div>

		<?php else:

			reycore__get_template_part('template-parts/woocommerce/header-account-menu');

		endif;
	}

	function display_login_forms_account_panel(){

		// is catalog mode and custom filter is enabled (which hides account forms)
		if( get_theme_mod('shop_catalog', false) && apply_filters('reycore/catalog_mode/hide_account', false) ){
			return;
		}

		$args = reycore_wc__get_account_panel_args();

		if( ! $args['forms'] ){
			return;
		}

		if( apply_filters('reycore/woocommerce/display_account_forms', true) ) {
			$this->display_account_forms();
		}

		if( $args['display'] === 'offcanvas' ){
			reycore_assets()->add_styles(['rey-overlay', 'reycore-side-panel']);
		}
	}

	public function register_actions( $ajax_manager ){

		$ajax_manager->register_ajax_action( 'account_forms', [$this, 'ajax__forms_process'], [
			'auth'   => 2,
			'nonce'  => false,
		] );

		$ajax_manager->register_ajax_action( 'account_forms_gn', [$this, 'account_forms_get_refreshed_nonces'], [
			'auth'   => 2,
			'nonce'  => false,
		] );

	}

	public function account_forms_get_refreshed_nonces() {

		$nonces = [];

		foreach ([
			'woocommerce-login-nonce' => 'woocommerce-login',
			'woocommerce-register-nonce' => 'woocommerce-register',
			'woocommerce-lost-password-nonce' => 'lost_password',
		] as $key => $value) {
			$nonces[$key] = wp_create_nonce( $value );
		}

		return $nonces;
	}

	public function ajax__forms_process( $action_data ) {

		if( ! (isset($action_data['action_type']) && ($action_type = reycore__clean($action_data['action_type']))) ) {
			return reycore_wc__add_notice( esc_html__('Something went wrong while submitting this form. Please try again!', 'rey-core'), 'error' );
		}

		$types = [
			'login',
			'register',
			'forgot'
		];

		if( ! in_array($action_type, $types, true) ){
			return reycore_wc__add_notice( esc_html__('Incorrect request!', 'rey-core'), 'error' );
		}

		if( ! method_exists($this, "process_{$action_type}") ){
			return reycore_wc__add_notice( esc_html__('Incorrect function!', 'rey-core'), 'error' );
		}

		$data = [];

		wc_clear_notices();

		ob_start();
		call_user_func( [$this, "process_{$action_type}"] );
		$data['html'] = ob_get_clean();

		ob_start();
		wc_print_notices();
		$data['notices'] = ob_get_clean();

		if( empty($data['html']) && empty($data['notices']) ){
			// something's wrong.
		}

		return $data;

	}

	public function process_login(){

		if( ! isset( $_POST['login'], $_POST['username'], $_POST['password'] ) ){
			wc_add_notice( '<strong>' . __( 'Error:', 'rey-core' ) . '</strong> ' . esc_html__('Missing form data.', 'rey-core'), 'error' );
			return;
		}

		// The global form-login.php template used `_wpnonce` in template versions < 3.3.0.
		$nonce_value = wc_get_var( $_REQUEST['woocommerce-login-nonce'], wc_get_var( $_REQUEST['_wpnonce'], '' ) ); // @codingStandardsIgnoreLine.

		if( ! wp_verify_nonce( $nonce_value, 'woocommerce-login' ) ){
			wc_add_notice( '<strong>' . __( 'Error:', 'rey-core' ) . '</strong> ' . sprintf(__('It seems there\'s an issue with the form submission (incorrect Nonce). Please refresh the page and try again. If the problem persists, visit <a href="%s">this form</a> and retry submitting it.', 'rey-core'), wc_get_page_permalink( 'myaccount' ) ), 'error' );
			return;
		}

		try {
			$creds = array(
				'user_login'    => trim( wp_unslash( $_POST['username'] ) ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				'user_password' => $_POST['password'], // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
				'remember'      => isset( $_POST['rememberme'] ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			);

			$validation_error = new \WP_Error();
			$validation_error = apply_filters( 'woocommerce_process_login_errors', $validation_error, $creds['user_login'], $creds['user_password'] );

			if ( $validation_error->get_error_code() ) {

				return wc_add_notice( apply_filters( 'login_errors', '<strong>' . __( 'Error:', 'rey-core' ) . '</strong> ' . $validation_error->get_error_message() ), 'error' );

			}

			if ( empty( $creds['user_login'] ) ) {

				return wc_add_notice( apply_filters( 'login_errors', '<strong>' . __( 'Error:', 'rey-core' ) . '</strong> ' . __( 'Username is required.', 'rey-core' ) ), 'error' );

			}

			// On multisite, ensure user exists on current site, if not add them before allowing login.
			if ( is_multisite() ) {
				$user_data = get_user_by( is_email( $creds['user_login'] ) ? 'email' : 'login', $creds['user_login'] );

				if ( $user_data && ! is_user_member_of_blog( $user_data->ID, get_current_blog_id() ) ) {
					add_user_to_blog( get_current_blog_id(), $user_data->ID, 'customer' );
				}
			}

			// Perform the login.
			$user = wp_signon( apply_filters( 'woocommerce_login_credentials', $creds ), is_ssl() );

			if ( is_wp_error( $user ) ) {

				return wc_add_notice( apply_filters( 'login_errors', $user->get_error_message() ), 'error' );

			} else {

				printf('<div class="rey-accountForms-notice --vanish">%s</div>', reycore_wc__add_notice([
					sprintf( esc_html__( 'Logged in as %s', 'woocommerce' ), esc_html( $user->display_name ) ),
				]));

				$GLOBALS['rey_ajax_login_uid'] = $user->ID;

				reycore__get_template_part('template-parts/woocommerce/header-account-menu');

			}
		} catch ( \Exception $e ) {
			wc_add_notice( apply_filters( 'login_errors', $e->getMessage() ), 'error' );
			do_action( 'woocommerce_login_failed' );
		}
	}

	public function process_register(){

		if( ! isset( $_POST['register'], $_POST['email'] ) ){
			wc_add_notice( '<strong>' . __( 'Error:', 'rey-core' ) . '</strong> ' . esc_html__('Missing form data.', 'rey-core'), 'error' );
			return;
		}

		$nonce_value = isset( $_POST['_wpnonce'] ) ? wp_unslash( $_POST['_wpnonce'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$nonce_value = isset( $_POST['woocommerce-register-nonce'] ) ? wp_unslash( $_POST['woocommerce-register-nonce'] ) : $nonce_value; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if( ! wp_verify_nonce( $nonce_value, 'woocommerce-register' ) ){
			wc_add_notice( '<strong>' . __( 'Error:', 'rey-core' ) . '</strong> ' . sprintf(__('It seems there\'s an issue with the form submission (incorrect Nonce). Please refresh the page and try again. If the problem persists, visit <a href="%s">this form</a> and retry submitting it.', 'rey-core'), wc_get_page_permalink( 'myaccount' ) ), 'error' );
			return;
		}

		$username = 'no' === get_option( 'woocommerce_registration_generate_username' ) && isset( $_POST['username'] ) ? wp_unslash( $_POST['username'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$password = 'no' === get_option( 'woocommerce_registration_generate_password' ) && isset( $_POST['password'] ) ? $_POST['password'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$email    = wp_unslash( $_POST['email'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		try {

			$validation_error  = new \WP_Error();
			$validation_error  = apply_filters( 'woocommerce_process_registration_errors', $validation_error, $username, $password, $email );
			$validation_errors = $validation_error->get_error_messages();

			if ( 1 === count( $validation_errors ) ) {
				throw new \Exception( $validation_error->get_error_message() );
			} elseif ( $validation_errors ) {
				foreach ( $validation_errors as $message ) {
					wc_add_notice( '<strong>' . __( 'Error:', 'woocommerce' ) . '</strong> ' . $message, 'error' );
				}
				throw new \Exception();
			}

			$new_customer = wc_create_new_customer( sanitize_email( $email ), wc_clean( $username ), $password );

			if ( is_wp_error( $new_customer ) ) {
				throw new \Exception( $new_customer->get_error_message() );
			}

			$notices = [];

			if ( 'yes' === get_option( 'woocommerce_registration_generate_password' ) ) {
				$notices[] = __( 'Your account was created successfully and a password has been sent to your email address.', 'woocommerce' );
			} else {
				$notices[] = __( 'Your account was created successfully. Your login details have been sent to your email address.', 'woocommerce' );
			}

			printf('<div class="rey-accountForms-notice --vanish">%s</div>', reycore_wc__add_notice($notices));

			// Only redirect after a forced login - otherwise output a success notice.
			if ( apply_filters( 'woocommerce_registration_auth_new_customer', true, $new_customer ) ) {
				wc_set_customer_auth_cookie( $new_customer );
			}

			reycore__get_template_part('template-parts/woocommerce/header-account-menu');

		} catch ( \Exception $e ) {
			if ( $e->getMessage() ) {
				wc_add_notice( '<strong>' . __( 'Error:', 'woocommerce' ) . '</strong> ' . $e->getMessage(), 'error' );
			}
		}

	}

	public function process_forgot(){

		if ( isset( $_POST['wc_reset_password'], $_POST['user_login'] ) ) {

			$nonce_value = wc_get_var( $_REQUEST['woocommerce-lost-password-nonce'], wc_get_var( $_REQUEST['_wpnonce'], '' ) ); // @codingStandardsIgnoreLine.

			if ( ! wp_verify_nonce( $nonce_value, 'lost_password' ) ) {
				wc_add_notice( '<strong>' . __( 'Error:', 'rey-core' ) . '</strong> ' . sprintf(__('It seems there\'s an issue with the form submission (incorrect Nonce). Please refresh the page and try again. If the problem persists, visit <a href="%s">this form</a> and retry submitting there.', 'rey-core'), wc_get_endpoint_url( 'lost-password', '', wc_get_page_permalink( 'myaccount' ) ) ), 'error' );
				return;
			}

			if( \WC_Shortcode_My_Account::retrieve_password() ){
				wc_get_template( 'myaccount/lost-password-confirmation.php' );
			}

		}

	}

	public function wrap_account_content_start(){
		printf('<div class="rey-myAccContent" data-endpoint="%s">', esc_attr(WC()->query->get_current_endpoint()) );
	}
	public function wrap_account_content_end(){
		echo '</div>';
	}
}
