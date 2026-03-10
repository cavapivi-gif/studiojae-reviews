<?php
namespace ReyCore\Modules\DynamicTags\Tags\Woo;

use \ReyCore\Modules\DynamicTags\Base as TagDynamic;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Information extends \ReyCore\Modules\DynamicTags\Tags\Tag {

	public static function __config() {
		return [
			'id'         => 'product-information',
			'title'      => esc_html__( 'Product Information', 'rey-core' ),
			'categories' => [ 'text' ],
			'group'      => TagDynamic::GROUPS_WOO,
		];
	}

	protected function register_controls() {
		TagDynamic::woo_product_control($this);
	}

	public function render() {

		if( ! ($product = TagDynamic::get_product($this)) ){
			return TagDynamic::display_placeholder_data( esc_html__( '{Product Information}', 'rey-core' ) );
		}

		$GLOBALS['post'] = get_post( $product->get_id() );
		setup_postdata( $GLOBALS['post'] );

			echo wp_kses_post( reycore__parse_text_editor( reycore__get_option( 'product_info_content' ) ) );

		wp_reset_postdata();

	}

}
