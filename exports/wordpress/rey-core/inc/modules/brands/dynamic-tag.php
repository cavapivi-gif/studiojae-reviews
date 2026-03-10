<?php
namespace ReyCore\Modules\Brands;

use \ReyCore\Modules\DynamicTags\Base as TagDynamic;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class DynamicTag extends \ReyCore\Modules\DynamicTags\Tags\Tag {

	public static function __config() {
		return [
			'id'         => 'product-brand',
			'title'      => esc_html__( 'Product Brand', 'rey-core' ),
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

		$base = Base::instance();

		if ( ! ( $base->brands_tax_exists() && ( $brands = $base->get_brands('', $product->get_id()) ) ) ) {
			return;
		}

		echo wp_kses_post( $base->catalog__brand_output($brands) );

	}

}
