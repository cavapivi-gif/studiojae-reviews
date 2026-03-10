<?php
namespace ReyCore\Libs;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Colors {
	/**
	 * adjust brightness of a colour
	 * not the best way to do it but works well enough here
	 *
	 * @param string $hex
	 * @param string $percentage -100 to 100
	 * @return mixed
	 */
	public static function adjust_color_brightness( $hex, $percentage ) {

		if( strpos($hex, 'rgb') !== false ){
			return $hex;
		}

		$hex_hash = '#';

		$percentage = 255 * ($percentage / 100);
		$percentage = max( -255, min( 255, $percentage ) );

		$hex = str_replace( $hex_hash, '', $hex );
		if ( strlen( $hex ) == 3 ) {
			$hex = str_repeat( substr( $hex, 0, 1 ), 2 ) . str_repeat( substr( $hex, 1, 1 ), 2 ) . str_repeat( substr( $hex, 2, 1), 2 );
		}

		$color_parts = str_split( $hex, 2 );
		$return = '';

		foreach ($color_parts as $color) {
			$color = hexdec($color);
			$color = round(max(0, min(255, $color + $percentage)));
			$return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT);
		}

		if( ! $return ){
			return '';
		}

		return self::sanitize_hex_color( $return );
	}

	/**
	 * Calculate whether black or white is best for readability based upon the brightness of specified colour
	 *
	 * @param string $hex
	 */
	public static function readable_colour( $hex ) {

		if( strpos($hex, 'rgb') !== false ){
			return $hex;
		}

		$hex = str_replace( '#', '', $hex );

		if ( strlen( $hex ) == 3 ) {
			$hex = str_repeat( substr( $hex, 0, 1 ), 2 ) . str_repeat( substr( $hex, 1, 1 ), 2 ) . str_repeat( substr( $hex, 2, 1), 2 );
		}

		$color_parts = str_split( $hex, 2 );

		$brightness = ( hexdec( $color_parts[0] ) * 0.299 ) + ( hexdec( $color_parts[1] ) * 0.587 ) + ( hexdec( $color_parts[2] ) * 0.114 );

		if ( $brightness > 128 ) {
			return '#000';
		} else {
			return '#fff';
		}

	}

	/**
	 * HEX to RGBA
	 *
	 * Convert the normal hex to
	 * rgba to easier use.
	 */
	public static function hex_to_rgb($hex, $alpha = false) {
		$hex = str_replace('#', '', $hex);
		$length = strlen($hex);

		$rgb = [
			'r' => hexdec($length == 6 ? substr($hex, 0, 2) : ($length == 3 ? str_repeat(substr($hex, 0, 1), 2) : 0)),
			'g' => hexdec($length == 6 ? substr($hex, 2, 2) : ($length == 3 ? str_repeat(substr($hex, 1, 1), 2) : 0)),
			'b' => hexdec($length == 6 ? substr($hex, 4, 2) : ($length == 3 ? str_repeat(substr($hex, 2, 1), 2) : 0))
		];

		if ($alpha == 'zero') {
			$rgb['a'] = 0;
		} elseif ($alpha) {
			$rgb['a'] = $alpha;
		}

		return implode(', ', $rgb);
	}

	public static function hex2rgba($color, $opacity = false) {

		$default = 'rgb(0,0,0)';

		//Return default if no color provided
		if(empty($color))
				return $default;

			//Sanitize $color if "#" is provided
			if ($color[0] == '#' ) {
				$color = substr( $color, 1 );
			}

			//Check if color has 6 or 3 characters and get values
			if (strlen($color) == 6) {
				$hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
			} elseif ( strlen( $color ) == 3 ) {
				$hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
			} else {
				if( ! $opacity ){
					return $default;
				}
			}

			if( ! isset($hex) ){
				return $default;
			}

			//Convert hexadec to rgb
			$rgb =  array_map('hexdec', $hex);
			//Check if opacity is set(rgba or rgb)
			if($opacity){
				if(abs($opacity) > 1){
					$opacity = 1.0;
				}
				$output = 'rgba('.implode(",",$rgb).','.$opacity.')';
			} else {
				$output = 'rgb('.implode(",",$rgb).')';
			}

			//Return rgb(a) color string
			return $output;
	}

	/**
	 * sanitize hexedecimal numbers used for colors
	 *
	 * @param string $color
	 * @return string
	 */
	public static function sanitize_hex_color( $color ) {

		if ( '' === $color ) {
			return '';
		}

		// make sure the color starts with a hash
		$color = '#' . ltrim( $color, '#' );

		// 3 or 6 hex digits, or the empty string.
		if ( preg_match( '|^#([A-Fa-f0-9]{3}){1,2}$|', $color ) ) {
			return $color;
		}

		return null;

	}

}
