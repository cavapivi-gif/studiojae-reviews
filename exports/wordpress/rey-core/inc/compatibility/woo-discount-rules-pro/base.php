<?php
namespace ReyCore\Compatibility\WooDiscountRulesPro;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{
	public function __construct() {
		add_action('init', [$this, 'init']);
	}

	function init(){
		add_filter('reycore/woocommerce/cartpanel/show_qty', [$this, 'hide_qty'], 10, 2);
		add_filter('reycore/woocommerce/discount_labels/sale_price', [$this, 'sale_price'], 10, 2);
		add_action('advanced_woo_discount_rules_after_save_rule', [$this, 'clear_labels_transients']);
		add_filter('reycore/woocommerce/cache_discounts', '__return_false');
	}

	function hide_qty( $status, $cart_item ){

		if( isset($cart_item['wdr_free_product']) ){
			return false;
		}

		return $status;
	}

	function sale_price( $sale_price, $product ){

		if( ! class_exists('\Wdr\App\Controllers\Configuration') ){
			return $sale_price;
		}

		$calculate_discount_from = \Wdr\App\Controllers\Configuration::getInstance()->getConfig('calculate_discount_from', 'sale_price');

		if ($calculate_discount_from == 'regular_price') {
			$product_price = \Wdr\App\Helpers\Woocommerce::getProductRegularPrice($product);
		} else {
			$product_price = \Wdr\App\Helpers\Woocommerce::getProductPrice($product);
		}

		$calculated = apply_filters('advanced_woo_discount_rules_get_product_discount_price_from_custom_price', $product_price, $product, 1, $product_price, 'discounted_price', true, false);

		if( ! $calculated ){
			return $sale_price;
		}

		return $calculated;

	}

	function clear_labels_transients(){
		return \ReyCore\Helper::clean_db_transient( '_rey__discount_' );
	}

}
