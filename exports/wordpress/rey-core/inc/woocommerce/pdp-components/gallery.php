<?php
namespace ReyCore\WooCommerce\PdpComponents;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Gallery extends Component {

	protected $galleries = [];

	private $__added_lazy_loading = false;

	public function __construct(){
		add_action( 'init', [$this, 'register_galleries']);
		add_filter( 'woocommerce_get_image_size_gallery_thumbnail', [$this, 'disable_thumbs_cropping']);
		add_action( 'wp_head', [$this, 'preload_main_image']);
		add_action( 'reycore/frontend/wp_head', [ $this, 'body_classes'], 30 );
		add_filter( 'reycore/quickview/ajax_response', [$this, 'include_pswp_in_quickview'], 10, 2 );
		add_filter( 'reycore/delay_js/exclusions', [$this, 'append_delay_js_exclusions']);
	}

	/**
	 * Register Gallery types
	 *
	 * @return void
	 */
	public function register_galleries(){

		$default_galleries = [
			'PdpComponents/GalleryCascadeGrid',
			'PdpComponents/GalleryCascadeScattered',
			'PdpComponents/GalleryCascade',
			'PdpComponents/GalleryGrid',
			'PdpComponents/GalleryGridPattern',
			'PdpComponents/GalleryHorizontal',
			'PdpComponents/GalleryVertical',
		];

		foreach ($default_galleries as $item) {

			$class_name = \ReyCore\Helper::fix_class_name($item, 'WooCommerce');
			$gallery = new $class_name();

			if( $gallery_id = $gallery->get_id() ){
				$this->galleries[ $gallery_id ] = $gallery;
			}

		}

		do_action('reycore/woocommerce/pdp/gallery/init', $this);

	}

	/**
	 * Initialize Gallery Component
	 *
	 * @return void
	 */
	public function init(){

		add_filter( 'woocommerce_single_product_image_gallery_classes', [$this, 'gallery_classes']);
		add_filter( 'woocommerce_gallery_image_html_attachment_image_params', [$this, 'filter_image_attributes'], 20, 4);
		add_filter( 'reycore/woocommerce/product_image/params', [$this, 'gallery_params'] );
		add_filter( 'woocommerce_single_product_zoom_enabled', '__return_false');
		add_filter( 'woocommerce_single_product_photoswipe_enabled', '__return_false');
		add_filter( 'woocommerce_product_thumbnails_columns', [$this, 'product_thumbnails_columns']);
		add_action( 'reycore/woocommerce/product_image/before_gallery', [$this, 'before_gallery']);
		add_action( 'reycore/woocommerce/product_image/before_gallery_wrapper', [$this, 'before_gallery_wrapper']);
		add_action( 'reycore/woocommerce/product_image/after_gallery_wrapper', [$this, 'after_gallery_wrapper']);
		add_action( 'woocommerce_product_thumbnails', [$this, 'before__woocommerce_product_thumbnails'], -1);
		add_action( 'woocommerce_product_thumbnails', [$this, 'remove_lazy_load_images'], 1000);
		add_filter( 'woocommerce_single_product_image_thumbnail_html', [$this, 'single_product_image_thumbnail_html'], 9, 2);
		add_action( 'reycore/woocommerce_single_product_image/before', [$this, 'before_main_image']);
		add_filter( 'woocommerce_gallery_image_size', [$this, 'thumbs_to_large_size']);

	}

	/**
	 * Product Page Component ID
	 *
	 * @return string
	 */
	public function get_id(){
		return 'gallery';
	}

	/**
	 * Product Page Component Name
	 *
	 * @return string
	 */
	public function get_name(){
		return 'Gallery';
	}

	public function render(){}

	/**
	 * Retrieve Gallery Types (for lists)
	 *
	 * @return array
	 */
	public function get_gallery_types(){

		$galleries = [];

		foreach ($this->galleries as $id => $gallery) {
			$galleries[ $id ] = $gallery->get_name();
		}

		return $galleries;
	}

	/**
	 * Retrieve Active Gallery type
	 *
	 * @return string
	 */
	public static function get_active_gallery_type(){
		return get_theme_mod('product_gallery_layout', 'vertical');
	}

	/**
	 * Preload the main image
	 *
	 * @return void
	 */
	public function preload_main_image(){

		if( ! is_product() ){
			return;
		}

		if( ! ($product = wc_get_product()) ){
			return;
		}

		if( ! ($image_id = $product->get_image_id()) ){
			return;
		}

		if( ! ($image_src = wp_get_attachment_image_src( $image_id, 'woocommerce_single' )) ){
			return;
		}

		printf('<link rel="preload" as="image" type="image/jpeg" href="%s"/>', $image_src[0]) . "\n";
	}

	/**
	 * Append CSS classes to Gallery
	 *
	 * @param array $classes
	 * @return array
	 */
	public function gallery_classes($classes)
	{

		$classes['gallery_type'] = 'woocommerce-product-gallery--' . esc_attr( self::get_active_gallery_type() );

		foreach ($this->galleries as $id => $gallery) {
			if( $id === self::get_active_gallery_type() ){
				$classes = array_merge($classes, $gallery->get_gallery_css_classes());
			}
		}

		if( get_theme_mod('product_gallery_preview_ratio', '') ){
			$classes['preview_ratio'] = '--preview-ratio';
		}

		if( get_theme_mod('pdp_img_radius', '') ){
			$classes['radius'] = '--radius';
		}

		if( get_theme_mod('product_page_gallery_mobile_peek', false) && ($product = wc_get_product()) && $product->get_gallery_image_ids() ){
			$classes['peek_mobile'] = '--peek-side-mobile';
		}

		/**
		 * `--default-variation-img` css class is used to determine if the page has loaded
		 * with a default variation selection.
		 */
		if( self::get_default_variation_image_id() ){
			$classes['loading'] = '--loading';
			$classes['is_variation'] = '--default-variation-img';
		}

		if( ! self::maybe_support_lazy_loading() ){
			$classes['no_lazy'] = '--no-rey-lazy';
		}

		if( self::is_gallery_with_thumbs() ){

			$classes['gallery_with_thumbs'] = '--gallery-thumbs';

			if( get_theme_mod('product_gallery_thumbs_flip', false) ){
				$classes['gallery_flip_thumbs'] = '--flip-thumbs';
			}

			if( get_theme_mod('product_gallery_thumbs_disable_cropping', false) ){
				$classes['gallery_thumbs_nocrop'] = '--thumbs-no-crop';
			}
		}

		if(
			self::is_gallery_with_thumbs() &&
			get_theme_mod('custom_main_image_height', false)
		){
			$classes['main-image-container-height'] = '--main-img-height';
		}

		if( ! self::mobile_gallery_enabled() ){
			$classes['no_mobile_gallery'] = '--no-mobile-gallery';
		}

		if( self::supports_animated_entries() ){
			$classes['animated_entries'] = '--supports-animated-entry';
		}

		if( ! self::is_lightbox_enabled() ){
			$classes['no_lightbox'] = '--no-lb';
		}

		return $classes;
	}

	public function body_classes( $frontend )
	{
		if( ! is_product() ){
			return;
		}

		if( self::is_gallery_with_thumbs() && ! get_theme_mod('product_page_summary_fixed__gallery', false) ){
			$frontend->remove_body_class(['fixed_summary', 'fixed_css_first', 'fixed_summary_gallery', 'fixed_summary_animate']);
		}

		$classes['gallery_type'] = '--gallery-' . self::get_active_gallery_type();

		$frontend->add_body_class($classes);

	}

	public function gallery_params($params)
	{

		$params['type'] = self::get_active_gallery_type();
		$params['zoom'] = get_theme_mod('product_page_gallery_zoom', true);
		$params['cascade_bullets'] = get_theme_mod('single_skin_cascade_bullets', true);
		$params['thumb_event'] = get_theme_mod('product_gallery_thumbs_event', 'click');
		$params['lightbox_captions'] = self::maybe_remove_image_title();
		$params['lightbox'] = self::is_lightbox_enabled();
		$params['photoswipe_options'] = apply_filters( 'woocommerce_single_product_photoswipe_options', [
			'shareEl'               => false,
			'closeOnScroll'         => false,
			'history'               => false,
			'hideAnimationDuration' => 0,
			'showAnimationDuration' => 0,
		] );
		$params['mobile_gallery_nav'] = self::get_mobile_nav_style();
		$params['loop'] = true;
		$params['autoheight'] = true;
		$params['autoheight_desktop'] = get_theme_mod('product_page_gallery_autoheight_desktop', true);
		$params['autoheight_mobile'] = get_theme_mod('product_page_gallery_autoheight_mobile', true);
		$params['start_index'] = 0;
		$params['lazy_images'] = self::maybe_support_lazy_loading();

		if( $product = wc_get_product() ){
			$params['product_page_id'] = $product->get_id();
			$params['product_main_image']['id'] = $product->get_image_id();
			$params['product_main_image']['src'] = wp_get_attachment_image_src( $params['product_main_image']['id'], apply_filters( 'woocommerce_gallery_full_size', apply_filters( 'woocommerce_product_thumbnails_large_size', 'full' ) ) );
			$params['product_main_image']['thumb'] = wp_get_attachment_image_src( $params['product_main_image']['id'], 'woocommerce_gallery_thumbnail' );
		}

		return $params;
	}

	/**
	 * Run before the gallery rendering
	 *
	 * @return void
	 */
	public function before_gallery()
	{

		add_action( 'wp_footer', [$this, 'wp_footer_after_gallery'] );

		$active_gallery_type = self::get_active_gallery_type();

		foreach ($this->galleries as $id => $gallery) {
			if( $id === $active_gallery_type ){
				$gallery->init( $this );
				$gallery->load_assets();
				reycore_assets()->add_styles( ['rey-wc-product-gallery', 'rey-wc-product-gallery-' . $id]);
			}
		}

	}

	/**
	 * Retrieve the count of the gallery items
	 *
	 * @return int
	 */
	public static function count_gallery_items(){
		return (int) apply_filters('reycore/woocommerce/pdp/gallery/items_count', count( reycore_wc__get_product_images_ids() ) );
	}

	/**
	 * Control number of columns thumbs in vertical/horizontal gallery
	 *
	 * * Does not influence mobile gallery (which can only be set with CSS)
	 *
	 * @param int $cols
	 * @return int
	 */
	public function product_thumbnails_columns($cols){
		/**
		 * For Vertical, it's intentionally not working.
		 * That's because the thumbs track is stretched and because of the flexible thumbs height,
		 * the height of the track cannot be properly calculated.
		 */
		if( $custom = get_theme_mod('product_gallery_thumbs_max', '') ){
			return absint($custom);
		}
		return $cols;
	}

	public function disable_thumbs_cropping($size){

		if( get_theme_mod('product_gallery_thumbs_disable_cropping', false) ){
			$size['height']  = 9999;
			$size['crop']   = false;
		}

		return $size;
	}

	public static function mobile_gallery_enabled(){
		return apply_filters('reycore/woocommerce/allow_mobile_gallery', true);
	}

	/**
	 * Load the gallery "thumbnails" as large images because
	 * all galleries use large format.
	 *
	 * @param string $size
	 * @return string
	 */
	public function thumbs_to_large_size($size) {
		return 'woocommerce_single';
	}

	/**
	 * Adds attributes to gallery images.
	 *
	 * @param array $attributes
	 * @return array
	 */
	public function filter_image_attributes( $attributes, $attachment_id, $image_size, $main_image ){

		$classes['default'] = '__img';
		$classes['image_type'] = '--gallery-img';

		// specify main image
		if( self::is_main_image_environment() ){
			$classes['image_type'] = '--main-img';
		}

		// prevent other plugins from trying to lazy load
		$classes['plugins_no_lazy'] = 'no-lazy';
		$attributes['data-skip-lazy'] = 1;
		$attributes['data-no-lazy'] = 1;

		if( $main_image ){
			$attributes['loading'] = 'eager';
		}

		if( self::maybe_remove_image_title() ){
			$attributes['title'] = '';
		}

		if( ! empty($attributes['class']) ){
			$attributes['class'] .= ' ';
			$attributes['class'] .= implode(' ', $classes);
		}
		else {
			$attributes['class'] = implode(' ', $classes);
		}

		return $attributes;

	}

	/**
	 * Adds lazy loading attributes to "thumbnails" (gallery images) images.
	 *
	 * @param array $attributes
	 * @return array
	 */
	public function set_thumbnails_lazy_load( $attributes ){

		$attributes['src'] = '';
		$attributes['loading'] = '';
		$attributes['data-rey-lazy-src'] = $attributes['data-src'];

		return $attributes;
	}

	/**
	 * Sets a global variable to indicate that the current image is the main image
	 *
	 * @return void
	 */
	public function set_main_image_environment(){
		$GLOBALS['gallery_is_main'] = true;
	}

	/**
	 * Removes the global variable that indicates that the current image is the main image
	 *
	 * @return void
	 */
	public function remove_main_image_environment(){
		unset($GLOBALS['gallery_is_main']);
	}

	/**
	 * Checks if the current image is the main image
	 *
	 * @return boolean
	 */
	public static function is_main_image_environment(){
		return ! empty($GLOBALS['gallery_is_main']);
	}

	/**
	 * Before the main image, or more exactly before
	 * the `reycore/woocommerce/single_product_image/before` hook
	 *
	 * @return void
	 */
	public function before_main_image(){
		$this->set_main_image_environment();
	}

	/**
	 * Before the gallery thumbnails's, or more exactly before
	 * the `woocommerce_product_thumbnails` hook
	 *
	 * @return void
	 */
	public function before__woocommerce_product_thumbnails(){
		$this->remove_main_image_environment();
		$this->set_lazy_load_images();
	}

	/**
	 * Force "thumbs" (gallery images) to lazy load
	 *
	 * @return void
	 */
	public function set_lazy_load_images( $bypass = false ) {

		if( $this->__added_lazy_loading && ! $bypass ){
			return;
		}

		if( ! self::maybe_support_lazy_loading() ){
			return;
		}

		add_filter( 'wp_calculate_image_srcset', '__return_false' );
		add_filter( 'woocommerce_gallery_image_html_attachment_image_params', [$this, 'set_thumbnails_lazy_load'], 30);

		$this->__added_lazy_loading = true;

	}

	/**
	 * Force "thumbs" (gallery images) to lazy load.
	 * Remove the hook
	 *
	 * @return void
	 */
	public function remove_lazy_load_images() {

		if( ! self::maybe_support_lazy_loading() ){
			return;
		}

		remove_filter( 'wp_calculate_image_srcset', '__return_false' );
		remove_filter( 'woocommerce_gallery_image_html_attachment_image_params', [$this, 'set_thumbnails_lazy_load'], 30);
	}

	/**
	 * Checks if the lightbox is enabled
	 *
	 * @return boolean
	 */
	public static function is_lightbox_enabled(){
		return get_theme_mod('product_page_gallery_lightbox', true);
	}

	public static function maybe_remove_image_title(){
		return apply_filters('reycore/woocommerce/pdp/gallery/remove_title', true);
	}

	public static function maybe_support_lazy_loading(){
		return apply_filters('reycore/woocommerce/pdp/gallery/lazy_loading', get_theme_mod('product_page_gallery__lazy_load', false));
	}

	public static function is_gallery_with_thumbs(){
		return apply_filters('reycore/woocommerce/pdp/gallery/with_thumbs', in_array( self::get_active_gallery_type(), ['vertical', 'horizontal'], true ));
	}

	public static function get_mobile_nav_style(){
		return get_theme_mod('product_gallery_mobile_nav_style', 'bars');
	}

	public static function supports_animated_entries(){
		return apply_filters('reycore/woocommerce/pdp/gallery/add_animation', in_array( self::get_active_gallery_type(), [
				'grid',
				'cascade',
				'cascade-scattered',
				'cascade-grid'
			], true)
		);
	}

	/**
	 * Detect and get the default selected variation's ID
	 *
	 * @return int
	 */
	public static function get_default_variation_id(){

		if( ! apply_filters('reycore/woocommerce/pdp/gallery/get_default_variation', true) ){
			return;
		}

		if( ! ($product = wc_get_product()) ){
			return;
		}

		// Ensure it's a variable product
		if ( ! $product->is_type( 'variable' ) ) {
			return;
		}

		/**
		 * @var \WC_Product_Variable $product
		 */

		static $variation_id;

		// Return cached value
		if( ! is_null($variation_id) ){
			return $variation_id;
		}

		$variation_id = 0;
		$default_attributes = $product->get_default_attributes(); // Get the default attributes

		if ( empty( $default_attributes ) ) { // Check if there are any default attributes
			return $variation_id;
		}

		$maybe_check_variation_attributes = false;

		if(
			($update_gallery_on_single_default = get_theme_mod('pdp_swatches__update_gallery', ''))
			&& ($update_gallery__attribute = wc_attribute_taxonomy_name( $update_gallery_on_single_default )) // prefix with pa_
			&& isset($default_attributes[ $update_gallery__attribute ]) // it's set as default
		){
			// will check the variation attributes for empty values
			$maybe_check_variation_attributes = true;
		}
		else {
			// get all parent product attributes used for variations
			$variation_attributes = $product->get_variation_attributes();
			// Check if all attributes are set in the default attributes array
			if( count($variation_attributes) !== count($default_attributes) ){
				return $variation_id;
			}
		}

		$prefixed_default_attributes = [];

		foreach ($default_attributes as $key => $value) {
			$prefixed_default_attributes['attribute_' . $key] = $value;
		}

		$variation_id = \WC_Data_Store::load( 'product' )->find_matching_product_variation( $product, $prefixed_default_attributes );

		if( $maybe_check_variation_attributes && $variation_id && ($variation = wc_get_product( $variation_id )) ){

			/**
			 * @var \WC_Product_Variation $variation
			 */
			$variation_attributes = array_filter($variation->get_variation_attributes());

			// if all of the variation attributes is empty, then it's not a valid variation
			if( empty($variation_attributes) ){
				$variation_id = 0;
			}

		}

		return $variation_id;
	}

	/**
	 * Detect and get the default selected variation's main image
	 *
	 * @return int
	 */
	public static function get_default_variation_image_id(){

		if( wp_doing_ajax() ){
			return;
		}

		static $variation_image_id;

		if( is_null( $variation_image_id ) ){

			$variation_image_id = 0;

			if( apply_filters('reycore/woocommerce/pdp/gallery/default_variation_swap', true) ){
				if( $variation_id = self::get_default_variation_id() ){
					if( $variation = wc_get_product( $variation_id ) ){
						// it's mandatory to have a main variation image
						if(
							($__variation_image_id = absint($variation->get_image_id())) &&
							// get main product
							($main_product = wc_get_product( $variation->get_parent_id() )) &&
							// ensure main product image is not the same as the variation
							$__variation_image_id !== absint($main_product->get_image_id())
						){
							$variation_image_id = $__variation_image_id;
						}

					}
				}
			}
		}

		return $variation_image_id;
	}


	/**
	 * Filter the gallery images html. Usually `wc_get_gallery_image_html` is wrapped in this hook so its markup can be modified.
	 *
	 * @param string $html
	 * @param int $post_thumbnail_id
	 * @return string
	 */
	public function single_product_image_thumbnail_html($html, $post_thumbnail_id){

		$is_main = self::is_main_image_environment();

		$index = isset($GLOBALS['pdp_gallery_item_count']) ? $GLOBALS['pdp_gallery_item_count'] : 0;

		// Add attributes to the main image wrapper
		// ensures it starts with <div

		$start_search_for = '<div ';
		$start_replace_with = '';

		// check for main image environment
		if( $is_main ){
			$start_replace_with .= 'data-main-item '; // specify main item attribute
			// ensure it's loaded
			if( ! wp_doing_ajax() && ! isset($GLOBALS['disable_image_loaded_attribute']) ){
				$start_replace_with .= 'data-image-loaded ';
			}
		}

		// increment index attribute
		$start_replace_with .= sprintf('data-index="%d" ', $index);

		// do the replacement
		if (substr($html, 0, 5) === $start_search_for) {
			$html = substr_replace($html, $start_search_for . apply_filters('reycore/woocommerce/pdp/gallery/item_attributes', $start_replace_with, $index), 0, 5);
		}

		// ------------------------------

		// add preloaders before ending anchor tag
		$html = str_replace( '</a>', '<span class="rey-lineLoader"></span></a>', $html );
		// ensure elementor's lightbox is disabled
		$link_attributes = 'data-elementor-open-lightbox="no" ';
		$link_attributes .= 'aria-haspopup="true" aria-expanded="false" ';
		$link_attributes .= sprintf('aria-label="%s" ', esc_html__('OPEN GALLERY', 'rey-core'));
		$html = str_replace( '<a ', '<a ' . $link_attributes, $html );

		// ------------------------------

		// Adds buttons
		// like video, lightbox etc.
		if (substr($html, -6) === '</div>') {

			ob_start();

			/**
			 * Fires before ending </div> tag.
			 * @since 2.8.0
			 */
			do_action('reycore/woocommerce/pdp/gallery/after_image', $this, $is_main, $post_thumbnail_id);

			// add lightbox button
			if( strpos($html, 'data-no-lightbox') === false ){
				self::render_lightbox_button();
			}

			// output is catched and appended to the html
			if( $action = ob_get_clean() ){
				$html = substr_replace($html, $action . '</div>', -6);
			}

		}

		if( isset($GLOBALS['pdp_gallery_item_count']) ){
			$GLOBALS['pdp_gallery_item_count']++;
		}

		return $html;
	}

	/**
	 * Render mobile navigation
	 *
	 * @return void
	 */
	public function before_gallery_wrapper(){

		$GLOBALS['pdp_gallery_item_count'] = 0;

		if( ! ($count = self::count_gallery_items()) ){
			return;
		}

		// no need navigations if just a single image
		if( $count <= 1 ){
			return;
		}

		// add preloader for various actions
		// hidden by default
		// echo '<div class="rey-lineLoader __gallery-loader"></div>';

		// start extra wrapper in order to properly
		// position the arrows vertically, in the middle
		echo '<div class="__topWrapper">';

	}

	/**
	 * Render mobile navigation
	 *
	 * @return void
	 */
	public function after_gallery_wrapper(){

		unset($GLOBALS['pdp_gallery_item_count']);

		if( ! ($count = self::count_gallery_items()) ){
			return;
		}

		// no need navigations if just a single image
		if( $count <= 1 ){
			return;
		}

		// Render counter
		self::add_counter_markup();

		// Render arrows
		self::add_arrows_navigation_markup();

		// close extra wrapper in order to properly
		// position the arrows vertically, in the middle
		// end .__topWrapper
		echo '</div>';

		$dots_nav = self::get_mobile_nav_style();

		// it's still added because it's likely to be used on mobile
		if( 'thumbs' !== $dots_nav ){
			// Dots Nav.
			$dots_nav_dots_classes[] = 'dotsNav';
			$dots_nav_dots_classes['nav_style'] = '--nav-' . $dots_nav;
			$dots_nav_dots_classes['hide_desktop'] = '--dnone-lg';
			$dots_nav_dots_classes = apply_filters('reycore/woocommerce/pdp/gallery/mobile_nav_classes', $dots_nav_dots_classes, $dots_nav);
			self::add_dots_navigation_markup(implode(' ', $dots_nav_dots_classes), false);
		}

		self::add_thumbs_navigation_markup();

	}

	/**
	 * Generate HTML markup for gallery navigation with thumbnails
	 *
	 * @return void
	 */
	public static function add_thumbs_navigation_markup(){

		if( ! ($count = self::count_gallery_items()) ){
			return;
		}

		$gallery_with_thumbs = self::is_gallery_with_thumbs();
		$mobile_with_thumbs = 'thumbs' === self::get_mobile_nav_style();

		if( ! ($gallery_with_thumbs || $mobile_with_thumbs) ){
			return;
		}

		$gallery_thumbnail = wc_get_image_size( 'gallery_thumbnail' );

		if( get_theme_mod('product_gallery_thumbs_disable_cropping', false) ){
			$gallery_thumbnail['height']  = 9999;
		}

		$thumbnail_size = apply_filters( 'woocommerce_gallery_thumbnail_size', [$gallery_thumbnail['width'], $gallery_thumbnail['height']] );

		$thumbs = [];

		$i = 0;

		foreach ( apply_filters('reycore/woocommerce/pdp/gallery/thumbnail_items', reycore_wc__get_product_images_ids() ) as $key => $image_id) {

			$uses_image_id = false !== $image_id;

			add_filter( 'wp_calculate_image_srcset', '__return_false' );

			$thumb_image_attributes = [
				'class' => 'woocommerce-product-gallery__mobile-img',
			];

			if( self::maybe_support_lazy_loading() ){
				$thumb_image_attributes['loading'] = '';
				$thumb_image_attributes['src'] = '';
				$thumb_image_attributes['data-rey-lazy-src'] = $uses_image_id ?
					wp_get_attachment_image_url($image_id, $thumbnail_size) :
					wc_placeholder_img_src( 'woocommerce_single' );
			}

			$thumb_image = $uses_image_id ?
				wp_get_attachment_image($image_id, $thumbnail_size, false, $thumb_image_attributes) :
				wc_placeholder_img('woocommerce_single', $thumb_image_attributes);

			// prefix preloader
			if( self::maybe_support_lazy_loading() ){
				$thumb_image .= '<div class="rey-lineLoader"></div>';
			}

			$thumb = sprintf('<button class="__thumbItem %s" data-index="%d" aria-label="%s" tabindex="-1">%s</button>',
				($i === 0 ? '--active' : ''),
				$i,
				esc_html__('Go to ', 'rey-core') . ($i+1),
				$thumb_image
			);

			remove_filter( 'wp_calculate_image_srcset', '__return_false' );

			$thumbs[] = apply_filters('reycore/woocommerce/product_mobile_gallery/slide_html', $thumb, $image_id, $key);

			$i++;
		}

		$wrapper_classes = [];

		if( $gallery_with_thumbs ){
			$wrapper_classes[] = '--thumbs-gallery';
		}

		if( $mobile_with_thumbs ){
			$wrapper_classes[] = '--mobile-thumbs';
		}

		$wrapper_classes['gallery_thumbs_style'] = '--thumbs-arr-' . esc_attr(get_theme_mod('product_gallery_thumbs_nav_style', 'boxed'));

		$disabled_attribute_prev = 'data-disabled';
		$disabled_attribute_next = '';

		if( is_rtl() ){
			$disabled_attribute_prev = '';
			$disabled_attribute_next = 'data-disabled';
		}

		$nav_arrows = sprintf('<button data-dir="<" %3$s aria-label="%1$s" tabindex="-1">%2$s</button>', esc_html__('Previous', 'rey-core'), reycore__get_svg_icon(['id' => 'arrow']), $disabled_attribute_prev);
		$nav_arrows .= sprintf('<button data-dir=">" %3$s aria-label="%1$s" tabindex="-1">%2$s</button>', esc_html__('Next', 'rey-core'), reycore__get_svg_icon(['id' => 'arrow']), $disabled_attribute_next);

		// On horizontal, when max thumbs is larger than the published thumbs
		if( self::get_active_gallery_type() === 'horizontal' ){
			if( ($max_thumbs = get_theme_mod('product_gallery_thumbs_max', '')) && count($thumbs) <= $max_thumbs ){
				$wrapper_classes[] = '--j-center';
			}
		}

		return printf('<div class="__thumbs-wrapper %s"><div class="__thumbs-track" data-count="%s">%s</div><div class="__thumbs-arr">%s</div></div>',
			implode(' ', $wrapper_classes),
			count($thumbs),
			implode('', $thumbs),
			$nav_arrows
		);

	}

	/**
	 * Generate HTML markup for gallery navigation
	 *
	 * @param string $wrapper_class
	 * @param boolean $scroll_item
	 * @return void
	 */
	public static function add_dots_navigation_markup($wrapper_class = '', $after = ''){

		if( ! ($count = self::count_gallery_items()) ){
			return;
		}

		$bullets = [];

		for ($i=0; $i < $count; $i++) {
			$bullets[] = sprintf('<button class="__navItem %s" data-index="%d" aria-label="%s"></button>', ($i === 0 ? "--active" : ""), $i, esc_html__('Go to image', 'rey-core'),);
		}

		printf('<div class="__nav-wrapper %1$s" data-count="%4$d"><div class="__nav">%2$s</div>%3$s</div>',
			$wrapper_class,
			implode('', $bullets),
			$after,
			$count
		);

	}

	/**
	 * Generate HTML markup for counter (1/5)
	 *
	 * @return void
	 */
	public static function add_counter_markup(){

		if( ! get_theme_mod('product_gallery_counter_data', false) ){
			return;
		}

		printf(
			'<div class="__counter-wrapper"><span class="__current">%s</span>/<span class="__total">%s</span></div>',
			1,
			self::count_gallery_items()
		);

	}

	/**
	 * Generate HTML markup for arrows navigation
	 *
	 * @return void
	 */
	public static function add_arrows_navigation_markup(){

		if( ! ($count = self::count_gallery_items()) ){
			return;
		}

		$cond['desktop'] = get_theme_mod('product_page_gallery_arrow_nav', false) && self::is_gallery_with_thumbs();
		$cond['mobile'] = get_theme_mod('product_gallery_mobile_arrows', false);

		if( ! in_array(true, $cond, true) ){
			return;
		}

		$classes = [];

		if( ! $cond['desktop'] ){
			$classes[] = '--dnone-lg';
		}

		if( ! $cond['mobile'] ){
			$classes[] = '--dnone-md --dnone-sm';
		}

		printf(
			'<div class="__arr-wrapper %2$s">%1$s</div>',
			reycore__svg_arrows([
				'attributes' => [
					'left' => sprintf('data-dir="<" aria-label="%s"', esc_html__('Previous', 'rey-core')),
					'right' => sprintf('data-dir=">" aria-label="%s"', esc_html__('Next', 'rey-core')),
				],
				'tag' => 'button',
				'echo' => false,
				'name' => 'pdp_gallery_arrows',
			]),
			implode(' ', $classes)
		);

	}

	public function wp_footer_after_gallery(){
		$this->add_delay_js_hack();
		$this->add_photoswipe_template();
	}

	/**
	 * Hack to force load the JS, when delayed JS is enabled. This prevents the gallery to remain in loading state, as if it's stuck, until the visitor makes any event.
	 * Should happen only if a variation is set as default and has a different image vs. main one.
	 *
	 * @return void
	 */
	public function add_delay_js_hack(){

		if( ! is_product() ){
			return;
		}

		if( ! apply_filters('reycore/woocommerce/pdp/gallery/delayed_js_preselected_variation_instant_load', reycore__js_is_delayed() && self::get_default_variation_image_id()) ){
			return;
		}

		echo '<script type="text/javascript" id="reycore-gallery-has-default" data-noptimize="" data-no-optimize="1" data-no-defer="1" data-pagespeed-no-defer=""> setTimeout(()=>{window.dispatchEvent(new Event("mousemove")); console.log("Force load JS because of the pre-selected variation in the gallery.")}, 100);</script>';

	}

	/**
	 * Adds the Photoswipe template.
	 * Also creates a global variable with the assets paths in order to be downloaded on request.
	 *
	 * @return void
	 */
	public function add_photoswipe_template(){

		wc_get_template( 'single-product/photoswipe.php' );

		$assets = [
			'styles' => ['photoswipe', 'photoswipe-default-skin'],
			'scripts' => ['wc-photoswipe', 'wc-photoswipe-ui-default'],
		];

		$assets_with_paths = [];

		if( wp_doing_ajax() ){
			do_action('wp_enqueue_scripts');
		}

		foreach ($assets as $type => $handles) {
			foreach ($handles as $handle) {
				if ('styles' === $type && wp_style_is($handle, 'registered')) {
					$assets_with_paths[$type][$handle] = wp_styles()->registered[$handle]->src . '?ver=' . REY_CORE_VERSION;
				}
				elseif ('scripts' === $type && wp_script_is($handle, 'registered')) {
					$assets_with_paths[$type][$handle] = wp_scripts()->registered[$handle]->src . '?ver=' . wp_scripts()->registered[$handle]->ver;
				}
			}
		}

		if( empty($assets_with_paths['styles']) || empty($assets_with_paths['scripts']) ){
			return;
		}

		printf('<script type="text/javascript">var PS_scripts_styles = %s;</script>', wp_json_encode($assets_with_paths));

	}

	public static function render_lightbox_button(){

		if( ! self::is_lightbox_enabled() ){
			return;
		}

		if( ! get_theme_mod('product_page_gallery__btn__enable', true) ){
			return;
		}

		$classes = ['rey-openBtn', '__lightbox-btn', 'btn'];
		$btn_text = get_theme_mod('product_page_gallery__btn__text', esc_html__('OPEN GALLERY', 'rey-core'));
		$btn_content = '';

		// text
		if( get_theme_mod('product_page_gallery__btn__text_enable', false) ){
			$classes[] = '--has-text';
			$btn_content .= '<span class="__lightbox-btnContent">' . $btn_text . '</span>';
		}

		// icon
		if( $icon = get_theme_mod('product_page_gallery__btn__icon', 'plus-stroke') ){
			$classes[] = '--has-icon';
			$btn_content .= reycore__get_svg_icon(['id' => 'reycore-icon-' . $icon]);
		}

		printf('<button class="%3$s" title="%2$s" aria-label="%2$s" role="button">%1$s</button>',
			$btn_content,
			$btn_text,
			implode(' ', $classes)
		);

	}

	public function append_delay_js_exclusions($scripts) {
		return array_merge($scripts, ['reycore-gallery-has-default' => 'reycore-gallery-has-default']);
	}

	/**
	 * Include PSWP template in quickview response
	 *
	 * @param array $return
	 * @param array $data
	 * @return array
	 */
	public function include_pswp_in_quickview( $return, $data ){

		if( ! (isset($data['pswp']) && ! absint($data['pswp'])) ){
			return $return;
		}

		ob_start();
		$this->add_photoswipe_template();
		$return['pswp'] = ob_get_clean();

		return $return;
	}

}
