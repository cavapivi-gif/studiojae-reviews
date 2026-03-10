<?php
namespace ReyCore\Modules\ElementorAcf;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Heading extends \Elementor\Widget_Base {

	public function get_name() {
		return 'reycore-acf-heading';
	}

	public function get_title() {
		return __( 'Heading', 'rey-core' );
	}

	public function get_icon() {
		return 'rey-editor-icons --acf-heading';
	}
	public function get_categories() {
		return [ 'rey-acf' ];
	}

	public function show_in_panel() {
		return class_exists('\ACF') && (bool) reycore__get_purchase_code();
	}

	public function get_keywords() {
		return [ 'acf', 'heading', 'title', 'text' ];
	}

	// public function get_custom_help_url() {
	// 	return '';
	// }

	protected function register_controls() {

		$this->start_controls_section(
			'section_title',
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
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => 'Lorem ipsum dolor sit amet',
					'label_block' => true,
					'condition' => [
						'fallback!' => '',
					],
				]
			);

			$this->add_control(
				'link',
				[
					'label' => __( 'Link', 'elementor' ),
					'type' => \Elementor\Controls_Manager::URL,
					'dynamic' => [
						'active' => true,
					],
					'default' => [
						'url' => '',
					],
					'separator' => 'before',
				]
			);

			$this->add_control(
				'link_acf_field',
				[
					'label' => esc_html__( 'ACF Link', 'rey-core' ),
					'description' => esc_html__( 'Choose the link field to pull.', 'rey-core' ),
					'default' => '',
					'label_block' => true,
					'type' => 'rey-query',
					'query_args' => [
						'type' => 'acf',
						'format' => '%key%:%name%',
						'field_types' => [
							'text',
							'url',
						],
					],
				]
			);


			$this->add_control(
				'size',
				[
					'label' => __( 'Size', 'elementor' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'default',
					'options' => [
						'default' => __( 'Default', 'elementor' ),
						'small' => __( 'Small', 'elementor' ),
						'medium' => __( 'Medium', 'elementor' ),
						'large' => __( 'Large', 'elementor' ),
						'xl' => __( 'XL', 'elementor' ),
						'xxl' => __( 'XXL', 'elementor' ),
					],
				]
			);

			$this->add_control(
				'header_size',
				[
					'label' => __( 'HTML Tag', 'elementor' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'options' => [
						'h1' => 'H1',
						'h2' => 'H2',
						'h3' => 'H3',
						'h4' => 'H4',
						'h5' => 'H5',
						'h6' => 'H6',
						'div' => 'div',
						'span' => 'span',
						'p' => 'p',
					],
					'default' => 'h2',
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
					'default' => '',
					'selectors' => [
						'{{WRAPPER}}' => 'text-align: {{VALUE}};',
					],
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_title_style',
			[
				'label' => __( 'Title', 'elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => __( 'Text Color', 'elementor' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'global' => [
					'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Colors::COLOR_PRIMARY,
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-heading-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'typography',
				'global' => [
					'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}} .elementor-heading-title',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Text_Shadow::get_type(),
			[
				'name' => 'text_shadow',
				'selector' => '{{WRAPPER}} .elementor-heading-title',
			]
		);

		$this->add_control(
			'blend_mode',
			[
				'label' => __( 'Blend Mode', 'elementor' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'' => __( 'Normal', 'elementor' ),
					'multiply' => 'Multiply',
					'screen' => 'Screen',
					'overlay' => 'Overlay',
					'darken' => 'Darken',
					'lighten' => 'Lighten',
					'color-dodge' => 'Color Dodge',
					'saturation' => 'Saturation',
					'color' => 'Color',
					'difference' => 'Difference',
					'exclusion' => 'Exclusion',
					'hue' => 'Hue',
					'luminosity' => 'Luminosity',
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-heading-title' => 'mix-blend-mode: {{VALUE}}',
				],
				'separator' => 'none',
			]
		);

		$this->end_controls_section();

	}

	protected function render() {

		$settings = $this->get_settings_for_display();

		$title = '';

		if( $settings['fallback'] !== '' ){
			$title = $settings['fallback_text'];
		}

		if( $acf_field = $settings['acf_field'] ){
			$title = Base::get_field($acf_field);
		}

		if( ! $title ){
			return;
		}

		$this->add_render_attribute( '_wrapper', 'class', 'elementor-widget-heading' );
		$this->add_render_attribute( 'title', 'class', 'elementor-heading-title' );

		if ( ! empty( $settings['size'] ) ) {
			$this->add_render_attribute( 'title', 'class', 'elementor-size-' . $settings['size'] );
		}

		if( $link_acf_field = $settings['link_acf_field'] ){
			$settings['link']['url'] = Base::get_field($link_acf_field);
		}

		if ( ! empty( $settings['link']['url'] ) ) {

			$this->add_link_attributes( 'url', $settings['link'] );

			$title = sprintf( '<a %1$s>%2$s</a>', $this->get_render_attribute_string( 'url' ), $title );
		}

		$title_html = sprintf( '<%1$s %2$s>%3$s</%1$s>',
			\Elementor\Utils::validate_html_tag( $settings['header_size'] ),
			$this->get_render_attribute_string( 'title' ),
			$title
		);

		echo $title_html;
	}

}
