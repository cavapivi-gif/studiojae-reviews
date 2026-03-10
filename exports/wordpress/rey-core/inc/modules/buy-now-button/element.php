<?php
namespace ReyCore\Modules\BuyNowButton;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Element extends \ReyCore\Modules\CustomTemplates\WooBase {

	public function get_name() {
		return 'reycore-woo-pdp-buy-now';
	}

	public function get_title() {
		return __( 'Buy Now (PDP)', 'rey-core' );
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
	protected function register_controls() {

		$this->start_controls_section(
			'section_settings',
			[
				'label' => __( 'Settings', 'rey-core' ),
			]
		);

			$this->add_control(
				'button_text',
				[
					'label' => esc_html__( 'Button Text', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'placeholder' => esc_html__( 'eg: Buy Now', 'rey-core' ),
				]
			);

			$this->add_control(
				'customize_settings_notice',
				[
					'type' => \Elementor\Controls_Manager::RAW_HTML,
					'content_classes' => 'rey-raw-html',
					'raw' => sprintf( _x( '<a href="%s" target="_blank" class="__title-link">Customize Buy Now options<i class="eicon-editor-external-link"></i></a><br>Access Customizer > WooCommerce > Product page Components to customize the display of this feature.', 'Elementor control label', 'rey-core' ), add_query_arg( ['autofocus[control]' => 'buynow_pdp__enable'], admin_url( 'customize.php' ) ) ),
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
				'main' => '{{WRAPPER}} .rey-buyNowBtn-wrapper',
				'btn' => '{{WRAPPER}} .rey-buyNowBtn',
				'btn_hover' => '{{WRAPPER}} .rey-buyNowBtn:hover',
			];

			$this->add_control(
				'btn_style',
				[
					'type' => \Elementor\Controls_Manager::SELECT,
					'label'       => esc_html__( 'Button Style', 'rey-core' ),
					'default'     => '',
					'options'     => [
						'' => esc_html__( 'None', 'rey-core' ),
						'btn-line' => esc_html__( 'Underlined on hover', 'rey-core' ),
						'btn-line-active' => esc_html__( 'Underlined', 'rey-core' ),
						'btn-primary' => esc_html__( 'Regular', 'rey-core' ),
						'btn-primary-outline' => esc_html__( 'Regular outline', 'rey-core' ),
						'btn-secondary' => esc_html__( 'Secondary', 'rey-core' ),
					],
				]
			);

			$this->add_control(
				'color',
				[
					'label' => esc_html__( 'Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selectors['main'] => '--accent-text-color: {{VALUE}}; --link-color: {{VALUE}}; --body-color: {{VALUE}};',
					],
				]
			);

			$this->add_control(
				'bg_color',
				[
					'label' => esc_html__( 'Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selectors['main'] => '--accent-color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'color_hover',
				[
					'label' => esc_html__( 'Hover Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selectors['btn_hover'] => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'bg_color_hover',
				[
					'label' => esc_html__( 'Hover Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selectors['btn_hover'] => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'typo',
					'selector' => $selectors['btn'],
				]
			);


		$this->end_controls_section();

	}

	function render_template() {

		$this->_settings = $this->get_settings_for_display();

		add_filter('theme_mod_buynow_pdp__btn_text', [$this, 'button_text']);
		add_filter('theme_mod_buynow_pdp__btn_style', [$this, 'btn_style']);

		Base::instance()->display();

		remove_filter('theme_mod_buynow_pdp__btn_text', [$this, 'button_text']);
		remove_filter('theme_mod_buynow_pdp__btn_style', [$this, 'btn_style']);
	}

	function button_text($mod){

		if( $text = $this->_settings['button_text'] ){
			return $text;
		}

		return $mod;
	}

	function btn_style($mod){

		if( $style = $this->_settings['btn_style'] ){
			return $style;
		}

		return $mod;
	}

}
