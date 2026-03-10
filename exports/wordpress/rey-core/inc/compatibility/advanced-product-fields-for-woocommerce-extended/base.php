<?php
namespace ReyCore\Compatibility\AdvancedProductFieldsForWoocommerceExtended;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{

	const ASSET_HANDLE = 'reycore-wapf';

	public function __construct()
	{
		add_action( 'wp_enqueue_scripts', [$this, 'load_scripts']);
		add_filter('reycore/woocommerce/product_mobile_gallery/slide_html', [$this, 'add_attr'], 10, 2);

	}

	public function add_attr( $html, $attachment_id ){
		return str_replace('<div class="splide__slide"','<div class="splide__slide" data-wapf-att-id="'.$attachment_id.'" ',$html);
	}

	public function load_scripts(){

		wp_register_script(
			self::ASSET_HANDLE,
			self::get_path( basename( __DIR__ ) ) . '/script.js',
			[],
			REY_CORE_VERSION,
			true
		);

		wp_localize_script( self::ASSET_HANDLE, 'reycore_wapf', apply_filters('reycore/compatibility/wapf', [
			'typing_timer' => 1000,
		] ) );

		wp_register_style(
			self::ASSET_HANDLE,
			self::get_path( basename( __DIR__ ) ) . '/style.css',
			[],
			REY_CORE_VERSION
		);

		if( is_product() ){
			wp_enqueue_script( self::ASSET_HANDLE );
			wp_enqueue_style( self::ASSET_HANDLE );
		}
	}

}
