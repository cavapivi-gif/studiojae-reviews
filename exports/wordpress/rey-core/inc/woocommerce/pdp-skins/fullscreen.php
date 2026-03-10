<?php
namespace ReyCore\WooCommerce\PdpSkins;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use ReyCore\WooCommerce\Pdp as Base;
use ReyCore\Plugin;

class Fullscreen extends Skin
{
	public function get_id(){
		return 'fullscreen';
	}

	public function get_name(){
		return esc_html__('Full-screen Summary', 'rey-core');
	}

	public function init(){

		remove_action( 'woocommerce_before_single_product', 'woocommerce_output_all_notices', 10 ); // move notices
		add_action( 'woocommerce_single_product_summary', 'woocommerce_output_all_notices', 1 );

		add_filter( 'rey/header/header_classes', [$this, 'header_classes'], 20 );
		add_filter( 'reycore/header_helper/overlap_classes', [$this, 'header_overlapping_helper'], 100);
		add_filter( 'reycore/woocommerce/short_desc/can_reposition', '__return_true' );

		$priority = 3;

		if( Base::breadcrumb_enabled() ){
			add_action( 'woocommerce_single_product_summary', 'reycore__woocommerce_breadcrumb', $priority );
		}

		add_action( 'woocommerce_single_product_summary', [ $this, 'get_navigation' ], $priority); // right after summary begins
	}

	public function register_scripts( $assets ){

		$rtl = $assets::rtl();

		$assets->register_asset('styles', [
			$this->get_asset_key() => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-product/skin-fullscreen/style' . $rtl . '.css',
				'deps'    => ['woocommerce-general', 'rey-wc-product-lite', 'rey-wc-product'],
				'version'   => REY_CORE_VERSION,
			]
		]);

	}

	public function get_styles(){
		return $this->get_asset_key();
	}

	public function header_overlapping_helper( $classes ){

		if( ! is_product()){
			return $classes;
		}

		if( ! apply_filters('reycore/woocommerce/pdp/fullscreen/header_overlap_desktop', true) ){
			return $classes;
		}

		$classes['desktop'] = '--dnone-lg';
		$classes['tablet'] = '';
		$classes['mobile'] = '';

		return $classes;
	}

	public function product_page_classes()
	{
		$classes = [];

		// make sure it's a product page
		if( get_theme_mod('single_skin_default_flip', false) ){
			$classes['reversed'] = '--reversed';
		}

		if(
			get_theme_mod('single_skin_fullscreen_stretch_gallery', false) &&
			get_theme_mod('product_gallery_layout', 'vertical') === 'cascade' ){
			$classes['stretch-gallery'] = '--fullscreen-stretch-gallery';
		}

		if(
			get_theme_mod('single_skin_fullscreen_custom_height', false) &&
			( get_theme_mod('product_gallery_layout', 'vertical') === 'vertical' ||
			get_theme_mod('product_gallery_layout', 'vertical') === 'horizontal' )
		){
			$classes['fs_custom_height'] = '--fs-custom-height';
		}

		return $classes;
	}

	public function header_classes($classes){

		if( 'header-pos--rel' === $classes['position'] && get_theme_mod('single_skin_fullscreen__header_rel_abs', true) ){
			$classes['header_rel_abs'] = '--fullscreen-header-rel-abs';
		}

		return $classes;
	}

	public function get_navigation(){
		if( $c = Plugin::instance()->woocommerce_pdp->get_component('product_nav') ){
			$c->render();
		}
	}

}
