<?php
namespace ReyCore;

defined('ABSPATH') || exit;

/**
 * ReyCore autoloader.
 * Handles dynamically loading classes only when needed.
 *
 * @since 1.0.0
 */
class Autoloader
{

	private static $cached_paths = [];

	public static $plugin_dir_path = '';

	/**
	 * Run autoloader.
	 * Register a function as `__autoload()` implementation.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public static function run() {

		self::$plugin_dir_path = plugin_dir_path(__FILE__);

		spl_autoload_register([__CLASS__, 'autoload']);
	}

	/**
	 *
	 * @param $class_name
	 */
	private static function autoload($class_name) {

		if( 0 !== strpos($class_name, __NAMESPACE__) ) {
			return;
		}

		// Check if we've got it cached and ready.
		if ( isset( self::$cached_paths[ $class_name ] ) ) {
			return self::$cached_paths[ $class_name ];
		}

		$file_name = strtolower(
			preg_replace(
				[
					'/\b' . __NAMESPACE__ . '\\\/',
					'/WooCommerce/',
					'/([a-z])([A-Z])/',
					'/_/',
					'/\\\/'
				],
				[
					'',
					'woocommerce',
					'$1-$2',
					'-',
					DIRECTORY_SEPARATOR
				],
				$class_name
			)
		);

		$sources = [
			'inc/',
		];

		foreach ($sources as $source) {

			$file = self::$plugin_dir_path . $source . $file_name . '.php';

			self::$cached_paths[ $class_name ] = $file;

			// if( file_exists( $file ) ) {
			// 	include_once $file;
			// }

			if ( is_readable( $file ) ) {
				require $file;
			}

		}

	}
}

/**
 * Calling the autoloader to class files
 *
 */
Autoloader::run();
