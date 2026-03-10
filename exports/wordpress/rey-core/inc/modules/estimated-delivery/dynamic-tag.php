<?php
namespace ReyCore\Modules\EstimatedDelivery;

use \ReyCore\Modules\DynamicTags\Base as TagDynamic;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class DynamicTag extends \ReyCore\Modules\DynamicTags\Tags\Tag {

	public static function __config() {
		return [
			'id'         => 'product-est-delivery',
			'title'      => esc_html__( 'Product Estimated Delivery', 'rey-core' ),
			'categories' => [ 'text' ],
			'group'      => TagDynamic::GROUPS_WOO,
		];
	}

	protected function register_controls() {
		TagDynamic::woo_product_control($this);
	}

	public function render() {

		if( ! ($product = TagDynamic::get_product($this)) ){
			return;
		}

		ob_start();
		Base::instance()->display([
			'id'      => $product->get_id(),
			'title'   => false,
			'wrapper' => false,
		]);
		$output = ob_get_clean();

		echo wp_strip_all_tags($output);

	}

}
