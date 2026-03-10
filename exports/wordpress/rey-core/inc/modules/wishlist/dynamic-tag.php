<?php
namespace ReyCore\Modules\Wishlist;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use \ReyCore\Modules\DynamicTags\Base as TagDynamic;

class DynamicTag extends \ReyCore\Modules\DynamicTags\Tags\Tag {

	public static function __config() {
		return [
			'id'         => 'product-loop-wishlist',
			'title'      => esc_html__( 'Product Wishlist Button (Loop)', 'rey-core' ),
			'categories' => [ 'text' ],
			'group'      => TagDynamic::GROUPS_WOO,
		];
	}

	protected function register_controls() {
		TagDynamic::woo_product_control($this);
	}

	public function render() {

		if( ! ($product = TagDynamic::get_product($this)) ){
			return TagDynamic::display_placeholder_data( esc_html__( '{Wishlist Button}', 'rey-core' ) );
		}

		do_action('reycore/woocommerce/loop/wishlist_button');

	}

}
