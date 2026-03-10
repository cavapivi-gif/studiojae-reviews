<?php
namespace ReyCore\Modules\ScheduledSales;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Element extends \ReyCore\Modules\CustomTemplates\WooBase {

	public $_settings;

	public function get_name() {
		return 'reycore-woo-pdp-scheduled-sale';
	}

	public function get_title() {
		return __( 'Scheduled Sale (PDP)', 'rey-core' );
	}

	public function get_icon() {
		return $this->get_icon_class();
	}

	public function get_categories() {
		return [ 'rey-woocommerce-pdp' ];
	}

	public function show_in_panel() {
		return $this->maybe_show_in_panel();
	}

	// public function get_custom_help_url() {
	// 	return '';
	// }

	/**
	 * Register widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function element_register_controls() {

		$this->start_controls_section(
			'section_layout',
			[
				'label' => __( 'Layout', 'rey-core' ),
			]
		);

		$this->add_control(
			'type',
			[
				'label' => esc_html__( 'Type', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'badge',
				'options' => [
					'badge' => esc_html_x( 'Text Badge', 'Customizer control text', 'rey-core' ),
					'countdown' => esc_html_x( 'Countdown', 'Customizer control text', 'rey-core' ),
					'inline-countdown' => esc_html_x( 'Countdown Inline', 'Customizer control text', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'text',
			[
				'label' => esc_html__( 'Text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => esc_html_x('eg: Ends in', 'Customizer control text', 'rey-core'),
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_styles',
			[
				'label' => __( 'Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'text_color',
			[
				'label' => esc_html__( 'Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => '--sch-sale-pdp-text-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'bg_color',
			[
				'label' => esc_html__( 'Background Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => '--sch-sale-pdp-bg-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'stretch',
			[
				'label' => esc_html__( 'Stretch', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''    => esc_html__( '- Inherit -', 'rey-core' ),
					'yes' => esc_html__( 'Yes', 'rey-core' ),
					'no'  => esc_html__( 'No', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'center',
			[
				'label' => esc_html__( 'Align Center', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''    => esc_html__( '- Inherit -', 'rey-core' ),
					'yes' => esc_html__( 'Yes', 'rey-core' ),
					'no'  => esc_html__( 'No', 'rey-core' ),
				],
			]
		);

		$this->end_controls_section();

	}

	function render_template() {

		if( ! class_exists('\ReyCore\WooCommerce\Pdp') ){
			return;
		}

		$this->_settings = $this->get_settings_for_display();

		Base::instance()->render([
			'position' => 'custom-template',
			'type'     => $this->_settings['type'],
			'text'     => $this->_settings['text'] ? $this->_settings['text'] : get_theme_mod('scheduled_sale__pdp_text', ''),
			'bg'       => (bool) ($this->_settings['bg_color'] ? $this->_settings['bg_color'] : get_theme_mod('scheduled_sale__pdp_bg_color', '')),
			'stretch'  => $this->_settings['stretch'] ? $this->_settings['stretch'] === 'yes' : get_theme_mod('scheduled_sale__pdp_stretch', false),
			'center'   => $this->_settings['center'] ? $this->_settings['center'] === 'yes' : get_theme_mod('scheduled_sale__pdp_center', false),
		]);

	}

}
