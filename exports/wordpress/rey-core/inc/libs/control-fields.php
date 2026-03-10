<?php
namespace ReyCore\Libs;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class ControlFields
{
	private $fields = [];
	private $config = [];

	public function __construct( $config = [] )
	{

		if( empty($config) ){
			return;
		}

		$this->config = $config;

		if( isset($config['fields']) && ! empty($config['fields']) ){
			foreach ($config['fields'] as $field) {
				$this->add_control($field);
			}
		}

	}

	function control_name($args){

		if( empty($args['name']) ){
			$args['name'] = $args['id_prefix'] . sanitize_title($args['label']);
		}

		if( isset($this->config['prefix']) ){
			return $this->config['prefix'] . $args['name'];
		}

		return $args['name'];
	}

	function add_control( $args = [] )
	{
		if( empty($args) ){
			return;
		}

		$args = wp_parse_args($args, [
			'name'          => '',
			'type'          => '',
			'label'         => '',
			'value'         => '',
			'conditions'    => [],
			'wrapper_class' => '',
			'field_class'   => 'widefat',
			'separator'     => '',
			'placeholder'   => '',
			'suffix'        => '',
			'id_prefix'     => 'rey-control-',
			'description'   => '',
		]);

		if( empty($args['type']) ){
			return;
		}

		if( isset( $this->config[ 'data' ] ) && $saved_data = $this->config[ 'data' ] ) {
			if( isset($saved_data[ $args['name'] ]) ){
				$args['value'] = $saved_data[ $args['name'] ];
			}
		}

		// Prefix field name
		$args['name'] = $this->control_name($args);

		if( isset( $_REQUEST[ $args['name'] ] ) ){
			$args['value'] = reycore__clean($_REQUEST[ $args['name'] ]);
		}

		// calc. separator
		if( !empty($args['separator']) ){
			$args['wrapper_class'] .= '--separator-' . $args['separator'];
		}

		$func = "__control_{$args['type']}";

		if( ! method_exists($this, $func) ){
			return;
		}

		printf('<div id="%1$s-wrapper" class="rey-control %3$s" %2$s>',
			$args['name'],
			!empty($args['conditions']) ? sprintf("data-rey-condition='%s'", wp_json_encode($args['conditions'])) : '',
			$args['wrapper_class']
		);

			$this->$func($args);

			if( $suffix = $args['suffix'] ){
				printf('<span class="__suffix">%s</span>', $suffix);
			}

			if( $description = $args['description'] ){
				printf('<p class="description">%s</p>', $description);
			}

		echo '</div>';

	}

	private function __control_heading($args) {
		printf('<h2 class="%s">%s</h2>', $args['field_class'], $args['label'] );
	}

	private function __control_number($args) {

		$field_id = $args['id_prefix'] . $args['name'];

		printf(
			'<label for="%1$s">%2$s</label>',
			$field_id,
			$args['label']
		);

		$attributes = [];

		if( isset($args['options']) && !empty($args['options']) ){

			if( isset($args['options']['step']) ){
				$attributes[] = sprintf('step="%s"', $args['options']['step']);
			}

			if( isset($args['options']['min']) ){
				$attributes[] = sprintf('min="%s"', $args['options']['min']);
			}

			if( isset($args['options']['max']) ){
				$attributes[] = sprintf('max="%s"', $args['options']['max']);
			}
		}

		if( $placeholder = $args['placeholder'] ){
			$attributes[] = sprintf('placeholder="%s"', $placeholder);
		}

		printf( '<input class="%4$s" type="number" id="%1$s" name="%2$s" value="%3$s" %5$s>',
			$field_id,
			$args['name'],
			$args['value'],
			$args['field_class'],
			implode(' ', $attributes)
		);
	}


	private function __control_select($args)
	{
		$field_id = $args['id_prefix'] . $args['name'];

		printf(
			'<label for="%1$s">%2$s</label>',
			$field_id,
			$args['label']
		);

		$is_multiple = isset( $args['multiple'] ) && $args['multiple'];

		$options = '';

		if( isset($args['options']) && !empty($args['options']) ){
			foreach ($args['options'] as $key => $value) {

				if( $is_multiple ){
					$is_selected = in_array( $key, $args['value'], true ) ? 'selected' : '';
				}
				else {
					$is_selected = selected( $args['value'], $key, false);
				}

				$options .= sprintf('<option value="%1$s" %3$s>%2$s</option>', $key, $value, $is_selected );
			}
		}

		printf( '<select class="%4$s" id="%1$s" name="%2$s" %5$s>%3$s</select>',
			$field_id,
			$args['name'] . ( $is_multiple ? '[]' : '' ),
			$options,
			$args['field_class'],
			$is_multiple ? 'multiple' : ''
		);

	}

}
