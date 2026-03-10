<?php
namespace ReyCore\Elementor\Widgets\ReyInsta;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Base {

	public $_settings = [];

	private $access_token = null;
	private $api_url = 'https://graph.instagram.com/';
	private $feed_endpoint = 'me/media/';

	public $_config = [];

	private static $_instance = null;

	private function __construct() {}

	public static function get_token(){
		return \ReyCore\Libs\IgTokenManager::get_token();
	}

	public function query_items( $settings, $element_id = '' ){

		$config = [
			'cache_refresh' => DAY_IN_SECONDS,
			'timeout'       => 60,
			'sslverify'     => false,
		];

		if( ! ($access_token = self::get_token()) ){
			return [
				'errors' => [
					esc_html__('Invalid or missing Access Token.', 'rey-core')
				]
			];
		}

		$config['access_token'] = $access_token;

		$this->_settings = $settings;

		$this->_config = apply_filters('reycore/elementor/instagram/config', $config, $this->_settings, $element_id );

		$items = $errors = [];

		$transient_key = sprintf('rey_insta_%s_%s', $this->_settings['limit'], md5($this->_config['access_token']));

		if( isset($_REQUEST['ig_clear_cache']) && current_user_can('administrator') ){
			delete_transient($transient_key);
		}

		$data = get_transient( $transient_key );

		if ( isset($data['errors']) ) {
			return [
				'errors' => $data['errors']
			];
		}

		if ( $data ) {
			return [
				'items' => $data
			];
		}

		do_action( 'qm/debug', 'Refreshed Instagram FEED CACHE.' );

		$response = wp_safe_remote_get( $this->get_url() , [
			'timeout'   => $this->_config['timeout'],
			'sslverify' => $this->_config['sslverify'],
		] );

		if( is_wp_error( $response ) ){

			$return = [
				'errors' => [
					sprintf('Response Code: %d', 400),
					$response->get_error_message(),
				]
			];

			set_transient( $transient_key, $return, $this->_config['cache_refresh'] );

			return $return;
		}

		$response_code  = wp_remote_retrieve_response_code( $response );
		$result         = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( 200 !== $response_code ) {

			$return = [
				'errors' => [
					sprintf('Response Code: %d', $response_code),
					sprintf('Response Body: %s', wp_kses_post(wp_remote_retrieve_body( $response ))),
					is_array( $result ) && isset( $result['error']['message'] ) ? $result['error']['message'] : __( 'Something is not right. Please double-check Access Token.', 'rey-core' ),
				]
			];

			set_transient( $transient_key, $return, $this->_config['cache_refresh'] );

			return $return;
		}

		if ( ! $result ) {
			return [
				'errors' => [
					__( 'Invalid format response. Please double-check the Access Token.', 'rey-core' ),
					sprintf('Response Body: %s', wp_kses_post(wp_remote_retrieve_body( $response ))),
				]
			];
		}

		if ( ! is_array( $result ) ) {
			return [
				'errors' => [
					__( 'Invalid format response.', 'rey-core' )
				]
			];
		}

		if ( ! array_key_exists( 'data', $result ) ) { // Avoid PHP notices
			return [
				'errors' => [
					__( 'Missing data.', 'rey-core' )
				]
			];
		}

		$response_items = $result['data'];

		if ( empty( $response_items ) ) {
			return [
				'errors' => [
					__( 'Missing items in response.', 'rey-core' )
				]
			];
		}

		$sliced_items = array_slice( $response_items, 0, $this->_settings['limit'], true );

		foreach ( $sliced_items as $item ) {

			$_item = [
				'image-url'          => '',
				'local-image-url'    => '',
				'children'           => '',
				'likes_count'        => 0,
				'comments_count'     => 0,
			];

			$_item['link'] = isset($item['permalink']) ? esc_url($item['permalink']) : '';
			$_item['type'] = isset($item['media_type']) ? esc_attr($item['media_type']) : '';
			$_item['image-caption'] = isset($item['caption']) ? esc_attr($item['caption']) : '';
			$_item['image-id'] = isset($item['id']) ? esc_attr($item['id']) : '';
			$_item['timestamp'] =  isset($item['timestamp'])? esc_attr($item['timestamp']) : '';
			$_item['original-image-url'] = isset($item['media_url']) ? esc_url($item['media_url']) : '';

			if( 'VIDEO' === $_item['type'] && isset($item['thumbnail_url']) ){
				$_item['original-image-url'] = esc_url($item['thumbnail_url']);
			}

			$items['items'][] = $_item;

			if( ! isset($items['username']) && isset($item['username']) ){
				$items['username'] = $item['username'];
			}

		}

		set_transient( $transient_key, $items, $this->_config['cache_refresh'] );

		return [
			'items' => $items,
		];

	}

	public function get_url(){

		return add_query_arg( [
			'fields'       => 'id,media_type,media_url,thumbnail_url,permalink,caption,timestamp,username',
			'access_token' => $this->_config['access_token'],
		], $this->api_url . $this->feed_endpoint );

	}

	/**
	 * Retrieve the reference to the instance of this class
	 * @return Base
	 */
	public static function getInstance()
	{
		if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}
}
