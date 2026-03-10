<?php
namespace ReyCore\Modules\DynamicTags\Tags\Archive;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Desc extends \ReyCore\Modules\DynamicTags\Tags\Tag {

	public static function __config() {
		return [
			'id'         => 'archive-desc',
			'title'      => esc_html__( 'Archive description', 'rey-core' ),
			'categories' => [ 'text' ],
			'group'      => \ReyCore\Modules\DynamicTags\Base::GROUPS_ARCHIVE,
		];
	}

	public function render() {
		echo wp_kses_post( get_the_archive_description() );
	}

}
