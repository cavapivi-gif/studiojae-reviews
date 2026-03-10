<?php
namespace ReyCore\WooCommerce\PdpComponents;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class GalleryGridPattern extends GalleryBase {

	public $itemCount = 0;
	public $patternIndex = 0;

	public function init( $gallery ){

		add_filter('reycore/woocommerce/pdp/gallery/item_attributes', array($this, 'item_attributes'), 10, 2);
	}

	public function get_id(){
		return 'grid-pattern';
	}

	public function get_name(){
		return 'Grid Pattern';
	}

	public static function get_scheme(){

		static $scheme;

		if( is_null($scheme) ){

			$scheme = [2, 3];

			if( $custom_scheme = get_theme_mod('product_gallery_grid_pattern', '') ){
				$scheme = array_filter( explode(' ', str_replace(',', ' ', $custom_scheme) ) );
				$scheme = array_map(function($i){
					if( absint($i) > 8 ){
						return 8;
					}
					return absint($i);
				}, $scheme);
			}

		}

		return $scheme;
	}

	public function item_attributes( $attributes, $index ){

		$scheme = self::get_scheme();

		$attributes .= sprintf('style="--basis: %d;" ', $scheme[$this->patternIndex]);

		if( 4 <= $scheme[$this->patternIndex] ){
			$attributes .= 'data-no-zoom data-small-item ';
		}

		// Increment the item count and check if we need to move to the next part of the pattern
		$this->itemCount++;

		if ($this->itemCount === $scheme[$this->patternIndex]) {
			$this->itemCount = 0;
			$this->patternIndex = ($this->patternIndex + 1) % count($scheme); // Loop back to the start of the pattern if necessary
		}

		return $attributes;
	}

}
