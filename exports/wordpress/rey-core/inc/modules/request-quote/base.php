<?php
namespace ReyCore\Modules\RequestQuote;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	public static $settings = [];

	private $defaults = [];

	private $type = '';

	private $_load_template;

	const ASSET_HANDLE = 'reycore-request-quote';

	public function __construct()
	{
		add_action('init', [$this, 'init']);
		add_action( 'reycore/templates/register_widgets', [$this, 'register_widgets']);
		add_action( 'reycore/woocommerce/loop/init', [$this, 'register_component']);
	}

	public function init(){

		new Customizer();

		if( ! $this->is_enabled() ) {
			return;
		}

		$this->set_defaults();

		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
		add_filter( 'reycore/modal_template/show', '__return_true' );
		add_action( 'rey/after_site_wrapper', [$this, 'add_modal_template'], 50);
		add_action( 'woocommerce_single_product_summary', [$this, 'product_page_render'], 30);
		add_shortcode( 'rey_request_quote', [$this, 'get_button_html']);
		add_action( 'reycore/woocommerce/loop/quickview_button', [$this, 'load_assets_for_quickview']);
		add_filter( 'reycore/elementor/tag_archive/components', [$this, 'product_grid_components'], 10, 2);

		new CompatCf7();
		new CompatWpforms();
	}

	public function register_widgets($widgets_manager){
		$widgets_manager->register( new Element );
	}

	public function register_component($base){
		$base->register_component( new Catalog );
	}

	public function register_assets($assets){

		$assets->register_asset('styles', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/frontend-style.css',
				'deps'    => ['woocommerce-general'],
				'version'   => REY_CORE_VERSION,
				'priority' => 'low',
			],
		]);

		$assets->register_asset('scripts', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/frontend-script.js',
				'deps'    => ['reycore-woocommerce', 'rey-tmpl'],
				'version'   => REY_CORE_VERSION,
				'localize' => [
					'name' => 'reycoreRequestQuoteParams',
					'params' => [
						'variation_aware' => $this->defaults['variation_aware'],
						'disabled_text'   => $this->defaults['disabled_text'],
						'close_position'  => $this->defaults['close_position'],
						'modal_title'     => $this->defaults['title'],
						'product_title'   => $this->defaults['product_title'],
					],
				],
			],
		]);

	}

	/**
	 * Set defaults
	 *
	 * @since 1.2.0
	 **/
	public function set_defaults()
	{
		$this->defaults = apply_filters('reycore/woocommerce/request_quote_defaults', [
			'title' => get_theme_mod('request_quote__btn_text', esc_html__( 'Request a Quote', 'rey-core' ) ),
			'product_title' => esc_html__( 'PRODUCT: ', 'rey-core' ),
			'close_position' => 'inside',
			'show_in_quickview' => get_theme_mod('request_quote__qv', true ),
			'variation_aware' => get_theme_mod('request_quote__var_aware', false ),
			'disabled_text' => esc_html__('Please select some product options before requesting quote.', 'rey-core')
		]);
	}

	private function maybe_show(){

		if( ! $this->is_enabled() ) {
			return;
		}

		$this->set_defaults();

		static $show;

		if( is_null($show) ){

			$show = true;

			if( $this->type === 'products' )
			{
				if( $products = get_theme_mod('request_quote__products', '') ){
					$get_products_ids = array_map( 'absint', array_map( 'trim', explode( ',', $products ) ) );
					$show = in_array(get_the_ID(), $get_products_ids);
				}
			}

			elseif( $this->type === 'categories' )
			{
				if( $categories = get_theme_mod('request_quote__categories', []) ){
					if( $product = reycore_wc__get_product() ){
						$show = has_term($categories, 'product_cat', $product->get_id());
					}
				}
			}

		}

		return apply_filters('reycore/woocommerce/request_quote/maybe_show', $show, $this);
	}

	public function add_modal_template(){

		if( ! $this->_load_template ){
			return;
		}

		$form = apply_filters('reycore/woocommerce/request_quote/output', '', [
			'class' => 'rey-form--basic'
		] );

		if( empty($form) ){
			return;
		}

		reycore__get_template_part('template-parts/woocommerce/request-quote-modal', false, false, [
			'form' => $form,
			'defaults' => $this->defaults
		]);

	}

	public function load_scripts(){

		reycore_assets()->add_styles(['rey-buttons', self::ASSET_HANDLE]);
		reycore_assets()->add_scripts(self::ASSET_HANDLE);

		// load modal scripts
		add_filter('reycore/modals/always_load', '__return_true');

		$this->_load_template = true;
	}

	/**
	* Add the button
	*
	* @since 1.2.0
	*/
	public function get_button_html( $args = [] ){

		if( ! empty($args) ){
			$this->defaults = array_merge($this->defaults, $args);
		}

		$product_data = [
			'title' => '',
			'sku' => '',
		];

		if( $product = wc_get_product() ){
			$product_data['title'] = $product->get_title();
			$product_data['sku'] = $product->get_sku();
		}

		$css_classes = [];

		reycore__get_template_part('template-parts/woocommerce/request-quote-button', false, false, [
			'button_text'  => $this->defaults['title'],
			'classes'      => $css_classes,
			'product_data' => $product_data,
		]);

		$this->load_scripts();

	}

	public function product_page_render(){

		if( ! reycore_wc__is_product() ){
			return;
		}

		if( get_query_var('rey__is_quickview', false) ){
			if( false === $this->defaults['show_in_quickview'] ){
				return;
			}
		}

		if( ! $this->maybe_show() ){
			return;
		}

		$this->get_button_html();
	}

	public function catalog_render(){

		if( ! $this->maybe_show() ){
			return;
		}

		$this->get_button_html();
	}

	public function load_assets_for_quickview(){

		if( ! $this->defaults['show_in_quickview'] ){
			return;
		}

		static $loaded;

		if( is_null($loaded) ){
			$this->load_scripts();
			$loaded = true;
		}
	}

	public function product_grid_components( $components, $element ){

		// 'inherits' will bail
		if( isset( $element->_settings['hide_request_quote'] ) && ($setting = $element->_settings['hide_request_quote']) ){
			$components['request_quote'] = $setting === 'no';
		}

		return $components;
	}

	public function get_type(){
		return $this->type = get_theme_mod('request_quote__type', '');;
	}

	public function is_enabled(){
		return $this->get_type() !== '';
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Request a quote', 'Module name', 'rey-core'),
			'description' => esc_html_x('Allow customers to request quotes or informations about a specific product.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => ['product page'],
			'help'        => reycore__support_url('kb/request-a-quote-form/'),
			'video' => true,
		];
	}

	public function module_in_use(){
		return $this->is_enabled();
	}

}
