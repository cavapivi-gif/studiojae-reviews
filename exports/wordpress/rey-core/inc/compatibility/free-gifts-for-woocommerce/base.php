<?php
namespace ReyCore\Compatibility\FreeGiftsForWoocommerce;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{


	public function __construct() {
		add_action('init', [$this, 'init']);
	}

	function init(){
		add_filter('reycore/woocommerce/cartpanel/show_qty', [$this, 'hide_qty'], 10, 2);
		add_action( 'woocommerce_before_mini_cart' , [$this , 'add_to_cart_automatic_gift_product_ajax'] ) ;
		add_action( 'woocommerce_before_mini_cart' , [$this , 'remove_gift_product_from_cart_ajax'] ) ;
	}

	function hide_qty( $status, $cart_item ){

		if( isset($cart_item['fgf_gift_product']) ){
			return false;
		}

		return $status;
	}

	function maybe_check_minicart(){
		return isset($_REQUEST['wc-ajax']) && in_array(reycore__clean($_REQUEST['wc-ajax']), ['remove_from_cart', 'get_refreshed_fragments', 'add_to_cart', 'reycore_ajax_add_to_cart', 'rey_update_minicart'], true);
	}

	function add_to_cart_automatic_gift_product_ajax(){

		if( ! $this->maybe_check_minicart() ){
			return;
		}

		\FGF_Gift_Products_Handler::automatic_gift_product( false ) ;
		\FGF_Gift_Products_Handler::bogo_gift_product( false ) ;
		\FGF_Gift_Products_Handler::coupon_gift_product( false ) ;
	}

	function remove_gift_product_from_cart_ajax() {

		if( ! $this->maybe_check_minicart() ){
			return;
		}

		\FGF_Gift_Products_Handler::remove_gift_products() ;
	}
}
