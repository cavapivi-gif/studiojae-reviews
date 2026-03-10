<?php
namespace ReyCore\WooCommerce;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Assets {

	public function __construct(){
		add_action( 'reycore/woocommerce/init', [ $this, 'init']);
		add_action( 'reycore/customizer/section=general-performance/marker=performance_toggles', [$this, 'add_defer_js_control']);
	}

	/**
	 * General actions
	 * @since 1.0.0
	 **/
	public function init()
	{
		add_filter( 'woocommerce_enqueue_styles', [ $this, 'enqueue_styles'], 10000 ); // suprases Cartflows override
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts'] );
		add_action( 'wp_enqueue_scripts', [$this, 'relocate_scripts'], 999 );
		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
		add_filter( 'rey/css_styles', [ $this, 'css_styles'] );
		add_filter( 'woocommerce_product_get_rating_html', [$this, 'include_loop_rating_styles']);
		add_action( 'reycore/woocommerce/pdp', [$this, 'include_pdp_rating_styles']);
		add_filter( 'woocommerce_get_asset_url', [$this, 'photoswipe_styles'], 10, 2 );
	}

	/**
	 * Enqueue CSS for this theme.
	 *
	 * @param  array $styles Array of registered styles.
	 * @return array
	 */
	public function enqueue_styles( $styles )
	{
		// Override WooCommerce general styles
		$styles['woocommerce-general'] = [
			'src'     => REY_CORE_URI . 'assets/css/woocommerce.css',
			'deps'    => '',
			'version' => REY_CORE_VERSION,
			'media'   => 'all',
			'has_rtl' => true,
		];

		// disable smallscreen stylesheet
		if( isset($styles['woocommerce-smallscreen']) ){
			unset( $styles['woocommerce-smallscreen'] );
		}

		// disable layout stylesheet
		if( isset($styles['woocommerce-layout']) ){
			unset( $styles['woocommerce-layout'] );
		}

		return $styles;
	}

	function woocommerce_styles(){

		$rtl = reycore_assets()::rtl();
		$is_catalog = apply_filters('reycore/woocommerce/css_is_catalog', is_shop() || is_product_taxonomy() || is_product_category() || is_product_tag() );

		return [

			'rey-wc-notices' => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/general/notices.css',
				'deps'    => ['woocommerce-general'],
				'version'   => REY_CORE_VERSION,
				'callback' => function(){
					return is_woocommerce() || is_cart() || is_checkout() || is_account_page();
				},
				'priority' => 'low',
			],

			'rey-wc-store-notice' => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/general/store-notice.css',
				'deps'    => ['woocommerce-general'],
				'version'   => REY_CORE_VERSION,
				'callback' => 'is_store_notice_showing',
			],

			'rey-wc-pass-meter' => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/general/pass-meter.css',
				'deps'    => ['woocommerce-general'],
				'version'   => REY_CORE_VERSION,
				'callback' => function(){
					return ( 'no' === get_option( 'woocommerce_registration_generate_password' ) && ! is_user_logged_in() ) || is_edit_account_page() || is_lost_password_page();
				},
				'priority' => 'low',
			],

			'rey-wc-general' => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/general/general' . $rtl . '.css',
				'deps'    => ['woocommerce-general', 'rey-buttons'],
				'version'   => REY_CORE_VERSION,
				'callback' => 'is_woocommerce',
			],
				'rey-wc-general-deferred' => [
					'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/general/general-deferred.css',
					'deps'    => [],
					'version'   => REY_CORE_VERSION,
					'callback' => 'is_woocommerce',
					'priority' => 'low',
				],
				'rey-wc-general-deferred-font' => [
					'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/general/general-deferred-font.css',
					'deps'    => [],
					'version'   => REY_CORE_VERSION,
					'callback' => function(){
						return apply_filters('reycore/woocommerce/load_woo_font_file', is_woocommerce() || is_cart() || is_checkout() || is_account_page());
					},
					'priority' => 'low',
				],

			'rey-wc-forms' => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/tag-forms/woo-forms' . $rtl . '.css',
				'deps'    => ['woocommerce-general'],
				'version'   => REY_CORE_VERSION,
				'callback' => function(){
					return is_cart() || is_checkout() || is_account_page();
				},
			],

			'rey-wc-cca' => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/general/cca.css',
				'deps'    => ['woocommerce-general'],
				'version'   => REY_CORE_VERSION,
				'callback' => function(){
					return is_cart() || is_checkout() || is_account_page();
				},
			],

			'rey-wc-loop' => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-loop/general/style' . $rtl . '.css',
				'deps'    => ['woocommerce-general', 'rey-wc-general' ,'rey-wc-general-deferred'],
				'version'   => REY_CORE_VERSION,
				'callback' => function() use ($is_catalog){
					return $is_catalog;
				},
				'priority' => 'low',
			],
				'rey-wc-loop-header-lite' => [
					'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-loop/header/header-lite.css',
					'deps'    => [],
					'version'   => REY_CORE_VERSION,
					'callback' => function() use ($is_catalog){
						return $is_catalog;
					},
				],
				'rey-wc-loop-header' => [
					'src'      => REY_CORE_URI . 'assets/css/woocommerce-components/page-loop/header/style' . $rtl . '.css',
					'deps'     => ['woocommerce-general', 'rey-wc-general' ,'rey-wc-general-deferred', 'rey-wc-loop-header-lite'],
					'version'  => REY_CORE_VERSION,
					'callback' => function() use ($is_catalog){
						return $is_catalog;
					},
					'priority' => 'low',
				],
				'rey-wc-loop-inlinelist' => [
					'src'      => REY_CORE_URI . 'assets/css/woocommerce-components/page-loop/header/inline-list.css',
					'deps'     => ['woocommerce-general', 'rey-wc-general' ,'rey-wc-general-deferred'],
					'version'  => REY_CORE_VERSION,
					'priority' => 'low',
				],
				'rey-wc-loop-slideshow' => [
					'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-loop/general/loop-slideshow.css',
					'deps'    => ['woocommerce-general', 'rey-wc-general' ,'rey-wc-general-deferred'],
					'version'   => REY_CORE_VERSION,
					'priority' => 'low',
				],
				'rey-wc-loop-qty' => [
					'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-loop/general/loop-qty.css',
					'deps'    => ['woocommerce-general', 'rey-wc-general' ,'rey-wc-general-deferred'],
					'version'   => REY_CORE_VERSION,
					'priority' => 'low',
				],
				'rey-wc-loop-grid-skin-metro' => [
					'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-loop/grid-skin-metro/style' . $rtl . '.css',
					'deps'    => ['woocommerce-general', 'rey-wc-loop'],
					'version'   => REY_CORE_VERSION,
					'priority' => 'low',
				],
				'rey-wc-loop-grid-skin-masonry' => [
					'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-loop/grid-skin-masonry/style' . $rtl . '.css',
					'deps'    => ['woocommerce-general', 'rey-wc-loop'],
					'version'   => REY_CORE_VERSION,
					'priority' => 'low',
				],
				'rey-wc-loop-grid-skin-masonry2' => [
					'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-loop/grid-skin-masonry2/style' . $rtl . '.css',
					'deps'    => ['woocommerce-general', 'rey-wc-loop'],
					'version'   => REY_CORE_VERSION,
					'priority' => 'low',
				],
				'rey-wc-loop-grid-skin-scattered' => [
					'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-loop/grid-skin-scattered/style' . $rtl . '.css',
					'deps'    => ['woocommerce-general', 'rey-wc-loop'],
					'version'   => REY_CORE_VERSION,
					'priority' => 'low',
				],
				'rey-wc-loop-grid-mobile-list-view' => [
					'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-loop/grid-mobile-list-view/style' . $rtl . '.css',
					'deps'    => ['woocommerce-general', 'rey-wc-loop'],
					'version'   => REY_CORE_VERSION,
					'priority' => 'low',
				],
				'reycore-loop-product-skin-basic' => [
					'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-loop/item-skin-basic/style' . $rtl . '.css',
					'deps'    => ['woocommerce-general', 'rey-wc-loop'],
					'version'   => REY_CORE_VERSION,
					'priority' => 'low',
				],
				'reycore-loop-product-skin-wrapped' => [
					'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-loop/item-skin-wrapped/style' . $rtl . '.css',
					'deps'    => ['woocommerce-general', 'rey-wc-loop'],
					'version'   => REY_CORE_VERSION,
					'priority' => 'low',
				],
				'reycore-loop-titles-height' => [
					'src'      => REY_CORE_URI . 'assets/css/woocommerce-components/page-loop/general/titles-height.css',
					'deps'     => [],
					'version'  => REY_CORE_VERSION,
					'priority' => 'low',
				],
				'rey-wc-product-lite' => [
					'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-product/general/pdp-lite.css',
					'deps'    => ['woocommerce-general', 'rey-wc-general' ,'rey-wc-general-deferred'],
					'version'   => REY_CORE_VERSION,
				],
				'rey-wc-product' => [
					'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-product/general/pdp' . $rtl . '.css',
					'deps'    => ['woocommerce-general', 'rey-wc-general' ,'rey-wc-general-deferred', 'rey-wc-product-lite'],
					'version'   => REY_CORE_VERSION,
					'priority' => 'mid',
				],
				'rey-wc-product-gallery' => [
					'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-product/gallery/gallery.css',
					'deps'    => ['woocommerce-general', 'rey-wc-product'],
					'version'   => REY_CORE_VERSION,
					// 'priority' => 'mid',
				],
				'rey-wc-product-gallery-horizontal' => [
					'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-product/gallery/g-horizontal.css',
					'deps'    => ['woocommerce-general', 'rey-wc-product'],
					'version'   => REY_CORE_VERSION,
					// 'priority' => 'mid',
				],
				'rey-wc-product-gallery-vertical' => [
					'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-product/gallery/g-vertical.css',
					'deps'    => ['woocommerce-general', 'rey-wc-product'],
					'version'   => REY_CORE_VERSION,
					// 'priority' => 'mid',
				],
				'rey-wc-product-gallery-grid' => [
					'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-product/gallery/g-grid.css',
					'deps'    => ['woocommerce-general', 'rey-wc-product'],
					'version'   => REY_CORE_VERSION,
					// 'priority' => 'mid',
				],
				'rey-wc-product-gallery-grid-pattern' => [
					'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-product/gallery/g-grid-pattern.css',
					'deps'    => ['woocommerce-general', 'rey-wc-product'],
					'version'   => REY_CORE_VERSION,
					// 'priority' => 'mid',
				],
				'rey-wc-product-gallery-cascade-grid' => [
					'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-product/gallery/g-cascade-grid.css',
					'deps'    => ['woocommerce-general', 'rey-wc-product'],
					'version'   => REY_CORE_VERSION,
					// 'priority' => 'mid',
				],
				'rey-wc-product-gallery-cascade' => [
					'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-product/gallery/g-cascade.css',
					'deps'    => ['woocommerce-general', 'rey-wc-product'],
					'version'   => REY_CORE_VERSION,
					// 'priority' => 'mid',
				],
				'rey-wc-product-gallery-cascade-scattered' => [
					'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-product/gallery/g-cascade-scattered.css',
					'deps'    => ['woocommerce-general', 'rey-wc-product'],
					'version'   => REY_CORE_VERSION,
					// 'priority' => 'mid',
				],

				'rey-wc-product-reviews' => [
					'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-product/reviews/style' . $rtl . '.css',
					'deps'    => ['woocommerce-general', 'rey-wc-product'],
					'version'   => REY_CORE_VERSION,
					'priority' => 'low'
				],
				'rey-wc-product-fixed-summary' => [
					'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-product/fixed-summary.css',
					'deps'    => ['woocommerce-general', 'rey-wc-product'],
					'version'   => REY_CORE_VERSION,
					'priority' => 'low'
				],
				'rey-wc-product-grouped' => [
					'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-product/general/pdp-grouped.css',
					'deps'    => ['woocommerce-general', 'rey-wc-product'],
					'version'   => REY_CORE_VERSION,
					'priority' => 'mid',
				],
				'rey-wc-product-nav' => [
					'src'      => REY_CORE_URI . 'assets/css/woocommerce-components/page-product/general/pdp-nav.css',
					'deps'     => ['woocommerce-general', 'rey-wc-product'],
					'version'  => REY_CORE_VERSION,
					'priority' => 'low',
				],
				'rey-wc-product-share' => [
					'src'      => REY_CORE_URI . 'assets/css/woocommerce-components/page-product/general/pdp-share.css',
					'deps'     => ['woocommerce-general', 'rey-wc-product'],
					'version'  => REY_CORE_VERSION,
					'priority' => 'low',
				],
				'rey-wc-product-tabs' => [
					'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-product/general/pdp-tabs.css',
					'deps'    => ['woocommerce-general', 'rey-wc-product'],
					'version'   => REY_CORE_VERSION,
					'priority' => 'low'
				],
				'rey-wc-product-blocks' => [
					'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-product/general/pdp-blocks.css',
					'deps'    => ['woocommerce-general', 'rey-wc-product'],
					'version'   => REY_CORE_VERSION,
					'priority' => 'low'
				],

				'rey-wc-product-linked' => [
					'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-product/general/pdp-linked.css',
					'deps'    => ['woocommerce-general', 'rey-wc-product'],
					'version'   => REY_CORE_VERSION,
					'priority' => 'low'
				],


			'rey-wc-cart' => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-cart/style' . $rtl . '.css',
				'deps'    => ['woocommerce-general', 'rey-wc-general' ,'rey-wc-general-deferred'],
				'version'   => REY_CORE_VERSION,
				'callback' => function(){
					return is_cart() || is_checkout();
				},
			],
			'rey-wc-checkout' => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-checkout/style' . $rtl . '.css',
				'deps'    => ['woocommerce-general', 'rey-wc-general' ,'rey-wc-general-deferred', 'rey-wc-cart'],
				'version'   => REY_CORE_VERSION,
				'callback' => function(){
					return is_cart() || is_checkout();
				},
			],
			'rey-wc-myaccount' => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-myaccount/style' . $rtl . '.css',
				'deps'    => ['woocommerce-general', 'rey-wc-general' ,'rey-wc-general-deferred', 'rey-wc-cart'],
				'version'   => REY_CORE_VERSION,
				'callback' => 'is_account_page',
			],
			'rey-wc-elementor' => [
				'src'      => REY_CORE_URI . 'assets/css/elementor-components/woocommerce/woocommerce' . $rtl . '.css',
				'deps'     => ['woocommerce-general'],
				'version'  => REY_CORE_VERSION,
				'priority' => 'low',
			],

			'rey-wc-widgets-filter-button' => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/tag-widgets/filter-button.css',
				'deps'    => ['woocommerce-general', 'rey-wc-loop'],
				'version'   => REY_CORE_VERSION,
				'priority' => 'low'
			],

			'rey-wc-widgets-mobile-panel' => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/tag-widgets/mobile-panel.css',
				'deps'    => ['woocommerce-general', 'rey-wc-loop'],
				'version'   => REY_CORE_VERSION,
				'priority' => 'high',
			],

			'rey-wc-widgets-sidebar-filter-panel' => [
				'src'      => REY_CORE_URI . 'assets/css/woocommerce-components/tag-widgets/sidebar-filter-panel.css',
				'deps'     => ['woocommerce-general', 'rey-wc-loop'],
				'version'  => REY_CORE_VERSION,
				'priority' => 'low',
			],

			'rey-wc-widgets-sidebar-filter-top' => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/tag-widgets/sidebar-filter-top.css',
				'deps'    => ['woocommerce-general', 'rey-wc-loop'],
				'version'   => REY_CORE_VERSION,
				'priority' => 'mid',
			],

			'rey-wc-widgets-sidebar-shop' => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/tag-widgets/sidebar-shop.css',
				'deps'    => ['woocommerce-general', 'rey-wc-loop'],
				'version'   => REY_CORE_VERSION,
				'priority' => 'mid',
			],

			'rey-wc-widgets-toggable-widgets' => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/tag-widgets/toggable-widgets.css',
				'deps'    => ['woocommerce-general', 'rey-wc-loop'],
				'version'   => REY_CORE_VERSION,
				'priority' => 'mid',
			],

			'rey-wc-widgets-classic' => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/tag-widgets/widgets-classic.css',
				'deps'    => ['woocommerce-general', 'rey-wc-loop'],
				'version'   => REY_CORE_VERSION,
				'priority' => 'mid',
			],

			'rey-wc-widgets-titles' => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/tag-widgets/widgets-titles.css',
				'deps'    => ['woocommerce-general', 'rey-wc-loop'],
				'version'   => REY_CORE_VERSION,
				'priority' => 'mid',
			],

			'rey-wc-tag-attributes' => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/tag-widgets/attributes' . $rtl . '.css',
				'deps'    => ['woocommerce-general'],
				'version'   => REY_CORE_VERSION,
				'priority' => 'mid',
			],

			'rey-wc-tag-stretch' => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/tag-stretch/tag-stretch' . $rtl . '.css',
				'deps'    => ['woocommerce-general', 'rey-wc-loop'],
				'version'   => REY_CORE_VERSION,
				'callback' => function() use ($is_catalog){
					return $is_catalog;
				},
				'priority' => 'low',
			],

			'rey-wc-header-account-panel-top' => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/header-account-panel-top/header-account-panel-top' . $rtl . '.css',
				'deps'    => ['woocommerce-general'],
				'version'   => REY_CORE_VERSION,
				'priority' => 'low'
			],

			'rey-wc-header-account-panel' => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/header-account-panel/header-account-panel' . $rtl . '.css',
				'deps'    => ['woocommerce-general'],
				'version'   => REY_CORE_VERSION,
				'priority' => 'low'
			],
			'rey-wc-header-mini-cart-top' => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/header-mini-cart-top/header-minicart-top.css',
				'deps'    => ['woocommerce-general'],
				'version'   => REY_CORE_VERSION,
				'priority' => 'low'
			],
			'rey-wc-header-mini-cart' => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/header-mini-cart/header-mini-cart' . $rtl . '.css',
				'deps'    => ['woocommerce-general'],
				'version'   => REY_CORE_VERSION,
				'priority' => 'low'
			],
			'rey-wc-header-wishlist-element' => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/header-wishlist/wishlist-element.css',
				'deps'    => ['woocommerce-general'],
				'version'   => REY_CORE_VERSION,
				'priority' => 'low'
			],
			'rey-wc-header-wishlist' => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/header-wishlist/header-wishlist' . $rtl . '.css',
				'deps'    => ['woocommerce-general'],
				'version'   => REY_CORE_VERSION,
				'priority' => 'low'
			],
			'rey-wc-star-rating' => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/general/star-rating.css',
				'deps'    => ['woocommerce-general'],
				'version'   => REY_CORE_VERSION,
				'priority' => 'mid'
			],
			'rey-wc-checkbox-label' => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/tag-forms/checkbox-label.css',
				'deps'    => ['woocommerce-general'],
				'version'   => REY_CORE_VERSION,
				'priority' => 'low'
			],
			'rey-wc-select2' => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/tag-forms/select2.css',
				'deps'    => ['woocommerce-general'],
				'version'   => REY_CORE_VERSION,
				'priority' => 'low'
			],
			'rey-wc-coupon-toggle' => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/general/coupon-toggle.css',
				'deps'    => ['woocommerce-general'],
				'version'   => REY_CORE_VERSION,
				'priority' => 'low'
			],

		];

	}

	function woocommerce_scripts(){

		$is_catalog = is_shop() || is_product_taxonomy() || is_product_category() || is_product_tag();

		return [

			'reycore-woocommerce' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/general.js',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
				'callback' => 'is_woocommerce'
			],

			'reycore-wc-cart' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/cart.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
				'callback' => 'is_cart'
			],

			'reycore-wc-checkout-classic' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/checkout-classic.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
				'callback' => 'is_checkout'
			],
			'reycore-wc-header-account-forms' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/header-account-forms.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-wc-header-account-panel' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/header-account-panel.js',
				'deps'    => ['rey-drop-panel', 'reycore-woocommerce', 'reycore-sidepanel'],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-wc-header-wishlist' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/header-wishlist.js',
				'deps'    => ['reycore-woocommerce', 'rey-tmpl'],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-wc-header-ajax-search' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/header-ajax-search.js',
				'deps'    => ['reycore-woocommerce', 'rey-tmpl'],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-wc-header-minicart' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/header-minicart.js',
				'deps'    => ['reycore-woocommerce', 'reycore-sidepanel'],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-wc-loop-count-loadmore' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/loop-count-loadmore.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-wc-loop-equalize' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/loop-equalize.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-wc-loop-filter-count' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/loop-filter-count.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-wc-loop-filter-panel' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/loop-filter-panel.js',
				'deps'    => ['reycore-woocommerce', 'reycore-sidepanel'],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-wc-loop-grids' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/loop-grids.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
				'callback' => function() use ($is_catalog){
					return $is_catalog;
				},
			],
			'reycore-wc-loop-slideshows' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/loop-slideshows.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-wc-loop-stretch' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/loop-stretch.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-wc-loop-toggable-widgets' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/loop-toggable-widgets.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
				'callback' => function() use ($is_catalog){
					return $is_catalog && get_theme_mod('sidebar_shop__toggle__enable', false) && (reycore_wc__check_filter_panel() || reycore_wc__check_filter_sidebar_top() || reycore_wc__check_shop_sidebar());
				},
			],
			'reycore-wc-product-grid-carousels' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/product-grid-carousel.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-wc-product-page-ajax-add-to-cart' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/product-page-ajax-add-to-cart.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-wc-product-page-fixed-summary' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/product-page-fixed-summary.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-wc-product-page-general' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/product-page-general.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
				'callback' => 'is_product'
			],
			'reycore-wc-product-gallery' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/pdp-gallery.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-wc-product-page-mobile-tabs' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/product-page-mobile-tabs.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-wc-product-page-qty-controls' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/product-page-qty-controls.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-wc-product-page-qty-select' => [
				'src'     => REY_CORE_URI . 'assets/js/woocommerce/product-page-qty-select.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
			],

		];
	}

	function register_assets($assets){
		$assets->register_asset('styles', array_merge($this->woocommerce_styles(), self::assets_to_relocate('styles')));
		$assets->register_asset('scripts', array_merge($this->woocommerce_scripts(), self::assets_to_relocate('scripts')));
	}

	public function add_defer_js_control($section){

		if( ! $section ){
			return;
		}

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'perf__js_woo',
			'label'       => esc_html_x( 'WooCommerce JS Defer', 'Customizer control title', 'rey-core' ),
			'help' => [
				esc_html_x( 'This option will defer WooCommerce\'s JavaScript and prevent it from render blocking the page.', 'Customizer control description', 'rey-core')
			],
			'default'     => true,
		] );

	}

	public function defer_woo_scripts()
	{
		if( ! get_theme_mod('perf__js_woo', true) ){
			return;
		}
		if( is_cart() || is_checkout() || is_account_page() ){
			return;
		}
		reycore_assets()->defer_page_scripts([
			'wc-cart-fragments',
			'wc-js-cookie',
			'wc-add-to-cart',
			'wc-add-to-cart-variation',
			'wc-jquery-blockui',
			'wc-single-product',
			'woocommerce',
		]);
	}

	public static function assets_to_relocate( $type = '' ){

		static $assets;

		if( is_null($assets) ){

			$assets['scripts'] = [
				'wc-add-to-cart-variation' => [
					'src'     => REY_CORE_URI . 'assets/js/woocommerce/add-to-cart-variation.js',
					'deps'    => ['jquery', 'rey-tmpl', 'wc-jquery-blockui'],
					'plugin'  => true,
					'version' => REY_CORE_VERSION,
				],
				// replaces Woo's jquery block UI with custom made lighter version
				'wc-jquery-blockui' => [
					'src'     => REY_CORE_URI . 'assets/js/woocommerce/blockui.js',
					'deps'    => ['jquery'],
					'version' => REY_CORE_VERSION,
				],
				'wc-single-product' => [
					'src'     => REY_CORE_URI . 'assets/js/woocommerce/wc-single-product.js',
					'deps'    => ['jquery'],
					'version' => REY_CORE_VERSION,
				],
			];

			$assets['styles'] = [

			];

			$assets = apply_filters('reycore/woocommerce/assets_to_relocate', $assets);

		}

		if( ! empty($type) ){
			return $assets[$type];
		}

		return $assets;
	}

	/**
	 * Override Photoswipe styles
	 *
	 * @param string $abs_path
	 * @param string $path
	 * @return string
	 */
	public function photoswipe_styles( $abs_path, $path ){

		if( 'assets/css/photoswipe/photoswipe.min.css' === $path ){
			$abs_path = REY_CORE_URI . 'assets/css/woocommerce-components/photoswipe/photoswipe.css';
		}

		elseif( 'assets/css/photoswipe/default-skin/default-skin.min.css' === $path ){
			$abs_path = REY_CORE_URI . 'assets/css/woocommerce-components/photoswipe/photoswipe-skin.css';
		}

		return $abs_path;
	}

	/**
	 * Relocate some scripts.
	 *
	 * @return void
	 */
	public function relocate_scripts() {

		global $wp_scripts, $wp_styles;

		foreach (self::assets_to_relocate() as $type => $assets) {

			if( 'scripts' === $type ){
				foreach ($assets as $handle => $data) {
					if( isset( $wp_scripts->registered[ $handle ] ) && $wp_scripts->registered[ $handle ] ) {
						$wp_scripts->registered[ $handle ]->src = $data['src'];
					}
				}
			}

		}
	}

	/**
	 * Enqueue scripts for WooCommerce
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts()
	{
		if( apply_filters('reycore/woocommerce/load_all_styles', false) ){
			foreach( $this->woocommerce_styles() as $handle => $style ){
				reycore_assets()->add_styles($handle);
			}
		}

		// Pass visibility style
		if( is_checkout() || is_page( wc_get_page_id( 'myaccount' ) ) ){
			reycore_assets()->add_styles('reycore-pass-visibility');
		}

		$this->defer_woo_scripts();
	}

	/**
	 * Filter css styles
	 * @since 1.1.2
	 */
	function css_styles($styles)
	{
		$styles[] = sprintf( ':root{ --woocommerce-grid-columns:%d; }', reycore_wc_get_columns('desktop') );
		$styles[] = sprintf( '@media(min-width: 768px) and (max-width: 1024px){:root{ --woocommerce-grid-columns:%d; }}', reycore_wc_get_columns('tablet') );
		$styles[] = sprintf( '@media(max-width: 767px){:root{ --woocommerce-grid-columns:%d; }}', reycore_wc_get_columns('mobile') );
		return $styles;
	}

	public function include_loop_rating_styles($html){
		reycore_assets()->add_styles('rey-wc-star-rating');
		return $html;
	}

	public function include_pdp_rating_styles(){
		reycore_assets()->add_styles('rey-wc-star-rating');
	}

}
