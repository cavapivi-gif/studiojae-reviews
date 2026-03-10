<?php
namespace ReyCore\Modules\Wishlist;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class CompBottom extends \ReyCore\WooCommerce\LoopComponents\Component {

	public function status(){
		return Base::instance()->is_enabled() && $this->group_default();
	}

	public function get_id(){
		return 'wishlist-bottom';
	}

	public function get_name(){
		return 'Wishlist - Bottom';
	}

	public function get_group(){
		return 'wishlist';
	}

	public function group_default(){
		return reycore_wc__get_setting('loop_wishlist_position') === 'bottom';
	}

	public function scheme(){
		return [
			'type'          => 'action',
			'tag'           => 'woocommerce_after_shop_loop_item',
			'priority'      => 40,
		];
	}

	public function render(){

		if( ! $this->maybe_render() ){
			return;
		}

		do_action('reycore/woocommerce/loop/wishlist_button');
	}

}
