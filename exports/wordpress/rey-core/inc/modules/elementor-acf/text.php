<?php
namespace ReyCore\Modules\ElementorAcf;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Text extends \Elementor\Widget_Base {

	public function get_name() {
		return 'reycore-acf-text';
	}

	public function get_title() {
		return __( 'Text', 'rey-core' );
	}

	public function get_icon() {
		return 'rey-editor-icons --acf-text';
	}

	public function get_categories() {
		return [ 'rey-acf' ];
	}

	public function get_keywords() {
		return [ 'acf', 'text' ];
	}

	public function show_in_panel() {
		return class_exists('\ACF') && (bool) reycore__get_purchase_code();
	}

	// public function get_custom_help_url() {
	// 	return '';
	// }

	protected function register_controls() {

		$this->start_controls_section(
			'section_editor',
			[
				'label' => __( 'Settings', 'rey-core' ),
			]
		);

			$this->add_control(
				'acf_field',
				[
					'label' => esc_html__( 'Select Text Field', 'rey-core' ),
					'description' => esc_html__( 'Choose the field to pull.', 'rey-core' ),
					'default' => '',
					'label_block' => true,
					'type' => 'rey-query',
					'query_args' => [
						'type' => 'acf',
						'format' => '%key%:%name%',
						'field_types' => [
							'text',
							'textarea',
							'number',
							'email',
							'wysiwyg',
						],
					],
				]
			);

			$this->add_control(
				'fallback',
				[
					'label' => esc_html__( 'Add fallback?', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

			$this->add_control(
				'fallback_text',
				[
					'label' => 'Fallback text',
					'type' => \Elementor\Controls_Manager::WYSIWYG,
					'default' => '<p>' . __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'rey-core' ) . '</p>',
					'condition' => [
						'fallback!' => '',
					],
				]
			);

			$text_columns = range( 1, 10 );
			$text_columns = array_combine( $text_columns, $text_columns );
			$text_columns[''] = __( 'Default', 'rey-core' );

			$this->add_responsive_control(
				'text_columns',
				[
					'label' => __( 'Columns', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'separator' => 'before',
					'options' => $text_columns,
					'selectors' => [
						'{{WRAPPER}}' => 'columns: {{VALUE}};',
					],
				]
			);

			$this->add_responsive_control(
				'column_gap',
				[
					'label' => __( 'Columns Gap', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', '%', 'em', 'vw' ],
					'range' => [
						'px' => [
							'max' => 100,
						],
						'%' => [
							'max' => 10,
							'step' => 0.1,
						],
						'vw' => [
							'max' => 10,
							'step' => 0.1,
						],
						'em' => [
							'max' => 10,
							'step' => 0.1,
						],
					],
					'selectors' => [
						'{{WRAPPER}}' => 'column-gap: {{SIZE}}{{UNIT}};',
					],
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'show_label' => false,
			]
		);

			$this->add_responsive_control(
				'align',
				[
					'label' => __( 'Alignment', 'elementor' ),
					'type' => \Elementor\Controls_Manager::CHOOSE,
					'options' => [
						'left' => [
							'title' => __( 'Left', 'elementor' ),
							'icon' => 'eicon-text-align-left',
						],
						'center' => [
							'title' => __( 'Center', 'elementor' ),
							'icon' => 'eicon-text-align-center',
						],
						'right' => [
							'title' => __( 'Right', 'elementor' ),
							'icon' => 'eicon-text-align-right',
						],
						'justify' => [
							'title' => __( 'Justified', 'elementor' ),
							'icon' => 'eicon-text-align-justify',
						],
					],
					'selectors' => [
						'{{WRAPPER}}' => 'text-align: {{VALUE}};',
					],
				]
			);

			$this->add_control(
				'text_color',
				[
					'label' => __( 'Text Color', 'elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'default' => '',
					'selectors' => [
						'{{WRAPPER}}' => 'color: {{VALUE}};',
					],
					'global' => [
						'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Colors::COLOR_TEXT,
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'typography',
					'global' => [
						'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Typography::TYPOGRAPHY_TEXT,
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Text_Shadow::get_type(),
				[
					'name' => 'text_shadow',
					'selector' => '{{WRAPPER}}',
				]
			);


		$this->end_controls_section();

	}

	protected function render() {

		$settings = $this->get_settings_for_display();

		$text = '';

		if( $settings['fallback'] !== '' ){
			$text = $settings['fallback_text'];
		}

		if( $acf_field = $settings['acf_field'] ){
			$text = Base::get_field($acf_field);
		}

		if( ! $text ){
			return;
		}

		echo $this->parse_text_editor( $text );
	}

}
