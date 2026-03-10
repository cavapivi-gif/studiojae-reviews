<?php
namespace ReyCore\WooCommerce\PdpComponents;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class ShortDesc extends Component {

	public function init(){

		if( ! get_theme_mod('product_short_desc_enabled', true) ){
			add_filter( 'woocommerce_short_description', '__return_empty_string');
			return;
		}

		add_filter( 'woocommerce_short_description', [$this, 'add_excerpt_toggle']);

		if( apply_filters( 'reycore/woocommerce/short_desc/can_reposition', get_theme_mod('product_short_desc_after_atc', false)) ){
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
			add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 35 );
		}

	}

	public function get_id(){
		return 'short_desc';
	}

	public function get_name(){
		return 'Short description';
	}


	/**
	 * Creates a read more / less toggle for short description
	 *
	 * @since 1.0.0
	 */
	function add_excerpt_toggle( $excerpt ){

		$short_desc_toggle = get_theme_mod('product_short_desc_toggle_v2', false);

		if( ! $short_desc_toggle || (!is_single() && !get_query_var('rey__is_quickview', false)) ){
			return $excerpt;
		}

		$stript_tags = get_theme_mod('product_short_desc_toggle_strip_tags', true);

		if( $stript_tags ){

			$intro = wp_strip_all_tags($excerpt);
			$limit = 50;

			if ( strlen($intro) > $limit) {

				$full_content = $excerpt;
				// truncate string
				$excerptCut = substr($intro, 0, $limit);
				$endPoint = strrpos($excerptCut, ' ');

				reycore_assets()->add_styles(['rey-buttons', 'reycore-text-toggle']);
				reycore_assets()->add_scripts('reycore-text-toggle');

				$excerpt = '<div class="u-toggle-text --collapsed">';
					$excerpt .= '<div class="u-toggle-content">';
					$excerpt .= $intro;
					$excerpt .= '</div>';
					$excerpt .= '<button aria-label="Toggle" class="btn u-toggle-btn" data-read-more="'. esc_html_x('Read more', 'Toggling the product excerpt.', 'rey-core') .'" data-read-less="'. esc_html_x('Less', 'Toggling the product excerpt.', 'rey-core') .'"></button>';
				$excerpt .= '</div>';

				return $excerpt;
			}
		}
		// keep tags
		else{
			$full_content = $excerpt;
			if( $full_content ):

				reycore_assets()->add_styles(['rey-buttons', 'reycore-text-toggle']);
				reycore_assets()->add_scripts('reycore-text-toggle');

				$excerpt = '<div class="u-toggle-text-next-btn --short">';
				$excerpt .= $full_content;
				$excerpt .= '</div>';
				$excerpt .= '<button class="btn btn-minimal" aria-label="Toggle"><span data-read-more="'. esc_html_x('Read more', 'Toggling the product excerpt.', 'rey-core') .'" data-read-less="'. esc_html_x('Less', 'Toggling the product excerpt.', 'rey-core') .'"></span></button>';
			endif;
		}

		remove_filter( 'woocommerce_short_description', [$this, 'add_excerpt_toggle']);

		return $excerpt;
	}
}
