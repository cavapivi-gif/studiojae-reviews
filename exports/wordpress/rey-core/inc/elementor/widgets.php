<?php
namespace ReyCore\Elementor;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use \ReyCore\Elementor\Helper;

class Widgets
{
	/**
	 * Holds the widgets that are registered
	 */
	private $widgets = [];

	/**
	 * All widgets that are included
	 */
	private $all_widgets = [];

	/**
	 * Holds registered widget JS scripts
	 */
	public $widgets_scripts = [];

	/**
	 * Holds registered widget CSS styles
	 */
	public $widgets_styles = [];

	/**
	 * Holds registered widget CSS styles
	 * that should be added inline with the post's stylesheet
	 */
	public $inline_widgets_styles = [];

	/**
	 * Holds registered widget CSS styles
	 * that should be added inline with the post's stylesheet
	 * however it needs to be loaded into the editor too.
	 */
	public $inline_widgets_styles_paths = [];

	/**
	 * Holds the absolute path to widgets folder
	 */
	public $widgets_dir = '';

	/**
	 * Holds the local elementor widgets path
	 */
	const WIDGETS_FOLDER = 'inc/elementor/widgets';

	const PREFIX = 'reycore-';

	const ASSET_PREFIX = 'reycore-widget-';

	const ASSET_STYLE_SUFFIX = '-styles';

	const ASSET_SCRIPT_SUFFIX = '-scripts';

	public function __construct(){
		add_action( 'init', [ $this, 'set_widgets' ] );
		add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
	}

	public function get_registered_widgets(){
		return $this->widgets;
	}

	public function get_all_widgets(){
		return $this->all_widgets;
	}

	public function set_widgets(){

		foreach ( self::get_default_widgets_list() as $widget_id ) {

			// prevent registering if disabled
			if( ! \ReyCore\Plugin::instance()->elementor->widgets_manager->is_enabled( $widget_id ) ){
				continue;
			}

			$widget_dir = sprintf( '%1$s/%2$s/', REY_CORE_DIR . self::WIDGETS_FOLDER, $widget_id);
			$widget_path = sprintf( '%1$s/%2$s/', REY_CORE_URI . self::WIDGETS_FOLDER, $widget_id);

			$file_path = $widget_dir . $widget_id . '.php';

			if ( ! is_file( $file_path ) ) {
				continue;
			}

			// load widget
			include_once $file_path;

			// Normalize class name
			$class_name = ucwords( str_replace( '-', ' ', $widget_id ) );
			$class_name = str_replace( ' ', '', $class_name );
			$class_name = \ReyCore\Helper::fix_class_name($class_name, 'Elementor\Widgets');

			// bail if class is missing
			if ( ! class_exists( $class_name ) ) {
				continue;
			}

			// get widget config
			$widget = $class_name::get_rey_config();

			$this->all_widgets[ $widget_id ] = $widget;

			// append classname
			$widget['class_name'] = $class_name;

			// assign widget
			$this->widgets[$widget_id] = $widget;


			if( ! is_admin() && ! wp_doing_cron() ){

				// Gather scripts to register
				if( isset($widget['js']) ){
					foreach ($widget['js'] as $key => $js_file) {
						if( is_readable( $widget_dir . $js_file ) ){
							$suffix = (0 === $key) ? self::ASSET_SCRIPT_SUFFIX : '-' . sanitize_file_name(pathinfo($js_file, PATHINFO_FILENAME));
							$data['path'] = $widget_path . $js_file;
							$data['dependencies'] = [];
							$this->widgets_scripts[ $widget_id . $suffix ] = $data;
						}
					}
				}

				// Gather styles to register
				if( isset($widget['css']) ){
					foreach ($widget['css'] as $key => $css_file) {

						// supports rtl file
						if( strpos($css_file, '[rtl]') !== false ){
							$css_file = str_replace('[rtl]', (is_rtl() ? '-rtl' : ''), $css_file);
						}

						// in `elementor-post-##` stylesheet
						$load_inline = false;

						// style to be loaded inline eg: covers (in page post.css)
						if( strpos($css_file, '!') === 0 ){

							// clean fn
							$css_file = substr($css_file, 1);

							// force load inline
							$load_inline = true;
						}

						// file exists
						if( ! is_readable( $widget_dir . $css_file ) ){
							continue;
						}

						$suffix = '-' . str_replace('-rtl', '', sanitize_file_name(pathinfo($css_file, PATHINFO_FILENAME)));

						// make it load inline
						if( $load_inline ){
							$this->inline_widgets_styles[ $widget_id ][] = $widget_dir . $css_file;
							$this->inline_widgets_styles_paths[ $widget_id . $suffix ] = $widget_path . $css_file;
						}

						// pass along the registered widget styles
						else {
							$this->widgets_styles[ $widget_id . $suffix ] = $widget_path . $css_file;
						}

					}
				}
			}

		}

	}


	public function register_widgets( $widgets_manager ) {

		foreach ($this->widgets as $widget_id => $widget) {

			if( isset($widget['class_name']) ){
				$widgets_manager->register( new $widget['class_name'] );
			}

		}

		do_action('reycore/elementor/register_widgets', $widgets_manager, $this);

	}

	public static function get_default_widgets_list(){
		$list = [
			'basic-post-grid',
			'basic-slider',
			'before-after',
			'button-skew',
			'carousel',
			'carousel-uno',
			'cf7',
			'countdown',
			'cover-blurry',
			'cover-distortion',
			'cover-double-slider',
			'cover-nest',
			'cover-panels',
			'cover-sideslide',
			'cover-skew',
			'cover-split',
			'global-section',
			'grid',
			'header-caller',
			'header-language',
			'header-logo',
			'header-navigation',
			'header-search',
			'hotspots',
			'hoverbox-distortion',
			'instagram',
			'marquee',
			'menu',
			'menu-fancy',
			'sale-badge',
			'scroll-decorations',
			'searchform',
			'section-skew',
			'slider-nav',
			'social-sharing',
			'stamp',
			'text-scroller',
			'toggle-boxes',
			'trigger',
			'trigger-v2',
		];

		if( class_exists('\WooCommerce') ){

			$list[] = 'breadcrumbs';
			$list[] = 'header-account';
			$list[] = 'header-cart';
			$list[] = 'header-wishlist';
			$list[] = 'product-grid';
			$list[] = 'wc-attributes';
			$list[] = 'wc-cart';
			$list[] = 'wc-checkout';

			if(
				class_exists( '\WOOCS_STARTER' ) ||
				class_exists( '\WOOMULTI_CURRENCY' ) ||
				class_exists( '\WOOMULTI_CURRENCY_F' ) ||
				class_exists( '\WC_Aelia_CurrencySwitcher' ) ||
				class_exists( '\woocommerce_wpml' )
			){
				$list[] = 'header-currency';
			}
		}

		$list[] = 'newsletter';

		if( class_exists( 'SIB_Manager' ) ){
			$list[] = 'newsletter-sendinblue';
		}

		if( class_exists( '\MailerLiteForms\Core' ) ){
			$list[] = 'newsletter-mailerlite';
		}

		if( class_exists('\RevSliderSlider') ){
			$list[] = 'revolution-slider';
		}

		return $list;
	}


}
