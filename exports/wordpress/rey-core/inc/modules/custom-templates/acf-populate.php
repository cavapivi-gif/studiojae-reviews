<?php
namespace ReyCore\Modules\CustomTemplates;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class AcfPopulate
{

	public function __construct()
	{
		add_filter('acf/prepare_field/key=field_5f2d2f38ffab0', [$this, 'advanced__add_template_types']);
		add_filter('acf/prepare_field/key=field_5eca52213daa7', [$this, 'products__add_attributes']);
		add_filter('acf/prepare_field/key=field_5eca925bbfd13', [$this, 'product_archives__add_attributes']);
		add_filter('acf/prepare_field/key=field_5ecd817164c3d', [$this, 'single__add_post_types']);
		add_filter('acf/load_field/key=field_5eca76648cf2d', [$this, 'single__add_custom_post_types'] );
		add_filter('acf/load_field/key=field_5f2c3195e038d', [$this, 'archives__add_cpt_taxonomies__location'] );
		add_filter('acf/load_field/key=field_5f2c3195e038a', [$this, 'archives__add_cpt_taxonomies'] );
	}

	/**
	 * Add template types option
	 *
	 * @since 2.1.0
	 **/
	function advanced__add_template_types($field)
	{
		if( function_exists('rey__page_templates') ){
			$field['choices'] = [
				'' => esc_html__('Default', 'rey-core')
			] + rey__page_templates();
		}

		return $field;
	}

	/**
	 * Add attribute to lists
	 *
	 * @since 2.1.0
	 **/
	function products__add_attributes($field)
	{
		if( function_exists('reycore_wc__get_all_attributes_terms') ){
			$field['choices'] = reycore_wc__get_all_attributes_terms();
		}
		return $field;
	}

	/**
	 * Add public attributes to archive condition attribute list
	 *
	 * @since 2.1.0
	 **/
	function product_archives__add_attributes($field)
	{

		if( function_exists('wc_get_attribute_taxonomies') ){
			foreach( wc_get_attribute_taxonomies() as $attribute ) {

				if( $attribute->attribute_public){
					$taxonomy = wc_attribute_taxonomy_name( $attribute->attribute_name );
					$field['choices'][$taxonomy] = ucfirst($attribute->attribute_label);
				}

			}
		}

		return $field;
	}

	/**
	 * Add post types into single condition location list
	 *
	 * @since 2.1.0
	 **/
	function single__add_post_types($field)
	{
		if( \ReyCore\ACF\Helper::is_exporting() ){
			return $field;
		}

		if( $post_types = reycore_rt__get_post_types() ){
			$field['choices'] = $post_types;
		}

		return $field;
	}

	/**
	 * Adding CPT posts & taxonomies to single posts conditions lists
	 *
	 * @since 2.1.0
	 */
	function single__add_custom_post_types( $field ) {

		if( \ReyCore\ACF\Helper::is_exporting() ){
			return $field;
		}

		if( get_post_type() === 'acf-field-group' ){
			return $field;
		}

		if( ! isset($field['sub_fields']) ){
			return $field;
		}

		$ctps = reycore_rt__get_cpt();

		if( empty($ctps) ){
			return $field;
		}

		foreach ($ctps as $key => $ctp) {

			$conditional_logic = [
				// single
				[
					// template type
					[
						'field' => 'field_5eca340a16793',
						'operator' => '==',
						'value' => 'single',
					],
					// post type
					[
						'field' => 'field_5ecd817164c3d',
						'operator' => '==',
						'value' => $ctp['post_type'],
					],
				],
			];

			// Posts
			$field['sub_fields'][] = [
				'key'               => 'field_' . $ctp['post_field_name'],
				'name'              => $ctp['post_field_name'],
				'_name'             => $ctp['post_field_name'],
				'choices'           => $ctp['post_choices'],
				'label'             => sprintf(esc_html__('%s posts', 'rey-core'), $ctp['post_type_title'] ),
				'type'              => 'select',
				'ID'                => '',
				'parent'		    => '',
				'class'             => '',
				'instructions'      => esc_html__('Select specific posts.', 'rey-core'),
				'conditional_logic' => $conditional_logic,
				'wrapper' => [
					'width' => '',
					'class' => '',
					'id' => '',
				],
				'required' => 0,
				'allow_null' => 1,
				'multiple' => 1,
				'return_format' => 'value',
				'ui' => 1,
				'ajax' => 0,
				'placeholder' => esc_html__('- Select posts -', 'rey-core'),
			];

			// Taxonomies
			foreach ($ctp['taxonomies'] as $tx) {
				$field['sub_fields'][] = [
					'key'           => 'field_' . $tx['tax_field_name'],
					'name'          => $tx['tax_field_name'],
					'_name'         => $tx['tax_field_name'],
					'label'         => sprintf(esc_html__('%s taxonomy', 'rey-core'), $tx['tax_label'] ),
					'choices'       => $tx['tax_choices'],
					'type'          => 'checkbox',
					'default_value' => [],
					'ID'            => '',
					'parent'		=> '',
					'class'         => '',
					'instructions'  => esc_html__('Select specific taxonomies.', 'rey-core'),
					'conditional_logic' => $conditional_logic,
					'wrapper' => [
						'width' => '',
						'class' => '',
						'id' => '',
					],
					'return_format' => 'value',
					'allow_null' => 1,
					'required' => 0,
					'save_custom' => 0,
				];

			}
		}

		return $field;
	}

	/**
	 * Adding CPT taxonomies to archive taxonomies location list
	 *
	 * @since 2.1.0
	 */
	function archives__add_cpt_taxonomies__location( $field ) {

		if( \ReyCore\ACF\Helper::is_exporting() ){
			return $field;
		}

		if( get_post_type() === 'acf-field-group' ){
			return $field;
		}

		$ctps = reycore_rt__get_cpt();

		if( !is_array($ctps) ){
			return $field;
		}

		foreach ($ctps as $key => $ctp) {

			if( !isset($ctp['taxonomies']) ){
				continue;
			}

			foreach ($ctp['taxonomies'] as $tx) {
				$field['choices'][$tx['tax_name']] = $tx['tax_label'] . ' ( ' . $ctp['post_type_title'] . ' )';
			}
		}

		return $field;
	}

	/**
	 * Adding CPT taxonomies to archive conditions
	 *
	 * @since 2.1.0
	 */
	function archives__add_cpt_taxonomies( $field ) {

		if( \ReyCore\ACF\Helper::is_exporting() ){
			return $field;
		}

		if( get_post_type() === 'acf-field-group' ){
			return $field;
		}

		if( ! isset($field['sub_fields']) ){
			return $field;
		}

		$ctps = reycore_rt__get_cpt();

		if( empty($ctps) ){
			return $field;
		}

		foreach ($ctps as $key => $ctp) {

			// Taxonomies
			foreach ($ctp['taxonomies'] as $tx) {

				$field['sub_fields'][] = [
					'key'           => 'field_' . $tx['tax_field_name'],
					'name'          => $tx['tax_field_name'],
					'_name'         => $tx['tax_field_name'],
					'label'         => sprintf(esc_html__('%s taxonomy', 'rey-core'), $tx['tax_label'] ),
					'choices'       => $tx['tax_choices'],
					'type'          => 'checkbox',
					'default_value' => [],
					'ID'            => '',
					'parent'		=> '',
					'class'         => '',
					'instructions'  => esc_html__('Select specific taxonomies.', 'rey-core'),
					'conditional_logic' => [
						[
							// template type
							[
								'field' => 'field_5eca340a16793',
								'operator' => '==',
								'value' => 'archive',
							],
							// post type
							[
								'field' => 'field_5f2c3195e038d',
								'operator' => '==',
								'value' => $tx['tax_name'],
							],
						],
					],
					'wrapper' => [
						'width' => '',
						'class' => '',
						'id' => '',
					],
					'return_format' => 'value',
					'allow_null' => 1,
					'required' => 0,
					'save_custom' => 0,
				];

			}
		}

		return $field;
	}


}
