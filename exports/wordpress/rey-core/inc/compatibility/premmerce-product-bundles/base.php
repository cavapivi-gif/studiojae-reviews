<?php
namespace ReyCore\Compatibility\PremmerceProductBundles;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{

	public function __construct()
	{
		add_action('wp', [$this, 'reposition'], 10);
	}

	public function reposition()
	{
		if( ! isset($GLOBALS['premmerce_bundles_frontend']) ){
			return;
		}

		if ('default' === get_option('premmerce_product_bundles_position', 'default')) {
			remove_action('woocommerce_after_single_product_summary', [$GLOBALS['premmerce_bundles_frontend'], 'renderProductBundles'], 1);
			add_action('woocommerce_after_single_product_summary', [$GLOBALS['premmerce_bundles_frontend'], 'renderProductBundles'], 3);
		}

	}

}
