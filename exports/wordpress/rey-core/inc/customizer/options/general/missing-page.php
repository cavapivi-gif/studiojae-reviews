<?php
namespace ReyCore\Customizer\Options\General;

if ( ! defined( 'ABSPATH' ) ) exit;

use \ReyCore\Customizer\Controls;

class MissingPage extends \ReyCore\Customizer\SectionsBase {

	public static function get_id(){
		return 'general-missing-page';
	}

	public function get_title(){
		return esc_html__('404 Page', 'rey-core');
	}

	public function get_priority(){
		return 70;
	}

	public function get_icon(){
		return '404-page';
	}

	public function get_separator(){
		return 'after';
	}

	public function can_load(){
		return class_exists('\Elementor\Plugin');
	}

	public function controls(){

		$this->add_control( array(
			'type'        => 'select',
			'settings'    => '404_gs',
			'label'       => esc_html__( 'Global Section', 'rey-core' ),
			'description' => esc_html__('Select a generic global section to override the 404 page.', 'rey-core'),
			'default'     => '',
			'choices'     => [
				'' => '- Select -'
			],
			'ajax_choices' => 'get_global_sections',
			'edit_preview' => true,
		));

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => '404_gs_stretch',
			'label'       => esc_html__( 'Stretch Content', 'rey-core' ),
			'default'     => false,
			'active_callback' => [
				[
					'setting'  => '404_gs',
					'operator' => '!=',
					'value'    => '',
				],
			],
		] );

	}
}
