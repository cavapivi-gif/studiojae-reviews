<?php
namespace ReyCore\WooCommerce\LoopSkins;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Skin
{
	const ASSET_HANDLE = 'reycore-loop-product-skin';

	public function __construct() {
		add_action( 'reycore/customizer/control=loop_skin', [ $this, 'add_customizer_options' ], 10, 2 );
	}

	public function get_id(){
		return '';
	}

	public function get_name(){
		return '';
	}

	public function get_list_option_key(){}

	public function get_asset_key(){
		return self::ASSET_HANDLE . '-' . $this->get_id();
	}

	public function add_hooks(){

		$this->__add_hooks();

		add_filter( 'woocommerce_post_class', [$this,'custom_css_classes'], 20 );
		add_filter( 'product_cat_class', [$this,'custom_css_classes'], 20 );

		do_action( 'reycore/woocommerce/loop/after_skin_init', $this );

	}

	public function remove_hooks(){

		$this->__remove_hooks();

		remove_filter( 'woocommerce_post_class', [$this,'custom_css_classes'], 20 );
		remove_filter( 'product_cat_class', [$this,'custom_css_classes'], 20 );
	}

	protected function __add_hooks(){}

	protected function __remove_hooks(){}

	public function add_scripts(){
		reycore_assets()->add_styles($this->get_asset_key());
	}

	public function get_component_schemes(){
		return [];
	}

	public function get_script_params(){
		return [];
	}

	public function get_default_settings(){
		return [];
	}

	public function add_customizer_options( $control_args, $section ){}

	public function register_scripts($assets){}

	public function skin_classes(){
		return [];
	}

	public function custom_css_classes($classes){

		if( apply_filters('reycore/woocommerce/loop/prevent_custom_css_classes', is_admin() && ! wp_doing_ajax() ) ){
			return $classes;
		}

		if( $skin_classes = $this->skin_classes() ){
			$classes = array_merge($classes, $skin_classes);
		}

		if( ($woo_loop = \ReyCore\Plugin::instance()->woocommerce_loop) && $general_css_classes = $woo_loop->general_css_classes() ){
			$classes = array_merge($classes, $general_css_classes);
		}

		return $classes;

	}

}
