<?php
namespace ReyCore\WooCommerce\LoopComponents;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class ThumbnailsSecond extends ThumbnailsExtra {


	public function status(){
		return get_theme_mod('loop_extra_media', 'second') === 'second';
	}

	public function get_id(){
		return 'thumbnails_second';
	}

	public function get_name(){
		return 'Thumbnails Second';
	}

	public function scheme(){
		return [
			'type'         => 'action',
			'tag'          => 'woocommerce_before_shop_loop_item_title',
			'priority'     => 9, // before main image
		];
	}

	public function render(){

		if( ! $this->maybe_render() ){
			return;
		}

		if( !($product = reycore_wc__get_product()) ){
			return;
		}

		if ( ! ( $images = self::get_images() ) ) {
			return;
		}

		$key = isset($images['main']) ? 0 : 1;

		if( empty($images[$key]) ){
			return;
		}

		echo apply_filters('reycore/woocommerce/loop/second_image',
			reycore__get_picture([
				'id' => $images[$key],
				'size' => apply_filters( 'single_product_archive_thumbnail_size', 'woocommerce_thumbnail' ),
				'class' => 'rey-productThumbnail__second',
				'disable_mobile' => ! self::loop_extra_media_mobile(),
			]),
			$product,
			$images[$key],
			$images
		);

	}

}
