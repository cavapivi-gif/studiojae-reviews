<?php
namespace ReyCore\Modules\Quickview;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class CompBottom extends \ReyCore\WooCommerce\LoopComponents\Component {

	public function status(){
		return reycore_wc__get_setting('loop_quickview') != '2' && $this->group_default();
	}

	public function get_id(){
		return 'quickview-bottom';
	}

	public function get_name(){
		return 'Quickview - Bottom';
	}

	public function get_group(){
		return 'quickview';
	}

	public function group_default(){
		return reycore_wc__get_setting('loop_quickview_position') === 'bottom';
	}

	public function scheme(){
		return [
			'type'          => 'action',
			'tag'           => 'woocommerce_after_shop_loop_item',
			'priority'      => 10,
		];
	}

	public function render(){
		do_action('reycore/woocommerce/loop/quickview_button');
	}

}
