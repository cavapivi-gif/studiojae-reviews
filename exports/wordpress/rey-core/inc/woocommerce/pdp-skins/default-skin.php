<?php
namespace ReyCore\WooCommerce\PdpSkins;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use ReyCore\WooCommerce\Pdp as Base;
use ReyCore\Plugin;

class DefaultSkin extends Skin
{
	public function get_id(){
		return 'default';
	}

	public function get_name(){
		return esc_html__('Default', 'rey-core');
	}

	public function init()
	{
		add_action( 'rey/get_sidebar', [ $this, 'get_product_page_sidebar'] );
		add_action( 'reycore/woocommerce/before_single_product_summary', [$this, 'render_pdp_sidebar_inside']);
		add_filter( 'rey/sidebar_name', [ $this, 'product_page_sidebar'] );
		add_filter( 'rey/content/sidebar_class', [ $this, 'sidebar_classes'], 10 );
		add_filter( 'rey/content/site_main_class', [ $this, 'main_classes'], 10 );
		add_filter( 'theme_mod_single_skin_cascade_bullets', [ $this, 'disable__cascade_bullets'], 90 );
		add_filter( 'reycore/woocommerce/short_desc/can_reposition', '__return_true' );

		$priority = 1;

		// make sure to include breadcrumbs and nav into fixed summary block.
		if( reycore_wc__get_pdp_component('fixed_summary')->get_status() ){
			$priority = 3;
		}

		if( Base::breadcrumb_enabled() ){
			add_action( 'woocommerce_single_product_summary', 'reycore__woocommerce_breadcrumb', $priority );
		}

		add_action( 'woocommerce_single_product_summary', [ $this, 'get_navigation' ], $priority); // right after summary begins
	}

	public function register_scripts( $assets ){

		$rtl = $assets::rtl();

		$assets->register_asset('styles', [
			$this->get_asset_key() => [
				'src'     => REY_CORE_URI . 'assets/css/woocommerce-components/page-product/skin-default/style' . $rtl . '.css',
				'deps'    => ['woocommerce-general', 'rey-wc-product-lite', 'rey-wc-product'],
				'version'   => REY_CORE_VERSION,
			]
		]);

	}

	public function get_styles(){
		return $this->get_asset_key();
	}

	public function product_page_classes()
	{

		$classes = [];

		if( get_theme_mod('single_skin_default_flip', false) == true ){
			$classes['default_flip'] = '--reversed';
		}

		return $classes;
	}


	/**
	 * Show Product page sidebar
	 *
	 * @since 1.6.15
	 */
	public function product_page_sidebar($sidebar)
	{
		if( is_product() ){
			return 'product-page-sidebar';
		}
		return $sidebar;
	}

	/**
	 * Check if sidebar is active
	 * @since 1.6.15
	 */
	public static function is_pdp_sidebar_active(){
		return is_product() &&
		is_active_sidebar('product-page-sidebar') &&
		get_theme_mod('single_skin__default__sidebar', '') !== '';
	}

	public static function is_sidebar_outside(){
		return apply_filters('reycore/woocommerce/pdp/sidebar_outside', true);
	}

	/**
	 * Get Shop Sidebar
	 * @hooks to rey/get_sidebar
	 * @since 1.6.15
	 */
	public function get_product_page_sidebar( $position )
	{
		if(
			self::is_pdp_sidebar_active() &&
			get_theme_mod('single_skin__default__sidebar', '') === $position &&
			self::is_sidebar_outside()
		) {
			get_sidebar('product-page-sidebar');
		}
	}
	public function render_pdp_sidebar_inside()
	{
		if(
			self::is_pdp_sidebar_active() &&
			get_theme_mod('single_skin__default__sidebar', '') !== '' &&
			! self::is_sidebar_outside()
		) {
			get_sidebar('product-page-sidebar');
		}
	}

	/**
	 * Filter main wrapper's css classes
	 *
	 * @since 1.6.15
	 **/
	public function main_classes($classes)
	{
		if( self::is_pdp_sidebar_active() ) {

			if( self::is_sidebar_outside() ){
				$classes['has_sidebar'] = '--has-sidebar';
			}
			else {
				$classes['sidebar_inside'] = '--sidebar-inside';
			}

			if( get_theme_mod('single_skin__default__sidebar_mobile', true) ) {
				$classes['sidebar_hidden_mobile'] = '--sidebar-hidden-mobile';
			}
		}

		return $classes;
	}

	/**
	 * Filter sidebar wrapper's css classes
	 *
	 * @since 1.6.15
	 **/
	public function sidebar_classes($classes)
	{
		if( self::is_pdp_sidebar_active() && get_theme_mod('single_skin__default__sidebar_mobile', true) ) {
			$classes[] = '--sidebar-hidden-mobile';
		}

		return $classes;
	}

	public function disable__cascade_bullets($status){
		if( self::is_pdp_sidebar_active() ) {
			$status = false;
		}
		return $status;
	}

	public function get_navigation(){
		if( $c = Plugin::instance()->woocommerce_pdp->get_component('product_nav') ){
			$c->render();
		}
	}
}
