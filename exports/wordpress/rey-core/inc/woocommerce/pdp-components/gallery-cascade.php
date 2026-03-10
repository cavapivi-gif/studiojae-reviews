<?php
namespace ReyCore\WooCommerce\PdpComponents;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class GalleryCascade extends GalleryBase {

	public function init( $gallery ){
		add_action( 'reycore/woocommerce/product_image/after_gallery_wrapper', [$this, 'add_cascade_navigation']);
	}

	public function get_id(){
		return 'cascade';
	}

	public function get_name(){
		return 'Cascade';
	}

	public function add_cascade_navigation(){

		$after = sprintf('<button class="__navItem-scroll">%s</button>', reycore__get_svg_icon(['id' => 'arrow-long']));

		\ReyCore\WooCommerce\PdpComponents\Gallery::add_dots_navigation_markup('cascadeNav', $after);

	}
}
