<?php
namespace ReyCore\Modules\EstimatedDelivery;

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

		add_filter('acf/prepare_field/name=estimated_delivery__days', [$this, 'prepend_delivery_days']);
		add_filter('acf/prepare_field/name=estimated_delivery__days_margin', [$this, 'append_delivery_days_margin']);

	}

	public function prepend_delivery_days($field){
		$field['prepend'] = sprintf(esc_html__('Global: %s', 'rey-core'), get_theme_mod('estimated_delivery__days', 3));
		return $field;
	}

	public function append_delivery_days_margin($field){
		if( $global = get_theme_mod('estimated_delivery__days_margin', '') ){
			$field['append'] = sprintf(esc_html__('Global: %s', 'rey-core'), $global);
		}
		return $field;
	}

	public function fields(){
		return [
			[
				'key' => 'field_5ebea4245eb8f',
				'label' => esc_html_x('Estimated Delivery', 'Backend setting label', 'rey-core'),
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
				'key' => 'field_604be57ae88dd',
				'label' => esc_html_x('Hide estimated delivery text', 'Backend setting label', 'rey-core'),
				'name' => 'estimated_delivery__hide',
				'type' => 'true_false',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => [
					'width' => '',
					'class' => '',
					'id' => '',
				],
				'message' => '',
				'default_value' => 0,
				'ui' => 1,
				'ui_on_text' => '',
				'ui_off_text' => '',
				'parent' => self::FIELDS_GROUP_KEY,
			],
			[
				'key' => 'field_5ebea4205eb8e',
				'label' => esc_html_x('Days', 'Backend setting label', 'rey-core'),
				'name' => 'estimated_delivery__days',
				'type' => 'number',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => [
					[
						[
							'field' => 'field_604be57ae88dd',
							'operator' => '!=',
							'value' => '1',
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
				'key' => 'field_5f5951eb0dcc9',
				'label' => esc_html_x('Margin', 'Backend setting label', 'rey-core'),
				'name' => 'estimated_delivery__days_margin',
				'type' => 'number',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => [
					[
						[
							'field' => 'field_604be57ae88dd',
							'operator' => '!=',
							'value' => '1',
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
				'key' => 'field_604be5abe88de',
				'label' => esc_html_x('Custom Text', 'Backend setting label', 'rey-core'),
				'name' => 'estimated_delivery__custom_text',
				'type' => 'text',
				'instructions' => esc_html_x('Replace default with custom text for this product. Please know that it replaces the entire text!', 'Backend setting description', 'rey-core'),
				'required' => 0,
				'conditional_logic' => [
					[
						[
							'field' => 'field_604be57ae88dd',
							'operator' => '!=',
							'value' => '1',
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
				'maxlength' => '',
				'parent' => self::FIELDS_GROUP_KEY,
			],
		];
	}
}
