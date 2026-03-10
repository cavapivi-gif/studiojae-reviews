<?php
namespace ReyCore\Modules\ElementorAcf;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Table extends \Elementor\Widget_Base {

	public function get_name() {
		return 'reycore-acf-table';
	}

	public function get_title() {
		return __( 'Table (ACF)', 'rey-core' );
	}

	// public function get_icon() {
	// 	return $this->get_icon_class();
	// }

	public function get_categories() {
		return [ 'rey-acf' ];
	}

	public function show_in_panel() {
		return class_exists('\ACF') && (bool) reycore__get_purchase_code();
	}

	// public function get_custom_help_url() {
	// 	return '';
	// }

	protected function register_controls() {

		$this->start_controls_section(
			'section_table',
			[
				'label' => __( 'Settings', 'rey-core' ),
			]
		);

			$this->add_control(
				'acf_field',
				[
					'label' => esc_html__( 'Select Table Field', 'rey-core' ),
					'description' => esc_html__( 'Choose the field to pull. Please know that only text strings are supported', 'rey-core' ),
					'default' => '',
					'label_block' => true,
					'type' => 'rey-query',
					'query_args' => [
						'type'        => 'acf',
						'format'      => '%key%:%name%',
						'field_types' =>  [
							'table',
							'repeater',
						],
					],
				]
			);

			$this->add_control(
				'index',
				[
					'label' => esc_html_x( 'Repeater index', 'Elementor control', 'rey-core' ),
					'label' => esc_html_x( 'If the table belongs to a repeater, please select an index.', 'Elementor control', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 1,
					'min' => 1,
					'max' => 1000,
					'step' => 0,
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_table',
			[
				'label' => __( 'Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'show_label' => false,
			]
		);

			$this->add_control(
				'cell_color',
				[
					'label' => esc_html__( 'Cell Text Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} tbody tr' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'cell_background',
				[
					'label' => esc_html__( 'Cell Background color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} tbody tr td' => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'head_cell_color',
				[
					'label' => esc_html__( 'Head Cell Text Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} thead th' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'head_cell_background',
				[
					'label' => esc_html__( 'Head Cell Background color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} thead th' => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'alt_row_color',
				[
					'label' => esc_html__( 'Alternative row color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} tbody tr:nth-child(2n) td' => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'border_color',
				[
					'label' => esc_html__( 'Border color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} tbody tr:last-of-type td, {{WRAPPER}} thead th, {{WRAPPER}} tbody td' => 'border-color: {{VALUE}}',
					],
				]
			);

		$this->end_controls_section();

	}

	protected function render() {

		$settings = $this->get_settings_for_display();

		if( ! ($acf_field = $settings['acf_field']) ){
			return;
		}

		if( \Elementor\Plugin::$instance->editor->is_edit_mode() ){
			$GLOBALS['provider_post_id'] = [get_the_ID()];
		}

		$table_data = Base::get_field($acf_field, ($settings['index'] ? $settings['index'] : 1));

		if( ! $table_data ){
			return;
		}

		echo \ReyCore\ACF\Helper::get_table_html([
			'table' => $table_data,
			'css_class' => '--basic',
		]);

	}

}
