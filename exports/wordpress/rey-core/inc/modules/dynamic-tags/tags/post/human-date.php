<?php
namespace ReyCore\Modules\DynamicTags\Tags\Post;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class HumanDate extends \ReyCore\Modules\DynamicTags\Tags\Tag {

	public static function __config() {
		return [
			'id'         => 'post-human-date',
			'title'      => esc_html__( 'Post Date (Human)', 'rey-core' ),
			'categories' => [ 'text' ],
			'group'      => \ReyCore\Modules\DynamicTags\Base::GROUPS_POST,
		];
	}

	public function render() {

		if( function_exists('rey__humanDate') ){
			echo rey__humanDate();
		}

	}

}
