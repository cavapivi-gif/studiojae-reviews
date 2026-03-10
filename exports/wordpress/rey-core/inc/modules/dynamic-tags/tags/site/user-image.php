<?php
namespace ReyCore\Modules\DynamicTags\Tags\Site;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class UserImage extends \ReyCore\Modules\DynamicTags\Tags\DataTag {

	public static function __config() {
		return [
			'id'         => 'site-user-image',
			'title'      => esc_html__( 'User Image', 'rey-core' ),
			'categories' => [ 'image' ],
			'group'      => \ReyCore\Modules\DynamicTags\Base::GROUPS_SITE,
		];
	}

	public function get_value( $options = [] )
	{
		$current_user = wp_get_current_user();

		if( ! isset($current_user->ID) ){
			return [];
		}

		return [
			'id' => '',
			'url' => get_avatar_url( $current_user->ID ),
		];
	}

}
