<?php
namespace ReyCore\Customizer\Options\General;

if ( ! defined( 'ABSPATH' ) ) exit;

use \ReyCore\Customizer\Controls;

class Preloader extends \ReyCore\Customizer\SectionsBase {

	public static function get_id(){
		return 'general-preloader';
	}

	public function get_title(){
		return esc_html__('Site Preloader', 'rey-core');
	}

	public function get_priority(){
		return 20;
	}

	public function get_icon(){
		return 'site-preloader';
	}

	public function help_link(){
		return reycore__support_url('kb/customizer-general-settings/#site-preloader');
	}

	public function controls(){

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'site_preloader',
			'label'       => esc_html__( 'Enable Site Preloader', 'rey-core' ),
			'default'     => false,
		] );

		$this->add_control( [
			'type'        => 'rey-color',
			'settings'    => 'preloader_color',
			'label'       => __( 'Color', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'alpha' => true,
			],
			'transport'   => 'auto',
			'output'      		=> [
				[
					'element'  		=> ':root',
					'property' 		=> '--preloader-color',
				],
			],
			'active_callback' => [
				[
					'setting'  => 'site_preloader',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$this->add_title('', [
			'description' => sprintf(__('Want more preloaders? Head over to <a href="%1$s" target="_blank">%2$s > Plugins Manager</a> and install & activate the <strong>Rey Module - Preloaders Pack</strong> plugin and you\'ll find new options here in this panel.', 'rey-core'), admin_url('admin.php?page=rey-install-required-plugins'), reycore__get_props('theme_title') ) . '<br><br>',
		]);


	}
}
