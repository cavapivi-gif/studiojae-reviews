<?php
namespace ReyCore\Compatibility\TiWishlist;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{
	/**
	 * Wishlist plugin integration
	 * https://wordpress.org/plugins/ti-woocommerce-wishlist/
	 */

	const ASSET_HANDLE = 'ti-wishlist';

	public function __construct()
	{
		add_filter('reycore/woocommerce/wishlist/tag/enabled', '__return_true');
		add_action('init', [$this, 'init']);
		add_action( 'reycore/woocommerce/loop/init', [$this, 'register_components']);

	}

	function init(){

		add_filter( 'tinvwl_enable_wizard', '__return_false', 10);
		add_filter( 'tinvwl_prevent_automatic_wizard_redirect', '__return_true', 10);
		add_filter( 'tinvwl_wishlist_item_thumbnail', [ $this, 'prevent_product_slideshows'], 10, 3 );

		remove_action( 'woocommerce_before_shop_loop_item', 'tinvwl_view_addto_htmlloop', 9 );
		remove_action( 'woocommerce_after_shop_loop_item', 'tinvwl_view_addto_htmlloop', 9 );
		remove_action( 'woocommerce_after_shop_loop_item', 'tinvwl_view_addto_htmlloop', 10 );

		add_filter('reycore/woocommerce/wishlist/default_catalog_position', [$this, 'add_default_catalog_position']);
		add_filter('reycore/woocommerce/wishlist/ids', [$this, 'get_wishlist_ids']);
		add_filter('reycore/woocommerce/wishlist/button_html', [$this, 'button_html']);
		add_filter('reycore/woocommerce/wishlist/url', [$this, 'wishlist_url']);
		add_filter('reycore/woocommerce/wishlist/counter_html', [$this, 'wishlist_counter_html']);

		add_action( 'woocommerce_single_product_summary', [ $this, 'show_add_to_wishlist_in_product_page_catalog_mode'], 20);

		add_action( 'wp_footer', [$this, 'force_scripts']);
		add_action( 'reycore/assets/register_scripts', [ $this, 'register_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		add_filter( 'rey/main_script_params', [ $this, 'script_params'], 20 );

	}

	public function register_components( $base ){

		$base->register_component( new CompBottom );
		$base->register_component( new CompTopRight );

	}

	function add_default_catalog_position(){
		return tinv_get_option( 'add_to_wishlist_catalog', 'position' ) === 'above_thumb' ? 'topright' :  'bottom';
	}

	function get_wishlist_ids( $ids ){

		$products = \TInvWL_Public_Wishlist_View::instance()->get_current_products();

		if( empty($products) ){
			return $ids;
		}

		return wp_list_pluck($products, 'product_id');
	}

	function button_html(){
		return do_shortcode('[ti_wishlists_addtowishlist loop=yes]');
	}

	function wishlist_url(){
		return tinv_url_wishlist_default();
	}

	function wishlist_counter_html(){
		return '<span class="rey-wishlistCounter-number wishlist_products_counter"><span class="wishlist_products_counter_number"></span></span>';
	}

	function prevent_product_slideshows($html, $wl_product, $product){

		if( get_theme_mod('loop_extra_media', 'second') === 'slideshow' ) {
			$product->set_catalog_visibility('hidden');
			$html = str_replace('rey-productSlideshow', 'rey-productSlideshow --prevent-thumbnail-sliders --show-first-only', $html);
		}

		return $html;
	}

	function show_add_to_wishlist_in_product_page_catalog_mode(){
		if( reycore_wc__is_product() ) {
			$product = wc_get_product();
			if(
				! $product->is_purchasable() &&
				( $product->get_regular_price() || $product->get_sale_price() ||
					( $product->is_type( 'variable' ) && $product->get_price() !== '' )
				) ) {

				remove_action( 'woocommerce_single_product_summary', 'tinvwl_view_addto_htmlout', 29 );
				remove_action( 'woocommerce_single_product_summary', 'tinvwl_view_addto_htmlout', 31 );
				remove_action( 'woocommerce_before_add_to_cart_button', 'tinvwl_view_addto_html', 20 );
				remove_action( 'woocommerce_after_add_to_cart_button', 'tinvwl_view_addto_html', 0 );

				echo do_shortcode("[ti_wishlists_addtowishlist]");
			}
		}
	}

	function force_scripts(){

		if( ! function_exists('reycore_wc__get_account_panel_args') ){
			return;
		}

		$args = reycore_wc__get_account_panel_args();

		if( !($args['wishlist'] && $args['counter']) ){
			return;
		}

		wp_enqueue_script( 'tinvwl' );
	}

	public function script_params($params)
	{
		$params['wishlist_type'] = 'tinvwl';
		return $params;
	}

	public function enqueue_scripts(){
		reycore_assets()->add_styles(self::ASSET_HANDLE);
	}

	public function register_scripts($assets){

		$assets->register_asset('styles', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/style.css',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			]
		]);

	}

}
