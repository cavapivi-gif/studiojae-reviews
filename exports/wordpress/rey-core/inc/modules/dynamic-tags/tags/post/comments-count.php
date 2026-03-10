<?php
namespace ReyCore\Modules\DynamicTags\Tags\Post;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class CommentsCount extends \ReyCore\Modules\DynamicTags\Tags\Tag {

	public static function __config() {
		return [
			'id'         => 'post-comments-count',
			'title'      => esc_html__( 'Post Comments Count', 'rey-core' ),
			'categories' => [ 'text' ],
			'group'      => \ReyCore\Modules\DynamicTags\Base::GROUPS_POST,
		];
	}

	public function render() {
		echo absint( get_comments_number() );
	}

}
