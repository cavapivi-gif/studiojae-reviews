<?php
namespace ReyCore\Modules\DynamicTags\Tags\Woo;

use \ReyCore\Modules\DynamicTags\Base as TagDynamic;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Stock extends \ReyCore\Modules\DynamicTags\Tags\Tag {

	public static function __config() {
		return [
			'id'         => 'product-stock',
			'title'      => esc_html__( 'Product Stock Availability', 'rey-core' ),
			'categories' => [ 'text' ],
			'group'      => TagDynamic::GROUPS_WOO,
		];
	}

	protected function register_controls() {
		TagDynamic::woo_product_control($this);
	}

	public function render() {

		if( ! ($product = TagDynamic::get_product($this)) ){
			return TagDynamic::display_placeholder_data( esc_html__( '{Product Stock Availability}', 'rey-core' ) );
		}

		$av = $product->get_availability();

		if( isset($av['availability']) ){
			if( $av['availability'] ){
				echo wp_kses_post( $av['availability'] );
			}
			else {
				if( current_user_can('administrator') ){
					echo esc_html__('Likely in stock, but availablility text is empty. Please add a Fallback text.', 'rey-core');
				}
			}
		}

	}

}
