<?php
namespace ReyCore\WooCommerce\Tags;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Cart {

	const ELEMENT_DEFAULT_LAYOUT = 'custom';
	const SITE_DEFAULT_LAYOUT = 'classic';

	public $el_settings = [];

	protected $layout;

	public function __construct()
	{
		add_action( 'init', [$this, 'init']);
	}

	function init() {
		add_action( 'wp', [$this, 'wp']);
		add_action( 'woocommerce_before_calculate_totals', [$this, 'handle_early_ajax']);
		add_filter( 'woocommerce_package_rates', [$this, 'hide_flatrate_if_freeshipping'], 100 );
		add_action( 'woocommerce_before_cart_table', [$this, 'enable_cart_controls']);
		add_action( 'woocommerce_after_cart_table', [$this, 'enable_cart_controls__cleanup']);
		add_filter( 'woocommerce_cart_item_quantity', [$this, 'modify_qty_buttons'], 20, 3);
		add_action( 'woocommerce_before_shipping_calculator', [$this, 'before_shipping_calculator'], 10, 4);
	}



	function wp() {

		if( ! is_cart() ){
			return;
		}

		add_action( 'reycore/woocommerce/loop/before_grid', [$this, 'cross_sells_before_grid'], 9);
		add_action( 'reycore/woocommerce/loop/after_grid', [$this, 'cross_sells_after_grid'], 11);
		add_filter( 'reycore/woocommerce/wc_get_template', [$this, 'override_template'], 20);
		add_filter( 'wp_footer', [$this, 'sticky_button'], 0);
	}

	public function before_shipping_calculator(){
		reycore_assets()->add_styles(['rey-form-select2', 'rey-wc-select2']);
	}

	/**
	 * Get layout option
	 *
	 * @since 2.0.0
	 **/
	function get_cart_layout() {

		if( defined('REY_CART_SETTINGS') ){
			$this->el_settings = REY_CART_SETTINGS;
			return $this->layout = isset($this->el_settings['layout']) ? $this->el_settings['layout'] : self::ELEMENT_DEFAULT_LAYOUT;
		}

		if( $this->layout ){
			return $this->layout;
		}

		if( ! class_exists('\Elementor\Plugin') ){
			return $this->layout = self::SITE_DEFAULT_LAYOUT;
		}

		// for WC-AJAX
		$url = wp_get_referer();

		if( ! ( $post_id = absint( url_to_postid( $url ) ) ) ){
			return $this->layout = self::SITE_DEFAULT_LAYOUT;
		}

		$elementor = \Elementor\Plugin::$instance;
		$document = $elementor->documents->get( $post_id );

		if( ! $document->is_built_with_elementor() ){
			return $this->layout = self::SITE_DEFAULT_LAYOUT;
		}

		$data = $document ? $document->get_elements_data() : '';

		if ( empty( $data ) ) {
			return $this->layout = self::SITE_DEFAULT_LAYOUT;
		}

		$_settings = [];

		$elementor->db->iterate_data( $data, function( $element ) use (&$_settings) {
			if ( !empty( $element['widgetType'] ) && $element['widgetType'] === 'reycore-wc-cart' ) {
				$_settings[] = $element['settings'];
			}
		});

		// always get the first one
		if( empty( $_settings ) ) {
			return $this->layout = self::SITE_DEFAULT_LAYOUT;
		}

		$this->el_settings = $_settings[0];

		return $this->layout = isset( $this->el_settings['layout'] ) && !empty( $this->el_settings['layout'] ) ? $this->el_settings['layout'] : self::ELEMENT_DEFAULT_LAYOUT;

	}

	function is_custom_layout(){
		return $this->get_cart_layout() === 'custom';
	}

	/**
	 * Handle Cart's Ajax early calls
	 *
	 * @since 2.0.0
	 **/
	function handle_early_ajax() {
		$this->custom_text_markup();
	}

	function get_setting( $setting, $default ){

		if( isset($this->el_settings[$setting]) ) {
			return $this->el_settings[$setting];
		}

		return $default;
	}

	function custom_text_markup(){

		if( ! $this->is_custom_layout() ){
			return;
		}

		if( $text = $this->get_setting('custom_text_before_proceed', '') ){
			add_action('woocommerce_proceed_to_checkout', function() use ($text){
				printf('<div class="rey-cart-customText --before-proceed">%s</div>', reycore__parse_text_editor($text));
			}, absint( $this->get_setting('custom_text_before_proceed_pos', '1') ) );
		}

	}


	function override_template( $templates ){

		$templates[] = [
			'template_name' => 'cart/cart.php',
			'template' => 'template-parts/woocommerce/cart/cart.php'
		];

		return $templates;

	}

	function hide_flatrate_if_freeshipping( $rates ) {

		if( ! get_theme_mod('cart_checkout_hide_flat_rate_if_free_shipping', false) ){
			return $rates;
		}

		$flat_rate_id = $supports_free_shipping = false;

		foreach ( $rates as $rate_id => $rate ) {

			// there is free shipping
			if( ! $supports_free_shipping ){
				$supports_free_shipping = 'free_shipping' === $rate->get_method_id();
			}

			// there is flat rate, get key
			if( $flat_rate_id === false ){
				$flat_rate_id = 'flat_rate' === $rate->get_method_id() ? $rate_id : false;
			}

		}

		if( $supports_free_shipping && $flat_rate_id !== false ){
			unset($rates[$flat_rate_id]);
		}

		return $rates;
	}

	/**
	 * Get shipping costs
	 */
	function get_shipping_cost( $cart_panel = false ){

		/**
		 * @filter
		 *
		 * Use code below if you want to hide the shipping costs from cart panel & checkout
		 *
		 * add_filter('reycore/woocommerce/cart_checkout/show_shipping', '__return_false', 20);
		 *
		 */
		if( ! apply_filters('reycore/woocommerce/cart_checkout/show_shipping', true) ){
			return;
		}

		if( !WC()->shipping || !WC()->cart || !WC()->countries ){
			return;
		}

		if( ! WC()->cart->show_shipping() ){
			return;
		}

		// Calculate shipping before totals. This will ensure any shipping methods that affect things like taxes are chosen prior to final totals being calculated. Ref: #22708.
		if( isset($_REQUEST['wc-ajax']) && 'get_refreshed_fragments' === $_REQUEST['wc-ajax'] ){
			WC()->cart->calculate_shipping();
			WC()->cart->calculate_totals();
		}

		$shipping_price = '';
		$supports_free_shipping = [];
		$free_shipping_methods = ['free_shipping'];

		/**
		 * When in cart panel, Local pickup is basically free,
		 * however it first requires to be selected in Cart/Checkout pages.
		 * So it's pointless to keep it ans instead, when in Cart panel, will just display "0"
		 */
		if( ! $cart_panel ){
			$free_shipping_methods[] = 'local_pickup';
		}

		foreach ( WC()->shipping()->get_packages() as $i => $package ) :

			foreach ( $package['rates'] as $key => $method ) :

				$supports_free_shipping[] = in_array( $method->get_method_id(), $free_shipping_methods, true );

				/**
				 * Get Shipping cost (based on Cart/Checkout shipping selected method).
				 * But if "Free Shipping" is supported,
				 * just show "Free" regardless of the chosen method in Checkout.
				 */
				if ( WC()->session->chosen_shipping_methods[ $i ] == $key ) {

					$shipping_price_text = '';

					if ( $method->cost ) {
						if ( WC()->cart->display_prices_including_tax() ) {
							$shipping_price_text .= wc_price( $method->cost + $method->get_shipping_tax() );
							if ( $method->get_shipping_tax() > 0 && ! wc_prices_include_tax() ) {
								$shipping_price_text .= ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>';
							}
						} else {
							$shipping_price_text .= wc_price( $method->cost );
							if ( $method->get_shipping_tax() > 0 && wc_prices_include_tax() ) {
								$shipping_price_text .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
							}
						}
					}

					$shipping_price = apply_filters('reycore/woocommerce/cart_panel/shipping_price_text', $shipping_price_text, $method, false, false);
				}

			endforeach;

		endforeach;

		// Just show the Free shipping text if supported (and it's the Cart Panel)
		if( $cart_panel && in_array(true, $supports_free_shipping, true) ){
			return esc_html_x('FREE', 'Shipping status in Checkout when 0.', 'rey-core');
		}

		return wp_kses_post( $shipping_price );
	}

	/**
	 * Enable Qty controls on Cart & disable select (if enabled)
	 * @since 1.6.6
	 */
	function enable_cart_controls(){
		add_filter( 'reycore/woocommerce/add_quantity_controls', '__return_true');
		add_filter('theme_mod_single_atc_qty_controls', '__return_true');
		add_filter('reycore/woocommerce/quantity_field/can_add_select', '__return_false');
	}

	function enable_cart_controls__cleanup(){
		remove_filter( 'reycore/woocommerce/add_quantity_controls', '__return_true');
		remove_filter('theme_mod_single_atc_qty_controls', '__return_true');
		remove_filter('reycore/woocommerce/quantity_field/can_add_select', '__return_false');
	}

	/**
	 * Get cart product
	 *
	 * @since 1.9.2
	 **/
	public static function cart_get_product($cart_item)
	{
		$product_id   = absint( $cart_item['product_id'] );
		$variation_id = absint( $cart_item['variation_id'] );

		// Ensure we don't add a variation to the cart directly by variation ID.
		if ( 'product_variation' === get_post_type( $product_id ) ) {
			$variation_id = $product_id;
			$product_id   = wp_get_post_parent_id( $variation_id );
		}

		return wc_get_product( $variation_id ? $variation_id : $product_id );
	}

	/**
	 * Disable plus minus
	 *
	 * @since 1.9.2
	 **/
	function modify_qty_buttons($quantity, $cart_item_key, $cart_item)
	{

		$product = self::cart_get_product($cart_item);

		if ( ! $product ) {
			return $quantity;
		}

		// prevent showing quantity controls
		if ($product->is_sold_individually() ) {
			return $quantity;
		}

		$defaults = array_map('intval', [
			'input_value'  	=> $cart_item['quantity'],
			'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
			'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
			'step' 		=> apply_filters( 'woocommerce_quantity_input_step', 1, $product ),
		] );

		$quantity = str_replace('cartBtnQty-control --minus --disabled', 'cartBtnQty-control --minus', $quantity);
		$quantity = str_replace('cartBtnQty-control --plus --disabled', 'cartBtnQty-control --plus', $quantity);

		if( $defaults['input_value'] === $defaults['min_value'] ){
			$quantity = str_replace('cartBtnQty-control --minus', 'cartBtnQty-control --minus --disabled', $quantity);
		}
		else if( $defaults['max_value'] > $defaults['min_value'] && $defaults['input_value'] === $defaults['max_value'] ) {
			$quantity = str_replace('cartBtnQty-control --plus', 'cartBtnQty-control --plus --disabled', $quantity);
		}

		return $quantity;
	}


	function cross_sells_buttons_text( $text, $product ){

		if( $custom_text = get_theme_mod('header_cart__cross_sells_btn_text', '') ){

			if(
				$product->is_purchasable() &&
				(
					( $product->get_type() === 'simple' && $product->is_in_stock() ) ||
					apply_filters('reycore/woocommerce/cross_sells_btn_text/supports_variable', $product->get_type() === 'variable', $product)
				)
			){
				return $custom_text;
			}
		}

		return $text;
	}

	public function cross_sells_before_grid(){

		if( ! ( ($q_name = wc_get_loop_prop( 'name' )) && 'cross-sells' === $q_name ) ){
			return;
		}

		add_filter( 'woocommerce_product_add_to_cart_text', [$this, 'cross_sells_buttons_text'], 20, 2);

		reycore_wc__setup_carousel__before();
	}

	public function cross_sells_after_grid(){

		if( ! ( ($q_name = wc_get_loop_prop( 'name' )) && 'cross-sells' === $q_name ) ){
			return;
		}

		remove_filter( 'woocommerce_product_add_to_cart_text', [$this, 'cross_sells_buttons_text'], 20, 2);

		reycore_wc__setup_carousel__after();
	}

	/**
	 * Add a sticky button to the cart page, to proceed to checkout
	 *
	 * @return void
	 */
	public function sticky_button()
	{
		if( ! is_cart() ){
			return;
		}

		if( ! get_theme_mod('cart_sticky_button', false) ){
			return;
		}

		echo '<div class="rey-cart-stickyBtn --dnone-lg">';

			do_action('reycore/woocommerce/cart/sticky_button/before');

			woocommerce_button_proceed_to_checkout();

			do_action('reycore/woocommerce/cart/sticky_button/after');

		echo '</div>';
	}
}
