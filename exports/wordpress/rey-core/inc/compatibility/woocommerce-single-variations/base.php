<?php
namespace ReyCore\Compatibility\WoocommerceSingleVariations;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{
	public function __construct() {
		add_action('init', [$this, 'init']);
	}

	function init(){

		global $woocommerce_single_variations_options;

		if( ! (isset($woocommerce_single_variations_options['enable']) && $woocommerce_single_variations_options['enable']) ){
			return;
		}

		add_filter('reycore/ajaxfilters/post_types_count', [$this, 'filter_product_count_post_types']);
	}

	function filter_product_count_post_types( $types ){

		global $woocommerce_single_variations_options;

		if(
			isset($woocommerce_single_variations_options['hideParentProducts'] ) &&
			$woocommerce_single_variations_options['hideParentProducts']
		){
			if (($key = array_search('product', $types)) !== false) {
				unset($types[$key]);
			}
		}

		$types[] = 'product_variation';

		return $types;
	}
}
