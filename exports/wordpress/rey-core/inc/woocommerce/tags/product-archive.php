<?php
namespace ReyCore\WooCommerce\Tags;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class ProductArchive {

	public $_args = [];
	public $_settings = [];
	public $_products;
	public $query_args = [];
	public static $_selectors_to_replace = [];
	public static $btn_styles = [];
	public $components_statuses = [];
	public $query;

	function __construct( $args, $settings = [] ){

		if( empty( $settings ) && ! isset( $args['product_ids'] ) ){
			return;
		}

		$this->_products = (object)[];

		$this->_args = wp_parse_args($args, [
			'name'        => '',
			'filter_name' => '',
			'main_class'  => '',
			'filter_button'  => false,
			'attributes'  => [],
			'el_instance' => false
		]);

		$this->_settings = wp_parse_args($settings, [
			'_skin'      => '',
			'query_type' => 'current_query',
			'paginate'   => null,
			'per_row'    => wc_get_default_products_per_row(),
			'show_header' => false,
		]);

		if( ! isset( $this->_args['product_ids'] ) ){
			$this->get_query_args();
		}

		return $this;
	}

	/**
	 * Retrieves current loop skin
	 */
	public function get_loop_skin(){

		if( isset($this->_settings['loop_skin']) && $loop_skin = $this->_settings['loop_skin']){
			return $loop_skin;
		}

		return get_theme_mod('loop_skin', 'basic');
	}

	public function get_default_limit(){
		return apply_filters( 'loop_shop_per_page', wc_get_default_products_per_row() * wc_get_default_product_rows_per_page() );
	}

	/**
	 * Get query arguments based on settings.
	 *
	 * @since 1.0.0
	 */
	public function get_query_args(){

		$force_failed_query = false;
		$query_args = [];

		if ( 'current_query_original' === $this->_settings['query_type'] )
		{
			return [];
		}

		if ( 'acf' === $this->_settings['query_type'] )
		{
			return [];
		}

		else if ( 'current_query' === $this->_settings['query_type'] )
		{
			if( is_post_type_archive('product') || is_tax(get_object_taxonomies('product')) )
			{

				$query_args = array_filter($GLOBALS['wp_query']->query_vars);

				if( isset($this->_settings['limit']) ){
					$query_args['posts_per_page'] = !empty($this->_settings['limit'])  ? absint($this->_settings['limit']) : $this->get_default_limit();
				}

				else if ( isset($this->_settings['rows_per_page']) ){
					$query_args['posts_per_page'] = !empty($this->_settings['rows_per_page']) ?
						(absint($this->_settings['rows_per_page']) * absint($this->_settings['per_row'])) : $this->get_default_limit();
					// fix pagination
					$GLOBALS['wp_query']->set( 'posts_per_page', $query_args['posts_per_page'] );
				}

				if( empty($_REQUEST['orderby']) ){

					$change_selector_order = false;
					$query_args['orderby'] = '';

					if( isset($this->_settings['default_catalog_orderby']) && ($default_catalog_orderby = $this->_settings['default_catalog_orderby']) ){

						$query_args['orderby'] = $default_catalog_orderby;

						if( strpos($default_catalog_orderby, 'desc') !== false ){
							$query_args['orderby'] = 'price';
							$query_args['order'] = 'DESC';
						}

						$change_selector_order = true;

						$ordering_args         = WC()->query->get_catalog_ordering_args( $query_args['orderby'], $query_args['order'] );
						$query_args['orderby'] = $ordering_args['orderby'];
						$query_args['order']   = $ordering_args['order'];
						if ( $ordering_args['meta_key'] ) {
							$query_args['meta_key'] = $ordering_args['meta_key']; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
						}
					}

					else if( isset($this->_settings['orderby']) && $this->_settings['orderby'] ){
						$change_selector_order = true;
						$query_args['orderby'] = $this->_settings['orderby'];
					}

					if( isset($this->_settings['order']) && $this->_settings['order'] ){
						$query_args['order'] = $this->_settings['order'];
					}

					if( $change_selector_order ){
						add_filter('woocommerce_default_catalog_orderby', function( $value ) use ($query_args) {
							return $query_args['orderby'];
						});
					}

				}
				else {
					$ordering_args = WC()->query->get_catalog_ordering_args();
					$query_args['orderby'] = $ordering_args['orderby'];
					$query_args['order'] = $ordering_args['order'];
					if( $ordering_args['meta_key'] ){
						$query_args['meta_key'] = $ordering_args['meta_key'];
					}
				}

				$query_args['post_type'] = 'product';
				$query_args['fields'] = 'ids';

				$page = absint( empty( $_GET['product-page'] ) ? 1 : $_GET['product-page'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				if ( 1 < $page ) {
					$query_args['paged'] = absint( $page );
				}
			}
		}

		else
		{
			/**
			 * Create attributes for WC Shortcode Products
			 */
			$type = 'products';

			$atts = [
				// Widget query type
				'query_type'        => $this->_settings['query_type'],
				// Number of columns.
				'columns'        => $this->_settings['per_row'],
				// menu_order, title, date, rand, price, popularity, rating, or id.
				'orderby'        => isset($this->_settings['orderby']) ? $this->_settings['orderby'] : 'menu_order',
				// ASC or DESC.
				'order'          => isset($this->_settings['order']) ? $this->_settings['order'] : 'DESC',
				// Should shortcode output be cached.
				'cache'          => false,
				// Paginate
				'paginate'       => is_null($this->_settings['paginate']) ? false : $this->_settings['paginate'],
			];

			$atts['limit'] = !empty($this->_settings['limit'])  ? absint($this->_settings['limit']) : $this->get_default_limit();

			// Manual selection settings
			if ( $this->_settings['query_type'] == 'manual-selection' ) {
				$manual_ids = [];
				if( is_array($this->_settings['include']) ){
					$manual_ids[] = implode(',',$this->_settings['include']);
				}
				else {
					$manual_ids[] = trim($this->_settings['include']);
				}

				if( ! empty($this->_settings['include_qa']) && is_array($this->_settings['include_qa']) ){
					$manual_ids[] = implode(',',$this->_settings['include_qa']);
				}

				$atts['ids'] = implode(',', $manual_ids);

				$atts['orderby'] = 'post__in';
			}

			// Related
			elseif ( $this->_settings['query_type'] == 'related' ) {

				$product_id = $this->get_page_product_id();

				if( $custom_product_id = $this->_settings['custom_product_id'] ){
					$product_id = $custom_product_id;
				}

				$excludes = [];

				if( ($product = wc_get_product( $product_id )) && ($up_sells = $product->get_upsell_ids()) && ! empty($up_sells) ){
					$excludes = $up_sells;
				}

				$related_products = array_filter( array_map( 'wc_get_product', wc_get_related_products( $product_id, $atts['limit'], $excludes ) ), 'wc_products_array_filter_visible' );

				$related_products = wc_products_array_orderby( $related_products, $atts['orderby'], $atts['order'] );

				if( ! empty($related_products) ){

					$related_ids = [];

					foreach ($related_products as $related_product) {
						$related_ids[] = $related_product->get_id();
					}

					$atts['ids'] = implode(',', $related_ids);
				}
				else {
					$force_failed_query = true;
				}

			}

			// Cross Sells
			elseif ( $this->_settings['query_type'] == 'cross-sells' ) {

				// Array to hold cross-sell products IDs
				$cross_sell_ids = [];

				if( ! empty($this->_settings['cross_sells_current_product']) ){
					$current_product_id = $this->get_page_product_id();
					if( $cs_product = wc_get_product(absint($current_product_id)) ){
						$cross_sells = $cs_product->get_cross_sell_ids();
						$cross_sell_ids = array_merge($cross_sell_ids, $cross_sells);
					}
				}

				// use custom Product ID
				else if( $custom_product_id = $this->_settings['custom_product_id'] ){
					if( $cs_product = wc_get_product(absint($custom_product_id)) ){
						$cross_sells = $cs_product->get_cross_sell_ids();
						$cross_sell_ids = array_merge($cross_sell_ids, $cross_sells);
					}
				}

				// get cart items
				else if( isset(WC()->cart) ) {
					// Get cart items
					$cart_items = WC()->cart->get_cart();
					// Loop through cart items
					foreach ($cart_items as $cart_item_key => $cart_item) {
						if ($product = $cart_item['data']) {
							$cross_sells = $product->get_cross_sell_ids();
							$cross_sell_ids = array_merge($cross_sell_ids, $cross_sells);
						}
					}
				}

				// Remove duplicates
				if( ! empty($cross_sell_ids) ){
					$atts['ids'] = implode(',', array_unique($cross_sell_ids));
				}
				else {
					$force_failed_query = true;
				}
			}

			// Up Sells
			elseif ( $this->_settings['query_type'] == 'up-sells' ) {

				// Array to hold cross-sell products IDs
				$upsell_ids = [];

				if( is_cart() ) {
					// Get cart items
					$cart_items = WC()->cart->get_cart();
					// Loop through cart items
					foreach ($cart_items as $cart_item_key => $cart_item) {
						if ($product = $cart_item['data']) {
							$upsells = $product->get_upsell_ids();
							$upsell_ids = array_merge($upsell_ids, $upsells);
						}
					}
				}
				else {

					$up_product_id = $this->get_page_product_id();

					if( $custom_product_id = $this->_settings['custom_product_id'] ){
						$up_product_id = $custom_product_id;
					}

					if( $up_product = wc_get_product(absint($up_product_id)) ){
						$upsells = $up_product->get_upsell_ids();
						$upsell_ids = array_merge($upsell_ids, $upsells);
					}

				}

				// Remove duplicates
				if( ! empty($upsell_ids) ){
					$atts['ids'] = implode(',', array_unique($upsell_ids));
				}
				else {
					$force_failed_query = true;
				}

			}
			else {

				// categories
				if( !empty($this->_settings['categories']) ) {

					$atts['category'] = implode(',',$this->_settings['categories']);

					if( !in_array($this->_settings['query_type'], ['manual-selection', 'recently-viewed', 'recently-purchased', 'current_query', 'cross-sells', 'up-sells'], true) ){
						if(  'and' === $this->_settings['categories_query_type'] ){
							$atts['cat_operator'] = 'AND';
						}
						else if(  'not_in' === $this->_settings['categories_query_type'] ){
							$atts['cat_operator'] = 'NOT IN';
						}
					}

				}
				// tags
				if( !empty($this->_settings['tags']) ) {
					$atts['tag'] = implode(',',$this->_settings['tags']);

					if( !in_array($this->_settings['query_type'], ['manual-selection', 'recently-viewed', 'recently-purchased', 'current_query', 'cross-sells', 'up-sells'], true) ){
						if(  'and' === $this->_settings['tags_query_type'] ){
							$atts['tag_operator'] = 'AND';
						}
						else if(  'not_in' === $this->_settings['tags_query_type'] ){
							$atts['tag_operator'] = 'NOT IN';
						}
					}

				}
				// attributes
				if( isset($this->_settings['attribute']) && ($attribute = $this->_settings['attribute']) && ($terms = $this->_settings[ 'attribute__' . $attribute ]) ) {
					if( array_filter($terms) ){
						$atts['attribute'] = $attribute;
						$atts['terms'] = implode(',',$terms);
					}
				}
			}

			// Recent
			if ( $this->_settings['query_type'] == 'recent' ) {
				$atts['orderby'] = 'date';
				$atts['order'] = 'DESC';
				$type = 'recent_products';
			}
			elseif ( $this->_settings['query_type'] == 'sale' ) {
				$type = 'sale_products';
			}
			elseif ( $this->_settings['query_type'] == 'best-selling' ) {
				$type = 'best_selling_products';
			}
			elseif ( $this->_settings['query_type'] == 'featured' ) {
				$atts['visibility'] = 'featured';
				$type = 'featured_products';
			}

			if( current_user_can('administrator') && isset($this->_settings['debug__show_query']) && $this->_settings['debug__show_query'] === 'yes' ){
				echo '<pre><h4>Atts:</h4>';
				var_dump( $atts );
				echo '</pre>';
			}

			$shortcode = new \WC_Shortcode_Products( $atts, $type );
			$query_args = $shortcode->get_query_args();

		}

		$hide_out_of_stock = 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' );

		if( isset($this->_settings['hide_out_of_stock']) && ($custom_oos = $this->_settings['hide_out_of_stock']) ){
			$hide_out_of_stock = 'no' !== $custom_oos;
		}

		// Exclude duplicates
		$to_exclude = [];

		if(
			is_singular('product')
			&& ($this_product = wc_get_product())
			&& ($this_product_id = $this_product->get_id())
			&& ($this_product_id === get_queried_object_id())
		){
			$to_exclude[] = $this_product_id;
		}

		if( isset($this->_settings['exclude_duplicates']) &&
			$this->_settings['exclude_duplicates'] !== '' &&
			isset($GLOBALS["rey_exclude_products"]) &&
			!empty($GLOBALS["rey_exclude_products"]) ){
			$to_exclude = $GLOBALS["rey_exclude_products"];
		}

		/**
		* If we have products on sale, and we want to exclude,
		* override the query args.
		*/
		if( 'sale' === $this->_settings['query_type'] ) {

			if( isset($query_args['post__in']) && !empty($query_args['post__in']) ) {

				// exclude
				if( ! empty($this->_settings['exclude']) ) {
					$excludes = array_map( 'trim', explode( ',', $this->_settings['exclude'] ) );
					$excludes = array_merge($excludes, $to_exclude);
					$query_args['post__in'] = array_diff( $query_args['post__in'], array_map( 'absint', $excludes ) );
				}

			}
		}

		/**
		* Get Top Rated
		*/
		elseif ( $this->_settings['query_type'] == 'top' ) {
			$query_args['meta_key'] = '_wc_average_rating';
			$query_args['orderby'] = 'meta_value_num';
			$query_args['order'] = 'DESC';
		}
		/**
		* Recently Viewed
		*/
		elseif ( $this->_settings['query_type'] == 'recently-viewed' ) {
			$viewed_products = ! empty( $_COOKIE['woocommerce_recently_viewed'] ) ? (array) explode( '|', wp_unslash( $_COOKIE['woocommerce_recently_viewed'] ) ) : [];
			$query_args['post__in'] = array_reverse( array_filter( array_map( 'absint', $viewed_products ) ) );
			$query_args['orderby']  = 'post__in';
			// $query_args['order'] = 'DESC';
		}
		/**
		* Recently Purchased
		*/
		elseif ( $this->_settings['query_type'] == 'recently-purchased' ) {

			// last 10 orders
			$customer_orders_limit = apply_filters( "reycore/elementor/{$this->_args['filter_name']}/recently_purchased_order_limit", 10, $this );

			$customer_orders = wc_get_orders([
				'customer_id' => get_current_user_id(),
				'limit' => $customer_orders_limit,
			]);

			$recently_purchased_product_ids = [];

			if( ! is_wp_error($customer_orders) ){

				foreach ($customer_orders as $customer_order) {

					if( isset($query_args['posts_per_page']) &&
						count($recently_purchased_product_ids) > absint($query_args['posts_per_page']) ){
						continue;
					}

					$product_items = $customer_order->get_items();

					foreach ( $product_items as $product_item ) {

						/**
						 * @var WC_Abstract_Order $product_item
						 */
						if( ! ($product = $product_item->get_product()) ){
							continue;
						}

						$recently_purchased_product_ids[] = ($parent_product_id = $product->get_parent_id()) ? $parent_product_id : $product->get_id();
					}
				}
			}

			if( ! empty($recently_purchased_product_ids) ){
				$query_args['post__in'] = array_unique( array_filter( array_map( 'absint', $recently_purchased_product_ids ) ) );
				$query_args['orderby']  = 'post__in';
			}
			// force an empty query
			else {
				$query_args['post__in'] = [0];
			}
		}
		/**
		* Add excludes for the rest
		*/
		else {

			if ( $this->_settings['query_type'] != 'manual-selection' && (!empty($this->_settings['exclude']) || !empty($to_exclude))) {
				$query_args['post__not_in'] = array_map( 'trim', explode( ',', $this->_settings['exclude'] ?? '' ) );
				$query_args['post__not_in'] = array_filter( array_merge($query_args['post__not_in'], $to_exclude) );
				$post__in = isset($query_args['post__in']) ? $query_args['post__in'] : [];
				$query_args['post__in'] = array_diff( $post__in, array_map( 'absint', $query_args['post__not_in'] ) );
			}

		}

		if ( $hide_out_of_stock ) {
			$query_args['meta_query'][] = [
				'key'     => '_stock_status',
				'value'   => 'outofstock',
				'compare' => 'NOT LIKE',
			];
		}

		$min_price = isset($this->_settings['query_price_min']) && '' !== $this->_settings['query_price_min'] ? absint($this->_settings['query_price_min']) : '';
		$max_price = isset($this->_settings['query_price_max']) && '' !== $this->_settings['query_price_max'] ? absint($this->_settings['query_price_max']) : '';

		if ( $min_price || $max_price ) {

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

				$price_query = [
					'key'          => '_price',
					'value'        => [ $min_price, $max_price ],
					'type'         => 'numeric',
					'compare'      => 'BETWEEN',
				];

				if( ! $min_price && $max_price ){
					$price_query['value'] = $max_price;
					$price_query['compare'] = '<=';
				}

				if( $min_price && ! $max_price ){
					$price_query['value'] = $min_price;
					$price_query['compare'] = '>=';
				}

				$query_args['meta_query'][] = $price_query;

			}
		}

		if( isset($_REQUEST['orderby']) && $_REQUEST['orderby'] === 'price' ){
			$ordering_args = WC()->query->get_catalog_ordering_args( $query_args['orderby'], 'ASC' );
			$query_args['orderby'] = $ordering_args['orderby'];
			$query_args['order']   = $ordering_args['order'];
			if ( $ordering_args['meta_key'] ) {
				$query_args['meta_key'] = $ordering_args['meta_key']; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			}
		}

		if( isset($this->_settings['offset']) && $offset = $this->_settings['offset'] ){
			$query_args['offset'] = $offset;
		}

		if( isset($this->_settings['exclude_terms']) && ($exclude_terms = $this->_settings['exclude_terms']) ){

			$txqs = [];

			foreach ( $exclude_terms as $term_id) {

				$exc_term_txq = [
					'field'    => 'term_id',
					'terms'    => [],
					'operator' => 'NOT IN',
				];

				$term_obj = get_term($term_id);

				if( $term_obj && isset($term_obj->taxonomy) && ! is_wp_error($term_obj) ){
					$exc_term_txq['taxonomy'] = $term_obj->taxonomy;
					$exc_term_txq['terms'][] = $term_id;
				}

				$txqs[] = $exc_term_txq;
			}

			if( isset($query_args['tax_query']) ){
				$query_args['tax_query'][] = $txqs;
			}
			else {
				$query_args['tax_query'] = $txqs;
			}
		}

		if( $force_failed_query ){
			$query_args['post__in'] = [0];
		}

		return $this->query_args = $query_args;
	}

	public function get_page_product_id(){

		if( isset($_REQUEST['pid']) && $pid = absint($_REQUEST['pid']) ){
			$product_id = $pid;
		}
		else {
			$product_id = get_the_ID();
		}

		return $product_id;
	}

	/**
	 * Get the query results,
	 * based on $query_args.
	 *
	 * @since 1.0.0
	 */
	public function get_query_results()
	{

		$element_id = $this->_args['el_instance'] ? $this->_args['el_instance']->get_id() : 0;

		if( isset( $this->_args['product_ids'] ) ){

			$product_ids = [];

			if( ! empty($this->_args['product_ids']) ){
				$product_ids = $this->_args['product_ids'];
			}

			$limit = !empty($this->_settings['limit'])  ? absint($this->_settings['limit']) : $this->get_default_limit();

			$product_ids = array_slice( $product_ids, 0, $limit );

			$results = (object) [
				'ids'          => $product_ids,
				'total'        => count( $product_ids ),
				'total_pages'  => 1,
				'per_page'     => $limit,
				'current_page' => 1,
			];

		}

		/**
		 * Cancel default query and override all results
		 * @since 1.6.3
		 */
		else if( $pre_results = apply_filters( "reycore/elementor/{$this->_args['filter_name']}/pre_results", [], $element_id, $this ) )
		{
			$results = (object) $pre_results;
		}

		else {

			if ( 'current_query_original' === $this->_settings['query_type'] ){
				$this->query = $GLOBALS['wp_query'];
			}

			else if ( 'acf' === $this->_settings['query_type'] ){

				$acf_products = [];

				if( ! empty($this->_settings['acf_field']) ){

					if( is_tax() ){
						$term = get_queried_object();
						if( ! empty($term->term_id) ){
							$post_id = $term->term_id;
							if( reycore__is_multilanguage() ){
								$post_id = apply_filters('reycore/translate_ids', $post_id, $term->taxonomy );
							}
						}
						$post_id = get_term_by('term_taxonomy_id', $post_id);
					}

					else {
						$post_id = ($_pid = get_the_ID()) ? $_pid : get_queried_object_id();
						if( reycore__is_multilanguage() ){
							$post_id = apply_filters('reycore/translate_ids', $post_id );
						}
					}

					if( $acf_field = get_field($this->_settings['acf_field'], $post_id) ){
						if( is_array($acf_field) && ! empty($acf_field) ){
							if( is_object($acf_field[0]) ){
								$acf_products = wp_list_pluck($acf_field, 'ID');
							}
							else if ( is_string( $acf_field ) ) {
								$acf_products = explode(',', $acf_field);
							}
							else if ( is_array( $acf_field ) ) {
								$acf_products = $acf_field;
							}
						}
					}
				}

				if( ! empty($acf_products) ){

					$acf_query_vars = [
						'post__in'       => array_filter( array_map( 'absint', $acf_products ) ),
						'orderby'        => 'post__in',
						'post_type'      => 'product',
						'fields'         => 'ids',
						'posts_per_page' => $this->get_default_limit(),
					];

					$page = absint( empty( $_GET['product-page'] ) ? 1 : $_GET['product-page'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

					if ( 1 < $page ) {
						$acf_query_vars['paged'] = absint( $page );
					}

					if( ! empty($this->_settings['limit']) ){
						$acf_query_vars['posts_per_page'] = absint($this->_settings['limit']);
					}

					else if ( isset($this->_settings['rows_per_page']) ){
						$acf_query_vars['posts_per_page'] = !empty($this->_settings['rows_per_page']) ?
							(absint($this->_settings['rows_per_page']) * absint($this->_settings['per_row'])) : $this->get_default_limit();
					}

					$this->query = new \WP_Query( $acf_query_vars );

				}
			}

			else {

				if( empty($this->query_args) ){
					return [];
				}

				$query_args = apply_filters( "reycore/elementor/{$this->_args['filter_name']}/query_args", $this->query_args, $element_id, $this->_settings );

				if( 'product_grid' === $this->_args['filter_name'] && ! empty($this->_settings['custom_query_id']) ){
					$query_args = apply_filters( "reycore/elementor/product_grid/{$this->_settings['custom_query_id']}/query_args", $query_args, $this );
				}

				if( current_user_can('administrator') && isset($this->_settings['debug__show_query']) && $this->_settings['debug__show_query'] === 'yes' ){
					echo '<pre><h4>Query args:</h4>';
					var_dump( $query_args );
					echo '</pre>';
				}

				$this->query = new \WP_Query( $query_args );

				if( is_search() ){
					do_action('reycore/woocommerce/search/search_products_query', $this->query );
				}

			}

			if( is_null($this->query) ){
				return [];
			}

			do_action("reycore/elementor/{$this->_args['filter_name']}/query", $this);

			if( 'product_grid' === $this->_args['filter_name'] && ! empty($this->_settings['custom_query_id']) ){
				do_action( "reycore/elementor/product_grid/{$this->_settings['custom_query_id']}/query", $this );
			}

			$paginated = ! $this->query->get( 'no_found_rows' );

			// convert post objects to IDs
			if ( ! empty( $this->query->posts ) && is_object( $this->query->posts[0] ) ) {
				$this->query->posts = array_map( function ( $post ) {
					return $post->ID;
				}, $this->query->posts );
			}

			$results = (object) apply_filters( "reycore/elementor/{$this->_args['filter_name']}/results", [
				'ids'          => wp_parse_id_list( $this->query->posts ),
				'total'        => $paginated ? (int) $this->query->found_posts : count( $this->query->posts ),
				'total_pages'  => $paginated ? (int) $this->query->max_num_pages : 1,
				'per_page'     => (int) $this->query->get( 'posts_per_page' ),
				'current_page' => $paginated ? (int) max( 1, $this->query->get( 'paged', 1 ) ) : 1,
			], $element_id, $this->query );

		}

		$GLOBALS["rey_exclude_products"] = $results->ids;

		// Remove ordering query arguments which may have been added by get_catalog_ordering_args.
		WC()->query->remove_ordering_args();

		return $this->_products = $results;
	}

	public function update_query( $query ){
		$this->query = $query;
	}

	/**
	 * Get Grid type
	 */
	public function get_grid_type(){
		return esc_attr( get_theme_mod('loop_grid_layout', 'default') );
	}

	public function set_grid_type( $mod ){

		if( ! empty($this->_settings['grid_layout']) ){
			return $this->_settings['grid_layout'];
		}

		return $mod;
	}

	public function set_title_tag( $tag ){

		if( ! empty($this->_settings['title_tag']) ){
			return \ReyCore\Helper::get_valid_title_tag($this->_settings['title_tag']);
		}

		return $tag;
	}

	public function allow_css_classes_elementor_edit_mode( $status ){

		if( \Elementor\Plugin::$instance->editor->is_edit_mode() ){
			return false;
		}

		return $status;
	}


	public function resize_images( $size ){

		if( isset($this->_settings['image_size']) &&
			($image_size = $this->_settings['image_size']) &&
			$image_size != 'custom' &&
			isset($this->_settings['hide_thumbnails']) && $this->_settings['hide_thumbnails'] != 'yes'
		){
			if( 'shop_catalog' === $image_size ){
				$image_size = 'woocommerce_thumbnail';
			}
			$size = $image_size;
		}

		return $size;
	}

	public function resize_images__custom( $image, $product ){

		if ( isset($this->_settings['image_custom_dimension']) && $image_custom_dimension = $this->_settings['image_custom_dimension'] ) {

			$image_id = 0;

			if ( $product->get_parent_id() ) {
				$parent_product = wc_get_product( $product->get_parent_id() );
				if ( $parent_product ) {
					$image_id = $parent_product->get_image_id();
				}
			}
			else {
				$image_id = $product->get_image_id();
			}

			return reycore__get_attachment_image( [
				'image' => [
					'id' => $image_id
				],
				'size' => 'custom',
				'attributes' => [ 'class' => "rey-thumbImg attachment-custom size-custom" ],
				'settings' => $this->_settings,
			] );
		}

		return $image;
	}

	public function resize_second_images( $image, $product, $image_id ){

		if ( isset($this->_settings['image_custom_dimension']) && $image_custom_dimension = $this->_settings['image_custom_dimension'] ) {

			return reycore__get_attachment_image( [
				'image' => [
					'id' => $image_id
				],
				'size' => 'custom',
				'attributes' => [ 'class' => "rey-productThumbnail__second" ],
				'settings' => $this->_settings,
			] );
		}

		return $image;
	}

	function default_orderby($orderby){

		// Product Archive Element
		if( isset($this->_settings['default_catalog_orderby']) ){
			if( $default_catalog_orderby = $this->_settings['default_catalog_orderby'] ){
				return $default_catalog_orderby;
			}
		}

		if( isset($this->_settings['query_type']) ){
			if( $this->_settings['query_type'] === 'recent' ){
				return 'date';
			}
		}

		return $orderby;
	}

	public function disable_before_after($mod){

		if( isset($this->_settings['prevent_ba_content']) ){
			if( $this->_settings['prevent_ba_content'] === 'yes' || in_array($this->_settings['_skin'], ['carousel-section', 'carousel', 'mini']) ){
				return false;
			}
		}

		return $mod;
	}

	public function disable_stretched_products($mod){

		if( \Elementor\Plugin::$instance->editor->is_edit_mode() ){
			return false;
		}

		if( isset($this->_settings['prevent_stretched']) ){
			if( $this->_settings['prevent_stretched'] === 'yes' || in_array($this->_settings['_skin'], ['carousel-section', 'carousel', 'mini']) ){
				return false;
			}
		}

		return $mod;
	}

	function get_loop_components(){

		$components = [];

		foreach( [
			'category',
			'excerpt',
			'prices',
			'ratings',
			'add_to_cart',
			'thumbnails',
			'new_badge',
			'variations',
			'notices',
			'quickview',
			'wishlist',
			'discount',
		] as $st_comp ){

			// 'inherits' will bail
			if( ! ( isset( $this->_settings['hide_' . $st_comp] ) && ($setting = $this->_settings['hide_' . $st_comp]) ) ){
				continue;
			}

			$components[ $st_comp ] = $setting === 'no';
		}

		$paginate = $this->_settings['paginate'] === 'yes'; // make archive
		$show_header = $this->_settings['show_header'] === 'yes'; // show header

		$components['view_selector'] = $paginate && $show_header && $this->_settings['show_view_selector'] === 'yes';
		$components['result_count'] = $show_header;
		$components['catalog_ordering'] = $show_header;
		$components['filter_button'] = $paginate && $show_header && $this->_args['filter_button'];
		$components['filter_top_sidebar'] = false;

		if( isset($this->_settings['show_count']) && $this->_settings['show_count'] === '' ){
			$components['result_count'] = false;
		}

		if( isset($this->_settings['show_sorting']) && $this->_settings['show_sorting'] === '' ){
			$components['catalog_ordering'] = false;
		}

		if( in_array($this->_settings['_skin'], ['carousel-section'] ) &&
			isset( $this->_settings['hide_thumbnails'] ) && $this->_settings['hide_thumbnails'] === 'no' ){
			$components['thumbnails'] = true;
		}

		if( $this->_settings['show_header'] === 'yes' &&
			(isset($this->_settings['show_filter_button']) && $this->_settings['show_filter_button'] === 'yes') ){
			$components['filter_button'] = true;
		}

		if( isset($this->_settings['prevent_2nd_image']) && $this->_settings['prevent_2nd_image'] === 'yes' ){
			$components['thumbnails_second'] = false;
			$components['thumbnails_slideshow'] = false;
		}

		if( in_array($this->_settings['_skin'], ['carousel-section', 'carousel'] ) ){
			// disable thumbnails slideshow
			$components['thumbnails_slideshow'] = false;
		}

		return apply_filters('reycore/elementor/tag_archive/components', $components, $this);
	}

	// default false
	function disable_grid_components( $status ){
		return $this->_settings['paginate'] === '';
	}

	function override_loop_components( $instance ){

		$comps = $this->get_loop_components();
		$groups = $instance->get_components_groups();

		foreach ( $instance->get_components() as $id => $component) {

			// reset
			// $component->set_status();

			if( $this->_settings['paginate'] === '' && 'grid' === $component->loop_type() ){
				continue;
			}

			if(
				isset( $groups[ $id ]) &&
				($group_id = $groups[ $id ])
			){
				if( isset( $comps[$group_id] ) ) {
					if( $component->group_default() ){
						$component->set_status( $comps[$group_id] );
						$this->components_statuses[ $id ] = $component;
					}
					continue;
				}
			}

			if( isset( $comps[ $id ] ) ){
				$component->set_status( $comps[ $id ] );
				$this->components_statuses[ $id ] = $component;
			}

		}

		$instance::$custom_product_item_inner_classes['box_styler'] = '--box-styler';
	}

	public function set_loop_props(){

		$loop_components = [
			'columns'                      => $this->_settings['per_row'],
			'is_paginated'                 => $this->_settings['paginate'] === 'yes',
		];

		// Tweak
		if( in_array($this->_settings['_skin'], ['carousel-section', 'carousel'] ) ){
			// Remove entry animation class
			$loop_components['entry_animation'] = false;
		}

		foreach ($loop_components as $component => $status) {
			wc_set_loop_prop( $component, $status );
		}

	}

	public function loop_header_tweaks(){
		if( isset($this->_settings['show_header']) && $this->_settings['show_header'] === 'yes' ){
			do_action("reycore/elementor/{$this->_args['filter_name']}/show_header", $this);
		}
	}

	/**
	 * Render Start
	 *
	 * @since 1.0.0
	 */
	public function render_start()
	{

		$this->set_loop_props();
		$this->before_products();

		if ( is_null(WC()->session) ) {
			WC()->session = new \WC_Session_Handler();
			WC()->session->init();
		}

		// Include WooCommerce frontend stuff
		wc()->frontend_includes();

		// Prime caches to reduce future queries.
		if ( is_callable( '_prime_post_caches' && $this->_products && $this->_products->ids) ) {
			_prime_post_caches( $this->_products->ids );
		}

		add_action( 'reycore/woocommerce/loop_components/add', [$this, 'override_loop_components']);
		add_filter( 'reycore/woocommerce/catalog/before_after/enable', [$this, 'disable_before_after'], 10);
		add_filter( 'reycore/woocommerce/loop_components/disable_grid_components', [$this, 'disable_grid_components'], 10);
		add_filter( 'reycore/woocommerce/catalog/stretch_product/enable', [$this, 'disable_stretched_products'], 10);
		add_filter( 'reycore/woocommerce/loop/prevent_custom_css_classes', [$this, 'allow_css_classes_elementor_edit_mode'], 10 );
		add_filter( 'single_product_archive_thumbnail_size', [$this, 'resize_images'], 10 );
		add_filter( 'woocommerce_product_get_image', [$this, 'resize_images__custom'], 10, 2 );
		add_filter( 'reycore/woocommerce/loop/second_image', [$this, 'resize_second_images'], 10, 3 );
		add_filter( 'woocommerce_default_catalog_orderby', [$this, 'default_orderby'], 10 );
		add_filter( 'theme_mod_loop_grid_layout', [$this, 'set_grid_type']);
		add_filter( 'woocommerce_product_loop_title_tag', [$this, 'set_title_tag']);

		if( isset($this->_settings['hide_notices']) && $this->_settings['hide_notices'] !== '' ){
			remove_action( 'woocommerce_before_shop_loop', 'woocommerce_output_all_notices', 10 );
		}

		add_action( 'woocommerce_before_shop_loop', [$this, 'loop_header_tweaks'], 6);

		// Setup the loop.
		wc_setup_loop(
			[
				'is_shortcode' => ! is_tax(),
				'is_product_grid' => true,
				'is_search'    => false,
				'total'        => $this->_products->total,
				'total_pages'  => $this->_products->total_pages,
				'per_page'     => $this->_products->per_page,
				'current_page' => $this->_products->current_page,
			]
		);

		$wrapper_classes = [
			'woocommerce',
			'rey-element',
			$this->_args['main_class'],
			$this->_args['main_class'] ? $this->_args['main_class'] . '--' . ( isset($this->_settings['hide_thumbnails']) && $this->_settings['hide_thumbnails'] == 'yes' ? 'no-thumbs' : 'has-thumbs' ) : '',
			$this->_settings['_skin'] ? 'reyEl-productGrid--skin-' . $this->_settings['_skin'] : '',
			isset($this->_settings['show_header']) && $this->_settings['show_header'] === 'yes' ? '--show-header' : ''
		];

		// Vertical align in middle for
		// uncropped images.
		if(
			get_option('woocommerce_thumbnail_cropping', '1:1') === 'uncropped' &&
			isset( $this->_settings['uncropped_vertical_align'] ) && $this->_settings['uncropped_vertical_align'] !== '' ){
				$wrapper_classes[] = '--vertical-middle-thumbs';
		}

		if( $this->_settings['paginate'] === 'yes' ){

			add_filter( 'reycore/load_more_pagination/product', '__return_true');

			add_filter( 'reycore/load_more_pagination_args', function($args){

				$pagenum =  wc_get_loop_prop( 'current_page' ) + 1;

				if( wc_get_loop_prop( 'total_pages' ) >= $pagenum ) {

					// ensure /page/x/ does not conflict with ?product-page=x
					if ( ! preg_match('/\/page\/(\d+)\//', $args['url'], $matches)) {

						$path = add_query_arg( 'product-page', $pagenum, false );

						if( is_multisite() ){
							$args['url'] = esc_url_raw ( network_site_url( $path ) );
						}
						else {
							$args['url'] = esc_url_raw ( site_url( $path ) );
						}

					}
				}
				else {
					$args['url'] = '';
				}

				$args['post_type'] = 'product';
				return $args;
			});
		}

		$this->_args['attributes']['data-qt'] = $this->_settings['query_type'];

		?>

		<div class="<?php echo implode( ' ', $wrapper_classes ) ?>" <?php echo reycore__implode_html_attributes($this->_args['attributes']) ?>>

		<?php
		do_action( 'woocommerce_before_shop_loop' );

	}

	/**
	 * Grid CSS Classes
	 */
	public function get_css_classes(){

		// Grid Gap CSS class
		$classes['grid_gap'] = reycore_wc__get_gap_css_class(
			isset($this->_settings['gaps_v2']) ? $this->_settings['gaps_v2'] : false
		);

		// Product Grid CSS class
		$classes['grid_layout'] = 'rey-wcGrid-' . $this->get_grid_type();

		if( $skin = $this->get_loop_skin() ){
			$classes['skin'] = '--skin-' . esc_attr( $skin );
		}

		if( wc_get_loop_prop( 'is_paginated' ) && wc_get_loop_prop( 'total_pages' ) ){

			$classes['paginated'] = '--paginated';

			if( in_array(get_theme_mod('loop_pagination', 'paged'), ['load-more', 'infinite'], true) ){
				$classes['paginated_infinite'] = '--infinite';
			}

		}

		return $classes;
	}

	function __loop_hooks_start(){

		wc_set_loop_prop( 'name', $this->_args['name'] );
		wc_set_loop_prop( 'loop', 0 );

		do_action('reycore/woocommerce/loop/before_grid');
		do_action('reycore/woocommerce/loop/before_grid/name=' . $this->_args['name']);

	}

	function __loop_hooks_end(){

		do_action('reycore/woocommerce/loop/after_grid');
		do_action('reycore/woocommerce/loop/after_grid/name=' . $this->_args['name']);

	}

	public function loop_start()
	{

		$this->__loop_hooks_start();

		$classes = $this->get_css_classes();

		if(
			isset($this->_settings['ajax_load_more']) && $this->_settings['ajax_load_more'] !== '' &&
			! wc_get_loop_prop( 'is_paginated' ) &&
			$this->_settings['_skin'] === '' ){
			unset($classes['prevent_margin']);
		}

		if( $skin = $this->get_loop_skin() ){
			$attributes['data-skin'] = '--skin-' . esc_attr( $skin );
		}

		$attributes['data-cols'] = absint(wc_get_loop_prop( 'columns' ));

		$attributes['data-cols-tablet'] = absint( isset($this->_settings['per_row_tablet'] ) && $this->_settings['per_row_tablet'] ? $this->_settings['per_row_tablet'] : reycore_wc_get_columns('tablet') );
		wc_set_loop_prop( 'pg_columns_tablet', $attributes['data-cols-tablet'] );

		$attributes['data-cols-mobile'] = absint( isset($this->_settings['per_row_mobile'] ) && $this->_settings['per_row_mobile'] ? $this->_settings['per_row_mobile'] : reycore_wc_get_columns('mobile') );;
		wc_set_loop_prop( 'pg_columns_mobile', $attributes['data-cols-mobile'] );

		printf('<ul class="products %s" %s>',
			reycore__product_grid_classes($classes),
			reycore__product_grid_attributes($attributes, $this->_settings)
		);

	}

	public function loop_end(){

		echo '</ul>';

		$this->__loop_hooks_end();

	}

	public function product_css_classes($classes){

		$loop_skin = $this->get_loop_skin();

		if(
			('basic' === $loop_skin && (isset($this->_settings['basic_hover_animation']) && $hover_anim = $this->_settings['basic_hover_animation']) ) ||
			('wrapped' === $loop_skin && (isset($this->_settings['wrapped_hover_animation']) && $hover_anim = $this->_settings['wrapped_hover_animation']) ) ||
			('cards' === $loop_skin && (isset($this->_settings['cards_hover_animation']) && $hover_anim = $this->_settings['cards_hover_animation']) ) ||
			('iconized' === $loop_skin && (isset($this->_settings['iconized_hover_animation']) && $hover_anim = $this->_settings['iconized_hover_animation']) ) ||
			('proto' === $loop_skin && (isset($this->_settings['proto_hover_animation']) && $hover_anim = $this->_settings['proto_hover_animation']) )
		){
			if( 'yes' === $hover_anim ){
				$classes['hover-animated'] = 'is-animated';
			}
			else {
				unset( $classes['hover-animated'] );
			}
		}

		if( 'cards' === $loop_skin && ($cards__expand_thumbs = $this->_settings['cards_expand_thumbs']) ){
			if( 'yes' === $cards__expand_thumbs ){
				$classes['cards_expand_thumbs'] = '--expand-thumbs';
			}
			else {
				unset( $classes['cards_expand_thumbs'] );
			}
		}


		if( 'proto' === $loop_skin ){
			if ($proto__loop_shadow = $this->_settings['proto_loop_shadow']) {
				if( 'no' === $proto__loop_shadow ){
					unset( $classes['shadow_active'] );
				}
				else {
					$classes['shadow_active'] = '--shadow-' . $proto__loop_shadow;
				}
			}
			if ($proto__loop_shadow_hover = $this->_settings['proto_loop_shadow_hover']) {
				if( 'no' === $proto__loop_shadow_hover ){
					unset( $classes['shadow_hover'] );
				}
				else {
					$classes['shadow_hover'] = '--shadow-h-' . $proto__loop_shadow_hover;
				}
			}
		}

		// If both Add to cart & Quickview buttons are disabled
		// remove the is-animated class as it breaks the layout.
		if( array_key_exists('hover-animated', $classes) ){
			if( isset($this->_settings['hide_add_to_cart']) && $this->_settings['hide_add_to_cart'] === 'yes' &&
				isset($this->_settings['hide_quickview']) && $this->_settings['hide_quickview'] === 'yes' ) {
				unset( $classes['hover-animated'] );
			}
		}

		// if text align is selected
		// remove global class and replace with selected text align
		if( isset($this->_settings['text_align']) && !empty($this->_settings['text_align']) ){
			if( array_key_exists( 'rey-wc-loopAlign-' . reycore_wc__get_setting('loop_alignment') , $classes) ){
				unset( $classes['text-align'] );
			}
			$classes['text-align'] = 'rey-wc-loopAlign-' . $this->_settings['text_align'];
		}

		// custom height for cropped image layout
		$unsupported_grids_custom_container_height = ['metro'];
		if( get_option('woocommerce_thumbnail_cropping', '1:1') === 'uncropped' &&
			! in_array( $this->get_grid_type(), $unsupported_grids_custom_container_height) &&
			isset($this->_settings['custom_image_container_height']) && $this->_settings['custom_image_container_height']['size'] !== '' ) {
			$classes[] = '--customImageContainerHeight';
		}

		if( in_array($this->_settings['_skin'], ['carousel', 'carousel-section'], true) ) {
			$classes['splide-item-class'] = 'splide__slide';
		}

		$disable_animated_entry = [];

		if( ! ( isset($this->_settings['entry_animation']) && $this->_settings['entry_animation'] === 'yes') ){
			$disable_animated_entry[] = true;
		}

		if( $this->_settings['_skin'] === '' ){
			if(
				(isset($this->_settings['horizontal_desktop']) && $this->_settings['horizontal_desktop'] !== '') ||
				(isset($this->_settings['horizontal_tablet']) && $this->_settings['horizontal_tablet'] !== '') ||
				(isset($this->_settings['horizontal_mobile']) && $this->_settings['horizontal_mobile'] !== '')
			){
				$disable_animated_entry[] = true;
			}
		}

		if( in_array(true, $disable_animated_entry, true) ){
			unset($classes['animated-entry']);
		}

		return $classes;
	}


	/**
	 * Product Loop
	 *
	 * @since 1.0.0
	 */
	public function render_products()
	{
		ob_start();

		if( isset($GLOBALS['post']) ) {
			$original_post = $GLOBALS['post'];
		}

		if ( wc_get_loop_prop( 'total' ) ) {

			add_filter( 'woocommerce_post_class', [$this, 'product_css_classes'], 20 );

			foreach ( $this->_products->ids as $product_id ) {
				$GLOBALS['post'] = get_post( $product_id ); // WPCS: override ok.
				setup_postdata( $GLOBALS['post'] );
				// Hook: woocommerce_shop_loop.
				do_action( 'woocommerce_shop_loop' );
				// Render product template.
				wc_get_template_part( 'content', 'product' );
			}

			remove_filter( 'woocommerce_post_class', [$this, 'product_css_classes'], 20 );

		}

		if( isset($original_post) ) {
			$GLOBALS['post'] = $original_post; // WPCS: override ok.
		}

		$output = ob_get_clean();

		if( !empty( self::$_selectors_to_replace ) ){
			foreach (self::$_selectors_to_replace as $selector_to_search => $to_replace) {
				$output = str_replace( $selector_to_search, $selector_to_search . ' ' . $to_replace, $output );
			}
		}

		echo $output;
	}


	/**
	 * End rendering the widget
	 * Reset components at the end
	 *
	 * @since 1.0.0
	 */
	public function render_end(){

		wp_reset_postdata();

		do_action( 'woocommerce_after_shop_loop' );

		remove_action( 'woocommerce_before_shop_loop', [$this, 'loop_header_tweaks'], 6);
		remove_filter( 'single_product_archive_thumbnail_size', [$this, 'resize_images'], 10 );
		remove_filter( 'woocommerce_product_get_image', [$this, 'resize_images__custom'], 10 );
		remove_filter( 'reycore/woocommerce/loop/second_image', [$this, 'resize_second_images'], 10 );
		remove_filter( 'reycore/woocommerce/loop/prevent_custom_css_classes', [$this, 'allow_css_classes_elementor_edit_mode'], 10 );
		remove_filter( 'reycore/woocommerce/catalog/stretch_product/enable', '__return_false', 10);
		remove_filter( 'woocommerce_default_catalog_orderby', [$this, 'default_orderby'], 10 );
		remove_filter( 'reycore/woocommerce/catalog/before_after/enable', [$this, 'disable_before_after'], 10);
		remove_filter( 'reycore/woocommerce/catalog/stretch_product/enable', [$this, 'disable_stretched_products'], 10);
		remove_filter( 'reycore/woocommerce/loop_components/disable_grid_components', [$this, 'disable_grid_components'], 10);
		remove_action( 'reycore/woocommerce/loop_components/add', [$this, 'override_loop_components']);
		remove_filter( 'theme_mod_loop_grid_layout', [$this, 'set_grid_type']);
		remove_filter( 'woocommerce_product_loop_title_tag', [$this, 'set_title_tag']);

		// reset components
		foreach ($this->components_statuses as $id => $component) {
			$component->set_status();
		}

		$this->after_products();
		$this->ajax_load_more();

		?></div><?php

	}

	public static function add_extra_data_controls( $element ){

		/**
		 * Extra Data
		 */
		$element->start_controls_section(
			'section_extra_data',
			[
				'label' => __( 'Extra Data', 'rey-core' ),
				'condition' => [
					'loop_skin!' => 'template',
				],
			]
		);

			$extra = new \Elementor\Repeater();

			$extra->add_control(
				'component',
				[
					'label' => esc_html__( 'Component', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						'' => '- Select -',
						'acf' => esc_html__('ACF Field', 'rey-core'),
						'dimensions' => esc_html__('Product Dimensions', 'rey-core'),
						'weight' => esc_html__('Product Weight', 'rey-core'),
						'attribute' => esc_html__('Product Attribute', 'rey-core'),
						'sku' => esc_html__('SKU', 'rey-core'),
						'stock' => esc_html__('Stock amount', 'rey-core'),
						'placeholder' => esc_html__('Placeholder', 'rey-core'),
					],
				]
			);

			$extra->add_control(
				'acf_field',
				[
					'label' => esc_html__( 'Select ACF Field', 'rey-core' ),
					'default' => '',
					'label_block' => true,
					'type' => 'rey-query',
					'query_args' => [
						'type'        => 'acf',
						'field_types' =>  [
							'text',
							'textarea',
							'number',
							'wysiwyg',
							'url',
							'image',
						],
					],
					'condition' => [
						'component' => 'acf',
					],
				]
			);

			$extra->add_control(
				'acf_display',
				[
					'label' => esc_html__( 'Display as', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'text',
					'options' => [
						'text'  => esc_html__( 'Text', 'rey-core' ),
						'image'  => esc_html__( 'Image', 'rey-core' ),
					],
					'condition' => [
						'component' => 'acf',
					],
				]
			);

			$extra->add_control(
				'placeholder_hook',
				[
					'label' => esc_html__( 'Placeholder Hook', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'placeholder' => esc_html__( 'eg: unique_key', 'rey-core' ),
					'description' => 'You can use the filter hook "reycore/woocommerce/products/extra_data/placeholder=unique_key" to add any <a href="https://d.pr/n/wqCcKN">custom data</a>.',
					'condition' => [
						'component' => 'placeholder',
					],
				]
			);

			$extra->add_control(
				'stock_text',
				[
					'label' => esc_html__( 'Stock Text', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '%d in stock',
					'placeholder' => esc_html__( 'eg: %d in stock', 'rey-core' ),
					'condition' => [
						'component' => 'stock',
					],
				]
			);

			$extra->add_control(
				'attribute',
				[
					'label' => esc_html__( 'Attribute', 'rey-core' ),
					'default' => '',
					'condition' => [
						'component' => 'attribute',
					],
					'type' => 'rey-ajax-list',
					'query_args' => [
						'request' => 'get_attributes_list'
					],
				]
			);

			$extra->add_control(
				'position',
				[
					'label' => esc_html__( 'Position', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						'' => '- Select -',
						'top_left' => esc_html__('Thumb Top-Left', 'rey-core'),
						'top_right' => esc_html__('Thumb Top-Right', 'rey-core'),
						'bottom_left' => esc_html__('Thumb Bottom-Left', 'rey-core'),
						'bottom_right' => esc_html__('Thumb Bottom-Right', 'rey-core'),
						'before_title' => esc_html__('Before Title', 'rey-core'),
						'after_title' => esc_html__('After Title', 'rey-core'),
						'after_price' => esc_html__('After Price', 'rey-core'),
						'after_content' => esc_html__('After all content', 'rey-core'),
					],
					'condition' => [
						'component!' => '',
					],
				]
			);

			$extra->add_control(
				'title',
				[
					'label' => esc_html__( 'Title', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'condition' => [
						'component!' => '',
					],
				]
			);

			// General

			$extra->add_control(
				'color',
				[
					'label' => esc_html__( 'Text Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ul.products li.product {{CURRENT_ITEM}}' => 'color: {{VALUE}}',
					],
					'condition' => [
						'component!' => '',
					],
					'dynamic' => [
						'active' => true,
					],
				]
			);

			$extra->add_control(
				'bg_color',
				[
					'label' => esc_html__( 'Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ul.products li.product {{CURRENT_ITEM}}' => 'background-color: {{VALUE}}',
					],
					'condition' => [
						'component!' => '',
					],
				]
			);

			$extra->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'typo',
					'selector' => '{{WRAPPER}} ul.products li.product {{CURRENT_ITEM}}',
					'condition' => [
						'component!' => '',
					],
				]
			);

			$extra->add_control(
				'radius',
				[
					'label' => esc_html__( 'Border Radius', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} ul.products li.product {{CURRENT_ITEM}}' => 'border-radius: {{VALUE}}px',
					],
					'condition' => [
						'component!' => '',
					],
				]
			);

			$extra->add_responsive_control(
				'padding',
				[
					'label' => __( 'Padding', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em' ],
					'selectors' => [
						'{{WRAPPER}} ul.products li.product {{CURRENT_ITEM}}' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
					'condition' => [
						'component!' => '',
					],
				]
			);

			$extra->add_responsive_control(
				'margin',
				[
					'label' => __( 'Margin', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em' ],
					'selectors' => [
						'{{WRAPPER}} ul.products li.product {{CURRENT_ITEM}}' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
					'condition' => [
						'component!' => '',
					],
				]
			);


			$extra->add_control(
				'img_size',
				[
					'label' => esc_html__( 'Image Size', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} ul.products li.product {{CURRENT_ITEM}}' => 'width: {{VALUE}}px',
					],
					'condition' => [
						'component' => 'acf',
						'acf_display' => 'image',
						'acf_field!' => '',
					],
				]
			);

			$extra->add_control(
				'mobile',
				[
					'label' => esc_html__( 'Show on mobiles', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'condition' => [
						'component!' => '',
					],
				]
			);

			$extra->add_control(
				'stretch',
				[
					'label' => esc_html__( 'Stretch', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'block',
					'default' => '',
					'condition' => [
						'component!' => '',
					],
					'selectors' => [
						'{{WRAPPER}} ul.products li.product {{CURRENT_ITEM}}' => 'display:{{VALUE}};',
					],
				]
			);

			// $extra->add_control(
			// 	'hover',
			// 	[
			// 		'label' => esc_html__( 'Hover only', 'rey-core' ),
			// 		'type' => \Elementor\Controls_Manager::SWITCHER,
			// 		'default' => '',
			// 		'condition' => [
			// 			'component!' => '',
			// 		],
			// 	]
			// );

			$element->add_control(
				'extra_data',
				[
					'label' => __( 'Extra data items', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::REPEATER,
					'fields' => $extra->get_controls(),
					'default' => [],
					'title_field' => '{{{ component }}}',
					'prevent_empty' => false,
				]
			);

		$element->end_controls_section();

	}

	public static function add_component_display_controls( $element ){

		$element->start_controls_section(
			'section_layout_components',
			[
				'label' => __( 'Components Display', 'rey-core' ),
			]
		);

		$yesno_opts = [
			''  => esc_html__( '- Inherit -', 'rey-core' ),
			'no'  => esc_html__( 'Show', 'rey-core' ),
			'yes'  => esc_html__( 'Hide', 'rey-core' ),
		];

		$element->add_control(
			'hide_category',
			[
				'label' => __( 'Category', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => $yesno_opts,
				'default' => '',
				'condition' => [
					'loop_skin!' => 'template',
				],
			]
		);

		$element->add_control(
			'hide_excerpt',
			[
				'label' => __( 'Short Description', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => $yesno_opts,
				'default' => '',
				'condition' => [
					'loop_skin!' => 'template',
				],
			]
		);

		$element->add_control(
			'hide_prices',
			[
				'label' => __( 'Price', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => $yesno_opts,
				'default' => '',
				'condition' => [
					'loop_skin!' => 'template',
				],
			]
		);

		$element->add_control(
			'hide_ratings',
			[
				'label' => __( 'Ratings', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => $yesno_opts,
				'default' => '',
				'condition' => [
					'loop_skin!' => 'template',
				],
			]
		);

		$element->add_control(
			'hide_add_to_cart',
			[
				'label' => __( 'Add To Cart', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => $yesno_opts,
				'default' => '',
				'condition' => [
					'loop_skin!' => 'template',
				],
			]
		);

		$element->add_control(
			'hide_thumbnails',
			[
				'label' => __( 'Thumbnails', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => $yesno_opts,
				'default' => '',
				'condition' => [
					'loop_skin!' => 'template',
				],
				// 'condition' => [
				// 	'_skin!' => 'carousel-section',
				// ],
			]
		);

		$element->add_control(
			'hide_new_badge',
			[
				'label' => __( '"New" Badge', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => $yesno_opts,
				'default' => '',
				'condition' => [
					'loop_skin!' => 'template',
				],
			]
		);

		$element->add_control(
			'hide_variations',
			[
				'label' => __( 'Product Variations', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => $yesno_opts,
				'default' => '',
				'separator' => 'after',
				'condition' => [
					'loop_skin!' => 'template',
				],
			]
		);

		$element->add_control(
			'_loop_extra_media',
			[
				'type' => \Elementor\Controls_Manager::HIDDEN,
				'default' => get_theme_mod('loop_extra_media', 'second'),
			]
		);

		$element->add_control(
			'prevent_2nd_image',
			[
				'label' => __( 'Prevent extra images', 'rey-core' ),
				'description' => __( 'This option will disable showing extra images inside the product item. The option overrides the one located in Customizer > WooCommerce > Product catalog - Layout.', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [
					'_loop_extra_media!' => 'no',
					'loop_skin!' => 'template',
				],
			]
		);

		$element->add_control(
			'prevent_ba_content',
			[
				'label' => __( 'Prevent Before/After Content', 'rey-core' ),
				'description' => __( 'This option will disable showing products that have other Products or Global sections, assigned before or after them.', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$element->add_control(
			'prevent_stretched',
			[
				'label' => __( 'Prevent Stretched Products', 'rey-core' ),
				'description' => __( 'This option will disable showing products that are Stretched in the catalog.', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$element->add_control(
			'hide_notices',
			[
				'label' => __( 'Hide Notices', 'rey-core' ),
				'description' => __( 'This option will disable showing the notices that are usually added for any product loop (archive).', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$element->add_control(
			'show_view_selector',
			[
				'label' => __( 'Show View Selector', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'description' => esc_html__('If enabled, the grid will use the stored user selected column number.', 'rey-core'),
				'condition' => [
					'paginate!' => '',
					'show_header!' => '',
					'_skin' => '',
				],
			]
		);

		$element->end_controls_section();
	}

	public static function add_common_styles_controls( $element ){

		$element->start_controls_section(
			'section_styles_general',
			[
				'label' => __( 'General Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$element->add_control(
			'loop_skin',
			[
				'label' => esc_html__( 'Product Item Skin', 'rey-core' ),
				'default' => '',
				'condition' => [
					'_skin' => ['', 'carousel'],
				],
				'type' => 'rey-ajax-list',
				'query_args' => [
					'request' => 'get_loop_skins'
				],
				'label_block' => true
			]
		);

		do_action('reycore/elementor/products/after_loop_skin', $element);

		$element->add_control(
			'color',
			[
				'label' => __( 'Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} ul.products li.product' => '--body-color: {{VALUE}}',
				],
			]
		);

		$element->add_control(
			'link_color',
			[
				'label' => __( 'Links Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} ul.products li.product' => '--link-color: {{VALUE}}',
				],
			]
		);

		$element->add_control(
			'link_hover_color',
			[
				'label' => __( 'Links Hover Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} ul.products li.product' => '--link-color-hover: {{VALUE}}',
				],
			]
		);

		$element->add_control(
			'text_align',
			[
				'label' => __( 'Alignment', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => __( 'Left', 'rey-core' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'rey-core' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => __( 'Right', 'rey-core' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'condition' => [
					'_skin!' => 'mini',
				],
			]
		);

		if( get_option('woocommerce_thumbnail_cropping', '1:1') === 'uncropped' ){

			$element->add_control(
				'uncropped_vertical_align',
				[
					'label' => esc_html__( 'Middle Vertical Align', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'condition' => [
						'_skin!' => 'mini',
					],
				]
			);

			$element->add_control(
				'custom_image_container_height',
				[
				   'label' => esc_html__( 'Custom Image Container Height', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', 'em' ],
					'range' => [
						'px' => [
							'min' => 100,
							'max' => 1000,
							'step' => 1,
						],
						'em' => [
							'min' => 3,
							'max' => 15.0,
						],
					],
					'selectors' => [
						'{{WRAPPER}}' => '--woocommerce-custom-image-height: {{SIZE}}{{UNIT}};',
					],
					'render_type' => 'template',
					'condition' => [
						'_skin!' => 'mini',
					],
				]
			);
		}

		$element->add_control(
			'entry_animation',
			[
				'label' => __( 'Animate product on scroll', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
				'condition' => [
					'_skin!' => ['carousel', 'carousel-section'],
				],
				'separator' => 'before'
			]
		);

		$element->add_control(
			'grid_styles_settings_title', [
				'label' => __( 'GRID STYLES', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$element->add_control(
			'grid_layout',
			[
				'label' => esc_html__( 'Grid Layout', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''           => esc_html__( 'Inherit', 'rey-core' ),
					'default'    => esc_html__( 'Default', 'rey-core' ),
					'masonry'    => esc_html__( 'Masonry', 'rey-core' ),
					'masonry2'   => esc_html__( 'Masonry V2', 'rey-core' ),
					'metro'      => esc_html__( 'Metro', 'rey-core' ),
					'scattered'  => esc_html__( 'Scattered', 'rey-core' ),
					'scattered2' => esc_html__( 'Scattered Mixed & Random', 'rey-core' ),
				],
				'condition' => [
					'_skin!' => 'mini',
				],
			]
		);

		$element->add_responsive_control(
			'gaps_v2',
			[
				'label' => __( 'Grid Gaps', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 200,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}}' => '--woocommerce-products-gutter: {{VALUE}}px;',
				],
				'condition' => [
					'_skin!' => ['carousel', 'carousel-section'],
				],
			]
		);

		$element->add_control(
			'grid_mb',
			[
				'label' => __( 'Grid Vertical Margin', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 180,
						'step' => 1,
					],
					'em' => [
						'min' => 0,
						'max' => 5.0,
					],
					'rem' => [
						'min' => 0,
						'max' => 5.0,
					],
				],
				'selectors' => [
					'{{WRAPPER}} ul.products' => '--woocommerce-products-gutter-v: calc({{SIZE}}{{UNIT}} / 2);',
				],
				'condition' => [
					'_skin' => '',
				],
			]
		);

		$element->add_control(
			'misc_styles_settings_title', [
				'label' => __( 'MISC. STYLES', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$element->add_responsive_control(
			'th_distance',
			[
				'label' => esc_html__( 'Thumbnails badges distance', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 1000,
				'step' => 1,
				'condition' => [
					'_skin' => ['', 'carousel'],
				],
				'selectors' => [
					'{{WRAPPER}} ul.products li.product .rey-productThumbnail' => '--woocomerce-thpos-distance: {{VALUE}}px;',
				],
			]
		);

		$element->add_control(
			'title_tag',
			[
				'label' => esc_html__( 'Title Tag', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => esc_html__( 'Default', 'rey-core' ),
					'h1'  => esc_html__( 'H1', 'rey-core' ),
					'h2'  => esc_html__( 'H2', 'rey-core' ),
					'h3'  => esc_html__( 'H3', 'rey-core' ),
					'h4'  => esc_html__( 'H4', 'rey-core' ),
					'h5'  => esc_html__( 'H5', 'rey-core' ),
					'div'  => esc_html__( 'Div', 'rey-core' ),
				],
			]
		);

		$element->end_controls_section();

		self::add_components_styles_controls( $element );
		self::add_box_styles_controls( $element );
		self::add_loop_header_controls( $element );
		self::add_skin_controls__basic( $element );
		self::add_skin_controls__wrapped( $element );
		self::add_skin_controls__cards( $element );
		self::add_skin_controls__iconized( $element );
		self::add_skin_controls__proto( $element );
	}

	public static function add_components_styles_controls( $element ){

		$element->start_controls_section(
			'section_component_styles',
			[
				'label' => __( 'Component Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'show_label' => false,
				'condition' => [
					'loop_skin!' => 'template',
				],
			]
		);

			$components = new \Elementor\Repeater();
			$conditions = [];

			$component_options = [
				''  => esc_html__( '- Select -', 'rey-core' )
			];

			foreach (self::component_mapping() as $key => $comp) {

				$component_options[ $key ] = $comp['name'];

				if( isset($comp['supports']) ){

					foreach ( $comp['supports'] as $support) {

						$conditions[$support]['relation'] = 'or';
						$conditions[$support]['terms'][] = [
							'name' => 'component',
							'operator' => '==',
							'value' => $key,
						];

					}

				}
			}

			$components->add_control(
				'component',
				[
					'label' => esc_html__( 'Component', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => $component_options,
				]
			);

			$components->add_control(
				'btn_style',
				[
					'label' => esc_html__( 'Button Style', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						'' => esc_html__( '- Inherit -', 'rey-core' ),
						'under' => esc_html__( 'Default (underlined)', 'rey-core' ),
						'hover' => esc_html__( 'Hover Underlined', 'rey-core' ),
						'primary' => esc_html__( 'Primary', 'rey-core' ),
						'primary-out' => esc_html__( 'Primary Outlined', 'rey-core' ),
						'clean' => esc_html__( 'Clean', 'rey-core' ),
					],
					'conditions' => $conditions['btn_style']
				]
			);

			$components->add_control(
				'color',
				[
					'label' => esc_html__( 'Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ul.products li.product {{CURRENT_ITEM}}' => 'color: {{VALUE}}',
					],
				]
			);

			$components->add_control(
				'bg_color',
				[
					'label' => esc_html__( 'Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ul.products li.product {{CURRENT_ITEM}}' => 'background-color: {{VALUE}}',
					],
					'conditions' => $conditions['bg_color']
				]
			);

			$components->add_control(
				'hover_color',
				[
					'label' => esc_html__( 'Hover Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ul.products li.product {{CURRENT_ITEM}}:hover' => 'color: {{VALUE}}',
					],
					'conditions' => $conditions['hover_color']
				]
			);

			$components->add_control(
				'hover_bg_color',
				[
					'label' => esc_html__( 'Hover Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ul.products li.product {{CURRENT_ITEM}}:hover' => 'background-color: {{VALUE}}',
					],
					'conditions' => $conditions['hover_bg_color']
				]
			);

			$components->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'typo',
					'selector' => '{{WRAPPER}} ul.products li.product {{CURRENT_ITEM}}',
				]
			);

			$components->add_control(
				'minheight',
				[
					'label' => esc_html__( 'Min. Height', 'rey-core' ) . ' (px)',
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} ul.products li.product {{CURRENT_ITEM}}' => 'min-height: {{VALUE}}px',
					],
					'conditions' => $conditions['minheight']
				]
			);

			$components->add_control(
				'hide_mobile',
				[
					'label' => esc_html__( 'Hide on mobiles', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- Default -', 'rey-core' ),
						'yes'  => esc_html__( 'Yes', 'rey-core' ),
						'no'  => esc_html__( 'No', 'rey-core' ),
					],
				]
			);

			$element->add_control(
				'comp_styles',
				[
					'label' => __( 'Component Style', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::REPEATER,
					'fields' => $components->get_controls(),
					'default' => [],
					'title_field' => '{{{ component }}}',
					'prevent_empty' => false,
				]
			);


		$element->end_controls_section();

	}

	public static function add_box_styles_controls( $element ){

		\ReyCore\Elementor\Helper::widgets_box_styles_controls([
			'element'       => $element,
			'selectors'     => [
				'active' => '{{WRAPPER}} ul.products li.product .--box-styler',
				'hover'  => '{{WRAPPER}} ul.products li.product .--box-styler:hover',
			],
			'section_title' =>  __( 'Product Box Styles', 'rey-core' ),
		]);

	}

	public static function add_loop_header_controls( $element ){

		$selector = '{{WRAPPER}} .rey-loopHeader';

		$element->start_controls_section(
			'section_header_styles',
			[
				'label' => __( 'Header Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'show_label' => false,
				'condition' => [
					'paginate!' => '',
					'show_header!' => '',
				],
			]
		);

			$element->add_control(
				'header_color',
				[
					'label' => esc_html__( 'Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selector => 'color: {{VALUE}};',
					],
				]
			);

			$element->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'header_typo',
					'selector' => $selector,
					'fields_options' => [
						'font_size' => [
							'selectors' => [
								'{{SELECTOR}}' => '--loop-header-font-size: {{SIZE}}{{UNIT}}',
							],
						],
					],
				]
			);

		$element->end_controls_section();

	}

	public static function component_mapping(){
		return [
			'title'     => [
				'name' => esc_html__('Title', 'rey-core'),
				'selector' => 'woocommerce-loop-product__title',
				'supports' => ['minheight']
			],
			'price'     => [
				'name' => esc_html__('Price', 'rey-core'),
				'selector' => 'rey-loopPrice',
			],
			'brand'     => [
				'name' => esc_html__('Brand', 'rey-core'),
				'selector' => 'rey-brandLink',
			],
			'category'  => [
				'name' => esc_html__('Categories', 'rey-core'),
				'selector' => 'rey-productCategories',
			],
			'excerpt'   => [
				'name' => esc_html__('Description', 'rey-core'),
				'selector' => 'woocommerce-product-details__short-description',
			],
			'atc'       => [
				'name' => esc_html__('Add to cart button', 'rey-core'),
				'selector' => 'add_to_cart_button',
				'supports' => ['btn_style', 'hover_color', 'bg_color', 'hover_bg_color']
			],
			'quickview' => [
				'name' => esc_html__('Quickview button', 'rey-core'),
				'selector' => 'rey-quickviewBtn',
				'supports' => ['btn_style', 'hover_color', 'bg_color', 'hover_bg_color']
			],
			'wishlist'  => [
				'name' => esc_html__('Wishlist', 'rey-core'),
				'selector' => 'rey-wishlistBtn-link',
				'supports' => ['hover_color', 'bg_color', 'hover_bg_color']
			],
			'compare'   => [
				'name' => esc_html__('Compare', 'rey-core'),
				'selector' => 'rey-compareBtn-link',
				'supports' => ['hover_color', 'bg_color', 'hover_bg_color']
			],
			'new'       => [
				'name' => esc_html__('New Badge', 'rey-core'),
				'selector' => 'rey-new-badge',
				'supports' => ['bg_color'],
			],
			'soldout'   => [
				'name' => esc_html__('Stock Badge', 'rey-core'),
				'selector' => 'rey-soldout-badge',
				'supports' => ['bg_color'],
			],
			'featured'  => [
				'name' => esc_html__('Featured Badge', 'rey-core'),
				'selector' => 'rey-featured-badge',
				'supports' => ['bg_color'],
			],
			'sale'      => [
				'name' => esc_html__('Discount badge', 'rey-core'),
				'selector' => 'rey-discount',
				'supports' => ['bg_color'],
			],
			'rating'      => [
				'name' => esc_html__('Star rating', 'rey-core'),
				'selector' => 'star-rating',
			],
		];
	}

	function before_products(){

		if( isset($this->_settings['comp_styles']) && !empty( $this->_settings['comp_styles'] ) ){

			$all_components = self::component_mapping();

			foreach ($this->_settings['comp_styles'] as $comp) {

				if( isset( $all_components[ $comp[ 'component' ] ] ) && $component = $all_components[ $comp[ 'component' ] ] ){

					$comp_classes[] = 'elementor-repeater-item-' . esc_attr($comp['_id']);

					if( isset($comp['hide_mobile']) ){
						if( 'no' === $comp['hide_mobile'] ){
							$comp_classes[] = '--show-mobile';
						}
						else {
							if( '' !== $comp['hide_mobile'] ){
								$comp_classes[] = '--dnone-sm';
							}
						}
					}

					self::$_selectors_to_replace[ $component['selector'] ] = implode(' ', $comp_classes);
				}

				if( isset($comp[ 'btn_style' ]) && $btn_style = $comp[ 'btn_style' ] ){
					self::$btn_styles[ $comp[ 'component' ] ] = $btn_style;
				}

			}
		}

		add_filter( 'theme_mod_loop_skin', [$this, 'set_loop_skin'] );
		add_filter( 'theme_mod_product_loop_template', [$this, 'set_product_loop_template'] );
		add_filter( 'theme_mod_loop_add_to_cart_style', [$this, 'atc_button_style'] );
		add_filter( 'theme_mod_loop_quickview_style', [$this, 'qv_button_style'] );
		add_filter( 'theme_mod_proto_loop_padded', [$this, 'proto_loop_padded'] );

		$this->add_remove_extra_data();

	}

	function add_remove_extra_data( $add = true ){

		if( ! (isset($this->_settings['extra_data']) && !empty($this->_settings['extra_data'])) ){
			return;
		}

		$positions = [
			'top_left'      => 'reycore/loop_inside_thumbnail/top-left',
			'top_right'     => 'reycore/loop_inside_thumbnail/top-right',
			'bottom_left'   => 'reycore/loop_inside_thumbnail/bottom-left',
			'bottom_right'  => 'reycore/loop_inside_thumbnail/bottom-right',
			'before_title'  => ['woocommerce_before_shop_loop_item_title', 13],
			'after_title'   => ['woocommerce_after_shop_loop_item_title', 10],
			'after_price'   => ['woocommerce_after_shop_loop_item_title', 11],
			'after_content' => ['woocommerce_after_shop_loop_item', 998],
		];

		// Basic & Cards have the hover effects
		if( in_array($this->_settings['loop_skin'], ['basic', 'cards']) ){
			$positions['after_price'] = $positions['after_content'];
		}
		else {

			$price_scheme = [];

			// get price hooks based on item skin
			if( $price_component = reycore_wc__get_loop_component('prices') ){
				$price_scheme = $price_component->get_scheme();
			}

			if( ! empty($price_hook) ){
				$positions['after_price'] = [
					$price_hook['tag'],
					$price_hook['priority'] + 1,
				];
			}
		}

		foreach ($positions as $name => $hook) {

			if( is_array($hook) ){
				$hook_position = $hook[0];
				$hook_priority = $hook[1];
			}
			else {
				$hook_position = $hook;
				$hook_priority = 10;
			}

			$method = 'add_action';

			if( ! $add ){
				$method = 'remove_action';
			}

			if( method_exists($this, "render_extra_data__{$name}") ){
				call_user_func( $method, $hook_position, [ $this, "render_extra_data__{$name}"], $hook_priority );
			}
		}
	}

	function render_extra_data__top_left(){
		$this->render_extra_data('top_left');
	}

	function render_extra_data__top_right(){
		$this->render_extra_data('top_right');
	}

	function render_extra_data__bottom_left(){
		$this->render_extra_data('bottom_left');
	}

	function render_extra_data__bottom_right(){
		$this->render_extra_data('bottom_right');
	}

	function render_extra_data__before_title(){
		$this->render_extra_data('before_title');
	}

	function render_extra_data__after_price(){
		$this->render_extra_data('after_price');
	}

	function render_extra_data__after_title(){
		$this->render_extra_data('after_title');
	}

	function render_extra_data__after_content(){
		$this->render_extra_data('after_content');
	}

	function render_extra_data( $position ){

		if( ! ($extra_data = $this->_settings['extra_data']) ){
			return;
		}

		foreach( $extra_data as $edata ){

			if( ! ($component = $edata['component']) ){
				continue;
			}

			if( $edata['position'] === '' ){
				continue;
			}

			if( $edata['position'] !== $position ){
				continue;
			}

			if( method_exists($this, "render_extra_data_item__{$component}") ){

				$classes[] = 'rey-peItem';
				$classes[] = 'elementor-repeater-item-' . esc_attr($edata['_id']);
				$classes[] = 'rey-pe-' . esc_attr($component);
				$classes[] = '--pos-' . esc_attr($position);

				// if yes show on mobiles
				if( $edata['mobile'] === '' ){
					$classes[] = '--dnone-sm';
				}

				$title = isset($edata['title']) && ! empty($edata['title']) ? $edata['title'] : '';

				call_user_func([$this, "render_extra_data_item__{$component}"], [
					'data' => $edata,
					'class' => implode(' ', $classes),
					'title' => $title ? "<span class='rey-peItem-title'>{$title}</span>" : '',
				]);
			}

		}
	}

	function render_extra_data_item__acf( $args = [] ){

		$args = wp_parse_args($args, [
			'data' => [],
			'class' => '',
			'title' => '',
		]);

		if( ! ($product = wc_get_product()) ){
			return;
		}

		if( ! ($acf_field = $args['data']['acf_field']) ){
			return;
		}

		$parts = explode(':', $acf_field);

		if( $field_val = get_field($parts[0]) ){

			if( 'text' === $args['data']['acf_display'] ){
				printf( '<div class="%2$s">%1$s</div>', $args['title'] . $field_val, $args['class']);
			}

			elseif( 'image' === $args['data']['acf_display'] && is_array($field_val) && isset($field_val['id']) ){
				$thumb_size = apply_filters('reycore/woocommerce/products/extra_data/acf_image_size', 'medium');
				$img = str_replace('width="1" height="1"', '', wp_get_attachment_image($field_val['id'], $thumb_size) );
				printf( '<div class="%2$s">%1$s</div>', $args['title'] . $img, $args['class']);
			}
		}

	}

	function render_extra_data_item__sku( $args = [] ){

		$args = wp_parse_args($args, [
			'data' => [],
			'class' => '',
			'title' => '',
		]);

		if( ($product = wc_get_product()) && $sku = $product->get_sku() ){
			printf( '<div class="%2$s">%1$s</div>', $args['title'] . $sku, $args['class']);
		}
	}

	function render_extra_data_item__dimensions( $args = [] ){

		$args = wp_parse_args($args, [
			'data' => [],
			'class' => '',
			'title' => '',
		]);

		if( ($product = wc_get_product()) && $dimensions = wc_format_dimensions( $product->get_dimensions( false ) ) ){
			printf( '<div class="%2$s">%1$s</div>', $args['title'] . $dimensions, $args['class'] );
		}
	}

	function render_extra_data_item__weight( $args = [] ){

		$args = wp_parse_args($args, [
			'data' => [],
			'class' => '',
			'title' => '',
		]);

		if( ($product = wc_get_product()) && $weight = wc_format_weight( $product->get_weight() ) ){
			printf( '<div class="%2$s">%1$s</div>', $args['title'] . $weight, $args['class'] );
		}
	}

	function render_extra_data_item__stock( $args = [] ){

		$args = wp_parse_args($args, [
			'data' => [],
			'class' => '',
			'title' => '',
		]);

		if( ! ($product = wc_get_product()) ){
			return;
		}

		if( ! $product->managing_stock() ){
			return;
		}

		if( ! ($stock_quantity = $product->get_stock_quantity()) ){
			return;
		}

		$stock_text = sprintf($args['data']['stock_text'], $stock_quantity);
		$stock_html = sprintf( '<div class="%2$s">%1$s</div>', $args['title'] . $stock_text, $args['class'] );

		echo apply_filters('reycore/woocommerce/products/extra_data/stock', $stock_html, $args, $stock_quantity);
	}

	function render_extra_data_item__placeholder( $args = [] ){

		$args = wp_parse_args($args, [
			'data' => [],
			'class' => '',
			'title' => '',
		]);

		if( ! ($placeholder_hook = $args['data']['placeholder_hook']) ){
			return;
		}

		echo apply_filters("reycore/woocommerce/products/extra_data/placeholder={$placeholder_hook}", '', $args);
	}

	function render_extra_data_item__attribute( $args = [] ){

		$args = wp_parse_args($args, [
			'data' => [],
			'class' => '',
			'title' => '',
		]);

		if( ! ($attribute = $args['data']['attribute']) ){
			return;
		}

		if( ! ($product = wc_get_product()) ){
			return;
		}

		$attribute_name = strpos( $attribute, 'pa_' ) !== 0 ? wc_attribute_taxonomy_name( $attribute ) : $attribute;

		if ( ! taxonomy_exists( $attribute_name ) ) {
			return;
		}

		$attribute_taxonomy = get_taxonomy($attribute_name);

		$attribute_values   = wc_get_product_terms( $product->get_id(), $attribute_name, ['fields' => 'all'] );

		$values = [];

		foreach ( $attribute_values as $attribute_value ) {
			$value_name = esc_html( $attribute_value->name );

			if ( isset($attribute_taxonomy->attribute_public) && $attribute_taxonomy->attribute_public ) {
				$values[] = '<a href="' . esc_url( get_term_link( $attribute_value->term_id, $attribute_name ) ) . '" rel="tag">' . $value_name . '</a>';
			} else {
				$values[] = $value_name;
			}
		}

		if( empty($values) ){
			return;
		}

		if( $attrs = wptexturize( implode( ', ', $values ) ) ){
			printf( '<div class="%2$s">%1$s</div>', $args['title'] . "<span>{$attrs}</span>", $args['class'] );
		}
	}

	function set_loop_skin($mod){

		if( isset($this->_settings['loop_skin']) && $loop_skin = $this->_settings['loop_skin']){
			return $loop_skin;
		}

		return $mod;
	}

	function set_product_loop_template($mod){

		// Check if Skin is set on template
		if( isset($this->_settings['loop_skin']) && 'template' === $this->_settings['loop_skin'] ){

			// check for the template ID
			if( isset($this->_settings['product_loop_template']) ){

				// it might exist, but if not, relies on the Customizer global setting
				if( $template_id = absint($this->_settings['product_loop_template']) ){
					return $template_id;
				}

				// if Customizer has look_skin on something else
				// but has a template ID set, should not inherit it.
				else {
					if ( ($mods = get_theme_mods()) && isset( $mods[ 'loop_skin' ] ) && 'template' !== $mods[ 'loop_skin' ] ) {
						return false;
					}
				}

			}
		}

		return $mod;
	}

	function atc_button_style($mod){

		if( isset(self::$btn_styles['atc']) && $style = self::$btn_styles['atc'] ){
			return $style;
		}

		return $mod;
	}

	function qv_button_style($mod){

		if( isset(self::$btn_styles['quickview']) && $style = self::$btn_styles['quickview'] ){
			return $style;
		}

		return $mod;
	}

	function proto_loop_padded($mod){

		if( isset($this->_settings['proto_loop_padded']) && $padded = $this->_settings['proto_loop_padded'] ){
			return $padded === 'yes';
		}

		return $mod;
	}

	function after_products(){

		remove_filter( 'theme_mod_loop_skin', [$this, 'set_loop_skin'] );
		remove_filter( 'theme_mod_product_loop_template', [$this, 'set_product_loop_template'] );
		remove_filter( 'theme_mod_loop_add_to_cart_style', [$this, 'atc_button_style'] );
		remove_filter( 'theme_mod_loop_quickview_style', [$this, 'qv_button_style'] );

		$this->add_remove_extra_data(false);

	}

	public static function add_skin_controls__basic( $element ){

		$element->start_controls_section(
			'section_basic_skin_styles',
			[
				'label' => __( 'Basic Skin Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'loop_skin' => 'basic',
					'_skin' => '',
				],
			]
		);

			$element->add_control(
				'basic_hover_animation',
				[
					'label' => esc_html__( 'Hover Animation', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- Inherit -', 'rey-core' ),
						'yes'  => esc_html__( 'Yes', 'rey-core' ),
						'no'  => esc_html__( 'No', 'rey-core' ),
					],
				]
			);

			$element->add_responsive_control(
				'basic_content_inner_padding',
				[
					'label' => esc_html__( 'Content Inner Padding', 'rey-core' ) . ' (px)',
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}}' => '--woocommerce-loop-basic-padding: {{VALUE}}px;',
					],
				]
			);

			$element->add_control(
				'basic_border_color',
				[
					'label' => esc_html__( 'Border Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .woocommerce ul.products.--skin-basic' => '--woocommerce-loop-basic-bordercolor: {{VALUE}}',
					],
					'condition' => [
						'gaps_v2' => '0',
					],
				]
			);

			$element->add_control(
				'basic_bg_color',
				[
					'label' => esc_html__( 'Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}}' => '--woocommerce-loop-basic-bgcolor: {{VALUE}}',
					],
				]
			);


		$element->end_controls_section();
	}

	public static function add_skin_controls__wrapped( $element ){

		$element->start_controls_section(
			'section_wrapper_styles',
			[
				'label' => __( 'Wrapper Skin Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'loop_skin' => 'wrapped',
					'_skin' => '',
				],
			]
		);

		$element->add_control(
			'wrapped_hover_animation',
			[
				'label' => esc_html__( 'Hover Animation', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => esc_html__( '- Inherit -', 'rey-core' ),
					'yes'  => esc_html__( 'Yes', 'rey-core' ),
					'no'  => esc_html__( 'No', 'rey-core' ),
				],
			]
		);

		$element->add_responsive_control(
			'wrapped_inner_padding',
			[
				'label' => __( 'Inner Padding', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} li.product.rey-wc-skin--wrapped .rey-loopWrapper-details' => 'bottom: {{BOTTOM}}{{UNIT}}; left: {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} li.product.rey-wc-skin--wrapped .rey-new-badge' => 'top: {{TOP}}{{UNIT}}; left: {{LEFT}}{{UNIT}};',
					'.rtl {{WRAPPER}} li.product.rey-wc-skin--wrapped .rey-loopWrapper-details' => 'bottom: {{BOTTOM}}{{UNIT}}; right: {{LEFT}}{{UNIT}}; left: auto;',
					'.rtl {{WRAPPER}} li.product.rey-wc-skin--wrapped .rey-new-badge' => 'top: {{TOP}}{{UNIT}}; right: {{LEFT}}{{UNIT}}; left: auto;',
				],
			]
		);

		$element->start_controls_tabs('wrapped_colors_tabs');

			$element->start_controls_tab(
				'wrapped_colors_active',
				[
					'label' => __( 'Active', 'rey-core' ),
			]);
			// Active
			$element->add_control(
				'wrapped_text_color',
				[
					'label' => __( 'Text Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-wc-skin--wrapped, {{WRAPPER}} .rey-wc-skin--wrapped a {{WRAPPER}} .rey-wc-skin--wrapped a:hover, {{WRAPPER}} .rey-wc-skin--wrapped .button, {{WRAPPER}} .rey-wc-skin--wrapped .reyEl-productGrid-cs-dots' => 'color: {{VALUE}}',
					],
				]
			);
			$element->add_control(
				'wrapped_overlay_color',
				[
					'label' => __( 'Overlay Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .woocommerce ul.products li.product.rey-wc-skin--wrapped .woocommerce-loop-product__link:after' => 'background-color: {{VALUE}}',
					],
				]
			);
			$element->end_controls_tab();

			$element->start_controls_tab(
				'wrapped_colors_hover',
				[
					'label' => __( 'Hover', 'rey-core' ),
			]);
			// Hover
			$element->add_control(
				'wrapped_text_color_hover',
				[
					'label' => __( 'Text Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-wc-skin--wrapped a:hover, {{WRAPPER}} .rey-wc-skin--wrapped .button' => 'color: {{VALUE}}',
					],
				]
			);
			$element->add_control(
				'wrapped_overlay_color_hover',
				[
					'label' => __( 'Overlay Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .woocommerce ul.products li.product.rey-wc-skin--wrapped:hover .woocommerce-loop-product__link:after' => 'background-color: {{VALUE}}',
					],
				]
			);
			$element->end_controls_tab();
		$element->end_controls_tabs();

		$element->end_controls_section();
	}


	public static function add_skin_controls__cards( $element ){

		$element->start_controls_section(
			'section_cards_skin_styles',
			[
				'label' => __( 'Cards Skin Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'loop_skin' => 'cards',
					'_skin' => '',
				],
			]
		);

			$element->add_control(
				'cards_hover_animation',
				[
					'label' => esc_html__( 'Hover Animation', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- Inherit -', 'rey-core' ),
						'yes'  => esc_html__( 'Yes', 'rey-core' ),
						'no'  => esc_html__( 'No', 'rey-core' ),
					],
				]
			);

			$element->add_responsive_control(
				'cards_content_inner_padding',
				[
					'label' => esc_html__( 'Content Inner Padding', 'rey-core' ) . ' (px)',
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}}' => '--woocommerce-loop-cards-padding: {{VALUE}}px;',
					],
				]
			);

			$element->add_control(
				'cards_border_color',
				[
					'label' => esc_html__( 'Border Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .woocommerce ul.products.--skin-cards' => '--woocommerce-loop-cards-bordercolor: {{VALUE}}',
					],
				]
			);

			$element->add_control(
				'cards_bg_color',
				[
					'label' => esc_html__( 'Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}}' => '--woocommerce-loop-cards-bgcolor: {{VALUE}}',
					],
				]
			);

			$element->add_control(
				'cards_expand_thumbs',
				[
					'label' => esc_html__( 'Expand Thumbnails', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- Inherit -', 'rey-core' ),
						'yes'  => esc_html__( 'Yes', 'rey-core' ),
						'no'  => esc_html__( 'No', 'rey-core' ),
					],
				]
			);

			$element->add_control(
				'cards_corner_radius',
				[
					'label' => esc_html__( 'Corner radius', 'rey-core' ) . ' (px)',
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .woocommerce ul.products.--skin-cards' => '--skin-cards-border-radius: {{VALUE}}px',
					],
				]
			);

		$element->end_controls_section();
	}

	public static function add_skin_controls__iconized( $element ){

		$element->start_controls_section(
			'section_iconized_skin_styles',
			[
				'label' => __( 'Iconized Skin Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'loop_skin' => 'iconized',
					'_skin' => '',
				],
			]
		);

			$element->add_control(
				'iconized_hover_animation',
				[
					'label' => esc_html__( 'Hover Animation', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- Inherit -', 'rey-core' ),
						'yes'  => esc_html__( 'Yes', 'rey-core' ),
						'no'  => esc_html__( 'No', 'rey-core' ),
					],
				]
			);

			$element->add_responsive_control(
				'iconized_content_inner_padding',
				[
					'label' => esc_html__( 'Content Inner Padding', 'rey-core' ) . ' (px)',
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} ul.products.--skin-iconized' => '--woocommerce-loop-iconized-padding: {{VALUE}}px;',
					],
				]
			);

			$element->add_control(
				'iconized_border_size',
				[
					'label' => esc_html__( 'Border Size', 'rey-core' ) . ' (px)',
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} ul.products.--skin-iconized' => '--woocommerce-loop-iconized-size: {{VALUE}}px;',
					],
				]
			);

			$element->add_control(
				'iconized_border_color',
				[
					'label' => esc_html__( 'Border Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ul.products.--skin-iconized' => '--woocommerce-loop-iconized-bordercolor: {{VALUE}}',
					],
				]
			);

			$element->add_control(
				'iconized_bg_color',
				[
					'label' => esc_html__( 'Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ul.products.--skin-iconized' => '--woocommerce-loop-iconized-bgcolor: {{VALUE}}',
					],
				]
			);

			$element->add_control(
				'iconized_corner_radius',
				[
					'label' => esc_html__( 'Corner radius', 'rey-core' ) . ' (px)',
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} ul.products.--skin-iconized' => '--woocommerce-loop-iconized-radius: {{VALUE}}px',
					],
				]
			);

		$element->end_controls_section();
	}

	public static function add_skin_controls__proto( $element ){

		$element->start_controls_section(
			'section_proto_skin_styles',
			[
				'label' => __( 'Proto Skin Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'loop_skin' => 'proto',
					'_skin' => '',
				],
			]
		);

			$element->add_control(
				'proto_hover_animation',
				[
					'label' => esc_html__( 'Hover Animation', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- Inherit -', 'rey-core' ),
						'yes'  => esc_html__( 'Yes', 'rey-core' ),
						'no'  => esc_html__( 'No', 'rey-core' ),
					],
				]
			);

			$element->add_control(
				'proto_text_color',
				[
					'label' => esc_html__( 'Text Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ul.products.--skin-proto' => '--woocommerce-loop-proto-color: {{VALUE}}',
					],
				]
			);

			$element->add_control(
				'proto_link_color',
				[
					'label' => esc_html__( 'Link Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ul.products.--skin-proto' => '--woocommerce-loop-proto-color-link: {{VALUE}}',
					],
				]
			);

			$element->add_control(
				'proto_bg_color',
				[
					'label' => esc_html__( 'Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} ul.products.--skin-proto' => '--woocommerce-loop-proto-bgcolor: {{VALUE}}',
					],
				]
			);

			$element->add_control(
				'proto_loop_padded',
				[
					'label' => esc_html__( 'Inner Padding', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- Inherit -', 'rey-core' ),
						'yes'  => esc_html__( 'Yes', 'rey-core' ),
						'no'  => esc_html__( 'No', 'rey-core' ),
					],
				]
			);

			$element->add_control(
				'proto_loop_shadow',
				[
					'label' => esc_html__( 'Box Shadow', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- Inherit -', 'rey-core' ),
						'no' => esc_html__( 'Disabled', 'rey-core' ),
						'1' => esc_html__( 'Level 1', 'rey-core' ),
						'2' => esc_html__( 'Level 2', 'rey-core' ),
						'3' => esc_html__( 'Level 3', 'rey-core' ),
						'4' => esc_html__( 'Level 4', 'rey-core' ),
						'5' => esc_html__( 'Level 5', 'rey-core' ),
					],
					'condition' => [
						'proto_loop_padded' => 'yes',
					],
				]
			);
			$element->add_control(
				'proto_loop_shadow_hover',
				[
					'label' => esc_html__( 'Hover Box Shadow', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- Inherit -', 'rey-core' ),
						'no' => esc_html__( 'Disabled', 'rey-core' ),
						'1' => esc_html__( 'Level 1', 'rey-core' ),
						'2' => esc_html__( 'Level 2', 'rey-core' ),
						'3' => esc_html__( 'Level 3', 'rey-core' ),
						'4' => esc_html__( 'Level 4', 'rey-core' ),
						'5' => esc_html__( 'Level 5', 'rey-core' ),
					],
					'condition' => [
						'proto_loop_padded' => 'yes',
					],
				]
			);


		$element->end_controls_section();
	}

	function lazy_start(){

		// Initial Load (not Ajax)
		if( '' !== $this->_settings['lazy_load'] &&
			'yes' !== $this->_settings['paginate'] &&
			! wp_doing_ajax() &&
			! ( reycore__elementor_edit_mode() ) ){

			$qid = (isset($GLOBALS['global_section_ids']) && ($gs_ids = $GLOBALS['global_section_ids'])) ? end($gs_ids) : get_queried_object_id();

			$config = [
				'element_id' => $this->_args['el_instance']->get_id(),
				'skin' => $this->_settings['_skin'],
				'trigger' => $this->_settings['lazy_load_trigger'] ? $this->_settings['lazy_load_trigger'] : 'scroll',
				'qid' => apply_filters('reycore/elementor/product_grid/lazy_load_qid', $qid),
				'pid' => get_the_ID(),
				'options' => apply_filters('reycore/elementor/product_grid/lazy_load_options', [
					'prevent_ba_content' => 'yes',
					'prevent_stretched' => 'yes'
				]),
				'cache' => $this->_settings['lazy_load_cache'] !== '',
			];

			if( 'click' === $this->_settings['lazy_load_trigger'] ){
				$config['trigger__click'] = $this->_settings['lazy_load_click_trigger'];
			}

			$this->_args['el_instance']->add_render_attribute( '_wrapper', [
				'data-lazy-load' => wp_json_encode( $config )
			] );

			if( $this->_settings['_skin'] === 'carousel' ){
				$per_row = $this->_settings['slides_to_show'];
				$per_row_tablet = isset($this->_settings['slides_to_show_tablet']) ? $this->_settings['slides_to_show_tablet'] : reycore_wc_get_columns('tablet');
				$per_row_mobile = isset($this->_settings['slides_to_show_mobile']) ? $this->_settings['slides_to_show_mobile'] : reycore_wc_get_columns('mobile');
			}
			else {
				$per_row = $this->_settings['per_row'];
				$per_row_tablet = isset($this->_settings['per_row_tablet']) ? $this->_settings['per_row_tablet'] : reycore_wc_get_columns('tablet');
				$per_row_mobile = isset($this->_settings['per_row_mobile']) ? $this->_settings['per_row_mobile'] : reycore_wc_get_columns('mobile');
			}

			$count = $this->_settings['_skin'] !== 'carousel' ? ( $this->_settings['limit'] ? $this->_settings['limit'] : $per_row ) : $per_row;

			echo reycore__lazy_placeholders([
				'class'              => 'placeholder_products products',
				'filter_title'       => 'placeholder_products',
				'blocktitle'         => false,
				'desktop'            => absint($per_row),
				'tablet'             => absint($per_row_tablet),
				'mobile'             => absint($per_row_mobile),
				'limit'              => $count,
				'placeholders_class' => isset($this->_args['placeholder_class']) ? $this->_args['placeholder_class'] : '',
				// 'nowrap'             => $this->_settings['carousel'] === '',
			]);

			reycore_assets()->add_scripts(['reycore-elementor-elem-lazy-load']);

			do_action('reycore/elementor/product_grid/lazy_load_assets', $this->_settings);

			return true;
		}

	}

	function lazy_end(){

	}


	function ajax_load_more(){

		if (($this->_settings['ajax_load_more'] ?? null) !== 'yes') {
			return;
		}

		if( $this->_settings['paginate'] === 'yes' ){
			return;
		}

		if( ! ($this->_settings['_skin'] === '' || $this->_settings['_skin'] === 'mini') ){
			return;
		}

		$button_text = esc_html__('LOAD MORE', 'rey-core');

		$classes = [
			'style' => 'btn-line-active'
		];

		if( $custom_text = $this->_settings['ajax_load_more_text'] ){
			$button_text = $custom_text;
		}

		reycore_assets()->add_styles('rey-buttons');

		if( $btn_style = $this->_settings['ajax_load_more_btn_style'] ){
			$classes['style'] = $btn_style;
		}

		$limit = $this->_settings['ajax_load_more_limit'] ? $this->_settings['ajax_load_more_limit'] : $this->_settings['limit'];

		if( reycore__elementor_edit_mode() ) {
			$classes[] = '--disabled';
		}

		$qid = (isset($GLOBALS['global_section_ids']) && ($gs_ids = $GLOBALS['global_section_ids'])) ? end($gs_ids) : get_queried_object_id();

		if(
			isset($_REQUEST[\ReyCore\Ajax::ACTION_KEY]) &&
			'element_lazy' === reycore__clean($_REQUEST[\ReyCore\Ajax::ACTION_KEY]) &&
			isset($_REQUEST[\ReyCore\Ajax::DATA_KEY], $_REQUEST[\ReyCore\Ajax::DATA_KEY]['qid'])
		){
			$qid = absint($_REQUEST[\ReyCore\Ajax::DATA_KEY]['qid']);
		}

		$config = [
			'element_id' => $this->_args['el_instance']->get_id(),
			'skin'       => $this->_settings['_skin'],
			'qid'        => apply_filters('reycore/elementor/product_grid/load_more_qid', $qid),
			'options'    => apply_filters('reycore/elementor/product_grid/load_more_options', [
				'prevent_ba_content' => 'yes',
				'prevent_stretched'  => 'yes'
			]),
			'offset' => absint($this->_settings['limit']),
			'limit' => absint($limit),
			'max'   => $this->_settings['ajax_load_more_max'] ? absint($this->_settings['ajax_load_more_max']) : 1
		];

		printf('<div class="rey-pg-loadmoreWrapper"><button class=\'btn rey-pg-loadmore %3$s\' data-config=\'%2$s\'><span class="rey-pg-loadmoreText">%1$s</span><div class="rey-lineLoader"></div></button></div>',
			$button_text,
			wp_json_encode( $config ),
			implode(' ', $classes)
		);

	}
}
