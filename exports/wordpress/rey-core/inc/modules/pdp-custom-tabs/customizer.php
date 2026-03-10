<?php
namespace ReyCore\Modules\PdpCustomTabs;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Customizer {

	public function __construct(){
		add_action('reycore/customizer/section=woo-product-page-tabs', [$this, 'controls']);
	}

	public function controls( $section ){

		$section->add_title( esc_html__('Custom Tabs', 'rey-core'), [
			'description' => esc_html__('Add extra tabs (blocks). Content can be edited in product page settings.', 'rey-core'),
		]);

		$section->add_control( [
			'type'        => 'repeater',
			'settings'    => 'single__custom_tabs',
			'label'       => esc_html__('Add Custom tabs', 'rey-core'),
			'row_label' => [
				'value' => esc_html__('Tab', 'rey-core'),
				'type'  => 'field',
				'field' => 'text',
			],
			'button_label' => esc_html__('New Tab', 'rey-core'),
			'default'      => [],
			'fields' => [
				'text' => [
					'type'        => 'text',
					'label'       => esc_html__('Title', 'rey-core'),
					'default'       => '',
					'input_attrs'     => [
						'placeholder' => esc_html__('eg: Tab title', 'rey-core'),
					],
				],
				'priority' => [
					'type'        => 'text',
					'label'       => esc_html__('Priority', 'rey-core'),
					'default'       => 40,
				],
				'content' => [
					'type'        => 'textarea',
					'label'       => esc_html__('Default Content', 'rey-core'),
					'default'       => '',
				],
				'uid' => [
					'type'        => 'text',
					'label'       => esc_html__('Unique ID', 'rey-core'),
					'default'     => '',
					'input_attrs' => [
						'readonly'   => 'readonly',
						'data-hash'   => '',
					],
				],
			],
		] );


	}
}
