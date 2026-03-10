<?php
namespace ReyCore\Compatibility\IconicWooShowSingleVariations;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{

	public function __construct() {
		add_action('init', [$this, 'init']);
	}

	function init(){
		add_filter('reycore/ajaxfilters/post_types_count', [$this, 'filter_product_count_post_types']);
		add_filter('jck_wssv_add_to_cart_button_class', [$this, 'add_to_cart_button_class']);
	}

	function filter_product_count_post_types( $types ){
		$types[] = 'product_variation';
		return $types;
	}

	function add_to_cart_button_class( $class ){

		if( $atc = reycore_wc__get_loop_component('add_to_cart') ){
			return $atc::add_to_cart_classes([
				'class' => $class
			]);
		}

		return $class;
	}
}
