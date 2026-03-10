<?php
namespace ReyCore\Modules\AjaxFilters;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class FilterQuery {

	protected $filter_data = [];

	public function __construct( $url_query = [] ) {

		$filters = new ChosenFilters( $url_query );
		$this->filter_data = $filters->get_data();

		return true;
	}

	/**
	 * Get the chosen filters data
	 *
	 * @return array
	 */
	public function get_filter_data(){

		if( ! isset($this->filter_data['filters']) ){
			return [];
		}

		return $this->filter_data['filters'];
	}

	/**
	 * Return the count of active filters
	 *
	 * @return int
	 */
	public function get_filters_count(){

		if( ! isset($this->filter_data['count']) ){
			return 0;
		}

		$count = $this->filter_data['count'];

		if( ! empty($this->filter_data['filters']['orderby']) && apply_filters('reycore/ajaxfilters/active_filters/order_display', false) ){
			$count--;
		}

		return absint($count);
	}

	/**
	 * Query for meta that should be set to the main query.
	 *
	 * @return array
	 */
	public function query_for_meta()
	{
		$meta_query = [];
		$filters = $this->filter_data['filters'];

		// rating filter
		if (isset($filters['min_rating'])) {
			$meta_query[] = [
				'key'           => '_wc_average_rating',
				'value'         => $filters['min_rating'],
				'compare'       => '>=',
				'type'          => 'DECIMAL',
				'rating_filter' => true,
			];
		}

		if( isset($filters['in-stock']) ){
			$meta_query = reyajaxfilter_meta_query_stock($meta_query, absint($filters['in-stock']));
		}

		if (isset($filters['min_price']) || isset($filters['max_price'])) {

			// $price_range = $this->get_price_range();
			$step = max( apply_filters( 'woocommerce_price_filter_widget_step', 1 ), 1 );
			$step = 1;

			$min_price = (!empty($filters['min_price'])) ? absint($filters['min_price']) : 0;
			$max_price = (!empty($filters['max_price'])) ? absint($filters['max_price']) : 0;

			if( $min_price !== $max_price ) {

				// Check to see if we should add taxes to the prices if store are excl tax but display incl.
				$tax_display_mode = get_option( 'woocommerce_tax_display_shop' );

				if ( wc_tax_enabled() && ! wc_prices_include_tax() && 'incl' === $tax_display_mode ) {
					$tax_class = apply_filters( 'woocommerce_price_filter_widget_tax_class', '' ); // Uses standard tax class.
					$tax_rates = \WC_Tax::get_rates( $tax_class );

					if ( $tax_rates ) {
						$min_price += \WC_Tax::get_tax_total( \WC_Tax::calc_exclusive_tax( $min_price, $tax_rates ) );
						$max_price += \WC_Tax::get_tax_total( \WC_Tax::calc_exclusive_tax( $max_price, $tax_rates ) );
					}
				}

				$min_price = apply_filters( 'woocommerce_price_filter_widget_min_amount', floor( $min_price / $step ) * $step, $min_price, $step );
				$max_price = apply_filters( 'woocommerce_price_filter_widget_max_amount', ceil( $max_price / $step ) * $step, $max_price, $step );

				// get max price from range
				if( ! (bool) $max_price ){
					if( ($prices = reyajaxfilter_get_prices_range(['avoid_recursiveness' => true])) && isset($prices['max_price']) ) {
						$max_price = ceil( floatval( wp_unslash( $prices['max_price'] ) ) / $step ) * $step;
					}
				}

				$price_range_query = [
					'key'          => '_price',
					'value'        => [ $min_price, $max_price ],
					'type'         => 'numeric',
					'compare'      => 'BETWEEN',
					'price_filter' => true,
				];

				// the values from _price meta key are cast to SIGNED integer before the BETWEEN operation, so a value like '20.4' becomes '20' which is included in range of 10 to 20.
				// to keep the decimal values, casting to DECIMAL instead of SIGNED is the solution
				if( apply_filters('reycore/woocommerce/price_filter/decimal_query', false) ){
					$price_range_query['type'] = sprintf('DECIMAL(10,%d)', wc_get_price_decimals());
				}

				$meta_query[] = $price_range_query;
			}
		}

		if ( ! empty($filters['product-meta']) )
		{
			foreach ($filters['product-meta'] as $hash)
			{
				if( ($rmq = \ReyCore\Modules\AjaxFilters\Helpers::get_registered_meta_query($hash)) && !empty($rmq) ){
					$meta_query['rey-product-meta'] = $rmq;
				}
			}
		}

		// Custom fields
		if ( ! empty($filters['cf']) )
		{
			foreach ($filters['cf'] as $field)
			{
				$meta_terms = Helpers::get_meta_converted_values($field['field_name']);

				if( empty($meta_terms) ){
					continue;
				}

				$cf_query['relation'] = 'OR';

				foreach ($field['terms'] as $v) {

					if( empty($meta_terms[$v]) ){
						$v = strtolower(urlencode($v)); // check for encoded values (eg: Cyrilic characters)
						if( empty($meta_terms[$v]) ){
							continue;
						}
					}

					$cf_query[] = [
						'key'           => $field['field_name'],
						'value'         => $meta_terms[$v], // must be like in DB
						'compare'       => 'LIKE',
					];

				}

				$meta_query['cf'] = $cf_query;
			}
		}

		return apply_filters('reycore/ajaxfilters/products/meta_query', $meta_query);
	}

	/**
	 * Filtered product ids for given terms.
	 *
	 * @return array
	 */
	public function query_for_tax( $q = null )
	{
		$tax_query = [];

		if( $q ){
			$tax_query = $q->get( 'tax_query' );
		}

		global $wp_query;
		$main_query = $wp_query->is_main_query();

		$tax_query['relation'] = 'AND';

		// go through the tax filters
		if(
			isset($this->filter_data['filters']['tax'])
			&& ($taxonomies = $this->filter_data['filters']['tax'])
		){

			foreach ( $taxonomies as $taxonomy => $terms )
			{
				if( ! is_array($terms) ){
					continue;
				}

				// skip attributes if filtering via lookup table is active
				if( reyajaxfilter_filtering_via_lookup_table_is_active() && strpos( $taxonomy, 'pa_' ) === 0 ){
					continue;
				}

				$query_type = isset($terms[0]['query_type']) ? $terms[0]['query_type'] : 'or';
				unset($terms['query_type']);

				$term_ids = wp_list_pluck($terms, 'id');

				if( ! empty($term_ids) ){

					$tq = [
						'taxonomy'         => $taxonomy,
						'field'            => 'term_id',
						'terms'            => array_map('absint', array_unique($term_ids)),
						'operator'         => 'and' === strtolower($query_type) ? 'AND' : 'IN',
						'include_children' => false,
					];

					if( $taxonomy === 'product_cat' ){

						$tq['include_children'] = true;

						// Different scenarios when in categories
						if( is_product_category() && count($tq['terms']) > 1 ){

							$current_cat_id = get_queried_object_id();

							// This forces the main query to allow multiple categories,
							// including the current one (not just the current one)
							if( $q && empty( get_term_children( $current_cat_id , 'product_cat' ) ) ){
								unset($q->query_vars['product_cat']);
							}

							// exclude ancestors when in category
							else {

								$tq['terms'] = array_filter($tq['terms'], function($id) use ($current_cat_id){
									$anc = get_ancestors($id, 'product_cat');
									return ! empty($anc) && in_array( $current_cat_id, $anc, true );
								});
							}
						}

					}

					$tax_query[] = $tq;
				}

			}
		}
		/**
		 * Attribute Ranges
		 */

		$ranges_map = [];

		foreach (['range_min', 'range_max'] as $type)
		{
			if( isset($this->filter_data['filters'][$type]) && ! empty($this->filter_data['filters'][$type]) )
			{
				foreach ($this->filter_data['filters'][$type] as $taxonomy => $term)
				{
					$ranges_map[$taxonomy][$type] = $term;
				}
			}
		}

		foreach ($ranges_map as $taxonomy => $data) {

			if( !(isset($data['range_max']) || isset($data['range_min'])) ){
				continue;
			}

			$range_tax_terms = get_terms([
				'taxonomy' => $taxonomy,
				'hide_empty' => true,
			] );

			$clean_range_terms = wp_list_pluck($range_tax_terms, 'name', 'term_id');

			if( empty($clean_range_terms) ){
				continue;
			}

			$__ranges = array_filter($clean_range_terms, function($item) use ($data) {

				$cond = [];

				if( isset($data['range_max']) && $max = $data['range_max'] ){
					$cond[] = floatval($item) <= floatval($max);
				}

				if( isset($data['range_min']) && $min = $data['range_min'] ){
					$cond[] = floatval($item) >= floatval($min);
				}

				return ! in_array(false, $cond, true);
			});

			if( empty($__ranges) ){
				// continue; // if not in range, it will show all. Commenting this forces the query
			}

			$tax_query[] = [
				'taxonomy'         => $taxonomy,
				'field'            => 'term_id',
				'terms'            => array_keys($__ranges),
				'operator'         => 'IN',
				'include_children' => false,
			];
		}

		$product_visibility_terms  = wc_get_product_visibility_term_ids();
		$product_visibility_not_in = array( is_search() && $main_query ? $product_visibility_terms['exclude-from-search'] : $product_visibility_terms['exclude-from-catalog'] );

		// Hide out of stock products.
		if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
			$product_visibility_not_in[] = $product_visibility_terms['outofstock'];
		}

		if ( ! empty( $product_visibility_not_in ) ) {
			$tax_query[] = [
				'taxonomy' => 'product_visibility',
				'field'    => 'term_taxonomy_id',
				'terms'    => $product_visibility_not_in,
				'operator' => 'NOT IN',
			];
		}

		// remove duplicate entries from the $tax_query array
		// only happens when main query has entries.
		if( $q ){
			$tax_query = self::remove_duplicate_entries($tax_query);
		}

		return array_filter( $tax_query );
	}

	/**
	 * Add extra clauses to the MAIN product query.
	 *
	 * @param array    $args Product query clauses.
	 * @param \WP_Query $wp_query The current product query.
	 * @return array The updated product query clauses array.
	 */
	public function product_query_post_clauses_main_query( $args, $wp_query ) {

		if( $wp_query->is_main_query() ){
			$args = $this->product_query_post_clauses( $args, $wp_query );
		}

		return $args;
	}

	public function get_chosen_attributes(){

		if( ! isset($this->filter_data['filters']['tax']) ){
			return [];
		}

		if( ! ( ($taxonomies = $this->filter_data['filters']['tax']) ) ){
			return [];
		}

		// Prepare the chosen attributes by going through the active filters
		$chosen_attributes = [];

		foreach ( $taxonomies as $taxonomy => $terms )
		{
			if( ! is_array($terms) ){
				continue;
			}

			// skip other taxonomies if filtering via lookup table is active
			if( strpos( $taxonomy, 'pa_' ) !== 0 ){
				continue;
			}

			$term_ids = wp_list_pluck($terms, 'id');

			if( empty($term_ids) ){
				continue;
			}

			$chosen_attributes[$taxonomy] = [
				'terms' => array_map('absint', array_unique($term_ids)),
				'query_type' => isset($terms[0]['query_type']) ? $terms[0]['query_type'] : 'or',
			];
		}

		return $chosen_attributes;
	}

	/**
	 * Add extra clauses to the product query.
	 * This method should be invoked within a 'posts_clauses' filter,
	 * and only when filtering via lookup table is active.
	 *
	 * @param array    $args Product query clauses.
	 * @param \WP_Query $wp_query The current product query.
	 * @return array The updated product query clauses array.
	 */
	public function product_query_post_clauses( $args, $wp_query ) {

		if( ! reyajaxfilter_filtering_via_lookup_table_is_active() ){
			return $args;
		}

		$chosen_attributes = $this->get_chosen_attributes();

		if( empty($chosen_attributes) ){
			return $args;
		}

		$args = $this->filter_by_attribute_post_clauses( $args, $wp_query, $chosen_attributes );

		return $args;
	}


	/**
	 * Adds post clauses for filtering via lookup table.
	 * This method should be invoked within a 'posts_clauses' filter.
	 *
	 * * Replica of Automattic\WooCommerce\Internal\ProductAttributesLookup\Filterer->filter_by_attribute_post_clauses()
	 *
	 * @param array     $args Product query clauses as supplied to the 'posts_clauses' filter.
	 * @param \WP_Query $wp_query Current product query as supplied to the 'posts_clauses' filter.
	 * @param array     $attributes_to_filter_by Attribute filtering data as generated by WC_Query::get_layered_nav_chosen_attributes.
	 * @return array The updated product query clauses.
	 */
	private function filter_by_attribute_post_clauses( array $args, \WP_Query $wp_query, array $attributes_to_filter_by ) {
		global $wpdb;

		$lookup_table_name = wc_get_container()->get( 'Automattic\WooCommerce\Internal\ProductAttributesLookup\LookupDataStore' )->get_lookup_table_name();

		// The extra derived table ("SELECT product_or_parent_id FROM") is needed for performance
		// (causes the filtering subquery to be executed only once).
		$clause_root = " {$wpdb->posts}.ID IN ( SELECT product_or_parent_id FROM (";
		if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
			$in_stock_clause = ' AND in_stock = 1';
		} else {
			$in_stock_clause = '';
		}

		$attribute_ids_for_and_filtering = array();
		$clauses                         = array();
		foreach ( $attributes_to_filter_by as $taxonomy => $data ) {
			$term_ids_to_filter_by      = $data['terms'];
			$term_ids_to_filter_by_list = '(' . join( ',', $term_ids_to_filter_by ) . ')';
			$is_and_query               = 'and' === strtolower($data['query_type']);

			$count = count( $term_ids_to_filter_by );

			if ( 0 !== $count ) {
				if ( $is_and_query && $count > 1 ) {
					$attribute_ids_for_and_filtering = array_merge( $attribute_ids_for_and_filtering, $term_ids_to_filter_by );
				} else {
					$clauses[] = "
							{$clause_root}
							SELECT product_or_parent_id
							FROM {$lookup_table_name} lt
							WHERE term_id in {$term_ids_to_filter_by_list}
							{$in_stock_clause}
						)";
				}
			}
		}

		if ( ! empty( $attribute_ids_for_and_filtering ) ) {
			$count                      = count( $attribute_ids_for_and_filtering );
			$term_ids_to_filter_by_list = '(' . join( ',', $attribute_ids_for_and_filtering ) . ')';
			$clauses[]                  = "
				{$clause_root}
				SELECT product_or_parent_id
				FROM {$lookup_table_name} lt
				WHERE is_variation_attribute=0
				{$in_stock_clause}
				AND term_id in {$term_ids_to_filter_by_list}
				GROUP BY product_id
				HAVING COUNT(product_id)={$count}
				UNION
				SELECT product_or_parent_id
				FROM {$lookup_table_name} lt
				WHERE is_variation_attribute=1
				{$in_stock_clause}
				AND term_id in {$term_ids_to_filter_by_list}
			)";
		}

		if ( ! empty( $clauses ) ) {
			// "temp" is needed because the extra derived tables require an alias.
			$args['where'] .= ' AND (' . join( ' temp ) AND ', $clauses ) . ' temp ))';
		} elseif ( ! empty( $attributes_to_filter_by ) ) {
			$args['where'] .= ' AND 1=0';
		}

		return $args;
	}

	public static function remove_duplicate_entries($array) {
		$serializedArrays = array_map('serialize', $array);
		$uniqueSerializedArrays = array_unique($serializedArrays);
		return array_map('unserialize', $uniqueSerializedArrays);
	}


	public function query_for_post__in(){

		$post__in = [];

		if ( ! empty($this->filter_data['filters']['on-sale']) ) {
			$post__in = array_merge( $post__in, $this->get_onsale_products() );
		}

		if ( ! empty ($this->filter_data['filters']['is-featured']) ) {
			$post__in = array_merge( $post__in, wc_get_featured_product_ids() );
		}

		return apply_filters('reycore/ajaxfilters/query_post__in', $post__in);

	}

	public function get_onsale_products($args = []){

		$args = wp_parse_args($args, [
			'force_chosen_filters' => false,
			'out_of_stock_variations' => true,
		]);

		$ids = wc_get_product_ids_on_sale();

		if($args['out_of_stock_variations']){
			$ids = array_diff( $ids, $this->get_out_of_stock_variations($args['force_chosen_filters']) );
		}

		return array_map('absint', array_unique( apply_filters('reycore/ajaxfilters/product_ids_on_sale', $ids) ) );
	}

	public function query_for_post__not_in(){

		$p = [];

		if( $out_of_stock_variations = $this->get_out_of_stock_variations() ){
			$p = $out_of_stock_variations;
		}

		return apply_filters('reycore/ajaxfilters/query_posts_not_in', $p);
	}

	public function get_out_of_stock_variations( $force_chosen_filters = false ){

		if( reyajaxfilter_filtering_via_lookup_table_is_active() ){
			return [];
		}

		// https://github.com/woocommerce/woocommerce/issues/27935

		if( 'yes' !== get_option( 'woocommerce_hide_out_of_stock_items' ) ){
			return [];
		}

		$maybe_continue = [
			$force_chosen_filters
		];

		// no point in carying on.
		// It might be problematic for products with variations that are all out of stock.
		if( empty($this->filter_data['filters']['tax']) ){
			return [];
		}

		if( $this->filter_data['count'] ){
			$maybe_continue[] = true;
		}

		if( ! empty($this->filter_data['filters']['on-sale']) ){
			$maybe_continue[] = true;
		}

		if( ! in_array(true, $maybe_continue, true) ){
			return [];
		}

		$custom_results = apply_filters('reycore/ajaxfilters/out_of_stock_variations/shortcircuit', null, $this );

		if( ! is_null($custom_results) ){
			return $custom_results;
		}

		$do_cache = apply_filters('reycore/ajaxfilters/out_of_stock_variations/cache', true );
		$var_inner_join = '';
		$var_where = [];

		global $wpdb;

		if( false === ($variation_attributes = get_transient('rey_get_variation_attributes')) ){
			// get all variation attributes
			$variation_attributes = $wpdb->get_col("SELECT DISTINCT `taxonomy` FROM `{$wpdb->prefix}wc_product_attributes_lookup` WHERE `is_variation_attribute`=1");
			// cache for a week
			set_transient('rey_get_variation_attributes', $variation_attributes, WEEK_IN_SECONDS);
		}

		foreach ($this->filter_data['filters']['tax'] as $wc_tax => $terms_data) {

			if( ! in_array($wc_tax, $variation_attributes, true) ){
				continue;
			}

			$taxonomy = reycore__clean(strtolower(urlencode($wc_tax)));
			$taxonomy_hash = str_replace(['pa_','-'], '', $taxonomy);

			$var_inner_join .= "INNER JOIN {$wpdb->postmeta} AS {$taxonomy_hash}_meta ON the_variations.ID = {$taxonomy_hash}_meta.post_id ";

			foreach ($terms_data as $term_data) {

				if( empty($term_data['slug']) ){
					$term_object = get_term_by('id', absint($term_data['id']), $taxonomy);
					$term_data['slug'] = $term_object->slug;
				}

				if ( ! empty($term_data['slug'])) {
					$var_where[] = $wpdb->prepare( "({$taxonomy_hash}_meta.meta_key = 'attribute_{$taxonomy}' AND {$taxonomy_hash}_meta.meta_value = '%s')", $term_data['slug'] );
				}
			}
		}

		if( empty($var_inner_join) ){
			return [];
		}

		$var_where = implode(' OR ', $var_where);

		// get product visibility terms
		// others: 'featured', 'rated-1', 'rated-2', 'rated-3', 'rated-4', 'rated-5',
		$product_visibility_term_ids  = wc_get_product_visibility_term_ids();

		$product_visibility_terms_curated = [
			$product_visibility_term_ids['exclude-from-catalog'],
			$product_visibility_term_ids['outofstock'],
		];

		if( is_search() ){
			$product_visibility_terms_curated[] = $product_visibility_term_ids['exclude-from-search'];
		}

		$product_visibility_terms = implode(',', array_map('absint', $product_visibility_terms_curated) );

		$variations_with_current_attributes = "
			SELECT the_variations.ID
			FROM {$wpdb->posts} AS the_variations
			{$var_inner_join}
			WHERE
				the_variations.post_type = 'product_variation'
				AND the_variations.post_status = 'publish'
				AND ({$var_where})
				AND the_variations.ID IN (
					SELECT object_id
					FROM {$wpdb->term_relationships}
					WHERE term_taxonomy_id IN ({$product_visibility_terms})
				)
		";

		if( $do_cache ){

			// Generate a unique cache key using md5
			$cache_key = 'rey_variations_curr_attr_' . md5($variations_with_current_attributes);

			// Try to get the cached result
			if ( false !== ($cached_result = get_transient($cache_key)) ) {
				return $cached_result;
			}

		}

		$variation_ids = $wpdb->get_col( $variations_with_current_attributes );
		$parent_of_variations = array_unique( array_map('wp_get_post_parent_id', $variation_ids) );

		if ( $do_cache ) {
			set_transient($cache_key, $parent_of_variations, 2 * HOUR_IN_SECONDS);
		}

		return $parent_of_variations;

	}

}
