<?php
namespace ReyCore\WooCommerce\LoopComponents;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class FeaturedBadge extends Component {

	public function status(){
		return get_theme_mod('loop_featured_badge', 'hide') !== 'hide';
	}

	public function get_id(){
		return 'featured_badge';
	}

	public function get_name(){
		return 'Featured Badge';
	}

	public function scheme(){
		return [
			'type'          => 'action',
			'tag'           => 'reycore/loop_inside_thumbnail/top-left',
			'priority'      => 10,
		];
	}

	/**
	 * Item Component - NEW badge to product entry for any product added in the last 30 days.
	*
	* @since 1.0.0
	*/
	public function render() {

		if( ! $this->maybe_render() ){
			return;
		}

		if( ($product = wc_get_product()) && get_theme_mod('loop_featured_badge', 'hide') === 'show' && $product->is_featured() ){
			printf('<div class="rey-itemBadge rey-featured-badge">%s</div>', get_theme_mod('loop_featured_badge__text', esc_html__('FEATURED', 'rey-core')) );
		}

	}

}
