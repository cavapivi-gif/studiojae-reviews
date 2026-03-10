<?php
namespace ReyCore\Customizer;

if ( ! defined( 'ABSPATH' ) ) exit;

class KirkiSupport {

	public function __construct(){

		add_action( 'init', [$this, 'init']);
		add_filter( 'kirki_config', [$this, 'kirki_config']);
		add_filter( 'kirki_telemetry', '__return_false');

	}

	public function init() {

		if( class_exists('\Kirki') ){
			// Add Config for Kirki Settings
			\Kirki::add_config(\ReyCore\Customizer\Controls::CONFIG_KEY, [
				'capability'    => 'edit_theme_options',
				'option_type'   => 'theme_mod'
			]);
		}
	}

	public function kirki_config($config) {
		$config['disable_loader'] = true;
		return $config;
	}


}
