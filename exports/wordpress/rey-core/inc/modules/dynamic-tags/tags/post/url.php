<?php
namespace ReyCore\Modules\DynamicTags\Tags\Post;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Url extends \ReyCore\Modules\DynamicTags\Tags\Tag {

	public static function __config() {
		return [
			'id'         => 'post-url',
			'title'      => esc_html__( 'Post URL', 'rey-core' ),
			'categories' => [ 'url' ],
			'group'      => \ReyCore\Modules\DynamicTags\Base::GROUPS_POST,
		];
	}

	public function render()
	{
		echo get_permalink();
	}

}
