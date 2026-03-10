<?php
namespace ReyCore\Modules\MiniCartRecents;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Customizer
{
	public function __construct()
	{
		add_action( 'reycore/customizer/section=header-mini-cart/marker=after_crosssells', [$this, 'add_controls'], 20);
	}

	function add_controls( $section ){

		$section->start_controls_accordion([
			'label'  => esc_html__( 'Recently Viewed products', 'rey-core' ),
		]);

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'header_cart__recent',
			'label'       => esc_html__( 'Show "Recently viewed" products', 'rey-core' ),
			'help' => [
				esc_html__( 'This will show up a list of 10 of the most recently viewed products.', 'rey-core')
			],
			'default'     => false,
			// 'separator'   => 'before',
		] );

		$section->end_controls_accordion();

	}

}
