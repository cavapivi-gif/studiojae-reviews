<?php
namespace ReyCore\Elementor;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use \ReyCore\Elementor\Helper;

class Editor
{

	public function __construct(){

		add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'editor_scripts'] );
		add_action( 'elementor/editor/after_save', [ $this, 'elementor_to_acf'], 10);
		add_filter( 'elementor/editor/localize_settings', [ $this, 'localized_settings' ], 10 );
		add_action( 'customize_save_after', [ $this, 'update_elementor_schemes' ]);
		add_filter( 'acf/validate_post_id', [ $this, 'acf_validate_post_id'], 10, 2);
		add_filter( 'theme_elementor_library_templates', [$this, 'library__page_templates_support'] );
		add_action( 'elementor/controls/controls_registered', [ $this, 'register_controls' ] );
		add_action( 'elementor/elements/categories_registered', [ $this, 'add_elementor_widget_categories'] );
		add_action( 'admin_init', [$this, 'filter_html_tag'] );
		add_filter( 'admin_body_class', [$this, 'shop_page_disable_button']);
		add_action( 'reycore/customizer/section=general-layout/marker=layout_options', [$this, 'add_grid_type_control'], 100);
		// add_action( 'reycore/customizer/section=general-performance/marker=performance_toggles', [$this, 'add_defer_js_control']);
		add_action( 'elementor/ajax/register_actions', [$this, 'ajax_set_pages']);

		if ( ! empty( $_REQUEST['action'] ) && 'elementor' === $_REQUEST['action'] && is_admin() ) {
			add_action( 'init', [ $this, 'register_wc_hooks' ], 5 /* Priority = 5, in order to allow plugins remove/add their wc hooks on init. */);
		}

		// fixes an annoying PHP Warning
		if( isset($_REQUEST['action']) && 'elementor_ajax' === $_REQUEST['action'] ){
			remove_filter( 'wp_nav_menu_objects', 'wc_nav_menu_item_classes', 2 );
		}
	}

	public function register_controls( $controls_manager ) {

		if( version_compare('3.5.0', ELEMENTOR_VERSION, '>') ){
			$controls_manager->register_control( 'rey-query', new \ReyCore\Elementor\Controls\ReyQuery() );
		}
		else {
			$controls_manager->register( new \ReyCore\Elementor\Controls\ReyQuery() );
			$controls_manager->register( new \ReyCore\Elementor\Controls\ReyAjaxList() );
		}

	}

	/**
	 * Add Rey Widget Categories
	 *
	 * @since 1.0.0
	 */
	public function add_elementor_widget_categories( $elements_manager ) {

		$elements_manager->add_category(
			'rey-header',
			[
				'title' => __( 'Header', 'rey-core' ) . \ReyCore\Elementor\Helper::rey_badge(),
			]
		);

		$elements_manager->add_category(
			'rey-theme',
			[
				'title' => __( 'Theme', 'rey-core' ) . \ReyCore\Elementor\Helper::rey_badge(),
			]
		);
		$elements_manager->add_category(
			'rey-theme-covers',
			[
				'title' => __( 'Covers (Sliders)', 'rey-core' ) . \ReyCore\Elementor\Helper::rey_badge(),
			]
		);
		if( class_exists('\WooCommerce') ){
			$elements_manager->add_category(
				'rey-woocommerce',
				[
					'title' => __( 'WooCommerce', 'rey-core' ). \ReyCore\Elementor\Helper::rey_badge(),
				]
			);
		}

	}

	/**
	 * Load Editor JS
	 *
	 * @since 1.0.0
	 */
	public function editor_scripts() {
		wp_enqueue_style( 'rey-core-elementor-editor-css', REY_CORE_URI . 'assets/css/elementor-editor.css', [], REY_CORE_VERSION );
		wp_enqueue_script( 'rey-core-elementor-editor', REY_CORE_URI . 'assets/js/elementor-editor/elementor-editor.js', [], REY_CORE_VERSION, true );
		wp_localize_script('rey-core-elementor-editor', 'reyElementorEditorParams', [
			'branding' => reycore__get_props('branding'),
			'reload_text' => esc_html__('Please save & reload page to apply this setting.', 'rey-core'),
			'rey_typography' => $this->get_typography_names(),
			'icon' => reycore__get_props('button_icon'),
			'button_text' => reycore__get_props('button_text'),
			'title' => sprintf(esc_html__('%s - Quick Menu', 'rey-core'), reycore__get_props('theme_title')),
			'optimized_dom' => Helper::is_optimized_dom(),
			'elements_icons_sprite_path' => REY_CORE_URI  . 'assets/images/elementor-el-icons.svg',
			'rey_container_spacing' => get_theme_mod('container_spacing', 15),
		]);
	}


	public function get_typography_names(){

		$pff = $sff = '';

		if( ($primary_typo = get_theme_mod('typography_primary', [])) && isset($primary_typo['font-family']) ){
			$pff = "( {$primary_typo['font-family']} )";
		}

		$primary = sprintf(esc_html__('Primary Font %s', 'rey-core'), $pff);

		if( ($secondary_typo = get_theme_mod('typography_secondary', [])) && isset($secondary_typo['font-family']) ){
			$sff = "( {$secondary_typo['font-family']} )";
		}

		$secondary = sprintf(esc_html__('Secondary Font %s', 'rey-core'), $sff);

		return [
			'primary' => $primary,
			'secondary' => $secondary,
		];
	}



	/**
	 * Push (Sync) elementor meta to ACF fields
	 *
	 * @since 1.0.0
	 */
	function elementor_to_acf( $post_id ) {

		$post_type = get_post_type($post_id);

		// settings to update
		$settings = [];

		// get Elementor' meta
		$em = get_post_meta( $post_id, \Elementor\Core\Settings\Page\Manager::META_KEY, true );

		if( empty($em) ){
			return;
		}

		// Title Display
		$settings['title_display'] = isset($em['hide_title']) && $em['hide_title'] == 'yes' ? 'hide' : '';

		if ( class_exists('\ReyCore\Elementor\GlobalSections') && ( $post_type === \ReyCore\Elementor\GlobalSections::POST_TYPE || $post_type === 'revision' ) ) {
			if( isset($em['gs_type']) && ! is_null( $em['gs_type'] ) ){
				$settings['gs_type'] = $em['gs_type'];
			}
		}

		// Transparent gradient
		$settings['rey_body_class'] = isset($em['rey_body_class']) ? $em['rey_body_class'] : '';

		if( !empty($settings) && class_exists('\ACF') ){
			foreach ($settings as $key => $value) {
				update_field($key, $value, $post_id);
			}
		}
	}


	/**
	 * Add Rey Config into Elementor's
	 *
	 * @since 1.0.0
	 **/
	public function localized_settings( $settings )
	{

		if( ! apply_filters('reycore/elementor/quickmenu', reycore__get_props('elementor_menu')) ){
			return $settings;
		}

		$settings['rey'] = [
			'global_links' => [

				'dashboard' => [
					'title' => esc_html__('WordPress Dashboard', 'rey-core'),
					'link' => esc_url( admin_url() ),
					'icon' => 'eicon-wordpress'
				],

				'exit_backend' => [
					'title' => esc_html__('Exit to page backend', 'rey-core'),
					'link' => add_query_arg([
						'post' => get_the_ID(),
						'action' => 'edit',
						], admin_url( 'post.php' )
					),
					'icon' => 'fa fa-code',
					'show_in_el_menu' => false,
				],

				'global_sections' => [
					'title' => esc_html__('Global Sections', 'rey-core'),
					'link' => add_query_arg([
						'post_type' => \ReyCore\Elementor\GlobalSections::POST_TYPE
						], admin_url( 'edit.php' )
					),
					'icon' => 'fa fa-columns'
				],

				'customizer' => [
					'title' => esc_html__('Customizer Settings', 'rey-core'),
					'link' => add_query_arg([
						'url' => get_permalink(  )
						], admin_url( 'customize.php' )
					),
					'icon' => 'fa fa-paint-brush',
					'class' => '--top-separator'
				],
				'settings' => [
					'title' => sprintf(esc_html__('%s Settings', 'rey-core'), reycore__get_props('theme_title')),
					'link' => add_query_arg([
						'page' => 'rey-settings'
						], admin_url( 'admin.php' )
					),
					'icon' => 'fa fa-cogs'
				],
				'custom_css' => [
					'title' => esc_html__('Additional CSS', 'rey-core'),
					'link' => add_query_arg([
						'autofocus[section]' => 'custom_css',
						'url' => get_permalink(  )
						], admin_url( 'customize.php' )
					),
					'icon' => 'fa fa-code'
				],
				'new_page' => [
					'title' => esc_html__('New Page', 'rey-core'),
					'link' => add_query_arg([
						'post_type' => 'page'
						], admin_url( 'post-new.php' )
					),
					'icon' => 'fa fa-edit',
					'class' => '--top-separator'
				],
				'new_global_section' => [
					'title' => esc_html__('New Global Section', 'rey-core'),
					'link' => add_query_arg([
						'post_type' => \ReyCore\Elementor\GlobalSections::POST_TYPE
						], admin_url( 'post-new.php' )
					),
					'icon' => 'fa fa-edit'
				],

			]
		];

		return $settings;
	}


	/**
	 * Load WooCommerce's
	 * On Editor - Register WooCommerce frontend hooks before the Editor init.
	 * Priority = 5, in order to allow plugins remove/add their wc hooks on init.
	 *
	 * @since 1.0.0
	 */
	public function register_wc_hooks(){
		if( ! class_exists('\WooCommerce') ){
			return;
		}

		WC()->frontend_includes();
	}

	/**
	 * Sync Customizer's colors with Elementor's
	 * Only update if the scheme's haven't been modified in Elementor
	 *
	 * @since 1.0.0
	 */
	public function update_elementor_schemes()
	{

		if( ! class_exists('\Elementor\Core\Schemes\Base') ){
			return;
		}

		if( get_option(\Elementor\Core\Schemes\Base::LAST_UPDATED_META) ){
			return;
		}

		// Color Scheme
		$el_scheme_color = get_option( 'elementor_scheme_color' );

		if( $el_scheme_color && is_array($el_scheme_color) ){
			// Theme Text Color
			$text_color = get_theme_mod('style_text_color');
			// Primary
			$el_scheme_color[1] = $text_color ? $text_color : '#373737';
			// Text
			$el_scheme_color[3] = $text_color ? $text_color : '#373737';
			// Accent
			$el_scheme_color[4] = get_theme_mod('style_accent_color', '#212529');

			update_option( 'elementor_scheme_color', $el_scheme_color );
		}

		// Typography
		$el_scheme_typography = get_option( 'elementor_scheme_typography' );

		if( $el_scheme_typography && is_array($el_scheme_typography) )
		{
			foreach($el_scheme_typography as $key => $typography_scheme){
				// Just reset to defaults
				$el_scheme_typography[$key]['font_family'] = '';
			}
			update_option( 'elementor_scheme_typography', $el_scheme_typography );
		}
	}

	/**
	 * Wierd bug in Elementor Preview, where ACF
	 * is not getting the proper POST ID
	 *
	 * @since 1.0.0
	 */
	function acf_validate_post_id( $pid, $_pid ){
		// No need ATM
		// if( isset($_GET['preview_id']) && isset($_GET['preview_nonce']) ){
		// 	return $_pid;
		// }
		return $pid;
	}


	function library__page_templates_support( $templates ){
		$rey_templates = function_exists('rey__page_templates') ? rey__page_templates() : [];
		return $templates + $rey_templates;
	}

	/**
	 * Add an attribute to html tag
	 *
	 * @since 1.0.0
	 **/
	function filter_html_tag()
	{
		add_filter('language_attributes', function($output){
			$attributes[] = sprintf("data-post-type='%s'", get_post_type());
			return $output . implode(' ', $attributes);
		} );
	}

	/**
	 * Disable page builder button
	 *
	 * @since 1.6.x
	 */
	public function shop_page_disable_button($classes){

		if( class_exists('\WooCommerce') && (get_the_ID() === wc_get_page_id('shop')) && apply_filters('reycore/elementor/hide_shop_page_btn', true) ){
			$classes .= ' --prevent-elementor-btn ';
		}

		return $classes;
	}

	/**
	 * Temporarily disabled BC https://github.com/hogash/rey/issues/291
	 *
	 * @param object $section
	 * @return void
	 */
	public function add_defer_js_control($section){

		if( ! $section ){
			return;
		}

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'perf__js_elementor',
			'label'       => esc_html_x( 'Elementor JS Defer', 'Customizer control title', 'rey-core' ),
			'help' => [
				esc_html_x( 'This option will defer Elementor\'s JavaScript and prevent it from render blocking the page. If you are using Elementor 3rd party plugins, addons, etc. please keep this option disabled because it might cause JavaScript errors in those plugins.', 'Customizer control description', 'rey-core')
			],
			'default'     => false,
		] );

	}


	public function add_grid_type_control($section){

		if( ! $section ){
			return;
		}

		$section->add_title( esc_html__('Elementor settings', 'rey-core'));

		$section->add_control( [
			'type'        => 'select',
			'settings'    => 'elementor_grid',
			'label'       => esc_html__( 'Elementor Section Gaps', 'rey-core' ),
			'help' => [
				sprintf(__( 'Rey\'s override will make the Section gaps between Columns actually act like gaps instead of Column inner padding. This should reduce the need for customizing each Column to adjust its padding and margins. More on <a href="%s" target="_blank">Elementor Default vs. Rey Overrides</a>', 'rey-core' ), reycore__support_url('kb/elementor-grid-type-default-vs-rey-overrides/')),
				'clickable' => true
			],
			'default'     => 'rey',
			'choices'     => [
				'rey' => esc_html__( 'Theme Override', 'rey-core' ),
				'default' => esc_html__( 'Elementor Default', 'rey-core' ),
			],
			'css_class' => '--c-size-sm',
		] );

		if( \ReyCore\Elementor\Helper::is_experiment_active('container') ):

		$section->add_control( [
			'type'        => 'rey-number',
			'settings'    => 'containers_top_bottom_spacing',
			'label'       => esc_html__( 'Containers Top/Bottom Padding', 'rey-core' ) . ' (px)',
			'help' => [
				sprintf(__( 'Adds a default Top and Bottom padding to Container elements. Default %s.', 'rey-core' ), '<strong>10px</strong>')
			],
			'transport'   		=> 'auto',
			'output'      		=> [
				[
					'element'  => ':root',
					'property' => '--container-default-padding-top',
					'units'    => 'px',
				],
				[
					'element'  => ':root',
					'property' => '--container-default-padding-bottom',
					'units'    => 'px',
				],
			],
			'default'        => '',
			'choices'     => [
				// 'min'  => 0,
				'max'  => 50,
				'step' => 1,
				'placeholder' => 10,
			],
		] );

		endif;
	}

	/**
	 * Sets body class in Elementor element
	 * @since 3.0.0
	 */
	function ajax_set_pages( $ajax_manager ) {

		$ajax_manager->register_ajax_action( 'rey_set_anim_header', function ( $data ){

			if( empty($data['editor_post_id']) ){
				return;
			}

			if( ! isset($data['action']) ){
				return;
			}

			$action = reycore__clean($data['action']);

			$classes = (string) get_field('rey_body_class', $data['editor_post_id']);
			$css_class = ' rey-animated-header';

			if( $action === 'disable' ) {
				$classes = str_replace($css_class, '', $classes);
			}
			else {
				$classes .= $css_class;
			}

			update_field('rey_body_class', $classes, $data['editor_post_id']);

			return $action === 'disable' ? 'enable' : 'disable';
		} );

	}
}
