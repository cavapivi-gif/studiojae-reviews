<?php
namespace ReyCore\Modules\MiniCartShippingBar;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use \Automattic\WooCommerce\Utilities\NumberUtil;

class Base extends \ReyCore\Modules\ModuleBase {

	const ASSET_HANDLE = 'reycore-module-minicart-shipping-bar';

	public function __construct()
	{
		add_action( 'reycore/woocommerce/init', [$this, 'init']);
	}

	public function init() {

		add_action( 'reycore/customizer/section=header-mini-cart/marker=after_components', [$this, 'add_customizer_options'], 20);

		if( ! $this->is_enabled() ){
			return;
		}

		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
		add_action( 'reycore/woocommerce/minicart/products_scripts', [$this, 'enqueue_scripts']);
		add_action( 'reycore/woocommerce/minicart/before_totals', [$this, 'shipping_bar'], 10);
		add_action( 'woocommerce_before_cart_table', [$this, 'shipping_bar_cart_page'], 20);
		add_action( 'woocommerce_before_mini_cart', [$this, 'add_cart_assets']);
		add_shortcode( 'rey_shipping_bar', [$this, 'shipping_bar_shortcode']);
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

	}

	public function enqueue_scripts(){
		reycore_assets()->add_styles(self::ASSET_HANDLE);
	}

	public function add_cart_assets(){

		if( WC()->cart->is_empty() ){
			return;
		}

		$this->enqueue_scripts();

	}

	public function shipping_bar_cart_page(){

		if( ! get_theme_mod('header_cart_shipping_bar__cart_page', false) ){
			return;
		}

		reycore_assets()->add_styles(self::ASSET_HANDLE);

		$this->shipping_bar();
	}

	public function shipping_bar_shortcode(){

		reycore_assets()->add_styles(self::ASSET_HANDLE);

		$this->shipping_bar();
	}


	/**
	 * Show Shipping Bar
	 *
	 * @since 2.0.4
	 **/
	public function shipping_bar()
	{

		if( ! ($minicart_tag = reycore_wc__get_tag('minicart')) ){
			return;
		}

		if( ! WC()->shipping || ! WC()->cart || ! WC()->countries ){
			return;
		}

		// show shipping doesn't matter
		// if (manual) minimum amount is added
		if( ! WC()->cart->show_shipping() ){
			if( ! get_theme_mod('header_cart_shipping_bar__min', '') ){
				return;
			}
		}

		if( ! get_theme_mod('header_cart_shipping_bar__enable', false) ){
			return;
		}

		$show_over = get_theme_mod('header_cart_shipping_bar__show_over', false);

		if( $free_shipping_min = apply_filters('reycore/woocommerce/mini_cart_shipping_bar/min_free_shipping_amount', $this->get_min_free_shipping_amount()) ){
			if( $free_shipping_min['is_available'] && ! $show_over ) {
				return;
			}
		}

		if( ! ($min = $free_shipping_min['amount']) ){
			return;
		}

		$total = WC()->cart->get_displayed_subtotal();

		if ( WC()->cart->display_prices_including_tax() ) {
			$total = $total - WC()->cart->get_discount_tax();
		}

		if ( 'no' === $free_shipping_min['ignore_discounts'] ) {
			$total = $total - WC()->cart->get_discount_total();
		}

		foreach ( WC()->cart->get_cart_contents() as $cart_item ) {
			if( ! $cart_item['data']->needs_shipping() ){
				$total -= $cart_item['line_subtotal'];
			}
		}

		$total = apply_filters('reycore/woocommerce/mini_cart_shipping_bar/total', NumberUtil::round( $total, wc_get_price_decimals() ));

		if( $minimum_total = apply_filters('reycore/woocommerce/mini_cart_shipping_bar/show_over_total', 0) ){
			if( NumberUtil::round( $minimum_total, wc_get_price_decimals() ) > $total ){
				return;
			}
		}

		$is_over = $min < $total;

		$over_text = '';
		$over_class = '';

		if( $is_over ){
			if( $show_over ){
				$over_text = esc_html__('Free shipping!', 'rey-core');
				if( $custom_over_text = get_theme_mod('header_cart_shipping_bar__show_over_text', '') ){
					$over_text = $custom_over_text;
				}
				$over_class = '--over';
			}
			else {
				return;
			}
		}

		$diff = $min - $total;
		$percentage = 100 - (($diff / $min) * 100);

		echo sprintf('<div class="rey-cartShippingBar %2$s" style="--bar-perc:%1$d%%;">', $percentage > 100 ? 100 : $percentage, $over_class);

			$text = esc_html__('You\'re only {{diff}} away from free shipping.', 'rey-core');

			if( $custom_text = get_theme_mod('header_cart_shipping_bar__text', '') ){
				$text = $custom_text;
			}

			if( $over_text ){
				$text = $over_text;
			}

			echo sprintf('<div class="__text">%s</div>', str_replace('{{diff}}', wc_price( $diff ), $text));

			echo '<div class="__bar"></div>';

		echo '</div>';

	}


	public function get_min_free_shipping_amount() {

		$is_available = false;
		$ignore_discounts = 'no';

		$manual_min_amount_data = get_theme_mod('header_cart_shipping_bar__min', '');

		if( $manual_min_amount_data && function_exists('wmc_get_price') ){
			$manual_min_amount_data = wmc_get_price($manual_min_amount_data);
		}

		if ( $manual_min_amount_data ) {
			return [
				'amount' => floatval($manual_min_amount_data),
				'is_available' => false,
				'ignore_discounts' => $ignore_discounts,
			];
		}

		$min_free_shipping_amount = 0;

		$legacy_free_shipping = new \WC_Shipping_Legacy_Free_Shipping();

		if ( 'yes' === $legacy_free_shipping->enabled ) {
			if ( in_array( $legacy_free_shipping->requires, array( 'min_amount', 'either', 'both' ) ) ) {
				$min_free_shipping_amount = $legacy_free_shipping->min_amount;
			}
		}

		$do_check_for_available_free_shipping = true;

		if (
			0 == $min_free_shipping_amount &&
			function_exists( 'WC' ) &&
			( $wc_shipping = WC()->shipping ) &&
			( $wc_cart = WC()->cart ) &&
			$wc_shipping->enabled &&
			( $packages = $wc_cart->get_shipping_packages() )
		) {

			$shipping_methods = $wc_shipping->load_shipping_methods( $packages[0] );

			foreach ( $shipping_methods as $shipping_method ) {
				if (
					$shipping_method instanceof \WC_Shipping_Free_Shipping &&
					'yes' === $shipping_method->enabled && 0 != $shipping_method->instance_id
				) {

					$ignore_discounts = isset($shipping_method->ignore_discounts) ? $shipping_method->ignore_discounts : false;

					if ( in_array( $shipping_method->requires, array( 'min_amount', 'either', 'both' ) ) ) {

						if ( $shipping_method->is_available( $packages[0] ) ) {
							$is_available = true;
						}

						$min_free_shipping_amount = $shipping_method->min_amount;

						if ( ! $do_check_for_available_free_shipping ) {
							continue;
						}

					}

					elseif ( $shipping_method->requires !== 'coupon' && $do_check_for_available_free_shipping ) {

						$is_available = true;
						$min_free_shipping_amount = 0;

						continue;

					}
				}
			}
		}

		return [
			'amount' => floatval($min_free_shipping_amount),
			'is_available' => $is_available,
			'ignore_discounts' => $ignore_discounts,
		];
	}

	public function add_customizer_options( $section ){

		$section->start_controls_accordion([
			'label'  => esc_html__( 'Free-Shipping Progress Bar', 'rey-core' ),
		]);

			$section->add_control( [
				'type'        => 'toggle',
				'settings'    => 'header_cart_shipping_bar__enable',
				'label'       => esc_html__( 'Show Bar', 'rey-core' ),
				'default'     => false,
				'help' => [
					esc_html__( 'Shows a progress bar inside the panel, which indicates the amount needed to get free shipping.', 'rey-core')
				],
			] );

			$section->start_controls_group( [
				'label'    => esc_html__( 'Options', 'rey-core' ),
				'active_callback' => [
					[
						'setting'  => 'header_cart_shipping_bar__enable',
						'operator' => '==',
						'value'    => true,
					],
				],
			]);

				$section->add_control( [
					'type'        => 'text',
					'settings'    => 'header_cart_shipping_bar__text',
					'label'       => esc_html__( 'Text', 'rey-core' ),
					'help' => [
						__('Override the text. Use <code>{{diff}}</code> to add the difference amount.', 'rey-core')
					],
					'default'     => '',
					'input_attrs'     => [
						'placeholder' => esc_html__('You\'re only {{diff}} away from free shipping.', 'rey-core'),
						'data-control-class' => '--text-lg',
					],
				] );

				$section->add_control( [
					'type'        => 'toggle',
					'settings'    => 'header_cart_shipping_bar__show_over',
					'label'       => esc_html_x( 'Show over threshold?', 'Customizer control text', 'rey-core' ),
					'help' => [
						esc_html_x('Show the bar when it reaches over the minimum amount threshold?', 'Customizer control text', 'rey-core')
					],
					'default'     => false,
				] );

				$section->add_control( [
					'type'        => 'text',
					'settings'    => 'header_cart_shipping_bar__show_over_text',
					'label'       => esc_html__( 'Text (Over Threshold)', 'rey-core' ),
					'help' => [
						esc_html_x('Override the text when the bar reaches over the minimum amount threshold.', 'Customizer control text', 'rey-core')
					],
					'default'     => '',
					'input_attrs'     => [
						'placeholder' => '',
						'data-control-class' => '--text-md',
					],
					'active_callback' => [
						[
							'setting'  => 'header_cart_shipping_bar__show_over',
							'operator' => '==',
							'value'    => true,
						],
					],
				] );

				$section->add_control( [
					'type'        => 'toggle',
					'settings'    => 'header_cart_shipping_bar__cart_page',
					'label'       => esc_html_x( 'Add to Cart page', 'Customizer control text', 'rey-core' ),
					'help' => [
						esc_html_x('If enabled will display on Cart page too.', 'Customizer control text', 'rey-core')
					],
					'default'     => false,
				] );

				$section->add_control( [
					'type'        => 'text',
					'settings'    => 'header_cart_shipping_bar__min',
					'label'       => esc_html_x( 'Manual input value', 'Customizer control text', 'rey-core' ),
					'help' => [
						esc_html_x('By default minimum free shipping value gets automatically calculated, however you can manually override it, but please know it\'s only for display, it won\'t change your shipping costs.', 'Customizer control text', 'rey-core')
					],
					'default'     => '',
					'input_attrs'     => [
						'placeholder' => esc_html_x('eg: 20', 'Customizer control text', 'rey-core'),
						'data-control-class' => '--text-md',
					],
				] );

			$section->end_controls_group();
		$section->end_controls_accordion();

	}

	public function is_enabled() {
		return get_theme_mod('header_cart_shipping_bar__enable', false);
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Mini-Cart Free-Shipping Bar', 'Module name', 'rey-core'),
			'description' => esc_html_x('Shows a progress bar which incentivizes customers to reach free shipping by buying more.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => [''],
			'help'        => reycore__support_url('kb/shopping-cart-popup-side-panel/#show-a-free-shipping-progress-bar'),
			'video' => true,
		];
	}

	public function module_in_use(){
		return $this->is_enabled();
	}
}
