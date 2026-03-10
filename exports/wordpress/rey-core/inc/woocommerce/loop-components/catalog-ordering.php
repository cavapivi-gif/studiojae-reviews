<?php
namespace ReyCore\WooCommerce\LoopComponents;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class CatalogOrdering extends Component {

	public function init(){
		remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
	}

	public function status(){
		return true;
	}

	public function get_id(){
		return 'catalog_ordering';
	}

	public function get_name(){
		return 'Catalog Ordering';
	}

	public function loop_type(){
		return 'grid';
	}

	public function scheme(){

		return [
			'type'          => 'action',
			'tag'           => 'reycore/woocommerce/loop/before_grid',
			'priority'      => 30,
		];

	}

	public function render(){

		if( ! wc_get_loop_prop( 'is_paginated' ) ){
			return;
		}

		/**
		 * Use hook below to disable sorting
		 * add_filter( 'reycore/woocommerce/loop/render/catalog_ordering', '__return_false', 20 );
		 */

		if( ! $this->maybe_render() ){
			return;
		}

		woocommerce_catalog_ordering();
	}

}
