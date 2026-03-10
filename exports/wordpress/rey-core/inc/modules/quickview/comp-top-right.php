<?php
namespace ReyCore\Modules\Quickview;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class CompTopRight extends \ReyCore\WooCommerce\LoopComponents\Component {

	public function status(){
		return reycore_wc__get_setting('loop_quickview') != '2' && $this->group_default();
	}

	public function get_id(){
		return 'quickview-topright';
	}

	public function get_name(){
		return 'Quickview - Top Right';
	}

	public function get_group(){
		return 'quickview';
	}

	public function group_default(){
		return reycore_wc__get_setting('loop_quickview_position') === 'topright';
	}

	public function scheme(){
		return [
			'type'          => 'action',
			'tag'           => 'reycore/loop_inside_thumbnail/top-right',
			'priority'      => 10,
		];
	}

	public function render(){
		do_action('reycore/woocommerce/loop/quickview_button');
	}

}
