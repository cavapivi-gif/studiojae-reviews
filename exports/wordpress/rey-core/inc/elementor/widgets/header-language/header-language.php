<?php
namespace ReyCore\Elementor\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if( ! reycore__is_multilanguage() ){
	return;
}

class HeaderLanguage extends \ReyCore\Elementor\WidgetsBase {

	public static function get_rey_config(){
		return [
			'id' => 'header-language',
			'title' => __( 'Language Switcher', 'rey-core' ),
			'icon' => 'eicon-select',
			'categories' => [ 'rey-header' ],
			'keywords' => ['wpml', 'polylang', 'language'],
		];
	}

	public function get_custom_help_url() {
		return reycore__support_url('kb/rey-elements-header/#language-switcher');
	}

	/**
	 * Register widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'section_settings',
			[
				'label' => __( 'Settings', 'rey-core' ),
			]
		);

		$this->add_control(
			'layout',
			[
				'label' => esc_html__( 'Layout', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'dropdown',
				'options' => [
					'dropdown'  => esc_html__( 'Dropdown', 'rey-core' ),
					'list'  => esc_html__( 'Inline List', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'trigger',
			[
				'label' => esc_html__( 'Open Trigger', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'click',
				'options' => [
					'click'  => esc_html__( 'Click', 'rey-core' ),
					'hover'  => esc_html__( 'Hover', 'rey-core' ),
				],
				'prefix_class' => '--dp-',
				'condition' => [
					'layout' => 'dropdown',
				],
			]
		);

		$this->add_control(
			'show_flags',
			[
				'label' => esc_html__( 'Show Flags', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_active_flag',
			[
				'label' => esc_html__( 'Show Active Flag', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [
					'layout' => 'dropdown',
				],
			]
		);

		$this->add_control(
			'show_short_text',
			[
				'label' => esc_html__( 'Text display', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => esc_html__( 'Default', 'rey-core' ),
					'yes'  => esc_html__( 'Language Code', 'rey-core' ),
					'no'  => esc_html__( 'No text', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'show_mobile',
			[
				'label' => esc_html__( 'Show on mobiles', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'return_value' => '--show-mobile',
				'prefix_class' => ''
			]
		);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_styles',
			[
				'label' => __( 'Dropdown styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'layout' => 'dropdown',
				],
			]
		);

		$this->add_control(
			'active_text_color',
			[
				'label' => esc_html__( 'Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-headerIcon-btn' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'active_typo',
				'selector' => '{{WRAPPER}} .rey-headerIcon-btn',
			]
		);


		$this->add_control(
			'drop_text_color',
			[
				'label' => esc_html__( 'Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-headerDropSwitcher ul li a span' => 'color: {{VALUE}}',
				],
				'separator' => 'before'
			]
		);

		$this->add_control(
			'drop_bg_color',
			[
				'label' => esc_html__( 'Background Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-header-dropPanel .rey-header-dropPanel-content:before' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'drop_typo',
				'selector' => '{{WRAPPER}} .rey-headerDropSwitcher ul li a span',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_list_styles',
			[
				'label' => __( 'List styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'layout' => 'list',
				],
			]
		);

		$this->add_control(
			'list_text_color',
			[
				'label' => esc_html__( 'Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-langSwitcher ul a' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'hover_list_text_color',
			[
				'label' => esc_html__( 'Hover Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-langSwitcher ul li a:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'active_list_text_color',
			[
				'label' => esc_html__( 'Active Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-langSwitcher ul li.--active a' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'list_typo',
				'selector' => '{{WRAPPER}} .rey-langSwitcher ul a',
			]
		);

		$this->add_responsive_control(
			'list_distance',
			[
				'label' => esc_html__( 'Items Distance', 'rey-core' ) . ' (em)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0.1,
				'max' => 10,
				'step' => 0.1,
				'selectors' => [
					'{{WRAPPER}} .rey-langSwitcher' => '--distance: {{VALUE}}em',
				],
			]
		);

		$this->end_controls_section();

	}

	/**
	 * Render widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {

		do_action('reycore/elementor/header_language/render', $this->get_settings_for_display() );

	}

	/**
	 * Render widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function content_template() {}
}
