<?php
namespace ReyCore\Modules\ProductsPerPage;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class LoopComponent extends \ReyCore\WooCommerce\LoopComponents\Component {

	public function status(){
		return Base::instance()->is_enabled();
	}

	public function get_id(){
		return 'ppp_selector';
	}

	public function get_name(){
		return 'Products Per Page';
	}

	public function loop_type(){
		return 'grid';
	}

	public function scheme(){

		return [
			'type'          => 'action',
			'tag'           => 'reycore/woocommerce/loop/before_grid',
			'priority'      => 29,
		];

	}

	function get_layout(){
		return get_theme_mod('loop_switcher_ppp_layout', 'inline');
	}

	public function render(){

		if( ! (wc_get_loop_prop( 'is_paginated' ) && wc_get_loop_prop( 'total_pages' )) ){
			return;
		}

		$settings = Base::instance()->get_settings();

		reycore__get_template_part('template-parts/woocommerce/ppp-selector-' . $this->get_layout(), false, false, [
			'label' => $settings['label'],
			'options' => $settings['options'],
			'selected' => $settings['selected'],
		]);

		reycore_assets()->add_styles(['rey-buttons', 'rey-wc-loop-inlinelist', Base::ASSET_HANDLE]);
		reycore_assets()->add_scripts(Base::ASSET_HANDLE);
	}

}
