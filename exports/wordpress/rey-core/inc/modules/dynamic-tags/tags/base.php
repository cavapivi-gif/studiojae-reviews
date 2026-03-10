<?php
namespace ReyCore\Modules\DynamicTags\Tags;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use \Elementor\Modules\DynamicTags\Module;

trait Base {

	private $product_id;

	public function get_name() {
		return 'rey-' . static::get_tag_data( 'id' );
	}

	public function get_title() {
		return static::get_tag_data( 'title' );
	}

	public function get_categories(){

		$map = [
			'text'      => Module::TEXT_CATEGORY, // heading, text editor, button text, etc.
			'url'       => Module::URL_CATEGORY, // heading url, button url, all URL
			'image'     => Module::IMAGE_CATEGORY, // image, all image
			'media'     => Module::MEDIA_CATEGORY,
			'post_meta' => Module::POST_META_CATEGORY,
			'gallery'   => Module::GALLERY_CATEGORY,
			'number'    => Module::NUMBER_CATEGORY,
			'color'     => Module::COLOR_CATEGORY,
			'datetime'  => 'datetime',
		];

		$categories = [];

		foreach (static::get_tag_data( 'categories' ) as $cat) {
			$categories[] = $map[$cat];
		}

		return $categories;
	}

	public function get_group(){
		return static::get_tag_data( 'group' );
	}

	/**
	 * Holds the tag configuration
	 *
	 * @return array
	 */
	protected static function __config() {}

	/**
	 * Retrieve tag data
	 *
	 * @return array
	 */
	public static function get_tag_data( $key = '' ){

		$config = static::__config();

		if( ! ( is_array($config) && ! empty( $config ) ) ){
			return;
		}

		$tag_data = wp_parse_args($config, [
			'id'              => '',
			'title'           => '',
			'categories'      => [],
			'group'           => '',
		]);

		return $key ? $tag_data[$key] : $tag_data;
	}

}
