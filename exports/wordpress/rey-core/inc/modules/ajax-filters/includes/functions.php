<?php
/**
 * Necessary functions in Rey Ajax Product Filter plugin.
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

if(!function_exists('reyajaxfilter_search_query')):

	function reyajaxfilter_search_query($args = []){

		global $wpdb;

		$search = [];

		if( apply_filters('reycore/ajaxfilters/search_query/can_use_main', is_main_query() && ( is_post_type_archive('product') || is_tax(get_object_taxonomies('product')) ) ) ){
			$search[] = WC_Query::get_main_search_query_sql();
		}

		// Products on sale
		if( (isset($_GET['on-sale']) && 1 === absint($_GET['on-sale'])) || (isset($args['onsale']) && $args['onsale']) ){
			$filter_query = new \ReyCore\Modules\AjaxFilters\FilterQuery();
			if( $sale_ids = $filter_query->get_onsale_products() ){
				$search[] = esc_sql( sprintf(" {$wpdb->posts}.ID IN (%s) ", implode(',', $sale_ids)) );
			}
		}

		// Featured
		if( (isset($_GET['is-featured']) && 1 === absint($_GET['is-featured'])) || (isset($args['featured']) && $args['featured']) ){
			if( $featured_ids = wc_get_featured_product_ids() ){
				$search[] = esc_sql( sprintf(" {$wpdb->posts}.ID IN (%s) ", implode(',', $featured_ids)) );
			}
		}

		return implode(' AND ', array_filter( apply_filters('reycore/ajaxfilters/search_query', $search) ) );
	}
endif;


if(!function_exists('reyajaxfilter_meta_query_stock')):
	function reyajaxfilter_meta_query_stock($meta_query, $stock){

		if( ! $stock ){
			return $meta_query;
		}

		if( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ){
			return $meta_query;
		}

		// Show only out of stock
		if( 2 === $stock ){
			$meta_query['stock_query'] = [
				'key'     => '_stock_status',
				'value'   => 'outofstock',
				'compare' => '=',
			];
		}
		// Show only In-Stock
		else if( 1 === $stock ){
			$meta_query['stock_query'] = [
				[
					'key'     => '_stock_status',
					'value'   => 'outofstock',
					'compare' => '!=',
				],
			];
			if( apply_filters('reycore/ajaxfilters/stock_meta_query/force_physical_stock', false) ){
				$meta_query['stock_query']['relation'] = 'OR';
				$meta_query['stock_query'][] = [
					'key'     => '_stock',
					'value'   => 1,
					'compare' => '>=',
					'type'    => 'NUMERIC',
				];
			}
		}

		return $meta_query;

	}
endif;


if(!function_exists('reyajaxfilter_meta_query')):
	function reyajaxfilter_meta_query($args = []){

		$meta_query = [];

		if( apply_filters('reycore/ajaxfilters/meta_query/can_use_main', is_main_query() && ( is_post_type_archive('product') || is_tax(get_object_taxonomies('product')) ) ) ){
			$meta_query  = WC_Query::get_main_meta_query();
		}

		if( isset($args['stock']) ){
			$meta_query = reyajaxfilter_meta_query_stock($meta_query, absint($args['stock']));
		}

		if( isset($args['hash']) && !empty($args['hash']) ){
			if( ($rmq = \ReyCore\Modules\AjaxFilters\Helpers::get_registered_meta_query($args['hash'])) && !empty($rmq) ){
				$meta_query['rey-product-meta'] = $rmq;
			}
		}

		if( isset($args['surpress_filter']) && $args['surpress_filter'] ){
			return $meta_query;
		}

		return apply_filters('reycore/ajaxfilters/meta_query', $meta_query, $args);
	}
endif;

if(!function_exists('reyajaxfilter_tax_query')):
	/**
	 * Retrieve the tax query based on the current query.
	 *
	 * @param array $args
	 * @return array
	 */
	function reyajaxfilter_tax_query($args = []){

		$tax_query = [];

		if( apply_filters('reycore/ajaxfilters/tax_query/can_use_main', is_main_query() && ( is_post_type_archive('product') || is_tax(get_object_taxonomies('product')) ) ) ){
			$tax_query  = WC_Query::get_main_tax_query();
		}

		if ( isset($args['query_type']) && 'or' === $args['query_type'] && is_array($tax_query) && !empty($tax_query) ) {
			foreach ( $tax_query as $key => $query ) {
				if ( is_array( $query ) && isset($query['taxonomy'], $args['taxonomy']) && $args['taxonomy'] === $query['taxonomy'] ) {
					unset( $tax_query[ $key ] );
				}
			}
		}

		if( isset($args['surpress_filter']) && $args['surpress_filter'] ){
			return $tax_query;
		}

		return apply_filters('reycore/ajaxfilters/tax_query', $tax_query);
	}
endif;


if(!function_exists('reyajaxfilter_post_types_count')):

	function reyajaxfilter_post_types_count(){

		$collect_post_types = apply_filters('reycore/ajaxfilters/post_types_count', ['product']);

		$post_types = [];

		foreach ($collect_post_types as $value) {

			$post_types[] = "'" . esc_sql($value) . "'";
		}

		return implode(',', $post_types);
	}

endif;


if(!function_exists('reyajaxfilter_hash_query_string')):
	/**
	 * Hash query string
	 *
	 * @since 3.0.0
	 **/
	function reyajaxfilter_hash_query_string($query_string) {
		// Regular expression to match the dynamic hash parts
		$pattern = '/\{[a-f0-9]{64}\}/';
		// Replace the dynamic hashes with a placeholder
		$sanitized_query = preg_replace($pattern, '{hash}', $query_string);
		return md5($sanitized_query);
	}
endif;

if(!function_exists('reyajaxfilter_get_prices_range')):
	/**
	 * Get filtered min price for current products.
	 *
	 * @return array
	 */
	function reyajaxfilter_get_prices_range($args = []) {

		global $wpdb, $wp_query;

		$tax_query  = reyajaxfilter_tax_query();

		$meta_query_args = [];

		if( isset($args['avoid_recursiveness']) && $args['avoid_recursiveness']){
			$meta_query_args = [
				'surpress_filter' => true
			];
		}

		$meta_query  = reyajaxfilter_meta_query($meta_query_args);

		foreach ( $meta_query + $tax_query as $key => $query ) {
			if ( ! empty( $query['price_filter'] ) || ! empty( $query['rating_filter'] ) ) {
				unset( $meta_query[ $key ] );
			}
		}

		$meta_query = new \WP_Meta_Query( $meta_query );
		$tax_query  = new \WP_Tax_Query( $tax_query );
		$search = reyajaxfilter_search_query();

		$meta_query_sql   = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
		$tax_query_sql    = $tax_query->get_sql( $wpdb->posts, 'ID' );
		$search_query_sql = $search ? ' AND ' . $search : '';

		$sql = "
			SELECT min( min_price ) as min_price, MAX( max_price ) as max_price
			FROM {$wpdb->wc_product_meta_lookup}
			WHERE product_id IN (
				SELECT ID FROM {$wpdb->posts}
				" . $tax_query_sql['join'] . $meta_query_sql['join'] . "
				WHERE {$wpdb->posts}.post_type IN ('" . implode( "','", array_map( 'esc_sql', apply_filters( 'woocommerce_price_filter_post_type', array( 'product' ) ) ) ) . "')
				AND {$wpdb->posts}.post_status = 'publish'
				" . $tax_query_sql['where'] . $meta_query_sql['where'] . $search_query_sql . '
			)';

		$sql = apply_filters( 'woocommerce_price_filter_sql', $sql, $meta_query_sql, $tax_query_sql );

		return (array) $wpdb->get_row( $sql ); // WPCS: unprepared SQL ok.
	}
endif;


if(!function_exists('reyajaxfilter_filtering_via_lookup_table_is_active')):
	/**
	 * Check if filtering via attribute lookup is enabled
	 *
	 * @since 3.1.0
	 * @return bool
	 **/
	function reyajaxfilter_filtering_via_lookup_table_is_active()
	{

		if( ! ($filterer = reyajaxfilter_get_attributes_lookup_filterer()) ){
			return false;
		}

		return $filterer->filtering_via_lookup_table_is_active();
	}
endif;


if(!function_exists('reyajaxfilter_get_attributes_lookup_filterer')):
	/**
	 * Check if filtering via attribute lookup is enabled
	 *
	 * @since 3.1.0
	 * @return object
	 **/
	function reyajaxfilter_get_attributes_lookup_filterer()
	{

		if( ! (function_exists('wc_get_container') && class_exists('Automattic\WooCommerce\Internal\ProductAttributesLookup\Filterer')) ){
			return false;
		}

		return wc_get_container()->get( 'Automattic\WooCommerce\Internal\ProductAttributesLookup\Filterer' );
	}
endif;


if(!function_exists('reyajaxfilter_get_filtered_term_product_counts')):
	/**
	 * Count products within certain terms, taking the main WP query into consideration.
	 *
	 * This query allows counts to be generated based on the viewed products, not all products.
	 *
	 * @param  array  $term_ids Term IDs.
	 * @param  string $taxonomy Taxonomy.
	 * @param  string $query_type Query Type.
	 * @return array
	 */
	function reyajaxfilter_get_filtered_term_product_counts( $term_ids, $taxonomy, $query_type ) {
		global $wpdb;

		$use_lookup_table = reyajaxfilter_filtering_via_lookup_table_is_active() && strpos( $taxonomy, 'pa_' ) === 0;

		$meta_query     = reyajaxfilter_meta_query();
		$tax_query      = reyajaxfilter_tax_query([
			'term_ids' => $term_ids,
			'taxonomy' => $taxonomy,
			'query_type' => $query_type,
		]);

		if ( $use_lookup_table ) {
			$query = reyajaxfilter_get_product_counts_query_using_lookup_table( $tax_query, $meta_query, $taxonomy, $term_ids );
		} else {
			$query = reyajaxfilter_get_product_counts_query_not_using_lookup_table( $tax_query, $meta_query, $term_ids );
		}

		$query = apply_filters( 'reycore/woocommerce_get_filtered_term_product_counts_query', $query );
		$query = implode( ' ', $query );

		// We have a query - let's see if cached results of this query already exist.
		$query_hash = reyajaxfilter_hash_query_string($query);

		// Maybe store a transient of the count values.
		$cache = reyajaxfilter_transient_lifespan() !== false;

		if ( true === $cache ) {
			$cached_counts = (array) get_transient( 'reyajaxfilter_counts_' . sanitize_title( $taxonomy ) );
		} else {
			$cached_counts = array();
		}

		if ( ! isset( $cached_counts[ $query_hash ] ) ) {
			$results = $wpdb->get_results( $query, ARRAY_A ); // @codingStandardsIgnoreLine
			$counts = array_map( 'absint', wp_list_pluck( $results, 'term_count', 'term_count_id' ) );
			$cached_counts[ $query_hash ] = $counts;
			if ( true === $cache ) {
				set_transient( 'reyajaxfilter_counts_' . sanitize_title( $taxonomy ), $cached_counts, reyajaxfilter_transient_lifespan() );
			}
		}

		return array_map( 'absint', (array) $cached_counts[ $query_hash ] );
	}
endif;


if(!function_exists('reyajaxfilter_get_product_counts_query_not_using_lookup_table')):
	/**
	 * Get the query for counting products by terms NOT using the product attributes lookup table.
	 *
	 * @param \WP_Tax_Query  $tax_query The current main tax query.
	 * @param \WP_Meta_Query $meta_query The current main meta query.
	 * @param array         $term_ids The term ids to include in the search.
	 * @return array An array of SQL query parts.
	 */
	function reyajaxfilter_get_product_counts_query_not_using_lookup_table( $tax_query, $meta_query, $term_ids ) {
		global $wpdb;

		$meta_query     = new WP_Meta_Query( $meta_query );
		$tax_query      = new WP_Tax_Query( $tax_query );

		$meta_query_sql = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
		$tax_query_sql  = $tax_query->get_sql( $wpdb->posts, 'ID' );

		// Generate query.
		$query           = [];
		$query['select'] = "SELECT COUNT( DISTINCT {$wpdb->posts}.ID ) as term_count, terms.term_id as term_count_id";
		$query['from']   = "FROM {$wpdb->posts}";
		$query['join']   = "
			INNER JOIN {$wpdb->term_relationships} AS term_relationships ON {$wpdb->posts}.ID = term_relationships.object_id
			INNER JOIN {$wpdb->term_taxonomy} AS term_taxonomy USING( term_taxonomy_id )
			INNER JOIN {$wpdb->terms} AS terms USING( term_id )
			" . $tax_query_sql['join'] . $meta_query_sql['join'];

		$post_types = reyajaxfilter_post_types_count();

		$posts_not_in_sql = '';
		// $__post__not_in = \ReyCore\Modules\AjaxFilters\Base::get_the_filters_query()->query_for_post__not_in();
		// if( ! empty($__post__not_in) ){
		// 	$posts_not_in_sql = "AND {$wpdb->posts}.ID NOT IN (" . implode( ',', array_map( 'absint', $__post__not_in ) ) . ")";
		// }

		$query['where'] = "
			WHERE {$wpdb->posts}.post_type IN ( $post_types )
			{$posts_not_in_sql}
			AND {$wpdb->posts}.post_status = 'publish'"
			. $tax_query_sql['where'] . $meta_query_sql['where'] .
			' AND terms.term_id IN (' . implode( ',', array_map( 'absint', $term_ids ) ) . ')';

		$search = reyajaxfilter_search_query();

		if ( $search ) {
			$query['where'] .= ' AND ' . $search;
		}

		$query['group_by'] = 'GROUP BY terms.term_id';

		return $query;
	}
endif;

if(!function_exists('reyajaxfilter_get_product_counts_query_using_lookup_table')):
	/**
	 * Get the query for counting products by terms using the product attributes lookup table.
	 *
	 * @param \WP_Tax_Query  $tax_query The current main tax query.
	 * @param \WP_Meta_Query $meta_query The current main meta query.
	 * @param string         $taxonomy The attribute name to get the term counts for.
	 * @param array         $term_ids The term ids to include in the search.
	 * @return array An array of SQL query parts.
	 */
	function reyajaxfilter_get_product_counts_query_using_lookup_table( $tax_query, $meta_query, $taxonomy, $term_ids ) {
		global $wpdb;

		$meta_query     = new WP_Meta_Query( $meta_query );
		$tax_query      = new WP_Tax_Query( $tax_query );

		$lookup_table_name = wc_get_container()->get( 'Automattic\WooCommerce\Internal\ProductAttributesLookup\LookupDataStore' )->get_lookup_table_name();

		$meta_query_sql    = $meta_query->get_sql( 'post', $lookup_table_name, 'product_or_parent_id' );
		$tax_query_sql     = $tax_query->get_sql( $lookup_table_name, 'product_or_parent_id' );
		$hide_out_of_stock = 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' );
		$in_stock_clause   = $hide_out_of_stock ? ' AND in_stock = 1' : '';

		$query           = array();
		$query['select'] = 'SELECT COUNT(DISTINCT product_or_parent_id) as term_count, term_id as term_count_id';
		$query['from']   = "FROM {$lookup_table_name}";
		$query['join']   = "
			{$tax_query_sql['join']} {$meta_query_sql['join']}
			INNER JOIN {$wpdb->posts} ON {$wpdb->posts}.ID = {$lookup_table_name}.product_or_parent_id";

		$encoded_taxonomy = sanitize_title( $taxonomy );
		$term_ids_sql     = '(' . implode( ',', array_map( 'absint', $term_ids ) ) . ')';
		$query['where']   = "
			WHERE {$wpdb->posts}.post_type IN ( 'product' )
			AND {$wpdb->posts}.post_status = 'publish'
			{$tax_query_sql['where']} {$meta_query_sql['where']}
			AND {$lookup_table_name}.taxonomy='{$encoded_taxonomy}'
			AND {$lookup_table_name}.term_id IN $term_ids_sql
			{$in_stock_clause}";

		if ( ! empty( $term_ids ) ) {

			$attributes_to_filter_by = \ReyCore\Modules\AjaxFilters\Base::get_the_filters_query()->get_chosen_attributes();

			if ( ! empty( $attributes_to_filter_by ) ) {

				$and_term_ids = array();

				foreach ( $attributes_to_filter_by as $taxonomy => $data ) {
					if ( 'and' !== strtolower($data['query_type']) ) {
						continue;
					}
					$term_ids_to_filter_by = $data['terms'];
					$and_term_ids = array_merge( $and_term_ids, $term_ids_to_filter_by );
				}

				if ( ! empty( $and_term_ids ) ) {
					$terms_count   = count( $and_term_ids );
					$active_term_ids_list = '(' . join( ',', $and_term_ids ) . ')';
					// The extra derived table ("SELECT product_or_parent_id FROM") is needed for performance
					// (causes the filtering subquery to be executed only once).
					$query['where'] .= "
						AND product_or_parent_id IN (
							SELECT product_or_parent_id FROM (
								SELECT product_or_parent_id
									FROM {$lookup_table_name} lt
									WHERE is_variation_attribute=0
										{$in_stock_clause}
										AND term_id in {$active_term_ids_list}
									GROUP BY product_id
									HAVING COUNT(product_id)={$terms_count}
								UNION
								SELECT product_or_parent_id
									FROM {$lookup_table_name} lt
										WHERE is_variation_attribute=1
										{$in_stock_clause}
									AND term_id in {$active_term_ids_list}
							) temp
						)";
				}

			} else {
				$query['where'] .= $in_stock_clause;
			}
		} elseif ( $hide_out_of_stock ) {
			$query['where'] .= " AND {$lookup_table_name}.in_stock=1";
		}

		$search_query_sql = \WC_Query::get_main_search_query_sql();
		if ( $search_query_sql ) {
			$query['where'] .= ' AND ' . $search_query_sql;
		}

		$query['group_by'] = "GROUP BY {$lookup_table_name}.term_id";

		return $query;
	}
endif;

if(!function_exists('reyajaxfilter_get_filtered_meta_product_counts')):
	/**
	 * Count products with meta fields, taking the main WP query into consideration.
	 *
	 * This query allows counts to be generated based on the viewed products, not all products.
	 */
	function reyajaxfilter_get_filtered_meta_product_counts($hash) {

		global $wpdb;

		$meta_query     = new WP_Meta_Query( reyajaxfilter_meta_query([
			'hash' => $hash
		]) );
		$tax_query      = new WP_Tax_Query( reyajaxfilter_tax_query() );

		$meta_query_sql = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
		$tax_query_sql  = $tax_query->get_sql( $wpdb->posts, 'ID' );

		// if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) && isset($meta_query_sql['join']) && empty($meta_query_sql['join']) ) {
		// 	$meta_query_sql['join'] = "INNER JOIN {$wpdb->postmeta} ON ( {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id )";
		// }
		$post_types = reyajaxfilter_post_types_count();

		$sql  = "SELECT COUNT( DISTINCT {$wpdb->posts}.ID ) FROM {$wpdb->posts} ";
		$sql .= $tax_query_sql['join'] . $meta_query_sql['join'];
		$sql .= " WHERE {$wpdb->posts}.post_type IN ( $post_types ) AND {$wpdb->posts}.post_status = 'publish' ";
		$sql .= $tax_query_sql['where'] . $meta_query_sql['where'];

		$search = reyajaxfilter_search_query();

		if ( $search ) {
			$sql .= ' AND ' . $search;
		}

		// We have a query - let's see if cached results of this query already exist.
		$query_hash = reyajaxfilter_hash_query_string($sql);

		// Maybe store a transient of the count values.
		$cache = reyajaxfilter_transient_lifespan() !== false;

		if ( true === $cache ) {
			$cached_counts = (array) get_transient( 'reyajaxfilter_prod_meta_counts');
		} else {
			$cached_counts = [];
		}

		if ( ! isset( $cached_counts[ $query_hash ] ) ) {

			$cached_counts[ $query_hash ] = absint( $wpdb->get_var( $sql ) );

			if ( true === $cache ) {
				set_transient( 'reyajaxfilter_prod_meta_counts', $cached_counts, reyajaxfilter_transient_lifespan() );
			}
		}

		return $cached_counts[ $query_hash ];
	}
endif;


if(!function_exists('reyajaxfilter_get_filtered_product_counts__general')):

	/**
	 * Retrieve the product count based on the current query.
	 * Used in misc widgets such as Featured, On Sale, Stock Status, etc.
	 *
	 * @param array $args
	 * @return int
	 */
	function reyajaxfilter_get_filtered_product_counts__general($args = []) {

		$args = reycore__wp_parse_args($args, [
			'tax_query' => [],
			'meta_query' => [],
			'search' => [],
			'cache_key' => ''
		]);

		global $wpdb;

		$meta_query     = new WP_Meta_Query( reyajaxfilter_meta_query($args['meta_query']) );
		$tax_query      = new WP_Tax_Query( reyajaxfilter_tax_query($args['tax_query']) );

		$meta_query_sql = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
		$tax_query_sql  = $tax_query->get_sql( $wpdb->posts, 'ID' );
		$post_types = reyajaxfilter_post_types_count();

		$sql  = "SELECT COUNT( DISTINCT {$wpdb->posts}.ID ) FROM {$wpdb->posts} ";
		$sql .= $tax_query_sql['join'] . $meta_query_sql['join'];
		$sql .= " WHERE {$wpdb->posts}.post_type IN ( $post_types ) AND {$wpdb->posts}.post_status = 'publish' ";
		$sql .= $tax_query_sql['where'] . $meta_query_sql['where'];

		$search = reyajaxfilter_search_query($args['search']);

		if ( $search ) {
			$sql .= ' AND ' . $search;
		}

		// We have a query - let's see if cached results of this query already exist.
		$query_hash = reyajaxfilter_hash_query_string($sql);

		// Maybe store a transient of the count values.
		$cache = reyajaxfilter_transient_lifespan() !== false;

		if ( true === $cache ) {
			$cached_counts = (array) get_transient( 'reyajaxfilter_prod_counts' . $args['cache_key']);
		} else {
			$cached_counts = [];
		}

		if ( ! isset( $cached_counts[ $query_hash ] ) ) {

			$cached_counts[ $query_hash ] = absint( $wpdb->get_var( $sql ) );

			if ( true === $cache ) {
				set_transient( 'reyajaxfilter_prod_counts' . $args['cache_key'], $cached_counts, reyajaxfilter_transient_lifespan() );
			}
		}

		return $cached_counts[ $query_hash ];
	}
endif;


/**
 * Get child term ids for given term.
 *
 * @param  int $term_id
 * @param  string $taxonomy
 * @return array
 */
if (!function_exists('reyajaxfilter_get_term_childs')) {
	function reyajaxfilter_get_term_childs($term_id, $taxonomy, $hide_empty, $order = 'name') {

		if( reyajaxfilter_transient_lifespan() === false ){
			$term_childs = get_terms( $taxonomy, [
				'child_of' => $term_id,
				'fields' => 'ids',
				'hide_empty' => $hide_empty,
				'orderby' => $order
			] );
			return (array)$term_childs;
		}

		$transient_name = 'reyajaxfilter_term_childs_' . md5(sanitize_key($taxonomy) . sanitize_key($term_id));

		if (false === ($term_childs = get_transient($transient_name))) {
			$term_childs = get_terms( $taxonomy, [
				'child_of' => $term_id,
				'fields' => 'ids',
				'hide_empty' => $hide_empty,
				'orderby' => $order
			] );
			set_transient($transient_name, $term_childs, reyajaxfilter_transient_lifespan());
		}

		return (array)$term_childs;
	}
}

/**
 * Get details for given term.
 *
 * @param  int $term_id
 * @param  string $taxonomy
 * @return mixed
 */
if (!function_exists('reyajaxfilter_get_term_data')) {

	function reyajaxfilter_get_term_data($term_value, $taxonomy, $by = 'id') {

		if( $by === 'id' ){

			if( reyajaxfilter_transient_lifespan() === false ){
				return get_term($term_value, $taxonomy);
			}

			$transient_name = 'reyajaxfilter_term_data_' . md5(sanitize_key($taxonomy) . sanitize_key($term_value));

			if (false === ($term_data = get_transient($transient_name))) {
				$term_data = get_term($term_value, $taxonomy);
				set_transient($transient_name, $term_data, reyajaxfilter_transient_lifespan());
			}
		}

		else {

			if( reyajaxfilter_transient_lifespan() === false ){
				return get_term_by($by, esc_attr( $term_value ), $taxonomy);
			}

			$transient_name = 'reyajaxfilter_term_data_' . md5(sanitize_key($taxonomy) . sanitize_key($term_value));

			if (false === ($term_data = get_transient($transient_name))) {
				$term_data = get_term_by($by, esc_attr( $term_value ), $taxonomy);
				set_transient($transient_name, $term_data, reyajaxfilter_transient_lifespan());
			}
		}

		return $term_data;
	}
}


if (!function_exists('reyajaxfilter_clear_term_transients')) {
	function reyajaxfilter_clear_term_transients(int $term_id, int $tt_id, string $taxonomy) {
		foreach ([
			'reyajaxfilter_term_childs_' . md5($taxonomy . $term_id),
			'reyajaxfilter_term_data_' . md5($taxonomy . $term_id),
		] as $value) {
			\ReyCore\Helper::clean_db_transient( $value );
		}
	}
}
add_action('create_term', 'reyajaxfilter_clear_term_transients', 10, 3);
add_action('edit_term', 'reyajaxfilter_clear_term_transients', 10, 3);
add_action('delete_term', 'reyajaxfilter_clear_term_transients', 10, 3);


if (!function_exists('reyajaxfilter_clear_transients')) {
	/**
	 * Flush transients after updates.
	 *
	 * @return void
	 */
	function reyajaxfilter_clear_transients() {
		\ReyCore\Helper::clean_db_transient( 'reyajaxfilter_counts_' );
		\ReyCore\Helper::clean_db_transient( 'reyajaxfilter_prod_meta_counts' );
		\ReyCore\Helper::clean_db_transient( 'reyajaxfilter_prod_counts' );
		\ReyCore\Helper::clean_db_transient( 'reyajaxfilter_cfquery_' );
	}
}
add_action('rey/flush_cache_after_updates', 'reyajaxfilter_clear_transients');


if (!function_exists('reyajaxfilter_clear_attribute_lookup_counts')) {
	/**
	 * Clear count transients when attribute lookup table is regenerated.
	 * The method is hooked to an option that's deleted when the table regeneration callback ends.
	 *
	 * @return void
	 */
	function reyajaxfilter_clear_attribute_lookup_counts() {
		if( reyajaxfilter_filtering_via_lookup_table_is_active() ){
			\ReyCore\Helper::clean_db_transient( 'reyajaxfilter_counts_' );
		}
	}
}
add_action('delete_option_woocommerce_attribute_lookup_regeneration_in_progress', 'reyajaxfilter_clear_attribute_lookup_counts', 10);


// flush after system status clear transients tool
if (!function_exists('reyajaxfilter_clear_transients_in_woo_tools')) {
	function reyajaxfilter_clear_transients_in_woo_tools($tool) {
		if( ! empty($tool['id']) && 'clear_transients' === $tool['id'] ){
			reyajaxfilter_clear_transients();
		}
	}
}
add_action('woocommerce_system_status_tool_executed', 'reyajaxfilter_clear_transients_in_woo_tools');
add_action('woocommerce_rest_insert_system_status_tool', 'reyajaxfilter_clear_transients_in_woo_tools');

if(!function_exists('reyajaxfilter_ajax_clear_transients')):
	/**
	 * Clear transients ajax
	 *
	 * @since 1.5.0
	 **/
	function reyajaxfilter_ajax_clear_transients()
	{
		if( !current_user_can('manage_options') ){
			wp_send_json_error();
		}

		\ReyCore\Helper::clean_db_transient( 'reyajaxfilter_term_childs_' );
		\ReyCore\Helper::clean_db_transient( 'reyajaxfilter_term_data_' );

		do_action('reycore/ajaxfilters/flush_transients');

		wp_send_json_success();

	}
	add_action('wp_ajax_ajaxfilter_clear_transient', 'reyajaxfilter_ajax_clear_transients');
endif;


if (!function_exists('reyajaxfilter_terms_output')):
	/**
	 * Lists terms based on taxonomy
	 *
	 * @since 1.6.2
	 */
	function reyajaxfilter_terms_output($args) {

		$filter_query = \ReyCore\Modules\AjaxFilters\Base::get_the_filters_query();
		$filters_data = $filter_query->get_filter_data();

		$args = wp_parse_args($args, [
			'taxonomy'               => '',
			'data_key'               => '',
			'url_array'              => '',
			'query_type'             => '',
			'placeholder'            => '',
			'enable_multiple'        => false,
			'show_count'             => false,
			'enable_hierarchy'       => false,
			'cat_structure'          => '',
			'manual_cat_ids'         => '',
			'hide_empty'             => true,
			'order_by'               => 'name',
			'custom_height'          => '',
			'show_back_btn'          => false,
			'alphabetic_menu'        => false,
			'search_box'             => false,
			'drop_panel'             => false,
			'drop_panel_button'      => '',
			'dropdown'               => false,
			'show_tooltips'          => false,
			'accordion_list'         => false,
			'show_checkboxes'        => false,
			'show_checkboxes__radio' => false,
			'manually_pick_ids'      => '',
			'widget_id'              => '',
			'value_type'             => 'id',
			'terms'                  => [],
			'has_filters'            => 0 !== $filter_query->get_filters_count(),
			'selected_term_ids'      => [],
		]);

		$args['multiple_all'] = get_theme_mod('ajaxfilters__multiple_all', false);

		if( isset($filters_data['tax'][$args['taxonomy']]) ){
			unset($filters_data['tax'][$args['taxonomy']]['query_type']);
			$args['selected_term_ids'] = wp_list_pluck($filters_data['tax'][$args['taxonomy']], 'id');
			$args['selected_term_ids'] = array_map('absint', $args['selected_term_ids']);
		}

		if(
			$args['taxonomy'] === 'product_cat' &&
			'manual' === $args['cat_structure'] &&
			$manual_cat_ids = $args['manual_cat_ids']
		){
			$args['terms'] = array_map('absint', explode(',', $manual_cat_ids) );
			return reyajaxfilter_main_output($args);
		}

		$parent_args = [
			'fields'       => 'ids',
			'taxonomy'     => $args['taxonomy'],
			'hide_empty'   => $args['hide_empty'],
			'orderby'      => $args['order_by'],
			'order'        => 'ASC',
			'hierarchical' => $args['enable_hierarchy'],
		];

		// Show all including parents
		if ( $args['enable_hierarchy'] ) {
			$parent_args['parent'] = 0;
		}

		// Show all on first timers
		$cat_structure = 'all';

		/**
		 * Legacy.
		 * Show Current if previous "show_children_only" was enabled
		 */
		if( ! $args['cat_structure'] ){
			if( isset($args['show_children_only']) && $args['show_children_only'] ){
				$cat_structure = 'current';

				// show Ancestors if previous "show_children_only__ancestors" was enabled
				if( isset($args['show_children_only__ancestors']) && $args['show_children_only__ancestors'] ){
					$cat_structure = 'all_ancestors';
				}
			}
		}
		else {
			$cat_structure = $args['cat_structure'];
		}
		// End legacy.

		// we're inside a category
		if ( $args['taxonomy'] === 'product_cat') {

			// get current category object
			$current_cat   = get_queried_object();

			// Only show current category's children
			if ( $cat_structure === 'current' ){

				$parent_args['parent'] = 0;

				// only needs to query current category's direct children
				// by pointing out the parent is the current category.
				if( is_shop() ){
					if( !empty($args['selected_term_ids']) ){
						$parent_args['parent'] = $args['selected_term_ids'][0];
					}
				}
				elseif( is_product_category() ){
					if( ! empty($args['selected_term_ids']) ){
						$parent_args['parent'] = $args['selected_term_ids'][0];
					}
					else {
						$parent_args['parent'] = $current_cat->term_id;
					}
				}

			}
			else if ( $cat_structure === 'all_current' ){

				$parent_args['parent'] = 0;
				// only needs to query current category's direct children
				// by pointing out the parent is the current category.
				if( is_shop() ){
					if( ! empty($args['selected_term_ids']) ){
						// commented bc all the other items are hidden in shop page
						// $parent_args['parent'] = $args['selected_term_ids'][0];
					}
				}
				if( is_product_category() ){
					$parent_args['parent'] = $current_cat->term_id;
				}
			}
			else if ( $cat_structure === 'all_ancestors' ){
				$parent_args['parent'] = 0;
			}
			else if ( $cat_structure === 'all' ){
				$parent_args['parent'] = 0;
			}
			else if ( $cat_structure === 'manual_sub' ){
				if( $manual_cat_ids = $args['manual_cat_ids'] ){

					$manual_cat_ids_split = array_map('absint', explode(',', $manual_cat_ids) );

					$picked_terms = array_filter( $manual_cat_ids_split, function($id){
						return empty( get_ancestors( $id, 'product_cat' ) );
					});

					$get_other_parents = get_terms([
						'taxonomy' => 'product_cat',
						'parent' => 0,
						'fields' => 'ids',
					]);

					if( ! empty($get_other_parents) ){
						$parent_args['exclude'] = array_diff( $get_other_parents, $picked_terms );
					}
				}
			}

			// Is likely an attribute page. Force show All categories.
			if( ! is_product_category() && (is_post_type_archive('product') || is_tax(get_object_taxonomies('product'))) ){
				$args['cat_structure'] = 'all';
			}
		}

		// TAGS only
		if( $manual_ids = $args['manually_pick_ids'] ){
			$parent_args['include'] = array_map('absint', explode(',', $manual_ids) );
			$parent_args['orderby'] = 'include';
		}

		// Compatibility with "Custom Taxonomy Order" plugin
		if( defined('CUSTOMTAXORDER_VER') && $parent_args['orderby'] === 'menu_order' ){
			$parent_args['orderby'] = 'term_order';
		}

		$parent_args = apply_filters('reycore/ajaxfilters/terms_args', $parent_args, $args);

		if( ! empty($parent_args['parents_only']) ){
			$args['parents_only'] = true;
		}

		// On-sale page only. Likely built with Elementor.
		if( isset($args['url_array']['on-sale']) && $args['url_array']['on-sale'] ){

			$filter_query = new \ReyCore\Modules\AjaxFilters\FilterQuery();

			$wp_get_object_terms = wp_get_object_terms( $filter_query->get_onsale_products(), $args['taxonomy'], $parent_args );
			$get_sale_terms = ! is_wp_error($wp_get_object_terms) ? array_unique($wp_get_object_terms) : [];

			if( $args['taxonomy'] === 'product_cat' ){

				if( $args['enable_hierarchy'] ){
					$args['terms'] = $get_sale_terms;
				}

				// show only child terms
				else {

					$sale_terms = [];

					foreach ($get_sale_terms as $term_id) {
						if( $has_ancestors = get_ancestors( $term_id, $args['taxonomy'] ) ){
							$sale_terms[] = $term_id;
						}
					}

					$args['terms'] = $sale_terms;
				}

			}
			// others than categories
			else {
				$args['terms'] = $get_sale_terms;
			}

		}
		else {

			$args['terms'] = get_terms($parent_args);

			/**
			 * If inside a category that doesn't have any children, show the parent ancestor
			 */

			if( empty($args['terms']) && $args['taxonomy'] === 'product_cat' ){

				if( is_shop() && !empty($args['selected_term_ids']) ){
					$current_id = $args['selected_term_ids'][0];
				}
				else if( is_tax('product_cat') || is_post_type_archive('product') || is_tax(get_object_taxonomies('product'))) {
					$current_id = get_queried_object_id();
				}

				$cat_ancestors = get_ancestors( $current_id, 'product_cat' );

				// get first parent ancestor
				if( isset($cat_ancestors[0]) ){
					$parent_args['parent'] = $cat_ancestors[0];
					$args['terms'] = get_terms($parent_args);
				}
			}
		}

		return reyajaxfilter_main_output($args);
	}
endif;


if (!function_exists('reyajaxfilter_main_output')) {
	function reyajaxfilter_main_output( $args ){

		if ( ! (is_array($args['terms']) && !empty($args['terms'])) ) {
			return [
				'html'  => '',
				'found' => false
			];
		}

		if( $args['dropdown'] ){
			return reyajaxfilter_dropdown_terms($args);
		}

		return apply_filters('reycore/ajaxfilters/main_terms_output', reyajaxfilter_main_terms_output($args), $args);

	}
}

if (!function_exists('reyajaxfilter_jump_to_cat')) {
	function reyajaxfilter_jump_to_cat($term, $args){

		$output = '';

		if( $args['taxonomy'] !== 'product_cat' ){
			return $output;
		}

		// jump to category
		if( ! is_product_category() ){
			return $output;
		}

		$current_cat_id = get_queried_object_id();

		if( $term->term_id === $current_cat_id ){
			return $output;
		}

		if( $term->parent === $current_cat_id ){
			return $output;
		}

		$anc = get_ancestors($term->term_id, $args['taxonomy']);

		if( in_array($current_cat_id, $anc, true) ){
			return $output;
		}

		$output = 'data-jump="1"';
		// $output .= ' style="color:red"';

		return $output;
	}
}


if (!function_exists('reyajaxfilter_sub_terms_output')) {
	/**
	 * Render Sub-terms
	 *
	 * @param  array $sub_term_args
	 * @param  bool $found used for widgets to determine if they should hide entirely
	 * @return mixed
	 */
	function reyajaxfilter_sub_terms_output($args, $found) {

		$html = '';

		$term_counts = reyajaxfilter_get_filtered_term_product_counts( $args['sub_term_ids'], $args['taxonomy'], $args['query_type'] );
		$allow_all_multiple = true;

		foreach ($args['sub_term_ids'] as $sub_term_id) {

			$term = reyajaxfilter_get_term_data($sub_term_id, $args['taxonomy']);

			if ( ! ( $term && ($term->parent == $args['parent_term_id']) )) {
				continue;
			}

			$_term_id = $term->term_id;
			$_term_name = $term->name;
			$_term_parent = $term->parent;

			$count = isset( $term_counts[ $sub_term_id ] ) ? $term_counts[ $sub_term_id ] : 0;

			$in_filters = in_array( $_term_id, $args['selected_term_ids'] );
			$show_term = $in_filters;

			// Make sure categories are selected
			if( ! empty($args['selected_term_ids']) ){

				// it's cat widget
				if( $args['taxonomy'] === 'product_cat' ) {

					// Just hide if no count.
					if( $args['hide_empty'] && ! $count ){
						$show_term = false;
					}
					else {

						$current_ancestors = get_ancestors( $args['selected_term_ids'][0], $args['taxonomy'] );

						// Force all categories of the most higher active category
						if ( $args['cat_structure'] === 'all' ) {
							$term_ancestors = get_ancestors( $_term_id, $args['taxonomy'] );
							$show_term = !empty( array_intersect($args['selected_term_ids'], $term_ancestors) ) || !empty( array_intersect($current_ancestors, $term_ancestors) );
						}

						// show all subcategories of the current one
						elseif ( $args['cat_structure'] === 'all_current' ) {

							$term_ancestors = get_ancestors( $_term_id, $args['taxonomy'] );
							$show_term = in_array($args['selected_term_ids'][0], $term_ancestors) || in_array($_term_parent, $current_ancestors) || $in_filters;

							if( is_shop() ){
								$show_term = $show_term || in_array($_term_id, $current_ancestors);
							}
						}

						// show selected's sub-categories and all ancestors and sibligns
						elseif ( $args['cat_structure'] === 'all_ancestors' ) {
							// this shows active siblings of this cat
							$show_siblings = in_array($_term_parent, $current_ancestors);
							$show_term = in_array($_term_parent, $args['selected_term_ids']) || $show_siblings;
						}

					}
				}
			}
			else {

				// default landing shop/tax, without active cat filters
				if( ! is_product_category() && ( is_shop() || is_post_type_archive('product') || is_tax(get_object_taxonomies('product'))) ){

					// it's cat widget
					if( $args['taxonomy'] === 'product_cat' ) {

						if ( $args['cat_structure'] === 'all' ) {
							$show_term = true;
						}
						elseif ( $args['cat_structure'] === 'all_current' ) {
							$show_term = true;
						}
						elseif ( $args['cat_structure'] === 'all_ancestors' ) {
							$term_ancestors = get_ancestors( $_term_id, $args['taxonomy'] );
							$show_term = $_term_parent === 0 || count($term_ancestors) === 1;
						}
						// no need
						elseif ( $args['cat_structure'] === 'current' ) {}
					}

					// Just hide if no count.
					if( ! $count && $args['hide_empty'] ){
						$show_term = false;
					}
				}

				else {
					if(
						$args['taxonomy'] === 'product_cat'
						&& $args['enable_hierarchy']
						&& in_array($args['cat_structure'], ['all', 'all_current', 'all_ancestors'], true)
					){
						$show_term = true;
					}
				}

			}

			/**
			 * To be used for exceptions, such as adjusting the display on the Shop page,
			 * so that it shouldn't follow the `cat_structure`.
			 * @since 2.0.0
			 */
			$show_term = apply_filters('reycore/ajaxfilters/subterm_display', $show_term, $term, $args, $term_counts);

			if ( $show_term ) {

				$found = true;
				$_sub_term_ids = [];

				if ( in_array( $args['cat_structure'], ['all', 'all_current', 'all_ancestors'], true) ) {

					// get sub term ids for this term
					$_sub_term_ids = reyajaxfilter_get_term_childs($_term_id, $args['taxonomy'], $args['hide_empty'], $args['order_by'] );

					if ( ! empty($_sub_term_ids) ) {
						$sub_args = [
							'taxonomy'          => $args['taxonomy'],
							'data_key'          => $args['data_key'],
							'query_type'        => $args['query_type'],
							'enable_multiple'   => $args['enable_multiple'],
							'multiple_all'      => $args['multiple_all'],
							'show_count'        => $args['show_count'],
							'enable_hierarchy'  => $args['enable_hierarchy'],
							'cat_structure'     => $args['cat_structure'],
							'selected_term_ids' => $args['selected_term_ids'],
							'hide_empty'        => $args['hide_empty'],
							'order_by'          => $args['order_by'],
							'parent_term_id'    => $_term_id,
							'sub_term_ids'      => $_sub_term_ids,
							'dropdown'          => $args['dropdown'],
						];
					}
				}

				// List
				if( ! $args['dropdown'] ){

					$li_attribute = $before_text = $after_text = '';

					if( ! empty($args['selected_term_ids']) ){
						// only if term id is in active filters
						$is_active_term = in_array($_term_id, $args['selected_term_ids'], true);
					}
					else {
						// if it's the current category
						$is_active_term = is_tax( $args['taxonomy'], $_term_id );
					}

					// Is active term?
					$li_classes = $is_active_term ? 'chosen' : '';

					if( is_product() ){
						$product_categories = wp_get_post_terms( get_the_ID(), 'product_cat', ['fields' => 'ids'] );
						if( !empty($product_categories) && in_array($_term_id, $product_categories, true) ){
							$li_classes = 'chosen-ish';
						}
					}

					if( $args['alphabetic_menu'] && strlen($_term_name) > 0 ){
						$li_attribute = sprintf('data-letter="%s"', mb_substr($_term_name, 0, 1, 'UTF-8') );
					}

					if ($args['show_tooltips']) {
						reycore_assets()->add_scripts('reycore-tooltips');
						reycore_assets()->add_styles('reycore-tooltips');
						$li_attribute .= sprintf(' data-rey-tooltip="%s"', $_term_name);
					}

					$li_attribute .= sprintf(' data-value="%s"', esc_attr($_term_id));

					$html .= sprintf('<li class="%s" %s>', $li_classes, $li_attribute);

					// show accordion list icon
					if( !empty($_sub_term_ids) && $args['accordion_list'] ){
						$html .= '<button class="__toggle">'. reycore__get_svg_icon(['id'=>'arrow']) .'</button>';
					}

					// show checkboxes
					if( $args['show_checkboxes'] ){
						$radio = $args['show_checkboxes__radio'] ? '--radio' : '';
						$before_text .= sprintf('<span class="__checkbox %s"></span>', $radio);
					}

					// show counter
					$after_text .= ($args['show_count'] ? sprintf('<span class="__count">%s</span>', $count) : '');

					$link_attributes = [];
					$link_attributes['key'] = sprintf('data-key="%s"', esc_attr($args['data_key']));
					$link_attributes['value'] = sprintf('data-value="%s"', esc_attr($_term_id));
					$link_attributes['slug'] = sprintf('data-slug="%s"', esc_attr($term->slug));
					$link_attributes['jump'] = reyajaxfilter_jump_to_cat($term, $args);
					$link_attributes['aria_label'] = sprintf('aria-label="%s %d"', esc_attr($term->name), ($args['show_count'] ? esc_attr($count) : ''));

					if( $args['enable_multiple'] && ! empty($args['selected_term_ids']) ){

						// only active's siblings can support multiple
						$selected_parents = [];

						foreach($args['selected_term_ids'] as $selected_item_id){

							$selected_item = reyajaxfilter_get_term_data($selected_item_id, $args['taxonomy']);
							$selected_parents[] = $selected_item->parent;
						}

						if( in_array($_term_parent, array_unique($selected_parents), true) || $args['multiple_all'] ){
							$link_attributes['multiple-filter'] = sprintf('data-multiple-filter="%s"', esc_attr($args['enable_multiple']));
						}
					}

					$link = \ReyCore\Plugin::instance()->woo::get_term_link( $_term_id, $args['taxonomy'] );

					$term_html = sprintf(
						'<a href="%1$s" %5$s>%4$s <span class="__name">%2$s</span> %3$s</a>',
						! is_wp_error($link) ? $link : '#',
						$_term_name,
						$after_text,
						$before_text,
						implode(' ', $link_attributes)
					);

					$html .= apply_filters( 'woocommerce_layered_nav_term_html', $term_html, $term, $link, $count );

					if (!empty($_sub_term_ids)) {

						$sub_args['alphabetic_menu'] = $args['alphabetic_menu'];
						$sub_args['show_tooltips'] = $args['show_tooltips'];
						$sub_args['accordion_list'] = $args['accordion_list'];
						$sub_args['show_checkboxes'] = $args['show_checkboxes'];
						$sub_args['show_checkboxes__radio'] = $args['show_checkboxes__radio'];

						$results = reyajaxfilter_sub_terms_output($sub_args, $found);

						$html .= $results['html'];
						$found = $results['found'];
					}

					$html .= '</li>';
				}

				// dropdown
				else {

					$html .= sprintf(
						'<option value="%1$s" %2$s data-depth="%5$s" data-count="%4$s">%3$s</option>',
						$_term_id,
						(in_array($_term_id, $args['selected_term_ids'], true)) ? 'selected="selected"' : '',
						$_term_name,
						$args['show_count'] ? $count : '',
						$args['depth']
					);

					if (!empty($_sub_term_ids)) {

						$sub_args['depth'] = $args['depth'] + 1;
						$results = reyajaxfilter_sub_terms_output($sub_args, $found);

						$html .= $results['html'];
						$found = $results['found'];
					}
				}
			}

		}

		if( ! $args['dropdown'] && $args['enable_hierarchy'] && !empty($html) ){
			$html = '<ul class="children">' . $html . '</ul>';
		}

		return array(
			'html'  => $html,
			'found' => $found
		);
	}
}


if (!function_exists('reyajaxfilter_main_terms_output')):
	/**
	 * Lists terms based on taxonomy
	 *
	 * @since 1.5.0
	 */
	function reyajaxfilter_main_terms_output($args) {

		$html = $list_html = '';
		$found = false;
		$term_counts = reyajaxfilter_get_filtered_term_product_counts( $args['terms'], $args['taxonomy'], $args['query_type'] );
		$is_attribute = 'pa_' === substr( $args['taxonomy'], 0, 3 );
		$allow_all_multiple = true;
		$has_selection = false;

		foreach ($args['terms'] as $term_id) {

			$count = isset( $term_counts[ $term_id ] ) ? $term_counts[ $term_id ] : 0;

			$should_display_empty = apply_filters('reycore/ajaxfilters/force_empty', (bool) $count || ! $args['hide_empty'], $args );

			$show_term = $should_display_empty;

			if( ! empty($args['selected_term_ids']) ){

				// if this term id is in active filters we will force
				$in_filters = in_array($term_id, $args['selected_term_ids'], true);
				$show_term = $in_filters;

				if( $in_filters ){
					$has_selection = true;
				}

				// is attribute / tag / custom taxonomy
				// show non empty terms
				if( ($is_attribute || ($args['taxonomy'] === 'product_tag') || isset($args['taxonomy_name'])) && $should_display_empty ){
					$show_term = true;
				}

				// For hierarchical + no support for multiple-filters
				if( $args['taxonomy'] === 'product_cat' ){

					if( $args['hide_empty'] && ! $count ){
						$show_term = false;
					}
					else {

						$term = reyajaxfilter_get_term_data($term_id, $args['taxonomy']);

						$current_ancestors = get_ancestors( $args['selected_term_ids'][0], $args['taxonomy'] );
						$term_ancestors = get_ancestors( $term_id, $args['taxonomy'] );

						if ( $args['cat_structure'] === 'all' || ('manual' === $args['cat_structure'] && $args['manual_cat_ids']) ) {
							$show_term = true;
						}

						elseif ( $args['cat_structure'] === 'all_current' ) {
							$show_term = in_array($args['selected_term_ids'][0], $term_ancestors) || in_array($term->parent, $term_ancestors) || $in_filters;
						}

						elseif ( $args['cat_structure'] === 'all_ancestors' ) {
							$show_term = in_array($term_id, $current_ancestors) || $in_filters;
						}

						elseif ( $args['cat_structure'] === 'current' ) {
							$show_term = in_array($args['selected_term_ids'][0], $term_ancestors) || in_array($term->parent, $term_ancestors) || $in_filters;
						}

					}

				}

			}

			if ( $show_term ) {

				// flag widgets to output HTML
				$found = true;
				$li_attribute = $before_text = $after_text = '';

				if( ! empty($args['selected_term_ids']) ){
					// only if term id is in active filters
					$is_active_term = in_array($term_id, $args['selected_term_ids'], true);
				}
				else {
					// if it's the current category
					$is_active_term = is_tax( $args['taxonomy'], $term_id );
				}

				// Is active term?
				$li_classes = $is_active_term ? 'chosen' : '';

				if( is_product() ){
					$product_categories = wp_get_post_terms( get_the_ID(), 'product_cat', ['fields' => 'ids'] );
					if( !empty($product_categories) && in_array($term_id, $product_categories, true) ){
						$li_classes = 'chosenish';
					}
				}

				$sub_term_ids = [];
				$term = get_term_by( 'id', $term_id, $args['taxonomy'] );

				/**
				 * Determines if sub-terms should be loaded.
				 * Useful for Hierarchical taxonomies.
				 * @since 2.0.0
				 */
				$maybe_get_subterms = $args['taxonomy'] === 'product_cat' && in_array( $args['cat_structure'], ['all', 'all_current', 'all_ancestors'], true);

				if( !empty($args['parents_only']) ){
					$maybe_get_subterms = false;
				}

				if ( apply_filters('reycore/ajaxfilters/show_subterms', $maybe_get_subterms, $args, $show_term ) ) {
					$sub_term_ids = reyajaxfilter_get_term_childs($term_id, $args['taxonomy'], $args['hide_empty'], $args['order_by']);
				}

				$sub_term_list = '';

				if (!empty($sub_term_ids)) {

					$sub_term_args = [
						'taxonomy'          => $args['taxonomy'],
						'data_key'          => $args['data_key'],
						'query_type'        => $args['query_type'],
						'enable_multiple'   => $args['enable_multiple'],
						'multiple_all'      => $args['multiple_all'],
						'show_count'        => $args['show_count'],
						'enable_hierarchy'  => $args['enable_hierarchy'],
						'cat_structure'     => $args['cat_structure'],
						'parent_term_id'    => $term_id,
						'sub_term_ids'      => $sub_term_ids,
						'selected_term_ids' => $args['selected_term_ids'],
						'hide_empty'        => $args['hide_empty'],
						'order_by'          => $args['order_by'],
						'alphabetic_menu'   => $args['alphabetic_menu'],
						'show_tooltips'     => $args['show_tooltips'],
						'accordion_list'    => $args['accordion_list'],
						'show_checkboxes'   => $args['show_checkboxes'],
						'show_checkboxes__radio'   => $args['show_checkboxes__radio'],
						'dropdown'          => false,
					];

					$results = reyajaxfilter_sub_terms_output($sub_term_args, $found);

					$sub_term_list = $results['html'];
					$found = $results['found'];
				}

				if( $args['alphabetic_menu'] && strlen($term->name) > 0 ){
					$li_attribute = sprintf('data-letter="%s"', mb_substr($term->name, 0, 1, 'UTF-8') );
				}

				if ($args['show_tooltips']) {
					reycore_assets()->add_styles('reycore-tooltips');
					reycore_assets()->add_scripts('reycore-tooltips');
					$li_attribute .= sprintf(' data-rey-tooltip="%s"', $term->name);
				}

				$li_attribute .= sprintf(' data-value="%s"', esc_attr($term_id));

				if( is_tax( $args['taxonomy'], $term_id ) ){
					$li_attribute .= ' data-disabled';
				}

				$html .= sprintf('<li class="%s" %s>', $li_classes, $li_attribute);

				// show accordion list icon
				if( !empty($sub_term_list) && $args['accordion_list'] ){
					$html .= '<button class="__toggle">'. reycore__get_svg_icon(['id'=>'arrow']) .'</button>';
				}

				// show checkboxes
				if( $args['show_checkboxes'] ){
					$radio = $args['show_checkboxes__radio'] ? '--radio' : '';
					$before_text .= sprintf('<span class="__checkbox %s"></span>', $radio);
				}

				// show counter
				$after_text .= ($args['show_count'] ? sprintf('<span class="__count">%s</span>', $count) : '');

				$link = \ReyCore\Plugin::instance()->woo::get_term_link($term, $args['taxonomy']);

				$link_attributes = [];
				$link_attributes['key'] = sprintf('data-key="%s"', esc_attr($args['data_key']));
				$link_attributes['value'] = sprintf('data-value="%s"', esc_attr($term_id));
				$link_attributes['slug'] = sprintf('data-slug="%s"', esc_attr($term->slug));
				$link_attributes['jump'] = reyajaxfilter_jump_to_cat($term, $args);
				$link_attributes['aria_label'] = sprintf('aria-label="%s %d"', esc_attr($term->name), ($args['show_count'] ? esc_attr($count) : ''));

				if( $args['enable_multiple'] ){

					// only active's siblings can support multiple
					$selected_parents = [];

					if( ! empty($args['selected_term_ids']) ) {
						foreach($args['selected_term_ids'] as $selected_item_id){
							$selected_item = reyajaxfilter_get_term_data($selected_item_id, $args['taxonomy']);
							if( isset($selected_item->parent) ){
								$selected_parents[] = $selected_item->parent;
							}
						}
					}

					if( in_array($term->parent, array_unique($selected_parents), true) || $is_attribute || $args['multiple_all'] ){
						$link_attributes['multiple-filter'] = sprintf('data-multiple-filter="%s"', esc_attr($args['enable_multiple']));
					}

				}

				// if product category is active, and is top level,
				// and no selected terms,
				// when selecting, make sure to redirect to shop
				if(
					$args['taxonomy'] === 'product_cat' &&
					is_tax( $args['taxonomy'], $term_id ) &&
					$term->parent === 0 &&
					! get_theme_mod('ajaxfilter_apply_filter', false)
				){
					$link_attributes['to_shop'] = sprintf('data-to-shop="%s"', 1);
				}

				$term_html = sprintf(
					'<a href="%1$s" %5$s>%4$s <span class="__name">%2$s</span> %3$s</a>',
					! is_wp_error($link) ? $link : '#',
					isset($term->name) ? $term->name : '',
					$after_text,
					$before_text,
					implode(' ', $link_attributes)
				);

				$html .= apply_filters( 'woocommerce_layered_nav_term_html', $term_html, $term, $link, $count );

				$html .= $sub_term_list;

				$html .= '</li>';
			}
		}

		$list_classes = $list_wrapper_styles = $list_attributes = [];

		if( ! $args['accordion_list'] && $custom_height = absint($args['custom_height'] ) ){
			$list_wrapper_styles[] = sprintf('height:%spx', $custom_height);
			$list_attributes[] = sprintf('data-height="%s"', $custom_height);
			reycore_assets()->add_scripts('rey-simple-scrollbar');
			reycore_assets()->add_styles('rey-simple-scrollbar');
		}

		if( $args['enable_hierarchy'] ){
			$list_classes[] = '--hierarchy';

			if( $args['accordion_list'] ){
				$list_classes[] = '--accordion';
			}
		}

		$list_classes[] = '--style-' . ($args['show_checkboxes'] ? 'checkboxes' : 'default');

		if( $args['enable_multiple'] && get_theme_mod('ajaxfilter_apply_filter', false) ){
			$list_classes[] = '--apply-multiple';
		}

		if( $args['alphabetic_menu'] ){
			reycore_assets()->add_styles('reycore-ajaxfilter-layered-nav-alphabetic');
			$list_html .= sprintf('<div class="reyajfilter-alphabetic"><span class="reyajfilter-alphabetic-all %3$s" data-key="%2$s">%1$s</span></div>',
				esc_html__('All', 'rey-core'),
				esc_attr($args['data_key']),
				'' // --reset-filter
			);
		}

		if( $args['search_box'] ){
			reycore_assets()->add_styles('reycore-ajaxfilter-layered-nav-search');
			$list_html .= '<div class="reyajfilter-searchbox js-reyajfilter-searchbox">';
			$list_html .= reycore__get_svg_icon(['id'=>'search']);
			$taxonomy_object = get_taxonomy( $args['taxonomy'] );
			$searchbox_label = sprintf(esc_html__('Search %s', 'rey-core'), strtolower($taxonomy_object->label));
			$list_html .= sprintf('<input type="text" placeholder="%s" name="rey-filter-search-tag-list" id="%s-searchbox" >', $searchbox_label, $args['widget_id']);
			$list_html .= '</div>';
		}

		if( $args['show_back_btn'] ){

			$list_html .= '<div class="reyajfilter-backBtn">';

			$sb_parent_term_id = $url = $term_name = $sb_attributes = '';

			if( is_shop() && !empty($args['selected_term_ids']) ){
				$sb_parent_term__shop = reyajaxfilter_get_term_data($args['selected_term_ids'][0], 'product_cat');
				if( isset($sb_parent_term__shop->parent) ){
					$sb_parent_term_id = $sb_parent_term__shop->parent;
				}
				$sb_attributes .= ' data-shop';
			}
			else if( is_product_category() ){

				$sb_parent_term_id = get_queried_object()->parent;

				if( !empty($args['selected_term_ids']) ){
					$sb_parent_term__cat = reyajaxfilter_get_term_data($args['selected_term_ids'][0], 'product_cat');
					if( isset($sb_parent_term__cat->parent) ){
						$sb_parent_term_id = $sb_parent_term__cat->parent;
					}
				}
			}

			if( $sb_parent_term_id ):

				$sb_parent_term = reyajaxfilter_get_term_data($sb_parent_term_id, 'product_cat');
				$url = get_term_link( $sb_parent_term->term_id, 'product_cat' );
				$term_name = $sb_parent_term->name;

				$sb_data_key = 'product-cato';
				if( $sb_custom_key = esc_attr($args['data_key']) ){
					$sb_data_key = $sb_custom_key;
				}

				$sb_attributes .= sprintf(' data-key="%s" data-value="%d" data-slug="%s"', $sb_data_key, $sb_parent_term->term_id, $sb_parent_term->slug);

				if( $value_type = $args['value_type'] ){
					$sb_attributes .= sprintf(' data-vtype="%s"', esc_attr($value_type) );
				}

				$list_html .= sprintf('<a href="%1$s" %5$s>%4$s<span>%2$s %3$s</span></a>',
					$url,
					esc_html__('Back to', 'rey-core'),
					$term_name,
					reycore__arrowSvg(false),
					$sb_attributes
				);
			endif;

			$list_html .= '</div>';
		}

		$list_attributes[] = sprintf('data-taxonomy="%s"', esc_attr($args['taxonomy']));
		$list_attributes[] = sprintf('data-shop="%s"', esc_url( get_permalink( wc_get_page_id('shop') ) ) );

		if( $value_type = $args['value_type'] ){
			$list_attributes[] = sprintf('data-vtype="%s"', esc_attr($value_type) );
		}

		$list_html .= sprintf('<div class="reyajfilter-layered-nav %s" %s>', implode(' ', $list_classes), implode(' ', $list_attributes));

			$list_html .= sprintf('<div class="reyajfilter-layered-navInner" style="%s">', implode(' ', $list_wrapper_styles));
			$list_html .= '<ul class="reyajfilter-layered-list">';
			$list_html .= $html;
			$list_html .= '</ul>';
			$list_html .= '</div>';

			if( ! $args['accordion_list'] && $custom_height ){
				$list_html .= '<span class="reyajfilter-customHeight-all">'. esc_html__('Show All +', 'rey-core') .'</span>';
			}

		$list_html .= '</div>';

		reycore_assets()->add_styles('reycore-ajaxfilter-layered-nav');

		$widget_output = '';

		if( $args['drop_panel'] ){

			$widget_output .= reyajaxfilter_droppanel_output( $list_html, [
				'button' => $args['drop_panel_button'],
				'keep_active' => $args['drop_panel_keep_active'],
				'key' => $args['data_key'],
				'selection' => $has_selection,
				'active_count' => count($args['selected_term_ids'])
			] );

		}
		else {
			$widget_output .= $list_html;
		}

		return [
			'html'  => $widget_output,
			'found' => $found
		];
	}
endif;

if(!function_exists('reyajaxfilter_droppanel_output')):
	/**
	 * Drop panel markup
	 *
	 * @since 2.0.0
	 **/
	function reyajaxfilter_droppanel_output( $html, $args = [] )
	{

		$args = wp_parse_args($args, [
			'button'      => '',
			'keep_active' => false,
			'key'         => false,
			'selection'   => false,
			'clear_text'  =>  esc_html__('Clear all', 'rey-core')
		]);


		ob_start();

		reycore__get_template_part('inc/modules/ajax-filters/tpl/dropdown', false, false, array_merge( $args, [
			'html' => $html
		]));

		return ob_get_clean();

	}
endif;

/**
 * reyajaxfilter_dropdown_terms function
 *
 * @param  array $args
 * @return mixed
 */
if (!function_exists('reyajaxfilter_dropdown_terms')):
	function reyajaxfilter_dropdown_terms($args) {

		$html = '';
		$found = false;

		$placeholder = $args['placeholder'];

		if( empty($placeholder) ):
			if (preg_match('/^attr/', $args['data_key'])) {
				$attr = str_replace(['attra-', 'attro-'], '', $args['data_key']);
				$placeholder = sprintf(__('Choose %s', 'rey-core'), reyajaxfilter_get_attribute_name( $attr ));
			} elseif (preg_match('/^product-cat/', $args['data_key'])) {
				$placeholder = sprintf(__('Choose category', 'rey-core'));
			}
			elseif (preg_match('/^product-tag/', $args['data_key'])) {
				$placeholder = sprintf(__('Choose tag', 'rey-core'));
			}
			elseif ( isset($args['taxonomy_name']) && !empty($args['taxonomy_name']) ) {
				$placeholder = sprintf(__('Choose %s', 'rey-core'), $args['taxonomy_name']);
			}
		endif;

		if (!empty($args['terms'])) {

			// required scripts
			reycore_assets()->add_scripts('reycore-ajaxfilter-select2');
			reycore_assets()->add_styles(['reycore-ajaxfilter-dropdown', 'rey-form-select2', 'rey-wc-select2', 'reycore-ajaxfilter-select2']);

			$html .= '<div class="reyajfilter-dropdown-nav">';

				$attributes = ($args['enable_multiple'] ? 'multiple="multiple"' : '');

				if( $args['search_box'] ):
					$attributes .= ' data-search="true"';
				endif;

				if( $args['show_checkboxes'] ):

					if( $args['enable_multiple'] ) {
						reycore_assets()->add_scripts('reycore-ajaxfilter-select2-multi-checkboxes');
					}

					$attributes .= ' data-checkboxes="true"';
				endif;

				if( isset($args['dd_width']) && $dropdown_width = $args['dd_width'] ){
					$attributes .= sprintf(' data-ddcss=\'%s\'', wp_json_encode([
						'min-width' => $dropdown_width . 'px'
					]));
				}

				$html .= sprintf( '<select class="%1$s" name="%2$s" style="width: 100%%;" %3$s data-placeholder="%4$s">',
					'reyajfilter-select2 ' . (($args['enable_multiple'] ? 'reyajfilter-select2-multiple' : 'reyajfilter-select2-single')),
					$args['data_key'],
					$attributes,
					$placeholder
				);

				if (!$args['enable_multiple']) {
					$html .= '<option value=""></option>';
				}

				$term_counts = reyajaxfilter_get_filtered_term_product_counts( $args['terms'], $args['taxonomy'], $args['query_type'] );

				foreach ($args['terms'] as $term_id) {

					$count = isset( $term_counts[ $term_id ] ) ? $term_counts[ $term_id ] : 0;

					$should_display_empty = apply_filters('reycore/ajaxfilters/force_empty', (bool) $count || ! $args['hide_empty'], $args );

					$show_term = $should_display_empty;

					if( ! empty($args['selected_term_ids']) ){

						// if this term id is in active filters we will force
						$in_filters = in_array($term_id, $args['selected_term_ids'], true);
						$show_term = $in_filters;

						// For hierarchical + no support for multiple-filters
						if( $args['taxonomy'] === 'product_cat'){

							$term = reyajaxfilter_get_term_data($term_id, $args['taxonomy']);

							$current_ancestors = get_ancestors( $args['selected_term_ids'][0], $args['taxonomy'] );
							$term_ancestors = get_ancestors( $term_id, $args['taxonomy'] );

							if ( $args['cat_structure'] === 'all' ) {
								$show_term = true;
							}

							elseif ( $args['cat_structure'] === 'all_current' ) {

								$show_term = in_array($args['selected_term_ids'][0], $term_ancestors) || in_array($term->parent, $term_ancestors) || $in_filters;

							}

							elseif ( $args['cat_structure'] === 'all_ancestors' ) {
								$show_term = in_array($term_id, $current_ancestors) || $in_filters;
							}

							elseif ( $args['cat_structure'] === 'current' ) {
								$show_term = in_array($args['selected_term_ids'][0], $term_ancestors) || in_array($term->parent, $term_ancestors) || $in_filters;
							}
						}
					}

					if ($show_term) {

						$found = true;

						if( ! empty($args['selected_term_ids']) ){
							// only if term id is in active filters
							$is_active_term = in_array($term_id, $args['selected_term_ids'], true);
						}
						else {
							// if it's the current category
							$is_active_term = is_tax( $args['taxonomy'], $term_id );
						}

						$term = get_term_by( 'id', $term_id, $args['taxonomy'] );

						$html .= sprintf( '<option value="%1$s" %2$s data-count="%4$s">%3$s</option>',
							$term_id,
							($is_active_term ? 'selected="selected"' : ''),
							$term->name,
							($args['show_count'] ? $count : '')
						);

						/**
						 * Determines if sub-terms should be loaded.
						 * Useful for Hierarchical taxonomies.
						 * @since 2.0.0
						 */
						$maybe_get_subterms = $args['taxonomy'] === 'product_cat' && in_array( $args['cat_structure'], ['all', 'all_current', 'all_ancestors'], true);

						if ( apply_filters('reycore/ajaxfilters/show_subterms', $maybe_get_subterms, $args, $show_term ) ) {

							// get sub term ids for this term
							$sub_term_ids = reyajaxfilter_get_term_childs($term_id, $args['taxonomy'], $args['hide_empty']);

							if (!empty($sub_term_ids)) {
								$sub_term_args = [
									'taxonomy'          => $args['taxonomy'],
									'data_key'          => $args['data_key'],
									'query_type'        => $args['query_type'],
									'enable_multiple'   => $args['enable_multiple'],
									'multiple_all'      => $args['multiple_all'],
									'show_count'        => $args['show_count'],
									'enable_hierarchy'  => $args['enable_hierarchy'],
									'cat_structure'     => $args['cat_structure'],
									'parent_term_id'    => $term_id,
									'sub_term_ids'      => $sub_term_ids,
									'selected_term_ids' => $args['selected_term_ids'],
									'hide_empty'        => $args['hide_empty'],
									'order_by'          => $args['order_by'],
									'dropdown'          => true,
									'depth'             => 1,
								];

								$results = reyajaxfilter_sub_terms_output($sub_term_args, $found);

								$html .= $results['html'];
								$found = $results['found'];
							}
						}
					}
				}

				$html .= '</select>';
			$html .= '</div>';
		}

		return [
			'html'  => $html,
			'found' => $found
		];
	}
endif;


if( !function_exists('reyajaxfilter_get_attribute_name') ):
	/**
	 * Pulls label from attributes
	 *
	 * @since 3.0.1
	 */
	function reyajaxfilter_get_attribute_name( $attr ){

		$attribute_taxonomies = wc_get_attribute_taxonomies();

		if( is_array($attribute_taxonomies) && !empty($attribute_taxonomies) ){

			$attribute = array_filter($attribute_taxonomies, function($v, $k) use ($attr){
				return $v->attribute_name === $attr;
			}, ARRAY_FILTER_USE_BOTH);

			$attribute = array_values($attribute);

			if( isset($attribute[0]) && isset($attribute[0]->attribute_label) ){
				return $attribute[0]->attribute_label;
			}
		}

		return $attr;
	}
endif;


/**
 * Transient lifespan
 *
 * @return int
 */
if (!function_exists('reyajaxfilter_transient_lifespan')) {

	function reyajaxfilter_transient_lifespan() {

		static $lifespan = null;

		if ($lifespan !== null) {
			return $lifespan;
		}

		if (apply_filters('woocommerce_layered_nav_count_maybe_cache', true)) {
			$lifespan = apply_filters('reycore/ajaxfilters/transient_lifespan', MONTH_IN_SECONDS);
		} else {
			$lifespan = false;
		}

		return $lifespan;
	}

}


if(!function_exists('reyajaxfilter__prevent_search_redirect')):
	/**
	 * Prevent redirect in Search, when filtering
	 *
	 * @since 1.9.7
	 **/
	function reyajaxfilter__prevent_search_redirect($status) {
		if( is_filtered() ){
			return false;
		}
		return $status;
	}
	add_filter( 'woocommerce_redirect_single_search_result', 'reyajaxfilter__prevent_search_redirect' );
endif;


if(!function_exists('reyajaxfilter_load_simple_template')):
	function reyajaxfilter_load_simple_template($template) {

		if( ! apply_filters('reycore/woocommerce/products/minimal_tpl', true) ){
			return $template;
		}

		if( ! ( isset($_REQUEST['reynotemplate']) && absint( $_REQUEST['reynotemplate'] ) === 1) ){
			return $template;
		}

		if( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtoupper( $_SERVER['HTTP_X_REQUESTED_WITH'] ) === 'XMLHTTPREQUEST' ){
			$template = __DIR__ . '/notemplate.php';
		}

		return $template;
	}
	add_filter( 'template_include', 'reyajaxfilter_load_simple_template', 20 );
endif;


if(!function_exists('reyajaxfilters__filter_sidebar_classes')):
	/**
	 * Filter "Top Filters Sidebar" classes
	 *
	 * @since 1.6.6
	 **/
	function reyajaxfilters__filter_sidebar_classes( $classes, $position )
	{

		if( $position === 'filters-top-sidebar' ){

			if( $sticky_side = get_theme_mod('ajaxfilter_topbar_sticky_2') ){
				$classes['topbar_sticky'] = '--sticky';
				$classes['topbar_sticky_pos'] = '--' . esc_attr($sticky_side);
			}
			// legacy option
			else if( get_theme_mod('ajaxfilter_topbar_sticky') === true ) {
				$classes['topbar_sticky'] = '--sticky';
				$classes['topbar_sticky_pos'] = '--t';
			}
		}

		return $classes;
	}
	add_filter('rey/content/sidebar_class', 'reyajaxfilters__filter_sidebar_classes', 10, 2);
endif;


if(!function_exists('reyajaxfilters__filter_admin_titles')):
	/**
	 * add custom titles in widget title
	 *
	 * @since 1.6.6
	 **/
	function reyajaxfilters__filter_admin_titles( $categs, $sh = 'hide' )
	{

		if( empty($categs) ){
			return;
		}

		$published_categories = [];
		foreach ( $categs as $key => $slug) {
			if( ($term = get_term_by('slug', $slug, 'product_cat')) && isset($term->name) ){
				$published_categories[] = $term->name;
			}
		}

		if( !empty($published_categories) ): ?>
			<span class="rey-widgetToTitle --hidden" data-class="__published-on-categ">
				<span><?php printf(esc_html__('%s on: ', 'rey-core'), ucfirst($sh)) ?></span>
				<span><?php echo implode(', ', $published_categories); ?></span>
			</span>
		<?php endif;
	}
endif;
