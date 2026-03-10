<?php
namespace ReyCore\Modules\DynamicTags\Tags\Post;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Duration extends \ReyCore\Modules\DynamicTags\Tags\Tag {

	public static function __config() {
		return [
			'id'         => 'post-duration',
			'title'      => esc_html__( 'Post Read Duration', 'rey-core' ),
			'categories' => [ 'text' ],
			'group'      => \ReyCore\Modules\DynamicTags\Base::GROUPS_POST,
		];
	}

	public function render() {

		if( ! function_exists('rey__estimated_reading_time') ){
			return;
		}

		echo rey__estimated_reading_time();

		if( empty($this->get_settings('after')) ){
			echo esc_html__(' min read', 'rey-core');
		}
	}

}
