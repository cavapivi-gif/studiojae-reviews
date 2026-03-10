<?php
namespace ReyCore\Modules\CustomTemplates;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WooPdpTitle extends WooBase {

	public function get_name() {
		return 'reycore-woo-pdp-title';
	}

	public function get_title() {
		return __( 'Title (PDP)', 'rey-core' );
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
			'section_styles',
			[
				'label' => __( 'Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'show_label' => false,
			]
		);

			$selectors['main'] = '{{WRAPPER}} .product_title';

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

		woocommerce_template_single_title();

	}

}
