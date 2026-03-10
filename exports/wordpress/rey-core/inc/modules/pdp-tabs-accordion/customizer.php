<?php
namespace ReyCore\Modules\PdpTabsAccordion;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Customizer {

	public function __construct(){
		add_action('reycore/customizer/section=woo-product-page-tabs', [$this, 'controls']);
	}

	public function controls( $section ){

		$section->add_title( esc_html__('Summary Accordion', 'rey-core'), [
			'description' => esc_html__('Display some of the product tabs in the Summary content, as an accordion (or tabs layout). *Note: Requires refresh if you\'ve just added custom tabs.', 'rey-core'),
		]);

		$section->add_control( [
			'type'        => 'select',
			'settings'    => 'single__accordion_layout',
			'label'       => esc_html__( 'Layout', 'rey-core' ),
			'default'     => 'acc',
			'choices'     => [
				'acc' => esc_html__( 'Accordions', 'rey-core' ),
				'tabs' => esc_html__( 'Tabs', 'rey-core' ),
			],
		] );


		$section->add_control( [
			'type'        => 'select',
			'settings'    => 'single__accordion_position',
			'label'       => esc_html__( 'Position', 'rey-core' ),
			'default'     => '39',
			'choices'     => [
				'39' => esc_html__( 'After Add to cart', 'rey-core' ),
				'45' => esc_html__( 'After Product Meta', 'rey-core' ),
				'100' => esc_html__( 'Summary End', 'rey-core' ),
			],
		] );

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'single__accordion_first_active',
			'label'       => esc_html__( 'First start opened', 'rey-core' ),
			'default'     => false,
			'active_callback' => [
				[
					'setting'  => 'single__accordion_layout',
					'operator' => '==',
					'value'    => 'acc',
					],
			],
		] );


		$section->add_control( [
			'type'        => 'repeater',
			'settings'    => 'single__accordion_items',
			'label'       => esc_html__('Add Accordion Items', 'rey-core'),
			'row_label' => [
				'value' => esc_html__('Item', 'rey-core'),
				'type'  => 'field',
				'field' => 'item',
			],
			'button_label' => esc_html__('New Item', 'rey-core'),
			'default'      => [],
			'fields' => [
				'item' => [
					'type'        => 'select',
					'label'       => esc_html__('Select item', 'rey-core'),
					'default'     => '',
					'choices'     => call_user_func( function() {

						$items = [
							'' => esc_html__('- Select -', 'rey-core'),
							'description' => esc_html__('Description', 'rey-core'),
							'short_desc' => esc_html__('Short Description', 'rey-core'),
							'information' => esc_html__('Information', 'rey-core'),
							'additional_information' => esc_html__('Additional Info / Specs.', 'rey-core'),
							'reviews' => esc_html__('Reviews', 'rey-core'),
						];

						$custom_tabs = get_theme_mod('single__custom_tabs', []);

						foreach ($custom_tabs as $key => $value) {
							$items[ 'custom_tab_' . $key] = $value['text'];
						}

						return $items;
					}),
				],

				'title' => [
					'type'        => 'text',
					'label'       => esc_html__('Custom Title', 'rey-core'),
					'default'       => '',
				]
			],
		] );


	}
}
