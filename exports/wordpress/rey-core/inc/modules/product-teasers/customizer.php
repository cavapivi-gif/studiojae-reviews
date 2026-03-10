<?php
namespace ReyCore\Modules\ProductTeasers;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Customizer {

	public function __construct(){
		add_action('reycore/customizer/section=woo-catalog-grid-components/marker=components', [$this, 'add_controls'], 100);
	}

	public function add_controls( $section ){

		if( ! $section ){
			return;
		}

		$section->start_controls_accordion([
			'label'  => esc_html__( 'Product List Teasers', 'rey-core' ),
		]);

		$section->add_title( '', [
			'description' => __('Select generic global sections to be assigned in product catalog in specific locations.', 'rey-core'),
			'separator' => 'none'
		]);

		$section->add_control( [
			'type'        => 'repeater',
			'settings'    => 'loop_teasers',
			// 'label'       => esc_html__('Add Teasers', 'rey-core'),
			'row_label' => [
				'value' => esc_html__('Global Section', 'rey-core'),
				'type'  => 'field',
				'field' => 'gs',
			],
			'button_label' => esc_html__('New teaser', 'rey-core'),
			'default'      => [],
			'fields' => [
				'gs' => [
					'type'        => 'select',
					'label'       => esc_html__('Select Global Section', 'rey-core'),
					'choices'     => \ReyCore\Customizer\Helper::global_sections('generic', ['' => esc_html__('- Select -', 'rey-core')]),
					'export' => 'post_id'
				],
				'size' => [
					'type'    => 'number',
					'label'   => esc_html__('Choose Size', 'rey-core'),
					'default' => 2,
					'choices' => [
						'min'  => 1,
						'max'  => 10,
						'step' => 1,
					],
				],
				'row' => [
					'type'    => 'number',
					'label'   => esc_html__('Nth row to show in', 'rey-core'),
					'default' => 1,
					'choices' => [
						'min'  => 1,
						'max'  => 30,
						'step' => 1,
					],
				],
				'position' => [
					'type'        => 'select',
					'label'       => esc_html__('Select Position in Row', 'rey-core'),
					'default' => 'end',
					'choices'     => [
						'start' => esc_html__('Start', 'rey-core'),
						'end' => esc_html__('End', 'rey-core')
					],
				],
				'repeat' => [
					'type'        => 'select',
					'label'       => esc_html__('Repeat on each page', 'rey-core'),
					'default' => 'no',
					'choices'     => [
						'no' => esc_html__('No', 'rey-core'),
						'yes' => esc_html__('Yes', 'rey-core'),
					],
				],
				'categories' => [
					'type'        => 'select',
					'label'       => esc_html__('Assign on Categories', 'rey-core'),
					'query_args' => [
						'type' => 'terms',
						'taxonomy' => 'product_cat',
					],
					'multiple' => 100
				],
				'shop_page' => [
					'type'        => 'select',
					'label'       => esc_html__('Show on Shop page', 'rey-core'),
					'default' => 'no',
					'choices'     => [
						'no' => esc_html__('No', 'rey-core'),
						'yes' => esc_html__('Yes', 'rey-core'),
					],
				],
				'tags_page' => [
					'type'        => 'select',
					'label'       => esc_html__('Show on Tag pages', 'rey-core'),
					'default' => 'no',
					'choices'     => [
						'no' => esc_html__('No', 'rey-core'),
						'yes' => esc_html__('Yes', 'rey-core'),
					],
				],
			],
		] );

		$section->end_controls_accordion();


	}

}
