<?php
namespace ReyCore;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Misc {

	public function __construct(){
		add_filter('pre_option_wc_feature_woocommerce_brands_enabled', [$this, 'disable_wc_brands' ], 0);
		add_action('wp_loaded', [$this , 'prevent_wc_login_reg_process'], 0);
		add_action( 'plugins_loaded', [$this, 'revslider_fix_import_notices_php819'], 0);
		add_action('reycore/manager_base/change_item_status', [$this, 'handle_change_item_status'], 10, 3);
		$this->wp_rocket();
	}

	/**
	 * Handle Brands module status change.
	 * This is used to enable/disable the WooCommerce Brands feature based on the module status
	 * @since 3.1.6
	 */
	public function handle_change_item_status( $manager, $item_id, $status ){

		if ( $manager->get_id() !== 'modules' ) {
			return;
		}

		if( $item_id !== 'brands' ){
			return;
		}

		update_option('wc_feature_woocommerce_brands_enabled', $status === true ? 'no' : 'yes');
	}

	/**
	 * Disable WooCommerce Brands module
	 * @since 3.1.6
	 */
	public function disable_wc_brands( $value ) {

		static $maybe_use_wc_brands;

		if( is_null($maybe_use_wc_brands) ){
			$maybe_use_wc_brands = ($disabled_modules = get_option('reycore-disabled-modules')) && in_array('brands', (array) $disabled_modules);
		}

		if( $maybe_use_wc_brands || (defined('REY_USE_WC_BRANDS') && REY_USE_WC_BRANDS) ){
			return 'yes';
		}

		return 'no';
	}

	/**
	 * Helps Ajax Login from Rey.
	 * Added here to be loaded before plugins_loaded hook.
	 * @since 1.7.0
	 */
	public function prevent_wc_login_reg_process(){

		if(
			wp_doing_ajax() &&
			isset( $_REQUEST[\ReyCore\Ajax::ACTION_KEY] ) &&
			$_REQUEST[\ReyCore\Ajax::ACTION_KEY] === 'account_forms'
		){
			remove_action( 'wp_loaded', ['WC_Form_Handler', 'process_login'], 20 );
			remove_action( 'wp_loaded', ['WC_Form_Handler', 'process_registration'], 20 );
			remove_action( 'wp_loaded', ['WC_Form_Handler', 'process_lost_password'], 20 );
		}

	}

	public function wp_rocket(){

		// disable cache for empty cart because Rey delays it already
		add_filter( 'rocket_cache_wc_empty_cart', '__return_false' );

	}

	private static function get_json_data($fname){

		$data = [];

		$data_json_file = sprintf( '%sinc/libs/sample/%s.json', REY_CORE_DIR, $fname);

		$wp_filesystem = reycore__wp_filesystem();

		if(	$wp_filesystem->is_file( $data_json_file ) ) {
			if( $json_raw = $wp_filesystem->get_contents( $data_json_file ) ){
				$data = json_decode($json_raw, true );
			}
		}

		if( empty($data) ){
			return $data;
		}

		return reycore__clean($data);
	}

	function revslider_fix_import_notices_php819(){

		if( ! class_exists('\RevSliderPluginUpdate') ){
			return;
		}

		if( ! (isset($_REQUEST['reycore-ajax']) && 'run_import' === $_REQUEST['reycore-ajax']) ){
			return;
		}

		remove_action('plugins_loaded', ['RevSliderPluginUpdate', 'do_update_checks']);
	}

}

new Misc();
