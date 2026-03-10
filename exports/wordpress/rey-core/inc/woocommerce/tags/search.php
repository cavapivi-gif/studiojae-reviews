<?php
namespace ReyCore\WooCommerce\Tags;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Search {

	public static $sku_priority = 11;
	public static $posts_search_hooks_priority = 10;
	public static $safe_mode = false;

	public function __construct()
	{
		add_action( 'init', [$this, 'init']);
		add_action( 'reycore/customizer/section=header-search', [ $this, 'add_search_types_option' ] );
		add_action( 'reycore/ajax/register_actions', [ $this, 'register_actions' ] );

		do_action('reycore/woocommerce/search', $this);
	}

	function init(){
		add_filter( 'rey/main_script_params', [ $this, 'script_params'], 10 );
		add_action( 'rey/search_form', [ $this, 'search_form' ], 10);
		add_action( 'reycore/search_panel/after_search_form', [ $this, 'results_html' ], 10);
		add_filter( 'reycore/cover/get_cover', [$this, 'search_page_cover'], 40);
		add_filter( 'theme_mod_header_position', [$this, 'search_page_header_position'], 40);
		add_filter( 'rey_acf_option_header_position', [$this, 'search_page_header_position'], 40);
		add_filter( 'acf/load_value', [$this, 'search_page_reset_header_text_color'], 10, 3);
		add_filter( 'posts_clauses', [$this, 'product_search_sku'], self::$sku_priority, 2);
		add_filter( 'posts_where', [$this, '__search_where'], self::$posts_search_hooks_priority, 2);
		add_filter( 'posts_join', [$this, '__search_join'], self::$posts_search_hooks_priority, 2);
		add_filter( 'posts_groupby', [$this, '__search_groupby'], self::$posts_search_hooks_priority, 2);
		add_action( 'woocommerce_no_products_found', [$this, 'empty_page_gs'], 4);
	}

	function add_search_types_option( $section ) {

		$section->add_control( [
			'type'        => 'repeater',
			'settings'    => 'search_supported_post_types_list',
			'label'       => esc_html__('Post Type list in Search form', 'rey-core'),
			'description' => __('Choose multiple post types to add to the search form select list. List will display if more than one is selected', 'rey-core'),
			'row_label' => [
				'type' => 'field',
				'value' => esc_html__('Post Type', 'rey-core'),
				'field' => 'post_type',
			],
			'button_label' => esc_html__('New Post Type', 'rey-core'),
			'default'      => [
				[
					'post_type' => 'product',
					'title' => 'SHOP'
				],
			],
			'fields' => [
				'post_type' => [
					'type'        => 'select',
					'label'       => esc_html__('Post Type', 'rey-core'),
					'choices'     => [
						'' => '-- Select --'
					] + reycore__get_post_types_list(),
				],
				'title' => [
					'type'        => 'text',
					'label'       => esc_html__('Title', 'rey-core'),
				]
			],
		] );

	}

	/**
	 * Filter main script's params
	 *
	 * @since 1.0.0
	 **/
	public function script_params($params)
	{
		$params['search_texts'] = [
			'NO_RESULTS' => esc_html__('Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'rey-core'),
		];
		$params['ajax_search_only_title'] = false;
		$params['ajax_search'] = get_theme_mod('header_enable_ajax_search', true);
		return $params;
	}

	public function register_actions($ajax_manager){
		$ajax_manager->register_ajax_action( 'ajax_search', [$this, 'run_ajax_search'], [
			'auth'   => 3,
			'nonce'  => false,
		] );
	}

	public function run_ajax_search( $data ){

		// return if search is disabled
		if( ! get_theme_mod('header_enable_ajax_search', true) ) {
			return;
		}

		$search_string = reycore__clean( $data['s'] );

		if( empty($search_string) ) {
			return ['errors' => esc_html__('Empty string!', 'rey-core')];
		}

		wc_set_loop_prop( 'is_search', true );

		if( $before_results_process = apply_filters('reycore/woocommerce/search/pre_request', [], $search_string, $this) ){
			return $before_results_process;
		}

		do_action('reycore/woocommerce/search/before_request', $search_string, $this);

		$args = array_merge(
			WC()->query->get_catalog_ordering_args('relevance'),
			[
				's'             => $search_string,
				'cache_results' => true,
				'post_type'     => isset($data['post_type']) ? reycore__clean( $data['post_type'] ) : ''
			]
		);

		if( (defined('WP_DEBUG') && WP_DEBUG) || isset($data['lang']) ){
			$args['cache_results'] = false;
		}

		if( isset($data['product_cat']) && $product_cat = reycore__clean($data['product_cat']) ){
			$args['product_cat'] = $product_cat;
		}

		$args = apply_filters_deprecated('reycore/woocommerce/search/rest_args', [$args], '2.3.0' );

		$results = $this->json_results( $this->search_products_query( $args ) );

		return $results;
	}


	/**
	 * Query
	 *
	 * @since   1.0.0
	 */
	public function search_products_query( $args = [] )
	{
		reycore__maybe_disable_obj_cache();

		$args = apply_filters('reycore/woocommerce/search/ajax_args',
			wp_parse_args( $args, [
				'post_type'           => 'product',
				'post_status'         => 'publish',
				's'                   => '',
				'paged'               => 0,
				'orderby'             => 'relevance',
				'order'               => 'asc',
				'posts_per_page'      => 5,
				'cache_results'       => false,
				'cache_timeout'       => 4,
			] )
		);

		if ( 'product' === $args['post_type'] ) {

			$args['tax_query'][] = [
				'taxonomy' => 'product_visibility',
				'field'    => 'name',
				'terms'    => ['exclude-from-catalog', 'exclude-from-search'],
				'operator' => 'NOT IN',
			];

			if ( 'yes' == get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
				$args['meta_query'][] = [
					'key'     => '_stock_status',
					'value'   => 'outofstock',
					'compare' => 'NOT LIKE',
				];
			}

			if( isset($args['product_cat']) && $cat = $args['product_cat'] ){
				$args['tax_query'][] = [
					'taxonomy' => 'product_cat',
					'field'    => 'slug',
					'terms'    => $cat,
					'operator' => 'IN',
				];
			}
		}

		set_query_var('rey_search', true );
		set_query_var('search_terms', explode(' ', $args['s']) );

		// add_action( 'parse_query', function($query){
		// 	$query->is_search = true;
		// } );

		do_action('reycore/woocommerce/search/before_search_products_query');

		if ( true === $args['cache_results'] ) {
			$dynamic_key    = md5( sanitize_text_field( $args['s'] ) );
			$transient_name = "rey-wc-search-results_{$dynamic_key}";
			$timeout        = absint( sanitize_text_field( $args['cache_timeout'] ) );
			if ( false === ( $the_query = get_transient( $transient_name ) ) ) {
				$the_query = new \WP_Query( $args );
				set_transient( $transient_name, $the_query, $timeout * HOUR_IN_SECONDS );
			}
		} else {
			$the_query = new \WP_Query( $args );
		}

		do_action('reycore/woocommerce/search/search_products_query', $the_query);

		return $the_query;
	}


	/**
	 * Gets the default search value linking to a search page with an "s" query string
	 * @since   1.0.0
	 */
	protected function get_default_search_value( \WP_Query $query, $result ) {

		$post_type = 'product';

		if( isset($_REQUEST[ \ReyCore\Ajax::DATA_KEY ]) && $action_data = reycore__clean($_REQUEST[ \ReyCore\Ajax::DATA_KEY ]) ){
			if( isset($action_data['post_type']) && ($search_post_type = $action_data['post_type']) ){
				$post_type = $search_post_type;
			}
		}

		$search_permalink = add_query_arg( array(
			's'         => sanitize_text_field( $query->query_vars['s'] ),
			'post_type' => $post_type,
		), get_home_url() );

		$result['items'][] = array(
			'default'   => true,
			'id'        => get_the_ID(),
			'text'      => esc_html__('View all results', 'rey-core') . sprintf( '<span class="__search-count">%d</span>', $query->found_posts ),
			'permalink' => $search_permalink,
		);

		return $result;
	}

	/**
	 * Converts a wp_query result in a select 2 format result
	 * @since   1.0.0
	 */
	public function json_results( \WP_Query $query, $args = [] )
	{
		$result = [
			'items' => [],
			'total_count' => 0
		];

		if ( $query->have_posts() ) {

			while ( $query->have_posts() ) {

				$query->the_post();

				if( 'publish' !== get_post_status() ){
					continue;
				}

				do_action('reycore/woocommerce/search/before_get_data');

				add_filter( 'reycore/woocommerce/loop/render/thumbnails_slideshow', '__return_false');

				$post_id = get_the_ID();

				$result_item = [
					'id'        => $post_id,
					'text'      => get_the_title(),
					'permalink' => get_permalink( $post_id ),
					'img'       => '',
					'price'     => apply_filters('reycore/woocommerce/ajax_search/price', $this->get_product_price() ),
				];

				if ( $post_thumbnail_id = get_post_thumbnail_id( $post_id ) ) {
					$result_item['img'] = wp_get_attachment_image_url( $post_thumbnail_id,  'woocommerce_thumbnail' );
				}

				$result['items'][] = apply_filters('reycore/woocommerce/ajax_search/result_items', $result_item);

			}

			$result['total_count']    = $query->found_posts;
			$result['posts_per_page'] = $query->query_vars['posts_per_page'];

			wp_reset_postdata();

			if ( $result['total_count'] > $result['posts_per_page']) {
				$result = $this->get_default_search_value( $query, $result );
			}
		}

		return $result;
	}

	public function get_product_price() {

		if( $product = wc_get_product() ) {
			return $product->get_price_html();
		}

		return '';
	}

	/**
	 * Make search form product type
	 * @since 1.0.0
	 **/
	public function search_form(){

		if( apply_filters('reycore/woocommerce/search/prevent_post_type', false) ){
			return;
		}

		reycore_assets()->add_scripts(['reycore-wc-header-ajax-search', 'rey-tmpl']);

		$post_type_field = '<input type="hidden" name="post_type" value="product" />';

		if( get_theme_mod('header_enable_categories', false) ){

			$cat_list_text = esc_html__('Category', 'woocommerce');

			printf('<label class="rey-searchForm-list rey-searchForm-cats" data-lazy-hidden><span>%1$s</span>', $cat_list_text);

			wc_product_dropdown_categories( apply_filters( 'reycore/search/categories_list_args', [
				'hierarchical'       => true,
				'show_uncategorized' => 0,
				'show_count'         => true,
				'show_option_none'  => $cat_list_text,
				'class'         => 'rey-searchForm-catList',
			] ) );

			echo '</label>';

		}

		if( $post_types = get_theme_mod('search_supported_post_types_list', []) ){

			if( count($post_types) === 1 && !empty($post_types[0]['post_type']) ){
				$post_type_field = sprintf('<input type="hidden" name="post_type" value="%s" />', $post_types[0]['post_type']);
			}

			else {

				$plist = reycore__get_post_types_list();
				$options = '';
				$first = '';

				foreach ($post_types as $key => $value) {
					$post_type = $value['post_type'] ? $value['post_type'] : 'post';
					$title = isset($value['title']) && !empty($value['title']) ? $value['title'] : ( isset($plist[$post_type]) ? $plist[$post_type] : '' );
					$options .= sprintf('<option value="%1$s">%2$s</option>', esc_attr($post_type), $title );
					if( $key === 0 ){
						$first = $title;
					}
				}

				printf('<label class="rey-searchForm-list rey-searchForm-postType" data-lazy-hidden><span>%2$s</span><select name="post_type" >%1$s</select></label>', $options, $first);
				return;
			}
		}

		echo $post_type_field;
	}

	/**
	 * Adds markup for Ajax Search's results
	 *
	 * @since 1.0.0
	 */
	function results_html()
	{
		$classes = [];

		if( class_exists('\ReyCore\WooCommerce\Loop') && \ReyCore\WooCommerce\Loop::is_custom_image_height() ) {
			$classes[] = '--customImageContainerHeight';
		} ?>

		<div class="rey-searchResults js-rey-searchResults <?php echo esc_attr( implode(' ', $classes) ) ?>"></div>
		<div class="rey-lineLoader"></div>
		<?php

		reycore_assets()->add_styles('rey-buttons');

		add_action( 'wp_footer', [ $this, 'search_template' ], 20);
	}

	/**
	 * Search template used for search drop panel.
	 * @since 1.0.0
	 */
	function search_template(){
		reycore__get_template_part('template-parts/woocommerce/search-panel-results');
	}

	function is_product_post_type($wp_query){

		if( ! isset($wp_query->query_vars['post_type']) ){
			return false;
		}

		$post_type = $wp_query->query_vars['post_type'];

		if( is_array($post_type) ){
			$is_product = in_array('product', $post_type, true);
		}
		else {
			$is_product = 'product' === $post_type;
		}

		return $is_product;
	}

	function product_search_sku( $args, $wp_query ){

		if( self::$safe_mode ){
			return $args;
		}

		$sku_search_type = get_theme_mod('search__sku', 'yes');

		if( $sku_search_type === false ){
			return $args;
		}

		if( $sku_search_type === 'no' ){
			return $args;
		}

		if( ! $this->is_product_post_type($wp_query) ){
			return $args;
		}

		$where = $args['where'];

		global $pagenow, $wpdb;

		if (
			( ! wp_doing_ajax() && is_admin() && 'edit.php' != $pagenow ) ||
			! ($wp_query->is_search || get_query_var('rey_search') ) ||
			! (isset($wp_query->query_vars['s']) && $wp_query->query_vars['s'])
		) {
			return $args;
		}

		$search_ids = [];
		$terms = explode(',', $wp_query->query_vars['s']);

		foreach ($terms as $term) {

			$term = trim($term);

			//Include search by id if admin area.
			if (is_admin() && is_numeric($term)) {
				$search_ids[] = $term;
			}

			$sku_products = [];

			$query_config = [
				'numberposts' => -1,
				'post_type'   => 'product',
				'fields'      => 'ids',
				'meta_query'  => [
					[
						'key'       => '_sku',
						'value'     => reycore__clean($term),
						'compare'   => 'LIKE',
					]
				],
			];

			$show_product_type = get_theme_mod('search__sku_show', 'parents'); // parents, variations, both

			// SEARCH IN Parents only
			// "Yes - in Parent products only"
			if( 'yes' === $sku_search_type ){
				$sku_products = get_posts($query_config);
			}

			// SEARCH IN Parents and Variations
			// "Yes - in Parents & Variations"
			elseif( 'yes-variations' === $sku_search_type ){
				$query_config['post_type'] = ['product', 'product_variation'];
				$query_config['fields'] = 'id=>parent';
				$simple_products_query_config = $query_config;
				$simple_products_query_config['post_type'] = ['product'];
				$simple_products_query_config['fields'] = 'ids';
				$sku_products = array_merge(get_posts($query_config), get_posts($simple_products_query_config));
			}

			// SEARCH IN Variations only
			// "Yes - Only in Variations"
			elseif( 'yes-variations-only' === $sku_search_type ){
				$query_config['post_type'] = 'product_variation';
				$query_config['fields'] = 'id=>parent';
				$sku_products = get_posts($query_config);
			}

			// For variations
			if( in_array($sku_search_type, ['yes-variations', 'yes-variations-only'], true) ){

				// determine what type of product types to show (parents, variations or both)
				if( in_array($show_product_type, ['variations', 'both'], true) ){

					// Filter for children only
					$variables = array_filter(array_flip($sku_products), function($parent_id){
						return $parent_id !== 0;
					}, ARRAY_FILTER_USE_KEY);

					// variations only
					if( 'variations' === $show_product_type ){
						$sku_products = $variables;
					}
					// both parents and variabiles
					else if( 'both' === $show_product_type ){
						$sku_products = array_merge($variables, $sku_products);
					}
				}
			}

			$search_ids = array_merge($search_ids, $sku_products);

		}

		$search_ids = array_unique(array_filter(array_map('absint', $search_ids)));

		if (!empty($search_ids)) {
			$where = str_replace('))', ") OR ({$wpdb->posts}.ID IN (" . implode(',', $search_ids) . ")))", $where);
		}

		$args['where'] = $where;

		reycore__remove_filters_for_anonymous_class('posts_search', 'WC_Admin_Post_Types', 'product_search', 10);

		return $args;
	}

	function search_includes(){
		return array_filter( apply_filters('reycore/woocommerce/search_taxonomies', get_theme_mod('search__include', [])), function($el){
			return taxonomy_exists($el);
		});
	}

	function __search_where($where, $wp_query){

		if( self::$safe_mode ){
			return $where;
		}

		if( ! $this->is_product_post_type($wp_query) ){
			return $where;
		}

		global $wpdb;

		if( empty($this->search_includes()) ){
			return $where;
		}

		if ( ! (is_search() || get_query_var('rey_search')) ) {
			return $where;
		}

		$search_terms = get_query_var( 'search_terms' );

		if( empty($search_terms) ){
			return $where;
		}

		// Prepare the term name search condition
		$term_name_condition = '';
		$i = 0;
		foreach ($search_terms as $search_term) {
			$i++;
			if ($i > 1) {
				$term_name_condition .= " OR";
			}
			$term_name_condition .= $wpdb->prepare( ' (t.name LIKE %s)', '%' . $wpdb->esc_like( $search_term ) . '%' );
		}

		// Now locate where to insert the condition
		// preferrably before the post content search condition
		$search_position = strpos($where, " OR ({$wpdb->posts}.post_content LIKE");

		// If we find the correct position, insert the term name condition
		if ($search_position !== false) {
			$before = substr($where, 0, $search_position); // Get the part of the WHERE clause before the title search condition
			$after = substr($where, $search_position); // Get the rest of the WHERE clause (from the position of the title search onward)
			$where = $before . ' OR ' . $term_name_condition . '' . $after;
		}
		else {
			$where .= " OR (" . $term_name_condition . ")"; // Fallback: Just append the term name condition at the end if no exact position is found
		}

		return $where;
	}

	function __search_join($join, $wp_query){

		if( self::$safe_mode ){
			return $join;
		}

		if( ! $this->is_product_post_type($wp_query) ){
			return $join;
		}

		global $wpdb;

		$includes = $this->search_includes();

		if( empty($includes) ){
			return $join;
		}

		if ( ! (is_search() || get_query_var('rey_search')) ) {
			return $join;
		}


		foreach ($includes as $key => $inc) {
			if( ! taxonomy_exists($inc) ){
				continue;
			}
			$on[] = sprintf("tt.taxonomy = '%s'", esc_sql($inc));
		}

		// build our final string
		$on = ' ( ' . implode( ' OR ', $on ) . ' ) ';
		$join .= " LEFT JOIN {$wpdb->term_relationships} AS tr ON ({$wpdb->posts}.ID = tr.object_id) LEFT JOIN {$wpdb->term_taxonomy} AS tt ON ( " . $on . " AND tr.term_taxonomy_id = tt.term_taxonomy_id) LEFT JOIN {$wpdb->terms} AS t ON (tt.term_id = t.term_id) ";

		return $join;
	}

	function __search_groupby($groupby, $wp_query){

		if( self::$safe_mode ){
			return $groupby;
		}

		if( ! $this->is_product_post_type($wp_query) ){
			return $groupby;
		}

		global $wpdb;

		if( empty($this->search_includes()) ){
			return $groupby;
		}

		// we need to group on post ID
		$groupby_id = "{$wpdb->posts}.ID";

		if( ! (is_search() || get_query_var('rey_search')) || strpos($groupby, $groupby_id) !== false) {
			return $groupby;
		}

		// groupby was empty, use ours
		if(!strlen(trim($groupby))) {
			return $groupby_id;
		}

		// wasn't empty, append ours
		return $groupby.", ".$groupby_id;
	}


	public function search_page_cover( $cover ){

		if( ! is_search() ){
			return $cover;
		}

		$search_cover = get_theme_mod('cover__search_page', 'no');

		if( $search_cover === 'no' ){
			return false;
		}

		return $search_cover;
	}

	public function search_page_header_position( $pos ){

		if( ! is_search() ){
			return $pos;
		}

		if( $search_header_pos = get_theme_mod('search__header_position', 'rel') ){
			return $search_header_pos;
		}

		return $pos;
	}

	public function search_page_reset_header_text_color( $value, $post_id, $field ){

		if( $field['name'] === 'header_text_color' && is_search() ){
			return '';
		}

		return $value;
	}


	function empty_page_gs(){

		if( ! is_search() ){
			return;
		}

		if( ! class_exists('\ReyCore\Elementor\GlobalSections') ){
			return;
		}

		add_filter('theme_mod_loop_empty__gs', '__return_false');

		if( ! ($gs = get_theme_mod('loop_search_empty__gs', '')) ){
			return;
		}

		if( ! ($mode = get_theme_mod('loop_search_empty__mode', 'overwrite')) ){
			return;
		}

		if( 'overwrite' === $mode ){
			remove_action( 'woocommerce_no_products_found', 'wc_no_products_found', 10 );
			echo \ReyCore\Elementor\GlobalSections::do_section($gs, false, true);
			return;
		}

		$pos[$mode] = \ReyCore\Elementor\GlobalSections::do_section($gs, false, true);

		if( isset($pos['before']) && ($before = $pos['before']) ){
			add_action( 'woocommerce_no_products_found', function() use ($before) {
				echo $before;
			}, 9 );
		}

		if( isset($pos['after']) && ($after = $pos['after']) ){
			add_action( 'woocommerce_no_products_found', function() use ($after) {
				echo $after;
			}, 11 );
		}

	}

}
