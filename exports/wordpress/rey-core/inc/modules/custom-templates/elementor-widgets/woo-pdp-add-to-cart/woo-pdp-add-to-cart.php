<?php
namespace ReyCore\Modules\CustomTemplates;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WooPdpAddToCart extends WooBase {

	public $_settings = [];

	public function get_name() {
		return 'reycore-woo-pdp-add-to-cart';
	}

	public function get_title() {
		return __( 'Add To Cart (PDP)', 'rey-core' );
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
				'button_title',
				[
				   'label' => esc_html__( 'Button', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::HEADING,
				]
			);

			$this->add_control(
				'button_text',
				[
					'label' => esc_html__( 'Button Text', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'placeholder' => esc_html__( 'eg: Add to cart', 'rey-core' ),
				]
			);

			$this->add_control(
				'button_stretch',
				[
					'label' => esc_html__( 'Stretch Button', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

			$this->add_control(
				'button_text_before',
				[
					'label' => esc_html__( 'Before text', 'rey-core' ),
					'description' => sprintf(__( 'In case you\'ve added a global text (or in product settings) before ATC button. <a href="%s">Read more</a> here.', 'rey-core' ), reycore__support_url('kb/add-content-before-or-after-add-to-cart-button-in-product-page/') ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

			$this->add_control(
				'button_text_after',
				[
					'label' => esc_html__( 'After text', 'rey-core' ),
					'description' => sprintf(__( 'In case you\'ve added a global text (or in product settings) after ATC button. <a href="%s">Read more</a> here.', 'rey-core' ), reycore__support_url('kb/add-content-before-or-after-add-to-cart-button-in-product-page/') ),
						'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

			// -----

			$this->add_control(
				'quantity_title',
				[
				   'label' => esc_html__( 'Quantity', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_control(
				'quantity',
				[
					'label' => esc_html__( 'Enable Quantity', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
				]
			);


			// -----

			//! TODO

			// $this->add_control(
			// 	'variations_title',
			// 	[
			// 	   'label' => esc_html__( 'Variations', 'rey-core' ),
			// 		'type' => \Elementor\Controls_Manager::HEADING,
			// 		'separator' => 'before',
			// 	]
			// );

			// $this->add_control(
			// 	'variations_display',
			// 	[
			// 		'label' => esc_html__( 'Enable variations display?', 'rey-core' ),
			// 		'type' => \Elementor\Controls_Manager::SWITCHER,
			// 		'default' => 'yes',
			// 		'prefix_class' => ''
			// 	]
			// );

			// -----

			$this->add_control(
				'stock_pdp__title',
				[
				'label' => esc_html__( 'Stock Information', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

				$this->add_control(
					'stock_pdp__enable',
					[
						'label' => esc_html__( 'Show "Stock"?', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::SELECT,
						'default' => '',
						'options' => [
							''  => esc_html__( '- Inherit -', 'rey-core' ),
							'yes'  => esc_html__( 'Yes', 'rey-core' ),
							'no'  => esc_html__( 'No', 'rey-core' ),
						],
					]
				);

			$this->add_control(
				'buynow_pdp__title',
				[
				'label' => esc_html__( 'Buy Now', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_control(
				'buynow_pdp__enable',
				[
					'label' => esc_html__( 'Show "Buy Now" button', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- Inherit -', 'rey-core' ),
						'yes'  => esc_html__( 'Yes', 'rey-core' ),
						'no'  => esc_html__( 'No', 'rey-core' ),
					],
				]
			);

			$this->add_control(
				'buynow_pdp__desc',
				[
					'type' => \Elementor\Controls_Manager::RAW_HTML,
					'content_classes' => 'rey-raw-html',
					'raw' => sprintf( _x( '<a href="%s" target="_blank" class="__title-link">Show "Buy Now" button <i class="eicon-editor-external-link"></i></a><br>Display a button that skips the cart and redirects to checkout. Head over to Customizer to enable and customize it.', 'Elementor control label', 'rey-core' ), add_query_arg( ['autofocus[control]' => 'buynow_pdp__enable'], admin_url( 'customize.php' ) ) ),
					// 'separator' => 'before',
					'condition' => [
						'buynow_pdp__enable!' => 'no',
					],
				]
			);

			$this->add_control(
				'single_product_atc__price',
				[
					'type' => \Elementor\Controls_Manager::RAW_HTML,
					'content_classes' => 'rey-raw-html',
					'raw' => sprintf( _x( '<a href="%s" target="_blank" class="__title-link">Show price inside the button<i class="eicon-editor-external-link"></i></a><br>Display the product price inside the Add To Cart button.', 'Elementor control label', 'rey-core' ), add_query_arg( ['autofocus[control]' => 'single_product_atc__price'], admin_url( 'customize.php' ) ) ),
					'separator' => 'before',
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_button_styles',
			[
				'label' => __( 'Button Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'show_label' => false,
			]
		);

			$selectors = [
				'main'              => '{{WRAPPER}} .single_add_to_cart_button',
				'atc'               => '{{WRAPPER}} .rey-cartBtnQty',
				'atc_hover'         => '{{WRAPPER}} .rey-cartBtnQty .button.single_add_to_cart_button: hover',
				'qty'               => '{{WRAPPER}} .rey-qtyField',
				'form'              => '{{WRAPPER}} form.cart',
				'in_stock'          => '{{WRAPPER}} .stock.in-stock',
				'in_stock_icon'     => '{{WRAPPER}} .stock.in-stock .rey-icon, {{WRAPPER}} .stock.in-stock .rey-wicon svg',
				'out_of_stock'      => '{{WRAPPER}} .stock.out-of-stock',
				'out_of_stock_icon' => '{{WRAPPER}} .stock.out-of-stock .rey-icon, {{WRAPPER}} .stock.out-of-stock .rey-wicon svg',
				'stock_icon'        => '{{WRAPPER}} .stock .rey-icon, {{WRAPPER}} .stock .rey-wicon svg',
			];

			$this->add_control(
				'color',
				[
					'label' => esc_html__( 'Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selectors['atc'] => '--accent-text-color: {{VALUE}}; --link-color: {{VALUE}};',
					],
				]
			);

			$this->add_control(
				'bg_color',
				[
					'label' => esc_html__( 'Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selectors['atc'] => '--accent-color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'color_hover',
				[
					'label' => esc_html__( 'Hover Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selectors['atc_hover'] => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'bg_color_hover',
				[
					'label' => esc_html__( 'Hover Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selectors['atc_hover'] => 'background-color: {{VALUE}}',
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
				'height',
				[
				   'label' => esc_html__( 'Height', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', 'em' ],
					'range' => [
						'px' => [
							'min' => 9,
							'max' => 180,
							'step' => 1,
						],
						'em' => [
							'min' => 0,
							'max' => 5.0,
						],
					],
					'default' => [],
					'selectors' => [
						$selectors['form'] => '--comp-heights: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_control(
				'button_icon',
				[
					'label' => esc_html__( 'Button Icon', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''        => esc_html__( 'No Icon', 'rey-core' ),
						'bag'     => esc_html__( 'Shopping Bag', 'rey-core' ),
						'bag2'    => esc_html__( 'Shopping Bag 2', 'rey-core' ),
						'bag3'    => esc_html__( 'Shopping Bag 3', 'rey-core' ),
						'basket'  => esc_html__( 'Shopping Basket', 'rey-core' ),
						'basket2' => esc_html__( 'Shopping Basket 2', 'rey-core' ),
						'cart'    => esc_html__( 'Shopping Cart', 'rey-core' ),
						'cart2'   => esc_html__( 'Shopping Cart 2', 'rey-core' ),
						'cart3'   => esc_html__( 'Shopping Cart 3', 'rey-core' ),
					],
				]
			);

			$this->add_responsive_control(
				'inline_distance',
				[
					'label' => esc_html__( 'Components distance', 'rey-core' ) . ' (px)',
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						$selectors['form'] => '--inline-distance: {{VALUE}}px',
					],
				]
			);


		$this->end_controls_section();

		$this->start_controls_section(
			'section_quantity_styles',
			[
				'label' => __( 'Quantity Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'show_label' => false,
				'condition' => [
					'quantity!' => '',
				],
			]
		);

			$this->add_control(
				'qty_style',
				[
					'label' => esc_html__( 'Quantity Style', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- Inherit -', 'rey-core' ),
						'default'  => esc_html__( 'Default', 'rey-core' ),
						'basic'  => esc_html__( 'Basic', 'rey-core' ),
						'select'  => esc_html__( 'Select Box', 'rey-core' ),
					],
				]
			);

			$this->add_control(
				'qty_color',
				[
					'label' => esc_html__( 'Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selectors['qty'] => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'qty_border_color',
				[
					'label' => esc_html__( 'Border Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selectors['qty'] => '--accent-color: {{VALUE}}',
					],
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_stock_styles',
			[
				'label' => __( 'Stock Label Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'show_label' => false,
				'condition' => [
					'stock_pdp__enable!' => 'no',
				],
			]
		);

			$this->add_control(
				'in_stock_styles',
				[
				   'label' => esc_html__( 'IN STOCK', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::HEADING,
				]
			);

				$this->add_control(
					'in_stock_color',
					[
						'label' => esc_html__( 'Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							$selectors['in_stock'] => 'color: {{VALUE}}',
						],
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' => 'in_stock_typo',
						'selector' => $selectors['in_stock'],
					]
				);

				$this->add_control(
					'in_stock_icon',
					[
						'label' => __( 'Icon', 'elementor' ),
						'type' => \Elementor\Controls_Manager::ICONS,
						'default' => [],
						'skin' => 'inline',
						'label_block' => false,
					]
				);

				$this->add_responsive_control(
					'in_stock_icon_size',
					[
						'label' => esc_html__( 'Icon Size', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::NUMBER,
						'min' => 1,
						'max' => 300,
						'step' => 1,
						'selectors' => [
							$selectors['in_stock_icon'] => 'font-size: {{VALUE}}px',
						],
					]
				);

				$this->add_responsive_control(
					'in_stock_icon_color',
					[
						'label' => esc_html__( 'Icon Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							$selectors['in_stock_icon'] => 'color: {{VALUE}}',
						],
					]
				);


			$this->add_control(
				'out_of_stock_styles',
				[
					'label' => esc_html__( 'OUT OF STOCK', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::HEADING,
				]
			);

				$this->add_control(
					'out_of_stock_color',
					[
						'label' => esc_html__( 'Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							$selectors['out_of_stock'] => 'color: {{VALUE}}',
						],
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' => 'out_of_stock_typo',
						'selector' => $selectors['out_of_stock'],
					]
				);

				$this->add_control(
					'out_of_stock_icon',
					[
						'label' => __( 'Icon', 'elementor' ),
						'type' => \Elementor\Controls_Manager::ICONS,
						'default' => [],
						'skin' => 'inline',
						'label_block' => false,
					]
				);

				$this->add_responsive_control(
					'out_of_stock_icon_size',
					[
						'label' => esc_html__( 'Icon Size', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::NUMBER,
						'min' => 1,
						'max' => 300,
						'step' => 1,
						'selectors' => [
							$selectors['out_of_stock_icon'] => 'font-size: {{VALUE}}px',
						],
					]
				);

				$this->add_responsive_control(
					'out_of_stock_icon_color',
					[
						'label' => esc_html__( 'Icon Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							$selectors['out_of_stock_icon'] => 'color: {{VALUE}}',
						],
					]
				);

			$this->add_responsive_control(
				'stock_icon_distance',
				[
					'label' => esc_html__( 'Icon Distance', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'min' => 0,
					'max' => 300,
					'step' => 1,
					'selectors' => [
						$selectors['stock_icon'] => 'margin-right: {{VALUE}}px',
					],
					'separator' => 'before',
				]
			);

		$this->end_controls_section();

	}

	function render_template() {

		$this->_settings = $this->get_settings_for_display();

		add_filter('theme_mod_single_atc__text', [$this, 'button_text']);
		add_filter('theme_mod_single_atc__stretch', [$this, 'button_stretch']);
		add_filter('theme_mod_enable_text_before_add_to_cart', [$this, 'button_text_before']);
		add_filter('theme_mod_enable_text_after_add_to_cart', [$this, 'button_text_after']);
		add_filter('theme_mod_single_atc_qty_controls', '__return_true'); // force true
		add_filter('theme_mod_single_atc_qty_controls_styles', [$this, 'qty_style']);
		add_filter('theme_mod_single_atc__icon', [$this, 'change_button_icon']);
		add_filter('theme_mod_buynow_pdp__enable', [$this, 'buy_now_button']);
		add_filter( 'reycore/woocommerce/wrap_quantity', '__return_true');
		add_filter( 'reycore/woocommerce/add_quantity_controls', '__return_true');
		add_filter( 'woocommerce_get_stock_html', [$this, 'stock_display'], 100);
		add_filter( 'reycore/woocommerce/stock/icon/in_stock', [$this, 'in_stock_icon']);
		add_filter( 'reycore/woocommerce/stock/icon/out_of_stock', [$this, 'out_of_stock_icon']);

		do_action('reycore/elementor/pdp-add-to-cart/render', $this);

		ob_start();
		woocommerce_template_single_add_to_cart();
		$form = ob_get_clean();
		$form = str_replace( 'single_add_to_cart_button ', 'single_add_to_cart_button rey-button ', $form );
		echo $form;

		remove_filter( 'reycore/woocommerce/wrap_quantity', '__return_true');
		remove_filter( 'reycore/woocommerce/add_quantity_controls', '__return_true');
		remove_filter('theme_mod_single_atc__text', [$this, 'button_text']);
		remove_filter('theme_mod_single_atc__stretch', [$this, 'button_stretch']);
		remove_filter('theme_mod_enable_text_before_add_to_cart', [$this, 'button_text_before']);
		remove_filter('theme_mod_enable_text_after_add_to_cart', [$this, 'button_text_after']);
		remove_filter('theme_mod_single_atc_qty_controls', '__return_true'); // force true
		remove_filter('theme_mod_single_atc_qty_controls_styles', [$this, 'qty_style']);
		remove_filter('theme_mod_single_atc__icon', [$this, 'change_button_icon']);
		remove_filter('theme_mod_buynow_pdp__enable', [$this, 'buy_now_button']);
		remove_filter( 'woocommerce_get_stock_html', [$this, 'stock_display'], 100);
		remove_filter( 'reycore/woocommerce/stock/icon/in_stock', [$this, 'in_stock_icon']);
		remove_filter( 'reycore/woocommerce/stock/icon/out_of_stock', [$this, 'out_of_stock_icon']);

	}

	function button_text($mod){

		if( $text = $this->_settings['button_text'] ){
			return $text;
		}

		return $mod;
	}
	function button_stretch($mod){
		if( '' !== $this->_settings['button_stretch'] ){
			return true;
		}
		return $mod;
	}
	function button_text_before($mod){
		if( '' !== $this->_settings['button_text_before'] ){
			return true;
		}
		return $mod;
	}
	function button_text_after($mod){
		if( '' !== $this->_settings['button_text_after'] ){
			return true;
		}
		return $mod;
	}
	function qty_style($mod){

		if( '' === $this->_settings['quantity'] ){
			return 'disabled';
		}

		if( $style = $this->_settings['qty_style'] ){
			return $style;
		}
		return $mod;
	}
	function buy_now_button($mod){
		if( $opt = $this->_settings['buynow_pdp__enable'] ){
			return $opt === 'yes';
		}
		return $mod;
	}

	function change_button_icon($mod){
		if( $style = $this->_settings['button_icon'] ){
			return $style;
		}
		return $mod;
	}

	function stock_display($stock){

		if( ($display = $this->_settings['stock_pdp__enable']) && 'no' === $display ){
			return false;
		}

		return $stock;
	}

	function in_stock_icon($icon){

		$display = $this->_settings['stock_pdp__enable'];

		// make sure label is not disabled
		if( 'no' !== $display ){
			if( ($custom_icon = $this->_settings['in_stock_icon']) && ! empty($custom_icon['value'] ) ){
				return \ReyCore\Elementor\Helper::render_icon( $custom_icon, [ 'aria-hidden' => 'false', 'class' => 'rey-icon' ] );
			}
		}

		return $icon;
	}

	function out_of_stock_icon($icon){

		$display = $this->_settings['stock_pdp__enable'];

		if( 'no' !== $display ){
			if( ($custom_icon = $this->_settings['out_of_stock_icon']) && ! empty($custom_icon['value'] ) ){
				return \ReyCore\Elementor\Helper::render_icon( $custom_icon, [ 'aria-hidden' => 'false', 'class' => 'rey-icon' ] );
			}
		}

		return $icon;
	}




}
