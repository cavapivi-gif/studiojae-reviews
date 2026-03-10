<?php
namespace ReyCore\Customizer\Options;

if ( ! defined( 'ABSPATH' ) ) exit;

class Woocommerce extends \ReyCore\Customizer\OptionsBase {

	public function __construct(){

		if( ! class_exists('\WooCommerce') ){
			return;
		}

		parent::__construct();

	}

	public static function get_id(){
		return 'woocommerce';
	}

	public function can_load(){
		return class_exists('\WooCommerce');
	}

	public function get_title(){
		return esc_html__('WooCommerce', 'rey-core');
	}

	public function get_icon(){
		return 'woocommerce';
	}

	public function get_priority(){
		return 40;
	}

	public function get_title_after(){
		return esc_html__('WORDPRESS SETTINGS', 'rey-core');
	}

	public function get_default_sections(){

		return [
			'catalog-grid',
			'catalog-grid-components',
			'catalog-product',
			'catalog-images',
			'catalog-misc',
			'cart',
			'checkout',
			'header-cart',
			'product-page-layout',
			'product-page-gallery',
			'product-page-summary-components',
			'product-page-components',
			'product-page-tabs',
			'advanced-settings',
			'search',
		];

	}

}
