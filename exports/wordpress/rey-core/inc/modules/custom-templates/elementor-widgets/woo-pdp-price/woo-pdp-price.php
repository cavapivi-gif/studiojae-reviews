<?php
namespace ReyCore\Modules\CustomTemplates;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WooPdpPrice extends WooBase {

	public function get_name() {
		return 'reycore-woo-pdp-price';
	}

	public function get_title() {
		return __( 'Price (PDP)', 'rey-core' );
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

		$selectors = [
			'main' => '{{WRAPPER}} p.price',
			'discount' => '{{WRAPPER}} p.price .rey-discount'
		];

		$this->start_controls_section(
			'section_styles',
			[
				'label' => __( 'Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'show_label' => false,
			]
		);

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
						'flex-start'           => [
							'title'         => __( 'Start', 'rey-core' ),
							'icon'          => 'eicon-text-align-left',
						],
						'center'        => [
							'title'         => __( 'Center', 'rey-core' ),
							'icon'          => 'eicon-text-align-center',
						],
						'flex-end'          => [
							'title'         => __( 'End', 'rey-core' ),
							'icon'          => 'eicon-text-align-right',
						],
					],
					'default' => '',
					'selectors' => [
						$selectors['main'] => 'justify-content: {{VALUE}};',
					],
				]
			);

			$this->add_control(
				'discount_label_title',
				[
				   'label' => esc_html__( 'Sale/Discount Label', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_control(
				'discount_label_show',
				[
					'label' => esc_html__( 'Show discount label?', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
				]
			);

			$this->add_control(
				'discount_color',
				[
					'label' => esc_html__( 'Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selectors['discount'] => 'color: {{VALUE}}',
					],
					'condition' => [
						'discount_label_show!' => '',
					],
				]
			);

			$this->add_control(
				'discount_bg_color',
				[
					'label' => esc_html__( 'Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selectors['discount'] => 'background-color: {{VALUE}}',
					],
					'condition' => [
						'discount_label_show!' => '',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'discount_typo',
					'selector' => $selectors['discount'],
					'condition' => [
						'discount_label_show!' => '',
					],
				]
			);

			$this->add_responsive_control(
				'discount_padding',
				[
					'label' => __( 'Padding', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%' ],
					'selectors' => [
						$selectors['discount'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
					'condition' => [
						'discount_label_show!' => '',
					],
				]
			);

			$this->add_control(
				'discount_spacing',
				[
					'label' => esc_html__( 'Spacing', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						$selectors['discount'] => '--woocommerce-discount-spacing: {{VALUE}}px',
					],
					'condition' => [
						'discount_label_show!' => '',
					],
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_other_settings_info',
			[
				'label' => __( 'Other Settings', 'rey-core' ),
				'tab' => 'info',
			]
		);

			$this->add_control(
				'other_settings_info_notice',
				[
					'type' => \Elementor\Controls_Manager::RAW_HTML,
					'content_classes' => 'rey-raw-html --em',
					'raw' => 'These settings below can only be changed globally in Customizer.',
				]
			);


			$this->add_control(
				'single_product_price__variations',
				[
					'type' => \Elementor\Controls_Manager::RAW_HTML,
					'content_classes' => 'rey-raw-html',
					'raw' => sprintf( _x( '<a href="%s" target="_blank" class="__title-link">Change main price in Variable products<i class="eicon-editor-external-link"></i></a><br> Enable changing this main price for variable products, when selecting variations.', 'Elementor control label', 'rey-core' ), add_query_arg( ['autofocus[control]' => 'single_product_price__variations'], admin_url( 'customize.php' ) ) ),
				]
			);

			//
			$this->add_control(
				'single_product_price__show_total',
				[
					'type' => \Elementor\Controls_Manager::RAW_HTML,
					'content_classes' => 'rey-raw-html',
					'raw' => sprintf( _x( '<a href="%s" target="_blank" class="__title-link">Display Total (based on Quantity)<i class="eicon-editor-external-link"></i></a><br> Enable a total price, based on quantity.', 'Elementor control label', 'rey-core' ), add_query_arg( ['autofocus[control]' => 'single_product_price__show_total'], admin_url( 'customize.php' ) ) ),
				]
			);

			$this->add_control(
				'single_product_price__instalments',
				[
					'type' => \Elementor\Controls_Manager::RAW_HTML,
					'content_classes' => 'rey-raw-html',
					'raw' => sprintf( _x( '<a href="%s" target="_blank" class="__title-link">Show price installments <i class="eicon-editor-external-link"></i></a><br>Display an automatically calculated text that shows the price split in pre-defined installments.', 'Elementor control label', 'rey-core' ), add_query_arg( ['autofocus[control]' => 'single_product_price__instalments'], admin_url( 'customize.php' ) ) ),
				]
			);



		$this->end_controls_section();

	}

	public function price_discount($html, $product = null){

		// recursion protection
		// This is needed to prevent infinite recursion when the discount component is used
		// in the price_discount method, which is called by the woocommerce_get_price_html filter.
		static $in_progress = false;

		if ( $in_progress ) return $html;

		$in_progress = true;

		if( ($pdp = \ReyCore\Plugin::instance()->woocommerce_pdp) && ($c = $pdp->get_component('discount')) ){
			return $c->discount_percentage($html, $product);
		}

		$in_progress = false;

		return $html;
	}

	function render_template() {

		$show_discount = $this->get_settings_for_display('discount_label_show') !== '' && class_exists('\ReyCore\WooCommerce\Pdp');

		if( $show_discount ){
			add_filter( 'woocommerce_get_price_html', [ $this, 'price_discount' ], 100, 2);
		}

		woocommerce_template_single_price();

		if( $show_discount ){
			remove_filter( 'woocommerce_get_price_html', [ $this, 'price_discount' ], 100);
		}

	}

}
