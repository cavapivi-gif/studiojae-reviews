<?php
namespace ReyCore\Modules\PdpRecentlyViewed;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Customizer {

	public function __construct(){
		add_action('reycore/customizer/section=woo-product-page-components', [$this, 'controls'], 20);
	}

	public function controls( $section ){

		$section->add_title( esc_html__('Recently Viewed Products', 'rey-core'), [
				'description' => esc_html__( 'This will show a product list filled with products the visitor has viewed in a session.', 'rey-core' )
			]
		);

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'single__recently_viewed',
			'label'       => esc_html__( 'Enable Recently viewed', 'rey-core' ),
			'default'     => false,
		] );

		$section->add_control( [
			'type'     => 'text',
			'settings' => 'single__recently_viewed_title',
			'label'    => esc_html__('Title', 'rey-core'),
			'default'  => '',
			'active_callback' => [
				[
					'setting'  => 'single__recently_viewed',
					'operator' => '==',
					'value'    => true,
				],
			],
			'input_attrs' => [
				'placeholder' => esc_html__('eg: RECENTLY VIEWED', 'rey-core')
			]
		] );

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'category__recently_viewed',
			'label'       => esc_html__( 'Show Recently viewed in Category pages', 'rey-core' ),
			'default'     => false,
		] );

	}
}
