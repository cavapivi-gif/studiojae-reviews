<?php
namespace ReyCore\Modules\Compare;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Element extends \ReyCore\Modules\CustomTemplates\WooBase {

	public function get_name() {
		return 'reycore-woo-pdp-compare';
	}

	public function get_title() {
		return __( 'Compare Button (PDP)', 'rey-core' );
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
				'customize_settings_notice',
				[
					'type' => \Elementor\Controls_Manager::RAW_HTML,
					'content_classes' => 'rey-raw-html',
					'raw' => sprintf( _x( '<a href="%s" target="_blank" class="__title-link">Customize Compare options<i class="eicon-editor-external-link"></i></a><br>Access Customizer > WooCommerce > Compare to customize the display of this feature.', 'Elementor control label', 'rey-core' ), add_query_arg( ['autofocus[panel]' => 'compare'], admin_url( 'customize.php' ) ) ),
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
				'main' => '{{WRAPPER}} .rey-compareBtn-wrapper',
				'btn' => '{{WRAPPER}} .rey-compareBtn',
				'btn_hover' => '{{WRAPPER}} .rey-compareBtn:hover',
			];

			$this->add_control(
				'text_visibility',
				[
					'label' => esc_html__( 'Text Visibility', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'show_desktop',
					'options' => [
						'' => esc_html__('Hide', 'rey-core'),
						'show' => esc_html__('Show', 'rey-core'),
						'show_desktop' => esc_html__('Show text on desktop only', 'rey-core'),
					],
				]
			);

			$this->add_control(
				'show_tooltip',
				[
					'label' => esc_html__( 'Show tooltip', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'condition' => [
						'text_visibility' => '',
					],
				]
			);

			$this->add_control(
				'btn_style',
				[
					'type' => \Elementor\Controls_Manager::SELECT,
					'label'       => esc_html__( 'Button Style', 'rey-core' ),
					'default'     => 'btn-line',
					'options'     => [
						'' => esc_html__( '- Inherit -', 'rey-core' ),
						'btn-line' => esc_html__( 'Underlined inactive', 'rey-core' ),
						'btn-line-active' => esc_html__( 'Underlined', 'rey-core' ),
						'btn-primary' => esc_html__( 'Regular', 'rey-core' ),
						'btn-primary btn--block' => esc_html__( 'Regular & Full width', 'rey-core' ),
						'btn-primary-outline' => esc_html__( 'Regular outline', 'rey-core' ),
						'btn-primary-outline btn--block' => esc_html__( 'Regular outline & Full width', 'rey-core' ),
						'btn-secondary' => esc_html__( 'Secondary', 'rey-core' ),
						'btn-secondary btn--block' => esc_html__( 'Secondary & Full width', 'rey-core' ),
					],
				]
			);

			$this->add_control(
				'color',
				[
					'label' => esc_html__( 'Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selectors['main'] => '--accent-text-color: {{VALUE}}; --link-color: {{VALUE}};',
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

		add_filter('theme_mod_compare__pdp_enable', '__return_true');

		add_filter('theme_mod_compare__pdp_btn_style', [$this, 'btn_style']);
		add_filter('theme_mod_compare__pdp_wtext', [$this, 'text_visibility']);
		add_filter('theme_mod_compare__pdp_tooltip', [$this, 'show_tooltip']);

		Base::instance()->output_pdp_button();

		remove_filter('theme_mod_compare__pdp_btn_style', [$this, 'btn_style']);
		remove_filter('theme_mod_compare__pdp_wtext', [$this, 'text_visibility']);
		remove_filter('theme_mod_compare__pdp_tooltip', [$this, 'show_tooltip']);
	}

	function btn_style($mod){

		if( $style = $this->_settings['btn_style'] ){
			return $style;
		}

		return $mod;
	}

	function text_visibility($mod){
		return $this->_settings['text_visibility'];
	}

	function show_tooltip($mod){

		if( '' === $this->_settings['text_visibility'] ){
			if( '' !== $this->_settings['show_tooltip'] ){
				return true;
			}
		}

		return $mod;
	}

}
