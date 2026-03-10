<?php
namespace ReyCore\Compatibility\TiWishlist;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class CompTopRight extends \ReyCore\WooCommerce\LoopComponents\Component {

	public function status(){
		return \ReyCore\WooCommerce\Tags\Wishlist::is_enabled() && $this->group_default();
	}

	public function get_id(){
		return 'wishlist-topright';
	}

	public function get_name(){
		return 'Wishlist - Top Right';
	}

	public function get_group(){
		return 'wishlist';
	}

	public function group_default(){
		return \ReyCore\WooCommerce\Tags\Wishlist::catalog_default_position() === 'topright';
	}

	public function scheme(){
		return [
			'type'          => 'action',
			'tag'           => 'reycore/loop_inside_thumbnail/top-right',
			'priority'      => 10,
		];
	}

	public function render(){
		\ReyCore\WooCommerce\Tags\Wishlist::get_button_html();
	}

}
