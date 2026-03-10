<?php

/**
 * Fonts-downloading manager.
 *
 * @since 2.5.5
 */
class Kirki_Fonts_Downloader {

	const FOLDER = 'fonts';

	public $transient_name;
	public $url;

	public function __construct( $url = '', $transient_name = '' ){
		$this->transient_name = $transient_name;
		$this->url = $url;
	}

	/**
	 * Get styles from URL.
	 *
	 * @access public
	 * @since 2.5.5
	 * @return string
	 */
	public function get_styles() {

		if( ! ($this->url && $this->transient_name) ){
			return '';
		}

		$transient_name = $this->transient_name . md5($this->url);

		if( false !== ( $data = get_transient($transient_name) ) ){
			if( ! empty($data['css']) && ! empty($data['files']) ){
				return $this->process_transient_data($data['css'], $data['files']);
			}
		}

		// get the stylesheet css contents remotely
		$remote_css = \ReyCore\Webfonts::get_remote_url_contents($this->url);

		// get the remote => local relative paths
		$files = $this->get_local_files_from_css( $remote_css );

		set_transient($transient_name, ['css' => $remote_css, 'files' => $files], MONTH_IN_SECONDS);

		return $this->process_transient_data($remote_css, $files);
	}

	protected function process_transient_data( $remote_css, $files ){

		$fonts_url_path = trailingslashit(content_url()) . self::FOLDER;

		// Convert paths to URLs.
		foreach ( (array) $files as $remote_url => $local_url ) {
			$files[ $remote_url ] = $fonts_url_path . $local_url;
		}

		return str_replace( array_keys( $files ), array_values( $files ), $remote_css );
	}

	/**
	 * Download files mentioned in our CSS locally.
	 *
	 * @access protected
	 * @since 2.5.5
	 * @param string $css The CSS we want to parse.
	 * @return array      Returns an array of remote URLs and their local counterparts.
	 */
	protected function get_local_files_from_css( $css ) {

		if( empty($css) ){
			return [];
		}

		if( ! ($fs = reycore__wp_filesystem()) ){
			return [];
		}

		$fonts_dir_path = trailingslashit(WP_CONTENT_DIR) . self::FOLDER;

		// ensure fonts dir exists
		if ( ! $fs->is_dir( $fonts_dir_path ) ) {
			$fs->mkdir( $fonts_dir_path );
		}

		// extract filepaths from the CSS
		$font_files = $this->extract_filepaths_from_css( $css );

		$stored = [];

		foreach ( $font_files as $font_family => $files ) {

			// The folder path for this font-family.
			$family_folder_path = trailingslashit($fonts_dir_path) . $font_family;

			// ensure fonts dir exists
			if ( ! $fs->is_dir( $family_folder_path ) ) {
				$fs->mkdir( $family_folder_path );
			}

			foreach ( $files as $url ) {

				// Get the filename.
				// $filename  = basename( wp_parse_url( $url, PHP_URL_PATH ) );
				// $filename  = substr( md5( sanitize_url( $url ) ), 0, 16 ) . '.woff';
				// seems like URLs have changed and cannot extract the actual filename
				// so we'll use the md5 of the URL as the filename
				$filename  = substr( md5( sanitize_url( $url ) ), 0, 16 );

				// set the font file server path
				$font_path = trailingslashit($family_folder_path) . $filename;

				// set a relative path to be stored (to avoid site migration problems)
				$rel_font_path = str_replace($fonts_dir_path, '', $font_path);

				// check if font file exists already
				if ( $fs->is_file( $font_path ) ) {
					// already exists as a file but not cached in DB
					$stored[ $url ] = $rel_font_path;
				}

				else {

					if ( ! function_exists( 'download_url' ) ) {
						require_once wp_normalize_path( ABSPATH . '/wp-admin/includes/file.php' );
					}

					// Download file to temporary location.
					$tmp_path = download_url( $url );

					// Make sure there were no errors.
					if ( is_wp_error( $tmp_path ) ) {
						continue;
					}

					// Move temp file to final destination.
					if ( $fs->move( $tmp_path, $font_path, true ) ) {
						$stored[ $url ] = $rel_font_path;
						self::log( sprintf('Downloaded font. Remote Url: %s', $url) );
					}
				}
			}
		}

		return $stored;
	}



	/**
	 * Get font files from the CSS.
	 *
	 * @access public
	 * @since 2.5.5
	 * @param string $css The CSS we want to parse.
	 * @return array      Returns an array of font-families and the font-files used.
	 */
	public function extract_filepaths_from_css( $css ) {

		$font_faces = explode( '@font-face', $css );

		$result = [];

		// Loop all our font-face declarations.
		foreach ( $font_faces as $font_face ) {

			// Make sure we only process styles inside this declaration.
			$style = explode( '}', $font_face )[0];

			// Sanity check.
			if ( false === strpos( $style, 'font-family' ) ) {
				continue;
			}

			// Get an array of our font-families.
			preg_match_all( '/font-family.*?\;/', $style, $matched_font_families );

			// Get an array of our font-files.
			preg_match_all( '/url\(.*?\)/i', $style, $matched_font_files );

			// get the format
			// preg_match_all( '/format\(.*?\)/i', $style, $matched_font_files_format );

			// Get the font-family name.
			$font_family = 'unknown';
			if ( isset( $matched_font_families[0] ) && isset( $matched_font_families[0][0] ) ) {
				$font_family = rtrim( ltrim( $matched_font_families[0][0], 'font-family:' ), ';' );
				$font_family = trim( str_replace( array( "'", ';' ), '', $font_family ) );
				$font_family = sanitize_key( strtolower( str_replace( ' ', '-', $font_family ) ) );
			}

			// Make sure the font-family is set in our array.
			if ( ! isset( $result[ $font_family ] ) ) {
				$result[ $font_family ] = [];
			}

			// Get files for this font-family and add them to the array.

			foreach ( $matched_font_files as $match ) {

				// Sanity check.
				if ( ! isset( $match[0] ) ) {
					continue;
				}

				// Add the file URL.
				$result[ $font_family ][] = rtrim( ltrim( $match[0], 'url(' ), ')' );

			}

			// Make sure we have unique items.
			// We're using array_flip here instead of array_unique for improved performance.
			$result[ $font_family ] = array_flip( array_flip( $result[ $font_family ] ) );
		}

		return $result;
	}

	public static function log($message){

		do_action( 'qm/debug', $message );

		if( \ReyCore\Plugin::is_dev_mode() ){
			error_log(var_export( $message, true));
		}

	}

}
