<?php
namespace ReyCore;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Mobile {

	public $is_mobile;
	public $mobile_cache;
	public $image_sizes;

	const IMAGE_SIZE_HALF = 'rey-mobile-240';
	const IMAGE_SIZE_FULL = 'rey-mobile-400';

	public function __construct()
	{
		add_action( 'init', [$this, 'init']);
	}

	function init(){

		if( ! apply_filters('reycore/mobile_improvements', false) ){
			return;
		}

		if( ! reycore__supports_mobile_caching() ){
			return;
		}

		$this->image_sizes = apply_filters('reycore/mobile_improvements/image_sizes', [
			'full' => 480,
			'half' => 240,
		]);

		if( !empty($this->image_sizes) ){
			add_image_size( self::IMAGE_SIZE_FULL, $this->image_sizes['full'], 9999 );
			add_image_size( self::IMAGE_SIZE_HALF, $this->image_sizes['half'], 9999 );
		}

		if( ! reycore__is_mobile() ){
			return;
		}

		if( apply_filters('reycore/mobile_improvements/check_visbility', true) ){
			add_filter( 'elementor/frontend/widget/should_render', [$this, 'maybe_stop_rendering_elementor_content'], 10, 2);
			add_filter( 'elementor/frontend/column/should_render', [$this, 'maybe_stop_rendering_elementor_content'], 10, 2);
			add_filter( 'elementor/frontend/section/should_render', [$this, 'maybe_stop_rendering_elementor_content'], 10, 2);
		}

		if( !empty($this->image_sizes) ){
			add_filter( 'single_product_archive_thumbnail_size', [$this, 'adjust_product_thumbs_sizes'], 20);
			add_filter( 'wp_get_attachment_image_attributes', [$this, 'adjust_product_image_attributes'], 20, 3 );
			add_filter( 'post_thumbnail_size', [$this, 'adjust_image_sizes'], 20 );
			add_filter( 'reycore/elementor/bg_image_lazy', [$this, 'adjust_image_sizes'], 20 );
			add_filter( 'reycore/elementor/bg_image_lazy/mobile', [$this, 'adjust_image_sizes'], 20 );
		}

		add_filter( 'theme_mod_loop_quickview', [$this, 'disable_quickview'], 20 );
		add_filter( 'pre_wp_nav_menu', [$this, 'disable_desktop_main_nav'], 20, 2 );
	}

	function disable_quickview(){
		return '2';
	}

	function adjust_product_image_attributes($attributes, $attachment, $size){

		if( in_array($size, [self::IMAGE_SIZE_FULL, self::IMAGE_SIZE_HALF], true) ){
			unset($attributes['srcset']);
			unset($attributes['sizes']);
		}

		return $attributes;
	}

	function adjust_image_sizes(){
		return self::IMAGE_SIZE_FULL;
	}

	function adjust_product_thumbs_sizes(){

		if( wc_get_loop_prop( 'pg_columns_mobile', reycore_wc_get_columns('mobile') ) > 1 ){
			return self::IMAGE_SIZE_HALF;
		}

		return self::IMAGE_SIZE_FULL;
	}

	function maybe_stop_rendering_elementor_content( $status, $element ){

		if( $element->get_settings('hide_mobile') !== '' ){
			return false;
		}

		return $status;
	}

	function disable_desktop_main_nav( $output, $args ){
		if( strpos($args->menu_class, 'rey-mainMenu--desktop') !== false ){
			return '';
		}
		return $output;
	}

}
