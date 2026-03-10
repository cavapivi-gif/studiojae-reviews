<?php
namespace ReyCore\Modules\DynamicTags\Tags\Site;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Url extends \ReyCore\Modules\DynamicTags\Tags\Tag {

	public static function __config() {
		return [
			'id'         => 'site-url',
			'title'      => esc_html__( 'Site URL', 'rey-core' ),
			'categories' => [ 'url', 'text' ],
			'group'      => \ReyCore\Modules\DynamicTags\Base::GROUPS_SITE,
		];
	}

	public function render() {
		echo esc_url( get_bloginfo('url') );
	}

}
