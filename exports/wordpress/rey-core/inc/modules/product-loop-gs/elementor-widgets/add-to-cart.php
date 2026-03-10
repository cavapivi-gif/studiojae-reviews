<?php
namespace ReyCore\Modules\ProductLoopGs\ElementorWidgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class AddToCart extends WooBase {

	public function get_name() {
		return 'reycore-woo-grid-addtocart';
	}

	public function get_title() {
		return __( 'Add To Cart (Product Grid)', 'rey-core' );
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
				'text_display',
				[
					'label' => esc_html__( 'Text Display', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- Default -', 'rey-core' ),
						'hide'  => esc_html__( 'Hide', 'rey-core' ),
						'custom'  => esc_html__( 'Custom Text', 'rey-core' ),
					],
				]
			);

			$this->add_control(
				'text_s',
				[
					'label' => esc_html__( 'Text (Simple Products)', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'placeholder' => esc_html__( 'ex: Add to cart', 'rey-core' ),
					'condition' => [
						'text_display' => 'custom',
					],
				]
			);

			$this->add_control(
				'text_v',
				[
					'label' => esc_html__( 'Text (Variable)', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'placeholder' => esc_html__( 'ex: Select Options', 'rey-core' ),
					'condition' => [
						'text_display' => 'custom',
					],
				]
			);

			$this->add_control(
				'icon_display',
				[
					'label' => esc_html__( 'Icon Display', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- Default -', 'rey-core' ),
						'show'  => esc_html__( 'Show', 'rey-core' ),
						'hide'  => esc_html__( 'Hide', 'rey-core' ),
					],
					'separator' => 'before',
					//'prefix_class' => '--icon-'
				]
			);

			$this->add_control(
				'icon',
				[
					'label' => esc_html__( 'Icon', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'bag',
					'options' => [
						'bag' => esc_html__('Shopping Bag', 'rey-core'),
						'bag2' => esc_html__('Shopping Bag 2', 'rey-core'),
						'bag3' => esc_html__('Shopping Bag 3', 'rey-core'),
						'basket' => esc_html__('Shopping Basket', 'rey-core'),
						'basket2' => esc_html__('Shopping Basket 2', 'rey-core'),
						'cart' => esc_html__('Shopping Cart', 'rey-core'),
						'cart2' => esc_html__('Shopping Cart 2', 'rey-core'),
						'cart3' => esc_html__('Shopping Cart 3', 'rey-core'),
						'custom' => esc_html__('- Custom -', 'rey-core'),
					],
					'condition' => [
						'icon_display' => 'show',
					],
				]
			);

			$this->add_control(
				'custom_icon',
				[
					'label' => __( 'Icon', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'default' => [],
					'condition' => [
						'icon' => 'custom',
						'icon_display' => 'show',
					],
				]
			);

			$this->add_control(
				'qty',
				[
					'label'   => esc_html__( 'Quantity Control for Simple Products', 'rey-core' ),
					'type'    => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''     => esc_html__('- Inherit -', 'rey-core'),
						'show' => esc_html__( 'Show', 'rey-core' ),
						'hide' => esc_html__( 'Hide', 'rey-core' ),
					],
					'separator' => 'before',
				]
			);

		$this->end_controls_section();

		$selectors['main'] = '{{WRAPPER}} .button.add_to_cart_button';
		$selectors['main_hover'] = '{{WRAPPER}} .button.add_to_cart_button:hover';
		$selectors['icon'] = '{{WRAPPER}} .rey-icon';

		$this->start_controls_section(
			'section_styles',
				[
					'label' => __( 'Styles', 'rey-core' ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
					'show_label' => false,
				]
			);

			$this->add_control(
				'btn_style',
				[
					'label' => __( 'Button Style', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- Inherit -', 'rey-core' ),
						'under' => esc_html__('Default (underlined)', 'rey-core'),
						'hover' => esc_html__('Hover Underlined', 'rey-core'),
						'primary' => esc_html__('Primary', 'rey-core'),
						'primary-out' => esc_html__('Primary Outlined', 'rey-core'),
						'clean' => esc_html__('Clean', 'rey-core'),
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'btn_typo',
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

			$this->start_controls_tabs( 'tabs_items_styles' );

				$this->start_controls_tab(
					'tabs_btn_normal',
					[
						'label' => esc_html__( 'Normal', 'rey-core' ),
					]
				);

					$this->add_control(
						'btn_color',
						[
							'label' => __( 'Text Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								$selectors['main'] => 'color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'btn_bg_color',
						[
							'label' => __( 'Background Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								$selectors['main'] => 'background-color: {{VALUE}}',
							],
						]
					);

					$this->add_responsive_control(
						'btn_border_width',
						[
							'label' => __( 'Border Width', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::DIMENSIONS,
							'size_units' => [ 'px', 'em', '%' ],
							'selectors' => [
								$selectors['main'] => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'btn_border_color',
						[
							'label' => __( 'Border Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								$selectors['main'] => 'border-color: {{VALUE}};',
							],
						]
					);

				$this->end_controls_tab();

				$this->start_controls_tab(
					'tabs_btn_hover',
					[
						'label' => esc_html__( 'Hover', 'rey-core' ),
					]
				);

					$this->add_control(
						'btn_color_hover',
						[
							'label' => __( 'Text Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								$selectors['main_hover'] => 'color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'btn_bg_color_hover',
						[
							'label' => __( 'Background Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								$selectors['main_hover'] => 'background-color: {{VALUE}}',
							],
						]
					);

					$this->add_responsive_control(
						'btn_border_width_hover',
						[
							'label' => __( 'Border Width', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::DIMENSIONS,
							'size_units' => [ 'px', 'em', '%' ],
							'selectors' => [
								$selectors['main_hover'] => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'btn_border_color_hover',
						[
							'label' => __( 'Border Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								$selectors['main_hover'] => 'border-color: {{VALUE}};',
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
						$selectors['main'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'btn_padding',
				[
					'label' => __( 'Padding', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em' ],
					'selectors' => [
						$selectors['main'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_control(
				'stretch_button',
				[
					'label' => esc_html__( 'Stretch button', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'return_value' => 'flex',
					'selectors' => [
						$selectors['main'] => 'display: {{VALUE}}; flex: 1;',
					],
				]
			);

		$this->end_controls_section();


		$this->start_controls_section(
				'icon_style',
				[
					'label' => __( 'Icon Styles', 'rey-core' ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
					'condition' => [
						'icon_display' => 'show',
					],
				]
			);

			$this->add_responsive_control(
				'icon_size',
				[
					'label' => esc_html__( 'Icon Size', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'min' => 1,
					'max' => 300,
					'step' => 1,
					'selectors' => [
						$selectors['icon'] => 'font-size: {{VALUE}}px',
					],
				]
			);

			$this->add_responsive_control(
				'icon_distance',
				[
					'label' => esc_html__( 'Icon Distance', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'min' => 0,
					'max' => 300,
					'step' => 1,
					'selectors' => [
						$selectors['main'] => 'gap: {{VALUE}}px',
					],
				]
			);

			$this->add_responsive_control(
				'icon_color',
				[
					'label' => esc_html__( 'Icon Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selectors['icon'] => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'icon_reverse',
				[
					'label' => esc_html__( 'Reverse Order', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'selectors' => [
						$selectors['icon'] => 'order: 1',
					],
				]
			);

		$this->end_controls_section();

	}

	public function render_template() {

		if( '' !== $this->_settings['text_display'] ){
			add_filter( 'woocommerce_product_add_to_cart_text', [$this, '__text']);
		}

		if( 'hide' === $this->_settings['icon_display'] ){
			add_filter('reycore/woocommerce/loop/add_to_cart/icon', '__return_empty_string');
		}
		elseif( 'show' === $this->_settings['icon_display'] ){
			add_filter('reycore/woocommerce/loop/add_to_cart/icon', '__return_empty_string');
			add_filter('reycore/woocommerce/loop/add_to_cart/content', [$this, '__icon']);
		}

		if( '' !== $this->_settings['btn_style'] ){
			add_filter('reycore/woocommerce/loop/add_to_cart/classes', [$this, '__css_classes']);
		}

		if( '' !== $this->_settings['qty'] ){
			add_filter('theme_mod_loop_supports_qty', [$this, '__qty']);
		}

		woocommerce_template_loop_add_to_cart();

		if( '' !== $this->_settings['text_display'] ){
			remove_filter( 'woocommerce_product_add_to_cart_text', [$this, '__text']);
		}

		if( '' !== $this->_settings['icon_display'] ){
			remove_filter('reycore/woocommerce/loop/add_to_cart/icon', '__return_empty_string');
			remove_filter('reycore/woocommerce/loop/add_to_cart/content', [$this, '__icon']);
		}

		if( '' !== $this->_settings['btn_style'] ){
			remove_filter('reycore/woocommerce/loop/add_to_cart/classes', [$this, '__css_classes']);
		}

		if( '' !== $this->_settings['qty'] ){
			remove_filter('theme_mod_loop_supports_qty', [$this, '__qty']);
		}

	}

	public function __text($text){

		if( 'hide' === $this->_settings['text_display'] ){
			return '';
		}

		if( 'simple' === $this->_product_type ){
			if( $t_simple = $this->_settings['text_s'] ){
				return $t_simple;
			}
		}

		else if( 'variable' === $this->_product_type ){
			if( $t_var = $this->_settings['text_v'] ){
				return $t_var;
			}
		}

		return $text;

	}

	public function __icon( $html ){

		$icon = false;

		if( 'custom' === $this->_settings['icon'] ){
			if( ($custom_icon = $this->_settings['custom_icon']) ){
				$icon = \ReyCore\Elementor\Helper::render_icon( $custom_icon, [ 'class' => 'rey-icon' ] );
			}
		}
		else {
			$icon = reycore__get_svg_icon([ 'id'=> $this->_settings['icon'] ]);
		}

		if( ! $icon ){
			return $html;
		}

		return $icon . $html;
	}

	public function __css_classes($classes){

		$classes['style'] = 'rey-btn--' . esc_attr($this->_settings['btn_style']);

		return $classes;
	}

	public function __qty($mod){
		return 'show' === $this->_settings['qty'];
	}

}
