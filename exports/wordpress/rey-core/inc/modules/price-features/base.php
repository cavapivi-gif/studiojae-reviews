<?php
namespace ReyCore\Modules\PriceFeatures;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	private $settings = [];

	private $product;

	const ASSET_HANDLE = 'reycore-price-features';

	public function __construct()
	{

		add_action( 'reycore/customizer/control=single_product_price_text_inline', [ $this, 'add_customizer_options' ], 10, 2 );
		add_action( 'wp', [$this, 'init']);
	}

	public function init()
	{

		if( ! $this->is_enabled() ){
			return;
		}

		if( ! is_product() ){
			return;
		}

		if( ! ($this->product = wc_get_product()) ){
			return;
		}

		$this->settings = apply_filters('reycore/module/price_features', [
			'force_is_purchasable' => $this->product->is_purchasable()
		]);

		// product must be purchasable to allow loading this mod
		// but, can be overriden by filter
		if( ! $this->settings['force_is_purchasable'] ){
			return;
		}

		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_filter( 'body_class', [$this, 'add_hide_variation_price_class']);

	}

	function add_hide_variation_price_class($classes){

		if( $this->price_variation_change__enabled() ){
			$classes[] = '--hide-variation-price';
		}

		return $classes;
	}

	public static function load_scripts(){
		reycore_assets()->add_scripts(['wnumb', self::ASSET_HANDLE]);
		reycore_assets()->add_styles(self::ASSET_HANDLE);
	}

	public function enqueue_scripts(){
		if( is_product() ){
			self::load_scripts();
		}
	}

	public function register_assets($assets){

		$assets->register_asset('styles', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/style.css',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			]
		]);

		$params = [
			'variation_price_to_main'  => $this->price_variation_change__enabled(),
			'price_show_total'         => $this->price_add_total__enabled(),
			'price_instalments'        => $this->price_instalments__enabled(),
			'price_instalments_number' => get_theme_mod('single_product_price__instalments_number', 6),
			'price_instalments_text'   => __('Pay %p in %i instalments.', 'rey-core'),
		];

		if( $custom_text = get_theme_mod('single_product_price__instalments_text') ){
			$params['price_instalments_text'] = $custom_text;
		}

		$assets->register_asset('scripts', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/script.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
				'localize' => [
					'name' => 'reycorePriceFeaturesParams',
					'params' => $params,
				],
			]
		]);

	}

	function add_customizer_options($control_args, $section){

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'single_product_price__variations',
			'label'       => esc_html__( 'Change main price in Variable products', 'rey-core' ),
			'default'     => false,
			'active_callback' => [
				[
					'setting'  => 'single_product_price',
					'operator' => '==',
					'value'    => true,
				],
			],
			'separator' => 'before',
			'help' => [
				esc_html__('When selecting variations, the main price will also get updated with the variation price.', 'rey-core')
			]
		] );

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'single_product_price__show_total',
			'label'       => esc_html__( 'Add "Total" based on Quantity', 'rey-core' ),
			'default'     => false,
			'active_callback' => [
				[
					'setting'  => 'single_product_price',
					'operator' => '==',
					'value'    => true,
				],
			],
			'help' => [
				esc_html__('When changing quantity, a new "Total" price will be shown under the main price.', 'rey-core')
			]
		] );

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'single_product_price__instalments',
			'label'       => esc_html__( 'Add payment in instalments text.', 'rey-core' ),
			'default'     => false,
			'active_callback' => [
				[
					'setting'  => 'single_product_price',
					'operator' => '==',
					'value'    => true,
				],
			],
			'help' => [
				esc_html__('Shows the price split in instalments.', 'rey-core')
			]
		] );

		$section->add_control( [
			'type'        => 'rey-number',
			'settings'    => 'single_product_price__instalments_number',
			'label'       => esc_html__( 'Number of instalments', 'rey-core' ),
			'default'     => 6,
			'choices'     => [
				'min'  => 1,
				'max'  => 100,
				'step' => 1,
			],
			'active_callback' => [
				[
					'setting'  => 'single_product_price',
					'operator' => '==',
					'value'    => true,
				],
				[
					'setting'  => 'single_product_price__instalments',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$section->add_control( [
			'type'        => 'text',
			'settings'    => 'single_product_price__instalments_text',
			'label'       => esc_html__( 'instalments Text', 'rey-core' ),
			'default'     => __('Pay %p in %i instalments.', 'rey-core'),
			'input_attrs'     => [
				'placeholder' => __('eg: Pay %p in %i instalments.', 'rey-core'),
				'data-control-class' => '--text-xl',
			],
			'sanitize_callback' => ['Kirki_Sanitize_Values', 'unfiltered'],
			'active_callback' => [
				[
					'setting'  => 'single_product_price',
					'operator' => '==',
					'value'    => true,
				],
				[
					'setting'  => 'single_product_price__instalments',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );
	}

	public function price_variation_change__enabled(){
		return get_theme_mod('single_product_price__variations', false);
	}

	public function price_add_total__enabled(){
		return get_theme_mod('single_product_price__show_total', false);
	}

	public function price_instalments__enabled(){
		return get_theme_mod('single_product_price__instalments', false);
	}

	public function is_enabled() {
		return ($this->price_variation_change__enabled() || $this->price_add_total__enabled() || $this->price_instalments__enabled());
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Price Features (Show total, Quantity variations, Price Instalments)', 'Module name', 'rey-core'),
			'description' => esc_html_x('Adds various features inside the product page, to extend the display of the price.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => ['product page'],
			'help'        => reycore__support_url('kb/price-features-in-product-page'),
			'video' => true,
		];
	}

	public function module_in_use(){
		return $this->is_enabled();
	}
}
