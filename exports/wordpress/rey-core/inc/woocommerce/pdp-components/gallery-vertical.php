<?php
namespace ReyCore\WooCommerce\PdpComponents;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class GalleryVertical extends GalleryBase {

	public function init( $gallery ){
	}

	public function get_id(){
		return 'vertical';
	}

	public function get_name(){
		return 'Vertical';
	}

	public function load_assets(){

	}
}
