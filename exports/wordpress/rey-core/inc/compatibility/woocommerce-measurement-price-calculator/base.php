<?php
namespace ReyCore\Compatibility\WoocommerceMeasurementPriceCalculator;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{

	const ASSET_HANDLE = 'reycore-wc-measurement-price-calculator';

	public function __construct()
	{
		add_action( 'reycore/module/satc/before_markup', [ $this, 'sticky_bar_compatibility' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	function sticky_bar_compatibility(){
		reycore__remove_filters_for_anonymous_class( 'woocommerce_before_add_to_cart_button', 'WC_Price_Calculator_Product_Page', 'render_price_calculator', 5 );
	}

	public function enqueue_scripts(){
		if( is_singular('product') ){
			wp_enqueue_style( self::ASSET_HANDLE, self::get_path( basename( __DIR__ ) ) . '/style.css', [], REY_CORE_VERSION );
		}
	}

}
