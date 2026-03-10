<?php
/**
 * Theme functions and definitions
 *
 * @package rey
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Global Variables
 */
define('REY_THEME_DIR', get_template_directory());
define('REY_THEME_PARENT_DIR', get_stylesheet_directory());
define('REY_THEME_URI', get_template_directory_uri());
define('REY_THEME_PLACEHOLDER', REY_THEME_URI . '/assets/images/placeholder.png');
define('REY_THEME_NAME', 'rey');
define('REY_THEME_CORE_SLUG', 'rey-core');
define('REY_THEME_VERSION', '3.1.5' );
define('REY_THEME_REQUIRED_PHP_VERSION', '5.4.0' ); // Minimum required versions

/**
 * Load Core
 */
require_once REY_THEME_DIR . '/inc/core/core.php';
