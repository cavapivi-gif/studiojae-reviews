<?php
namespace ReyCore\Modules\ProductLoopGs\ElementorWidgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Title extends WooBase {

	public function get_name() {
		return 'reycore-woo-grid-title';
	}

	public function get_title() {
		return __( 'Title (Product Grid)', 'rey-core' );
	}

	public function get_icon() {
		return $this->get_icon_class();
	}

	public function show_in_panel() {
		return $this->maybe_show_in_panel();
	}

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

			$this->add_wrapper_css_class();

			$selectors['main'] = '{{WRAPPER}} .woocommerce-loop-product__title';

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

			$this->add_control(
				'tag',
				[
					'label' => esc_html__( 'HTML Tag', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- Inherit -', 'rey-core' ),
						'h1'  => esc_html__( 'H1', 'rey-core' ),
						'h2'  => esc_html__( 'H2', 'rey-core' ),
						'h3'  => esc_html__( 'H3', 'rey-core' ),
						'h4'  => esc_html__( 'H4', 'rey-core' ),
						'h5'  => esc_html__( 'H5', 'rey-core' ),
						'h6'  => esc_html__( 'H6', 'rey-core' ),
						'div'  => esc_html__( 'Div', 'rey-core' ),
						'span'  => esc_html__( 'Span', 'rey-core' ),
						'p'  => esc_html__( 'P', 'rey-core' ),
					],
				]
			);

		$this->end_controls_section();

	}

	public function render_template() {

		add_filter('woocommerce_product_loop_title_tag', [$this, 'change_tag']);
		// add_filter('woocommerce_product_loop_title_classes', [$this, 'inherit_styles']);

			woocommerce_template_loop_product_title();

		remove_filter('woocommerce_product_loop_title_tag', [$this, 'change_tag']);
		// remove_filter('woocommerce_product_loop_title_classes', [$this, 'inherit_styles']);

	}

	public function change_tag($tag){
		if( $custom_tag = \ReyCore\Helper::get_valid_title_tag($this->_settings['tag']) ){
			return $custom_tag;
		}
		return $tag;
	}

	public function inherit_styles($classes){
		// return 'elementor-loop-product__title';
		return $classes;
	}


}
