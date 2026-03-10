<?php
namespace ReyCore\Compatibility\WoocommerceSocialLogin;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{

	public function __construct()
	{
		add_action('init', [$this, 'init']);
	}

	function init(){

		if( apply_filters('reycore/compatibility/woocommerce_social_login/disable', false) ){
			return;
		}

		remove_action( 'woocommerce_login_form_end', [ wc_social_login()->get_frontend_instance(), 'render_social_login_buttons' ] );
		add_action('woocommerce_after_customer_login_form', function(){
			echo do_shortcode('[woocommerce_social_login_buttons]');
		});
	}
}
