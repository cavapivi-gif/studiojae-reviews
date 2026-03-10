<?php
namespace ReyCore\Customizer\Options\Header;

if ( ! defined( 'ABSPATH' ) ) exit;

use \ReyCore\Customizer\Controls;

class Logo extends \ReyCore\Customizer\SectionsBase {

	public static function get_id(){
		return 'header-logo';
	}

	public function get_title(){
		return esc_html__('Logo', 'rey-core');
	}

	public function get_priority(){
		return 20;
	}

	public function get_icon(){
		return 'logo';
	}

	public function help_link(){
		return reycore__support_url('kb/customizer-header-settings/#logo');
	}

	public function customize_register(){

		global $wp_customize;

		$wp_customize->get_control( 'site_icon' )->priority = 50;
		$wp_customize->get_control( 'site_icon' )->section = self::get_id();
	}


	public function controls(){

		$this->add_control( [
			'type'        => 'dimensions',
			'settings'    => 'logo_sizes',
			'label'       => esc_html__( 'Logo size', 'rey-core' ),
			'default'     => [
				'max-width'  => '',
				'max-height' => '',
			],
			'choices'     => [
				'labels' => [
					'max-width'  => esc_html__( 'Width (eg 100px)', 'rey-core' ),
					'max-height' => esc_html__( 'Height (eg 50px)', 'rey-core' ),
				],
			],
			'output' => [
				[
					'element'  => ':root',
					'property'  => '--logo-',
					'units'    => 'px',
				],
			],
			'active_callback' => [
				[
					'setting'  => 'header_layout_type',
					'operator' => '==',
					'value'    => 'default',
				],
			],
		] );

		$this->add_control( [
			'type'        => 'image',
			'settings'    => 'logo_mobile',
			'label'       => esc_html__( 'Mobile Logo', 'rey-core' ),
			'description' => esc_html__( 'This logo will be shown on mobile devices.', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'save_as' => 'id',
			],
		] );

		$this->add_notice([
			'default'     => sprintf(__('<strong>Logo not changing?</strong> Please <a href="%s" target="_blank">follow this article</a> which explains why this is happening.', 'rey-core'), reycore__support_url('kb/logo-not-changing/')),
		] );


	}
}
