<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if(!function_exists('rey__add_header_markup')):
	/**
	 * Add header markup
	 *
	 * @since 1.0.0
	 **/
	function rey__add_header_markup()
	{
		if( apply_filters('rey/header/maybe_hide', rey__get_option('header_layout_type', 'default') == 'none' || is_404() ) ){
			return;
		}
		rey_assets()->add_styles('rey-header');
		get_template_part( 'template-parts/header/base' );
	}
endif;
add_action('rey/header', 'rey__add_header_markup', 10);


if(!function_exists('rey__header__content')):
	/**
	 * Add header content into header
	 *
	 * @since 1.0.0
	 **/
	function rey__header__content(){
		// Load default header
		if( rey__get_option('header_layout_type', 'default') == 'default'  || ! class_exists('ReyCore') ){
			// load template
			get_template_part('template-parts/header/content');
			// load style
			rey_assets()->add_styles('rey-header-default');
		}
	}
endif;
add_action('rey/header/content', 'rey__header__content', 10);


if(!function_exists('rey__header__overlay')):
	/**
	 * Add header overlay
	 *
	 * @since 1.0.0
	 **/
	function rey__header__overlay(){
		get_template_part('template-parts/header/overlay');
	}
endif;
add_action('rey/header/content', 'rey__header__overlay', 200);


if(!function_exists('rey__header__logo')):
	/**
	 * Add logo markup
	 *
	 * @since 1.0.0
	 **/
	function rey__header__logo(){
		get_template_part('template-parts/header/logo');
	}
endif;
add_action('rey/header/row', 'rey__header__logo', 10);


if(!function_exists('rey__header__navigation')):
	/**
	 * Add navigation markup
	 *
	 * @since 1.0.0
	 **/
	function rey__header__navigation(){
		get_template_part('template-parts/header/navigation');
	}
endif;
add_action('rey/header/row', 'rey__header__navigation', 20);


if(!function_exists('rey__header__search')):
	/**
	 * Add search button markup
	 *
	 * @since 1.0.0
	 **/
	function rey__header__search(){
		// return if search is disabled
		if( get_theme_mod('header_enable_search', false) ) {
			get_template_part('template-parts/header/search-button');
			rey_assets()->add_styles('rey-header-search');
			rey_assets()->add_scripts('rey-searchform');
		}
	}
endif;
add_action('rey/header/row', 'rey__header__search', 30);



/**
 * Add classes to header
 *
 * @since 1.0.0
 */
add_filter('rey/header/header_classes', function($classes){

	$header_layout = rey__get_option('header_layout_type', 'default');

	if( $header_layout != 'default' && !class_exists('ReyCore') ){
		$header_layout = 'default';
	}

	// Header Style Class
	$classes['layout'] = 'rey-siteHeader--' . $header_layout;

	if( $header_layout != 'default' && $header_layout != 'none' ) {
		$classes['layout'] = 'rey-siteHeader--custom rey-siteHeader--' . $header_layout;
	}

	if( $header_pos = rey__get_option('header_position', 'rel') ) {
		// Header Position
		$classes['position'] = 'header-pos--' . $header_pos;

		// Fixed header - Disable mobile option
		if( $header_pos === 'fixed' && get_theme_mod('header_fixed_disable_mobile', true) === true ){
			$classes['fixed-mobile'] = '--not-mobile';
		}
	}

	if( get_theme_mod('header_preloader_animation', true) && get_theme_mod('site_preloader', false) ){
		$classes['preloader-anim'] = '--preloader-anim';
	}

	return $classes;
});


if(!function_exists('rey__custom_logo')):
	/**
	 * Prints Logo HTML
	 * Based on WP get_custom_logo()
	 *
	 * @since 1.0.0
	 **/
	function rey__custom_logo( $args = [] )
	{
		$html          = '';

		rey_assets()->add_styles('rey-logo');

		// We have a logo. Logo is go.
		if ( isset($args['logo']) && $custom_logo_id = $args['logo'] ) {

			$custom_logo_attr = [
				'class'    => 'custom-logo',
			];

			if( 'default' === get_theme_mod('header_layout_type', 'default') && $logo_sizes = get_theme_mod('logo_sizes', get_theme_mod('my_setting', []) ) ){
				if(isset($logo_sizes['max-width']) && ! empty($logo_sizes['max-width']) ){
					$custom_logo_attr['class'] .= ' --has-mw';
				}
			}

			/*
			* If the logo alt attribute is empty, get the site title and explicitly
			* pass it to the attributes used by wp_get_attachment_image().
			*/
			$image_alt = get_post_meta( $custom_logo_id, '_wp_attachment_image_alt', true );
			if ( empty( $image_alt ) ) {
				$custom_logo_attr['alt'] = get_bloginfo( 'name', 'display' );
			}

			/**
			 * Get The mobile logo
			 */
			$mobile_logo   = '';
			if( isset($args['logo_mobile']) && $custom_logo_mobile_id = $args['logo_mobile'] ){
				$mobile_logo = wp_get_attachment_image($custom_logo_mobile_id, 'full', false, ['class' => 'rey-mobileLogo']);
			}

			$link['start'] = '';
			$link['end'] = '';

			if( apply_filters('rey/header/logo_wrap_link', true) ){
				$link['start'] = sprintf( '<a href="%1$s" class="custom-logo-link" rel="home" itemprop="url">', esc_url( apply_filters('rey/header/logo_link', home_url( '/' ) ) ) );
				$link['end'] = '</a>';
			}

			$custom_logo_attr['loading'] = 'eager';

			$html .= $link['start'];
			$html .= wp_get_attachment_image( $custom_logo_id, 'full', false, apply_filters('rey/logo/attributes', $custom_logo_attr) );
			$html .= $mobile_logo;
			$html .= $link['end'];

		} elseif ( is_customize_preview() ) {
			// If no logo is set but we're in the Customizer, leave a placeholder (needed for the live preview).
			$html = sprintf(
				'<a href="%1$s" class="custom-logo-link" style="display:none;"><img class="custom-logo"/></a>',
				esc_url( home_url( '/' ) )
			);
		}

		return apply_filters('rey/header/logo_img_html', $html);
	}
endif;


if(!function_exists('rey__main_menu_css_class')):
	/**
	 * Menu CSS Classes
	 *
	 * @since: 1.0.0
	 */
	function rey__main_menu_css_class( $classes, $item, $args, $depth)
	{

		if( isset($args->menu_class) && strpos($args->menu_class, 'id--mainMenu--desktop') !== false ) {

			$classes['depth'] = 'depth--' . $depth;

			if( $depth === 0 ) {
				$classes['type'] = '--is-regular';
			}
		}

		// force active ancestors' an active css class
		if( in_array('current-menu-parent', $classes, true) ){
			$classes[] = 'current-menu-item';
		}

		$classes = rey__hash_links_remove_active_class($classes, $item, $args, $depth);

		return $classes;
	}
endif;
add_filter('nav_menu_css_class', 'rey__main_menu_css_class', 10, 4);


if(!function_exists('rey__get_nav_menu_by_location')):
	/**
	 * Get menu location by name
	 *
	 * @since 1.0.2
	 */
	function rey__get_nav_menu_by_location( $menu_name = '' )
	{
		$menu_locations = get_nav_menu_locations();

		if ( isset($menu_locations[$menu_name]) && $menu = $menu_locations[$menu_name] ) {
			return $menu;
		}

		return $menu_name;
	}
endif;

if(!function_exists('rey__header_nav_params')):
	/**
	 * Default settings
	 *
	 * @since 1.6.10
	 **/
	function rey__header_nav_params()
	{
		return apply_filters('rey/header/nav_params', [
			'override' => false,
			'menu' => 'main-menu',
			'mobile_menu' => get_theme_mod('mobile_menu', 'main-menu'),
			'load_hamburger' => [],
			'nav_id' => 'mm1',
			'nav_style' => '--style-default',
			'nav_ul_style' => '',
			'nav_indicator' => 'circle',
		]);
	}
endif;

if(!function_exists('rey__header_logo_params')):
	/**
	 * Default settings
	 *
	 * @since 1.6.10
	 **/
	function rey__header_logo_params()
	{
		return apply_filters('rey/header/logo_params', [
			'blog_name' => get_bloginfo( 'name', 'display' ),
			'blog_description' => get_bloginfo( 'description', 'display' ),
			'logo' => rey__get_option('custom_logo', ''),
			'logo_mobile' => rey__get_option('logo_mobile', ''),
			'mobile_panel_logo' => false
		]);
	}
endif;

if(!function_exists('rey__hash_links_remove_active_class')):
	/**
	 * Remove active menu item classes when having #hash links inside the menu.
	 * They'll be added automatically by JS
	 *
	 * @since 2.4.0
	 **/
	function rey__hash_links_remove_active_class($classes, $item, $args, $depth)
	{

		if( $depth !== 0 ){
			return $classes;
		}

		$css_class = 'current-menu-item';

		if( ! in_array($css_class, $classes, true) ){
			return $classes;
		}

		if( ! ($item->url && '#' !== $item->url) ){
			return $classes;
		}

		$hash_position = strpos($item->url, '#');

		if( ! ( $hash_position !== false && $hash_position !== 0) ){
			return $classes;
		}

		if(($key = array_search($css_class, $classes)) !== false) {
			unset($classes[$key]);
		}

		return $classes;
	}
endif;

/**
 * Add indicator tags
 *
 * @since 2.5.0
 **/
add_filter('nav_menu_item_args', function($args, $menu_item, $depth) {

	if( ! ( isset($args->rey_indicators) && ($indicator_type = $args->rey_indicators) && 'none' !== $indicator_type) ) {
		return $args;
	}

	// run first and store default link_after
	if( ! isset($args->default_link_after) ){
		$args->default_link_after = $args->link_after;
	}

	if( in_array('menu-item-has-children', $menu_item->classes, true) || isset($args->mega_classes) ) {
		$args->link_after = sprintf('%s<i class="--submenu-indicator --submenu-indicator-%s"></i>', $args->default_link_after, $indicator_type);
	}
	else {
		$args->link_after = $args->default_link_after;
	}

	return $args;

}, 100, 3);
