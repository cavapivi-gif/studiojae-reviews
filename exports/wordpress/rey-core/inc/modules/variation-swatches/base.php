<?php
namespace ReyCore\Modules\VariationSwatches;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	public static $settings = [];

	const ASSET_HANDLE = 'reycore-variation-swatches';

	const OPT = 'rey_swatches_data';

	const FIELDS_PREFIX = 'rey__';

	const TRANSIENT__ATTRIBUTE_SWATCHES_SETTINGS = 'rey_attribute_swatches_settings_';

	const TYPES_PREFIX = 'rey_';

	public $swatches = [];

	public function __construct()
	{
		add_action( 'reycore/woocommerce/init', [$this, 'init']);

		new Migration();
		new Impex();

	}

	public function is_enabled(){

		$enabled = class_exists('\WooCommerce');

		// Prevent conflicts between Rey's Variation Swatches module
		// and other Variation Swatches plugins
		if(
			class_exists('Woo_Variation_Swatches') // Variation Swatches for WooCommerce (by Emran Ahmed)
			|| class_exists('Woo_Variation_Swatches_Pro') // Variation Swatches for WooCommerce PRO (by Emran Ahmed)
			|| class_exists('Iconic_Woo_Attribute_Swatches') // WooCommerce Attribute Swatches by Iconic
			|| class_exists('VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES') // WooCommerce Product Variations Swatches Premium (by VillaTheme)
			|| class_exists('WC_SwatchesPlugin') // WooCommerce Variation Swatches and Photos (by Element Stark)
			|| class_exists('X_for_WooCommerce') // XforWooCommerce
		){
			$enabled = false;
		}

		return apply_filters('reycore/variation_swatches/enabled', $enabled );
	}

	function init(){

		self::$settings = apply_filters('reycore/variation_swatches/settings', [
			'tooltip_theme'                            => 'light',
			'tooltip_image_size'                       => 'thumbnail',
			'variations_use_main_image'                => false, // variations use the main image
			'variations_image_size'                    => 'thumb_src', // variations image size (full_src, gallery_thumbnail_src, thumb_src)
			'optimized_assets_load'                    => true, // loads the assets only on demand
			'show_all_labels'                          => false, //
			'always_show_migration'                    => false, // always show the migration banner
			'attr_data_cache'                          => false, // store attribute data in transient
			'loop_cache'                               => ! \ReyCore\Plugin::is_dev_mode(), // cache the variations in catalog
			'show_empty_catalog_div'                   => false, // shows the variations block, regardless if empty or not
			'update_catalog_price_if_single_attribute' => false, // Updates the price when a single Attribute type is chosen
			'update_catalog_stock_if_single_attribute' => true, // Updates the stock badges when a single Attribute type is chosen
			'fallback_image_text'                      => true, // when there's no image, fallback to text.
			'image_uses_tag'                           => false, // Change to true, tp make the image swatches use image tags to keep aspect ratio
			'catalog_show_single'                      => false, // Show single swatch in catalog
			'autoselect_single_variation'              => true, // Auto select single variation
			'autoselect_catalog_variation'             => true, // Auto select variations in catalog
			'update_product_page_url'                  => false, // Update product page url on variation change
		] );

		if( ! (self::$settings['enabled'] = $this->is_enabled()) ){
			return;
		}

		$this->register_default_types();

		do_action('reycore/variation_swatches/init', $this);

		new Frontend();
		new Admin();
		new Compatibility();

	}

	function register_default_types(){
		$this->register_swatch_type( new SwatchButton );
		$this->register_swatch_type( new SwatchColor );
		$this->register_swatch_type( new SwatchImage );
		$this->register_swatch_type( new SwatchLargeButton );
		$this->register_swatch_type( new SwatchRadio );
	}

	function register_swatch_type( $swatch_class ){
		if( $swatch_id = $swatch_class->get_id() ){
			$this->swatches[ $swatch_id ] = $swatch_class;
		}
	}

	public function swatch_exists( $type ){
		$types = $this->get_swatches_list();
		return isset( $types[ $type ] );
	}

	public function get_swatches_list(){

		$swatches = [];

		foreach ($this->swatches as $id => $swatch) {
			$swatches[ $id ] = $swatch->get_name();
		}

		return $swatches;
	}

	public function get_swatches( $swatch_id = '' ){

		if( $swatch_id && isset( $this->swatches[ $swatch_id ] ) ){
			return $this->swatches[ $swatch_id ];
		}

		return $this->swatches;
	}

	public function get_default_swatch(){

		$default_type = '';

		foreach ($this->swatches as $id => $swatch) {
			if( $swatch->is_default ){
				$default_type = $id;
			}
		}

		return $default_type;
	}

	static function get_attributes_swatch_settings( $tax = '' ){

		$opt = get_option(self::OPT, []);

		if( $tax ){
			if( isset($opt[ $tax ]) ){
				return $opt[ $tax ];
			}
			return [];
		}

		return $opt;
	}

	static function set_attributes_swatch_settings($data){
		return update_option(self::OPT, $data, false);
	}

	static function is_single_product(){

		if( reycore_wc__is_product() ){
			return ! in_array( wc_get_loop_prop('name'), ['upsells', 'up-sells', 'crosssells', 'cross-sells', 'related'] );
		}

		return false;
	}

	public static function get_term($term, $taxonomy = null){

		if ( empty( $term ) ) {
			return;
		}

		if ( $term instanceof \WP_Term ) {
			$_term = $term;
		} elseif ( is_object( $term ) && isset($term->term_id) ) {
			$_term = \WP_Term::get_instance( $term->term_id );
		} else {
			if( ! $_term = \WP_Term::get_instance( $term, $taxonomy ) ){
				$_term = get_terms( ['include' => (array) $term] );
			}
		}

		if( is_wp_error($_term) ){
			return;
		}

		return $_term;
	}

	public static function get_attribute_transient_name( $tax ){

		$suffix = [
			$tax
		];

		if( $lang = reycore__is_multilanguage() ){
			$suffix[] = $lang;
		}

		return self::TRANSIENT__ATTRIBUTE_SWATCHES_SETTINGS . implode('_', $suffix);
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Variation Swatches', 'Module name', 'rey-core'),
			'description' => esc_html_x('Extend variation lists with buttons, images, colors lists that enriches the shopping experience.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => [''],
			'help'        => reycore__support_url('kb/how-to-create-variations/'),
			'video' => true,
		];
	}

	public function module_in_use(){
		return $this->is_enabled();
	}
}
