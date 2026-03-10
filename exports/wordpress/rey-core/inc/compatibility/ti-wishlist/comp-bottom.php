<?php
namespace ReyCore\Compatibility\TiWishlist;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class CompBottom extends \ReyCore\WooCommerce\LoopComponents\Component {

	public function status(){

		return \ReyCore\WooCommerce\Tags\Wishlist::is_enabled() && $this->group_default();
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
		return \ReyCore\WooCommerce\Tags\Wishlist::catalog_default_position() === 'bottom';
	}

	public function scheme(){
		return [
			'type'          => 'action',
			'tag'           => 'woocommerce_after_shop_loop_item',
			'priority'      => 40,
		];
	}

	public function render(){
		\ReyCore\WooCommerce\Tags\Wishlist::get_button_html();
	}

}
