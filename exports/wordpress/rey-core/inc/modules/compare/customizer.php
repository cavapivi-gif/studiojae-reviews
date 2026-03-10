<?php
namespace ReyCore\Modules\Compare;

if ( ! defined( 'ABSPATH' ) ) exit;

class Customizer extends \ReyCore\Customizer\SectionsBase {

	public static function get_id(){
		return 'compare';
	}

	public function get_title(){
		return esc_html__('Compare', 'rey-core');
	}

	public function get_priority(){
		return 150;
	}

	public function get_icon(){
		return 'woo-compare';
	}

	public function get_breadcrumbs(){
		return ['WooCommerce', 'Modules'];
	}

	public function controls(){

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'compare__enable',
			'label'       => esc_html__( 'Enable Compare', 'rey-core' ),
			'default'     => false,
		] );

		$this->add_control( [
			'settings'    => 'compare__default_url',
			'label'       => esc_html__( 'Compare page', 'rey-core' ),
			'default'     => '',
			'active_callback' => [
				[
					'setting'  => 'compare__enable',
					'operator' => '==',
					'value'    => true,
				],
			],
			'type'        => 'select',
			'ajax_choices' => 'get_pages',
			'new_page' => [
				'placeholder' => esc_attr__('New page', 'rey-core'),
				'button_text' => esc_attr__('Add', 'rey-core'),
				'new_link' => esc_attr__('+ Add new page', 'rey-core'),
			],
		] );

			$this->add_control( [
				'type'        => 'select',
				'settings'    => 'compare__after_add',
				'label'       => esc_html__( 'After add to list', 'rey-core' ),
				'default'     => 'notice',
				'choices'     => [
					'' => esc_html__( 'Do nothing', 'rey-core' ),
					'notice' => esc_html__( 'Show Notice', 'rey-core' ),
				],
				'active_callback' => [
					[
						'setting'  => 'compare__enable',
						'operator' => '==',
						'value'    => true,
					],
				],
			] );

			$this->add_control( [
				'type'        => 'select',
				'settings'    => 'compare__excludes',
				'label'       => esc_html__('Exclude attributes', 'rey-core'),
				'help' => [
					__('Select if you want to exclude attributes from the compare list.', 'rey-core')
				],
				'default'     => [],
				'multiple' => 100,
				'choices'     => [
					'image' => esc_html_x('Image', 'Customizer control choices', 'rey-core'),
					'description' => esc_html_x('Description', 'Customizer control choices', 'rey-core'),
					'sku' => esc_html_x('SKU', 'Customizer control choices', 'rey-core'),
					'stock' => esc_html_x('Stock', 'Customizer control choices', 'rey-core'),
					'weight' => esc_html_x('Weight', 'Customizer control choices', 'rey-core'),
					'dimensions' => esc_html_x('Dimensions', 'Customizer control choices', 'rey-core'),
				],
				'ajax_choices' => 'get_pa_attributes_list',
				'active_callback' => [
					[
						'setting'  => 'compare__enable',
						'operator' => '==',
						'value'    => true,
					],
				],
			]);

		$this->add_title( esc_html__('Catalog', 'rey-core'), [
			'active_callback' => [
				[
					'setting'  => 'compare__enable',
					'operator' => '==',
					'value'    => true,
				],
			],
		]);


			$this->add_control( [
				'type'        => 'toggle',
				'settings'    => 'compare__loop_enable',
				'label'       => esc_html__( 'Enable button', 'rey-core' ),
				'default'     => true,
				'active_callback' => [
					[
						'setting'  => 'compare__enable',
						'operator' => '==',
						'value'    => true,
					],
				],
			] );


		$this->add_title( esc_html__('Product Page', 'rey-core'), [
			'active_callback' => [
				[
					'setting'  => 'compare__enable',
					'operator' => '==',
					'value'    => true,
				],
			],
		]);

			$this->add_control( [
				'type'        => 'toggle',
				'settings'    => 'compare__pdp_enable',
				'label'       => esc_html__( 'Enable button', 'rey-core' ),
				'default'     => true,
				'active_callback' => [
					[
						'setting'  => 'compare__enable',
						'operator' => '==',
						'value'    => true,
					],
				],
			] );


			$this->add_control( [
				'type'        => 'select',
				'settings'    => 'compare__pdp_wtext',
				'label'       => esc_html__( 'Text visibility', 'rey-core' ),
				'default'     => 'show_desktop',
				'choices' => [
					'' => esc_html__('Hide', 'rey-core'),
					'show' => esc_html__('Show', 'rey-core'),
					'show_desktop' => esc_html__('Show text on desktop only', 'rey-core'),
				],
				'active_callback' => [
					[
						'setting'  => 'compare__enable',
						'operator' => '==',
						'value'    => true,
					],
					[
						'setting'  => 'compare__pdp_enable',
						'operator' => '==',
						'value'    => true,
					],
				],
			] );

			$this->add_control( [
				'type'        => 'toggle',
				'settings'    => 'compare__pdp_tooltip',
				'label'       => esc_html__( 'Show tooltip', 'rey-core' ),
				'default'     => false,
				'active_callback' => [
					[
						'setting'  => 'compare__enable',
						'operator' => '==',
						'value'    => true,
					],
					[
						'setting'  => 'compare__pdp_enable',
						'operator' => '==',
						'value'    => true,
					],
					[
						'setting'  => 'compare__pdp_wtext',
						'operator' => '==',
						'value'    => '',
					],
				]
			] );

			$this->add_control( [
				'type'        => 'select',
				'settings'    => 'compare__pdp_position',
				'label'       => esc_html__( 'Button Position', 'rey-core' ),
				'default'     => 'after',
				'choices'     => [
					'inline' => esc_html__( 'Inline with ATC. button', 'rey-core' ),
					'before' => esc_html__( 'Before ATC. button', 'rey-core' ),
					'after' => esc_html__( 'After ATC. button', 'rey-core' ),
				],
				'active_callback' => [
					[
						'setting'  => 'compare__enable',
						'operator' => '==',
						'value'    => true,
					],
					[
						'setting'  => 'compare__pdp_enable',
						'operator' => '==',
						'value'    => true,
					],
				],
			] );

			$this->add_control( [
				'type'        => 'select',
				'settings'    => 'compare__pdp_btn_style',
				'label'       => esc_html__( 'Button Style', 'rey-core' ),
				'default'     => 'btn-line',
				'choices'     => [
					'none' => esc_html__( 'None', 'rey-core' ),
					'btn-line' => esc_html__( 'Underlined on hover', 'rey-core' ),
					'btn-line-active' => esc_html__( 'Underlined', 'rey-core' ),
					'btn-primary' => esc_html__( 'Regular', 'rey-core' ),
					'btn-primary btn--block' => esc_html__( 'Regular & Full width', 'rey-core' ),
					'btn-primary-outline' => esc_html__( 'Regular outline', 'rey-core' ),
					'btn-primary-outline btn--block' => esc_html__( 'Regular outline & Full width', 'rey-core' ),
					'btn-secondary' => esc_html__( 'Secondary', 'rey-core' ),
					'btn-secondary btn--block' => esc_html__( 'Secondary & Full width', 'rey-core' ),
				],
				'active_callback' => [
					[
						'setting'  => 'compare__enable',
						'operator' => '==',
						'value'    => true,
					],
					[
						'setting'  => 'compare__pdp_enable',
						'operator' => '==',
						'value'    => true,
					],
				],
			] );

	}
}
