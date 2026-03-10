<?php
namespace ReyCore\Modules\DynamicTags\Tags\Woo;

use \ReyCore\Modules\DynamicTags\Base as TagDynamic;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Sku extends \ReyCore\Modules\DynamicTags\Tags\Tag {

	public static function __config() {
		return [
			'id'         => 'product-sku',
			'title'      => esc_html__( 'Product Sku', 'rey-core' ),
			'categories' => [ 'text' ],
			'group'      => TagDynamic::GROUPS_WOO,
		];
	}

	protected function register_controls() {
		TagDynamic::woo_product_control($this);
	}

	public function render() {

		if( ! ($product = TagDynamic::get_product($this)) ){
			return TagDynamic::display_placeholder_data( esc_html__( '{Product SKU}', 'rey-core' ) );
		}

		echo wp_kses_post( $product->get_sku() );
	}

}
