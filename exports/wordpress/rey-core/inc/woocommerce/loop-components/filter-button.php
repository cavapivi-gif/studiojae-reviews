<?php
namespace ReyCore\WooCommerce\LoopComponents;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class FilterButton extends Component {

	public function status(){
		return reycore_wc__check_filter_btn() !== false;
	}

	public function get_id(){
		return 'filter_button';
	}

	public function get_name(){
		return 'Filter Button';
	}

	public function loop_type(){
		return 'grid';
	}

	public function scheme(){

		return [
			'type'          => 'action',
			'tag'           => 'reycore/woocommerce/loop/before_grid',
			'priority'      => 35,
		];

	}

	public function render(){

		if( ! wc_get_loop_prop( 'is_paginated' ) ){
			return;
		}

		if( ! $this->maybe_render() ){
			return;
		}

		reycore__get_template_part('template-parts/woocommerce/filter-panel-button');
		reycore_assets()->add_scripts(['reycore-wc-loop-filter-count']);
	}

}
