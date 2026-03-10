<?php
namespace ReyCore\ACF;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use ReyCore\Plugin;
use ReyCore\ACF\Helper;

class Base {

	public $force_singular_settings_support;

	public function __construct(){

		if ( ! class_exists('\ACF') ) {
			return;
		}

		do_action('reycore/acf', $this);

		require_once __DIR__ . '/fields.php';

		add_filter('acf/settings/show_admin', [$this, 'show_admin']);
		add_action('acf/init', [$this, 'acf_init']);
		add_action('acf/include_field_types', [$this, 'include_field_types']);
		add_action('template_redirect', [$this, 'redirect_url'], 10);
		add_filter('rey/add_titles', [$this, 'remove_titles']);
		add_action('wp', [$this, 'remove_product_archive_titles']);
		add_action('acf/save_post', [$this, 'to_elementor'], 20);
		add_action('acf/save_post', [$this, 'to_customizer'], 20);
		add_action('admin_footer', [$this, 'responsive_handlers'] );
		add_action('acf/render_field', [$this, 'render_field_adobe'] );
		add_action('acf/render_field/name=gs_type', [$this, 'add_descriptions_to_gs_types'] );

		add_filter('acf/prepare_field/name=top_sticky_gs', [$this, 'populate_sticky_lists']);
		add_filter('acf/prepare_field/name=bottom_sticky_gs', [$this, 'populate_sticky_lists']);
		add_filter('acf/prepare_field/name=header_layout_type', [$this, 'rename_placeholder']);
		add_filter('acf/prepare_field/name=header_position', [$this, 'rename_placeholder']);
		add_filter('acf/prepare_field/name=header_fixed_overlap', [$this, 'rename_placeholder']);
		add_filter('acf/prepare_field/name=footer_layout_type', [$this, 'rename_placeholder']);
		add_filter('acf/prepare_field/name=title_display', [$this, 'rename_placeholder']);
		add_filter('acf/prepare_field/name=page_cover', [$this, 'rename_placeholder']);
		add_filter('acf/prepare_field/name=product_content_after_summary', [$this, 'product_page_options_global_lists']);
		add_filter('acf/prepare_field/name=product_content_after_content', [$this, 'product_page_options_global_lists']);
		add_filter('acf/prepare_field/name=header_layout_type', [$this, 'set_header_styles'] );
		add_filter('acf/prepare_field/name=footer_layout_type', [$this, 'set_footer_types'] );
		add_filter('acf/prepare_field/name=gs_type', [$this, 'set_gs_types'] );
		add_filter('acf/prepare_field/name=remove_global_sections', [$this, 'remove_global_sections_list'] );
		add_filter('acf/prepare_field/name=global_sections_id', [$this, 'populate_global_sections_id'] );
		add_filter('acf/prepare_field/name=page_cover', [$this, 'populate_page_cover'] );
		add_filter('acf/prepare_field/name=singular_settings_support_roles', [$this, 'populate__singular_settings_support_roles'] );

		add_filter('acf/prepare_field/name=singular_settings_support', [$this, 'populate__singular_settings_support'] );
		add_filter('acf/load_field/name=singular_settings_support', [$this, 'set_default__singular_settings_support'] );
		add_filter('acf/update_value/name=singular_settings_support', [$this, 'update__singular_settings_support'], 10, 3);

		add_filter('acf/prepare_field/name=singular_settings_support_taxonomy', [$this, 'populate__singular_settings_support_taxonomy'] );
		add_filter('acf/load_field/name=singular_settings_support_taxonomy', [$this, 'set_default__singular_settings_support_taxonomy'] );
		add_filter('acf/update_value/name=singular_settings_support_taxonomy', [$this, 'update__singular_settings_support_taxonomy'], 10, 3);

		add_filter('acf/load_field_group', [$this, 'field_group__singular_settings_support'] );
		add_filter('acf/load_field_groups', [$this, 'field_group__singular_settings_support_roles'], 20);
		add_filter('acf/input/meta_box_priority', [$this, 'input_meta_box_priority'], 10, 2);
		add_filter('acf/location/rule_types', [$this, 'location_rule_types'] );
		add_filter('acf/location/rule_values/wc_prod_attr_visibility', [$this, 'location_rule_values'] );
		add_filter('acf/location/rule_match/wc_prod_attr_visibility', [$this, 'location_rule_match'], 10, 3 );

		new Helper;

		/**
		 * Forces "Page Settings" ACF settings panel,
		 * for default posts.
		 */
		$this->force_singular_settings_support = apply_filters('reycore/acf/force_singular_settings_support', false);
	}

	function acf_init(){

		// prevent showing updates
		acf_update_setting('show_updates', false);

		$this->add_theme_settings_page();

	}

	function add_theme_settings_page(){

		/**
		 * Adds settings page
		 */
		$opts = [
			'page_title'     => __( 'Settings', 'rey-core' ),
			'menu_title'	 => __( 'Settings', 'rey-core' ),
			'menu_slug' 	 => REY_CORE_THEME_NAME . '-settings',
			'capability'	 => 'manage_options',
			'post_id'        => REY_CORE_THEME_NAME,
			'update_button'	 => __('Save Options', 'rey-core'),
		];

		if( $dashboard_page_id = reycore__get_dashboard_page_id() ){
			$opts['parent_slug'] = $dashboard_page_id;
		}

		if( ! function_exists('acf_add_options_page') ){
			if( apply_filters('reycore/acf/can_show_lite_notice', true) ){
			add_action('admin_notices', function (){
				_e('<div class="notice notice-error is-dismissible"><p>You\'re using Advanced Custom Fields Lite but Rey Core already has the PRO version built-in. Please head over to Plugins and <strong>delete Advanced Custom Fields plugin</strong>.</p></div>', 'rey-core');
			});
			}
			return;
		}

		acf_add_options_page($opts);

		add_action('admin_notices', function (){

			$current = get_current_screen();

			if ( $current->id == 'rey-theme_page_rey-settings' ) {
				printf(
					__('<div class="notice notice-info is-dismissible"><p>To customize the theme, head over to <a href="%s" target="_blank">Customizer</a>. To learn more about these options, please visit <a href="%s" target="_blank">Rey\'s Documentation</a>.</p></div>', 'rey-core'),
					add_query_arg( [
						'url' => get_site_url()
					], admin_url( 'customize.php' ) ),
					reycore__support_url('kb/theme-settings-backend/')
				);
			}
		});

	}

	/**
	 * Show ACF menu in WP Dashboard
	 *
	 * @return bool
	 */
	function show_admin(){
		return reycore__acf_get_field('acf_fields_panel', REY_CORE_THEME_NAME) !== false;
	}

	/**
	 * Register custom fields
	 *
	 * @since 1.0.0
	 */
	function include_field_types( $version = false ){

		if( !is_admin() ){
			return;
		}

		if( is_customize_preview() ){
			return;
		}

		// support empty $version
		if ( ! $version ) {
			$version = 5;
		}

		include_once __DIR__ . '/custom-fields/acf-global-sections.php';
		include_once __DIR__ . '/custom-fields/table/acf-field-table.php';
	}

	/**
	 * Redirect if enabled in post/page settings
	 *
	 * @since 1.0.0
	 */
	function redirect_url(){

		if ( ! is_admin() && reycore__acf_get_field('general_redirect') && ($location = reycore__acf_get_field('redirect_url')) ) {
			$status = apply_filters('reycore/general_redirect/status', 302, $location);
			wp_redirect( $location, $status );
			exit;
		}

	}

	/**
	 * Set description for Global Sections - Type selector.
	 *
	 * @since 1.0.0
	 **/
	public static function get_gs_type_info()
	{

		return sprintf(
			__('This is a powerful tool for embedding Elementor built content anywhere in the website through various means, such as Customizer Options, Elementor Widgets, Shortcodes and even PHP codes. <a href="%s" target="_blank">Learn More</a>.', 'rey-core'),
			reycore__support_url('/kbtopic/global-sections/')
		);

	}

	/**
	 * Removes title if it's disabled in post/page options
	 *
	 * @since 1.0.0
	 **/
	function remove_titles( $status )
	{
		if( reycore__acf_get_field('title_display') && reycore__acf_get_field('title_display') == 'hide' ) {
			$status = false;
		}

		return $status;
	}

	/**
	 * Removes title if it's disabled in product archive's ACF settings
	 *
	 * @since 1.0.0
	 **/
	function remove_product_archive_titles()
	{
		if(
			class_exists('\WooCommerce') &&
			(is_product_category() || is_product_taxonomy() || is_product_tag() || is_shop()) &&
			reycore__acf_get_field('title_display') == 'hide'
		){
			// remove title
			add_filter( 'woocommerce_show_page_title', '__return_false', 10 );
			// remove breadcrumb too
			remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
			// remove description
			remove_action( 'woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10 );
			remove_action( 'woocommerce_archive_description', 'woocommerce_product_archive_description', 10 );
		}
	}

	/**
	 * Populate Sticky global sections options in acf lists
	 *
	 * @since 1.6.x
	 **/
	function populate_sticky_lists($field)
	{
		if( class_exists('\ReyCore\Elementor\GlobalSections') && isset($field['choices']) ) {
			if( ($gs = \ReyCore\Elementor\GlobalSections::get_global_sections(['generic','header'])) && is_array($gs) ){
				$field['choices'] = $field['choices'] + $gs + ['none' => esc_html__('- Disabled -', 'rey-core')];
			}
		}
		return $field;
	}

	/**
	 * Renames placeholder for some options
	 *
	 * @since 1.3.5
	 **/
	function rename_placeholder( $field )
	{
		$field['placeholder'] = esc_html__('Inherit', 'rey-core');
		return $field;
	}

	/**
	 * Populate ACF's Global Sections list in Product page
	 *
	 * @since 1.0.0
	 */
	function product_page_options_global_lists( $field ) {

		if( ! class_exists('\ReyCore\Elementor\GlobalSections') ){
			return $field;
		}

		$generic_sections_items = \ReyCore\Elementor\GlobalSections::get_global_sections('generic', [
			'none'  => esc_attr__( '- Disabled -', 'rey-core' )
		]);

		if( isset($field['choices']) && is_array($generic_sections_items) && !empty($generic_sections_items) ) {
			$field['choices'] = $field['choices'] + $generic_sections_items;
		}

		return $field;
	}

	/**
	 * Push ACF fields to elementor meta
	 *
	 * @since 1.0.0
	 */
	function to_elementor( $post_id ) {

		if( !class_exists('\Elementor\Core\Settings\Page\Manager') ){
			return;
		}

		$elementor_meta = get_post_meta( $post_id, \Elementor\Core\Settings\Page\Manager::META_KEY, true );

		if ( ! $elementor_meta ) {
			$elementor_meta = [];
		}

		$post_type = get_post_type($post_id);

		// Title Display
		$elementor_meta['hide_title'] = reycore__acf_get_field('title_display', $post_id, '')  == 'hide' ? 'yes' : '';

		if ( class_exists('\ReyCore\Elementor\GlobalSections') && $post_type === \ReyCore\Elementor\GlobalSections::POST_TYPE ) {
			$gs_type = sanitize_text_field(reycore__acf_get_field('gs_type', $post_id, '') );

			if( $gs_type === '' ){
				$gs_type = 'generic';
			}

			$elementor_meta['gs_type'] =  $gs_type;
		}

		// Body Class
		$elementor_meta['rey_body_class'] = sanitize_text_field( reycore__acf_get_field('rey_body_class', $post_id, '') );

		update_post_meta( $post_id, \Elementor\Core\Settings\Page\Manager::META_KEY, $elementor_meta );
	}

	/**
	 * Push ACF fields to Customizer's options
	 *
	 * @since 1.0.0
	 */
	function to_customizer( $post_id )
	{
		// Shop Page
		if( class_exists('\WooCommerce') && wc_get_page_id('shop') == $post_id ){
			set_theme_mod('cover__shop_page', reycore__acf_get_field('page_cover', $post_id, '') );
		}
		// Blog page
		if( get_option( 'page_for_posts' ) == $post_id ){
			set_theme_mod('cover__blog_home', reycore__acf_get_field('page_cover', $post_id, '') );
		}
	}

	/**
	 * Add responsive handlers
	 *
	 * @since 1.3.5
	 **/
	function responsive_handlers()
	{ ?>
		<script type="text/html" id="tmpl-rey-acf-responsive-handler">
			<div class="rey-acf-responsiveHandlers">
				<span data-breakpoint="desktop"></span>
				<span data-breakpoint="tablet"></span>
				<span data-breakpoint="mobile"></span>
			</div>
		</script>
		<?php
	}

	/**
	 * Populate ACF header layouts choices
	 *
	 * @since 1.0.0
	 */
	function set_header_styles( $field ) {

		$styles = reycore__get_header_styles();

		if( isset($field['choices']) && is_array($styles) ) {
			$field['choices'] = $field['choices'] + $styles;
		}

		$field['placeholder'] = esc_html__('Inherit option (from Customizer > Header > General)', 'rey-core');
		$field['instructions'] = reycore__header_footer_layout_desc( 'header', true );

		return $field;
	}

	/**
	 * Populate ACF header layouts choices
	 *
	 * @since 1.0.0
	 */
	function set_footer_types( $field ) {

		$styles = reycore__get_footer_styles();

		if( isset($field['choices']) && is_array($styles) ) {
			$field['choices'] = $field['choices'] + $styles;
		}

		$field['instructions'] = reycore__header_footer_layout_desc( 'footer', true );
		return $field;
	}

	public static function add_icons_to_gs_types( $choices ){

		$map = apply_filters('reycore/acf/global_section_icons', [
			'generic'   => 'woo-pdp-layout',
			'header'    => 'header',
			'footer'    => 'footer',
			'cover'     => 'page-cover',
		]);

		foreach ($map as $type => $icon) {
			if( isset($choices[$type]) ){
				$choices[$type] = sprintf('<i data-icon-path="#cs-icon-%s"></i>', $icon) . $choices[$type];
			}
		}

		return $choices;
	}

	public function add_descriptions_to_gs_types( $field ){

		$descs = [];

		// Generic
		$generic_link_query['autofocus[control]'] = 'global_sections';
		$generic_link = add_query_arg( $generic_link_query, admin_url( 'customize.php' ) );

		$generic_desc = sprintf(
			__('<strong>Generic sections</strong> can be assigned to different page positions, either in Customizer\'s <a href="%s" target="_blank">%s</a> (for site-wide use), or for specific pages - into the page (or taxonomy) custom options, or in various places inside the content. <a href="%s" target="_blank">Learn More</a>.', 'rey-core'),
			esc_url( $generic_link ),
			esc_html__('Global Sections', 'rey-core'),
			reycore__support_url('/kb/how-to-create-generic-global-sections/')
		);

		$descs['generic'] = $generic_desc;

		// Header Footer

		$hf_desc = __('Build your website %1$s with Elementor. %1$s need to be manually assigned in Customizer\'s <a href="%3$s" target="_blank" title="Will open in a new tab">%2$s</a> panel, or for specific pages - into the page (or taxonomy) custom options. <a href="%4$s" target="_blank">Learn More</a>.', 'rey-core');

		$descs['header'] = sprintf(	$hf_desc,
			esc_html__('Headers', 'rey-core'),
			esc_html__('Header', 'rey-core'),
			esc_url( add_query_arg( ['autofocus[control]' => 'header_layout_type'], admin_url( 'customize.php' ) ) ),
			esc_url( reycore__support_url('/kb/how-to-create-headers/') )
		);

		$descs['footer'] = sprintf(	$hf_desc,
			esc_html__('Footers', 'rey-core'),
			esc_html__('Footer', 'rey-core'),
			esc_url( add_query_arg( ['autofocus[control]' => 'footer_layout_type'], admin_url( 'customize.php' ) ) ),
			esc_url( reycore__support_url('/kb/how-to-create-footers/') )
		);

		// Cover
		$cover_link_query['autofocus[panel]'] = 'cover';
		$cover_link = add_query_arg( $cover_link_query, admin_url( 'customize.php' ) );

		$cover_desc = sprintf(
			__('The Page Cover section is the block between the <strong>Header</strong> and <strong>Page Content</strong> which is built entirely of Elementor global sections. They can be either published in Customizer\'s <a href="%s" target="_blank" title="Will open in a new tab">%s</a> panel, or for specific pages - into the page (or taxonomy) page options. <a href="%s" target="_blank">Learn More</a>.', 'rey-core'),
			esc_url( $cover_link ),
			esc_html__('Page Covers', 'rey-core'),
			reycore__support_url('/kb/how-to-create-page-covers/')
		);

		$descs['cover'] = $cover_desc;

		$descs = apply_filters('reycore/acf/global_section_descriptions', $descs);

		$content = '';

		foreach ($descs as $type => $desc) {
			$content .= sprintf('<div class="__gs-item" data-type="%s" style="display:none">%s</div>', esc_attr($type), $desc);
		}

		if( $content ){
			printf('<div class="__gs-item-desc">%s</div>', $content);
		}

	}

	/**
	 * Populate ACF global section types
	 *
	 * @since 1.0.0
	 */
	function set_gs_types( $field ) {

		if( class_exists('\ReyCore\Elementor\GlobalSections') && isset($field['choices']) ) {

			$field['choices'] = \ReyCore\Elementor\GlobalSections::get_global_section_types();
			$field['choices'] = self::add_icons_to_gs_types($field['choices']);

			if( isset($_REQUEST['gs_type']) && array_key_exists($_REQUEST['gs_type'], $field['choices'] ) ){
				$field['value'] = $_REQUEST['gs_type'];
			}

		}

		if( isset($field['instructions']) ) {
			$field['instructions'] = self::get_gs_type_info();
		}

		return $field;
	}


	/**
	 * Populate ACF adobe fonts list in theme settings
	 *
	 * @since 1.0.0
	 */
	function render_field_adobe( $field ) {

		reycore__maybe_disable_obj_cache();

		if( $field['_name'] == 'adobe_fonts_project_id' && $adobe_fonts = get_transient( 'rey_adobe_fonts' ) ){
			foreach( $adobe_fonts as $key => $font ) {
				printf( __('<p><strong>%s</strong> font. Weights <strong>%s</strong>. Use <em>"font-family: %s;"</em> in CSS. </p>', 'rey-core'),
					$font['family'],
					implode(', ', $font['font_variants']),
					$font['font_name']
				);
			}
		}
	}


	/**
	 * Populate ACF's Remove Global Sections, if there are
	 * assignments in Customizer's > Global Sections
	 *
	 * @since 1.0.0
	 */
	function remove_global_sections_list( $field ) {

		$sections = get_theme_mod('global_sections', false);

		// Don't bother to display field
		// if it's empty
		if( empty($sections) ) {
			return false;
		}

		if( isset($field['choices']) && is_array($sections) && !empty($sections) ) {
			foreach ($sections as $k => $s) {
				if( ! $s['id'] ){
					continue;
				}
				// dumb solution to keep id
				$field['choices'][$s['id'] . '__' . $s['hook']] = get_the_title($s['id']) . ' ( ' . $s['hook'] . ' )';
			}
		}
		return $field;
	}

	/**
	 * Populate ACF's Global Sections list
	 *
	 * @since 1.0.0
	 */
	function populate_global_sections_id( $field ) {

		$generic_sections_items = \ReyCore\Elementor\GlobalSections::get_global_sections('generic');

		if( isset($field['choices']) && is_array($generic_sections_items) && !empty($generic_sections_items) ) {
			$field['choices'] = $field['choices'] + $generic_sections_items;
		}

		return $field;
	}

	/**
	 * Populate ACF page cover choices
	 *
	 * @since 1.0.0
	 */
	function populate_page_cover( $field ) {

		$styles = \ReyCore\Elementor\GlobalSections::get_global_sections('cover', [
			'no'  => esc_attr__( 'Disabled', 'rey-core' )
		]);

		if( isset($field['choices']) && is_array($styles) ) {
			$field['choices'] = $field['choices'] + $styles;
		}

		return $field;
	}


	function populate__singular_settings_support( $field ) {
		if( $new_choices = reycore__get_post_types_list() ){
			$field['choices'] = $new_choices;
		}
		return $field;
	}

	function set_default__singular_settings_support( $field ) {
		$field['default_value'] = Helper::default_supported_singular_post_types('post_type');
		return $field;
	}

	function update__singular_settings_support( $value, $post_id, $field ) {
		if( $this->force_singular_settings_support ){
			return array_unique(array_merge($value, Helper::default_supported_singular_post_types('post_type')));
		}
		return $value;
	}

	function populate__singular_settings_support_taxonomy( $field ) {

		global $wp_taxonomies;

		foreach ($wp_taxonomies as $tax_id => $taxonomy) {

			// Only for public taxonomies
			if( isset($taxonomy->public) && ! $taxonomy->public ){
				continue;
			}

			// Exclude some of the taxonomies
			if( in_array($tax_id, [
				'nav_menu',
				'link_category',
				'post_format',
				'wp_theme',
				'wp_template_part_area',
				'elementor_library_type',
				'elementor_library_category',
				'product_type',
				'product_visibility',
				'product_shipping_class',
			], true) ){
				continue;
			}

			$field['choices'][$tax_id] = $taxonomy->label;
		}

		return $field;
	}

	function set_default__singular_settings_support_taxonomy( $field ) {
		$field['default_value'] = Helper::default_supported_singular_post_types('taxonomy');
		return $field;
	}

	function update__singular_settings_support_taxonomy( $value, $post_id, $field ) {
		if( $this->force_singular_settings_support ){
			return array_unique(array_merge($value, Helper::default_supported_singular_post_types('taxonomy')));
		}
		return $value;
	}

	/**
	 * Set the Page Settings ACF field group
	 * to the supported post type and taxonomies in the backend.
	 *
	 * @param array $field_group
	 * @return array
	 */
	public function field_group__singular_settings_support($field_group){

		if( \ReyCore\ACF\Helper::prevent_export_dynamic_field() ){
			return $field_group;
		}

		if( ! (isset($field_group['key']) && $field_group['key'] === 'group_5c4ad0bd35b33') ){
			return $field_group;
		}

		$defaults = Helper::default_supported_singular_post_types();

		$custom = [
			'post_type' => reycore__acf_get_field('singular_settings_support', REY_CORE_THEME_NAME),
			'taxonomy' => reycore__acf_get_field('singular_settings_support_taxonomy', REY_CORE_THEME_NAME),
		];

		foreach ($custom as $type => $custom_values) {

			$values = $defaults[$type];

			if( $custom_values && is_array($custom_values) ){
				$values = $custom_values;
			}

			if( $this->force_singular_settings_support ){
				$values = array_unique(array_merge($values, $defaults[$type]));
			}

			foreach ($values as $value) {
				$field_group['location'][] = [
					[
						'param' => $type,
						'operator' => '==',
						'value' => $value,
					]
				];
			}

		}

		return $field_group;
	}

	function populate__singular_settings_support_roles( $field ) {

		if( function_exists('get_editable_roles') ){

			$roles = get_editable_roles();

			$field['choices']['all'] = esc_html__('All', 'rey-core');

			foreach ($roles as $key => $value) {
				$field['choices'][$key] = $value['name'];
			}

		}

		return $field;
	}

	function field_group__singular_settings_support_roles($field_groups){

		if( \ReyCore\ACF\Helper::prevent_export_dynamic_field() ){
			return $field_groups;
		}

		foreach ($field_groups as $key => $field_group) {
			if( ! (isset($field_group['key']) && $field_group['key'] === 'group_5c4ad0bd35b33') ){
				continue;
			}

			if( !($role = reycore__acf_get_field('singular_settings_support_roles', REY_CORE_THEME_NAME)) ){
				continue;
			}

			if( $role === 'all' ){
				continue;
			}

			if( ! current_user_can($role) ){
				$field_groups[$key]['active'] = false;
			}
		}

		return $field_groups;
	}


	/**
	 * Fix metabox position in product pages
	 */
	function input_meta_box_priority($priority, $field_group){

		// adjust Singular settings position in page
		// On product page, needs to be super low
		if( get_post_type() === 'product' && isset($field_group['key']) ) {
			if( $field_group['key'] === 'group_5c4ad0bd35b33' || $field_group['key'] === 'group_5d4ff536a2684' ){
				$priority = 'low';
			}
		}

		return $priority;
	}


	// Adds a custom rule type.
	function location_rule_types( $choices ){
		$choices[ __("Other",'acf') ]['wc_prod_attr_visibility'] = 'Product Attribute Visibility';
		return $choices;
	}

	// Adds custom rule values.
	function location_rule_values( $choices ){
		$choices['attribute_public'] = "Public";
		return $choices;
	}


	// Matching the custom rule.
	function location_rule_match( $match, $rule, $options ){

		if( ! (isset($rule['param']) && 'wc_prod_attr_visibility' === $rule['param']) ){
			return $match;
		}

		if( 'attribute_public' !== $rule['value'] ){
			return $match;
		}

		if( '==' !== $rule['operator'] ){
			return $match;
		}

		if ( ! isset( $options['taxonomy'] ) ) {
			return $match;
		}

		if ( ! function_exists('wc_get_attribute_taxonomies') ) {
			return $match;
		}

		foreach ( wc_get_attribute_taxonomies() as $attribute ) {

			if( $options['taxonomy'] === wc_attribute_taxonomy_name( $attribute->attribute_name ) ){

				if( (bool) $attribute->attribute_public ){
					$match = true;
					continue;
				}

			}

		}

		return $match;

	}
}
