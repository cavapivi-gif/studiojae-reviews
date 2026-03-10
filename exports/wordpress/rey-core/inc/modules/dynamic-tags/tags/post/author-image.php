<?php
namespace ReyCore\Modules\DynamicTags\Tags\Post;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class AuthorImage extends \ReyCore\Modules\DynamicTags\Tags\DataTag {

	public static function __config() {
		return [
			'id'         => 'post-author-image',
			'title'      => esc_html__( 'Post Author Image', 'rey-core' ),
			'categories' => [ 'image' ],
			'group'      => \ReyCore\Modules\DynamicTags\Base::GROUPS_POST,
		];
	}

	public function get_value( $options = [] )
	{
		global $authordata;

		if( ! isset( $authordata->ID ) ){
			return [];
		}

		return [
			'id' => '',
			'url' => get_avatar_url( $authordata->ID )
		];
	}

}
