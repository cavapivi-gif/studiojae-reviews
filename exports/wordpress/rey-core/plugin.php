<?php
namespace ReyCore;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

require_once __DIR__ . '/autoloader.php';

class Plugin
{
	/**
	 * ReyCore instance.
	 *
	 * Holds the plugin instance.
	 *
	 * @since 2.3.0
	 * @access public
	 * @static
	 *
	 * @var Plugin
	 */
	public static $_instance;

	public $svg;
	public $frontend;
	public $acf;
	public $compatibility;
	public $elementor;
	public $customizer;
	public $assets_manager;
	public $fonts;
	public $critical_css;
	public $woo;
	public $woocommerce_loop;
	public $woocommerce_pdp;
	public $woocommerce_assets;
	public $woocommerce_tags = [];
	public $modules;
	public $demo_import;
	public $plugins_manager;
	public $js_icons;

	public static $props;

	private function __construct()
	{
		add_action( 'after_setup_theme', [$this, 'early_init'], 5 );
		add_action( 'after_setup_theme', [$this, 'init'] );
		add_action( 'rey/after_setup_theme', [$this, 'extend_properties'] );
	}

	public function early_init() {

		require_once REY_CORE_DIR . 'inc/fw/out-of-sync.php';
		require_once REY_CORE_DIR . 'inc/functions.php';
		require_once REY_CORE_DIR . 'inc/icons.php';
		require_once REY_CORE_DIR . 'inc/tags.php';
		require_once REY_CORE_DIR . 'inc/tags-header.php';

	}

	public function init() {

		$this->svg             = new Svg();
		$this->assets_manager  = new AssetsManager();
		$this->fonts           = new Webfonts();
		$this->frontend        = new Frontend();
		$this->critical_css    = new CriticalCSS();
		$this->woo             = new WooCommerce\Base();
		$this->acf             = new ACF\Base();
		$this->compatibility   = new Compatibility\Base();
		$this->elementor       = new Elementor\Base();
		$this->customizer      = new Customizer\Base();
		$this->modules         = new Modules\Base();
		$this->demo_import     = new \ReyCore\Libs\Importer\Base();
		$this->plugins_manager = new \ReyCore\Libs\PluginsManager\Base();
		$this->js_icons        = new \ReyCore\Libs\JsIcons();

		new Migrations();
		new Version();
		new Ajax();
		new Assets();
		new Admin();
		new AdminBanners();
		new WhatsNew();
		new Styles();
		new Mobile();
		new Helper();
		new Shortcodes();
		new QueryControl;
		new QueryControlAjaxList;
		new Menus;
		new Gutenberg\Base();

		do_action('reycore/init');
	}

	public function extend_properties( $theme ){

		$default_props = [
			'theme_title'        => esc_html__( 'Rey Theme', 'rey-core' ),
			'theme_name'        => 'Rey',
			'core_title'         => esc_html__( 'Rey Core', 'rey-core' ),
			'menu_icon'          => REY_THEME_URI . '/assets/images/theme-icon.svg',
			'branding'           => true,
			'dashboxes'          => true,
			'setup_wizard'       => true,
			'plugins_manager'    => true,
			'kb_links'           => true,
			'excluded_dashboxes' => [],
			'support_url'        => 'https://support.reytheme.com/',
		];

		$rc_properties = [
			'admin_menu'       => true,
			'admin_bar_menu'   => true,
			'elementor_menu'   => true,
			'demo_import'      => true,
			'elements_manager' => true,
			'modules_manager'  => true,
			'whats_new'        => true,
			'button_icon'      => REY_CORE_URI . 'assets/images/logo-simple-white.svg',
		];

		// Fallback when no hook or passed instance (2.2.0-)
		if( ! $theme ){

			if( ! isset(self::$props['theme_title']) ){
				self::$props = $default_props;
			}

			self::$props = wp_parse_args($rc_properties, self::$props );
			self::$props['button_text'] = explode(' ', self::$props['theme_title'])[0];

			return;
		}

		// Fallback if theme is outdated (2.3.0)
		if( ! isset($theme::$props['theme_title']) ){
			$theme::$props = $default_props;
		}

		$theme::$props = wp_parse_args($rc_properties, $theme::$props );
		$theme::$props['button_text'] = explode(' ', $theme::$props['theme_title'])[0];

		self::$props = $theme::$props;
	}

	public static function is_dev_mode(){
		return defined('REY_DEV_MODE') && REY_DEV_MODE;
	}

	/**
	 * Retrieve the reference to the instance of this class
	 * @return ReyCore
	 */
	public static function instance()
	{
		if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}

}
