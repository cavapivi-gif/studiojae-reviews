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
final class Kirki_Modules_Webfonts_Embed {

	public $_fonts_google;

	const LOCAL_TRANSIENT = 'rey_local_fonts_contents_';
	const REMOTE_TRANSIENT = 'rey_remote_fonts_contents_';

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 3.0
	 */
	public function __construct() {
		add_action( 'reycore/customizer/make_dynamic_css', [$this, 'fonts_css'] );
	}

	/**
	 * Append contents of Google Fonts stylesheets,
	 * into the cached CSS.
	 * Runs only if the css is not stored.
	 *
	 * @return void
	 */
	public function fonts_css() {

		$this->_fonts_google = Kirki_Fonts_Google::get_instance();

		// Goes through $this->fonts and adds or removes things as needed.
		$this->_fonts_google->process_fonts();

		/**
		 * Store unique customize font families into an option for later use.
		 */
		update_option(\ReyCore\WebfontsGoogle::CUSTOMIZER_FONTS_OPTION, $this->_fonts_google->get_fonts_to_load());

		foreach ( $this->_fonts_google->get_fonts_to_load() as $font => $weights ) {

			foreach ( $weights as $key => $value ) {
				if ( 'italic' === $value ) {
					$weights[ $key ] = '400i';
				} else {
					$weights[ $key ] = str_replace( array( 'regular', 'bold', 'italic' ), array( '400', '', 'i' ), $value );
				}
			}

			$weights = join( ',', \ReyCore\Webfonts::get_google_fonts_weights($weights) );

			$query_args = [
				'family' => str_replace( ' ', '+', trim( $font ) ) . ":" . $weights,
				'display' => 'swap'
			];

			if( $subsets = \ReyCore\Webfonts::get_google_fonts_subsets() ){
				$query_args['subset'] = implode(',', $subsets );
			}

			$url = add_query_arg( $query_args, 'https://fonts.googleapis.com/css' . \ReyCore\WebfontsGoogle::$apis );

			// get CSS with local paths
			if( \ReyCore\WebfontsGoogle::maybe_selfhost_fonts() ){
				if ( ! class_exists( 'Kirki_Fonts_Downloader' ) ) {
					include_once wp_normalize_path( dirname( __FILE__ ) . '/class-kirki-fonts-downloader.php' ); // phpcs:ignore WPThemeReview.CoreFunctionality.FileInclude
				}
				$downloader = new Kirki_Fonts_Downloader( $url, self::LOCAL_TRANSIENT );
				$contents   = $downloader->get_styles();
			}

			// get CSS with remote paths
			else {
				$c_transient_name = self::REMOTE_TRANSIENT . md5($url);
				// check if transient exists
				if( false === ( $contents = get_transient($c_transient_name) ) ){
					// get the stylesheet css contents remotely
					$contents = \ReyCore\Webfonts::get_remote_url_contents($url);
					// store
					set_transient($c_transient_name, $contents, MONTH_IN_SECONDS);
				}
			}

			if ( $contents ) {

				// Remove blank lines and extra spaces.
				$contents = str_replace(
					array( ': ', ';  ', '; ', '  ' ),
					array( ':', ';', ';', ' ' ),
					preg_replace( "/\r|\n/", '', $contents )
				);

				/**
				 * Note to code reviewers:
				 *
				 * Though all output should be run through an escaping function, this is pure CSS
				 * and it is added on a call that has a PHP `header( 'Content-type: text/css' );`.
				 * No code, script or anything else can be executed from inside a stylesheet.
				 * For extra security we're using the wp_strip_all_tags() function here
				 * just to make sure there's no <script> tags in there or anything else.
				 */
				echo wp_strip_all_tags( $contents ); // phpcs:ignore WordPress.Security.EscapeOutput

				do_action('reycore/fonts/css_contents', $contents, $font, $weights, $this );
			}

		}

	}
}
