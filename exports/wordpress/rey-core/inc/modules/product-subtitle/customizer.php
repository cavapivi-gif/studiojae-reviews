<?php
namespace ReyCore\Modules\ProductSubtitle;

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
			'label'  => esc_html__( 'Product Subtitle', 'rey-core' ),
		]);

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'psubtitle__loop',
			'label'       => esc_html_x( 'Display subtitle', 'Customizer control text', 'rey-core' ),
			'default'     => false,
		] );

		$section->add_control( [
			'type'        => 'select',
			'settings'    => 'psubtitle__loop_pos',
			'label'       => esc_html_x( 'Position', 'Customizer control text', 'rey-core' ),
			'default'     => 'after_title',
			'choices'     => [
				'before_title' => 'Before Title',
				'after_title' => 'After Title',
				'after_content' => 'After content',
			],
			'active_callback' => [
				[
					'setting'  => 'psubtitle__loop',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$section->add_control( [
			'type'        => 'select',
			'settings'    => 'psubtitle__loop_tag',
			'label'       => esc_html_x( 'Tag', 'Customizer control text', 'rey-core' ),
			'default'     => 'h5',
			'choices'     => [
				'h2' => 'H2',
				'h3' => 'H3',
				'h4' => 'H4',
				'h5' => 'H5',
				'h6' => 'H6',
				'p' => 'Paragraph',
				'div' => 'Div',
			],
			'active_callback' => [
				[
					'setting'  => 'psubtitle__loop',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$section->add_control( [
			'type'        => 'rey-color',
			'settings'    => 'psubtitle__loop_color',
			'label'       => esc_html__( 'Text Color', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'alpha' => true,
			],
			'transport'   => 'auto',
			'output'      => [
				[
					'element'  		=> ':root',
					'property' 		=> '--psubtitle-loop-text-color',
				],
			],
			'active_callback' => [
				[
					'setting'  => 'psubtitle__loop',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$section->end_controls_accordion();

	}

	function add_pdp_controls( $section ){

		$section->start_controls_accordion([
			'label'  => esc_html__( 'Product Subtitle', 'rey-core' ),
		]);

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'psubtitle__pdp',
			'label'       => esc_html_x( 'Display subtitle', 'Customizer control text', 'rey-core' ),
			'default'     => false,
		] );

		$section->add_control( [
			'type'        => 'select',
			'settings'    => 'psubtitle__pdp_pos',
			'label'       => esc_html_x( 'Position', 'Customizer control text', 'rey-core' ),
			'default'     => 'after_title',
			'choices'     => [
				'before_title' => esc_html_x( 'Before title', 'Customizer control text', 'rey-core' ),
				'after_title' => esc_html_x( 'After title', 'Customizer control text', 'rey-core' ),
				'after_price' => esc_html_x( 'After price', 'Customizer control text', 'rey-core' ),
				'before_meta' => esc_html_x( 'Before meta', 'Customizer control text', 'rey-core' ),
				'after_meta' => esc_html_x( 'After meta', 'Customizer control text', 'rey-core' ),
			],
			'active_callback' => [
				[
					'setting'  => 'psubtitle__pdp',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$section->add_control( [
			'type'        => 'select',
			'settings'    => 'psubtitle__pdp_tag',
			'label'       => esc_html_x( 'Tag', 'Customizer control text', 'rey-core' ),
			'default'     => 'h4',
			'choices'     => [
				'h2' => 'H2',
				'h3' => 'H3',
				'h4' => 'H4',
				'h5' => 'H5',
				'h6' => 'H6',
				'p' => 'Paragraph',
				'div' => 'Div',
			],
			'active_callback' => [
				[
					'setting'  => 'psubtitle__pdp',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$section->add_control( [
			'type'        => 'rey-color',
			'settings'    => 'psubtitle__pdp_color',
			'label'       => esc_html__( 'Text Color', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'alpha' => true,
			],
			'transport'   => 'auto',
			'output'      => [
				[
					'element'  		=> ':root',
					'property' 		=> '--psubtitle-pdp-text-color',
				],
			],
			'active_callback' => [
				[
					'setting'  => 'psubtitle__pdp',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );


		$section->end_controls_accordion();

	}
}
