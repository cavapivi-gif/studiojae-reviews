<?php
namespace ReyCore\Modules\RequestQuote;

if( ! class_exists('\ReyCore\WooCommerce\Loop') ){
	return;
}

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Catalog extends \ReyCore\WooCommerce\LoopComponents\Component {

	public function status(){
		return get_theme_mod('request_quote__catalog', false);
	}

	public function get_id(){
		return 'request_quote';
	}

	public function get_name(){
		return 'Request a quote';
	}

	public function scheme(){
		return [
			'type'          => 'action',
			'tag'           => 'woocommerce_after_shop_loop_item',
			'priority'      => 905,
		];
	}

	public function render(){
		Base::instance()->catalog_render();
	}

}
