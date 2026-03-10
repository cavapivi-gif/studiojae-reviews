<?php
namespace ReyCore\WooCommerce\LoopComponents;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Title extends Component {

	public function init(){
		remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10);
	}

	public function get_id(){
		return 'title';
	}

	public function get_name(){
		return 'Title';
	}

	public function scheme(){
		return [
			'type'          => 'action',
			'tag'           => 'woocommerce_shop_loop_item_title',
			'priority'      => 10,
		];
	}

	public function render(){

		if( ! $this->maybe_render() ){
			return;
		}

		woocommerce_template_loop_product_title();
	}
}
