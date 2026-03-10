<?php
namespace ReyCore\Modules\ScheduledSales;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Customizer
{
	public function __construct()
	{
		add_action('reycore/customizer/section=woo-catalog-product-item', [$this, 'add_loop_controls']);
		add_action('reycore/customizer/section=woo-product-page-summary-components/marker=before_meta', [$this, 'add_pdp_controls'], 20);
	}

	function add_loop_controls( $section ){

		$section->start_controls_accordion([
			'label'  => esc_html__( 'Scheduled Sales', 'rey-core' ),
		]);

		$section->add_title( '', [
			'description' => sprintf(_x('Badge or countdown notice inside the product showing when the scheduled sale will end. <a href="%s" target="_blank">How to schedule a sale</a>?', 'Customizer control text', 'rey-core'), 'https://d.pr/v/SSJZ4n'),
			'separator' => 'none'
		]);

		$section->add_control( [
			'type'        => 'select',
			'settings'    => 'scheduled_sale__loop_type',
			'label'       => esc_html_x( 'Display type', 'Customizer control text', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'' => esc_html_x( '- Disabled -', 'Customizer control text', 'rey-core' ),
				'badge' => esc_html_x( 'Text Badge', 'Customizer control text', 'rey-core' ),
				'countdown' => esc_html_x( 'Countdown', 'Customizer control text', 'rey-core' ),
				'inline-countdown' => esc_html_x( 'Countdown Inline', 'Customizer control text', 'rey-core' ),
			],
		] );

		$section->add_control( [
			'type'        => 'select',
			'settings'    => 'scheduled_sale__loop_pos',
			'label'       => esc_html_x( 'Position', 'Customizer control text', 'rey-core' ),
			'default'     => 'top_left',
			'choices'     => [
				'top_left' => 'Top Left',
				'top_right' => 'Top Right',
				'bottom_left' => 'Bottom Left',
				'bottom_right' => 'Bottom Right',
				'before_title' => 'Before Title',
				'after_title' => 'After Title',
				'after_content' => 'After content',
			],
			'active_callback' => [
				[
					'setting'  => 'scheduled_sale__loop_type',
					'operator' => '!=',
					'value'    => '',
				],
			],
		] );

		$section->add_control( [
			'type'        => 'text',
			'settings'    => 'scheduled_sale__loop_text',
			'label'       => esc_html_x( 'Text', 'Customizer control text', 'rey-core' ),
			'default'     => '',
			'input_attrs'     => [
				'placeholder' => esc_html_x('eg: Ends in', 'Customizer control text', 'rey-core'),
			],
			'active_callback' => [
				[
					'setting'  => 'scheduled_sale__loop_type',
					'operator' => '!=',
					'value'    => '',
				],
			],
		] );

		$section->add_control( [
			'type'        => 'rey-color',
			'settings'    => 'scheduled_sale__loop_text_color',
			'label'       => esc_html__( 'Text Color', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'alpha' => true,
			],
			'transport'   => 'auto',
			'output'      => [
				[
					'element'  		=> ':root',
					'property' 		=> '--sch-sale-text-color',
				],
			],
			'active_callback' => [
				[
					'setting'  => 'scheduled_sale__loop_type',
					'operator' => '!=',
					'value'    => '',
				],
			],
		] );

		$section->add_control( [
			'type'        => 'rey-color',
			'settings'    => 'scheduled_sale__loop_bg_color',
			'label'       => esc_html__( 'Background Color', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'alpha' => true,
			],
			'transport'   => 'auto',
			'output'      => [
				[
					'element'  		=> ':root',
					'property' 		=> '--sch-sale-bg-color',
				],
			],
			'active_callback' => [
				[
					'setting'  => 'scheduled_sale__loop_type',
					'operator' => '!=',
					'value'    => '',
				],
			],
		] );

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'scheduled_sale__loop_stretch',
			'label'       => esc_html__( 'Stretch', 'rey-core' ),
			'default'     => false,
			'active_callback' => [
				[
					'setting'  => 'scheduled_sale__loop_type',
					'operator' => '!=',
					'value'    => '',
				],
			],
		] );

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'scheduled_sale__loop_center',
			'label'       => esc_html__( 'Align Center', 'rey-core' ),
			'default'     => false,
			'active_callback' => [
				[
					'setting'  => 'scheduled_sale__loop_type',
					'operator' => '!=',
					'value'    => '',
				],
			],
		] );

		$section->end_controls_accordion();

	}

	function add_pdp_controls( $section ){

		$section->start_controls_accordion([
			'label'  => esc_html__( 'Scheduled Sale', 'rey-core' ),
		]);

		$section->add_title( '', [
			'description' => sprintf(_x('Badge or countdown notice inside the product showing when the scheduled sale will end. <a href="%s" target="_blank">How to schedule a sale</a>?', 'Customizer control text', 'rey-core'), 'https://d.pr/v/SSJZ4n'),
			'separator' => 'none',
		]);

		$section->add_control( [
			'type'        => 'select',
			'settings'    => 'scheduled_sale__pdp_type',
			'label'       => esc_html_x( 'Display type', 'Customizer control text', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'' => esc_html_x( '- Disabled -', 'Customizer control text', 'rey-core' ),
				'badge' => esc_html_x( 'Text Badge', 'Customizer control text', 'rey-core' ),
				'countdown' => esc_html_x( 'Countdown', 'Customizer control text', 'rey-core' ),
				'inline-countdown' => esc_html_x( 'Countdown Inline', 'Customizer control text', 'rey-core' ),
			],
		] );

		$section->add_control( [
			'type'        => 'select',
			'settings'    => 'scheduled_sale__pdp_pos',
			'label'       => esc_html_x( 'Position', 'Customizer control text', 'rey-core' ),
			'default'     => 'after_price',
			'choices'     => [
				'before_title' => esc_html_x( 'Before title', 'Customizer control text', 'rey-core' ),
				'after_price' => esc_html_x( 'After price', 'Customizer control text', 'rey-core' ),
				'before_meta' => esc_html_x( 'Before meta', 'Customizer control text', 'rey-core' ),
				'after_meta' => esc_html_x( 'After meta', 'Customizer control text', 'rey-core' ),
			],
			'active_callback' => [
				[
					'setting'  => 'scheduled_sale__pdp_type',
					'operator' => '!=',
					'value'    => '',
				],
			],
		] );

		$section->add_control( [
			'type'        => 'text',
			'settings'    => 'scheduled_sale__pdp_text',
			'label'       => esc_html_x( 'Text', 'Customizer control text', 'rey-core' ),
			'default'     => '',
			'input_attrs'     => [
				'placeholder' => esc_html_x('eg: Ends in', 'Customizer control text', 'rey-core'),
			],
			'active_callback' => [
				[
					'setting'  => 'scheduled_sale__pdp_type',
					'operator' => '!=',
					'value'    => '',
				],
			],
		] );

		$section->add_control( [
			'type'        => 'rey-color',
			'settings'    => 'scheduled_sale__pdp_text_color',
			'label'       => esc_html__( 'Text Color', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'alpha' => true,
			],
			'transport'   => 'auto',
			'output'      => [
				[
					'element'  		=> ':root',
					'property' 		=> '--sch-sale-pdp-text-color',
				],
			],
			'active_callback' => [
				[
					'setting'  => 'scheduled_sale__pdp_type',
					'operator' => '!=',
					'value'    => '',
				],
			],
		] );

		$section->add_control( [
			'type'        => 'rey-color',
			'settings'    => 'scheduled_sale__pdp_bg_color',
			'label'       => esc_html__( 'Background Color', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'alpha' => true,
			],
			'transport'   => 'auto',
			'output'      => [
				[
					'element'  		=> ':root',
					'property' 		=> '--sch-sale-pdp-bg-color',
				],
			],
			'active_callback' => [
				[
					'setting'  => 'scheduled_sale__pdp_type',
					'operator' => '!=',
					'value'    => '',
				],
			],
		] );

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'scheduled_sale__pdp_stretch',
			'label'       => esc_html__( 'Stretch', 'rey-core' ),
			'default'     => false,
			'active_callback' => [
				[
					'setting'  => 'scheduled_sale__pdp_type',
					'operator' => '!=',
					'value'    => '',
				],
			],
		] );

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'scheduled_sale__pdp_center',
			'label'       => esc_html__( 'Align Center', 'rey-core' ),
			'default'     => false,
			'active_callback' => [
				[
					'setting'  => 'scheduled_sale__pdp_type',
					'operator' => '!=',
					'value'    => '',
				],
			],
		] );

		$section->end_controls_accordion();

	}
}
