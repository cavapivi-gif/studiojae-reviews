<?php
namespace ReyCore\Modules\VariationSwatches;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Compatibility
{

	public function __construct()
	{

		add_filter( 'reycore/elementor/wc-attributes/get_term_tag', [$this, 'wc_attributes_element_get_term_tag'], 10, 3 );
		add_action( 'reycore/ajaxfilters/terms_output/before', [$this, 'before_render_filters_output']);
		add_action( 'reycore/ajaxfilters/terms_output/after', [$this, 'after_render_filters_output']);
		add_filter( 'theme_mod_loop_ajax_variable_products', [$this, 'disable_loop_ajax_variable_products'], 20 );
		add_filter( 'reycore/woocommerce/maybe_add_loop_qty', [$this, 'enable_qty_for_variable'], 10, 2);

	}

	function basic_markup($term, $type){

		if( ! isset($term->term_id) ){
			return '';
		}

		if( strpos($type, Base::TYPES_PREFIX) === false ){
			$type = Base::TYPES_PREFIX . $type;
		}

		$instance = Base::instance();

		if( ! $instance->swatch_exists( $type ) ){
			return '';
		}

		$style = $instance->get_swatches($type)->get_swatch_style($term);

		if( ! $style ){
			return '';
		}

		return sprintf('<span style="%s" data-slug="%s" title="%s" class="__swatch"></span>', $style, $term->slug, $term->name);

	}

	function wc_attributes_element_get_term_tag($html, $term, $type){

		if( reycore__is_multilanguage() && isset($term->term_id) ){
			$term = get_term( apply_filters('reycore/translate_ids', $term->term_id, $term->taxonomy), $term->taxonomy);
		}

		if( $basic_markup = $this->basic_markup($term, $type) ){
			return $basic_markup;
		}

		return $html;
	}

	function before_render_filters_output( $display_type ){

		if ($display_type === 'color') {
			add_filter('woocommerce_layered_nav_term_html', [$this, 'filter_color_attr_html'], 10, 2);
		}
		elseif ($display_type === 'color_list') {
			add_filter('woocommerce_layered_nav_term_html', [$this, 'filter_color_list_attr_html'], 10, 2);
		}
		elseif ($display_type === 'image') {
			add_filter('woocommerce_layered_nav_term_html', [$this, 'filter_image_attr_html'], 10, 2);
		}

	}

	function after_render_filters_output(){
		remove_filter('woocommerce_layered_nav_term_html', [$this, 'filter_color_attr_html'], 10, 2);
		remove_filter('woocommerce_layered_nav_term_html', [$this, 'filter_color_list_attr_html'], 10, 2);
		remove_filter('woocommerce_layered_nav_term_html', [$this, 'filter_image_attr_html'], 10, 2);
	}

	function filter_swatch_markup( $html, $term, $type ){

		if( ($taxonomy = $term->taxonomy) && $taxonomy == 'product_cat' ) {
			return $html;
		}

		if( $swatch_tag = $this->basic_markup($term, $type) ){
			$search = '</a>';
			$replace = $swatch_tag . $search;
			$html = str_replace( $search, $replace, $html );
		}

		return $html;

	}

	/**
	 * Override Color Attribute html in filter nav
	 **/
	function filter_color_list_attr_html($term_html, $term) {
		return $this->filter_swatch_markup($term_html, $term, 'color');
	}

	/**
	 * Override Color Attribute html in filter nav
	 **/
	function filter_color_attr_html($term_html, $term) {
		return $this->filter_swatch_markup($term_html, $term, 'color');
	}

	/**
	 * Override Image Attribute html in filter nav
	 **/
	function filter_image_attr_html($term_html, $term)
	{
		return $this->filter_swatch_markup($term_html, $term, 'image');
	}

	function disable_loop_ajax_variable_products($mod){

		if( 'all_attributes' === get_theme_mod('woocommerce_loop_variation', 'disabled') ){
			return false;
		}

		return $mod;
	}

	function enable_qty_for_variable($status, $product){

		if( $product->get_type() === 'variable' && 'all_attributes' === get_theme_mod('woocommerce_loop_variation', 'disabled') ){
			return true;
		}

		return $status;
	}

}
