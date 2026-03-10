<?php
namespace ReyCore\WooCommerce\Tags;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Checkout {

	const CUSTOM_LAYOUT = 'custom';
	const DEFAULT_LAYOUT = 'classic';

	const KEY__LAYOUT = 'rey_checkout_layout';
	const KEY__REVIEW_COUPON_ENABLE = 'rey_review_coupon_enable';
	const KEY__REVIEW_COUPON_TOGGLE = 'rey_review_coupon_toggle';

	protected $layout;
	protected $element_settings;

	public function __construct() {
		add_action( 'init', [$this, 'init']);
		add_filter('reycore/assets/settings', [$this, 'disable_assets'], 20);
		add_action( 'wp_enqueue_scripts', [$this, 'unload_assets_when_disabled'], 100 );
	}

	function init(){

		if( defined('REYCORE_DISABLE_CHECKOUT') && REYCORE_DISABLE_CHECKOUT ){
			return;
		}

		add_action( 'wp', [$this, 'onwp']);
		add_action( 'reycore/ajax/register_actions', [ $this, 'register_actions' ] );
		add_filter( 'theme_mod_social__enable', [$this, 'checkout_disable_social_icons'], 20);
		add_filter( 'woocommerce_cart_item_name', [$this, 'checkout__classic_add_thumb'], 100, 3);
		add_action( 'woocommerce_thankyou', [$this, 'checkout__add_buttons_order_confirmation']);
		add_action( 'woocommerce_before_cart', [$this, 'cart_progress'] );
		add_action( 'woocommerce_before_checkout_form', [$this, 'cart_progress'], 5 );
		add_action( 'woocommerce_check_cart_items', [$this, 'load_styles'], 10 );
		add_filter( 'rey/main_script_params', [ $this, 'script_params'], 20 );
		add_action( 'wp_enqueue_scripts', [$this, 'load_checkout_styles'] );
		add_action( 'woocommerce_checkout_before_customer_details', [$this, 'add_settings_fields']);
		add_action( 'woocommerce_checkout_update_order_review', [$this, 'handle_early_ajax']);
		add_filter( 'woocommerce_update_order_review_fragments', [$this, 'update_order_fragments']);
		add_action( 'reycore/woocommerce/checkout/before_information', [$this, 'checkout_express'], 10);
		add_filter( 'woocommerce_registration_error_email_exists', [$this, 'checkout_change_login_link'], 20);
		add_filter( 'woocommerce_checkout_redirect_empty_cart', [$this, 'checkout_redirect_empty_cart'], 20);
		add_filter( 'reycore/woocommerce/wc_get_template', [$this, 'add_templates'], 20);
		add_filter( 'woocommerce_checkout_fields', [$this, 'customize_checkout_fields'], 20);
		add_action( 'woocommerce_checkout_update_order_meta', [$this, 'save_checkout_fields'] );
		add_filter( 'woocommerce_cart_item_subtotal', [$this, 'display_discount_subtotal'], 10, 2);
	}

	public function disable_assets($settings){

		if( ! (defined('REYCORE_DISABLE_CHECKOUT') && REYCORE_DISABLE_CHECKOUT) ){
			return $settings;
		}

		if( ! (defined('REYCORE_DISABLE_CHECKOUT_ASSETS') && REYCORE_DISABLE_CHECKOUT_ASSETS) ){
			return $settings;
		}

		if( $disable_by_path = apply_filters('reycore/checkout/disable_assets_by_path', '') ){
			if( strpos($_SERVER['REQUEST_URI'], $disable_by_path) !== false ){
				$settings['disable_assets'] = true;
			}
		}
		else if( reycore__get_post_id_by_url() === absint(get_option( 'woocommerce_checkout_page_id' )) ){
			$settings['disable_assets'] = true;
		}

		return $settings;
	}

	public function unload_assets_when_disabled(){

		if( defined('REYCORE_DISABLE_CHECKOUT') && REYCORE_DISABLE_CHECKOUT && defined('REYCORE_DISABLE_CHECKOUT_ASSETS') && REYCORE_DISABLE_CHECKOUT_ASSETS ){
			wp_dequeue_script('wc-jquery-blockui');
			wp_deregister_script('wc-jquery-blockui');
		}

	}

	public function onwp()
	{
		if( ! is_checkout() ){
			return;
		}

		$this->set_element_settings();

		if( apply_filters('reycore/checkout/form_row_styles', true) ){
			reycore_assets()->add_styles('rey-form-row');
		}

		reycore_assets()->add_styles(['rey-form-select2', 'rey-buttons', 'rey-wc-checkbox-label', 'rey-wc-select2']);

		$this->distraction_free_checkout();
	}

	public function script_params($params)
	{
		$params['checkout'] = [
			'error_text' => esc_html__('This information is required.', 'rey-core')
		];

		return $params;
	}

	public function load_checkout_styles($classes)
	{

		if ( true === wc_string_to_bool( get_option( 'woocommerce_checkout_highlight_required_fields', 'yes' ) ) ) {
			wp_add_inline_style( 'woocommerce-inline', '.woocommerce form .form-row abbr.required { visibility: visible; }' );
		}

		return $classes;
	}

	public function set_element_settings(){

		if( ! class_exists('\Elementor\Plugin') ){
			return;
		}

		$page_id = wc_get_page_id( 'checkout' );

		if( ! ( $page_id = get_the_ID() ) ){
			return;
		}

		$data = null;

		$document = \Elementor\Plugin::$instance->documents->get( $page_id );

		if ( ! $document || ! $document->is_built_with_elementor() ) {
			return false;
		}

		$data = $document->get_elements_data();

		if ( ! $data ) {
			return;
		}

		$checkout_element = null;

		 \Elementor\Plugin::$instance->db->iterate_data( $data, function( $element ) use (&$checkout_element) {

			if( ! is_null($checkout_element) ){
				return;
			}

			if ( empty( $element['widgetType'] ) || 'reycore-wc-checkout' !== $element['widgetType'] ) {
				return;
			}

			$checkout_element = $element;

		} );

		if( is_null($checkout_element) ){
			return;
		}

		if( empty($checkout_element['settings']) ){
			return;
		}

		$this->element_settings = $checkout_element['settings'];
	}

	/**
	 * Retrieve Element settings
	 *
	 * @return array|null
	 */
	public function get_element_settings( $setting = '' ){

		if( is_null($this->element_settings) && isset($GLOBALS['reycore_checkout_settings']) && reycore__elementor_edit_mode() ){
			$this->element_settings = $GLOBALS['reycore_checkout_settings'];
		}

		if( $setting ){
			if( isset($this->element_settings[$setting]) ){
				return $this->element_settings[$setting];
			}
			else {
				return; // null
			}
		}

		return $this->element_settings;
	}

	/**
	 * Get checkout layout option
	 *
	 * @since 2.0.0
	 **/
	function get_checkout_layout() {

		$layout = self::DEFAULT_LAYOUT;

		// ajax calls
		// returns empty if not custom layout
		if( wp_doing_ajax() && self::CUSTOM_LAYOUT === $this->get_custom_layout_ajax_settings(self::KEY__LAYOUT) ){
			return self::CUSTOM_LAYOUT;
		}

		if( $settings = $this->get_element_settings() ){
			$layout = isset($settings['layout']) ? $settings['layout'] : self::CUSTOM_LAYOUT;
		}

		return $layout;
	}

	public function is_custom_layout(){
		return apply_filters('reycore/woocommerce/checkout/force_custom_layout', $this->get_checkout_layout() === self::CUSTOM_LAYOUT);
	}

	function update_order_fragments($fragments){

		if( ! $this->is_custom_layout() ){
			return $fragments;
		}

		ob_start();
			wc_cart_totals_order_total_html();
		$fragments['#rey-checkoutPage-review-toggle__total'] = sprintf('<span id="rey-checkoutPage-review-toggle__total" class="__total">%s</span>', ob_get_clean());

		ob_start();
			reycore__get_template_part('template-parts/woocommerce/checkout/custom-shipping-methods');
		$fragments['.rey-checkout-shipping'] = ob_get_clean();

		// Shipping address in "Review Order block"
		if( $shipping_address_fragment = $this->get_shipping_address_block() ){
			$fragments['.rey-formReview-content--address_ship'] = sprintf('<div class="rey-formReview-content--address_ship">%s</div>', $shipping_address_fragment);
		}

		// Billing address in "Review Order block"
		if( $billing_address_fragment = $this->get_billing_address_block() ){
			$fragments['.rey-formReview-content--address_bill'] = sprintf('<div class="rey-formReview-content--address_bill">%s</div>', $billing_address_fragment);
		}

		return $fragments;
	}

	public function get_shipping_address_block(){

		$shipping_address = '';
		foreach ( WC()->shipping()->get_packages() as $package ) {
			$shipping_address = WC()->countries->get_formatted_address( apply_filters('reycore/woocommerce/checkout/address_fields', $package['destination'], 'shipping'), ', ' );
			break;
		}

		return $shipping_address;
	}

	public function get_billing_address_block(){

		return WC()->countries->get_formatted_address( apply_filters('reycore/woocommerce/checkout/address_fields', [
			'company'   => WC()->customer->get_billing_company(),
			'address_1' => WC()->customer->get_billing_address(),
			'address_2' => WC()->customer->get_billing_address_2(),
			'city'      => WC()->customer->get_billing_city(),
			'state'     => WC()->customer->get_billing_state(),
			'postcode'  => WC()->customer->get_billing_postcode(),
			'country'   => WC()->customer->get_billing_country(),
		], 'billing'), ', ' );

	}

	/**
	 * Retrieve custom layout settings from AJAX post data.
	 *
	 * @param string $setting
	 * @return null | string
	 */
	public function get_custom_layout_ajax_settings( $setting = '' ){

		if( empty($setting) ){
			return;
		}

		// If options keys are already posted (eg: wc-ajax: checkout ).
		// The keys are sent directly
		if( isset($_REQUEST[$setting]) ){
			// ensure it's a custom layout
			if( ! (isset($_REQUEST[self::KEY__LAYOUT]) && self::CUSTOM_LAYOUT === $_REQUEST[self::KEY__LAYOUT]) ){
				return;
			}
			return reycore__clean($_REQUEST[$setting]);
		}

		// Must receive the keys through post_data (eg: wc-ajax: update_order_review )
		if( ! (isset($_REQUEST['post_data']) && ($post_data = reycore__clean($_REQUEST['post_data']))) ){
			return;
		}

		parse_str($post_data, $ajax_settings);

		// ensure it's a custom layout
		if( ! (isset($ajax_settings[self::KEY__LAYOUT]) && self::CUSTOM_LAYOUT === $ajax_settings[self::KEY__LAYOUT]) ){
			return;
		}

		if( ! isset($ajax_settings[$setting]) ){
			return;
		}

		return $ajax_settings[$setting];
	}

	public function add_settings_fields(){

		if( ! ( $settings = $this->get_element_settings() ) ){
			return;
		}

		printf('<input type="hidden" name="%s" value="%s">',
			self::KEY__LAYOUT,
			isset($settings['layout']) ? esc_attr($settings['layout']) : self::CUSTOM_LAYOUT
		);

		printf('<input type="hidden" name="%s" value="%s">',
			self::KEY__REVIEW_COUPON_ENABLE,
			isset($settings['review_coupon_enable']) && $settings['review_coupon_enable'] === '' ? '' : 'yes'
		);

		printf('<input type="hidden" name="%s" value="%s">',
			self::KEY__REVIEW_COUPON_TOGGLE,
			isset($settings['review_coupon_toggle']) && $settings['review_coupon_toggle'] !== '' ? 'yes' : ''
		);

	}

	public function register_actions($ajax_manager){
		$ajax_manager->register_ajax_action( 'custom_layout_process_data', [$this, 'ajax__process_data'], 3 );
	}

	public function ajax__process_data( $data ){

		if( empty($data['fields']) ){
			return ['error' => ['message' => 'Fields not provided']];
		}

		$key = ! empty($data['shipping']) && 'true' == $data['shipping'] ? 'shipping_' : 'billing_';

		$country = null;

		if( ! empty($data['fields'][ $key . 'country' ]) ){
			if( ! (($country = $data['fields'][ $key . 'country' ]) && WC()->countries->country_exists( $country )) ){
				return ['error' => [
					'message' => sprintf( __( "'%s' is not a valid country code.", 'woocommerce' ), $country ),
					'field' => $key . 'country',
				] ];
			}
		}

		if( ! empty($data['fields'][ $key . 'postcode' ]) && ! is_null($country) ){

			$postcode = wc_format_postcode( $data['fields'][ $key . 'postcode' ], $country );

			if ( ! \WC_Validation::is_postcode( $postcode, $country ) ) {
				return ['error' => [
					'message' => sprintf( __( "%s is not a valid postcode / ZIP.", 'woocommerce' ), $postcode ),
					'field' => $key . 'postcode',
				]];
			}
		}

		if( ! empty($data['fields'][ $key . 'state' ]) && ! is_null($country) ){

			$valid_states = WC()->countries->get_states( $country );

			if ( ! empty( $valid_states ) && is_array( $valid_states ) && count( $valid_states ) > 0 ) {

				$valid_state_values = array_map( 'wc_strtoupper', array_flip( array_map( 'wc_strtoupper', $valid_states ) ) );
				$state = wc_strtoupper( $data['fields'][ $key . 'state' ] );

				if ( isset( $valid_state_values[ $state ] ) ) {
					// With this part we consider state value to be valid as well, convert it to the state key for the valid_states check below.
					$state = $valid_state_values[ $state ];
				}

				if ( ! in_array( $state, $valid_state_values, true ) ) {
					return ['error' => [
						'message' => sprintf( __( '%1$s State is not valid.', 'woocommerce' ), $state ),
						'field' => $key . 'state',
					]];
				}
			}
		}

		return true;
	}

	/**
	 * Handle Checkout's Ajax - Update Order Review
	 *
	 * @since 2.0.0
	 **/
	function handle_early_ajax() {

		if( '' === $this->get_custom_layout_ajax_settings(self::KEY__REVIEW_COUPON_ENABLE) ){
			add_filter('woocommerce_coupons_enabled', '__return_false', 20);
			return; // no need to go further
		}

		if( '' !== $this->get_custom_layout_ajax_settings(self::KEY__REVIEW_COUPON_TOGGLE) ){
			add_filter('reycore/woocommerce/checkout/coupon_toggle', '__return_true', 20);
		}

	}

	/**
	 * Disable Side social icons for checkout and cart pages
	 *
	 * @since 1.9.2
	 **/
	function checkout_disable_social_icons($status)
	{
		if( is_checkout() || is_cart() ){
			return false;
		}

		return $status;
	}

	/**
	 * Classic layout, add thumbnails
	 */
	function checkout__classic_add_thumb( $html, $cart_item, $cart_item_key ){

		if( ! is_checkout() ){
			return $html;
		}

		if( ! get_theme_mod('checkout_add_thumbs', true) ){
			return $html;
		}

		if( $this->get_checkout_layout() !== self::DEFAULT_LAYOUT ){
			return $html;
		}

		return sprintf('<div class="rey-classic-reviewOrder-img">%s</div>%s',
			apply_filters( 'woocommerce_cart_item_thumbnail',
				$cart_item['data']->get_image( apply_filters('reycore/woocommerce/checkout/classic_thumbnail_size', 'woocommerce_thumbnail') ),
				$cart_item,
				$cart_item_key
			),
			$html
		);
	}

	/**
	 * Add buttons in the confirmation order
	 *
	 * @since 1.9.7
	 **/
	function checkout__add_buttons_order_confirmation($order_id) {

		if( ! apply_filters( 'reycore/woocommerce/checkout/order_confirmation/add_buttons', true) ){
			return;
		}

		reycore_assets()->add_styles('rey-buttons');

		echo '<div class="rey-ordRecPage-buttons" style="margin-bottom: 2em;">';

			printf('<a href="%s" class="btn btn-primary">%s</a>',
				esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ),
				esc_html__( 'Continue shopping', 'woocommerce' )
			);

			echo apply_filters(
				'reycore/woocommerce/checkout/order_confirmation/link',
				sprintf('<a href="%s" class="btn btn-secondary">%s</a>',
					esc_url( wc_get_endpoint_url( 'orders', '', wc_get_page_permalink( 'myaccount' ) ) ),
					esc_html__( 'Review your orders', 'woocommerce' )
				),
				$order_id
			);

		echo '</div>';
	}

	/**
	 * More product info
	 * Link to product
	 *
	 * @return void
	 * @since  1.0.0
	 */
	function cart_progress() {

		if( ! get_theme_mod('cart_checkout_bar_process', true) ){
			return;
		}

		$pid = get_the_ID();
		$active_cart = wc_get_page_id( 'cart' ) == $pid || wc_get_page_id( 'checkout' ) == $pid;
		$active_checkout = wc_get_page_id( 'checkout' ) == $pid;
		?>

		<div class="rey-checkoutBar-wrapper <?php echo get_theme_mod('cart_checkout_bar_icons', 'icon') === 'icon' ? '--icon' : '--numbers'; ?>">
			<ul class="rey-checkoutBar">
				<li class="<?php echo ($active_cart ? '--is-active' : '') ?>">
					<a href="<?php echo get_permalink( wc_get_page_id( 'cart' ) ); ?>">
						<h4>
							<?php echo ($active_cart ? reycore__get_svg_icon(['id' => 'check']) : ''); ?>
							<span><?php
								if( $title_1 = get_theme_mod('cart_checkout_bar_text1_t', '' ) ) {
									echo $title_1;
								}
								else {
									echo esc_html_x('Shopping Bag', 'Checkout bar shopping cart title', 'rey-core');
								}
							?></span>
						</h4>
						<p><?php
							if( $subtitle_1 = get_theme_mod('cart_checkout_bar_text1_s', '' ) ) {
								echo $subtitle_1;
							}
							else {
								echo esc_html_x('View your items', 'Checkout bar shopping cart subtitle', 'rey-core');
							}
						?></p>
					</a>
				</li>
				<li class="<?php echo ($active_checkout ? '--is-active' : '') ?>">
					<a href="<?php echo get_permalink( wc_get_page_id( 'checkout' ) ); ?>">
						<h4>
							<?php echo ($active_checkout ? reycore__get_svg_icon(['id' => 'check']) : ''); ?>
							<span><?php
								if( $title_2 = get_theme_mod('cart_checkout_bar_text2_t', '' ) ) {
									echo $title_2;
								}
								else {
									echo esc_html_x('Shipping and Checkout', 'Checkout bar checkout title', 'rey-core');
								}
							?></span>
						</h4>
						<p><?php
							if( $subtitle_2 = get_theme_mod('cart_checkout_bar_text2_s', '' ) ) {
								echo $subtitle_2;
							}
							else {
								echo esc_html_x('Enter your details', 'Checkout bar checkout subtitle', 'rey-core');
							}
						?></p>
					</a>
				</li>
				<li>
					<div>
						<h4><?php
							if( $title_3 = get_theme_mod('cart_checkout_bar_text3_t', '' ) ) {
								echo $title_3;
							}
							else {
								echo esc_html_x('Confirmation', 'Checkout bar confirmation title', 'rey-core');
							}
						?></h4>
						<p><?php
							if( $subtitle_3 = get_theme_mod('cart_checkout_bar_text3_s', '' ) ) {
								echo $subtitle_3;
							}
							else {
								echo esc_html_x('Review your order!', 'Checkout bar confirmation subtitle', 'rey-core');
							}
						?></p>
					</div>
				</li>
			</ul>
		</div>
		<?php

	}

	/**
	 * Display Express layout block
	 *
	 * @since 1.8.1
	 **/
	function checkout_express()
	{

		ob_start();
		do_action('reycore/woocommerce/checkout/express_checkout');
		$content = ob_get_clean();

		if( empty($content) ){
			return;
		} ?>

		<div class="rey-checkoutExpress">

			<div class="rey-checkoutExpress-title">
				<?php echo esc_html_x('Express checkout', 'Title in checkout form.', 'rey-core') ?>
			</div>

			<div class="rey-checkoutExpress-content">
				<?php echo $content; ?>
			</div>

		</div>
		<?php
	}

	/**
	 * Replaces the login button
	 */
	function checkout_change_login_link( $html ){

		// it's Elementor WC. Checkout (Custom)
		if( $this->is_custom_layout() ){

			$custom = sprintf(' data-reymodal=\'%s\' ', wp_json_encode([
				'content' => '.rey-checkoutLogin-form',
				'width' => 700,
				'id' => 'rey-checkout-login-modal',
			]));

			add_filter( 'reycore/modals/always_load', '__return_true');

			$new_html = str_replace('class="showlogin"', $custom . 'class="custom-showlogin"', $html);

			return $new_html;
		}

		return $html;
	}

	function checkout_redirect_empty_cart($status){

		if(
			is_null($this->get_element_settings()) &&
			(reycore__elementor_edit_mode())
		){
			return false;
		}

		return $status;
	}

	/**
	 * Check if Billing is first in custom layout
	 *
	 * @since 1.9.0
	 **/
	function checkout_custom_billing_first() {

		if( ! $this->is_custom_layout() ){
			return false;
		}

		if( WC()->cart ){
			$shipping_needed = WC()->cart->needs_shipping();
		}

		if( wc_ship_to_billing_address_only() ){
			$shipping_needed = false;
		}

		$shipping_disabled = $this->checkout_custom_shipping_disabled();

		if( $shipping_disabled ){
			$shipping_needed = false;
		}

		// force true if no shipping available
		if( ! $shipping_needed ){
			return true;
		}

		return $this->get_element_settings('show_billing_first') === 'yes';
	}

	/**
	 * Check if custom layout uses steps
	 *
	 * @since 1.9.0
	 **/
	function checkout_custom_use_steps() {

		if( ! $this->is_custom_layout() ){
			return false;
		}

		return $this->get_element_settings('use_steps') !== '';
	}

	/**
	 * Check if Billing is first in custom layout
	 *
	 * @since 1.9.0
	 **/
	function checkout_custom_shipping_disabled() {
		return $this->is_custom_layout() && $this->get_element_settings('disable_shipping_step') === 'yes';
	}

	function customize_checkout_fields( $fields ){

		if( ! $this->is_custom_layout() ){
			return $fields;
		}

		// Add phone to shipping (clone billing phone)
		if( ! isset($fields['shipping']['shipping_phone'] ) && ! empty( $fields['billing']['billing_phone'] ) ){

			$maybe_dont_add_shipping_phone = [
				// Checkout Field Editor for WooCommerce
				defined('THWCFE_VERSION') || defined('THWCFD_VERSION')
			];

			if( ! in_array(true, $maybe_dont_add_shipping_phone, true) ){
				$fields['shipping']['shipping_phone'] = $fields['billing']['billing_phone'];
				$fields['shipping']['shipping_phone']['description'] = esc_html__('In case we need to contact you about your order.', 'rey-core');
			}
		}

		return $fields;
	}

	function save_checkout_fields( $order_id ) {
		if ( ! empty( $_REQUEST['shipping_phone'] ) ) {
			update_post_meta( $order_id, '_shipping_phone', reycore__clean( $_REQUEST['shipping_phone'] ) );
		}
	}

	/**
	 * Override checkout templates
	 * @since 1.8.0
	 */
	function add_templates( $templates ){

		if( ! is_checkout() ){
			return $templates;
		}

		$custom_checkout_templates = [
			[
				'template_name' => 'checkout/form-checkout.php',
				'template' => 'template-parts/woocommerce/checkout/custom-form-checkout.php'
			],
			[
				'template_name' => 'checkout/form-billing.php',
				'template' => 'template-parts/woocommerce/checkout/custom-form-billing.php'
			],
			[
				'template_name' => 'checkout/review-order.php',
				'template' => 'template-parts/woocommerce/checkout/custom-review-order.php'
			],
			[
				'template_name' => 'checkout/form-shipping.php',
				'template' => 'template-parts/woocommerce/checkout/custom-form-shipping.php'
			]
		];

		if( $this->is_custom_layout() ){
			return array_merge($templates, $custom_checkout_templates);
		}

		return $templates;

	}

	function load_styles(){
		reycore_assets()->add_styles(['rey-wc-cart', 'rey-wc-checkout']);
	}

	/**
	 * Generate form review block
	 *
	 * @since 1.8.0
	 **/
	public static function review_form_block($name, $fill, $target, $content = '')
	{
		?>
		<div class="rey-formReview-block rey-formReview-block--<?php echo esc_attr($fill) ?>" data-type="<?php echo esc_attr($fill) ?>">
			<div class="rey-formReview-title">
				<?php echo esc_html_x($name, 'Title in checkout steps form review.', 'rey-core') ?>
			</div>
			<div class="rey-formReview-content" data-fill="<?php echo esc_attr($fill) ?>">
				<div class="rey-formReview-content--<?php echo esc_attr($fill) ?>"><?php echo $content; ?></div>
			</div>
			<div class="rey-formReview-action">
				<a href="#" data-target="<?php echo esc_attr($target) ?>">
					<?php echo esc_html_x('Change', 'Action to take in checkout steps form review', 'rey-core') ?>
				</a>
			</div>
		</div>
		<?php
	}

	/**
	 * Sets Distraction Free Checkout
	 *
	 * @return void
	 */
	public function distraction_free_checkout(){

		if ( is_wc_endpoint_url('order-received') && apply_filters('reycore/checkout/distraction_free/order-received', false) ){
			return;
		}

		if ( ! get_theme_mod('checkout_distraction_free', false) ){
			return;
		}

		// disable header
		remove_all_actions('rey/header');

		// adds a logo only
		add_action('rey/content/title', 'reycore__tags_logo_block', 0);

		// adds class
		add_filter('rey/site_content_classes', function($classes){
			return $classes + ['--checkout-distraction-free'];
		});
	}

	public function display_discount_subtotal($subtotal, $cart_item){

		if( ! is_checkout() ){
			return $subtotal;
		}

		if( ! apply_filters('reycore/woocommerce/checkout/show_discount', true) ){
			return $subtotal;
		}

		$product = $cart_item['data'];

		if( $product->is_on_sale() ){
			$regular_price = wc_get_price_to_display( $product, ['price' => $product->get_regular_price(), 'qty' => $cart_item['quantity']] );
			$sale_price = wc_get_price_to_display( $product, ['qty' => $cart_item['quantity']] );
			$price = '<span class="sale-checkout"><del aria-hidden="true">' . ( is_numeric( $regular_price ) ? wc_price( $regular_price ) : $regular_price ) . '</del><br><ins>' . ( is_numeric( $sale_price ) ? wc_price( $sale_price ) : $sale_price ) . '</ins></span>';
			return apply_filters( 'woocommerce_format_sale_price', $price, $regular_price, $sale_price );
		}

		return $subtotal;
	}
}
