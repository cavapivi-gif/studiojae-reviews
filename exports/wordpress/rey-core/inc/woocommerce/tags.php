<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! function_exists( 'woocommerce_template_loop_product_title' ) ):
	/**
	 * Override native function, by adding link into H2 tag.
	 *
	 * Show the product title in the product loop. By default this is an H2.
	 *
	 * @since 1.0.0
	 */
	function woocommerce_template_loop_product_title() {
		global $product;

		printf(
			'<%4$s class="%1$s"><a href="%2$s">%3$s</a>%5$s</%4$s>',
			esc_attr( apply_filters( 'woocommerce_product_loop_title_classes', 'woocommerce-loop-product__title' ) ),
			esc_url( apply_filters( 'woocommerce_loop_product_link', get_the_permalink(), $product ) ),
			get_the_title(),
			esc_attr( apply_filters( 'woocommerce_product_loop_title_tag', 'h2', $product ) ),
			apply_filters( 'woocommerce_product_loop_title_content', '', $product )
		);
	}
endif;


if ( ! function_exists( 'woocommerce_template_loop_product_link_open' ) ) {
	/**
	 * Override native function, by adding aria-label attribute.
	 *
	 * Insert the opening anchor tag for products in the loop.
	 */
	function woocommerce_template_loop_product_link_open() {
		global $product;

		$link = apply_filters( 'woocommerce_loop_product_link', get_the_permalink(), $product );

		echo '<a href="' . esc_url( $link ) . '" class="woocommerce-LoopProduct-link woocommerce-loop-product__link" aria-label="' . esc_attr(get_the_title()) . '">';
	}
}


if(!function_exists('reycore_wc__add_account_btn')):
	/**
	 * Add account button and panel markup.
	 * "Default" header only.
	 * @since 1.0.0
	 **/
	function reycore_wc__add_account_btn(){
		if( get_theme_mod('header_enable_account', false) ) {
			reycore__get_template_part('template-parts/woocommerce/header-account');
			add_filter('reycore/woocommerce/account_panel/render', '__return_true');
		}
	}
endif;
add_action('rey/header/row', 'reycore_wc__add_account_btn', 50);


if(!function_exists('reycore_wc__get_account_panel_args')):
	/**
	 * Get account panel options
	 * @since 1.0.0
	 **/
	function reycore_wc__get_account_panel_args( $option = '' ){

		$options = apply_filters('rey/header/account_params', [
			'button_type' => get_theme_mod('header_account_type', 'text'),
			'button_text' => get_theme_mod('header_account_text', 'ACCOUNT'),
			'button_text_logged_in' => get_theme_mod('header_account_text_logged_in', ''),
			'icon_type' => get_theme_mod('header_account_icon_type', 'rey-icon-user'),
			'wishlist' =>  get_theme_mod('header_account_wishlist', true) && \ReyCore\WooCommerce\Tags\Wishlist::is_enabled(),
			'counter' => get_theme_mod('header_account_wishlist_counter', true) && \ReyCore\WooCommerce\Tags\Wishlist::is_enabled(),
			'wishlist_prod_layout' => 'grid',
			'show_separately' => true,
			'login_register_redirect' => get_theme_mod('header_account_redirect_type', 'load_menu'),
			'login_register_redirect_url' => get_theme_mod('header_account_redirect_url', ''),
			'ajax_forms' => get_theme_mod('header_account__enable_ajax', apply_filters('reycore/header/account/ajax_forms', true)),
			'forms' => get_theme_mod('header_account__enable_forms', true),
			'display' => get_theme_mod('header_account__panel_display', 'drop'),
			'drop_close_on_scroll' => get_theme_mod('header_account__close_on_scroll', false)
		] );

		if( !empty($option) && isset($options[$option]) ){
			return $options[$option];
		}

		return $options;
	}
endif;


if(!function_exists('reycore_wc__account_heading_tags')):
	/**
	 * Get account panel options
	 * @since 1.0.0
	 **/
	function reycore_wc__account_heading_tags( $tag = '' ){

		$tags = apply_filters('rey/header/account_heading_tags', [
			'wishlist'      => 'div',
			'login'         => 'div',
			'lost_password' => 'div',
			'register'      => 'div',
			'hello_title'   => 'div',
		] );

		if( !empty($tag) && isset($tags[$tag]) ){
			return $tags[$tag];
		}

		return $tags;
	}
endif;


if(!function_exists('reycore_wc__account_nav_wrap_start')):
	function reycore_wc__account_nav_wrap_start() {
		// no because the markup is needed
		// if( is_user_logged_in() ) return;
		?>
			<div class="woocommerce-MyAccount-navigation-wrapper --active" <?php reycore_wc__account_redirect_attrs() ?> data-account-tab="account">
		<?php
		add_action('woocommerce_after_account_navigation', 'reycore_wc__generic_wrapper_end', 20);
	}
	add_action('woocommerce_before_account_navigation', 'reycore_wc__account_nav_wrap_start');
endif;

if(!function_exists('reycore_wc__header_account_custom_items')):
	/**
	 * Add custom menu items
	 *
	 * @since 1.6.10
	 **/
	function reycore_wc__header_account_custom_items($items, $endpoints)
	{

		$logout_item = false;

		if( isset($items['customer-logout']) ){
			$logout_item = $items['customer-logout'];
			unset($items['customer-logout']);
		}

		if( $custom_menu_items = get_theme_mod('header_account_menu_items', []) ){
			foreach ( (array) $custom_menu_items as $item) {
				if( $item['text'] ){
					$items[ sanitize_title_with_dashes($item['text']) ] = $item['text'];
				}
			}
		}

		if( $logout_item ){
			$items = apply_filters('reycore/woocommerce/account_menu_items/before_logout', $items, $endpoints);
			$items['customer-logout'] = $logout_item;
		}

		return $items;
	}
	add_filter('woocommerce_account_menu_items', 'reycore_wc__header_account_custom_items', 10, 2);
endif;


if(!function_exists('reycore_wc__header_account_custom_items_endpoint')):
	/**
	 * Add custom menu items endpoint URLs
	 *
	 * @since 1.6.10
	 **/
	function reycore_wc__header_account_custom_items_endpoint($url, $endpoint)
	{

		if( ($custom_menu_items = get_theme_mod('header_account_menu_items', [])) && is_array($custom_menu_items)  ){
			foreach ($custom_menu_items as $item) {
				if( $item['text'] && $endpoint === sanitize_title_with_dashes($item['text']) ){
					$url = esc_url($item['url']);
				}
			}
		}

		return $url;
	}
	add_filter('woocommerce_get_endpoint_url', 'reycore_wc__header_account_custom_items_endpoint', 10, 2);
endif;

if(!function_exists('reycore_wc__header_account_items_labels')):
	/**
	 * Add menu items counters
	 *
	 * @since 2.8.2
	 **/
	function reycore_wc__header_account_items_labels($label, $endpoint)
	{
		$sup = '';

		if( $endpoint == 'orders' ){
			$sup = sprintf('<sup>%d</sup>', reycore_wc__count_orders(wp_get_current_user()->ID));
		}

		else if( $endpoint == 'downloads' && reycore_wc__check_downloads_endpoint() ){
			$sup = sprintf('<sup>%d</sup>', reycore_wc__count_downloads(wp_get_current_user()->ID));
		}

		return ! is_rtl() ? $label . $sup : $sup . $label;
	}
	add_filter('reycore/woocommerce/account-menu/link_label', 'reycore_wc__header_account_items_labels', 10, 2);
endif;


if(!function_exists('reycore_wc__account_redirect_attrs')):
/**
 * Redirect attributes for account panel containing login register forms
 *
 * @since 1.4.5
 **/
function reycore_wc__account_redirect_attrs( $args = [] )
{
	$args = wp_parse_args($args, reycore_wc__get_account_panel_args());

	$redirect_type = $args['login_register_redirect'];
	$redirect_url = $args['login_register_redirect_url'];

	if( $redirect_type === 'myaccount' ){

		$redirect_url = wc_get_page_permalink( 'myaccount' );

		if( is_user_logged_in() ){
			$redirect_url = apply_filters( 'woocommerce_login_redirect', $redirect_url, wp_get_current_user() );
		}

	}

	printf( 'data-redirect-type="%s" data-redirect-url="%s" %s',
		esc_attr($redirect_type),
		esc_attr($redirect_url),
		! $args['ajax_forms'] ? 'data-no-ajax' : ''
	);

}
endif;


if(!function_exists('reycore_wc__add_account_panel')):
	/**
	 * Add account button and panel markup
	 * @since 1.0.0
	 **/
	function reycore_wc__add_account_panel(){

		$args = reycore_wc__get_account_panel_args();

		if( ! apply_filters( 'reycore/woocommerce/account_panel/render', false ) ){
			return;
		}

		// assets
		reycore_assets()->add_styles([
			'rey-header-drop-panel',
			'rey-wc-header-account-panel-top',
			'rey-wc-header-account-panel',
			'rey-wc-header-wishlist',
		]);

		// if( is_user_logged_in() ){
		// 	reycore_assets()->add_styles('rey-wc-account-menu');
		// }

		reycore_assets()->add_scripts([
			'rey-drop-panel',
			'reycore-woocommerce',
			'reycore-wc-header-account-panel',
			'reycore-sidepanel',
			'reycore-wc-header-wishlist',
			'rey-tmpl',
		]);

		$wrapper_attributes = [
			'data-layout' => $args['display']
		];

		$wrapper_attributes['class'] = [
			'rey-accountPanel-wrapper',
			'--layout-' . $args['display'],
			'--invisible'
		];

		$inner_attributes['class'] = [
			'rey-accountPanel'
		];

		if( $args['display'] === 'drop' ){

			$wrapper_attributes['class'][] = 'rey-header-dropPanel';
			$wrapper_attributes['class'][] = '--manual';

			if( $args['drop_close_on_scroll'] ){
				$wrapper_attributes['data-close-scroll'] = '';
			}

			$inner_attributes['class'][] = 'rey-header-dropPanel-content';
			$inner_attributes['data-lazy-hidden'] = '';
			$inner_attributes['aria-modal'] = 'true';
			$inner_attributes['role'] = 'dialog';
			$inner_attributes['tabindex'] = '-1';

			reycore_assets()->add_styles('rey-header-drop-panel');
			reycore_assets()->add_scripts('rey-drop-panel');

		}
		else if( $args['display'] === 'offcanvas' ){
			$wrapper_attributes['class'][] = 'rey-sidePanel';
			reycore_assets()->add_styles('reycore-close-arrow');
		} ?>

		<div <?php echo reycore__implode_html_attributes($wrapper_attributes); ?>>
			<div <?php echo reycore__implode_html_attributes($inner_attributes); ?>>
				<?php do_action('reycore/woocommerce/account_panel'); ?>
			</div>
		</div><?php
	}
endif;
add_action('rey/after_site_wrapper', 'reycore_wc__add_account_panel');

if(!function_exists('reycore_wc__generic_wrapper_end')):
	/**
	 * Ending wrapper
	 *
	 * @since 1.0.0
	 **/
	function reycore_wc__generic_wrapper_end()
	{ ?>
		</div>
	<?php }
endif;


if( !function_exists('reycore_wc__checkout_required_span') ):

	/**
	 * Add the required mark to the terms & comditions text
	 * to maintain it on the same line visually.
	 *
	 * @since 1.0.0
	 **/
	function reycore_wc__checkout_required_span($text)
	{
		return $text . '<abbr class="required" title="required">*</abbr>';

	}
endif;
add_filter('woocommerce_get_terms_and_conditions_checkbox_text', 'reycore_wc__checkout_required_span');


if(!function_exists('reycore_wc__placeholder_img_src')):
	/**
	 * Placeholder
	 */
	function reycore_wc__placeholder_img_src( $placeholder ) {

		if( strpos($placeholder, 'woocommerce-placeholder.png') !== false ){
			return defined('REY_CORE_PLACEHOLDER') ? REY_CORE_PLACEHOLDER : $placeholder;
		}

		return $placeholder;
	}
endif;
add_filter('woocommerce_placeholder_img_src', 'reycore_wc__placeholder_img_src');
// add_filter( 'option_woocommerce_placeholder_image', 'reycore_wc__placeholder_img_src' );


if(!function_exists('reycore_wc__get_product_images_ids')):
	/**
	 * Get product's image ids
	 *
	 * @since 1.0.0
	 * @return array
	 **/
	function reycore_wc__get_product_images_ids( $add_main = true )
	{
		$product = wc_get_product();
		$ids = [];

		if( $product && $main_image_id = $product->get_image_id() ){

			if( $add_main ){
				// get main image' id
				$ids['main'] = absint($main_image_id);
			}

			// get gallery
			if( $gallery_image_ids = $product->get_gallery_image_ids() ){
				foreach ($gallery_image_ids as $key => $gallery_img_id) {
					$ids[] = $gallery_img_id;
				}
			}
		}

		return (array) apply_filters('reycore/woocommerce/product_image_gallery_ids', $ids);
	}
endif;


if(!function_exists('reycore_wc__add_mobile_nav_link')):
	/**
	 * Adds dashboard (my account) link into Mobile navigation's footer
	 * @since 1.0.0
	 */
	function reycore_wc__add_mobile_nav_link(){

		// is catalog mode and custom filter is enabled (which hides account forms)
		if( get_theme_mod('shop_catalog', false) && apply_filters('reycore/catalog_mode/hide_account', false) ){
			return;
		}

		reycore__get_template_part('template-parts/woocommerce/header-mobile-navigation-footer-link');
	}
endif;
add_action('rey/mobile_nav/footer', 'reycore_wc__add_mobile_nav_link', 5);


if(!function_exists('reycore_wc__exclude_products_from_cats')):
	/**
	 * Exclude categories from shop page query
	 *
	 * @since 1.2.0
	 **/
	function reycore_wc__exclude_products_from_cats( $q )
	{
		if( ! is_shop()){
			return;
		}

		if( ! ($exclude_cats = reycore__get_theme_mod('shop_catalog_page_exclude', [], [
				'translate' => true,
				'translate_post_type' => 'product_cat',
			])) ){
			return;
		}

		$tax_query = (array) $q->get( 'tax_query' );

		$tax_query[] = array(
			'taxonomy' => 'product_cat',
			'field' => isset($exclude_cats[0]) && is_numeric($exclude_cats[0]) ? 'term_id' : 'slug',
			'terms' => $exclude_cats,
			'operator' => 'NOT IN'
		);

		$q->set( 'tax_query', $tax_query );

	}
	add_action('woocommerce_product_query', 'reycore_wc__exclude_products_from_cats', 1001); // after filters
endif;

if(!function_exists('reycore_wc__exclude_cats')):
	/**
	 * Exclude categories
	 *
	 * @since 1.6.10
	 **/
	function reycore_wc__exclude_cats($args)
	{
		if( ! ($exclude_cats = reycore__get_theme_mod('shop_catalog_page_exclude', [], [
			'translate' => true,
			'translate_post_type' => 'product_cat',
		])) ){
			return $args;
		}

		$terms_ids = [];

		foreach ($exclude_cats as $term_slug) {
			$term = get_term_by('slug', $term_slug, 'product_cat');
			if( isset($term->term_id) ){
				$terms_ids[] = $term->term_id;
			}
		}

		if( !empty($terms_ids) ){
			$args['exclude'] = $terms_ids;
		}

		return $args;
	}
	add_filter('woocommerce_product_subcategories_args', 'reycore_wc__exclude_cats');
endif;




if(!function_exists('reycore_wc__format_price_range')):
	/**
	 * Remove dash from grouped products
	 *
	 * @since 1.0.0
	 */
	function reycore_wc__format_price_range( $price, $from, $to ) {

		$min = is_numeric( $from ) ? wc_price( $from ) : $from;
		$max = is_numeric( $to ) ? wc_price( $to ) : $to;

		/* translators: 1: price from 2: price to */
		$price = sprintf(
			esc_html_x( '%1$s %2$s', 'Price range: from-to', 'rey-core' ),
			$min,
			$max
		);

		if( $custom_price_range = get_theme_mod('custom_price_range', '') ){

			$custom_price_range = explode(' ', $custom_price_range);
			$custom_price_range = array_map(function($arr) use ($min, $max){
				$arr = str_replace('{{min}}', $min, $arr);
				$arr = str_replace('{{max}}', $max, $arr);
				return "<span class='__custom-price-range'>{$arr}</span>";
			}, $custom_price_range);

			return sprintf('<span>%s</span>', implode('', $custom_price_range));
		}


		return $price;
	}
endif;
add_filter('woocommerce_format_price_range', 'reycore_wc__format_price_range', 10, 3);


if( ! function_exists( 'reycore_wc__ajax_add_to_cart' ) ):

	function reycore_wc__ajax_add_to_cart() {

		$data = [];

		// Notices
		ob_start();
		wc_print_notices();
		$data['notices'] = ob_get_clean();

		if( ! did_action('woocommerce_ajax_added_to_cart') && isset($_REQUEST['add-to-cart']) ){
			do_action( 'woocommerce_ajax_added_to_cart', apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_REQUEST['add-to-cart'] ) ) );
		}

		ob_start();
		woocommerce_mini_cart();
		$wscc['div.widget_shopping_cart_content'] = sprintf('<div class="widget_shopping_cart_content">%s</div>', ob_get_clean());

		$data = [
			'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', $wscc ),
			'cart_hash' => WC()->cart->get_cart_hash(),
		];

		// enforce the `div.widget_shopping_cart_content` fragment
		$data['fragments'] = array_merge($data['fragments'], $wscc);

		$data = apply_filters('reycore/woocommerce/cart/data', $data);

		wp_send_json( $data );
		die();
	}
endif;
add_action( 'wc_ajax_reycore_ajax_add_to_cart', 'reycore_wc__ajax_add_to_cart' );


if(!function_exists('reycore__woocommerce_filter_js')):
	/**
	 * Filter WC JS
	 *
	 * @since 1.0.0
	 **/
	function reycore__woocommerce_filter_js($js)
	{
		$search_for = '.selectWoo( {';
		$replace_with = '.selectWoo( {';
		$replace_with .= 'containerCssClass: "select2-reyStyles",';
		$replace_with .= 'dropdownCssClass: "select2-reyStyles",';
		$replace_with .= 'dropdownAutoWidth: true,';
		$replace_with .= 'width: "auto",';

		return str_replace($search_for, $replace_with, $js);
	}
endif;
add_filter('woocommerce_queued_js', 'reycore__woocommerce_filter_js');


if(!function_exists('reycore_wc__related_change_cols')):
	/**
	 * Filter related products columns no.
	 *
	 * @since 1.5.0
	 **/
	function reycore_wc__related_change_cols( $args )
	{
		$args['posts_per_page'] = reycore_wc_get_columns('desktop');
		$args['columns'] = reycore_wc_get_columns('desktop');

		return $args;
	}
add_filter('woocommerce_output_related_products_args', 'reycore_wc__related_change_cols', 10);
endif;


if(!function_exists('reycore_wc__track_product_view')):
	/**
	 * Track product views.
	 */
	function reycore_wc__track_product_view() {

		$track = false;

		if ( is_singular( 'product' ) ) {
			$track = true;
		}

		if( get_query_var('rey__is_quickview', false) === true ){
			$track = true;
		}

		$track = apply_filters('reycore/woocommerce/track_product_view', $track);

		if ( ! $track ) {
			return;
		}

		global $post;

		if ( empty( $_COOKIE['woocommerce_recently_viewed'] ) ) { // @codingStandardsIgnoreLine.
			$viewed_products = array();
		} else {
			$viewed_products = wp_parse_id_list( (array) explode( '|', wp_unslash( $_COOKIE['woocommerce_recently_viewed'] ) ) ); // @codingStandardsIgnoreLine.
		}

		// Unset if already in viewed products list.
		$keys = array_flip( $viewed_products );

		if ( isset( $keys[ $post->ID ] ) ) {
			unset( $viewed_products[ $keys[ $post->ID ] ] );
		}

		$viewed_products[] = $post->ID;

		if ( count( $viewed_products ) > 15 ) {
			array_shift( $viewed_products );
		}

		// Store for session only.
		wc_setcookie( 'woocommerce_recently_viewed', implode( '|', $viewed_products ) );
	}
	add_action( 'template_redirect', 'reycore_wc__track_product_view', 20 );
	add_action( 'reycore/woocommerce/quickview/before_render', 'reycore_wc__track_product_view', 20 );
endif;


if(!function_exists('reycore_wc__fix_variable_sale_product_prices')):
	/**
	 * Fix variable products on sale price display
	 *
	 * @since 1.6.6
	 **/
	function reycore_wc__fix_variable_sale_product_prices($price, $product)
	{
		if( ! get_theme_mod('fix_variable_product_prices', false) ){
			return $price;
		}

		if( ! $product ){
			return $price;
		}

		if( ! $product->is_on_sale() ){
			return $price;
		}

		$show_from_in_price = false;

		// Regular Price
		$regular_prices = [
			$product->get_variation_regular_price( 'min', true ),
			$product->get_variation_regular_price( 'max', true )
		];

		$flexible_variables_prices = $regular_prices[0] !== $regular_prices[1];

		if( $show_from_in_price ){
			$regular_price = $flexible_variables_prices ? sprintf( '<span class="woocommerce-Price-from">%1$s</span> %2$s', esc_html__('From:', 'woocommerce'), wc_price( $regular_prices[0] ) ) : wc_price( $regular_prices[0] );
		}
		else {
			$regular_price = wc_price( $regular_prices[0] );
		}

		// Sale Price
		$prod_prices = [
			$product->get_variation_price( 'min', true ),
			$product->get_variation_price( 'max', true )
		];

		if( $show_from_in_price ){
			$prod_price = $prod_prices[0] !== $prod_prices[1] ? sprintf( '<span class="woocommerce-Price-from">%1$s</span> %2$s', esc_html__('From:', 'woocommerce'), wc_price( $prod_prices[0] ) ) : wc_price( $prod_prices[0] );
		}
		else {
			$prod_price = wc_price( $prod_prices[0] );
		}

		if ( $prod_price !== $regular_price ) {
			$prod_price = sprintf('<del>%s</del> <ins>%s</ins>', $regular_price . $product->get_price_suffix(), $prod_price . $product->get_price_suffix() );
		}

		return $prod_price;
	}
	add_filter( 'woocommerce_variable_price_html', 'reycore_wc__fix_variable_sale_product_prices', 10, 2 );
endif;


if(!function_exists('reycore_wc__archive_title_back_button')):
	/**
	 * Add back button to archive titles
	 *
	 * @since 1.6.13
	 **/
	function reycore_wc__archive_title_back_button( $title )
	{

		if( ! get_theme_mod('archive__title_back', false) ){
			return $title;
		}

		if( is_shop() ){
			return $title;
		}

		$id = wc_get_page_id( 'shop' );
		$url = '';
		$behaviour = get_theme_mod('archive__back_behaviour', 'parent');
		$shop_url = get_permalink(wc_get_page_id( 'shop' ));
		$prev_url = 'javascript:window.history.back();';

		if ( is_search() ) {

			$url = $shop_url;

			if( 'page' === $behaviour ){
				$url = $prev_url;
			}
		}

		elseif ( is_tax() ) {

			if( 'parent' === $behaviour ){
				if( ($this_term = get_term(get_queried_object_id())) && isset($this_term->parent) && $this_term->parent !== 0 ){
					$url = get_term_link($this_term->parent);
				}
			}
			elseif( 'shop' === $behaviour ){
				$url = $shop_url;
			}
			elseif( 'page' === $behaviour ){
				$url = $prev_url;
			}
		}

		if( ! $url ){
			return $title;
		}

		$btn = sprintf('<a href="%2$s" class="rey-titleBack">%1$s</a>', reycore__arrowSvg(false), $url);

		return "{$btn}<span>{$title}</span>";
	}
	add_filter('woocommerce_page_title', 'reycore_wc__archive_title_back_button');
endif;


if(!function_exists('reycore__disable_wc_redirect_after_add')):
	/**
	 * Disable "Redirect to the cart page after successful addition" woocommerce option
	 * if Rey's after add to cart option is not disabled.
	 *
	 * @since 1.8.1
	 */
	function reycore__disable_wc_redirect_after_add( $wp_customizer )
	{
		if( get_theme_mod('product_page_after_add_to_cart_behviour', 'cart') !== '' ){
			delete_option('woocommerce_cart_redirect_after_add');
		}
	}
endif;
add_action('customize_save_after', 'reycore__disable_wc_redirect_after_add', 20);


function recore_wc__noscript() {
	?>
	<noscript><style>
		.woocommerce ul.products li.product.is-animated-entry {opacity: 1;transform: none;}
		.woocommerce div.product .woocommerce-product-gallery:after {display: none;}
		.woocommerce div.product .woocommerce-product-gallery .woocommerce-product-gallery__wrapper {opacity: 1}
	</style></noscript>
	<?php
}
add_action( 'wp_head', 'recore_wc__noscript' );

if(!function_exists('reycore_wc__check_if_hide_prices_visitors')):
	/**
	 * Check if "Hide prices for logged out visitors" is enabled
	 *
	 * @since 2.8.7
	 **/
	function reycore_wc__check_if_hide_prices_visitors()
	{
		return get_theme_mod('shop_hide_prices_logged_out', false) && ! is_user_logged_in();
	}
endif;

/**
 * Hide discount label if "hide prices for logged out visitors" is enabled
 *
 * @since 1.9.7
 */
add_filter('theme_mod_loop_show_sale_label', function($status){

	if( reycore_wc__check_if_hide_prices_visitors() ){
		return false;
	}

	return $status;

}, 20);

/**
 * Hide ATC button if "hide prices for logged out visitors" is enabled
 *
 * @since 2.8.6
 */
add_action('woocommerce_single_variation', function(){

	if( reycore_wc__check_if_hide_prices_visitors() ){
		remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20 );
	}

}, 10);

/**
 * Hide prices if "hide prices for logged out visitors" is enabled
 *
 * @since 1.9.7
 */
add_filter( 'woocommerce_get_price_html', function($html, $product){

	if( reycore_wc__check_if_hide_prices_visitors() ){

		if( ($custom_text = get_theme_mod('shop_hide_prices_logged_out_text', '')) ){
			if( ($my_account_page = wc_get_page_permalink( 'myaccount' )) && apply_filters('reycore/woocommerce/hide_prices/account_link', true) ){
				return sprintf('<a href="%s"><span class="woocommerce-Price-amount">%s</span></a>', $my_account_page, $custom_text);
			}
			else {
				return sprintf('<span class="woocommerce-Price-amount">%s</span>', $custom_text);
			}
		}

		return '';
	}

	return $html;
}, 100, 2);

add_filter( 'theme_mod_loop_show_prices', function($status){

	if( ! empty($_REQUEST['wp_customize']) || ! empty($_REQUEST['customize_changeset_uuid']) ){
		return $status;
	}

	if( is_admin() ){
		return $status;
	}

	if( 2 == $status && get_theme_mod('shop_hide_prices_logged_out', false) && is_user_logged_in() ){
		return '1';
	}

	return $status;
});

add_filter( 'woocommerce_is_purchasable', function($status){

	if( reycore_wc__check_if_hide_prices_visitors() ){
		return false;
	}

	return $status;
});

/**
 * Remove price from structured data if "hide prices for logged out visitors" is enabled
 */
add_filter( 'woocommerce_structured_data_product', function ( $markup, $product )
{
    if ( reycore_wc__check_if_hide_prices_visitors() && ! empty( $markup['offers'] ) ) {
		foreach ( (array) $markup['offers'] as $key => $offer) {
			unset( $markup['offers'][$key]['price'] );
			unset( $markup['offers'][$key]['priceSpecification']['price'] );
			unset( $markup['offers'][$key]['priceCurrency'] );
		}
    }
    return $markup;
}, 10, 2 );



/**
 * Disable variations that are out of stock.
 * @since 2.0.5
 */
add_filter( 'woocommerce_variation_is_active', function( $is_active, $variation ) {

	if( ! get_theme_mod('single_product_hide_out_of_stock_variation', true) ){
		return $is_active;
	}

	if ( ! $variation->is_in_stock() && ! $variation->backorders_allowed() ) {
		return false;
	}

	if( $variation->managing_stock() && 0 === $variation->get_stock_quantity() && ! $variation->backorders_allowed() ){
		return false;
	}

	return $is_active;
}, 10, 2 );


if(!function_exists('reycore_wc__get_default_variation')):

	function reycore_wc__get_default_variation( $product ){

		if( ! $product->is_type('variable') ){
			return;
		}

		$default_attributes = $product->get_default_attributes();

		if( empty( $default_attributes ) ){
			return;
		}

		if( ! ($available_variations = $product->get_available_variations()) ){
			return;
		}

		// make sure all attributes are selected as default
		if( count($available_variations[0]['attributes']) !== count($default_attributes) ){
			return;
		}

		$variation_id = false;

		foreach($available_variations as $variation_values ){

			if( $variation_id ){
				continue;
			}

			foreach($variation_values['attributes'] as $key => $attribute_value ){

				if( $variation_id ){
					continue;
				}

				$attribute_name = str_replace( 'attribute_', '', $key );

				if( isset($default_attributes[$attribute_name]) && $default_attributes[$attribute_name] === $attribute_value ){
					$variation_id = $variation_values['variation_id'];
				}
			}
		}

		return $variation_id;
	}
endif;


add_filter('woocommerce_before_output_product_categories', function($content){

	if( ! (($display_type = woocommerce_get_loop_display_mode()) && 'both' === $display_type) ){
		return $content;
	}

	if( ! get_theme_mod('shop_display_categories__enable', false) ){
		return $content;
	}

	$classes = ['product-category', 'product', 'rey-categories-loop', '--before'];

	if( get_theme_mod('loop_animate_in', true) ){
		reycore_assets()->add_scripts('rey-animate-items');
		$classes['animated-entry'] = 'is-animated-entry';
	}

	return $content . sprintf(
		'<li class="%2$s"><%1$s>%3$s</%1$s></li>',
		apply_filters('reycore/woocommerce/loop/shop_display_categories/tag', 'h2'),
		implode(' ', $classes),
		get_theme_mod('shop_display_categories__title_cat', esc_html__('Shop by Category', 'rey-core'))
	);
});

add_filter('woocommerce_after_output_product_categories', function($content){

	if( ! (($display_type = woocommerce_get_loop_display_mode()) && 'both' === $display_type) ){
		return $content;
	}

	if( ! get_theme_mod('shop_display_categories__enable', false) ){
		return $content;
	}

	$classes = ['product-category', 'product', 'rey-categories-loop', '--after'];

	if( get_theme_mod('loop_animate_in', true) ){
		reycore_assets()->add_scripts('rey-animate-items');
		$classes['animated-entry'] = 'is-animated-entry';
	}

	$title = esc_html__('Products', 'rey-core');

	if( is_product_category() || is_product_tag() || is_product_taxonomy() ){
		$title = reycore__get_page_title();
	}

	$text = get_theme_mod('shop_display_categories__title_prod', esc_html__('Shop All %s', 'rey-core'));

	$the_title = strpos($text, '%s') !== false ? sprintf($text, $title) : $text;

	return $content . sprintf(
		'<li class="%2$s"><%1$s>%3$s</%1$s></li>',
		apply_filters('reycore/woocommerce/loop/shop_display_categories/tag', 'h2'),
		implode(' ', $classes),
		$the_title
	);
});


if(!function_exists('reycore_wc__override_stars_markup')):
	/**
	 * Override the stars markup
	 *
	 * @since 2.1.1.1
	 **/
	function reycore_wc__override_stars_markup($html, $rating, $count)
	{
		$html = '';

		$icon = reycore__get_svg_icon(['id'=>'star']);

		$html .= '<i class="rey-starsGroup">';

		for( $i = 0; $i < 5; $i++ ){
			$html .= $icon;
		}

		$html .= '</i>';

		$html .= '<span style="--rating-width:' . ( ( $rating / 5 ) * 100 ) . '%">';


		$html .= '<i class="rey-starsGroup">';

		for( $i = 0; $i < 5; $i++ ){
			$html .= $icon;
		}

		$html .= '</i>';

		if ( 0 < $count ) {
			/* translators: 1: rating 2: rating count */
			$html .= sprintf( _n( 'Rated %1$s out of 5 based on %2$s customer rating', 'Rated %1$s out of 5 based on %2$s customer ratings', $count, 'woocommerce' ), '<strong class="rating">' . esc_html( $rating ) . '</strong>', '<span class="rating">' . esc_html( $count ) . '</span>' );
		}
		else {
			/* translators: %s: rating */
			$html .= sprintf( esc_html__( 'Rated %s out of 5', 'woocommerce' ), '<strong class="rating">' . esc_html( $rating ) . '</strong>' );
		}

		$html .= '</span>';

		reycore_assets()->add_styles('rey-wc-star-rating');

		return $html;

	}
	add_filter('woocommerce_get_star_rating_html', 'reycore_wc__override_stars_markup', 999, 3);
endif;


if(!function_exists('reycore__woocommerce_breadcrumb')):
	/**
	 * Woo. Breadcrumbs Wrapper.
	 * Offers the posibility of overriding the output of the breadcrumbs.
	 *
	 * @since 3.0.1
	 */
	function reycore__woocommerce_breadcrumb()
	{
		ob_start();

		/**
		 * Override Breadcrumbs by hooking into `rey/breadcrumbs/override`.
		 * This will shortcircuit  the code and display whatever is hooked.
		 */
		do_action('rey/breadcrumbs/override');

		$override = ob_get_clean();

		if( $override ){
			echo $override;
			return;
		}

		woocommerce_breadcrumb();
	}
endif;

if(!function_exists('reycore__breadcrumbs_nav_tag')):
	/**
	 * Woo. Breadcrumbs Nav Tag
	 *
	 * @since 2.6.1
	 */
	function reycore__breadcrumbs_nav_tag()
	{

		$classes = 'rey-breadcrumbs';

		if( is_product() && 'special' === get_theme_mod('single_breadcrumbs_style', 'special')){
			$classes .= ' --pdp-style';
		}

		return sprintf('<nav class="%s">', esc_attr($classes));
	}
endif;


if(!function_exists('reycore__breadcrumbs_args')):
	/**
	 * Filter WooCommerce Breadcrumbs defaults
	 *
	 * @since 1.0.0
	 */
	function reycore__breadcrumbs_args($args)
	{

		$args['delimiter']   = '<span class="rey-breadcrumbs-del">&#8250;</span>';
		$args['wrap_before'] = reycore__breadcrumbs_nav_tag();
		$args['wrap_after']  = '</nav>';
		$args['before']  = '<div class="rey-breadcrumbs-item">';
		$args['after']  = '</div>';

		return $args;
	}
endif;
add_filter('woocommerce_breadcrumb_defaults', 'reycore__breadcrumbs_args', 5);

/**
 * Force load breadcrumbs stylesheet
 * @since 2.6.0
 */
add_action('woocommerce_breadcrumb', function(){

	reycore_assets()->add_styles('reycore-breadcrumbs');

}, 10);

if(!function_exists('reycore__breadcrumbs_home_url')):
	/**
	 * Filter WooCommerce Breadcrumbs home url
	 *
	 * @since 2.4.0
	 */
	function reycore__breadcrumbs_home_url($url){

		if( ! is_shop() && (is_singular( 'product' ) || is_product_taxonomy() || is_post_type_archive( 'product' )) ){
			return get_permalink(wc_get_page_id( 'shop' ));
		}

		return $url;
	}
endif;
add_filter('woocommerce_breadcrumb_home_url', 'reycore__breadcrumbs_home_url');


if(!function_exists('reycore__breadcrumbs_main_term')):
	/**
	 * Filter WooCommerce Breadcrumbs main term selection
	 *
	 * @since 2.9.0
	 */
	function reycore__breadcrumbs_main_term($term, $terms){

		if( count($terms) > 2 && $terms[1]->parent !== 0 ){
			$term = $terms[1];
		}

		return $term;
	}
endif;
// add_filter('woocommerce_breadcrumb_main_term', 'reycore__breadcrumbs_main_term', 10, 2);


if(!function_exists('reycore_wc__minicart_product_title_tag')):
/**
 * Specify mini cart products tag
 *
 * @since 2.7.1
 **/
function reycore_wc__minicart_product_title_tag()
{
	return apply_filters('reycore/woocommerce/minicart/product_title_tag', 'h4');
}
endif;

/*
function reycore_wc__hide_colspanned_surplussed_products($visibility){

	if( $colspans = absint($GLOBALS['wp_query']->get('colspans')) ){
		if( (absint($GLOBALS['wp_query']->get('posts_per_page')) - $colspans) < ($GLOBALS['wp_query']->current_post + 1) ){
			return false;
		}
	}

	return $visibility;
}

add_action('reycore/woocommerce/content_product/before', function(){
	add_filter('reycore/woocommerce/content_product/render', 'reycore_wc__hide_colspanned_surplussed_products');
}, 10);

add_action('reycore/woocommerce/content_product/after', function(){
	remove_filter('reycore/woocommerce/content_product/render', 'reycore_wc__hide_colspanned_surplussed_products');
}, 10);
*/


if(!function_exists('reycore_wc__setup_carousel__grid_attributes')):
	/**
	 * Carousel Grid attributes
	 *
	 * @since 2.8.4
	 **/
	function reycore_wc__setup_carousel__grid_attributes( $attributes, $settings ){

		if( isset($settings['_skin']) && $settings === 'carousel' ){
			return $attributes;
		}

		$mobile_cols = \ReyCore\WooCommerce\Loop::is_mobile_list_view() ? 1 : 2;

		$attributes['data-cols'] = 3;
		$attributes['data-cols-tablet'] = 3;
		$attributes['data-cols-mobile'] = $mobile_cols;

		$params = [
			'autoplay'            => true,
			'interval'            => 6000,
			'auto_width'          => true,
			'desktop_only_arrows' => true,
			'rewind'              => true,
			'per_page'            => $attributes['data-cols'],
			'per_page_tablet'     => $attributes['data-cols-tablet'],
			'per_page_mobile'     => $mobile_cols,
		];

		$params = apply_filters('reycore/woocommerce/product_carousel/grid_config', $params);

		$attributes['data-prod-carousel-config'] = wp_json_encode($params);

		return $attributes;
	}
endif;


if(!function_exists('reycore_wc__setup_carousel__grid_classes')):
	/**
	 * Before grid carousel
	 *
	 * @since 2.8.4
	 **/
	function reycore_wc__setup_carousel__grid_classes($classes){
		return array_merge($classes, [
			'splide__list',
			'--prevent-metro',
			'--prevent-thumbnail-sliders', // make sure it does not have thumbnail slideshow
			'--prevent-scattered', // make sure scattered is not applied
			'--prevent-masonry', // make sure masonry is not applied
		]);
	}
endif;


if(!function_exists('reycore_wc__setup_carousel__product_classes')):
	/**
	 * Before grid carousel
	 *
	 * @since 2.8.4
	 **/
	function reycore_wc__setup_carousel__product_classes($classes){

		$classes['carousel_item'] = 'splide__slide';
		unset($classes['animated-entry']);

		return $classes;
	}
endif;


if(!function_exists('reycore_wc__setup_carousel__before')):
	/**
	 * Before grid carousel
	 *
	 * @since 2.8.4
	 **/
	function reycore_wc__setup_carousel__before(){

		wc_set_loop_prop( 'is_paginated', false );

		reycore_assets()->add_scripts(['splidejs', 'rey-splide', 'reycore-wc-product-grid-carousels']);
		reycore_assets()->add_styles(['rey-splide']);

		add_filter( 'woocommerce_post_class', 'reycore_wc__setup_carousel__product_classes', 30 );
		add_filter( 'reycore/woocommerce/product_loop_attributes/v2', 'reycore_wc__setup_carousel__grid_attributes', 10, 2);
		add_filter( 'reycore/woocommerce/product_loop_classes', 'reycore_wc__setup_carousel__grid_classes');


		add_filter( 'reycore/woocommerce/loop_components/disable_grid_components', '__return_true');
		add_filter( 'reycore/woocommerce/loop/render/thumbnails_slideshow', '__return_false', 100);
		add_filter( 'reycore/woocommerce/catalog/before_after/enable', '__return_false');
		add_filter( 'reycore/woocommerce/catalog/stretch_product/enable', '__return_false');

		// add carousel wrapper
		printf('<div class="splide __product-carousel" data-skin="%s"><div class="splide__track">', esc_attr(get_theme_mod('loop_skin', 'basic')));

	}
endif;

if(!function_exists('reycore_wc__setup_carousel__after')):
	/**
	 * After grid carousel
	 *
	 * @since 2.8.4
	 **/
	function reycore_wc__setup_carousel__after(){

		remove_filter( 'woocommerce_post_class', 'reycore_wc__setup_carousel__product_classes', 30 );
		remove_filter( 'reycore/woocommerce/product_loop_attributes/v2', 'reycore_wc__setup_carousel__grid_attributes', 10, 2);
		remove_filter( 'reycore/woocommerce/product_loop_classes', 'reycore_wc__setup_carousel__grid_classes');
		remove_filter( 'reycore/woocommerce/loop_components/disable_grid_components', '__return_true');
		remove_filter( 'reycore/woocommerce/loop/render/thumbnails_slideshow', '__return_false', 100);
		remove_filter( 'reycore/woocommerce/catalog/before_after/enable', '__return_false');
		remove_filter( 'reycore/woocommerce/catalog/stretch_product/enable', '__return_false');

		echo '</div></div>';
	}
endif;
