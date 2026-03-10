<?php
namespace ReyCore\WooCommerce\PdpComponents;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class GalleryBase {

	public function init($gallery){}

	public function before_gallery(){}

	public function get_id(){}

	public function get_name(){}

	public function load_assets(){}

	public function get_gallery_css_classes(){
		return [];
	}
}
