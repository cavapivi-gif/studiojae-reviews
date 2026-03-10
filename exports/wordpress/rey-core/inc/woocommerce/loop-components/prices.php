<?php
namespace ReyCore\WooCommerce\LoopComponents;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Prices extends Component {

	public function init(){
		remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);
	}

	public function status(){
		return get_theme_mod('loop_show_prices', '1') == '1';
	}

	public function get_id(){
		return 'prices';
	}

	public function get_name(){
		return 'Prices';
	}

	public function scheme(){

		return [
			'type'          => 'action',
			'tag'           => 'woocommerce_after_shop_loop_item_title',
			'priority'      => 10,
		];

	}

	public function render(){

		if( ! $this->maybe_render() ){
			return;
		}

		woocommerce_template_loop_price();
	}

}
