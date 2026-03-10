<?php
namespace ReyCore\Modules\DiscountBadge;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class DiscountTop extends \ReyCore\WooCommerce\LoopComponents\Component {

	public function status(){
		return Base::loop_top_enabled();
	}

	public function get_id(){
		return 'discount-top';
	}

	public function get_name(){
		return 'Discount at Top';
	}

	public function scheme(){
		return apply_filters('reycore/woocommerce/discounts/top_hook', [
			'type'          => 'action',
			'tag'           => 'reycore/loop_inside_thumbnail/top-right',
			'priority'      => 10
		]);
	}

	/**
	 * Item Component - discount percentage or SALE label to product, top-right
	 *
	 * @since 1.0.0
	 */
	public function render(){

		if( ! $this->maybe_render() ){
			return;
		}

		global $product;

		if( ! $product ){
			$product = wc_get_product();
		}

		if( ! $product ){
			return;
		}

		if( ! apply_filters('reycore/woocommerce/discounts/check', ($product->is_on_sale() || $product->is_type( 'grouped' )), $product) ){
			return;
		}

		$should_add = (! is_product() || in_array( wc_get_loop_prop('name'), ['upsells', 'up-sells', 'crosssells', 'cross-sells', 'related'] ));

		if( ! ($should_add && reycore_wc__get_loop_component_status('prices')) ){
			return;
		}

		$label_pos = reycore_wc__get_setting('loop_discount_label');

		if( $label_pos === 'top' ){
			echo Base::get_discount_output();
		}

	}

}
