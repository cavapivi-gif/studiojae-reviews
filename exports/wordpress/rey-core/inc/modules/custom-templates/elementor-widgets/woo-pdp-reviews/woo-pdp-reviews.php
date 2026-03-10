<?php
namespace ReyCore\Modules\CustomTemplates;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WooPdpReviews extends WooBase {

	public $_settings;

	public function get_name() {
		return 'reycore-woo-pdp-reviews';
	}

	public function get_title() {
		return __( 'Reviews (PDP)', 'rey-core' );
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
				'layout',
				[
					'label' => esc_html__( 'Layout', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- Inherit -', 'rey-core' ),
						'default'  => esc_html__( 'Button pill', 'rey-core' ),
						'classic'  => esc_html__( 'Classic', 'rey-core' ),
					],
				]
			);

			$this->add_control(
				'opened',
				[
					'label' => esc_html__( 'Load opened?', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- Inherit -', 'rey-core' ),
						'yes'  => esc_html__( 'Opened', 'rey-core' ),
						'no'  => esc_html__( 'Closed', 'rey-core' ),
					],
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_button_styles',
			[
				'label' => __( 'Button Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$selectors = [
				'btn' => '{{WRAPPER}} .rey-reviewsBtn',
				'btn_hover' => '{{WRAPPER}} .rey-reviewsBtn:hover',
			];

			$this->add_control(
				'btn_style',
				[
					'type' => \Elementor\Controls_Manager::SELECT,
					'label'       => esc_html__( 'Button Style', 'rey-core' ),
					'default'     => 'btn-secondary-outline',
					'options'     => [
						'' => esc_html__( '- Inherit -', 'rey-core' ),
						'btn-line' => esc_html__( 'Underlined on hover', 'rey-core' ),
						'btn-line-active' => esc_html__( 'Underlined', 'rey-core' ),
						'btn-primary' => esc_html__( 'Regular', 'rey-core' ),
						'btn-primary-outline' => esc_html__( 'Regular outline', 'rey-core' ),
						'btn-secondary' => esc_html__( 'Secondary', 'rey-core' ),
						'btn-secondary-outline' => esc_html__( 'Secondary outline', 'rey-core' ),
					],
				]
			);

			$this->start_controls_tabs( 'tabs_btn_styles' );

				$this->start_controls_tab(
					'tabs_btn_styles_normal',
					[
						'label' => esc_html__( 'Normal', 'rey-core' ),
					]
				);

					$this->add_control(
						'btn_color',
						[
							'label' => esc_html__( 'Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								$selectors['btn'] => '--accent-text-color: {{VALUE}}; --link-color: {{VALUE}}; color: {{VALUE}};',
							],
						]
					);

					$this->add_control(
						'btn_bg_color',
						[
							'label' => esc_html__( 'Background Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								$selectors['btn'] => '--accent-color: {{VALUE}}; background-color: {{VALUE}};',
							],
						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Border::get_type(),
						[
							'name' => 'btn_border',
							'selector' => $selectors['btn'],
						]
					);

				$this->end_controls_tab();

				$this->start_controls_tab(
					'tabs_btn_styles_hover',
					[
						'label' => esc_html__( 'Hover', 'rey-core' ),
					]
				);

					$this->add_control(
						'btn_color_hover',
						[
							'label' => esc_html__( 'Hover Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								$selectors['btn_hover'] => 'color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'btn_bg_color_hover',
						[
							'label' => esc_html__( 'Hover Background Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								$selectors['btn_hover'] => 'background-color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'btn_border_color_hover',
						[
							'label' => esc_html__( 'Hover Border Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								$selectors['btn_hover'] => 'border-color: {{VALUE}}',
							],
						]
					);


				$this->end_controls_tab();

			$this->end_controls_tabs();

			$this->add_responsive_control(
				'btn_border_radius',
				[
					'label' => __( 'Border Radius', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em' ],
					'selectors' => [
						$selectors['btn'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'btn_typo',
					'selector' => $selectors['btn'],
				]
			);


		$this->end_controls_section();

	}

	function render_template() {

		$maybe_show[] = wc_review_ratings_enabled();

		if( $product = wc_get_product() ){
			$maybe_show[] = $product->get_reviews_allowed();
		}

		if( in_array(false, $maybe_show, true) ){

			if( current_user_can('administrator') && apply_filters('reycore/woocommerce/tabs_blocks/show_info_help', true) ){
				echo '<p class="__notice">';
					echo reycore__get_svg_icon(['id'=>'help']);
					printf( __('Seems like Reviews are disabled. Please check <a href="%s" target="_blank">this article</a> to learn how to enable them. <br>This text is only shown to administrators.', 'rey-core'), 'https://themeisle.com/blog/customer-reviews-for-woocommerce/');
				echo '</p>';
			}

			return;
		}

		$this->_settings = $this->get_settings_for_display();

		if( 'classic' === $this->_settings['layout'] ){
			comments_template();
			return;
		}

		add_filter('theme_mod_single_reviews_start_opened', [$this, 'start_open']);
		add_filter('reycore/woocommerce/single/reviews_btn', [$this, 'btn_style']);

		reycore__get_template_part('template-parts/woocommerce/single-block-reviews');

		remove_filter('theme_mod_single_reviews_start_opened', [$this, 'start_open']);
		remove_filter('reycore/woocommerce/single/reviews_btn', [$this, 'btn_style']);

	}

	function start_open( $mod ){

		if( 'classic' !== $this->_settings['layout'] ){
			if( $status = $this->_settings['opened'] ){
				return 'yes' === $status;
			}
		}

		return $mod;
	}

	function btn_style( $classes ){

		if( 'classic' !== $this->_settings['layout'] ){
			if( $style = $this->_settings['btn_style'] ){
				$classes['style'] = $style;
			}
		}

		return $classes;
	}

}
