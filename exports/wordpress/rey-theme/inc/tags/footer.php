<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Footer Tags Functions
 */

if(!function_exists('rey__add_footer_markup')):
	/**
	 * Add footer markup
	 *
	 * @since 1.0.0
	 **/
	function rey__add_footer_markup()
	{
		if( apply_filters('rey/footer/maybe_hide', rey__get_option('footer_layout_type', 'default') == 'none' || is_404() ) ){
			return;
		}
		get_template_part( 'template-parts/footer/base' );

	}
endif;
add_action('rey/footer', 'rey__add_footer_markup', 10);


if(!function_exists('rey_action__footer__content')):
	/**
	 * Add Footer content into footer content
	 *
	 * @since 1.0.0
	 **/
	function rey_action__footer__content()
	{
		if( rey__get_option('footer_layout_type', 'default') == 'default' || ! class_exists('ReyCore') ){
			get_template_part('template-parts/footer/content');
			rey_assets()->add_styles('rey-footer');
		}
	}
endif;
add_action('rey/footer/content', 'rey_action__footer__content', 10);


if(!function_exists('rey_action__footer_css_classes')):
	/**
	 * Add classes to footer
	 *
	 * @since 1.0.0
	 */
	function rey_action__footer_css_classes($classes){

		$footer_layout = rey__get_option('footer_layout_type', 'default');

		if( $footer_layout != 'default' && !class_exists('ReyCore') ){
			$footer_layout = 'default';
		}

		// Footer Style Class
		$classes['layout'] = 'rey-siteFooter--' . $footer_layout;

		if( $footer_layout != 'default' && $footer_layout != 'none' ) {
			$classes['layout'] = 'rey-siteFooter--custom';
		}

		return $classes;
	}
endif;
add_filter('rey/footer/footer_classes', 'rey_action__footer_css_classes');


if(!function_exists('rey_action__footer__copyright')):
	/**
	 * Add copyright markup
	 *
	 * @since 1.0.0
	 **/
	function rey_action__footer__copyright(){
		get_template_part('template-parts/footer/copyright');
	}
endif;
add_action('rey/footer/row', 'rey_action__footer__copyright', 10);


if(!function_exists('rey_action__footer__navigation')):
	/**
	 * Add footer's navigation markup
	 *
	 * @since 1.0.0
	 **/
	function rey_action__footer__navigation(){
		get_template_part('template-parts/footer/navigation');
	}
endif;
add_action('rey/footer/row', 'rey_action__footer__navigation', 20);


if(!function_exists('rey_action__footer_copyright_text')):
	/**
	 * Add custom copyright text
	 *
	 * @since 1.0.0
	 */
	function rey_action__footer_copyright_text($text){
		if( get_theme_mod('footer_copyright_automatic', true) === false ) {
			if( function_exists('reycore__parse_text_editor') ){
				return reycore__parse_text_editor( get_theme_mod('footer_copyright', '') );
			}
			return get_theme_mod('footer_copyright', '');
		}
		return $text;
	}
endif;
add_filter('rey/footer/copyright_text', 'rey_action__footer_copyright_text');
