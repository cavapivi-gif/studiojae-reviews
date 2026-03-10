<?php
namespace ReyCore\WooCommerce\LoopComponents;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class ResultCount extends Component {

	public function init(){
		remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
	}

	public function status(){
		return get_theme_mod('loop_product_count', true);
	}

	public function get_id(){
		return 'result_count';
	}

	public function get_name(){
		return 'Results count';
	}

	public function loop_type(){
		return 'grid';
	}

	public function scheme(){

		return [
			'type'          => 'action',
			'tag'           => 'reycore/woocommerce/loop/before_grid',
			'priority'      => 15,
		];

	}

	public function render(){

		if( ! wc_get_loop_prop( 'is_paginated' ) ){
			return;
		}

		if( ! $this->maybe_render() ){
			return;
		}

		woocommerce_result_count();
	}

}
