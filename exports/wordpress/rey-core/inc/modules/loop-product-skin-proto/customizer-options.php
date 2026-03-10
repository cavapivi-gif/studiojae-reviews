<?php
namespace ReyCore\Modules\LoopProductSkinProto;

if ( ! defined( 'ABSPATH' ) ) exit;

class CustomizerOptions {

	public function __construct( $control_args, $section ){

		$section->start_controls_group( [
			'label'    => esc_html__( 'Proto Skin Options', 'rey-core' ),
			'active_callback' => [
				[
					'setting'  => 'loop_skin',
					'operator' => '==',
					'value'    => 'proto',
				],
			],
		]);

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'proto_loop_hover_animation',
			'label'       => __('Hover animation', 'rey-core'),
			'help' => [
				__('Select if products should have an animation effect on hover.', 'rey-core')
			],
			'default'     => true,
		] );

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'proto_loop_padded',
			'label'       => __('Inner padding', 'rey-core'),
			'help' => [
				__('Adds a surrounding padding to the product text, and enables the shadows.', 'rey-core')
			],
			'default'     => false,
			'active_callback' => [
				[
					'setting'  => 'loop_skin',
					'operator' => '==',
					'value'    => 'proto',
				],
			],
		] );

		$section->add_control( [
			'type'            => 'rey-color',
			'settings'        => 'proto_loop_text_color',
			'label'           => __( 'Text Color', 'rey-core' ),
			'default'         => '',
			'choices'         => [
				'alpha'          => true,
			],
			'transport'       => 'auto',
			'active_callback' => [
				[
					'setting'  => 'loop_skin',
					'operator' => '==',
					'value'    => 'proto',
				],
			],
			'output'          => [
				[
					'element'  		   => '.woocommerce ul.products.--skin-proto',
					'property' 		   => '--woocommerce-loop-proto-color',
				],
			]
		] );


		$section->add_control( [
			'type'            => 'rey-color',
			'settings'        => 'proto_loop_link_color',
			'label'           => __( 'Link Color', 'rey-core' ),
			'default'         => '',
			'choices'         => [
				'alpha'          => true,
			],
			'transport'       => 'auto',
			'active_callback' => [
				[
					'setting'  => 'loop_skin',
					'operator' => '==',
					'value'    => 'proto',
				],
			],
			'output'          => [
				[
					'element'  		   => '.woocommerce ul.products.--skin-proto',
					'property' 		   => '--woocommerce-loop-proto-color-link',
				],
			]
		] );

		$section->add_control( [
			'type'            => 'rey-color',
			'settings'        => 'proto_loop_bg_color',
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
					'value'    => 'proto',
				],
			],
			'output'          => [
				[
					'element'  		   => '.woocommerce ul.products.--skin-proto',
					'property' 		   => '--woocommerce-loop-proto-bgcolor',
				],
			],
		] );

		$section->add_control( [
			'type'        => 'select',
			'settings'    => 'proto_loop_shadow',
			'label'       => esc_html__( 'Box Shadow', 'rey-core' ),
			'default'     => '1',
			'choices'     => [
				'' => esc_html__( 'Disabled', 'rey-core' ),
				'1' => esc_html__( 'Level 1', 'rey-core' ),
				'2' => esc_html__( 'Level 2', 'rey-core' ),
				'3' => esc_html__( 'Level 3', 'rey-core' ),
				'4' => esc_html__( 'Level 4', 'rey-core' ),
				'5' => esc_html__( 'Level 5', 'rey-core' ),
			],
			'active_callback' => [
				[
					'setting'  => 'loop_skin',
					'operator' => '==',
					'value'    => 'proto',
				],
				[
					'setting'  => 'proto_loop_padded',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$section->add_control( [
			'type'        => 'select',
			'settings'    => 'proto_loop_shadow_hover',
			'label'       => esc_html__( 'Hover Box Shadow', 'rey-core' ),
			'default'     => '3',
			'choices'     => [
				'' => esc_html__( 'Disabled', 'rey-core' ),
				'1' => esc_html__( 'Level 1', 'rey-core' ),
				'2' => esc_html__( 'Level 2', 'rey-core' ),
				'3' => esc_html__( 'Level 3', 'rey-core' ),
				'4' => esc_html__( 'Level 4', 'rey-core' ),
				'5' => esc_html__( 'Level 5', 'rey-core' ),
			],
			'active_callback' => [
				[
					'setting'  => 'loop_skin',
					'operator' => '==',
					'value'    => 'proto',
				],
				[
					'setting'  => 'proto_loop_padded',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$section->end_controls_group();

	}
}
