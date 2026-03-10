<?php
namespace ReyCore\Compatibility\LoginRecaptcha;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{

	/**
	 * Plugin Name: Login No Captcha reCAPTCHA (Google)
	 * Plugin URI: https://wordpress.org/plugins/login-recaptcha/
	 */

	public function __construct()
	{
		add_action('woocommerce_after_customer_login_form', function(){

			wp_enqueue_script('login_nocaptcha_google_api');
			wp_enqueue_style('login_nocaptcha_css');

		}, 10);
	}

}
