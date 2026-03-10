<?php
namespace ReyCore\Modules\ExtraVariationImages;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Frontend {

	private $base;
	private $settings;

	const AJAX_LAZY_ACTION = 'get_extra_variation_images';

	private $_variation_image_ids = [];

	public function __construct( $base, $settings )
	{

		$this->base = $base;
		$this->settings = $settings;

		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
		add_action( 'reycore/ajax/register_actions', [ $this, 'register_actions' ] );
		add_filter( 'rey/main_script_params', [$this, 'add_script_params'], 10 );
		add_filter( 'woocommerce_available_variation', [$this, 'filter_available_variations'], 10, 3);
		add_action( 'woocommerce_update_product', [$this, 'refresh_transient'], 10, 2);
		add_action( 'woocommerce_delete_product_transients', [$this, 'refresh_transient']);
		add_filter( 'stop_gwp_live_feed', '__return_true' );
		add_action( 'woocommerce_before_variations_form', [$this, 'load_module_assets']);
		add_filter( 'woocommerce_single_product_image_gallery_classes', [$this, 'disable_classic_wc_image_swapping']);
		add_action( 'wp_footer', [$this, 'add_gallery_markup_on_default_selection'], 20);
		add_action( 'reycore/woocommerce_single_product_image/before', [$this, 'before_main_image']);


	}
	public function register_assets($assets){

		$assets->register_asset('styles', [
			Base::ASSET_HANDLE => [
				'src'     => Base::get_path( basename( __DIR__ ) ) . '/style.css',
				'deps'    => ['woocommerce-general'],
				'version'   => REY_CORE_VERSION,
			],
		]);

		$assets->register_asset('scripts', [
			Base::ASSET_HANDLE => [
				'src'     => Base::get_path( basename( __DIR__ ) ) . '/script.js',
				'deps'    => ['reycore-wc-product-gallery'],
				'version'   => REY_CORE_VERSION,
			],
		]);

	}

	public function get_gallery(){
		return reycore_wc__get_pdp_component('gallery');
	}

	public function load_module_assets(){

		if( ! $this->base->is_enabled() ){
			return;
		}

		// reycore_assets()->add_styles(Base::ASSET_HANDLE);
		reycore_assets()->add_scripts(Base::ASSET_HANDLE);

	}

	public function add_script_params($params) {
		$params['module_extra_variation_images'] = $this->base->is_enabled();
		return $params;
	}

	/**
	 * Disable classic WC image swapping
	 *
	 * @param array $classes
	 * @return array
	 */
	public function disable_classic_wc_image_swapping($classes){
		if( $this->base->is_enabled() ){
			$classes[] = '--no-variation-image-swap';
		}
		return $classes;
	}

	/**
	 * Get variation images ids
	 *
	 * @param \WC_Product_Variation $variation
	 * @return array
	 */
	public function get_variation_images_ids($variation){

		$variation_id = $variation->get_id();

		if( ! empty($this->_variation_image_ids[$variation_id]) ){
			return $this->_variation_image_ids[$variation_id];
		}

		$variation_image_ids = [];

		// it's mandatory to have a main variation image
		if( $main_variation_image_id = absint($variation->get_image_id()) ){

			$variation_image_ids['main'] = $main_variation_image_id;

			if( $extra_images = $this->base->get_variation_images( $variation_id ) ){

				if( ! is_array($extra_images) ){
					$extra_images = explode(',', trim($extra_images));
				}

				$__valid_extra_images = array_filter( array_unique(array_map('absint', $extra_images)) , function($id){
					return $id && wp_get_attachment_url($id) !== false;
				});

				$variation_image_ids = array_merge($variation_image_ids, $__valid_extra_images);
			}
		}

		$this->_variation_image_ids[$variation_id] = $variation_image_ids;

		return $variation_image_ids;
	}

	/**
	 * Append new property to the variation object, which specifies the total number of images
	 * including the total number of extra images.
	 *
	 * @param array $available_attr
	 * @param WC_Product $product
	 * @param WC_Product_Variation $variation
	 * @return array
	 */
	public function filter_available_variations( $available_attr, $product, $variation ){

		if( is_admin() ){
			return $available_attr;
		}

		if( ! $this->base->is_enabled() ){
			return $available_attr;
		}

		$available_attr['variation_images'] = [];
		$available_attr['variation_images_hash'] = '';

		if( $variation_ids = $this->get_variation_images_ids($variation) ){
			$available_attr['variation_images'] = array_values($variation_ids);
			$available_attr['variation_images_hash'] = \ReyCore\Helper::hash($variation_ids);
		}

		return $available_attr;
	}

	public function register_actions( $ajax_manager ){
		$ajax_manager->register_ajax_action( self::AJAX_LAZY_ACTION, [$this, 'ajax__get_extra_variation_images'], [
			'auth'   => 3,
			'nonce'  => false,
			'transient' => [
				'expiration'         => 2 * WEEK_IN_SECONDS,
				'unique_id'          => ['variation_id', 'type'],
				'unique_id_sanitize' => ['absint', 'sanitize_text_field'],
			],
		] );
	}

	/**
	 * Ajax, get variation gallery html
	 *
	 * @since 1.5.0
	 */
	public function ajax__get_extra_variation_images( $data )
	{

		if ( ! ( isset( $data['variation_id'] ) && ($variation_id = absint($data['variation_id'])) ) ) {
			return ['errors' => esc_html__('No variation ID specified.', 'rey-core')];
		}

		if ( ! isset( $data['count'] ) ) {
			return ['errors' => esc_html__('No count specified.', 'rey-core')];
		}

		$count = absint($data['count']); // 1, 2+

		// if it's 0, we should not even be here
		if( ! $count ){
			return ['errors' => esc_html__('Empty gallery.', 'rey-core')];
		}

		do_action('reycore/module/extra_variation_images/ajax', $data);

		self::setup_gallery_based_on_variation_id( $variation_id );

		return self::get_gallery_html();

	}

	/**
	 * Setup gallery based on variation id.
	 * Make necessary changes to the images and markup in order to
	 * display the variation gallery based on the variation as "main" product.
	 *
	 * @param int $variation_id
	 * @return void
	 */
	public function setup_gallery_based_on_variation_id( $variation_id ){

		if( ! ($variation = wc_get_product( $variation_id )) ){
			return;
		}

		$variation_image_ids = $this->get_variation_images_ids($variation);

		if( empty($variation_image_ids) ){
			return;
		}

		$GLOBALS['disable_image_loaded_attribute'] = true;

		// replace main image ID
		add_filter( 'reycore/woocommerce/product_image/main_image_id', function($image_id) use ($variation_image_ids){
			return $variation_image_ids['main'];
		});

		// replace main image ID
		add_filter( 'woocommerce_product_get_image_id', function($image_id) use ($variation_image_ids){
			if( '' === $image_id ){
				return $variation_image_ids['main'];
			}
			return $image_id;
		});

		// replace the parent product's gallery image ids with the variation's
		// also unset the main because this refers to the gallery only
		add_filter('woocommerce_product_get_gallery_image_ids', function( $ids ) use ($variation_image_ids){
			unset($variation_image_ids['main']);
			return $variation_image_ids;
		}, 9);

		// replace the parent product's gallery image ids with the variation's
		// also unset the main because this refers to the gallery only
		add_filter('reycore/woocommerce/pdp/gallery/thumbnail_items', function( $ids ) use ($variation_image_ids){
			return $variation_image_ids;
		}, 9); // "9" to allow others to append items

		// Set the correct counter for variation gallery
		add_filter( 'reycore/woocommerce/pdp/gallery/items_count', function($c) use ($variation_image_ids){
			return count($variation_image_ids);
		}, 9); // "9" to allow others to increase it

		// optimisation
		// remove thumbnails action if there is only one image
		// probleme cu video
		// if( count($variation_image_ids) <= 1 ){
		// 	remove_all_actions('woocommerce_product_thumbnails');
		// }

		if( wp_doing_ajax() ){
			// restart initialization of gallery
			$this->get_gallery()->init();
		}

		$parent_post = get_post( $variation->get_parent_id() );
		// set the global post to the variation product
		$GLOBALS['post'] = $parent_post; // WPCS: override ok.
		$GLOBALS['rey_parent_post'] = $parent_post; // WPCS: override ok.
		setup_postdata( $GLOBALS['post'] );

	}

	/**
	 * Before the main image, or more exactly before
	 * the `reycore/woocommerce/single_product_image/before` hook
	 *
	 * @return void
	 */
	public function before_main_image(){

		// when loading through Ajax, lazy load main image too.
		if( wp_doing_ajax() ){
			$this->get_gallery()->set_lazy_load_images();
		}

	}

	/**
	 * Create a global variable with the gallery html based on the default variation id.
	 *
	 * @return void
	 */
	public function add_gallery_markup_on_default_selection(){

		if( ! $this->base->is_enabled() ){
			return;
		}

		if( ! $this->settings['default_variation_direct_replacement'] ){
			return;
		}

		if( ! ($default_variation_id = $this->get_gallery()::get_default_variation_id()) ){
			return;
		}

		// we need once more to allow adding the lazy attributes
		$this->get_gallery()->set_lazy_load_images(true);

		self::setup_gallery_based_on_variation_id( $default_variation_id );

		// get gallery html based on the default variation id
		printf('<script type="text/javascript">var reyPDPDefaultVariationMarkup = %s;</script>', wp_json_encode(self::get_gallery_html()));

		unset($GLOBALS['disable_image_loaded_attribute']);

		if( ! empty($GLOBALS['rey_parent_post']) ){
			unset($GLOBALS['rey_parent_post']);
			wp_reset_postdata();
		}

	}

	public static function get_gallery_html(){
		ob_start();
		woocommerce_show_product_images();
		return ob_get_clean();
	}


	/**
	 * Refresh transient
	 *
	 * @since 2.4.0
	 **/
	public function refresh_transient( $product_id = 0, $product = null )
	{

		if( ! $this->base->is_enabled() ){
			return;
		}

		$transient_name = implode('_', [\ReyCore\Ajax::AJAX_TRANSIENT_NAME, self::AJAX_LAZY_ACTION]);
		$transients = [];

		if( $product_id > 0 ){

			// Check if the product is a variable product
			if ($product && $product->is_type('variable')) {
				foreach ($product->get_children() as $product_id) {
					$transients[] = $transient_name . '_' . absint($product_id);
				}
			}
			else {
				$transients[] = $transient_name . '_' . absint($product_id);
			}

		}
		else {
			$transients[] = $transient_name;
		}

		foreach ($transients as $t) {
			\ReyCore\Helper::clean_db_transient( $t );
		}

	}

}
