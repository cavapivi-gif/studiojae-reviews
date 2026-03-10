<?php
namespace ReyCore;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Assets {

	protected static $css_to_exclude_from_loading = [];

	private static $maybe_dequeue = null;

	public function __construct(){

		add_action( 'init', [$this, 'init']);

	}

	public function init(){
		add_action( 'reycore/assets/register_scripts', [$this, 'register_scripts']);
		add_action( 'wp_enqueue_scripts', [$this, 'fallback_enqueue_scripts']);
		add_action( 'wp_enqueue_scripts', [$this, 'admin_bar_scripts']);
		add_action( 'admin_enqueue_scripts', [$this, 'admin_bar_scripts']);
		add_action( 'wp', [$this, 'collect_excludes_from_loading']);
		add_filter( 'rey/main_script_params', [$this, 'core_script_params'], 5);
		add_filter( 'rey/assets/helper_path', [$this, 'set_helpers_path'], 10, 2);
		add_action( 'wp_enqueue_scripts', [ $this, 'dequeue_styles']);
	}

	public static function styles(){

		$rtl = reycore_assets()::rtl();

		$styles = [
			// 'reycore-general' => [
			// 	'src'      => REY_CORE_URI . 'assets/css/general-components/general/general' . $rtl . '.css',
			// 	'priority' => 'high',
			// ],
			'reycore-header-cta' => [
				'src'     => REY_CORE_URI . 'assets/css/general-components/header-cta/header-cta.css',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
				// 'priority' => 'low'
			],
			'reycore-header-search-top' => [
				'src'     => REY_CORE_URI . 'assets/css/general-components/header-search-top/header-search-top.css',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
				'priority' => 'low'
			],
			'reycore-header-search' => [
				'src'     => REY_CORE_URI . 'assets/css/general-components/header-search/header-search' . $rtl . '.css',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
				'priority' => 'low'
			],
			'reycore-main-menu' => [
				'src'     => REY_CORE_URI . 'assets/css/general-components/main-menu/main-menu.css',
				'priority' => 'low'
			],
			'reycore-ajax-load-more' => [
				'src'     => REY_CORE_URI . 'assets/css/general-components/ajax-load-more/ajax-load-more' . $rtl . '.css',
				'priority' => 'low'
			],
			'reycore-language-switcher' => [
				'src'     => REY_CORE_URI . 'assets/css/general-components/language-switcher/language-switcher' . $rtl . '.css',
				'priority' => 'high',
			],
			'reycore-menu-icons' => [
				'src'     => REY_CORE_URI . 'assets/css/general-components/menu-icons/menu-icons' . $rtl . '.css',
				'priority' => 'high',
			],
			'reycore-modals' => [
				'src'     => REY_CORE_URI . 'assets/css/general-components/modals/modals' . $rtl . '.css',
				'priority' => 'low',
				'lazy_assets' => '[data-reymodal],[data-rey-inline-modal]',
				'admin'   => true,
			],
			'reycore-post-social-share' => [
				'src'     => REY_CORE_URI . 'assets/css/general-components/post-social-share/post-social-share' . $rtl . '.css',
				'priority' => 'low',
			],
			'reycore-side-panel' => [
				'src'     => REY_CORE_URI . 'assets/css/general-components/side-panel/side-panel' . $rtl . '.css',
				'deps' => ['rey-overlay'],
				'priority' => 'low',
			],
			'reycore-sticky-social' => [
				'src'     => REY_CORE_URI . 'assets/css/general-components/sticky-social/sticky-social' . $rtl . '.css',
				'priority' => 'low',
			],
			'reycore-utilities' => [
				'src'     => REY_CORE_URI . 'assets/css/general-components/utilities/utilities' . $rtl . '.css',
				'priority' => 'low',
			],
			'reycore-pass-visibility' => [
				'src'     => REY_CORE_URI . 'assets/css/general-components/pass-visibility/pass-visibility' . $rtl . '.css',
				'priority' => 'low',
			],
			'reycore-videos' => [
				'src'     => REY_CORE_URI . 'assets/css/general-components/videos/videos.css',
				'priority' => 'low',
			],
			'rey-simple-scrollbar' => [
				'src'     => REY_CORE_URI . 'assets/css/lib/simple-scrollbar.css',
				'deps'      => [],
				'priority' => 'low',
			],
			'rey-splide-lite' => [
				'src'     => REY_CORE_URI . 'assets/css/lib/splide-lite.css',
				'priority' => 'high',
				'deps'      => [],
			],
			'rey-splide' => [
				'src'     => REY_CORE_URI . 'assets/css/lib/splide.css',
				'priority' => 'low',
				'deps'      => ['rey-splide-lite'],
			],
			'reycore-slider-components' => [
				'src'     => REY_CORE_URI . 'assets/css/lib/slider-components.css',
				'deps'      => [],
				'priority' => 'low',
			],
			'reycore-tooltips' => [
				'src'     => REY_CORE_URI . 'assets/css/general-components/tooltips/tooltips.css',
				'priority' => 'low',
			],
			'reycore-text-toggle' => [
				'src'      => REY_CORE_URI . 'assets/css/general-components/text-toggle/text-toggle.css',
				'priority' => 'low',
			],
			'reycore-header-abs-fixed' => [
				'src'      => REY_CORE_URI . 'assets/css/general-components/header-abs-fixed/header-abs-fixed.css',
				'priority' => 'high',
			],
			'reycore-placeholders' => [
				'src'      => REY_CORE_URI . 'assets/css/general-components/placeholders/placeholders.css',
				'priority' => 'low',
			],
			'reycore-close-arrow' => [
				'src'      => REY_CORE_URI . 'assets/css/general-components/close-arrow/close-arrow.css',
				'priority' => 'low',
			],
			'reycore-breadcrumbs' => [
				'src'      => REY_CORE_URI . 'assets/css/general-components/breadcrumbs/breadcrumbs.css',
				'priority' => 'high',
			],
			'reycore-hbg-styles' => [
				'src'      => REY_CORE_URI . 'assets/css/general-components/hbg/styles.css',
				'priority' => 'high',
			],
			'reycore-hbg-text' => [
				'src'      => REY_CORE_URI . 'assets/css/general-components/hbg/text.css',
				'priority' => 'high',
			],
			'reycore-countdown' => [
				'src'      => REY_CORE_URI . 'assets/css/general-components/countdown/countdown.css',
			],
		];

		foreach ($styles as $key => $style) {

			if( ! isset($style['deps']) ){
				$style_deps = [];
				if( ! (isset($style['admin']) && $style['admin']) ){
					$style_deps[] =  REY_CORE_STYLESHEET_HANDLE;
				}
				$styles[$key]['deps'] = $style_deps;
			}

			if( ! isset($style['version']) ){
				$styles[$key]['version'] = REY_CORE_VERSION;
			}
		}

		return $styles;
	}

	public static function scripts(){

		return [
			'animejs' => [
				'src'     => REY_CORE_URI . 'assets/js/lib/anime.min.js',
				'deps'    => [],
				'version' => '3.1.0',
				'plugin' => true
			],
			'rey-simple-scrollbar' => [
				'src'     => REY_CORE_URI . 'assets/js/lib/simple-scrollbar.js', // REFACTOR
				'deps'    => [],
				'version' => '0.4.0',
			],
			'wnumb' => [
				'src'     => REY_CORE_URI . 'assets/js/lib/wnumb.js',
				'deps'    => [],
				'version' => '1.2.0',
				'plugin' => true
			],
			'splidejs' => [
				'src'     => REY_CORE_URI . 'assets/js/lib/splide.js',
				'deps'    => [],
				'version' => '4.1.2',
				'plugin' => true,
			],
			'splidejs-autoscroll' => [
				'src'     => REY_CORE_URI . 'assets/js/lib/splide-autoscroll.js',
				'deps'    => [],
				'version' => '0.5.2',
				'plugin' => true,
			],
			'rey-splide' => [
				'src'     => REY_CORE_URI . 'assets/js/general/c-slider.js',
				'deps'    => ['splidejs'],
				'version' => REY_CORE_VERSION,
				'icons'   => ['arrow-long']
			],
			'rey-tmpl' => [
				'src'     => REY_CORE_URI . 'assets/js/lib/c-rey-template.js',
				'deps'    => [],
				'version' => REY_CORE_VERSION,
				'admin'   => true,
			],
			'reycore-script' => [
				'src'      => REY_CORE_URI . 'assets/js/general/c-general.js',
				'deps'     => [],
				'version'  => REY_CORE_VERSION,
				// 'enqueue' => true
			],
			'rey-videos' => [
				'src'     => REY_CORE_URI . 'assets/js/general/c-videos.js',
				'deps'    => [],
				'version' => REY_CORE_VERSION,
			],
			'rey-horizontal-drag' => [
				'src'     => REY_CORE_URI . 'assets/js/general/c-horizontal-drag.js',
				'deps'    => [],
				'version' => REY_CORE_VERSION,
			],
			'reycore-header-search' => [
				'src'     => REY_CORE_URI . 'assets/js/general/c-header-search.js',
				'deps'    => ['reycore-sidepanel'],
				'version' => REY_CORE_VERSION,
			],
			'reycore-load-more' => [
				'src'     => REY_CORE_URI . 'assets/js/general/c-load-more.js',
				'deps'    => [],
				'version' => REY_CORE_VERSION,
			],
			'reycore-modals' => [
				'src'     => REY_CORE_URI . 'assets/js/general/c-modal.js',
				'deps'    => [],
				'version' => REY_CORE_VERSION,
				'lazy_assets' => '[data-reymodal],[data-rey-inline-modal]',
				'admin'   => true,
			],
			'reycore-sidepanel' => [
				'src'     => REY_CORE_URI . 'assets/js/general/c-sidepanel.js',
				'deps'    => [],
				'version' => REY_CORE_VERSION,
				'icons'   => ['close', 'arrow-classic'],
			],
			'reycore-sticky-global-sections' => [
				'src'     => REY_CORE_URI . 'assets/js/general/c-sticky-global-sections.js',
				'deps'    => [],
				'version' => REY_CORE_VERSION,
			],
			'reycore-sticky' => [
				'src'     => REY_CORE_URI . 'assets/js/general/c-sticky.js',
				'deps'    => [],
				'version' => REY_CORE_VERSION,
			],
			'reycore-tooltips' => [
				'src'     => REY_CORE_URI . 'assets/js/general/c-tooltips.js',
				'deps'    => [],
				'version' => REY_CORE_VERSION,
			],
			'reycore-text-toggle' => [
				'src'     => REY_CORE_URI . 'assets/js/general/c-text-toggle.js',
				'deps'    => [],
				'version' => REY_CORE_VERSION,
			],
			'reycore-countdown' => [
				'src'     => REY_CORE_URI . 'assets/js/general/c-countdown.js',
				'deps'    => [],
				'version' => REY_CORE_VERSION,
			],
		];
	}

	/**
	 * Override Helpers.js path if Core is newer then Theme.
	 *
	 * @param string $path
	 * @param string $version
	 * @return string
	 * @since 2.4.0
	 */
	public function set_helpers_path($path, $version){

		if( version_compare(REY_CORE_VERSION, $version, '>') ){
			return REY_CORE_URI . 'assets/js/general/c-helpers.js';
		}

		return $path;
	}

	public function core_script_params($params)
	{
		$params['wpch'] = defined('WP_CACHE') && WP_CACHE;
		$params['delay_forced_js_event'] = false;
		$params['delay_final_js_event'] = false;
		$params['delay_js_dom_event'] = false;
		$params['lazy_attribute'] = reycore_assets()::LAZY_ATTRIBUTE;
		$params['core'] = apply_filters('reycore/script_params', [
			'js_params'     => [
				'sticky_debounce' => 200,
				'dir_aware' => false,
				'panel_close_text' => esc_html__('Close Panel', 'rey-core'),
				'refresh_forms_nonces' => false,
			],
			'v' => substr( md5( REY_CORE_VERSION ), 0, 12),
		]);

		if( $lang = reycore__is_multilanguage() ){
			$params['lang'] = $lang;
		}

		return $params;
	}

	function register_scripts( $assets_manager )
	{

		$assets_manager->register_asset('styles', self::styles());
		$assets_manager->register_asset('scripts', self::scripts());

		if( is_user_logged_in() ){
			wp_register_style(
				'reycore-frontend-admin',
				REY_CORE_URI . 'assets/css/general-components/frontend-admin/frontend-admin' . $assets_manager::rtl() . '.css',
				[],
				REY_CORE_VERSION
			);
		}

		// move stylesheet late
		if( function_exists('wp_enqueue_classic_theme_styles') && ! self::maybe_dequeue_default_editor_blocks() ){
			remove_action( 'wp_enqueue_scripts', 'wp_enqueue_classic_theme_styles' );
			add_action( 'wp_footer', 'wp_enqueue_classic_theme_styles', 15 );
		}
	}

	function fallback_enqueue_scripts()
	{
		if( function_exists('rey_assets') || function_exists('reyAssets') ){
			return;
		}

		$excludes = self::get_excludes();

		foreach (['styles', 'scripts'] as $type) {

			$func = 'all_' . $type;

			if( !( is_callable([$this, $func]) && $assets = call_user_func([$this, $func]) ) ){
				continue;
			}

			foreach( $assets as $handle => $asset ){

				$enqueue = false;

				// always enqueue
				if( isset($asset['enqueue']) && $asset['enqueue'] ){
					$enqueue = ! in_array($handle, $excludes, true);
				}

				else {
					// check callback
					if( isset($asset['callback']) ){
						if( is_callable($asset['callback']) && call_user_func($asset['callback']) ){
							$enqueue = true;
						}
					}
				}

				if( $enqueue ){
					call_user_func( [ reycore_assets(), 'add_' . $type ], $handle );
				}
			}
		}
	}

	public function admin_bar_scripts(){
		if( is_admin_bar_showing() ){
			wp_enqueue_script(
				'reyadminbar',
				REY_CORE_URI . 'assets/js/general/c-adminbar.js',
				['jquery'],
				REY_CORE_VERSION
			);
		}
	}

	public function collect_excludes_from_loading(){

		self::$css_to_exclude_from_loading = get_theme_mod('perf__css_exclude', ['rey-presets'] );

		if( self::maybe_dequeue_default_editor_blocks() ){
			self::$css_to_exclude_from_loading[] = 'rey-gutenberg';
		}

	}

	public static function get_excludes(){
		return self::$css_to_exclude_from_loading;
	}

	public function dequeue_styles(){

		if( self::maybe_dequeue_default_editor_blocks() ){
			wp_dequeue_style( 'global-styles' );
			wp_dequeue_style( 'wp-block-library' );
			wp_dequeue_style( 'wp-block-library-theme' );
		}

		// Remove WooCommerce block CSS
		if( (bool) reycore__get_option( 'perf__disable_wcblock', false ) ){
			wp_dequeue_style( 'wc-blocks-vendors-style' );
			wp_dequeue_style( 'wc-blocks-style' );
		}
	}

	public static function maybe_dequeue_default_editor_blocks(){

		static $__dequeue;

		if( ! is_null($__dequeue) ){
			return $__dequeue;
		}

		$__dequeue = false;

		if( (bool) reycore__get_option( 'perf__disable_wpblock', false ) ){

			$__dequeue = true;

			if( get_theme_mod('perf__disable_wpblock__posts', true) && is_single() && 'post' == get_post_type() ){
				$__dequeue = false;
			}
		}

		return $__dequeue;
	}

	public static function caching_plugins(){

		$plugins = [];

		foreach (\ReyCore\Helper::caching_plugins() as $key => $plugin) {
			$plugins[$key] = $plugin['enabled'];
		}

		return $plugins;
	}

}
