<?php
namespace ReyCore\Customizer\Options\General;

if ( ! defined( 'ABSPATH' ) ) exit;

use \ReyCore\Customizer\Controls;

class TitleTagline extends \ReyCore\Customizer\SectionsBase {

	public static function get_id(){
		return 'title_tagline';
	}

	public function get_title(){}

	public function get_icon(){
		return '';
	}

	public function help_link(){
		return reycore__support_url('kb/customizer-general-settings/#site-identity');
	}

	// public function get_title_before(){
	// 	return esc_html__('SITE SETTINGS', 'rey-core');
	// }

	public function controls(){}
}
