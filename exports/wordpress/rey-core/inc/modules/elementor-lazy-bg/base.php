<?php
namespace ReyCore\Modules\ElementorLazyBg;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	const ASSET_HANDLE = 'reycore-module-elementor-lazy-bg';

	const KEY = 'rey_bg_image_lazy';

	const CSS_CLASS = 'rey-lazyBg';

	public function __construct()
	{
		add_action( 'init', [$this, 'init']);
	}

	public function init() {

		if( ! $this->is_enabled() ){
			return;
		}

		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);

		add_action( 'wp_head', [$this, 'head_scripts'], 1000);

		add_action( 'elementor/element/section/section_background/before_section_end', [$this, 'el_settings']);
		add_action( 'reycore/frontend/section/before_render', [$this, 'before_render']);
		add_action( 'elementor/element/container/section_background/before_section_end', [$this, 'el_settings']);
		add_action( 'reycore/frontend/container/before_render', [$this, 'before_render']);
		add_action( 'elementor/element/column/section_style/before_section_end', [$this, 'el_settings']);
		add_action( 'elementor/frontend/column/before_render', [$this, 'before_render']);
		add_action( 'elementor/element/common/_section_background/before_section_end', [$this, 'common_settings']);
		add_action( 'elementor/frontend/widget/before_render', [$this, 'before_render']);

		add_filter( 'reycore/delay_js/exclusions', [$this, 'exclude_delay_js']);

		add_action( 'reycore/customizer/section=general-performance/marker=performance_toggles', [ $this, 'add_customizer_options' ] );
	}

	/**
	 * Unset background images.
	 * Make sure JS works.
	 *
	 * @return void
	 */
	public function head_scripts(){

		echo '<style id="rey-lazy-bg">.rey-js .elementor-element.rey-lazyBg, .rey-js .elementor-element.rey-lazyBg > .elementor-widget-container, .rey-js .elementor-element.rey-lazyBg > .elementor-widget-wrap { background-image: none !important; }</style>';

		if( $this->enabled_globally() ){
			reycore_assets()->add_scripts(self::ASSET_HANDLE);
		}
	}

	public function register_assets($assets){

		$assets->register_asset('scripts', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/script.js',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			]
		]);

	}

	/**
	 * Add custom settings into Elementor's Section
	 *
	 * @since 1.0.0
	 */
	private function _settings( $element, $selector = 'background_background' )
	{

		if( $this->enabled_globally() ){
			return;
		}

		$element->add_control( self::KEY, [
			'label' => esc_html__( 'Lazy load Background Image', 'rey-core' ) .  \ReyCore\Elementor\Helper::rey_badge(),
			'type' => \Elementor\Controls_Manager::SWITCHER,
			'default' => '',
			'condition' => [
				$selector => 'classic',
			],
		] );

	}

	/**
	 * Add custom settings into Elementor's Layout elements
	 *
	 * @since 1.0.0
	 */
	public function el_settings( $element )
	{
		$this->_settings($element);
	}

	/**
	 * Add custom settings into Elementor's Common widgets background
	 *
	 * @since 1.0.0
	 */
	public function common_settings( $element )
	{
		$this->_settings($element, '_background_background');
	}

	/**
	* Render before rendering
	*
	* @since 1.0.0
	**/
	public function before_render( $element )
	{

		$bg_key = ('widget' === $element->get_type() ? '_' : '') . 'background_background';

		// SiteWide
		if( $this->enabled_globally() ){

			if( 'classic' !== $element->get_settings($bg_key) ){
				return;
			}

			$element->add_render_attribute( '_wrapper', 'class', self::CSS_CLASS );

			return;
		}

		// Element based
		$settings = $element->get_settings();

		if( ! isset($settings[self::KEY]) ){
			return;
		}

		if( $settings[self::KEY] === '' ){
			return;
		}

		$bg_key = ('widget' === $element->get_type() ? '_' : '') . 'background_background';

		if( 'classic' !== $settings[$bg_key] ){
			return;
		}

		$element->add_render_attribute( '_wrapper', 'class', self::CSS_CLASS );

		reycore_assets()->add_scripts(self::ASSET_HANDLE);

	}

	/**
	 * Exclude script from delay JS
	 *
	 * @param array $pattern
	 * @return array
	 */
	function exclude_delay_js( $pattern ) {
		$pattern[self::ASSET_HANDLE] = self::get_path( basename( __DIR__ ) ) . '/script.js';
		return $pattern;
	}

	/**
	 * Add Customizer site-wide enable button
	 *
	 * @param array $control_args
	 * @param object $section
	 * @return void
	 */
	public function add_customizer_options( $section ){

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'perf__lazy_bg',
			'label'       => esc_html__( 'Lazy-load Background images', 'rey-core' ),
			'default'     => false,
			'help' => [
				esc_html__( 'This will make Elementor\'s elements and widgets background images to lazy load on scroll, until in viewport.', 'rey-core')
			],
		] );

	}

	public function enabled_globally(){

		static $global;

		if( is_null($global) ){
			$global = get_theme_mod('perf__lazy_bg', false);
		}

		return $global;
	}

	public function is_enabled() {
		return true;
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Lazy Load Background Images', 'Module name', 'rey-core'),
			'description' => esc_html_x('Adds an option inside Elementor elements to lazy load the background images.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['elementor'],
			'keywords'    => ['Elementor', 'Image', 'Section', 'Container'],
			'video' => true
		];
	}

	public function module_in_use(){

		if( $this->enabled_globally() ){
			return true;
		}

		$results = \ReyCore\Elementor\Helper::scan_content_in_site( 'content', sprintf( '"%s"', self::KEY ) );

		return ! empty($results);

	}
}
