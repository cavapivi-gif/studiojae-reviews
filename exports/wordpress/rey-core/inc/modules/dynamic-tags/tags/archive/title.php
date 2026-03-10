<?php
namespace ReyCore\Modules\DynamicTags\Tags\Archive;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Title extends \ReyCore\Modules\DynamicTags\Tags\Tag {

	public static function __config() {
		return [
			'id'         => 'archive-title',
			'title'      => esc_html__( 'Archive Title', 'rey-core' ),
			'categories' => [ 'text' ],
			'group'      => \ReyCore\Modules\DynamicTags\Base::GROUPS_ARCHIVE,
		];
	}

	public function render() {
		echo wp_kses_post( reycore__get_page_title() );
	}

}
