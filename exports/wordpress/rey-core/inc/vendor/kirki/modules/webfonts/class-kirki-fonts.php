<?php
/**
 * A simple object containing properties for fonts.
 *
 * @package     Kirki
 * @category    Core
 * @author      Ari Stathopoulos (@aristath)
 * @copyright   Copyright (c) 2020, David Vongries
 * @license     https://opensource.org/licenses/MIT
 * @since       1.0
 */

/**
 * The Kirki_Fonts object.
 */
final class Kirki_Fonts {

	/**
	 * The mode we'll be using to add google fonts.
	 * This is a todo item, not yet functional.
	 *
	 * @static
	 * @todo
	 * @access public
	 * @var string
	 */
	public static $mode = 'link';

	/**
	 * Holds a single instance of this object.
	 *
	 * @static
	 * @access private
	 * @var null|object
	 */
	private static $instance = null;

	/**
	 * An array of our google fonts.
	 *
	 * @static
	 * @access public
	 * @var null|object
	 */
	public static $google_fonts = null;

	/**
	 * The class constructor.
	 */
	private function __construct() {}

	/**
	 * Get the one, true instance of this class.
	 * Prevents performance issues since this is only loaded once.
	 *
	 * @return object Kirki_Fonts
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Return an array of standard websafe fonts.
	 *
	 * @return array    Standard websafe fonts.
	 */
	public static function get_standard_fonts() {
		$standard_fonts = array(
			'serif'      => array(
				'label' => 'Serif',
				'stack' => 'Georgia,Times,\'Times New Roman\',serif',
				'local' => 'Times New Roman',
			),
			'sans-serif' => array(
				'label' => 'Sans Serif',
				'stack' => 'system-ui, sans-serif',
				'local' => 'system-ui',
			),
			'monospace'  => array(
				'label' => 'Monospace',
				'stack' => 'Monaco,\'Lucida Sans Typewriter\',\'Lucida Typewriter\',\'Courier New\',Courier,monospace',
				'local' => 'Courier New',
			),
			'arial'     => array(
				'label' => 'Arial',
				'stack' => 'Arial,Helvetica,sans-serif',
				'local' => 'Arial',
			),
			'arial_black' => array(
				'label' => 'Arial Black',
				'stack' => '\'Arial Black\', Gadget, sans-serif',
				'local' => 'Arial Black',
			),
			'verdana'   => array(
				'label' => 'Verdana',
				'stack' => 'Verdana, Geneva, sans-serif',
				'local' => 'Verdana',
			),
			'tahoma'    => array(
				'label' => 'Tahoma',
				'stack' => 'Tahoma, Geneva, sans-serif',
				'local' => 'Tahoma',
			),
			'trebuchet' => array(
				'label' => 'Trebuchet MS',
				'stack' => 'Trebuchet MS, Helvetica, sans-serif',
				'local' => 'Trebuchet MS',
			),
			'georgia'   => array(
				'label' => 'Georgia',
				'stack' => 'Georgia, Times, \'Times New Roman\', serif',
				'local' => 'Georgia',
			),
			'times'     => array(
				'label' => 'Times New Roman',
				'stack' => 'Times New Roman, Times, serif',
				'local' => 'Times New Roman',
			),
		);

		return apply_filters( 'kirki_fonts_standard_fonts', $standard_fonts );
	}

	public static function get_standard_fonts_by_stack() {
		$fonts = self::get_standard_fonts();

		$mapped = [];

		foreach ($fonts as $key => $font_data) {
			if ( isset($font_data['stack']) ) {
				$mapped[$font_data['stack']] = array_merge(
					['key' => $key],
					$font_data
				);
			}
		}

		return $mapped;
	}


	/**
	 * Return an array of all available Google Fonts.
	 *
	 * @return array    All Google Fonts.
	 */
	public static function get_google_fonts() {
		return [];
	}

	/**
	 * Returns an array of all available subsets.
	 *
	 * @static
	 * @access public
	 * @return array
	 */
	public static function get_google_font_subsets() {
		return array(
			'cyrillic'     => 'Cyrillic',
			'cyrillic-ext' => 'Cyrillic Extended',
			'devanagari'   => 'Devanagari',
			'greek'        => 'Greek',
			'greek-ext'    => 'Greek Extended',
			'khmer'        => 'Khmer',
			'latin'        => 'Latin',
			'latin-ext'    => 'Latin Extended',
			'vietnamese'   => 'Vietnamese',
			'hebrew'       => 'Hebrew',
			'arabic'       => 'Arabic',
			'bengali'      => 'Bengali',
			'gujarati'     => 'Gujarati',
			'tamil'        => 'Tamil',
			'telugu'       => 'Telugu',
			'thai'         => 'Thai',
		);
	}

	/**
	 * Dummy function to avoid issues with backwards-compatibility.
	 * This is not functional, but it will prevent PHP Fatal errors.
	 *
	 * @static
	 * @access public
	 */
	public static function get_google_font_uri() {}

	/**
	 * Returns an array of all available variants.
	 *
	 * @static
	 * @access public
	 * @return array
	 */
	public static function get_all_variants() {
		return array(
			'100'       => esc_html__( 'Ultra-Light 100', 'kirki' ),
			'100light'  => esc_html__( 'Ultra-Light 100', 'kirki' ),
			'100italic' => esc_html__( 'Ultra-Light 100 Italic', 'kirki' ),
			'200'       => esc_html__( 'Light 200', 'kirki' ),
			'200italic' => esc_html__( 'Light 200 Italic', 'kirki' ),
			'300'       => esc_html__( 'Book 300', 'kirki' ),
			'300italic' => esc_html__( 'Book 300 Italic', 'kirki' ),
			'400'       => esc_html__( 'Normal 400', 'kirki' ),
			'regular'   => esc_html__( 'Normal 400', 'kirki' ),
			'italic'    => esc_html__( 'Normal 400 Italic', 'kirki' ),
			'500'       => esc_html__( 'Medium 500', 'kirki' ),
			'500italic' => esc_html__( 'Medium 500 Italic', 'kirki' ),
			'600'       => esc_html__( 'Semi-Bold 600', 'kirki' ),
			'600bold'   => esc_html__( 'Semi-Bold 600', 'kirki' ),
			'600italic' => esc_html__( 'Semi-Bold 600 Italic', 'kirki' ),
			'700'       => esc_html__( 'Bold 700', 'kirki' ),
			'700italic' => esc_html__( 'Bold 700 Italic', 'kirki' ),
			'800'       => esc_html__( 'Extra-Bold 800', 'kirki' ),
			'800bold'   => esc_html__( 'Extra-Bold 800', 'kirki' ),
			'800italic' => esc_html__( 'Extra-Bold 800 Italic', 'kirki' ),
			'900'       => esc_html__( 'Ultra-Bold 900', 'kirki' ),
			'900bold'   => esc_html__( 'Ultra-Bold 900', 'kirki' ),
			'900italic' => esc_html__( 'Ultra-Bold 900 Italic', 'kirki' ),
		);
	}

}
