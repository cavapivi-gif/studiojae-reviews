<?php
namespace ReyCore\Compatibility\WcFrontendManagerElementor;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{
	public function __construct()
	{
		add_filter( 'reycore/ajaxfilters/js_params', [$this, 'filter_params'], 20);
		add_filter( 'reycore/load_more_pagination_args', [$this, 'pagination'], 20);
	}

	function filter_params($params){

		if( isset($params['shop_loop_container']) ){
			$params['shop_loop_container'] .= ', div[data-elementor-type="wcfmem-store"] .reyajfilter-before-products';
		}

		if( isset($params['not_found_container']) ){
			$params['not_found_container'] .= ', div[data-elementor-type="wcfmem-store"] .reyajfilter-before-products';
		}

		return $params;
	}

	function pagination ($params){

		if( isset($params['target']) ){
			$params['target'] = $params['target'] . ', div[data-elementor-type="wcfmem-store"] ul.products';
		}

		return $params;
	}
}
