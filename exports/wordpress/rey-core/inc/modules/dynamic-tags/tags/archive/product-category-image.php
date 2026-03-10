<?php
namespace ReyCore\Modules\DynamicTags\Tags\Archive;

use \ReyCore\Modules\DynamicTags\Base as TagDynamic;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class ProductCategoryImage extends \ReyCore\Modules\DynamicTags\Tags\DataTag {

	public static function __config() {
		return [
			'id'         => 'product-category-image',
			'title'      => esc_html__( 'Product Category Image', 'rey-core' ),
			'categories' => [ 'image' ],
			'group'      => TagDynamic::GROUPS_ARCHIVE,
		];
	}

	protected function register_controls() {

		$this->add_control(
			'custom_key',
			[
				'label' => esc_html__( 'Meta Key (Optional)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'description' => esc_html__('By default `thumbnail_id` is used however you can specify another term meta key.', 'rey-core')
			]
		);

	}

	public function get_value( $options = [] ) {

		$key = 'thumbnail_id';

		if( $custom_key = $this->get_settings('custom_key') ){
			$key = $custom_key;
		}

		$att_id = absint( get_term_meta( get_queried_object_id(), $key, true ) );

		if( empty($att_id) ){
			return [];
		}

		return [
			'id' => $att_id,
			'url' => wp_get_attachment_image_src($att_id, 'full'),
		];
	}

}
