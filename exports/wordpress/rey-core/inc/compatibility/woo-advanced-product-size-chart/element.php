<?php
namespace ReyCore\Compatibility\WooAdvancedProductSizeChart;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Element extends \ReyCore\Modules\CustomTemplates\WooBase {

	public function get_name() {
		return 'reycore-woo-pdp-size-chart';
	}

	public function get_title() {
		return __( 'Size Chart (PDP)', 'rey-core' );
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

			if( ! class_exists('Size_Chart_For_Woocommerce') ){
				$this->add_control(
					'install_notice',
					[
						'type' => \Elementor\Controls_Manager::RAW_HTML,
						'content_classes' => 'rey-raw-html',
						'raw' => 'Install <a href="https://wordpress.org/plugins/woo-advanced-product-size-chart/" target="_blank" class="__title-link">Product Size Charts Plugin for WooCommerce<i class="eicon-editor-external-link"></i></a>',
					]
				);
			}

			$this->add_control(
				'button_text',
				[
					'label' => esc_html__( 'Button Text', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'placeholder' => esc_html__( 'eg: Size Chart', 'rey-core' ),
				]
			);

			$this->add_control(
				'customize_settings_notice',
				[
					'type' => \Elementor\Controls_Manager::RAW_HTML,
					'content_classes' => 'rey-raw-html',
					'raw' => sprintf( _x( '<a href="%s" target="_blank" class="__title-link">Modify Size Charts options<i class="eicon-editor-external-link"></i></a><br>Access DotsTore Plugins > Product Size Charts Plugin for WooCommerce, to customize the display and options of this feature.', 'Elementor control label', 'rey-core' ), admin_url( 'admin.php?page=size-chart-get-started' ) ),
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
				'main' => '{{WRAPPER}} .rey-sizeChart-btnWrapper',
				'btn' => '{{WRAPPER}} .rey-sizeChart-btn',
				'btn_hover' => '{{WRAPPER}} .rey-sizeChart-btn:hover',
			];

			$this->add_control(
				'btn_style',
				[
					'type' => \Elementor\Controls_Manager::SELECT,
					'label'       => esc_html__( 'Button Style', 'rey-core' ),
					'default'     => 'line-active',
					'options'     => [
						'primary' => 'Primary',
						'secondary' => 'Secondary',
						'line-active' => 'Underlined',
						'line' => 'Underlined on hover',
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

		$args = [];

		if( $button_text = $this->_settings['button_text'] ){
			$args['btn_text'] = $button_text;
		}

		if( $btn_style = $this->_settings['btn_style'] ){
			$args['btn_style'] = $btn_style;
		}

		do_action('reycore/templates/elements/woo_size_charts', $args);

	}

}
