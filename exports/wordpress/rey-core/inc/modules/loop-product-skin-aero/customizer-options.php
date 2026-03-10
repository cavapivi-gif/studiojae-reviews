<?php
namespace ReyCore\Modules\LoopProductSkinAero;

if ( ! defined( 'ABSPATH' ) ) exit;

class CustomizerOptions {

	public function __construct( $control_args, $section ){

		$section->start_controls_group( [
			'label'    => esc_html__( 'Skin Options', 'rey-core' ),
			'active_callback' => [
				[
					'setting'  => 'loop_skin',
					'operator' => '==',
					'value'    => Base::KEY,
				],
			],
		]);

			$section->add_control( [
				'type'        => 'toggle',
				'settings'    => 'aero_loop_hover_animation',
				'label'       => __('Hover animation', 'rey-core'),
				'help' => [
					__('Select if products should have an animation effect on hover.', 'rey-core')
				],
				'default'     => true,
			] );

		$section->end_controls_group();

	}
}
