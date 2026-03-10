<?php
namespace ReyCore\Modules;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base {

	public $modules_manager;

	/**
	 * Holds the modules that are registered
	 */
	private $modules = [];

	/**
	 * All modules that are included
	 */
	private $all_modules = [];

	public function __construct(){

		$this->modules_manager = new ModuleManager();

		$this->load_modules();
	}

	public static function default_disabled_modules(){
		return apply_filters('reycore/modules/disabled_defaults', [
			'product-size-guides',
			'product-grid-custom-tax', // rarely used, so better keep it disabled by default
			'gs-visibility',
		] );
	}

	public function module_is_enabled($item_id){
		return ! in_array( $item_id , $this->modules_manager->get_disabled_items(), true );
	}

	public function load_modules(){

		foreach ( $this->get_modules_list() as $module_id => $supported) {

			if( ! $supported ){
				continue;
			}

			// prevent loading if disabled
			if( ! $this->module_is_enabled( $module_id ) ){
				continue;
			}

			/**
			 * Legacy way of disabling modules
			 * @todo remove in 2.5.0
			 */
			if( defined('REY_CORE_MODULES_EXCLUDE') ){

				if ( WP_DEBUG ) {
					trigger_error( '"REY_CORE_MODULES_EXCLUDE" constant is deprecated. Use Module Manager instead.', E_USER_DEPRECATED );
				}

				if( is_array(REY_CORE_MODULES_EXCLUDE) && in_array( $module_id, REY_CORE_MODULES_EXCLUDE, true ) ){
					continue;
				}

			}

			// Normalize class name
			$class_name = ucwords( str_replace( '-', ' ', $module_id ) );
			$class_name = str_replace( ' ', '', $class_name );
			$class_name = \ReyCore\Helper::fix_class_name($class_name, 'Modules', 'Base');

			if( ! class_exists($class_name) ){
				trigger_error('Module classname is incorrect and cannot be loaded.', E_USER_NOTICE);
				continue;
			}

			// assign module
			$this->modules[ $module_id ] = new $class_name();

		}

	}

	public function get_all_modules( $sorted = false ){

		if( $sorted ){
			usort($this->all_modules, function ($a, $b)	{
				return strcmp($a['title'], $b['title']);
			});
		}

		return $this->all_modules;
	}

	public function get_module( $module = '' ){

		if( $module && isset($this->modules[ $module ]) ){
			return $this->modules[ $module ];
		}

	}

	public function get_modules_list(){

		$is_woocommerce = class_exists('\WooCommerce');
		$is_acf = class_exists('\ACF');
		$is_elementor = class_exists('\Elementor\Plugin') && is_callable( '\Elementor\Plugin::instance' );
		$beta = defined('REY_BETA') && REY_BETA;

		return [
			'after-atc-popup'                  => $is_woocommerce,
			'ajax-filters'                     => $is_woocommerce,
			'ajax-variables-popup'             => $is_woocommerce,
			'archive-bottom-desc'              => $is_woocommerce,
			'atc-button-icon'                  => $is_woocommerce,
			'blog-teasers'                     => true,
			'brands'                           => $is_woocommerce,
			'buy-now-button'                   => $is_woocommerce,
			'card-gs'                          => $is_elementor,
			'card-code'                        => $is_elementor,
			'card-loop'                        => $is_elementor,
			'cards'                            => $is_elementor,
			'compare'                          => $is_woocommerce,
			'cookie-notice'                    => true,
			'custom-sidebars'                  => $is_woocommerce,
			'custom-templates'                 => $is_woocommerce && $is_acf && $is_elementor,
			'customizer-search'                => true,
			'discount-badge'                   => $is_woocommerce,
			'dynamic-tags'                     => $is_elementor,
			'elementor-acf'                    => $is_acf && $is_elementor,
			'elementor-animations'             => $is_elementor,
			'elementor-section-modals'         => $is_elementor,
			'elementor-section-tabs'           => $is_elementor,
			'elementor-section-scroll-effects' => $is_elementor,
			'elementor-section-hideondemand'   => $is_elementor,
			'elementor-section-slideshow'      => $is_elementor,
			'elementor-lazy-bg'                => $is_elementor,
			'estimated-delivery'               => $is_woocommerce,
			'extra-variation-images'           => $is_woocommerce,
			'footer-reveal'                    => true,
			'gallery-three-sixty'              => $is_woocommerce,
			'gs-visibility'                    => $is_acf && $is_elementor,
			'header-fixed'                     => true,
			'inline-search'                    => true,
			'loop-single-variations'           => $is_woocommerce && (defined('REY_SSV') && REY_SSV),
			'loop-product-skin-minimal'        => $is_woocommerce,
			'loop-product-skin-aero'           => $is_woocommerce,
			'loop-product-skin-proto'          => $is_woocommerce,
			'loop-product-skin-rigo'           => $is_woocommerce,
			'loop-product-skin-cards'          => $is_woocommerce,
			'loop-product-skin-iconized'       => $is_woocommerce,
			'mega-menus'                       => $is_acf,
			'mini-cart-bubble'                 => $is_woocommerce,
			'mini-cart-carousel'               => $is_woocommerce,
			'mini-cart-extra-products'         => $is_woocommerce,
			'mini-cart-recents'                => $is_woocommerce,
			'mini-cart-shipping-bar'           => $is_woocommerce,
			'mini-cart-shipping-calculator'    => $is_woocommerce,
			'offcanvas-panels'                 => $is_elementor,
			'order-refunds'                    => $is_woocommerce,
			'product-badges'                   => $is_woocommerce,
			'product-quantity'                 => $is_woocommerce && $is_acf,
			'product-before-after'             => $is_woocommerce,
			'product-grid-custom-tax'          => $is_woocommerce,
			// 'product-loop-gs'                  => $is_woocommerce && $beta,
			'product-size-guides'              => $is_woocommerce,
			'product-stretch'                  => $is_woocommerce,
			'product-subtitle'                 => $is_woocommerce,
			'product-video'                    => $is_woocommerce,
			'product-teasers'                  => $is_woocommerce,
			'pdp-benefits'                     => $is_woocommerce,
			'pdp-custom-tabs'                  => $is_woocommerce,
			'pdp-recently-viewed'              => $is_woocommerce,
			'pdp-tabs-accordion'               => $is_woocommerce,
			'price-features'                   => $is_woocommerce,
			'price-in-atc'                     => $is_woocommerce,
			'product-page-after-content'       => $is_woocommerce,
			'products-per-page'                => $is_woocommerce,
			'related-products'                 => $is_woocommerce,
			'store-notice'                     => $is_woocommerce,
			'quickview'                        => $is_woocommerce,
			'request-quote'                    => $is_woocommerce,
			'scheduled-sales'                  => $is_woocommerce,
			'sticky-add-to-cart'               => $is_woocommerce,
			'scroll-to-top'                    => true,
			'variation-swatches'               => $is_woocommerce,
			'view-switcher'                    => $is_woocommerce,
			'wc-product-attributes-widget'     => $is_woocommerce,
			'wishlist'                         => $is_woocommerce,
			'woo-local-pickup'                 => $is_woocommerce,
			'woo-min-order-amount'             => $is_woocommerce,
		];

	}

}
