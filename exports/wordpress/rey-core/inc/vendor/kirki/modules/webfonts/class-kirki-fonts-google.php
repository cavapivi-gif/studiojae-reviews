<?php
/**
 * Processes typography-related fields
 * and generates the google-font link.
 *
 * @package     Kirki
 * @category    Core
 * @author      Ari Stathopoulos (@aristath)
 * @copyright   Copyright (c) 2020, David Vongries
 * @license     https://opensource.org/licenses/MIT
 * @since       1.0
 */

/**
 * Manages the way Google Fonts are enqueued.
 */
final class Kirki_Fonts_Google {

	/**
	 * The Kirki_Fonts_Google instance.
	 * We use the singleton pattern here to avoid loading the google-font array multiple times.
	 * This is mostly a performance tweak.
	 *
	 * @access private
	 * @var null|object
	 */
	private static $instance = null;

	/**
	 * DUMMY. DOESN'T DO ANYTHING, SIMPLY BACKWARDS-COMPATIBILITY.
	 *
	 * @static
	 * @access public
	 * @var bool
	 */
	public static $force_load_all_subsets = false;

	/**
	 * If set to true, forces loading ALL variants.
	 *
	 * @static
	 * @access public
	 * @var bool
	 */
	public static $force_load_all_variants = false;

	/**
	 * The array of fonts
	 *
	 * @access public
	 * @var array
	 */
	public $fonts = array();

	/**
	 * An array of all google fonts.
	 *
	 * @access private
	 * @var array
	 */
	private $google_fonts = array();

	public static $cached_google_fonts = [];

	/**
	 * An array of fonts that should be hosted locally instead of served via the google-CDN.
	 *
	 * @access protected
	 * @since 3.0.32
	 * @var array
	 */
	protected $hosted_fonts = array();

	/**
	 * The class constructor.
	 */
	private function __construct() {}

	public function get_fonts_to_load(){
		return apply_filters( 'kirki_enqueue_google_fonts', $this->fonts );
	}

	/**
	 * Loop fields and extract typography fields with conditions met.
	 *
	 * @return void
	 */
	public function set_fonts_to_load(){

		foreach ( Kirki::$fields as $field ) {

			if ( ! (isset( $field['kirki_config'] ) && Kirki_Modules_Webfonts::CONFIG_ID === $field['kirki_config']) ) {
				continue;
			}

			// Process typography fields only
			if ( ! (isset( $field['type'] ) && 'kirki-typography' === $field['type']) ) {
				continue;
			}

			// Only continue if field dependencies are met.
			if ( ! empty( $field['required'] ) ) {

				$valid = true;

				foreach ( $field['required'] as $requirement ) {
					if ( isset( $requirement['setting'] ) && isset( $requirement['value'] ) && isset( $requirement['operator'] ) ) {
						$controller_value = Kirki_Values::get_value( Kirki_Modules_Webfonts::CONFIG_ID, $requirement['setting'] );
						if ( ! Kirki_Helper::compare_values( $controller_value, $requirement['value'], $requirement['operator'] ) ) {
							$valid = false;
						}
					}
				}

				if ( ! $valid ) {
					continue;
				}

			}

			$this->set_font( $field );
		}

	}

	/**
	 * Processes the arguments of a field
	 * determines if it's a typography field
	 * and if it is, then takes appropriate actions.
	 *
	 * @param array $args The field arguments.
	 */
	private function set_font( $args ) {

		// Get the value.
		$value = Kirki_Values::get_sanitized_field_value( $args );

		// If we don't have a font-family then we can skip this.
		if ( ! isset( $value['font-family'] ) || in_array( $value['font-family'], $this->hosted_fonts, true ) ) {
			return;
		}

		// If not a google-font, then we can skip this.
		if ( ! isset( $value['font-family'] ) || ! self::is_google_font( $value['font-family'] ) ) {
			return;
		}

		// Set a default value for variants.
		if ( ! isset( $value['variant'] ) ) {
			$value['variant'] = 'regular';
		}

		// Add the requested google-font.
		if ( ! isset( $this->fonts[ $value['font-family'] ] ) ) {
			$this->fonts[ $value['font-family'] ] = array();
		}
		if ( ! in_array( $value['variant'], $this->fonts[ $value['font-family'] ], true ) ) {
			$this->fonts[ $value['font-family'] ][] = $value['variant'];
		}

		// Are we force-loading all variants?
		if ( true === self::$force_load_all_variants ) {
			$all_variants               = Kirki_Fonts::get_all_variants();
			$args['choices']['variant'] = array_keys( $all_variants );
		}

		if ( ! empty( $args['choices']['variant'] ) && is_array( $args['choices']['variant'] ) ) {
			foreach ( $args['choices']['variant'] as $extra_variant ) {
				$this->fonts[ $value['font-family'] ][] = $extra_variant;
			}
		}

	}

	/**
	 * Determines the validity of the selected font as well as its properties.
	 * This is vital to make sure that the google-font script that we'll generate later
	 * does not contain any invalid options.
	 */
	public function process_fonts() {

		$cached_google_fonts = self::get_cached_google_fonts_list();

		// Go through our fields and populate $this->fonts.
		$this->set_fonts_to_load();

		// Early exit if font-family is empty.
		if ( empty( $this->fonts ) ) {
			return;
		}

		foreach ( $this->fonts as $font => $variants ) {

			// Determine if this is indeed a google font or not.
			// If it's not, then just remove it from the array.
			if ( ! array_key_exists( $font, $cached_google_fonts ) ) {
				unset( $this->fonts[ $font ] );
				continue;
			}

			// Get all valid font variants for this font.
			$font_variants = array();
			if ( isset( $cached_google_fonts[ $font ]['variants'] ) ) {
				$font_variants = $cached_google_fonts[ $font ]['variants'];
			}
			foreach ( $variants as $variant ) {

				// If this is not a valid variant for this font-family
				// then unset it and move on to the next one.
				if ( ! in_array( strval( $variant ), $font_variants, true ) ) {
					$variant_key = array_search( $variant, $this->fonts[ $font ], true );
					unset( $this->fonts[ $font ][ $variant_key ] );
					continue;
				}
			}
		}
	}

	/**
	 * Determine if a font-name is a valid google font or not.
	 *
	 * @static
	 * @access public
	 * @param string $fontname The name of the font we want to check.
	 * @return bool
	 */
	public static function is_google_font( $fontname ) {
		return ( array_key_exists( $fontname, self::$cached_google_fonts ?? [] ) );
	}

	public static function get_cached_google_fonts_list() {

		$transient_name = 'kirki_googlefonts_cache_' . REY_CORE_VERSION;

		// Get fonts from cache.
		self::$cached_google_fonts = get_transient( $transient_name );

		/**
		 * Reset the cache if we're using action=kirki-reset-cache in the URL.
		 *
		 * Note to code reviewers:
		 * There's no need to check nonces or anything else, this is a simple true/false evaluation.
		 */
		if ( ! empty( $_GET['action'] ) && 'kirki-reset-cache' === $_GET['action'] ) { // phpcs:ignore WordPress.Security.NonceVerification
			self::$cached_google_fonts = false;
		}

		// If cache is populated, return cached fonts array.
		if ( false !== self::$cached_google_fonts ) {
			return self::$cached_google_fonts;
		}

		// If we got this far, cache was empty so we need to get from JSON.
		ob_start();
		include wp_normalize_path( dirname( __FILE__ ) . '/webfonts.json' ); // phpcs:ignore WPThemeReview.CoreFunctionality.FileInclude
		$fonts_json = ob_get_clean();

		$fonts = json_decode( $fonts_json, true );

		$cached_google_fonts = [];

		if ( is_array( $fonts ) ) {
			foreach ( $fonts['items'] as $font ) {
				$cached_google_fonts[ $font['family'] ] = array(
					'label'    => $font['family'],
					'variants' => $font['variants'],
					'category' => $font['category'],
				);
			}
		}

		// Apply the 'kirki_fonts_google_fonts' filter.
		self::$cached_google_fonts = apply_filters( 'kirki_fonts_google_fonts', $cached_google_fonts );

		// Save the array in cache.
		set_transient( $transient_name, self::$cached_google_fonts, apply_filters( 'kirki_googlefonts_transient_time', MONTH_IN_SECONDS ) );

		return self::$cached_google_fonts;
	}

	/**
	 * Get the one, true instance of this class.
	 * Prevents performance issues since this is only loaded once.
	 *
	 * @return object Kirki_Fonts_Google
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new Kirki_Fonts_Google();
		}
		return self::$instance;
	}
}
