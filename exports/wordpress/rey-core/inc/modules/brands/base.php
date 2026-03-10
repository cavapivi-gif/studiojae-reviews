<?php
namespace ReyCore\Modules\Brands;

use ReyCore\WooCommerce\Pdp as PdpBase;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	private $brand_terms = [];

	const ASSET_HANDLE = 'reycore-brands';

	public function __construct()
	{
		if( class_exists( '\WC_Brands' ) ){
			return;
		}

		parent::__construct();

		add_action('init', [$this, 'init']);
		add_action('reycore/woocommerce/loop/init', [$this, 'register_brands_component']);
		add_action('reycore/customizer/panel=woocommerce', [$this, 'load_customizer_options']);
		add_action('reycore/templates/register_widgets', [$this, 'register_widgets']);
		add_action( 'reycore/dynamic_tags', [$this, 'dynamic_tag']);

		new Admin();

	}

	public function register_widgets($widgets_manager){
		$widgets_manager->register( new Element );
	}

	public function register_brands_component( $base ){
		$base->register_component( new BrandsComponent );
	}

	public function load_customizer_options( $base ){
		$base->register_section( new Customizer() );
	}

	public function dynamic_tag( $tags ){
		$tags->get_manager()->register( new DynamicTag() );
	}

	public function init(){

		add_filter( 'woocommerce_structured_data_product', [$this, 'structured_data_brands'], 10, 2 );
		add_filter( 'saswp_modify_product_schema_output', [$this, 'saswp_structured_data_brands'], 10 );
		add_filter( 'facebook_for_woocommerce_integration_prepare_product', [$this, 'facebook_catalog_brand'], 10, 2 );
		add_filter( 'reycore/cover/get_cover', [$this, 'brand_cover'], 10, 2);
		add_filter( 'reycore/elementor/wc-attributes/render_link', [$this, 'elementor_output_add_image'], 10, 4);
		add_action( 'woocommerce_order_item_meta_start', [$this, 'display_brand_in_order'], 10, 2);
		add_action( 'reycore/minicart/after_woocommerce_cart_item_name', [$this, 'display_brand_in_cart_panel']);
		add_filter( 'woocommerce_cart_item_name', [$this,'display_brand_in_cart'], 200, 2);
		add_action( 'reycore/woocommerce/loop/brands', [$this, 'render_catalog_button']);
		add_action( 'woocommerce_single_product_summary', [$this, 'render_pdp_button'], $this->get_pdp_position() );
		add_filter( 'reycore/elementor/tag_archive/components', [$this, 'product_grid_components'], 10, 2);
		add_filter( 'reycore/woocommerce/cross_sells/item', [$this, 'add_to_fragments'], 10, 2);
		add_filter( 'reycore/woocommerce/recent/item', [$this, 'add_to_fragments'], 10, 2);
		add_action( 'reycore/woocommerce/cart/crosssells/before', [$this, 'render_in_products']);
		add_action( 'reycore/woocommerce/cart/cart_recent/before', [$this, 'render_in_products']);
		add_filter( 'reycore/elementor/wc-attributes/get_term_tag', [$this, 'wc_attributes_get_term_tag'], 10, 3 );

	}


	/**
	 * Get Brand Attribute Name
	 *
	 * @since 1.0.0
	 **/
	public function get_brand_attribute() {

		static $brand_attribute;

		if( is_null($brand_attribute) ){
			$brand_attribute = reycore__clean( apply_filters('reycore/woocommerce/brand_attribute', get_theme_mod('brand_taxonomy', 'pa_brand')) );
		}

		return $brand_attribute;
	}

	public function get_brand_attribute_slug() {

		static $brand_attribute_slug;

		if( ! is_null($brand_attribute_slug) ){
			return $brand_attribute_slug;
		}

		return $brand_attribute_slug = wc_attribute_taxonomy_slug( $this->get_brand_attribute() );
	}

	public function brands_taxonomy_is_public(){

		static $public;

		if( ! is_null($public) ){
			return $public;
		}

		return $public = \ReyCore\Plugin::instance()->woo::taxonomy_is_public( $this->get_brand_attribute() );
	}

	function structured_data_brands( $markup, $product ){

		if( ! is_array($markup) ){
			return $markup;
		}

		$markup['brand'] = [
			"@type" => "Thing",
			'name' => $this->get_brand_name()
		];

		return $markup;
	}

	/**
	 * Compatibility with `Schema & Structured Data for WP` plugin
	 * @since 1.3.0
	 */
	function saswp_structured_data_brands( $data ){

		if( isset($data['brand']) ){
			$data['brand']['name'] = $this->get_brand_name();
		}

		return $data;
	}


	function get_brands( $field = '', $product_id = false ){

		// Compatibility with Single Variations in Catalog
		if( 'product_variation' === get_post_type() ){
			$product_id = wp_get_post_parent_id();
		}

		$product = wc_get_product( $product_id );

		if( ! $product ){
			return;
		}

		$taxonomy = $this->get_brand_attribute();
		$terms = get_the_terms( $product->get_id(), $taxonomy );

		if($terms && !empty($field) ){
			return wp_list_pluck($terms, $field);
		}

		return $terms;
	}

	/**
	 * Get Product Brand
	 *
	 * @since 1.0.0
	 */
	function get_brand_name( $product_id = false ){

		if( $custom_brand = apply_filters('reycore/structured_data/brand', false) ){
			return $custom_brand;
		}

		$product = wc_get_product( $product_id );

		if ( $product && $brand = $product->get_attribute( $this->get_brand_attribute() ) ) {
			return $brand;
		}

		return false;
	}

	public function brands_tax_exists(){

		static $exists;

		if( is_null($exists) ){
			if( $tax = $this->get_brand_attribute() ){
				$exists = taxonomy_exists( $tax );
			}
		}

		return $exists;
	}

	/**
	 * Show brand attribute in loop & product
	 *
	 * @since 1.0.0
	 */
	function get_brand_link( $brand ){

		if ( empty($brand) ) {
			return '';
		}

		$brand_attribute_name = $this->get_brand_attribute();

		if( $this->brands_taxonomy_is_public() && ($term_link = get_term_link( $brand, $brand_attribute_name )) && is_string($term_link) ){
			return esc_url( $term_link );
		}

		$shop_url = get_permalink( wc_get_page_id( 'shop' ) );

		// Default attribute filtering
		$brand_attribute_name = $this->get_brand_attribute_slug();
		$brand_url = sprintf( '%1$s?filter_%2$s=%3$s', $shop_url, $brand_attribute_name, $brand->slug );

		return esc_url( apply_filters('reycore/woocommerce/brands/url', $brand_url, $brand_attribute_name, $shop_url ) );
	}

	public function render_catalog_button(){
		$this->get_brands_html();
	}

	/**
	 * Show brand attribute in product
	 *
	 * @since 1.0.0
	 */
	function render_pdp_button(){

		if( get_theme_mod('brands__pdp', 'link') === 'none' ){
			return;
		}

		$this->get_brands_html('pdp');
	}

	public function get_pdp_position(){

		$positions = [
			'before' => 4,
			'after' => 7,
		];

		return $positions[ get_theme_mod('brands__pdp_pos', 'after') ];
	}


	/**
	 * Get Brands HTML
	 *
	 * @since 1.0.0
	 */
	function get_brands_html( $source = 'catalog', $product_id = false ){

		if ( ! ( $this->brands_tax_exists() && ( $brands = $this->get_brands('', $product_id) ) ) ) {
			return;
		}

		$product = wc_get_product( $product_id );

		if( ! $product ){
			global $product;
		}

		if( $product && apply_filters('reycore/woocommerce/brands/check_visibility', get_queried_object_id() === $product_id ) ){
			$attributes = array_filter( $product->get_attributes(), 'wc_attributes_array_filter_visible' );
			if( ! isset($attributes[ $this->get_brand_attribute() ]) ){
				return;
			}
		}

		if( method_exists($this, $source . '__brand_output') && ($html = call_user_func( [$this, $source . '__brand_output'], $brands )) ){

			if(
				'catalog' === $source
				&& apply_filters('reycore/woocommerce/brands/catalog/show_image', false)
				&& ($brand_att_image_id = get_term_meta( $brands[0]->term_id, 'rey_brand_image', true ))
			) {
				$html = sprintf( '<a href="%2$s" class="rey-brandLink --catalog --image">%1$s</a>',
					wp_get_attachment_image( $brand_att_image_id, 'full' ),
					$this->get_brand_link( $brands[0] )
				);
			}
			else {
				$html = sprintf( '<div class="rey-brandLink %2$s">%1$s</div>', $html, '--' . $source);
			}

			echo apply_filters('reycore/woocommerce/brands/html', $html, $brands, $this, $source);
		}
	}

	function catalog__brand_output( $brands ){

		$html = '';

		$the_brands = [];

		foreach ($brands as $brand) {

			if( ! isset($brand->name) ){
				continue;
			}

			// sometimes there are duplicates
			if( in_array($brand->slug, $the_brands, true) ){
				continue;
			}

			$the_brands[] = $brand->slug;

			$html .= sprintf('<a href="%s">%s</a>', $this->get_brand_link( $brand ), $brand->name);
		}

		return $html;
	}

	function pdp__brand_output( $brands ){

		$html = '';
		$type = get_theme_mod('brands__pdp', 'link');
		$taxonomy = $this->get_brand_attribute();

		foreach ($brands as $brand) {

			$brand_content = '';

			if( $type === 'image' || $type === 'both' ){

				if( $image_id = reycore__acf_get_field( 'rey_brand_image', $taxonomy . '_' . $brand->term_id) ){
					$image_size = get_post_mime_type($image_id) === 'image/svg+xml' ? 'full' : 'woocommerce_single';
					$image_size = apply_filters( 'reycore/woocommerce/brands/pdp_brand_image_size', $image_size, $image_id, $type );
					$brand_content .= wp_get_attachment_image($image_id, $image_size);
				}
			}

			if( $type === 'link' || $type === 'both' ){
				$brand_content .= sprintf('<span class="__text">%s</span>', $brand->name);
			}

			if( $brand_content ){
				$html .= sprintf('<a href="%1$s">%2$s</a>', $this->get_brand_link( $brand ), $brand_content);
			}
		}

		return $html;
	}


	function display_brand_in_cart($html, $cart_item){

		$status = false;

		if( is_cart() && get_theme_mod('show_brads_cart', false) ){
			$status = true;
		}

		if( is_checkout() && get_theme_mod('show_brads_checkout', false) ){
			$status = true;
		}

		if( ! $status ){
			return $html;
		}

		$product_id = ($parent_id = $cart_item['data']->get_parent_id()) ? $parent_id : $cart_item['data']->get_id();

		ob_start();
		$this->render_brands_by_product_id($product_id);
		$brands = ob_get_clean();

		return $html . $brands;
	}

	function display_brand_in_cart_panel($cart_item){

		if( ! get_theme_mod('show_brads_cart_panel', false) ){
			return;
		}

		$product_id = ($parent_id = $cart_item['data']->get_parent_id()) ? $parent_id : $cart_item['data']->get_id();

		$this->render_brands_by_product_id($product_id);
	}

	function display_brand_in_order($item_id, $item){

		if( ! get_theme_mod('show_brads_order', false) ){
			return;
		}

		if( ! ($product = $item->get_product()) ){
			return;
		}

		$product_id = ($parent_id = $product->get_parent_id()) ? $parent_id : $product->get_id();

		$this->render_brands_by_product_id($product_id);
	}

	function render_brands_by_product_id( $product_id ){

		$GLOBALS['post'] = get_post( $product_id ); // WPCS: override ok.
		setup_postdata( $GLOBALS['post'] );

		$this->get_brands_html();

		wp_reset_postdata();

	}

	/**
	 * Append brand to facebook catalog
	 * if using Facebook official plugin
	 *
	 * @since 1.6.8
	 */
	function facebook_catalog_brand( $product_data, $id ) {

		if( $brand_name = $this->get_brand_name($id) ) {
			$product_data['brand'] = $brand_name;
		}

		return $product_data;
	}



	function brand_cover( $cover, $base ){

		if( is_tax( $this->get_brand_attribute() ) && ($cover_brands = get_theme_mod('cover__shop_brands', '')) ) {

			// dont set if cover is set per page
			if( $base->is_cover_per_page() ){
				return $cover;
			}

			$cover = $cover_brands;
		}

		return $cover;
	}


	function elementor_output_add_image($html, $settings, $attr, $link){

		if( $settings['display'] !== 'list' ){
			return $html;
		}

		if( $settings['attr_id'] !== $this->get_brand_attribute_slug() ){
			return $html;
		}

		if( ! (isset($settings['show_brand_images']) && $settings['show_brand_images'] === 'yes') ){
			return $html;
		}

		$link_content = '';

		if( $image_id = reycore__acf_get_field( 'rey_brand_image', $attr->taxonomy . '_' . $attr->term_id) ){
			$image_size = get_post_mime_type($image_id) === 'image/svg+xml' ? 'full' : 'thumbnail';
			$link_content .= wp_get_attachment_image($image_id, $image_size);
		}

		$link_content .= sprintf('<span class="__text">%s</span>', esc_html( $attr->name ));

		return sprintf( '<a href="%s" class="__img-link">%s</a>', esc_url($link), $link_content );
	}

	public function product_grid_components( $components, $element ){

		// 'inherits' will bail
		if( isset( $element->_settings['hide_brands'] ) && ($setting = $element->_settings['hide_brands']) ){
			$components['brands'] = $setting === 'no';
		}

		return $components;
	}

	/**
	 * Add placeholders into Cross-sells markup template
	 *
	 * @return void
	 * @since 2.4.0
	 */
	public function render_in_products(){
		echo '<# if(items[i].brand){ #> {{{items[i].brand}}} <# } #>';
	}

	/**
	 * When using Woo Attributes widget, set on Image & Brand.
	 * Add the brand images.
	 *
	 * @param string $html
	 * @param object $term
	 * @param string $type
	 * @return string
	 */
	public function wc_attributes_get_term_tag($html, $term, $type){

		if( 'image' !== $type ){
			return $html;
		}

		if( $term->taxonomy !== $this->get_brand_attribute() ){
			return $html;
		}

		if( reycore__is_multilanguage() && isset($term->term_id) ){
			$term = get_term( apply_filters('reycore/translate_ids', $term->term_id, $term->taxonomy), $term->taxonomy);
		}

		if( $image_id = reycore__acf_get_field( 'rey_brand_image', $term->taxonomy . '_' . $term->term_id) ){

			$image_size = apply_filters( 'reycore/woocommerce/brands/attr_size',
				( get_post_mime_type($image_id) === 'image/svg+xml' ? 'full' : 'thumbnail' ),
				$image_id
			);

			return wp_get_attachment_image($image_id, $image_size);
		}

		return $html;
	}

	/**
	 * Add brands into Cross-sells fragments data
	 *
	 * @return void
	 * @since 2.4.0
	 */
	public function add_to_fragments( $data, $product ){

		ob_start();
		$this->get_brands_html('catalog', $product->get_id() );
		$data['brand'] = ob_get_clean();

		return $data;
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Product Brands (Manufacturer)', 'Module name', 'rey-core'),
			'description' => esc_html_x('Adds support for products to have a Brand/Manufacturer assigned to it.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => ['Product Page', 'Product Catalog'],
			'help'        => reycore__support_url('kb/how-to-create-product-brands/'),
		];
	}

	public function module_in_use(){

		return ! empty( get_terms([
			'taxonomy'   => $this->get_brand_attribute(),
			'fields'     => 'ids',
			'hide_empty' => true
		]) );

	}
}
