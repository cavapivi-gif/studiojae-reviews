<?php
/**
 * Adds the Webfont Loader to load fonts asyncronously.
 *
 * @package     Kirki
 * @category    Core
 * @author      Ari Stathopoulos (@aristath)
 * @copyright   Copyright (c) 2020, David Vongries
 * @license     https://opensource.org/licenses/MIT
 * @since       3.0
 */

/**
 * Manages the way Google Fonts are enqueued.
 */
final class Kirki_Modules_Webfonts_Async {

	public $_fonts_google;

	/**
	 * Fonts to load.
	 *
	 * @access protected
	 * @since 3.0.26
	 * @var array
	 */
	protected $fonts_to_load = array();

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 3.0
	 */
	public function __construct() {
		add_action( 'customize_controls_init', array( $this, 'populate_fonts' ) );
		add_action( 'customize_controls_head', array( $this, 'webfont_loader_script' ), 30 );
		add_action( 'customize_preview_init', array( $this, 'webfont_loader_script_file' ), 30 );
	}

	/**
	 * Webfont Loader for Google Fonts.
	 *
	 * @access public
	 * @since 3.0.0
	 */
	public function populate_fonts() {

		$this->_fonts_google = Kirki_Fonts_Google::get_instance();

		// Goes through $this->fonts and adds or removes things as needed.
		$this->_fonts_google->process_fonts();

		foreach ( $this->_fonts_google->get_fonts_to_load() as $font => $weights ) {

			foreach ( $weights as $key => $value ) {
				if ( 'italic' === $value ) {
					$weights[ $key ] = '400i';
				} else {
					$weights[ $key ] = str_replace( array( 'regular', 'bold', 'italic' ), array( '400', '', 'i' ), $value );
				}
			}

			$this->fonts_to_load[] = $font . ':' . join( ',', $weights ) . ':cyrillic,cyrillic-ext,devanagari,greek,greek-ext,khmer,latin,latin-ext,vietnamese,hebrew,arabic,bengali,gujarati,tamil,telugu,thai';
		}

	}

	/**
	 * Webfont Loader script for Google Fonts.
	 *
	 * @access public
	 * @since 3.0.0
	 */
	public function webfont_loader_script_file() {
		wp_enqueue_script( 'webfont-loader', trailingslashit( Kirki::$url ) . 'modules/webfont-loader/vendor-typekit/webfontloader.js', array(), REY_CORE_VERSION, true );
	}

	/**
	 * Webfont Loader script for Google Fonts.
	 *
	 * @access public
	 * @since 3.0.0
	 */
	public function webfont_loader_script() {

		if( empty( $this->fonts_to_load ) ){
			return;
		}

		$this->webfont_loader_script_file();

		wp_add_inline_script(
			'webfont-loader',
			'WebFont.load({google:{families:[\'' . join( '\', \'', $this->fonts_to_load ) . '\']}});',
			'after'
		);

	}
}
