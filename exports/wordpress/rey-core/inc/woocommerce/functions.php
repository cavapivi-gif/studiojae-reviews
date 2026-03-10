<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly


if ( ! function_exists( 'reycore_wc__get_loop_component' ) ):

	function reycore_wc__get_loop_component( $c ) {

		if( ! ($loop = \ReyCore\Plugin::instance()->woocommerce_loop) ){
			return;
		}

		$instance = \ReyCore\Plugin::instance()->woocommerce_loop;

		if( $component = $instance->get_component($c) ){
			return $component;
		}

		$component = null;

		// maybe in groups
		if( ($groups = $instance->get_components_groups()) && in_array( $c, $groups, true ) ){

			foreach ($groups as $g_real_component => $g_parent) {
				if( $g_parent === $c ){
					if( $g_component = $instance->get_component($g_real_component) ){
						if( $g_component->group_default() ){
							$component = $g_component;
							break;
						}
					}
				}
			}

		}

		if( ! $component ){
			return;
		}

		return $component;

	}
endif;


if ( ! function_exists( 'reycore_wc__get_loop_component_status' ) ):

	function reycore_wc__get_loop_component_status( $c ) {

		if( ! ($component = reycore_wc__get_loop_component( $c )) ){
			return;
		}

		return $component->get_status();

	}
endif;


if ( ! function_exists( 'reycore_wc__get_pdp_component' ) ):

	function reycore_wc__get_pdp_component( $id ) {

		if( ! ($instance = \ReyCore\Plugin::instance()->woocommerce_pdp) ){
			return;
		}

		if( $component = $instance->get_component($id) ){
			return $component;
		}

	}
endif;

if ( ! function_exists( 'reycore_wc__get_tag' ) ):

	function reycore_wc__get_tag( $tag ) {

		if( ! ($tags = \ReyCore\Plugin::instance()->woocommerce_tags) ){
			return;
		}

		if( ! (isset($tags[ $tag ]) && ($the_tag = $tags[ $tag ])) ){
			return;
		}

		return $the_tag;

	}
endif;

if(!function_exists('reycore_wc__is_catalog')):
/**
 * Check if store is catalog
 *
 * @since 1.2.0
 **/
function reycore_wc__is_catalog()
{
	return get_theme_mod('shop_catalog', false) === true;
}
endif;


if(!function_exists('reycore_wc__reset_filters_link')):
	/**
	 * Get link for resetting filters
	 *
	 * @since 1.0.0
	 **/
	function reycore_wc__reset_filters_link()
	{
		$link = '';

		if ( defined( 'SHOP_IS_ON_FRONT' ) ) {
			$link = home_url();
		} elseif ( is_shop() ) {
			$link = get_permalink( wc_get_page_id( 'shop' ) );
		} elseif ( is_product_category() && $cat = get_query_var( 'product_cat' ) ) {
			$link = get_term_link( $cat, 'product_cat' );
		} elseif ( is_product_tag() && $tag = get_query_var( 'product_tag' ) ) {
			$link = get_term_link( $tag, 'product_tag' );
		} else {
			$queried_object = get_queried_object();
			if( is_object($queried_object) && isset($queried_object->slug) && !empty($queried_object->slug) && isset($queried_object->taxonomy) ){
				$link = get_term_link( $queried_object->slug, $queried_object->taxonomy );
			}
		}

		/**
		 * Search Arg.
		 * To support quote characters, first they are decoded from &quot; entities, then URL encoded.
		 */
		if ( ($search_query = get_search_query()) && ! isset($_REQUEST['keyword']) ) {
			$link = add_query_arg( 's', rawurlencode( wp_specialchars_decode( $search_query ) ), $link );
		}

		// Post Type Arg
		if ( isset( $_REQUEST['post_type'] ) ) {
			$link = add_query_arg( 'post_type', reycore__clean($_REQUEST['post_type']), $link );
		}

		return esc_url( apply_filters('reycore/woocommerce/reset_filters_link', $link) );
	}
endif;


if(!function_exists('reycore_wc__check_filter_panel')):
	/**
	 * Check if panel filter is enabled
	 *
	 * @since 1.0.0
	 **/
	function reycore_wc__check_filter_panel()
	{
		return is_active_sidebar( 'filters-sidebar' );
	}
endif;


if(!function_exists('reycore_wc__check_filter_sidebar_top')):
	/**
	 * Check if top sidebar filter is enabled
	 *
	 * @since 1.0.0
	 **/
	function reycore_wc__check_filter_sidebar_top()
	{
		return is_active_sidebar( 'filters-top-sidebar' );
	}
endif;


if(!function_exists('reycore_wc__check_shop_sidebar')):
	/**
	 * Check if shop sidebar filter is enabled
	 *
	 * @since 1.5.0
	 **/
	function reycore_wc__check_shop_sidebar()
	{
		return apply_filters('reycore/woocommerce/check_shop_sidebar', is_active_sidebar( 'shop-sidebar' ) );
	}
endif;


if(!function_exists('reycore_wc__get_active_filters')):
	/**
	 * Check if panel filter is enabled
	 *
	 * @since 1.0.0
	 **/
	function reycore_wc__get_active_filters()
	{
		return apply_filters('reycore/woocommerce/get_active_filters', 0);
	}
endif;


if(!function_exists('reycore_wc_get_columns')):
	/**
	 * Get Enabled WooCommerce Components
	 */
	function reycore_wc_get_columns( $device = '' ){

		$devices = apply_filters('reycore/woocommerce/columns', [
			'desktop' => wc_get_default_products_per_row(),
			'tablet' => get_theme_mod('woocommerce_catalog_columns_tablet', 2),
			'mobile' => get_theme_mod('woocommerce_catalog_columns_mobile', 2)
		]);

		if( isset($devices[$device]) ){
			return absint($devices[$device]);
		}

		return $devices;
	}
endif;

if(!function_exists('reycore__product_grid_attributes')):
/**
 * Get Product Grid Attributes
 *
 * @since 2.4.0
 **/
function reycore__product_grid_attributes( $attributes = [], $settings = [] )
{

	/**
	 * Pass attributes to the product loop grid wrapper (ul.products)
	 * Use this one instead of the one below which is deprecated.
	 *
	 * @since 2.4.0
	 */
	$_attrs = reycore__implode_html_attributes(
		apply_filters('reycore/woocommerce/product_loop_attributes/v2', $attributes, $settings)
	);

	/**
	 * Initial attributes hook. Should have been an array from the start,
	 * however it's going to be kept maintained.
	 *
	 * @deprecated 2.4.0
	 * @since 2.4.0
	 */
	$_attrs .= ' ' . apply_filters('reycore/woocommerce/product_loop_attributes', '', $settings);

	return $_attrs;
}
endif;

if(!function_exists('reycore__product_grid_classes')):
/**
 * Get Product Grid CSS classes
 *
 * @since 2.4.0
 **/
function reycore__product_grid_classes($classes = [])
{

	/**
	 * Pass css classes to the product loop grid (ul.products)
	 *
	 * @since 2.4.0
	 * @type array
	 */
	return implode(' ', apply_filters('reycore/woocommerce/product_loop_classes', $classes));

}
endif;


if(!function_exists('reycore_wc__check_filter_btn')):
	/**
	 * Check if filter button should be added
	 *
	 * @since 1.9.0
	 **/
	function reycore_wc__check_filter_btn()
	{
		if( $custom_sidebar = apply_filters('reycore/woocommerce/filter_button/custom_sidebar', '') ){
			return $custom_sidebar;
		}

		if( $mobile_btn = get_theme_mod('ajaxfilter_mobile_button_opens', '') ){
			return $mobile_btn;
		}

		if( reycore_wc__check_filter_panel() ){
			return 'filters-sidebar';
		}

		else if( reycore_wc__check_shop_sidebar() && get_theme_mod('ajaxfilter_shop_sidebar_mobile_offcanvas', true) ){
			return 'shop-sidebar';
		}

		else if( reycore_wc__check_filter_sidebar_top() ){
			return 'filters-top-sidebar';
		}

		return false;
	}
endif;


if(!function_exists('reycore_wc__get_setting')):
	/**
	 * Get setting with skin default
	 *
	 * @since 2.2.0
	 **/
	function reycore_wc__get_setting( $setting )
	{

		if( ! ($woo_loop = \ReyCore\Plugin::instance()->woocommerce_loop) ){
			return;
		}

		$default = $woo_loop->get_default_settings($setting);

		if( $mod = get_theme_mod($setting, $default) ){
			return $mod;
		}

		return $default;
	}
endif;

if(!function_exists('reycore_wc__check_downloads_endpoint')):
	/**
	 * Check if downloads endpoint is disabled
	 *
	 * @since 1.0.0
	 */
	function reycore_wc__check_downloads_endpoint() {
		return get_option('woocommerce_myaccount_downloads_endpoint') != '';
	}
endif;

if(!function_exists('reycore_wc__count_downloads')):
	/**
	 * Get downloads count and store in transient
	 *
	 * @since 1.0.0
	 */
	function reycore_wc__count_downloads( $user_id ) {

		reycore__maybe_disable_obj_cache();

		if( ! $user_id && isset($GLOBALS['rey_ajax_login_uid']) ){
			$user_id = $GLOBALS['rey_ajax_login_uid'];
		}

		$transient_name = "rey-wc-user-dld-{$user_id}";

		if ( false === ( $downloads_count = get_transient( $transient_name ) ) ) {

			if( isset(WC()->customer) ){
				$customer = WC()->customer;
			}
			else {
				$customer = new WC_Customer( $user_id );
			}

			$downloads_count = $customer->get_downloadable_products();
			set_transient( $transient_name, $downloads_count, HOUR_IN_SECONDS );
		}

		return $downloads_count;
	}
endif;

if(!function_exists('reycore_wc__count_orders')):
	/**
	 * Get orders count and store in transient
	 *
	 * @since 1.0.0
	 */
	function reycore_wc__count_orders( $user_id ) {

		reycore__maybe_disable_obj_cache();

		if( ! $user_id && isset($GLOBALS['rey_ajax_login_uid']) ){
			$user_id = $GLOBALS['rey_ajax_login_uid'];
		}

		$transient_name = "rey-wc-user-order-{$user_id}";

		if ( false === ( $orders_count = get_transient( $transient_name ) ) ) {
			$orders_count = wc_get_customer_order_count($user_id);
			set_transient( $transient_name, $orders_count, HOUR_IN_SECONDS );
		}

		return $orders_count;
	}
endif;

if(!function_exists('reycore_wc__account_counters_reset')):
	/**
	 * Reset orders count transients
	 *
	 * @since 1.6.3
	 */
	function reycore_wc__account_counters_reset( $order_id ) {
		if( ($order = wc_get_order( $order_id )) && ($user_id = $order->get_user_id()) ){
			delete_transient("rey-wc-user-order-{$user_id}");
			delete_transient("rey-wc-user-dld-{$user_id}");
		}
	}
	add_action( 'woocommerce_delete_shop_order_transients', 'reycore_wc__account_counters_reset' );
endif;


if(!function_exists('reycore_wc__product_categories')):
	/**
	 * WooCommerce Product Query
	 * @return array
	 */
	function reycore_wc__product_categories( $args = [] ){

		$args = wp_parse_args($args, [
			'hide_empty'         => true,
			'parent'             => false,
			'labels'             => false,
			'hierarchical'       => false,
			'orderby'            => 'term_id',
			'extra_item'         => [],
			'field'              => 'slug',
			'hide_uncategorized' => false,
			'exclude'            => [],
		]);

		$terms_args = [
			'taxonomy'   => 'product_cat',
			'hide_empty' => $args['hide_empty'],
			'orderby' => $args['orderby'], // 'name', 'term_id', 'term_group', 'parent', 'menu_order', 'count'
			'update_term_meta_cache'	=> false,
		];

		if( $args['hide_uncategorized'] && $uncategorized = get_option( 'default_product_cat' ) ){
			$terms_args['exclude'] = (array) $uncategorized;
		}

		if( ! empty($args['exclude']) ){
			$exclude = isset($terms_args['exclude']) ? $terms_args['exclude'] : [];
			$terms_args['exclude'] = array_merge($exclude, $args['exclude']);
		}

		// if parent only
		if( $args['parent'] === 0 ){
			$terms_args['parent'] = 0;
		}

		// if subcategories
		elseif( $args['parent'] !== 0 && $args['parent'] !== false && ! is_array($args['parent']) ){

			$parent_term = false;

			// if automatic
			if( $args['parent'] === '' ) {
				if( ! is_shop() ){
					$parent_term = get_queried_object();
				}
			}
			// if pre-defined parent category
			else {
				$parent_term = get_term_by( (is_numeric($args['parent']) ? 'term_id' : 'slug'), $args['parent'], 'product_cat');
			}

			if(is_object($parent_term) && isset($parent_term->term_id) ) {
				$terms_args['parent'] = $parent_term->term_id;
			}
			else {
				$terms_args['parent'] = 0;
			}
		}

		$terms = \ReyCore\Helper::get_terms( $terms_args );
		// $terms = get_terms( $terms_args );

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
			$options = $args['extra_item'];
			foreach ( $terms as $term ) {

				$term_name = wp_specialchars_decode($term->name);

				if( $args['labels'] === true && isset($term->parent) && $term->parent === 0 ){
					$term_name = sprintf('%s (%s)', $term_name, esc_html__('Parent Category', 'rey-core'));
				}

				$parents_symbol = '';

				if( $args['hierarchical'] ){

					$ancestors = get_ancestors($term->term_id, $term->taxonomy);
					$ancestors = array_reverse($ancestors);

					foreach ($ancestors as $key => $anc) {
						$parents_symbol .= get_term( $anc )->name . ' > ';
					}

					$term_name = $parents_symbol . $term_name;
				}

				$field = $args['field'];
				if( isset($term->$field) ){
					$options[ $term->$field ] = $term_name;
				}

			}

			if( $args['hierarchical'] ){
				asort($options);
			}

			return $options;
		}

		return [];
	}
endif;

if(!function_exists('reycore_wc__product_tags')):
	/**
	 * WooCommerce Product Query
	 * @return array
	 */
	function reycore_wc__product_tags( $args = [] ){

		$args = wp_parse_args($args, [
			'taxonomy'   => 'product_tag',
			'hide_empty' => true,
		]);

		$terms = get_terms( $args );

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
			foreach ( $terms as $term ) {
				$options[ $term->slug ] = $term->name;
			}
			return $options;
		}
	}
endif;

if(!function_exists('reycore_wc__products')):
	/**
	 * WooCommerce Product Query
	 * @return array
	 */
	function reycore_wc__products(){

		$product_ids = get_posts( [
				'post_type' => 'product',
				'numberposts' => -1,
				'post_status' => 'publish',
				'fields' => 'ids',
		 ]);

		if ( ! empty( $product_ids ) ){
			foreach ( $product_ids as $product_id ) {
				$options[ $product_id ] = get_the_title($product_id);
			}
			return $options;
		}
		return [];
	}
endif;


if(!function_exists('reycore_wc__attributes_list')):
	/**
	 * WooCommerce Attribites
	 * @return array
	 * @deprecated 2.4.5
	 */
	function reycore_wc__attributes_list( $attributes ){
		return [];
	}

endif;

if(!function_exists('reycore_wc__get_all_attributes_terms')):
	function reycore_wc__get_all_attributes_terms(){

		$attrs = [];

		foreach( wc_get_attribute_taxonomies() as $attribute ) {

			$taxonomy = wc_attribute_taxonomy_name($attribute->attribute_name);

			$terms = get_terms([
				'taxonomy' => $taxonomy,
				'hide_empty' => true
			]);

			if( is_wp_error($terms) ){
				continue;
			}

			foreach ($terms as $key => $term) {
				if( isset($term->term_id) ){
					$attrs[ $term->term_id ] = sprintf('%s (%s)', $term->name, $attribute->attribute_label);
				}
			}

		}

		return $attrs;

	}
endif;

if(!function_exists('reycore_wc__get_product_taxonomies')):
	/**
	 * Get product taxononomies
	 *
	 * @since 2.5.4
	 **/
	function reycore_wc__get_product_taxonomies( $excludes = [] )
	{
		$taxes = [];

		foreach (get_object_taxonomies( 'product' ) as $key => $value) {
			if( in_array($value, ['product_type', 'product_visibility', 'product_shipping_class'], true) ){
				continue;
			}
			if( in_array($value, $excludes, true) ){
				continue;
			}
			$taxes[] = $value;
		}

		return $taxes;

	}
endif;

if(!function_exists('reycore_wc__get_attributes_list')):
	/**
	 * Get Attributes list for select
	 *
	 * @since 2.1.4
	 **/
	function reycore_wc__get_attributes_list( $wc_taxonomies = false ){

		$attrs = [];

		if( ! function_exists('wc_get_attribute_taxonomies') ){
			return $attrs;
		}

		foreach( wc_get_attribute_taxonomies() as $attribute ) {
			$attribute_name = $attribute->attribute_name;

			if( $wc_taxonomies ){
				$attribute_name = wc_attribute_taxonomy_name($attribute_name);
			}

			$attrs[$attribute_name] = $attribute->attribute_label;
		}

		return apply_filters('reycore/woocommerce/attributes_taxonomies', $attrs, $wc_taxonomies);
	}

endif;

if(!function_exists('reycore_wc__get_product')):
	function reycore_wc__get_product( $product_id = false ){

		if( $product_id ){
			$product = wc_get_product( $product_id );
		}
		else {

			global $product;

			if( ! $product ){
				$product = wc_get_product();
			}
		}

		if( ! $product ){
			return false;
		}

		return $product;

	}
endif;

if(!function_exists('reycore_wc__is_product')):
	/**
	 * Is product
	 *
	 * @since 2.1.0
	 **/
	function reycore_wc__is_product()
	{
		return apply_filters('reycore/woocommerce/is_product', is_product() || get_query_var('rey__is_quickview', false) );
	}
endif;

if(!function_exists('reycore_wc__add_notice')):
	/**
	 * Render WooCommerce notice
	 *
	 * @since 2.3.3
	 **/
	function reycore_wc__add_notice( $messages = [], $notice_type = 'success', $echo = false ) {

		$notices = [];

		foreach ( (array) $messages as $message) {
			$notices[] = [
				'notice' => $message,
				'data' => [],
			];
		}

		if( empty($notices) ){
			return;
		}

		ob_start();

		wc_get_template( "notices/{$notice_type}.php", [
			'notices'  => $notices,
		] );

		$notice = ob_get_clean();

		if( $echo ){
			echo $notice;
		}
		else {
			return $notice;
		}

	}
endif;


/**
 * Override Related products output
 * for lazy loading
 */
if ( ! function_exists( 'woocommerce_output_related_products' ) ) {
	function woocommerce_output_related_products() {
		\ReyCore\Plugin::instance()->woocommerce_tags[ 'related' ]->run();
	}
}

/**
 * Override Upsells products output
 * for lazy loading
 */
if ( ! function_exists( 'woocommerce_upsell_display' ) ) {
	/**
	 * Output product up sells.
	 *
	 * @param int    $limit (default: -1).
	 * @param int    $columns (default: 4).
	 * @param string $orderby Supported values - rand, title, ID, date, modified, menu_order, price.
	 * @param string $order Sort direction.
	 */
	function woocommerce_upsell_display( $limit = '-1', $columns = 4, $orderby = 'rand', $order = 'desc' ) {
		\ReyCore\Plugin::instance()->woocommerce_tags[ 'related' ]->run_upsells();
	}
}

if ( ! function_exists( 'woocommerce_widget_shopping_cart_subtotal' ) ) {
	/**
	 * Output to view cart subtotal.
	 *
	 * @since 3.7.0
	 */
	function woocommerce_widget_shopping_cart_subtotal() {

		printf('<div class="minicart-total-row minicart-total-row--subtotal"><div class="minicart-total-row-head">%s</div><div class="minicart-total-row-content">%s</div></div>', esc_html__( 'Subtotal:', 'woocommerce' ), WC()->cart->get_cart_subtotal());

	}
}

if(!function_exists('reycore_wc__get_gap_css_class')):
	/**
	 * Get CSS Class for different values.
	 *
	 * @since 2.4.0
	 **/
	function reycore_wc__get_gap_css_class( $custom = false )
	{
		$css_class = '';

		$gaps = array_flip([
			'no' => 0,
			'line' => 2,
			'narrow' => 10,
			'default' => 30,
			'extended' => 50,
			'wide' => 70,
			'wider' => 100,
		]);

		$gap_v2 = $custom ? $custom : get_theme_mod('loop_gap_size_v2');

		if( '' !== $gap_v2 && false !== $gap_v2 && isset($gaps[$gap_v2]) ){
			$css_class = $gaps[$gap_v2];
		}

		return $css_class ? 'rey-wcGap-' . esc_attr( $css_class ) : '';
	}
endif;


if(!function_exists('reycore_wc__get_attributes')):
/**
 * Attributes
 *
 * @since 2.5.0
 **/
function reycore_wc__get_attributes( $args = [] )
{
	$args = wp_parse_args($args, [
		'taxonomy'   => '',
		'product_id' => false,
		'product'    => false,
		'limit'      => 0,
		'link'       => false,
		'separator'  => ' ',
		'display'    => 'value',
	]);

	if( ! ( ($taxonomy = $args['taxonomy']) && taxonomy_exists($taxonomy) ) ){
		return '';
	}

	if( $args['product'] ){
		$product = $args['product'];
	}
	else {
		$product = wc_get_product(absint($args['product_id']));
	}

	if( ! $product ){
		return '';
	}

	$attributes = array_filter( $product->get_attributes(), 'wc_attributes_array_filter_visible' );

	if( ! isset($attributes[ $taxonomy ]) ){
		return '';
	}

	if( ! ( isset($attributes[ $taxonomy ]['options']) && ($term_ids = $attributes[ $taxonomy ]['options'] ) && !empty($term_ids) ) ){
		return '';
	}

	if( $limit = absint($args['limit']) ){
		$term_ids = array_slice($term_ids, 0, $limit);
	}

	$output = [];

	foreach ($term_ids as $i => $term_id)
	{

		if( ! $limit && $index = absint($args['index']) ){
			if( $index !== ($i+1) ){
				continue;
			}
		}

		$term_obj = get_term_by( 'term_taxonomy_id', $term_id, $taxonomy );

		if( ! isset($term_obj->name) ){
			continue;
		}

		$wrapper = '<span class="__term">%s</span>';

		if( $args['link'] ){
			if( ($term_link = get_term_link( $term_id, $taxonomy )) && is_string($term_link) ){
				$wrapper = "<a href='{$term_link}'>%s</a>";
			}
		}

		$output[] = sprintf($wrapper, $term_obj->name);
	}

	$value = implode($args['separator'], $output);

	if( in_array($args['display'], ['both', 'label'], true) ){

		$taxonomy_object = get_taxonomy( $taxonomy );
		$label = ! is_wp_error($taxonomy_object) ? $taxonomy_object->labels->name : '';

		if( 'both' === $args['display'] ){
			return sprintf('%s: %s', $label, $value);
		}
		else if( 'label' === $args['display'] ){
			return $label;
		}
	}

	return $value;
}
endif;


if(!function_exists('reycore_wc__get_hidden_product_ids')):
	/**
	 * Get a list of products IDs which are hidden from the catalog
	 *
	 * @return array
	 */
	function reycore_wc__get_hidden_product_ids(){

		if ( false === ($hidden_ids = get_transient('reycore_hidden_product_ids')) ) {

			global $wpdb;

			// Direct SQL to get hidden product IDs
			$hidden_ids = $wpdb->get_col("
				SELECT p.ID FROM $wpdb->posts p
				LEFT JOIN $wpdb->term_relationships tr ON (p.ID = tr.object_id)
				LEFT JOIN $wpdb->term_taxonomy tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
				LEFT JOIN $wpdb->terms t ON (tt.term_id = t.term_id)
				WHERE p.post_type = 'product'
				AND tt.taxonomy = 'product_visibility'
				AND t.name IN ('exclude-from-catalog', 'exclude-from-search');
			");

			set_transient('reycore_hidden_product_ids', $hidden_ids, WEEK_IN_SECONDS);
		}

		return array_map('absint', array_filter( array_unique( (array) $hidden_ids ) ) );
	}
endif;

if(!function_exists('reycore_wc__clear_hidden_product_ids')):
	/**
	 * Invalidate hidden products IDs transient
	 *
	 * @return void
	 */
	function reycore_wc__clear_hidden_product_ids( $product_id ) {
		if( 0 === absint($product_id) ){ // only on global delete
			delete_transient('reycore_hidden_product_ids');
		}
	}
endif;

add_action( 'woocommerce_delete_product_transients', 'reycore_wc__clear_hidden_product_ids');
add_action( 'woocommerce_update_product', 'reycore_wc__clear_hidden_product_ids');
