<?php
/**
 * Handles the CSS Output of fields.
 *
 * @package     Kirki
 * @category    Modules
 * @author      Ari Stathopoulos (@aristath)
 * @copyright   Copyright (c) 2020, David Vongries
 * @license     https://opensource.org/licenses/MIT
 * @since       3.0.0
 */

/**
 * The Kirki_Modules_CSS object.
 */
class Kirki_Modules_CSS {

	/**
	 * The object instance.
	 *
	 * @static
	 * @access private
	 * @since 3.0.0
	 * @var object
	 */
	private static $instance;

	/**
	 * The CSS array
	 *
	 * @access public
	 * @var array
	 */
	public static $css_array = array();

	/**
	 * Constructor
	 *
	 * @access protected
	 */
	protected function __construct() {

		$class_files = array(
			'Kirki_Modules_CSS_Generator'               => '/class-kirki-modules-css-generator.php',
			'Kirki_Output'                              => '/class-kirki-output.php',
			'Kirki_Output_Field_Background'             => '/field/class-kirki-output-field-background.php',
			'Kirki_Output_Field_Image'                  => '/field/class-kirki-output-field-image.php',
			'Kirki_Output_Field_Multicolor'             => '/field/class-kirki-output-field-multicolor.php',
			'Kirki_Output_Field_Dimensions'             => '/field/class-kirki-output-field-dimensions.php',
			'Kirki_Output_Field_Typography'             => '/field/class-kirki-output-field-typography.php',
			'Kirki_Output_Property'                     => '/property/class-kirki-output-property.php',
			'Kirki_Output_Property_Background_Image'    => '/property/class-kirki-output-property-background-image.php',
			'Kirki_Output_Property_Background_Position' => '/property/class-kirki-output-property-background-position.php',
			'Kirki_Output_Property_Font_Family'         => '/property/class-kirki-output-property-font-family.php',
		);

		foreach ( $class_files as $class_name => $file ) {
			if ( ! class_exists( $class_name ) ) {
				include_once wp_normalize_path( dirname( __FILE__ ) . $file ); // phpcs:ignore WPThemeReview.CoreFunctionality.FileInclude
			}
		}

		add_action('reycore/customizer/make_dynamic_css', [$this, 'add_css_styles']);

	}

	/**
	 * Gets an instance of this object.
	 * Prevents duplicate instances which avoid artefacts and improves performance.
	 *
	 * @static
	 * @access public
	 * @since 3.0.0
	 * @return object
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Prints the styles.
	 *
	 * @access public
	 */
	public function add_css_styles() {

		// Go through all configs.
		$configs = Kirki::$config;

		foreach ( $configs as $config_id => $args ) {
			if ( isset( $args['disable_output'] ) && true === $args['disable_output'] ) {
				continue;
			}

			$styles = self::loop_controls( $config_id );

			$styles = apply_filters( "kirki_{$config_id}_dynamic_css", $styles );

			if ( ! empty( $styles ) ) {
				echo wp_strip_all_tags( $styles ); // phpcs:ignore WordPress.Security.EscapeOutput
			}

		}

		do_action( 'kirki_dynamic_css' );
	}

	/**
	 * Loop through all fields and create an array of style definitions.
	 *
	 * @static
	 * @access public
	 * @param string $config_id The configuration ID.
	 */
	public static function loop_controls( $config_id ) {

		// Get an instance of the Kirki_Modules_CSS_Generator class.
		// This will make sure google fonts and backup fonts are loaded.
		Kirki_Modules_CSS_Generator::get_instance();

		$fields = Kirki::$fields;
		$css    = array();

		// Early exit if no fields are found.
		if ( empty( $fields ) ) {
			return;
		}

		foreach ( $fields as $field ) {

			// Only continue if $field['output'] is set.
			if( ! (isset( $field['output'] ) && ! empty( $field['output'] )) ){
				continue;
			}

			// Only process fields that belong to $config_id.
			if ( $config_id !== $field['kirki_config'] ) {
				continue;
			}

			if ( true === apply_filters( "kirki_{$config_id}_css_skip_hidden", true ) ) {

				// Only continue if field dependencies are met.
				if ( ! empty( $field['required'] ) ) {
					$valid = true;

					foreach ( $field['required'] as $requirement ) {
						if ( isset( $requirement['setting'] ) && isset( $requirement['value'] ) && isset( $requirement['operator'] ) ) {
							$controller_value = Kirki_Values::get_value( $config_id, $requirement['setting'] );
							if ( ! Kirki_Helper::compare_values( $controller_value, $requirement['value'], $requirement['operator'] ) ) {
								$valid = false;
							}
						}
					}

					if ( ! $valid ) {
						continue;
					}
				}
			}

			$css = Kirki_Helper::array_replace_recursive( $css, Kirki_Modules_CSS_Generator::css( $field ) );

			// Add the globals.
			if ( isset( self::$css_array[ $config_id ] ) && ! empty( self::$css_array[ $config_id ] ) ) {
				Kirki_Helper::array_replace_recursive( $css, self::$css_array[ $config_id ] );
			}
		}

		$css = apply_filters( "kirki_{$config_id}_styles", $css );

		if ( is_array( $css ) ) {
			return Kirki_Modules_CSS_Generator::styles_parse( $css );
		}
	}

}
