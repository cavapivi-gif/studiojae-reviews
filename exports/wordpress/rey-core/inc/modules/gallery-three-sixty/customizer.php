<?php
namespace ReyCore\Modules\GalleryThreeSixty;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Customizer {

	public function __construct(){

		add_action( 'reycore/customizer/section=woo-product-page-gallery', [ $this, 'fields' ] );
	}

	public function fields( $section ){

		$section->add_title( esc_html__('360 Images', 'rey-core'), [
			'description' => esc_html__('These are options for the 360 image in product gallery.', 'rey-core'),
		]);

		$section->add_control( [
			'type'        => 'select',
			'settings'    => 'wc360_position',
			'label'       => esc_html__( 'Position in gallery', 'rey-core' ),
			'default'     => 'second',
			'choices'     => [
				'first' => esc_html__( 'First', 'rey-core' ),
				'second' => esc_html__( 'Second', 'rey-core' ),
				'last' => esc_html__( 'Last', 'rey-core' ),
			],
		] );

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'wc360_autoplay',
			'label'       => esc_html__( 'Enable autoplay', 'rey-core' ),
			'default'     => true,
		] );

		$section->add_control( [
			'type'        => 'rey-number',
			'settings'    => 'wc360_autoplay_speed',
			'label'       => esc_html__( 'Autoplay Speed', 'rey-core' ) . ' (ms)',
			'default'     => 250,
			'choices'     => [
				'min'  => 50,
				'max'  => 1000,
				'step' => 10,
			],
			'active_callback' => [
				[
					'setting'  => 'wc360_autoplay',
					'operator' => '==',
					'value'    => true,
					],
			],
		] );

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'wc360_fullscreen',
			'label'       => esc_html__( 'Enable Full-screen button', 'rey-core' ),
			'default'     => true,
		] );

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'wc360_drag_reverse',
			'label'       => esc_html__( 'Reverse drag', 'rey-core' ),
			'default'     => false,
		] );


	}
}
