<?php
namespace ReyCore;
if (!defined('ABSPATH')) exit; // Exit if accessed directly

class WebfontsGoogle extends WebfontsBase {

	const TRANSIENT_NAME__CSS = 'rey_google_fonts';

	const CUSTOMIZER_FONTS_OPTION = 'rey_customizer_google_fonts';

	// https://developers.google.com/fonts/docs/css2
	public static $apis = '';

	public $fonts_to_append_symbol = [];

	public function __construct() {
		add_filter( 'reycore/styles/root_css', [$this, 'root_css']);
		add_action( 'reycore/fonts/css_contents', [$this, 'css_contents'], 10, 2 );
		add_action( 'wp_head', [ $this, 'remove_elementor_google_fonts_duplicates' ], 6 ); // before Elementor default "7" priority
	}

	public function get_id(){
		return 'google';
	}

	public function preconnect_urls(){

		if( self::maybe_selfhost_fonts() ){
			return [];
		}

		$urls = [];

		$urls[] = [
			'href' => '//fonts.gstatic.com',
			'crossorigin',
		];

		$urls[] = [
			'href' => '//fonts.googleapis.com',
		];

		return $urls;
	}

	public static function maybe_selfhost_fonts(){

		static $status;

		if( is_null($status) ){
			$value = get_field('self_host_fonts', REY_CORE_THEME_NAME);
			$status = $value || is_null($value);
		}

		return $status;
	}

	public function get_transients(){
		return [ self::TRANSIENT_NAME__CSS ];
	}

	/**
	 * Define Primary fonts with wrapper, in root CSS
	 *
	 * @param string $styles
	 * @return string
	 */
	public function root_css($styles){

		$wrappers = apply_filters('reycore/styles/font_quotes', false) ? '"' : '';

		// Font Family typography
		if( ($primary_typo = get_theme_mod('typography_primary', [])) && isset($primary_typo['font-family']) && $pff = $primary_typo['font-family'] ){
			$styles .= "--primary-ff:{$wrappers}{$pff}{$wrappers}, \"Helvetica Neue\", Helvetica, Arial, sans-serif;";
		}

		if( ($secondary_typo = get_theme_mod('typography_secondary', [])) && isset($secondary_typo['font-family']) && $sff = $secondary_typo['font-family'] ){
			$styles .= "--secondary-ff:{$wrappers}{$sff}{$wrappers}, \"Helvetica Neue\", Helvetica, Arial, sans-serif;";
		}

		return $styles;
	}

	/**
	 * Make primary & secondary aliases.
	 * Mainly to be used in Elementor select lists because it's not support with css vars.
	 *
	 * @param string $contents
	 * @param string $font
	 * @return void
	 */
	public function css_contents($contents, $font){

		foreach (Webfonts::get_typography_vars() as $key => $vars ) {

			if( empty($vars['font-family']) ){
				continue;
			}

			if( $font !== $vars['font-family'] ){
				continue;
			}

			$FR_contents = str_replace(
				"'$font';",
				"'{$vars['nice-name']}';",
				stripslashes($contents),
				$FR_counter
			);

			if( $FR_counter ){
				echo $FR_contents;
			}
		}
	}

	/**
	 * Unset duplicated Elementor Google fonts URL stylesheets if they're already loaded in Rey's Customizer options.
	 *
	 * @return void
	 */
	public function remove_elementor_google_fonts_duplicates(){

		if( ! class_exists('\Elementor\Plugin') ){
			return;
		}

		if( ! isset(\Elementor\Plugin::instance()->frontend->fonts_to_enqueue) ){
			return;
		}

		if( ! ($fonts_to_enqueue = \Elementor\Plugin::instance()->frontend->fonts_to_enqueue) ){
			return;
		}

		if( ! apply_filters('reycore/webfonts/google/remove_elementor_duplicates', true) ){
			return;
		}

		if( ! ($customizer_fonts = get_option(self::CUSTOMIZER_FONTS_OPTION)) ){
			return;
		}

		foreach ($fonts_to_enqueue as $key => $value) {
			if( in_array($value, array_keys($customizer_fonts), true) ){
				unset(\Elementor\Plugin::instance()->frontend->fonts_to_enqueue[$key]);
			}
		}

	}
}
