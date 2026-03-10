<?php
namespace ReyCore\Modules\DynamicTags\Tags\Woo;

use \ReyCore\Modules\DynamicTags\Base as TagDynamic;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Image extends \ReyCore\Modules\DynamicTags\Tags\DataTag {

	public static function __config() {
		return [
			'id'         => 'product-image',
			'title'      => esc_html__( 'Product Image', 'rey-core' ),
			'categories' => [ 'image' ],
			'group'      => TagDynamic::GROUPS_WOO,
		];
	}

	protected function register_controls() {
		TagDynamic::woo_product_control($this);
	}

	public function get_value( $options = [] ) {

		if( ! ($product = TagDynamic::get_product($this)) ){
			return [
				'id' => '',
				'url' => wc_placeholder_img_src(),
			];
		}

		$id = $product->get_image_id();

		return [
			'id' => $id,
			'url' => wp_get_attachment_image_src($id, 'full'),
		];

	}

}
