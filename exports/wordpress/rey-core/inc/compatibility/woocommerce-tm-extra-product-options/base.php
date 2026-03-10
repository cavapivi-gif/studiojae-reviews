<?php
namespace ReyCore\Compatibility\WoocommerceTmExtraProductOptions;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{

	public function __construct()
	{
		add_action( 'wp_enqueue_scripts', [ $this, 'load_scripts' ] );
	}


	public function load_scripts(){
		// wp_enqueue_style( 'reycore-tm-epo-styles', self::get_path( basename( __DIR__ ) ) . '/style.css', [], REY_CORE_VERSION );
		wp_enqueue_script( 'reycore-tm-epo-scripts', self::get_path( basename( __DIR__ ) ) . '/script.js', ['jquery'], REY_CORE_VERSION, true );
	}
}
