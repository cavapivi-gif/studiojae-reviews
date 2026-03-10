<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if(!function_exists('reycore__language_switcher_markup')):
	/**
	 * Language switcher markup for Header
	 *
	 * @since 1.0.0
	 **/
	function reycore__language_switcher_markup($args = [], $options = []){

		if( empty($args['languages']) ) {
			return;
		}

		$options = wp_parse_args($options, [
			'show_flags' => 'yes',
			'show_active_flag' => '',
			'show_short_text' => '',
			'layout' => 'dropdown'
		]);

		$classes = [
			'rey-langSwitcher',
			'rey-langSwitcher--'. $args['type'],
			'rey-langSwitcher--layout-'. $options['layout'],
		];

		if( $options['layout'] === 'dropdown' ){

			$classes[] = 'rey-headerIcon';
			$classes[] = 'rey-headerDropSwitcher';
			$classes[] = 'rey-header-dropPanel';

			reycore_assets()->add_styles(['rey-header-drop-panel', 'rey-header-icon']);
			reycore_assets()->add_scripts('rey-drop-panel');
		}

		$html = sprintf('<div class="%s">', implode(' ', $classes));

			// Active
			if( $options['layout'] === 'dropdown' ){
				$active_flag = '';

				if( $options['show_active_flag'] === 'yes' && isset($args['current_flag']) && ($current_flag_img = $args['current_flag']) ){
					$active_flag = $current_flag_img;
				}

				$html .= '<button class="btn rey-headerIcon-btn rey-header-dropPanel-btn notranslate" aria-label="'. esc_html__('Switch language', 'rey-core') .'" style="--flag-url:url('.$active_flag.');"><span class="rey-headerIcon-btnText">' . $args['current'] . '</span></button>';

				$html .= '<div class="rey-header-dropPanel-content" data-lazy-hidden>';
			}

			$html .= '<ul>';

			foreach ($args['languages'] as $key => $language) {

				$item_flag = '';

				if( $options['show_flags'] === 'yes' ){
					if( strpos($language['flag'], '<img') !== false ){
						$item_flag = $language['flag'];
					}
					else {
						if( $language['flag'] ){
							$item_flag = sprintf( '<img src="%1$s" alt="%2$s">', $language['flag'], $language['name'] );
						}
					}
				}

				$lang_text = $language['name'];

				if( $options['show_short_text'] === 'yes' ){
					$lang_text = strtoupper($language['code']);
				}
				elseif( $options['show_short_text'] === 'no' ){
					$lang_text = '';
				}

				$html .= sprintf( '<li class="%3$s"><a href="%4$s" %5$s>%1$s<span>%2$s</span></a></li>',
					$item_flag,
					$lang_text,
					$language['active'] ? '--active' : '',
					$language['url'],
					isset($language['attr']) && $language['attr'] ? $language['attr'] : ''
				);
			}
			$html .= '</ul>';

			if( $options['layout'] === 'dropdown' ){
				$html .= '</div>';
			}

		$html .= '</div>';

		reycore_assets()->add_styles('reycore-language-switcher');

		return apply_filters('reycore/language_switcher_markup', $html, $args);
	}
endif;


if(!function_exists('reycore__language_switcher_markup_mobile')):
	/**
	 * Language switcher markup for Mobile panel
	 *
	 * @since 1.0.0
	 **/
	function reycore__language_switcher_markup_mobile($args = []){

		if( empty($args['languages']) ) {
			return;
		}

		$template = '';

		$html = '<ul class="rey-mobileNav--footerItem rey-dropSwitcher-mobile rey-langSwitcher-mobile rey-langSwitcher-mobile--'. $args['type'] .'">';
		$html .= '<li class="rey-dropSwitcher-mobileTitle">'. esc_html_x('LANGUAGE:', 'Language switcher title in Mobile panel.', 'rey-core') .'</li>';
		foreach ($args['languages'] as $key => $language) {

			$item_flag = '';

			if( strpos($language['flag'], '<img') !== false ){
				$item_flag = $language['flag'];
			}
			else {
				if( $language['flag'] ){
					$item_flag = sprintf( '<img src="%1$s" alt="%2$s" data-no-lazy="1" data-skip-lazy="1" class="no-lazy">', $language['flag'], $language['name'] );
				}
			}

			$item_template = sprintf( '<li class="%2$s"><a href="%3$s" %5$s>%1$s<span>%4$s</span></a></li>',
				$item_flag,
				$language['active'] ? '--active' : '',
				$language['url'],
				$language['code'],
				isset($language['attr']) ? $language['attr'] : ''
			);
			$html .= apply_filters('reycore/language_switcher/markup_mobile/item', $item_template, $language, $item_flag);
		}
		$html .= '</ul>';

		reycore_assets()->add_styles('reycore-language-switcher');

		return apply_filters('reycore/language_switcher_markup_mobile', $html, $args);
	}
endif;


if(!function_exists('reycore_wc__get_header_search_args')):
	/**
	 * Get account panel options
	 * @since 1.0.0
	 **/
	function reycore_wc__get_header_search_args( $option = '' ){

		$options = apply_filters('reycore/header/search_params', [
			'search_complementary' => get_theme_mod('search_complementary', 'menu'),
			'search_menu_source'   => get_theme_mod('search_menu', ''),
			'keywords' => get_theme_mod('search_suggestions', '' ),
			'search_style' => get_theme_mod('header_search_style', 'wide'),
			'custom_text_reverse' => false, // deprecated
			'text_position' => 'before',
			'search__before_content' => '',
			'classes' => '',
			'trending_keywords_tag' => 'div',
			'menu_title_tag' => 'div',
		]);

		if( !empty($option) && isset($options[$option]) ){
			return $options[$option];
		}

		return $options;
	}
endif;

if(!function_exists('reycore__header_cart_params')):
	/**
	 * Default settings
	 *
	 * @since 1.6.10
	 **/
	function reycore__header_cart_params( $option = '' )
	{

		$options = apply_filters('reycore/header/cart_params', [
			'hide_empty' => get_theme_mod('header_cart_hide_empty', 'no'),
			'classes'    => '',
			'title_tag'  => 'div'
		]);

		if( !empty($option) && isset($options[$option]) ){
			return $options[$option];
		}

		return $options;
	}
endif;


if(!function_exists('reycore__remove_button_search')):
	/**
	 * Remove default search button
	 *
	 * @since 1.0.0
	 */
	function reycore__remove_button_search() {
		if(
			get_theme_mod('header_enable_search', false) &&
			in_array(reycore_wc__get_header_search_args('search_style'), ['wide', 'side']) ){
			remove_action('rey/header/row', 'rey__header__search', 30);
		}
	}
endif;
add_action('wp', 'reycore__remove_button_search');


if(!function_exists('reycore__header__search')):
	/**
	 * Add search button markup
	 *
	 * @since 1.0.0
	 **/
	function reycore__header__search(){

		// return if search is disabled
		if( ! get_theme_mod('header_enable_search', false) ) {
			return;
		}

		reycore_assets()->add_styles(['reycore-header-search-top', 'reycore-header-search', 'rey-header-icon']);
		reycore_assets()->add_scripts(['reycore-header-search']);

		if( ($search_style = reycore_wc__get_header_search_args('search_style')) && in_array($search_style, ['wide', 'side']) ) {

			reycore__get_template_part('template-parts/header/search-toggle');

			add_filter('reycore/header/search_panel', '__return_true');

			if( 'side' === $search_style ){
				reycore_assets()->add_styles(['rey-overlay', 'reycore-side-panel']);
				reycore_assets()->add_scripts('reycore-sidepanel');
			}
		}
	}
endif;
add_action('rey/header/row', 'reycore__header__search', 30);


if(!function_exists('reycore__header__add_search_panel')):
	/**
	 * Add search panel markup
	 *
	 * @since 1.0.0
	 **/
	function reycore__header__add_search_panel()
	{
		if( ! apply_filters('reycore/header/search_panel', false) ){
			return;
		}
		reycore__get_template_part('template-parts/header/search-panel');
	}
	add_action('wp_footer', 'reycore__header__add_search_panel', 11); // after offcanvas
endif;

if(!function_exists('reycore__header_search_enable_if_gs')):
	/**
	 * Enable Search if Header is set on Global Section
	 *
	 * @since 2.1.4
	 **/
	function reycore__header_search_enable_if_gs()
	{
		if( $header = reycore__get_option('header_layout_type', 'default') ){
			// only if custom GS header
			if( ! in_array($header, ['default', 'none'], true) ){
				add_filter('theme_mod_header_enable_search', '__return_true', 10);
			}
		}
	}
	add_action('init', 'reycore__header_search_enable_if_gs', 5);
endif;


if(!function_exists('reycore__header__search_complementary_menu')):
	/**
	 * Load Search panel complementary navigation
	 *
	 * @since 1.0.0
	 **/
	function reycore__header__search_complementary_menu($args)
	{
		if( isset($args['search_complementary']) && $args['search_complementary'] === 'menu' ){
			reycore__get_template_part('template-parts/header/search-complementary-menu');
		}
	}
endif;
add_action('reycore/search_panel/after_search_form', 'reycore__header__search_complementary_menu', 20);


if(!function_exists('reycore__header__search_complementary_keywords')):
	/**
	 * Load Search panel complementary keywords suggestion
	 *
	 * @since 1.0.0
	 **/
	function reycore__header__search_complementary_keywords($args)
	{
		if( isset($args['search_complementary']) && $args['search_complementary'] === 'keywords' ){
			reycore__get_template_part('template-parts/header/search-complementary-keywords');
		}
	}
endif;
add_action('reycore/search_panel/after_search_form', 'reycore__header__search_complementary_keywords', 20);


if(!function_exists('reycore__add_sticky_global_sections')):
	/**
	 * Append Top Sticky Content Hook
	 *
	 * @since 1.0.0
	 **/
	function reycore__add_sticky_global_sections()
	{

		if(is_admin()){
			return;
		}
		if( wp_doing_ajax() || ! get_the_ID() ){
			return;
		}
		if( ! class_exists('\ReyCore\Elementor\GlobalSections') || ! class_exists('\Elementor\Plugin') ){
			return;
		}
		if( reycore__elementor_edit_mode() ){
			return;
		}
		if( \ReyCore\Elementor\GlobalSections::POST_TYPE === get_post_type() ){
			return;
		}

		add_filter('reycore/script_params', function($params) {

			if(
				(reycore__get_option( 'top_sticky_gs', '' ) && get_theme_mod('top_sticky_gs_dir_only', false)) ||
				(reycore__get_option( 'bottom_sticky_gs', '' ) && get_theme_mod('bottom_sticky_gs_dir_only', false))
				){
				$params['js_params']['dir_aware'] = true;
			}

			return $params;
		}, 20);

		$positions = [
			'top' => 'top_sticky_gs',
			'bottom' => 'bottom_sticky_gs',
		];

		foreach ($positions as $position => $option) {

			if( ($gs = reycore__get_option( $option, '' )) && $gs !== '' && $gs !== 'none' ) {

				$same_gs_as_header = 'top' === $position && $gs == reycore__get_option( 'header_layout_type', 'default' );

				// load their css
				add_filter('reycore/global_sections/css', function($css) use ($gs, $same_gs_as_header) {
					if( ! $same_gs_as_header ){ // don't defer
						reycore_assets()->defer_page_styles('elementor-post-' . $gs, true);
					}
					array_push($css, $gs);
					return $css;
				});

				// add into position
				add_action( "rey/after_site_wrapper", function() use ($gs, $option, $position) {

					$GLOBALS['rey__is_sticky'] = true;

					$gs_content = \ReyCore\Elementor\GlobalSections::do_section( $gs, false, false );

					if( ! $gs_content ){
						unset($GLOBALS['rey__is_sticky']);
						return;
					}

					$attributes = 'data-lazy-hidden';
					$attributes .= ' data-offset="'. esc_attr( reycore__get_option( $option . '_offset' ) ) .'"';
					$attributes .= ' data-align="'. esc_attr( $position ) .'"';

					if( reycore__get_option( $option . '_close' ) ){
						$attributes .= sprintf(' data-close="%s"', esc_attr(apply_filters('reycore/sticky_global_section/expiration', 'week')));
						\ReyCore\Plugin::instance()->js_icons->include_icons( 'close' );
					}

					$classes = '';

					$sticky_gs_hide_devices__default = get_theme_mod($position . '_sticky_gs_hide_on_mobile', true) === false ? [] : ['mobile'];
					$sticky_gs_hide_devices = reycore__get_option($position . '_sticky_gs_hide_devices', $sticky_gs_hide_devices__default);

					$devices_map = [
						'desktop' => 'lg',
						'tablet' => 'md',
						'mobile' => 'sm',
					];

					foreach ($sticky_gs_hide_devices as $key => $value) {
						$classes .= ' --dnone-' . $devices_map[$value];
					}

					if( get_theme_mod($position . '_sticky_gs_dir_only', false) ){
						$classes .= ' --dir-aware';
					}

					if( $position === 'bottom' && get_theme_mod('bottom_sticky_gs_always_visible', false) ){
						$classes .= ' --always-visible';
					}

					echo '<div class="rey-stickyContent rey-noSp '. $classes .'" '. $attributes .'>';
						echo $gs_content;
					echo '</div>';

					unset($GLOBALS['rey__is_sticky']);

					reycore_assets()->add_styles('reycore-elementor-sticky-gs');
					reycore_assets()->add_scripts('reycore-sticky-global-sections');

				}, 0);

				// Switch to absolute if Main Header is Fixed
				if( 'top' === $position && 'fixed' === reycore__get_option( 'header_position', 'rel' ) ){
					$switch_to_absolute = function(){ return 'absolute'; };
					add_filter( 'theme_mod_header_position', $switch_to_absolute, 40);
					add_filter( 'rey_acf_option_header_position', $switch_to_absolute, 40);
				}
			}
		}
	}
endif;
add_action('wp', 'reycore__add_sticky_global_sections');

if(!function_exists('reycore__search_wide_logo')):
	/**
	 * Add suport for custom logo in Wide Search panel (when opened)
	 *
	 * @since 1.1.0
	 **/
	function reycore__search_wide_logo($html){

		if( $search_wide_logo = get_theme_mod('search_wide_logo', '') ){
			$to_add = sprintf( 'data-search-logo="%s" ', wp_get_attachment_url( $search_wide_logo ) );
			$html = str_replace('class="custom-logo', $to_add .'class="custom-logo',  $html);
		}

		$html = str_replace('class="',  'data-no-lazy="1" data-skip-lazy="1" class="no-lazy ', $html);

		return $html;
	}
endif;
add_filter('rey/header/logo_img_html', 'reycore__search_wide_logo');


if(!function_exists('reycore__header_fixed_overlapping_classes')):
/**
 * Get Overlapping Classes
 *
 * @since 1.9.6
 **/
function reycore__header_fixed_overlapping_classes( $skip_acf = false)
{
	return apply_filters('reycore/header_helper/overlap_classes', [
		'desktop' => filter_var( reycore__get_option('header_fixed_overlap', true, $skip_acf) , FILTER_VALIDATE_BOOLEAN) === true ? '--dnone-lg' : '',
		'tablet' => filter_var( reycore__get_option('header_fixed_overlap_tablet', true, $skip_acf) , FILTER_VALIDATE_BOOLEAN) === true ? '--dnone-md' : '',
		'mobile' => filter_var( reycore__get_option('header_fixed_overlap_mobile', true, $skip_acf) , FILTER_VALIDATE_BOOLEAN) === true ? '--dnone-sm' : '',
	]);
}
endif;


if(!function_exists('reycore__header_fixed_nonoverlapping_helper')):
	/**
	 * Add Fixed header non-overlapping helper
	 *
	 * @since 1.2.0
	 **/
	function reycore__header_fixed_nonoverlapping_helper()
	{
		if(
			reycore__get_option('header_layout_type', 'default') !== 'none'
			&& in_array(reycore__get_option('header_position', 'rel'), ['fixed', 'absolute'], true)
		) {

			reycore_assets()->add_styles('reycore-header-abs-fixed');

			// Fix when Header position Customizer option is set on Relative, but page has Fixed/Absolute
			// This will set Overlap Customizer option as true, always but only if it's overwridden in the page settings.
			if( get_theme_mod('header_position', 'rel') === 'rel' ){
				add_filter('theme_mod_header_fixed_overlap', '__return_true');
			}

			$skip_acf = reycore__acf_get_field('header_position') === '';

			printf( '<div id="rey-siteHeader-helper" class="rey-siteHeader-helper %s"></div>', esc_attr( implode(' ', array_filter( reycore__header_fixed_overlapping_classes( $skip_acf ) ) ) ) );
		}
	}
endif;
add_action('rey/after_header', 'reycore__header_fixed_nonoverlapping_helper', 0); // 0 priority

if(!function_exists('reycore__header_fixed_body_class')):
	/**
	 * Append fixed class to body
	 *
	 * @since 2.5.0
	 **/
	function reycore__header_fixed_body_class($classes)
	{
		if( 'fixed' === reycore__get_option('header_position', 'rel') ){
			$classes['header_position'] = '--fixed-header';
		}

		return $classes;
	}
	add_filter('body_class', 'reycore__header_fixed_body_class');
endif;

if(!function_exists('reycore__header_navigation_args')):
/**
 * Add nav walker
 *
 * @since 3.0.0
 **/
function reycore__header_navigation_args($args) {

	if( 'main-menu' !== $args['menu'] ){
		return $args;
	}

	$args['walker'] = new \ReyCore\Libs\Nav_Walker;

	// set if menu should be cached
	if( ! isset($args['cache_menu']) ){
		$args['cache_menu'] = get_theme_mod('header_nav_cache', false);
	}

	return $args;
}
endif;
add_filter('wp_nav_menu_args', 'reycore__header_navigation_args', 9);

if(!function_exists('reycore__header_navigation_classes')):
/**
 * Filter menu navigation classes
 *
 * @since 1.5.0
 **/
function reycore__header_navigation_classes($classes, $args, $device) {

	if( $device === 'desktop' ){
		$classes['shadow'] = '--shadow-' . get_theme_mod('header_nav_submenus_shadow', '1');
	}

	return $classes;
}
endif;
add_filter('rey/header/nav_classes', 'reycore__header_navigation_classes', 10, 3);

if(!function_exists('reycore__tags_logo_block')):
	/**
	 * Shows logo only
	 *
	 * @since 1.5.0
	 */
	function reycore__tags_logo_block(){
		echo '<div class="rey-logoBlock-header">';
		get_template_part('template-parts/header/logo');
		echo '</div>';
	}
endif;


if(!function_exists('reycore__reset_logos_on_save')):
	/**
	 * After a new logo is set globally in Customizer > Header > Logo, this will reset
	 * the logos set in pages.
	 *
	 * @since 2.6.0
	 */
	function reycore__reset_logos_on_save( $setting )
	{

		if( ! current_user_can('administrator') ){
			return;
		}
		if( ! method_exists($setting, 'value') ){
			return;
		}

		$setting_id = $setting->id;

		global $wpdb;

		$logo_esc = esc_sql($setting_id);

		$post_ids = $wpdb->get_col( "SELECT `post_id` FROM `{$wpdb->postmeta}` WHERE `meta_key` = '{$logo_esc}' AND `meta_value` != '';" );

		foreach ($post_ids as $post_id) {
			delete_field($setting_id, $post_id);
		}

		if( '' !== get_theme_mod('search_wide_logo', '') ){
			remove_theme_mod('search_wide_logo', '');
		}

	}
endif;

add_action( 'customize_save_custom_logo', 'reycore__reset_logos_on_save');
add_action( 'customize_save_logo_mobile', 'reycore__reset_logos_on_save');


if(!function_exists('reycore__sticky_social_icons__output')):
	/**
	 * Social icons
	 *
	 * @since 1.9.0
	 **/
	function reycore__sticky_social_icons__output()
	{
		if( !($icons = reycore__sticky_social_icons()) ){
			return;
		}

		$icons_html = '';

		foreach ($icons as $key => $icon) {

			$styles = [];

			$tag = 'div';
			$attributes = $classes = '';

			if ( isset($icon['color']) && $color = $icon['color'] ){
				$styles[] = 'color:' . $color;
			}

			if ( isset($icon['bg_color']) && $bg_color = $icon['bg_color'] ){
				$styles[] = 'background-color:' . $bg_color;
				$classes .= ' --bgcolor';
			}

			if ( isset($icon['url']) && $url = $icon['url'] ){
				$tag = 'a';
				$attributes = sprintf( 'href="%s" target="_blank"', esc_url($url) );
			}

			$icons_html .= sprintf('<%1$s class="rey-stickySocial-item %4$s" rel="noreferrer" style="%2$s" %3$s>', $tag, implode(';', $styles), $attributes, $classes);

				if ( isset($icon['text']) && $text = $icon['text'] ){
					$icons_html .= sprintf( '<span class="__text">%s</span>', $text );
				}

				if ( isset($icon['image']) && $image = $icon['image'] ){

					if( ($svg = \ReyCore\Plugin::instance()->svg) && $svg_code = $svg->get_inline_svg( [ 'id' => $image, 'class' => '__icon' ] ) ){
						$icons_html .= $svg_code;
					}
				}

			$icons_html .= sprintf('</%s>', $tag);
		}

		if( empty($icons_html) ){
			return;
		}

		$classes = [
			'rey-stickySocial',
			'--layout-' . get_theme_mod('social__layout', 'minimal'),
			'--position-' . get_theme_mod('social__position', 'right'),
			get_theme_mod('social__verticalize', true) ? '--vert' : '--no-vert',
			get_theme_mod('social__diff', true) ? '--diff' : '',
			get_theme_mod('social__btn_line', true) ? '--linebtn' : '',
			'--visb-' . get_theme_mod('social__visibility', 'always'),
		];

		printf('<div class="%s" data-lazy-hidden>', implode(' ', array_map('esc_attr', $classes)));

			echo '<div class="rey-stickySocial-inner">';

				if( $title = get_theme_mod('social__text', '') ){
					printf('<h5 class="rey-stickySocial-title">%s</h5>', $title);
				}

				printf('<div class="rey-stickySocial-items">%s</div>', $icons_html);

			echo '</div>';
		echo '</div>';

		reycore_assets()->add_styles('reycore-sticky-social');

	}
	add_action('rey/after_site_wrapper', 'reycore__sticky_social_icons__output');
endif;

if(!function_exists('reycore__sticky_social_icons')):
	function reycore__sticky_social_icons() {

		if( ! get_theme_mod('social__enable', false) ){
			return;
		}

		$icons = get_theme_mod('social__icons', []);

		if( empty($icons) ){
			return;
		}

		return $icons;
	}
endif;

add_filter('reycore/script_params', function($params) {

	if( ($icons = reycore__sticky_social_icons()) && get_theme_mod('social__visibility', 'always') !== 'always'){
		$params['js_params']['dir_aware'] = true;
	}

	return $params;
}, 20);




if(!function_exists('reycore__header__cta')):
	/**
	 * Add CTA markup
	 *
	 * @since 2.5.0
	 **/
	function reycore__header__cta(){

		if( ! get_theme_mod('header_cta', false) ) {
			return;
		}

		$text = get_theme_mod('header_cta__text', '');
		$url = get_theme_mod('header_cta__url', '');

		if( ! ($url && $text) ){
			return;
		}

		$classes = [
			'style'  => get_theme_mod('header_cta__style', 'btn-primary'),
			'size'   => ($size = get_theme_mod('header_cta__size', '')) ? '--s-' . $size : '',
			'mobile' => '--dnone-sm',
		];

		if( get_theme_mod('header_cta__mobile', false) ){
			unset($classes['mobile']);
		}

		printf('<a href="%1$s" class="btn rey-headerCta %3$s" %4$s>%2$s</a>',
			esc_url($url),
			$text,
			esc_attr(implode(' ', $classes)),
			apply_filters('reycore/header/default/cta_attributes', '')
		);

		reycore_assets()->add_styles(['rey-buttons', 'reycore-header-cta']);

	}
	add_action('rey/header/row', 'reycore__header__cta', 60);
endif;
