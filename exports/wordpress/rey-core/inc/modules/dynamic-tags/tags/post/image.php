<?php
namespace ReyCore\Modules\DynamicTags\Tags\Post;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Image extends \ReyCore\Modules\DynamicTags\Tags\DataTag {

	public static function __config() {
		return [
			'id'         => 'post-image',
			'title'      => esc_html__( 'Post Image', 'rey-core' ),
			'categories' => [ 'image' ],
			'group'      => \ReyCore\Modules\DynamicTags\Base::GROUPS_POST,
		];
	}

    public function get_value( $options = [] ){
		return [
			'id' => get_post_thumbnail_id(),
			'url' => get_the_post_thumbnail_url(),
		];
	}

}
