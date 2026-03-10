<?php
namespace ReyCore\Compatibility\PwGiftCard;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{
	public $redeem_instance;

	const ASSET_HANDLE = 'reycore-pw-gift-cards-styles';


	public function __construct()
	{
		add_action('init', [$this, 'init']);
		add_action( 'reycore/assets/register_scripts', [ $this, 'register_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	function init(){

		global $pw_gift_cards_redeeming;

		if( $pw_gift_cards_redeeming ){
			$this->redeem_instance = $pw_gift_cards_redeeming;
		}

		if( ! $this->redeem_instance ){
			return;
		}

		remove_action( 'woocommerce_after_cart_contents', [$this->redeem_instance, 'woocommerce_after_cart_contents'] );
		add_action( 'woocommerce_after_cart_table', [$this, 'woocommerce_after_cart_table'] );

		// Checkout
		if ( apply_filters('reycore/compatibility/pw_gift_cards/relocate_checkout', false) &&
				'review_order_before_submit' === get_option( 'pwgc_redeem_checkout_location', 'review_order_before_submit' ) ) {
			remove_action( 'woocommerce_review_order_before_submit', [$this->redeem_instance, 'woocommerce_review_order_before_submit'] );
			add_action( 'reycore/checkout/woocommerce_review_order_before_subtotals', [$this, 'woocommerce_review_order_before_submit'] );
		}

		// add_action( 'reycore/woocommerce/minicart/before_totals', [$this->redeem_instance, 'woocommerce_proceed_to_checkout'] );
		// add_action( 'reycore/woocommerce/mini-cart/after_content', [$this->redeem_instance, 'woocommerce_proceed_to_checkout'] );

	}

	function woocommerce_after_cart_table() {
		wp_enqueue_script( 'pw-gift-cards' );
		echo '<div class="rey-pwgc-afterCart">';
			wc_get_template( 'cart/apply-gift-card-after-cart-contents.php', array(), '', PWGC_PLUGIN_ROOT . 'templates/woocommerce/' );
		echo '</div>';
	}

	function woocommerce_review_order_before_submit() {
		wp_enqueue_script( 'pw-gift-cards' );
		echo '<tr class="rey-pwgc __coupon-row"><td colspan="2">';
			wc_get_template( 'checkout/payment-method-pw-gift-card.php', array(), '', PWGC_PLUGIN_ROOT . 'templates/woocommerce/' );
		echo '</td></tr>';
	}

	public function enqueue_scripts(){
		reycore_assets()->add_styles(self::ASSET_HANDLE);
	}

	public function register_scripts($assets){

		$assets->register_asset('styles', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/style.css',
				'deps'    => ['woocommerce-general'],
				'version'   => REY_CORE_VERSION,
			]
		]);

	}

}
