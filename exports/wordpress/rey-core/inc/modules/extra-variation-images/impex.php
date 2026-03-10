<?php
namespace ReyCore\Modules\ExtraVariationImages;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Impex {

	private $export_type = 'product';

	private $key;

	private $column_name = 'Rey Variations extra images';

	public function __construct( $meta_key ) {

		// TODO:
		// make SKU based automatic import
		// prod_image__{variation_SKU}__#.extension/

		$this->key = $meta_key;

		add_filter( "woocommerce_product_export_{$this->export_type}_default_columns", [$this, 'column_name'] );
		add_filter( "woocommerce_product_export_{$this->export_type}_column_{$this->key}", [$this, 'export_column_data'], 10, 3 );
		add_filter( 'woocommerce_product_export_skip_meta_keys', [$this, 'skip_meta'] );
		add_filter( 'woocommerce_csv_product_import_mapping_options', [$this, 'column_name'] );
		add_filter( 'woocommerce_csv_product_import_mapping_default_columns', [$this, 'default_import_column_name'] );
		add_filter( 'woocommerce_csv_product_import_mapping_special_columns', [$this, 'remove_meta'], 10, 2 );
		add_action( 'woocommerce_product_import_inserted_product_object', [$this, 'process_wc_import'], 10, 2 );

		// WP All Import
		add_filter( 'wp_all_import/import_additional_variation_images/key', [$this, 'compat_wp_all_import_key'] );
		add_filter( 'wp_all_import/import_additional_variation_images/gallery', [$this, 'compat_wp_all_import_gallery'], 10, 3 );
	}

	public function compat_wp_all_import_key(){
		return $this->key;
	}

	public function compat_wp_all_import_gallery($meta, $product_id, $variation_id){

		if( $current_meta = get_post_meta( $variation_id, $this->key, true ) ){
			return implode( ',', (array) $current_meta );
		}

		return $meta;
	}

	public function column_name( $columns ){

		$columns[ $this->key ] = $this->column_name;

		return $columns;
	}

	public function export_column_data( $value, $product, $column_id ) {

		$image_ids  = get_post_meta( $product->get_id(), $this->key, true );

		if ( empty( $image_ids ) ) {
			return '';
		}

		$images = [];

		if( ! is_array($image_ids) ){
			$image_ids = explode(',', $image_ids);
		}

		foreach( $image_ids as $attach_id ) {
			$img = wp_get_attachment_image_src( $attach_id, 'full' );
			$images[] = $img[0];
		}

		return implode( ',', $images );
	}

	public function default_import_column_name( $columns ) {

		$columns[ $this->column_name ] = $this->key;

		return $columns;
	}

	public function skip_meta( $meta ) {

		$meta[] = $this->key;

		return $meta;
	}

	public function remove_meta( $columns ) {

		unset($columns[ 'Meta: ' . $this->key ]);

		return $columns;
	}

	public function process_wc_import( $product, $data ) {

		if( ! $product ){
			return;
		}

		$product_id = $product->get_id();

		if ( isset( $data[ $this->key ] ) && $col = $data[ $this->key ] ) {

			$gallery = [];
			$image_urls    = (array) explode( ',', $col );

			foreach ( $image_urls as $url ) {
				$gallery[] = $this->get_attachment_id_from_url( $url, $product_id );
			}

			update_post_meta( $product_id, $this->key, implode(',', array_values( $gallery) ) );
		}
	}

	public function get_attachment_id_from_url( $url, $product_id ) {

		if ( empty( $url ) ) {
			return 0;
		}

		$id         = 0;
		$upload_dir = wp_upload_dir( null, false );
		$base_url   = $upload_dir[ 'baseurl' ] . '/';

		// Check first if attachment is inside the WordPress uploads directory, or we're given a filename only.
		if ( false !== strpos( $url, $base_url ) || false === strpos( $url, '://' ) ) {

			// Search for yyyy/mm/slug.extension or slug.extension - remove the base URL.
			$file = str_replace( $base_url, '', $url );

			$args = [
				'post_type'   => 'attachment',
				'post_status' => 'any',
				'fields'      => 'ids',
				'meta_query'  => [
					'relation' => 'OR',
					[
						'key'     => '_wp_attached_file',
						'value'   => '^' . $file,
						'compare' => 'REGEXP',
					],
					[
						'key'     => '_wp_attached_file',
						'value'   => '/' . $file,
						'compare' => 'LIKE',
					],
					[
						'key'     => '_wc_attachment_source',
						'value'   => '/' . $file,
						'compare' => 'LIKE',
					],
				]
			];
		} else {
			// This is an external URL, so compare to source.
			$args = [
				'post_type'   => 'attachment',
				'post_status' => 'any',
				'fields'      => 'ids',
				'meta_query'  => [
					[
						'value' => $url,
						'key'   => '_wc_attachment_source',
					]
				],
			];
		}

		$ids = get_posts( $args ); // @codingStandardsIgnoreLine.

		if ( $ids ) {
			$id = current( $ids );
		}

		// Upload if attachment does not exists.
		if ( ! $id && apply_filters('reycore/extra_images/impex/check_for_full_path', stristr( $url, '://' ) ) ) {

			$url = apply_filters('reycore/extra_images/impex/upload_image_from_url', $url);

			$upload = wc_rest_upload_image_from_url( $url );

			if ( is_wp_error( $upload ) ) {
				throw new \Exception( $upload->get_error_message(), 400 );
			}

			$id = wc_rest_set_uploaded_image_as_attachment( $upload, $product_id );

			if ( ! wp_attachment_is_image( $id ) ) {
				/* translators: %s: image URL */
				throw new \Exception( sprintf( __( 'Not able to attach "%s".', 'woocommerce' ), $url ), 400 );
			}

			// Save attachment source for future reference.
			update_post_meta( $id, '_wc_attachment_source', $url );
		}

		if ( ! $id ) {
			/* translators: %s: image URL */
			throw new \Exception( sprintf( __( 'Unable to use image "%s".', 'woocommerce' ), $url ), 400 );
		}

		return $id;
	}

}
