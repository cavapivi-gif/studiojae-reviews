<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if(!function_exists('rey__custom_styles')):
	/**
	 * Custom Styles
	 *
	 * @since 1.0.0
	 */
	function rey__custom_styles() {

		if( defined('WP_ROCKET_VERSION') || apply_filters('rey/css_zero_px_fix', false) ){
			// fix for WPRocket trimmig 0px
			// ref https://github.com/wp-media/wp-rocket/issues/2083
			$styles_output['zeropx'] = ':root{--zero-px: 0.001px;}';
		}

		// Mobile Nav Breakpoint
		$styles_output['menu_breakpoint'] = '@media (max-width: 1024px) {
			:root {
				--nav-breakpoint-desktop: none;
				--nav-breakpoint-mobile: flex;
			}
		}';

		return apply_filters('rey/css_styles', $styles_output );
	}
endif;


if(!function_exists('rey__enqueue_custom_styles')):
	/**
	 * Load Rey's custom CSS in HEAD
	 * if Kirki is not loaded
	 */
	function rey__enqueue_custom_styles(){

		if( ! apply_filters('rey/allow_enqueue_custom_styles', true) ){
			return;
		}

		if( $css = rey__get_minified_css() ){
			wp_register_style( 'rey-custom-styles', false );
			wp_enqueue_style( 'rey-custom-styles' );
			wp_add_inline_style( 'rey-custom-styles', $css);
		}
	}
endif;
add_action('wp_enqueue_scripts', 'rey__enqueue_custom_styles', 9999);


if(!function_exists('rey__get_minified_css')):
	function rey__get_minified_css(){

		$styles_output = rey__custom_styles();

		if( !empty($styles_output) && is_array($styles_output) ){
			$styles_output = implode(' ', $styles_output);
			$styles_output = str_replace(array("\r\n", "\r", "\n"), '', $styles_output);
			return $styles_output;
		}
	}
endif;


if(!function_exists('rey__filter_html_tag')):
	/**
	 * Add an attribute to html tag
	 *
	 * @since 1.0.0
	 **/
	function rey__filter_html_tag($output)
	{
		$attributes = [];

		$attributes[] = ' data-df';
		$attributes[] = ' data-container="'. absint(get_theme_mod('container_width_px', '1440')) .'"';
		$attributes[] = sprintf('data-xl="%d"', wp_is_mobile() ? 0 : 2);
		$attributes[] = 'data-admin-bar="'. absint( is_admin_bar_showing() ) .'"';

		if( ! is_customize_preview() && get_theme_mod('style_neutrals_theme', 'light') === 'dark' ){
			$attributes[] = 'data-theme="dark"';
		}

		return $output . implode(' ', $attributes);
	}
endif;
add_filter('language_attributes', 'rey__filter_html_tag' );
