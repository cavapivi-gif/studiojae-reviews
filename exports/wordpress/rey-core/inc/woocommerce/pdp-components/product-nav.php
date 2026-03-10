<?php
namespace ReyCore\WooCommerce\PdpComponents;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class ProductNav extends Component {

	public $settings = [];

	public function init(){
		add_shortcode('rey_product_navigation', [$this, 'render']);
		add_filter('navigation_markup_template', [$this, 'replace_the_h2']);
	}

	public function get_id(){
		return 'product_nav';
	}

	public function get_name(){
		return 'Product Navigation';
	}

	function get_type(){
		return get_theme_mod('product_navigation', '1');
	}

	public function get_settings(){

		$same_term = get_theme_mod('product_navigation_same_term', true);

		$this->settings = apply_filters('reycore/woocommerce/product_nav_settings', [
			'title_limit'  => 5,
			'in_same_term' => $same_term,
			'only_childless_categories' => $same_term,
			'all_subcategories' => $same_term,
		]);
	}

	public function replace_the_h2( $template ){
		return str_replace('<h2 class="screen-reader-text">%2$s</h2>', '<div class="screen-reader-text">%2$s</div>', $template);
	}

	/**
	 * Exclude parent categories
	 *
	 * @param array $terms
	 * @return array
	 * @since 2.3.4
	 */
	public function set_only_childless_categories($terms, $object_ids, $taxonomies, $args){

		if( ! (isset($args['fields']) && 'ids' === $args['fields']) ){
			return $terms;
		}

		foreach ($terms as $k => $term) {
			if( ! empty( get_term_children($term, 'product_cat') ) ){
				unset($terms[$k]);
			}
		}

		if( ! $this->settings['all_subcategories'] && count($terms) > 1 ){
			return [ end($terms) ];
		}

		return $terms;
	}

	public function render( $force = false )
	{

		if( ! $this->maybe_render() ){
			return;
		}

		if( $this->get_type() === '2' && ! $force ) {
			return;
		}

		reycore_assets()->add_styles('rey-wc-product-nav');

		$this->get_settings();

		add_filter( 'get_previous_post_where', [$this, 'get_adjacent_product_where__prev'], 10, 5);
		add_filter( 'get_next_post_where', [$this, 'get_adjacent_product_where__next'], 10, 5);
		add_filter( 'get_previous_post_join', [$this, 'get_adjacent_product_join']);
		add_filter( 'get_next_post_join', [$this, 'get_adjacent_product_join']);
		add_filter( 'get_previous_post_sort', [$this, 'get_adjacent_post_sort'], 10, 3);
		add_filter( 'get_next_post_sort', [$this, 'get_adjacent_post_sort'], 10, 3);

		if( $this->settings['only_childless_categories'] ){
			add_filter( 'wp_get_object_terms', [$this, 'set_only_childless_categories'], 10, 4 );
		}

			echo $this->get_the_post_navigation();

		if( $this->settings['only_childless_categories'] ){
			remove_filter( 'wp_get_object_terms', [$this, 'set_only_childless_categories'], 10 );
		}

		remove_filter( 'get_previous_post_where', [$this, 'get_adjacent_product_where__prev'], 10, 5);
		remove_filter( 'get_next_post_where', [$this, 'get_adjacent_product_where__next'], 10, 5);
		remove_filter( 'get_previous_post_join', [$this, 'get_adjacent_product_join']);
		remove_filter( 'get_next_post_join', [$this, 'get_adjacent_product_join']);
		remove_filter( 'get_previous_post_sort', [$this, 'get_adjacent_post_sort'], 10);
		remove_filter( 'get_next_post_sort', [$this, 'get_adjacent_post_sort'], 10);

	}

	/**
	 * Retrieves the navigation to next/previous post, when applicable.
	 *
	 * @since 4.1.0
	 * @since 4.4.0 Introduced the `in_same_term`, `excluded_terms`, and `taxonomy` arguments.
	 * @since 5.3.0 Added the `aria_label` parameter.
	 * @since 5.5.0 Added the `class` parameter.
	 *
	 * @param array $args {
	 *     Optional. Default post navigation arguments. Default empty array.
	 *
	 *     @type string       $prev_text          Anchor text to display in the previous post link.
	 *                                            Default '%title'.
	 *     @type string       $next_text          Anchor text to display in the next post link.
	 *                                            Default '%title'.
	 *     @type bool         $in_same_term       Whether link should be in the same taxonomy term.
	 *                                            Default false.
	 *     @type int[]|string $excluded_terms     Array or comma-separated list of excluded term IDs.
	 *                                            Default empty.
	 *     @type string       $taxonomy           Taxonomy, if `$in_same_term` is true. Default 'category'.
	 *     @type string       $screen_reader_text Screen reader text for the nav element.
	 *                                            Default 'Post navigation'.
	 *     @type string       $aria_label         ARIA label text for the nav element. Default 'Posts'.
	 *     @type string       $class              Custom class for the nav element. Default 'post-navigation'.
	 * }
	 * @return string Markup for post links.
	 */
	public function get_the_post_navigation() {

		static $navigation;

		if( is_null($navigation) ){

			$args = [
				'format'             => '<div class="nav-%dir">%link</div>',
				'in_same_term'       => $this->settings['in_same_term'],
				'excluded_terms'     => [],
				'taxonomy'           => 'product_cat',
				'screen_reader_text' => esc_html__( 'Product navigation', 'rey-core' ),
				'aria_label'         => __( 'Products' ),
				'class'              => 'post-navigation',
			];

			$navigation = '';

			$previous = $this->get_adjacent_link($args, true);
			$next = $this->get_adjacent_link($args, false);

			// Only add markup if there's somewhere to navigate to.
			if ( $previous || $next ) {
				$navigation = _navigation_markup( $previous . $next, $args['class'], $args['screen_reader_text'], $args['aria_label'] );
			}

		}

		return $navigation;
	}

	public function get_adjacent_link( $args, $previous = true ){

		$adjacent_post = get_adjacent_post( $args['in_same_term'], $args['excluded_terms'], $previous, $args['taxonomy'] );

		if( ! $adjacent_post ){
			return;
		}

		$title = $adjacent_post->post_title;

		if ( empty( $adjacent_post->post_title ) ) {
			$title = $previous ? __( 'Previous Post' ) : __( 'Next Post' );
		}

		/** This filter is documented in wp-includes/post-template.php */
		$title = apply_filters( 'the_title', $title, $adjacent_post->ID );

		$date = mysql2date( get_option( 'date_format' ), $adjacent_post->post_date );
		$rel  = $previous ? 'prev' : 'next';

		$string = '<a href="' . get_permalink( $adjacent_post ) . '" rel="' . $rel . '">';
		$inlink = str_replace( '%title', $title, $this->get_nav_text( $adjacent_post, $previous ) );
		$inlink = str_replace( '%date', $date, $inlink );
		$inlink = $string . $inlink . '</a>';

		$output = str_replace( [
			'%link',
			'%dir',
		], [
			$inlink,
			$previous ? 'previous' : 'next',
		], $args['format'] );

		return $output;
	}

	public function get_nav_text( $adjacent_post, $previous = true ){

		if( ! $adjacent_post ){
			return;
		}

		$product = wc_get_product( $adjacent_post->ID );
		$product_id = $product->get_id();

		return sprintf('
			<span class="rey-productNav__meta" data-id="%1$s" aria-hidden="true" title="%2$s">%3$s</span>
			%7$s
			<div class="rey-productNav__metaWrapper --%4$s">
				<div class="rey-productNav__thumb">%5$s</div>
				%6$s
			</div>',
			esc_attr( $product_id ),
			esc_attr( $product->get_title() ),
			reycore__arrowSvg(['right' => !$previous]),
			esc_attr($this->get_type()),
			$this->get_thumbnail($product),
			$this->get_extended($product),
			$this->get_screen_text($previous)
		);
	}

	public function get_screen_text($previous = true){

		$text = __( 'Previous product:', 'rey-core' );

		if( !$previous ){
			$text = __( 'Next product:', 'rey-core' );
		}

		return sprintf('<span class="screen-reader-text">%s</span>', $text );
	}

	public function get_thumbnail($product){

		$thumbnail_id = $product->get_image_id();
		$thumbnail_size = 'woocommerce_gallery_thumbnail';

		if( $this->get_type() === 'full' ){
			$thumbnail_size = 'woocommerce_single';
		}

		return !empty($thumbnail_id) ? wp_get_attachment_image( absint($thumbnail_id), $thumbnail_size ) : '';
	}

	public function get_extended($product){

		if( $this->get_type() !== 'extended' && $this->get_type() !== 'full' ){
			return;
		}

		$title = $this->get_title($product, $this->get_type() === 'extended');

		// Product bundles are accessing several methods that affect current product page's settings.
		$price = ! is_a($product, 'WC_Product_Bundle') ? wp_kses_post( $product->get_price_html() ) : '';

		return sprintf('<div class="rey-productNav__metaExtend"><div class="rey-productNav__title">%1$s</div><div class="rey-productNav__price">%2$s</div></div>', $title, $price);
	}

	public function get_title($product, $limited = false){

		if( $limited ){
			return reycore__limit_text( $product->get_title(), $this->settings['title_limit'] );
		}

		return $product->get_title();
	}

	public static function is_custom_engine(){
		return 'custom' === get_theme_mod('product_navigation_engine', 'default');
	}

	/**
	 * Filter Adjacent Post SORT query
	 *
	 * @since 2.6.0
	 */
	function get_adjacent_post_sort($sort, $post, $order){

		if( self::is_custom_engine() ){
			$sort = "ORDER BY p.menu_order $order LIMIT 1";
		}

		return $sort;
	}

	/**
	 * Filter Adjacent Post JOIN query
	 * to exclude out of stock items
	 *
	 * @since 1.3.7
	 */
	function get_adjacent_product_join($join){
		global $wpdb;

		if ( self::is_out_of_stock() ) {
			$join = $join . " INNER JOIN $wpdb->postmeta ON ( p.ID = $wpdb->postmeta.post_id )";
		}

		return $join;
	}

	/**
	 * Filter Adjacent Post WHERE query
	 * to exclude out of stock items
	 *
	 * @since 1.3.7
	 */
	function get_adjacent_product_where($where, $in_same_term, $excluded_terms, $taxonomy, $post){

		global $wpdb;

		// remove date
		if( self::is_custom_engine() || 'default_alt' === get_theme_mod('product_navigation_engine', 'default') ){
			$where = str_replace([
				sprintf("p.post_date > '%s' AND", $post->post_date),
				sprintf("p.post_date < '%s' AND", $post->post_date),
			], '', $where);
		}

		if ( self::is_out_of_stock() ) {
			$where = $wpdb->prepare("$where AND ($wpdb->postmeta.meta_key = %s AND $wpdb->postmeta.meta_value NOT LIKE %s)", '_stock_status', 'outofstock');
		}

		// Exclude "hidden from catalog" products
		if ($post->post_type === 'product' && in_array('hidden_catalog', self::excludes(), true) ) {

			static $hidden_ids;

			if( is_null($hidden_ids) ){
				$hidden_ids = reycore_wc__get_hidden_product_ids();
			}

			if (! empty($hidden_ids)) {
				$where = $wpdb->prepare("$where AND p.ID NOT IN (%s)", implode(',', $hidden_ids));
			}
		}

		return $where;
	}

	public function get_adjacent_product_where__prev($where, $in_same_term, $excluded_terms, $taxonomy, $post){

		global $wpdb;

		if( self::is_custom_engine() ){
			$where = $wpdb->prepare("$where AND p.menu_order < %d", $post->menu_order);
		}

		$where = $this->get_adjacent_product_where($where, $in_same_term, $excluded_terms, $taxonomy, $post);

		return $where;
	}

	public function get_adjacent_product_where__next($where, $in_same_term, $excluded_terms, $taxonomy, $post){

		global $wpdb;

		if( self::is_custom_engine() ){
			$where = $wpdb->prepare("$where AND p.menu_order > %d", $post->menu_order);
		}

		$where = $this->get_adjacent_product_where($where, $in_same_term, $excluded_terms, $taxonomy, $post);

		return $where;
	}

	/**
	 * Work in progress.
	 * Find alternative to -1 as it can become a very heavy query
	 *
	 * @return void
	 */
	public function alternative_method(){

		$categories = wc_get_product_term_ids( get_the_ID(), 'product_cat' );

		$q_args = array(
			'post_type'      => 'product',
			'posts_per_page' => -1, // Get all products; be cautious as this can be performance-intensive
			'tax_query'      => array(
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'term_id',
					'terms'    => $categories,
					'operator' => 'IN',
				),
			),
			'fields' => 'ids',
		);

		$q_args = array_merge(WC()->query->get_catalog_ordering_args(), $q_args);

		$products_query = new \WP_Query( $q_args );

		$prods = $products_query->posts;
		$current = array_search(get_the_ID(), $prods);
		$prevID = isset($prods[$current-1]) ? $prods[$current-1] : 0;
		$nextID = isset($prods[$current+1]) ? $prods[$current+1] : 0;

	}

	public static function is_out_of_stock(){
		return 'yes' == get_option( 'woocommerce_hide_out_of_stock_items' ) || ( in_array('outofstock', self::excludes(), true) );
	}

	public static function excludes(){
		return get_theme_mod('product_navigation__exclude', []);
	}
}
