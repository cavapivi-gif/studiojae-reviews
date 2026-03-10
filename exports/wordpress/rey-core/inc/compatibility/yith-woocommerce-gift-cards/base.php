<?php
namespace ReyCore\Compatibility\YithWoocommerceGiftCards;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{
	private $settings = [];

	const ASSET_HANDLE = 'reycore-yithgiftcards-styles';

	public function __construct()
	{
		add_action( 'init', [ $this, 'init' ] );
		add_action( 'reycore/assets/register_scripts', [ $this, 'register_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_filter( 'theme_mod_single_skin', [$this, 'disable_fullscreen_pdp_skin']);
		// add_filter( 'theme_mod_product_page_summary_fixed', [$this, 'disable_fullscreen_summary']);
		add_filter( 'yith_woocommerce_gift_cards_amount_range', [$this, 'fix_range_dash']);
		add_action( 'yith_gift_cards_template_after_gift_card_form', [$this, 'wrap_qty_start'], 19);
		add_action( 'yith_gift_cards_template_after_gift_card_form', [$this, 'wrap_qty_end'], 21);
		add_filter( 'rey/main_script_params', [$this, 'script_params'], 20);

	}

	public function init(){
		$this->settings = apply_filters('reycore/yith_gift_cards/params', [
		]);

	}

	public function enqueue_scripts(){
		reycore_assets()->add_styles(self::ASSET_HANDLE);
	}

	public function register_scripts($assets){

		$assets->register_asset('styles', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/style.css',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			]
		]);

	}

	public function is_gift_cart_product(){

		$product = wc_get_product();

		if( ! $product ){
			global $product;
		}

		if( ! is_object($product) ){
			return false;
		}

		return $product->get_type() === 'gift-card';
	}

	function disable_mobile_gallery( $status ){

		if( $this->is_gift_cart_product() ){
			return false;
		}

		return $status;
	}

	function disable_fullscreen_pdp_skin( $skin ){

		if( $this->is_gift_cart_product() ){
			if( $skin === 'fullscreen' ){
				return 'default';
			}
		}

		return $skin;
	}

	function disable_qty_style($style){

		if( $this->is_gift_cart_product() ){
			return 'default';
		}

		return $style;
	}

	function fix_range_dash($price){
		return str_replace('&ndash;', '', $price);
	}

	function script_params($params){
		$params['check_for_empty'][] = '.gift-card-content-editor.step-content';
		return $params;
	}

	function wrap_qty_start(){
		add_filter( 'theme_mod_single_atc_qty_controls_styles', [$this, 'disable_qty_style']);
		add_filter( 'reycore/woocommerce/wrap_quantity', '__return_true');
		do_action( 'reycore/woocommerce/quantity/wrap_start' );
	}

	function wrap_qty_end(){
		do_action( 'reycore/woocommerce/quantity/wrap_end' );
		remove_filter( 'reycore/woocommerce/wrap_quantity', '__return_true');
		remove_filter( 'theme_mod_single_atc_qty_controls_styles', [$this, 'disable_qty_style']);
	}

}
