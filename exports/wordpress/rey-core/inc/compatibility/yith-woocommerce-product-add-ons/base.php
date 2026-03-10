<?php
namespace ReyCore\Compatibility\YithWoocommerceProductAddOns;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{

	public function __construct()
	{
		add_filter('yith_wccl_enable_handle_variation_gallery','__return_false');
		add_action( 'woocommerce_after_add_to_cart_button', [$this, 'force_is_single_input'] );

	}

	function force_is_single_input(){
		echo '<input type="hidden" name="yith_wapo_is_single" id="yith_wapo_is_single" value="1">';
	}

}
