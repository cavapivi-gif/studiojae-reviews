<?php
namespace ReyCore;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Migrations {

	public function __construct(){

		add_action( 'reycore/ajax/register_actions', [ $this, 'register_actions' ] );
		add_action( 'rey/dashboard/box/versions', [$this, 'render_migrations_list'], 50);
	}

	public function register_actions( $ajax_manager ){
		$ajax_manager->register_ajax_action( 'migrations', [$this, 'ajax__migrations'], [
			'auth' => 1,
			'capability' => 'administrator',
		] );
	}

	public function render_migrations_list(){

		if( ! (\ReyCore\Plugin::is_dev_mode() || isset($_REQUEST['debug']) && 1 === absint($_REQUEST['debug'])) ){
			return;
		} ?>

		<tr class="__border">
			<td colspan="2"><hr></td>
		</tr>

		<tr class="__migrations-scripts" id="migrations-scripts">
			<td colspan="2">
				<label class="__rb-title">Select migration script to run:</label>
				<p class="__row">
					<select name="migration_type">
						<option value="">- Select migration -</option>
						<option value="fonts">Font migrations</option>
						<option value="tabs">Custom Tabs migrations</option>
						<option value="cardsgap">Cards Skin with Carousel Gap Fix migrations</option>
					</select>
					<button class="rey-adminBtn rey-adminBtn-secondary --sm-padding --disabled">Run script</button>
				</p>
				<p class="js-dashResponse __response"></p>
			</td>
		</tr>

		<?php
	}

	public function ajax__migrations( $action_data ){

		if( ! isset($action_data['script']) ){
			return ['error' => 'Missing script!'];
		}

		if( 'fonts' === $action_data['script'] ){
			self::font_migration__optimisation_control();
			self::font_migration__customizer();
			self::font_migration__revolution();
			self::font_migration__elementor();
			self::font_migration__elementor_page_settings();
			return 'Done!';
		}

		if( 'tabs' === $action_data['script'] ){
			self::tabs_migration();
			return 'Done!';
		}

		if( 'cardsgap' === $action_data['script'] ){
			self::cards_gap_migration();
			return 'Done!';
		}

	}

	public static function get_legacy_preloaded_fonts(){

		$data = [
			'families' => [],
			'families_sql' => [],
		];

		for ( $i=0; $i < 4; $i++ ) {

			if( $opt = get_option("rey_preload_google_fonts_{$i}_font_name") ){

				$new_name = $opt;
				$data['families_sql'][] = ' (`meta_key` = "_elementor_data" AND `meta_value` LIKE \'%_font_family":"' . esc_sql($opt) . '__"%\') ';
				$data['families_sql_ele_page_settings'][] = ' (`meta_key` = "_elementor_page_settings" AND `meta_value` LIKE \'%:"' . esc_sql($opt) . '__"%\') ';
				$data['families_sql_rev'][] = ' `layers` LIKE \'%"fontFamily":"' . esc_sql($opt) . '__"%\' ';

				// set primary/secondary font name
				foreach (Webfonts::get_typography_vars() as $key => $vars ) {
					if( ! empty($vars['font-family']) ){
						if( $opt === $vars['font-family'] ){
							$new_name = $vars['nice-name'];
							break;
						}
					}
				}

				$data['families'][ $opt . '__'] = esc_attr($new_name);
			}

		}

		return $data;
	}

	public static function font_migration__customizer() {

		$theme_slug     = get_option( 'stylesheet' );
		$mods           = get_option( "theme_mods_$theme_slug" );
		$regenerate_css = false;

		foreach ($mods as $key => $value) {
			if( isset($value['font-family']) && strpos($value['font-family'], '__') !== false ){
				$value['font-family'] = str_replace('__', '', $value['font-family']);
				set_theme_mod($key, $value);
				$regenerate_css = true;
			}
		}

		if( $regenerate_css ){
			do_action( 'rey/customizer/regenerate_css');
		}

	}

	public static function font_migration__revolution() {

		if( ! class_exists('\RevSliderFront') ){
			return;
		}

		$f_data = self::get_legacy_preloaded_fonts();

		if( empty($f_data['families_sql_rev']) ){
			return;
		}

		global $wpdb;

		$layers = $wpdb->get_results( 'SELECT * FROM `' . $wpdb->prefix . \RevSliderFront::TABLE_SLIDES . '` WHERE ( ' . implode(' OR ', $f_data['families_sql_rev']) . ' ) ;' );

		if( ! $layers ){
			return;
		}

		foreach ( (array) $layers as $layer) {

			if( ! isset($layer->layers) ){
				continue;
			}

			// replace fonts
			$value = str_replace(array_keys($f_data['families']), $f_data['families'], $layer->layers);

			// update the table
			$wpdb->update( $wpdb->prefix . \RevSliderFront::TABLE_SLIDES, ['layers' => $value], ['id' => $layer->id] );

		}

	}

	public static function font_migration__optimisation_control() {

		$variants = $subsets = [];

		for ($i=0; $i < 4; $i++) {
			if( $variant = (array) get_option("rey_preload_google_fonts_{$i}_font_variants", []) ){
				$variants = array_merge($variants, $variant);
			}
			if( $subset = (array) get_option("rey_preload_google_fonts_{$i}_font_subsets", []) ){
				$subsets = array_merge($subsets, $subset);
			}
		}

		if( ! empty($variants) ){
			$variants = array_filter(array_unique($variants));
			\ReyCore\ACF\Helper::update_group_sub_field('font_optimisations', 'weights', $variants, REY_CORE_THEME_NAME);
		}

		if( ! empty($subsets) ){
			$subsets  = array_filter(array_unique($subsets));
			\ReyCore\ACF\Helper::update_group_sub_field('font_optimisations', 'subsets', $subsets, REY_CORE_THEME_NAME);
		}

	}

	public static function font_migration__elementor() {

		$f_data = self::get_legacy_preloaded_fonts();

		if( empty($f_data['families_sql']) ){
			return;
		}

		global $wpdb;

		$post_ids = $wpdb->get_col( 'SELECT `post_id` FROM `' . $wpdb->postmeta . '` WHERE ( ' . implode(' OR ', $f_data['families_sql']) . ' ) ;' );

		if ( empty( $post_ids ) ) {
			return;
		}

		foreach ( $post_ids as $post_id ) {

			// don't do it for revisions
			if( wp_is_post_revision($post_id) ){
				continue;
			}

			$document = \Elementor\Plugin::$instance->documents->get( $post_id );

			if ( ! $document ) {
				continue;
			}

			$editor_data = $document->get_json_meta( '_elementor_data' );

			if ( empty( $editor_data ) ) {
				continue;
			}

			// encode the editor data
			$json_value = wp_json_encode( $editor_data );

			// replace fonts
			$json_value = str_replace(array_keys($f_data['families']), $f_data['families'], $json_value);

			// We need the `wp_slash` in order to avoid the unslashing during the `update_post_meta`
			$json_value = wp_slash( $json_value );

			// update the data
			$document->update_meta( '_elementor_data', $json_value );

			// Clear WP cache for next step.
			wp_cache_flush();

		} // End foreach().

		\Elementor\Plugin::$instance->files_manager->clear_cache();

	}

	public static function replace_value(&$array, $search_value, $replace_value) {
		foreach ($array as $key => &$value) {
			if (is_array($value)) {
				// Recursively search in the nested array
				self::replace_value($value, $search_value, $replace_value);
			} else {
				if ($value === $search_value) {
					$value = $replace_value;
				}
			}
		}
	}

	public static function font_migration__elementor_page_settings() {

		$f_data = self::get_legacy_preloaded_fonts();

		if( empty($f_data['families_sql_ele_page_settings']) ){
			return;
		}

		global $wpdb;

		$post_ids = $wpdb->get_col( 'SELECT `post_id` FROM `' . $wpdb->postmeta . '` WHERE ( ' . implode(' OR ', $f_data['families_sql_ele_page_settings']) . ' ) ;' );

		if ( empty( $post_ids ) ) {
			return;
		}

		foreach ( $post_ids as $post_id ) {

			// don't do it for revisions
			if( wp_is_post_revision($post_id) ){
				continue;
			}

			$document = \Elementor\Plugin::$instance->documents->get( $post_id );

			if ( ! $document ) {
				continue;
			}

			$editor_data = $document->get_json_meta( '_elementor_page_settings' );

			if ( empty( $editor_data ) ) {
				continue;
			}

			// encode the editor data
			$value = wp_json_encode( $editor_data );

			// replace fonts
			$value = str_replace(array_keys($f_data['families']), $f_data['families'], $value);

			// We need the `wp_slash` in order to avoid the unslashing during the `update_post_meta`
			$value = wp_slash( json_decode($value, true) );

			// update the data
			update_metadata( 'post', $post_id, '_elementor_page_settings', $value );

			// Clear WP cache for next step.
			wp_cache_flush();

		} // End foreach().

		\Elementor\Plugin::$instance->files_manager->clear_cache();

	}

	public static function tabs_migration(){

		$_global_tabs = (array) get_theme_mod('single__custom_tabs', []);

		if( empty($_global_tabs) ){
			return;
		}

		$global_tabs = [];

		foreach ($_global_tabs as $key => $value) {
			$new_gt = $value;
			if( empty($value['uid']) ){
				$new_gt['uid'] = 't-' . substr(uniqid(), 2);
			}
			$global_tabs[] = $new_gt;
		}

		set_theme_mod('single__custom_tabs', $global_tabs);

		$post_ids = get_posts([
			'post_type'   => 'product',
			'numberposts' => -1,
			'post_status' => 'publish',
			'fields'      => 'ids',
			'meta_query'  => [
				'relation' => 'OR',
				[
					'key'     => 'product_custom_tabs_0_tab_content',
					'compare' => 'EXISTS' // check if the meta key exists
				],
				[
					'key'     => 'product_custom_tabs_0_tab_title',
					'compare' => 'EXISTS' // check if the meta key exists
				]
			]
		]);

		if ( empty( $post_ids ) ) {
			return;
		}

		foreach ( $post_ids as $post_id ) {

			if( ! ($old_field = (array) get_field('product_custom_tabs', $post_id, false)) ){
				continue;
			}

			$new_field = [];

			// because the $old_field is unformatted, let's pull the field names (not keys)
			$_fields_map = [
				'field_615b37e5b5408' => 'tab_type',
				'field_5ecae9c356e6e' => 'tab_title',
				'field_5ecae9ef56e6f' => 'tab_content',
				'field_649453268d134' => 'custom_tab_priority',
				'field_649453638d135' => 'custom_add_into_accordion',
				'field_649453c98d136' => 'tab_disable',
			];

			foreach ($old_field as $key => $value) {

				$new_field[$key] = [];

				// if the old field is unformatted and has the field name as key,
				// then remap with field names (instead of field keys)
				if( isset($value['field_615b37e5b5408']) ){
					foreach ($_fields_map as $field_key => $field_name) {
						$new_field[$key][$field_name] = $value[$field_key];
					}
				}
				// if formatted, just set the new field data
				else {
					$new_field[$key] = $value;
				}

				// make sure to use the global UID's
				if( isset($global_tabs[$key]['uid']) ){
					$new_field[$key]['tab_type'] = $global_tabs[$key]['uid'];
				}

				// check for the legacy override title option
				$legacy_override_title = get_post_meta($post_id, "product_custom_tabs_{$key}_override_title", true );

				// if it was enabled to Override the title
				// just remove the title
				if( $legacy_override_title ){
					delete_post_meta($post_id, "product_custom_tabs_{$key}_override_title", $legacy_override_title );
				}

				else if( '0' === $legacy_override_title ){
					$new_field[$key]['tab_title'] = '';
					delete_post_meta($post_id, "product_custom_tabs_{$key}_override_title", $legacy_override_title );
				}

			}

			if( $new_field ){
				// update_field('product_custom_tabs', $new_field, $post_id);
			}

		}

	}


	public static function cards_gap_migration(){

		if( 'cards' !== get_theme_mod('loop_skin', 'basic') ){
			return;
		}

		global $wpdb;

		$post_ids = $wpdb->get_col(
			'SELECT `post_id` FROM `' . $wpdb->postmeta . '` WHERE `meta_key` = "_elementor_data" AND `meta_value` LIKE \'%"widgetType":"reycore-product-grid"%\';'
		);

		if ( empty( $post_ids ) ) {
			return;
		}

		foreach ( $post_ids as $post_id ) {

			$do_update = false;
			$document = \Elementor\Plugin::$instance->documents->get( $post_id );

			if ( $document ) {
				$data = $document->get_elements_data();
			}

			if ( empty( $data ) ) {
				continue;
			}

			$data = \Elementor\Plugin::$instance->db->iterate_data( $data, function( $element ) use ( &$do_update ) {

				if ( empty( $element['widgetType'] ) || 'reycore-product-grid' !== $element['widgetType'] ) {
					return $element;
				}

				foreach ([
					'', '_tablet', '_mobile'
				] as $device) {
					if ( isset($element['settings']['gap' . $device]) && 0 === $element['settings']['gap' . $device] ) {
						$element['settings']['gap' . $device] = ($gap = get_theme_mod('loop_gap_size_v2' . $device)) ? $gap : 20;
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

		\Elementor\Plugin::$instance->files_manager->clear_cache();

	}

}
