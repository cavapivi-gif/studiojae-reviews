<?php
namespace ReyCore\Modules\CustomTemplates;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WooPdpShortDesc extends WooBase {

	public function get_name() {
		return 'reycore-woo-pdp-short-desc';
	}

	public function get_title() {
		return __( 'Short Description (PDP)', 'rey-core' );
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
				'toggle_text',
				[
					'label' => esc_html__( 'Toggle Text', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

			$this->add_control(
				'toggle_text_strip_tags',
				[
					'label' => esc_html__( 'Strip Tags', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
					'condition' => [
						'toggle_text!' => '',
					],
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

			$selector = '{{WRAPPER}} .woocommerce-product-details__short-description';


			$this->add_control(
				'color',
				[
					'label' => esc_html__( 'Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selector => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'typo',
					'selector' => $selector,
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
						$selector => 'text-align: {{VALUE}};',
					],
				]
			);


		$this->end_controls_section();

	}

	function toggle_text($status){
		return $this->get_settings_for_display('toggle_text') !== '';
	}

	function toggle_text_strip_tags($status){
		return $this->get_settings_for_display('toggle_text_strip_tags') !== '';
	}


	function render_template() {

		add_filter('theme_mod_product_short_desc_enabled', '__return_true');
		add_filter('theme_mod_product_short_desc_toggle_v2', [$this, 'toggle_text']);
		add_filter('theme_mod_product_short_desc_toggle_strip_tags', [$this, 'toggle_text_strip_tags']);

		woocommerce_template_single_excerpt();

		remove_filter('theme_mod_product_short_desc_enabled', '__return_true');
		remove_filter('theme_mod_product_short_desc_toggle_v2', [$this, 'toggle_text']);
		remove_filter('theme_mod_product_short_desc_toggle_strip_tags', [$this, 'toggle_text_strip_tags']);

	}

}
