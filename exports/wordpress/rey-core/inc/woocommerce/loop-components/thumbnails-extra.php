<?php
namespace ReyCore\WooCommerce\LoopComponents;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class ThumbnailsExtra extends Component {

	public function get_id(){}

	public function get_name(){}

	public static function get_images( $add_main = true ){
		return ($images = reycore_wc__get_product_images_ids($add_main)) && count($images) > 1 ? $images : [];
	}

	public function css_classes(){

		$classes['extra-media'] = '--extraImg-' . get_theme_mod('loop_extra_media', 'second');
		$classes['extra-media-mobile'] = '--extraImg-mobile' . ( ! self::loop_extra_media_mobile() ? '-disabled' : '');

		return $classes;
	}

	/**
	 * Handle mobile extra media.
	 * `wp_is_mobile` could've been used, but if a page is cached,
	 * it would stop showing for one of the versions, since there aren't separate cached versions.
	 *
	 * @return bool
	 * @since 2.0.0
	 */
	public static function loop_extra_media_mobile(){

		$default = false;

		if( get_theme_mod('loop_extra_media', 'second') === 'slideshow' ){
			$default = get_theme_mod('loop_slideshow_disable_mobile', false); // legacy option
		}

		$disabled = get_theme_mod('loop_extra_media_disable_mobile', $default);

		return ! $disabled;
	}

}
