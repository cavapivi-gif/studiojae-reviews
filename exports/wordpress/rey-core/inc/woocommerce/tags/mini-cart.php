<?php
namespace ReyCore\WooCommerce\Tags;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use Automattic\WooCommerce\Utilities\ArrayUtil;
use Automattic\WooCommerce\Utilities\StringUtil;

class MiniCart {

	private static $settings = [
		'cart_fragment_tweak' => true,
	];

	public $collected_assets = [];

	public function __construct()
	{
		add_action('reycore/woocommerce/init', [$this, 'init']);
	}

	function init(){

		add_action( 'rey/header/row', [$this, 'add_cart_into_header'], 40);
		add_filter( 'woocommerce_add_to_cart_fragments', [$this, 'add_cart_fragment'] );
		add_action( 'woocommerce_before_mini_cart', [$this,'before_mini_cart'], 0);
		add_action( 'woocommerce_before_mini_cart_contents', [$this,'minicart_before_cart_items'], 0);
		add_action( 'woocommerce_mini_cart_contents', [$this,'minicart_after_cart_items'], 999);
		add_action( 'woocommerce_after_mini_cart', [$this,'after_mini_cart'], 200);
		add_action( 'woocommerce_widget_shopping_cart_total', [$this,'show_shipping_minicart'], 15);
		add_filter( 'woocommerce_cart_item_name', [$this,'cart_wrap_name']);
		add_filter( 'woocommerce_widget_cart_item_quantity', [$this,'mini_cart_quantity'], 10, 3 );
		add_action( 'wc_ajax_rey_update_minicart', [$this, 'update_minicart_qty']);
		add_action( 'wc_ajax_rey_apply_coupon', [$this, 'apply_coupon']);
		add_action( 'wc_ajax_rey_remove_coupon', [$this, 'remove_coupon']);
		add_action( 'woocommerce_before_mini_cart_contents', [$this, 'enable_qty_controls_minicart']);
		add_action( 'woocommerce_mini_cart_contents', [$this, 'enable_qty_controls_minicart__remove']);
		remove_action( 'woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_button_view_cart', 10 );
		remove_action( 'woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_proceed_to_checkout', 20 );
		add_action( 'woocommerce_widget_shopping_cart_buttons', [$this, 'shopping_cart_button_view_cart'], 10 );
		add_action( 'woocommerce_widget_shopping_cart_buttons', [$this, 'shopping_cart_proceed_to_checkout'], 20 );
		add_action( 'reycore/elementor/header-cart/render', [$this, 'header_cart_element'], 20 );
		add_filter( 'option_elementor_use_mini_cart_template', [$this, 'elementor_disable_mini_cart_template']);
		add_action( 'wp_enqueue_scripts', [$this, 'relocate_cart_fragments'], 999 );
		add_filter( 'rey/main_script_params', [ $this, 'script_params'], 10 );
		add_action( 'reycore/ajax/register_actions', [ $this, 'register_actions' ] );
		add_action( 'elementor/editor/after_save', [ $this, 'flush_minicart_custom_content_when_empty']);
		add_action( 'woocommerce_before_mini_cart', [$this, 'render_content__before'], 10);
		add_action( 'woocommerce_after_mini_cart', [$this, 'render_content__after'], 10);
		add_action( 'reycore/woocommerce/minicart/before_totals', [$this, 'render_content__before_total'], 10);

		do_action('reycore/woocommerce/minicart_panel', $this);

	}

	public function supports_minicart(){

		if( reycore_wc__is_catalog() || is_cart() || is_checkout() ){
			return;
		}

		if( ! get_theme_mod('header_enable_cart', true) ){
			return;
		}

		return true;
	}

	public static function set_settings( $settings ) {
		foreach ($settings as $key => $value) {
			self::$settings[$key] = $value;
		}
	}

	/**
	 * Filter main script's params
	 *
	 * @since 1.0.0
	 **/
	public function script_params($params)
	{

		$params['header_cart_panel'] = [
			'apply_coupon_nonce'  => wp_create_nonce( 'apply-coupon' ),
			'remove_coupon_nonce' => wp_create_nonce( 'remove-coupon' ),
			'cart_fragment_tweak' => is_checkout() || is_cart() ? false : self::$settings['cart_fragment_tweak'],
		];

		if( get_theme_mod('header_cart__close_extend', false) && ($close_text = get_theme_mod('header_cart__close_text', '')) ){
			$params['header_cart_panel']['close_text'] = $close_text;
		}

		return $params;
	}

	public function register_actions( $ajax_manager ){
		$ajax_manager->register_ajax_action( 'get_empty_minicart_gs_content', [$this, 'ajax__minicart_custom_content_when_empty'], [
			'auth'      => 3,
			'nonce'     => false,
			'assets'    => true,
			'transient' =>  MONTH_IN_SECONDS,
		] );
	}

	public static function is_cart_fragments_js_running(){

		global $wp_scripts;

		$handle = 'wc-cart-fragments';

		if( ! (isset( $wp_scripts->registered[ $handle ] ) && $wp_scripts->registered[ $handle ]) ) {
			return false;
		}

		return $wp_scripts->registered[ $handle ];
	}

	/**
	 * Delay Cart Fragments Ajax execution on page load.
	 *
	 * @return void
	 */
	public function relocate_cart_fragments() {

		if( ! self::$settings['cart_fragment_tweak'] ){
			return;
		}

		if( $wps = self::is_cart_fragments_js_running() ){
			$wps->src = REY_CORE_URI . 'assets/js/woocommerce/cart-fragments.js';
		}

		// Seems like the cart-fragments.js script is not loaded
		// this can cause problems with the panel's contents
		else {

			self::load_panel_assets();

			/**
			 * In case Cart fragments script is not loaded.
			 */
			do_action('reycore/woocommerce/minicart/products_scripts');

		}

	}

	/**
	 * Add Mini Cart to Header (Default)
	 */
	function add_cart_into_header(){

		if( ! $this->supports_minicart() ){
			return;
		}

		reycore_assets()->add_styles(['rey-wc-header-mini-cart-top', 'rey-wc-header-mini-cart', 'rey-overlay', 'reycore-side-panel', 'rey-header-icon']);
		reycore_assets()->add_scripts(['reycore-wc-header-minicart', 'reycore-sidepanel']);

		reycore__get_template_part('template-parts/woocommerce/header-shopping-cart');

		// load panel
		add_action( 'rey/after_site_wrapper', [$this, 'add_cart_panel']);

	}

	/**
	 * Render Elementor element header cart
	 *
	 * @param [type] $element
	 * @return void
	 */
	public function header_cart_element( $element ){

		reycore__get_template_part('template-parts/woocommerce/header-shopping-cart');

		add_action( 'rey/after_site_wrapper', [$this, 'add_cart_panel']);

	}

	/**
	 * Add Cart Panel (triggered by header)
	 * @since 1.0.8
	 */
	function add_cart_panel(){

		if( ! $this->supports_minicart() ){
			return;
		}

		$cart_title = esc_html_x('SHOPPING BAG', 'Shopping bag title in cart panel', 'rey-core');

		if( $custom_text = get_theme_mod( 'header_cart__title', '' ) ){
			$cart_title = $custom_text;
		}

		if( ! get_theme_mod('header_cart__btn_cart__enable', true) ){
			$cart_title = '<a href="' . esc_url( wc_get_cart_url() ) . '">'. $cart_title .'</a>';
		}

		reycore__get_template_part('template-parts/woocommerce/header-shopping-cart-panel', false, false, [
			'inline_buttons' => get_theme_mod('header_cart__btns_inline', true),
			'cart_title' => $cart_title,
		]);

		reycore_assets()->add_styles(['rey-wc-general', 'rey-wc-general-deferred', 'rey-wc-header-mini-cart-top']);

		do_action('reycore/woocommerce/minicart/cart_panel');

	}

	/**
	 * Cart Counter
	 * Displayed a link to the cart including the number of items present
	 *
	 * @return void
	 * @since  1.0.0
	 */
	public static function get_cart_count( $class = '__cart-count' ) {
		printf('<span class="%s">%d</span>', esc_attr($class), is_object( WC()->cart ) ? WC()->cart->get_cart_contents_count() : '');
	}

	/**
	 * Cart total
	 *
	 * @since  1.4.5
	 */
	public static function get_cart_subtotal() {
		return sprintf('<span class="__cart-subtotal">%s</span>', is_object( WC()->cart ) ? WC()->cart->get_cart_subtotal() : '');
	}

	/**
	 * Cart Fragments
	 * Ensure cart contents update when products are added to the cart via AJAX
	 *
	 * @param  array $fragments Fragments to refresh via AJAX.
	 * @return array            Fragments to refresh via AJAX
	 */
	public function add_cart_fragment( $fragments ) {

		ob_start();
		echo self::get_cart_subtotal();
		$fragments['.__cart-subtotal'] = ob_get_clean();

		ob_start();
		echo self::get_cart_count();
		$fragments['.__cart-count'] = ob_get_clean();

		$fragments['_count_'] =  WC()->cart->get_cart_contents_count();

		if( ! empty($this->collected_assets) ){
			$fragments['_cart_assets_'] = $this->collected_assets;
		}

		return $fragments;
	}

	public function before_mini_cart(){

		if( wp_doing_ajax() ){
			reycore_assets()->register_assets();
			reycore_assets()->collect_start('minicart');
		}

		set_query_var('rey-cart-panel', true);
	}


	/**
	 * After mini cart
	 *
	 * @since 2.4.0
	 **/
	public function after_mini_cart() {

		set_query_var('rey-cart-panel', false);

		/**
		 * This filter is used to determine if the Cart Panel will include
		 * products, cross-sells, recent products etc.
		 * This way, it'll enable a hook which can be used to load
		 * styles inside.
		 */
		if( apply_filters('reycore/woocommerce/minicarts/supports_products', false) ){
			/**
			 * This hook should be used for modules (Wishlist, Quickview etc.) to
			 * load their scripts in order to be collected
			 * but the MiniCart, and included in the panel load.
			 */
			do_action('reycore/woocommerce/minicart/products_scripts');
		}

		if( wp_doing_ajax() ){

			self::load_panel_assets();

			if( empty($this->collected_assets) ){
				$this->collected_assets = reycore_assets()->collect_end('minicart', true);
			}

		}

		$this->minicart_custom_content_when_empty();

	}

	public static function load_panel_assets(){
		reycore_assets()->add_styles(['rey-simple-scrollbar', 'rey-wc-header-mini-cart', 'rey-overlay', 'reycore-side-panel', 'rey-header-icon']);
		reycore_assets()->add_scripts( ['rey-simple-scrollbar', 'reycore-wc-product-page-qty-controls'] );
	}

	/**
	 * Wrap mini cart items
	 *
	 * @since 1.3.7
	 **/
	function minicart_before_cart_items()
	{
		echo '<div class="woocommerce-mini-cart-inner">';
	}

	/**
	 * Wrap mini cart items
	 *
	 * @since 1.3.7
	 **/
	function minicart_after_cart_items()
	{
		echo '</div>';

		$this->add_continue_shopping_button();
	}


	/**
	 * Checks if the request is for get_refreshed_fragments and the cart is empty
	 *
	 * @return boolean
	 */
	public static function is_get_refreshed_fragments() {

		if ( ! isset( $_REQUEST['wc-ajax'] ) ) {
			return false;
		}

		if ( 'get_refreshed_fragments' !== $_REQUEST['wc-ajax'] ) {
			return false;
		}

		if ( ! empty( $_COOKIE['woocommerce_cart_hash'] ) ) {
			return false;
		}

		if ( ! empty( $_COOKIE['woocommerce_items_in_cart'] ) ) {
			return false;
		}

		return true;
	}


	/**
	 * Adds placeholder into Cart Panel when empty
	 *
	 * @since 1.4.3
	 **/
	function minicart_custom_content_when_empty()
	{
		if(
			WC()->cart->is_empty() &&
			($header_cart_gs = get_theme_mod('header_cart_gs', 'none')) && $header_cart_gs !== 'none'
		){
			printf('<div class="rey-emptyMiniCartGs" data-gsid="%d"></div>', esc_attr($header_cart_gs));
		}
	}


	/**
	 * Adds ajax call for minicart's global section when empty
	 *
	 * @since 2.4.0
	 **/
	function ajax__minicart_custom_content_when_empty( $data )
	{
		if( class_exists('\ReyCore\Elementor\GlobalSections') && isset($data['gsid']) && ($gs_id = absint($data['gsid'])) ){
			return \ReyCore\Elementor\GlobalSections::do_section( $gs_id, true, true );
		}
	}

	/**
	 * Flush minicart's empty global section cache
	 *
	 * @param int $post_id
	 * @return void
	 */
	public function flush_minicart_custom_content_when_empty( $post_id ){

		if(
			! ( ($header_cart_gs = get_theme_mod('header_cart_gs', 'none')) && $header_cart_gs !== 'none' )
		){
			return;
		}

		if( absint($header_cart_gs) !== $post_id ){
			return;
		}

		\ReyCore\Helper::clean_db_transient( \ReyCore\Ajax::AJAX_TRANSIENT_NAME . '_get_empty_minicart_gs_content' );
	}

	/**
	 * CHecks if calculator is allowed to be shown
	 *
	 * @return void
	 */
	public static function maybe_show_shipping_calculator(){

		if ( ! WC()->cart->needs_shipping() ) {
			return;
		}

		if ( 'no' === get_option( 'woocommerce_enable_shipping_calc', 'yes' ) ) {
			return;
		}

		return true;
	}



	/**
	 * Show Shipping in minicart
	 *
	 * @since 1.6.3
	 **/
	function show_shipping_minicart()
	{
		if( ! get_theme_mod('header_cart_show_shipping', false) ){
			return;
		}

		$cost = reycore_wc__get_tag('cart')->get_shipping_cost(true);
		$cost_html = apply_filters('reycore/minicart/shipping/pre_cost_html', '', $cost, $this);

		if( $cost ){
			$cost_html = sprintf('<span class="__shipping-cost">%s</span>', $cost);
		}

		if( ! $cost_html ){
			return;
		}

		$cost_html = apply_filters('reycore/minicart/shipping/cost_html', $cost_html, $cost, $this);

		printf(
			'<div class="minicart-total-row minicart-total-row--shipping"><div class="minicart-total-row-head">%1$s</div><div class="minicart-total-row-content">%2$s</div></div>',
			esc_html__( 'Shipping', 'rey-core' ),
			$cost_html
		);

		do_action('reycore/woocommerce/minicart/after_shipping', $cost, $this);

	}

	/**
	 * Change cart remove text
	 *
	 * @since 1.0.0
	 */
	function cart_wrap_name($html) {
		return sprintf('<div class="woocommerce-mini-cart-item-title">%s</div>', $html);
	}

	public function elementor_disable_mini_cart_template( $opt ){

		if( ! apply_filters('reycore/minicart/force_disable_elementor_template', true) ){
			return $opt;
		}

		if( get_theme_mod('header_cart__panel_disable', false) ){
			return $opt;
		}

		return 'no';
	}

	/**
	 * Check if Elementor PRO mini cart template is loaded
	 *
	 * @since 1.6.7
	 **/
	public static function mini_cart_elementorpro_template_active()
	{
		return 'yes' === get_option( 'elementor_use_mini_cart_template', 'no' );
	}

	public static function get_product_price($cart_item){

		if( apply_filters('reycore/woocommerce/cartpanel/show_discount', $cart_item['data']->is_on_sale(), $cart_item ) ){
			return wc_format_sale_price(
				wc_get_price_to_display( $cart_item['data'], ['price' => $cart_item['data']->get_regular_price()] ),
				wc_get_price_to_display( $cart_item['data'] ) ) .
				$cart_item['data']->get_price_suffix();
		}
		else {
			return WC()->cart->get_product_price( $cart_item['data'] );
		}

	}

	/**
	 * Add quantity in Mini-Cart
	 *
	 * @since 1.6.6
	 **/
	function mini_cart_quantity($html, $cart_item, $cart_item_key)
	{

		if ( ! get_theme_mod('header_cart_show_qty', true) ) {
			return $html;
		}

		if ( self::mini_cart_elementorpro_template_active() ) {
			return $html;
		}

		if ( ! ($product = \ReyCore\WooCommerce\Tags\Cart::cart_get_product( $cart_item )) ) {
			return $html;
		}

		// prevent showing quantity controls
		if ( ! apply_filters('reycore/woocommerce/cartpanel/show_qty', ! $product->is_sold_individually(), $cart_item ) ) {
			return $html;
		}

		$defaults = [
			'input_value'  	=> $cart_item['quantity'],
			'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
			'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
			'step' 		=> apply_filters( 'woocommerce_quantity_input_step', 1, $product ),
		];

		$is_hidden = $defaults['min_value'] > 0 && $defaults['min_value'] === $defaults['max_value'];

		if( $is_hidden ){
			$defaults['readonly'] = true;
			add_filter( 'woocommerce_quantity_input_type', [$this, 'set_text_quantity_input_type']);
		}

		add_filter( 'reycore/woocommerce/add_quantity_controls', '__return_true');

		$quantity = woocommerce_quantity_input( $defaults, $cart_item['data'], false );

		remove_filter( 'reycore/woocommerce/add_quantity_controls', '__return_true');
		remove_filter( 'woocommerce_quantity_input_type', [$this, 'set_text_quantity_input_type']);

		$quantity = str_replace([ 'cartBtnQty-control --minus --disabled', 'cartBtnQty-control --plus --disabled' ], [ 'cartBtnQty-control --minus', 'cartBtnQty-control --plus' ], $quantity);

		if( $is_hidden ) {
			$quantity = str_replace([ 'cartBtnQty-control --minus', 'cartBtnQty-control --plus' ], [ 'cartBtnQty-control --minus --disabled', 'cartBtnQty-control --plus --disabled' ], $quantity);
		}
		else {
			if( $defaults['input_value'] == $defaults['min_value'] ){
				$quantity = str_replace('cartBtnQty-control --minus', 'cartBtnQty-control --minus --disabled', $quantity);
			}
			else if( $is_hidden || ($defaults['max_value'] > $defaults['min_value'] && $defaults['input_value'] == $defaults['max_value']) ) {
				$quantity = str_replace('cartBtnQty-control --plus', 'cartBtnQty-control --plus --disabled', $quantity);
			}
		}

		$price_html = self::get_product_price( $cart_item );

		$should_show_subtotal = $cart_item['quantity'] > 1 && isset($cart_item['line_total']) && get_theme_mod('header_cart_show_subtotal', true);

		$product_price = sprintf(
			'<span class="woocommerce-mini-cart-price">%1$s %2$s</span>',
			apply_filters( 'woocommerce_cart_item_price', $price_html, $cart_item, $cart_item_key, 'mini-cart' ),
			apply_filters( 'woocommerce_cart_item_subtotal_display', $should_show_subtotal) ? '<span class="__item-total">' . apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $cart_item['data'], $cart_item['quantity'] ), $cart_item, $cart_item_key ) . '</span>' : ''
		);

		return sprintf('<div class="quantity-wrapper">%s %s</div>',
			$quantity,
			$product_price
		);
	}

	public function set_text_quantity_input_type(){
		return 'text';
	}

	/**
	 * update cart
	 *
	 * @since 1.6.6
	 **/
	public function update_minicart_qty()
	{

		if( self::mini_cart_elementorpro_template_active() ){
			wp_send_json_error(esc_html__('Please disable Elementor Mini-Cart Template.', 'rey-core'));
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$cart_item_key = wc_clean( isset( $_POST['cart_item_key'] ) ? wp_unslash( $_POST['cart_item_key'] ) : '' );
		$cart_item_qty = wc_clean( isset( $_POST['cart_item_qty'] ) && ($item_qty = $_POST['cart_item_qty']) ? filter_var( $item_qty, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION ) : '' );

		if ( $cart_item_key && $cart_item_qty ) {

			$cart_item_qty = apply_filters('woocommerce_update_cart_quantity', $cart_item_qty, $cart_item_key);

			WC()->cart->set_quantity($cart_item_key, $cart_item_qty, $refresh_totals = true);

			self::get_cart_fragments();
		}

		wp_send_json_error();
	}

	/**
	 * Enable Qty controls on mini-cart & disable select (if enabled)
	 * @since 1.6.6
	 */
	function enable_qty_controls_minicart(){
		add_filter('theme_mod_single_atc_qty_controls', '__return_true');
		add_filter('reycore/woocommerce/quantity_field/can_add_select', '__return_false');
	}

	function enable_qty_controls_minicart__remove(){
		remove_filter('theme_mod_single_atc_qty_controls', '__return_true');
		remove_filter('reycore/woocommerce/quantity_field/can_add_select', '__return_false');
	}

	public function atc_button_convert_to_minimal( $args ){

		if( isset($args['class']) ){

			$classes = explode(' ', $args['class']);

			if( in_array('button', $classes, true) ){
				if (($key = array_search('button', $classes)) !== false) {
					unset($classes[$key]);
				}
			}

			reycore_assets()->add_styles('rey-buttons');

			$classes[] = 'btn';
			$classes[] = 'btn-line-active';

			$args['class'] = implode(' ', $classes);
		}

		return $args;
	}

	public static function cs_cart_btn_class( $html ){
		$html = str_replace('"button ', '"btn btn-line-active ', $html);
		$html = str_replace(' button ', ' btn btn-line-active ', $html);
		return $html;
	}

	public static function cs_cart_loop_skin(){
		return 'default';
	}

	public function render_cross_sells( $args = [] ){

		$args = wp_parse_args($args, [
			'class' => ''
		]); ?>

		<# for (var i = 0; i < items.length; i++) { #>
		<div class="rey-crossSells-item __cart-product <?php echo esc_attr($args['class']) ?>" data-id="{{items[i].id}}">
			<div class="rey-crossSells-itemThumb">
				<a href="{{items[i].link}}" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">
					{{{items[i].image}}}
				</a>
			</div>
			<div class="rey-crossSells-itemContent">
				<?php do_action('reycore/woocommerce/cart/crosssells/before'); ?>
				<<?php echo reycore_wc__minicart_product_title_tag() ?> class="rey-crossSells-itemTitle"><a href="{{items[i].link}}">{{{items[i].title}}}</a></<?php echo reycore_wc__minicart_product_title_tag() ?>>
				<span class="price rey-loopPrice">{{{items[i].price}}}</span>
				<div class="rey-crossSells-itemButtons">
					{{{items[i].button}}}
					<?php do_action('reycore/woocommerce/cart/crosssells/after'); ?>
				</div>
			</div>
		</div>
		<# } #>

		<?php
	}

	/**
	 * Utility to prepare the cross-sells products for fragments
	 *
	 * @param array $products
	 * @return array
	 */
	public function prepare_products_data_fragment( $products = [], $type = 'cross_sells', $validations = [] ){

		$_p = [];

		$validations = wp_parse_args($validations, [
			'purchasable' => true,
			'stock' => true,
		]);

		foreach ($products as $id) {

			if( ! ($product = wc_get_product( $id )) ){
				continue;
			}

			if ( $validations['purchasable'] && ! $product->is_purchasable() ) {
				continue;
			}

			if ( $validations['stock'] && 'yes' == get_option( 'woocommerce_hide_out_of_stock_items' ) && ! $product->is_in_stock() ) {
				continue;
			}

			$GLOBALS['product'] = $product;

			$_data['id']    = $id;
			$_data['title'] = $product->get_title();
			$_data['image'] = $product->get_image();
			$_data['link']  = get_the_permalink($id);
			$_data['price'] = $product->get_price_html();

			ob_start();
			$this->get_clean_atc_button();
			$_data['button'] = ob_get_clean();

			$_p[] = apply_filters("reycore/woocommerce/{$type}/item", $_data, $product);

			unset($GLOBALS['product']);

		}

		return $_p;
	}


	/**
	 * Retrieve a clean ATC. button (lined)
	 *
	 * @return void
	 */
	public function get_clean_atc_button(){

		$cart_tag = reycore_wc__get_tag('cart');

		// don't change text for variable products
		add_filter('reycore/woocommerce/cross_sells_btn_text/supports_variable', '__return_false');
		// add the custom text in buttons
		add_filter('woocommerce_product_add_to_cart_text', [$cart_tag, 'cross_sells_buttons_text'], 20, 2);
		add_filter('woocommerce_loop_add_to_cart_args', [$this, 'atc_button_convert_to_minimal'], 20);

		woocommerce_template_loop_add_to_cart([
			'wrap_button' => false,
			'supports_qty' => false,
		]);

		remove_filter('reycore/woocommerce/cross_sells_btn_text/supports_variable', '__return_false');
		remove_filter('woocommerce_product_add_to_cart_text', [$cart_tag, 'cross_sells_buttons_text'], 20);
		remove_filter('woocommerce_loop_add_to_cart_args', [$this, 'atc_button_convert_to_minimal'], 20);

	}

	/**
	 * Output the view cart button.
	 */
	function shopping_cart_button_view_cart() {

		if( ! get_theme_mod('header_cart__btn_cart__enable', true) ){
			return;
		}

		$text = esc_html__( 'View cart', 'woocommerce' );

		if( $custom_text = get_theme_mod('header_cart__btn_cart__text', '' ) ){
			$text = $custom_text;
		}

		$args = [
			'url'  => esc_url( wc_get_cart_url() ),
			'text' => $text,
		];

		reycore_assets()->add_styles('rey-buttons');

		echo apply_filters('reycore/minicart/view_cart_button', sprintf('<a href="%s" class="btn btn-secondary wc-forward button--cart">%s</a>', $args['url'], $args['text'] ), $args );

	}

	function shopping_cart_proceed_to_checkout() {

		if( site_url() === wc_get_checkout_url() ){
			return;
		}

		$args = [
			'url'  => esc_url( wc_get_checkout_url() ),
			'text' => ($checkout_button_text = get_theme_mod('header_cart__btn_checkout__text', '')) ? $checkout_button_text : esc_html__( 'Checkout', 'woocommerce' ),
			'icon' => rey__arrowSvg(['right' => true, 'tag' => 'span']),
		];

		reycore_assets()->add_styles('rey-buttons');

		echo apply_filters('reycore/minicart/proceed_to_checkout_button', sprintf('<a href="%s" class="btn btn-primary checkout wc-forward">%s %s</a>', $args['url'], $args['text'], $args['icon'] ), $args );
	}

	function add_continue_shopping_button(){

		if( ! get_theme_mod('header_cart__close_extend', false) ){
			return;
		}

		if( ! get_theme_mod('header_cart__continue_shop', false) ){
			return;
		}

		reycore_assets()->add_styles('rey-buttons');

		echo apply_filters('reycore/minicart/continue_shopping_button', sprintf(
			'<div class="rey-cartPanel-continue"><button class="btn btn-line-active">%s</button></div>',
			esc_html__( 'Continue shopping', 'woocommerce' )
		) );
	}

	public function render_content__before(){
		echo self::render_custom_content('before');
	}

	public function render_content__after(){
		echo self::render_custom_content('after');
	}

	public function render_content__before_total(){
		echo self::render_custom_content('before_total');
	}

	public static function render_custom_content( $type ){

		if( ! ($gs = get_theme_mod("header_cart__gs_{$type}", '')) ){
			return;
		}

		$before = sprintf('<div class="__content" data-pos="%s">', esc_attr($type));
		$after = '</div>';

		if( 'text' === $gs ){
			return $before . (($text = trim( get_theme_mod("header_cart__gs_{$type}__text", '') )) ? $text : '') . $after;
		}

		if( class_exists('\ReyCore\Elementor\GlobalSections') && ($gs_id = absint($gs)) ){
			return $before . \ReyCore\Elementor\GlobalSections::do_section( $gs_id, true, true ) . $after;
		}

	}

	public static function get_cart_fragments(){

		$data = [];

		if( ! did_action('woocommerce_ajax_added_to_cart') && isset($_REQUEST['add-to-cart']) ){
			do_action( 'woocommerce_ajax_added_to_cart', apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_REQUEST['add-to-cart'] ) ) );
		}

		ob_start();
		woocommerce_mini_cart();
		$wscc['div.widget_shopping_cart_content'] = sprintf('<div class="widget_shopping_cart_content">%s</div>', ob_get_clean());

		$data = [
			'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', $wscc ),
			'cart_hash' => WC()->cart->get_cart_hash(),
		];

		// enforce the `div.widget_shopping_cart_content` fragment
		$data['fragments'] = array_merge($data['fragments'], $wscc);

		// Notices
		ob_start();
		wc_print_notices();
		$data['notices'] = ob_get_clean();

		$data = apply_filters('reycore/woocommerce/cart/data', $data);

		wp_send_json( $data );
		die();
	}

	/**
	 * AJAX apply coupon on checkout page.
	 */
	public static function apply_coupon() {

		check_ajax_referer( 'apply-coupon', 'security' );

		$coupon_code   = ArrayUtil::get_value_or_default( $_POST, 'coupon_code' );
		$billing_email = ArrayUtil::get_value_or_default( $_POST, 'billing_email' );

		if ( is_string( $billing_email ) && is_email( $billing_email ) ) {
			wc()->customer->set_billing_email( $billing_email );
		}

		if ( ! StringUtil::is_null_or_whitespace( $coupon_code ) ) {
			WC()->cart->add_discount( wc_format_coupon_code( wp_unslash( $coupon_code ) ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		} else {
			wc_add_notice( \WC_Coupon::get_generic_coupon_error( \WC_Coupon::E_WC_COUPON_PLEASE_ENTER ), 'error' );
		}

		reycore_assets()->add_styles(['rey-wc-notices']);

		self::get_cart_fragments();
	}

	/**
	 * AJAX remove coupon on cart and checkout page.
	 */
	public static function remove_coupon() {
		check_ajax_referer( 'remove-coupon', 'security' );

		$coupon = isset( $_POST['coupon'] ) ? wc_format_coupon_code( wp_unslash( $_POST['coupon'] ) ) : false; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( StringUtil::is_null_or_whitespace( $coupon ) ) {
			wc_add_notice( __( 'Sorry there was a problem removing this coupon.', 'woocommerce' ), 'error' );
		} else {
			WC()->cart->remove_coupon( $coupon );
			wc_add_notice( __( 'Coupon has been removed.', 'woocommerce' ) );
		}

		reycore_assets()->add_styles(['rey-wc-notices']);

		self::get_cart_fragments();
	}

	/**
	 * Legacy alias
	 *
	 * @return void
	 */
	public static function cart_link($class=''){return self::get_cart_count();}
	public static function cart_total(){return self::get_cart_subtotal();}

}
