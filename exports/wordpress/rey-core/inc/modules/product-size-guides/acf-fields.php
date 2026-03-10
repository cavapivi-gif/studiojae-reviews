<?php
namespace ReyCore\Modules\ProductSizeGuides;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class AcfFields {

	const FIELDS_GROUP_KEY = 'group_5d4ff536a2684';

	public function __construct(){

		if( ! function_exists('acf_add_local_field') ){
			return;
		}

		$this->guide_fields();

		foreach ($this->pdp_fields() as $key => $field) {
			acf_add_local_field($field);
		}

		add_filter('acf/prepare_field/key=field_6431a05890657', [$this, 'add_attributes']);

	}

	public function pdp_fields(){
		return [
			[
				'key' => 'field_6431b4e4e332d',
				'label' => esc_html_x('Size Guides', 'Backend setting label', 'rey-core'),
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
			array(
				'key' => 'field_6431b46fe332c',
				'label' => esc_html_x('Size Guide Display', 'Backend setting label', 'rey-core'),
				'name' => 'pdp_size_guide_display',
				'aria-label' => '',
				'type' => 'select',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '--size-1',
					'id' => '',
				),
				'choices' => array(
					'' => esc_html_x('- Inherit -', 'Backend setting label', 'rey-core'),
					'show' => esc_html_x('Show', 'Backend setting label', 'rey-core'),
					'hide' => esc_html_x('Hide', 'Backend setting label', 'rey-core'),
				),
				'default_value' => false,
				'return_format' => 'value',
				'multiple' => 0,
				'allow_null' => 0,
				'ui' => 0,
				'ajax' => 0,
				'placeholder' => '',
				'parent' => self::FIELDS_GROUP_KEY,
			),
			array(
				'key' => 'field_6431b439e332b',
				'label' => esc_html_x('Select Size Guide', 'Backend setting label', 'rey-core'),
				'name' => 'pdp_select_size_guide',
				'aria-label' => '',
				'type' => 'post_object',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'field_6431b46fe332c',
							'operator' => '==',
							'value' => 'show',
						),
					),
				),
				'wrapper' => array(
					'width' => '',
					'class' => '--size-2',
					'id' => '',
				),
				'post_type' => array(
					0 => Base::POST_TYPE,
				),
				'taxonomy' => '',
				'return_format' => 'id',
				'multiple' => 0,
				'allow_null' => 0,
				'ui' => 1,
				'parent' => self::FIELDS_GROUP_KEY,
			),
		];
	}

	public function guide_fields(){

		acf_add_local_field_group(array(
			'key' => 'group_64319f847f540',
			'title' => 'Select where to display the Guide',
			'fields' => array(
				array(
					'key' => 'field_64319f8590656',
					'label' => esc_html_x('Product categories', 'Backend setting label', 'rey-core'),
					'name' => 'guides_product_categories',
					'aria-label' => '',
					'type' => 'taxonomy',
					'instructions' => esc_html_x('Assign this guide to products belonging to the selected categories.', 'Backend setting description', 'rey-core'),
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'taxonomy' => 'product_cat',
					'add_term' => 0,
					'save_terms' => 0,
					'load_terms' => 0,
					'return_format' => 'id',
					'field_type' => 'multi_select',
					'allow_null' => 1,
					'multiple' => 0,
				),
				array(
					'key' => 'field_6431a05a90658',
					'label' => esc_html_x('Product Tags', 'Backend setting label', 'rey-core'),
					'name' => 'guides_product_tags',
					'aria-label' => '',
					'type' => 'taxonomy',
					'instructions' => esc_html_x('Assign this guide to products belonging to the selected product tags.', 'Backend setting description', 'rey-core'),
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'taxonomy' => 'product_tag',
					'add_term' => 0,
					'save_terms' => 0,
					'load_terms' => 0,
					'return_format' => 'id',
					'field_type' => 'select',
					'allow_null' => 1,
					'multiple' => 0,
				),
				array(
					'key' => 'field_6431a05890657',
					'label' => esc_html_x('Product Attributes', 'Backend setting label', 'rey-core'),
					'name' => 'guides_product_attributes',
					'aria-label' => '',
					'type' => 'select',
					'instructions' => esc_html_x('Assign this guide to products belonging to the selected product attributes.', 'Backend setting description', 'rey-core'),
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'choices' => array(
					),
					'default_value' => array(
					),
					'return_format' => 'value',
					'multiple' => 1,
					'allow_null' => 0,
					'ui' => 1,
					'ajax' => 0,
					'placeholder' => '',
				),
				array(
					'key' => 'field_6431a0db90659',
					'label' => esc_html_x('Products', 'Backend setting label', 'rey-core'),
					'name' => 'guides_product_products',
					'aria-label' => '',
					'type' => 'post_object',
					'instructions' => esc_html_x('Assign this guide to selected products.', 'Backend setting description', 'rey-core'),
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'post_type' => array(
						0 => 'product',
					),
					'taxonomy' => '',
					'return_format' => 'id',
					'multiple' => 1,
					'allow_null' => 0,
					'ui' => 1,
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'post_type',
						'operator' => '==',
						'value' => Base::POST_TYPE,
					),
				),
			),
			'menu_order' => 0,
			'position' => 'side',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
			'active' => true,
			'description' => '',
			'show_in_rest' => 0,
		));

		acf_add_local_field_group(array(
			'key' => 'group_6433224b2042c',
			'title' => 'Tables Content',
			'fields' => array(
				array(
					'key' => 'field_643322c0cab7c',
					'label' => esc_html_x('Hide Tables', 'Backend setting label', 'rey-core'),
					'name' => 'rey_guides_hide_tables',
					'aria-label' => '',
					'type' => 'true_false',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'message' => 'In case you want to manually display tables with ACF Table Elementor widget',
					'default_value' => 0,
					'ui_on_text' => '',
					'ui_off_text' => '',
					'ui' => 1,
				),
				array(
					'key' => 'field_6433224ccab7b',
					'label' => esc_html_x('Display as', 'Backend setting label', 'rey-core'),
					'name' => 'rey_guides_display_as',
					'aria-label' => '',
					'type' => 'button_group',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => array(
						array(
							array(
								'field' => 'field_643322c0cab7c',
								'operator' => '!=',
								'value' => '1',
							),
						),
					),
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'choices' => array(
						'stacked' => esc_html_x('Stacked', 'Backend setting label', 'rey-core'),
						'tabs' => esc_html_x('Tabs', 'Backend setting label', 'rey-core'),
					),
					'default_value' => 'stacked',
					'return_format' => 'value',
					'allow_null' => 0,
					'layout' => 'horizontal',
				),
				array(
					'key' => 'field_6433230ecab7d',
					'label' => esc_html_x('Size Guides', 'Backend setting label', 'rey-core'),
					'name' => 'rey_guides_tables',
					'aria-label' => '',
					'type' => 'repeater',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'layout' => 'block',
					'pagination' => 0,
					'min' => 0,
					'max' => 0,
					'collapsed' => '',
					'button_label' => 'Add Table',
					'rows_per_page' => 20,
					'sub_fields' => array(
						array(
							'key' => 'field_64332332cab7e',
							'label' => esc_html_x('Table name', 'Backend setting label', 'rey-core'),
							'name' => 'table_name',
							'aria-label' => '',
							'type' => 'text',
							'instructions' => esc_html_x('Useful to show the Table name caption or as Tab name.', 'Backend setting description', 'rey-core'),
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '--size-2',
								'id' => '',
							),
							'default_value' => '',
							'maxlength' => '',
							'placeholder' => 'ex: INCH',
							'prepend' => '',
							'append' => '',
							'parent_repeater' => 'field_6433230ecab7d',
						),
						array(
							'key' => 'field_643323bdcab7f',
							'label' => esc_html_x('Table data', 'Backend setting label', 'rey-core'),
							'name' => 'table_data',
							'aria-label' => '',
							'type' => 'table',
							'instructions' => esc_html_x('Please insert the size guide data in this table.', 'Backend setting description', 'rey-core'),
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'use_header' => 1,
							'use_caption' => 2,
							'parent_repeater' => 'field_6433230ecab7d',
						),
					),
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'post_type',
						'operator' => '==',
						'value' => Base::POST_TYPE,
					),
				),
			),
			'menu_order' => 0,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
			'active' => true,
			'description' => '',
			'show_in_rest' => 0,
		));

	}


	/**
	 * Add attribute to lists
	 *
	 * @since 2.1.0
	 **/
	function add_attributes($field)
	{
		if( function_exists('reycore_wc__get_all_attributes_terms') ){
			$field['choices'] = reycore_wc__get_all_attributes_terms();
		}
		return $field;
	}

}
