<?php
namespace ReyCore\Modules\ProductVideo;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Element extends \ReyCore\Modules\CustomTemplates\WooBase {

	public function get_name() {
		return 'reycore-woo-pdp-video-link';
	}

	public function get_title() {
		return __( 'Video Link (PDP)', 'rey-core' );
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
				'button_text',
				[
					'label' => esc_html__( 'Button Text', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'placeholder' => esc_html__( 'eg: PLAY PRODUCT VIDEO', 'rey-core' ),
				]
			);

			$this->add_control(
				'modal_width',
				[
					'label' => esc_html__( 'Modal Width', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 100,
					'max' => 2000,
					'step' => 1,
				]
			);

			$this->add_control(
				'modal_video_ratio',
				[
					'label' => esc_html__( 'Video Ratio', 'rey-core' ) . ' (%)',
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'placeholder' => esc_html__( 'eg: 56.25', 'rey-core' ),
				]
			);

			$this->add_control(
				'customize_settings_notice',
				[
					'type' => \Elementor\Controls_Manager::RAW_HTML,
					'content_classes' => 'rey-raw-html',
					'raw' => 'Other options of this features can be controlled in each product individual settings.',
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
				'main' => '{{WRAPPER}} .rey-singlePlayVideo-wrapper',
				'btn' => '{{WRAPPER}} .rey-singlePlayVideo-wrapper > .btn',
				'btn_hover' => '{{WRAPPER}} .rey-singlePlayVideo-wrapper > .btn:hover',
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

		$args = [
			'wrap' => true
		];

		if( $button_text = $this->_settings['button_text'] ){
			$args['custom_text'] = $button_text;
		}

		if( $modal_width = $this->_settings['modal_width'] ){
			$args['modal_width'] = $modal_width;
		}

		if( $modal_video_ratio = $this->_settings['modal_video_ratio'] ){
			$args['modal_video_ratio'] = $modal_video_ratio;
		}

		if( $btn_style = $this->_settings['btn_style'] ){
			$args['attr']['class'] = 'btn u-btn-icon-sm ' . $btn_style;
		}

		Base::instance()->print_button($args);

	}


}
