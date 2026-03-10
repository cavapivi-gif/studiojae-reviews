<?php
namespace ReyCore\Modules\AjaxFilters;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Helpers
{

	const CUSTOM_KEYS_OPTION_KEY = 'rey_filters_custom_keys_map';

	const TRANSIENT_KEY_CF_VALUES = 'rey_filters_cf_';

	public function __construct(){}

	public static function get_custom_keys(){

		static $opt;

		if( is_null($opt) ){
			$opt = array_filter((array) get_option(self::CUSTOM_KEYS_OPTION_KEY, []));
		}

		return $opt;
	}

	public static function save_custom_key( $args = [] ){

		$args = wp_parse_args($args, [
			'new_instance' => [],
			'old_instance' => [],
			'taxonomy'     => '',
			'cf'           => '',
		]);

		// value is added
		if( ! empty($args['new_instance']['key_name']) ){

			$data = self::get_custom_keys();
			$key = sanitize_title( $args['new_instance']['key_name'] );

			// if renaming
			// unset the previous one
			if( ! empty($args['old_instance']['key_name']) ){
				$old_key = sanitize_title( $args['old_instance']['key_name'] );
				unset($data[$old_key]);
			}

			if( ! empty($args['taxonomy']) ){
				$data_to_save = [
					'type' => 'taxonomy',
					'value' => $args['taxonomy']
				];
			}
			else if( ! empty($args['cf']) ){
				$data_to_save = [
					'type' => 'cf',
					'value' => $args['cf']
				];
			}

			$data[ $key ] = $data_to_save;

			update_option(self::CUSTOM_KEYS_OPTION_KEY, $data, false);
		}

		// it's empty
		else {
			// however it seems that it's purposely emptied, to disable
			// if so, just remove from custom keys option
			if( ! empty($args['old_instance']['key_name']) ){
				if( $data = self::get_custom_keys() ){
					$old_key = sanitize_title( $args['old_instance']['key_name'] );
					unset($data[$old_key]);
					update_option(self::CUSTOM_KEYS_OPTION_KEY, $data, false);
				}
			}
		}

	}

	public static function get_meta_query( $data = [] ){

		if( empty($data) ){
			return [];
		}

		$current_meta_query = [
			'key'           => reycore__clean($data['key']),
			'value'         => reycore__clean($data['value']),
			'compare'       => reycore__clean($data['operator']),
		];

		switch($data['operator']):

			// Is not empty
			case "!=empty":
				$current_meta_query['compare'] = '!=';
				$current_meta_query['value'] = '';
				break;

			// Is empty
			case "==empty":
				$current_meta_query['compare'] = '=';
				$current_meta_query['value'] = '';
				break;

			case "==":
				$current_meta_query['compare'] = '=';
				break;

			case "!=":
				$current_meta_query['compare'] = '!=';
				break;

			case ">":
				$current_meta_query['type'] = 'DECIMAL';
				break;

			case "<":
				$current_meta_query['type'] = 'DECIMAL';
				break;

		endswitch;

		return $current_meta_query;
	}


	/**
	 * Get Registered meta query by hash
	 *
	 * @since 1.9.4
	 **/
	public static function get_registered_meta_query( $hash )
	{
		return self::get_meta_query( self::get_registered_meta_query_data($hash) );
	}

	/**
	 * Get Registered meta query by hash
	 *
	 * @since 1.9.4
	 **/
	public static function get_registered_meta_query_data( $hash )
	{
		$registered_mq = get_theme_mod('ajaxfilters_meta_queries', []);
		$data = [];

		foreach($registered_mq as $mq){

			$registered_hash = substr( md5( wp_json_encode( $mq ) ), 0, 10 );

			if( $registered_hash !== $hash ){
				continue;
			}

			$data = $mq;
		}

		return $data;
	}

	/**
	 * Retrieve mapped custom fields values, sanitized => non-sanitized ,
	 * in order to compare them in the query's for custom fields
	 *
	 * @param string $key
	 * @return array
	 */
	public static function get_meta_converted_values( $key ){

		$meta_terms = [];

		if( false === ($meta_terms = get_transient( self::TRANSIENT_KEY_CF_VALUES . $key )) ){

			$meta_terms = [];

			// Get most recent product IDs in date descending order.
			$query = new \WC_Product_Query( array(
				'limit'        => -1,
				'post_status'  => 'publish',
				'return'       => 'ids',
				'meta_key'     => $key,
				'meta_compare' => 'EXISTS',
				'fields'       => 'ids',
			) );

			foreach ($query->get_products() as $id) {

				if( ! ($meta_value = get_post_meta($id, $key, true)) ){
					continue;
				}

				$meta_values = [];

				if( is_array($meta_value) ){
					$meta_values = array_merge( $meta_values, $meta_value );
				}
				else {
					$meta_values[] = $meta_value;
				}

				foreach ($meta_values as $mkey => $mvalue) {
					$meta_slug = sanitize_title($mvalue);
					$meta_terms[$meta_slug] = $mvalue;
				}

			}

			set_transient(self::TRANSIENT_KEY_CF_VALUES . $key, $meta_terms, MONTH_IN_SECONDS);
		}

		return $meta_terms;
	}

	public static function update_widgets($args){

		$instance = [];

		$args = wp_parse_args($args, [
			'widget' => null,
			'new_instance' => [],
			'sanitization' => 'reycore__clean',
			'custom_sanitizations' => [
				'key_name' => 'sanitize_title'
			],
		]);

		if( ! $args['widget'] ){
			return;
		}

		if( ! isset($args['widget']->defaults) ){
			return $instance;
		}

		foreach ($args['widget']->defaults as $key => $value) {

			$instance[$key] = $value;

			if( isset($args['custom_sanitizations'][$key]) ){
				$args['sanitization'] = $args['custom_sanitizations'][$key];
			}

			if( isset($args['new_instance'][$key]) ){
				$instance[$key] = $args['sanitization']( $args['new_instance'][$key] );
			}
		}

		return $instance;
	}
}
