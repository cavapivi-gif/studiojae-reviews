<?php
namespace ReyCore\Modules\CustomSidebars;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Customizer {

	public function __construct(){
		add_action('reycore/customizer/section=woo-catalog-grid-components/marker=sidebar', [$this, 'add_controls'], 100);
	}

	public function add_controls( $section ){

		if( ! $section ){
			return;
		}

		$section->start_controls_accordion([
			'label'  => esc_html__( 'Custom Sidebars', 'rey-core' ),
		]);

		$section->add_title( '', [
			'description' => esc_html__('Create custom sidebars and show them on specific archives.', 'rey-core'),
			'separator' => 'none'
		]);

		$section->add_control( [
			'type'        => 'repeater',
			'settings'    => 'loop_sidebars_v2',
			'row_label' => [
				'value' => esc_html__('Sidebar', 'rey-core'),
				'type'  => 'field',
				'field' => 'name',
			],
			'button_label' => esc_html__('New sidebar', 'rey-core'),
			'default'      => [],
			'fields' => [

				'name' => [
					'type'        => 'text',
					'label'       => esc_html__('Sidebar name (required)', 'rey-core'),
					'default'     => '',
				],

				'type' => [
					'type'        => 'select',
					'label'       => esc_html__('Sidebar type', 'rey-core'),
					'default'     => 'shop-sidebar',
					'choices'     => [
						'shop-sidebar' => esc_html__('Shop Sidebar', 'rey-core'),
						'filters-sidebar' => esc_html__('Filter Panel', 'rey-core'),
						'filters-top-sidebar' => esc_html__('Filter Top Bar', 'rey-core'),
					],
				],

				'terms' => [
					'type'       => 'select',
					'label'      => esc_html__('Choose terms (Categories, Attributes etc.)', 'rey-core'),
					'query_args' => [
						'type' => 'terms',
						'taxonomy' => 'product_taxonomies',
					],
					'multiple' => 100,
					'condition' => [
						[
							'setting'  => 'all',
							'operator' => '==',
							'value'    => 'no',
						],
					],
				],

				'tax' => [
					'type'     => 'select',
					'label'    => esc_html__('Product Taxonomies', 'rey-core'),
					'multiple' => 100,
					'choices'  => $this->get_product_taxonomies(),
					'condition' => [
						[
							'setting'  => 'all',
							'operator' => '==',
							'value'    => 'no',
						],
					],
				],

				'all' => [
					'type'        => 'select',
					'label'       => esc_html__('All Product Taxonomies?', 'rey-core'),
					'default'     => 'no',
					'choices'     => [
						'no' => esc_html__('No', 'rey-core'),
						'yes' => esc_html__('Yes', 'rey-core'),
					],
				],

				'shop_page' => [
					'type'        => 'select',
					'label'       => esc_html__('Add on Shop Page?', 'rey-core'),
					'default'     => 'no',
					'choices'     => [
						'no' => esc_html__('No', 'rey-core'),
						'yes' => esc_html__('Yes', 'rey-core'),
					],
				],

			],
		] );

		$section->end_controls_accordion();

	}

	public function get_product_taxonomies(){

		$taxs = array_diff( get_object_taxonomies( 'product' ), [
			'product_type',
			'product_visibility',
			'product_shipping_class',
		] );

		$obl = [];

		foreach ($taxs as $tax_slug) {
			$taxonomy = get_taxonomy($tax_slug);
			$obl[$tax_slug] = isset($taxonomy->label) ? $taxonomy->label : ucfirst($tax_slug);
		}

		return $obl;
	}

}
