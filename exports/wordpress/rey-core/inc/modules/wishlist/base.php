<?php
namespace ReyCore\Modules\Wishlist;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	public static $is_enabled = false;
	public static $page_id = 0;
	public static $show_in_catalog_mode = true;

	public $load_markup = false;
	private $__button_attributes;

	/**
	 * Holds product ids stored in cookie
	 */
	const COOKIE_KEY = 'rey_wishlist_ids';

	/**
	 * Holds user unique key
	 */
	const USER_KEY = 'rey_wishlist_u';

	const ASSET_HANDLE = 'reycore-wishlist';

	public function __construct()
	{

		if( class_exists('TInvWL_Public_AddToWishlist') ){
			return;
		}

		add_filter( 'reycore/woocommerce/wishlist/tag/enabled', '__return_true');
		add_action( 'reycore/woocommerce/init', [$this, 'init']);
		add_action( 'reycore/customizer/panel=woocommerce', [$this, 'load_customizer_options']);
		add_action( 'reycore/ajax/register_actions', [ $this, 'register_actions' ] );
		add_action( 'reycore/templates/register_widgets', [$this, 'register_widgets']);
		add_action( 'reycore/woocommerce/loop/init', [$this, 'register_components']);
		add_action( 'customize_save_wishlist__default_url', [$this, 'flush_rewrite_rules']);
		add_action( 'elementor/element/reycore-product-grid/section_layout_components/before_section_end', [ $this, 'elementor__add_pg_control' ], 30 );
		add_action( 'elementor/element/reycore-woo-loop-products/section_layout_components/before_section_end', [ $this, 'elementor__add_pg_control' ], 30 );
		add_action( 'reycore/dynamic_tags', [$this, 'dynamic_tag']);
	}

	function init(){

		if( ! (self::$is_enabled = $this->is_enabled()) ){
			return;
		}

		add_filter( 'reycore/woocommerce/wishlist/ids', [$this, 'get_wishlist_ids']);
		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
		add_filter( 'reycore/woocommerce/wishlist/button_html', [$this, 'catalog_button_html']);
		add_filter( 'reycore/woocommerce/wishlist/url', [$this, 'wishlist_url']);
		add_filter( 'reycore/delay_js', [$this, 'exclude_delay_js'], 20);

		if( reycore_wc__is_catalog() && ! apply_filters('reycore/woocommerce/wishlist/catalog_mode', true ) ){
			return;
		}

		add_action( 'reycore/elementor/product_grid/lazy_load_assets', [$this, 'lazy_load_markup']);

		add_filter( 'rey/site_content_classes', [$this, 'add_loading'], 10);

		add_action( 'rey/before_site_container', [$this, 'apply_filter_content']);
		add_action( 'rey/after_site_container', [$this, 'remove_filter_content']);

		add_filter( 'body_class', [$this, 'append_wishlist_page_class']);
		add_filter( 'rey/main_script_params', [$this, 'script_params']);

		add_filter( 'reycore/woocommerce/wishlist/counter_html', [$this, 'wishlist_counter_html']);
		add_filter( 'reycore/woocommerce/wishlist/title', [$this, 'wishlist_title']);

		add_filter( 'reycore/woocommerce/account_menu_items/before_logout', [$this, 'add_wishlist_page_to_account_menu']);
		add_filter( 'woocommerce_get_endpoint_url', [$this, 'add_wishlist_url_endpoint'], 20, 4);

		add_action( 'woocommerce_before_single_product', [$this, 'pdp_button']);
		add_action( 'reycore/module/quickview/product', [$this, 'pdp_button']);

		add_action( 'wp_login', [$this, 'update_ids_after_login'], 10, 2);

		add_action( 'reycore/woocommerce/wishlist/render_products', [$this, 'render_products']);

		add_shortcode('rey_wishlist', [$this, 'wishlist_page_output']);
		add_shortcode('rey_wishlist_page', [$this, 'wishlist_shortcode_page_output']);
		add_shortcode('rey_wishlist_products', [$this, 'products_shortcode']);
		add_shortcode('rey_wishlist_catalog_button', [$this, 'shortcode_catalog_button_html']);

		add_filter( 'reycore/woocommerce/recent/item', [$this, 'add_to_fragments'], 10, 2);
		add_action( 'reycore/woocommerce/cart/cart_recent/after', [$this, 'render_in_products'], 15);

		add_filter( 'reycore/woocommerce/cross_sells/item', [$this, 'add_to_fragments'], 10, 2);
		add_action( 'reycore/woocommerce/cart/crosssells/after', [$this, 'render_in_products'], 15);

		add_action( 'woocommerce_before_mini_cart', [$this, 'add_cart_assets']);

		add_action( 'wp_footer', [$this, 'after_add_markup']);

		new CompatStickyAtc();
		new ElementorProductsGrid();
		new MostWishlisted();

	}

	public function load_customizer_options( $base ){
		$base->register_section( new Customizer() );
	}

	public function register_widgets($widgets_manager){
		$widgets_manager->register( new Element );
	}

	public function register_components( $base ){

		$base->register_component( new CompBottom );
		$base->register_component( new CompBottomRight );
		$base->register_component( new CompTopRight );

	}

	public function dynamic_tag( $tags ){
		$tags->get_manager()->register( new DynamicTag() );
	}

	public function add_assets(){

		static $added;

		if( ! $added ){

			reycore_assets()->add_scripts([self::ASSET_HANDLE]);
			reycore_assets()->add_styles([self::ASSET_HANDLE, 'reycore-tooltips']);

			$added = true;
		}

		$this->load_markup = true;

	}

	public function register_assets($assets){

		$assets->register_asset('styles', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/style.css',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
				'priority'  => 'low',
			]
		]);

		$assets->register_asset('styles', [
			self::ASSET_HANDLE . '-page' => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/style-page.css',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			]
		]);

		$assets->register_asset('scripts', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/script.js',
				'deps'    => ['reycore-woocommerce', 'wc-js-cookie', 'reycore-tooltips'],
				'version'   => REY_CORE_VERSION,
			]
		]);

	}

	public static function get_cookie_key(){
		return self::COOKIE_KEY . '_' . (is_multisite() ? get_current_blog_id() : 0);
	}

	public function script_params($params){

		$params['wishlist_after_add'] = get_theme_mod('wishlist__after_add', 'notice');
		$params['wishlist_text_add'] = self::get_texts('wishlist__texts_add');
		$params['wishlist_text_rm'] = self::get_texts('wishlist__texts_rm');
		$params['wishlist_expire'] = false;
		$params['wishlist_get_results'] = is_user_logged_in();

		if( is_user_logged_in() ){
			$params['wishlist_umeta_counter'] = self::get_ids();
		}

		return $params;
	}

	public static function get_texts( $text = '' ){

		$defaults = [
			'wishlist__text' => _x('Wishlist', 'Title', 'rey-core'),
			'wishlist__texts_add' => esc_html__('Add to wishlist', 'rey-core'),
			'wishlist__texts_rm' => esc_html__('Remove from wishlist', 'rey-core'),
			'wishlist__texts_added' => esc_html__('Added to wishlist!', 'rey-core'),
			'wishlist__texts_btn' => esc_html__('VIEW WISHLIST', 'rey-core'),
			'wishlist__texts_page_title' => __('Wishlist is empty.', 'rey-core'),
			'wishlist__texts_page_text' => __('You don\'t have any products added in your wishlist. Search and save items to your liking!', 'rey-core'),
			'wishlist__texts_page_btn_text' => __('SHOP NOW', 'rey-core'),
		];

		if( !empty($text) ){

			$opt = get_theme_mod($text, $defaults[$text]);

			if( empty($opt) ){
				$opt = $defaults[$text];
			}

			return $opt;
		}

		return '';

	}

	public static function get_cookie_products_ids(){
		$products = [];

		if ( ! empty( $_COOKIE[self::get_cookie_key()] ) ) { // @codingStandardsIgnoreLine.
			$products = wp_parse_id_list( (array) explode( '|', wp_unslash( $_COOKIE[self::get_cookie_key()] ) ) ); // @codingStandardsIgnoreLine.
		}

		return $products;
	}

	/**
	 * Get the unique wishlist ID associated with user
	 *
	 * @return string
	 */
	public static function get_shared_id(){

		if( ! isset($_REQUEST['wid']) ){
			return;
		}

		if( strlen($_REQUEST['wid']) !== 12 ){
			return;
		}

		return reycore__clean($_REQUEST['wid']);

	}

	/**
	 * Retrieve the wishlist product ids
	 *
	 * @return array
	 */
	public static function get_ids(){

		static $products;

		if( is_null($products) ){

			$products = [];

			if( is_user_logged_in() ){
				$user = wp_get_current_user();
				$products = get_user_meta($user->ID, self::get_cookie_key(), true);
			}
			else {
				$products = self::get_cookie_products_ids();
			}

			$products = array_filter((array) apply_filters('reycore/translate_ids', $products, 'product'));
		}

		return $products;
	}

	public function catalog_button_html( $btn_html ){

		if( ! self::$is_enabled ){
			return $btn_html;
		}

		return $this->render_catalog_button();
	}

	public function get_button_attributes(){

		if( ! is_null($this->__button_attributes) ){
			return $this->__button_attributes;
		}

		$button_class = [];

		$position = reycore_wc__get_setting('loop_wishlist_position');

		$button_text = self::get_texts('wishlist__texts_add');

		if( get_theme_mod('wishlist_loop__mobile', false)){

			// place it above the thumbnail
			$button_class['mobile'] = '--show-mobile--top';

			// if ATC. button is enabled, leave it in item's footer
			if( get_theme_mod('loop_add_to_cart_mobile', false) || $position === 'topright' ){
				$button_class['mobile'] = '--show-mobile';
			}

		}

		if( is_user_logged_in() ){
			$button_class['supports_ajax'] = '--supports-ajax';
		}

		// style icon only when on icon over thumbnail
		if(
			in_array($position, ['topright', 'bottomright'], true) &&
			$icon_style = reycore_wc__get_setting('wishlist_loop__icon_style')
		){
			$button_class['icon_style'] = '--icon-style-' . $icon_style;
		}

		$attributes = [];

		if( get_theme_mod('wishlist_loop__tooltip', false) ){
			$attributes['data-rey-tooltip'] = $button_text;
		}

		if( get_theme_mod('wishlist_loop__diff', true) ){
			// $button_class['diff'] = '--diff';
		}

		return $this->__button_attributes = [
			'class'      => $button_class,
			'text'       => $button_text,
			'content'    => apply_filters('reycore/woocommerce/wishlist/catalog_btn_content', $this->get_wishlist_icon(), $button_class, $this),
			'attributes' => reycore__implode_html_attributes($attributes),
		];

	}

	private function render_catalog_button( $product_id = 0 ){

		$product = wc_get_product($product_id);

		if( ! $product ){
			global $product;
		}

		if ( ! ($product && $id = $product->get_id()) ) {
			return;
		}

		$this->add_assets();

		$button_attributes = $this->get_button_attributes();

		static $active_products;
		if( is_null($active_products) ){
			$active_products = self::get_ids();
		}

		if( in_array($id, $active_products, true) ){
			$button_attributes['class']['is_active'] = '--in-wishlist';
			$button_attributes['text'] = self::get_texts('wishlist__texts_rm');
		}

		return sprintf(
			'<button class="%1$s rey-wishlistBtn rey-wishlistBtn-link" data-lazy-hidden data-id="%2$s" title="%3$s" aria-label="%3$s" %5$s>%4$s</button>',
			esc_attr(implode(' ', $button_attributes['class'])),
			esc_attr($id),
			$button_attributes['text'],
			$button_attributes['content'],
			$button_attributes['attributes']
		);

	}

	public function lazy_load_markup(){
		$this->load_markup = true;
	}

	public function pdp_button(){

		if( !get_theme_mod('wishlist_pdp__enable', true) ){
			return;
		}

		$position = get_theme_mod('wishlist_pdp__position', 'inline');

		$hooks = [
			'before' => [
				'hook' => 'woocommerce_before_add_to_cart_form',
				'priority' => 10
			],
			'inline' => [
				'hook' => 'woocommerce_after_add_to_cart_button',
				'priority' => 0
			],
			'after' => [
				'hook' => 'reycore/woocommerce/single/after_add_to_cart_form',
				'priority' => 0
			],
		];

		if( reycore_wc__is_catalog() ){
			$position = 'catalog_mode';
			$hooks['catalog_mode'] = [
				'hook' => 'woocommerce_single_product_summary',
				'priority' => 30
			];
		}

		add_action( $hooks[$position]['hook'], [$this, 'output_pdp_button'], $hooks[$position]['priority'] );

	}

	public function maybe_render(){

		if( ! apply_filters( 'reycore/woocommerce/pdp/render/wishlist', true ) ){
			return;
		}

		return true;
	}

	public function output_pdp_button(){

		if( ! $this->maybe_render() ){
			return;
		}

		$product = wc_get_product();

		if ( ! ($product && $id = $product->get_id()) ) {
			return;
		}

		$button_class = $text_class = [];
		$active_products = self::get_ids();

		$button_text = self::get_texts('wishlist__texts_add');

		if( !empty($active_products) && in_array($id, $active_products, true) ){
			$button_class[] = '--in-wishlist';
			$button_text = self::get_texts('wishlist__texts_rm');
		}

		if( is_user_logged_in() ){
			$button_class[] = '--supports-ajax';
		}

		$button_content = $this->get_wishlist_icon();
		$is_block = false;

		if( ($btn_style = get_theme_mod('wishlist_pdp__btn_style', 'btn-line')) && $btn_style !== 'none' ){

			reycore_assets()->add_styles('rey-buttons');

			if( strpos($btn_style, 'btn--block') !== false ){
				$is_block = true;
			}

			$button_class['btn_style'] = 'btn --pdp ' . $btn_style;
			// disable line buttons
			if( in_array($btn_style, ['btn-line', 'btn-line-active'], true) ){
				$text_class['text_style'] = 'btn ' . $btn_style;
				$button_class['btn_style'] = 'btn --btn-text';
			}
		}

		$text_visibility = get_theme_mod('wishlist_pdp__wtext', 'show_desktop');

		if( $text_visibility && $button_text ){
			if( $text_visibility === 'show_desktop' ){
				$text_class[] = '--dnone-sm --dnone-md';
			}
			$button_content .= sprintf('<span class="rey-wishlistBtn-text %s">%s</span>', esc_attr(implode(' ', $text_class)), $button_text);
		}

		$attributes = [];

		// only when text is hidden
		if( $text_visibility === '' && get_theme_mod('wishlist_pdp__tooltip', false) ){
			$attributes['data-rey-tooltip'] = $button_text;
		}

		$btn_html = sprintf(
			'<div class="rey-wishlistBtn-wrapper %6$s" data-transparent><button class="%1$s rey-wishlistBtn" data-id="%2$s" title="%3$s" aria-label="%3$s" %5$s>%4$s</button></div>',
			esc_attr(implode(' ', $button_class)),
			esc_attr($id),
			$button_text,
			$button_content,
			reycore__implode_html_attributes($attributes),
			($is_block ? '--block' : '')
		);

		echo $btn_html;

		$this->add_assets();

	}

	public function get_wishlist_icon(){
		return reycore__get_svg_icon([
			'id'           => get_theme_mod('wishlist__icon_type', 'heart'),
			'class'        => 'rey-wishlistBtn-icon',
			'id_attribute' => false,
		]);
	}

	public static function _get_page_id(){

		if( $wishlist_page_id = get_theme_mod('wishlist__default_url', '') ){
			return absint($wishlist_page_id);
		}

		// Inherit from TI Wishlist
		if( ($ti_wishlist = get_option('tinvwl-page')) && isset($ti_wishlist['wishlist']) && $ti_wishlist['wishlist'] !== '' ){
			return absint($ti_wishlist['wishlist']);
		}
	}

	public static function wishlist_page_id(){
		return apply_filters('reycore/translate_ids', self::_get_page_id(), 'post');
	}

	public static function wishlist_url( $url = '' ){

		if( ! self::$is_enabled ){
			return $url;
		}

		if( $wishlist_page_id = self::wishlist_page_id() ){
			return esc_url( get_permalink($wishlist_page_id) );
		}

		return $url;
	}

	public function append_wishlist_page_class($classes){

		// $classes[] = 'rey-wishlist';

		if( ($wishlist_page_id = self::wishlist_page_id()) && is_page($wishlist_page_id) ){
			$classes[] = 'woocommerce';
			$classes[] = 'rey-wishlist-page';
		}

		return $classes;
	}

	public function apply_filter_content(){

		if( !($wishlist_page_id = self::wishlist_page_id()) ){
			return;
		}

		if( ! is_page($wishlist_page_id) ){
			return;
		}

		do_action('reycore/woocommerce/wishlist/page');

		add_filter( 'the_content', [$this, 'append_wishlist_page_content']);
		remove_all_actions('rey/content/title');
	}

	public function remove_filter_content(){
		remove_filter( 'the_content', [$this, 'append_wishlist_page_content']);
	}

	/**
	 * Notice which shows when the Wishlist page is not set
	 * and shortcode is used
	 *
	 * @return string
	 */
	public function page_unset_notice(){

		if( current_user_can('administrator') && self::wishlist_page_id() !== get_queried_object_id() ){
			return _x('<p role="alert">To display the Wishlist products, please set this page as Wishlist page in Customizer > WooCommerce > Wishlist. <br>If you just want to show the Wishlist products, please use <code>[[rey_wishlist_products]]</code> shortcode or the "Products" widget in Elementor (having Wishlist products selected in Products query settings).<br><small><em>This notice shows only for Administrators.</em></small></p>', 'Various admin. texts', 'rey-core');
		}
	}

	/**
	 * Shows a list of products
	 *
	 * @param array $atts
	 * @return void
	 */
	public function products_shortcode($atts = []){

		if( ! class_exists('\ReyCore\WooCommerce\Tags\ProductArchive') ){
			return;
		}

		$atts = shortcode_atts([
			'hide_empty' => 'no',
		], $atts);

		$products_ids = self::get_ids();

		ob_start();

		if( ! $products_ids ){

			if( $atts['hide_empty'] !== 'yes' ){
				/**
				 * Hook: woocommerce_no_products_found.
				 *
				 * @hooked wc_no_products_found - 10
				 */
				do_action( 'woocommerce_no_products_found' );
			}

			return;
		}

		$product_archive = new \ReyCore\WooCommerce\Tags\ProductArchive(
			[
				'name'        => 'wishlist_products',
				'filter_name' => 'wishlist_products',
				'main_class'  => 'rey-wishlistProducts',
				'el_instance' => false,
				'product_ids' => $products_ids,
			]
		);

		if ( ($query_results = (array) $product_archive->get_query_results()) && ! empty($query_results['ids']) ) {

			$product_archive->render_start();
				$product_archive->loop_start();
					$product_archive->render_products();
				$product_archive->loop_end();
			$product_archive->render_end();

		}

		return ob_get_clean();
	}

	public function shortcode_catalog_button_html(){
		return $this->render_catalog_button();
	}

	/**
	 * Wrapper for the base page shortcode
	 *
	 * @param array $atts
	 * @return void
	 */
	public function wishlist_shortcode_page_output($atts = []){
		return $this->wishlist_page_output($atts);
	}

	/**
	 * Output of the Wishlist page
	 *
	 * @param array $atts
	 * @return void
	 */
	public function wishlist_page_output($atts = []){

		if( $notice = $this->page_unset_notice() ){
			return $notice;
		}

		// Ensure default grid type
		add_filter( 'theme_mod_loop_grid_layout', function(){
			return 'default';
		});

		reycore_assets()->add_styles(self::ASSET_HANDLE . '-page');

		$this->add_assets();

		do_action('reycore/woocommerce/loop/scripts');

		$classes = '';

		if( isset($atts['hide_title']) && $atts['hide_title'] === 'yes' ){
			$classes .= ' --hide-title';
		}

		ob_start(); ?>

		<div class="rey-wishlistWrapper --empty <?php echo esc_attr($classes); ?>"></div>

		<div class="rey-wishlist-emptyPage" data-id="<?php echo esc_attr( self::wishlist_page_id() ); ?>">
			<?php $this->render_empty_page() ?>
		</div>

		<?php $this->render_share() ?>

		<div class="rey-lineLoader rey-wishlistLoader"></div>

		<?php
		return ob_get_clean();
	}

	public function render_empty_page(){

		$gs = get_theme_mod('wishlist__empty_gs', '');
		$mode = get_theme_mod('wishlist__empty_mode', 'overwrite');

		if( $gs && 'overwrite' === $mode ){
			reycore_assets()->defer_page_styles('elementor-post-' . $gs);
			echo \ReyCore\Elementor\GlobalSections::do_section($gs, false, true);
			return;
		}

		$gs_positions = [
			'before' => '',
			'after' => '',
		];

		if( $gs ){
			reycore_assets()->defer_page_styles('elementor-post-' . $gs);
			$gs_positions[$mode] = \ReyCore\Elementor\GlobalSections::do_section($gs, false, true);
		}

		echo $gs_positions['before'];

		reycore_assets()->add_styles('rey-buttons'); ?>

		<div class="rey-wishlist-emptyPage-icon">
			<?php echo $this->get_wishlist_icon(); ?>
		</div>

		<div class="rey-wishlist-emptyPage-title">
			<?php echo sprintf(
				'<%1$s>%2$s</%1$s>',
				apply_filters('reycore/woocommerce/wishlist/empty_page_title_tag', 'h2'),
				self::get_texts('wishlist__texts_page_title')
			) ?>
		</div>

		<div class="rey-wishlist-emptyPage-content">
			<p><?php echo self::get_texts('wishlist__texts_page_text'); ?></p>
			<?php echo apply_filters('reycore/woocommerce/wishlist/empty_page_button', sprintf('<a href="%s" class="btn btn-primary">%s</a>', get_permalink( wc_get_page_id( 'shop' ) ), self::get_texts('wishlist__texts_page_btn_text') ), $this ); ?>
		</div>

		<?php

		echo $gs_positions['after'];
	}

	/**
	 * Render Wishlist sharing icons
	 *
	 * @return void
	 */
	public function render_share(){

		$shared_id = self::get_shared_id();

		if( ! is_user_logged_in() && ! $shared_id ) {
			return;
		}

		if( ! function_exists('reycore__socialShare') ){
			return;
		}

		if( ! get_theme_mod('wishlist__share_enable', true) ){
			return;
		}

		$share_icons = get_theme_mod('wishlist__share_icons', [
			[
				'social_icon' => 'twitter',
			],
			[
				'social_icon' => 'facebook-f',
			],
			[
				'social_icon' => 'linkedin',
			],
			[
				'social_icon' => 'pinterest-p',
			],
			[
				'social_icon' => 'mail',
			],
			[
				'social_icon' => 'copy',
			],
		]);

		$user_key = $shared_id ? $shared_id : self::get_user_key();
		$url = add_query_arg( 'wid', $user_key, self::wishlist_url() );

		printf('<div class="rey-wishlistShare"><div class="rey-wishlistShare-title">%s</div>', get_theme_mod('wishlist__share_title', 'SHARE ON'));

			reycore__socialShare([
				'share_items' => wp_list_pluck($share_icons, 'social_icon'),
				'colored'     => get_theme_mod('wishlist__share_icons_colored', false),
				'url'         => $url,
				'class'       => '--round_m',
				'title'       => sprintf( esc_html__('See my Wishlist on %s at ', 'rey-core'), get_bloginfo() ),
				'title'       => get_theme_mod('wishlist__share_text', sprintf( esc_html__('See my Wishlist on %s at ', 'rey-core'), get_bloginfo() ) ),
			]);

		echo '</div>';

	}

	public function append_wishlist_page_content( $content ){

		if( function_exists('reycore__elementor_edit_mode') && reycore__elementor_edit_mode() ){
			return $content;
		}

		if( !is_main_query() ){
			return $content;
		}

		add_filter('comments_open', '__return_false', 20, 2);
		add_filter('pings_open', '__return_false', 20, 2);
		add_filter('comments_array', '__return_empty_array', 10, 2);

		$wishlist__inj_type = get_theme_mod('wishlist__inj_type', 'override');

		if( $wishlist__inj_type === 'override' ){
			return do_shortcode('[rey_wishlist]');
		}
		else if( $wishlist__inj_type === 'append' ){
			return $content . do_shortcode('[rey_wishlist]');
		}

		return $content;
	}

	public function add_loading($classes){

		if( ($wishlist_page_id = self::wishlist_page_id()) && is_page($wishlist_page_id) ){
			if( self::get_ids() ){
				$classes[] = '--loading';
			}
		}

		return $classes;
	}

	/**
	 * Render products in various locations (eg: Cart)
	 *
	 * @param integer $columns
	 * @return void
	 */
	public function render_products( $columns = 3 ){

		$product_ids = self::get_ids();

		if( empty($product_ids) ){
			return;
		}

		if( is_cart() ){

			$product_ids_in_cart = [];

			foreach( WC()->cart->get_cart() as $cart_item ){
				$product_ids_in_cart[] = $cart_item['product_id'];
			}

			$product_ids = array_diff($product_ids, $product_ids_in_cart);

			if( empty($product_ids) ){
				return;
			}
		}

		// let's randomize
		shuffle($product_ids);

		// show 6 tops
		$product_ids = array_slice($product_ids, 0, apply_filters('reycore/woocommerce/wishlist/cart_limit', 6));

		add_filter( 'reycore/woocommerce/loop/render/wishlist-topright', '__return_false');
		add_filter( 'reycore/woocommerce/loop/render/wishlist-bottomright', '__return_false');
		add_filter( 'reycore/woocommerce/loop/render/wishlist-bottom', '__return_false');
		add_filter('theme_mod_wishlist__top_label', '__return_false');

		echo '<div class="rey-wishlistProds">';

			printf('<h5 class="rey-wishlistProds-title">%s</h5>', esc_html__('Some of your favorite products', 'rey-core'));

			wc_set_loop_prop( 'name', 'wishlist' );
			wc_set_loop_prop( 'columns', $columns );

			$wishlist_products = array_filter( array_map( 'wc_get_product', $product_ids ), 'wc_products_array_filter_visible' );

			reycore_wc__setup_carousel__before();

			woocommerce_product_loop_start();
				foreach ( $wishlist_products as $wprod ) :
					$post_object = get_post( $wprod->get_id() );
					setup_postdata( $GLOBALS['post'] =& $post_object ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, Squiz.PHP.DisallowMultipleAssignments.Found
					wc_get_template_part( 'content', 'product' );
					wp_reset_postdata();
				endforeach;
			woocommerce_product_loop_end();

			reycore_wc__setup_carousel__after();

		echo '</div>';

		remove_filter( 'reycore/woocommerce/loop/render/wishlist-topright', '__return_false');
		remove_filter( 'reycore/woocommerce/loop/render/wishlist-bottomright', '__return_false');
		remove_filter( 'reycore/woocommerce/loop/render/wishlist-bottom', '__return_false');
		remove_filter('theme_mod_wishlist__top_label', '__return_false');
	}

	public function register_actions( $ajax_manager ){
		$ajax_manager->register_ajax_action( 'wishlist_get_page_content', [$this, 'ajax__get_page_content'], [
			'auth'   => 3,
			'nonce'  => false,
			'assets' => true,
		] );
		$ajax_manager->register_ajax_action( 'wishlist_add_to_user', [$this, 'ajax__add_to_user_meta'] );
	}

	public function ajax__get_page_content(){

		if( ! self::$is_enabled ){
			return;
		}

		$product_ids = [];
		$user_id = false;

		if( ($shared_id = self::get_shared_id()) ){

			global $wpdb;

			$sql = $wpdb->prepare("
				SELECT
					productsIds.meta_value as product_ids,
					{$wpdb->users}.ID as user_id
				FROM {$wpdb->usermeta}
				INNER JOIN {$wpdb->users}
					ON ({$wpdb->users}.ID = {$wpdb->usermeta}.user_id)
				INNER JOIN (
					SELECT *
					FROM {$wpdb->usermeta}
					WHERE {$wpdb->usermeta}.meta_key = '%s'
				) AS productsIds
					ON ({$wpdb->users}.ID = productsIds.user_id)
				WHERE {$wpdb->usermeta}.meta_key = '%s'
					AND {$wpdb->usermeta}.meta_value = '%s'",
					self::get_cookie_key(),
					self::USER_KEY,
					$shared_id
				);

			$results = $wpdb->get_results($sql, ARRAY_A);

			if( isset($results[0]['product_ids']) && ! empty($results[0]['product_ids']) ){
				$product_ids = maybe_unserialize( $results[0]['product_ids'] );
			}
			if( isset($results[0]['user_id']) && ! empty($results[0]['user_id']) ){
				$user_id = $results[0]['user_id'];
			}

		}
		else {
			$product_ids = self::get_ids();
		}

		if( empty($product_ids) ){
			return;
		}

		ob_start();

		$this->get_products_page_html([
			'product_ids' => $product_ids,
			'user_id'     => $user_id,
		]);

		return ob_get_clean();

	}

	public function get_products_page_html( $args = [] ){

		$args = wp_parse_args($args, [
			'product_ids' => [],
			'user_id'     => false,
		]);

		$product_ids = array_reverse($args['product_ids']);

		if( ! (isset($_GET['pid']) && $post_id = absint($_GET['pid'])) ){
			$url     = wp_get_referer();
			$post_id = url_to_postid( $url );
		}

		// Include WooCommerce frontend stuff
		wc()->frontend_includes();

		$button_position = 'reycore/loop_inside_thumbnail/bottom-left';

		if( 'wrapped' === get_theme_mod('loop_skin', 'basic') && ! is_rtl() ){
			$button_position = 'reycore/loop_inside_thumbnail/bottom-right';
		}

		add_action( $button_position, [$this, 'add_remove_buttons']);

		add_filter( 'reycore/variation_swatches/support', '__return_false');
		add_filter( 'reycore/woocommerce/loop/render/thumbnails_second', '__return_false');
		add_filter( 'reycore/woocommerce/loop/render/thumbnails_slideshow', '__return_false');
		add_filter( 'reycore/woocommerce/catalog/before_after/enable', '__return_false');
		add_filter( 'reycore/woocommerce/catalog/stretch_product/enable', '__return_false');
		add_filter( 'reycore/woocommerce/loop_components/disable_grid_components', '__return_true');

		add_action( 'reycore/woocommerce/loop_components/add', function($instance){

			foreach ([
				'wishlist-bottom'   => false,
				'wishlist-topright'   => false,
				'wishlist-bottomright'   => false,
			] as $id => $status) {

				if( $c = $instance->get_component($id) ){
					$c->set_status( $status );
				}
			}

		});

		$title = '<header class="rey-pageHeader"><h1 class="rey-pageTitle entry-title">' . get_the_title($post_id) . '</h1></header>';

		if( ! ( isset($_REQUEST[\ReyCore\Ajax::DATA_KEY]['hide-title']) && absint($_REQUEST[\ReyCore\Ajax::DATA_KEY]['hide-title']) === 1 ) ){
			echo apply_filters('reycore/woocommerce/wishlist/title_output', $title);
		}

		$shortcode = new \WC_Shortcode_Products( [
			'ids' => implode(',', $product_ids)
		] );

		echo $shortcode->get_content();

	}

	public static function is_ajax_call(){
		return wp_doing_ajax() && isset($_REQUEST[\ReyCore\Ajax::ACTION_KEY]) && $_REQUEST[\ReyCore\Ajax::ACTION_KEY] === 'wishlist_get_page_content';
	}

	public static function is_wishlist_owner($args){

		// user must be logged in
		if( ! is_user_logged_in() ){
			return;
		}

		// logged in, but in shared mode
		// must check the user ID
		if( $user_ID = absint($args['user_id']) ){
			return $user_ID === get_current_user_id();
		}

		return true;
	}

	public function add_remove_buttons(){

		if( self::get_shared_id() ){
			return;
		}

		global $product;

		if( ! $product ){
			return;
		}

		printf('<a class="rey-wishlist-removeBtn" href="#" data-id="%1$d" data-rey-tooltip="%4$s" aria-label="%3$s">%2$s</a>',
			$product->get_id(),
			reycore__get_svg_icon(['id' => 'close']),
			self::get_texts('wishlist__texts_rm'),
			esc_attr(self::get_texts('wishlist__texts_rm'))
		);
	}

	public function after_add_markup(){

		if( ! $this->load_markup ){
			return;
		}

		if( function_exists('reycore__elementor_edit_mode') && reycore__elementor_edit_mode() ){
			return;
		}

		if( ! reycore__can_add_public_content() ){
			return;
		}

		$type = get_theme_mod('wishlist__after_add', 'notice');

		if( $type === 'notice' ){

			reycore_assets()->add_styles('rey-buttons');

			$url = '';

			if( $wishlist_url = self::wishlist_url() ){
				$url = sprintf('<a href="%1$s" class="btn btn-line-active">%2$s</a>',
					$wishlist_url,
					self::get_texts('wishlist__texts_btn')
				);
			}

			printf( '<div class="rey-wishlist-notice-wrapper" data-lazy-hidden><div class="rey-wishlist-notice"><span>%1$s</span> %2$s</div></div>',
				self::get_texts('wishlist__texts_added'),
				$url
			);
		}
	}

	public function add_cart_assets(){

		if( WC()->cart->is_empty() ){
			return;
		}

		reycore_assets()->add_scripts(self::ASSET_HANDLE);
		reycore_assets()->add_styles(self::ASSET_HANDLE);
	}

	/**
	 * Add placeholders into Cross-sells markup template
	 *
	 * @return void
	 * @since 2.4.0
	 */
	public function render_in_products(){
		echo '<# if(items[i].wishlist){ #> {{{items[i].wishlist}}} <# } #>';
	}

	/**
	 * Add wishlist button into Cross-sells fragments data
	 *
	 * @return void
	 * @since 2.4.0
	 */
	public function add_to_fragments( $data, $product ){

		if( ! apply_filters('reycore/woocommerce/cart/crosssells/wishlist', true) ) {
			return $data;
		}

		$data['wishlist'] = $this->render_catalog_button( $product->get_id() );

		return $data;
	}

	public function add_wishlist_page_to_account_menu($items){

		if( self::wishlist_page_id() ){
			$sup = sprintf(' <sup>%s</sup>', $this->wishlist_counter_html() );
			$items['rey_wishlist'] = ! is_rtl() ? $this->wishlist_title() . $sup : $sup . $this->wishlist_title();
		}

		return $items;
	}

	public function add_wishlist_url_endpoint($url, $endpoint, $value, $permalink){

		if( $endpoint === 'rey_wishlist') {
			$url = self::wishlist_url();
		}

		return $url;
	}

	public function wishlist_counter_html(){
		return '<span class="rey-wishlistCounter-number" data-count=""></span>';
	}

	public function exclude_delay_js($status){

		if( $status && ($wishlist_page_id = self::wishlist_page_id()) && is_page($wishlist_page_id) ){
			return false;
		}

		return $status;
	}

	public function wishlist_title(){
		return self::get_texts('wishlist__text');
	}

	public function get_wishlist_ids( $ids ){

		if( ! self::$is_enabled ){
			return [];
		}

		$product_ids = self::get_ids();

		if( empty($product_ids) ){
			return $ids;
		}

		return array_reverse($product_ids);
	}

	public function ajax__add_to_user_meta(){

		if( ! is_user_logged_in() ){
			return ['errors' => esc_html__('Not logged in!', 'rey-core')];
		}

		$product_ids = self::get_cookie_products_ids();

		if( update_user_meta(get_current_user_id(), self::get_cookie_key(), $product_ids) ){
			return $product_ids;
		}
	}

	/**
	 * Transfer product ids to User meta from session,
	 * after user has logged in.
	 *
	 * @param string $user_login
	 * @param object $user
	 * @return void
	 */
	public function update_ids_after_login( $user_login, $user){

		$product_ids = self::get_cookie_products_ids();
		$saved_product_ids = get_user_meta($user->ID, self::get_cookie_key(), true);

		if( ! is_array($saved_product_ids) ) {
			$saved_product_ids = [];
		}

		update_user_meta($user->ID, self::get_cookie_key(), array_unique(array_merge($product_ids, $saved_product_ids)) );
	}

	public function flush_rewrite_rules( $setting ){

		if( ! method_exists($setting, 'value') ){
			return;
		}

		if( ! $setting->value() ){
			return;
		}

		flush_rewrite_rules();

	}

	public static function get_user_key() {

		$user_id = get_current_user_id();

		if ( ! ($user_key = get_user_meta( $user_id, self::USER_KEY, true )) ) {

			$user_key = strtoupper( substr( base_convert( md5( self::USER_KEY . $user_id ), 16, 32), 0, 12) );

			update_user_meta( $user_id, self::USER_KEY, $user_key );
		}

		return $user_key;
	}

	public function elementor__add_pg_control( $stack ){

		$stack->start_injection( [
			'of' => 'hide_new_badge',
		] );

		$stack->add_control(
			'hide_wishlist',
			[
				'label' => esc_html__( 'Wishlist', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => esc_html__( '- Inherit -', 'rey-core' ),
					'no'  => esc_html__( 'Show', 'rey-core' ),
					'yes'  => esc_html__( 'Hide', 'rey-core' ),
				],
				'condition' => [
					'loop_skin!' => 'template',
				],
			]
		);

		$stack->end_injection();

	}

	public function is_enabled(){
		return get_theme_mod('wishlist__enable', true);
	}

	public static function __config(){
		return [
			'id'          => basename(__DIR__),
			'title'       => esc_html_x('Wishlist', 'Module name', 'rey-core'),
			'description' => esc_html_x('Allows customers to pick favorite products.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => [],
			'help'        => reycore__support_url('kb/wishlist/'),
			'video'       => true,
		];
	}

	public function module_in_use(){
		return $this->is_enabled();
	}

}
