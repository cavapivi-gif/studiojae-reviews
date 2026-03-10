<?php
namespace ReyCore\Modules\Wishlist;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class CompTopRight extends \ReyCore\WooCommerce\LoopComponents\Component {

	public function status(){
		return Base::instance()->is_enabled() && $this->group_default();
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
		return reycore_wc__get_setting('loop_wishlist_position') === 'topright';
	}

	public function scheme(){
		return [
			'type'          => 'action',
			'tag'           => 'reycore/loop_inside_thumbnail/top-right',
			'priority'      => 10,
		];
	}

	public function render(){

		if( ! $this->maybe_render() ){
			return;
		}

		do_action('reycore/woocommerce/loop/wishlist_button');
	}

}
