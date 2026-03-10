<?php
namespace ReyCore\Compatibility\Pitchprint;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{
	const ASSET_HANDLE = 'reycore-pitchprint';

	public function __construct()
	{
		add_action( 'wp_enqueue_scripts', [$this, 'load_scripts']);
	}

	public function load_scripts(){
		wp_enqueue_script(
			self::ASSET_HANDLE,
			self::get_path( basename( __DIR__ ) ) . '/script.js',
			[],
			REY_CORE_VERSION,
			true
		);
		if( function_exists('is_checkout') && is_checkout() ){
			wp_enqueue_script( 'wc-cart-fragments' );
		}
	}


}
