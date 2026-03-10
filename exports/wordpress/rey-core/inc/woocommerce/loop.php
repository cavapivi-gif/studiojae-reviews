<?php
namespace ReyCore\WooCommerce;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use ReyCore\Plugin;
use ReyCore\Helper;

class Loop {

	protected $skins = [];

	protected $active_skin = '';

	private $components = [];
	private $component_groups = [];
	private $components_to_remove = [];
	private $product_item_classes = [];

	public static $custom_product_item_classes = [];
	public static $custom_product_item_inner_classes = [];

	public function __construct()
	{
		add_action( 'init', [$this, 'early_init'], 5 );
		add_action( 'init', [$this, 'init'] );
	}

	public function early_init(){
		$this->register_defaults();
		do_action('reycore/woocommerce/loop/init', $this);
	}

	public function register_defaults(){

		$defaults = [
			'skin' => [
				'LoopSkins/DefaultSkin',
				'LoopSkins/Basic',
				'LoopSkins/Wrapped',
			],
			'component' => [
				'LoopComponents/AddToCart',
				'LoopComponents/CatalogOrdering',
				'LoopComponents/Category',
				'LoopComponents/Excerpt',
				'LoopComponents/FeaturedBadge',
				'LoopComponents/FilterButton',
				'LoopComponents/FilterTopSidebar',
				'LoopComponents/NewBadge',
				'LoopComponents/Prices',
				'LoopComponents/Ratings',
				'LoopComponents/ResultCount',
				'LoopComponents/SoldOutBadge',
				'LoopComponents/ThumbnailsSecond',
				'LoopComponents/ThumbnailsSlideshow',
				'LoopComponents/Thumbnails',
				'LoopComponents/Title',
				'LoopComponents/Variations',
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

		add_action( 'woocommerce_product_query', [ $this, 'fix_pagination_current_query_original'], 1000);

		add_action( 'reycore/assets/register_scripts', [$this, 'register_scripts']);
		add_action( 'reycore/woocommerce/loop/scripts', [$this, 'enqueue_scripts']);

		add_filter( 'woocommerce_product_loop_start', [$this, 'product_loop_start'], 0);
		add_filter( 'woocommerce_product_loop_end', [$this, 'product_loop_end']);

		add_action( 'reycore/woocommerce/loop/before_grid', [$this, 'before_grid']);
		add_action( 'reycore/woocommerce/loop/after_grid', [$this, 'after_grid']);

		add_action( 'woocommerce_no_products_found', [$this, 'no_products_global_section'], 5);

		// cleanups
		remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
		remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
		remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );

		// wrappers
		add_action( 'reycore/woocommerce/loop/before_grid', [$this, 'header_wrapper_start'], 14 );
		add_action( 'reycore/woocommerce/loop/before_grid', [$this, 'header_wrapper_end'], 41 );
		add_action( 'woocommerce_before_shop_loop_item', [$this, 'start_wrapper_div'], 0);
		add_action( 'woocommerce_after_shop_loop_item', 'reycore_wc__generic_wrapper_end', 999);
		add_action( 'woocommerce_before_subcategory', [$this, 'start_wrapper_div'], 0);
		add_action( 'woocommerce_after_subcategory', 'reycore_wc__generic_wrapper_end', 999);
		add_action( 'woocommerce_before_shop_loop_item_title', [$this, 'loop_product_link_open'], 9 );
		add_action( 'woocommerce_before_shop_loop_item_title', [$this, 'loop_product_link_close'], 12 );
		add_action( 'woocommerce_before_shop_loop_item_title', [$this, 'thumbnail_wrapper_start'], 5);
		add_action( 'woocommerce_before_shop_loop_item_title', [$this, 'thumbnail_wrapper_end'], 12);

		add_action( 'reycore/ajax/register_actions', [ $this, 'register_actions' ] );
		add_action( 'template_redirect', [ $this, 'late_components_init' ] );

		if( ! empty($this->components) ){
			foreach ($this->components as $key => $component) {
				$component->init();
			}
		}
	}

	public function get_active_skin(){
		return $this->active_skin ? $this->active_skin : get_theme_mod('loop_skin', 'basic');
	}

	public function register_skin( $class ){
		if( $skin_id = $class->get_id() ){
			$this->skins[ $skin_id ] = $class;
		}
	}

	public function get_skins_list( $skins = [] ){
		foreach ($this->skins as $id => $skin) {
			$skins[ $id ] = ($skin_name = $skin->get_list_option_key()) ? $skin_name : $skin->get_name();
		}
		return $skins;
	}

	public function get_skins( $skin_id = '' ){
		if( $skin_id && isset( $this->skins[ $skin_id ] ) ){
			return $this->skins[ $skin_id ];
		}
		return $this->skins;
	}

	public function register_scripts( $assets ){
		foreach ($this->skins as $skin) {
			$skin->register_scripts($assets);
		}
	}

	public function late_components_init(){
		foreach ($this->components as $key => $component) {
			$component->late_init();
		}
	}

	public function disable_pagination(){

		$non_paginated_loops = [
			'related',
			'up-sells',
			'cross-sells',
		];

		if( $q_name = wc_get_loop_prop( 'name' ) ){
			if( in_array($q_name, $non_paginated_loops, true) ){
				wc_set_loop_prop( 'is_paginated', false );
			}
		}

	}

	public function before_grid(){

		$this->active_skin = get_theme_mod('loop_skin', 'basic');

		$this->disable_pagination();

		$this->components_add_remove( 'add' );

		if( isset( $this->skins[ $this->active_skin ] ) && $skin = $this->skins[ $this->active_skin ] ){
			$skin->add_hooks();
		}

		add_filter( 'reycore/woocommerce/product_loop_attributes/v2', [ $this, 'skin_script_params'], 20 );

		$this->enqueue_scripts();
		$this->set_product_item_classes();
	}

	public function after_grid(){

		$this->components_add_remove( 'remove' );

		if( isset( $this->skins[ $this->active_skin ] ) && $skin = $this->skins[ $this->active_skin ] ){
			$skin->remove_hooks();
		}

		remove_filter( 'reycore/woocommerce/product_loop_attributes/v2', [ $this, 'skin_script_params'], 20 );

	}

	public function get_default_settings( $setting = '', $default = '' ){

		static $settings;

		if( is_null($settings) ){

			$settings = [
				'loop_alignment'            => 'left',
				'loop_add_to_cart_style'    => 'under',
				'loop_quickview'            => '1',
				'loop_quickview_style'      => 'under',
				'loop_quickview_position'   => 'bottom',
				'loop_quickview_icon_type'  => 'eye',
				'loop_wishlist_position'    => 'bottom',
				'wishlist_loop__icon_style' => 'minimal',
				'loop_discount_label'       => 'price',
			];

			if( isset( $this->skins[ $this->active_skin ] ) && $skin = $this->skins[ $this->active_skin ] ){
				$skin_settings = $skin->get_default_settings();
				$settings = wp_parse_args( $skin_settings, $settings );
			}

		}

		if( $setting ){
			return isset($settings[$setting]) ? $settings[$setting] : $default;
		}

		return $settings;
	}

	public function skin_script_params( $attributes ){

		$params = [];

		if( ($titles_height = reycore_wc__get_setting('product_titles_height', '')) && 'disabled' !== $titles_height ){

			$attributes['data-title-height'] = $titles_height;

			if( 'eq' === $titles_height ){
				$params['equalize_selectors'] = apply_filters('reycore/woocommerce/loop/equalize_selectors', [
					'.woocommerce-loop-product__title',
					// '.rey-productVariations',
				]);
			}
			else {
				if( ! get_theme_mod('product_titles_height_min', true) ){
					$attributes['data-title-height-min'] = '';
				}
			}
		}

		if( isset( $this->skins[ $this->active_skin ] ) && $skin = $this->skins[ $this->active_skin ] ){
			if( $script_params = $skin->get_script_params() ){
				$params = wp_parse_args($script_params, $params);
			}
		}

		$attributes['data-params'] = wp_json_encode($params);

		return $attributes;
	}



	public function register_component( $component_class ){

		if( $component_id = $component_class->get_id() ){

			$this->components[ $component_id ] = $component_class;

			// collect groups
			if( $component_group = $component_class->get_group() ){
				$this->component_groups[ $component_id ] = $component_group;
			}

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

	public function get_components_groups(){
		return $this->component_groups;
	}

	public function components_add_remove( $task = 'add' ){

		// run cleanup
		if( 'remove' === $task && ! is_null($this->components_to_remove) ){

			foreach($this->components_to_remove as $component_to_remove){
				$this->hooks_add_remove($component_to_remove, $task);
			}

			return;
		}

		$skin_components = [];

		if( isset( $this->skins[ $this->active_skin ] ) && ($skin = $this->skins[ $this->active_skin ]) ){
			$skin_components = $skin->get_component_schemes();
		}

		$maybe_disable_grid_components = apply_filters('reycore/woocommerce/loop_components/disable_grid_components', false);

		do_action('reycore/woocommerce/loop_components/add', $this);

		foreach ($this->components as $id => $component) {

			// Disable Grid components
			if( $maybe_disable_grid_components && 'grid' === $component->loop_type() ){
				continue;
			}

			do_action('reycore/woocommerce/loop_components/add/component', $id, $component, $this);

			if( ! $component->get_status() ){
				continue;
			}

			if( isset($skin_components[$id]) ){
				$component->set_scheme( $skin_components[$id] );
			}

			do_action("reycore/woocommerce/loop_components:{$id}", $component, $this);

			$scheme = $component->get_scheme();

			if( empty($scheme) ){
				continue;
			}

			if( ! is_array($scheme) ){
				continue;
			}

			if( ! isset( $scheme['type'] ) ){
				continue;
			}

			if( ! isset($scheme['callback']) && method_exists( $component, 'render' ) ){
				$scheme['callback'] = [$component, 'render'];
			}

			if( $component_classes = $component->get_css_classes() ){
				$this->product_item_classes = $component_classes;
			}

			$this->hooks_add_remove( $scheme, $task );
		}
	}

	/**
	 * Add Remove Hook
	 *
	 * @since 1.0.0
	 **/
	private function hooks_add_remove($item, $task)
	{
		if( isset($item['type']) && isset($item['callback']) ){

			if( 'add' === $task ){
				$this->components_to_remove[] = $item;
			}

			call_user_func(
				sprintf('%s_%s', $task, $item['type'] ),
				$item['tag'],
				$item['callback'],
				isset($item['priority']) ? $item['priority'] : 10,
				isset($item['accepted_args']) ? $item['accepted_args'] : 1
			);
		}
	}

	function set_product_item_classes( $classes = [] ){

		if( ! empty($classes) ){
			return $this->product_item_classes = $classes;
		}

		$classes['rey_skin'] = 'rey-wc-skin--' . get_theme_mod('loop_skin', 'basic');
		$classes['text-align'] = 'rey-wc-loopAlign-' . reycore_wc__get_setting('loop_alignment');

		if(
			get_theme_mod('loop_animate_in', true) &&
			! reycore__elementor_edit_mode() &&
			wc_get_loop_prop( 'entry_animation' ) !== false
		){
			reycore_assets()->add_scripts('rey-animate-items');
			$classes['animated-entry'] = 'is-animated-entry';
		}

		// Check if product has custom height
		// @note: using get_option on WC's cropping option intentionally
		if( self::is_custom_image_height() ){
			$classes['image-container-height'] = ' --customImageContainerHeight';
		}

		// grid class
		if( get_option('woocommerce_thumbnail_cropping', '1:1') === 'uncropped' ){
			$classes[] = '--uncropped';
		}

		$this->product_item_classes = array_merge($this->product_item_classes, $classes, self::$custom_product_item_classes);
	}

	/**
	 * Utility
	 *
	 * Add animation hover class in loop
	 *
	 * @since 1.0.0
	 */
	function general_css_classes()
	{
		if ( ! self::is_product() ) {
			return;
		}

		return $this->product_item_classes;
	}

	function enqueue_scripts(){

		$assets = [
			'styles' => [
				'rey-wc-general',
				'rey-wc-general-deferred',
				'rey-wc-general-deferred-font',
				'rey-wc-loop',
			],
			'scripts' => [
				'reycore-wc-loop-grids',
			],
		];

		switch( get_theme_mod('loop_grid_layout', 'default') ):
			case"masonry":
				wp_enqueue_script('masonry');
				$assets['styles'][] = 'rey-wc-loop-grid-skin-masonry';
				break;
			case"masonry2":
				wp_enqueue_script('masonry');
				$assets['styles'][] = 'rey-wc-loop-grid-skin-masonry';
				$assets['styles'][] = 'rey-wc-loop-grid-skin-masonry2';
				break;
			case"metro":
				$assets['styles'][] = 'rey-wc-loop-grid-skin-metro';
				break;
			case"scattered":
				$assets['styles'][] = 'rey-wc-loop-grid-skin-scattered';
				break;
			case"scattered2":
				$assets['styles'][] = 'rey-wc-loop-grid-skin-scattered';
				break;
		endswitch;

		// List view
		if( self::is_mobile_list_view() ){
			$assets['styles'][] = 'rey-wc-loop-grid-mobile-list-view';
		}

		if( ($titles_height = reycore_wc__get_setting('product_titles_height')) && 'disabled' !== $titles_height ){
			if( 'eq' === $titles_height ){
				$assets['scripts'][] = 'reycore-wc-loop-equalize';
			}
			else {
				$assets['styles'][] = 'reycore-loop-titles-height';
			}
		}

		reycore_assets()->add_styles($assets['styles']);
		reycore_assets()->add_scripts($assets['scripts']);

		if( ($skin_id = $this->get_active_skin()) && isset( $this->skins[ $skin_id ] ) ){
			$this->skins[ $skin_id ]->add_scripts();
		}
	}

	/**
	 * Filter product list start to add custom CSS classes
	 *
	 * @since 1.1.2
	 */
	public function product_loop_start( $html )
	{
		$before_content = '';

		ob_start();

			do_action('reycore/woocommerce/loop/before_grid', $this);

			if( $q_name = wc_get_loop_prop( 'name' ) ){
				do_action('reycore/woocommerce/loop/before_grid/name=' . $q_name, $this);
			}

		if( $hooks_data = ob_get_clean() ){
			$before_content .= $hooks_data;
		}

		$search_for = 'class="products';

		if( self::is_mobile_list_view() ){
			$classes['mobile_listview'] = 'rey-wcGrid-mobile-listView';
		}

		$classes['skin'] = '--skin-' . esc_attr( $this->get_active_skin() );

		/**
		 * Grid Gap CSS class
		 */
		$classes['grid_gap'] = reycore_wc__get_gap_css_class();

		/**
		 * Product Grid CSS class
		 */

		$grid_layout = get_theme_mod('loop_grid_layout', 'default');
		$classes['grid_layout'] = 'rey-wcGrid-' . esc_attr( $grid_layout );

		if( 'metro' === $grid_layout ){
			$classes['metro_prevents'] = '--prevent-colspan';
		}

		if( wc_get_loop_prop( 'is_paginated' ) && wc_get_loop_prop( 'total_pages' ) ){

			$classes['paginated'] = '--paginated';

			if( in_array(get_theme_mod('loop_pagination', 'paged'), ['load-more', 'infinite'], true) ){
				$classes['paginated_infinite'] = '--infinite';
			}
		}

		$attributes['data-cols'] = wc_get_loop_prop( 'columns' );
		$attributes['data-cols-tablet'] = reycore_wc_get_columns('tablet');
		$attributes['data-cols-mobile'] = reycore_wc_get_columns('mobile');
		$attributes['data-skin'] = esc_attr( $this->get_active_skin() );

		$html = str_replace( $search_for, $search_for . ' ' . reycore__product_grid_classes($classes), $html );
		$html = str_replace( '">', sprintf('" %s>', reycore__product_grid_attributes($attributes) ), $html );

		return $before_content . $html;
	}

	public function product_loop_end( $html )
	{

		$after_content = '';

		ob_start();

			do_action('reycore/woocommerce/loop/after_grid', $this);

			if( $q_name = wc_get_loop_prop( 'name' ) ){
				do_action('reycore/woocommerce/loop/after_grid/name=' . $q_name, $this);
			}

		if( $hooks_data = ob_get_clean() ){
			$after_content .= $hooks_data;
		}

		global $wp_query;

		if( $colspans = $wp_query->get('colspans') ){
			$after_content .= sprintf('<div data-colspans="%d"></div>', $colspans);
		}

		return $html . $after_content;
	}

	public function no_products_global_section(){

		if( ! class_exists('\ReyCore\Elementor\GlobalSections') ){
			return;
		}

		if( ! ($gs = get_theme_mod('loop_empty__gs', '')) ){
			return;
		}

		if( ! ($mode = get_theme_mod('loop_empty__mode', 'overwrite')) ){
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


	/**
	 * Wrap loop header
	 *
	 * @since 1.0.0
	 **/
	public function header_wrapper_start()
	{

		if ( ! wc_get_loop_prop( 'is_paginated' ) ) {
			return;
		}

		$classes = [];

		if( reycore_wc__check_filter_btn() ){
			$classes['has_btn'] = '--has-filter-btn';
		}

		do_action('reycore/loop_products/before_header_start');

		echo sprintf('<div class="rey-loopHeader %s">', esc_attr( implode(' ', apply_filters('rey/woocommerce/loop/header_classes', $classes ) ) ) );

		reycore_assets()->add_styles('rey-wc-loop-header');

		do_action('reycore/loop_products/after_header_start');

	}

	/**
	 * Wrap loop header
	 *
	 * @since 1.0.0
	 **/
	public function header_wrapper_end()
	{
		if ( ! wc_get_loop_prop( 'is_paginated' ) ) {
			return;
		}

		do_action('reycore/loop_products/before_header_end');

		echo '</div>';

		do_action('reycore/loop_products/after_header_end');

	}


	/**
	 * Wrap layout - start
	 *
	 * @since 1.0.0
	 **/
	function start_wrapper_div()
	{

		if( get_theme_mod('loop_edit_link', true) && function_exists('rey__edit_link') ){
			rey__edit_link(['class' => 'rey-productEditLink']);
		}

		do_action('reycore/woocommerce/loop/before_product_inner', $this);

		printf('<div class="rey-productInner %s">', esc_attr(implode(' ', self::$custom_product_item_inner_classes)));

	}

	/**
	 * Utility
	 *
	 * Checks if the thumbnail should be wrapped with link
	 *
	 * @since 1.2.0
	 */
	public function wrap_thumbnails_with_link() {
		return apply_filters('reycore/woocommerce/loop/wrap_thumbnails_with_link', true);
	}

	public function loop_product_link_open(){
		if( $this->wrap_thumbnails_with_link() ){
			woocommerce_template_loop_product_link_open();
		}
	}

	public function loop_product_link_close(){
		if( $this->wrap_thumbnails_with_link() ){
			woocommerce_template_loop_product_link_close();
		}
	}

	public function thumbnail_wrapper_start()
	{
		echo '<div class="rey-productThumbnail">';
	}

	public function thumbnail_wrapper_end()
	{

		foreach([ 'top-left', 'top-right', 'bottom-left', 'bottom-right', 'bottom-center' ] as $position){

			ob_start();
			do_action('reycore/loop_inside_thumbnail/' . $position);
			$th_content = ob_get_clean();

			if( !empty($th_content) ){

				if( in_array($position, ['bottom-left', 'bottom-right', 'bottom-center'], true) ){
					$position .= ' rey-thPos--bottom';
				}

				printf('<div class="rey-thPos rey-thPos--%2$s" data-position="%2$s">%1$s</div>', $th_content, $position);
			}
		}

		echo '</div>';

	}

	public static function is_mobile_list_view(){
		return reycore_wc_get_columns('mobile') === 1 && get_theme_mod('woocommerce_catalog_mobile_listview', false);
	}

	public static function is_product(){
		return in_array( get_post_type(), [ 'product', 'product_variation' ], true );
	}

	public static function is_custom_image_height(){
		return ! in_array( get_theme_mod('loop_grid_layout', 'default'), [ 'metro' ]) &&
		get_option('woocommerce_thumbnail_cropping', '1:1') === 'uncropped' &&
		get_theme_mod('custom_image_height', false) === true ;
	}

	public function register_actions( $ajax_manager ) {
		$ajax_manager->register_ajax_action( 'get_loop_skins_list', [$this, 'customizer__get_loop_skins'], 1 );
	}

	public function customizer__get_loop_skins(){
		return apply_filters('reycore/woocommerce/loop/control_skins', $this->get_skins_list());
	}

	public function fix_pagination_current_query_original( $query ){

		if( ! empty( $_REQUEST['product-page'] ) && ($page = absint($_REQUEST['product-page']))  ){
			if ( 1 < $page && ! $query->get('paged') ) {
				$query->set('paged', $page);
			}
		}

	}
}
