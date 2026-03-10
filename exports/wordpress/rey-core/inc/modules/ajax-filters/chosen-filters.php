<?php
namespace ReyCore\Modules\AjaxFilters;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Handler for Product Grid element, integration
 * with product filters
 *
 * @since 1.5.4
 */
class ChosenFilters
{

	private $query = [];

	private $filters_data = [];

	private $count = 0;

	public function __construct( $url_query = [] ){

		if( ! empty($url_query) ) {
			$this->query = $url_query;
		}

		else if( isset($_SERVER['QUERY_STRING']) ) {
			$url = $_SERVER['QUERY_STRING'];
			parse_str($url, $this->query);
		}

		if( empty($this->query) ){
			return;
		}

		$this->handle__custom_keys();
		$this->handle__categories();
		$this->handle__tags();
		$this->handle__attributes();
		$this->handle__taxonomies();
		$this->handle__max_range();
		$this->handle__min_range();
		$this->handle__custom_fields();
		$this->handle__min_price();
		$this->handle__max_price();
		$this->handle__stock();
		$this->handle__sale();
		$this->handle__featured();
		$this->handle__meta();
		$this->handle__rating();
		$this->handle__keyword();
		$this->handle__order();

	}

	public function get_data(){
		return wc_clean( apply_filters('reycore/ajaxfilters/chosen_filters', [
			'count'          => $this->count,
			'filters'        => $this->filters_data,
		]) );
	}

	/**
	 * Custom Keys
	 */
	public function handle__custom_keys(){

		foreach (Helpers::get_custom_keys() as $key => $data)
		{
			if( ! ($filters = reycore__preg_grep_keys("/{$key}/", $this->query)) ){
				continue;
			}

			if( empty($filters) ){
				continue;
			}

			if( empty( $filters[$key] ) ) {
				continue;
			}

			$values = reycore__clean(explode(',', $filters[ $key ]));

			if( isset($data['type']) && 'taxonomy' === $data['type'] ){

				$taxonomy = $data['value'];

				// loop through values
				foreach ($values as $term_value) {

					$term_value = reycore__clean($term_value);
					$term_item = [];

					if( is_numeric($term_value) ){
						$term_item = [
							'id'  => $term_value,
							'key' => $key,
						];
					}

					else {
						$category_term_object = get_term_by('slug', $term_value, $taxonomy, 'absint');
						if( isset($category_term_object->term_id) ){
							$term_item = [
								'id'   => $category_term_object->term_id,
								'slug' => $term_value,
								'key'  => $key,
							];
						}
					}

					if( empty($term_item) ){
						continue;
					}

					$this->filters_data['tax'][$taxonomy]['query_type'] = 'or';
					$this->filters_data['tax'][$taxonomy][] = $term_item;
					$this->count++;

				}

			}

			elseif( isset($data['type']) && 'cf' === $data['type'] )
			{
				$this->filters_data['cf'][$data['value']] = [
					'field_name' => $data['value'],
					'key'        => $key,
					'terms'      => $values,
				];
				foreach ( $values as $v)
				{
					$this->count++;
				}
			}
		}
	}

	/**
	 * Product Category
	 */
	public function handle__categories(){

		if( ! ($category_filters = reycore__preg_grep_keys('/product-cat/', $this->query))){
			return;
		}

		if( empty($category_filters)){
			return;
		}

		$category_filters_keys = [
			'product-cata' => 'and',
			'product-cato' => 'or',
		];

		$category_taxonomy = 'product_cat';

		$_cat_keys = array_keys( array_intersect_key($category_filters_keys, $category_filters ) );

		if( empty($_cat_keys) ){
			return;
		}

		$category_filters_key = $_cat_keys[0];
		$category_filters_query_type = $category_filters_keys[$category_filters_key];

		if( empty($category_filters[$category_filters_key]) ) {
			return;
		}

		// loop through category values
		foreach (explode(',', $category_filters[ $category_filters_key ]) as $term_value) {

			$term_value = reycore__clean($term_value);
			$term_item = [];

			if( is_numeric($term_value) ){

				$term_item = [
					'id'         => absint($term_value),
					'key'        => $category_filters_key,
				];

			}

			else {
				$category_term_object = get_term_by('slug', $term_value, $category_taxonomy, 'absint');
				if( isset($category_term_object->term_id) ){
					$term_item = [
						'id'   => $category_term_object->term_id,
						'slug' => $term_value,
						'key'  => $category_filters_key,
					];
				}
			}

			if( empty($term_item) ){
				continue;
			}

			$this->count++;

			$this->filters_data['tax'][$category_taxonomy]['query_type'] = $category_filters_query_type;
			$this->filters_data['tax'][$category_taxonomy][] = $term_item;

		}


	}

	public function handle__tags(){

		/**
		 * Product Tag
		 */
		if ( ($tag_filters = reycore__preg_grep_keys('/product-tag/', $this->query)) && !empty($tag_filters)) {

			$tag_filters_keys = [
				'product-taga' => 'and',
				'product-tago' => 'or',
			];

			$tag_filters_key = array_keys( array_intersect_key($tag_filters_keys, $tag_filters ) )[0];
			$tag_filters_query_type = $tag_filters_keys[$tag_filters_key];

			if( ! empty($tag_filters[$tag_filters_key]) ) {

				$tag_terms = explode(',', $tag_filters[ $tag_filters_key ]);
				$tag_terms = reycore__clean($tag_terms);
				$tag_taxonomy = 'product_tag';

				foreach ($tag_terms as $term_id) {

					$this->filters_data['tax'][$tag_taxonomy][] = [
						'id'         => $term_id,
						'key'        => $tag_filters_key,
						'query_type' => $tag_filters_query_type,
					];

					$this->count++;
				}
			}
		}

	}

	public function handle__attributes(){

		/**
		 * Product Attributes
		 */
		if ( ($attribute_filters = reycore__preg_grep_keys('/^attr/', $this->query)) && !empty($attribute_filters)) {

			$attribute_filters_keys = [
				'attra' => 'and',
				'attro' => 'or',
			];

			foreach ($attribute_filters as $akey => $avalue) {

				if( $avalue === '' ){
					continue;
				}

				$attribute_taxonomy_slug = '';
				$attribute_filters_query_type = 'and';

				foreach ($attribute_filters_keys as $k => $v) {
					if( strpos($akey, $k ) !== false ){
						$attribute_filters_query_type = $v;
						$attribute_taxonomy_slug = str_replace( $k . '-', '', $akey );
					}
				}

				if( empty($attribute_taxonomy_slug) ){
					continue;
				}

				$attribute_terms = reycore__clean(explode(',', $avalue));

				$attribute_taxonomy_slug_clean = wc_sanitize_taxonomy_name ( $attribute_taxonomy_slug );
				$attribute_taxonomy = $attribute_taxonomy_slug_clean;

				if( strpos($attribute_taxonomy_slug_clean, 'pa_') === false ){
					$attribute_taxonomy = wc_attribute_taxonomy_name($attribute_taxonomy_slug_clean);
				}

				foreach ($attribute_terms as $term_value) {

					if( is_numeric($term_value) ){

						$this->filters_data['tax'][$attribute_taxonomy][] = [
							'id'         => absint($term_value),
							'key'        => $akey,
							'query_type' => $attribute_filters_query_type,
						];

						$this->count++;
					}

					else {
						$_term_object = get_term_by('slug', $term_value, $attribute_taxonomy, 'absint');
						if( isset($_term_object->term_id) ){
							$this->filters_data['tax'][$attribute_taxonomy][] = [
								'id'         => $_term_object->term_id,
								'slug' => $term_value,
								'key'        => $akey,
								'query_type' => $attribute_filters_query_type,
							];
							$this->count++;
						}
					}

				}
			}
		}

	}

	public function handle__taxonomies(){

		/**
		 * Custom taxonomies
		 */
		foreach (Base::get_registered_taxonomies() as $taxonomy) {

			$tid = str_replace('-', '', sanitize_title( $taxonomy['name'] ));

			if ( ($ctax_filters = reycore__preg_grep_keys("/product-{$tid}/", $this->query)) && !empty($ctax_filters)) {

				$ctax_filters_keys = [
					"product-{$tid}a" => "and",
					"product-{$tid}o" => "or",
				];

				$__filters_key = array_keys( array_intersect_key($ctax_filters_keys, $ctax_filters ) );

				if( empty($__filters_key) ){
					continue;
				}

				$ctax_filters_key = $__filters_key[0];
				$ctax_filters_query_type = $ctax_filters_keys[$ctax_filters_key];

				$ctax_terms = reycore__clean( explode(',', $ctax_filters[ $ctax_filters_key ]) );
				$ctax_taxonomy = $taxonomy['id'];

				foreach ($ctax_terms as $term_value) {

					if( is_numeric($term_value) ){

						$this->filters_data['tax'][$ctax_taxonomy][] = [
							'id'         => absint($term_value),
							'key'        => $ctax_filters_key,
							'query_type' => $ctax_filters_query_type,
						];

						$this->count++;
					}

					else {
						$_term_object = get_term_by('slug', $term_value, $ctax_taxonomy, 'absint');
						if( isset($_term_object->term_id) ){
							$this->filters_data['tax'][$ctax_taxonomy][] = [
								'id'         => $_term_object->term_id,
								'slug'       => $term_value,
								'key'        => $ctax_filters_key,
								'query_type' => $ctax_filters_query_type,
							];
							$this->count++;
						}
					}

				}
			}
		}

	}

	private function __ranges( $type, $filter_key ){

		if( ! ($range_attribute_filters = reycore__preg_grep_keys("/^{$type}-/", $this->query)) ){
			return;
		}

		if( empty($range_attribute_filters) ){
			return;
		}

		foreach ($range_attribute_filters as $akey => $term) {

			if( strpos($akey, $type ) === false ){
				continue;
			}

			$term = min(floatval($term), 1000000000);
			$attribute_taxonomy_slug = wc_sanitize_taxonomy_name ( str_replace( $type . '-', '', $akey ) );
			$attribute_taxonomy = $attribute_taxonomy_slug;

			if( strpos($attribute_taxonomy_slug, 'pa_') === false ){
				$attribute_taxonomy = wc_attribute_taxonomy_name($attribute_taxonomy_slug);
			}

			$this->filters_data[$filter_key][$attribute_taxonomy] = $term;
			$this->count++;

		}
	}

	/**
	 * Range Attributes - Max Range
	 */
	public function handle__max_range()
	{
		$this->__ranges('max-range', 'range_max');
	}

	/**
	 * Range Attributes - Min Range
	 */
	public function handle__min_range()
	{
		$this->__ranges('min-range', 'range_min');
	}

	/**
	 * Custom Fields
	 */
	public function handle__custom_fields(){

		$cf_key = Base::CF_KEY;

		if( ! ($cf_filters = reycore__preg_grep_keys("/^{$cf_key}/", $this->query)) ){
			return;
		}

		if( empty($cf_filters) ){
			return;
		}

		foreach ($cf_filters as $prefixed_key => $value)
		{

			$prefixed_key = reycore__clean($prefixed_key); // ex: cf-my-custom-field
			$clean_key = str_replace($cf_key, '', $prefixed_key); // ex: my-custom-field
			$values = reycore__clean(explode(',', $value));

			$this->filters_data['cf'][$clean_key] = [
				'field_name' => $clean_key,
				'key'        => $prefixed_key,
				'terms'      => $values,
			];

			foreach ( $values as $v)
			{
				$this->count++;
			}
		}
	}

	/**
	 * Minimum Price
	 */
	public function handle__min_price(){

		if (isset($this->query['min-price'])) {
			$this->filters_data['min_price'] = min(reycore__clean($this->query['min-price']), 1000000000);
			$this->count++;
		}
	}

	/**
	 * Maximum Price (cannot be 0)
	 */
	public function handle__max_price(){

		if (!empty($this->query['max-price'])) {
			$this->filters_data['max_price'] = min(reycore__clean($this->query['max-price']), 1000000000);
			$this->count++;
		}
	}

	/**
	 * Stock Products
	 */
	public function handle__stock(){

		if ( ! isset($this->query['in-stock']) ) {
			return;
		}

		$stock_val = false;

		// all
		if( 0 === absint($this->query['in-stock']) ) {
			$stock_val = 0;
		}
		// in stock
		else if( 1 === absint($this->query['in-stock']) ) {
			$stock_val = 1;
		}
		// out of stock
		else if( 2 === absint($this->query['in-stock']) ) {
			$stock_val = 2;
		}

		if( false !== $stock_val ){
			$this->filters_data['in-stock'] = $stock_val;
			$this->count++;
		}

	}

	/**
	 * On Sale products
	 */
	public function handle__sale(){

		if ( apply_filters('reycore/ajaxfilters/query/on_sale', isset($this->query['on-sale']) && 1 === absint($this->query['on-sale']) ) ) {
			$this->filters_data['on-sale'] = true;
			$this->count++;
		}
	}

	/**
	 * Featured products
	 */
	public function handle__featured(){

		if ( apply_filters('reycore/ajaxfilters/query/featured', isset($this->query['is-featured']) && 1 === absint($this->query['is-featured']) ) ) {
			$this->filters_data['is-featured'] = true;
			$this->count++;
		}
	}

	/**
	 * Product Meta
	 */
	public function handle__meta(){

		if (
			isset($this->query['product-meta'])
			&& !empty($this->query['product-meta'])
			&& $mq_hashes = reycore__clean($this->query['product-meta'])
		) {
			$this->filters_data['product-meta'] = explode(',', $mq_hashes);
			$this->count++;
		}
	}

	/**
	 * Rating
	 */
	public function handle__rating(){

		if (!empty($this->query['min_rating'])) {
			$this->filters_data['min_rating'] = floatval(reycore__clean($this->query['min_rating']));
			$this->count++;
		}
	}

	/**
	 * Keyword (Search)
	 */
	public function handle__keyword(){
		if (!empty($this->query['keyword'])) {
			$this->filters_data['keyword'] = reycore__clean($this->query['keyword']);
			$this->count++;
		}

	}

	/**
	 * Order by
	 */
	public function handle__order(){

		if (!empty($this->query['orderby'])) {
			$this->filters_data['orderby'] = reycore__clean($this->query['orderby']);
		}
	}


}
