<?php
namespace ReyCore;
if (!defined('ABSPATH')) exit; // Exit if accessed directly

class WebfontsCustom extends WebfontsBase {

	public function __construct() {}

	public function get_id(){
		return 'custom';
	}

	public function get_list(){

		$list = [];

		$custom_fonts = reycore__acf_get_field('custom_fonts', REY_CORE_THEME_NAME);

		if( ! $custom_fonts ){
			return $list;
		}

		foreach ( (array) $custom_fonts as $font) {

			if( ! is_array($font) ){
				continue;
			}

			if( empty($font['name']) ){
				continue;
			}

			$list[$font['name']] = [
				'font_name' => $font['name'],
				'font_variants' => [ '100', '200', '300', '400', '500', '600', '700', '800', '900'],
				'font_subsets' => '',
				'type' => 'custom',
			];
		}

		return $list;
	}

	public function get_css(){

		$css = '';

		$custom_fonts = reycore__acf_get_field('custom_fonts', REY_CORE_THEME_NAME);

		if( ! is_array($custom_fonts) ){
			return $css;
		}

		foreach ( (array) $custom_fonts as $font ) :

			$css .= self::get_font_code($font['name'], $font);

			// register primary/secondary
			foreach (Webfonts::get_typography_vars() as $control_key => $vars ) {

				if( empty($vars['font-family']) ){
					continue;
				}

				if( $font['name'] !== $vars['font-family'] ){
					continue;
				}

				$css .= self::get_font_code($vars['nice-name'], $font);

			}

		endforeach;

		return $css;

	}

	public static function get_font_code($name, $font){

		$font_css = '@font-face { font-family:"' . esc_attr( $name ) . '";';
		$font_css .= 'src:';
		$arr = [];

		foreach ([
			'font_woff2' => 'woff2',
			'font_woff'  => 'woff',
			'font_ttf'   => 'truetype',
			'font_otf'   => 'opentype',
			'font_svg'   => 'svg',
		] as $key => $format) {

			if( isset($font[$key]) && ! empty($font[$key]) ){

				$url = '';

				if( isset($font[$key]['url']) ){ // array
					if( ! empty($font[$key]['url']) ){ // check empty
						$url = $font[$key]['url'];
					}
				}
				else {
					if( is_numeric($font[$key]) ){
						$url = wp_get_attachment_url($font[$key]);
					}
					else {
						$url = $font[$key];
					}
				}

				if( $url ){
					$arr[] = sprintf('url(%s) format("%s")', esc_url( $url ), $format);
				}
			}
		}

		$font_css .= join( ', ', $arr ) . ';';
		$font_css .= 'font-display: ' . esc_attr( $font['font_display'] ) . ';';

		if( isset($font['weight']) ){
			$font_css .= 'font-weight: ' . esc_attr( $font['weight'] ) . ';';
		}

		$font_css .= '}';

		return $font_css;
	}
}
