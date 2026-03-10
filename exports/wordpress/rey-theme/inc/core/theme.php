<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if( !class_exists('ReyTheme_Setup') ):

	class ReyTheme_Setup
	{

		public static $props = [];

		public function __construct()
		{
			add_action( 'init', [$this, 'load_textdomain']);
			add_action( 'after_switch_theme', [$this, 'check_theme']);
			add_action( 'after_setup_theme', [$this, 'pre_after_setup_theme'], 0);
			add_action( 'after_setup_theme', [$this, 'after_setup_theme']);
			add_action( 'widgets_init', [$this, 'widgets_init']);
			add_action( 'init', [$this, 'init'], 0);
			add_filter( 'wp_prepare_themes_for_js', [$this, 'customize_theme_data']);
			add_filter( 'all_plugins', [$this, 'customize_plugin_data']);
		}

		public static function set_props(){

			self::$props = [
				'theme_title'        => esc_html__( 'Rey Theme', 'rey' ),
				'core_title'         => esc_html__( 'Rey Core', 'rey' ),
				'menu_icon'          => REY_THEME_URI . '/assets/images/theme-icon.svg',
				'branding'           => true,
				'dashboxes'          => true,
				'setup_wizard'       => true,
				'plugins_manager'    => true,
				'kb_links'           => true,
				'excluded_dashboxes' => [],
				'support_url'        => defined('DEV_REY_SUPPORT_URL') ? DEV_REY_SUPPORT_URL : 'https://support.reytheme.com/',
			];

		}

		/**
		 * Disable theme if PHP 5.4 not supported & WP Version is 4.7+
		 */
		function check_theme(){

			/**
			 * PHP Version Check.
			 */
			if ( version_compare( PHP_VERSION, REY_THEME_REQUIRED_PHP_VERSION, '<' ) ) :
				// Theme not activated info message
				add_action( 'admin_notices', 'rey__php_version_admin_notice' );
				function rey__php_version_admin_notice() {
					?>
					<div class="notice notice-error">
						<?php printf( esc_html__( 'This theme requires a minimum PHP version of %s. Your version is s%. Your previous theme has been restored.', 'rey' ), REY_THEME_REQUIRED_PHP_VERSION, PHP_VERSION ); ?>
					</div>
					<?php
				}
				// Switch back to previous theme
				switch_theme( get_option( 'theme_switched' ) );
				return false;
			endif;

		}

		/**
		 * Starting with WordPress 6.7.0, translation needs to be loaded earlier, on `init` hook.
		 */
		function load_textdomain() {

			/*
			 * Make theme available for translation.
			 * Translations can be filed in the /languages/ directory.
			 * If you're building a theme based on components, use a find and replace
			 * to change 'rey' to the name of your theme in all the template files.
			 */
			load_theme_textdomain( 'rey', REY_THEME_DIR . '/languages' );

		}

		/**
		 * Sets up theme defaults and registers support for various WordPress features.
		 *
		 * Note that this function is hooked into the after_setup_theme hook, which
		 * runs before the init hook. The init hook is too late for some features, such
		 * as indicating support for post thumbnails.
		 */
		function after_setup_theme() {

			// Add default posts and comments RSS feed links to head.
			add_theme_support( 'automatic-feed-links' );

			/*
			 * Let WordPress manage the document title.
			 * By adding theme support, we declare that this theme does not use a
			 * hard-coded <title> tag in the document head, and expect WordPress to
			 * provide it for us.
			 */
			add_theme_support( 'title-tag' );

			/*
			 * Enable support for Post Thumbnails on posts and pages.
			 *
			 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
			 */
			add_theme_support( 'post-thumbnails' );

			if( in_array( 'blog-thumbnail-size', rey__allowed_image_sizes(), true ) ){
				set_post_thumbnail_size( 1410, 9999 , true );
			}

			if( ($post_thumb_size = rey__standard_large_size()) && in_array( $post_thumb_size, rey__allowed_image_sizes(), true ) ){
				add_image_size( $post_thumb_size, 1024, 9999 );
			}

			if( in_array( 'rey-ratio-16-9', rey__allowed_image_sizes(), true ) ){
				// Used only in Distortion cover.
				add_image_size( 'rey-ratio-16-9', 1410, 810, true ); // height = 1410 x 0.5625
			}

			// This theme uses wp_nav_menu() in one location.
			register_nav_menus( array(
				'main-menu' => esc_html__( 'Main Menu', 'rey' ),
				'footer-menu' => esc_html__( 'Footer Menu', 'rey' ),
			));

			/**
			 * Add support for core custom logo.
			 */
			add_theme_support( 'custom-logo', array(
				'height'      => 200,
				'width'       => 200,
				'flex-width'  => true,
				'flex-height' => true,
			) );

			/*
			 * Switch default core markup for search form, comment form, and comments
			 * to output valid HTML5.
			 */
			add_theme_support( 'html5', array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
			) );

			// add support for post formats
			add_theme_support('post-formats', ['gallery', 'image', 'video', 'audio', 'link', 'quote', 'status']);

			// Gutenberg Editor
			add_theme_support( 'align-wide' );

			/*
			 * This theme styles the visual editor to resemble the theme style,
			 * specifically font, colors, and column width.
			  */
			add_theme_support( 'editor-styles' );
			add_editor_style( 'assets/css/editor.css' );

		}

		function pre_after_setup_theme() {

			/**
			 * Set the content width in pixels, based on the theme's design and stylesheet.
			 *
			 * Priority 0 to make it available to lower priority callbacks.
			 *
			 * @global int $content_width
			 */
			$GLOBALS['content_width'] = $content_width = apply_filters( 'rey/content/width', 1410 );

			/**
			 * Establish default parameters
			 */
			self::set_props();

			/**
			 * Hook before After setup theme
			 * @since 2.2.0
			 */
			do_action('rey/after_setup_theme', $this);

			/**
			 * Override theme properties
			 * define THEME_PROPERTIES in wp-config.php
			 */
			if( defined('THEME_PROPERTIES') && is_array(THEME_PROPERTIES) && ! empty(THEME_PROPERTIES) ){
				self::$props = wp_parse_args(THEME_PROPERTIES, self::$props);
			}

		}

		public function customize_theme_data( $data ){

			if( isset(self::$props['theme_data']['parent']) && isset($data[ REY_THEME_NAME ]) ){
				$data[ REY_THEME_NAME ] = wp_parse_args( self::$props['theme_data']['parent'], $data[ REY_THEME_NAME ] );
			}

			if( isset(self::$props['theme_data']['child']) && isset($data[ REY_THEME_NAME . '-child' ]) ){
				$data[ REY_THEME_NAME . '-child' ] = wp_parse_args( self::$props['theme_data']['child'], $data[ REY_THEME_NAME . '-child' ] );
			}

			return $data;
		}

		public function customize_plugin_data( $data ){

			$core_fn = 'rey-core/rey-core.php';

			if( isset($data[ $core_fn ]) &&
				isset(self::$props['core_title']) &&
				isset(self::$props['theme_data']['parent']) && !empty(self::$props['theme_data']['parent'])
			){

				$data[ $core_fn ]['Name'] = self::$props['core_title'];
				$data[ $core_fn ]['Title'] = self::$props['core_title'];
				$data[ $core_fn ]['Description'] = isset(self::$props['theme_data']['parent']['description']) ? self::$props['theme_data']['parent']['description'] : '';
				$data[ $core_fn ]['Author'] = isset(self::$props['theme_data']['parent']['author']) ? self::$props['theme_data']['parent']['author'] : '';
				$data[ $core_fn ]['AuthorName'] = isset(self::$props['theme_data']['parent']['author']) ? self::$props['theme_data']['parent']['author'] : '';
				$data[ $core_fn ]['PluginURI'] = '';
				$data[ $core_fn ]['AuthorURI'] = '';

				if( self::$props['whitelabel_plugins'] ){

					$plugins = [
						'rey-module-fullscreen-menu',
						'rey-module-preloaders',
						'rey-module-side-header',
					];

					foreach ($plugins as $plugin) {

						$plugin_fn = $plugin . '/' . $plugin . '.php';

						if( isset($data[ $plugin_fn ])	){

							$data[ $plugin_fn ]['Name'] = str_ireplace( REY_THEME_NAME.' ', '', $data[ $plugin_fn ]['Name']);
							$data[ $plugin_fn ]['Title'] = str_ireplace( REY_THEME_NAME.' ', '', $data[ $plugin_fn ]['Name']);
							$data[ $plugin_fn ]['Description'] = '';
							$data[ $plugin_fn ]['Author'] = '';
							$data[ $plugin_fn ]['AuthorName'] = '';
							$data[ $plugin_fn ]['PluginURI'] = '';
							$data[ $plugin_fn ]['AuthorURI'] = '';

						}
					}
				}
			}

			return $data;
		}

		/**
		 * Register widget area.
		 */
		function widgets_init() {

			register_sidebar( [
				'name'          => esc_html__( 'Sidebar', 'rey' ),
				'id'            => 'main-sidebar',
				'description'   => esc_html__('This sidebar will be visible on the pages with default template option.' , 'rey'),
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h3 class="widget-title">',
				'after_title'   => '</h3>',
			] );

			do_action('rey/widgets_init');

		}

		function init(){
			do_action('rey/init');
		}

	}

	new ReyTheme_Setup;

endif;
