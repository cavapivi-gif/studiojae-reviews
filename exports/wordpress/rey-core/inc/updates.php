<?php
namespace ReyCore;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Updates plugin class
 */
class Updates {

	/**
	 * Disable new modules
	 *
	 * @param array $new_modules
	 * @return void
	 */
	private static function disable_new_modules( $new_modules = [] ){

		foreach ( (array) $new_modules as $new_module) {
			\ReyCore\Plugin::instance()->modules->modules_manager->change_item_status($new_module, false);
		}

	}

	private static function check_for_widget_type( $type ){
		global $wpdb;

		return $wpdb->get_col(
			'SELECT `post_id` FROM `' . $wpdb->postmeta . '` WHERE `meta_key` = "_elementor_data" AND `meta_value` LIKE \'%"widgetType":"' . $type . '"%\';'
		);
	}

	/**
	 * Updates some legacy options.
	 *
	 * @return void
	 */
	public static function up_2_3_7_legacy_options() {

		set_theme_mod( 'header_nav_overlay', true === get_theme_mod('header_nav_overlay', true) ? 'show' : 'hide' );

		if( $product_short_desc_toggle = get_theme_mod('product_short_desc_toggle') ){
			set_theme_mod( 'product_short_desc_toggle_v2', 'no' !== $product_short_desc_toggle );
		}

		if( ($header_cart_layout = get_theme_mod('header_cart_layout')) && 'text' === $header_cart_layout ){
			set_theme_mod( 'header_cart_layout', 'bag' );
		}
		if( ($header_cart_text = get_theme_mod('header_cart_text')) ){
			if( false === get_theme_mod( 'header_cart_text_v2' ) ){
				set_theme_mod( 'header_cart_text_v2', $header_cart_text );
			}
		}

		if( ($product_sku = get_theme_mod('product_sku')) ){
			if( false === get_theme_mod( 'product_sku_v2' ) ){
				set_theme_mod( 'product_sku_v2', $product_sku == '1' );
			}
		}

		if( ($single_product_meta = get_theme_mod('single_product_meta')) ){
			if( false === get_theme_mod( 'single_product_meta_v2' ) ){
				set_theme_mod( 'single_product_meta_v2', $single_product_meta == 'show' );
			}
		}

		if( ($single_discount_badge = get_theme_mod('single_discount_badge')) ){
			if( false === get_theme_mod( 'single_discount_badge_v2' ) ){
				set_theme_mod( 'single_discount_badge_v2', $single_discount_badge == '1' );
			}
		}

	}

	/**
	 * Updates the size charts integration options.
	 *
	 * @return void
	 */
	public static function up_2_3_7_size_chart_options() {

		// Size charts options
		if( $wapsc_button_position = get_field('wapsc_button_position', REY_CORE_THEME_NAME) ){
			set_theme_mod( 'wapsc_button_position', $wapsc_button_position );
		}
		if( $wapsc_button_style = get_field('wapsc_button_style', REY_CORE_THEME_NAME) ){
			set_theme_mod( 'wapsc_button_style', $wapsc_button_style );
		}

	}

	/**
	 * Migrate swatches selected name option to new type of option.
	 *
	 * @return void
	 */
	public static function up_2_3_7_variations_selected_name() {

		// variations
		set_theme_mod( 'pdp_swatches__selected_name_v2', get_theme_mod('pdp_swatches__selected_name', false) ? 'name' : '' );

	}

	/**
	 * Migrate the old elementor animation option and disable the module.
	 *
	 * @return void
	 */
	public static function up_2_3_7_elementor_animation() {

		$opt_key = 'rey_elementor_animations_enable';

		// the option was not disabled before
		// so no point in disabling the module
		if( get_option($opt_key, null) != 0 ){
			return;
		}

		// disable the module
		\ReyCore\Plugin::instance()->modules->modules_manager->change_item_status('elementor-animations', false);

		delete_option($opt_key); // delete the old DB

	}

	/**
	 * Disable new modules that have been added in this latest release
	 *
	 * @return void
	 */
	public static function up_2_3_7_disable_new_modules() {

		// disable newly added modules
		self::disable_new_modules([
			'product-subtitle',
			'pdp-benefits',
			'pdp-recently-viewed',
		]);

	}

	/**
	 * Content padding control values, migrate to Container Padding.
	 *
	 * @return void
	 */
	public static function up_2_3_7_content_padding_to_container_spacing() {

		// Migrate content padding to container padding
		if( $content_padding = get_theme_mod('content_padding') ){
			$default = 15;
			if( isset($content_padding['padding-left']) && ($left_content_padding = $content_padding['padding-left']) ){
				set_theme_mod( 'container_spacing', (absint($left_content_padding) + $default) );
				set_theme_mod( 'container_spacing_tablet', $default );
				set_theme_mod( 'container_spacing_mobile', $default );

				unset($content_padding['padding-left']);
				unset($content_padding['padding-right']);
				set_theme_mod('content_padding', $content_padding);

			}
			elseif( isset($content_padding['padding-right']) && ($right_content_padding = $content_padding['padding-right']) ){
				set_theme_mod( 'container_spacing', (absint($right_content_padding) + $default) );
				set_theme_mod( 'container_spacing_tablet', $default );
				set_theme_mod( 'container_spacing_mobile', $default );

				unset($content_padding['padding-left']);
				unset($content_padding['padding-right']);
				set_theme_mod('content_padding', $content_padding);
			}
		}

	}

	/**
	 * Miograte old Elementor Grid option to Customizer one.
	 *
	 * @return void
	 */
	public static function up_2_3_7_el_grid_option() {

		// Elementor grid old DB option
		set_theme_mod( 'elementor_grid', get_option('rey_elementor_grid', 'rey') );
	}

	/**
	 * Migrate the Loop gap size controls to new responsive one.
	 *
	 * @return void
	 */
	public static function up_2_3_7_loop_gap_size() {

		// Loop Gap
		$presets_gap_map = [
			'no' => 0,
			'line' => 2,
			'narrow' => 10,
			'default' => 30,
			'extended' => 50,
			'wide' => 70,
			'wider' => 100,
		];

		// upgrades the product loop
		set_theme_mod( 'loop_gap_size_v2', $presets_gap_map[ get_theme_mod('loop_gap_size', 'default') ] );

		/**
		 * Elementor Product Grid element, gaps tp gaps_v2
		 */

		$post_ids = self::check_for_widget_type('reycore-product-grid');

		if ( empty( $post_ids ) ) {
			return;
		}

		foreach ( $post_ids as $post_id ) {

			// don't do it for revisions
			if( wp_is_post_revision($post_id) ){
				continue;
			}

			$do_update = false;
			$document = \Elementor\Plugin::$instance->documents->get( $post_id );

			if ( $document ) {
				$data = $document->get_elements_data();
			}

			if ( empty( $data ) ) {
				continue;
			}

			$data = \Elementor\Plugin::$instance->db->iterate_data( $data, function( $element ) use ( &$do_update, $presets_gap_map ) {

				if ( empty( $element['widgetType'] ) || 'reycore-product-grid' !== $element['widgetType'] ) {
					return $element;
				}

				if ( isset($element['settings']['gaps']) && ! empty($element['settings']['gaps']) ) {
					if ( ! empty( $element['settings'][ 'gaps' ] ) ) {
						$element['settings'][ 'gaps_v2' ] = $presets_gap_map[$element['settings'][ 'gaps' ]];
						$do_update = true;
					}
				}

				return $element;
			} );

			// Only update if needed.
			if ( ! $do_update ) {
				continue;
			}

			// We need the `wp_slash` in order to avoid the unslashing during the `update_post_meta`
			$json_value = wp_slash( wp_json_encode( $data ) );

			update_metadata( 'post', $post_id, '_elementor_data', $json_value );

			// Clear WP cache for next step.
			wp_cache_flush();

		} // End foreach().

	}

	public static function up_2_3_7_selects_to_toggles() {

		$map = [
			'1' => true, // enable
			'2' => false, // disable
			'show' => true, // enable
			'hide' => false, // disable
		];

		$controls = [
			'loop_hover_animation' => '1',
			'wrapped_loop_hover_animation' => '1',
			// 'loop_show_categories' => '2',
			// 'loop_ratings' => '2',
			// 'loop_short_desc' => '2',
			// 'loop_new_badge' => '1',
			// 'loop_featured_badge' => 'hide',
		];

		foreach ($controls as $control_name => $control_default) {
			set_theme_mod($control_name, $map[ get_theme_mod($control_name, $control_default) ]);
		}

	}

	public static function up_2_3_7_hoverbox_default_to_js() {

		$el = 'reycore-hoverbox-distortion';

		$post_ids = self::check_for_widget_type($el);

		if ( empty( $post_ids ) ) {
			return;
		}

		foreach ( $post_ids as $post_id ) {

			// don't do it for revisions
			if( wp_is_post_revision($post_id) ){
				continue;
			}

			$do_update = false;
			$document = \Elementor\Plugin::$instance->documents->get( $post_id );

			if ( $document ) {
				$data = $document->get_elements_data();
			}

			if ( empty( $data ) ) {
				continue;
			}

			$data = \Elementor\Plugin::$instance->db->iterate_data( $data, function( $element ) use ( &$do_update, $el ) {

				if ( empty( $element['widgetType'] ) || $el !== $element['widgetType'] ) {
					return $element;
				}

				$element['settings']['transition_group'] = 'js'; // force set JS
				$do_update = true;

				return $element;
			} );

			// Only update if needed.
			if ( ! $do_update ) {
				continue;
			}

			// We need the `wp_slash` in order to avoid the unslashing during the `update_post_meta`
			$json_value = wp_slash( wp_json_encode( $data ) );

			update_metadata( 'post', $post_id, '_elementor_data', $json_value );

			// Clear WP cache for next step.
			wp_cache_flush();

		} // End foreach().

	}

	public static function up_2_3_9_force_assets_flush() {
		do_action( 'rey/flush_cache_after_updates' );
	}

	public static function up_2_4_6_titles_equalize() {
		set_theme_mod('product_titles_height', get_theme_mod('product_items_eq', false) ? 'eq' : '' );
		set_theme_mod('logo_sizes', get_theme_mod('my_setting', []) );
	}

	public static function up_2_5_4_font_migration_customizer() {
		\ReyCore\Migrations::font_migration__optimisation_control();
		\ReyCore\Migrations::font_migration__customizer();
	}

	public static function up_2_5_4_font_migration_elementor() {
		\ReyCore\Migrations::font_migration__elementor();
		\ReyCore\Migrations::font_migration__elementor_page_settings();
		\ReyCore\Migrations::font_migration__revolution();
	}

	public static function up_2_6_2_stock_options() {

		set_theme_mod( 'product_page__stock_display', false === get_theme_mod('product_page__hide_stock', false) ? 'show' : 'hide' );

		$loop_stock_map = [
			'1'        => 'badge_so',
			'in-stock' => 'badge_is',
			'2'        => 'hide',
		];

		set_theme_mod( 'loop_stock_display', $loop_stock_map[ get_theme_mod('loop_sold_out_badge', '1') ] );

	}

	public static function up_2_6_2_review_link_controls() {

		if( false === get_theme_mod('single_product_reviews_after_meta', true) ){
			set_theme_mod( 'pdp_rating_link_display', 'show' );
		}

	}

	public static function up_2_6_2_migrate_custom_tabs() {
		\ReyCore\Migrations::tabs_migration();
	}

	public static function up_2_6_2_disable_autoloaded_options() {

		$keys = [
			'rey_demo_map_data',
			'reycore_demos',
			'rey_swatches_data',
			'rey_library_data',
			'rey_library_installed',
			'rey_filters_custom_keys_map',
			'rey_mega_menus_supported_menus',
			'rey_plugins_list',
			// 'rey_templates_data', // used in all pages
			'widget_reyajfilter-active-filters',
			'widget_reyajfilter-attribute-filter',
			'widget_reyajfilter-category-filter',
			'widget_reyajfilter-featured-filter',
			'widget_reyajfilter-price-filter',
			'widget_reyajfilter-sale-filter',
			'widget_reyajfilter-search-filter',
			'widget_reyajfilter-stock-filter',
			'widget_reyajfilter-tag-filter',
			'widget_reyajfilter-taxonomy-filter',
			'widget_reyajfilter-custom-fields-filter',
			'widget_reyajfilter-auto-custom-fields-filter',
		];

		global $wpdb;

		foreach ($keys as $key) {
			$wpdb->update(
				$wpdb->options,
				['autoload' => 'no'],
				['option_name' => $key]
			);
		}

	}

	public static function up_2_7_3_migrate_cards_gap_carousel() {
		\ReyCore\Migrations::cards_gap_migration();
	}

	// public static function up_2_4_0_() {}

}
