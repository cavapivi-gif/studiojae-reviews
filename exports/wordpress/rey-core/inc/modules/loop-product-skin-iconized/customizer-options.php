<?php
namespace ReyCore\Modules\LoopProductSkinIconized;

if ( ! defined( 'ABSPATH' ) ) exit;

class CustomizerOptions {

	public function __construct( $control_args, $section ){

		$section->start_controls_group( [
			'label'    => esc_html__( 'Iconized Skin Options', 'rey-core' ),
			'active_callback' => [
				[
					'setting'  => 'loop_skin',
					'operator' => '==',
					'value'    => 'iconized',
				],
			],
		]);

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'iconized_loop_hover_animation',
			'label'       => esc_html__('Hover animation', 'rey-core'),
			'help' => [
				__('Select if products should have an animation effect on hover.', 'rey-core')
			],
			'default'     => true,
		] );

		$section->add_control( [
			'type'        => 'slider',
			'settings'    => 'iconized_loop_inner_padding',
			'label'       => esc_html__( 'Content Inner padding', 'rey-core' ),
			'default'     => 15,
			'transport'   => 'auto',
			'choices'     => [
				'min'  => 0,
				'max'  => 100,
				'step' => 1,
			],
			'active_callback' => [
				[
					'setting'  => 'loop_skin',
					'operator' => '==',
					'value'    => 'iconized',
				],
			],
			'output'          => [
				[
					'element'  		   => '.woocommerce [data-skin="iconized"]',
					'property' 		   => '--woocommerce-loop-iconized-padding',
					'units' 		   => 'px',
				],
			],
		] );

		$section->add_control( [
			'type'        => 'rey-number',
			'settings'    => 'iconized_loop_border_size',
			'label'       => esc_html__( 'Border size', 'rey-core' ),
			'default'     => 1,
			'choices'     => [
				'min'  => 0,
				'max'  => 200,
				'step' => 1,
			],
			'output'          => [
				[
					'element'  		   => '.woocommerce [data-skin="iconized"]',
					'property' 		   => '--woocommerce-loop-iconized-size',
					'units' 		   => 'px',
				],
			],
			'active_callback' => [
				[
					'setting'  => 'loop_skin',
					'operator' => '==',
					'value'    => 'iconized',
				],
			],
		] );

		$section->add_control( [
			'type'            => 'rey-color',
			'settings'        => 'iconized_loop_border_color',
			'label'           => __( 'Border Color', 'rey-core' ),
			'default'         => '',
			'choices'         => [
				'alpha'          => true,
			],
			'transport'       => 'auto',
			'active_callback' => [
				[
					'setting'  => 'loop_skin',
					'operator' => '==',
					'value'    => 'iconized',
				],
			],
			'output'          => [
				[
					'element'  		   => '.woocommerce [data-skin="iconized"]',
					'property' 		   => '--woocommerce-loop-iconized-bordercolor',
				],
			]
		] );

		$section->add_control( [
			'type'            => 'rey-color',
			'settings'        => 'iconized_loop_bg_color',
			'label'           => __( 'Background Color', 'rey-core' ),
			'default'         => '',
			'choices'         => [
				'alpha'          => true,
			],
			'transport'       => 'auto',
			'active_callback' => [
				[
					'setting'  => 'loop_skin',
					'operator' => '==',
					'value'    => 'iconized',
				],
			],
			'output'          => [
				[
					'element'  		   => '.woocommerce [data-skin="iconized"]',
					'property' 		   => '--woocommerce-loop-iconized-bgcolor',
				],
			],
		] );

		$section->add_control( [
			'type'        => 'rey-number',
			'settings'    => 'iconized_loop_radius',
			'label'       => esc_html__( 'Border radius', 'rey-core' ),
			'default'     => 0,
			'choices'     => [
				'min'  => 0,
				'max'  => 200,
				'step' => 1,
			],
			'output'          => [
				[
					'element'  		   => '.woocommerce [data-skin="iconized"]',
					'property' 		   => '--woocommerce-loop-iconized-radius',
					'units' 		   => 'px',
				],
			],
			'active_callback' => [
				[
					'setting'  => 'loop_skin',
					'operator' => '==',
					'value'    => 'iconized',
				],
			],
		] );

		$section->end_controls_group();

	}
}
