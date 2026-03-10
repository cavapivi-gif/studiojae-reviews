<?php
namespace ReyCore\Modules\ProductQuantity;

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
				'key' => 'field_610e923f36f9',
				'label' => esc_html_x('Quantity Min/Max', 'Backend setting label', 'rey-core'),
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
				'key' => 'field_610ef36f92379',
				'label' => esc_html_x('Quantity Options', 'Backend setting label', 'rey-core'),
				'name' => 'quantity_options',
				'type' => 'group',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => [
					'width' => '',
					'class' => '--clean-table',
					'id' => '',
				],
				'layout' => 'table',
				'parent' => self::FIELDS_GROUP_KEY,
				'sub_fields' => [
					[
						'key' => 'field_610ef41f9237a',
						'label' => esc_html_x('Minimum', 'Backend setting label', 'rey-core'),
						'name' => 'minimum',
						'type' => 'number',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
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
					],
					[
						'key' => 'field_610ef4659237b',
						'label' => esc_html_x('Maximum', 'Backend setting label', 'rey-core'),
						'name' => 'maximum',
						'type' => 'number',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
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
					],
					[
						'key' => 'field_610ef4749237c',
						'label' => esc_html_x('Step', 'Backend setting label', 'rey-core'),
						'name' => 'step',
						'type' => 'number',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
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
					],
				],
			],

		];
	}
}
