<?php
namespace ReyCore\Elementor\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WcCart extends \ReyCore\Elementor\WidgetsBase {

	public $_settings = [];

	public static function get_rey_config(){
		return [
			'id' => 'wc-cart',
			'title' => __( 'WooCommerce Cart Page', 'rey-core' ),
			'icon' => 'eicon-cart',
			'categories' => [ 'rey-woocommerce' ],
			'keywords' => [],
			'css' => [
				'assets/style[rtl].css',
			],
			'js' => [
				'assets/script.js',
			],
		];
	}

	public function __construct( $data = [], $args = null ) {

		if ( $data && isset($data['settings']) && $settings = $data['settings'] ) {
			$this->set_cart_settings($settings);
		}

		parent::__construct( $data, $args );
	}

	function set_cart_settings( $settings ) {
		if( ! defined('REY_CART_SETTINGS') ){
			define('REY_CART_SETTINGS', $settings );
		}
	}

	public function rey_get_script_depends() {
		return [ 'rey-tmpl', 'reycore-woocommerce', 'reycore-widget-wc-cart-scripts' ];
	}

	public function get_custom_help_url() {
		return reycore__support_url('kb/how-to-create-a-custom-cart-checkout-layout/');
	}

	protected function register_controls() {

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
					'default' => 'custom',
					'options' => [
						'classic'  => esc_html__( 'Classic', 'rey-core' ),
						'custom'  => esc_html__( 'Custom', 'rey-core' ),
					],
				]
			);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_cart_style',
			[
				'label' => __( 'Cart Table', 'rey-core' ),
			]
		);

			$this->add_control(
				'custom_text',
				[
					'label' => esc_html__( 'Custom Content after cart', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::WYSIWYG,
					'default' => '',
					'placeholder' => __( 'Type your content here', 'rey-core' ),
				]
			);

			$this->add_control(
				'show_wishlist',
				[
					'label' => esc_html__( 'Show Wishlist Products', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
					'separator' => 'before'
				]
			);

			$this->add_control(
				'show_cross_sells',
				[
					'label' => esc_html__( 'Show Cross Sells', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_cart_totals_opts',
			[
				'label' => __( 'Cart Totals', 'rey-core' ),
			]
		);

			$this->add_control(
				'totals_shipping',
				[
				'label' => esc_html__( 'SHIPPING', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_control(
				'totals_shipping_enable',
				[
					'label' => esc_html__( 'Display Shipping', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

			$this->add_control(
				'totals_shipping_off_text',
				[
					'label' => esc_html__( 'Text under table', 'rey-core' ),
					'label_block' => true,
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => esc_html__( 'Taxes and Shipping are calculated at Checkout.', 'rey-core' ),
					'condition' => [
						'totals_shipping_enable' => '',
					],
				]
			);

			$this->add_control(
				'totals_coupon_enable',
				[
					'label' => esc_html__( 'Display Coupon', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
					'separator' => 'before',
				]
			);

				$this->add_control(
					'totals_coupon_toggle',
					[
						'label' => esc_html__( 'Toggle Coupon Link', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::SWITCHER,
						'default' => '',
						'condition' => [
							'totals_coupon_enable!' => '',
						],
					]
				);


			$this->add_control(
				'totals_custom_text_heading',
				[
				   'label' => esc_html__( 'CUSTOM CONTENT', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_control(
				'custom_text_before_proceed_pos',
				[
					'label' => esc_html__( 'Text position', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '1',
					'options' => [
						'1'  => esc_html__( 'Before Button', 'rey-core' ),
						'20'  => esc_html__( 'After Button', 'rey-core' ),
					],
				]
			);

			$this->add_control(
				'custom_text_before_proceed',
				[
					'label' => esc_html__( 'Custom Content before Proceed button', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::WYSIWYG,
					'default' => '',
					'placeholder' => __( 'Type your content here', 'rey-core' ),
				]
			);


		$this->end_controls_section();

		$this->start_controls_section(
			'section_empty_cart_opts',
			[
				'label' => __( 'Empty Cart', 'rey-core' ),
			]
		);

			$this->add_control(
				'empty_cart_gs',
				[
					'label' => esc_html__( 'Show Global Section', 'rey-core' ),
					'default' => '',
					'type' => 'rey-ajax-list',
					'query_args' => [
						'request' => 'global_sections_list',
						'type' => 'generic',
						'edit_link' => true,
						'export' => 'id',
					],
				]
			);

			$this->add_control(
				'empty_cart_mode',
				[
					'label' => esc_html__( 'Mode', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'overwrite',
					'options' => [
						'overwrite' => esc_html__( 'Overwrite Content', 'rey-core' ),
						'before' => esc_html__( 'Add Before', 'rey-core' ),
						'after' => esc_html__( 'Add After', 'rey-core' ),
					],
					'condition' => [
						'empty_cart_gs!' => '',
					],
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Style', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);


			$this->add_responsive_control(
				'image_size',
				[
					'label' => __( 'Image Size', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'range' => [
						'px' => [
							'max' => 300,
							'min' => 20,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .woocommerce-cart-form__contents .woocommerce-cart-form__cart-thumbnail' => 'width: {{SIZE}}px;',
					],
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_cart_table_style',
			[
				'label' => __( 'Table Style', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_responsive_control(
				'tbl__size',
				[
					'label' => esc_html__( 'Table Size', 'rey-core' ) . ' (%)',
					'type' => \Elementor\Controls_Manager::SLIDER,
					'range' => [
						'%' => [
							'min' => 30,
							'max' => 90,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .rey-cartPage.--layout-custom' => '--cart-table-size: {{SIZE}}%',
					],
					'condition' => [
						'layout' => 'custom',
					],
				]
			);

			$this->add_control(
				'tbl__style',
				[
					'label' => esc_html__( 'Table style', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '1',
					'options' => [
						''  => esc_html__( 'Default', 'rey-core' ),
						'1'  => esc_html__( 'Style #1', 'rey-core' ),
					],

				]
			);

			$this->add_control(
				'tbl__bg_color',
				[
					'label' => esc_html__( 'Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .woocommerce-cart-form__contents' => '--cart-table-bg-color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'tbl__text_color',
				[
					'label' => esc_html__( 'Text Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .woocommerce-cart-form__contents' => '--cart-table-text-color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'tbl__head_bg_color',
				[
					'label' => esc_html__( 'Head - Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .woocommerce-cart-form__contents' => '--cart-table-head-bg-color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'tbl__head_text_color',
				[
					'label' => esc_html__( 'Head - Text Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .woocommerce-cart-form__contents' => '--cart-table-head-text-color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'tbl__border_color',
				[
					'label' => esc_html__( 'Borders Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .woocommerce-cart-form__contents' => '--cart-table-border-color: {{VALUE}}',
					],
				]
			);

			$this->add_responsive_control(
				'tbl__border_size',
				[
					'label' => esc_html__( 'Borders Size', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 10,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .woocommerce-cart-form__contents' => '--cart-table-border-size: {{VALUE}}px',
					],
				]
			);

			$this->add_responsive_control(
				'tbl__border_radius',
				[
					'label' => esc_html__( 'Borders Radius', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 100,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .woocommerce-cart-form__contents' => '--cart-table-border-radius: {{VALUE}}px',
					],
				]
			);

			$this->add_control(
				'tbl__cell_padding',
				[
					'label' => __( 'Cells Padding', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em', '%' ],
					'selectors' => [
						'{{WRAPPER}} .woocommerce-cart-form__contents' => '--cart-table-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);


		$this->end_controls_section();

		$this->start_controls_section(
			'section_totals_style',
			[
				'label' => __( 'Totals Style', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_control(
				'totals__sticky',
				[
					'label' => esc_html__( 'Sticky', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
				]
			);

			$this->add_control(
				'totals__sticky_offset',
				[
					'label' => esc_html__( 'Sticky offset', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 1,
					'condition' => [
						'totals__sticky!' => '',
					],
					'selectors' => [
						'{{WRAPPER}}' => '--sticky-offset: {{VALUE}}px;'
					]
				]
			);

			$this->add_control(
				'totals__style',
				[
					'label' => esc_html__( 'Style', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '2',
					'options' => [
						''  => esc_html__( 'Default', 'rey-core' ),
						'1'  => esc_html__( 'Style #1', 'rey-core' ),
						'2'  => esc_html__( 'Style #2', 'rey-core' ),
					],
					'separator' => 'before'
				]
			);

			$this->add_control(
				'totals__bg_color',
				[
					'label' => esc_html__( 'Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .cart_totals .shop_table' => '--cart-totals-bg-color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'totals__text_color',
				[
					'label' => esc_html__( 'Text Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .cart_totals .shop_table' => '--cart-totals-text-color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'totals__head_bg_color',
				[
					'label' => esc_html__( 'Head - Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .cart_totals .shop_table' => '--cart-totals-head-bg-color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'totals__head_text_color',
				[
					'label' => esc_html__( 'Head - Text Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .cart_totals .shop_table' => '--cart-totals-head-text-color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'totals__border_color',
				[
					'label' => esc_html__( 'Borders Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .cart_totals .shop_table' => '--cart-totals-border-color: {{VALUE}}',
					],
				]
			);

			$this->add_responsive_control(
				'totals__border_size',
				[
					'label' => esc_html__( 'Borders Size', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 10,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .cart_totals .shop_table' => '--cart-totals-border-size: {{VALUE}}px',
					],
				]
			);

			$this->add_control(
				'totals__inner_borders',
				[
					'label' => esc_html__( 'Hide Inner Borders', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'prefix_class' => '--totals-hinb-'
				]
			);

			$this->add_responsive_control(
				'totals__border_radius',
				[
					'label' => esc_html__( 'Borders Radius', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 100,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .cart_totals .shop_table' => '--cart-totals-border-radius: {{VALUE}}px',
					],
				]
			);

			$this->add_control(
				'totals__cell_padding',
				[
					'label' => __( 'Cells Padding', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em', '%' ],
					'selectors' => [
						'{{WRAPPER}} .cart_totals .shop_table' => '--cart-totals-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);




			$this->add_control(
				'totals_btn_title',
				[
				   'label' => esc_html__( 'BUTTON', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->start_controls_tabs( 'totals_btn_tabs_styles');

				$this->start_controls_tab(
					'totals_btn__tab_default',
					[
						'label' => __( 'Default', 'rey-core' ),
					]
				);

					$this->add_responsive_control(
						'totals_btn__text_color',
						[
							'label' => __( 'Text Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .cart_totals .checkout-button' => 'color: {{VALUE}}',
							],
						]
					);

					$this->add_responsive_control(
						'totals_btn__bg_color',
						[
							'label' => __( 'Background Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .cart_totals .checkout-button' => 'background-color: {{VALUE}}',
							],
						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Border::get_type(),
						[
							'name' => 'border',
							'selector' => '{{WRAPPER}} .cart_totals .checkout-button',
							'responsive' => true,
							// 'separator' => 'before',
						]
					);

				$this->end_controls_tab();

				$this->start_controls_tab(
					'totals_btn__tab_hover',
					[
						'label' => __( 'Hover', 'rey-core' ),
					]
				);

					$this->add_responsive_control(
						'totals_btn__text_color_hover',
						[
							'label' => __( 'Hover Text Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .cart_totals .checkout-button:hover' => 'color: {{VALUE}}',
							],
						]
					);

					$this->add_responsive_control(
						'totals_btn__hover_bg_color',
						[
							'label' => __( 'Background Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .cart_totals .checkout-button:hover' => 'background-color: {{VALUE}}',
							],
						]
					);

					$this->add_responsive_control(
						'totals_btn__hover_border_color',
						[
							'label' => __( 'Border Color', 'elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'condition' => [
								'border_border!' => '',
							],
							'selectors' => [
								'{{WRAPPER}} .cart_totals .checkout-button:hover' => 'border-color: {{VALUE}};',
								'{{WRAPPER}} .cart_totals .checkout-button:focus' => 'border-color: {{VALUE}};',
							],
						]
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();

			$this->add_responsive_control(
				'totals_btn__radius',
				[
					'label' => esc_html__( 'Borders Radius', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 100,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .cart_totals .checkout-button' => 'border-radius: {{VALUE}}px',
					],
				]
			);


		$this->end_controls_section();

		$this->start_controls_section(
			'section_process_steps',
			[
				'label' => __( 'Process Steps', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_control(
				'process_enable',
				[
					'label' => esc_html__( 'Enable Process steps', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
				]
			);

			$this->add_control(
				'process_edit_link',
				[
					'type' => \Elementor\Controls_Manager::RAW_HTML,
					'raw' => sprintf(
						__( 'To change the texts or style, please access <a href="%1$s" target="_blank">Customizer > WooCommerce > Cart</a>.', 'rey-core' ),
						add_query_arg( ['autofocus[section]' => \ReyCore\Customizer\Options\Woocommerce\Cart::get_id()], admin_url( 'customize.php' ) ) ),
					'content_classes' => 'rey-raw-html',
					'condition' => [
						'process_enable!' => '',
					],
				]
			);

			$this->add_control(
				'process_color',
				[
					'label' => esc_html__( 'Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-checkoutBar' => '--checkout-bar-color: {{VALUE}}',
					],
					'condition' => [
						'process_enable!' => '',
					],
				]
			);


			$this->add_responsive_control(
				'process_distance',
				[
					'label' => esc_html__( 'Bottom Distance', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 200,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .rey-checkoutBar-wrapper' => 'margin-bottom: {{SIZE}}px',
					],
					'condition' => [
						'process_enable!' => '',
					],
				]
			);

		$this->end_controls_section();
	}

	function __load_coupon_template(){
		?>
		<script type="text/html" id="tmpl-reycore-cart-coupon">
		<?php reycore__get_template_part('template-parts/woocommerce/cart/coupon'); ?>
		</script>
		<?php
	}


	function allow_css_classes_elementor_edit_mode( $status ){

		if( \Elementor\Plugin::$instance->editor->is_edit_mode() ){
			return false;
		}

		return $status;
	}

	function render_custom_content(){
		/**
		 * Custom text
		 */
		if( $custom_text = $this->_settings['custom_text'] ){
			printf('<div class="rey-cart-customText">%s</div>', do_shortcode($custom_text));
		}
	}

	function render_wishlist_products(){
		if( $show_wishlist = $this->_settings['show_wishlist'] ){
			do_action('reycore/woocommerce/wishlist/render_products', 3);
		}
	}

	public function display_custom_layout(){

		if ( WC()->cart->is_empty() ) {

			remove_action( 'woocommerce_cart_is_empty', 'wc_empty_cart_message', 10 );
			$this->render_empty_cart();

		} else {

			// disable process
			if( $this->_settings['process_enable'] !== 'yes' ){
				add_filter('theme_mod_cart_checkout_bar_process', '__return_false', 20);
			}

			// add custom content
			add_action('woocommerce_after_cart_form_wrapper', [$this, 'render_custom_content'], 0);

			// add wislist products
			add_action('woocommerce_after_cart_form_wrapper', [$this, 'render_wishlist_products'], 5);
			// add_action('woocommerce_before_cart_collaterals', 'woocommerce_cart_totals', 5);

			// move cart totals
			remove_action('woocommerce_cart_collaterals', 'woocommerce_cart_totals', 10);
			add_action('woocommerce_before_cart_collaterals', 'woocommerce_cart_totals', 10);

			// move cart cross sells
			remove_action('woocommerce_cart_collaterals', 'woocommerce_cross_sell_display', 10);
			if( $this->_settings['show_cross_sells'] !== '' ){
				add_action('woocommerce_after_cart_form_wrapper', 'woocommerce_cross_sell_display', 10);
			}

			// tweaks
			add_filter('woocommerce_cross_sells_columns', function() {
				return 3;
			}, 20);

			if( $this->_settings['totals_coupon_enable'] === '' ){
				add_filter('woocommerce_coupons_enabled', '__return_false', 20);
			}

			if( $this->_settings['totals_coupon_enable'] !== '' && $this->_settings['totals_coupon_toggle'] !== '' ){

				add_action('reycore/woocommerce/before_woocommerce_cart_coupon', function(){
					reycore_assets()->add_styles('rey-wc-coupon-toggle'); ?>
					<div class="rey-toggleCoupon">
						<button class="rey-toggleCoupon-btn"><?php esc_html_e('Have a Coupon?', 'rey-core') ?></button>
						<div class="rey-toggleCoupon-content">
					<?php
				});

				add_action('woocommerce_cart_coupon', function(){
					?></div></div><?php
				});

			}

			if( $this->_settings['totals_shipping_enable'] === '' ){

				$has_errors = empty( $_POST ) && wc_notice_count( 'error' ) > 0;

				if( ! $has_errors ){

					add_filter('woocommerce_cart_needs_shipping', '__return_false', 20);
					add_action('woocommerce_cart_totals_before_order_total', function(){
						if( $totals_shipping_off_text = $this->_settings['totals_shipping_off_text'] ){
							printf('<tr class="__no-shipping-text"><td colspan="2">%s</td></tr>', $totals_shipping_off_text);
						}

					}, 10);

				}

			}

			// move coupon
			add_action('woocommerce_after_cart', [$this, '__load_coupon_template']);


			// load template
			reycore__get_template_part('template-parts/woocommerce/cart/cart');
		}

	}

	public function render_main(){

		reycore_assets()->add_styles('rey-wc-cart');

		wc_load_cart();

		// Constants.
		wc_maybe_define_constant( 'WOOCOMMERCE_CART', true );

		$nonce_value = wc_get_var( $_REQUEST['woocommerce-shipping-calculator-nonce'], wc_get_var( $_REQUEST['_wpnonce'], '' ) ); // @codingStandardsIgnoreLine.

		// Update Shipping. Nonce check uses new value and old value (woocommerce-cart). @todo remove in 4.0.
		if ( ! empty( $_POST['calc_shipping'] ) && ( wp_verify_nonce( $nonce_value, 'woocommerce-shipping-calculator' ) || wp_verify_nonce( $nonce_value, 'woocommerce-cart' ) ) ) { // WPCS: input var ok.
			\WC_Shortcode_Cart::calculate_shipping();

			// Also calc totals before we check items so subtotals etc are up to date.
			WC()->cart->calculate_totals();
		}

		// Check cart items are valid.
		do_action( 'woocommerce_check_cart_items' );

		// Calc totals.
		WC()->cart->calculate_totals();

		set_query_var('rey-cart-layout', $this->_settings['layout']);

		if( $this->_settings['layout'] === 'custom' ){
			$this->display_custom_layout();
		}
		else {
			if ( WC()->cart->is_empty() ) {
				$this->render_empty_cart();
			} else {
				reycore__get_template_part('template-parts/woocommerce/cart/cart');
			}
		}

	}

	public function render_empty_cart(){

		if( ! (isset($this->_settings['empty_cart_gs']) && ($gs = $this->_settings['empty_cart_gs'])) ){
			$this->__empty_cart();
			return;
		}

		if( ! (isset($this->_settings['empty_cart_mode']) && $mode = $this->_settings['empty_cart_mode']) ){
			$this->__empty_cart();
			return;
		}

		if( 'overwrite' === $mode ){
			echo \ReyCore\Elementor\GlobalSections::do_section($gs, false, true);
			return;
		}

		$pos[$mode] = \ReyCore\Elementor\GlobalSections::do_section($gs, false, true);

		if( isset($pos['before']) ){
			echo $pos['before'];
		}

		$this->__empty_cart();

		if( isset($pos['after']) ){
			echo $pos['after'];
		}
	}

	public function __empty_cart(){

		if( $this->_settings['layout'] === 'custom' ){
			reycore__get_template_part('template-parts/woocommerce/cart/cart-empty');
		}
		else {
			wc_get_template( 'cart/cart-empty.php' );
		}
	}

	public function render_start(){

		$this->_settings = $this->get_settings_for_display();

		$classes = [
			'woocommerce',
			'rey-cartPage',
			'--layout-' . $this->_settings['layout'],
			$this->_settings['tbl__style'] ? '--table-style-' . $this->_settings['tbl__style'] : '',
			$this->_settings['totals__style'] ? '--totals-style-' . $this->_settings['totals__style'] : '',
		];

		if($this->_settings['totals__sticky'] === 'yes' ){
			$classes[] = '--totals-sticky';

			if( ! empty($this->_settings['totals__sticky_offset']) ){
				$this->add_render_attribute( 'wrapper', 'data-sticky-offset', esc_attr($this->_settings['totals__sticky_offset']) );
			}

			reycore_assets()->add_scripts('reycore-sticky');
		}

		$this->add_render_attribute( 'wrapper', 'class', $classes ); ?>

		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>> <?php

		add_filter( 'reycore/woocommerce/loop/prevent_custom_css_classes', [$this, 'allow_css_classes_elementor_edit_mode'], 10 );

	}

	public function render_end(){
		?>
		</div><?php

		remove_filter( 'reycore/woocommerce/loop/prevent_custom_css_classes', [$this, 'allow_css_classes_elementor_edit_mode'], 10 );
	}

	protected function render() {

		reycore_assets()->add_styles($this->get_style_name());
		reycore_assets()->add_scripts( $this->rey_get_script_depends() );

		$this->render_start();
		$this->render_main();
		$this->render_end();

	}

	protected function content_template() {}
}
