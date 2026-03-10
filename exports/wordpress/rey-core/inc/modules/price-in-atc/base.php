<?php
namespace ReyCore\Modules\PriceInAtc;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	private $settings = [];

	private $product;

	const ASSET_HANDLE = 'reycore-price-in-atc';

	public function __construct()
	{
		add_action( 'reycore/customizer/control=single_atc__stretch', [ $this, 'add_customizer_options' ], 10, 2 );
		add_action( 'wp', [$this, 'init']);
	}

	public function init()
	{
		if( ! is_product() ){
			return;
		}

		$this->product = wc_get_product();

		if( ! $this->product ){
			return;
		}

		if( ! $this->product->is_purchasable() ){
			return;
		}

		if( ! $this->is_enabled() ){
			return;
		}

		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_filter( 'reycore/woocommerce/single_product/add_to_cart_button/simple', [ $this, 'add_in_button'], 30, 3 );
		add_filter( 'reycore/woocommerce/single_product/add_to_cart_button/variation', [ $this, 'add_in_button'], 30, 3 );

		$this->settings = apply_filters('reycore/module/price_in_atc', [
			'position' => 'after', //before/after
			'separator' => '' // eg: &nbsp;&nbsp;-&nbsp;&nbsp; or ' - '
		]);
	}

	public function enqueue_scripts(){
		reycore_assets()->add_scripts(['wnumb', self::ASSET_HANDLE]);
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

	public function add_in_button($html, $product, $text){

		if( ! apply_filters('reycore/module/price_in_atc/should_print_price', true) ){
			return $html;
		}

		$price = '';

		if( 'variable' === $product->get_type() ){

			$prices = $product->get_variation_prices( true );

			if ( ! empty( $prices['price'] ) ) {

				$min_price     = current( $prices['price'] );
				$max_price     = end( $prices['price'] );

				// is range, just be empty
				if ( $min_price !== $max_price ) {
					$price = '';
				}
				else {
					$price = wc_get_price_to_display( $product );
				}
			}

		}
		elseif( 'simple' === $product->get_type() ){
			$price = wc_get_price_to_display( $product );
		}
		else {
			return $html;
		}

		if( $price ){

			$min_quantity = apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product );

			// Check if the number is an integer
			if ( (int) $price == $price) {
				// Just return integer
				$price_format = number_format($price * $min_quantity, 0);
			} else {
				// If not, return the number with specified decimals
				$price_format = number_format(
					$price,
					wc_get_price_decimals(),
					wc_get_price_decimal_separator(),
					wc_get_price_thousand_separator()
				);
			}

			$price = sprintf(
				get_woocommerce_price_format(),
				get_woocommerce_currency_symbol(),
				$price_format
			);

		}

		$search = '<button type="submit"';
		$replace = sprintf(
			$search . ' data-price-atc-val="%1$s" data-price-atc-position="%2$s" data-price-atc-separator="%3$s"',
			$price,
			$this->settings['position'],
			$this->settings['separator']
		);
		return str_replace($search, $replace, $html);
	}

	function add_customizer_options($control_args, $section){

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'single_product_atc__price',
			'label'       => esc_html__( 'Add price inside the button', 'rey-core' ),
			'default'     => false,
		] );

	}

	public function is_enabled() {
		return get_theme_mod('single_product_atc__price', false);
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Price in Add to Cart button', 'Module name', 'rey-core'),
			'description' => esc_html_x('Shows the product price inside the add to cart button in the product page.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => [''],
			'help'        => reycore__support_url('kb/price-features-in-product-page'),
			'video' => true,
		];
	}

	public function module_in_use(){
		return $this->is_enabled();
	}
}
