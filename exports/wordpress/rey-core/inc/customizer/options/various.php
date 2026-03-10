<?php
namespace ReyCore\Customizer\Options;

if ( ! defined( 'ABSPATH' ) ) exit;

class Various extends \ReyCore\Customizer\OptionsBase {

	public static function get_id(){
		return 'various';
	}

	public function get_title(){
		return 'Various Settings';
	}

	public function prevent_creating_panel(){
		return true;
	}

	public function get_default_sections(){
		return [
			'cei-section',
		];
	}

}
