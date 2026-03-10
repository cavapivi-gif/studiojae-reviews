<?php
namespace ReyCore\Modules\MegaMenus;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	const ASSET_HANDLE = 'reycore-module-mega-menu';

	const SUPPORTED_MENUS = 'rey_mega_menus_supported_menus';

	const GSTYPE = 'megamenu';

	const AJAX_LAZY_ACTION = 'get_megamenu';

	public $lazy_items = [];

	public function __construct()
	{
		add_action( 'reycore/elementor', [$this, 'init']);
	}

	public function init() {

		if( ! $this->is_enabled() ){
			return;
		}

		new AcfFields();

		add_filter( 'reycore/global_sections/types', [$this, 'add_support']);
		add_filter( 'reycore/acf/global_section_icons', [$this, 'add_icon'], 20);
		add_filter( 'reycore/acf/global_section_descriptions', [$this, 'add_description'], 20);
		add_filter( 'acf/prepare_field/name=menu_global_section', [$this, 'add_global_sections_into_lists']);
		// add_action( 'wp_nav_menu_item_custom_fields', [$this, 'add_notice']);
		add_action( 'admin_footer', [$this, 'add_options']);
		add_action( 'wp_update_nav_menu', [$this, 'save_mm_option']);
		add_filter( 'walker_nav_menu_start_el_mega', [$this, 'render_mega_menu'], 10, 4 );
		add_filter( 'nav_menu_item_args', [$this, 'set_support_args'], 10, 3);
		add_filter( 'nav_menu_css_class', [$this, 'mega_menu_item_classes'], 20, 4);
		add_filter( 'wp_nav_menu_args', [$this, 'set_mega_menu_support']);
		add_filter( 'rey/css_styles', [$this, 'main_menu_submenus_styles'] );
		// add_filter( 'nav_menu_link_attributes', [$this, 'mega_menu_link_args'], 10, 4);
		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
		add_filter( 'reycore/elementor/header_nav/show_in_panel', [$this, 'header_nav_element']);
		add_action( 'reycore/ajax/register_actions', [ $this, 'register_actions' ] );
		add_action( 'save_post', [$this, 'flush_offcanvas_transient_ids'], 20, 2 );
		add_action( 'delete_post', [$this, 'flush_offcanvas_transient_ids'], 20, 2 );
		add_action( 'reycore/elementor/document_settings/gs/before', [$this, 'gs_settings']);
		add_action( 'wp_update_nav_menu', [$this, 'regenerate_customizer_css']);

	}

	public function add_support( $gs ){
		$gs[self::GSTYPE]  = __( 'Mega Menu', 'rey-core' );
		return $gs;
	}

	public function add_description( $gs ){
		$gs[self::GSTYPE]  = sprintf(_x('Embed Elementor built mega menus to the header\'s main menu items. <a href="%s" target="_blank">Learn More</a>.', 'Global section description', 'rey-core'), reycore__support_url('/kb/how-to-create-mega-menu-global-sections/') );
		return $gs;
	}

	public function add_icon( $gs ){
		$gs[self::GSTYPE]  = 'header-general';
		return $gs;
	}

	public function register_assets($assets){

		$assets->register_asset('styles', [
			self::ASSET_HANDLE => [
				'src'      => self::get_path( basename( __DIR__ ) ) . '/style' . $assets::rtl() . '.css',
				'deps'     => [],
				'version'  => REY_CORE_VERSION,
				'priority' => 'low'
			]
		]);

		$assets->register_asset('scripts', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/script.js',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			]
		]);

	}


	/**
	 * Populate ACF's Mega Menu Global Sections list
	 *
	 * @since 1.0.0
	 */
	public function add_global_sections_into_lists( $field ) {

		$generic_sections_items = \ReyCore\Elementor\GlobalSections::get_global_sections(self::GSTYPE);

		if( isset($field['choices']) && is_array($generic_sections_items) && !empty($generic_sections_items) ) {
			$field['choices'] = $field['choices'] + $generic_sections_items;
		}

		return $field;
	}

	/**
	 * Show notice for missing Mega menu items
	 */
	public function add_notice() {

		global $nav_menu_selected_id;

		if( ! is_admin() ){
			return;
		}

		if( $menu_locations = get_nav_menu_locations() ){
			if( isset($menu_locations['main-menu']) && $menu_locations['main-menu'] !== absint( $nav_menu_selected_id ) ){
				printf('<p class="field-rey-notice description description-wide">%s</p>', esc_html__('Looking for Mega Menu settings? Make sure to check the "Main Menu" checkbox at the bottom of this page, or "Enable Mega Menus".', 'rey-core') );
			}
		}
	}

	/**
	 * Add mega menu support form options
	 *
	 * @since 1.6.10
	 */
	public function add_options(){

		if( ! (($screen = get_current_screen()) && isset($screen->base) && 'nav-menus' === $screen->base) ){
			return;
		}

		global $nav_menu_selected_id;

		$supported_menus = get_option(self::SUPPORTED_MENUS, []);
		$is_enabled = in_array($nav_menu_selected_id,$supported_menus, true) ? 'checked="checked"' : '';

		if( $menu_locations = get_nav_menu_locations() ){
			if( isset($menu_locations['main-menu']) && $menu_locations['main-menu'] === absint( $nav_menu_selected_id ) ){
				$is_enabled = 'checked="checked"';
			}
		} ?>

		<script>
			jQuery(document).ready(function () {

				var html = '<fieldset class="menu-settings-group rey-enable-mega-menus">' +
								'<legend class="menu-settings-group-name howto"><?php esc_html_e('Enable Mega Menus', 'rey-core')?></legend>' +
								'<div class="menu-settings-input checkbox-input">' +
									'<input type="checkbox" name="rey-enable-mega-menus" id="rey-enable-mega-menus" value="1" <?php echo esc_attr($is_enabled); ?> <label for="rey-enable-mega-menus"><?php esc_html_e('Yes', 'rey-core')?></label>' +
								'</div>' +
							'</fieldset>';

				jQuery(html).insertAfter(jQuery('.menu-settings-group.menu-theme-locations'));
			})
		</script>
		<?php
	}

	/**
	 * Save mega menu enable flag for specific menu
	 *
	 * @since 1.6.10
	 */
	public function save_mm_option( $nav_menu_selected_id ){

		$supported_menus = get_option(self::SUPPORTED_MENUS, []);

		if( isset($_POST['rey-enable-mega-menus']) && absint($_POST['rey-enable-mega-menus']) === 1 ){
			$supported_menus[] = $nav_menu_selected_id;
		}
		else {
			if (($key = array_search($nav_menu_selected_id, $supported_menus)) !== false) {
				unset($supported_menus[$key]);
			}
		}

		update_option(self::SUPPORTED_MENUS, array_unique($supported_menus), false);
	}

	/**
	 * Filter menus to assign support for mega menus
	 *
	 * @since 1.6.10
	 */
	public function set_mega_menu_support($args){

		$args['rey_mega_menu'] = false;
		$header = reycore__get_option('header_layout_type', 'default');

		// only if custom GS header
		if( ! in_array($header, ['default', 'none'], true) ){

			if( ! isset($args['element_type']) ){
				return $args;
			}

			// check for supported elements. Only Header Nav. atm;
			$supported_elements = [
				'reycore-header-navigation'
			];

			if( ! in_array($args['element_type'], $supported_elements, true) ){
				return $args;
			}
		}

		if( is_object($args['menu']) && isset($args['menu']->term_id) ){
			$term_id = $args['menu']->term_id;
		}
		else if (is_numeric($args['menu'])) {
			$term_id = $args['menu'];
		}
		else {
			$term = get_term_by('slug', $args['menu'], 'nav_menu');
			$term_id = isset($term->term_id) ? $term->term_id : $args['menu'];
		}

		$supported_menus = get_option(self::SUPPORTED_MENUS, []);

		if(
			in_array($term_id, $supported_menus, true) ||
			(($menu_locations = get_nav_menu_locations()) && isset($menu_locations['main-menu']) && $menu_locations['main-menu'] === absint( $term_id ) )
		){

			$args['rey_mega_menu'] = true;
			$args['menu_class'] = $args['menu_class'] . ' --megamenu-support';

		}

		if( $args['rey_mega_menu'] && $header === 'default' ){
			if( class_exists('\ReyCore\Libs\Nav_Walker') ){
				$args['walker'] = new \ReyCore\Libs\Nav_Walker;
			}
		}

		return $args;
	}

	/**
	 * Render Mega Menu Global section
	 *
	 * @since: 1.0.0
	 */
	public function render_mega_menu( $item_output, $item, $depth, $args) {

		if( ! class_exists('\ReyCore\Elementor\GlobalSections') ){
			return $item_output;
		}

		if(
			reycore__acf_get_field('mega_menu', $item->ID)
			&& reycore__acf_get_field('mega_menu_type', $item->ID) == 'global_sections'
			&& ($gs_id = reycore__acf_get_field('menu_global_section', $item->ID))
		) {

			$mega_panel_classes = apply_filters('reycore/megamenu_panel/classes', ['rey-mega-gs'], $item);

			// prevent reloading in Edit mode
			if( isset($_REQUEST['action']) && 'elementor_ajax' === $_REQUEST['action'] ){
				return $item_output;
			}

			if( $gs_lazy = $this->is_lazy( $item->ID ) ){

				$item_output .= sprintf( '<div class="%1$s" data-lazy-config=\'%2$s\'></div>', implode(' ', $mega_panel_classes), wp_json_encode([
					'lazy_type' => $gs_lazy,
					'mid' => $gs_id,
				]));

			}

			else {

				if( isset($GLOBALS['gs_mega_menu']) && $GLOBALS['gs_mega_menu'] === $gs_id ){
					return $item_output;
				}

				$GLOBALS['menu_item_id'] = $item->ID;
				$GLOBALS['gs_mega_menu'] = $gs_id;

				// delay elementor stylesheets
				reycore_assets()->defer_page_styles('elementor-post-' . $gs_id, true);

				// collect assets to be downgraded to low priority
				$downgrading_styles_name = 'mega_menu_gs_' . $gs_id;
				reycore_assets()->collect_start($downgrading_styles_name);

				// load section
				$item_output .= sprintf( '<div class="%1$s">%2$s</div>', implode(' ', $mega_panel_classes),  \ReyCore\Elementor\GlobalSections::do_section( $gs_id ) );

				// collect end & downgrade styles priorities
				reycore_assets()->downgrade_styles_priority($downgrading_styles_name);

				unset($GLOBALS['gs_mega_menu']);
				unset($GLOBALS['menu_item_id']);
			}

		}

		return $item_output;
	}

	public function is_lazy( $post_id ){

		if( isset($this->lazy_items[ $post_id ]) ){
			return $this->lazy_items[ $post_id ];
		}

		if( reycore__elementor_edit_mode() ){
			return $this->lazy_items[ $post_id ] = false;
		}

		return $this->lazy_items[ $post_id ] = ($type = reycore__acf_get_field('mega_lazy', $post_id)) ? $type : false;
	}

	/**
	 * Mega Menu Args
	 *
	 * @since: 1.0.0
	 */
	public function set_support_args( $args, $menu_item, $depth)
	{

		unset($args->mega_classes);

		if( ! (isset($args->rey_mega_menu) && $args->rey_mega_menu && $depth === 0) ) {
			return $args;
		}

		if ( ! reycore__acf_get_field('mega_menu', $menu_item->ID) ) {
			return $args;
		}

		$mega_classes['type'] = '--is-mega';
		$mega_menu_type = reycore__acf_get_field('mega_menu_type', $menu_item->ID);

		if( $mega_menu_type == 'columns' && ($mega_menu_columns = reycore__acf_get_field('mega_menu_columns', $menu_item->ID)) ) {
			$mega_classes[] = '--is-mega-cols';
			$mega_classes[] = '--is-mega--cols-' . $mega_menu_columns;
		}
		else if( $mega_menu_type === 'global_sections' ){
			$mega_classes[] = '--is-mega-gs';
		}

		$mega_classes[] = '--mega-' . reycore__acf_get_field('panel_layout', $menu_item->ID);

		if( $lazy_type = $this->is_lazy( $menu_item->ID ) ){

			$mega_classes[] = '--mega-lazy';

			// overlay delay on mouseover and menu hover
			if( 'yes_pl' !== $lazy_type ){
				$mega_classes[] = '--overlay-delayed';
			}
		}

		$args->mega_classes = $mega_classes;

		$this->load_assets();

		return $args;
	}

	/**
	 * Mega Menu CSS Classes
	 *
	 * @since: 1.0.0
	 */
	public function mega_menu_item_classes( $classes, $item, $args, $depth)
	{
		if( ! isset($args->mega_classes) ){
			return $classes;
		}

		$classes = array_merge($classes, $args->mega_classes);

		// make sure to add it
		// to help global sections
		if( ! in_array('menu-item-has-children', $classes) ) {
			$classes[] = 'menu-item-has-children';
		}

		return $classes;
	}

	public function load_assets(){

		static $_assets_loaded;

		if( ! $_assets_loaded ){

			reycore_assets()->add_styles(['rey-overlay', self::ASSET_HANDLE]);
			reycore_assets()->add_scripts(self::ASSET_HANDLE);

			$_assets_loaded = true;
		}
	}

	/**
	 * Add menu items styles
	 *
	 * @since 1.5.0
	 **/
	public function main_menu_submenus_styles($styles)
	{

		$menu_items = get_posts( [
			'order'                  => 'ASC',
			'orderby'                => 'menu_order',
			'post_type'              => 'nav_menu_item',
			'post_status'            => 'publish',
			'output'                 => ARRAY_A,
			'output_key'             => 'menu_order',
			'nopaging'               => true,
			'update_post_term_cache' => false,
			'fields'                 => 'ids'
			// 'post_parent'         => 0, // doesn't work
			// 'meta_key'               => '_menu_item_menu_item_parent',
			// 'meta_value'             => 0,
		] );

		foreach( $menu_items as $menu_item ){

			$css_styles = [];

			if(
				reycore__acf_get_field('mega_menu_type', $menu_item)  &&
				'custom' === reycore__acf_get_field('panel_layout', $menu_item) &&
				( $panel_width = reycore__acf_get_field('panel_width', $menu_item ) )
			) {
				if( !is_null($panel_width) && $panel_width !== '' && $panel_width !== false ){
					$css_styles[] = sprintf('--ec-max-width:%spx;', absint($panel_width));
				}
			}

			if( reycore__acf_get_field('panel_styles', $menu_item ) ){

				if( $text_color = reycore__acf_get_field('panel_text_color', $menu_item ) ){
					$css_styles[] = sprintf('--link-color:%s;', $text_color);
				}

				if( $panel_bg_color = reycore__acf_get_field('panel_bg_color', $menu_item ) ){
					$css_styles[] = sprintf('--body-bg-color:%s;', $panel_bg_color);
				}

				$panel_padding = reycore__acf_get_field('panel_padding', $menu_item );
				if( !is_null($panel_padding) && $panel_padding !== '' && $panel_padding !== false ){
					$css_styles[] = sprintf('--submenus-padding:%spx;', absint($panel_padding));
					// if( ! absint($panel_padding) ){
					// 	$css_styles[] = '--submenu-box-shadow:none;';
					// }
				}
			}

			if( !empty($css_styles) ){
				$styles[] = sprintf('.rey-mainMenu--desktop .menu-item.menu-item-has-children.menu-item-%1$s {%2$s}',
					$menu_item,
					implode('', $css_styles)
				);
			}
		}

		return $styles;
	}

	/**
	 * Add page settings into Elementor
	 *
	 * @since 2.4.4
	 */
	public function gs_settings( $doc )
	{

		$params = $doc->get_params();
		$params['preview_width'][] = self::GSTYPE;
		$doc->set_params($params);

	}

	/**
	 * Filter Mega Menu link attributes to add sub-pabel's
	 * menu width
	 *
	 * @since: 1.0.0
	 */
	public function mega_menu_link_args( $atts, $item, $args, $depth )
	{
		if(
			isset($args->rey_mega_menu) && $args->rey_mega_menu && $depth == 0 &&
			reycore__acf_get_field('mega_menu', $item->ID) &&
			reycore__acf_get_field('panel_layout', $item->ID) == 'custom'
		) {
			$atts['data-panel-width'] = reycore__acf_get_field('panel_width', $item->ID);
		}

		return $atts;
	}

	public function register_actions( $ajax_manager ){
		$ajax_manager->register_ajax_action( self::AJAX_LAZY_ACTION, [$this, 'ajax__get_megamenu_content'], [
			'auth'      => 3,
			'nonce'     => false,
			'assets'    => true, // in case cache is disabled
			'transient' => [
				'expiration'         => 2 * WEEK_IN_SECONDS,
				'unique_id'          => 'mid',
				'unique_id_sanitize' => 'absint',
			],
		] );
	}


	/**
	 * Retrieve the panel's content via Ajax
	 *
	 * @param array $data
	 * @return void
	 */
	public function ajax__get_megamenu_content( $data ){

		if( ! (isset($data['mid']) && ($id = absint($data['mid']))) ){
			return ['errors'=> esc_html__('Missing Global Section.', 'rey-core')];
		}

		if( ! class_exists('\ReyCore\Elementor\GlobalSections') ){
			return ['errors'=> esc_html__('Elementor is disabled?', 'rey-core')];
		}

		if( reycore__is_multilanguage() ){
			$id = apply_filters('reycore/translate_ids', $id, \ReyCore\Elementor\GlobalSections::POST_TYPE);
		}

		return \ReyCore\Elementor\GlobalSections::do_section( $id );

	}

	public function flush_offcanvas_transient_ids( $post_id, $post ){

		if ( ! isset($post->post_type) ) {
			return;
		}

		if( $post->post_type !== \ReyCore\Elementor\GlobalSections::POST_TYPE ){
			return;
		}

		if( self::GSTYPE !== get_field('gs_type', $post_id) ){
			return;
		}

		do_action('reycore/megamenus/save_delete', $post_id, $this);

		// delete_transient( implode('_', [\ReyCore\Ajax::AJAX_TRANSIENT_NAME, self::AJAX_LAZY_ACTION, $post_id] ) );
	}

	public function regenerate_customizer_css($menu_id){

		if( ! in_array($menu_id, get_option(self::SUPPORTED_MENUS, []), true) ){
			return;
		}

		do_action('rey/customizer/regenerate_css');
	}

	/**
	 * Hide "Header - Navigation" element when editing a Mega menu.
	 * To avoid loopholes.
	 *
	 * @param bool $status
	 * @return bool
	 * @since 2.3.6
	 */
	public function header_nav_element( $status ){

		if( ! class_exists('\ReyCore\Elementor\GlobalSections') ){
			return $status;
		}

		if( get_post_type() !== \ReyCore\Elementor\GlobalSections::POST_TYPE ){
			return $status;
		}

		if( self::GSTYPE !== reycore__acf_get_field('gs_type', get_the_ID(), 'generic') ){
			return $status;
		}

		return false;
	}

	public function is_enabled() {
		return class_exists('\ACF') && class_exists('\Elementor\Plugin');
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Mega Menus in Navigation', 'Module name', 'rey-core'),
			'description' => esc_html_x('Adds support for mega menus in site header navigation.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['elementor'],
			'keywords'    => [],
			'help'        => reycore__support_url('kb/how-to-add-mega-menu-panels/'),
		];
	}

	public function module_in_use(){

		$post_ids = get_posts([
			'post_type' => 'nav_menu_item',
			'numberposts' => -1,
			'post_status' => 'publish',
			'fields' => 'ids',
			'tax_query' => [
				[
					'taxonomy' => 'nav_menu',
					'field'    => 'term_id',
					'terms'    => array_map('absint', array_unique( get_option(self::SUPPORTED_MENUS, []) )),
				],
			],
			'meta_query' => [
				[
					'key' => 'mega_menu',
					'value'   => '1',
					'compare' => 'IN'
				],
			]
		]);

		return ! empty($post_ids);

	}
}
