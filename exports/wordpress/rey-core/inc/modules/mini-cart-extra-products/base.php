<?php
namespace ReyCore\Modules\MiniCartExtraProducts;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	const ASSET_HANDLE = 'reycore-module-minicart-extra-products';

	public $settings;

	public function __construct()
	{
		add_action( 'reycore/woocommerce/init', [$this, 'init']);

		new Customizer();
	}

	public function init() {

		if( ! $this->is_enabled() ){
			return;
		}

		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
		add_action( 'reycore/ajax/register_actions', [ $this, 'register_actions' ] );
		add_action( 'reycore/woocommerce/mini-cart/before', [$this, 'render_panel']);

	}

	public function register_actions( $ajax_manager ) {
		$ajax_manager->register_ajax_action( 'cart_extra_products', [$this, 'ajax__response'], [
			'auth'   => 3,
			'nonce'  => false,
			'transient' => 2 * HOUR_IN_SECONDS,
			// 'assets' => true,
		] );
	}

	public function ajax__response(){

		$type = get_theme_mod('header_cart__extra_products_type', 'latest');
		$product_data = [];

		if( 'latest' === $type ){
			$product_ids = self::query();
		}

		elseif( 'bestsellers' === $type ){
			$query_args['meta_key'] = 'total_sales'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			$query_args['order']    = 'DESC';
			$query_args['orderby']  = 'meta_value_num';
			$product_ids = self::query($query_args);
		}

		elseif( 'sales' === $type ){
			$query_args['post__in']  = array_merge( array( 0 ), wc_get_product_ids_on_sale() );
			$product_ids = self::query($query_args);
		}

		elseif( 'wishlist' === $type ){
			if( $wishlist_mod = reycore__get_module('wishlist')){
				$product_ids = $wishlist_mod::get_cookie_products_ids();
			}
		}

		elseif( 'cross-sells' === $type ){
			$product_ids = array_merge( array( 0 ), WC()->cart->get_cross_sells() );
		}

		elseif( 'manual' === $type ){
			$product_ids = (array) get_theme_mod('header_cart__extra_products_manual', []);
		}

		if( ! $product_ids ){
			return $product_data;
		}

		foreach ($product_ids as $product_id) {

			if( ! ($product = wc_get_product($product_id)) ) {
				continue;
			}

			$item = [
				'id'    => $product_id,
				'link'  => $product->get_permalink(),
				'title' => $product->get_title(),
				'image' => $product->get_image(),
				'brand' => '',
				'price' => $product->get_price_html(),
			];

			if($brands_mod = reycore__get_module('brands')){
				if ( $brands_mod->brands_tax_exists() && ( $brands_terms = $brands_mod->get_brands('', $product_id) ) ) {
					$item['brand'] = $brands_mod->catalog__brand_output($brands_terms);
				}
			}

			$product_data[] = $item;
		}

		return $product_data;
	}

	public static function query( $query_args = [] ){

		$query_args = wp_parse_args($query_args, [
			'post_type'           => 'product',
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'orderby'             => 'date',
			'order'               => 'ASC',
			'posts_per_page'      => absint(get_theme_mod('header_cart__extra_products_limit', 12)),
			'fields'              => 'ids',
			'suppress_filters'    => true,
		]);

		$query_args = apply_filters('reycore/woocommerce/cartpanel/extra_products/query_args', $query_args);

		// get out of stock products
		$the_query = new \WP_Query( $query_args );

		if( empty($the_query->posts) ){
			return [];
		}

		return $the_query->posts;
	}

	public function render_panel(){

		?>

		<div class="rey-cartExtraProducts" id="rey-cart-extra-products" data-status="open">
			<button class="__toggle"><?php echo reycore__get_svg_icon(['id'=>'chevron']) ?></button>
			<div class="__inner">
				<div class="__title"><?php echo ($title = get_theme_mod('header_cart__extra_products_title', '')) ? $title : esc_html__('You might like..', 'rey-core'); ?></div>
				<div class="__content">
					<div class="rey-lineLoader"></div>
				</div>
			</div>
		</div>

		<script type="text/html" id="tmpl-reyCartExtraProducts">
			<# if( data.items.length ){ #>
				<div class="__the-content">
					<# for (var i = 0; i < data.items.length; i++) { #>
					<div class="__product" data-id="{{data.items[i].id}}">
						<a href="{{data.items[i].link}}" class="__product-link">
							{{{data.items[i].image}}}
						</a>
						<# if( data.items[i].brand ){ #>
							<span class="__product-brand">{{{data.items[i].brand}}}</span>
						<# } #>
						<<?php echo reycore_wc__minicart_product_title_tag() ?> class="__product-title"><a href="{{data.items[i].link}}">{{{data.items[i].title}}}</a></<?php echo reycore_wc__minicart_product_title_tag() ?>>
						<span class="__product-price">{{{data.items[i].price}}}</span>
					</div>
					<# } #>
				</div>
			<# } #>
		</script>

		<?php
		$this->add_cart_assets();
	}

	public function add_cart_assets(){
		reycore_assets()->add_styles( [ 'rey-wc-general', 'rey-wc-general-deferred', 'rey-simple-scrollbar', self::ASSET_HANDLE] );
		reycore_assets()->add_scripts( [ 'rey-tmpl', 'rey-simple-scrollbar', self::ASSET_HANDLE ] );
	}

	public function set_settings(){

		$this->settings = apply_filters('reycore/woocommerce/cartpanel/extra_products', [
		]);

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
				'deps'    => ['reycore-woocommerce', 'rey-tmpl'],
				'version'   => REY_CORE_VERSION,
			]
		]);

	}

	public function is_enabled() {
		return get_theme_mod('header_cart__extra_products', false);
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Mini-Cart Extra Products Popup', 'Module name', 'rey-core'),
			'description' => esc_html_x('Shows a vertical list of products on the side of the cart.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => ['cart'],
			'help'        => reycore__support_url('kb/shopping-cart-popup-side-panel/#display-a-vertical-list-of-products'),
			'video' => true,
		];
	}

	public function module_in_use(){
		return $this->is_enabled();
	}
}
