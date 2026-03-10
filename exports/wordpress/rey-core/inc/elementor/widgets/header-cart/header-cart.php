<?php
namespace ReyCore\Elementor\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( ! class_exists('\WooCommerce') ){
	return;
}

class HeaderCart extends \ReyCore\Elementor\WidgetsBase {

	public $_settings = [];

	public static function get_rey_config(){
		return [
			'id' => 'header-cart',
			'title' => __( 'Cart (Header)', 'rey-core' ),
			'icon' => 'eicon-cart',
			'categories' => [ 'rey-header' ],
			'keywords' => [],
		];
	}

	public function rey_get_script_depends() {
		return [ 'reycore-woocommerce', 'reycore-wc-header-minicart', 'reycore-sidepanel' ];
	}

	public function get_custom_help_url() {
		return reycore__support_url('kb/rey-elements-header/#cart');
	}

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

		if( get_theme_mod('shop_catalog', false) ):
			$this->add_control(
				'catalogue_mode_notice',
				[
					'type' => \Elementor\Controls_Manager::RAW_HTML,
					'raw' => sprintf(
						__( 'You seem to have Catalogue Mode enabled. This means that all Ecommerce functionalities are disabled. To use this element, please access <a href="%s" target="_blank">Customizer > WooCommerce > Product catalog - Misc</a>, and disable Catalogue mode.', 'rey-core' ),
						add_query_arg( ['autofocus[section]' => \ReyCore\Customizer\Options\Woocommerce\CatalogMisc::get_id()], admin_url( 'customize.php' ) )
					),
					'content_classes' => 'elementor-panel-alert elementor-panel-alert-danger',
				]
			);
		endif;

		$this->add_control(
			'edit_notice',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => esc_html__( 'If you don\'t want to show this element, simply remove it from its section.', 'rey-core' ),
				'content_classes' => 'rey-raw-html --notice',
			]
		);

		$this->add_control(
			'edit_link',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => sprintf(
					__( 'Cart options can be edited into the <a href="%1$s" target="_blank">Customizer Panel > Header > Cart</a>, but you can also override those settings below.', 'rey-core' ),
					add_query_arg( ['autofocus[section]' => \ReyCore\Customizer\Options\Header\Cart::get_id()], admin_url( 'customize.php' ) ) ),
				'content_classes' => 'rey-raw-html',
			]
		);

		$this->add_control(
			'custom',
			[
				'label' => __( 'Override global settings', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->add_control(
			'hide_empty',
			[
				'label' => __( 'Hide Cart if empty', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'no',
				'options' => [
					'yes' => __( 'Yes', 'rey-core' ),
					'no'  => __( 'No', 'rey-core' ),
				],
				'condition' => [
					'custom!' => '',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_styles',
			[
				'label' => __( 'Button Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE
			]
		);

			$this->add_control(
				'icon_color',
				[
					'label' => esc_html__( 'Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-headerCart' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'hover_color',
				[
					'label' => esc_html__( 'Hover Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-headerCart:hover' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_responsive_control(
				'cart_icon_size',
				[
					'label' => esc_html__( 'Icon Size', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 5,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .rey-headerCart .__icon' => '--icon-size: {{VALUE}}px;',
					],
				]
			);

			$this->add_control(
				'cart_icon',
				[
					'label' => esc_html__( 'Cart Icon', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						'' => esc_html__( '- Inherit -', 'rey-core' ),
						'custom' => esc_html__( '- Custom Icon -', 'rey-core' ),
						'disabled' => esc_html__( '- No Icon -', 'rey-core' ),
						'bag' => esc_html__( 'Icon - Shopping Bag', 'rey-core' ),
						'bag2' => esc_html__( 'Icon - Shopping Bag 2', 'rey-core' ),
						'bag3' => esc_html__( 'Icon - Shopping Bag 3', 'rey-core' ),
						'basket' => esc_html__( 'Icon - Shopping Basket', 'rey-core' ),
						'basket2' => esc_html__( 'Icon - Shopping Basket 2', 'rey-core' ),
						'cart' => esc_html__( 'Icon - Shopping Cart', 'rey-core' ),
						'cart2' => esc_html__( 'Icon - Shopping Cart 2', 'rey-core' ),
						'cart3' => esc_html__( 'Icon - Shopping Cart 3', 'rey-core' ),
					],
				]
			);

			$this->add_control(
				'custom_icon',
				[
					'label' => __( 'Custom Cart Icon', 'elementor' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'condition' => [
						'cart_icon' => 'custom',
					],

				]
			);

			$this->add_control(
				'hide_icon',
				[
					'label' => esc_html__( 'Hide Icon on', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT2,
					'default' => [],
					'options' => [
						'lg'  => esc_html__( 'Desktop', 'rey-core' ),
						'md'  => esc_html__( 'Tablet', 'rey-core' ),
						'sm'  => esc_html__( 'Mobile', 'rey-core' ),
					],
					'multiple' => true,
				]
			);

			$this->add_control(
				'text_title',
				[
				   'label' => esc_html__( 'TEXT', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_control(
				'cart_text',
				[
					'label' => esc_html__( 'Custom Text', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'placeholder' => esc_html__( 'eg: CART', 'rey-core' ),
					'description' => __( 'Use <code data-insert>{{total}}</code> string to add the cart subtotals.', 'rey-core' ),
				]
			);

			$this->add_control(
				'text_position',
				[
					'label' => __( 'Text Position', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => __( 'Default', 'rey-core' ),
						'before' => esc_html__( 'Before', 'rey-core' ),
						'after' => esc_html__( 'After', 'rey-core' ),
						'under' => esc_html__( 'Under', 'rey-core' ),
					],
					'condition' => [
						'cart_text!' => '',
					],
				]
			);

			$this->add_control(
				'text_distance',
				[
					'label' => esc_html__( 'Text Distance', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}}' => '--text-distance: {{VALUE}}px',
					],
					'condition' => [
						'cart_text!' => '',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'typography',
					'selector' => '{{WRAPPER}} .__text',
					'condition' => [
						'cart_text!' => '',
					],
				]
			);

			// by default hide text on tablet and mobile
			$default_hide_text = ['md', 'sm'];
			// legacy: show text on mobile option
			if( get_theme_mod('header_cart_text_mobile', false) ){
				$default_hide_text = [];
			}

			$this->add_control(
				'hide_text',
				[
					'label' => esc_html__( 'Hide Text on', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT2,
					'default' => $default_hide_text,
					'options' => [
						'lg'  => esc_html__( 'Desktop', 'rey-core' ),
						'md'  => esc_html__( 'Tablet', 'rey-core' ),
						'sm'  => esc_html__( 'Mobile', 'rey-core' ),
					],
					'multiple' => true,
					'condition' => [
						'cart_text!' => '',
					],
				]
			);

			$this->add_control(
				'counter_title',
				[
				   'label' => esc_html__( 'COUNTER', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_control(
				'hide_counter',
				[
					'label' => __( 'Hide Counter', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'none',
					'default' => 'inline-flex',
					'selectors' => [
						'{{WRAPPER}} .rey-headerCart .rey-headerIcon-counter' => 'display: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'hide_empty_counter',
				[
					'label' => esc_html__( 'Hide "0" Counter', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'condition' => [
						'hide_counter!' => 'none',
					],
					'selectors' => [
						'{{WRAPPER}} .rey-headerCart-wrapper[data-rey-cart-count="0"] .rey-headerIcon-counter' => 'display: none',
					],
				]
			);

			$this->add_control(
				'counter_layout',
				[
					'label' => esc_html__( 'Counter Style', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'bubble',
					'options' => [
						'bubble'  => esc_html__( 'Circle', 'rey-core' ),
						'minimal'  => esc_html__( 'Minimal', 'rey-core' ),
						'out'  => esc_html__( 'Outline', 'rey-core' ),
						'text'  => esc_html__( 'Text', 'rey-core' ),
					],
					'condition' => [
						'hide_counter!' => 'none',
					],
				]
			);

			$this->add_control(
				'counter_bg_color',
				[
					'label' => __( 'Counter Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-headerCart .rey-headerIcon-counter' => 'background-color: {{VALUE}}',
					],
					'condition' => [
						'hide_counter!' => 'none',
						'counter_layout' => 'bubble',
					],
				]
			);

			$this->add_control(
				'counter_text_color',
				[
					'label' => __( 'Counter Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-headerCart .rey-headerIcon-counter' => 'color: {{VALUE}}',
					],
					'condition' => [
						'hide_counter!' => 'none',
					],
				]
			);


		$this->end_controls_section();

		/* ------------------------------------ PANEL ------------------------------------ */

		$this->start_controls_section(
			'section_panel_styles',
			[
				'label' => __( 'Panel Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE
			]
		);

		$this->add_control(
			'cart_panel_notice',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => sprintf(
					__( 'Cart Panel\'s options can be edited into the <a href="%1$s" target="_blank">Customizer Panel > Header > Cart</a>.', 'rey-core' ),
					add_query_arg( ['autofocus[section]' => \ReyCore\Customizer\Options\Header\Cart::get_id()], admin_url( 'customize.php' ) ) ),
				'content_classes' => 'rey-raw-html',
			]
		);

		$this->end_controls_section();
	}

	function set_options( $args ){

		if( ! empty($this->_settings['custom']) ){
			$args['hide_empty'] = $this->_settings['hide_empty'];
		}

		if( isset($args['classes']) ){
			if( $this->_settings['cart_text'] !== '' && ($text_pos = $this->_settings['text_position']) ){
				$args['classes'] .= sprintf(' --tp-%1$s --hit-%1$s', esc_attr($text_pos));
			}
		}

		if( ! empty($this->_settings['counter_layout']) ){
			$args['counter_layout'] = $this->_settings['counter_layout'];
		}

		if( ! empty($this->_settings['hide_icon']) ){
			$hide_icon_classes = array_map( function($v){ return '--dnone-' . esc_attr($v); }, (array) $this->_settings['hide_icon'] );
			$args['icon_classes'] = implode(' ', $hide_icon_classes );
		}

		if( ! empty($this->_settings['hide_text']) ){
			$hide_text_classes = array_map( function($v){ return '--dnone-' . esc_attr($v); }, (array) $this->_settings['hide_text'] );
			$args['text_classes'] = implode(' ', $hide_text_classes );
		}

		return $args;
	}

	function set_layout( $opt ){

		$settings = $this->get_settings_for_display();

		if( $cart_layout = $settings['cart_icon'] ){
			return $cart_layout;
		}

		return $opt;
	}

	function set_text( $opt ){

		$settings = $this->get_settings_for_display();

		if( $cart_text = $settings['cart_text'] ){
			return $cart_text;
		}

		return $opt;
	}

	function set_cart_icon( $icon_html ){

		$settings = $this->get_settings_for_display();

		if( ! $settings['cart_icon'] ){
			return $icon_html;
		}

		else if( $settings['cart_icon'] === 'disabled' ){
			return '';
		}

		else if( $settings['cart_icon'] === 'custom' ) {

			if( ($custom_icon = $settings['custom_icon']) && isset($custom_icon['value']) && !empty($custom_icon['value']) ){
				return \ReyCore\Elementor\Helper::render_icon( $custom_icon, [ 'aria-hidden' => 'true', 'class' => 'rey-headerCart-customIcon rey-icon' ] );
			}
		}

		else {
			$icon_html = reycore__get_svg_icon([ 'id'=> $settings['cart_icon'] ]);
		}

		return $icon_html;
	}

	/**
	 * Render widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {

		$scripts = $this->rey_get_script_depends();

		$this->_settings = $this->get_settings();

		reycore_assets()->add_scripts( $scripts );

		// force enable
		add_filter('theme_mod_header_enable_cart', '__return_true', 10);
		add_filter('theme_mod_header_cart_layout', [$this, 'set_layout'], 10);
		add_filter('theme_mod_header_cart_text_v2', [$this, 'set_text'], 10);
		add_filter('reycore/woocommerce/header/shopping_cart_icon', [$this, 'set_cart_icon'], 10);
		add_filter('reycore/header/cart_params', [$this, 'set_options'], 10);

			do_action('reycore/elementor/header-cart/render', $this);

		remove_filter('reycore/header/cart_params', [$this, 'set_options'], 10);

		reycore_assets()->add_styles(['rey-wc-header-mini-cart-top']);

	}

	/**
	 * Render widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function content_template() {}
}
