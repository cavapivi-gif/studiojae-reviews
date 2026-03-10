<?php
namespace ReyCore\Elementor;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use \ReyCore\Elementor\Helper;

class Assets
{

	public function __construct(){

		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
		add_action( 'elementor/frontend/after_enqueue_styles', [ $this, 'enqueue_frontend_styles'] );
		add_action( 'elementor/frontend/after_enqueue_scripts', [ $this, 'enqueue_frontend_scripts'] );
		add_action( 'wp_enqueue_scripts', [ $this, 'wp_enqueue_scripts'] );

	}

	public static function get_stylesheet_suffix($target_key, $type = ''){

		static $suffix_opt_dom;
		static $suffix_grid;

		if( is_null($suffix_opt_dom) ){
			$suffix_opt_dom = Helper::is_optimized_dom() ? 'opt' : 'unopt';
		}
		if( is_null($suffix_grid) ){
			$suffix_grid = self::maybe_load_rey_grid()  ? '-rey' : '';
		}

		$targets = [
			'section'          => $suffix_opt_dom . $suffix_grid,
			'section-deferred' => $suffix_opt_dom,
			'container'        => $suffix_grid,
		];

		if( $type === 'key' ){
			return sprintf('reycore-elementor-%s-%s', $target_key, $targets[ $target_key ]);
		}

		return $targets[ $target_key ];
	}

	public function elementor_styles(){

		if( is_admin() ){
			return [];
		}

		$rtl_suffix = is_rtl() ? '-rtl' : '';

		$styles = [];

		$styles['reycore-elementor-frontend'] = [
			'src'     => REY_CORE_URI . 'assets/css/elementor-components/general/general'. $rtl_suffix . '.css',
			'deps'    => ['elementor-frontend'],
			'version'   => REY_CORE_VERSION,
		];

		$styles['reycore-elementor-frontend-deferred'] = [
			'src'     => REY_CORE_URI . 'assets/css/elementor-components/general/general-deferred.css',
			'deps'    => ['elementor-frontend'],
			'version'   => REY_CORE_VERSION,
			'priority' => 'low'
		];

		$styles[ self::get_stylesheet_suffix('section', 'key') ] = [
			'src'     => REY_CORE_URI . 'assets/css/elementor-components/section/section-' . self::get_stylesheet_suffix('section') . '.css',
			'deps'    => ['elementor-frontend'],
			'version'   => REY_CORE_VERSION,
		];

		$styles[ self::get_stylesheet_suffix('section-deferred', 'key') ] = [
			'src'      => REY_CORE_URI . 'assets/css/elementor-components/section/section-deferred-' . self::get_stylesheet_suffix('section-deferred') . '.css',
			'deps'     => ['elementor-frontend'],
			'version'  => REY_CORE_VERSION,
			'priority' => 'low'
		];

		$styles[self::get_stylesheet_suffix('container', 'key') ] = [
			'src'     => REY_CORE_URI . 'assets/css/elementor-components/container/container' . self::get_stylesheet_suffix('container') . '.css',
			'deps'    => ['elementor-frontend'],
			'version'   => REY_CORE_VERSION,
		];

		$styles['reycore-elementor-heading-animation-u'] = [ // underline
			'src'     => REY_CORE_URI . 'assets/css/elementor-components/heading-animation/heading-animation-u.css',
			'deps'    => ['elementor-frontend'],
			'version'   => REY_CORE_VERSION,
			'priority' => 'low'
		];

		$styles['reycore-elementor-heading-animation-v'] = [ // visible
			'src'     => REY_CORE_URI . 'assets/css/elementor-components/heading-animation/heading-animation-v.css',
			'deps'    => ['elementor-frontend'],
			'version'   => REY_CORE_VERSION,
			'priority' => 'low'
		];

		$styles['reycore-elementor-heading-animation-s'] = [ // slide
			'src'     => REY_CORE_URI . 'assets/css/elementor-components/heading-animation/heading-animation-s.css',
			'deps'    => ['elementor-frontend'],
			'version'   => REY_CORE_VERSION,
			'priority' => 'low'
		];

		$styles['reycore-elementor-heading-highlight'] = [
			'src'     => REY_CORE_URI . 'assets/css/elementor-components/heading/highlight.css',
			'deps'    => ['elementor-frontend'],
			'version'   => REY_CORE_VERSION,
			'priority' => 'low'
		];

		$styles['reycore-elementor-heading-vertical'] = [
			'src'     => REY_CORE_URI . 'assets/css/elementor-components/heading/vertical.css',
			'deps'    => ['elementor-frontend'],
			'version'   => REY_CORE_VERSION,
			'priority' => 'low'
		];

		$styles['reycore-elementor-scroll-deco'] = [
			'src'     => REY_CORE_URI . 'assets/css/elementor-components/scroll-deco/scroll-deco'. $rtl_suffix . '.css',
			'deps'    => ['elementor-frontend'],
			'version'   => REY_CORE_VERSION,
			'priority' => 'low'
		];

		$styles['reycore-elementor-sticky-gs'] = [
			'src'     => REY_CORE_URI . 'assets/css/elementor-components/sticky-gs/sticky-gs'. $rtl_suffix . '.css',
			'deps'    => ['elementor-frontend'],
			'version'   => REY_CORE_VERSION,
			'priority' => 'low'
		];

		$styles['reycore-elementor-text-links'] = [
			'src'     => REY_CORE_URI . 'assets/css/elementor-components/text-links/text-links'. $rtl_suffix . '.css',
			'deps'    => ['elementor-frontend'],
			'version'   => REY_CORE_VERSION,
			'priority' => 'low'
		];

		foreach ([ 'block', 'icon' ] as $type) {
			$styles['reycore-elementor-buttons-' . $type] = [
				'src'     => REY_CORE_URI . 'assets/css/elementor-components/buttons/buttons-'. $type .'.css',
				'deps'    => ['elementor-frontend'],
				'version'   => REY_CORE_VERSION,
			];
		}

		$styles['reycore-elementor-nav-styles'] = [
			'src'     => REY_CORE_URI . 'assets/css/elementor-components/nav-el/nav-styles'. $rtl_suffix . '.css',
			'deps'    => ['elementor-frontend'],
			'version'   => REY_CORE_VERSION,
			'priority' => 'low'
		];

		$styles['reycore-elementor-el-iconbox'] = [
			'src'     => REY_CORE_URI . 'assets/css/elementor-components/parts/el-icon-box.css',
			'deps'    => ['elementor-frontend'],
			'version'   => REY_CORE_VERSION,
		];

		$styles['reycore-elementor-el-image-gallery'] = [
			'src'     => REY_CORE_URI . 'assets/css/elementor-components/parts/el-image-gallery.css',
			'deps'    => ['elementor-frontend'],
			'version'   => REY_CORE_VERSION,
		];

		$styles['reycore-elementor-el-image-carousel'] = [
			'src'     => REY_CORE_URI . 'assets/css/elementor-components/parts/el-image-carousel.css',
			'deps'    => ['elementor-frontend'],
			'version'   => REY_CORE_VERSION,
		];

		$styles['reycore-elementor-el-video'] = [
			'src'     => REY_CORE_URI . 'assets/css/elementor-components/parts/el-video.css',
			'deps'    => ['elementor-frontend'],
			'version'   => REY_CORE_VERSION,
			'priority' => 'low'
		];

		$styles['reycore-elementor-bg-video-container'] = [
			'src'     => REY_CORE_URI . 'assets/css/elementor-components/parts/bg-video-container.css',
			'deps'    => ['elementor-frontend'],
			'version'   => REY_CORE_VERSION,
		];

		$styles['reycore-elementor-epro'] = [
			'src'     => REY_CORE_URI . 'assets/css/elementor-components/parts/e-pro.css',
			'deps'    => ['elementor-frontend'],
			'version'   => REY_CORE_VERSION,
			'priority' => 'low',
		];

		$styles['reycore-elementor-stretch-page'] = [
			'src'     => REY_CORE_URI . 'assets/css/elementor-components/parts/stretch-page.css',
			'deps'    => ['elementor-frontend'],
			'version'   => REY_CORE_VERSION,
			'priority' => 'high'
		];

		$styles['reycore-elementor-cover'] = [
			'src'     => REY_CORE_URI . 'assets/css/elementor-components/parts/cover.css',
			'deps'    => ['elementor-frontend'],
			'version'   => REY_CORE_VERSION,
		];

		$styles['reycore-elementor-el-heading-dashes'] = [
			'src'     => REY_CORE_URI . 'assets/css/elementor-components/parts/el-heading-dashes.css',
			'deps'    => ['elementor-frontend'],
			'version'   => REY_CORE_VERSION,
			'priority' => 'low',
		];

		$styles['reycore-elementor-mobi-offset'] = [
			'src'     => REY_CORE_URI . 'assets/css/elementor-components/parts/mobi-offset.css',
			'deps'    => ['elementor-frontend'],
			'version'   => REY_CORE_VERSION,
		];

		$styles['reycore-elementor-column-sticky'] = [
			'src'     => REY_CORE_URI . 'assets/css/elementor-components/parts/el-column-sticky.css',
			'deps'    => ['elementor-frontend'],
			'version'   => REY_CORE_VERSION,
			'priority' => 'low',
		];

		$styles['reycore-elementor-column-topdeco'] = [
			'src'     => REY_CORE_URI . 'assets/css/elementor-components/parts/el-col-topdeco.css',
			'deps'    => ['elementor-frontend'],
			'version'   => REY_CORE_VERSION,
			'priority' => 'low',
		];

		return $styles;
	}


	public function elementor_scripts(){

		if( is_admin() ){
			return [];
		}

		return [

			'threejs' => [
				'external' => true,
				'src'     => 'https://cdn.jsdelivr.net/npm/three@0.144/build/three.min.js',
				'deps'    => [],
				'version'   => 'r144',
				'localize' => [
					'name' => 'reyThreeConfig',
					'params' => [
						'displacements' => [
							'https://i.imgur.com/t4AA2A8.jpg',
							'https://i.imgur.com/10UwPUy.jpg',
							'https://i.imgur.com/tO1ukJf.jpg',
							'https://i.imgur.com/iddaUQ7.png',
							'https://i.imgur.com/YbFcFOJ.png',
							'https://i.imgur.com/JzGo2Ng.jpg',
							'https://i.imgur.com/0toUHNF.jpg',
							'https://i.imgur.com/NPnfoR8.jpg',
							'https://i.imgur.com/xpqg1ot.jpg',
							'https://i.imgur.com/Ttm5Vj4.jpg',
							'https://i.imgur.com/wrz3VyW.jpg',
							'https://i.imgur.com/rfbuWmS.jpg',
							'https://i.imgur.com/NRHQLRF.jpg',
							'https://i.imgur.com/G29N5nR.jpg',
							'https://i.imgur.com/tohZyaA.jpg',
							'https://i.imgur.com/YvRcylt.jpg',
						],
					],
				],
			],

			'distortion-app' => [
				'src'     => REY_CORE_URI . 'assets/js/lib/distortion-app.js',
				'deps'    => ['threejs', 'jquery'],
				'version'   => '1.0.0',
			],

			'lottie' => [
				'src'     => REY_CORE_URI . 'assets/js/lib/lottie.min.js',
				'deps'    => ['threejs', 'jquery'],
				'version'   => '5.6.8',
				'plugin' => true
			],

			'reycore-elementor-frontend' => [
				'src'      => REY_CORE_URI . 'assets/js/elementor/general.js',
				'deps'     => ['elementor-frontend'],
				'version'  => REY_CORE_VERSION,
				'localize' => [
					'name'   => 'reyElementorFrontendParams',
					'params' => [
						'compatibilities' => Helper::get_compatibilities(),
						'ajax_url'        => admin_url( 'admin-ajax.php' ),
						'ajax_nonce'      => wp_create_nonce('reycore-ajax-verification'),
					],
				],
			],

			'reycore-elementor-scroll-deco' => [
				'src'     => REY_CORE_URI . 'assets/js/elementor/scroll-deco.js',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-elementor-elem-accordion' => [
				'src'     => REY_CORE_URI . 'assets/js/elementor/elem-accordion.js',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-elementor-elem-button-add-to-cart' => [
				'src'     => REY_CORE_URI . 'assets/js/elementor/elem-button-add-to-cart.js',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-elementor-elem-carousel-links' => [
				'src'     => REY_CORE_URI . 'assets/js/elementor/elem-carousel-links.js',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-elementor-elem-column-click' => [
				'src'     => REY_CORE_URI . 'assets/js/elementor/elem-column-click.js',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-elementor-elem-column-sticky' => [
				'src'     => REY_CORE_URI . 'assets/js/elementor/elem-column-sticky.js',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-elementor-elem-column-video' => [
				'src'     => REY_CORE_URI . 'assets/js/elementor/elem-column-video.js',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-elementor-elem-header-navigation' => [
				'src'     => REY_CORE_URI . 'assets/js/elementor/elem-header-navigation.js',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-elementor-elem-header-wishlist' => [
				'src'     => REY_CORE_URI . 'assets/js/elementor/elem-header-wishlist.js',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-elementor-elem-heading' => [
				'src'     => REY_CORE_URI . 'assets/js/elementor/elem-heading.js',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-elementor-elem-image-carousel' => [
				'src'     => REY_CORE_URI . 'assets/js/elementor/elem-image-carousel.js',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-elementor-elem-section-pushback' => [
				'src'     => REY_CORE_URI . 'assets/js/elementor/elem-section-pushback.js',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-elementor-elem-section-video' => [
				'src'     => REY_CORE_URI . 'assets/js/elementor/elem-section-video.js',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-elementor-elem-woo-prod-gallery' => [
				'src'     => REY_CORE_URI . 'assets/js/elementor/elem-woo-prod-gallery.js',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			],
			'reycore-elementor-elem-lazy-load' => [
				'src'     => REY_CORE_URI . 'assets/js/elementor/elem-lazy-load.js',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			],

		];
	}


	public static function maybe_load_rey_grid(){

		$val = get_theme_mod('elementor_grid', 'rey');
		$opt = ! ( $val === 'default' );

		if( get_page_template_slug() === 'template-canvas.php' ){
			$opt = false;
		}

		return apply_filters('reycore/elementor/load_grid', $opt);
	}


	public function register_assets($assets){
		$assets->register_asset('styles', $this->elementor_styles());
		$assets->register_asset('scripts', $this->elementor_scripts());
	}

	/**
	 * Enqueue ReyCore's Elementor Frontend CSS
	 */
	public function enqueue_frontend_styles() {

		$styles = [
			'reycore-elementor-frontend',
			'reycore-elementor-frontend-deferred',
			'rey-wc-elementor'
		];

		if( defined('ELEMENTOR_PRO_VERSION') ){
			$styles[] = 'reycore-elementor-epro';
		}

		reycore_assets()->add_styles($styles);

		if( reycore__elementor_edit_mode() ) {
			wp_enqueue_style('reycore-frontend-admin');
		}
	}

	/**
	 * Load Frontend JS
	 *
	 * @since 1.0.0
	 */
	public function enqueue_frontend_scripts()
	{
		reycore_assets()->add_scripts('reycore-elementor-frontend');

		if( Helper::is_pushback_fallback_enabled() ){
			reycore_assets()->add_scripts('reycore-elementor-elem-section-pushback');
		}
	}

	public function wp_enqueue_scripts(){

		// provide code on need
		// if( isset(\Elementor\Plugin::$instance->kits_manager) ){
		// 	\Elementor\Plugin::$instance->kits_manager->frontend_before_enqueue_styles();
		// }

		// force elementor frontend style in certain conditions
		if( 'internal' === get_option( 'elementor_css_print_method' ) && (
			is_home() ||
			(class_exists('WooCommerce') && ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ))
		) ){
			wp_enqueue_style('elementor-frontend');
		}

		$this->optimize_elementor_css();
		$this->optimize_elementor_js();

	}

	public function optimize_elementor_css(){

		// Disabled because of https://github.com/hogash/rey/issues/291
		return;

		if( reycore__elementor_edit_mode() ){
			return;
		}

		if( ! reycore_assets()->get_settings('save_css') ){
			return;
		}

		if( 'block' === reycore_assets()->get_settings('css_head') ){
			return;
		}

		if( ! get_theme_mod('perf__css_elementor', false) ){
			return;
		}

		$styles[] = 'elementor-frontend'; // must split

		// Must be critical
		$styles[] = 'elementor-global';

		// Elementor Kit. Must be critical.
		if( class_exists('\Elementor\Plugin') && isset(\Elementor\Plugin::$instance->kits_manager) ){
			$styles[] = 'elementor-post-' . \Elementor\Plugin::$instance->kits_manager->get_active_id();
		}

		// Header CSS. Must be critical.
		if( ($gs_header = reycore__get_option( 'header_layout_type', '' )) && ! in_array($gs_header, ['default', 'none'], true) ) {
			$styles[] = 'elementor-post-' . $gs_header;
		}

		// Page CSS. Must be critical and it's huge. Maybe it can be split, first 2-3 sections.
		if( ($post_id = get_queried_object_id()) && ($document = \Elementor\Plugin::$instance->documents->get( $post_id )) && $document->is_built_with_elementor() ){
			$styles[] = 'elementor-post-' . $post_id;
		}

		$styles[] = 'elementor-pro';
		$styles[] = 'swiper'; // apparently this loads regardless if used or not.

		reycore_assets()->defer_page_styles($styles);
	}

	public function optimize_elementor_js(){

		// Disabled because of https://github.com/hogash/rey/issues/291
		return;

		if( reycore__elementor_edit_mode() ){
			return;
		}

		if( ! reycore_assets()->get_settings('save_js') ){
			return;
		}

		if( ! get_theme_mod('perf__js_elementor', false) ){
			return;
		}

		reycore_assets()->defer_page_scripts([
			// Elementor Lite
			'jquery-ui-core',
			'elementor-waypoints',
			'elementor-webpack-runtime',
			'elementor-frontend-modules',
			'elementor-frontend',
			// Elementor Pro
			'elementor-pro-webpack-runtime',
			'elementor-pro-frontend',
			'pro-elements-handlers',
		]);

	}
}
