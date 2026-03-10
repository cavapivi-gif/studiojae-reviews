<?php
namespace ReyCore\Elementor;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class WidgetsAssets
{

	private static $__count = 0;
	private static $__able_to_collect;
	private static $__started;
	private static $__limit = 1;
	private static $__tpl_id;

	private $inline_already_added = [];

	public function __construct()
	{

		add_action( 'reycore/assets/register_scripts', [$this, 'register_widgets_assets']);
		add_action( 'elementor/element/before_parse_css', [$this, 'add_inline_widgets_styles_to_post_file'], 0, 2 );
		add_action( 'elementor/element/parse_css', [$this, 'add_section_custom_css_to_post_file'], 10, 2 );

		add_action( 'reycore/frontend/section/after_render', [$this, 'after_render'], 10, 2);
		add_action( 'reycore/frontend/container/after_render', [$this, 'after_render'], 10, 2);
		add_action( 'wp_body_open', [$this, 'set_above_fold_start']);
		add_action( 'rey/after_site_wrapper', [$this, 'after_site_wrapper']);
		add_action( 'elementor/frontend/before_get_builder_content', [$this, 'before_get_builder_content']);
		add_action( 'reycore/templates/tpl/before_header', [$this, 'set_custom_template_id'], 10, 2);
		add_action( 'reycore/elementor/before_tb_location', [$this, 'set_theme_builder_template_id']);
		add_action( 'rey/before_footer', [$this, 'delay_footer_styles__start']);
		add_action( 'rey/after_footer', [$this, 'delay_footer_styles__end']);

		self::$__limit = apply_filters('reycore/elementor/page_collect_styles/limit', self::$__limit, $this);

	}

	/**
	 * Register elements widgets assets
	 *
	 * @since 2.0.0
	 */
	public function register_widgets_assets( $assets ){

		$styles = [];

		$source_styles = \ReyCore\Plugin::instance()->elementor->widgets->widgets_styles;

		// load the inlined styles too (when in edit mode)
		if( reycore__elementor_edit_mode() ){
			$source_styles = array_merge($source_styles, \ReyCore\Plugin::instance()->elementor->widgets->inline_widgets_styles_paths);
		}

		foreach ( $source_styles as $id => $style ) {

			$styles[ Widgets::ASSET_PREFIX . $id ] = [
				'src'     => $style,
				'deps'    => [],
				'version' => REY_CORE_VERSION,
			];

		}

		$assets->register_asset('styles', $styles);

		$scripts = [];

		foreach ( \ReyCore\Plugin::instance()->elementor->widgets->widgets_scripts as $id => $js_data ) {
			$scripts[ Widgets::ASSET_PREFIX . $id ] = [
				'src'     => $js_data['path'],
				'deps'    => array_merge(['elementor-frontend', 'reycore-elementor-frontend'], $js_data['dependencies']),
				'version'   => REY_CORE_VERSION,
			];
		}

		$assets->register_asset('scripts', $scripts);
	}

	/**
	 * Appends Section custom css to stylesheet
	 *
	 * @param object $post_css
	 * @param object $element
	 * @return string
	 */
	public function add_section_custom_css_to_post_file( $post_css, $element ){

		if ( $post_css instanceof \Elementor\Core\DynamicTags\Dynamic_CSS ) {
			return;
		}

		$rey_custom_css = $element->get_settings('rey_custom_css');

		if( ! ($css = trim( $rey_custom_css )) ) {
			return;
		}

		$type = $element->get_type();
		$unique_selector = $post_css->get_element_unique_selector( $element );

		$map_inner = [
			'section' => ' > .elementor-container',
			'container' => ' > .e-con-inner',
			'column' => ' > .elementor-widget-wrap',
			'widget' => ' > .elementor-widget-container',
		];

		$sr = [
			'SELECTOR-INNER' => $unique_selector . $map_inner[ $type ],
			'SELECTOR' => $unique_selector,
			'SECTION-ID' => $unique_selector, // legacy
		];

		$styles = str_replace( array_keys($sr), $sr, $css );

		$post_css->get_stylesheet()->add_raw_css( $styles );

	}

	/**
	 * Appends inline widgets styles css to stylesheet
	 *
	 * @param object $post_css
	 * @param object $element
	 * @return string
	 */
	public function add_inline_widgets_styles_to_post_file( $post_css, $element ){

		if ( $post_css instanceof \Elementor\Core\DynamicTags\Dynamic_CSS ) {
			return;
		}

		if( 'widget' !== $element->get_type() ){
			return;
		}

		$inline_styles = \ReyCore\Plugin::instance()->elementor->widgets->inline_widgets_styles;

		$el_name = Helper::unprefixed_widget_name( $element->get_unique_name() );

		// bail if not this element
		if( ! (isset($inline_styles[$el_name]) && ($element_styles = $inline_styles[$el_name])) ){
			return;
		}

		// bail if already added
		if( in_array($el_name, $this->inline_already_added, true) ){
			return;
		}

		if( ! ( $wp_filesystem = reycore__wp_filesystem() ) ){
			return;
		}

		$styles = '';

		foreach ($element_styles as $css_file) {
			if( $wp_filesystem->is_file( $css_file ) ){
				$styles .= $wp_filesystem->get_contents( $css_file );
			}
		}

		$this->inline_already_added[] = $el_name;

		$post_css->get_stylesheet()->add_raw_css( $styles );
	}

	public function set_above_fold_start(){
		$GLOBALS['rey_above_fold'] = true;
	}

	public function set_custom_template_id($template_type, $active_template){
		if( isset($active_template['id']) ){
			self::$__tpl_id = $active_template['id'];
		}
	}

	public function set_theme_builder_template_id($template_id){
		if( $template_id ){
			self::$__tpl_id = $template_id;
		}
	}

	public function before_get_builder_content($document){

		$page_id = get_queried_object_id();

		if( self::$__tpl_id ){
			$page_id = self::$__tpl_id;
		}

		if( $document->get_id() === $page_id && ! reycore__elementor_edit_mode() ){
			self::$__able_to_collect = true;
			$GLOBALS['rey_above_fold'] = false;
		}
	}

	public function after_site_wrapper()
	{
		if( self::$__started ){
			reycore_assets()->downgrade_styles_priority('page_elements');
		}
	}

	/**
	* Add HTML after section/container rendering
	*
	* @since 1.0.0
	**/
	public function after_render( $element, $is_inner )
	{

		// already started collecting, no need to go further
		if( self::$__started ){
			return;
		}

		// must be a page content
		if( ! self::$__able_to_collect ){
			return;
		}

		// must not be an inner section/container
		if( $is_inner ){
			return;
		}

		// determines the start
		if( self::$__count === ( (int) self::$__limit - 1) ){
			self::$__started = true;
			reycore_assets()->collect_start('page_elements');
		}

		self::$__count++;
	}

	public function delay_footer_styles__start(){
		reycore_assets()->collect_start('footer_elements');
	}

	public function delay_footer_styles__end(){
		reycore_assets()->downgrade_styles_priority('footer_elements');
	}

}
