<?php
namespace ReyCore\Modules\ProductBeforeAfter;

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
				'key' => 'field_5fa00e8b3dead',
				'label' => esc_html_x('Catalog Display', 'Backend setting label', 'rey-core'),
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
				'key' => 'field_5fa00e9e3deae',
				'label' => esc_html_x('Content Before', 'Backend setting label', 'rey-core'),
				'name' => 'content_before',
				'type' => 'select',
				'instructions' => esc_html_x('Select what type of content you want to show before the product.', 'Backend setting description', 'rey-core'),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => [
					'width' => '',
					'class' => 'rey-decrease-list-size rey-acf-title',
					'id' => '',
				],
				'choices' => [
					'gs' => esc_html_x('Generic Global Section', 'Backend setting label', 'rey-core'),
					'product' => 'Product',
				],
				'default_value' => false,
				'allow_null' => 1,
				'multiple' => 0,
				'ui' => 0,
				'return_format' => 'value',
				'ajax' => 0,
				'placeholder' => '',
				'parent' => self::FIELDS_GROUP_KEY,
			],
			[
				'key' => 'field_5fa014ff3deaf',
				'label' => esc_html_x('Global section', 'Backend setting label', 'rey-core'),
				'name' => 'content_before_global_section',
				'type' => 'select',
				'instructions' => esc_html_x('Select a generic global section to display before the product.', 'Backend setting description', 'rey-core'),
				'required' => 0,
				'conditional_logic' => [
					[
						[
							'field' => 'field_5fa00e9e3deae',
							'operator' => '==',
							'value' => 'gs',
						],
					],
				],
				'wrapper' => [
					'width' => '',
					'class' => 'rey-decrease-list-size',
					'id' => '',
				],
				'choices' => [
				],
				'default_value' => false,
				'allow_null' => 1,
				'multiple' => 0,
				'ui' => 0,
				'return_format' => 'value',
				'ajax' => 0,
				'placeholder' => '',
				'parent' => self::FIELDS_GROUP_KEY,
				'rey_export' => 'post_id',
			],
			[
				'key' => 'field_5fa0156a3deb0',
				'label' => esc_html_x('Choose Product', 'Backend setting label', 'rey-core'),
				'name' => 'content_before_product',
				'type' => 'relationship',
				'instructions' => esc_html_x('Select a product to display before the product.', 'Backend setting description', 'rey-core'),
				'required' => 0,
				'conditional_logic' => [
					[
						[
							'field' => 'field_5fa00e9e3deae',
							'operator' => '==',
							'value' => 'product',
						],
					],
				],
				'wrapper' => [
					'width' => '',
					'class' => '',
					'id' => '',
				],
				'post_type' => [
					0 => 'product',
				],
				'taxonomy' => '',
				'filters' => [
					0 => 'search',
				],
				'elements' => '',
				'min' => '',
				'max' => 1,
				'return_format' => 'id',
				'parent' => self::FIELDS_GROUP_KEY,
				'rey_export' => 'post_id',
			],
			[
				'key' => 'field_5fa016223deb1',
				'label' => esc_html_x('Column Span', 'Backend setting label', 'rey-core'),
				'name' => 'content_before_colspan',
				'type' => 'number',
				'instructions' => esc_html_x('Stretch product per multiple columns.', 'Backend setting description', 'rey-core'),
				'required' => 0,
				'conditional_logic' => [
					[
						[
							'field' => 'field_5fa00e9e3deae',
							'operator' => '==',
							'value' => 'gs',
						],
					],
				],
				'wrapper' => [
					'width' => '',
					'class' => '',
					'id' => '',
				],
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'min' => '',
				'max' => '',
				'step' => '',
				'parent' => self::FIELDS_GROUP_KEY,
			],
			[
				'key' => 'field_5fa01ec02e100',
				'label' => esc_html_x('Content After', 'Backend setting label', 'rey-core'),
				'name' => 'content_after',
				'type' => 'select',
				'instructions' => esc_html_x('Select what type of content you want to show after the product.', 'Backend setting description', 'rey-core'),
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => [
					'width' => '',
					'class' => 'rey-decrease-list-size rey-acf-title',
					'id' => '',
				],
				'choices' => [
					'gs' => esc_html_x('Generic Global Section', 'Backend setting label', 'rey-core'),
					'product' => 'Product',
				],
				'default_value' => false,
				'allow_null' => 1,
				'multiple' => 0,
				'ui' => 0,
				'return_format' => 'value',
				'ajax' => 0,
				'placeholder' => '',
				'parent' => self::FIELDS_GROUP_KEY,
			],
			[
				'key' => 'field_5fa01ed02e101',
				'label' => esc_html_x('Global section', 'Backend setting label', 'rey-core'),
				'name' => 'content_after_global_section',
				'type' => 'select',
				'instructions' => esc_html_x('Select a generic global section to display after the product.', 'Backend setting description', 'rey-core'),
				'required' => 0,
				'conditional_logic' => [
					[
						[
							'field' => 'field_5fa01ec02e100',
							'operator' => '==',
							'value' => 'gs',
						],
					],
				],
				'wrapper' => [
					'width' => '',
					'class' => 'rey-decrease-list-size',
					'id' => '',
				],
				'choices' => [
				],
				'default_value' => false,
				'allow_null' => 1,
				'multiple' => 0,
				'ui' => 0,
				'return_format' => 'value',
				'ajax' => 0,
				'placeholder' => '',
				'parent' => self::FIELDS_GROUP_KEY,
				'rey_export' => 'post_id',
			],
			[
				'key' => 'field_5fa01ed42e102',
				'label' => esc_html_x('Choose Product', 'Backend setting label', 'rey-core'),
				'name' => 'content_after_product',
				'type' => 'relationship',
				'instructions' => esc_html_x('Select a product to display after the product.', 'Backend setting description', 'rey-core'),
				'required' => 0,
				'conditional_logic' => [
					[
						[
							'field' => 'field_5fa01ec02e100',
							'operator' => '==',
							'value' => 'product',
						],
					],
				],
				'wrapper' => [
					'width' => '',
					'class' => '',
					'id' => '',
				],
				'post_type' => [
					0 => 'product',
				],
				'taxonomy' => '',
				'filters' => [
					0 => 'search',
				],
				'elements' => '',
				'min' => '',
				'max' => 1,
				'return_format' => 'id',
				'parent' => self::FIELDS_GROUP_KEY,
				'rey_export' => 'post_id',
			],
			[
				'key' => 'field_5fa01ed92e103',
				'label' => esc_html_x('Column Span', 'Backend setting label', 'rey-core'),
				'name' => 'content_after_colspan',
				'type' => 'number',
				'instructions' => esc_html_x('Stretch product per multiple columns.', 'Backend setting description', 'rey-core'),
				'required' => 0,
				'conditional_logic' => [
					[
						[
							'field' => 'field_5fa01ec02e100',
							'operator' => '==',
							'value' => 'gs',
						],
					],
				],
				'wrapper' => [
					'width' => '',
					'class' => '',
					'id' => '',
				],
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'min' => '',
				'max' => '',
				'step' => '',
				'parent' => self::FIELDS_GROUP_KEY,
			],
		];
	}
}
