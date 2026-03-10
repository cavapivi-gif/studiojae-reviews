<?php
namespace ReyCore\Customizer\Options\General;

if ( ! defined( 'ABSPATH' ) ) exit;

use \ReyCore\Customizer\Controls;

class Typography extends \ReyCore\Customizer\SectionsBase {

	public static function get_id(){
		return 'general-typography';
	}

	public function get_title(){
		return esc_html__('Site Typography', 'rey-core');
	}

	public function get_priority(){
		return 15;
	}

	public function get_icon(){
		return 'site-typography';
	}

	public function help_link(){
		return reycore__support_url('kb/customizer-general-settings/#typography-settings');
	}

	public function controls(){

		/**
		 * Default
		 */
		$rey_attributes_selectors = [
			'h1' => 'h1, .h1, .rey-pageTitle, .rey-postItem-catText',
			'h2' => 'h2, .h2',
			'h3' => 'h3, .h3',
			'h4' => 'h4, .h4',
			'h5' => 'h5, .h5',
			'h6' => 'h6, .h6',
		];

		$this->add_control([
			'type'        => 'typography',
			'settings'    => 'typography_primary',
			'label'       => esc_html__('PRIMARY FONT', 'rey-core'),
			'default'     => [
				'font-family' => '',
			],
			'load_choices' => 'exclude',
			'input_attrs' => [
				'data-should-collapse' => 'no'
			]
		]);

		$this->add_control( [
			'type'        => 'typography',
			'settings'    => 'typography_secondary',
			'label'       => esc_html__('SECONDARY FONT', 'rey-core'),
			'default'     => [
				'font-family' => '',
			],
			'load_choices' => 'exclude',
			'input_attrs' => [
				'data-should-collapse' => 'no'
			]
		]);


		/* ------------------------------------ Typo settings ------------------------------------ */

		$this->add_title( esc_html__('Typography settings', 'rey-core') );

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'typography_inherit_elementor',
			'label'       => esc_html_x('Inherit Typography from Elementor Site Settings', 'Customizer control title', 'rey-core'),
			'help' => [
				sprintf( _x('If enabled, the Typography settings will be inherited from Elementor Site Settings. <a href="%s" target="_blank">Learn more</a>.', 'Customizer control description', 'rey-core'), 'https://elementor.com/help/global-layout-settings/'),
				'size'      => 290,
				'clickable' => true
			],
			'default'     => false,
		] );

		/**
		 * Body Font
		 */
		$this->add_control( array(
			'type'        => 'typography',
			'settings'    => 'typography_body',
			'label'       => esc_attr__('Site typography', 'rey-core'),
			'description' => __('Site typography settings.', 'rey-core'),
			'default'     => array(
				'font-family' => 'var(--primary-ff)',
				'font-size'   => '',
				'line-height' => '',
				'variant' => '',
				'font-weight' => '',
			),
			'output'      => [
				[
					'element'  => reycore__wp_editor_selector(),
					'property' => 'font-family',
					'context'  => [ 'editor' ],
				],
			],
			'load_choices' => true,
			'responsive' => true,
			'active_callback' => [
				[
					'setting'  => 'typography_inherit_elementor',
					'operator' => '!=',
					'value'    => true,
					],
			],
		));


		/**
		 * Headings
		 */

		$this->add_control( array(
			'type'        => 'typography',
			'settings'    => 'typography_heading1',
			'label'       => esc_attr__('Heading 1', 'rey-core'),
			'transport' => 'auto',
			'default'     => array(
				'font-family'      => '',
				'font-size'      => '',
				'line-height'    => '',
				'letter-spacing' => '',
				'text-transform' => '',
				'variant' => '',
				'font-weight' => '',
			),
			'output' => array(
				array(
					'element' => $rey_attributes_selectors['h1'],
				),
				[
					'element'  => sprintf('%1$s h%2$d, %1$s .h%2$s', reycore__wp_editor_selector(), 1),
					'property' => 'font-family',
					'context'  => [ 'editor' ],
				],
			),
			'load_choices' => true,
			'responsive' => true,
			'active_callback' => [
				[
					'setting'  => 'typography_inherit_elementor',
					'operator' => '!=',
					'value'    => true,
					],
			],
		));

		$this->add_control( array(
			'type'        => 'typography',
			'settings'    => 'typography_heading2',
			'label'       => esc_attr__('Heading 2', 'rey-core'),
			'transport' => 'auto',
			'default'     => array(
				'font-family'      => '',
				'font-size'      => '',
				'line-height'    => '',
				'letter-spacing' => '',
				'text-transform' => '',
				'variant' => '',
				'font-weight' => '',
			),
			'output' => array(
				array(
					'element' => $rey_attributes_selectors['h2'],
				),
				[
					'element'  => sprintf('%1$s h%2$d, %1$s .h%2$s', reycore__wp_editor_selector(), 2),
					'property' => 'font-family',
					'context'  => [ 'editor' ],
				],
			),
			'load_choices' => true,
			'responsive' => true,
			'active_callback' => [
				[
					'setting'  => 'typography_inherit_elementor',
					'operator' => '!=',
					'value'    => true,
					],
			],
		));

		$this->add_control( array(
			'type'        => 'typography',
			'settings'    => 'typography_heading3',
			'label'       => esc_attr__('Heading 3', 'rey-core'),
			'transport' => 'auto',
			'default'     => array(
				'font-family'      => '',
				'font-size'      => '',
				'line-height'    => '',
				'letter-spacing' => '',
				'text-transform' => '',
				'variant' => '',
				'font-weight' => '',
			),
			'output' => array(
				array(
					'element' => $rey_attributes_selectors['h3'],
				),
				[
					'element'  => sprintf('%1$s h%2$d, %1$s .h%2$s', reycore__wp_editor_selector(), 3),
					'property' => 'font-family',
					'context'  => [ 'editor' ],
				],
			),
			'load_choices' => true,
			'responsive' => true,
			'active_callback' => [
				[
					'setting'  => 'typography_inherit_elementor',
					'operator' => '!=',
					'value'    => true,
					],
			],
		));

		$this->add_control( array(
			'type'        => 'typography',
			'settings'    => 'typography_heading4',
			'label'       => esc_attr__('Heading 4', 'rey-core'),
			'transport' => 'auto',
			'default'     => array(
				'font-family'      => '',
				'font-size'      => '',
				'line-height'    => '',
				'letter-spacing' => '',
				'text-transform' => '',
				'variant' => '',
				'font-weight' => '',
			),
			'output' => array(
				array(
					'element' => $rey_attributes_selectors['h4'],
				),
				[
					'element'  => sprintf('%1$s h%2$d, %1$s .h%2$s', reycore__wp_editor_selector(), 4),
					'property' => 'font-family',
					'context'  => [ 'editor' ],
				],
			),
			'load_choices' => true,
			'responsive' => true,
			'active_callback' => [
				[
					'setting'  => 'typography_inherit_elementor',
					'operator' => '!=',
					'value'    => true,
					],
			],
		));


		$this->add_control( array(
			'type'        => 'typography',
			'settings'    => 'typography_heading5',
			'label'       => esc_attr__('Heading 5', 'rey-core'),
			'transport' => 'auto',
			'default'     => array(
				'font-family'      => '',
				'font-size'      => '',
				'line-height'    => '',
				'letter-spacing' => '',
				'text-transform' => '',
				'variant' => '',
				'font-weight' => '',
			),
			'output' => array(
				array(
					'element' => $rey_attributes_selectors['h5'],
				),
				[
					'element'  => sprintf('%1$s h%2$d, %1$s .h%2$s', reycore__wp_editor_selector(), 5),
					'property' => 'font-family',
					'context'  => [ 'editor' ],
				],
			),
			'load_choices' => true,
			'responsive' => true,
			'active_callback' => [
				[
					'setting'  => 'typography_inherit_elementor',
					'operator' => '!=',
					'value'    => true,
					],
			],
		));

		$this->add_control( array(
			'type'        => 'typography',
			'settings'    => 'typography_heading6',
			'label'       => esc_attr__('Heading 6', 'rey-core'),
			'transport' => 'auto',
			'default'     => array(
				'font-family'      => '',
				'font-size'      => '',
				'line-height'    => '',
				'letter-spacing' => '',
				'text-transform' => '',
				'variant' => '',
				'font-weight' => '',
			),
			'output' => array(
				array(
					'element' => $rey_attributes_selectors['h6'],
				),
				[
					'element'  => sprintf('%1$s h%2$d, %1$s .h%2$s', reycore__wp_editor_selector(), 6),
					'property' => 'font-family',
					'context'  => [ 'editor' ],
				],
			),
			'load_choices' => true,
			'responsive' => true,
			'active_callback' => [
				[
					'setting'  => 'typography_inherit_elementor',
					'operator' => '!=',
					'value'    => true,
					],
			],
		));

		$this->add_control( array(
			'type'        => 'typography',
			'settings'    => 'typography_page_title',
			'label'       => esc_attr__('Page Titles', 'rey-core'),
			'transport' => 'auto',
			'default'     => array(
				'font-family'      => '',
				'font-size'      => '',
				'line-height'    => '',
				'letter-spacing' => '',
				'text-transform' => '',
				'variant' => '',
				'font-weight' => '',
			),
			'output' => [
				[
					'element' => '.rey-pageTitle',
				]
			],
			'load_choices' => true,
			'responsive' => true,
			'active_callback' => [
				[
					'setting'  => 'typography_inherit_elementor',
					'operator' => '!=',
					'value'    => true,
					],
			],
		));

	}
}
