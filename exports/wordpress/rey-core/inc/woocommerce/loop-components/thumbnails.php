<?php
namespace ReyCore\WooCommerce\LoopComponents;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Thumbnails extends Component {

	public function init(){
		remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10);
	}

	public function get_id(){
		return 'thumbnails';
	}

	public function get_name(){
		return 'Thumbnails';
	}

	public function scheme(){
		return [
			'type'          => 'action',
			'tag'           => 'woocommerce_before_shop_loop_item_title',
			'priority'      => 10,
		];
	}

	public function render(){

		if( ! $this->maybe_render() ){
			return;
		}

		add_filter( 'woocommerce_product_get_image', [$this, 'thumbnail_custom_class__filter'], 100, 4);

		woocommerce_template_loop_product_thumbnail();

		remove_filter( 'woocommerce_product_get_image', [$this, 'thumbnail_custom_class__filter'], 100, 4);
	}

	public function replace_placeholder_with_variation_image($product, $size, $attr){

		$available_attributes = $product->get_available_variations();

		if( empty($available_attributes) ){
			return;
		}

		$variation_image_id = false;

		foreach(array_reverse($available_attributes) as $variation_values ){
			if( isset($variation_values['image_id']) && !empty($variation_values['image_id']) ){
				$variation_image_id = $variation_values['image_id'];
			}
		}

		if( ! $variation_image_id ){
			return;
		}

		return wp_get_attachment_image( $variation_image_id, $size, false, $attr );
	}

	public function thumbnail_custom_class__filter( $image, $product, $size, $attr ){

		if( strpos($image, 'woocommerce-placeholder') > -1 && $product->get_type() === 'variable' ){
			if( $var_image = $this->replace_placeholder_with_variation_image($product, $size, $attr) ){
				$image = $var_image;
			}
		}

		return str_replace('class="attachment', 'class="rey-thumbImg img--1 attachment', $image);
	}
}
