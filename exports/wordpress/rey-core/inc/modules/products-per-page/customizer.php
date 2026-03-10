<?php
namespace ReyCore\Modules\ProductsPerPage;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Customizer
{
	public function __construct()
	{
		add_action('reycore/customizer/section=woo-catalog-grid-components/marker=components', [$this, 'add_loop_controls']);
	}

	function add_loop_controls( $section ){

		$section->start_controls_accordion([
			'label'  => esc_html__( 'Products per page Switcher', 'rey-core' ),
		]);

		$section->add_title( '', [
			'description' => _x('Will display a switcher to change the products per page count. This feature affects performance because the DB queries will be higher.', 'Customizer control text', 'rey-core'),
			'separator' => 'none'
		]);

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'loop_switcher_ppp',
			'label'       => esc_html__( 'Enable Switcher', 'rey-core' ),
			'default'     => false,
		] );

		$section->add_control( [
			'type'        => 'select',
			'settings'    => 'loop_switcher_ppp_layout',
			'label'       => esc_html_x( 'Switcher layout', 'Customizer control text', 'rey-core' ),
			'default'     => 'inline',
			'choices'     => [
				'inline' => esc_html_x( 'Inline list', 'Customizer control text', 'rey-core' ),
				'drop' => esc_html_x( 'Drop-down list', 'Customizer control text', 'rey-core' ),
			],
			'active_callback' => [
				[
					'setting'  => 'loop_switcher_ppp',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$section->end_controls_accordion();

	}

}
