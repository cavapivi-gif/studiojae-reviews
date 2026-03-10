<?php
namespace ReyCore\Modules\DynamicTags\Tags\Site;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Email extends \ReyCore\Modules\DynamicTags\Tags\Tag {

	public static function __config() {
		return [
			'id'         => 'site-email',
			'title'      => esc_html__( 'Site Admin Email', 'rey-core' ),
			'categories' => [ 'url', 'text' ],
			'group'      => \ReyCore\Modules\DynamicTags\Base::GROUPS_SITE,
		];
	}

	public function render() {
		echo esc_url( get_bloginfo('admin_email') );
	}

}
