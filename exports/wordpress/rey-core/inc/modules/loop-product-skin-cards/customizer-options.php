<?php
namespace ReyCore\Modules\LoopProductSkinCards;

if ( ! defined( 'ABSPATH' ) ) exit;

class CustomizerOptions {

	public function __construct( $control_args, $section ){

		$section->start_controls_group( [
			'label'    => esc_html__( 'Cards Skin Options', 'rey-core' ),
			'active_callback' => [
				[
					'setting'  => 'loop_skin',
					'operator' => '==',
					'value'    => 'cards',
				],
			],
		]);

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'cards_loop_hover_animation',
			'label'       => esc_html__('Hover animation', 'rey-core'),
			'help' => [
				__('Select if products should have an animation effect on hover.', 'rey-core')
			],
			'default'     => true,
		] );

		$section->add_control( [
			'type'        => 'slider',
			'settings'    => 'cards_loop_inner_padding',
			'label'       => esc_html__( 'Content Inner padding', 'rey-core' ),
			'default'     => 30,
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
					'value'    => 'cards',
				],
			],
			'output'          => [
				[
					'element'  		   => ':root',
					'property' 		   => '--woocommerce-loop-cards-padding',
					'units' 		   => 'px',
				],
			],
		] );

		$section->add_control( [
			'type'            => 'rey-color',
			'settings'        => 'cards_loop_border_color',
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
					'value'    => 'cards',
				],
			],
			'output'          => [
				[
					'element'  		   => ':root',
					'property' 		   => '--woocommerce-loop-cards-bordercolor',
				],
			]
		] );

		$section->add_control( [
			'type'            => 'rey-color',
			'settings'        => 'cards_loop_bg_color',
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
					'value'    => 'cards',
				],
			],
			'output'          => [
				[
					'element'  		   => ':root',
					'property' 		   => '--woocommerce-loop-cards-bgcolor',
				],
			],
		] );

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'cards_loop_expand_thumbnails',
			'label'       => esc_html__('Expand thumbnails?', 'rey-core'),
			'help' => [
				__('Force thumbnails to expand til edges regardless of surrounding padding.', 'rey-core')
			],
			'default'     => false,
			'active_callback' => [
				[
					'setting'  => 'loop_skin',
					'operator' => '==',
					'value'    => 'cards',
				],
			],
		] );

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'cards_loop_square_corners',
			'label'       => esc_html__('Use Square Corners?', 'rey-core'),
			'default'     => false,
			'active_callback' => [
				[
					'setting'  => 'loop_skin',
					'operator' => '==',
					'value'    => 'cards',
				],
			],
		] );

		$section->end_controls_group();


	}
}
