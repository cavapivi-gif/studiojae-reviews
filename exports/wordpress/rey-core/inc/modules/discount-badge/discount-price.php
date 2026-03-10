<?php
namespace ReyCore\Modules\DiscountBadge;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class DiscountPrice extends \ReyCore\WooCommerce\LoopComponents\Component {

	public function status(){
		return Base::loop_price_enabled();
	}

	public function get_id(){
		return 'discount-price';
	}

	public function get_name(){
		return 'Discount in Price';
	}

	public function scheme(){
		return [
			'type'          => 'filter',
			'tag'           => 'woocommerce_get_price_html',
			'callback'      => [$this, 'render_inside_price'],
			'priority'      => 10,
			'accepted_args' => 2
		];
	}

	/**
	 * Item Component - Discount label in product price
	 *
	 * @since 1.0.0
	 */
	public function render_inside_price( $html, $product )
	{
		if( ! $this->maybe_render() ){
			return $html;
		}

		if( ! $product ){
			$product = reycore_wc__get_product();
		}

		if( ! ($product && apply_filters('reycore/woocommerce/discounts/check', ($product->is_on_sale() || $product->is_type( 'grouped' )), $product) ) ){
			return $html;
		}

		$should_add = (! is_product() || in_array( wc_get_loop_prop('name'), ['upsells', 'up-sells', 'crosssells', 'cross-sells', 'related', 'product_grid_element'] ));

		if( $should_add ){

			if ( ($label_pos = reycore_wc__get_setting('loop_discount_label')) && $label_pos == 'price'  ) {
				return $html . Base::get_discount_output();
			}

		}

		return $html;
	}
}
