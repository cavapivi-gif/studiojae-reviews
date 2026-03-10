<?php
namespace ReyCore\Modules\ProductStretch;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	const ASSET_HANDLE = 'reycore-module-product-stretch';

	public $product_id = [];

	public function __construct()
	{
		add_action( 'reycore/woocommerce/init', [$this, 'init']);
	}

	public function init() {

		if( ! $this->is_enabled() ){
			return;
		}

		new AcfFields();

		add_action( 'reycore/woocommerce/content_product/before', [$this, 'before'], 10);
		add_action( 'reycore/woocommerce/content_product/after', [$this, 'after'], 10);
		add_action( 'woocommerce_after_single_product_summary', [$this, 'prevent_stretch'], 10);
		add_action( 'woocommerce_cart_collaterals', [$this, 'prevent_stretch'], 10);
		add_action( 'reycore/woocommerce/wishlist/render_products', [$this, 'prevent_stretch'], 10);
		add_action( 'reycore/woocommerce/loop/before_grid', [$this, 'load_product_grid_assets']);
	}


	function prevent_stretch(){
		add_filter('reycore/woocommerce/catalog/stretch_product/enable', '__return_false', 20);
	}

	function product_attributes($attributes, $product)
	{
		$product_id = $product->get_id();

		if( ! ( $image = reycore__acf_get_field('product_stretch_image_display', $product_id) ) ){
			return $attributes;
		}

		$classes[] = '--stretch-image-' . ($image ? esc_attr($image) : 'contain');

		if( $image === 'images' ){
			$attributes['class'] = str_replace(['--extraImg-no', '--extraImg-second', '--extraImg-slideshow'], '', $attributes['class']);
		}

		if( reycore__acf_get_field('product_stretch_center', $product_id) ){
			$align_class = 'rey-wc-loopAlign-';
			$attributes['class'] = str_replace([
				$align_class . 'left',
				$align_class . 'center',
				$align_class . 'right',
			], $align_class . 'center', $attributes['class']);
		}

		$attributes['class'] .= ' ' . implode( ' ', $classes );

		if( $colspan = $this->get_colspan() ){
			$attributes['data-colspan'] = $colspan;

			global $wp_query;

			$colspans = absint($colspan);

			if( $existing_colspans = $wp_query->get('colspans') ){
				$colspans += (absint($existing_colspans) - 1);
			}

			$wp_query->set('colspans', $colspans);
		}

		return $attributes;
	}

	function product_image($image, $product, $size, $attr){

		if( ! ( $image_type = reycore__acf_get_field('product_stretch_image_display', $product->get_id()) ) ){
			return $image;
		}

		// Cover: add custom image
		if( $image_type === 'cover' ){
			if( $custom_image = reycore__acf_get_field('product_stretch_custom_thumbnail', $product->get_id()) ){
				$image = wp_get_attachment_image( $custom_image, 'large', false, $attr );
			}
		}

		// Multiple
		else if( $image_type === 'images' && ! is_admin() ){

			if( ($colspan = $this->get_colspan()) && ($image_count = $colspan - 1) && $image_count >= 1 && $images = reycore_wc__get_product_images_ids(false) ){

				foreach ($images as $key => $img) {

					if( $image_count < ($key + 1) ) {
						continue;
					}
					// start count from 2 (2nd one)
					$image .= wp_get_attachment_image( $img, $size, false, ['class'=>'rey-thumbImg img--'.($key+2)] );
				}

			}
		}

		return $image;
	}

	function get_product(){

		$product = wc_get_product();

		if( ! $product ){
			global $product;
		}

		if( $product ){
			return $product;
		}
	}

	function get_product_id(){

		if( $this->get_product() ){
			return $this->get_product()->get_id();
		}

	}

	function get_colspan(){

		if( ! apply_filters('reycore/woocommerce/catalog/stretch_product/enable', true, $this->get_product()) ){
			return false;
		}

		if( $colspan = reycore__acf_get_field('product_stretch', $this->get_product_id()) ){

			$this->load_assets();

			if( ($layout_cols = absint(wc_get_loop_prop('columns'))) && $layout_cols < $colspan ){
				return absint($layout_cols);
			}

			return absint($colspan);
		}

		return false;
	}

	function __return_no(){
		return 'no';
	}

	function before(){

		if( !(($colspan = $this->get_colspan()) && $colspan > 1) ){
			return;
		}

		add_filter( 'reycore/woocommerce/content_product/attributes', [$this, 'product_attributes'], 20, 2 );
		add_filter( 'woocommerce_product_get_image', [$this, 'product_image'], 110, 4 );
		add_filter( 'reycore/woocommerce/loop/render/thumbnails_second', '__return_false');
		add_filter( 'reycore/woocommerce/loop/render/thumbnails_slideshow', '__return_false');
	}

	function after(){
		remove_filter( 'reycore/woocommerce/content_product/attributes', [$this, 'product_attributes'], 20 );
		remove_filter( 'woocommerce_product_get_image', [$this, 'product_image'], 110 );
		remove_filter( 'reycore/woocommerce/loop/render/thumbnails_second', '__return_false');
		remove_filter( 'reycore/woocommerce/loop/render/thumbnails_slideshow', '__return_false');
	}

	public function load_assets(){

		reycore_assets()->add_styles('rey-wc-tag-stretch');
		reycore_assets()->add_scripts('reycore-wc-loop-stretch');

	}

	public function load_product_grid_assets(){
		if( wc_get_loop_prop( 'is_paginated' ) ){
			$this->load_assets();
		}
	}

	public function is_enabled() {
		return true;
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Stretch products in Catalog grid', 'Module name', 'rey-core'),
			'description' => esc_html_x('Can make a product stand out by expanding and stretching it over the span of multiple columns.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => ['product catalog'],
			'help'        => reycore__support_url('kb/product-settings-options/#catalog-display-product-stretch'),
		];
	}

	public function module_in_use(){

		$post_ids = get_posts([
			'post_type' => 'product',
			'numberposts' => -1,
			'post_status' => 'publish',
			'fields' => 'ids',
			'meta_query' => [
				[
					'key' => 'product_stretch',
					'value'   => '',
					'compare' => 'NOT IN'
				]
			]
		]);

		return ! empty($post_ids);

	}
}
