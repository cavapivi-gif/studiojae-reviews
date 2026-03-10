<?php
namespace ReyCore\Modules\DynamicTags\Tags\Archive;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Url extends \ReyCore\Modules\DynamicTags\Tags\Tag {

	public static function __config() {
		return [
			'id'         => 'archive-url',
			'title'      => esc_html__( 'Archive URL', 'rey-core' ),
			'categories' => [ 'url' ],
			'group'      => \ReyCore\Modules\DynamicTags\Base::GROUPS_ARCHIVE,
		];
	}

	public function render() {

		if( ! (
			is_archive()
			|| is_post_type_archive()
			|| is_author()
			|| is_category()
			|| is_tag()
			|| is_date()
			|| is_day()
			|| is_month()
			|| is_year()
			|| is_tax()
		) ){
			return;
		}

		$object = get_queried_object();

		if( is_wp_error($object) || ! isset($object->taxonomy) ){
			return;
		}

		echo esc_url( get_term_link( $object->term_id, $object->taxonomy ) );
	}

}
