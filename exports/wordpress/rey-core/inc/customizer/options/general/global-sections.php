<?php
namespace ReyCore\Customizer\Options\General;

if ( ! defined( 'ABSPATH' ) ) exit;

use \ReyCore\Customizer\Controls;

class GlobalSections extends \ReyCore\Customizer\SectionsBase {

	public static function get_id(){
		return 'general-global-sections';
	}

	public function get_title(){
		return esc_html__('Site Global Sections', 'rey-core');
	}

	public function get_priority(){
		return 30;
	}

	public function get_icon(){
		return 'site-global-sections';
	}

	public function help_link(){
		return reycore__support_url('kb/customizer-general-settings/#global-sections');
	}

	public function can_load(){
		return class_exists('\Elementor\Plugin');
	}

	public function controls(){

		$this->add_control( [
			'type'        => 'repeater',
			'settings'    => 'global_sections',
			'label'       => esc_html__('Assign Global Sections', 'rey-core'),
			'description' => sprintf(__('Assign Generic global sections throughout your website. You can create as many as you want in <a href="%s">Global Sections</a> page.', 'rey-core'), admin_url('edit.php?post_type=rey-global-sections') ),
			'row_label' => array(
				'type' => 'text',
				'value' => esc_html__('Global Section', 'rey-core'),
			),
			'button_label' => esc_html__('New section', 'rey-core'),
			'default'      => [],
			'fields' => array(
				'id' => array(
					'type'        => 'select',
					'label'       => esc_html__('Generic Sections', 'rey-core'),
					'choices'     => \ReyCore\Customizer\Helper::global_sections('generic', ['' => '- Select -']),
					'export' => 'post_id'
				),
				'hook' => array(
					'type'        => 'select',
					'label'       => esc_html__('Position', 'rey-core'),
					'choices'     => array(
						'' => esc_html__('- Select -', 'rey-core'),
						'before_site_wrapper' => esc_html__('Before Site Wrapper', 'rey-core'),
						'before_header' => esc_html__('Before Header', 'rey-core'),
						'after_header' => esc_html__('After Header', 'rey-core'),
						'before_site_container' => esc_html__('Before Site Container', 'rey-core'),
						'after_site_container' => esc_html__('After Site Container', 'rey-core'),
						'before_footer' => esc_html__('Before Footer', 'rey-core'),
						'after_footer' => esc_html__('After Footer', 'rey-core'),
						'after_site_wrapper' => esc_html__('After Site Wrapper', 'rey-core'),
						'wp_body_open' => esc_html__('Body Start (wp_body_open)', 'rey-core'),
						'wp_footer' => esc_html__('Body End (wp_footer)', 'rey-core'),
					),
				),
				// TODO: Conditionals #52
			),
		] );

	}
}
