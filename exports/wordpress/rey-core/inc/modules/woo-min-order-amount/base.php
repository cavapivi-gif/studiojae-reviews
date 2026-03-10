<?php
namespace ReyCore\Modules\WooMinOrderAmount;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	const ASSET_HANDLE = 'reycore-module-min-order-amount';

	public $amount = false;
	public $total;

	public function __construct()
	{
		add_action( 'reycore/woocommerce/init', [$this, 'init']);
		add_action('reycore/customizer/section=woo-checkout/marker=before_texts', [$this, 'add_controls']);
	}

	public function init() {

		if( ! ( $this->amount = absint($this->is_enabled()) ) ){
			return;
		}

		add_action( 'woocommerce_checkout_process', [$this, 'notice'] );
		add_action( 'woocommerce_before_cart' , [$this, 'notice'] );
		add_action( 'woocommerce_widget_shopping_cart_total' , [$this, 'minicart_row'] );
		add_filter( 'reycore/minicart/proceed_to_checkout_button' , [$this, 'proceed_button'] );
		add_action( 'woocommerce_review_order_before_order_total' , [$this, 'checkout_total_row'] );

	}

	public function get_settings( $s = '' ){

		static $settings;

		if( is_null($settings) ){
			$settings = apply_filters('reycore/woocommerce/min-order-amount/settings', [
				'totals_key' => 'total', // can be used 'subtotal'
				'notice_text' => esc_html__('Your current order total is %s. You must have an order with a minimum of %s to place your order.', 'rey-core'),
				'minicart_text' => esc_html__( 'Minimum Order', 'rey-core' ),
			]);
		}

		if( isset($settings[$s]) ){
			return $settings[$s];
		}

		return $settings;
	}

	public function get_total(){

		if( is_null($this->total) ){
			$totals = WC()->cart->get_totals();
			if( isset($totals[ $this->get_settings('totals_key') ]) && ($total = $totals[ $this->get_settings('totals_key') ]) ){
				$this->total = $total;
			}
		}

		return $this->total;
	}

	public function is_under_amount(){
		return absint($this->get_total()) < $this->amount;
	}

	public function notice() {

		if ( ! $this->is_under_amount() ) {
			return;
		}

		$method = 'wc_add_notice';

		if( is_cart() ) {
			$method = 'wc_print_notice';
		}

		call_user_func( $method, sprintf( $this->get_settings('notice_text') , wc_price( $this->get_total() ), wc_price( $this->amount ) ), 'error' );
	}

	public function proceed_button($html){

		if ( ! $this->is_under_amount() ) {
			return $html;
		}

		$search_for = 'checkout wc-forward';
		return str_replace($search_for, $search_for . ' disabled', $html);
	}

	public function minicart_row(){

		if ( ! $this->is_under_amount() ) {
			return;
		}

		printf(
			'<div class="minicart-total-row minicart-total-row--under-am"><div class="minicart-total-row-head">%1$s</div><div class="minicart-total-row-content">%2$s</div></div>',
			$this->get_settings('minicart_text'),
			wc_price( $this->amount )
		);

	}

	public function checkout_total_row(){

		if ( ! $this->is_under_amount() ) {
			return;
		}

		printf(
			'<tr><th>%s</th><td><strong style="color: var(--colors-red)">%s</strong></td></tr>',
			$this->get_settings('minicart_text'),
			wc_price( $this->amount )
		);

	}


	public function add_controls( $section ){

		if( ! $section ){
			return;
		}

		$section->add_title( esc_html__('MINIMUM ORDER AMOUNT (Module)', 'rey-core'));

		$section->add_control( [
			'type'        => 'rey-number',
			'settings'    => 'minimum_order_amount',
			'label'       => esc_html__( 'Minimum Amount', 'rey-core' ),
			'help' => [
				esc_html__('If an amount is added, the orders will be forced to be over this amount and some notices will show up.', 'rey-core')
			],
			'default'     => '',
			'choices'     => [
				'min'  => 0,
				'step' => 1,
			],
		] );

	}

	public function is_enabled() {
		return get_theme_mod('minimum_order_amount', 0);
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Minimum Order Amount', 'Module name', 'rey-core'),
			'description' => esc_html_x('Forces orders to be over a specific amount by warning with notices in Mini-cart, Cart and Checkout pages.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => ['woocommerce', 'checkout', 'cart'],
			'video'       => true,
			'help'        => reycore__support_url('kb/set-a-minimum-order-amount/'),
		];
	}

	public function module_in_use(){
		return $this->is_enabled();
	}
}
