<?php
namespace ReyCore\Modules\DiscountBadge;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Element extends \ReyCore\Modules\CustomTemplates\WooBase {

	public $_settings;

	public function get_name() {
		return 'reycore-woo-pdp-discount-label';
	}

	public function get_title() {
		return __( 'Discount Label (PDP)', 'rey-core' );
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
					'label' => esc_html__( 'Label type', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( 'Default', 'rey-core' ),
						'percentage'  => esc_html__( 'Percentage', 'rey-core' ),
						'save'  => esc_html__( 'Save $$', 'rey-core' ),
						'sale'  => esc_html__( 'Sale', 'rey-core' ),
					],
				]
			);

			$this->add_control(
				'sale_text',
				[
					'label' => esc_html__( 'Sale text', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'condition' => [
						'type' => 'sale',
					],
				]
			);

			$this->add_control(
				'save_text',
				[
					'label' => esc_html__( 'Save text', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'condition' => [
						'type' => 'save',
					],
				]
			);

			$this->add_control(
				'perc_text',
				[
					'label' => esc_html__( 'Percentage text', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'condition' => [
						'type' => 'percentage',
					],
				]
			);

		$this->end_controls_section();


		$selectors = [
			'main' => '{{WRAPPER}} .rey-discount',
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

			$this->add_control(
				'bg_color',
				[
					'label' => esc_html__( 'Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selectors['main'] => 'background-color: {{VALUE}}',
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
						'{{WRAPPER}}' => 'display: flex; justify-content: {{VALUE}};',
					],
				]
			);

			$this->add_responsive_control(
				'padding',
				[
					'label' => __( 'Padding', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%' ],
					'selectors' => [
						$selectors['main'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);


		$this->end_controls_section();

	}

	function label_type($type){

		if( $custom_type = $this->_settings['type'] ){
			return $custom_type;
		}

		return $type;
	}

	function save_text($text){

		if( $this->_settings['type'] === 'save' && ($custom_text = $this->_settings['save_text']) ){
			return $custom_text;
		}

		return $text;
	}

	function perc_text($text){

		if( $this->_settings['type'] === 'percentage' && ($custom_text = $this->_settings['perc_text']) ){
			return $custom_text;
		}

		return $text;
	}

	function woocommerce_sale_flash($html){

		$text = esc_html__( 'Sale!', 'woocommerce' );

		if( $this->_settings['type'] === 'sale' && ($custom_text = $this->_settings['sale_text']) ){
			$text = $custom_text;
		}

		return '<span class="rey-discount">' . $text . '</span>';
	}

	function render_template() {

		if( ! class_exists('\ReyCore\WooCommerce\Pdp') ){
			return;
		}

		$this->_settings = $this->get_settings_for_display();

		add_filter('theme_mod_single_discount_badge_v2', '__return_true');
		add_filter('theme_mod_loop_show_sale_label', [$this, 'label_type']);
		add_filter('theme_mod_loop_sale__save_text', [$this, 'save_text']);
		add_filter('reycore/woocommerce/discounts/percentage_html_text', [$this, 'perc_text']);

		if( $this->_settings['type'] === 'sale' ){

			add_filter('woocommerce_sale_flash', [$this, 'woocommerce_sale_flash']);
			echo woocommerce_show_product_loop_sale_flash();
			remove_filter('woocommerce_sale_flash', [$this, 'woocommerce_sale_flash']);

		}
		else {

			if( ($pdp = \ReyCore\Plugin::instance()->woocommerce_pdp) && ($c = $pdp->get_component('discount')) ){
				echo $c->discount_percentage('');
			}

		}

		remove_filter('theme_mod_single_discount_badge_v2', '__return_true');
		remove_filter('theme_mod_loop_show_sale_label', [$this, 'label_type']);
		remove_filter('theme_mod_loop_sale__save_text', [$this, 'save_text']);
		remove_filter('reycore/woocommerce/discounts/percentage_html_text', [$this, 'perc_text']);

	}

}
