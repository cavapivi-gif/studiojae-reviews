<?php
namespace ReyCore\Modules\CustomTemplates;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WooPdpReviewsCount extends WooBase {

	public function get_name() {
		return 'reycore-woo-pdp-review-count';
	}

	public function get_title() {
		return __( 'Reviews Count (PDP)', 'rey-core' );
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

			$selectors['main'] = '{{WRAPPER}} .woocommerce-product-rating';
			$selectors['stars'] = '{{WRAPPER}} .star-rating';
			$selectors['stars_outline'] = '{{WRAPPER}} .star-rating > .rey-starsGroup';
			$selectors['link'] = '{{WRAPPER}} .woocommerce-review-link';

			$this->add_control(
				'stars_color',
				[
					'label' => esc_html__( 'Stars Color (Filled)', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selectors['stars'] => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'active_stars_color',
				[
					'label' => esc_html__( 'Stars Color (Outline)', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selectors['stars_outline'] => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'stars_spacing',
				[
					'label' => esc_html__( 'Stars spacing', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						$selectors['stars'] => '--star-rating-spacing: {{VALUE}}px',
					],
				]
			);


			$this->add_control(
				'link_display',
				[
					'label' => esc_html__( 'Link Display', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'inline-block',
					'options' => [
						'inline-block'  => esc_html__( 'Inline', 'rey-core' ),
						'block'  => esc_html__( 'Block', 'rey-core' ),
						'none'  => esc_html__( 'None (Hidden)', 'rey-core' ),
					],
					'selectors' => [
						$selectors['link'] => 'display: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'color',
				[
					'label' => esc_html__( 'Link Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selectors['link'] => 'color: {{VALUE}}',
					],
					'condition' => [
						'link_display!' => 'none',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'label' => esc_html__( 'Link typography', 'rey-core' ),
					'name' => 'typo',
					'selector' => $selectors['link'],
					'condition' => [
						'link_display!' => 'none',
					],
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

		woocommerce_template_single_rating();

	}

}
