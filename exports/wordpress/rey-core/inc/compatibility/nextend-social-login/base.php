<?php
namespace ReyCore\Compatibility\NextendSocialLogin;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{
	private $settings = [];

	public function __construct()
	{
		$this->add_settings();
		$this->add_hooks();
	}

	function add_hooks(){
		if( $this->settings['show_in_login'] ){
			add_action( 'woocommerce_login_form_start', [ $this, 'add_buttons_start' ] );
			add_action( 'woocommerce_login_form_end', [ $this, 'add_buttons_end' ] );
		}
		if( $this->settings['show_in_register'] ){
			add_action( 'woocommerce_register_form_start', [ $this, 'add_buttons_start' ] );
			add_action( 'woocommerce_register_form_end', [ $this, 'add_buttons_end' ] );
		}
		add_action( 'wp_enqueue_scripts', [ $this, 'load_styles' ] );
	}

	function load_styles(){
		wp_enqueue_style( 'reycore-nsl-styles', self::get_path( basename( __DIR__ ) ) . '/style.css', [], REY_CORE_VERSION );
	}

	function add_settings(){
		$this->settings = apply_filters('rey/nextend_social_login/settings', [
			'position' => 'after',
			'show_in_login' => true,
			'show_in_register' => true,
		] );
	}

	function add_buttons_start(){
		if( $this->settings['position'] === 'before' ){
			echo \NextendSocialLogin::renderButtonsWithContainer();
		}
	}

	function add_buttons_end(){
		if( $this->settings['position'] === 'after' ){
			echo \NextendSocialLogin::renderButtonsWithContainer();
		}
	}

}
