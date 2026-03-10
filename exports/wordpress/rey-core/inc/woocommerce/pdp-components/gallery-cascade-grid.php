<?php
namespace ReyCore\WooCommerce\PdpComponents;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class GalleryCascadeGrid extends GalleryBase {

	public function init( $gallery ){
	}

	public function get_id(){
		return 'cascade-grid';
	}

	public function get_name(){
		return 'Grid (1st large)';
	}

	public function get_gallery_css_classes(){

		$classes = [];

		if( get_theme_mod('grid_stretch_odd', false) ){
			$classes[] = '--stretch-uneven';
		}

		return $classes;
	}

	public function load_assets(){

	}

}
