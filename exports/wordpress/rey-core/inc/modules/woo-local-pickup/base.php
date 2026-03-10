<?php
namespace ReyCore\Modules\WooLocalPickup;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	const ASSET_HANDLE = 'reycore-module-local-pickup';

	public function __construct()
	{
		add_action( 'reycore/woocommerce/init', [$this, 'init']);
		add_action('reycore/customizer/section=woo-checkout/marker=before_texts', [$this, 'add_controls']);
	}

	public function init() {

		if( ! $this->is_enabled() ){
			return;
		}

		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'woocommerce_after_shipping_rate', [$this, 'append_address_in_method'], 10, 2 );
		add_action( 'reycore/woocommerce/checkout/review_form', [$this, 'add_lp_ship_to'], 10, 2 );
		add_action( 'woocommerce_new_order', [$this, 'new_order'], 10, 2 );

	}

	public static function get_store_address( $return_defaults = false ){

		$store_address = [
			'address_1' => WC()->countries->get_base_address(),
			'address_2' => WC()->countries->get_base_address_2(),
			'city'      => WC()->countries->get_base_city(),
			'state'     => WC()->countries->get_base_state(),
			'postcode'  => WC()->countries->get_base_postcode(),
			'country'   => WC()->countries->get_base_country(),
		];

		if( $return_defaults ){
			return $store_address;
		}

		if( get_theme_mod('lp__address_custom', false) ){
			$custom_address = [];
			foreach (array_keys($store_address) as $key) {
				$custom_address[ $key ] = get_theme_mod("lp__address_custom_{$key}", '');
			}
			$store_address = $custom_address;
		}

		return $store_address;
	}

	public static function get_full_store_address(){

		$data['store_address'] = self::get_store_address();
		$data['store_address_formatted'] = WC()->countries->get_formatted_address($data['store_address'], ', ');

		$data['maps'] = sprintf('&nbsp;<a href="%2$s" target="_blank"><u>%1$s</u></a>',
			esc_html__('See Google Maps', 'rey-core'),
			esc_url( apply_filters('reycore/local_shipping/maps_url', 'https://www.google.com/maps?q=' . str_replace(' ', '+', $data['store_address_formatted'])) )
		);

		return apply_filters('reycore/local_shipping/address',
			sprintf('<div class="__lpickup-addr">%s %s</div>', $data['store_address_formatted'], $data['maps']),
			$data
		);
	}

	public static function show_address(){
		return get_theme_mod('lp__show_address', false);
	}

	public function append_address_in_method($method, $index){

		if( ! self::show_address() ){
			return;
		}

		if( strpos($method->id, 'local_pickup') !== 0 ){
			return;
		}

		echo self::get_full_store_address();
	}

	public function add_lp_ship_to( $checkout, $step ){

		if( ! self::show_address() ){
			return;
		}

		if( 'payment' === $step ){
			$checkout::review_form_block(
				esc_html_x('Pickup from', 'Title in checkout steps form review.', 'rey-core'),
				'lp_address_ship',
				'shipping',
				self::get_full_store_address()
			);
		}

	}

	public function new_order( $order_id, $order ){

		if( ! get_theme_mod('lp__order_shipping', true) ){
			return;
		}

		if( ! ( $shipping_methods = $order->get_shipping_methods() ) ){
			return;
		}

		$shipping_method = array_shift($shipping_methods);

		if( ! ( 'local_pickup' === $shipping_method->get_method_id() ) ){
			return;
		}

		$fields = self::get_store_address( apply_filters('reycore/local_shipping/order_address/force_default', false) );

		if( get_theme_mod('lp__order_shipping_extra', false) ){
			$fields['first_name'] = '';
			$fields['last_name']  = '';
			$fields['phone']      = '';
			$fields['company']    = '';
		}

		foreach ($fields as $key => $value) {

			$method_name = 'set_shipping_' . $key;

			if( method_exists($order, $method_name) ){
				call_user_func([$order, $method_name], $value);
			}
		}

		$order->save();

	}

	public function enqueue_scripts(){

		if( ! (is_checkout() || is_cart()) ){
			return;
		}

		if( ! self::show_address() ){
			return;
		}

		if( ! in_array('local_pickup', array_keys(WC()->shipping->get_shipping_methods()), true) ){
			return;
		}

		reycore_assets()->add_scripts(self::ASSET_HANDLE);
		reycore_assets()->add_styles(self::ASSET_HANDLE);
	}

	public function register_assets($assets){

		$assets->register_asset('styles', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/style.css',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			]
		]);

		$assets->register_asset('scripts', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/script.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
			]
		]);

	}

	public function add_controls( $section ){

		if( ! $section ){
			return;
		}

		$section->add_title( esc_html__('LOCAL PICKUP (Module)', 'rey-core') );

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'lp__show_address',
			'label'       => esc_html__( 'Show Store Address in Local Pickup', 'rey-core' ),
			'help'            => [
				esc_html__('Displays the Store address in the Cart/Checkout shipping options, when selecting the shipping method.', 'rey-core')
			],
			'default'     => false,
		] );

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'lp__address_custom',
			'label'       => esc_html__( 'Custom Store Address', 'rey-core' ),
			'help'            => [
				esc_html__('Displays the Store address in the Cart/Checkout shipping options, when selecting the shipping method.', 'rey-core')
			],
			'default'     => false,
			'active_callback' => [
				[
					'setting'  => 'lp__show_address',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$section->start_controls_group( [
			// 'label'    => esc_html__( 'Custom Address', 'rey-core' ),
			'active_callback' => [
				[
					'setting'  => 'lp__show_address',
					'operator' => '==',
					'value'    => true,
				],
				[
					'setting'  => 'lp__address_custom',
					'operator' => '==',
					'value'    => true,
				],
			],
		]);

			$section->add_control( [
				'type'            => 'text',
				'settings'        => 'lp__address_custom_address_1',
				'label'           => esc_html__( 'Address Line 1', 'rey-core' ),
				'default'         => '',
			] );

			$section->add_control( [
				'type'            => 'text',
				'settings'        => 'lp__address_custom_address_2',
				'label'           => esc_html__( 'Address Line 2', 'rey-core' ),
				'default'         => '',
			] );

			$section->add_control( [
				'type'            => 'text',
				'settings'        => 'lp__address_custom_city',
				'label'           => esc_html__( 'City', 'rey-core' ),
				'default'         => '',
			] );

			$section->add_control( [
				'type'            => 'text',
				'settings'        => 'lp__address_custom_state',
				'label'           => esc_html__( 'State', 'rey-core' ),
				'default'         => '',
			] );

			$section->add_control( [
				'type'            => 'text',
				'settings'        => 'lp__address_custom_postcode',
				'label'           => esc_html__( 'Postcode', 'rey-core' ),
				'default'         => '',
			] );

			$section->add_control( [
				'type'            => 'text',
				'settings'        => 'lp__address_custom_country',
				'label'           => esc_html__( 'Country', 'rey-core' ),
				'default'         => '',
			] );

		$section->end_controls_group();

		// More Pickup Locations (repeater)

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'lp__order_shipping',
			'label'       => esc_html__( 'Override Order Shipping address', 'rey-core' ),
			'help'            => [
				esc_html__('Override the Shipping address in Orders, when Local Pickup is selected.', 'rey-core')
			],
			'default'     => true,
		] );

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'lp__order_shipping_extra',
			'label'       => esc_html__( 'Remove Name & Phone from Shipping', 'rey-core' ),
			'default'     => false,
			'active_callback' => [
				[
					'setting'  => 'lp__order_shipping',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );


	}

	public function is_enabled() {
		return true;
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Local Pickup Enhancement', 'Module name', 'rey-core'),
			'description' => esc_html_x('In Cart & Checkout, the Local Pickup choice will display the Store Address and force it instead of Shipping address.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => ['woocommerce', 'shipping', 'checkout', 'cart'],
			'video'       => true,
			'help'        => reycore__support_url('kb/extend-local-pickup-display-in-checkout/'),
		];
	}

	public function module_in_use(){

		if( ! (isset(WC()->shipping) && ($shipping = WC()->shipping)) ){
			return;
		}

		return in_array('local_pickup', array_keys($shipping->get_shipping_methods()), true) ;
	}
}
