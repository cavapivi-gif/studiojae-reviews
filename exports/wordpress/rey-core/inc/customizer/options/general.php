<?php
namespace ReyCore\Customizer\Options;

if ( ! defined( 'ABSPATH' ) ) exit;

class General extends \ReyCore\Customizer\OptionsBase {

	public static function get_id(){
		return 'general';
	}

	public function get_title(){
		return esc_html__('General Settings', 'rey-core');
	}

	public function get_priority(){
		return 1;
	}

	public function get_title_before(){
		return esc_html__('THEME SETTINGS', 'rey-core');
	}

	public function get_icon(){
		return 'general-settings';
	}

	public function get_default_sections(){

		return [
			'layout',
			'style',
			'typography',
			'preloader',
			'performance',
			'global-sections',
			'sticky-global-sections',
			'missing-page',
			'search-page',
			'social-icons',
			'blog-page',
			'blog-post',
		];

	}

}
