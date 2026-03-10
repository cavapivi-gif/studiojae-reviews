<?php
namespace ReyCore\Compatibility\FluidCheckout;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase {

	public function __construct() {

		add_filter('reycore/woocommerce/checkout/supports_element', '__return_false', 100);
		add_filter('reycore/woocommerce/checkout/force_custom_layout', '__return_false', 100);
		add_filter('theme_mod_checkout_distraction_free', '__return_false', 100);
		add_filter('theme_mod_cart_checkout_bar_process', '__return_false', 100);
		add_filter('theme_mod_checkout_add_thumbs', '__return_false', 100);
		add_action('reycore/assets/register_scripts', [$this, 'remove_styling'], 30);
		add_action('woocommerce_checkout_order_review', [$this, 'remove_title'], -1);
		add_action('wp_enqueue_scripts', [ $this, 'load_styles' ] );

	}

	public function remove_styling( $manager ){
		$manager->deregister_asset('styles', 'rey-wc-checkout');
	}

	public function remove_title(){

		if( $checkout = \ReyCore\Plugin::instance()->woocommerce_tags[ 'checkout' ] ){
			remove_action( 'woocommerce_checkout_order_review', [$checkout,'checkout_add_title'], 0);
		}

	}

	public function load_styles(){

		if( ! is_checkout() ){
			return;
		}

		wp_enqueue_style( 'reycore-fc-styles', self::get_path( basename( __DIR__ ) ) . '/style.css', [], REY_CORE_VERSION );

	}
}
