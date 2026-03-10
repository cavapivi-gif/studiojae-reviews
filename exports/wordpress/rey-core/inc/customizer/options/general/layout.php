<?php
namespace ReyCore\Customizer\Options\General;

if ( ! defined( 'ABSPATH' ) ) exit;

use \ReyCore\Customizer\Controls;

class Layout extends \ReyCore\Customizer\SectionsBase {

	public static function get_id(){
		return 'general-layout';
	}

	public function get_title(){
		return esc_html__('Site Layout', 'rey-core');
	}

	public function get_priority(){
		return 5;
	}

	public function get_icon(){
		return 'site-layout';
	}

	public function help_link(){
		return reycore__support_url('kb/customizer-general-settings/#layout-settings');
	}

	public function controls(){

		$this->add_title( esc_html__('Container Settings', 'rey-core'), [
			'separator' => 'none'
		]);

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'custom_container_width',
			'label'       => esc_html__( 'Containers Width (Desktop)', 'rey-core' ),
			'default'     => 'default',
			'choices'     => [
				'default'   => esc_html__( 'Default', 'rey-core' ),
				'full' => esc_html__( 'Full Width', 'rey-core' ),
				'px'  => esc_html__( 'Pixels (px)', 'rey-core' ),
				'vw' => esc_html__( 'Viewport (vw)', 'rey-core' ),
			],
			'css_class' => '--c-size-sm'
		] );

		$this->add_control( [
			'type'        		=> 'slider',
			'settings'    		=> 'container_width_vw',
			'label'       		=> esc_attr__( 'Container Width (vw)', 'rey-core' ),
			'default'     		=> 90,
			'choices'     		=> [
				'min'  => 50,
				'max'  => 99,
				'step' => 1,
			],
			'transport'   		=> 'auto',
			'active_callback' => [
				[
					'setting'  => 'custom_container_width',
					'operator' => '==',
					'value'    => 'vw',
				],
			],
		]);

		$this->add_control([
			'type'        		=> 'slider',
			'settings'    		=> 'container_width_px',
			'label'       		=> esc_attr__( 'Container Width (px)', 'rey-core' ),
			'default'     		=> 1440,
			'choices'     		=> [
				'min'  => 1025,
				'max'  => 2560,
				'step' => 10,
			],
			'transport'   		=> 'auto',
			'output'      		=> [
				[
					'element'  		=> ':root',
					'property' 		=> '--container-max-width',
					'units'    		=> 'px',
				]
			],
			'active_callback' => [
				[
					'setting'  => 'custom_container_width',
					'operator' => '==',
					'value'    => 'px',
				],
			],
		]);

		$this->add_control( [
			'type'        => 'rey-number',
			'settings'    => 'container_spacing',
			'label'       => esc_html__( 'Containers Horizontal Spacing', 'rey-core' ) . ' (px)',
			'help' => [
				sprintf(__( 'Adds a default container left and right side margin. It will be applied to the Site Container, Elementor Sections and Elementor Container elements. Default %s.', 'rey-core' ), '<strong>15px</strong>')
			],
			'transport'   		=> 'auto',
			'output'      		=> [
				[
					'element'  => ':root',
					'property' => '--rey-container-spacing',
					'units'    => 'px',
				],
				[
					'element'  => ':root',
					'property' => '--main-gutter-size',
					'units'    => 'px',
				],
			],
			'default'        => 15,
			// 'default_tablet' => '',
			// 'default_mobile' => '',
			'choices'     => [
				// 'min'  => 0,
				'max'  => 500,
				'step' => 1,
				'placeholder' => 15,
			],
			'responsive' => true
		] );


		$this->add_control( [
			'type'        => 'dimensions',
			'settings'    => 'content_padding',
			'label'       => esc_html__( 'Site-Container Vertical Spacing', 'rey-core' ),
			'help' => [
				__( 'Adds padding before and after the <strong>site-container</strong> only. Dont forget to include unit (eg: px, em, rem).', 'rey-core' )
			],
			'default'     => [
				'padding-top'    => '',
				'padding-bottom' => '',
			],
			'choices'     => [
				'labels' => [
					'padding-top'  => esc_html__( 'Top', 'rey-core' ),
					'padding-bottom'  => esc_html__( 'Bottom', 'rey-core' ),
				],
			],
			'transport'   		=> 'auto',
			'output'      		=> [
				[
					'element'  => ':root',
					'property' => '--content',
					'units'    => 'px',
				],
			],
			'input_attrs'     => [
				'padding-top'     => [
					'placeholder'    => '50px',
				],
				'padding-bottom'     => [
					'placeholder'    => '90px',
				],
			],
			'css_class' => 'dimensions-2-cols',
		] );

		$this->add_title( esc_html__('Site Settings', 'rey-core'));

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'wrapper_overflow',
			'label'       => esc_html__( 'Site Wrapper Overflow', 'rey-core' ),
			'help' => [
				__( 'Select the overflow type for the site wrapper block. This wrapper contains all elements of the site.', 'rey-core' )
			],
			'default'     => '',
			'choices'     => [
				''  => esc_html__( 'Unset (Hidden)', 'rey-core' ),
				'visible' => esc_html__( 'Visible', 'rey-core' ),
				'hidden'  => esc_html__( 'Hidden', 'rey-core' ),
			],
			'transport'   		=> 'auto',
			'output'      		=> [
				[
					'element'  => ':root',
					'property' => '--site-wrapper-overflow',
				],
			],
			'css_class' => '--c-size-sm',
		] );

		$this->add_control( [
			'type'        => 'dimensions',
			'settings'    => 'site_padding',
			'label' => esc_html__( 'Site Padding', 'rey-core' ) . ' (px)',
			'help' => [
				__( 'Will add padding around the site container (<strong>including header, content and footer</strong>). Dont forget to include unit (eg: px, em, rem).', 'rey-core' )
			],
			'default'     => [
				'padding-top'    => '',
				'padding-right'  => '',
				'padding-bottom' => '',
				'padding-left'   => '',
			],
			'choices'     => [
				'labels' => [
					'padding-top'  => esc_html__( 'Top', 'rey-core' ),
					'padding-right' => esc_html__( 'Right', 'rey-core' ),
					'padding-bottom'  => esc_html__( 'Bottom', 'rey-core' ),
					'padding-left' => esc_html__( 'Left', 'rey-core' ),
				],
			],
			'transport' => 'auto',
			'output'    => [
				[
					'element'  => ':root',
					'property' => '--site',
					'units'    => 'px',
				],
			],
			'css_class' => 'dimensions-4-cols',
			'separator' => 'top',
		] );

		$this->add_section_marker('layout_options');

		$this->add_title( esc_html__('Misc. Settings', 'rey-core'));

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'accessibility__hide_btn_focus',
			'label'       => esc_html__( 'Hide Button Focus (Accessibility)', 'rey-core' ),
			'help' => [
				__( 'This will hide the outlines added on buttons when focusing.', 'rey-core' )
			],
			'default'     => false,
			'separator' => 'before',
		] );


	}

}
