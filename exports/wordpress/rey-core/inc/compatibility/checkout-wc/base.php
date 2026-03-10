<?php
namespace ReyCore\Compatibility\CheckoutWc;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase {

	public function __construct() {
		add_action('reycore/assets/enqueue', [ $this, 'remove_styles' ] );
		add_filter('reycore/checkout/form_row_styles', [ $this, 'form_row_styles' ] );
	}

	public function remove_styles($manager){

		if( ! self::is_checkout() ){
			return;
		}

		$manager->remove_styles(['rey-wc-forms']);
	}

	public function form_row_styles($status){

		if( ! self::is_checkout() ){
			return $status;
		}

		return false;
	}

	public static function is_checkout(){
		return function_exists('is_checkout') && is_checkout();
	}

}
