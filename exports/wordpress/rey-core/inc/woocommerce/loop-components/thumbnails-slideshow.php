<?php
namespace ReyCore\WooCommerce\LoopComponents;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class ThumbnailsSlideshow extends ThumbnailsExtra {

	public function init(){
		add_action('woocommerce_after_shop_loop_item', [$this, 'remove_filters'], 1000);
	}

	public function status(){
		return ! (is_admin() && ! wp_doing_ajax()) &&
			get_theme_mod('loop_extra_media', 'second') === 'slideshow';
	}

	public function get_id(){
		return 'thumbnails_slideshow';
	}

	public function get_name(){
		return 'Thumbnails Slideshow';
	}

	public function scheme(){
		return [
			'type'          => 'action',
			'tag'           => 'woocommerce_before_shop_loop_item_title',
			'priority'      => 5,
		];
	}

	public function render(){

		if( ! $this->maybe_render() ){
			return;
		}

		add_filter('reycore/woocommerce/loop/wrap_thumbnails_with_link', '__return_false');
		add_filter('woocommerce_product_get_image', [$this, 'inject_slideshow'], 10, 3);

	}

	public function remove_filters(){
		remove_filter('reycore/woocommerce/loop/wrap_thumbnails_with_link', '__return_false');
		remove_filter('woocommerce_product_get_image', [$this, 'inject_slideshow'], 10, 3);
	}

	public function inject_slideshow( $html, $product, $size ){


		if ( ! ( $images = self::get_images() ) ) {
			return sprintf('<a href="%s" class=" woocommerce-LoopProduct-link woocommerce-loop-product__link">%s</a>',
				esc_url( apply_filters( 'woocommerce_loop_product_link', get_the_permalink(), $product ) ),
				$html
			);
		}

		reycore_assets()->add_scripts(['splidejs', 'rey-splide', 'reycore-wc-loop-slideshows']);
		reycore_assets()->add_styles(['rey-splide', 'rey-wc-loop-slideshow']);

		$images = array_slice($images, 0, absint(get_theme_mod('loop_slideshow_nav_max', 4)));

		$html_images = [];
		$nav_html_bullets = [];

		if( array_key_exists('main', $images) ){
			$images = array_values($images);
		}

		foreach ($images as $key => $img) {

			$html_images[] = sprintf('<a href="%s" class="splide__slide">%s</a>',
				esc_url( apply_filters( 'woocommerce_loop_product_link', get_the_permalink(), $product ) ),
				reycore__get_picture([
					'id' => $img,
					'size' => $size,
					'class' => 'rey-productThumbnail-extra',
					'disable_mobile' => ! self::loop_extra_media_mobile() && $key !== 0,
					// 'lazy_attribute' => $key !== 0 ? 'data-slideshow-lazy' : false,
					'lazy_attribute' => false,
				])
			);

			$nav_html_bullets[] = sprintf('<button data-go="%1$d" aria-label="%2$s %1$d"><span></span></button>', $key, esc_html__('Go to slide ', 'rey-core'));

		}

		$slider_attributes = [];

		// Start Nav
		$nav_type = get_theme_mod('loop_slideshow_nav', 'dots');
		$nav_html = '';

		// Bullets
		if( $nav_type === 'dots' || $nav_type === 'both' ){

			// pagination id
			$pagination_id = '__pagination-' . $product->get_id();
			// pass it to slider
			$slider_attributes['data-pagination-id'] = $pagination_id;

			$classes[] = $pagination_id;

			if( $bullets_style = get_theme_mod('loop_slideshow_nav_dots_style', 'bars') ){
				$classes[] = '--bullets-style-' . $bullets_style;
			}

			if( ! self::loop_extra_media_mobile() ){
				$classes[] = '--hide-mobile';
			}

			$nav_html .= sprintf('<div class="rey-productSlideshow-dots %1$s" data-position="%3$s">%2$s</div>',
				implode(' ', $classes),
				implode('', $nav_html_bullets),
				apply_filters('reycore/woocommerce/loop/thumbnail_slideshow/dots_position', '')
			);

		}

		// Arrows
		if( $nav_type === 'arrows' || $nav_type === 'both' ){
			// arrows nav id
			$arrows_id = '__arrows-' . $product->get_id();
			// add as attribute
			$slider_attributes['data-arrows-id'] = $arrows_id;
			// nav markup
			$nav_html .= sprintf('<div class="rey-productSlideshow-arrows %s">%s</div>',
				$arrows_id,
				reycore__arrowSvg([ 'right' => false, 'attributes' => 'data-dir="<"' ]) .
				reycore__arrowSvg([ 'right' => true, 'attributes' => 'data-dir=">"' ])
			);
		}

		$css_classes[] = (get_theme_mod('loop_slideshow_nav_color_invert', false) ? '--color-invert' : '');

		if( ! in_array( get_theme_mod('loop_grid_layout', 'default'), ['masonry', 'masonry2'], true ) ){

			if( get_theme_mod('loop_slideshow_nav', 'dots') !== 'arrows' && get_theme_mod('loop_slideshow_nav_hover_dots', false) ){
				$css_classes[] = '--dots-hover';
			}

			if( get_theme_mod('loop_slideshow_hover_slide', true) ){
				$css_classes[] = '--slide-hover';
			}

		}

		$slider_markup = sprintf(
			'<div class="splide %s" %s>',
			implode(' ', $css_classes),
			reycore__implode_html_attributes($slider_attributes)
		);

		$slider_markup .= '<div class="splide__track">';
		$slider_markup .= sprintf('<div class="rey-productSlideshow splide__list">%s</div>', implode('', $html_images) );
		$slider_markup .= $nav_html;
		$slider_markup .= '</div></div>';

		return $slider_markup;

	}

}
