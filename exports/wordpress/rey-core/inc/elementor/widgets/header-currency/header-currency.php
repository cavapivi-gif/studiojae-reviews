<?php
namespace ReyCore\Elementor\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class HeaderCurrency extends \ReyCore\Elementor\WidgetsBase {

	public static function get_rey_config(){
		return [
			'id' => 'header-currency',
			'title' => __( 'Currency Switcher', 'rey-core' ),
			'icon' => 'eicon-select',
			'categories' => [ 'rey-header' ],
			'keywords' => ['currency', 'woocs', 'switcher', 'aelia'],
		];
	}

	public function get_custom_help_url() {
		return reycore__support_url('kb/rey-elements-header/#currency-switcher');
	}

	public function rey_get_script_depends() {
		return [ 'reycore-woocommerce' ];
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

		if( class_exists('\Aelia_WC_TaxDisplayByCountry_RequirementsChecks') ):

			$this->add_control(
				'aelia_show_countries',
				[
					'label' => esc_html__( 'Show Countries (Aelia)', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

		endif;

		$this->add_control(
			'symbol',
			[
				'label' => esc_html__( 'Currency Symbol', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'yes',
				'options' => [
					'yes'  => esc_html__( 'Show symbol', 'rey-core' ),
					'first'  => esc_html__( 'Show symbol first', 'rey-core' ),
					'no'  => esc_html__( 'No symbol', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'always_show_caret',
			[
				'label' => esc_html__( 'Always show caret', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [
					'layout' => 'dropdown',
				],
			]
		);


		$this->add_control(
			'show_mobile',
			[
				'label' => esc_html__( 'Show on mobiles', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [
					'layout' => 'dropdown',
				],
			]
		);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_styles',
			[
				'label' => __( 'Style', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'layout' => 'dropdown',
				],
			]
		);

		$this->add_control(
			'text_color',
			[
				'label' => __( 'Button Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-woocurrency .rey-headerIcon-btn' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'hover_text_color',
			[
				'label' => __( 'Button Hover Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-woocurrency .rey-headerIcon-btn:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'typography',
				'label' => __( 'Button Typography', 'rey-core' ),
				'selector' => '{{WRAPPER}} .rey-woocurrency .rey-headerIcon-btn',
			]
		);

		$this->add_control(
			'panel_text_color',
			[
				'label' => __( 'Panel Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-woocurrency .rey-woocurrency-item' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'panel_hover_text_color',
			[
				'label' => __( 'Panel Hover Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-woocurrency .rey-woocurrency-item:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'panel_typography',
				'label' => __( 'Panel Typography', 'rey-core' ),
				'selector' => '{{WRAPPER}} .rey-woocurrency .rey-woocurrency-item',
			]
		);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_list_styles',
			[
				'label' => __( 'List Style', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'layout' => 'list',
				],
			]
		);


		$this->add_control(
			'list_text_color',
			[
				'label' => __( 'Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-woocurrency .rey-woocurrency-item' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'list_hover_text_color',
			[
				'label' => __( 'Hover Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-woocurrency .rey-woocurrency-item:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'list_active_text_color',
			[
				'label' => __( 'Active Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-woocurrency li.--active .rey-woocurrency-item' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'list_typography',
				'label' => __( 'Typography', 'rey-core' ),
				'selector' => '{{WRAPPER}} .rey-woocurrency .rey-woocurrency-item',
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
					'{{WRAPPER}} .rey-woocurrency' => '--distance: {{VALUE}}em',
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

		if(
			class_exists( '\WOOCS_STARTER' ) ||
			class_exists( '\WOOMULTI_CURRENCY_F' ) ||
			class_exists( '\WOOMULTI_CURRENCY' ) ||
			class_exists( '\WC_Aelia_CurrencySwitcher' ) ||
			class_exists( '\woocommerce_wpml' )
		) {

			do_action('reycore/elements/header_currency/render', $this );

		}

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
