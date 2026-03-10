<?php
namespace ReyCore\WooCommerce;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use ReyCore\Helper;

class Pdp {

	protected $skins = [];

	protected $active_skin = '';

	private $components = [];

	private $wrapper_classes = [];

	public function __construct()
	{
		add_action( 'init', [$this, 'early_init'], 5 );
		add_action( 'init', [$this, 'init'] );

	}

	public function early_init(){
		$this->register_defaults();
		do_action('reycore/woocommerce/pdp/init', $this);
	}

	public function register_defaults(){

		$defaults = [
			'skin' => [
				'PdpSkins/DefaultSkin',
				'PdpSkins/Fullscreen',
				'PdpSkins/Compact',
			],
			'component' => [
				'PdpComponents/AfterAtcForm',
				'PdpComponents/AfterAtcText',
				'PdpComponents/BeforeAtcText',
				'PdpComponents/FixedSummary',
				'PdpComponents/Gallery',
				'PdpComponents/ProductNav',
				'PdpComponents/Share',
				'PdpComponents/ShortDesc',
				'PdpComponents/StockHtml',
			],
		];

		foreach ( $defaults as $type => $items ) {
			foreach ($items as $item) {
				$class_name = Helper::fix_class_name($item, 'WooCommerce');
				call_user_func_array( [$this, "register_{$type}"], [ new $class_name() ]);
			}
		}

	}

	public function init(){

		add_action( 'wp', [ $this, 'product_page' ]);
		add_action( 'reycore/assets/register_scripts', [$this, 'register_skin_scripts']);
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ]);
		add_filter( 'woocommerce_single_product_flexslider_enabled', '__return_false', 100);
		add_action( 'reycore/ajax/register_actions', [ $this, 'register_actions' ] );
		add_filter( 'rey/main_script_params', [ $this, 'script_params'], 10 );

	}

	public function set_active_skin( $skin ){
		$this->active_skin = $skin;
	}

	public function get_active_skin(){
		return $this->active_skin;
	}

	public function register_skin( $class ){
		if( $skin_id = $class->get_id() ){
			$this->skins[ $skin_id ] = $class;
		}
	}

	public function get_skins_list( $skins = [] ){
		foreach ($this->skins as $id => $skin) {
			$skins[ $id ] = $skin->get_name();
		}
		return $skins;
	}

	public function register_skin_scripts($assets){
		foreach ($this->skins as $skin) {
			$skin->register_scripts($assets);
		}
	}

	public function register_component( $component_class ){
		if( $component_id = $component_class->get_id() ){
			$this->components[ $component_id ] = $component_class;
		}
	}

	public function get_components(){
		return $this->components;
	}

	public function get_component( $component_id = '' ){

		if( $component_id && isset( $this->components[ $component_id ] ) ){
			return $this->components[ $component_id ];
		}

		return false;
	}

	public function get_gallery_types(){
		return [];
	}

	/**
	 * Filter main script's params
	 *
	 * @since 1.0.0
	 **/
	public function script_params($params)
	{
		$params['single_ajax_add_to_cart'] = self::product_page_ajax_add_to_cart();
		$params['tabs_mobile_closed'] = false;
		$params['qty_debounce'] = 50;
		return $params;
	}

	public function enqueue_scripts( $force = false ){

		if( ! $force && ! is_product() ){
			return;
		}

		wp_dequeue_style( 'photoswipe' );
		wp_dequeue_style( 'photoswipe-default-skin' );

		$styles = [
			'rey-wc-product-lite',
			'rey-wc-product',
			'rey-wc-product-linked',
		];

		if( ($product = wc_get_product()) && 'grouped' === $product->get_type() ){
			$styles[] = 'rey-wc-product-grouped';
		}

		if( wc_reviews_enabled() ){
			$styles[] = 'rey-wc-product-reviews';
		}

		if( isset($this->skins[ $this->active_skin ]) && ($active_skin = $this->skins[ $this->active_skin ]) ){
			$styles = array_merge( (array) $active_skin->get_styles(), $styles);
		}

		reycore_assets()->add_styles($styles);

		if( self::product_page_ajax_add_to_cart() ){
			reycore_assets()->add_scripts('reycore-wc-product-page-ajax-add-to-cart');
		}

	}

	/**
	 * Move stuff in template
	 * @since 1.0.0
	 */
	public function product_page()
	{

		$this->set_active_skin( get_theme_mod('single_skin', 'default') );

		if( ! reycore_wc__is_product() ){
			return;
		}

		remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );

		// Move breadcrumbs
		remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );

		if( ! get_theme_mod('single_product_price', true) ){
			remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
		}

		if( $review_counter = get_theme_mod('pdp_rating_link_display', 'show-meta') ){
			// Move ratings at the end
			if( 'show-meta' === $review_counter ){
				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
				add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 45 );
			}
			// Move ratings above the title
			else if( 'before-title' === $review_counter ){
				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
				add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 4 );
			}
			// Disable
			else if( 'hide' === $review_counter ){
				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
			}
		}

		add_filter( 'wc_product_sku_enabled', [$this, 'product_sku']);
		add_action( 'reycore/frontend/wp_head', [ $this, 'body_classes'], 20 );
		add_filter( 'woocommerce_breadcrumb_defaults', [$this, 'remove_home_in_breadcrumbs']);
		add_action( 'woocommerce_single_product_summary', [ $this, 'wrap_inner_summary' ], 2);
		add_action( 'woocommerce_single_product_summary', [ $this, 'wrap_inner_summary_end' ], 500);
		add_action( 'woocommerce_before_single_product_summary', [ $this, 'wrap_product_summary' ], 0);
		add_action( 'woocommerce_after_single_product_summary', 'reycore_wc__generic_wrapper_end', 2);
		add_action( 'woocommerce_single_product_summary', [ $this, 'wrap_title' ], 4);
		add_action( 'woocommerce_single_product_summary', 'reycore_wc__generic_wrapper_end', 6);
		add_action( 'woocommerce_before_add_to_cart_button', [ $this, 'before_add_to_cart' ], 9);
		add_action( 'woocommerce_after_add_to_cart_button', [ $this, 'after_add_to_cart' ], 11);
		add_filter( 'woocommerce_post_class', [$this, 'product_page_classes'], 20, 2 );
		add_filter( 'woocommerce_product_single_add_to_cart_text', [$this, 'single_add_to_cart_text'], 10, 2);

		foreach ($this->components as $components) {
			$components->init();
		}

		if( isset($this->skins[ $this->active_skin ]) && ($active_skin = $this->skins[ $this->active_skin ]) ){
			$active_skin->init();
		}

		do_action('reycore/woocommerce/pdp', $this);

	}

	/**
	 * Wrap inner summary - start
	 *
	 * @since 1.0.0
	 **/
	function wrap_inner_summary()
	{ ?>
		<div class="rey-innerSummary">
		<?php
	}

	function wrap_inner_summary_end()
	{
		?></div><!-- .rey-innerSummary --><?php
	}

	/**
	 * Wrap single summary - start
	 *
	 * @since 1.0.0
	 **/
	function wrap_product_summary()
	{
		do_action('reycore/woocommerce/before_single_product_summary'); ?>

		<div class="rey-productSummary"><?php

		// force load product page styles
		$this->enqueue_scripts(true);
	}

	/**
	 * Wrap single summary - start
	 *
	 * @since 1.0.0
	 **/
	function wrap_title()
	{ ?>
		<div class="rey-productTitle-wrapper"><?php
	}

	function before_add_to_cart(){
		add_filter( 'reycore/woocommerce/wrap_quantity', '__return_true');
		add_filter( 'reycore/woocommerce/add_quantity_controls', '__return_true');
	}

	function after_add_to_cart(){
		remove_filter( 'reycore/woocommerce/wrap_quantity', '__return_true');
		remove_filter( 'reycore/woocommerce/add_quantity_controls', '__return_true');
	}

	public static function is_quickview(){
		return isset($_REQUEST['reycore-ajax']) && isset($_REQUEST['reycore-ajax-data']['id']);
	}

	public static function is_single_true_product(){

		if( ! reycore_wc__is_product() ){
			return;
		}

		global $product;

		if( ! $product ){
			$product = wc_get_product();
		}

		return $product && get_queried_object_id() === $product->get_id();
	}

	/**
	 * Toggle product sku visibility
	 *
	 * @since 1.0.0
	 */
	function product_sku( $status ){

		if( reycore_wc__is_product() ){
			return get_theme_mod('product_sku_v2', true);
		}

		return $status;
	}

	public static function product_meta_is_enabled(){
		return get_theme_mod('single_product_meta_v2', true);
	}


	/**
	 * Check if breadcrums are enabled
	 *
	 * @since 1.3.4
	 */
	public static function breadcrumb_enabled(){
		return get_theme_mod('single_breadcrumbs', 'yes_hide_home') !== 'no';
	}

	/**
	 * Remove Home button in breadcrumbs
	 *
	 * @since 1.3.4
	 */
	function remove_home_in_breadcrumbs( $args ){

		if( get_theme_mod('single_breadcrumbs', 'yes_hide_home') === 'yes_hide_home' ){
			$args['home']  = false;
		}

		return $args;
	}

	public function add_wrapper_classes( $class ){
		$this->wrapper_classes = array_merge($this->wrapper_classes, (array) $class);
	}

	/**
	 * Filter product page's css classes
	 * @since 1.0.0
	 */
	function product_page_classes($classes, $product)
	{
		if( $product->get_id() === get_queried_object_id() ) {

			$classes['product_page_class'] = 'rey-product';
			$classes['product_page_skin'] = 'pdp--' . esc_attr($this->active_skin);

			if ( $product->get_type() === 'grouped' && get_theme_mod('single_atc_qty_controls', true) ) {
				$classes['grouped_controls'] = '--grouped-qty-controls';
			}

			if( isset($this->skins[ $this->active_skin ]) && ($active_skin = $this->skins[ $this->active_skin ]) ){
				$classes = array_merge( (array) $active_skin->product_page_classes(), $classes, $this->wrapper_classes);
			}

		}

		return $classes;
	}

	/**
	 * Filter product page's css classes
	 * @since 1.0.0
	 */
	function body_classes($frontend)
	{
		if( ! is_product() ){
			return;
		}

		// Skin Class
		$classes['pdp_skin'] = 'single-skin--' . esc_attr($this->active_skin);

		$frontend->add_body_class($classes);

	}

	function single_add_to_cart_text( $text, $product ){

		if ( $custom_backorder_text = get_theme_mod('single_atc__text_backorders', '') && $product->is_on_backorder( 1 ) ) {
			return $custom_backorder_text;
		}

		$custom_text = get_theme_mod('single_atc__text', '');

		if( $custom_text !== '' ){

			if( $custom_text === '0' ){
				return '';
			}

			return $custom_text;
		}

		return $text;
	}

	public function register_actions( $ajax_manager ) {
		$ajax_manager->register_ajax_action( 'get_gallery_types_list', [$this, 'customizer__get_gallery_types'], 1 );
		$ajax_manager->register_ajax_action( 'get_pdp_skins_list', [$this, 'customizer__get_pdp_skins'], 1 );
	}

	public function customizer__get_gallery_types(){

		if( $g = $this->get_component('gallery') ){

			$gallery_types = $g->get_gallery_types();

			asort($gallery_types);

			return $gallery_types;
		}

		return [];
	}

	public function customizer__get_pdp_skins(){
		return $this->get_skins_list();
	}

	public static function product_page_ajax_add_to_cart(){
		return 'yes' === get_theme_mod('product_page_ajax_add_to_cart', 'yes') && 'no' !== get_option( 'woocommerce_enable_ajax_add_to_cart' );
	}

}
