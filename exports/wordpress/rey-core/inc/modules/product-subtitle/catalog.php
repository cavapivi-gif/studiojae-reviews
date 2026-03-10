<?php
namespace ReyCore\Modules\ProductSubtitle;

if( ! class_exists('\ReyCore\WooCommerce\Loop') ){
	return;
}

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Catalog extends \ReyCore\WooCommerce\LoopComponents\Component {

	public function status(){
		return ! empty( Base::instance()->data['loop']['enabled'] );
	}

	public function get_id(){
		return 'product_subtitle';
	}

	public function get_name(){
		return 'Product Subtitle';
	}

	public function scheme(){

		$instance = Base::instance();

		if( ! $instance->data['loop']['enabled'] ){
			return;
		}

		$position = $instance->data['loop']['position'];

		$hooks = [
			'before_title' => [
				'hook'     => 'woocommerce_before_shop_loop_item_title',
				'priority' => 20,
			],
			'after_title' => [
				'hook'     => 'woocommerce_after_shop_loop_item_title',
				'priority' => 0,
			],
			'after_content' => [
				'hook'     => 'woocommerce_after_shop_loop_item',
				'priority' => 905,
			],
		];

		if( ! isset($hooks[ $position ]) ){
			return ;
		}

		return [
			'type'          => 'action',
			'tag'           => $hooks[ $position ]['hook'],
			'callback'      => [ $instance, 'render' ],
			'priority'      => $hooks[ $position ]['priority'],
		];

	}

}
