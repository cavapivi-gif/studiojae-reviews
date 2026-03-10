<?php
namespace ReyCore\Modules\DynamicTags\Tags\Woo;

use \ReyCore\Modules\DynamicTags\Base as TagDynamic;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class AttributeImage extends \ReyCore\Modules\DynamicTags\Tags\DataTag {

	public static function __config() {
		return [
			'id'         => 'product-attribute-image',
			'title'      => esc_html__( 'Product Attribute Image', 'rey-core' ),
			'categories' => [ 'image' ],
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

		$this->add_control(
			'attribute',
			[
				'label'       => esc_html__( 'Attribute Term', 'rey-core' ),
				'default'     => '',
				'type'        => 'rey-query',
				'label_block' => true,
				'query_args'  => [
					'type'     => 'terms',
					'taxonomy' => 'product_taxonomies',
				],
				'separator'   => 'before',
			]
		);

		$this->add_control(
			'meta_key',
			[
				'label' => esc_html__( 'Meta Key', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'separator'   => 'after',
			]
		);

		TagDynamic::woo_product_control($this);

	}

	public function get_value( $options = [] ) {

		if( ! ($product = TagDynamic::get_product($this)) ){
			return [
				'id' => '',
				'url' => wc_placeholder_img_src(),
			];
		}

		$settings = $this->get_settings();

		if( $attribute_name = $settings['attr_id'] ){

			$attr_terms = get_the_terms( $product->get_id(), $attribute_name );

			if( isset($attr_terms[0], $attr_terms[0]->term_id) ){
				if( $image_id = reycore__acf_get_field( 'rey_attribute_image', $attribute_name . '_' . $attr_terms[0]->term_id) ){
					$image_size = get_post_mime_type($image_id) === 'image/svg+xml' ? 'full' : 'woocommerce_single';
					return [
						'id' => $image_id,
						'url' => wp_get_attachment_image($image_id, $image_size),
					];
				}
			}

		}

		if( ! ($term_id = $settings['attribute']) ){
			return [];
		}

		$term_obj = get_term_by( 'term_taxonomy_id', $term_id );

		if( ! isset($term_obj->name) ){
			return [];
		}

		// product must have this term
		if( ! has_term( $term_id, $term_obj->taxonomy, $product->get_id() ) ){
			return [];
		}

		if( ! (($meta_key = $settings['meta_key']) && ($meta = get_term_meta($term_id, $meta_key, true))) ){
			return [];
		}

		// likely an ID or URL
		if( is_string($meta) && ! empty($meta) ){
			if( is_numeric($meta) ){
				$att_id = absint($meta);
				return [
					'id' => $att_id,
					'url' => wp_get_attachment_image_src($att_id, 'full'),
				];
			}
			else {
				$att_url = esc_url($meta);
				return [
					'id'  => attachment_url_to_postid($att_url),
					'url' => $att_url,
				];
			}
		}
		else if( is_array($meta) ){
			if( isset($meta['id'], $meta['url']) ){
				return [
					'id'  => $meta['id'],
					'url' => $meta['url'],
				];
			}
			// likely an ID
			elseif( count($meta) === 1 ){
				return [
					'id'  => $meta[0],
					'url' => wp_get_attachment_image_src($meta[0], 'full'),
				];
			}
		}

		return [];
	}

}
