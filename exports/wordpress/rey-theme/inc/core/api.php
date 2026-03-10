<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles API calls to Rey API server.
 *
 * @since 1.0.0
 */
class ReyTheme_API
{

	/**
	 * Holds the reference to the instance of this class
	 * @var ReyTheme_API
	 */
	private static $_instance = null;

	public static $api_site_url = 'https://api.reytheme.com/';
	public static $api_url;
	public static $api_files_url;

	// legacy
	const API_SITE_URL = '';
	const API_URL = '';
	const API_FILES_URL = '';

	/**
	 * API Endpoints
	 */
	const REY_API_ENDPOINT__REGISTER = 'register/';
	const REY_API_ENDPOINT__DEREGISTER = 'deregister/';
	const REY_API_ENDPOINT__NEWSLETTER = 'subscribe_newsletter/';
	const REY_API_ENDPOINT__GET_THEME_DOWNLOAD_URL = 'get_theme_download_url/';
	const REY_API_ENDPOINT__GET_PLUGINS = 'get_plugins/';
	const REY_API_ENDPOINT__GET_PLUGIN_DATA = 'get_plugin_data/';
	const REY_API_ENDPOINT__GET_DEMOS = 'get_demos/';
	const REY_API_ENDPOINT__GET_DEMO_DATA = 'get_demo_data/';
	const REY_API_ENDPOINT__GET_TEMPLATE_DATA = 'get_template_data/';

	/**
	 * ReyTheme_API constructor.
	 */
	private function __construct() {
		self::$api_site_url = defined('DEV_REY_API_URL') ? DEV_REY_API_URL : self::$api_site_url;
		self::$api_url = self::$api_site_url . 'wp-json/rey-api/v1/';
        self::$api_files_url = self::$api_site_url . 'wp-content/uploads/api_routes/';
	}

	function get_purchase_code(){
		return ReyTheme_Base::get_purchase_code();
	}

	/**
	 * Get plugin data + download url
	 *
	 * @since 1.0.0
	 */
	public function get_plugin_data( $slug ){
		return $this->remote_get( [
			'purchase_code' => $this->get_purchase_code(),
			'slug' => $slug
		], self::REY_API_ENDPOINT__GET_PLUGIN_DATA );
	}


	/**
	 * Get demos list
	 *
	 * @since 1.0.0
	 */
	public function get_demos( $demo = false ){
		$params = [
			'purchase_code' => $this->get_purchase_code(),
		];
		if( $demo ){
			$params['slug'] = $demo;
		}
		return $this->remote_get( $params, self::REY_API_ENDPOINT__GET_DEMOS );
	}

	/**
	 * Get demo data + download url
	 *
	 * @since 1.0.0
	 */
	public function get_demo_data( $slug ){
		return $this->remote_get( [
			'purchase_code' => $this->get_purchase_code(),
			'slug' => $slug
		], self::REY_API_ENDPOINT__GET_DEMO_DATA );
	}


	/**
	 * Get templates list
	 * @param $type all,library,demo_bar
	 *
	 * @since 1.0.0
	 */
	public function get_templates( $type = 'all' ){
		return $this->file_remote_get( sprintf('templates_%s.json', $type) );
	}

	/**
	 * Send Registration request to API Server
	 *
	 * @since 1.0.0
	 */
	public function register($args){
		return $this->remote_post( $args, self::REY_API_ENDPOINT__REGISTER );
	}

	/**
	 * Send *DE*Registration request to API Server
	 *
	 * @since 1.0.0
	 */
	public function deregister($args){
		return $this->remote_post( $args, self::REY_API_ENDPOINT__DEREGISTER );
	}

	/**
	 * Get Theme version for update checks
	 *
	 * @since 1.0.0
	 */
	public function get_theme_version(){
		return $this->file_remote_get( 'version.json' );
	}

	/**
	 * Get Theme version for update checks
	 *
	 * @since 1.0.0
	 */
	public function get_main_versions(){
		return $this->file_remote_get( 'versions.json' );
	}

	/**
	 * Get theme download url for updates
	 *
	 * @since 1.0.0
	 */
	public function get_theme_download_url(){
		return $this->remote_get( [
			'purchase_code' => $this->get_purchase_code()
		], self::REY_API_ENDPOINT__GET_THEME_DOWNLOAD_URL );
	}

	/**
	 * Get plugins list
	 *
	 * @since 1.0.0
	 */
	public function get_plugins( $required = 3 ){

		return $this->remote_get( [
			'purchase_code' => $this->get_purchase_code(),
			'required' => $required
		], self::REY_API_ENDPOINT__GET_PLUGINS );

	}

	/**
	 * Get template download url
	 *
	 * @since 1.0.0
	 */
	public function get_template_data( $sku ){
		return $this->remote_get( [
			'purchase_code' => $this->get_purchase_code(),
			'sku' => $sku
		], self::REY_API_ENDPOINT__GET_TEMPLATE_DATA );
	}


	/**
	 * Send newsletter subscription request to API server
	 *
	 * @since 1.0.0
	 */
	public function subscribe_newsletter($email_address){
		return $this->remote_post( [
			'email_address' => $email_address
		], self::REY_API_ENDPOINT__NEWSLETTER );
	}

	/**
	 * Makes a download URL for either theme or plugins.
	 *
	 * @since 1.4.2
	 */
	public function get_download_url( $item = 'theme', $version = '' ){

		if( ! ($code = $this->get_purchase_code()) ){
			return;
		}

		return add_query_arg( array_merge([
				'download_item' => $item,
				'version' => $version,
				'purchase_code' => $code,
				'url' => site_url(),
			], self::get_extra_body_args()), self::$api_site_url
		);
	}

	/**
	 * Makes a download URL for either theme or plugins.
	 *
	 * @since 1.4.2
	 */
	public function get_test_url(){
		return add_query_arg([
			'purchase_code' => $this->get_purchase_code()
			], self::$api_site_url
		);
	}

	public function get_response_error( $data ){
		if( is_array($data) ){
			$return = [];
			unset($data['success']);
			array_walk_recursive($data, function($a) use (&$return) {
				$return[] = $a;
			});
			return implode('; ', $return);
		}
		else {
			return $data;
		}
	}

	/**
	 * Predefined error codes & messages
	 *
	 * @since 1.0.0
	 */
	public function get_errors() {
		return apply_filters('rey/api/errors', [
			'revoked' => rey__wp_kses( __( '<strong>Your purchase has been cancelled</strong> ( most likely due to a refund request ). Please consider acquiring a new license.', 'rey' ) ),
			'another_item' => rey__wp_kses( __( 'This purchase code seems to belong to another item, not Rey Theme. Please double check the purchase code.', 'rey' ) ),
			'not_valid' => rey__wp_kses( __( 'The purchase code is invalid. Please double check it.', 'rey' ) ),
			'invalid_request' => rey__wp_kses( __( 'Sorry, invalid API request.', 'rey' ) ),
			'retry' => rey__wp_kses( __( 'An error occurred, please try again.', 'rey' ) ),
			'already_exists' => rey__wp_kses( sprintf(__( 'This purchase code is already registered elsewhere. Either purchase a new Rey Theme license if you want to work on a new project, or deregister this purchase code from the other site where its in use, or <a href="%s" target="_blank">use this tool</a> to force deregister.', 'rey' ), rey__support_url('deregister-rey/') ) ),
			'no_json' => rey__wp_kses( sprintf(__( 'Response from API server seems to be empty. Please try again, or make sure your server is not blocking the API calls towards https://api.reytheme.com/. Follow <a href="%s" target="_blank">this article</a> for more informations.', 'rey' ), rey__support_url('kb/response-from-api-server-seems-to-be-empty/') ) ),
			'blocked' => rey__wp_kses(
				sprintf(
					__( 'Failed! Response from the API server is unusual and it\'s likely your website server\'s IP may be blocked. Please <a href="%s" target="_blank">submit a ticket request</a> and send us your server IP which is <u>%s</u> so we can search our server firewall and unblock it.', 'rey' ),
					rey__support_url('new/'),
					(($ips = ReyTheme_Base::get_ips_raw()) ? $ips['public'] : '')
				)
			),
		] );
	}

	/**
	 * Get predefined errors
	 *
	 * @since 1.0.0
	 */
	public function get_error_message( $error ) {
		$errors = $this->get_errors();

		if ( isset( $errors[ $error ] ) ) {
			$error_msg = $errors[ $error ];
		} else {
			$error_msg = $error . esc_html__( ' If the problem persists, contact our support.', 'rey' );
		}

		return $error_msg;
	}

	public static function get_extra_body_args(){
		return [
			'theme-version' => REY_THEME_VERSION,
			'core-version' => defined('REY_CORE_VERSION') ? REY_CORE_VERSION : '',
			'php-version' => PHP_VERSION,
		];
	}

	/**
	 * Wrapper for wp_remote_post, used for
	 * API Server requests
	 *
	 * @since 1.0.0
	 */
	protected function remote_post( $body_args, $endpoint ) {

		$args = apply_filters('rey/api/remote_post/args', [
			'timeout' => 40,
			'body' => array_merge((array) $body_args, self::get_extra_body_args()),
		], $endpoint);

		$args['body']['url'] = site_url();

		$request = wp_remote_post( self::$api_url . $endpoint, $args );

		if ( is_wp_error( $request ) ) {
			return $request;
		}

		return $this->resolve_data( $request );
	}

	/**
	 * Wrapper for wp_remote_get, used for
	 * API Server requests
	 *
	 * @since 1.0.0
	 */
	protected function remote_get( $body_args, $endpoint ) {

		$args = apply_filters('rey/api/remote_get/args', [
			'timeout' => 40,
			'body' => array_merge((array) $body_args, self::get_extra_body_args()),
		], $endpoint);

		$args['body']['url'] = site_url();
		$args['body']['ftime'] = current_time('timestamp');

		$request = wp_remote_get( self::$api_url . $endpoint, $args );

		if ( is_wp_error( $request ) ) {
			return $request;
		}

		return $this->resolve_data( $request );
	}

	/**
	 * Wrapper for wp_remote_get, used for
	 * API Server requests on JSON files
	 *
	 * @since 1.0.7
	 */
	public function file_remote_get( $filepath ) {

		$args = [
			'timeout' => 40,
			'body' => self::get_extra_body_args(),
		];

		$args = apply_filters('rey/api/file_remote_get/args', [
			'timeout' => 40,
			'body' => array_merge([
				'url' => site_url(),
			], self::get_extra_body_args()),
		], $filepath);

		if( current_user_can('administrator') ) {

			$add_timestamp = apply_filters('rey/file_remote_get/timestamp',
				// WP force check
				(isset($_REQUEST['force-check']) && 1 === absint($_REQUEST['force-check'])) ||
				// ajax calls
				(isset($_REQUEST['action']) && in_array(rey__clean($_REQUEST['action']), ['rey_outdated_link']))
			);

			if( $add_timestamp ) {
				$args['body']['ftime'] = current_time('timestamp');
			}
		}

		$request = wp_remote_get( self::$api_files_url . $filepath, $args );

		if ( is_wp_error( $request ) ) {
			return $request;
		}

		return $this->resolve_data( $request );
	}

	/**
	 * Parse the remote data
	 *
	 * @since 1.0.7
	 */
	protected function resolve_data( $request ){

		$response_body = wp_remote_retrieve_body( $request );

		// if SG firewall, return error
		if( strpos($response_body, 'well-known/captcha') !== false || strpos($response_body, 'http-equiv="refresh"') !== false ){
			return new \WP_Error( 'blocked', $this->get_error_message('blocked') );
		}

		$data = json_decode( $response_body, true );

		if ( empty( $data ) || ! is_array( $data ) ) {
			return new \WP_Error( 'no_json', $this->get_error_message('no_json') );
		}

		$response_code = wp_remote_retrieve_response_code( $request );

		do_action('rey/api/parse_remote/before', $data, $response_code);

		if ( 200 === (int) $response_code ) {
			return $data;
		}

		do_action('rey/api/parse_remote', $data, $response_code);

		return new \WP_Error( $response_code, $this->get_response_error( $data ) );
	}

	/**
	 * Retrieve the reference to the instance of this class
	 * @return ReyTheme_API
	 */
	public static function getInstance()
	{
		if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}
}

ReyTheme_API::getInstance();
