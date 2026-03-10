<?php
namespace ReyCore\Modules\EstimatedDelivery;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class PdpElement extends \ReyCore\Modules\CustomTemplates\WooBase {

	public $_settings;

	public function get_name() {
		return 'reycore-woo-pdp-estimated-delivery';
	}

	public function get_title() {
		return __( 'Estimated Delivery (PDP)', 'rey-core' );
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
			'section_settings',
			[
				'label' => __( 'Settings', 'rey-core' ),
			]
		);

			$this->add_control(
				'customize_settings_notice',
				[
					'type' => \Elementor\Controls_Manager::RAW_HTML,
					'content_classes' => 'rey-raw-html',
					'raw' => sprintf( _x( '<a href="%s" target="_blank" class="__title-link">Customize Estimated delivery options<i class="eicon-editor-external-link"></i></a><br>Access Customizer > WooCommerce > Product page - Content to customize the display of this feature.', 'Elementor control label', 'rey-core' ), add_query_arg( ['autofocus[control]' => 'single_extras__estimated_delivery'], admin_url( 'customize.php' ) ) ),
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_styles',
			[
				'label' => __( 'Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'show_label' => false,
			]
		);

			$selectors = [
				'main' => '{{WRAPPER}} .rey-estimatedDelivery'
			];

			$this->add_control(
				'color',
				[
					'label' => esc_html__( 'Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selectors['main'] => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'typo',
					'selector' => $selectors['main'],
				]
			);

			$this->add_responsive_control(
				'alignment',
				[
					'label' => __( 'Alignment', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::CHOOSE,
					'options' => [
						'left'           => [
							'title'         => __( 'Left', 'rey-core' ),
							'icon'          => 'eicon-text-align-left',
						],
						'center'        => [
							'title'         => __( 'Center', 'rey-core' ),
							'icon'          => 'eicon-text-align-center',
						],
						'right'          => [
							'title'         => __( 'Right', 'rey-core' ),
							'icon'          => 'eicon-text-align-right',
						],
					],
					'default' => '',
					'selectors' => [
						$selectors['main'] => 'text-align: {{VALUE}};',
					],
				]
			);

		$this->end_controls_section();

	}

	function render_template() {

		$this->_settings = $this->get_settings_for_display();

		$args = [];

		Base::instance()->display($args);

	}

}
