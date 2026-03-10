<?php
namespace ReyCore\Modules\ProductSubtitle;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Pdp
{
	public function __construct()
	{
		add_action('woocommerce_before_single_product', [$this, 'add']);
	}

	function add(){

		$instance = Base::instance();

		if( ! $instance->data['pdp']['enabled'] ){
			return;
		}

		$position = $instance->data['pdp']['position'];

		$hooks = [
			'before_title' => [
				'hook'     => 'woocommerce_single_product_summary',
				'priority' => 4,
			],
			'after_title' => [
				'hook'     => 'woocommerce_single_product_summary',
				'priority' => 6,
			],
			'after_price' => [
				'hook'     => 'woocommerce_single_product_summary',
				'priority' => 11,
			],
			'before_meta' => [
				'hook'     => 'reycore/woocommerce_product_meta/before',
				'priority' => 5,
			],
			'after_meta' => [
				'hook'     => 'reycore/woocommerce_product_meta/after',
				'priority' => 10,
			],
		];

		if( ! isset($hooks[ $position ]) ){
			return;
		}

		add_action($hooks[ $position ]['hook'], [ $instance, 'render' ], $hooks[ $position ]['priority']);

	}

}
