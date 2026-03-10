<?php
namespace ReyCore\Modules\AjaxFilters;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Handler for Product Grid element, integration
 * with product filters
 *
 * @since 1.5.4
 */
class ElementorProductsGrid
{

	const PG_INSTANCE = 'rey__product_grid_instance';

	const PG_QUERY_DATA = 'rey__product_grid_sidebar_query';

	const PG_ELEMENT_TYPE = 'reycore-product-grid';

	const OPTION_KEY = 'rey_';

	public $__already_set_product_query;

	public function __construct()
	{
		add_action( 'elementor/element/reycore-product-grid/section_layout/before_section_end', [$this,'adds_element_options']);
		add_action( 'reycore/ajaxfilters/before_widget_controls', [$this, 'notice_elementor_mode_widgets']);
		add_action( 'elementor/frontend/widget/before_render', [$this, 'before_render_elements']);
		add_action( 'reycore/filters_sidebar/before_widgets', [$this, 'before_filter_panel_widgets']);
		add_action( 'reycore/elementor/product_grid/query', [$this, 'set_query_for_sidebar']);
		add_filter( 'reycore/elementor/product_grid/query_args', [$this, 'override_product_grid_query'], 10, 3);
		add_filter( 'elementor/query/query_args', [$this, 'override_epro_loop_grid_product_grid_query_args'], 20, 2);
		add_filter( 'reycore/ajaxfilters/query/on_sale', [$this, 'check_on_sale']);
		add_filter( 'woocommerce_get_filtered_term_product_counts_query', [$this, 'fix_sale_counters']); // wc_container
		add_filter( 'reycore/woocommerce_get_filtered_term_product_counts_query', [$this, 'fix_sale_counters']);
		add_filter( 'reycore/ajaxfilters/query/featured', [$this, 'check_featured']);
		add_filter( 'body_class', [$this, 'body_classes']);
		add_filter( 'reycore/woocommerce/reset_filters_link', [$this, 'reset_filters_link']);
		add_filter( 'reycore/woocommerce/filter_button/custom_sidebar', [$this, 'filter_button_custom_sidebar']);
		add_filter( 'theme_mod_ajaxfilter_active_position', [$this, 'show_active_filters']);
		add_action( 'dynamic_sidebar_after', [ $this, 'sidebar_add_scripts' ] );
	}

	/**
	 * Sets the Product Query to be picked up by the sidebar
	 * and set the product query for the filters.
	 *
	 * @param object $element
	 * @return void
	 */
	public function set_query_for_sidebar( $element ){

		// must be a normal page
		if( ! $this->__is_page() ){
			return;
		}

		remove_filter( 'posts_clauses', [Base::get_the_filters_query(), 'product_query_post_clauses'], 10, 2 );

		if( ! ( isset($element->_settings['paginate']) && $element->_settings['paginate'] === 'yes' ) ){
			return;
		}

		if( $element->query ){

			foreach ([
				'query_type',
				'show_filter_button',
				'filter_button_sidebar',
				'show_active_filters',
			] as $setting_key) {
				if( isset($element->_settings[$setting_key]) && ($setting = $element->_settings[$setting_key]) ){
					$element->query->set( self::OPTION_KEY . $setting_key, $setting );
				}
			}

			$GLOBALS[self::PG_QUERY_DATA] = $element->query;
		}

	}

	/**
	 * Before rendering elements
	 *
	 * @param object $element
	 * @return void
	 */
	public function before_render_elements( $element ){
		$this->before_render_product_grid($element);
		$this->before_render_sidebar($element);
	}

	/**
	 * Before rendering the Product Grid.
	 * Checks if sidebar has been processed first,
	 * and override the product grid output.
	 *
	 * @param object $element
	 * @return void
	 */
	public function before_render_product_grid( $element ){

		// must have the product archive lib available
		if( ! class_exists('\ReyCore\WooCommerce\Tags\ProductArchive') ){
			return;
		}

		if( $element->get_unique_name() !== self::PG_ELEMENT_TYPE ){
			return;
		}

		// should continue only if the Sidebar happened before
		// and already created the element's instance and output
		if( ! isset($GLOBALS[self::PG_INSTANCE]) ){
			return;
		}

		// must be a normal page
		if( ! $this->__is_page() ){
			return;
		}

		if( $element->get_settings_for_display('paginate') !== 'yes' ){
			return;
		}

		// Override the Product grid's output
		add_filter('reycore/elementor/product_grid/custom_output', function($output, $element) {

			if( $GLOBALS[self::PG_INSTANCE]['id'] !== $element->get_id() ){
				return $output;
			}

			return $GLOBALS[self::PG_INSTANCE]['output'];

		}, 10, 2);

	}

	/**
	 * Before rendering the sidebar.
	 * Creates an instance of the Product Grid to get the product query data
	 * and set its output to prevent re-rendering.
	 *
	 * @param object $element
	 * @return void
	 */
	public function before_render_sidebar( $element ){

		if( ! (class_exists('\Elementor\Plugin') && is_callable( '\Elementor\Plugin::instance' )) ){
			return;
		}

		// must have the product archive lib available
		if( ! class_exists('\ReyCore\WooCommerce\Tags\ProductArchive') ){
			return;
		}

		if( $element->get_unique_name() !== 'sidebar' ){
			return;
		}

		$settings = $element->get_settings();

		// must have a sidebar name
		if( ! (isset($settings['sidebar']) && ($sidebar = $settings['sidebar'])) ){
			return;
		}

		// The product query has already been set, so no need to continue.
		// Product Grid is already rendered, because we have the query data.
		if( isset($this->__already_set_product_query) && $this->__already_set_product_query ){
			return;
		}

		// must be a normal page
		if( ! $this->__is_page() ){
			return;
		}

		// the Product Grid element has already been rendered,
		// so just set the Woo. Product Query and stop.
		if( isset($GLOBALS[self::PG_QUERY_DATA]) ){
			WC()->query->product_query( $GLOBALS[self::PG_QUERY_DATA] );
			$this->__already_set_product_query = true;
			return;
		}

		// already rendered the Product Grid element,
		// so basically the Woo. Product Query has already been set.
		if( isset($GLOBALS[self::PG_INSTANCE]) ){
			return;
		}

		// get page ID
		$page_id = get_the_ID();

		// get the document object
		$document = \Elementor\Plugin::$instance->documents->get( $page_id );

		// stop if no document object
		if ( ! $document ) {
			return;
		}

		// get the page's elements data
		$elements_data = $document->get_elements_data();

		// stop if no elements data
		if ( empty( $elements_data ) ) {
			return;
		}

		$pg_elements = [];

		// Collect product grids
		\Elementor\Plugin::$instance->db->iterate_data( $elements_data, function( $element ) use (&$pg_elements) {

			if( empty( $element['widgetType'] ) ){
				return;
			}

			if ( $element['widgetType'] !== self::PG_ELEMENT_TYPE ) {
				return;
			}

			if( ! (isset( $element['settings']['paginate'] ) && $element['settings']['paginate'] !== '') ){
				return;
			}

			$pg_elements[] = $element;
		});

		// stop if page doesn't have any product grid elements
		if( empty($pg_elements) ){
			return;
		}

		/**
		 * Get the first one only.
		 * @var \ReyCore\Elementor\Widgets\ProductGrid $instance
		 */
		$instance = \Elementor\Plugin::instance()->elements_manager->create_element_instance( $pg_elements[0] );

		// prepare the output
		ob_start();
		$instance->print_element();
		$pg_output = ob_get_clean();

		// no output, so stop
		if( ! $pg_output ){
			return;
		}

		// retrieve the product archive instance
		$product_archive = $instance->get_product_archive();

		// set the query if available
		if( isset($product_archive->query) && ($product_archive_query = $product_archive->query) ){
			// sets the Product Query (for the sidebar)
			WC()->query->product_query( $product_archive_query );
		}

		$GLOBALS[ self::PG_INSTANCE ] = [
			'output'   => $pg_output,
			'id'       => $instance->get_id(),
			'settings' => $instance->get_settings_for_display(),
		];

	}

	public function before_filter_panel_widgets(){

		if( ! (isset($GLOBALS[self::PG_QUERY_DATA]) && ($pg_query = $GLOBALS[self::PG_QUERY_DATA])) ){
			return;
		}

		WC()->query->product_query( $pg_query );

		add_filter('reycore/ajaxfilters/tax_query/can_use_main', '__return_true');
		add_filter('reycore/ajaxfilters/meta_query/can_use_main', '__return_true');
	}

	public static function is_filtering_and_has_active_filters(){

		// no parameters set, so just stop.
		if( empty($_REQUEST) ){
			return;
		}

		// check if filtered
		if( ! is_filtered() ){
			return;
		}

		// if nothing, just bail
		if( ! ( $filters = Base::get_the_filters_query()->get_filter_data() ) ){
			return;
		}

		if( empty($filters) ){
			return;
		}

		return $filters;
	}

	public static function parse_query_args($query_vars, $filters){

		$filter_query = Base::get_the_filters_query();

		$vars_from_url = array_filter([
			'meta_query'   => $filter_query->query_for_meta(),
			'tax_query'    => $filter_query->query_for_tax(),
			'post__in'     => $filter_query->query_for_post__in(),
			'post__not_in' => $filter_query->query_for_post__not_in()
		]);

		if( ! empty( $vars_from_url['post__not_in'] ) ){
			if( isset($vars_from_url['post__in']) && ! empty($vars_from_url['post__in']) ){
				$vars_from_url['post__in'] = array_diff($vars_from_url['post__in'], $vars_from_url['post__not_in']);
			}
			if( isset($query_vars['post__in']) && ! empty($query_vars['post__in']) ){
				$query_vars['post__in'] = array_diff($query_vars['post__in'], $vars_from_url['post__not_in']);
			}
		}

		$query_vars = reycore__wp_parse_args( $vars_from_url, $query_vars );

		if(
			isset($filters['orderby']) &&
			$orderby = $filters['orderby']
		){
			if( strpos($orderby, 'price') !== false ){
				$query_vars['orderby'] = 'price';
				$query_vars['order'] = $orderby === 'price-desc' ? 'DESC' : 'ASC';
			}
		}

		return $query_vars;
	}

	/**
	 * Must override the Product Grid's query vars
	 * to match the active selected filters.
	 *
	 * @param array $query_vars
	 * @param string $id
	 * @param array $settings
	 * @return array
	 */
	public function override_product_grid_query($query_vars, $id, $settings )
	{

		// only on Paginated Product grid instances
		if( ! ( isset($settings['paginate']) && $settings['paginate'] === 'yes' ) ){
			return $query_vars;
		}

		if( ! ($filters = self::is_filtering_and_has_active_filters()) ){
			return $query_vars;
		}

		add_filter( 'posts_clauses', [Base::get_the_filters_query(), 'product_query_post_clauses'], 10, 2 );

		return self::parse_query_args($query_vars, $filters);

	}

	public function override_epro_loop_grid_product_grid_query_args($query_vars, $widget){

		if( $widget->get_unique_name() !== 'loop-grid' ){
			return $query_vars;
		}

		if( 'product' !== $query_vars['post_type'] ){
			return $query_vars;
		}

		if( ! ($filters = self::is_filtering_and_has_active_filters()) ){
			return $query_vars;
		}

		return self::parse_query_args($query_vars, $filters);
	}

	/**
	 * Fix the counters of filters when Sale is active.
	 *
	 * @param array $query
	 * @return array
	 */
	public function fix_sale_counters( $query ){

		if( ! (isset($GLOBALS[self::PG_QUERY_DATA]) && ($pg_query = $GLOBALS[self::PG_QUERY_DATA])) ){
			return $query;
		}

		if( ! (($query_type = $pg_query->get( self::OPTION_KEY . 'query_type')) && 'sale' === $query_type) ){
			return $query;
		}

		global $wpdb;

		$filter_query = Base::get_the_filters_query();

		// Need to somehow pass the counted term's ID,
		// for the out of stock variation query
		$on_sale_products = $filter_query->get_onsale_products([
			'force_chosen_filters' => true,
			'out_of_stock_variations' => true,
			// 'out_of_stock_variations' => false,
		]);

		$on_sale = sprintf("{$wpdb->posts}.ID IN (%s)", trim(implode(',', $on_sale_products)));

		$query['where'] .= ' AND ' . $on_sale;

		return $query;
	}

	/**
	 * Checks if PG query is for discounted products only
	 *
	 * @return bool
	 */
	public function check_on_sale( $status ){

		if( ! (isset($GLOBALS[self::PG_QUERY_DATA]) && ($pg_query = $GLOBALS[self::PG_QUERY_DATA])) ){
			return $status;
		}

		if( ! (($query_type = $pg_query->get( self::OPTION_KEY . 'query_type')) && 'sale' === $query_type) ){
			return $status;
		}

		return true;

	}

	/**
	 * Checks if PG is featured
	 */
	public function check_featured($status){

		if( ! (isset($GLOBALS[self::PG_QUERY_DATA]) && ($pg_query = $GLOBALS[self::PG_QUERY_DATA])) ){
			return $status;
		}

		if( ! (($query_type = $pg_query->get( self::OPTION_KEY . 'query_type')) && 'featured' === $query_type) ){
			return $status;
		}

		return true;
	}

	/**
	 * Overrides the filters button
	 *
	 * @param string $opt
	 * @return string
	 */
	public function filter_button_custom_sidebar( $opt ){

		if( ! (isset($GLOBALS[self::PG_QUERY_DATA]) && ($pg_query = $GLOBALS[self::PG_QUERY_DATA])) ){
			return $opt;
		}

		if( '' === $pg_query->get( self::OPTION_KEY . 'show_filter_button', '') ){

			// stop filter panel from showing up since there's not specified to
			add_filter( 'reycore/woocommerce/loop/can_add_filter_panel_sidebar', '__return_false' );

			return $opt;
		}

		if( ! ($filter_button_sidebar = $pg_query->get( self::OPTION_KEY . 'filter_button_sidebar', '')) ){
			return $opt;
		}

		return $filter_button_sidebar;
	}

	/**
	 * Overrides the Active filters option
	 *
	 * @param string $opt
	 * @return string
	 */
	public function show_active_filters( $opt ){

		if( ! (isset($GLOBALS[self::PG_QUERY_DATA]) && ($pg_query = $GLOBALS[self::PG_QUERY_DATA])) ){
			return $opt;
		}

		if( '' === $pg_query->get( self::OPTION_KEY . 'show_filter_button', '') ){
			return $opt;
		}

		if( ! ($show_active_filters = $pg_query->get( self::OPTION_KEY . 'show_active_filters', '')) ){
			return $opt;
		}

		return $show_active_filters;
	}

	public function sidebar_add_scripts(){

		if( ! (class_exists('\Elementor\Plugin') && is_callable( '\Elementor\Plugin::instance' )) ){
			return;
		}

		if( ! Base::supports_filters() ){
			return;
		}

		if( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			return;
		}

		if ( ! isset($GLOBALS[self::PG_QUERY_DATA]) ){
			return;
		}

		Base::load_scripts();
	}

	/**
	 * Safety class for Woo
	 *
	 * @param array $classes
	 * @return array
	 */
	public function body_classes( $classes ){

		// must be a normal page
		if( $this->__is_page() ){
			$classes[] = 'woocommerce';
		}

		return $classes;
	}

	/**
	 * Points the reset filters link to the correct current page
	 *
	 * @param string $link
	 * @return string
	 */
	public function reset_filters_link( $link ){

		// must be a normal page
		if( ! $this->__is_page() ){
			return $link;
		}

		if( ! Base::supports_filters() ){
			return $link;
		}

		return get_permalink();
	}


	public function adds_element_options( $element ){

		$element->start_injection( [
			'of' => 'show_header',
		] );

		$element->add_control(
			'show_filter_button',
			[
				'label' => __( 'Show Filter Button', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [
					'paginate!' => '',
					'show_header!' => '',
					'_skin' => '',
				],
			]
		);

		$element->add_control(
			'filter_button_sidebar',
			[
				'label' => __( 'Sidebar to Open', 'rey-core' ),
				'default' => '',
				'condition' => [
					'paginate!' => '',
					'show_header!' => '',
					'_skin' => '',
					'show_filter_button!' => '',
				],
				'type' => 'rey-ajax-list',
				'query_args' => [
					'request' => 'get_button_sidebars',
				],
			]
		);


		$element->add_control(
			'show_active_filters',
			[
				'label' => esc_html__( 'Show Active Filters', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default'     => '',
				'options'     => [
					'' => esc_html__( 'None (manually added)', 'rey-core' ),
					'above_grid' => esc_html__( 'Above product grid', 'rey-core' ),
					'above_header' => esc_html__( 'Above grid header', 'rey-core' ),
				],
				'condition' => [
					'paginate!' => '',
					'show_header!' => '',
					'_skin' => '',
				],
			]
		);

		$element->end_injection();
	}


	/**
	 * Checks if it's a basic page.
	 *
	 * @return bool
	 */
	public function __is_page(){

		// must be a normal page
		if( ! is_page() ){
			return;
		}

		// must be a normal page
		if( is_post_type_archive('product') || is_tax( get_object_taxonomies('product') ) ){
			return;
		}

		return true;
	}

	/**
	 * Shows a notice in the widgets
	 * if added directly in Elementor mode.
	 *
	 * @return void
	 */
	public function notice_elementor_mode_widgets(){

		if( ! (isset($_REQUEST['action']) && $_REQUEST['action'] === 'elementor_ajax') ){
			return;
		}

		printf('<div class="elementor-control-raw-html rey-raw-html --warning">%s</div>', _x('Please don\'t add widgets directly into the page and instead use the <strong>Sidebar</strong> Element.', 'Elementor control label', 'rey-core'));
	}

}
