<?php
namespace ReyCore\WooCommerce\PdpComponents;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class GalleryHorizontal extends GalleryBase {

	public function init( $gallery ){
	}

	public function get_id(){
		return 'horizontal';
	}

	public function get_name(){
		return 'Horizontal';
	}

	public function load_assets(){

	}

}
