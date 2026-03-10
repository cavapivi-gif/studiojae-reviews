<?php
namespace ReyCore;
if (!defined('ABSPATH')) exit; // Exit if accessed directly

class WebfontsAdobe extends WebfontsBase {

	const TRANSIENT_NAME__LIST = 'rey_adobe_fonts';
	const TRANSIENT_NAME__CSS = 'rey_adobe_fonts_css';

	public function __construct() {
		add_action( 'admin_enqueue_scripts', [ $this, 'add_revolution_enqueue_scripts' ] );
	}

	public function get_id(){
		return 'adobe';
	}

	public function get_list(){

		reycore__maybe_disable_obj_cache();

		if ( $stored_list = get_transient( self::TRANSIENT_NAME__LIST ) ) {
			return $stored_list;
		}

		$list = [];

		$adobe_project_id = reycore__acf_get_field('adobe_fonts_project_id', REY_CORE_THEME_NAME);

		if( ! $adobe_project_id ) {
			return $list;
		}

		// Get the contents of the remote URL.
		$contents = Webfonts::get_remote_url_contents(
			sprintf('https://typekit.com/api/v1/json/kits/%s/published', $adobe_project_id),
			[
				'timeout' => '30',
			]
		);

		if ( ! $contents ) {
			return $list;
		}

		$data = json_decode( $contents, true );

		if( isset($data['kit']['families']) && $families = $data['kit']['families'] ){
			foreach ( $families as $i => $family ) {

				$the_font = [
					'font_name'     => $family['slug'],
					'font_variants' => [],
					'font_subsets'  => '',
					'family'        => str_replace( ' ', '-', $family['name'] ),
					'type'          => 'adobe',
				];

				if( isset($family['css_names']) && isset($family['css_names'][0]) && $css_handle = $family['css_names'][0] ){
					$the_font['css_handle'] = $css_handle;
				}

				$list[$i] = $the_font;

				foreach ( $family['variations'] as $variation ) {
					$variations = str_split( $variation );
					$weight = $variations[1] . '00';
					if ( ! in_array( $weight, $list[$i]['font_variants'] ) ) {
						$list[$i]['font_variants'][] = $weight;
					}
				}
			}

			// Set the transient for a week.
			set_transient( self::TRANSIENT_NAME__LIST, $list, WEEK_IN_SECONDS );
		}

		return $list;
	}

	public function extract_css(){

		if( ! ($url = $this->get_adobe_fonts_url()) ){
			return '';
		}

		$c_transient_name = self::TRANSIENT_NAME__CSS . md5( $url );

		// check if transient exists
		if( false !== ( $css = get_transient($c_transient_name) ) ){
			return $css;
		}

		// get the stylesheet css contents remotely
		$css = Webfonts::get_remote_url_contents($url);

		// Add font-display:swap to improve rendering speed.
		$css = str_replace( '@font-face {', '@font-face{', $css );
		$css = str_replace( '@font-face{', '@font-face{font-display:swap;', $css );

		// Remove blank lines and extra spaces.
		$css = str_replace(
			array( ': ', ';  ', '; ', '  ' ),
			array( ':', ';', ';', ' ' ),
			preg_replace( "/\r|\n/", '', $css )
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
		$css = wp_strip_all_tags( $css ); // phpcs:ignore WordPress.Security.EscapeOutput

		// store
		set_transient($c_transient_name, $css, MONTH_IN_SECONDS);

		return $css;
	}

	public function get_css(){

		$css = $this->extract_css();

		if( $css ){

			foreach (Webfonts::get_typography_vars() as $control_key => $vars ) {

				if( empty($vars['font-family']) ){
					continue;
				}

				// no point continuing
				if( strpos($css, $vars['font-family']) === false ){
					continue;
				}

				$pattern = "/@font-face\s*{[^}]*font-family:\s*[\"']{$vars['font-family']}[\"'][^}]*}/";

				preg_match_all($pattern, $css, $matches);

				if( empty($matches[0]) ) {
					continue;
				}

				$contents = implode('', $matches[0]);

				$FR_contents = str_replace(
					"\"{$vars['font-family']}\";",
					"\"{$vars['nice-name']}\";",
					stripslashes($contents),
					$FR_counter
				);

				if( $FR_counter ){
					$css .= $FR_contents;
				}
			}

		}

		return $css;
	}

	/**
	 * Get the Adobe Fonts embed URL
	 *
	 * @since 1.0.0
	 */
	private function get_adobe_fonts_url() {

		if( $adobe_project_id = reycore__acf_get_field('adobe_fonts_project_id', REY_CORE_THEME_NAME) ) {
			return sprintf( 'https://use.typekit.net/%s.css', $adobe_project_id );
		}

		return false;
	}

	/**
	 * Enqueue Typekit CSS.
	 *
	 * @return void
	 */
	public function adobe_fonts_embed_css() {
		if ( $embed_url = $this->get_adobe_fonts_url() ) {
			wp_enqueue_style( 'rey-adobe-fonts', $embed_url, [] );
		}
	}

	/**
	 * Enqueue styles in Revolution Slider Editor
	 *
	 * @since 1.0.0
	 */
	public function add_revolution_enqueue_scripts() {
		if ( ($screen = get_current_screen()) && 'toplevel_page_revslider' === $screen->id ) {
			$this->adobe_fonts_embed_css();
		}
	}

	public function preconnect_urls(){

		if( ! $this->get_list() ) {
			return [];
		}

		$urls[] = [
			'href' => '//use.typekit.net',
			'crossorigin',
		];

		return $urls;
	}

	public function get_transients(){
		return [ self::TRANSIENT_NAME__LIST, self::TRANSIENT_NAME__CSS ];
	}
}
