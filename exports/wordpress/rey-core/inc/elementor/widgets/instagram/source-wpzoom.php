<?php
namespace ReyCore\Elementor\Widgets\WPZoomInsta;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Base {

	private $_feeds;

	public static $_image_sizes = [
		'thumbnail' => 150,
		'low_resolution' => 320,
		'standard_resolution' => 640,
	];

	private static $_instance = null;

	private function __construct() {}

	public function add_control( $element ){

		if( $this->is_active() ){

			$has_feeds = false;

			if( $this->is_v2() ){

				$element->add_control(
					'wpzoom_v2_feed_id',
					[
						'label' => __( 'Select Feed', 'rey-core' ),
						'default' => '',
						'type' => 'rey-ajax-list',
						'query_args' => [
							'request' => 'insta_v2_feeds_get_list',
						],
					]
				);

			}
			else {

				if( $this->api()->is_configured() ) {
					$has_feeds = true;
				}

			}

			if( ! $has_feeds ){
				$element->add_control(
					'connect_msg',
					[
						'type' => \Elementor\Controls_Manager::RAW_HTML,
						'raw' => $this->get_misconfigured_message(),
						'content_classes' => 'elementor-panel-alert elementor-panel-alert-danger',
					]
				);
			}

			return true;
		}

		return false;
	}

	/**
	 * Determine if WPZOOM Instagram Feed Widget is installed and active
	 *
	 * @return bool
	 */
	public function is_active(){

		if( ! defined( 'WPZOOM_INSTAGRAM_VERSION' ) ){
			return;
		}

		if( ! class_exists('\Wpzoom_Instagram_Widget_API') ){
			return;
		}

		return true;
	}

	public function is_v2(){
		return version_compare(WPZOOM_INSTAGRAM_VERSION, '2.0', '>=');
	}

	public static function v2_feeds_get_posts(){
		return get_posts([
			'post_type' => 'wpz-insta_feed',
			'post_status' => 'publish',
		]);
	}

	public function v2_get_feeds(){

		if( $this->_feeds ){
			return $this->_feeds;
		}

		return $this->_feeds = self::v2_feeds_get_posts();
	}

	public function api(){
		return \Wpzoom_Instagram_Widget_API::getInstance();
	}

	/**
	 * WPZoom Instagram Plugin's `set_images_to_transient` is protected
	 */
	public function set_images_to_transient( $attachment_id, $media_id, $uploaderClass ){

		$wpZoomInstance = $uploaderClass::getInstance();
		$transient = $wpZoomInstance->get_api_transient();

		if ( ! empty( $transient->data ) ) {
			foreach ( $transient->data as $key => $item ) {
				if ( $item->id === $media_id ) {
					$thumbnail                         = wp_get_attachment_image_src( $attachment_id, $uploaderClass::get_image_size_name( 'thumbnail' ) );
					$low_resolution                    = wp_get_attachment_image_src( $attachment_id, $uploaderClass::get_image_size_name( 'low_resolution' ) );
					$standard_resolution               = wp_get_attachment_image_src( $attachment_id, $uploaderClass::get_image_size_name( 'standard_resolution' ) );
					$item->images->thumbnail->url      = ! empty( $thumbnail ) ? $thumbnail[0] : '';
					$item->images->low_resolution->url = ! empty( $low_resolution ) ? $low_resolution[0] : '';;
					$item->images->standard_resolution->url = ! empty( $standard_resolution ) ? $standard_resolution[0] : '';;

					$transient->data[ $key ] = $item;
				}
			}

			$wpZoomInstance->set_api_transient( $transient );
		}

	}

	/**
	 * WPZoom Instagram Plugin's `get_best_size` is protected
	 */
	public function get_best_size($desired_width, $image_resolution = 'default_algorithm' ) {

		$size = 'thumbnail';

		$sizes = self::$_image_sizes;

		$diff = PHP_INT_MAX;

		if ( array_key_exists( $image_resolution, $sizes ) ) {
			return $image_resolution;
		}

		foreach ( $sizes as $key => $value ) {
			if ( abs( $desired_width - $value ) < $diff ) {
				$size = $key;
				$diff = abs( $desired_width - $value );
			}
		}

		return $size;
	}

	public function query_items($settings){

		// v2.0 compatibility
		if( $this->is_v2() ){

			// Check for feeds
			if( ($wpzoom_v2_feeds = $this->v2_get_feeds()) && is_array($wpzoom_v2_feeds) ){

				$wpzoom_v2_feed_id = isset($settings['wpzoom_v2_feed_id']) && ! empty($settings['wpzoom_v2_feed_id']) ? $settings['wpzoom_v2_feed_id'] : $wpzoom_v2_feeds[0]->ID;

				$wpzoom_v2_user_id = get_post_meta($wpzoom_v2_feed_id, '_wpz-insta_user-id', true);

				if( $wpzoom_v2_user_id ){

					$wpzoom_v2_user_account_token = get_post_meta( $wpzoom_v2_user_id, '_wpz-insta_token', true ) ?: false;

					if( $wpzoom_v2_user_account_token ){
						$this->api()->set_access_token( $wpzoom_v2_user_account_token );
					}
					// must have User ID access token
					else {
						return;
					}
				}
				// must have User ID
				else {
					return;
				}
			}

			// check if it was configured previously
			else {

				$wpzoom_settings = get_option( 'wpzoom-instagram-widget-settings', wpzoom_instagram_get_default_settings() );

				if( isset($wpzoom_settings['basic-access-token']) && $basic_access_token = $wpzoom_settings['basic-access-token'] ){
					$this->api()->set_access_token( $basic_access_token );
				}

				// just bail
				else {
					return;
				}

			}

		}

		// legacy before v2.0
		else {
			// in v2.0 `is_configured` always returns false
			if( ! $this->api()->is_configured() ) {
				return;
			}
		}

		$items = $errors = [];

		$args = [
			'image-limit' => $settings['limit'],
			'image-width' => self::$_image_sizes[$settings['img_size']],
			'image-resolution' => $settings['img_size'],
		];

		$insta_items = $this->api()->get_items($args);

		if( $insta_items ){

			$wpzoom_uploaderClass = '\WPZOOM_Instagram_Image_Uploader';

			if( isset($insta_items['items']) && class_exists($wpzoom_uploaderClass) ){

				$wpzoom_uploader = $wpzoom_uploaderClass::getInstance();

				$wpzoom_media_metakey_name = 'wpzoom_instagram_media_id';
				$wpzoom_post_status_name = 'wpzoom-hidden';

				foreach ($insta_items['items'] as $key => $item) {

					if( $item['image-url'] === false ){

						$media_url = $wpzoom_uploaderClass::get_media_url_by_id( $item['image-id'] );

						$query = new \WP_Query( [
							'post_type'      => 'attachment',
							'posts_per_page' => 1,
							'post_status'    => $wpzoom_post_status_name,
							'meta_query'     => [
								[
									'key'   => $wpzoom_media_metakey_name,
									'value' => $item['image-id'],
								],
							],
						] );

						if ( $query->have_posts() ) {
							$post          = array_shift( $query->posts );
							$attachment_id = $post->ID;
						} else {
							$attachment_id = $wpzoom_uploaderClass::upload_image( $media_url, $item['image-id'] );
						}

						$this->set_images_to_transient( $attachment_id, $item['image-id'], $wpzoom_uploaderClass );

						if ( ! is_wp_error( $attachment_id ) ) {

							$media_size = $this->get_best_size( $args['image-width'], $args['image-resolution'] );

							$image_src = wp_get_attachment_image_src( $attachment_id, $wpzoom_uploaderClass::get_image_size_name( $media_size ) );

							$item['image-url'] = ! empty( $image_src ) ? $image_src[0] : $media_url;

							$items['items'][$key] = $item;
						}

					}
					else {
						$items['items'][$key] = $item;
					}
				}
			}

			if( isset($insta_items['username']) ){
				$items['username'] = $insta_items['username'];
			}

		}
		else {
			$errors = $this->api()->errors->get_error_messages();
		}

		return [
			'items' => $items,
			'errors' => $errors,
		];

	}

	public function get_misconfigured_message(){

		if( $this->is_v2() ){
			$url = admin_url('edit.php?post_type=wpz-insta_feed');
		}
		else {
			$url = admin_url('options-general.php?page=wpzoom-instagram-widget');
		}

		return sprintf( __( 'No Instagram posts found. If you have just installed or updated "Instagram Widget by WPZOOM" plugin, please go to the <a href="%s" target="_blank">Settings page</a> and <strong>connect</strong> it with your Instagram account.', 'rey-core' ), $url );

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
