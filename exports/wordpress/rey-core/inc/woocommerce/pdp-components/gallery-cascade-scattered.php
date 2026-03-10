<?php
namespace ReyCore\WooCommerce\PdpComponents;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class GalleryCascadeScattered extends GalleryBase {

	public function init( $gallery ){
	}

	public function get_id(){
		return 'cascade-scattered';
	}

	public function get_name(){
		return 'Cascade Scattered';
	}

}
