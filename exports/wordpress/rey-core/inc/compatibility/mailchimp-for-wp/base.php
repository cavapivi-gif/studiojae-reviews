<?php
namespace ReyCore\Compatibility\MailchimpForWp;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{
	const ASSET_HANDLE = 'reycore-mc4wp';

	public function __construct()
	{
		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
	}

	public function register_assets($assets){

		if( defined( 'MC4WP_PREMIUM_VERSION' ) ){
			return;
		}

		$assets->register_asset('scripts', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/script.js',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			],
		]);

	}

	function register_actions( $ajax_manager ) {
		$ajax_manager->register_ajax_action( 'submit_mc4wp', [$this, 'ajax_response__output'] );
	}

	function ajax_response__output(){

	}

}
