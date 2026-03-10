<?php
namespace ReyCore\Modules\DynamicTags\Tags\Woo;

use \ReyCore\Modules\DynamicTags\Base as TagDynamic;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class AttributeGallery extends \ReyCore\Modules\DynamicTags\Tags\DataTag {

	public static function __config() {
		return [
			'id'         => 'product-attribute-gallery',
			'title'      => esc_html__( 'Product Attribute Gallery', 'rey-core' ),
			'categories' => [ 'gallery' ],
			'group'      => TagDynamic::GROUPS_WOO,
		];
	}

	protected function register_controls() {

		$attributes = [];

		if( function_exists('wc_get_attribute_taxonomies') ){
			foreach( wc_get_attribute_taxonomies() as $attribute ) {

				if( $attribute->attribute_type !== 'rey_image' ){
					continue;
				}

				$attribute_name = wc_attribute_taxonomy_name($attribute->attribute_name);
				$attributes[$attribute_name] = $attribute->attribute_label;
			}
		}

		$this->add_control(
			'attr_id',
			[
				'label' => __( 'Attribute', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => ['' => esc_html__('- Select -', 'rey-core')] + $attributes,
				'label_block' => true,
			]
		);

		$this->add_control(
			'attr_id_desc',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => __('The attribute must be type "Image" ex: <a href="https://d.pr/i/2MWGQK" target="_blank">https://d.pr/i/2MWGQK</a>.', 'rey-core'),
				'content_classes' => 'rey-raw-html',
			]
		);

		// $this->add_control(
		// 	'meta_key',
		// 	[
		// 		'label' => esc_html__( 'Meta Key', 'rey-core' ),
		// 		'type' => \Elementor\Controls_Manager::TEXT,
		// 		'default' => '',
		// 		'separator'   => 'after',
		// 	]
		// );

		TagDynamic::woo_product_control($this);

	}

	public function get_value( $options = [] ) {

		if( ! ($product = TagDynamic::get_product($this)) ){
			return [
				[
					'id' => '',
					'url' => wc_placeholder_img_src(),
				],
			];
		}

		$settings = $this->get_settings();

		if( ! ($attribute_name = $settings['attr_id']) ){
			return [];
		}

		$images = [];
		$attr_terms = get_the_terms( $product->get_id(), $attribute_name );

		foreach ($attr_terms as $term) {
			if( $image_id = reycore__acf_get_field( 'rey_attribute_image', $attribute_name . '_' . $term->term_id) ){
				$images[] = [
					'id' => $image_id,
					'url' => wp_get_attachment_image_src($image_id, 'full'),
				];
			}
		}

		return $images;
	}

}
