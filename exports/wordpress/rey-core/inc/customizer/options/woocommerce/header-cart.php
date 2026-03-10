<?php
namespace ReyCore\Customizer\Options\Woocommerce;

if ( ! defined( 'ABSPATH' ) ) exit;

use \ReyCore\Customizer\Controls;

class HeaderCart extends \ReyCore\Customizer\SectionsBase {

	public static function get_id(){
		return 'woo-header-cart';
	}

	public function get_title(){
		return esc_html__('Mini-Cart Panel (Header)', 'rey-core');
	}

	public function get_priority(){
		return 115;
	}

	public function get_icon(){
		return 'woo-mini-cart-panel';
	}

	public function controls(){

		/** WooCommerce placeholder section */

		$this->add_control( [
			'type'        => 'custom',
			'settings'    => 'header_cart_options_woo_placeholder',
			'default'     => '',
		] );

	}
}
