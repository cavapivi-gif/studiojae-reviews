<?php
namespace ReyCore\Modules\ProductVideo;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Customizer {

	public function __construct(){

		add_action( 'reycore/customizer/section=woo-product-page-gallery', [ $this, 'fields' ] );
	}

	public function fields( $section ){

		$section->add_title( esc_html__('Product Video', 'rey-core'));

		$section->add_control( [
			'type'        => 'select',
			'settings'    => 'product_gallery_video_position',
			'label'       => esc_html__( 'Position in gallery', 'rey-core' ),
			'default'     => 'last',
			'choices'     => [
				'first' => esc_html__( 'First', 'rey-core' ),
				'second' => esc_html__( 'Second', 'rey-core' ),
				'last' => esc_html__( 'Last', 'rey-core' ),
			],
		] );

	}
}
