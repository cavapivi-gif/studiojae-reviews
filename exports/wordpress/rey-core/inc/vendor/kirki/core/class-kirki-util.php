<?php
/**
 * A utility class for Kirki.
 *
 * @package     Kirki
 * @category    Core
 * @author      Ari Stathopoulos (@aristath)
 * @copyright   Copyright (c) 2020, David Vongries
 * @license     https://opensource.org/licenses/MIT
 * @since       3.0.9
 */

/**
 * Utility class.
 */
class Kirki_Util {

	/**
	 * Constructor.
	 *
	 * @since 3.0.9
	 * @access public
	 */
	public function __construct() {
	}

	/**
	 * Determine if Kirki is installed as a plugin.
	 *
	 * @static
	 * @access public
	 * @since 3.0.0
	 * @return bool
	 */
	public static function is_plugin() {}

	/**
	 * Build the variables.
	 *
	 * @static
	 * @access public
	 * @since 3.0.9
	 * @return array Formatted as array( 'variable-name' => value ).
	 */
	public static function get_variables() {

		$variables = array();

		// Loop through all fields.
		foreach ( Kirki::$fields as $field ) {

			// Check if we have variables for this field.
			if ( isset( $field['variables'] ) && $field['variables'] && ! empty( $field['variables'] ) ) {

				// Loop through the array of variables.
				foreach ( $field['variables'] as $field_variable ) {

					// Is the variable ['name'] defined? If yes, then we can proceed.
					if ( isset( $field_variable['name'] ) ) {

						// Do we have a callback function defined? If not then set $variable_callback to false.
						$variable_callback = ( isset( $field_variable['callback'] ) && is_callable( $field_variable['callback'] ) ) ? $field_variable['callback'] : false;

						// If we have a variable_callback defined then get the value of the option
						// and run it through the callback function.
						// If no callback is defined (false) then just get the value.
						$variables[ $field_variable['name'] ] = Kirki_Values::get_value( $field['settings'] );
						if ( $variable_callback ) {
							$variables[ $field_variable['name'] ] = call_user_func( $field_variable['callback'], Kirki_Values::get_value( $field['settings'] ) );
						}
					}
				}
			}
		}

		// Pass the variables through a filter ('kirki_variable') and return the array of variables.
		return apply_filters( 'kirki_variable', $variables );
	}

	/**
	 * Returns the $wp_version.
	 *
	 * @static
	 * @access public
	 * @since 3.0.12
	 * @param string $context Use 'minor' or 'major'.
	 * @return int|string      Returns integer when getting the 'major' version.
	 *                         Returns string when getting the 'minor' version.
	 */
	public static function get_wp_version( $context = 'minor' ) {
		global $wp_version;

		// We only need the major version.
		if ( 'major' === $context ) {
			$version_parts = explode( '.', $wp_version );
			return $version_parts[0];
		}

		return $wp_version;
	}

	/**
	 * Returns the $wp_version, only numeric value.
	 *
	 * @static
	 * @access public
	 * @since 3.0.12
	 * @param string $context      Use 'minor' or 'major'.
	 * @param bool   $only_numeric Whether we wwant to return numeric value or include beta/alpha etc.
	 * @return int|float           Returns integer when getting the 'major' version.
	 *                             Returns float when getting the 'minor' version.
	 */
	public static function get_wp_version_numeric( $context = 'minor', $only_numeric = true ) {
		global $wp_version;

		// We only need the major version.
		if ( 'major' === $context ) {
			$version_parts = explode( '.', $wp_version );
			return absint( $version_parts[0] );
		}

		// If we got this far, we want the full monty.
		// Get the numeric part of the version without any beta, alpha etc parts.
		if ( false !== strpos( $wp_version, '-' ) ) {
			// We're on a dev version.
			$version_parts = explode( '-', $wp_version );
			return floatval( $version_parts[0] );
		}
		return floatval( $wp_version );
	}
}
