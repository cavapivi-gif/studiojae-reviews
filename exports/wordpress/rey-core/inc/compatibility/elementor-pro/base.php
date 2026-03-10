<?php
namespace ReyCore\Compatibility\ElementorPro;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{
	private $headers = [];
	private $footers = [];

	public $__can_render_tb_template;

	const NOTICE_TRANSIENT__HEADER = 'rey_epro__tb_header';
	const NOTICE_TRANSIENT__FOOTER = 'rey_epro__tb_footer';

	public function __construct()
	{
		add_action( 'elementor_pro/init', [ $this, 'on_elementor_pro_init' ] );
	}

	public function on_elementor_pro_init() {

		$this->misc();

		add_action( 'get_header', [ $this, 'handle_theme_support' ], 8 );
		add_filter( 'rey/header/header_classes', [$this, 'header_classes']);
		add_filter( 'rey/footer/footer_classes', [$this, 'footer_classes']);
		add_filter( 'reycore/customizer/pre_text/rey-hf-global-section', [$this, 'customizer_option_tweak'], 20, 2);

		// WooCommerce
		add_filter( 'reycore/woocommerce_cart_item_remove_link', [$this, 'remove_mini_cart_remove_btn']);
		add_action( 'elementor/widget/before_render_content', [$this, 'loop_props_in_elements']);
		add_action( 'elementor/widget/before_render_content', [$this, 'single_elements']);

		// Update Columns controls
		add_action( 'elementor/element/woocommerce-products/section_content/before_section_end', [$this, 'update_columns_control']);
		add_action( 'elementor/element/woocommerce-product-related/section_related_products_content/before_section_end', [$this, 'update_columns_control']);
		add_action( 'elementor/element/woocommerce-product-upsell/section_upsell_content/before_section_end', [$this, 'update_columns_control']);

		add_filter( 'reycore/ajaxfilters/js_params', [$this, 'handle_ajax_filters'], 20);
		add_filter( 'reycore/load_more_pagination_args', [$this, 'handle_pagination'], 20);
		add_filter('rey/site_content_classes', [$this, 'handle_product_post_classes']);

		add_action( 'elementor/element/product/document_settings/before_section_end', [$this, 'add_theme_container_control'], 10);
		add_action( 'elementor/element/product-archive/document_settings/before_section_end', [$this, 'add_theme_container_control'], 10);
		add_action( 'elementor/element/single-post/document_settings/before_section_end', [$this, 'add_theme_container_control'], 10);

		add_action( 'elementor/theme/before_do_single', [$this, 'load_single_assets']);
		add_action( 'elementor/theme/before_do_archive', [$this, 'load_archive_assets']);

		add_action( 'elementor/theme/before_do_single', [$this, 'location_before']);
		add_action( 'elementor/theme/after_do_single', [$this, 'location_after']);
		add_action( 'elementor/theme/before_do_archive', [$this, 'location_before']);
		add_action( 'elementor/theme/after_do_archive', [$this, 'location_after']);

		add_action( 'elementor/page_templates/header-footer/before_content', [$this, 'tb_template_before_content']);
		add_action( 'elementor/page_templates/header-footer/after_content', [$this, 'tb_template_after_content']);

		// add_action( 'elementor/frontend/widget/before_render', [$this, 'before_render'], 10);
		// add_action( 'elementor/frontend/widget/after_render', [$this, 'after_render'], 10);

		new LoopTemplates();
	}

	private function get_theme_builder_module() {
		return \ElementorPro\Modules\ThemeBuilder\Module::instance();
	}

	private function get_theme_support_instance() {
		$module = $this->get_theme_builder_module();
		return $module->get_component( 'theme_support' );
	}

	public function handle_theme_support() {

		if( 'none' === get_theme_mod('header_layout_type', 'default') ){
			return;
		}

		$module = $this->get_theme_builder_module();
		$conditions_manager = $module->get_conditions_manager();

		$this->headers = $conditions_manager->get_documents_for_location( 'header' );
		$this->footers = $conditions_manager->get_documents_for_location( 'footer' );

		$this->remove_action( 'header' );
		$this->remove_action( 'footer' );

		$this->add_support();
	}

	public function remove_action( $action ) {

		if( ! apply_filters('rey/elementor/pro/remove_instances', true) ){
			return;
		}

		$handler = 'get_' . $action;
		$instance = $this->get_theme_support_instance();
		remove_action( $handler, [ $instance, $handler ] );
	}

	public function do_header(){
		$module = $this->get_theme_builder_module();
		$location_manager = $module->get_locations_manager();
		$location_manager->do_location( 'header' );
	}

	public function do_footer(){
		$module = $this->get_theme_builder_module();
		$location_manager = $module->get_locations_manager();
		$location_manager->do_location( 'footer' );
	}

	public function add_support(){

		$header_tr = get_transient(self::NOTICE_TRANSIENT__HEADER);

		if ( ! empty( $this->headers ) && function_exists('rey__header__content') ) {

			if( ! $header_tr ){
				set_transient(self::NOTICE_TRANSIENT__HEADER, true, MONTH_IN_SECONDS);
			}

			remove_action('rey/header/content', 'rey__header__content');
			add_action('rey/header/content', [$this, 'do_header']);
			add_filter('reycore/header/display', '__return_false');
		}
		else {
			if( $header_tr ){
				delete_transient(self::NOTICE_TRANSIENT__HEADER);
			}
		}

		$footer_tr = get_transient(self::NOTICE_TRANSIENT__FOOTER);

		if ( !empty( $this->footers ) && function_exists('rey_action__footer__content') ) {

			if( ! $footer_tr ){
				set_transient(self::NOTICE_TRANSIENT__FOOTER, true, MONTH_IN_SECONDS);
			}

			remove_action('rey/footer/content', 'rey_action__footer__content');
			add_action('rey/footer/content', [$this, 'do_footer']);
			add_filter('reycore/footer/display', '__return_false');
		}
		else {
			if( $footer_tr ){
				delete_transient(self::NOTICE_TRANSIENT__FOOTER);
			}
		}
	}

	public function header_classes($classes){

		if( !empty( $this->headers ) && isset($classes['layout']) ){
			$classes['layout'] = 'rey-siteHeader--custom';
		}

		return $classes;
	}

	public function footer_classes($classes){

		if( !empty( $this->footers ) && isset($classes['layout']) ){
			$classes['layout'] = 'rey-siteFooter--custom';
		}

		return $classes;
	}

	function customizer_option_tweak($text, $name){

		if ( get_transient(self::NOTICE_TRANSIENT__HEADER) && $name === 'header_layout_type' ) {
			$text .= sprintf('<p class="rey-precontrol-wrap">%s</p>', __('This option is not available because <strong>Elementor Pro</strong> has a <em><strong>Theme Builder</strong> - Header template</em> published which overrides these options.', 'rey-core'));
		}

		else if ( get_transient(self::NOTICE_TRANSIENT__FOOTER) && $name === 'footer_layout_type' ) {
			$text .= sprintf('<p class="rey-precontrol-wrap">%s</p>', __('This option is not available because <strong>Elementor Pro</strong> has a <em><strong>Theme Builder</strong> - Footer template</em> published which overrides these options.', 'rey-core'));
		}

		return $text;
	}

	function misc(){

		new WoocommerceWidgets;

		// make search form element use the product catalog template
		add_action('elementor_pro/search_form/after_input', function(){
			echo '<input type="hidden" name="post_type" value="product">';
		});

	}

	function add_theme_container_control($element){

		$element->add_control(
			'rey_page_template_canvas',
			[
				'label' => esc_html__( 'Use Theme Container', 'rey-core' ) . \ReyCore\Elementor\Helper::rey_badge(),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'return_value' => 'rey',
				'condition' => [
					'page_template!' => 'elementor_canvas',
				],
				'description' => sprintf(__('If enabled, Rey will contain the page content into its Container (~1440px). Please make sure this template\'s backend Template is <a href="%1$s" target="_blank">set on Default</a> (because it is not synced with the Page Layout option just above this switcher).', 'rey-core'), 'https://d.pr/i/nbHgaV')
			]
		);

	}

	function location_before( $instance ){

		$template_id = self::get_tb_template_id();

		if( $template_id ){
			add_filter('reycore/elementor/product_grid/lazy_load_qid', function( $value ) use ($template_id){
				return $template_id;
			});
			add_filter('reycore/elementor/product_grid/load_more_qid', function( $value ) use ($template_id){
				return $template_id;
			});
		}

		do_action('reycore/elementor/before_tb_location', $template_id);
	}

	function location_after( $instance ){

		add_action( 'reycore/woocommerce/loop_components/add', function($instance){

			if( $c = $instance->get_component('filter_button') ){
				$c->set_status( get_theme_mod('ajaxfilter_shop_sidebar_mobile_offcanvas', true) );
			}

		});

	}

	function load_single_assets(){
		reycore_assets()->add_styles(['rey-wc-product', 'rey-wc-product-lite', 'rey-wc-product-gallery']);
	}

	function load_archive_assets(){
		do_action('reycore/woocommerce/loop/scripts');
	}

	public static function get_tb_template_id( $return_id = true ){

		$manager = \ElementorPro\Plugin::instance()->modules_manager->get_modules( 'theme-builder' )->get_conditions_manager();

		$template = false;

		if( $single = $manager->get_documents_for_location( 'single' ) ){
			$template = end($single);
		}

		if( $archive = $manager->get_documents_for_location( 'archive' ) ){
			$template = end($archive);
		}

		if( ! $template ){
			return;
		}

		if( ! $return_id ){
			return $template->get_post();
		}

		if( ! ($template_id = $template->get_post()->ID) ){
			return;
		}

		return $template_id;
	}

	public function can_render_tb_template(){

		if( isset($this->__can_render_tb_template) ){
			return $this->__can_render_tb_template;
		}

		if( ! ($template_id = self::get_tb_template_id()) ){
			return;
		}

		if( ! ( $meta = get_post_meta( $template_id, '_elementor_page_settings', true ) ) ){
			return;
		}

		if( ! (isset($meta['rey_page_template_canvas']) && 'rey' === $meta['rey_page_template_canvas']) ){
			return;
		}

		return $this->__can_render_tb_template = true;
	}

	public function tb_template_before_content(){

		if( ! $this->can_render_tb_template() ){
			return;
		}

		add_filter('reycore/woocommerce/loop/render/filter_top_sidebar', '__return_false');

		add_filter('rey/site_container/classes', function( $classes	){
			$classes[] = '--use-theme-container';
			return $classes;
		});

		if (function_exists('rey_action__before_site_container')){
			rey_action__before_site_container();
		}
	}

	public function tb_template_after_content(){

		if( ! $this->can_render_tb_template() ){
			return;
		}

		if (function_exists('rey_action__after_site_container')){
			rey_action__after_site_container();
		}
	}

	/**
	 * WooCommerce
	 */


	public function update_columns_control( $element ){

		$controls_manager = \Elementor\Plugin::instance()->controls_manager;
		// 'prefix_class' => 'elementor-grid%s-',

		$devices = \Elementor\Plugin::$instance->breakpoints->get_active_devices_list( [ 'reverse' => true ] );

		foreach ( $devices as $device_name ) {
			$id_suffix = \Elementor\Core\Breakpoints\Manager::BREAKPOINT_KEY_DESKTOP === $device_name ? '' : '_' . $device_name;

			$columns = $controls_manager->get_control_from_stack( $element->get_unique_name(), 'columns' . $id_suffix );
			if( $columns && ! is_wp_error($columns) ){
				$columns['selectors']['{{WRAPPER}} ul.products'] = '--woocommerce-grid-columns:{{VALUE}};';
				$element->update_control( 'columns' . $id_suffix, $columns );
			}
		}

	}

	/**
	 * Run loop props for Upsell & Related
	 *
	 * @since 1.3.2
	 */
	function loop_props_in_elements( $element ){


		$widgets = [
			'woocommerce-products',
			'woocommerce-product-related',
			'woocommerce-product-upsell',
			'wc-archive-products',
		];

		$widget_name = $element->get_unique_name();

		if( ! in_array($widget_name, $widgets) ){
			return;
		}

		$settings = $element->get_settings_for_display();

		$widget_name_clean = str_replace('-', '_', $widget_name);
		$widget_function = "el_widget__{$widget_name_clean}";

		if( method_exists($this, $widget_function ) ){
			$this->$widget_function($settings);
		}

		// Make sure to change cols
		if( isset($settings['columns']) ){
			wc_set_loop_prop('columns', $settings['columns']);
		}

		add_filter('reycore/woocommerce/columns', function($breakpoints) use ($settings){

			$breakpoints['tablet'] = 3;
			$breakpoints['mobile'] = 2;

			if( isset($settings['columns']) && $desktop = $settings['columns']){
				$breakpoints['desktop'] = $desktop;
			}

			if( isset($settings['columns_tablet']) && $tablet = $settings['columns_tablet']){
				$breakpoints['tablet'] = $tablet;
			}

			if( isset($settings['columns_mobile']) && $mobile = $settings['columns_mobile']){
				$breakpoints['mobile'] = $mobile;
			}

			return $breakpoints;
		});

		// Allow product classes in E templates
		add_filter( 'reycore/woocommerce/loop/prevent_custom_css_classes', function ( $status ){
			if( \Elementor\Plugin::$instance->editor->is_edit_mode() ){
				return false;
			}
			return $status;
		}, 10 );

	}

	/**
	 * Fix for EPRO custom mini-cart skin
	 *
	 * @since 1.3.1
	 */
	public function remove_mini_cart_remove_btn( $html ){

		$use_mini_cart_template = get_option( 'elementor_use_mini_cart_template', 'no' );

		if ( 'yes' === $use_mini_cart_template ) {
			return false;
		}

		return $html;
	}

	function single_elements( $element )
	{
		if( $element->get_unique_name() === 'woocommerce-product-add-to-cart' ){
			reycore_assets()->add_styles(['rey-wc-product-lite','rey-wc-product']);
			reycore_assets()->add_scripts(['reycore-wc-product-page-general', 'reycore-wc-product-page-qty-controls', 'reycore-wc-product-page-qty-select']);
		}
	}

	function handle_product_post_classes($classes){

		if( isset($classes['template_type'])
			&& $classes['template_type'] === '--tpl-elementor_header_footer'
			&& is_singular('product')
		){
			$classes['post_type'] = 'product';
		}

		return $classes;
	}

	function handle_pagination($params){

		if( isset($params['target']) ){
			$params['target'] = $params['target'] . ', .elementor-widget-wc-archive-products ul.products, .elementor.elementor-location-archive .reyEl-productGrid ul.products, .elementor-widget-woocommerce-products ul.products';
		}

		return $params;
	}

	/**
	 * Extend the filters container for Elementor Pro's widgets
	 *
	 * @param array $params
	 * @return array
	 */
	function handle_ajax_filters($params){

		$selectors = [
			'.elementor-widget-wc-archive-products .reyajfilter-before-products',
			'.elementor.elementor-location-archive .reyajfilter-before-products',
			'.reyEl-productGrid.--show-header .reyajfilter-before-products',
			'.elementor-widget-woocommerce-products .reyajfilter-before-products',
			'[data-widget_type="loop-grid.product"] .elementor-loop-container',
		];

		if( isset($params['shop_loop_container']) ){
			$params['shop_loop_container'] =  implode(',', array_merge([$params['shop_loop_container']], $selectors));
		}
		if( isset($params['not_found_container']) ){
			$params['not_found_container'] =  implode(',', array_merge([$params['not_found_container']], $selectors));
		}

		return $params;
	}

}
