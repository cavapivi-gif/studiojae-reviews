<?php
namespace Rey\Libs;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if ( ! class_exists( '\Plugin_Upgrader', false ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
}

/**
 * WordPress class extended for on-the-fly plugin installations.
 */
class PluginInstallerSkinSilent extends \WP_Upgrader_Skin {

	public function __construct( $args = [] ) {

		if ( ! function_exists( 'request_filesystem_credentials' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}

		parent::__construct($args);
	}

	/**
	 * Empty out the header of its HTML content.
	 */
	public function header() {}

	/**
	 * Empty out the footer of its HTML content.
	 */
	public function footer() {}

	/**
	 * Empty out the footer of its HTML content.
	 *
	 * @param string $string
	 * @param mixed  ...$args Optional text replacements.
	 */
	public function feedback( $string, ...$args ) {}

	/**
	 * Empty out JavaScript output that calls function to decrement the update counts.
	 *
	 * @param string $type Type of update count to decrement.
	 */
	public function decrement_update_count( $type ) {}

	/**
	 * Empty out the error HTML content.
	 *
	 * @param string|WP_Error $errors A string or WP_Error object of the install error/s.
	 */
	public function error( $errors ) {}
}
