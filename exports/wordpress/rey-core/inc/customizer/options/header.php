<?php
namespace ReyCore\Customizer\Options;

if ( ! defined( 'ABSPATH' ) ) exit;

class Header extends \ReyCore\Customizer\OptionsBase {

	public static function get_id(){
		return 'header';
	}

	public function get_title(){
		return esc_html__('Header', 'rey-core');
	}

	public function get_priority(){
		return 2;
	}

	public function get_icon(){
		return 'header';
	}

	public function get_default_sections(){

		return [
			'general',
			'logo',
			'search',
			'navigation',
			'account',
			'cart',
			'cta',
		];

	}

}
