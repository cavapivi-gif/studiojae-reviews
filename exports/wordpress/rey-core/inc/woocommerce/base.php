<?php
namespace ReyCore\WooCommerce;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use ReyCore\Plugin;
use ReyCore\Helper;

class Base {

	const REY_ENDPOINT = 'rey/v1';

	public $supported;

	protected static $public_taxonomies;

	public function __construct(){

		if ( ! class_exists('\WooCommerce') ) {
			return;
		}

		$this->includes();
		$this->add_support();

		add_action( 'init', [ $this, 'init']);

		Plugin::instance()->woocommerce_loop = new Loop();
		Plugin::instance()->woocommerce_pdp = new Pdp();
		Plugin::instance()->woocommerce_assets = new Assets();

		foreach ([
			'Tags/Shortcodes',
			'Tags/Templates',
			'Tags/Sidebar',
			'Tags/LoginRegister',
			'Tags/Reviews',
			'Tags/VariationsLoop',
			'Tags/Search',
			'Tags/Wishlist',
			'Tags/Quantity',
			'Tags/Cart',
			'Tags/MiniCart',
			'Tags/Checkout',
			'Tags/Tabs',
			'Tags/Related',
		] as $tag) {
			$class_name = Helper::fix_class_name($tag, 'WooCommerce');
			$tag_name = str_replace('tags/', '', strtolower($tag));
			Plugin::instance()->woocommerce_tags[ $tag_name ] = new $class_name();
		}

		$this->supported = true;

		do_action('reycore/woocommerce');
	}

	function includes(){

		// convert to class
		require_once __DIR__ . '/functions.php';
		require_once __DIR__ . '/tags.php';
		require_once __DIR__ . '/assets.php';

	}

	/**
	 * General actions
	 * @since 1.0.0
	 **/
	public function init()
	{

		self::handle_catalog_mode();

		// Remove default wrappers.
		remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper' );
		remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end' );
		remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

		add_filter( 'rey/main_script_params', [ $this, 'script_params'], 10 );
		add_action( 'admin_bar_menu', [$this, 'shop_page_toolbar_edit_link'], 100);
		add_filter( 'body_class', [ $this, 'body_classes'], 20 );

		if( function_exists('rey_action__before_site_container') && function_exists('rey_action__after_site_container') ){
			// add rey wrappers
			add_action( 'woocommerce_before_main_content', 'rey_action__before_site_container', 0 );
			add_action( 'woocommerce_after_main_content', 'rey_action__after_site_container', 10 );
		}

		if( apply_filters('reycore/woocommerce/prevent_atc_when_not_purchasable', false) ){
			add_action( 'woocommerce_single_variation', function(){
				if( ($product = wc_get_product()) && ! $product->is_purchasable() ){
					remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20 );
				}
			}, 0 );
		}

		// disable post thumbnail (featured-image) in woocommerce posts
		add_filter( 'rey__can_show_post_thumbnail', function(){
			return ! is_woocommerce();
		} );

		// force flexslider to be disabled
		add_filter( 'woocommerce_single_product_flexslider_enabled', '__return_false', 100 );

		do_action('reycore/woocommerce/init', $this);

	}

	public function add_support(){

		add_theme_support( 'woocommerce', [
			'product_grid::max_columns' => 6,
			'product_grid' => [
				'max_columns'=> 6
			],
		 ] );
	}

	public static function handle_catalog_mode(){

		// disable shop functionality
		if( ! reycore_wc__is_catalog() ){
			return;
		}

		add_filter( 'woocommerce_is_purchasable', '__return_false');


		// Variable products - hide ATC FORM
		if( get_theme_mod('shop_catalog__variable', 'hide') === 'hide' ){
			remove_action( 'woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart', 30 );
		}
		// Variable products - hide ATC BUTTON
		else if( get_theme_mod('shop_catalog__variable', 'hide') === 'hide_just_atc' ){
			remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20 );
		}
	}


	/**
	 * Filter main script's params
	 *
	 * @since 1.0.0
	 **/
	public function script_params($params)
	{

		$params['woocommerce'] = true;
		$params['wc_ajax_url'] = \WC_AJAX::get_endpoint( '%%endpoint%%' );
		$params['rest_url'] = esc_url_raw( rest_url( self::REY_ENDPOINT ) );
		$params['rest_nonce'] = wp_create_nonce( 'wp_rest' );
		$params['catalog_cols'] = reycore_wc_get_columns('desktop');
		$params['catalog_mobile_cols'] = reycore_wc_get_columns('mobile');
		$params['added_to_cart_text'] = reycore__texts('added_to_cart_text');
		$params['added_to_cart_text_timeout'] = 10000;
		$params['cannot_update_cart'] = reycore__texts('cannot_update_cart');
		$params['site_id'] = is_multisite() ? get_current_blog_id() : 0;
		$params['after_add_to_cart'] = get_theme_mod('product_page_after_add_to_cart_behviour', 'cart');
		$params['ajax_add_review'] = true;
		$params['ajax_add_review_reload_text'] = esc_html__('Reloading page...', 'rey-core');
		$params['ajax_add_review_await_approval_text'] = esc_html__( 'Your review is awaiting approval', 'woocommerce' );
		if( 'checkout' === $params['after_add_to_cart'] ){
			$params['checkout_url'] = get_permalink( wc_get_page_id( 'checkout' ) );
		}
		$params['js_params'] = [
			'select2_overrides' => true,
			'scattered_grid_max_items' => 7,
			'scattered_grid_custom_items' => [],
			'product_item_slideshow_nav' => get_theme_mod('loop_slideshow_nav', 'dots'),
			'product_item_slideshow_disable_mobile' => get_theme_mod('loop_extra_media_disable_mobile', get_theme_mod('loop_slideshow_disable_mobile', false) ),
			'product_item_slideshow_hover_delay' => 250,
			'scroll_top_after_variation_change' => get_theme_mod('product_page_scroll_top_after_variation_change', false),
			'scroll_top_after_variation_change_desktop' => false,
			'ajax_search_letter_count' => 3,
			'ajax_search_allow_empty' => false,
			'cart_update_threshold' => 1000,
			'cart_update_by_qty' => true,
			'photoswipe_light' => get_theme_mod('product_page_gallery_lightbox_theme', 'light' === get_theme_mod('style_neutrals_theme', 'light')),
			'customize_pdp_atc_text' => true,
			'infinite_cache' => get_theme_mod('loop_pagination_cache_products', true),
			'acc_animation' => 250,
			'acc_scroll_top' => false,
			'acc_scroll_top_mobile_only' => true,
		];

		$params['currency_symbol'] = get_woocommerce_currency_symbol();
		$params['price_format'] = sprintf( get_woocommerce_price_format(), $params['currency_symbol'], '{{price}}' );

		$params['total_text'] = __( 'Total:', 'woocommerce' );

		if( !isset($params['ajaxurl']) ){
			$params['ajaxurl'] = admin_url( 'admin-ajax.php' );
			$params['ajax_nonce'] = wp_create_nonce( 'rey_nonce' );
		}

		$params['price_thousand_separator'] = wc_get_price_thousand_separator();
		$params['price_decimal_separator'] = wc_get_price_decimal_separator();
		$params['price_decimal_precision'] = wc_get_price_decimals();

		if( isset($params['theme_js_params']['embed_responsive']['elements']) ){
			$params['theme_js_params']['embed_responsive']['elements'][] = '.rey-wcPanel iframe[src*="youtu"]';
			$params['theme_js_params']['embed_responsive']['elements'][] = '.woocommerce-Tabs-panel iframe[src*="youtu"]';
			$params['theme_js_params']['embed_responsive']['elements'][] = '.woocommerce-product-details__short-description iframe[src*="youtu"]';
		}

		return $params;
	}

	/**
	 * Add Edit Page toolbar link for Shop Page
	 *
	 * @since 1.0.0
	 */
	function shop_page_toolbar_edit_link( $admin_bar ){
		if( is_shop() ){
			$admin_bar->add_menu( array(
				'id'    => 'edit',
				'title' => __('Edit Shop Page', 'rey-core'),
				'href'  => get_edit_post_link( wc_get_page_id('shop') ),
				'meta'  => array(
					'title' => __('Edit Shop Page', 'rey-core'),
				),
			));
		}
	}

	/**
	 * Filter body css classes
	 * @since 1.0.0
	 */
	function body_classes($classes)
	{

		if( reycore_wc__is_catalog() ) {
			$classes[] = '--catalog-mode';
		}

		if( get_theme_mod('woo_notices', true) ){
			$classes[] = 'r-notices';
		}

		return $classes;
	}

	/**
	 * Get all public taxonomies
	 *
	 * @return array
	 */
	public static function get_public_taxonomies(){

		if( self::$public_taxonomies ){
			return self::$public_taxonomies;
		}

		$p = [
			'product_cat',
			'product_tag',
		];

		foreach (wc_get_attribute_taxonomies() as $key => $attribute) {
			if( $attribute->attribute_public ){
				$p[] = wc_attribute_taxonomy_name($attribute->attribute_name);
			}
		}

		return self::$public_taxonomies = $p;
	}

	/**
	 * Check if a taxonomy has "archives" enabled.
	 * Must have the "pa_" for attributes.
	 *
	 * @param string $tax
	 * @return bool
	 */
	public static function taxonomy_is_public( $tax = '' ){

		if( ! $tax ){
			return false;
		}

		// if( strpos($tax, 'pa_') === 0 ){
		// 	$tax = substr($tax, 3);
		// }

		return in_array($tax, self::get_public_taxonomies(), true);

	}

	public static function get_term_link($term_id, $tax, $fallback_url = ''){

		// if the Tax is public, can have links
		if( self::taxonomy_is_public( $tax ) ){

			$term_link = get_term_link( $term_id, $tax );

			if( is_wp_error($term_link) ){
				$term_link = '#';
			}

			return $term_link;
		}

		// default URL
		return $fallback_url ? $fallback_url : '#';
	}

}
