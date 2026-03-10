<?php
namespace ReyCore\Customizer\Options;

if ( ! defined( 'ABSPATH' ) ) exit;

class Cover extends \ReyCore\Customizer\OptionsBase {

	public static function get_id(){
		return 'cover';
	}

	public function get_title(){
		return esc_html_x('Page Cover', 'Customize label', 'rey-core');
	}

	public function get_description(){
		return esc_html_x('The page cover is the block between the Header and Page Content which is built entirely of Elementor global sections.', 'Customize label', 'rey-core');
	}

	public function get_priority(){
		return 30;
	}

	public function can_load(){
		return class_exists('\Elementor\Plugin');
	}

	public function get_icon(){
		return 'page-cover';
	}

	public function get_default_sections(){

		return [
			'blog',
			'frontpage',
			'page',
			'shop',
		];

	}

	public function get_title_after(){

		if( class_exists('\WooCommerce') ){
			return;
		}

		return esc_html__('WORDPRESS SETTINGS', 'rey-core');
	}

	/**
	 * Deprecated
	 *
	 * @return array
	 * @deprecated
	 */
	public static function get_global_sections(){

		return \ReyCore\Customizer\Helper::global_sections('cover', [
			'no'  => esc_attr__( 'Disabled', 'rey-core' )
		]);
	}

	public static function get_main_desc(){
		return sprintf('%s <a href="%s" target="blank">%s</a>.',
			esc_html__('To use or create more page cover layouts, head over to', 'rey-core'),
			admin_url('edit.php?post_type=rey-global-sections'),
			esc_html__('Global Sections', 'rey-core')
		);
	}

}
