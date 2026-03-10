<?php
namespace ReyCore\WooCommerce\PdpComponents;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class FixedSummary extends Component {

	public function init(){

		if( ! $this->get_status() ){
			return;
		}

		add_filter( 'rey/main_script_params', [ $this, 'script_params'], 10 );
		add_action( 'reycore/frontend/wp_head', [ $this, 'body_classes'], 20 );
		add_action( 'reycore/woocommerce/before_single_product_summary', [ $this, 'load_scripts']);
	}

	public function get_id(){
		return 'fixed_summary';
	}

	public function get_name(){
		return 'Fixed Summary';
	}

	public function get_status(){
		return get_theme_mod('product_page_summary_fixed', false);
	}

	function load_scripts(){
		reycore_assets()->add_scripts(['reycore-sticky', 'reycore-wc-product-page-fixed-summary']);
	}

	function css_first(){
		// return false;
		return apply_filters('reycore/woocommerce/product_page/fixed/css_first', true);
	}

	/**
	 * Filter product page's css classes
	 * @since 1.0.0
	 */
	function body_classes($frontend)
	{

		if( ! is_product() ){
			return;
		}

		if( ! $this->get_status() ){
			return;
		}

		reycore_assets()->add_styles('rey-wc-product-fixed-summary');

		$classes['fixed_summary'] = '--fixed-summary';

		if( $this->css_first() ){
			$classes['fixed_css_first'] = '--fixed-summary-cssfirst';
		}

		if( get_theme_mod('product_page_summary_fixed__gallery', false) && in_array(get_theme_mod('product_gallery_layout', 'vertical'), ['vertical', 'horizontal'], true) ){
			$classes['fixed_summary_gallery'] = '--fixed-gallery';
		}

		if( get_theme_mod('product_page_summary_fixed__offset_active', '') !== ''){
			$classes['fixed_summary_animate'] = '--fixed-summary-anim';
		}

		$frontend->add_body_class($classes);

	}

	/**
	 * Filter main script's params
	 *
	 * @since 1.0.0
	 **/
	public function script_params($params)
	{
		$params['fixed_summary'] = [
			'css_first'            => $this->css_first(),
			'enabled'              => $this->get_status(),
			'offset'               => get_theme_mod('product_page_summary_fixed__offset', ''),
			'offset_bottom'        => 0,
			'use_container_height' => true,
			'gallery'              => get_theme_mod('product_page_summary_fixed__gallery', false),
			'refresh_fixed_header' => false,
		];

		return $params;
	}

}
