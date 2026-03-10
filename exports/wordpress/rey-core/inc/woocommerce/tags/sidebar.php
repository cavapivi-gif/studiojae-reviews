<?php
namespace ReyCore\WooCommerce\Tags;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Sidebar {

	private $__sidebar_wrapped;

	/**
	 * Shop sidebar ID
	 */
	const SHOP_SIDEBAR_ID = 'shop-sidebar';
	const FILTER_PANEL_SIDEBAR_ID = 'filters-sidebar';
	const FILTER_TOP_BAR_SIDEBAR_ID = 'filters-top-sidebar';

	public function __construct() {
		add_action( 'init', [ $this, 'init'] );
		add_action( 'widgets_init', [ $this, 'register_sidebars'] );
	}

	function default_sidebars(){
		return [
			self::SHOP_SIDEBAR_ID,
			self::FILTER_PANEL_SIDEBAR_ID,
			self::FILTER_TOP_BAR_SIDEBAR_ID,
		];
	}

	public function script_params($params)
	{
		if( $this->toggle_enabled() ){
			$params['js_params']['sidebar_toggle__status'] = get_theme_mod('sidebar_shop__toggle__status', 'all');
			$params['js_params']['sidebar_toggle__exclude'] = get_theme_mod('sidebar_shop__toggle__exclude', '');
		}

		return $params;
	}

	function init(){
		add_filter( 'rey/sidebar_name', [ $this, 'shop_sidebar'] );
		add_action( 'rey/get_sidebar', [ $this, 'get_shop_sidebar'] );
		add_filter( 'is_active_sidebar', [ $this, 'disable_product_sidebar'] );
		add_filter( 'rey/content/site_main_class', [ $this, 'main_classes'], 10 );
		add_filter( 'rey/main_script_params', [ $this, 'script_params'], 10 );
		add_action( 'reycore/woocommerce/loop/before_grid', [$this, 'before_grid']);
		add_action( 'wp_footer', [$this, 'render_filter_sidebar_panel'], 0 );
		add_filter( 'rey/content/sidebar_class', [$this, 'mobile_sidebar_add_filter_class'], 10, 2);
		add_filter( 'rey/content/site_main_class', [$this, 'mobile_sidebar_add_filter_class']);
		add_action( 'dynamic_sidebar_before', [$this, 'sidebar_wrap_before'], 0 );
		add_action( 'dynamic_sidebar_after', [$this, 'sidebar_wrap_after'], 90 );
	}

	public static function can_output_shop_sidebar(){
		return apply_filters('reycore/woocommerce/sidebars/can_output_shop_sidebar', is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy());
	}

	/**
	 * Show Shop sidebar
	 *
	 * @since 1.0.0
	 */
	public function shop_sidebar($sidebar)
	{
		if( self::can_output_shop_sidebar() ){
			return self::SHOP_SIDEBAR_ID;
		}
		return $sidebar;
	}

	/**
	 * Get Shop Sidebar
	 * @hooks to rey/get_sidebar
	 */
	public function get_shop_sidebar( $position )
	{
		if(
			self::can_output_shop_sidebar() &&
			is_active_sidebar(self::SHOP_SIDEBAR_ID) &&
			get_theme_mod('catalog_sidebar_position', 'right') == $position
		) {
			get_sidebar();
		}
	}

	/**
	 * Wrap sidebar widgets into a block
	 *
	 * @since 1.5.0
	 **/
	function sidebar_wrap_before( $index )
	{

		$GLOBALS['rey_current_sidebar'] = $index;

		// allow wrapping when in elementor mode
		$is_elementor = isset($_REQUEST['action'],$_REQUEST['post']) && 'elementor' === $_REQUEST['action'];

		// not admin
		if( is_admin() ){
			if( ! $is_elementor ){
				return;
			}
		}

		if( ! in_array( $index, self::default_sidebars() ) ){
			return;
		}

		$this->__sidebar_wrapped = true;

		$styles = [];
		$classes = [];

		if( $index === self::FILTER_TOP_BAR_SIDEBAR_ID ){
			$classes[] = $index;
			$styles[] = 'rey-wc-widgets-sidebar-filter-top';
		}
		elseif( $index === self::SHOP_SIDEBAR_ID ){

			$styles[] = 'rey-wc-widgets-sidebar-shop';

			if( self::SHOP_SIDEBAR_ID === get_theme_mod('ajaxfilter_mobile_button_opens', '') ){
				// include "Close arrow" styles
				$styles[] = 'reycore-close-arrow';
			}
		}
		elseif( $index === self::FILTER_PANEL_SIDEBAR_ID ){

			$styles[] = 'rey-wc-widgets-sidebar-filter-panel';
			$styles[] = 'reycore-close-arrow';

			// delay whats inside
			reycore_assets()->collect_start('filter_panel_assets');
		}

		if( $sidebar_title_layout = get_theme_mod('sidebar_title_layouts', '') ){
			$styles[] = 'rey-wc-widgets-titles';
			$classes[] = 'widget-title--' . $sidebar_title_layout;
		}

		printf( '<div class="rey-ecommSidebar %s">', implode(' ', array_map('esc_attr', $classes) ) );

		reycore_assets()->add_styles($styles);

		if( get_theme_mod('sidebar_shop__toggle__enable', false) &&
			$index !== 'filters-top-sidebar' &&
			strpos($index, 'filters-top-sidebar') === false ){
			reycore_assets()->add_scripts('reycore-wc-loop-toggable-widgets');
		}

		if( in_array($index, [self::SHOP_SIDEBAR_ID, self::FILTER_PANEL_SIDEBAR_ID], true) ){
			add_filter( 'widget_title', [$this, 'toggable_icons']);
		}

	}

	/**
	 * Wrap sidebar widgets into a block
	 *
	 * @since 1.5.0
	 **/
	function sidebar_wrap_after($index)
	{
		// delay what's inside
		if( $index === self::FILTER_PANEL_SIDEBAR_ID ){
			reycore_assets()->downgrade_styles_priority('filter_panel_assets');
		}

		if( isset($this->__sidebar_wrapped) && $this->__sidebar_wrapped ){
			echo '</div>';
		}

		if( in_array($index, [self::SHOP_SIDEBAR_ID, self::FILTER_PANEL_SIDEBAR_ID], true) ){
			remove_filter( 'widget_title', [$this, 'toggable_icons']);
		}

		unset($GLOBALS['rey_current_sidebar']);
		$this->__sidebar_wrapped = null;
	}

	/**
	 * Disable sidebar on product pages
	 *
	 * @since 1.0.0
	 */
	public function disable_product_sidebar( $status ) {

		global $wp_query;

		if ( $wp_query->is_singular && $wp_query->get('post_type') === 'product' && get_theme_mod('single_skin__default__sidebar', '') === '' ) {
			return false;
		}

		return $status;
	}

	/**
	 * Filter main wrapper's css classes
	 *
	 * @since 1.0.0
	 **/
	public function main_classes($classes)
	{
		if( self::can_output_shop_sidebar() && is_active_sidebar(self::SHOP_SIDEBAR_ID) && get_theme_mod('catalog_sidebar_position', 'right') !== 'disabled' ) {
			$classes[] = '--has-sidebar';
		}

		return $classes;
	}


	function toggle_enabled(){
		return get_theme_mod('sidebar_shop__toggle__enable', false);
	}

	/**
	 * Register sidebars
	 *
	 * @since 1.0.0
	 **/
	public function register_sidebars()
	{
		$title_class = $widget_class = '';

		if( $this->toggle_enabled() ){
			$title_class = 'rey-toggleWidget';
			$widget_class = 'rey-toggleWidget-wrapper';
		}

		$tag = apply_filters('reycore/woocommerce/sidebars/titles_tag', 'h3');

		$default_sidebars = [
			self::SHOP_SIDEBAR_ID => [
				'name'          => esc_html__( 'Shop Sidebar', 'rey-core' ),
				'id'            => self::SHOP_SIDEBAR_ID,
				'description'   => esc_html__('This sidebar will be visible on the shop pages.' , 'rey-core'),
				'before_widget' => '<section id="%1$s" class="widget ' . $widget_class . ' %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => "<{$tag} class='widget-title {$title_class}'>",
				'after_title'   => "</{$tag}>",
			],
			self::FILTER_PANEL_SIDEBAR_ID => [
				'name'          => esc_html__( 'Filter Panel', 'rey-core' ),
				'id'            => self::FILTER_PANEL_SIDEBAR_ID,
				'description'   => esc_html__('This sidebar should contain WooCommerce filter widgets.' , 'rey-core'),
				'before_widget' => '<section id="%1$s" class="widget ' . $widget_class . ' %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => "<{$tag} class='widget-title {$title_class}'>",
				'after_title'   => "</{$tag}>",
			],
			self::FILTER_TOP_BAR_SIDEBAR_ID => [
				'name'          => esc_html__( 'Filter Top Bar', 'rey-core' ),
				'id'            => self::FILTER_TOP_BAR_SIDEBAR_ID,
				'description'   => esc_html__('This sidebar should contain WooCommerce filter widgets horizontally before the products.' , 'rey-core'),
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => "<{$tag} class='widget-title'>",
				'after_title'   => "</{$tag}>",
			],
		];

		foreach ($default_sidebars as $key => $sidebar) {
			register_sidebar( $sidebar );
		}

		if( get_theme_mod('single_skin__default__sidebar', '') !== '' ){
			register_sidebar( [
				'name'          => esc_html__( 'Product Page', 'rey-core' ),
				'id'            => 'product-page-sidebar',
				'description'   => esc_html__('This will be displayed only on Product Pages.' , 'rey-core'),
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => "<{$tag} class='widget-title'>",
				'after_title'   => "</{$tag}>",
			] );
		}

		do_action('reycore/woocommerce/sidebar/widget_init', $default_sidebars, $this);
	}

	public function toggable_icons( $title ){

		if( ! $this->toggle_enabled() ){
			return $title;
		}

		reycore_assets()->add_styles('rey-wc-widgets-toggable-widgets');

		if( get_theme_mod('sidebar_shop__toggle__indicator', 'plusminus') === 'plusminus' ){
			$title .= reycore__get_svg_icon(['id'=>'reycore-icon-minus', 'class' => '__indicator __minus']);
			$title .= reycore__get_svg_icon(['id'=>'reycore-icon-plus', 'class' => '__indicator __plus']);
		}

		else {
			$title .= reycore__get_svg_icon(['id'=>'reycore-icon-arrow', 'class' => '__indicator __arrow']);
		}

		return $title;
	}

	public function before_grid(){

		if( wc_get_loop_prop( 'is_paginated' ) ){
			add_filter( 'reycore/woocommerce/loop/can_add_filter_panel_sidebar', '__return_true' );
		}

	}

	public function render_filter_sidebar_panel()
	{

		if( is_cart() || is_checkout() || is_account_page() ){
			return;
		}

		foreach ([
			'woocommerce_layered_nav',
			'woocommerce_price_filter',
			'woocommerce_rating_filter',
			'woocommerce_widget_cart',
		] as $widget) {
			if( is_active_widget(false, false, $widget, true) ){
				reycore_assets()->add_styles(['rey-wc-widgets-classic']);
				break;
			}
		}

		if( ! apply_filters('reycore/woocommerce/loop/can_add_filter_panel_sidebar', false) ){
			return;
		}

		if( reycore_wc__check_filter_panel() ) {
			reycore__get_template_part('template-parts/woocommerce/filter-panel-sidebar');
			reycore_assets()->add_styles(['rey-overlay', 'reycore-side-panel']);
		}

		if( reycore_wc__check_filter_btn() ){
			reycore_assets()->add_scripts(['reycore-sidepanel', 'reycore-wc-loop-filter-panel', 'rey-simple-scrollbar']);
			reycore_assets()->add_styles('rey-simple-scrollbar');
		}
	}


	/**
	 * Filter Sidebar - Add CSS class to shop sidebar
	 *
	 * @since 1.0.0
	 **/
	function mobile_sidebar_add_filter_class($classes, $sidebar = '')
	{
		$mobile_btn = reycore_wc__check_filter_btn();

		if( is_singular('product') ){
			return $classes;
		}

		/**
		 * rey-filterSidebar means it's used on mobile as a filter panel
		 */

		if( reycore_wc__check_shop_sidebar() || reycore_wc__check_filter_sidebar_top() ) {
			$classes['filter_sidebar'] = 'rey-filterSidebar';
		}

		if( reycore_wc__check_filter_panel() ){
			$classes[] = '--filter-panel';
		}

		// also determines if it's a sidebar
		if( $mobile_btn === $sidebar ){
			$classes[] = '--supports-mobile';
			$classes['filter_sidebar'] = 'rey-filterSidebar';
			reycore_assets()->add_styles(['reycore-side-panel', 'rey-wc-widgets-sidebar-filter-panel', 'rey-wc-widgets-mobile-panel']);
		}

		if( $sidebar === self::SHOP_SIDEBAR_ID && get_theme_mod('sidebar_shop__sticky', false) ){
			$classes['shop_sidebar_sticky'] = '--sidebar-sticky';
		}

		return array_unique($classes);
	}

}
