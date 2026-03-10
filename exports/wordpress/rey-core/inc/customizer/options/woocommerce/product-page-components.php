<?php
namespace ReyCore\Customizer\Options\Woocommerce;

if ( ! defined( 'ABSPATH' ) ) exit;

use \ReyCore\Customizer\Controls;

class ProductPageComponents extends \ReyCore\Customizer\SectionsBase {

	public static function get_id(){
		return 'woo-product-page-components';
	}

	public function get_title(){
		return esc_html__('Components in Page', 'rey-core');
	}

	public function get_priority(){
		return 80;
	}

	public function get_icon(){
		return 'woo-pdp-components-in-page';
	}

	public function get_breadcrumbs(){
		return ['WooCommerce', 'Product Page'];
	}

	public function help_link(){
		return reycore__support_url('kb/customizer-woocommerce/#product-page-components');
	}

	public function controls(){

	}
}
