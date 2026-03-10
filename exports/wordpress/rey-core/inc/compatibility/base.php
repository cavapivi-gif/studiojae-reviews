<?php
namespace ReyCore\Compatibility;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base {

	public function __construct(){

		$this->load_items();

	}

	public function load_items(){

		foreach ( self::get_items_list() as $item => $status ) {

			if( ! $status ){
				continue;
			}

			$file_path = sprintf('%1$sinc/compatibility/%2$s/base.php', REY_CORE_DIR, $item);

			if ( ! is_readable( $file_path ) ) {
				continue;
			}

			require $file_path;

			// Normalize class name
			$class_name = ucwords( str_replace( '-', ' ', $item ) );
			$class_name = str_replace( ' ', '', $class_name );
			$class_name = \ReyCore\Helper::fix_class_name($class_name, 'Compatibility', 'Base');

			new $class_name( $status );

		}

	}

	public function get_items_list(){

		$list = [
			'autoptimize' => function_exists( 'autoptimize_autoload' ),
			'cf7' => class_exists('\WPCF7'),
			'dokan' => class_exists( '\WeDevs_Dokan' ),
			'elementor-pro' => defined( 'ELEMENTOR_PRO_VERSION' ),
			'facetwp' => class_exists('\FacetWP'),
			'gtranslate' => class_exists('\GTranslate'),
			'header-footer-elementor' => class_exists('\Header_Footer_Elementor'),
			'indeed-affiliate-pro' => class_exists( '\UAP_Main' ),
			'litespeed' => function_exists( 'run_litespeed_cache' ),
			'mailchimp-for-wp' => function_exists('mc4wp_get_forms'),
			'nextend-social-login' => class_exists( '\NextendSocialLogin' ),
			'optimole' => function_exists( 'optml' ),
			'polylang' => class_exists( '\Polylang' ),
			'perfmatters' => defined( 'PERFMATTERS_VERSION' ),
			'page-optimize' => defined( 'PAGE_OPTIMIZE_ABSPATH' ),
			'pitchprint' => class_exists( '\PitchPrint' ),
			'qtranslatex' => class_exists( '\QTX_Translator' ),
			'relevanssi' => function_exists('relevanssi_do_query'),
			'translatepress' => function_exists( 'trp_enable_translatepress' ),
			'uni-cpo' => class_exists('\Uni_Cpo'),
			'viewer3d' => defined('BP3D_VIEWER_PLUGIN_DIR'),
			'wc-frontend-manager-elementor' => class_exists('\WCFM_Elementor'),
			'wp-rocket' => defined('WP_ROCKET_VERSION'),
			'wp-store-locator' => class_exists( '\WP_Store_locator' ),
			'wp-hide' => defined('WPH_URL') && defined('WPH_PATH'),
			'wpforms' => function_exists('wpforms'),
			'wpml' => class_exists( '\SitePress' ),
			'zoom-instagram' => class_exists('\Wpzoom_Instagram_Widget_API'),
			'misc' => true,
		];


		if( class_exists('\WooCommerce') ){

			$woo_list = [
				'ti-wishlist' => class_exists('\TInvWL_Public_AddToWishlist'),
				'advanced-woo-search' => class_exists( '\AWS_Main' ),
				'advanced-product-fields-for-woocommerce-extended' => class_exists( '\SW_WAPF_PRO\WAPF' ),
				'ajax-search-for-woocommerce' => class_exists( '\DGWT_WC_Ajax_Search' ),
				'berocket-woocommerce-filters' => defined('BeRocket_AJAX_filters_version'),
				'checkout-wc' => defined('CFW_VERSION'),
				'fluid-checkout' => class_exists('\FluidCheckout'),
				'filter-everything' => class_exists('\FlrtFilter'),
				'free-gifts-for-woocommerce' => class_exists('\FP_Free_Gift'),
				'german-market' => class_exists('\Woocommerce_German_Market'),
				'iconic-woo-attribute-swatches-premium' => class_exists('\Iconic_Woo_Attribute_Swatches'),
				'iconic-woo-show-single-variations' => class_exists('\Iconic_WSSV'),
				'jet-smart-filter' => class_exists('\Jet_Smart_Filters'),
				'lumise-product-designer' => class_exists('\lumise_woocommerce'),
				'login-recaptcha' => class_exists('\LoginNocaptcha'),
				'premmerce-product-bundles' => class_exists('\Premmerce\ProductBundles\ProductBundlesPlugin'),
				'perfect-woocommerce-brands' => defined( 'PWB_PLUGIN_VERSION' ),
				'points-and-rewards-for-woocommerce' => defined('REWARDEEM_WOOCOMMERCE_POINTS_REWARDS_VERSION'),
				'pw-gift-card' => class_exists('\PW_Gift_Cards'),
				'wc-ajax-product-filter' => class_exists('\WCAPF'),
				'wc-kalkulator' => class_exists('\WCKalkulator\Plugin'),
				'wc-vendors-pro' => defined( 'WCV_PRO_VERSION' ),
				'wc-multivendor-marketplace' => class_exists('WCFMmp'),
				'woo-advanced-product-size-chart' => ( class_exists('\Size_Chart_For_Woocommerce') || class_exists('\SCFW_Size_Chart_For_Woocommerce') ),
				'woo-discount-rules-pro' => (defined('WDR_PRO_VERSION') || defined('WDR_VERSION')),
				'woo-variation-swatches' => class_exists('\Woo_Variation_Swatches'),
				'woocommerce-brands' => class_exists( '\WC_Brands' ),
				'woocommerce-custom-fields' => defined('WCCF_VERSION'),
				'woocommerce-gift-cards' => class_exists('\WC_Gift_Cards'),
				'woocommerce-germanized' => class_exists('\WooCommerce_Germanized'),
				'woocommerce-measurement-price-calculator' => class_exists( '\WC_Measurement_Price_Calculator' ),
				'woocommerce-photo-reviews' => defined( 'VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION' ),
				'woocommerce-product-bundles' => class_exists( '\WC_Bundles' ),
				'woocommerce-product-table' => function_exists('wc_product_table'),
				'woocommerce-single-variations' => class_exists('\WooCommerce_Single_Variations'),
				'woocommerce-social-login' => class_exists('\WC_Social_Login_Loader'),
				'woocommerce-tab-manager' => class_exists( '\WC_Tab_Manager_Loader' ),
				'woocommerce-tm-extra-product-options' => class_exists( '\Themecomplete_Extra_Product_Options_Setup' ),
				'yith-compare' => defined( 'YITH_WOOCOMPARE' ),
				'yith-woocommerce-ajax-product-filter-premium' => defined( 'YITH_WCAN' ),
				'yith-woocommerce-gift-cards' => class_exists('\YITH_WooCommerce_Gift_Cards'),
				'yith-woocommerce-name-your-price' => function_exists('yith_name_your_price_init'),
				'yith-woocommerce-product-add-ons' => (class_exists('\YITH_WCCL_Frontend') || defined('YITH_WAPO')),
				'yith-woocommerce-product-bundles' => defined( 'YITH_WCPB_VERSION' ),
				'yith-woocommerce-request-a-quote' => defined( 'YITH_YWRAQ_VERSION' ),
			];

			// Currency check
			foreach ([
				'\WOOCS_STARTER' => 'get_woocs_data',
				'\WOOMULTI_CURRENCY_F' => 'get_woomulticurrency_data',
				'\WOOMULTI_CURRENCY' => 'get_woomulticurrency_premium_data',
				'\WC_Aelia_CurrencySwitcher' => 'get_aelia_data',
				'\woocommerce_wpml' => 'get_wcml_data',
			] as $class => $function) {
				if( class_exists( $class ) ){
					$woo_list['woocommerce-currency-switcher'] = $function;
				}
			}

			$list = array_merge($list, $woo_list);
		}

		return $list;

	}

}
