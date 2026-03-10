<?php
namespace ReyCore\Customizer\Options\General;

if ( ! defined( 'ABSPATH' ) ) exit;

use \ReyCore\Customizer\Controls;

class StickyGlobalSections extends \ReyCore\Customizer\SectionsBase {

	public static function get_id(){
		return 'general-sticky-global-sections';
	}

	public function get_title(){
		return esc_html__('Sticky Top/Bottom Global Sections', 'rey-core');
	}

	public function get_priority(){
		return 35;
	}

	public function get_separator(){
		return 'after';
	}

	public function get_icon(){
		return 'sticky-global-sections';
	}

	public function help_link(){
		return reycore__support_url('kb/customizer-general-settings/#sticky-global-sections');
	}

	public function can_load(){
		return class_exists('\Elementor\Plugin');
	}

	public function controls(){

		$this->add_title( '', [
			'description' => esc_html__('Add sticky global sections in your site. For example you can build a Sticky Header which would be totally different than the site header (height, colors, background etc.).', 'rey-core') ,
			'separator' => 'none'
		]);


		/* ------------------------------------ TOP CONTENT ------------------------------------ */

		$this->add_title( esc_html__('Top Global Section', 'rey-core'), [
			'description' => esc_html__('This will be positioned at the top edge of the site.', 'rey-core'),
		]);

		$this->add_control( array(
			'type'        => 'select',
			'settings'    => 'top_sticky_gs',
			'label'       => esc_html__( 'Global Section', 'rey-core' ),
			'help' => [
				reycore__header_footer_layout_desc('generic section'),
				'size'      => 290,
				'clickable' => true
			],
			'default'     => '',
			'choices'     => [
				'' => '- Select -'
			],
			'ajax_choices' => [
				'action' => 'get_global_sections',
				'params' => [
					'type' => ['generic','header'],
				]
			],
			'edit_preview' => true,
		));

		$this->start_controls_group( [
			'label'    => esc_html__( 'Options', 'rey-core' ),
			'active_callback' => [
				[
					'setting'  => 'top_sticky_gs',
					'operator' => '!=',
					'value'    => '',
				],
			],
		]);

			$this->add_control( [
				'type'     => 'text',
				'settings' => 'top_sticky_gs_offset',
				'label'       => esc_html__( 'Activation Offset (px)', 'rey-core' ),
				'help' => [
					esc_html__( 'At what distance from the top edge should be displayed. Add value in pixels eg: 100 or 200px etc. or a unique id selector, eg: #my_unique_element . If empty it\'ll be triggered after the site-header exists viewport.', 'rey-core' )
				],
				'default'     => '',
				'input_attrs' => [
					'data-control-class' => '--text-md',
				],
			] );

			$this->add_control( [
				'type'        => 'rey-color',
				'settings'    => 'top_sticky_gs_color',
				'label'       => esc_html__( 'Text Color', 'rey-core' ),
				'default'     => '',
				'choices'     => [
					'alpha' => true,
				],
				'output'      		=> [
					[
						'element'  		=> ':root',
						'property' 		=> '--sticky-gs-top-color',
					],
				],
			] );

			$this->add_control( [
				'type'        => 'rey-color',
				'settings'    => 'top_sticky_gs_bg_color',
				'label'       => esc_html__( 'Background Color', 'rey-core' ),
				'default'     => '',
				'choices'     => [
					'alpha' => true,
				],
				'output'      		=> [
					[
						'element'  		=> ':root',
						'property' 		=> '--sticky-gs-top-bg-color',
					],
				],
			] );

			$this->add_control( [
				'type'        => 'multicheck',
				'settings'    => 'top_sticky_gs_hide_devices',
				'label'       => esc_html__( 'Select visibility', 'rey-core' ),
				'default'     => get_theme_mod('top_sticky_gs_hide_on_mobile', true) === false ? [] : ['mobile'],
				'choices'     => [
					'desktop' => esc_html__( 'Hide on desktop', 'rey-core' ),
					'tablet' => esc_html__( 'Hide on tablets', 'rey-core' ),
					'mobile' => esc_html__( 'Hide on mobile', 'rey-core' ),
				],
			] );

			$this->add_control( [
				'type'        => 'toggle',
				'settings'    => 'top_sticky_gs_dir_only',
				'label'       => esc_html__( 'Show only when scrolling upwards', 'rey-core' ),
				'default'     => false,
			] );

			$this->add_control( [
				'type'        => 'toggle',
				'settings'    => 'top_sticky_gs_close',
				'label'       => esc_html__( 'Add close button', 'rey-core' ),
				'default'     => false,
				'input_attrs' => array(
					'data-control-class' => 'mb-3',
				),
			] );

		$this->end_controls_group();


		/* ------------------------------------ BOTTOM CONTENT ------------------------------------ */

		$this->add_title( esc_html__('Bottom Global Section', 'rey-core'), [
			'description' => esc_html__('This will be positioned at the bottom edge of the site.', 'rey-core'),
		]);

		$this->add_control( array(
			'type'        => 'select',
			'settings'    => 'bottom_sticky_gs',
			'label'       => esc_html__( 'Global Section', 'rey-core' ),
			'help' => [
				reycore__header_footer_layout_desc('generic section'),
				'size'      => 290,
				'clickable' => true
			],
			'default'     => '',
			'choices'     => [
				'' => '- Select -'
			],
			'ajax_choices' => [
				'action' => 'get_global_sections',
				'params' => [
					'type' => ['generic','footer'],
				]
			],
			'edit_preview' => true,
		));

		$this->start_controls_group( [
			'label'    => esc_html__( 'Options', 'rey-core' ),
			'active_callback' => [
				[
					'setting'  => 'bottom_sticky_gs',
					'operator' => '!=',
					'value'    => '',
				],
			],
		]);

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'bottom_sticky_gs_always_visible',
			'label'       => esc_html__( 'Always visible', 'rey-core' ),
			'default'     => false,
		] );

		$this->add_control( [
			'type'     => 'text',
			'settings' => 'bottom_sticky_gs_offset',
			'label'       => esc_html__( 'Activation Offset (px)', 'rey-core' ),
			'help' => [
				esc_html__( 'At what distance from the top edge should be displayed. Add value in pixels eg: 100 or 200px etc. or a unique id selector, eg: #my_unique_element . If empty it\'ll be triggered after the site-header exists viewport.', 'rey-core' )
			],
			'default'     => '',
			'active_callback' => [
				[
					'setting'  => 'bottom_sticky_gs_always_visible',
					'operator' => '==',
					'value'    => false,
				],
			],
			'input_attrs' => [
				'data-control-class' => '--text-md',
			],
		] );

		$this->add_control( [
			'type'        => 'multicheck',
			'settings'    => 'bottom_sticky_gs_hide_devices',
			'label'       => esc_html__( 'Select visibility', 'rey-core' ),
			'default'     => get_theme_mod('bottom_sticky_gs_hide_on_mobile', true) === false ? [] : ['mobile'],
			'choices'     => [
				'desktop' => esc_html__( 'Hide on desktop', 'rey-core' ),
				'tablet' => esc_html__( 'Hide on tablets', 'rey-core' ),
				'mobile' => esc_html__( 'Hide on mobile', 'rey-core' ),
			],
		] );


		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'bottom_sticky_gs_dir_only',
			'label'       => esc_html__( 'Show only when scrolling upwards', 'rey-core' ),
			'default'     => false,
		] );

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'bottom_sticky_gs_close',
			'label'       => esc_html__( 'Add close button', 'rey-core' ),
			'default'     => false,
		] );

		$this->end_controls_group();

	}
}
