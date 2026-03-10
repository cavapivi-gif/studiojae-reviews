<?php
/**
 * Plugin Name: Rey Core
 * Description: Core plugin for Rey.
 * Plugin URI: http://www.reytheme.com/
 * Version: 3.1.10
 * Author: ReyTheme
 * Author URI:  https://x.com/mariushoria
 * Text Domain: rey-core
 */

if ( ! defined('ABSPATH')) exit; // Exit if accessed directly

// prevent running in Elementor Updater Crons
if( isset($_GET['action']) && strpos($_GET['action'], '_elementor_updater') !== false ){
	return;
}

if( ! class_exists('ReyCore') ):

class ReyCore
{
	private $requirements_errors = [];

	public function __construct()
	{

		if( defined('REYCORE_DISABLED') && REYCORE_DISABLED ){
			return;
		}

		$this->define_constants();

		if ( $this->requirements_errors = $this->check_requirements() ) {
			add_action( 'admin_notices', [$this, 'failed_requirements'] );
			add_action( 'wp_body_open', [$this, 'failed_requirements'] );
			return;
		}

		$this->includes();

		add_action( 'plugins_loaded', [$this, 'plugins_loaded'] );
		add_action( 'init', [$this, 'load_textdomain'] );
	}

	private function define_constants()
	{
		$this->define( 'REY_CORE_DIR', plugin_dir_path( __FILE__ ) );
		$this->define( 'REY_CORE_URI', plugin_dir_url( __FILE__ ) );
		$this->define( 'REY_CORE_VERSION', '3.1.10' );
		$this->define( 'REY_CORE_PLACEHOLDER', REY_CORE_URI . 'assets/images/placeholder.png' );
		$this->define( 'REY_CORE_REQUIRED_PHP_VERSION', '5.4.0' );
		$this->define( 'REY_CORE_DB_VERSION', '123086' );
		$this->define( 'REYCORE_DISABLE_ACF', false );
		$this->define( 'REY_CORE_THEME_NAME', 'rey' );
		$this->define( 'REY_CORE_NAME', 'Rey Core' );
		$this->define( 'REY_CORE_ASSETS_CSS', true );
		$this->define( 'REY_CORE_ASSETS_CSS_COMBINE', true );
		$this->define( 'REY_CORE_ASSETS_CSS_HEAD', get_theme_mod('perf__css', 'block') );
		$this->define( 'REY_CORE_ASSETS_CSS_DELAY_MODE', 'defer' );
		$this->define( 'REY_CORE_ASSETS_JS', true );
		$this->define( 'REY_CORE_STYLESHEET_HANDLE', 'rey-wp-style' );

		// some plugins need it very early
		if( ! empty($_GET['reycore-ajax']) ) {
			$this->define( 'DOING_AJAX', true );
			$this->define( 'REY_DOING_AJAX', true );
		}
	}

	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	public function includes(){
		require_once REY_CORE_DIR . 'inc/deprecated.php';
		require_once REY_CORE_DIR . 'inc/misc.php';
		require_once REY_CORE_DIR . 'inc/vendor/vendor.php';
		require_once REY_CORE_DIR . 'plugin.php';
	}

	public function load_textdomain() {
		load_plugin_textdomain( 'rey-core', false, plugin_basename(dirname(__FILE__)) . '/languages');
	}

	public function plugins_loaded() {

		\ReyCore\Plugin::instance();

		do_action('reycore/loaded');

	}

	public static function theme_is_active(){
		return ($theme_name = apply_filters('reycore/theme_name', ucfirst(REY_CORE_THEME_NAME))) &&
				($theme = wp_get_theme()) &&
				($theme_name == $theme->name || $theme_name == $theme->parent_theme);
	}

	private function check_requirements(){

		if( defined('DISABLE_REQUIREMENT_ERRORS') && DISABLE_REQUIREMENT_ERRORS ){
			return array();
		}

		$errors = array();

		// Check PHP version
		if ( version_compare( phpversion(), REY_CORE_REQUIRED_PHP_VERSION, '<' ) ) {
			$errors['php_version'] = sprintf( __( 'The PHP version <strong>%s</strong> is needed in order to be able to run the <strong>%s</strong> plugin. Please contact your hosting support and ask them to upgrade the PHP version to at least v<strong>%s</strong> for you.', 'rey-core' ),
				REY_CORE_REQUIRED_PHP_VERSION, REY_CORE_NAME, REY_CORE_REQUIRED_PHP_VERSION );
		}

		if( ! self::theme_is_active() ){
			$errors['theme_inactive'] = sprintf( __( '<strong>%s</strong> requires <strong><em>%s Theme</em></strong> to be active.', 'rey-core' ), REY_CORE_NAME, ucfirst(REY_CORE_THEME_NAME) );
		}

		return $errors;
	}

	/**
	 * Display errors and deactivate.
	 * Render the notices about the plugin's requirements
	 *
	 * @since 1.0.0
	 */
	public function failed_requirements()
	{

		echo '<div class="notice notice-error rey-noticeError">';

			foreach ( $this->requirements_errors as $error ) {
				echo "<div class='__item'>{$error}</div>";
			}

			if( isset($this->requirements_errors['php_version']) ){
				echo '<p>' . sprintf( __( '<strong>%s</strong> has been deactivated.', 'rey-core' ), ucfirst(REY_CORE_THEME_NAME) ) . '</p>';
			}

		echo '</div>';

		if( is_admin() && isset($this->requirements_errors['php_version']) ){
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			deactivate_plugins( 'rey-core/rey-core.php' );
			unset( $_GET['activate'], $_GET['plugin_status'], $_GET['activate-multi'] );
		}

	}
}

new ReyCore();

endif;
