<?php
namespace ReyCore\Modules\ProductVideo;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	const ASSET_HANDLE = 'reycore-module-product-video';

	public function __construct()
	{
		add_action( 'reycore/woocommerce/init', [$this, 'woo_init']);
		add_action( 'reycore/templates/register_widgets', [$this, 'register_widgets']);
	}

	public function register_widgets($widgets_manager){
		$widgets_manager->register( new Element );
	}

	public function woo_init(){

		new AcfFields();
		new Customizer();

		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);

		add_action( 'reycore/woocommerce/pdp/gallery/after_image', [ $this, 'render_button_over_gallery' ] );
		add_action( 'woocommerce_single_product_summary', [ $this, 'setup_button_in_summary' ], 0 );

		add_filter( 'reycore/woocommerce/pdp/gallery/items_count', [$this, 'increase_gallery_items_count']);
		add_action( 'reycore/woocommerce/pdp/gallery/thumbnail_items', [$this, 'push_thumbnail_id_into_gallery_items']);
		add_action( 'reycore/woocommerce/product_mobile_gallery/slide_html', [$this, 'render_video_icon_inside_thumbnail'], 10, 3);
		add_filter( 'reycore/woocommerce/product_image/params', [$this, 'gallery_params'], 10 );

		add_action( 'reycore/woocommerce_single_product_image/before', [$this, 'first__render_inside_gallery'], 5);

		add_action( 'woocommerce_product_thumbnails', [$this, 'second__render_inside_gallery'], 0);
		add_action( 'woocommerce_product_thumbnails', [$this, 'last__render_inside_gallery'], 100);

		add_action('reycore/module/quickview/panel_content', [$this, 'setup_video_holder_for_photoswipe']);

	}

	public function register_assets($assets){

		$assets->register_asset('styles', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/style.css',
				'deps'    => ['woocommerce-general'],
				'version'   => REY_CORE_VERSION,
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
	 * Get video url
	 *
	 * @return string|null
	 */
	public function get_video_url(){

		static $video_url;

		if( is_null($video_url) ){
			if( $product_id = $this->get_product_id() ){
				if( $_url = reycore__acf_get_field('product_video_url', $product_id ) ){
					$video_url = esc_url( $_url );
				}
			}
		}

		return $video_url;
	}

	/**
	 * Check if button over main image
	 *
	 * @return boolean
	 */
	public function is_button_over_image(){

		static $button_over_main_image;

		if( is_null($button_over_main_image) ){
			if( $product_id = $this->get_product_id() ){
				$button_over_main_image = reycore__acf_get_field('product_video_main_image', $product_id );
			}
		}

		return $button_over_main_image;
	}

	/**
	 * Check if video shows inline (not as an image)
	 *
	 * @return boolean
	 */
	public function maybe_show_inline(){

		static $maybe_show_inline;

		if( is_null($maybe_show_inline) ){
			if( $product_id = $this->get_product_id() ){
				$maybe_show_inline = reycore__acf_get_field('product_video_inline', $product_id );
			}
		}

		return $maybe_show_inline;
	}


	/**
	 * Get video image ID
	 *
	 * @return int
	 */
	public function get_video_image_id(){

		static $image_id;

		if( is_null($image_id) ){
			if( $product_id = $this->get_product_id() ){
				$image_id = (($image = reycore__acf_get_field('product_video_gallery_image', $product_id )) && isset($image['id'])) ? absint($image['id']) : 0;

			}
		}

		return $image_id;
	}

	/**
	 * Move video first in gallery
	 *
	 * @return boolean
	 * @deprecated 3.1.6
	 */
	public static function is_first(){}

	/**
	 * Get video position
	 *
	 * @return string
	 * @since 3.1.6
	 */
	public static function get_position(){

		if( apply_filters('reycore/woocommerce/video/is_first', false) ){
			return 'first';
		}

		return get_theme_mod('product_gallery_video_position', 'last');
	}

	public function gallery_params($params){

		if( ! $this->get_video_url() ){
			return $params;
		}

		$position = self::get_position();

		if( $position === 'first' ){
			$params['start_index'] = 1;
		}
		else if( $position === 'second' ){
			$params['start_index'] = 2;
		}

		return $params;
	}

	/**
	 * Render video icon inside video thumbnail
	 *
	 * @param string $thumb
	 * @param int $image_id
	 * @param mixed $key
	 * @return string
	 */
	public function render_video_icon_inside_thumbnail($thumb, $image_id, $key){

		if( ! $this->get_video_url() ){
			return $thumb;
		}

		if( $this->is_button_over_image() ){
			return $thumb;
		}

		if( 'video' === $key ){
			$video_icon = sprintf('<span class="rey-galleryPlayVideo-icon">%s</span>', reycore__get_svg_icon(['id' => 'play']));
			$thumb = str_replace('<img', $video_icon . '<img', $thumb);
		}

		return $thumb;
	}

	/**
	 * Push video image id into gallery items. Will be used by the gallery
	 * to determine the image
	 *
	 * @param array $ids
	 * @return array
	 */
	public function push_thumbnail_id_into_gallery_items( $ids ){

		if( ! $this->get_video_url() ){
			return $ids;
		}

		if( $this->is_button_over_image() ){
			return $ids;
		}

		if( $image_id = $this->get_video_image_id() ){
			$ids['video'] = $image_id;
		}
		else {
			$ids['video'] = false;
		}

		$position = self::get_position();

		// add video image id as first item
		if( $position === 'first' ){
			$ids = array_merge(['video' => $ids['video']], $ids);
		}
		// add video image id as second item
		else if( $position === 'second' ){
			$ids = array_merge(array_slice($ids, 0, 1), ['video' => $ids['video']], array_slice($ids, 1));
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

		if( ! $this->get_video_url() ){
			return $count;
		}

		if( $this->is_button_over_image() ){
			return $count;
		}

		return $count + 1;
	}

	/**
	 * Render gallery item, as first
	 *
	 * @return void
	 */
	public function first__render_inside_gallery(){
		if( self::get_position() === 'first' ){
			$this->render_inside_gallery();
		}
	}

	/**
	 * Render gallery item, as last (default)
	 *
	 * @return void
	 */
	public function second__render_inside_gallery(){
		if( self::get_position() === 'second' ){
			$this->render_inside_gallery();
		}
	}

	/**
	 * Render gallery item, as last (default)
	 *
	 * @return void
	 */
	public function last__render_inside_gallery(){
		if( self::get_position() === 'last' ){
			$this->render_inside_gallery();
		}
	}

	/**
	 * Render gallery item
	 *
	 * @return void
	 */
	public function render_inside_gallery(){

		if( ! $this->get_video_url() ){
			return;
		}

		if( $this->is_button_over_image() ){
			return;
		}

		reycore_assets()->add_styles(self::ASSET_HANDLE);

		$attributes = [
			'class' => '__img no-lazy --gallery-img',
			'data-no-lazy' => 1,
			'data-skip-lazy' => 1,
		];

		$video_poster = '';
		$video_poster_html = '';

		if( $image_id = $this->get_video_image_id() ){
			$image_url = wp_get_attachment_image_url($image_id, 'woocommerce_single');
			$video_poster = $image_url;
			if( \ReyCore\WooCommerce\PdpComponents\Gallery::maybe_support_lazy_loading() ){
				$attributes['data-rey-lazy-src'] = $image_url;
				$attributes['src'] = '';
			}
			$image = wp_get_attachment_image($image_id, 'large', false, $attributes);
			$video_poster_html = str_replace('__img', '__img --skipped', $image);
		}
		else {
			$image = wc_placeholder_img('woocommerce_single', $attributes);
		}

		if( $this->maybe_show_inline() ){

			$css_class = '--inline-video';

			$styles = ( $ratio = reycore__acf_get_field('product_video_modal_ratio', $this->get_product_id() ) ) ?
					'--custom-video-ratio:' . reycore__clean($ratio) . '%' : '';

			$content = \ReyCore\Helper::get_embed_video(
				apply_filters( 'reycore/woocommerce/video_embed_args', [
					'url'         => $this->get_video_url(),
					'style'       => $styles,
					'id'          => 'pdp-product-video',
					'class_video' => '__video',
					'attribute'   => self::get_position() === 'first' ? 'src': 'data-video',
					'poster'      => $video_poster,
					'poster_html' => $video_poster_html,
				], $this )
			);


		}

		// image only
		else {

			$css_class = '--video-button';

			// button over image
			$content = $this->print_button([
				'text' => $image,
				'icon' => '<span class="rey-galleryPlayVideo-icon">'. reycore__get_svg_icon(['id' => 'play']) .'</span>',
				'tag' => 'a',
				'attr' => [
					'href' => '#',
					'class' => 'rey-galleryPlayVideo',
				],
				'modal' => false,
				'echo' => false,
			]);

		}

		$html = sprintf('<div class="woocommerce-product-gallery__image %s" data-no-zoom data-html-lightbox="lightbox-videoHolder" data-html-lightbox-type="video" data-do-click >%s</div>', $css_class, $content);

		echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', $html, ($image_id ? $image_id : 0) ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped


	}

	public function render_button_over_gallery(){

		if( ! $this->get_video_url() ){
			return;
		}

		if( ! $this->is_button_over_image() ){
			return;
		}

		reycore_assets()->add_styles(self::ASSET_HANDLE);

		$this->print_button([
			'text' => '',
			'attr' => [
				'class' => 'rey-singlePlayVideo btn',
			],
		]);

	}

	public function render_button_in_summary(){

		reycore_assets()->add_styles(self::ASSET_HANDLE);

		$this->print_button([
			'wrap' => true,
			'attr' => [
				'class' => 'rey-singlePlayVideo-summary btn btn-line u-btn-icon-sm',
			],
		]);

	}

	public function print_button( $args = [] ){

		if( ! ($video_url = $this->get_video_url()) ){
			return;
		}

		$text_ = apply_filters( 'reycore/woocommerce/video/link_text', esc_html__('PLAY PRODUCT VIDEO', 'rey-core') );

		if( $custom_text = reycore__acf_get_field('product_video_link_text', $this->get_product_id() ) ){
			$text_ = $custom_text;
		}

		if( isset($args['custom_text']) ){
			$text_ = $args['custom_text'];
		}

		reycore_assets()->add_styles('rey-buttons');

		$args = wp_parse_args($args, [
			'text' => $text_,
			'icon' => reycore__get_svg_icon(['id' => 'play']),
			'tag' => 'button',
			'attr' => [
				'title' => $text_,
				'class' => 'btn btn-line u-btn-icon-sm',
				'data-elementor-open-lightbox' => 'no',
				'role' => 'button',
				'aria-label' => $text_,
			],
			'wrap' => false,
			'modal' => true,
			'echo' => true
		]);

		add_action('wp_footer', [$this, 'setup_video_holder_for_photoswipe']);

		$button = apply_filters( 'reycore/woocommerce/video_button', sprintf('<%3$s %4$s>%2$s %1$s</%3$s>',
			$args['text'],
			$args['icon'],
			$args['tag'],
			reycore__implode_html_attributes($args['attr'])
		, $args ));

		$print_before = $print_after = '';

		if( $args['wrap'] ){
			$print_before = '<div class="rey-singlePlayVideo-wrapper">';
			$print_after = '</div>';
		}

		if( $args['echo'] ){
			echo $print_before . $button . $print_after;
		}
		else {
			return $print_before . $button . $print_after;
		}
	}

	public function setup_button_in_summary(){

		// Button inside summary, in a specific position
		if( ! ($summary_position = reycore__acf_get_field('product_video_summary', $this->get_product_id() )) ) {
			return;
		}

		if( $summary_position === 'disabled' ){
			return;
		}

		$available_positions = [
			'after_title'         => 7,
			'before_add_to_cart'  => 29,
			'before_product_meta' => 39,
			'after_product_meta'  => 41,
			'after_share'         => 51,
		];

		if( empty($available_positions[$summary_position]) ){
			return;
		}

		add_action( 'woocommerce_single_product_summary', [ $this, 'render_button_in_summary' ], $available_positions[$summary_position] );

	}

	/**
	 * Create HTML markup for the video, when opened in PhotoSwipe
	 *
	 * @return void
	 */
	public function setup_video_holder_for_photoswipe(){

		if( ! ($video_url = $this->get_video_url()) ){
			return;
		}

		$styles = '';

		if( $ratio = reycore__acf_get_field('product_video_modal_ratio', $this->get_product_id() ) ){
			$_ratio = reycore__clean($ratio);
			$styles = sprintf('--custom-video-ratio:%s%%; --custom-video-ratio-ar:%s; max-width: min(80vw, calc(90vh / var(--custom-video-ratio-ar)));', $_ratio, ($_ratio / 100));
		}

		printf('<div id="lightbox-videoHolder" class="--hidden">%s</div>',
			\ReyCore\Helper::get_embed_video(
				apply_filters( 'reycore/woocommerce/video_embed_args', [
					'url'         => $video_url,
					'style'       => $styles,
					'id'          => 'pdp-product-video-lightbox',
					'class_video' => '__lightbox-video',
					'attribute'   => 'data-video',
 				], $this )
			)
		);
	}

	public function is_enabled() {
		return true;
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Product Video', 'Module name', 'rey-core'),
			'description' => esc_html_x('Adds support for video link or popup for products, in product page or gallery.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => ['product page'],
			'help'        => reycore__support_url('kb/product-settings-options/#video'),
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
					'key' => 'product_video_url',
					'value'   => '',
					'compare' => 'NOT IN'
				]
			]
		]);

		return ! empty($post_ids);
	}
}
