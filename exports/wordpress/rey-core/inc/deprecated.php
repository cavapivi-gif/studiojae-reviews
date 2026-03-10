<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if( ! class_exists('ReyCore_WooCommerce_Tabs') ){
	class ReyCore_WooCommerce_Tabs {
		public static function determine_acc_tab_to_start_opened($d){}
	}
}


if( ! class_exists('ReyCore_ColorUtilities') ):
	/**
	 * Wrapper for Color Utilities Library
	 *
	 * @since 2.3.0
	 * @todo remove in 2.5.0
	 * @used in FS Menu
	 **/
	class ReyCore_ColorUtilities {
		public static function adjust_color_brightness($a, $b){
			return \ReyCore\Libs\Colors::adjust_color_brightness($a, $b);
		}
		public static function readable_colour($a){
			return \ReyCore\Libs\Colors::readable_colour($a);

		}
		public static function hex2rgba($a, $b = false){
			return \ReyCore\Libs\Colors::hex2rgba($a, $b);
		}
	}
endif;

if( ! class_exists('ReyCore_GlobalSections') ):
	/**
	 * Wrapper for Global Sections
	 *
	 * @since 2.3.0
	 * @todo remove in 2.5.0
	 * @used in FS Menu
	 **/
	class ReyCore_GlobalSections {
		public static function do_section($a){
			return \ReyCore\Elementor\GlobalSections::do_section($a);
		}
		public static function get_global_sections($a){
			return \ReyCore\Elementor\GlobalSections::get_global_sections($a, $b = []);
		}
	}
endif;

if( ! class_exists('ReyCoreKirki') ):
	/**
	 * Wrapper for Customizer Options
	 *
	 * @since 2.3.0
	 * @todo remove in 2.5.0
	 * @used in various addons (Preloader Pack, Side Heading, FS Menu, etc.)
	 **/
	class ReyCoreKirki {
		public static function add_panel($a, $b){
			if( class_exists('\Kirki') && ! empty($b) ){
				\Kirki::add_panel( $a, $b );
			}
		}
		public static function add_section($a, $b){
			if( class_exists('\Kirki') && ! empty($b) ){
				if( class_exists('\ReyCore\Customizer\Base') ){
					$legacy_panels = \ReyCore\Customizer\Base::legacy_panels();
					if( isset($legacy_panels[ $b['panel'] ]) ){
						$b['panel'] = $legacy_panels[ $b['panel'] ];
					}
				}
				\Kirki::add_section( $a, $b );
			}
		}
		public static function add_field($a, $b){
			if( class_exists('\Kirki') && ! empty($b) ){
				if( class_exists('\ReyCore\Customizer\Controls') ){
					\ReyCore\Customizer\Controls::add_field( $b );
				}
				else {
					\Kirki::add_field( 'rey_core_kirki', $b );
				}
			}
		}
	}
endif;

if(!function_exists('reycore_customizer__help_link')):
	function reycore_customizer__help_link( $a = [] ){}
endif;

if(!function_exists('reycore_wc__get_discount_save_html')):
	function reycore_wc__get_discount_save_html(){}
endif;

if(!function_exists('reycore_wc__get_discount_percentage_html')):
	function reycore_wc__get_discount_percentage_html($a = ''){}
endif;

if(!function_exists('reycore_wc__get_discount')):
	function reycore_wc__get_discount($a = false, $b = true){}
endif;

if(!function_exists('reycore__social_icons_sprite_path')):
	/**
	 * Retrieve social icon sprite path
	 *
	 * @since 1.3.7
	 * @deprecated 2.4.0
	 **/
	function reycore__social_icons_sprite_path()
	{
		return '';
	}
endif;


if(!function_exists('reycore__get_svg_icon__core')):
	/**
	 * Wrapper for Rey Theme's rey__get_svg_icon()
	 * with the addition of the social icon sprite.
	 *
	 * @deprecated 2.4.0
	 * @since 1.0.0
	 */
	function reycore__get_svg_icon__core( $args = [] ) {
		return false;
	}
endif;

if(!function_exists('reycore__icons_sprite_path')):
	/**
	 * Retrieve icon sprite path
	 *
	 * @deprecated 2.4.0
	 * @since 1.3.7
	 **/
	function reycore__icons_sprite_path()
	{
		return '';
	}
endif;


/**
 * Deprecated
 *
 * @return empty array
 */
function reycore__get_all_menus(){
	return [];
}
