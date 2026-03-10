<?php
namespace ReyCore\Compatibility\WcKalkulator;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{

	/**
	 * Product Fields, Addons and Price Calculator for WooCommerce
	 * https://wordpress.org/plugins/wc-kalkulator/
	 */

	public function __construct()
	{
		add_action('woocommerce_before_mini_cart', function(){
			add_filter('woocommerce_cart_product_subtotal', [$this, 'get_product_subtotal'], 10, 4);
		}, 10);
	}

	/**
	 * Get the product row subtotal.
	 *
	 * Gets the tax etc to avoid rounding issues.
	 *
	 * When on the checkout (review order), this will get the subtotal based on the customer's tax rate rather than the base rate.
	 *
	 * @param WC_Product $product Product object.
	 * @param int        $quantity Quantity being purchased.
	 * @return string formatted price
	 */
	public function get_product_subtotal( $product_subtotal, $product, $quantity, $cart ) {

		$price = $product->get_price();

		foreach ($cart->get_cart() as $key => $cart) {
            if (isset($cart['wckalkulator_price'])) {
				if( $cart['data']->get_id() === $product->get_id() ){
					$price = $cart['wckalkulator_price'];
				}
            }
        }

		if ( $product->is_taxable() ) {

			if ( $cart->display_prices_including_tax() ) {
				$row_price        = wc_get_price_including_tax( $product, array( 'qty' => $quantity ) );
				$product_subtotal = wc_price( $row_price );

				if ( ! wc_prices_include_tax() && $cart->get_subtotal_tax() > 0 ) {
					$product_subtotal .= ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>';
				}
			} else {
				$row_price        = wc_get_price_excluding_tax( $product, array( 'qty' => $quantity ) );
				$product_subtotal = wc_price( $row_price );

				if ( wc_prices_include_tax() && $cart->get_subtotal_tax() > 0 ) {
					$product_subtotal .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
				}
			}
		} else {
			$row_price        = $price * $quantity;
			$product_subtotal = wc_price( $row_price );
		}

		return $product_subtotal;
	}

}
