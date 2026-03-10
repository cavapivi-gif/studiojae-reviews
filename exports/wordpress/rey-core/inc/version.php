<?php
namespace ReyCore;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Version {

	/**
	 * Option name for Rey Core plugin version.
	 * Used to store the last version.
	 */
	const VERSION = 'reycore_version';

	/**
	 * When this DB option was introduced.
 	 */
	const START_VERSION = '2.3.0';

	/**
	 * Prefix for the updating methods
 	 */
	const UPGRADE_PREFIX = 'up_';

	public function __construct(){

		if( ! function_exists('as_next_scheduled_action') ){
			return;
		}

		add_action( 'init', [__CLASS__, 'check_for_updates'], 5 );
		add_action( 'reycore_run_update_callback', [ __CLASS__, 'run_update_callback' ], 10, 2 );
		add_action( 'rey/rollback', [ __CLASS__, 'run_rollback_callback' ] );
	}

	/**
	 * Will check for updates and apply them if necessary
	 *
	 * @return void
	 */
	public static function check_for_updates() {
		if ( self::requires_db_update() ) {
			self::update();
		}
	}

	/**
	 * Will check if we need to apply an update
	 *
	 * @return boolean
	 */
	public static function requires_db_update() {
		$old_version = self::get_db_version();
		$new_version = REY_CORE_VERSION;
		return version_compare( $new_version, $old_version, '>' );
	}

	/**
	 * Set DB version
	 *
	 * @return string
	 */
	public static function update_db_version( $version = null ){
		$target_version = ! is_null( $version ) ? $version : REY_CORE_VERSION;
		if( update_option(self::VERSION, $target_version) ){
			return $target_version;
		}
	}

	/**
	 * Retrieve the DB version
	 *
	 * @param boolean $major
	 * @return string
	 */
	public static function get_db_version( $major = false ){

		$db_version = get_option(self::VERSION);

		if( ! $db_version ){

			// Fires after installation,
			// on a clean site
			do_action('reycore/clean_setup');

			// If DB version is missing,
			// force set it (once).
			// use current version as starting point
			$db_version = self::update_db_version();
		}

		if( $major ){
			return self::get_major_version($db_version);
		}

		return $db_version;
	}

	public static function get_major_version( $version ){
		$v = array_map('absint', explode( '.', $version ));
		return sprintf('%d.%d', $v[0], $v[1]);
	}

	/**
	 * Perform DB upgrades
	 *
	 * @return void
	 */
	public static function update() {

		// not in development mode
		if( \ReyCore\Plugin::is_dev_mode() ){
			return;
		}

		self::log('Start updating data.');

		$current_db_version = self::get_db_version();
		$loop               = 0;
		$version_updates    = [];

		// Check to see if an upgrade is already in progress
		if ( as_next_scheduled_action( 'reycore_run_update_callback', null, 'reycore-db-updates' ) ) {
			return;
		}

		foreach ( self::get_updates() as $version => $update_callbacks ) {

			if ( ! version_compare( $current_db_version, $version, '<' ) ) {
				continue;
			}

			foreach ( $update_callbacks as $update_callback ) {

				as_schedule_single_action(
					time() + $loop,
					'reycore_run_update_callback',
					$update_callback,
					'reycore-db-updates'
				);

				$version_updates[] = $update_callback;
				$loop++;
			}
		}

		if ( empty( $version_updates ) ) {
			self::update_db_version();
		}
	}

	/**
	 * Returns the update configuration
	 *
	 * @return array The version configuration and their callbacks
	 */
	public static function get_updates() {

		$reflection            = new \ReflectionClass( '\ReyCore\Updates' );
		$upgrade_method_prefix = self::UPGRADE_PREFIX;
		$updates               = [];

		foreach ( $reflection->getMethods() as $method ) {

			$callback_name = $method->getName();

			if ( 0 !== strpos( $callback_name, $upgrade_method_prefix ) ) {
				continue;
			}

			if ( ! preg_match( "/$upgrade_method_prefix(\d+_\d+_\d+)/", $callback_name, $matches ) ) {
				continue;
			}

			if( function_exists('rey__valid_url') ){
				if( ! rey__valid_url( \ReyTheme_API::getInstance()->get_test_url() ) ){
					continue;
				}
			}

			// Convert to valid version number
			$callback_version = (string) str_replace( '_', '.', $matches[1] );

			if ( ! isset( $updates[$callback_version] ) ) {
				$updates[$callback_version] = [];
			}

			$updates[$callback_version][] = [
				'callback' => [ $method->class, $method->name ],
			];

		}

		// Add update DB version after all callbacks
		foreach ( array_keys( $updates ) as $version ) {
			$updates[ $version ][] = [
				'callback' => [ get_class(), 'after_version_callbacks' ],
				'args' => [$version]
			];
		}

		// Sort by version number
		ksort( $updates );

		return $updates;
	}

	/**
	 * After a version's updates, run callback.
	 *
	 * @param string $v
	 * @return void
	 */
	public static function after_version_callbacks( $v = null ){

		// run hook for major version
		if( ! is_null($v) && version_compare( self::get_major_version($v), self::get_current_major_version(), '<' ) ){
			do_action("reycore/updates/major_version", $v);
		}

		$version = ! is_null($v) ? $v : REY_CORE_VERSION;

		$method = self::UPGRADE_PREFIX . str_replace( '.', '_', $version );

		// run hook for version
		do_action("reycore/updates/{$method}", $version);

		// update the DB version
		self::update_db_version($version);

		// adds a log
		self::log(sprintf('Upgraded data for v%s and updated DB version.', $version));

	}

	/**
	 * Runs the upgrade method with provided arguments
	 *
	 * @param callable $callback The arguments passed from self::get_updates()
	 *
	 * @return void
	 */
	public static function run_update_callback( $callback, $args = [] ) {
		if ( is_callable( $callback ) ) {
			call_user_func_array( $callback, $args );
		}
	}

	/**
	 * Runs a callback when downgrading
	 *
	 * @param array $item
	 * @return void
	 */
	public static function run_rollback_callback( $item ) {

		return; // MAYBE?

		if( isset($item['slug']) && 'rey-core' === $item['slug'] ){
			// update DB version with the rollbacked one
			self::update_db_version($item['version']);
		}

	}

	/**
	 * Retrieve the current major x.x version
	 *
	 * @param boolean $last
	 * @return string
	 */
	public static function get_current_major_version( $include_minor = false ){
		$v = array_map('absint', explode( '.', REY_CORE_VERSION ));

		if( $include_minor ){
			return sprintf('%d.%d.0', $v[0], $v[1]);
		}

		return sprintf('%d.%d', $v[0], $v[1]);
	}

	public static function log($message){
		do_action( 'qm/debug', '[rey core] ' . $message );
		if( defined('REY_DEBUG_LOG_UPDATES') && REY_DEBUG_LOG_UPDATES ){
			error_log(var_export(  '[rey core] ' . $message,1));
		}
	}
}
