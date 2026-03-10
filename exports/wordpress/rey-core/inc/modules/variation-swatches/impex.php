<?php
namespace ReyCore\Modules\VariationSwatches;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Impex
{

	private $column_key = 'rey_swatches';
	private $column_name = 'Rey Swatches';

	public $fields = [
		'rey_attribute_color',
		'rey_attribute_color_secondary',
		'rey_attribute_image',
	];

	public function __construct() {

		add_filter( "woocommerce_product_export_product_default_columns", [ $this, 'export__column_name' ] );
		add_filter( "woocommerce_product_export_product_column_{$this->column_key}", [ $this, 'export__column_data' ], 10, 3 );
		add_filter( 'woocommerce_csv_product_import_mapping_options', [ $this, 'import__column_name' ] );
		add_filter( 'woocommerce_csv_product_import_mapping_default_columns', [ $this, 'import__default_column_name' ] );
		add_action( 'woocommerce_product_import_inserted_product_object', [ $this, 'import__process' ], 10, 2 );

	}

	public function export__column_name( $columns ) {
		$columns[ $this->column_key ] = $this->column_name;
		return $columns;
	}

	public function export__column_data( $value, $product, $key ) {

		$attributes = $product->get_attributes();

		if ( ! count( $attributes ) ) {
			return '';
		}

		$types = [];

		foreach ( $attributes as $attribute_name => $attribute ) {

			if ( ! is_a( $attribute, 'WC_Product_Attribute' ) ) {
				continue;
			}

			if ( ! $attribute->is_taxonomy() ) {
				continue;
			}

			$name  = wc_attribute_label( $attribute->get_name(), $product );

			if ( in_array( $name, $types, true ) ) {
				continue;
			}

			$attr  = wc_get_attribute( $attribute->get_id() );

			if( $attr->type === 'select' ){
				continue;
			}

			$types[ $name ] = [
				'name'  => $name,
				'type'  => $attr->type,
				'terms' => [],
			];

			$terms = $attribute->get_terms();

			foreach ( $terms as $term ) {

				$types[ $name ][ 'terms' ][ $term->name ] = [
					'name' => $term->name
				];

				foreach ($this->fields as $field) {

					if( ! ($field_data = get_field($field, $term)) ){
						continue;
					}

					// get url for images
					if( 'rey_attribute_image' === $field ){
						if( $image_id = wp_get_attachment_image_url( absint($field_data), 'full' ) ){
							$types[ $name ][ 'terms' ][ $term->name ][ $field ] = $image_id;
						}
					}
					// just grab the color
					else {
						$types[ $name ][ 'terms' ][ $term->name ][ $field ] = sanitize_hex_color( $field_data );
					}
				}
			}
		}

		return !empty($types) ? wp_json_encode( $types ) : '';
	}

	public function import__column_name( $columns ) {
		$columns[ $this->column_key ] = $this->column_key;
		return $columns;
	}

	public function import__default_column_name( $columns ) {
		$columns[ $this->column_name ] = $this->column_key;
		return $columns;
	}

	public function import__process( $product, $data ) {

		$product_id = $product->get_id();

		if ( ! (isset( $data[ $this->column_key ] ) && ! empty( $data[ $this->column_key ] )) ) {
			return;
		}

		$raw_data = (array) json_decode( $data[ $this->column_key ], true );

		$__taxonomy = [];
		$__terms    = [];

		foreach ( $raw_data as $attr_name => $attr ) {

			$id       = wc_attribute_taxonomy_id_by_name( $attr_name );
			$taxonomy = wc_attribute_taxonomy_name( $attr_name );

			if ( in_array( $id, $__taxonomy ) ) {
				continue;
			}

			if ( ! $id ) {
				continue;
			}

			array_push( $__taxonomy, $id );

			wc_update_attribute( $id, [
				'type' => $attr[ 'type' ]
			] );

			foreach ( $attr[ 'terms' ] as $term_name => $term_data ) {

				$term = get_term_by( 'name', $term_name, $taxonomy );

				if ( in_array( $id, $__terms ) ) {
					continue;
				}

				if ( $term ) {

					array_push( $__terms, $term->term_id );

					foreach ($this->fields as $field) {

						if( ! (isset( $term_data[ $field ] ) && $field_data = $term_data[ $field ]) ){
							continue;
						}

						if( 'rey_attribute_image' === $field ){
							$field_data = attachment_url_to_postid( $field_data );
						}
						else {
							$field_data = sanitize_hex_color( $field_data );
						}

						update_field( $field, $field_data, $term );
					}
				}
			}
		}
	}

}
