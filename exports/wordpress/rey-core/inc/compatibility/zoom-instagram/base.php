<?php
namespace ReyCore\Compatibility\ZoomInstagram;

if ( ! defined( 'ABSPATH' ) ) exit;

use \ReyCore\Libs\IgTokenManager;

class Base extends \ReyCore\Compatibility\CompatibilityBase {

	public function __construct() {

		add_action( 'init', [$this, 'init'], 9 );
		add_filter( 'acf/prepare_field/name=' . IgTokenManager::OPTION_KEY, [$this, 'add_migrate_button'] );
		add_action( 'wp_ajax_rey_migrate_ig_token', [$this, 'ajax_migrate_token'] );

	}

	public function init(){

		if( ! apply_filters('reycore/compatibility/wpzoom_instagram/disable_assets', true) ){
			return;
		}

		add_action('wp_print_scripts', [$this, 'js_assets'], 100);
		add_action('wp_print_styles', [$this, 'css_assets'], 100);

		if( class_exists('\Wpzoom_Instagram_Block') ) {
			// Disable the block because it loads the block's assets, regardless if it's in use
			// in any given page
			remove_action('init', [ \Wpzoom_Instagram_Block::get_instance(), 'init']);
		}

	}

	/**
	 * Remove Instagram Widget Assets
	 *
	 * @since 1.0.0
	 **/
	function js_assets()
	{
		wp_dequeue_script( 'zoom-instagram-widget-lazy-load' );
		wp_dequeue_script( 'zoom-instagram-widget' );
		wp_dequeue_script( 'magnific-popup' );
		wp_dequeue_script( 'swiper-js' );
	}

	/**
	 * Remove Instagram Widget Assets
	 *
	 * @since 1.0.0
	 **/
	function css_assets()
	{
		wp_dequeue_style( 'magnific-popup' );
		wp_dequeue_style( 'swiper-css' );
		wp_dequeue_style( 'zoom-instagram-widget' );
	}

	public function is_v2(){
		return version_compare(WPZOOM_INSTAGRAM_VERSION, '2.0', '>=');
	}

	public function add_migrate_button($field){

		if( IgTokenManager::get_token() ){
			return $field;
		}

		if( $this->get_zoom_access_token() ){
			$field['instructions'] .= "<br><br>** " . __('It seems you currently use WPZoom Social Feeds plugin which has an Access Token setup. Would you like to copy that token and disable that plugin so it should just rely on Rey? <br><strong><a href="#" class="js-ig-migrate-token">Yes, migrate the Access Token.</a></strong>', 'rey-core');
		}

		return $field;
	}


	public function v2_get_feeds(){

		if( ! post_type_exists('wpz-insta_feed') ){
			return;
		}

		return get_posts([
			'post_type' => 'wpz-insta_feed',
			'post_status' => 'publish',
		]);
	}

	public function get_zoom_access_token(){

		if( ! current_user_can('administrator') ){
			return;
		}

		if(
			($wpzoom_settings = get_option( 'wpzoom-instagram-widget-settings', wpzoom_instagram_get_default_settings() )) &&
			isset($wpzoom_settings['basic-access-token']) &&
			! empty($wpzoom_settings['basic-access-token'])
		){
			return $wpzoom_settings['basic-access-token'];
		}

		// Check for feeds from V2
		if( ($wpzoom_v2_feeds = $this->v2_get_feeds()) && is_array($wpzoom_v2_feeds) && isset($wpzoom_v2_feeds[0]) ){

			$wpzoom_v2_user_id = get_post_meta($wpzoom_v2_feeds[0]->ID, '_wpz-insta_user-id', true);

			if( $wpzoom_v2_user_id && ($wpzoom_v2_user_account_token = get_post_meta( $wpzoom_v2_user_id, '_wpz-insta_token', true )) ){
				return $wpzoom_v2_user_account_token;
			}

		}

	}

	public function ajax_migrate_token(){

		if ( ! check_ajax_referer( 'reycore-ajax-verification', 'security', false ) ) {
			wp_send_json_error( 'invalid_nonce' );
		}

		if( ! current_user_can('administrator') ){
			wp_send_json_error( 'Not allowed.' );
		}

		if( ! ($wpzoom_token = $this->get_zoom_access_token()) ){
			wp_send_json_error( 'Missing Token.' );
		}

		if( ! function_exists('update_field') ){
			wp_send_json_error( 'ACF not installed.' );
		}

		if( ! update_field( IgTokenManager::OPTION_KEY, $wpzoom_token, REY_CORE_THEME_NAME ) ){
			wp_send_json_error( 'Could not copy the key.' );
		}

		// Deactivate plugin
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		deactivate_plugins( 'instagram-widget-by-wpzoom/instagram-widget-by-wpzoom.php' );

		wp_send_json_success();
	}
}
