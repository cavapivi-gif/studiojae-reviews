<?php
namespace ReyCore\Modules\DynamicTags\Tags\Site;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Title extends \ReyCore\Modules\DynamicTags\Tags\Tag {

	public static function __config() {
		return [
			'id'         => 'site-title',
			'title'      => esc_html__( 'Site Title', 'rey-core' ),
			'categories' => [ 'text' ],
			'group'      => \ReyCore\Modules\DynamicTags\Base::GROUPS_SITE,
		];
	}

	public function render() {
		echo wp_kses_post( get_bloginfo('name') );
	}

}
