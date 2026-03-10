<?php
namespace ReyCore\Compatibility\PerfectWoocommerceBrands;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{

	const BRAND_TAX = 'pwb-brand';

	public function __construct()
	{
		add_filter( 'reycore/ajaxfilters/tax_reset_query', [$this, 'tax_reset']);
		add_filter( 'reycore/woocommerce/attributes_taxonomies', [$this, 'product_grid_attributes_list'], 20);
		add_filter( 'reycore/elementor/product_grid/query_args', [$this, 'product_grid_query_args'], 20, 2);
		add_filter( 'reycore/ajaxfilters/registered_taxonomies', [$this, 'register_tax_ajaxfilter']);
	}

	function tax_reset($items){
		$items[] = self::BRAND_TAX;
		return $items;
	}

	function register_tax_ajaxfilter($tax){

		$tax[] = [
			'id' => self::BRAND_TAX,
			'name' => 'Brand',
		];

		return $tax;
	}

	function product_grid_attributes_list($attributes){
		$attributes[self::BRAND_TAX] = esc_html__('Product Brand', 'rey-core');
		return $attributes;
	}

	function product_grid_query_args($query_args){

		if( isset($query_args['tax_query']) ){

			foreach ($query_args['tax_query'] as $key => $value) {

				if( isset($query_args['tax_query'][$key]['taxonomy']) && $query_args['tax_query'][$key]['taxonomy'] === wc_attribute_taxonomy_name( self::BRAND_TAX ) ){
					$query_args['tax_query'][$key]['taxonomy'] = self::BRAND_TAX;
				}
			}

		}

		return $query_args;
	}

}
