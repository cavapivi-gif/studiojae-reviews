<?php
namespace ReyCore\Modules\ViewSwitcher;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Customizer {

	public function __construct(){
		add_action('reycore/customizer/section=woo-catalog-grid-components/marker=components', [$this, 'controls']);
	}

	public function controls( $section ){

		$section->start_controls_accordion([
			'label'  => esc_html__( 'View Switcher', 'rey-core' ),
		]);

		$section->add_title( '', [
			'description' => esc_html__('Displays a switcher to change the "products per row" count.', 'rey-core'),
			'separator' => 'none'
		]);

		$section->add_control( [
			'type' => 'select',
			'settings' => 'loop_view_switcher',
			'label'       => esc_html__('Enable', 'rey-core'),
			'help' => [
				__('Choose if you want to display the products per row switcher.', 'rey-core')
			],
			'default' => '1',
			'choices' => [
				'1' => esc_attr__('Show', 'rey-core'),
				'2' => esc_attr__('Hide', 'rey-core'),
			],
		]);

		$section->add_control( [
			'type' => 'text',
			'settings' => 'loop_view_switcher_options',
			'label'       => esc_html__('View Switcher - Columns', 'rey-core'),
			'help' => [
				esc_html__('Add columns variations, separated by comma, up to 6 columns.', 'rey-core')
			],
			'default' => esc_html__('2, 3, 4', 'rey-core'),
			'placeholder' => esc_html__('eg: 2, 3, 4', 'rey-core'),
			'active_callback' => [
				[
					'setting' => 'loop_view_switcher',
					'operator' => '==',
					'value' => '1',
				],
			],
			'input_attrs' => [
				'data-control-class' => '--text-md',
			],
		]);

		$section->add_control( [
			'type' => 'toggle',
			'settings' => 'loop_view_switcher_mobile',
			'label'       => esc_html__('Enable on mobiles', 'rey-core'),
			'help' => [
				esc_html__('Will display a simple icons selector of either 1 or 2 columns.', 'rey-core')
			],
			'default' => false,
			'active_callback' => [
				[
					'setting' => 'loop_view_switcher',
					'operator' => '==',
					'value' => '1',
				],
			],
		]);

		$section->end_controls_accordion();

	}
}
