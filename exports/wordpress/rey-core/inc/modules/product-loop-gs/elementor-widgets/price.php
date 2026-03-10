<?php
namespace ReyCore\Modules\ProductLoopGs\ElementorWidgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Price extends WooBase {

	public function get_name() {
		return 'reycore-woo-grid-price';
	}

	public function get_title() {
		return __( 'Price (Product Grid)', 'rey-core' );
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
			'section_title',
			[
				'label' => __( 'Settings', 'rey-core' ),
			]
		);

			$this->add_wrapper_css_class();

			$this->add_control(
				'display',
				[
					'label' => esc_html__( 'Display', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- Default -', 'rey-core' ),
						'regular'  => esc_html__( 'Regular Price Only', 'rey-core' ),
						'sale'  => esc_html__( 'Sale Price Only', 'rey-core' ),
					],
				]
			);

			$this->add_control(
				'sale_fallback',
				[
					'label' => esc_html__( 'Fallback to Regular price?', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'condition' => [
						'display' => 'sale',
					],
				]
			);

			$this->add_control(
				'discount',
				[
					'label' => esc_html__( 'Hide Discount Label', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- Inherit -', 'rey-core' ),
						'hide'  => esc_html__( 'Hide', 'rey-core' ),
					],
					'condition' => [
						'display' => '',
					],
				]
			);

			$this->add_control(
				'prefix',
				[
					'label' => esc_html__( 'Prefix Text', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
				]
			);

			$this->add_control(
				'suffix',
				[
					'label' => esc_html__( 'Suffix Text', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
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

			$selectors['main'] = '{{WRAPPER}} .rey-loopPrice';

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
						'{{WRAPPER}}' => 'text-align: {{VALUE}};',
					],
				]
			);

			$this->add_control(
				'regular_color',
				[
					'label' => esc_html__( 'Regular Price Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} del' => 'color: {{VALUE}}',
					],
					'condition' => [
						'display' => '',
					],
				]
			);

			$this->add_control(
				'discount_bg_color',
				[
					'label' => esc_html__( 'Discount Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-discount' => 'background-color: {{VALUE}}',
					],
					'condition' => [
						'display' => '',
						'discount' => '',
					],
				]
			);

			$this->add_control(
				'discount_text_color',
				[
					'label' => esc_html__( 'Discount Text Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-discount' => 'color: {{VALUE}}',
					],
					'condition' => [
						'display' => '',
						'discount' => '',
					],
				]
			);

		$this->end_controls_section();

	}

	public function render_template() {

		if( 'hide' === $this->_settings['discount'] ){
			add_filter( 'reycore/woocommerce/loop/render/discount-price', '__return_false' );
		}


		if( $prefix = $this->_settings['prefix'] ){
			printf('<span class="__prefix">%s</span>', $prefix);
		}

		if( '' !== $this->_settings['display'] ){
			$this->get_price($this->_settings['display']);
		}
		else {
			woocommerce_template_loop_price();
		}

		if( $suffix = $this->_settings['suffix'] ){
			printf('<span class="__suffix">%s</span>', $suffix);
		}

		if( 'hide' === $this->_settings['discount'] ){
			remove_filter( 'reycore/woocommerce/loop/render/discount-price', '__return_false' );
		}

	}

	public function get_price($price_type = 'regular') {

		if( ! $this->_product ){
			return;
		}

		if( 'variable' === $this->_product_type ){
			if( $price = $this->get_variable_price($price_type) ){
				$this->render_price($price);
			}
		}

		else if( 'simple' === $this->_product_type ){
			if( $price = $this->get_simple_price($price_type) ){
				$this->render_price($price);
			}
		}

		else {
			woocommerce_template_loop_price();
		}

	}

	public function get_variable_price($price_type) {

		$prices = $this->_product->get_variation_prices( true );

		if ( ! empty( $prices['price'] ) ) {
			if ( 'sale' === $price_type ) {
				if( $this->_product->is_on_sale() ){
					return current( $prices['price'] );
				}
				else {
					if( ! empty($this->_settings['sale_fallback']) ){
						return current( $prices['regular_price'] ); // fallback to regular
					}
				}
			}
			else {
				return current( $prices['regular_price'] );
			}
		}

	}

	public function get_simple_price($price_type) {

		if ( 'sale' === $price_type ) {
			if( $this->_product->is_on_sale() ){
				return $this->_product->get_sale_price();
			}
			else {
				if( ! empty($this->_settings['sale_fallback']) ){
					return $this->_product->get_regular_price(); // fallback to regular
				}
			}
		}

		else {
			return $this->_product->get_regular_price();
		}

	}

	public function render_price($price){
		printf('<span class="price rey-loopPrice">%s</span>', wc_price($price) . $this->_product->get_price_suffix() );
	}

}
