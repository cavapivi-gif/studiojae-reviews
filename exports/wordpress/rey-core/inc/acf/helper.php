<?php
namespace ReyCore\ACF;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use ReyCore\Plugin;

class Helper {

	const DEFAULT_ACF_FORMAT = '%name%';
	const SEPARATOR_REP = '|';

	public static $default_supported_singular_post_types = [];

	public function __construct(){
		add_filter( 'reycore/query-control/autocomplete', [$this, 'query_control_autocomplete'], 10, 2);
		add_filter( 'reycore/query-control/values', [$this, 'query_control_values'], 10, 2);
	}

	public static function get_acf_fields( $types, $format ){

		if ( function_exists( 'acf_get_field_groups' ) ) {
			$acf_groups = acf_get_field_groups();
		}

		$default_types = [
			'text',
			'textarea',
			'number',
			'email',
			'wysiwyg',
			'select',
			'checkbox',
			'radio',
			'true_false',
			'oembed',
			'google_map',
			'date_picker',
			'time_picker',
			'date_time_picker',
			'color_picker',
			'image',
		];

		$options = [];
		$opg_ids = []; // options page groups ids

		if ( function_exists( 'acf_options_page' ) ) {

			$pages = acf_options_page()->get_pages();

			foreach ( $pages as $slug => $page ) {

				$options_page_groups = acf_get_field_groups( [
					'options_page' => $slug,
				] );

				foreach ( $options_page_groups as $options_page_group ) {
					$opg_ids[ $options_page_group['key'] ] = $page['post_id'];
				}

			}
		}

		foreach ( $acf_groups as $acf_group ) {

			if ( function_exists( 'acf_get_fields' ) ) {
				if ( isset( $acf_group['ID'] ) && ! empty( $acf_group['ID'] ) ) {
					$fields = acf_get_fields( $acf_group['ID'] );
				} else {
					$fields = acf_get_fields( $acf_group );
				}
			}

			if ( ! is_array( $fields ) ) {
				continue;
			}

			foreach ( $fields as $field ) {

				if( in_array( 'repeater', $types, true ) && 'repeater' === $field['type'] && isset( $field['sub_fields'] ) && ! empty($field['sub_fields']) ){

					foreach ($field['sub_fields'] as $subfield) {

						$key = self::get_field_options([
							'field'        => $subfield,
							'types'        => $types,
							'format'       => $format,
							'opg_ids'      => $opg_ids,
							'parent_field' => $field,
						]);

						if( $key ){
							$options[ $key ] = sprintf('%2$s &rsaquo; %3$s (%1$s)', $acf_group['title'], $field['label'], $subfield['label']);
						}
					}
				}

				$key = self::get_field_options([
					'field'   => $field,
					'types'   => $types,
					'format'  => $format,
					'opg_ids' => $opg_ids,
				]);

				if( $key ){
					$options[ $key ] = sprintf('%2$s (%1$s)', $acf_group['title'], $field['label']);
				}

			}

			if ( empty( $options ) ) {
				continue;
			}

		}

		return $options;

	}

	public static function get_field_options( $args ){

		if ( ! in_array( $args['field']['type'], $args['types'], true ) ) {
			return;
		}

		$field_name = $args['field']['name'];

		if( isset($args['parent_field']) && 'repeater' === $args['parent_field']['type'] ){
			$field_name = implode(self::SEPARATOR_REP, [$args['parent_field']['name'], $field_name]);
		}

		$key = str_replace(['%name%', '%key%'], [$field_name, $args['field']['key']], $args['format']);

		// check for options page
		if( array_key_exists($args['field']['parent'], $args['opg_ids']) ){
			$key .= ':' . $args['opg_ids'][$args['field']['parent']];
		}

		return $key;
	}

	function query_control_values($results, $data){

		if( ! isset($data['query_args']['type']) ){
			return $results;
		}

		if( $data['query_args']['type'] !== 'acf' ){
			return $results;
		}

		$field_types = isset($data['query_args']['field_types']) ? $data['query_args']['field_types'] : [];
		$format = isset($data['query_args']['format']) ? $data['query_args']['format'] : self::DEFAULT_ACF_FORMAT;
		$fields = self::get_acf_fields( $field_types, $format );

		foreach ((array) $data['values'] as $id) {
			if( isset($fields[$id]) ){
				$results[ $id ] = $fields[$id];
			}
		}

		return $results;
	}

	function query_control_autocomplete($results, $data){

		if( ! isset($data['query_args']['type']) ){
			return $results;
		}

		if( $data['query_args']['type'] !== 'acf' ){
			return $results;
		}

		$field_types = isset($data['query_args']['field_types']) ? $data['query_args']['field_types'] : [];
		$format = isset($data['query_args']['format']) ? $data['query_args']['format'] : self::DEFAULT_ACF_FORMAT;
		$fields = self::get_acf_fields( $field_types, $format );

		foreach( $fields as $id => $text ){
			if( strpos($id, $data['q']) !== false || strpos(strtolower($text), strtolower($data['q'])) !== false ){
				$results[] = [
					'id' 	=> $id,
					'text' 	=> $text,
				];
			}
		}

		return $results;
	}

	/**
	 * Check if is exporting
	 *
	 * @since 1.6.10
	 **/
	public static function is_exporting()
	{
		if( isset($_REQUEST['page']) && $_REQUEST['page'] === 'acf-tools' && isset($_REQUEST['tool']) && $_REQUEST['tool'] === 'export' ){
			return true;
		}
		if( isset($_REQUEST['page']) && $_REQUEST['page'] === 'acf-tools' && isset($_REQUEST['action']) && $_REQUEST['action'] === 'download' ){
			return true;
		}
		return false;
	}

	/**
	 * Check if editing group
	 *
	 * @since 1.6.10
	 **/
	public static function is_editing_group()
	{
		return (get_post_type() === 'acf-field-group' && isset($_REQUEST['action']) && $_REQUEST['action'] === 'edit') ||
			(isset($_REQUEST['page']) && $_REQUEST['page'] === 'acf-tools');
	}

	/**
	 * Check if editing group
	 *
	 * @since 1.7.3
	 **/
	public static function prevent_export_dynamic_field() {
		return self::is_exporting() || self::is_editing_group();
	}

	/**
	 * ACF Image ID to ELementor Media control ID & URL
	 *
	 * @since 1.0.0
	 **/
	public static function image_to_elementor_image( $image_id )
	{
		$url = '';

		if( $image_id ){
			$url_array = wp_get_attachment_image_src( absint( $image_id ), 'full' );
			if( isset($url_array[0]) ){
				$url = $url_array[0];
			}
		}

		return [
			'url' => $url,
			'id' => $image_id ? absint( $image_id ) : ''
		];
	}

	/**
	 * Populate ACF's supported post types for Page Settings
	 *
	 * @since 1.6.6
	 */
	public static function default_supported_singular_post_types( $t = '' ){

		if( empty( self::$default_supported_singular_post_types ) ){

			$types = [
				'post_type' => [ 'post', 'page', 'product', 'rey-templates' ],
				'taxonomy' => [ 'category', 'product_cat', 'product_tag', 'pa_brand' ],
			];

			// automatically add product attributes which are public
			if( function_exists('wc_get_attribute_taxonomies') ):
				foreach ( wc_get_attribute_taxonomies() as $attribute ) {
					if( (bool) $attribute->attribute_public ){
						if( ($attr_name = wc_attribute_taxonomy_name($attribute->attribute_name)) && ! in_array($attr_name, $types['taxonomy'], true) ){
							$types['taxonomy'][] = $attr_name;
						}
					}
				}
			endif;

			self::$default_supported_singular_post_types = $types;

		}
		else {
			$types = self::$default_supported_singular_post_types;
		}

		if( isset($types[$t]) ){
			return $types[$t];
		}

		return $types;
	}

	public static function get_container_padding_placeholders( $edge = 'top' ){

		$prop = 'padding-';

		$defaults = [
			'top' => '50',
			'bottom' => '90',
		];

		if( ($cp = get_theme_mod('content_padding')) ){
			if( isset( $cp[ $prop . $edge ] ) && ($cp_edge = $cp[ $prop . $edge]) ){
				return $cp_edge;
			}
		}

		return $defaults[ $edge ];
	}

	public static function get_field_from_elementor( $args = [] ){

		$args = wp_parse_args($args, [
			'key'            => '',
			'multilanguage'  => false,
			'parse'          => false,
			'provider_aware' => false,
			'provider_key'   => '',
			'gs_aware'       => false,
			'index'          => null, // should start at 1 if enabled
			'parts_count'    => 2, // support ACF elements by providing 3 parts,
		]);

		if( ! $args['key'] ){
			return;
		}

		$include_provider_key = explode('/', $args['key']);
		if( count($include_provider_key) > 1 ){
			$args['key'] = $include_provider_key[0];
			$args['provider_key'] = $include_provider_key[1];
		}

		$args = apply_filters('reycore/acf/get_field_from_elementor/args', $args);

		$parts = explode(':', $args['key']);
		$field_name = $parts[0];

		if( $args['provider_key'] && isset($GLOBALS[$args['provider_key']]) && ($provider_post_id = $GLOBALS[$args['provider_key']]) ){
			$post_id = absint($provider_post_id);
		}

		// inside global section
		else if( $args['provider_aware'] && isset($GLOBALS['provider_post_id']) && ($provider_post_id = $GLOBALS['provider_post_id']) ){
			$post_id = absint(end($provider_post_id));
		}

		// inside global section
		else if( $args['gs_aware'] && isset($GLOBALS['global_section_ids']) && ($gs_ids = $GLOBALS['global_section_ids']) ){
			$post_id = end($gs_ids);
		}

		else {

			if( is_tax() ){
				$term = get_queried_object();
				if( ! empty($term->term_id) ){
					$post_id = $term->term_id;
					if( $args['multilanguage'] && reycore__is_multilanguage() ){
						$post_id = apply_filters('reycore/translate_ids', $post_id, $term->taxonomy );
					}
				}
				$post_id = get_term_by('term_taxonomy_id', $post_id);
			}

			else {
				$post_id = ($_pid = get_the_ID()) ? $_pid : get_queried_object_id();
				if( $args['multilanguage'] && reycore__is_multilanguage() ){
					$post_id = apply_filters('reycore/translate_ids', $post_id );
				}
			}

			// has option page
			if( count($parts) === $args['parts_count'] ){
				$post_id = $args['parts_count'] - 1;
			}

		}

		$subfield_name = '';

		// it's a repeater
		foreach ($parts as $value) {
			if(
				strpos($value, self::SEPARATOR_REP) !== false
				&& ($__parts = explode(self::SEPARATOR_REP, $value))
			){
				if( isset($__parts[1]) && ($sub = $__parts[1]) ){
					$field_name = $__parts[0];
					$subfield_name = $sub;
					break;
				}
			}
		}

		$data = get_field( $field_name, $post_id );

		$data = apply_filters('reycore/acf/get_field_from_elementor/data', $data, $post_id, $field_name, $args);

		if( $subfield_name && ! is_null($args['index']) && $args['index'] > 0 && isset($data[ $args['index'] - 1 ]) ){
			$data = $data[ $args['index'] - 1 ][ $subfield_name ];
		}

		if( $args['parse'] && ! (is_array($data) || is_bool($data)) ){
			return reycore__parse_text_editor($data);
		}

		return $data;
	}

	// Update the ACF group field sub-field value
	public static function update_group_sub_field($parent, $sub_field_name, $new_value, $post_id) {

		// Get the current group field values
		$group_field_values = get_field($parent, $post_id);

		// Update the sub-field value in the group field array
		$group_field_values[$sub_field_name] = $new_value;

		// Save the updated group field values
		return update_field($parent, $group_field_values, $post_id);
	}

	public static function get_table_html( $args = [] ){

		$args = wp_parse_args($args, [
			'table' => [],
			'css_class' => '',
			'caption' => '',
			'wrapper_id' => '',
			'wrapper_class' => '',
		]);

		$table = $args['table'];

		if( empty($table) ){
			return;
		}

		if( ! is_array($table) ){
			return;
		}

		$output = $head_items = $body = [];
		$head_count = 0;
		$body_count = 0;

		$keys = [
			'header' => 'h',
			'body' => 'b',
		];

		if( class_exists('\Elementor\Plugin') && \Elementor\Plugin::$instance->editor->is_edit_mode() ){
			$keys = [
				'header' => 'header',
				'body' => 'body',
			];
		}

		if ( ! empty( $args['caption'] ) ) {
			$output[] = '<caption>' . $args['caption'] . '</caption>';
		}

		if ( ! empty( $table[$keys['header']] ) ) {

			foreach ( (array) $table[$keys['header']] as $th ) {

				$cell = $th['c'];

				$head_items[] = sprintf('<th>%s</th>', $cell);

				if( $cell ){
					$head_count++;
				}

			}

			$output[] = sprintf('<thead><tr>%s</tr></thead>', implode('', $head_items));
		}

		if ( ! empty( $table[$keys['body']] ) ) {

			foreach ( (array) $table[$keys['body']] as $tr ) {

				$body_items = [];

				foreach ( $tr as $td ) {

					$cell = $td['c'];

					$body_items[] = sprintf('<td>%s</td>', $cell);

					if( $cell ){
						$body_count++;
					}

				}

				$body[] = sprintf('<tr>%s</tr>', implode('', $body_items));
			}

			if( ! empty($body) ){
				$output[] = sprintf('<tbody>%s</tbody>', implode('', $body));
			}

		}

		if( ! ($head_count && $body_count) ){
			return;
		}

		return sprintf('<div class="__table-container %s" data-id="%s"><table border="0" class="__table %s">%s</table></div>',
			$args['wrapper_class'],
			$args['wrapper_id'],
			$args['css_class'],
			implode('', $output)
		);

	}

	public static function get_table_field( $key, $post_id, $css_class = '' ){

		if ( ! ($table = get_field( $key, $post_id )) ) {
			return;
		}

		return self::get_table_html([
			'table' => $table,
			'css_class' => $css_class,
		]);
	}

	public static function admin_menu_item_is_hidden( $type ){

		static $links;

		if( is_null($links) ){
			$links = reycore__acf_get_field('hide_admin_links', REY_CORE_THEME_NAME);
		}

		if( $links ){
			return in_array($type, $links, true);
		}

		return false;
	}

}
