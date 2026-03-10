<?php
namespace ReyCore\Modules\ProductSubtitle;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	const ASSET_HANDLE = 'reycore-module-product-subtitle';

	public $data;

	public function __construct()
	{
		add_action( 'reycore/woocommerce/init', [$this, 'init']);
		add_action( 'reycore/woocommerce/loop/init', [$this, 'register_loop_component']);
	}

	public function init() {

		new Customizer();
		new AcfFields();

		$this->data = [
			'loop' => [
				'enabled' => get_theme_mod('psubtitle__loop', false)
			],
			'pdp' => [
				'enabled' => get_theme_mod('psubtitle__pdp', false)
			],
		];

		if( ! ($this->data['loop']['enabled'] || $this->data['pdp']['enabled']) ){
			return;
		}

		$this->data['loop']['position'] = get_theme_mod('psubtitle__loop_pos', 'after_title');
		$this->data['loop']['tag'] = \ReyCore\Helper::get_valid_title_tag(get_theme_mod('psubtitle__loop_tag', 'p'));

		$this->data['pdp']['position'] = get_theme_mod('psubtitle__pdp_pos', 'after_title');
		$this->data['pdp']['tag'] = \ReyCore\Helper::get_valid_title_tag(get_theme_mod('psubtitle__pdp_tag', 'h4'));

		new Pdp();

		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
		add_filter( 'reycore/elementor/tag_archive/components', [$this, 'product_grid_components'], 10, 2);
		add_filter( 'woocommerce_cart_item_name', [$this, 'cart_checkout'], 10, 2);

	}

	public function register_loop_component($base){
		$base->register_component( new Catalog );
	}

	public function product_grid_components( $components, $element ){

		// 'inherits' will bail
		if( isset( $element->_settings['hide_product_subtitle'] ) && ($setting = $element->_settings['hide_product_subtitle']) ){
			$components['product_subtitle'] = $setting === 'no';
		}

		return $components;
	}

	public function enqueue_scripts(){
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
	}

	public function cart_checkout( $name, $cart_item){

		if( ! apply_filters('reycore/woocommerce/module/product-subtitle/cart_display', false) ){
			return $name;
		}

		ob_start();
		$this->render($cart_item['product_id']);
		$subtitle = ob_get_clean();

		return $name . $subtitle;
	}

	public function render( $product_id = null ){

		if( is_null($product_id) || '' === $product_id ){

			if( ! ($product = wc_get_product()) ){
				return;
			}

			$product_id = $product->get_id();
		}

		if( ! (class_exists('ACF') && ($subtitle = get_field('product_subtitle_text', $product_id))) ){
			return;
		}

		$type = ($page_product_id = get_queried_object_id()) && $product_id === $page_product_id ? 'pdp' : 'loop';

		printf('<%2$s class="rey-pSubtitle --pos-%3$s">%1$s</%2$s>', reycore__parse_text_editor($subtitle), $this->data[$type]['tag'], esc_attr($this->data[$type]['position']));

		$this->enqueue_scripts();
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Product subtitle', 'Module name', 'rey-core'),
			'description' => esc_html_x('Display a text as subtitle, for products in catalog and product page.', 'Module description', 'rey-core'),
			'categories'  => ['woocommerce'],
			'keywords'    => ['Elementor', 'Product Page', 'Product catalog'],
			'help'        => reycore__support_url('kb/product-subtitle/'),
			'video'       => true,
		];
	}

	public function module_in_use(){

		if( ! ($this->data['loop']['enabled'] || $this->data['pdp']['enabled']) ){
			return false;
		}

		$post_ids = get_posts([
			'post_type'   => 'product',
			'numberposts' => -1,
			'post_status' => 'publish',
			'fields'      => 'ids',
			'meta_query'  => [
				[
					'key'     => 'product_subtitle_text',
					'value'   => '',
					'compare' => 'NOT IN'
				],
			]
		]);

		return ! empty($post_ids);
	}
}
