<?php
namespace ReyCore\Modules\ArchiveBottomDesc;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class DynamicTag extends \ReyCore\Modules\DynamicTags\Tags\Tag {

	public static function __config() {
		return [
			'id'         => 'archive-product-bottom-desc',
			'title'      => esc_html__( 'Product Archive Bottom description', 'rey-core' ),
			'categories' => [ 'text' ],
			'group'      => \ReyCore\Modules\DynamicTags\Base::GROUPS_ARCHIVE,
		];
	}

	public function render() {
		echo wp_kses_post( Base::get_bottom_description() );
	}

}
