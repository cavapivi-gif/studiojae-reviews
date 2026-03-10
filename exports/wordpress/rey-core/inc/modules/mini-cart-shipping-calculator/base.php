<?php
namespace ReyCore\Modules\MiniCartShippingCalculator;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	const ASSET_HANDLE = 'reycore-module-minicart-shipping-calculator';

	public $settings;

	public function __construct()
	{
		add_action( 'reycore/woocommerce/init', [$this, 'init']);
	}

	public function init() {

		add_action( 'reycore/ajax/register_actions', [ $this, 'register_actions' ] );
		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
		add_action( 'reycore/woocommerce/mini-cart/after_content', [$this, 'load_countries_localization']);
		add_action( 'reycore/woocommerce/minicart/after_shipping', [$this, 'add_shipping_form']);
		add_action( 'reycore/woocommerce/minicart/products_scripts', [$this, 'add_shipping_form_scripts']);
		add_filter( 'reycore/minicart/shipping/pre_cost_html', [ $this, 'add_calculate_button' ], 10, 2 );
		add_action( 'reycore/customizer/control=header_cart_show_shipping', [ $this, 'add_customizer_option' ], 10, 2 );

	}

	public function register_assets($assets){

		$assets->register_asset('styles', [
			self::ASSET_HANDLE => [
				'src'      => self::get_path( basename( __DIR__ ) ) . '/style.css',
				'deps'     => [],
				'version'  => REY_CORE_VERSION,
				'priority' => 'low',
			]
		]);

		$assets->register_asset('scripts', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/script.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
			]
		]);

		$assets->register_asset('scripts', [
			'wc-country-select' => [
				'src'    => sprintf( '%s/assets/js/frontend/country-select%s.js', WC()->plugin_url(), (defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min')),
				'deps'   => ['jquery'],
				'plugin' => true,
			]
		]);

	}

	public function register_actions( $ajax_manager ){
		$ajax_manager->register_ajax_action( 'calculate_shipping', [$this, 'ajax__calculate_shipping'], [
			'auth'   => 3,
			'nonce'  => false,
		] );
	}

	public function ajax__calculate_shipping( $action_data ){

		if( ! $this->option_is_enabled() ){
			return;
		}

		$nonce_value = wc_get_var( $action_data['woocommerce-shipping-calculator-nonce'], wc_get_var( $_REQUEST['_wpnonce'], '' ) ); // @codingStandardsIgnoreLine.

		// Update Shipping. Nonce check uses new value and old value (woocommerce-cart). @todo remove in 4.0.
		if ( ! ( ! empty( $action_data['calc_shipping'] ) && wp_verify_nonce( $nonce_value, 'woocommerce-shipping-calculator' )) ) { // WPCS: input var ok.
			return [
				'errors' => 'Invalid nonce check.'
			];
		}

		WC()->shipping()->reset_shipping();

		$address = [];

		$address['country']  = isset( $action_data['calc_shipping_country'] ) ? reycore__clean( $action_data['calc_shipping_country'] ) : ''; // WPCS: input var ok, CSRF ok, sanitization ok.
		$address['state']    = isset( $action_data['calc_shipping_state'] ) ? reycore__clean( $action_data['calc_shipping_state'] ) : ''; // WPCS: input var ok, CSRF ok, sanitization ok.
		$address['postcode'] = isset( $action_data['calc_shipping_postcode'] ) ? reycore__clean( $action_data['calc_shipping_postcode'] ) : ''; // WPCS: input var ok, CSRF ok, sanitization ok.
		$address['city']     = isset( $action_data['calc_shipping_city'] ) ? reycore__clean( $action_data['calc_shipping_city'] ) : ''; // WPCS: input var ok, CSRF ok, sanitization ok.

		$address = apply_filters( 'woocommerce_cart_calculate_shipping_address', $address );

		if ( $address['postcode'] && ! \WC_Validation::is_postcode( $address['postcode'], $address['country'] ) ) {
			wc_add_notice( __( 'Please enter a valid postcode / ZIP.', 'woocommerce' ), 'error' );
			add_filter( 'woocommerce_cart_needs_shipping', '__return_true' ); // fix for `get_cart_contents` which returns 0
			return [
				'form' => self::get_form_output(),
			];
		}
		elseif ( $address['postcode'] ) {
			$address['postcode'] = wc_format_postcode( $address['postcode'], $address['country'] );
		}

		if ( $address['country'] ) {
			if ( ! WC()->customer->get_billing_first_name() ) {
				WC()->customer->set_billing_location( $address['country'], $address['state'], $address['postcode'], $address['city'] );
			}
			WC()->customer->set_shipping_location( $address['country'], $address['state'], $address['postcode'], $address['city'] );
		}
		else {
			WC()->customer->set_billing_address_to_base();
			WC()->customer->set_shipping_address_to_base();
		}

		WC()->customer->set_calculated_shipping( true );
		WC()->customer->save();

		do_action( 'woocommerce_calculated_shipping' );

		// Also calc totals before we check items so subtotals etc are up to date.
		WC()->cart->calculate_totals();

		self::totals_shipping_html();

		return [
			'form' => self::get_form_output(),
			'cost' => reycore_wc__get_tag('cart')->get_shipping_cost(true),
		];
	}

	private static function get_form_output()
	{
		ob_start();

		wc_print_notices();
		woocommerce_shipping_calculator();

		return ob_get_clean();
	}

	/**
	 * Get shipping methods.
	 */
	private static function totals_shipping_html() {

		$packages = WC()->shipping()->get_packages();
		$first    = true;

		foreach ( $packages as $i => $package ) {

			$chosen_method = isset( WC()->session->chosen_shipping_methods[ $i ] ) ? WC()->session->chosen_shipping_methods[ $i ] : '';
			$product_names = array();

			if ( count( $packages ) > 1 ) {
				foreach ( $package['contents'] as $item_id => $values ) {
					$product_names[ $item_id ] = $values['data']->get_name() . ' &times;' . $values['quantity'];
				}
				$product_names = apply_filters( 'woocommerce_shipping_package_details_array', $product_names, $package );
			}

			$formatted_destination = WC()->countries->get_formatted_address( $package['destination'], ', ' );

			// default, no address or calculated shipping
			if ( ! WC()->customer->has_calculated_shipping() || ! $formatted_destination ) {
				wc_add_notice( wp_kses_post( apply_filters( 'woocommerce_shipping_may_be_available_html', __( 'Enter your address to view shipping options.', 'woocommerce' ) ) ), 'error' );
			}

			// no shipping method available
			elseif( empty($package['rates']) ) {
				wc_add_notice( wp_kses_post( apply_filters( 'woocommerce_cart_no_shipping_available_html', sprintf( esc_html__( 'No shipping options were found for %s.', 'woocommerce' ) . ' ', '<strong>' . esc_html( $formatted_destination ) . '</strong>' ) ) ), 'error' );
			}

			else {
				if( $formatted_destination ){
					wc_add_notice( sprintf( esc_html__( 'Shipping to %s.', 'woocommerce' ), '<strong>' . esc_html( $formatted_destination ) . '</strong>' ), 'notice' );
				}
				// stop
				return;
			}

			if ( count( $packages ) > 1 ) {
				echo '<p class="woocommerce-shipping-contents"><small>' . esc_html( implode( ', ', $product_names ) ) . '</small></p>';
			}

			$first = false;
		}
	}

	public function add_calculate_button( $cost_html, $cost ){

		if( ! $this->is_enabled() ){
			return $cost_html;
		}

		if( ! $cost ){

			$link = 'no' === get_option( 'woocommerce_enable_shipping_calc', 'yes' ) ? wc_get_checkout_url() : wc_get_cart_url();

			$text = esc_html( __( 'Calculate shipping', 'woocommerce' ) );
			$text .= reycore__get_svg_icon(['id'=>'reycore-icon-minus', 'class' => '__indicator __minus']);
			$text .= reycore__get_svg_icon(['id'=>'reycore-icon-plus', 'class' => '__indicator __plus']);

			return sprintf( '<a href="%s" class="__shipping-cost">%s</a>', esc_url($link), $text );
		}

		return $cost_html;
	}

	public function add_shipping_form_scripts(){

		reycore_assets()->add_styles(self::ASSET_HANDLE);
		reycore_assets()->add_scripts(['wc-country-select', self::ASSET_HANDLE]);

	}

	public function add_shipping_form( $cost ){

		if( ! $this->is_enabled() ){
			return;
		}

		// cost is already shown
		if( $cost ){
			return;
		}

		ob_start();

		$this->add_shipping_form_scripts();

		woocommerce_shipping_calculator();

		printf( '<div class="minicart-total-row minicart-total-row--shipping-form"><div class="rey-shippingCalc"><div class="rey-shippingCalc-inner">%s</div></div></div>', ob_get_clean() );

	}

	public function load_countries_localization(){

		if( ! $this->is_enabled() ){
			return;
		}

		if( wp_script_is('wc-country-select', 'enqueued') ){
			return;
		}

		$params = [
			'countries'                 => wp_json_encode( array_merge( WC()->countries->get_allowed_country_states(), WC()->countries->get_shipping_country_states() ) ),
			'i18n_select_state_text'    => esc_attr__( 'Select an option', 'woocommerce' ),
			'i18n_no_matches'           => _x( 'No matches found', 'enhanced select', 'woocommerce' ),
			'i18n_ajax_error'           => _x( 'Loading failed', 'enhanced select', 'woocommerce' ),
			'i18n_input_too_short_1'    => _x( 'Please enter 1 or more characters', 'enhanced select', 'woocommerce' ),
			'i18n_input_too_short_n'    => _x( 'Please enter %qty% or more characters', 'enhanced select', 'woocommerce' ),
			'i18n_input_too_long_1'     => _x( 'Please delete 1 character', 'enhanced select', 'woocommerce' ),
			'i18n_input_too_long_n'     => _x( 'Please delete %qty% characters', 'enhanced select', 'woocommerce' ),
			'i18n_selection_too_long_1' => _x( 'You can only select 1 item', 'enhanced select', 'woocommerce' ),
			'i18n_selection_too_long_n' => _x( 'You can only select %qty% items', 'enhanced select', 'woocommerce' ),
			'i18n_load_more'            => _x( 'Loading more results', 'enhanced select', 'woocommerce' ),
			'i18n_searching'            => _x( 'Searching', 'enhanced select', 'woocommerce' ),
		];

		printf('<script type=\'text/javascript\' id=\'wc-country-select-js-extra\'> var wc_country_select_params = %s; </script>', wp_json_encode($params));

	}

	public function option_is_enabled(){

		if( ! get_theme_mod('header_cart_show_shipping', false) ){
			return;
		}

		if( ! get_theme_mod('header_cart_show_shipping_calculator', false) ){
			return;
		}

		if ( 'no' === get_option( 'woocommerce_enable_shipping_calc', 'yes' ) ) {
			return;
		}

		return true;
	}

	public function is_enabled() {

		if( is_cart() || is_checkout() ){
			return;
		}

		if ( ! WC()->cart ) {
			return;
		}

		if ( ! (method_exists(WC()->cart, 'needs_shipping') && WC()->cart->needs_shipping()) ) {
			return;
		}

		if( ! $this->option_is_enabled() ){
			return;
		}

		return true;
	}

	public function add_customizer_option($control_args, $section){

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'header_cart_show_shipping_calculator',
			'label'       => esc_html__( 'Enable Shipping Calculator', 'rey-core' ),
			'default'     => false,
			'help' => [
				esc_html__( 'Enable shipping calculator form. Please know that this feature only works if "Enable the shipping calculator on the cart page" option in "WooCommerce > Settings > Shipping > Shipping Option" is enabled.', 'rey-core' )
			],
		] );

	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Mini-Cart Shipping Calculator', 'Module name', 'rey-core'),
			'description' => esc_html_x('Shows the calculator form from the Cart page, in order to estimate the shipping costs based on visitor information data.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => [''],
			'help'        => reycore__support_url('kb/shopping-cart-popup-side-panel/'),
			'video' => true,
		];
	}

	public function module_in_use(){

		if( ! $this->option_is_enabled() ){
			return;
		}

		return true;
	}
}
