<?php
namespace ReyCore\Modules\PdpRecentlyViewed;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	const ASSET_HANDLE = 'reycore-module-pdp-recent';

	public function __construct()
	{
		add_action( 'reycore/woocommerce/init', [$this, 'init']);
		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
		add_action( 'reycore/ajax/register_actions', [ $this, 'register_actions' ] );
	}

	public function init() {

		new Customizer();

		if( ! $this->is_enabled() ){
			return;
		}

		add_action( 'woocommerce_after_single_product_summary', [$this, 'add_grid'], 40 );
		add_action( 'woocommerce_after_shop_loop', [$this, 'render_categories'], 1000);
	}

	public function register_actions( $ajax_manager ){
		$ajax_manager->register_ajax_action( 'pdp_recent_products', [$this, 'ajax__get_recents'], [
			'auth'   => 3,
			'nonce'  => false,
			'assets' => true,
		] );
	}

	public function register_assets($assets){

		// $assets->register_asset('styles', [
		// 	self::ASSET_HANDLE => [
		// 		'src'     => self::get_path( basename( __DIR__ ) ) . '/style.css',
		// 		'deps'    => [],
		// 		'version'   => REY_CORE_VERSION,
		// 	]
		// ]);

		$assets->register_asset('scripts', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/script.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
			]
		]);

	}

	public function get_recent_product_ids(){

		$viewed_products = ! empty( $_COOKIE['woocommerce_recently_viewed'] ) ? (array) explode( '|', wp_unslash( $_COOKIE['woocommerce_recently_viewed'] ) ) : [];

		if( empty($viewed_products) ){
			return [];
		}

		return array_slice( array_reverse( array_filter( array_map( 'absint', $viewed_products ) ) ), 0, 10 );
	}

	public function render_categories(){

		if( ! (is_product_category() || is_shop()) ){
			return;
		}

		if( ! get_theme_mod('category__recently_viewed', false) ){
			return;
		}

		$this->add_grid();

	}

	public function add_grid(){

		if( ! $this->get_recent_product_ids() ){
			return;
		}

		reycore_assets()->add_scripts(['splidejs', 'rey-splide', 'reycore-wc-product-grid-carousels', self::ASSET_HANDLE]);
		reycore_assets()->add_styles(['rey-splide', self::ASSET_HANDLE]);

		echo reycore__lazy_placeholders([
			'class'        => 'rey-recentlyViewed rey-extra-products products',
			'filter_title' => 'recent_products',
			'desktop'      => reycore_wc_get_columns('desktop'),
			'tablet'       => reycore_wc_get_columns('tablet'),
			'mobile'       => reycore_wc_get_columns('mobile'),
			'limit'        => reycore_wc_get_columns('desktop'),
			'nowrap'       => true,
		]);

	}

	public function ajax__get_recents( $action_data ){

		if( ! $action_data['id'] ){
			return;
		}

		if( ! ($recent_products = $this->get_recent_product_ids()) ){
			return;
		}

		if( empty($recent_products) ){
			return;
		}

		$current_product_id = absint($action_data['id']) ;

		// must not have the current product
		if( in_array($current_product_id, $recent_products, true) ){
			$recent_products = array_filter($recent_products, function($m) use ($current_product_id) {
				return $m && $m != $current_product_id;
			});
		}

		// recheck because maybe the current product was
		if( empty($recent_products) ){
			return;
		}

		if( reycore__is_multilanguage() ){
			$recent_products = apply_filters('reycore/translate_ids', $recent_products, 'product');
		}

		ob_start();

		$title = ($_title = get_theme_mod('single__recently_viewed_title', '')) ? $_title : esc_html__('RECENTLY VIEWED', 'rey-core');

		if( $title ){
			printf('<section class="rey-recentlyViewed rey-extra-products"><h2>%s</h2>', $title);
		}

			wc_set_loop_prop( 'name', 'recently_viewed' );
			wc_set_loop_prop( 'is_paginated', false );

			add_filter( 'woocommerce_post_class', [$this,'add_product_classes'], 30 );
			add_filter( 'reycore/woocommerce/product_loop_classes', [$this, 'add_grid_classes']);
			add_filter( 'reycore/woocommerce/product_loop_attributes/v2', [$this, 'add_grid_attributes'], 10, 2);
			add_filter( 'reycore/woocommerce/catalog/before_after/enable', '__return_false');
			add_filter( 'reycore/woocommerce/catalog/stretch_product/enable', '__return_false');
			add_filter( 'reycore/woocommerce/loop_components/disable_grid_components', '__return_true');
			add_filter( 'reycore/woocommerce/loop/render/thumbnails_second', 'reycore__elementor_edit_mode__return_false');
			add_filter( 'reycore/woocommerce/loop/render/thumbnails_slideshow', 'reycore__elementor_edit_mode__return_false');
			add_filter( 'reycore/woocommerce/loop/render/thumbnails_slideshow', '__return_false', 100);

			// add carousel wrapper
			printf('<div class="splide __product-carousel" data-skin="%s"><div class="splide__track">', esc_attr(get_theme_mod('loop_skin', 'basic')));

			woocommerce_product_loop_start();

				foreach ( $recent_products as $recent_product_id ) :

					if( ! ($post_object = get_post( $recent_product_id )) ){
						continue;
					}

					setup_postdata( $GLOBALS['post'] =& $post_object ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, Squiz.PHP.DisallowMultipleAssignments.Found

					wc_get_template_part( 'content', 'product' );

				endforeach;

			woocommerce_product_loop_end();

			echo '</div></div>';

			remove_filter( 'reycore/woocommerce/product_loop_classes', [$this, 'add_grid_classes']);
			remove_filter( 'reycore/woocommerce/product_loop_attributes/v2', [$this, 'add_grid_attributes'], 10, 2);
			remove_filter( 'reycore/woocommerce/catalog/before_after/enable', '__return_false');
			remove_filter( 'reycore/woocommerce/catalog/stretch_product/enable', '__return_false');
			remove_filter( 'reycore/woocommerce/loop_components/disable_grid_components', '__return_true');
			remove_filter( 'reycore/woocommerce/loop/render/thumbnails_second', 'reycore__elementor_edit_mode__return_false');
			remove_filter( 'reycore/woocommerce/loop/render/thumbnails_slideshow', 'reycore__elementor_edit_mode__return_false');
			remove_filter( 'reycore/woocommerce/loop/render/thumbnails_slideshow', '__return_false', 100);
			remove_filter( 'woocommerce_post_class', [$this,'add_product_classes'], 30 );

		echo '</section>';

		return ob_get_clean();
	}

	public function add_grid_attributes( $attributes, $settings ){

		if( isset($settings['_skin']) && $settings === 'carousel' ){
			return $attributes;
		}

		$params = [
			'autoplay'            => false,
			'interval'            => 6000,
			'per_page'            => reycore_wc_get_columns('desktop'),
			'per_page_tablet'     => 3,
			'per_page_mobile'     => 2,
			'auto_width' => true,
			'desktop_only_arrows' => true,
			'rewind' => true,
		];

		$params = apply_filters('reycore/woocommerce/recent_products/grid_config', $params, $this);

		$attributes['data-prod-carousel-config'] = wp_json_encode($params);

		return $attributes;
	}

	function add_grid_classes($classes){
		return array_merge($classes, [
			'splide__list',
			'--prevent-metro',
			'--prevent-thumbnail-sliders', // make sure it does not have thumbnail slideshow
			'--prevent-scattered', // make sure scattered is not applied
			'--prevent-masonry', // make sure masonry is not applied
		]);
	}

	function add_product_classes($classes){

		$classes['carousel_item'] = 'splide__slide';
		unset($classes['animated-entry']);

		return $classes;
	}

	public function is_enabled() {
		return get_theme_mod('single__recently_viewed', false);
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Recently viewed in Product Page', 'Module name', 'rey-core'),
			'description' => esc_html_x('Shows a list of products which were recently viewed.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => ['product page'],
			'video' => true,
			// 'help'        => reycore__support_url('kb/'),
		];
	}

	public function module_in_use(){
		return $this->is_enabled();
	}

}
