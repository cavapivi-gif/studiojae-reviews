<?php
namespace ReyCore\Modules\DiscountBadge;

use ReyCore\WooCommerce\Pdp as PdpBase;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class DiscountPdp extends \ReyCore\WooCommerce\PdpComponents\Component {

	public function __construct(){
		add_action( 'reycore/ajax/register_actions', [ $this, 'register_actions' ] );
	}

	public function init(){
		// add_filter( 'woocommerce_get_price_html', [ $this, 'add_ajax' ], 100, 2);
		add_action( 'woocommerce_single_product_summary', [$this, 'add'], 0);
		add_action( 'woocommerce_single_product_summary', [$this, 'remove'], 9999);
		add_action( 'wmc_get_products_price_ajax_handle_before', [$this, 'add']); // WOO MULTI CURRENCY
	}

	public function get_id(){
		return 'discount';
	}

	public function get_name(){
		return 'Discount';
	}

	public function add_ajax($html, $product = null){
		if( wp_doing_ajax() ){
			return $this->discount_percentage($html, $product);
		}
		return $html;
	}

	public function add(){
		add_filter( 'woocommerce_get_price_html', [ $this, 'discount_percentage' ], 100, 2);
		// add_filter( 'woocommerce_available_variation', [$this, 'variation_discount'], 100, 3);
	}

	public function remove(){
		remove_filter( 'woocommerce_get_price_html', [ $this, 'discount_percentage' ], 100);
		// remove_filter( 'woocommerce_available_variation', [$this, 'variation_discount'], 100, 3);
	}

	public function variation_discount($available_variation, $_product_variable, $variation){
		$available_variation['price_html'] = $this->discount_percentage($available_variation['price_html'], $variation);
		return $available_variation;
	}

	public function discount_percentage($html, $product = null){

		if( ! $product ){
			global $product;
		}

		if( ! $product ){
			$product = wc_get_product();
		}

		if( ! $product ){
			return $html;
		}

		if( ! $this->maybe_render() ){
			return $html;
		}

		$content = '';

		if (
			Base::pdp_enabled() && PdpBase::is_single_true_product() &&
			apply_filters('reycore/woocommerce/discounts/check', ($product->is_on_sale() || $product->is_type( 'grouped' )), $product)
		) {

			/**
			 * Hook: reycore/woocommerce/discounts/check
			 * How to check if it has a discount. In case you want to hide the badge for main variable product, in case
			 * there are *some* variations with discoutns, not all.
			 *
			 * add_filter('reycore/woocommerce/discounts/check', function($status, $product){
			 * 		return $product->get_sale_price();
			 * });
			 */

			$args = [
				'pdp' => $product,
			];

			$content .= Base::get_discount_output($args);

		}

		/* ------------------------------------ CUSTOM TEXT ------------------------------------ */

		if(
			PdpBase::is_single_true_product() && $product->get_type() !== 'grouped' &&
			($text_type = get_theme_mod('single_product_price_text_type', 'no')) && $text_type !== 'no' ){

			// simple custom text
			if( $text_type === 'custom_text' ){
				$content .= self::get_custom_text();
			}

			// "Free Shipping" based on current product price & cart totals
			elseif(
				$text_type === 'free_shipping' &&
				null !== WC()->cart &&
				wc_shipping_enabled()
			){

				reycore_assets()->add_scripts(['reycore-wc-product-page-general']);

				$content .= '<span data-price-text="' . $product->get_id() . '"></span>';

			}

		}

		return $html . $content;
	}

	public function register_actions( $ajax_manager ) {
		$ajax_manager->register_ajax_action( 'pdp_custom_price_text', [$this, 'ajax__pdp_custom_price_text'], [
			'auth'   => 3,
			'nonce'  => false,
		] );
	}

	public function ajax__pdp_custom_price_text( $data ){

		if( ! (isset($data['pid']) && ($pid = $data['pid'])) ){
			return;
		}

		if( ! ($product = wc_get_product($pid)) ){
			return;
		}

		if( ! ( ($product_price = $product->get_price()) && is_numeric($product_price) ) ){
			return;
		}

		$cart_subtotal = absint( WC()->cart->get_displayed_subtotal() );
		$minimum_order = absint( get_theme_mod('single_product_price_text_shipping_cost', 0) );

		if( absint($product_price) + $cart_subtotal > $minimum_order ){
			return self::get_custom_text();
		}

	}

	public static function get_custom_text($attribute = ''){
		return sprintf( '<span class="rey-priceText %2$s" %3$s>%1$s</span>',
			get_theme_mod('single_product_price_text_custom', esc_html__('Free Shipping!', 'rey-core')),
			get_theme_mod('single_product_price_text_inline', false) ? '--block' : '',
			$attribute
		);
	}

}
