<?php
namespace ReyCore\Customizer\Options;

if ( ! defined( 'ABSPATH' ) ) exit;

class Footer extends \ReyCore\Customizer\OptionsBase {

	public static function get_id(){
		return 'footer';
	}

	public function get_title(){
		return esc_html__('Footer', 'rey-core');
	}

	public function get_priority(){
		return 3;
	}

	public function get_icon(){
		return 'footer';
	}

	public function get_default_sections(){

		return [
			'general',
		];

	}

}
