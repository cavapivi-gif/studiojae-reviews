<?php
namespace ReyCore\WooCommerce\PdpSkins;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use ReyCore\WooCommerce\Pdp as Base;
use ReyCore\Plugin;

class Compact extends Skin
{
	public function get_id(){
		return 'compact';
	}

	public function get_name(){
		return esc_html__('Compact Layout', 'rey-core');
	}

	public function init(){

		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );
		add_action( 'woocommerce_after_single_product_summary', 'woocommerce_template_single_sharing', 1 );
		add_filter('reycore/woocommerce/product_page/share/classes', [$this, 'make_sharing_vertical_sticky']);

		// move images after summary
		if( apply_filters('reycore/woocommerce/single_skin/compact/images_after', true) ){
			remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
			add_action( 'woocommerce_after_single_product_summary', 'woocommerce_show_product_images', 0 );
		}

		add_action( 'woocommerce_single_product_summary', [ $this, 'start_left_wrap' ], 2);
		add_action( 'woocommerce_single_product_summary', 'reycore_wc__generic_wrapper_end', 25);
		add_action( 'woocommerce_single_product_summary', [ $this, 'start_right_wrap' ], 25);
		add_action( 'woocommerce_single_product_summary', 'reycore_wc__generic_wrapper_end', 100);

		add_action( 'woocommerce_single_product_summary', [ $this, 'get_navigation' ], 1);

		$this->move_product_meta();

		if( Base::breadcrumb_enabled() ){
			add_action( 'woocommerce_single_product_summary', 'reycore__woocommerce_breadcrumb', 1 );
		}
	}

	public function register_scripts( $assets ){

		$rtl = $assets::rtl();

		$assets->register_asset('styles', [
			$this->get_asset_key() => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-product/skin-compact/style' . $rtl . '.css',
				'deps'    => ['woocommerce-general', 'rey-wc-product-lite', 'rey-wc-product'],
				'version'   => REY_CORE_VERSION,
			]
		]);

	}

	public function get_styles(){
		return $this->get_asset_key();
	}

	public function move_product_meta(){

		if( ! get_theme_mod('single_product_meta_v2', true) ){
			return;
		}

		// move meta to the bottom
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
		add_action( 'woocommerce_after_single_product_summary', 'woocommerce_template_single_meta', 0 );
	}

	/**
	 * Make the sharing buttons vertical
	 *
	 * @since 1.0.0
	 */
	public function make_sharing_vertical_sticky($classes){
		$classes[] = '--vertical';
		$classes[] = '--sticky';
		return $classes;
	}

	/**
	 * Wrap left summary - start
	 *
	 * @since 1.0.0
	 **/
	public function start_left_wrap()
	{ ?>
		<div class="rey-leftSummary"><?php
	}

	/**
	 * Wrap left summary - start
	 *
	 * @since 1.0.0
	 **/
	public function start_right_wrap()
	{ ?>
		<div class="rey-rightSummary"><?php
	}

	public function get_navigation(){
		if( $c = Plugin::instance()->woocommerce_pdp->get_component('product_nav') ){
			$c->render();
		}
	}
}
