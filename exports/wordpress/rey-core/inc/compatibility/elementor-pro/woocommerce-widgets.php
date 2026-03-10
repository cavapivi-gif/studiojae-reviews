<?php
namespace ReyCore\Compatibility\ElementorPro;

if ( ! defined( 'ABSPATH' ) ) exit;

class WoocommerceWidgets
{

	public function __construct()
	{

		if( ! class_exists('\WooCommerce') ){
			return;
		}

		add_action( 'elementor/frontend/widget/before_render', [$this, '_before_render'], 10);
		add_action( 'elementor/frontend/widget/after_render', [$this, '_after_render'], 10);

	}

	public function _before_render( $element ){

		$element_type = $element->get_unique_name();

		switch($element_type){
			case"woocommerce-product-price":
				$this->product_price__before();
				break;
		}
	}

	public function _after_render( $element ){

		$element_type = $element->get_unique_name();

		switch($element_type){
			case"woocommerce-product-price":
				$this->product_price__after();
				break;
		}
	}

	public function price_discount($html, $product = null){

		if( ($pdp = \ReyCore\Plugin::instance()->woocommerce_pdp) && $c = $pdp->get_component('discount') ){
			return $c->discount_percentage($html, $product);
		}

		return $html;
	}

	function product_price__before(){
		add_filter( 'woocommerce_get_price_html', [ $this, 'price_discount' ], 10, 2);
	}

	function product_price__after(){
		remove_filter( 'woocommerce_get_price_html', [ $this, 'price_discount' ], 10);
	}

}
