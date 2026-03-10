<?php
namespace ReyCore\Modules\DiscountBadge;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Customizer {

	public function __construct(){
		add_action('reycore/customizer/control=group_end__loop_price_options', [$this, 'loop_controls'], 10, 2);
		add_action('reycore/customizer/control=group_end__price_block_group', [$this, 'pdp_controls'], 10, 2);
	}

	public function loop_controls( $control_args, $section ){

		$section->add_control( [
			'type' => 'select',
			'settings' => 'loop_show_sale_label',
			'label' => esc_html__('Sale/Discount Label', 'rey-core'),
			'description' => __('Choose if you want to display a sale/discount label products.', 'rey-core'),
			'default' => 'percentage',
			'choices' => [
				'' => esc_attr__('Disabled', 'rey-core'),
				'sale' => esc_attr__('Sale Label (top right)', 'rey-core'),
				'percentage' => esc_attr__('Discount Percentage', 'rey-core'),
				'save' => esc_attr__('Save $$', 'rey-core'),
			],
			'active_callback' => [
				[
					'setting' => 'loop_show_prices',
					'operator' => '==',
					'value' => '1',
				],
			],
		]);

		$section->start_controls_group( [
			'label'    => esc_html__( 'Label options', 'rey-core' ),
			'active_callback' => [
				[
					'setting' => 'loop_show_prices',
					'operator' => '==',
					'value' => '1',
				],
				[
					'setting' => 'loop_show_sale_label',
					'operator' => '!=',
					'value' => '',
				],
			],
		]);

			$section->add_control( [
				'type' => 'text',
				'settings' => 'loop_sale__save_text',
				'label' => esc_html__('Save Text', 'rey-core'),
				'default' => '',
				'input_attrs' => [
					'placeholder' => esc_html__('eg: Save', 'rey-core'),
				],
				'active_callback' => [
					[
						'setting' => 'loop_show_sale_label',
						'operator' => '==',
						'value' => 'save',
					],
				],
			]);

			$section->add_control( [
				'type' => 'select',
				'settings' => 'loop_discount_label',
				'label' => esc_html__('Discount Label Position', 'rey-core'),
				'description' => __('Choose the discount label position.', 'rey-core'),
				'default' => '',
				'choices' => [
					'' => esc_attr__('- Inherit (from skin) -', 'rey-core'),
					'price' => esc_attr__('In Price', 'rey-core'),
					'top' => esc_attr__('Top Right', 'rey-core'),
				],
				'active_callback' => [
					[
						'setting' => 'loop_show_sale_label',
						'operator' => '==',
						'value' => 'percentage',
					],
					[
						'setting' => 'loop_show_prices',
						'operator' => '==',
						'value' => '1',
					],
				],
			]);

			$section->add_control( [
				'type' => 'rey-color',
				'settings' => 'loop_discount_label_color',
				'label' => __('Sale Price & Discount Color', 'rey-core'),
				'default' => '',
				'choices' => [
					'alpha' => true,
				],
				'output' => [
					[
						'element' => ':root',
						'property' => '--woocommerce-discount-color',
					],
				],
			]);

		$section->end_controls_group();

	}

	public function pdp_controls( $control_args, $section ){

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'single_discount_badge_v2',
			'label'       => esc_html__('Discount badge', 'rey-core'),
			'help' => [
				__('Select if the discount badge should be displayed.', 'rey-core')
			],
			'default'     => true,
			'active_callback' => [
				[
					'setting'  => 'single_product_price',
					'operator' => '==',
					'value'    => true,
				],
			],
			'separator' => 'before',
		] );

	}

}
