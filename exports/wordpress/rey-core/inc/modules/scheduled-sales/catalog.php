<?php
namespace ReyCore\Modules\ScheduledSales;

if( ! class_exists('\ReyCore\WooCommerce\Loop') ){
	return;
}

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Catalog extends \ReyCore\WooCommerce\LoopComponents\Component {

	public function status(){
		return ! empty( Base::instance()->data['loop']['type'] );
	}

	public function get_id(){
		return 'scheduled_sale';
	}

	public function get_name(){
		return 'Scheduled Sale Badge';
	}

	public function scheme(){

		$instance = Base::instance();
		$position = $instance->data['loop']['position'];

		$hooks = [
			'top_left' => [
				'hook'     => 'reycore/loop_inside_thumbnail/top-left',
				'priority' => 10,
			],
			'top_right' => [
				'hook'     => 'reycore/loop_inside_thumbnail/top-right',
				'priority' => 10,
			],
			'bottom_left' => [
				'hook'     => 'reycore/loop_inside_thumbnail/bottom-left',
				'priority' => 10,
			],
			'bottom_right' => [
				'hook'     => 'reycore/loop_inside_thumbnail/bottom-right',
				'priority' => 10,
			],
			'before_title' => [
				'hook'     => 'woocommerce_before_shop_loop_item_title',
				'priority' => 20,
			],
			'after_title' => [
				'hook'     => 'woocommerce_after_shop_loop_item_title',
				'priority' => 10,
			],
			'after_content' => [
				'hook'     => 'woocommerce_after_shop_loop_item',
				'priority' => 905,
			],
		];

		if( ! isset($hooks[ $position ]) ){
			return ;
		}

		// countdown is not fitting inside the thumb badge slots
		if( $instance->data['loop']['type'] === 'countdown' ){
			$hooks['top_left']     = $hooks['before_title'];
			$hooks['top_right']    = $hooks['before_title'];
			$hooks['bottom_left']  = $hooks['before_title'];
			$hooks['bottom_right'] = $hooks['before_title'];
		}

		return [
			'type'          => 'action',
			'tag'           => $hooks[ $position ]['hook'],
			'callback'      => [ $instance, 'render' ],
			'priority'      => $hooks[ $position ]['priority'],
		];

	}

}
