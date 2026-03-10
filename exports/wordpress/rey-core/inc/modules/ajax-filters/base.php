<?php
namespace ReyCore\Modules\AjaxFilters;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	/**
	 * Custom Fields key prefix
	 */
	const CF_KEY = 'cf-';

	public function __construct()
	{

		parent::__construct();

		$this->define_constants();
		$this->includes();

		add_action( 'woocommerce_product_query', [ $this, 'woocommerce_product_query'], 20);
		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
		add_action( 'reycore/ajax/register_actions', [ $this, 'register_actions' ] );
		add_action( 'reycore/customizer/panel=woocommerce', [$this, 'load_customizer_options']);
		add_filter( 'woocommerce_is_filtered', [$this, 'is_filtered']);
		add_action( 'acf/save_post', [$this, 'refresh_meta_converted_values'], 10, 2);

		add_action('admin_head', [$this, 'add_widgets_badges_css']);

		new Frontend();
		new ElementorProductsGrid();

	}

	/**
	 * Defind constants for this module.
	 */
	public function define_constants()
	{
		define('REYAJAXFILTERS_PATH', plugin_dir_url( __FILE__ ) );
		define('REYAJAXFILTERS_CACHE_TIME', 60 * 60 * 12 );
	}

	/**
	 * Include required core files.
	 */
	public function includes()
	{
		require_once __DIR__ . '/includes/functions.php';
		require_once __DIR__ . '/includes/ccss.php';

		$widgets = $this->widgets_list();

		foreach( $widgets as $widget ){
			require_once __DIR__ . "/widgets/{$widget}.php";
		}
	}

	/**
	 * Load the customizer options for this module.
	 *
	 * @param \ReyCore\Customizer\OptionsBase $base
	 */
	public function load_customizer_options( $base ){
		$base->register_section( new Customizer() );
	}

	/**
	 * Checks for REY Filtering widgets if they exist
	 * in any of the ecommerce sidebars.
	 *
	 * @return bool
	 */
	public static function filter_widgets_exist(){

		if( apply_filters('reycore/ajaxfilters/pre_widgets_exist', false) ){
			return true;
		}

		static $exists;

		if( is_null($exists) ){

			$exists = false;

			// get sidebar tag
			if( $woo_sidebar = \ReyCore\Plugin::instance()->woocommerce_tags[ 'sidebar' ] ){
				// run through sidebars
				foreach ($woo_sidebar->default_sidebars() as $sidebar_name) {
					// check for rey's filter widgets
					if( self::check_sidebar_for_filters($sidebar_name) ){
						$exists = true;
						break;
					}
				}
			}
		}

		return $exists;
	}

	/**
	 * Check if the sidebar has filtering widgets.
	 *
	 * @param string $sidebar
	 * @return bool
	 */
	private static function check_sidebar_for_filters( $sidebar ) {

		global $_wp_sidebars_widgets, $sidebars_widgets;

		// If loading from front page, consult $_wp_sidebars_widgets rather than options
		// to see if wp_convert_widget_settings() has made manipulations in memory.
		if ( ! is_admin() ) {

			if ( empty( $_wp_sidebars_widgets ) ) {
				$_wp_sidebars_widgets = get_option( 'sidebars_widgets', [] );
			}

			$sidebars_widgets = $_wp_sidebars_widgets;

		} else {
			$sidebars_widgets = get_option( 'sidebars_widgets', [] );
		}

		$sidebar = ( is_int( $sidebar ) ) ? "sidebar-$sidebar" : sanitize_title( $sidebar );

		if( empty( $sidebars_widgets[ $sidebar ] ) ) {
			return false;
		}

		$is_active = false;

		foreach ($sidebars_widgets[ $sidebar ] as $widget) {
			if( strpos($widget, 'reyajfilter-') !== false ){
				$is_active = true;
				break;
			}
		}

		return $is_active;
	}

	/**
	 * Some archive links will get formed with "?product-cato|a" / "attro"
	 * For example in Elementor Menu (with Ajax turned ON). Or for brands, with "attro"
	 * This checks if it's such a link.
	 *
	 * @return bool
	 */
	public static function check_forced_link_parameters(){

		$check = [];

		// for attribute links
		foreach ($_REQUEST as $key => $value) {

			foreach (array_keys(Helpers::get_custom_keys()) as $custom_key) {
				if( $custom_key === $key ){
					$check[] = true;
				}
			}

			// attributes
			if( strpos($key, 'attro-') === 0 ){
				$check[] = true;
			}
			// category & OR
			else if( strpos($key, 'product-cato') === 0 ){
				$check[] = true;
			}
			// category & AND
			else if( strpos($key, 'product-cata') === 0 ){
				$check[] = true;
			}
		}

		return in_array(true, $check, true);
	}

	/**
	 * Check if the page supports filters.
	 *
	 * @return bool
	 */
	public static function supports_filters()
	{
		return self::filter_widgets_exist() || self::check_forced_link_parameters();
	}

	/**
	 * Get the list of widgets for this module.
	 *
	 * @return array
	 */
	public function widgets_list(){
		return [
			'active-filters',
			'attribute-filter',
			'category-filter',
			'featured-filter',
			'price-filter',
			'sale-filter',
			'search-filter',
			'stock-filter',
			'tag-filter',
			'taxonomy-filter',
			'meta-filter',
			'custom-fields-filter',
		];
	}

	/**
	 * Register the assets for the module.
	 *
	 * @param Assets $assets
	 */
	public function register_assets($assets){

		$styles[ 'reycore-ajaxfilter-style' ] = [
			'src'      => REYAJAXFILTERS_PATH . 'assets/css/styles.css',
			'deps'     => [],
			'version'  => REY_CORE_VERSION,
			'priority' => 'mid',
		];

		$styles[ 'reycore-nouislider' ] = [
			'src'      => REYAJAXFILTERS_PATH . 'assets/css/nouislider.css',
			'deps'     => [],
			'version'  => REY_CORE_VERSION,
			'priority' => 'mid',
		];

		$styles[ 'reycore-ajaxfilter-dropdown' ] = [
			'src'      => REYAJAXFILTERS_PATH . 'assets/css/drop-down.css',
			'deps'     => [],
			'version'  => REY_CORE_VERSION,
			'priority' => 'mid',
		];

		$styles[ 'reycore-ajaxfilter-droppanel' ] = [
			'src'      => REYAJAXFILTERS_PATH . 'assets/css/drop-panel.css',
			'deps'     => [],
			'version'  => REY_CORE_VERSION,
			'priority' => 'mid',
		];

		$styles[ 'reycore-ajaxfilter-select2' ] = [
			'src'      => REYAJAXFILTERS_PATH . 'assets/css/select2.css',
			'deps'     => ['rey-form-select2', 'rey-wc-select2'],
			'version'  => REY_CORE_VERSION,
			'priority' => 'mid',
		];

		$styles[ 'reycore-ajaxfilter-apply-btn' ] = [
			'src'      => REYAJAXFILTERS_PATH . 'assets/css/apply-btn.css',
			'deps'     => [],
			'version'  => REY_CORE_VERSION,
			'priority' => 'mid',
		];

		$styles[ 'reycore-ajaxfilter-layered-nav' ] = [
			'src'      => REYAJAXFILTERS_PATH . 'assets/css/layered-nav.css',
			'deps'     => [],
			'version'  => REY_CORE_VERSION,
			'priority' => 'mid',
		];

		$styles[ 'reycore-ajaxfilter-layered-nav-alphabetic' ] = [
			'src'      => REYAJAXFILTERS_PATH . 'assets/css/layered-nav-alphabetic.css',
			'deps'     => [],
			'version'  => REY_CORE_VERSION,
			'priority' => 'mid',
		];

		$styles[ 'reycore-ajaxfilter-layered-nav-search' ] = [
			'src'      => REYAJAXFILTERS_PATH . 'assets/css/layered-nav-search.css',
			'deps'     => [],
			'version'  => REY_CORE_VERSION,
			'priority' => 'mid',
		];

		$styles[ 'reycore-ajaxfilter-price-slider' ] = [
			'src'      => REYAJAXFILTERS_PATH . 'assets/css/price-slider.css',
			'deps'     => [],
			'version'  => REY_CORE_VERSION,
			'priority' => 'mid',
		];

		$styles[ 'reycore-ajaxfilter-range-points' ] = [
			'src'      => REYAJAXFILTERS_PATH . 'assets/css/range-points.css',
			'deps'     => [],
			'version'  => REY_CORE_VERSION,
			'priority' => 'mid',
		];

		$styles[ 'reycore-ajaxfilter-price-custom' ] = [
			'src'      => REYAJAXFILTERS_PATH . 'assets/css/price-custom.css',
			'deps'     => [],
			'version'  => REY_CORE_VERSION,
			'priority' => 'mid',
		];

		$styles[ 'reycore-ajaxfilter-checkbox-filters' ] = [
			'src'      => REYAJAXFILTERS_PATH . 'assets/css/checkbox-filters.css',
			'deps'     => [],
			'version'  => REY_CORE_VERSION,
			'priority' => 'mid',
		];

		$styles[ 'reycore-ajaxfilter-stock' ] = [
			'src'      => REYAJAXFILTERS_PATH . 'assets/css/stock.css',
			'deps'     => [],
			'version'  => REY_CORE_VERSION,
			'priority' => 'mid',
		];

		$assets->register_asset('styles', $styles);

		$grid_containers = implode(',', [
			'.rey-siteMain .reyajfilter-before-products',
			'.elementor-widget-loop-grid .reyajfilter-before-products',
			'.elementor-widget-woocommerce-products .reyajfilter-before-products',
		]);

		$scripts = [
			'reycore-ajaxfilter-script' => [
				'src'     => REYAJAXFILTERS_PATH . 'assets/js/scripts.js',
				'deps'    => ['jquery'],
				'version'   => REY_CORE_VERSION,
				'localize' => [
					'name' => 'reyajaxfilter_params',
					'params' => apply_filters('reycore/ajaxfilters/js_params', [
						'shop_loop_container'  => $grid_containers,
						'not_found_container'  => $grid_containers,
						'pagination_container' => '.woocommerce-pagination',
						'extra_containers'     => [
							'pagecover' => '.rey-pageCover',
							'breadcrumbs' => '.rey-siteMain .rey-breadcrumbs',
							'products_header' => '.rey-siteMain .woocommerce-products-header',
						],
						'animation_type'          => get_theme_mod('ajaxfilter_animation_type', 'default'),
						'sorting_control'         => get_theme_mod('ajaxfilter_product_sorting', true),
						'scroll_to_top'           => get_theme_mod('ajaxfilter_scroll_to_top', true),
						'scroll_to_top_offset'    => get_theme_mod('ajaxfilter_scroll_to_top_offset', 100),
						'scroll_to_top_from'      => get_theme_mod('ajaxfilter_scroll_to_top_from', 'grid'),
						'apply_filter_fixed'      => true,
						'dd_search_threshold'     => 5,
						'prevent_mobile_popstate' => true,
						'page_url'                => reycore__page_url(),
						'minimal_tpl'             => apply_filters('reycore/woocommerce/products/minimal_tpl', true),
						'slider_margin'           => 10,
						'slider_step'             => apply_filters( 'woocommerce_price_filter_widget_step', 1 ),
						'apply_live_results'      => get_theme_mod('ajaxfilter_apply_filter_live', false),
						'reset_filters_text'      => esc_html__('RESET FILTERS', 'rey-core'),
						'reset_filters_link'      => reycore_wc__reset_filters_link(),
						'filter_params'           => self::filters_url_params_list(),
						'panel_keep_open'         => get_theme_mod('ajaxfilter_panel_keep_open', false),
						'shop_page' => esc_url( get_permalink( wc_get_page_id('shop') ) ) ,
					]),
				],
			],

			'reycore-nouislider' => [
				'src'     => REYAJAXFILTERS_PATH . 'assets/js/nouislider.min.js',
				'deps'    => ['jquery', 'reycore-ajaxfilter-script'],
				'version'   => '15.7.1',
			],

			'reycore-ajaxfilter-select2' => [
				'src'     => REYAJAXFILTERS_PATH . 'assets/js/select2.min.js',
				'deps'    => ['jquery', 'reycore-ajaxfilter-script'],
				'version'   => '4.0.13',
			],

			'reycore-ajaxfilter-select2-multi-checkboxes' => [
				'src'     => REYAJAXFILTERS_PATH . 'assets/js/select2-multi-checkboxes.js',
				'deps'    => ['reycore-ajaxfilter-select2'],
				'version'   => '1.0.0',
			],

			'reycore-ajaxfilter-droppanel' => [
				'src'     => REYAJAXFILTERS_PATH . 'assets/js/drop-panel.js',
				'deps'    => ['jquery', 'reycore-ajaxfilter-script'],
				'version'   => '1.0.0',
			],

		];

		$assets->register_asset('scripts', $scripts);

	}

	/**
	 * Load the scripts and styles for the module.
	 */
	public static function load_scripts(){
		reycore_assets()->add_scripts('reycore-ajaxfilter-script');
		reycore_assets()->add_styles(['rey-widgets-lite', 'rey-widgets', 'reycore-ajaxfilter-style']);
	}

	/**
	 * Get a list of the URL parameters used for filtering.
	 *
	 * @return array
	 */
	public static function filters_url_params_list(){

		$list = [
			'keyword',
			'product-cata',
			'product-cato',
			'product-taga',
			'product-tago',
			'attra',
			'attro',
			'max-range',
			'min-range',
			'min-price',
			'max-price',
			'in-stock',
			'on-sale',
			'is-featured',
			'rating_filter',
			'product-meta',
		];

		return array_merge($list, array_keys(Helpers::get_custom_keys()));
	}

	/**
	 * Check if the current query is filtered.
	 *
	 * @param bool $status
	 * @return bool
	 */
	public function is_filtered( $status ){

		$list = self::filters_url_params_list();

		$c = [];

		foreach($_REQUEST as $key => $value){
			if( in_array($key, $list, true) ){
				$c[] = true;
			}
			else {
				if( ! empty( array_filter($list, function($k) use ($key) {
					return strpos($key, $k) === 0;
				} ) ) ){
					$c[] = true;
				}
			}
		}

		if( in_array(true, $c, true) ){
			return true;
		}

		return $status;
	}

	/**
	 * Get the registered taxonomies.
	 *
	 * @return array
	 */
	public static function get_registered_taxonomies(){

		$product_taxonomies = [];

		$excluded = [
			'product_type',
			'product_visibility',
			'product_cat',
			'product_tag',
			'product_shipping_class',
		];

		foreach ( get_object_taxonomies( 'product', 'objects' ) as $taxonomy_slug => $taxonomy ){

			if( in_array($taxonomy_slug, $excluded, true) ){
				continue;
			}

			// exclude standard product taxonomies
			if ( 'pa_' === substr( $taxonomy_slug, 0, 3 ) ) {
				continue;
			}

			$product_taxonomies[] = [
				'id'   => $taxonomy_slug,
				'name' => $taxonomy->label,
			];
		}

		return apply_filters('reycore/ajaxfilters/registered_taxonomies', array_merge(get_theme_mod('ajaxfilters_taxonomies', []), $product_taxonomies));
	}

	/**
	 * Check if the widget should be hidden based on the settings.
	 *
	 * @param array $instance
	 * @return bool
	 */
	public static function should_hide_widget( $instance ){

		// Solution to disable Woo's Attribute lookup table when in Elementor edit mode
		// to avoid getting the `PHP Warning:  Attempt to read property "query_vars" on null in /../plugins/woocommerce/includes/class-wc-query.php on line 852`
		if(
			isset($instance['attr_name']) &&
			! isset(\WC_Query::get_main_query()->query_vars) &&
			class_exists('\Elementor\Plugin') &&
			is_callable( '\Elementor\Plugin::instance' ) &&
			( reycore__elementor_edit_mode() )
		){
			add_filter('option_woocommerce_attribute_lookup_enabled', '__return_false');
		}

		// bail if set to exclude on certain category
		if( !empty($instance['show_only_on_categories']) ) {
			$show_hide = $instance['show_hide_categories'];

			if ( $show_hide === 'hide' && is_tax( 'product_cat', $instance['show_only_on_categories'] ) ){
				return true;
			}
			elseif ( $show_hide === 'show' && !is_tax( 'product_cat', $instance['show_only_on_categories'] ) ){
				return true;
			}
		}

		if( isset($instance['selective_display']) && ($selective_display = array_filter( (array) $instance['selective_display']) ) ){

			if( ! empty($selective_display) ){

				$conditions = [];

				if( in_array('shop', $selective_display, true) ){
					if( is_shop() && ! is_search() ){
						$conditions['shop'] = true;
					}
				}

				if( in_array('cat', $selective_display, true) ){
					if( is_product_category() ){
						$conditions['cat'] = true;
					}
				}

				if( in_array('attr', $selective_display, true) ){
					if( is_product_taxonomy() ){
						$conditions['attr'] = true;
					}
				}

				if( in_array('tag', $selective_display, true) ){
					if( is_product_tag() ){
						$conditions['tag'] = true;
					}
				}

				if( in_array('search', $selective_display, true) ){
					if( is_search() ){
						$conditions['search'] = true;
					}
				}

				// legacy
				if( in_array('cat_attr_tag', $selective_display, true) ){
					if( is_product_category() || is_product_taxonomy() || is_product_tag() ){
						$conditions['cat_attr_tag'] = true;
					}
				}

				if( in_array(true, $conditions, true) ){
					return false;
				}

				return true;

			}

		}

		return apply_filters('reycore/ajaxfilters/should_hide_widget', false, $instance);
	}

	/**
	 * Get the filters query object.
	 *
	 * @return bool|FilterQuery
	 */
	public static function get_the_filters_query(){

		static $filter_query;

		if( is_null($filter_query) ){
			$filter_query = new FilterQuery();
		}

		return $filter_query;
	}

	/**
	 * Get the filters count.
	 *
	 * @return int
	 */
	public static function get_the_filters_count()
	{
		return self::get_the_filters_query()->get_filters_count();
	}

	/**
	 * Set filter for the current query.
	 *
	 * @param wp_query $wp_query
	 */
	public function woocommerce_product_query( $wp_query )
	{

		if( ! self::supports_filters() ){
			return;
		}

		$filter_query = self::get_the_filters_query();

		if( $meta_query = $filter_query->query_for_meta() ){
			$wp_query->set( 'meta_query', $meta_query );
		}

		if( $tax_query = $filter_query->query_for_tax($wp_query) ){
			$wp_query->set( 'tax_query', $tax_query );
		}

		if( $post__in = $filter_query->query_for_post__in() ){
			$wp_query->set('post__in', $post__in);
		}

		if( $filter_data = $filter_query->get_filter_data() ){
			if( isset($filter_data['keyword']) && $keyword = reycore__clean($filter_data['keyword']) ){
				$wp_query->set('s', $keyword);
			}
		}

		if( ( $post__not_in = $filter_query->query_for_post__not_in() ) && !empty($post__not_in) ){

			$wp_query->set('post__not_in', $post__not_in);

			// make sure to exclude not-in's from post__in
			if( $post_in = $wp_query->get('post__in') ){
				$wp_query->set('post__in', array_diff($post_in, $post__not_in) );
			}

		}

		add_filter( 'posts_clauses', [$filter_query, 'product_query_post_clauses_main_query'], 10, 2 );

	}


	public function register_actions( $ajax_manager ){
		$ajax_manager->register_ajax_action( 'filter_get_applied_products', [$this, 'ajax__get_applied_products'], [
			'auth'   => 3,
			'nonce'  => false,
		] );
	}

	public function ajax__get_applied_products( $action_data ){

		if( ! get_theme_mod('ajaxfilter_apply_filter_live', false) ){
			return;
		}

		if ( ! ( isset($action_data['url']) && $filtering_url = reycore__clean(esc_url_raw($action_data['url'])) ) ){
			return;
		}

		$url = parse_url($filtering_url, PHP_URL_QUERY);
		$url_query = [];

		if( ! is_null($url) ){
			parse_str($url, $url_query);
		}

		$filter_query = new FilterQuery($url_query);

		$query_args = [];

		if( $meta_query = $filter_query->query_for_meta() ){
			$query_args['meta_query'] = $meta_query;
		}

		if( $tax_query = $filter_query->query_for_tax() ){
			$query_args['tax_query'] = $tax_query;
		}

		if( $post__in = $filter_query->query_for_post__in() ){
			$query_args['post__in'] = $post__in;
		}

		add_filter( 'posts_clauses', [$filter_query, 'product_query_post_clauses'], 10, 2 );

		$query = new \WP_Query(array_merge([
			'post_status' => 'publish',
			'post_type'   => 'product',
			'fields'      => 'ids',
		], $query_args));

		if( ! isset($query->found_posts) ){
			return;
		}

		return $query->found_posts;
	}

	/**
	 * Refreshes the transients of Custom Fields queries
	 * used for converting data.
	 *
	 * @param int $post_id
	 * @param object $post
	 * @return void
	 */
	public function refresh_meta_converted_values( $post_id ){

		if( ! $post_id ){
			return;
		}

		if( get_post_type($post_id) !== 'product' ){
			return;
		}

		\ReyCore\Helper::clean_db_transient( Helpers::TRANSIENT_KEY_CF_VALUES );

	}

	function add_widgets_badges_css(){

		if( ! reycore__get_props('branding') ){
			return;
		}

		$current_screen = get_current_screen();

		if( ! (isset($current_screen->id) && 'widgets' === $current_screen->id) ){
			return;
		}

		printf('<style>.widget[id*="reyajfilter"] .widget-title h3, .widget[id*="rey_woocommerce_product_categories"] .widget-title h3 { padding-right: 0px; }
		.widget[id*="reyajfilter"] .widget-title h3:after, .widget[id*="rey_woocommerce_product_categories"] .widget-title h3:after { content: \'%s\'; padding: 0.2em 0.5em 0.3em; font-size: 9px; color: #fff; background-color: rgba(218, 41, 28, 0.35); border-radius: 2px; margin-left: auto; font-family: Helvetica, Verdana, sans-serif; float: right; }</style>', REY_CORE_THEME_NAME);
	}

	public static function multicols_css(){
		return '<style>.rey-filterList-cols ul{display:grid;column-gap:10px;grid-template-columns: 1fr 1fr}</style>';
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Ajax Filters for WooCommerce', 'Module name', 'rey-core'),
			'description' => esc_html_x('Provides Ajax filtering capability for Shop and catalog pages.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => ['Product catalog'],
			'help'        => reycore__support_url('kb/ajax-filter-widgets/'),
			'video'       => true,
		];
	}

	public function module_in_use(){


		$in_use = false;

		foreach (wp_get_sidebars_widgets() as $sidebar_widgets) {
			foreach ($sidebar_widgets as $widget) {
				if( strpos($widget, 'reyajfilter-') === 0 ){
					$in_use = true;
					break;
				}
			}
			if( $in_use ){
				break;
			}
		}

		return $in_use;
	}

	/**
	 * @deprecated
	 *
	 * @return array
	 */
	public function get_chosen_filters( $key = null ){ return []; }

	/**
	 * @deprecated
	 *
	 * @return int
	 */
	public function get_active_filters(){ return 0; }

}
