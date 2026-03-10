<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( ! class_exists('ReyTheme_Assets') ):
	/**
	 * Handles assets.
	 *
	 * @since 2.0.0
	 */
	class ReyTheme_Assets
	{

		/**
		 * Holds the reference to the instance of this class
		 * @var ReyTheme_Assets
		 */
		private static $_instance = null;

		const STYLE_HANDLE = 'rey-wp-style';

		private $widgets_scripts_loaded;

		private $script_fallback_loaded;

		protected $has_registered_assets = false;

		/**
		 * ReyTheme_Assets constructor.
		 */
		private function __construct() {

			add_action( 'reycore/assets/register_scripts', [$this, 'reycore_register_assets'], 5);
			add_action( 'admin_enqueue_scripts', [$this, 'load_helpers_script'] );
			add_action( 'admin_enqueue_scripts', [$this, 'enqueue_admin']);
			add_action( 'wp_enqueue_scripts', [$this, 'load_helpers_script'] );
			add_action( 'wp_enqueue_scripts', [$this, 'fallback_assets'], 6);
			add_action( 'wp_enqueue_scripts', [$this, 'enqueue_scripts'], 9 ); // make sure it's first
			add_filter( 'style_loader_tag', [$this, 'style_loader_tag'], 10, 2);
			add_filter( 'reycore/assets/excludes_choices', [$this, 'add_excludes_choices']);
			add_action( 'get_sidebar', [$this, 'do_widget_script'] );
			add_filter( 'gallery_style', [$this, 'include_gallery_css']);
			add_action( 'rey/after_header_outside', [$this, 'instant_header_height'] );
		}

		public static function rtl(){
			return is_rtl() ? '-rtl' : '';
		}

		public function get_styles( $_style = '' )
		{
			$rtl = self::rtl();

			/**
			 * Params:
			 * `src` Path to style;
			 * `deps` Dependencies;
			 * `version` Asset version;
			 * `priority` The loading priority sequence. Not specified means `medium`, while `low` will load in footer;
			 * `enqueue` Enqueue on page load, always;
			 * `callback` Function to run to check if it should load. `enqueue` not needed when callback is added;
			 */

			$styles = [

				// 'rey-wp-style' => [
				// 	'src'      => get_template_directory_uri() . '/style' . $rtl . (defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '': '.min') . '.css',
				// 	'deps'     => [],
				// 	'priority' => 'high',
				// 	'enqueue'  => true
				// ],

				'rey-theme'     => [
					'src'      => REY_THEME_URI . '/assets/css/theme' . $rtl . '.css',
					'priority' => 'high',
					'enqueue'  => true,
				],

				'rey-theme-ext'     => [
					'src'      => REY_THEME_URI . '/assets/css/components/extended/extended.css',
					'priority' => 'low',
					'enqueue'  => true,
				],

				'rey-buttons'     => [
					'src'      => REY_THEME_URI . '/assets/css/components/buttons/style' . $rtl . '.css',
					'priority' => 'high',
					// 'enqueue'  => true,
					'desc' => 'Buttons'
				],
				'rey-form-row'     => [
					'src'      => REY_THEME_URI . '/assets/css/components/forms/form-row.css',
					'priority' => 'high',
				],
				'rey-form-select2'     => [
					'src'      => REY_THEME_URI . '/assets/css/components/forms/select2.css',
					'priority' => 'high',
				],
				'rey-logo'     => [
					'src'      => REY_THEME_URI . '/assets/css/components/logo/logo.css',
					'priority' => 'high',
				],
				'rey-preloader'     => [
					'src'      => REY_THEME_URI . '/assets/css/components/preloader/style.css',
					'priority' => 'high',
				],
				'rey-icon'     => [
					'src'      => REY_THEME_URI . '/assets/css/components/icon/icon.css',
					'priority' => 'high',
				],
				'rey-header-icon'     => [
					'src'      => REY_THEME_URI . '/assets/css/components/icon/header-icon.css',
					'priority' => 'high',
				],
				'rey-custom-icon'     => [
					'src'      => REY_THEME_URI . '/assets/css/components/icon/custom-icon.css',
					'priority' => 'high',
				],
				'rey-overlay'     => [
					'src'      => REY_THEME_URI . '/assets/css/components/overlay/overlay.css',
					'priority' => 'low',
				],
				'rey-tpl-page'     => [
					'src'      => REY_THEME_URI . '/assets/css/components/tpl-pages/style.css',
					'priority' => 'high',
				],
				'rey-sidebar'     => [
					'src'      => REY_THEME_URI . '/assets/css/components/sidebar/sidebar' . $rtl . '.css',
					'priority' => 'high',
				],
				'rey-gallery'     => [
					'src'      => REY_THEME_URI . '/assets/css/components/gallery/style.css',
					'priority' => 'low',
				],
				'rey-embed-responsive'     => [
					'src'      => REY_THEME_URI . '/assets/css/components/embed-responsive/embed-responsive.css',
					'priority' => 'low'
				],
				'rey-tables'     => [
					'src'      => REY_THEME_URI . '/assets/css/components/tables/tables.css',
					'priority' => 'low'
				],
				// Loaded by default because there are times when users use header's elements inside the content.
				'rey-header'     => [
					'src'      => REY_THEME_URI . '/assets/css/components/header/style' . $rtl . '.css',
					'priority' => 'high',
					// 'enqueue'  => defined('REY_DEBUG_ASSETS') && REY_DEBUG_ASSETS,
					'desc' => 'Site Header'
				],
					'rey-header-default'     => [
						'src'      => REY_THEME_URI . '/assets/css/components/header-default/style' . $rtl . '.css',
						'priority' => 'high'
					],
					'rey-header-drop-panel'     => [
						'src'      => REY_THEME_URI . '/assets/css/components/header-drop-panel/style' . $rtl . '.css',
						'deps' => ['rey-overlay'],
						'priority' => 'low',
					],
					'rey-header-menu'     => [
						'src'      => REY_THEME_URI . '/assets/css/components/header-menu/style' . $rtl . '.css',
						'priority' => 'high',
					],
						'rey-hbg'     => [
							'src'      => REY_THEME_URI . '/assets/css/components/header-menu/hamburger.css',
							'deps' => [],
						],
						'rey-header-menu-submenus'     => [
							'src'      => REY_THEME_URI . '/assets/css/components/header-menu/style-deferred' . $rtl . '.css',
							'priority' => 'low',
							'deps' => ['rey-overlay'],
						],
						'rey-header-mobile-menu'     => [
							'src'      => REY_THEME_URI . '/assets/css/components/header-menu/mobile' . $rtl . '.css',
							'deps' => ['rey-overlay'],
							'priority' => 'low',
						],
						'rey-header-menu-color-badges'     => [
							'src'      => REY_THEME_URI . '/assets/css/components/header-menu/menu-badges.css',
							'priority' => 'low'
						],
						'rey-header-menu-indicators-arrow'     => [
							'src'      => REY_THEME_URI . '/assets/css/components/header-menu/indicators-arrow.css',
							'priority' => 'low'
						],
						'rey-header-menu-indicators-circle'     => [
							'src'      => REY_THEME_URI . '/assets/css/components/header-menu/indicators-circle.css',
							'priority' => 'low'
						],
						'rey-header-menu-indicators-dash'     => [
							'src'      => REY_THEME_URI . '/assets/css/components/header-menu/indicators-dash.css',
							'priority' => 'low'
						],
						'rey-header-menu-indicators-arrow2'     => [
							'src'      => REY_THEME_URI . '/assets/css/components/header-menu/indicators-arrow2.css',
							'priority' => 'low'
						],
						'rey-header-menu-indicators-plus'     => [
							'src'      => REY_THEME_URI . '/assets/css/components/header-menu/indicators-plus.css',
							'priority' => 'low'
						],
					'rey-header-search'     => [
						'src'      => REY_THEME_URI . '/assets/css/components/header-search/style' . $rtl . '.css',
						'priority' => 'high'
					],
				'rey-gutenberg'     => [
					'src'      => REY_THEME_URI . '/assets/css/components/gutenberg/style' . $rtl . '.css',
					'callback' => 'rey__has_blocks',
				],
				'rey-widgets-lite'  => [
					'src'      => REY_THEME_URI . '/assets/css/components/widgets/widgets-lite.css',
					'priority' => 'high'
				],
				'rey-widgets'  => [
					'src'      => REY_THEME_URI . '/assets/css/components/widgets/widgets.css',
					'priority' => 'low'
				],
				'rey-blog'     => [
					'src'      => REY_THEME_URI . '/assets/css/components/blog/style' . $rtl . '.css',
					'callback' => 'rey__is_blog_list',
				],
				'rey-page404'     => [
					'src'      => REY_THEME_URI . '/assets/css/components/page404/style' . $rtl . '.css',
					'callback' => 'is_404',
					'priority' => 'high',
				],
				'rey-pagination'     => [
					'src'      => REY_THEME_URI . '/assets/css/components/pagination/style' . $rtl . '.css',
					'priority' => 'low'
				],
				'rey-searchbox'     => [
					'src'      => REY_THEME_URI . '/assets/css/components/searchbox/style' . $rtl . '.css',
					'priority' => 'low',
				],
				'rey-presets'     => [
					'src'      => REY_THEME_URI . '/assets/css/components/presets/style' . $rtl . '.css',
					'priority' => 'low',
					'desc' => 'Utility Classes'
				],
				'rey-footer'     => [
					'src'      => REY_THEME_URI . '/assets/css/components/footer/style' . $rtl . '.css',
					'priority' => 'low'
				],
			];

			foreach ($styles as $key => $style) {

				if( !isset($style['deps']) ){
					$styles[$key]['deps'] = [self::STYLE_HANDLE];
				}
				if( !isset($style['version']) ){
					$styles[$key]['version'] = REY_THEME_VERSION;
				}

				if( isset($style['src']) && strpos($style['src'], REY_THEME_URI) !== false ){
					$styles[$key]['path'] = str_replace(REY_THEME_URI, REY_THEME_DIR, $style['src']);
				}
			}

			/**
			 * Use this filter below to override the styles which always load (forms, buttons etc.)
			 * You can pass a different path to the stylesheets.
			 */
			$the_styles = apply_filters('rey/assets/styles', $styles);

			if( $_style && isset($the_styles[$_style]) ){
				return $the_styles[$_style];
			}

			return $the_styles;

		}

		public static function rey_script_path(){
			return apply_filters('rey/assets/helper_path', REY_THEME_URI . '/assets/js/rey.js', REY_THEME_VERSION);
		}

		/**
		 * Registers the Helpers JS library
		 *
		 * @return void
		 */
		public function load_helpers_script(){

			wp_register_script(
				'rey-script',
				self::rey_script_path(),
				[],
				REY_THEME_VERSION,
				true
			);

			wp_localize_script(
				'rey-script',
				'reyParams',
				apply_filters('rey/main_script_params', [
					'theme_js_params' => [
						'menu_delays'            => get_theme_mod('header_nav_hover_delays', true),
						'menu_hover_overlay'     => get_theme_mod('header_nav_overlay', 'show'),
						'menu_mobile_overlay'    => get_theme_mod('header_nav_overlay', 'show'),
						'menu_hover_timer'       => 500,
						'menu_items_hover_timer' => 100, // was 150
						'menu_items_leave_timer' => 200, // was 300
						'menu_items_open_event'  => 'hover', // or "click",
						'embed_responsive' => [
							'src' => ($st = $this->get_styles('rey-embed-responsive')) ? $st['src'] : '', // just src
							'elements' => [
								'.rey-postContent p > iframe'
							],
						],
						'menu_badges_styles' => ($st = $this->get_styles('rey-header-menu-color-badges')) ? $st['src'] : '', // just src
						'header_height_on_first_interaction' => ! self::is_instant_header_height(), // run on first interaction (not instantly on page load)
					],
					'lazy_assets' => [],
					'log_events' => defined('REY_LOG_EVENTS') && REY_LOG_EVENTS,
					'debug'      => defined('WP_DEBUG') && WP_DEBUG,
					'ajaxurl'    => admin_url( 'admin-ajax.php' ),
					'ajax_nonce' => wp_create_nonce( 'rey_nonce' ),
					'preloader_timeout' => false,
					'v' => substr( md5( REY_THEME_VERSION ), 0, 12),
				])
			);


			if( current_user_can('administrator') && ! is_404() ){

				wp_enqueue_style(
					'rey-frontend-admin',
					REY_THEME_URI . '/assets/css/components/frontend-admin/frontend-admin.css',
					[],
					REY_THEME_VERSION
				);

				wp_enqueue_script(
					'rey-frontend-admin',
					REY_THEME_URI . '/assets/js/frontend-admin.js',
					['jquery', 'rey-script'],
					REY_THEME_VERSION,
					true
				);

				wp_localize_script( 'rey-frontend-admin', 'reyFrontendAdminParams', [
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'ajax_nonce' => wp_create_nonce( 'rey_fadm_nonce' ),
				] );

			}
		}

		/**
		 * Rey Scripts
		 *
		 * @since 2.0.0
		 **/
		function get_scripts()
		{
			$scripts = [

				'wp-util' => [
					'deps'    => ['jquery'],
					'plugin' => true
				],

				'masonry' => [
					'callback' => function(){
						return rey__is_blog_list() && get_theme_mod('blog_uses_masonry', true);
					},
					'plugin' => true
				],

				'comment-reply' => [
					'callback' => function(){
						return is_singular() && comments_open() && get_option( 'thread_comments' );
					},
					'plugin' => true
				],

				'rey-script' => [
					'src'      => self::rey_script_path(),
					'deps'     => [],
					// 'enqueue' => true
				],

				'rey-blog' => [
					'src'      => REY_THEME_URI . '/assets/js/components/c-blog.js',
					'deps'    => [],
					'callback' => 'rey__is_blog_list',
				],

				'rey-drop-panel' => [
					'src'      => REY_THEME_URI . '/assets/js/components/c-drop-panel.js',
					'deps'    => [],
				],

				'rey-main-menu' => [
					'src'      => REY_THEME_URI . '/assets/js/components/c-main-menu.js',
					'deps'    => ['rey-mobile-menu-trigger'],
				],

				'rey-mobile-menu-trigger' => [
					'src'      => REY_THEME_URI . '/assets/js/components/c-mobile-menu-trigger.js',
					'deps'    => [],
				],

				'rey-searchform' => [
					'src'      => REY_THEME_URI . '/assets/js/components/c-searchform.js',
					'deps'    => [],
				],

				'rey-preloader' => [
					'src'      => REY_THEME_URI . '/assets/js/components/c-preloader.js',
					'deps'    => [],
				],

				'rey-animate-items' => [
					'src'      => REY_THEME_URI . '/assets/js/components/c-animate-items.js',
					'deps'    => [],
				],

			];

			foreach ($scripts as $key => $script) {

				if( !isset($script['src']) ){
					continue;
				}

				if( !isset($script['version']) ){
					$scripts[$key]['version'] = REY_THEME_VERSION;
				}

				if( isset($script['src']) && strpos($script['src'], REY_THEME_URI) !== false ){
					$scripts[$key]['path'] = str_replace(REY_THEME_URI, REY_THEME_DIR, $script['src']);
				}
			}

			return $scripts;
		}

		function reycore_register_assets( $assets = null ){

			if( ! $assets && rey__core_assets() ){
				$assets = rey__core_assets();
			}

			$assets->register_asset('styles', $this->get_styles());
			$assets->register_asset('scripts', $this->get_scripts());

			$this->has_registered_assets = true;
		}

		/**
		 * Fallback when Core is disabled or outdated.
		 */
		function fallback_assets() {

			if( $this->has_registered_assets ){
				return;
			}

			foreach( $this->get_styles() as $handle => $style ){

				wp_register_style($handle, $style['src'], $style['deps'], $style['version']);

				if( $this->maybe_enqueue( $style ) ){
					wp_enqueue_style( $handle );
				}
			}

			foreach( $this->get_scripts() as $handle => $script ){

				if( isset($script['src']) ){
					wp_register_script(
						$handle,
						$script['src'],
						isset($script['deps']) ? $script['deps'] : [],
						isset($script['version']) ? $script['version'] : REY_THEME_VERSION,
						isset($script['in_footer']) ? $script['in_footer'] : true
					);
					if( isset($script['localize']) ){
						wp_localize_script($handle, $script['localize']['name'], $script['localize']['params']);
					}
				}

				if( $this->maybe_enqueue( $script ) ){
					wp_enqueue_script( $handle );
				}
			}
		}

		function maybe_enqueue( $asset ){

			$enqueue = false;

			// always enqueue
			if( isset($asset['enqueue']) && $asset['enqueue'] ){
				$enqueue = true;
			}

			else {
				// check callback
				if( isset($asset['callback']) ){
					if( is_callable($asset['callback']) && call_user_func($asset['callback']) ){
						$enqueue = true;
					}
				}
			}

			return $enqueue;
		}

		/**
		 * Enqueue Styles based of conditions
		 */
		function enqueue_scripts(){

			/**
			 * Load main stylesheet
			 */
			wp_enqueue_style(
				self::STYLE_HANDLE,
				get_template_directory_uri() . '/style' . self::rtl() . (defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '': '.min') . '.css',
				[],
				REY_THEME_VERSION
			);

		}

		public static function is_instant_header_height(){
			return apply_filters('rey/instant_header_height', false);
		}

		/**
		 * NO JS handling.
		 *
		 * @since 1.0.0
		 */
		function instant_header_height() {
			if( ! self::is_instant_header_height() ) return;
			?><script type="text/javascript" id="rey-instant-header" data-noptimize data-no-optimize="1" data-no-defer="1">
				(function(){
					const header = document.querySelector(".rey-siteHeader");
					if( header ) {
						document.documentElement.style.setProperty("--header-default--height", header.offsetHeight + "px");
					}
				})();
			</script><?php
		}

		/**
		 * Override style tag output
		 *
		 * @param string $tag
		 * @param string $handle
		 * @return string
		 */
		public function style_loader_tag($tag, $handle){

			if( self::STYLE_HANDLE === $handle ){
				return '';
			}

			return $tag;

		}

		function add_excludes_choices( $choices ){
			return array_merge($choices, wp_list_filter( $this->get_styles(), [ 'enqueue' => true ] ));
		}

		function enqueue_admin() {

			// Scripts
			wp_enqueue_script( 'rey-script' );
			wp_enqueue_script( 'rey-admin-scripts', REY_THEME_URI . '/assets/js/rey-admin.js', ['jquery', 'masonry' ], REY_THEME_VERSION, true );
			wp_localize_script('rey-admin-scripts', 'reyAdminParams', apply_filters('rey/admin_script_params', [
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'support_url' => rey__support_url(),
				'tgmpa'    => 2
			]));

			// Styles
			wp_enqueue_style('rey-admin-styles', REY_THEME_URI . '/assets/css/rey-admin.css', false, REY_THEME_VERSION);
		}

		function add_styles( $handler ){
			if( $reycore_assets = rey__core_assets() ){
				$reycore_assets->add_styles($handler);
			}
			else {
				wp_enqueue_style($handler);
			}
		}

		function add_scripts( $handler ){
			if( $reycore_assets = rey__core_assets() ){
				$reycore_assets->add_scripts($handler);
			}
			else {
				if( ! $this->script_fallback_loaded ){
					wp_enqueue_script('rey-script');
					$this->script_fallback_loaded = true;
				}
				wp_enqueue_script($handler);
			}
		}

		function do_widget_script(){

			if( ! $this->widgets_scripts_loaded ){
				$this->add_styles(['rey-widgets-lite', 'rey-widgets']);
				$this->widgets_scripts_loaded = true;
			}

		}

		public function include_gallery_css($html){
			$this->add_styles('rey-gallery');
			return $html;
		}

		/**
		 * Retrieve the reference to the instance of this class
		 * @return ReyTheme_Assets
		 */
		public static function getInstance()
		{
			if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}
	}

	ReyTheme_Assets::getInstance();

endif;
