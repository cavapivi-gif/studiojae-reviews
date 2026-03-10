<?php
namespace ReyCore\Modules\LoopProductSkinRigo;

if ( ! defined( 'ABSPATH' ) ) exit;

class CustomizerOptions {

	public function __construct( $control_args, $section ){

		$section->start_controls_group( [
			'label'    => esc_html__( 'Skin Options', 'rey-core' ),
			'active_callback' => [
				[
					'setting'  => 'loop_skin',
					'operator' => '==',
					'value'    => 'rigo',
				],
			],
		]);

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'rigo_loop_hover_animation',
			'label'       => __('Hover animation', 'rey-core'),
			'help' => [
				__('Select if products should have an animation effect on hover.', 'rey-core')
			],
			'default'     => true,
		] );

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'rigo_loop_item_invert',
			'label'       => esc_html__( 'Invert button colors', 'rey-core' ),
			'default'     => true,
			'active_callback' => [
				[
					'setting'  => 'loop_skin',
					'operator' => '==',
					'value'    => 'rigo',
				],
			],
		] );

		$section->end_controls_group();

	}
}
