<?php
namespace ReyCore\Modules\VariationSwatches;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Migration
{
	const OPTION = '__wvs_migration';

	private $types = [];

	private $data = [];

	public function __construct(){
		add_action( 'init', [$this, 'init']);
	}

	function init(){

		if( ! is_admin() ){
			return;
		}

		if( ! current_user_can( 'administrator' ) ){
			return;
		}

		add_action( 'wp_ajax_reycore_variation_swatches_wvs_migration', [$this, 'ajax_wvs_migration']);
		// add_action( 'reycore/customizer/control=single_product_hide_out_of_stock_variation', [ $this, 'customizer__show_migration_control' ], 10, 2 );
		// add_action( 'reycore/customizer/control=woocommerce_loop_variation', [ $this, 'customizer__show_migration_control' ], 10, 2 );
		add_action( 'reycore/customizer/section=woo-product-page-summary-components/marker=swatches_after_settings', [$this, 'customizer__show_migration_control']);
		add_action( 'customize_controls_print_scripts', [$this, 'customizer__migration_scripts'] );

		if( ! Base::$settings['always_show_migration'] ){

			if( ! self::can_show_migration() ){
				return;
			}

			if( self::has_migrated() ){
				return;
			}

			// check if can migrate WVS data
			if( ! self::can_migrate_wvs() ){
				self::set_migrated();
				return;
			}

		}

		// add_action( 'admin_enqueue_scripts', [$this, 'enqueue_scripts']);
		// add_action( 'admin_notices', [$this, 'show_migrate_banner'] );
	}

	public static function can_show_migration(){
		if( isset($_REQUEST['post_type']) && $_REQUEST['post_type'] === 'product' ){
			if(
				(isset($_REQUEST['taxonomy']) && $_REQUEST['taxonomy'] !== '' && ! in_array(reycore__clean($_REQUEST['taxonomy']), ['product_cat', 'product_tag'], true)) ||
				(isset($_REQUEST['page']) && $_REQUEST['page'] === 'product_attributes')
			 ){
				return true;
			}
		}
		return false;
	}

	public static function has_migrated(){
		return get_option( Base::OPT . self::OPTION, false);
	}

	public static function set_migrated( $clear_cache = true ){

		// Clear cache and flush rewrite rules.
		if( $clear_cache ){
			delete_transient( 'wc_attribute_taxonomies' );
			\WC_Cache_Helper::invalidate_cache_group( 'woocommerce-attributes' );
		}

		return update_option( Base::OPT . self::OPTION, true, false);
	}

	/**
	 * Checks if WVS had saved options.
	 *
	 * @return boolean
	 */
	public static function can_migrate_wvs(){

		$wvs_main_opt = get_option( 'woo_variation_swatches' );

		// Sometimes the option is empty.
		if( $wvs_main_opt === false ){
			return true;
		}

		return ! empty( $wvs_main_opt);
	}

	public function show_migrate_banner(){
		?>
		<div class="reyAdm-notice reyAdm-notice--wvs-migrate notice" data-key="wvs-migrate">
			<button type="button" class="reyAdm-noticeDismiss notice-dismiss" data-dismiss="1day"><span class="screen-reader-text"><?php esc_html_e('Dismiss this notice.', 'rey-core') ?></span></button>
			<div class="__inner">
				<div class="__logo">
					<?php echo reycore__get_svg_icon(['id'=>'logo']) ?>
				</div>
				<div class="__content">
					<h3><?php esc_html_e('Migrate "WooCommerce Variation Swatches" attribute types to Rey\'s?', 'rey-core') ?></h3>
					<p><?php esc_html_e('It appears the "WooCommerce Variation Swatches" plugin was previously installed. Do you want to migrate the Product Attributes values to Rey\'s attribute swatches types?', 'rey-core') ?></p>
					<p>
						<button class="js-wvs-migrate-btn button button-primary"><?php esc_html_e('Yes, migrate', 'rey-core') ?></button>
					</p>
				</div>
			</div>
		</div>
		<?php
	}

	static function wvs_map(){
		return [
			Base::TYPES_PREFIX . 'color' => [
				'product_attribute_color'       => 'rey_attribute_color',
				'product_attribute_color_2'     => 'rey_attribute_color_secondary',
				'product_attribute_color_2_img' => 'rey_attribute_image',
			],
			Base::TYPES_PREFIX . 'image' => [
				'product_attribute_image' => 'rey_attribute_image',
			],
		];
	}

	function ajax_wvs_migration(){

		if ( ! check_ajax_referer( 'reycore-ajax-verification', 'security', false ) ) {
			wp_send_json( ['error' => 'Invalid security nonce!'] );
		}

		$data_maps = self::wvs_map();

		$attributes_to_migrate = $data_to_migrate = [];

		$status = [
			'migrated' => false,
			'taxonomies' => []
		];

		foreach (wc_get_attribute_taxonomies() as $key => $attribute) {

			if( ! (isset($attribute->attribute_type) && $attribute->attribute_type !== 'select') ){
				continue;
			}

			$type = $attribute->attribute_type;

			if( strpos($type, Base::TYPES_PREFIX) === false ){
				$type = Base::TYPES_PREFIX . $type;
			}

			if( ! Base::instance()->swatch_exists( $type ) ){
				continue;
			}

			$attribute->attribute_type = $type;

			$attributes_to_migrate[] = $attribute;
		}

		// no attributes to migrate, so bail
		// and update option that migration is not needed anymore
		if( empty($attributes_to_migrate) ){
			$status['migrated'] = self::set_migrated();
			wp_send_json($status);
		}

		foreach ($attributes_to_migrate as $attribute) {

			$taxonomy = wc_attribute_taxonomy_name($attribute->attribute_name);

			// process type update
			$update_attribute_type = wc_update_attribute($attribute->attribute_id, [
				'id'           => $attribute->attribute_id,
				'name'         => $attribute->attribute_name,
				'slug'         => '',
				'order_by'     => $attribute->attribute_orderby,
				'type'         => $attribute->attribute_type,
				'has_archives' => (bool) $attribute->attribute_public,
			]);

			if( is_wp_error($update_attribute_type) ){
				continue;
			}

			if( ! ( isset($data_maps[ $attribute->attribute_type ]) && $data_map = $data_maps[ $attribute->attribute_type ] ) ) {
				continue;
			}

			$terms_to_migrate = [];

			$terms = get_terms([
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
			]);

			foreach ($terms as $term) {

				$term_meta = get_term_meta( $term->term_id );

				foreach ($data_map as $term_key => $new_term_key) {

					if( ! ( isset($term_meta[ $term_key ]) && $field_data = $term_meta[ $term_key ][0] ) ){
						continue;
					}

					$update = update_field( $new_term_key, $field_data, $term );

					if( $update ){
						$terms_to_migrate[$term->term_id][$new_term_key] = $field_data;
					}

				}
			}

			$data_to_migrate[ $taxonomy ] = $terms_to_migrate;

			if( ! empty($terms_to_migrate) ){
				delete_transient( Base::get_attribute_transient_name( $taxonomy ) );
			}

		}

		$status['migrated'] = self::set_migrated();
		$status['taxonomies'] = $data_to_migrate;

		wp_send_json($status);
	}

	public function enqueue_scripts(){

		\wp_enqueue_style(
			Base::ASSET_HANDLE . '-admin',
			Base::get_path( basename( __DIR__ ) ) . '/admin-style.css',
			[],
			REY_CORE_VERSION
		);

		\wp_enqueue_script(
			Base::ASSET_HANDLE . '-admin',
			Base::get_path( basename( __DIR__ ) ) . '/admin-script.js',
			['jquery'],
			REY_CORE_VERSION,
			true
		);

	}

	function customizer__show_migration_control($section){

		if( ! Base::instance()->is_enabled() ){
			return;
		}

		if( ! current_user_can( 'administrator' ) ){
			return;
		}

		if( self::has_migrated() ){
			return;
		}

		// If can't migrate, force set migration.
		if( ! self::can_migrate_wvs() ){
			self::set_migrated();
			return;
		}

		$section->add_control( [
			'type'        => 'rey-button',
			'settings'    => 'wvs_swatches_migration_button',
			'label'       => __('Migrate WVS. data', 'rey-core'),
			'help' => [
				__('Migrate swatches data from WooCommerce Variation Swatches plugin.', 'rey-core')
			],
			'default'     => '',
			'choices'     => [
				'text'   => esc_html__('Yes, migrate', 'rey-core'),
				'action' => 'reycore_variation_swatches_wvs_migration',
			],
		] );

	}

	function customizer__migration_scripts(){


		if( self::has_migrated() ){
			return;
		}

		$this->enqueue_scripts();
	}

}
