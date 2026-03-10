<?php
namespace ReyCore\Modules\GalleryThreeSixty;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	const ASSET_HANDLE = 'reycore-pdp-gallery-360';

	const ARRAY_KEY = 'image360';

	// private $gallery_with_thumbs;
	private $settings;

	public function __construct()
	{
		add_action( 'reycore/woocommerce/init', [$this, 'init']);
	}

	public function init() {

		if( ! $this->is_enabled() ){
			return;
		}

		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
		add_action( 'wp_head', [$this, 'prevent_start']);
		add_filter( 'woocommerce_single_product_image_gallery_classes', [$this, 'add_classes'], 20);

		add_action( 'reycore/woocommerce_single_product_image/before', [$this, 'first__render_inside_gallery'], 5);
		add_action( 'woocommerce_product_thumbnails', [$this, 'second__render_inside_gallery'], 5);
		add_action( 'woocommerce_product_thumbnails', [$this, 'last__render_inside_gallery'], 100);

		add_filter( 'reycore/woocommerce/pdp/gallery/items_count', [$this, 'increase_gallery_items_count']);
		add_action( 'reycore/woocommerce/pdp/gallery/thumbnail_items', [$this, 'push_thumbnail_id_into_gallery_items']);
		add_action( 'reycore/woocommerce/product_mobile_gallery/slide_html', [$this, 'render_icon_inside_thumbnail'], 10, 3);
		add_filter( 'reycore/woocommerce/product_image/params', [$this, 'gallery_params'], 10 );
		add_filter( 'reycore/quickview/dependency_assets', [$this, 'quickview_dependency_assets'], 10 );

		new Customizer();
		new AcfFields();
	}


	public function register_assets($assets){

		$assets->register_asset('styles', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/style.css',
				'deps'    => ['woocommerce-general'],
				'version'   => REY_CORE_VERSION,
			],
		]);

		$assets->register_asset('scripts', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/script.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
			],
		]);

		$assets->register_asset('scripts', [
			'js-cloudimage-360-view' => [
				'src'     => REY_CORE_URI . 'assets/js/lib/js-cloudimage-360-view.min.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => '2.6.0',
				'plugin' => true,
			],
		]);

	}

	/**
	 * Get current product
	 *
	 * @return null|WC_Product
	 */
	public function get_product(){

		$product_id = false;

		if( wp_doing_ajax() ){
			if( isset($_REQUEST['product_id']) && ($ajax_pid = absint($_REQUEST['product_id'])) ){
				$product_id = $ajax_pid;
			}
			elseif( isset($_REQUEST[\ReyCore\Ajax::DATA_KEY]['id']) && ($ajax_pid = absint($_REQUEST[\ReyCore\Ajax::DATA_KEY]['id'])) ){
				$product_id = $ajax_pid;
			}
		}

		if( ! ($product = wc_get_product( $product_id )) ){
			global $product;
		}

		if( ! $product ){
			return;
		}

		if( ! $product instanceof \WC_Product ){
			return;
		}

		return $product;
	}

	/**
	 * Get product id
	 *
	 * @return int|null
	 */
	public function get_product_id(){

		static $product_id;

		if( is_null($product_id) ){
			if( $product = $this->get_product() ){
				$product_id = $product->get_id();
			}
		}

		return absint($product_id);
	}

	/**
	 * Get 360 images list
	 *
	 * @return array|null
	 */
	public function get_images(){

		static $images;

		if( is_null($images) ){
			$images = [];
			if( $product_id = $this->get_product_id() ){
				if( $ids = reycore__acf_get_field('product_360_images', $product_id ) ){
					$images = (array) $ids;
				}
			}
		}

		return $images;
	}

	/**
	 * Get position in gallery
	 *
	 * @return string|null
	 */
	public function get_position( $hook_priority = false ){

		static $position;

		if( is_null($position) ){

			$positions = [
				'first'  => 4,
				'second' => 6,
				'last'   => 100
			];

			$_pos = get_theme_mod('wc360_position', 'second');

			if( $hook_priority ){
				if( ! empty($positions[$_pos]) ){
					$position = $positions[ $_pos ];
				}
			}
			else {
				$position = $_pos;
			}
		}

		return $position;
	}

	/**
	 * Render gallery item, as first
	 *
	 * @return void
	 */
	public function first__render_inside_gallery(){
		if( 'first' === $this->get_position() ){
			$this->render_inside_gallery();
		}
	}

	/**
	 * Render gallery item, as second (default)
	 *
	 * @return void
	 */
	public function second__render_inside_gallery(){
		if( 'second' === $this->get_position() ){
			$this->render_inside_gallery();
		}
	}

	/**
	 * Render gallery item, as last
	 *
	 * @return void
	 */
	public function last__render_inside_gallery(){
		if( 'last' === $this->get_position() ){
			$this->render_inside_gallery();
		}
	}

	/**
	 * Render gallery item
	 *
	 * @return void
	 */
	public function render_inside_gallery(){

		if( ! ($images = $this->get_images()) ){
			return;
		}

		reycore_assets()->add_styles(self::ASSET_HANDLE);
		reycore_assets()->add_scripts([self::ASSET_HANDLE, 'js-cloudimage-360-view']);

		$html = sprintf('<div class="woocommerce-product-gallery__image rey-360image --image-360" data-no-zoom data-no-lightbox data-no-click>%s</div>', $this->render_block( $images ) );

		echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', $html, $images[0] ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped

	}

	public function render_block( $image_ids = [] ){

		$images = [];

		foreach ($image_ids as $image_id) {
			$images[] = wp_get_attachment_url($image_id);
		}

		$attributes['data-hide-360-logo'] = 'true';
		$attributes['data-image-list'] = wp_json_encode($images);
		$attributes['data-full-screen'] = 'true';

		if( get_theme_mod('wc360_drag_reverse', false) ){
			$attributes['data-spin-reverse'] = 'true';
		}

		if( get_theme_mod('wc360_autoplay', true) ){
			$attributes['data-autoplay'] = 'true';
			$attributes['data-speed'] = get_theme_mod('wc360_autoplay_speed', 250);
		}

		if( $ratio = get_theme_mod('product_gallery_preview_ratio', '') ){
			$attributes['data-ratio'] = absint($ratio);
		}

		$placeholder_img_attributes = [
			'class' => '__img',
			'loading' => 'eager',
		];

		if( \ReyCore\WooCommerce\PdpComponents\Gallery::maybe_support_lazy_loading() ){
			$placeholder_img_attributes['loading'] = '';
			$placeholder_img_attributes['src'] = '';
			$placeholder_img_attributes['data-rey-lazy-src'] = wp_get_attachment_image_url($image_ids[0], 'woocommerce_single');
		}

		$html = wp_get_attachment_image($image_ids[0], 'woocommerce_single', false, $placeholder_img_attributes);

		if( get_theme_mod('wc360_fullscreen', true) ){
			$fs_button = sprintf('<button class="__fullscreen-icon" aria-label="%2$s">%1$s</button>', reycore__get_svg_icon(['id' => 'reycore-icon-' . esc_attr(get_theme_mod('product_page_gallery__btn__icon', 'plus-stroke'))]), esc_html__('Open Fullscreen', 'rey-core'));
		}
		else {
			$attributes['data-hide-full-screen'] = 'true';
		}

		$html .= sprintf('<div class="cloudimage-360" %s>%s</div>', reycore__implode_html_attributes( $attributes ), $fs_button);

		// mobile icon overlay
		$html .= sprintf('<div class="rey-gallery-360-mobile-icon --dnone-lg">%s</div>', $this->get_icon( true ) );

		return $html;
	}


	public function gallery_params($params){

		if( ! ($images = $this->get_images()) ){
			return $params;
		}

		if( 'first' === $this->get_position() ){
			$params['start_index'] = 1;
		}

		return $params;
	}

	public static function get_icon(){
		return sprintf('<span class="rey-gallery-360-icon" data-text="%2$s">%1$s</span>', reycore__get_svg_icon(['id' => 'ico360']), esc_html__('CLICK TO OPEN', 'rey-core'));
	}

	/**
	 * Render icon inside thumbnail
	 *
	 * @param string $thumb
	 * @param int $image_id
	 * @param mixed $key
	 * @return string
	 */
	public function render_icon_inside_thumbnail($thumb, $image_id, $key){

		if( ! ($images = $this->get_images()) ){
			return $thumb;
		}

		if( self::ARRAY_KEY === $key ){
			$thumb = str_replace('<img', self::get_icon() . '<img', $thumb);
		}

		return $thumb;
	}

	/**
	 * Push image id into gallery items. Will be used by the gallery
	 * to determine the image
	 *
	 * @param array $ids
	 * @return array
	 */
	public function push_thumbnail_id_into_gallery_items( $ids ){

		if( ! ($images = $this->get_images()) ){
			return $ids;
		}

		$position = $this->get_position(); // defaults to "second"

		if( 'first' === $position ){
			$ids = [self::ARRAY_KEY => $images[0]] + $ids;
		}
		elseif( 'second' === $position ){
			$ids = array_slice($ids, 0, 1, true) + [self::ARRAY_KEY => $images[0]] + array_slice($ids, 1, null, true);
		}
		elseif( 'last' === $position ){
			$ids = $ids + [self::ARRAY_KEY => $images[0]];
		}

		return $ids;
	}


	/**
	 * Increase gallery items count (used for pagination count)
	 *
	 * @param int $count
	 * @return int
	 */
	public function increase_gallery_items_count($count){

		if( ! ($images = $this->get_images()) ){
			return $count;
		}

		return $count + 1;
	}

	public function add_classes($classes){

		if( ! ($images = $this->get_images()) ){
			return $classes;
		}

		$classes['class_360'] = '--has-360';

		// if( !$this->gallery_with_thumbs ){
		// 	$classes['class_360_first'] = '--activate-on-load';
		// }

		return $classes;
	}

	/**
	 * Prevents the plugin from starting on page load
	 *
	 * @return void
	 */
	public function prevent_start(){

		if( ! ($images = $this->get_images()) ){
			return;
		}

		echo'<script>window.CI360 = { notInitOnLoad: true }</script>';
	}

	public function quickview_dependency_assets($assets){

		if( ! ($images = $this->get_images()) ){
			return $assets;
		}

		if( in_array('reycore-wc-product-gallery', $assets['scripts'], true) ){
			$assets['scripts'][] = self::ASSET_HANDLE;
			$assets['scripts'][] = self::ASSET_HANDLE;
			// $assets['scripts'] = array_merge([self::ASSET_HANDLE], $assets['scripts']);
		}

		return $assets;
	}

	public function is_enabled() {
		return true;
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('360 Image in Product Page Gallery', 'Module name', 'rey-core'),
			'description' => esc_html_x('Adds support for rotating and draggable 360 image in product gallery', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => ['product page', 'product gallery'],
			'help'        => reycore__support_url('kb/product-settings-options/#360-image'),
			'video' => true,
		];
	}

	public function module_in_use(){

		$post_ids = get_posts([
			'post_type' => 'product',
			'numberposts' => -1,
			'post_status' => 'publish',
			'fields' => 'ids',
			'meta_query' => [
				[
					'key' => 'product_360_images',
					'value'   => '',
					'compare' => 'NOT IN'
				],
			]
		]);

		return ! empty($post_ids);

	}
}
