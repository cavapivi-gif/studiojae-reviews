<?php
namespace ReyCore\Compatibility\IconicWooAttributeSwatchesPremium;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{
	public $iconic_was;

	const META_KEY = 'iconic_was_term_meta';

	public $swatches = [
		'color' => 'colour-swatch',
		'image' => 'image-swatch',
	];

	public function __construct()
	{
		reycore__remove_filters_for_anonymous_class('woocommerce_layered_nav_term_html', 'Iconic_WAS_Attributes', 'modify_layered_nav_term_html', 10);
		add_action( 'reycore/ajaxfilters/terms_output/before', [$this, 'before_render_filters_output']);
		add_action( 'reycore/ajaxfilters/terms_output/after', [$this, 'after_render_filters_output']);
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

	function filter_swatch_markup( $html, $term, $type ){

		if( ($taxonomy = $term->taxonomy) && $taxonomy == 'product_cat' ) {
			return $html;
		}

		if( $swatch_tag = $this->basic_markup($term, $type) ){
			$search = '</a>';
			$replace = $swatch_tag . $search;
			return str_replace( $search, $replace, $html );
		}

		return $html;

	}

	function basic_markup($term, $type){

		if( ! isset($term->term_id) ){
			return '';
		}

		if( ! method_exists($this, 'get_style__' . $type) ){
			return '';
		}

		global $iconic_was;

		if( $iconic_was ){
			$meta = $iconic_was->attributes_class()->get_term_meta( $term );
		}
		else {
			$meta = get_term_meta( $term->term_id, self::META_KEY, true );
		}

		if( ! ($style = call_user_func([$this, "get_style__{$type}"], $meta)) ){
			return '';
		}

		return sprintf('<span style="%s" data-slug="%s" title="%s" class="__swatch"></span>', $style, $term->slug, $term->name);
	}


	function get_style__color( $meta ){

		if(
			isset( $meta[$this->swatches['color']] ) &&
			( $swatch_color = $meta[$this->swatches['color']] )
		){
			return sprintf('background: %1$s;', $swatch_color);
		}

		return '';
	}

	function get_style__image( $meta ){

		if(
			isset( $meta[$this->swatches['image']] ) &&
			( $swatch_image = $meta[$this->swatches['image']] )
		){
			return sprintf('background-image: url(%1$s);', wp_get_attachment_url($swatch_image));
		}

		return '';
	}

}
