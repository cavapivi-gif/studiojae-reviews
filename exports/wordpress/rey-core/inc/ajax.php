<?php
namespace ReyCore;

use ReyCore\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ajax
{

	/**
	 * Action key name for making ajax calls.
	 */
	const ACTION_KEY = 'reycore-ajax';

	/**
	 * Key name for the pre-sanitized data store.
	 */
	const DATA_KEY = 'reycore-ajax-data';

	/**
	 * Key name for the ajax transients names.
	 */
	const AJAX_TRANSIENT_NAME = 'reycore_ajax_cache';

	/**
	 * Name of the Nonce.
	 */
	const NONCE_KEY = 'reycore_ajax';

	/**
	 * HTTP status code for bad request error.
	 */
	const BAD_REQUEST = 400;

	/**
	 * HTTP status code for unauthorized access error.
	 */
	const UNAUTHORIZED = 401;

	/**
	 * Ajax actions.
	 *
	 * Holds all the register ajax action.
	 *
	 * @since 2.2.2
	 * @access private
	 *
	 * @var array
	 */
	private $ajax_actions = [];

	/**
	 * Ajax requests.
	 *
	 * Holds all the register ajax requests.
	 *
	 * @since 2.2.2
	 * @access private
	 *
	 * @var array
	 */
	private $requests = [];

	/**
	 * Ajax response data.
	 *
	 * Holds all the response data for all the ajax requests.
	 *
	 * @since 2.2.2
	 * @access private
	 *
	 * @var array
	 */
	private $response_data = [];

	/**
	 * Current ajax action ID.
	 *
	 * Holds all the ID for the current ajax action.
	 *
	 * @since 2.2.2
	 * @access private
	 *
	 * @var string|null
	 */
	private $current_action_id = null;

	/**
	 * Ajax manager constructor.
	 *
	 * Initializing ajax manager.
	 *
	 * @since 2.2.2
	 * @access public
	 */
	public function __construct() {

		add_action( 'init', [$this, 'define_ajax'], 0 );
		add_action( 'wp_loaded', [$this, 'handle_ajax_request'], 0 );
		// add_action( 'template_redirect', [$this, 'handle_ajax_request'], 0 );
		// add_action( 'wp', [$this, 'handle_ajax_request'], 0 );
		add_filter( 'reycore/script_params', [$this, 'script_params'], 20);
		add_filter( 'reycore/admin_script_params', [$this, 'script_params'], 20);
		add_filter( 'reycore/admin_bar_menu/nodes', [$this, 'adminbar_add_node'], 20);
		add_action( 'reycore/caching_plugins/flush', [$this, 'flush_ajax_transients']);
		add_action( 'reycore/refresh_all_caches', [$this, 'flush_ajax_transients']);
	}

	public static function is_debug(){
		return defined('REY_DISABLE_AJAX_CACHE') && REY_DISABLE_AJAX_CACHE;
	}

	/**
	 * Append params to main script.
	 *
	 * @param array $params
	 * @return array
	 */
	public function script_params($params){

		$params['r_ajax_debug'] = self::is_debug();
		$params['r_ajax_nonce'] = wp_create_nonce( self::NONCE_KEY );
		$params['r_ajax_url'] = esc_url_raw(
			add_query_arg(
				self::ACTION_KEY,
				'%%endpoint%%',
				remove_query_arg( [
						'_wpnonce'
					],
					home_url( '/', 'relative' )
				)
			)
		);
		$params['ajax_queue'] = true;

		return $params;
	}

	/**
	 * Set AJAX constant and headers.
	 */
	public function define_ajax() {

		if ( empty( $_GET[ self::ACTION_KEY ] ) ) {
			return;
		}

		if ( ! WP_DEBUG || ( WP_DEBUG && ! WP_DEBUG_DISPLAY ) ) {
			@ini_set( 'display_errors', 0 ); // Turn off display_errors during AJAX events to prevent malformed JSON.
		}

		$GLOBALS['wpdb']->hide_errors();

	}

	/**
	 * Check if doing ajax action
	 *
	 * @param string $action_id
	 * @return bool
	 */
	public static function doing_ajax( $action_id = '' ){

		$defined = defined( 'REY_DOING_AJAX' ) && REY_DOING_AJAX;

		if( $defined && isset($_REQUEST[self::ACTION_KEY]) && $_REQUEST[self::ACTION_KEY] === $action_id ){
			return true;
		}

		return $defined;
	}

	/**
	 * Handle ajax request.
	 *
	 * Verify ajax nonce, and run all the registered actions for this request.
	 *
	 * Fired by `wp_ajax_reycore_ajax` action.
	 *
	 * @since 2.2.2
	 * @access public
	 */
	public function handle_ajax_request() {

		if( ! isset($_REQUEST[self::ACTION_KEY]) ){
			return;
		}

		if( empty($_REQUEST[self::ACTION_KEY]) ){
			return;
		}

		if ( ! headers_sent() ) {

			send_origin_headers();
			send_nosniff_header();

			if ( ! defined( 'DONOTCACHEPAGE' ) ) define( 'DONOTCACHEPAGE', true );
			if ( ! defined( 'DONOTCACHEOBJECT' ) ) define( 'DONOTCACHEOBJECT', true );
			if ( ! defined( 'DONOTCACHEDB' ) ) define( 'DONOTCACHEDB', true );

			nocache_headers();

			header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
			header( 'X-Robots-Tag: noindex' );

			status_header( 200 );
		}
		elseif ( defined('WP_DEBUG') && WP_DEBUG ) {
			headers_sent( $file, $line );
			trigger_error( "Cannot set headers - headers already sent by {$file} on line {$line}", E_USER_NOTICE ); // @codingStandardsIgnoreLine
		}

		if( ! ($action_id = reycore__clean($_REQUEST[self::ACTION_KEY])) ){
			$this->add_response_data( false, [
				'data'    => esc_html__( 'Missing action.', 'rey-core' ),
			] )->send_error( self::BAD_REQUEST );
		}

		/**
		 * Register ajax actions.
		 *
		 * Fires when an ajax request is received and verified.
		 *
		 * Used to register new ajax action handles.
		 *
		 * @since 2.2.2
		 *
		 * @param self $this An instance of ajax manager.
		 */
		do_action( 'reycore/ajax/register_actions', $this );

		$this->register_default_actions();

		if ( ! isset( $this->ajax_actions[ $action_id ] ) ) {
			$this->add_response_data( false, [
				'data' => esc_html__( 'Action not found.', 'rey-core' ),
			] )->send_error( self::UNAUTHORIZED );
		}

		$maybe_check_nonce = apply_filters( 'reycore/ajax/nonce/action=' . $action_id, $this->ajax_actions[ $action_id ]['nonce'] );

		$deprecated_hooks = [
			'account_forms' => 'reycore/woocommerce/lrf_forms_ajax/nonce'
		];

		if( isset($deprecated_hooks[$action_id]) ){
			$maybe_check_nonce = apply_filters_deprecated($deprecated_hooks[$action_id], [$maybe_check_nonce], '2.5.0');
		}

		if ( $maybe_check_nonce && ! $this->verify_request_nonce() ) {
			$this->add_response_data( false, [
				'data' => esc_html__( 'Token Expired or Invalid.', 'rey-core' ),
			] )->send_error( self::UNAUTHORIZED );
		}

		$this->current_action_id = $action_id;

		if ( ! isset( $this->ajax_actions[ $action_id ]['auth'] ) ) {
			$this->add_response_data( false, [
				'data' => esc_html__( 'Missing auth level.', 'rey-core' ),
			] )->send_error( self::UNAUTHORIZED );
		}

		else {

			if( ($cap = $this->ajax_actions[ $action_id ]['capability']) && ! current_user_can($cap) ){
				$this->add_response_data( false, [
					'data' => esc_html__( 'Not allowed.', 'rey-core' ),
				] )->send_error( self::UNAUTHORIZED );
			}

			$logged_status = is_user_logged_in();

			if( $this->ajax_actions[ $action_id ]['auth'] === 1 && ! $logged_status ){
				$this->add_response_data( false, [
					'data' => esc_html__( 'Incorrect authorization level.', 'rey-core' ),
				] )->send_error( self::UNAUTHORIZED );
			}

			elseif( $this->ajax_actions[ $action_id ]['auth'] === 2 && $logged_status ){
				$this->add_response_data( false, [
					'data' => esc_html__( 'Incorrect authorization level.', 'rey-core' ),
				] )->send_error( self::UNAUTHORIZED );
			}

		}

		// get the action params
		$action_data = isset($_REQUEST[self::DATA_KEY]) ? reycore__clean($_REQUEST[self::DATA_KEY]) : [];

		// is admin but only in frontend
		$is_administrator_frontend = current_user_can('administrator') && ! ($this->ajax_actions[ $action_id ]['auth'] === 1);

		// Disable caching in WP_DEBUG mode or for administrators
		if( apply_filters('reycore/ajax/cache', self::is_debug() || $is_administrator_frontend , $this) ){
			$this->ajax_actions[ $action_id ]['transient'] = false;
		}

		// Shortcircuit caching, if specified
		if( isset($action_data['cache']) && filter_var($action_data['cache'], FILTER_VALIDATE_BOOL) === false ){
			$this->ajax_actions[ $action_id ]['transient'] = false;
		}

		// possiblility to pass after data
		// for exampole for the lazy popover
		$after_data = $this->ajax_actions[ $action_id ]['after_data'];

		// Get cached response
		if( $transient = $this->ajax_actions[ $action_id ]['transient'] ){

			// prepare the transient data (name & expiration)
			$transient_data = $this->get_transient_data($transient, $action_id, $action_data);

			// Force a refresh of the transient
			// For example passed through Element_lazy, which needs an instant refresh
			if(
				current_user_can('administrator') &&
				isset($action_data['refresh']) &&
				filter_var($action_data['refresh'], FILTER_VALIDATE_BOOL) === true ){
				delete_transient($transient_data['name']);
			}

			// check for existing transient data
			if( false !== ($cached_data = get_transient($transient_data['name'])) ){

				$r_data['data'] = array_key_exists('data', $cached_data) ? $cached_data['data'] : $cached_data;
				$r_data['after_data'] = $after_data;

				if( isset($cached_data['assets']) ){
					$r_data['assets'] = $cached_data['assets'];
				}

				if( isset($cached_data['transient']) && is_user_logged_in() && current_user_can('edit_posts') ){
					$r_data['transient'] = $cached_data['transient'];
				}

				$this->add_response_data( true, $r_data )->send_success();
			}

		}

		try {

			// start collecting assets
			if( $transient || $this->ajax_actions[ $action_id ]['assets'] ){
				reycore_assets()->register_assets();
				reycore_assets()->collect_start('ajax_' . $action_id);
			}

			// Run callback
			$r_data['data'] = call_user_func( $this->ajax_actions[ $action_id ]['callback'], $action_data, $this );
			$r_data['after_data'] = $after_data;

			// end collecting assets
			if( $transient || $this->ajax_actions[ $action_id ]['assets'] ){

				$collected_assets = reycore_assets()->collect_end('ajax_' . $action_id, true);

				if(
					$r_data['data'] &&
					! isset( $r_data['data']['errors'] ) &&
					! empty($collected_assets)
				){

					// pass collected assets object
					$r_data['assets'] = $collected_assets;

					// pass transient name for admin bar flushing
					if( $transient && is_user_logged_in() && current_user_can('edit_posts') ){
						$r_data['transient'] = $transient_data['name'];
					}
				}
			}

			if( $transient ){
				set_transient($transient_data['name'], $r_data, $transient_data['expiration']);
			}

			$this->add_response_data( true, $r_data );

		} catch ( \Exception $e ) {
			$this->add_response_data( false, [
				'data' => $e->getMessage(),
			] )->send_error( $e->getCode() );
		}

		$this->current_action_id = null;

		$this->send_success();
	}

	private function get_transient_data($transient, $action_id, $action_data){

		// unique hash
		$transient_unique_id = Helper::hash($action_data);

		// sometimes transient data is more complex
		if( is_array($transient) ){

			// set expiration and default to a day if unspecified
			$transient_expiration = isset($transient['expiration']) ? $transient['expiration'] : WEEK_IN_SECONDS;

			// check for a unique action data parameter to replace the hash
			if( isset($transient['unique_id']) ){
				// has multiple pieces
				if( is_array($transient['unique_id']) ){
					// start collecting ids
					$transient_unique_ids = [];
					// iterate
					foreach ($transient['unique_id'] as $key => $_unique_id) {
						// must have it as an action data
						if( isset($action_data[$_unique_id]) ){
							// look if sanitization method provided
							if( isset($transient['unique_id_sanitize'][$key]) ){
								// call sanitization
								if( $a_data_id = call_user_func( $transient['unique_id_sanitize'][$key], $action_data[$_unique_id] ) ){
									$transient_unique_ids[] = $a_data_id;
								}
							}
							// just add if not (doesn't need)
							else {
								$transient_unique_ids[] = $action_data[$_unique_id];
							}
						}
					}
					// prepare the final unique id
					$transient_unique_id = implode('_', $transient_unique_ids);
				}
				// single value
				else {
					if( isset($action_data[$transient['unique_id']]) ){
						// look if sanitization method provided
						if( isset($transient['unique_id_sanitize']) ){
							// call sanitization
							if( $a_data_id = call_user_func( $transient['unique_id_sanitize'], $action_data[$transient['unique_id']] ) ){
								$transient_unique_id = $a_data_id;
							}
						}
						// just add if not (doesn't need)
						else {
							$transient_unique_id = $action_data[$transient['unique_id']];
						}

					}
				}
			}
		}
		// likely just expiration passed
		else {
			$transient_expiration = $transient;
		}

		// transient name pieces
		$transient_name_pieces[] = self::AJAX_TRANSIENT_NAME;
		$transient_name_pieces[] = $action_id;
		$transient_name_pieces[] = $transient_unique_id;

		if( $lang = reycore__is_multilanguage() ){
			$transient_name_pieces[] = $lang;
		}

		$transient_name_pieces[] = reycore__versions_hash();

		// sets the transient name by gluing the pieces
		$transient_name = implode('_', $transient_name_pieces);

		return [
			'name' => $transient_name,
			'expiration' => $transient_expiration,
		];

	}

	/**
	 * Register ajax action.
	 *
	 * Add new actions for a specific ajax request and the callback function to
	 * be handle the response.
	 *
	 * Auth levels
	 * 1 = logged-in only
	 * 2 = logged-out only
	 * 3 = both logged-in and out
	 *
	 * @since 2.2.2
	 * @access public
	 *
	 * @param string   $tag      Ajax request name/tag.
	 * @param callable $callback The callback function.
	 */
	public function register_ajax_action( $tag, $callback, $data = 3 ) {

		if ( ! did_action( 'reycore/ajax/register_actions' ) ) {
			_doing_it_wrong( __METHOD__, esc_html( sprintf( 'Use `%s` hook to register ajax action.', 'reycore/ajax/register_actions' ) ), '2.2.2' );
		}

		if( empty($callback) ){
			_doing_it_wrong( __METHOD__, 'Must provide a valid callback.', '2.4.0' );
			return;
		}

		$tag_config = [
			// callback method to run
			'callback'        => $callback,
			// Authentication level
			'auth'            => 3,
			// Check nonce
			'nonce'           => true,
			// Pass a capability check
			'capability'      => '',
			// Scan for assets and return their handle => path
			'assets'          => false,
			// cache the response
			'transient'       => false,
			// custom data (non-cached) for various purposes
			'after_data'      => false,
		];

		if( is_array($data) ){
			$this->ajax_actions[ $tag ] = wp_parse_args($data, $tag_config);
		}

		// means only auth level specified
		elseif (is_numeric($data)){
			$tag_config['auth'] = $data;
			$this->ajax_actions[ $tag ] = $tag_config;
		}

	}

	/**
	 * Verify request nonce.
	 *
	 * Whether the request nonce verified or not.
	 *
	 * @since 2.2.2
	 * @access public
	 *
	 * @return bool True if request nonce verified, False otherwise.
	 */
	public function verify_request_nonce() {
		return ! empty( $_REQUEST['_nonce'] ) && wp_verify_nonce( $_REQUEST['_nonce'], self::NONCE_KEY );
	}

	/**
	 * Add response data.
	 *
	 * Add new response data to the array of all the ajax requests.
	 *
	 * @since 2.2.2
	 * @access protected
	 *
	 * @param bool  $success True if the requests returned successfully, False
	 *                       otherwise.
	 * @param mixed $data    Optional. Response data. Default is null.
	 *
	 * @param int   $code    Optional. Response code. Default is 200.
	 *
	 * @return Base An instance of ajax manager.
	 */
	private function add_response_data( $success, $args ) {

		$this->response_data[ $this->current_action_id ] = wp_parse_args($args, [
			// standard response
			'success'    => $success,
			'code'       => 200,
			// response data
			'data'       => null,
			// assets object (type : {handler: path})
			'assets'     => false,
			// transient name (logged-in only)
			'transient'  => false,
			// custom data (non-cached)
			'after_data' => false,
		]);

		return $this;
	}

	/**
	 * Ajax failure response.
	 *
	 * Send a JSON response data back to the ajax request, indicating failure.
	 *
	 * @since 2.2.2
	 * @access protected
	 *
	 * @param null $code
	 */
	private function send_error( $code = null ) {
		wp_send_json_error( $this->response_data, $code );
	}

	public function get_response_data(){
		return $this->response_data;
	}

	/**
	 * Ajax success response.
	 *
	 * Send a JSON response data back to the ajax request, indicating success.
	 *
	 * @since 2.2.2
	 * @access protected
	 */
	private function send_success() {

		/**
		 * Fires before sending the success response.
		 * Can be used for additional actions before sending the response,
		 * or to modify the response data by short-circuiting the send_success method.
		 */
		do_action( 'reycore/ajax/before_send_success', $this );

		$response = [
			'success' => true,
			'data' => $this->response_data,
		];

		$json = wp_json_encode( $response );

		while ( ob_get_status() ) {
			ob_end_clean();
		}

		$accept_encoding = isset($_SERVER['HTTP_ACCEPT_ENCODING']) ? $_SERVER['HTTP_ACCEPT_ENCODING'] : '';

		if ( function_exists( 'gzencode' ) && strpos( $accept_encoding, 'gzip' ) !== false ) {
			$response = gzencode( $json );

			header( 'Content-Type: application/json; charset=utf-8' );
			header( 'Content-Encoding: gzip' );
			header( 'Content-Length: ' . strlen( $response ) );

			echo $response; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			header( 'Content-Type: application/json; charset=utf-8' );
			echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		wp_die( '', '', [ 'response' => null ] );

	}

	private function register_default_actions(){
		$this->register_ajax_action('ajax_flush_transients', [$this, 'flush_ajax_transients'], [
			'auth' => 1,
			'capability' => 'edit_posts'
		]);
	}

	public function flush_ajax_transients(){
		Helper::clean_db_transient( self::AJAX_TRANSIENT_NAME );
	}


	/**
	 * Adds flushing button into Rey's admin bar menu
	 *
	 * @param array $nodes
	 * @return array
	 */
	public function adminbar_add_node( $nodes ){

		if( ! current_user_can('administrator') ){
			return $nodes;
		}

		$nodes['refresh']['nodes']['refresh_ajax'] = [
			'title'  => esc_html__( 'Ajax Cache', 'rey-core' ),
			'href'  => '#',
			'meta_title' => esc_html__( 'Refresh the cached Ajax requests responses.', 'rey-core' ),
			'class' => 'qm-refresher',
		];

		return $nodes;
	}

}
