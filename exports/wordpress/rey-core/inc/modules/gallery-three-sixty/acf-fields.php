<?php
namespace ReyCore\Modules\GalleryThreeSixty;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class AcfFields {

	const FIELDS_GROUP_KEY = 'group_5d4ff536a2684';

	public function __construct(){

		if( ! function_exists('acf_add_local_field') ){
			return;
		}

		foreach ($this->fields() as $key => $field) {
			acf_add_local_field($field);
		}

	}

	public function fields(){
		return [
			[
				'key' => 'field_5f79924bfabba',
				'label' => esc_html_x('360 Image', 'Backend setting label', 'rey-core'),
				'name' => '',
				'type' => 'tab',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => [
					'width' => '',
					'class' => '',
					'id' => '',
				],
				'placement' => 'top',
				'endpoint' => 0,
				'parent' => self::FIELDS_GROUP_KEY,
			],
			[
				'key' => 'field_5f799265fabbb',
				'label' => esc_html_x('Add images', 'Backend setting label', 'rey-core'),
				'name' => 'product_360_images',
				'type' => 'gallery',
				'instructions' => sprintf(
					__('You can tweak some settings in <a href="%s" target="_blank">Customizer > WooCommerce > Product Images</a> panel.', 'rey-core'),
					add_query_arg(['autofocus[control]' => 'wc360_position'], admin_url( 'customize.php' ))
				),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => [
					'width' => '',
					'class' => '',
					'id' => '',
				],
				'return_format' => 'id',
				'preview_size' => 'woocommerce_gallery_thumbnail',
				'insert' => 'append',
				'library' => 'all',
				'min' => '',
				'max' => '',
				'min_width' => '',
				'min_height' => '',
				'min_size' => '',
				'max_width' => '',
				'max_height' => '',
				'max_size' => '',
				'mime_types' => 'jpg, jpeg, png, webp',
				'parent' => self::FIELDS_GROUP_KEY,
			],
		];
	}
}
