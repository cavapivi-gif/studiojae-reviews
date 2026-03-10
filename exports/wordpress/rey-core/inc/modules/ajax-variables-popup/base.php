<?php
namespace ReyCore\Modules\AjaxVariablesPopup;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	private $settings = [];

	const ASSET_HANDLE = 'reycore-ajax-variables-popup';

	public static $supported_types = [];

	public function __construct()
	{
		parent::__construct();

		add_action( 'reycore/customizer/section=woo-catalog-product-item/marker=loop_atc_options', [ $this, 'add_customizer_options' ] );

		if( ! $this->is_enabled() ){
			return;
		}

		self::$supported_types = apply_filters('reycore/woocommerce/variables_popup/supported_types', ['bundle', 'variable']);

		add_filter( 'rey/main_script_params', [ $this, 'script_params'], 20 );
		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
		add_filter( 'reycore/woocommerce/loop/add_to_cart/content', [ $this, 'add_preloader'], 20, 2 );
		add_action( 'reycore/ajax/register_actions', [ $this, 'register_actions' ] );
		add_action( 'woocommerce_after_shop_loop_item', [ $this, 'load_module_assets' ] );
		add_action( 'reycore/woocommerce/minicart/products_scripts', [ $this, 'add_assets' ] );
		add_filter( 'woocommerce_loop_add_to_cart_args', [ $this, 'remove_ajax_atc_variations'], 20, 2 );

	}

	public function add_assets(){

		reycore_assets()->add_styles(['rey-overlay', self::ASSET_HANDLE]);
		reycore_assets()->add_scripts(self::ASSET_HANDLE);

	}

	public function load_module_assets(){

		global $product;

		if( ! $product ){
			$product = wc_get_product();
		}

		if( ! $product ){
			return;
		}

		if( ! in_array($product->get_type(), self::get_supported_types(), true) ){
			return;
		}

		$this->add_assets();
	}

	public function load_dependencies(){

		if( ! $this->is_enabled() ){
			return;
		}

		$assets = [
			'styles' => [
				'rey-wc-product-lite',
				'rey-wc-product',
				'rey-wc-general',
				'rey-wc-general-deferred',
			],
			'scripts' => [
				'reycore-wc-product-page-general',
				'wc-add-to-cart-variation',
			],
		];

		if( class_exists('\ReyCore\WooCommerce\Pdp') && \ReyCore\WooCommerce\Pdp::product_page_ajax_add_to_cart() ){
			$assets['scripts'][] = 'reycore-wc-product-page-ajax-add-to-cart';
		}

		if( get_theme_mod('single_atc_qty_controls', true) ){
			$assets['scripts'][] = 'reycore-wc-product-page-qty-controls';
			if( 'select' === get_theme_mod('single_atc_qty_controls_styles', 'default') ){
				$assets['scripts'][] = 'reycore-wc-product-page-qty-select';
			}
		}

		reycore_assets()->add_scripts($assets['scripts']);
		reycore_assets()->add_styles($assets['styles']);
	}


	public static function get_supported_types(){
		return self::$supported_types;
	}

	public function register_assets($assets){

		if( ! $this->is_enabled() ){
			return;
		}

		$assets->register_asset('styles', [
			self::ASSET_HANDLE => [
				'src'      => self::get_path( basename( __DIR__ ) ) . '/style.css',
				'deps'     => [],
				'version'  => REY_CORE_VERSION,
				'priority' => 'low'
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

	public function script_params($params)
	{
		$params['loop_ajax_variable_products'] = true;
		$params['loop_ajax_variable_supported'] = self::get_supported_types();
		return $params;
	}

	public function register_actions( $ajax_manager ){
		$ajax_manager->register_ajax_action( 'loop_variable_product_add_to_cart', [$this, 'ajax__get_content'], [
			'auth'   => 3,
			'nonce'  => false,
			'assets' => true,
		] );
	}

	public function ajax__get_content( $action_data ) {

		if( ! ( isset($action_data['product_id']) && $product_id = absint($action_data['product_id']) ) ){
			return ['errors' => esc_html__('Product ID not found.', 'rey-core')];
		}

		global $post, $product;

		$this->load_dependencies();

		remove_all_actions('woocommerce_after_add_to_cart_button');
		remove_all_actions('woocommerce_after_add_to_cart_form');

		add_filter( 'reycore/woocommerce/pdp/render/before_add_to_cart', '__return_false');
		add_filter( 'reycore/woocommerce/pdp/render/after_add_to_cart', '__return_false');

		add_filter( 'reycore/woocommerce/wrap_quantity', '__return_true');
		add_filter( 'reycore/woocommerce/add_quantity_controls', '__return_true');

		if( 'hide' === get_theme_mod('product_page__stock_display', 'show') ){
			add_filter( 'woocommerce_get_stock_html', '__return_false');
		}

		do_action('reycore/woocommerce/quantity/add_to_cart_button_wrap');

		if( ! ( $product = wc_get_product($product_id)) ){
			return ['errors' => esc_html__('Not a product.', 'rey-core')];
		}

		$supported_types = self::get_supported_types();

		if( in_array('variation', $supported_types, true) && $product->is_type('variation') ){

			$variation_product_id = $product_id;
			$variation_product = $product;

			$product_id = $product->get_parent_id();
			$product = wc_get_product( $product_id );

			$variation_attributes = $variation_product->get_variation_attributes();
			$variation_attributes_data = array_filter( $variation_attributes, 'wc_array_filter_default_attributes' );

			add_filter( 'woocommerce_dropdown_variation_attribute_options_args', function($args) use ($variation_attributes_data) {

				if( empty($variation_attributes_data) ){
					return $args;
				}

				if( ! isset($variation_attributes_data[ 'attribute_' . $args['attribute'] ]) ){
					return $args;
				}

				$args['selected'] = $variation_attributes_data[ 'attribute_' . $args['attribute'] ];

				return $args;
			}, 100);
		}

		if( ! $product->is_purchasable() ){
			return ['errors' => esc_html__('Product not purchasable.', 'rey-core')];
		}

		if( $product->is_type('variable') ){

			// Include WooCommerce frontend stuff
			wc()->frontend_includes();

			$post = get_post( $product_id );
			setup_postdata( $post );

			ob_start();

			reycore__get_template_part('template-parts/woocommerce/variations-popup', false, false, [
				'pid' => $product_id
			]);

			$data['markup'] = ob_get_clean();

			wp_reset_postdata();

			if( isset($action_data['woo_template']) && ! absint($action_data['woo_template']) ){
				ob_start();
				wc_get_template( 'single-product/add-to-cart/variation.php' );
				$data['woo-template-scripts'] = ob_get_clean();
			}

			return $data;
		}

		return ['errors' => esc_html__('Product not purchasable.', 'rey-core')];

	}

	public function remove_ajax_atc_variations($args, $product){

		$supported_types = self::get_supported_types();

		if( in_array('variation', $supported_types, true) && $product->is_type('variation') ){
			if( isset($args['class']) ){
				$args['class'] = str_replace(' ajax_add_to_cart', '', $args['class']);
			}
		}

		return $args;
	}

	function add_preloader( $content, $product ){

		if( in_array($product->get_type(), self::get_supported_types(), true) ){
			return $content . '<i class="rey-lineLoader __ajax-preloader --invisible"></i>';
		}

		return $content;
	}

	public function add_customizer_options($section){

		$section->add_control( [
			'type'     => 'toggle',
			'settings' => 'loop_ajax_variable_products',
			'label'       => esc_html__('Ajax Variations Form Pop-up', 'rey-core'),
			'help' => [
				__('If enabled, variable products with "Select Options" button, will show the variations form on click.', 'rey-core')
			],
			'default'  => false,
		]);

	}

	public function is_enabled() {
		return get_theme_mod('loop_ajax_variable_products', false);
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Options Popup for Variable Products', 'Module name', 'rey-core'),
			'description' => esc_html_x('This will make "Select Options" buttons in product listings to open a small popup containing the variation options to be picked and added to cart.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => ['Product catalog', 'Variations'],
			'help'        => reycore__support_url('kb/add-to-cart-popup-for-variable-products'),
			'video' => true,
		];
	}

	public function module_in_use(){
		return $this->is_enabled();
	}

}
