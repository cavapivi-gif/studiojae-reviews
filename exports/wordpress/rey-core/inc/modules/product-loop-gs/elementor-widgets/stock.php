<?php
namespace ReyCore\Modules\ProductLoopGs\ElementorWidgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Stock extends WooBase {

	public function get_name() {
		return 'reycore-woo-grid-stock';
	}

	public function get_title() {
		return __( 'Stock Status (Product Grid)', 'rey-core' );
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
						''  => esc_html__( '- Inherit -', 'rey-core' ),
						'show'  => esc_html__( 'Show If', 'rey-core' ),
						'hide'  => esc_html__( 'Hide If', 'rey-core' ),
					],
				]
			);

			$this->add_control(
				'status',
				[
					'label' => esc_html__( 'Stock Status', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT2,
					'default' => [],
					'multiple' => true,
					'options' => [
						'instock'  => esc_html__( 'In Stock', 'rey-core' ),
						'outofstock'  => esc_html__( 'Out Of Stock', 'rey-core' ),
						'onbackorder'  => esc_html__( 'Available on Backorder', 'rey-core' ),
					],
					'condition' => [
						'display!' => '',
					],
				]
			);

		$this->end_controls_section();

		$selectors['main'] = '{{WRAPPER}} .stock';
		$selectors['in'] = '{{WRAPPER}} .stock.in-stock';
		$selectors['out'] = '{{WRAPPER}} .stock.out-of-stock';
		$selectors['bo'] = '{{WRAPPER}} .stock.available-on-backorder';

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
							'title'         => __( 'Left', 'rey-core' ),
							'icon'          => 'eicon-text-align-left',
						],
						'center'        => [
							'title'         => __( 'Center', 'rey-core' ),
							'icon'          => 'eicon-text-align-center',
						],
						'flex-end'          => [
							'title'         => __( 'Right', 'rey-core' ),
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
				'in_heading',
				[
				   'label' => esc_html__( 'In Stock', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_control(
				'in_color',
				[
					'label' => esc_html__( 'Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selectors['in'] => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'in_icon',
				[
					'label' => __( 'Icon', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
					'default' => [],
				]
			);

			$this->add_control(
				'in_icon_color',
				[
					'label' => esc_html__( 'Icon Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selectors['in'] . ' .rey-icon' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'in_text',
				[
					'label' => esc_html__( 'Text', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'placeholder' => esc_html__( 'ex: In Stock', 'rey-core' ),
				]
			);

			//  ----

			$this->add_control(
				'out_heading',
				[
				   'label' => esc_html__( 'Out Of Stock', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_control(
				'out_color',
				[
					'label' => esc_html__( 'Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selectors['out'] => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'out_icon',
				[
					'label' => __( 'Icon', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
					'default' => [],
				]
			);

			$this->add_control(
				'out_icon_color',
				[
					'label' => esc_html__( 'Icon Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selectors['out'] . ' .rey-icon' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'out_text',
				[
					'label' => esc_html__( 'Text', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'placeholder' => esc_html__( 'ex: Out Of Stock', 'rey-core' ),
				]
			);

			//  ----

			$this->add_control(
				'bo_heading',
				[
				   'label' => esc_html__( 'Backorder', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_control(
				'bo_color',
				[
					'label' => esc_html__( 'Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selectors['bo'] => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'bo_icon',
				[
					'label' => __( 'Icon', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
					'default' => [],
				]
			);

			$this->add_control(
				'bo_icon_color',
				[
					'label' => esc_html__( 'Icon Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selectors['bo'] . ' .rey-icon' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'bo_text',
				[
					'label' => esc_html__( 'Text', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'placeholder' => esc_html__( 'ex: Available on Backorder', 'rey-core' ),
				]
			);

		$this->end_controls_section();

	}

	public function render_template() {

		$stock_status = $this->_product->get_stock_status();

		if( ! $this->maybe_show($stock_status) ){
			return;
		}

		add_filter('woocommerce_get_availability_text', [$this, '__texts']);

			echo wc_get_stock_html( $this->_product ); // WPCS: XSS ok.

		remove_filter('woocommerce_get_availability_text', [$this, '__texts']);

	}

	public function __texts() {

		$availability = '';

		if ( ! $this->_product->is_in_stock() ) {
			$availability .= $this->get_status_icon('out_icon');
			$availability .= ($txt = $this->_settings['out_text']) ? $txt : __( 'Out of stock', 'woocommerce' );
		}

		elseif ( $this->_product->managing_stock() && $this->_product->is_on_backorder( 1 ) ) {
			$availability .= $this->get_status_icon('bo_icon');
			$availability .= $this->_product->backorders_require_notification() ? (($txt = $this->_settings['bo_text']) ? $txt : __( 'Available on backorder', 'woocommerce' )) : '';
		}

		elseif ( ! $this->_product->managing_stock() && $this->_product->is_on_backorder( 1 ) ) {
			$availability .= $this->get_status_icon('bo_icon');
			$availability .= ($txt = $this->_settings['bo_text']) ? $txt : __( 'Available on backorder', 'woocommerce' );
		}

		elseif ( $this->_product->managing_stock() ) {
			$availability .= $this->get_status_icon('in_icon');
			$availability .= wc_format_stock_for_display( $this->_product );
		}

		else {
			$availability .= $this->get_status_icon('in_icon');
			$availability .= ($txt = $this->_settings['in_text']) ? $txt : __( 'In stock', 'woocommerce' );
		}

		return $availability;

	}

	public function get_status_icon( $type ){
		if( ($custom_icon = $this->_settings[$type]) ){
			return \ReyCore\Elementor\Helper::render_icon( $custom_icon, [ 'class' => 'rey-icon' ] );
		}
	}

	public function maybe_show($stock_status) {

		if ( ! $this->_product->is_purchasable() ) {
			return;
		}

		if( empty($this->_settings['status']) ){
			return true;
		}

		if( 'show' === $this->_settings['display'] ){
			if( in_array($stock_status, $this->_settings['status'], true) ){
				return true;
			}
		}

		else if( 'hide' === $this->_settings['display'] ){
			if( in_array($stock_status, $this->_settings['status'], true) ){
				return false;
			}
		}

	}

}
