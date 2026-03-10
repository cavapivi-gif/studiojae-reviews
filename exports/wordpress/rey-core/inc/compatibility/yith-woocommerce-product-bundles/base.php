<?php
namespace ReyCore\Compatibility\YithWoocommerceProductBundles;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{
	private $settings = [];

	const ASSET_HANDLE = 'reycore-yith-pb';

	public function __construct()
	{
		add_filter('reycore/module/extra-variation-images/maybe_surpress_filter', [$this, 'surpress_filters_extra_variation_images'], 20);
	}

	function surpress_filters_extra_variation_images( $stat ){

		if( ! get_theme_mod('enable_extra_variation_images', false) ){
			return $stat;
		}

		$product = wc_get_product();

		if( ! $product ){
			global $product;
		}

		if( ! $product ){
			return $stat;
		}

		if( $product->get_type() === 'yith_bundle' ){
			return true;
		}

		return $stat;
	}

}
