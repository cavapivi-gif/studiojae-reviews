<?php
namespace ReyCore\Compatibility\GermanMarket;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{
	public function __construct()
	{
		add_action('init', [$this, 'init']);
	}

	function init(){
		add_filter( 'theme_mod_loop_show_prices', [$this,'disable_loop_prices'], 10 );

		if ( get_option( 'gm_deactivate_checkout_hooks', 'off' ) == 'off' ) {
			update_option( 'gm_deactivate_checkout_hooks', 'on' );
		}

	}

	function disable_loop_prices($status){
		return '2';
	}

	function load_styles(){
		wp_enqueue_style( 'rey-compat-german-market', self::get_path( basename( __DIR__ ) ) . '/style.css', [], REY_CORE_VERSION );
	}

}
