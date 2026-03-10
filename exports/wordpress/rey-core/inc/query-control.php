<?php
namespace ReyCore;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class QueryControl
{

	public function __construct(){
		add_action( 'elementor/ajax/register_actions', [ $this, 'register_ajax_actions' ] );
		add_action( 'wp_ajax_rey_select_control_filter_autocomplete', [$this, 'customizer_filter_autocomplete']);
		add_action( 'wp_ajax_rey_select_control_value_titles', [$this, 'customizer_value_titles']);
	}

	/**
	 * Get saved value titles
	 */
	public function elementor_value_titles( $data ){

		if( !(isset($data['query_args']['type']) && $query_type = $data['query_args']['type']) ) {
			throw new \Exception( 'Missing query type.' );
		}

		if( $custom = apply_filters('reycore/query-control/values', [], $data) ){
			return $custom;
		}

		if ( method_exists($this, 'get_value_titles_for_' . $query_type) ) {
			return call_user_func( [ $this, 'get_value_titles_for_' . $query_type ], $data );
		}

		return [];
	}

	/**
	 * Get saved value titles
	 */
	public function customizer_value_titles(){

		if ( ! check_ajax_referer( 'reycore-ajax-verification', 'security', false ) ) {
			wp_send_json( ['error' => 'Invalid security nonce.'] );
		}

		$data = [];

		if( ! (isset($_REQUEST['query_args']) && $data['query_args'] = reycore__clean($_REQUEST['query_args'])) ){
			wp_send_json( ['error' => 'Empty query args.'] );
		}

		if( ! (isset($_REQUEST['values']) && $data['values'] = reycore__clean($_REQUEST['values'])) ){
			wp_send_json( ['error' => 'Empty values.'] );
		}

		if( !(isset($data['query_args']['type']) && $query_type = $data['query_args']['type']) ) {
			wp_send_json( ['error' => 'Missing query type.'] );
		}

		wp_send_json( call_user_func( [ $this, 'get_value_titles_for_' . $query_type ], $data ) );

	}

	/**
	 * Get titles of the saved values
	 */
	public function get_value_titles_for_terms($data){

		$results = [];

		if( ! (isset( $data['values'] ) && $values = $data['values']) ){
			return $results;
		}

		$key = 'term_id';
		$query_args = [
			'term_taxonomy_id' => $values,
			'hide_empty' 	   => false,
		];

		if( isset($data['query_args']['field']) && $field = $data['query_args']['field'] ){
			// Use Slug if specified
			if( 'slug' === $field ){
				unset($query_args['term_taxonomy_id']);
				$query_args['slug'] = $values;
				$key = 'slug';
			}
		}

		$terms = get_terms($query_args);

		foreach ( $terms as $term ) {
			if( ! isset($results[ $term->$key ]) ){
				$taxonomy = get_taxonomy( $term->taxonomy );
				$results[ $term->$key ] = sprintf($term->name . ' (%s)', ucfirst($taxonomy->labels->singular_name));
			}
		}

		return $results;
	}

	/**
	 * Get titles of the saved values
	 */
	public function get_value_titles_for_posts($data){

		$results = [];

		if( ! (isset( $data['values'] ) && $values = $data['values']) ){
			return $results;
		}

		foreach ((array) $data['values'] as $id) {

			if( isset($data['query_args']['edit_link']) && $data['query_args']['edit_link'] ){
				$item = [];
				$item['id']    = $id;
				$item['title'] = get_the_title($id);
				$item['link']  = admin_url( sprintf('post.php?post=%d&action=elementor', $id ));
				$results[] = $item;
			}
			else {
				$results[ $id ] = get_the_title($id);
			}
		}

		return $results;
	}


	/**
	 * Get search results
	 *
	 * @since 1.5.0
	 */
	public function elementor_filter_autocomplete( $data ){

		if ( empty( $data['query_args'] ) || empty( $data['q'] ) ) {
			throw new \Exception( 'Bad Request' );
		}

		if( !(isset($data['query_args']['type']) && $query_type = $data['query_args']['type']) ) {
			throw new \Exception( 'Missing query type.' );
		}

		$results = [];

		if( $custom_results = apply_filters('reycore/query-control/autocomplete', [], $data) ){
			return [
				'results' => $custom_results,
			];
		}

		if( method_exists( $this, 'get_autocomplete_for_' . $query_type ) ){
			$results = call_user_func( [ $this, 'get_autocomplete_for_' . $query_type ], $data );
		}

		return [
			'results' => $results,
		];
	}

	/**
	 * Get search results
	 *
	 * @since 2.0.8
	 */
	public function customizer_filter_autocomplete(){

		if ( ! check_ajax_referer( 'reycore-ajax-verification', 'security', false ) ) {
			wp_send_json( ['error' => 'Invalid security nonce!'] );
		}

		$data = [];

		if( ! (isset($_REQUEST['q']) && $data['q'] = reycore__clean($_REQUEST['q'])) ){
			wp_send_json( ['error' => 'Empty search query.'] );
		}

		if( ! (isset($_REQUEST['query_args']) && $data['query_args'] = reycore__clean($_REQUEST['query_args'])) ){
			wp_send_json( ['error' => 'Empty query args.'] );
		}

		if( !(isset($data['query_args']['type']) && $query_type = $data['query_args']['type']) ) {
			wp_send_json( ['error' => 'Missing query type.'] );
		}

		$results = call_user_func( [ $this, 'get_autocomplete_for_' . $query_type ], $data );

		wp_send_json( [
			'results' => $results,
		] );

	}

	/**
	 * Terms search results
	 */
	function get_autocomplete_for_terms( $data ){

		$results = [];

		$key = 'term_id';
		$query_args = [
			'search' 		=> $data['q'],
			'hide_empty' 	=> false,
		];

		$taxonomies = [];

		if( ! isset($data['query_args']['taxonomy']) ){
			return $results;
		}

		if( isset($data['query_args']['field']) && $field = $data['query_args']['field'] ){
			// Use Slug if specified
			if( 'slug' === $field ){
				$key = 'slug';
			}
		}

		if( 'all_attributes' === $data['query_args']['taxonomy'] ){

			if( function_exists('wc_get_attribute_taxonomies') ){
				foreach( wc_get_attribute_taxonomies() as $attribute ) {
					$taxonomies[] = wc_attribute_taxonomy_name($attribute->attribute_name);
				}
			}

		}

		if( in_array($data['query_args']['taxonomy'], ['all_taxonomies', 'product_taxonomies'], true) ){

			$ob_taxonomies = [];

			if( 'product_taxonomies' === $data['query_args']['taxonomy'] ){
				$ob_taxonomies = array_diff( get_object_taxonomies( 'product' ), [
					'product_type',
					'product_visibility',
					'product_shipping_class',
				] );
			}

			$terms = get_terms($query_args);

			foreach ( $terms as $term ) {

				if( ! isset($term->taxonomy) ){
					continue;
				}

				if( ! empty($ob_taxonomies) && ! in_array($term->taxonomy, $ob_taxonomies, true) ){
					continue;
				}

				// $taxonomy = get_taxonomy( $term->taxonomy );

				// $tax_label = ucfirst($term->taxonomy);

				// if( isset($taxonomy->labels) && isset($taxonomy->labels->singular_name) ){
				// 	$tax_label = ucfirst($taxonomy->labels->singular_name);
				// }

				$results[] = [
					'id' 	=> $term->$key,
					'text' 	=> sprintf($term->name . ' (%s)', $term->taxonomy),
				];
			}

		}
		else {
			$taxonomies[] = $data['query_args']['taxonomy'];
		}


		foreach ($taxonomies as $tax) {

			if( ! taxonomy_exists($tax) ){
				continue;
			}

			$query_args['taxonomy'] = $tax;

			$terms = get_terms($query_args);

			// if( reycore__is_multilanguage() ){
			// 	$terms = apply_filters('reycore/translate_ids', $terms, $query_args['taxonomy']);
			// }

			foreach ( $terms as $term ) {

				if( ! isset($term->taxonomy) ){
					continue;
				}

				$taxonomy = get_taxonomy( $term->taxonomy );

				$results[] = [
					'id' 	=> $term->$key,
					'text' 	=> $term->name . sprintf(' (%s)', ucfirst($taxonomy->labels->singular_name)),
				];
			}

		}

		return $results;
	}

	/**
	 * Posts search results
	 */
	function get_autocomplete_for_posts( $data ){

		$results = [];

		$query_args = [
			's' 		       => $data['q'],
			'posts_per_page'   => 200,
			'orderby'          => 'date',
			'post_status'      => 'publish',
		];

		if( isset($data['query_args']['post_type']) ){
			$query_args['post_type'] = $data['query_args']['post_type'];
		}

		if( isset($data['query_args']['meta']) && $meta = $data['query_args']['meta'] ){
			$query_args['meta_key'] = $meta['meta_key'];
			$query_args['meta_value'] = $meta['meta_value'];
		}

		$posts = get_posts($query_args);

		foreach ( $posts as $post ) {

			$item = [
				'id' 	=> $post->ID,
				'text' 	=> $post->post_title,
			];

			if( isset($data['query_args']['edit_link']) && $data['query_args']['edit_link'] ){
				$item['link']  = admin_url( sprintf('post.php?post=%d&action=elementor', $post->ID ));
			}

			$results[] = $item;
		}

		return $results;
	}

	/**
	 * Register Elementor Ajax Actions
	 *
	 * @since  2.0.0
	 * @return array
	 */
	public function register_ajax_actions( $ajax_manager ) {
		$ajax_manager->register_ajax_action( 'rey_query_control_value_titles', [ $this, 'elementor_value_titles' ] );
		$ajax_manager->register_ajax_action( 'rey_query_control_filter_autocomplete', [ $this, 'elementor_filter_autocomplete' ] );
	}

}
