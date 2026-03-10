<?php
namespace ReyCore\Compatibility\PointsAndRewardsForWoocommerce;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{

	public $settings = [];

	public function __construct()
	{
		add_action( 'init', [ $this, 'init' ] );
	}

	public function init(){

		$this->set_settings();

		add_action( 'wp_enqueue_scripts', [ $this, 'load_styles' ] );
	}

	public function set_settings(){
		$this->settings = apply_filters('reycore/compatibility/points_and_rewards_wc/settings', [
		]);
	}


	public function load_styles(){

		if( ! (is_cart() || is_checkout()) ){
			return;
		}

		wp_enqueue_style( 'reycore-parw-styles', self::get_path( basename( __DIR__ ) ) . '/style.css', [], REY_CORE_VERSION );
	}

}
