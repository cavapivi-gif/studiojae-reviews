<?php
namespace ReyCore\WooCommerce\LoopComponents;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class NewBadge extends Component {

	public function status(){
		return get_theme_mod('loop_new_badge', '1')  === '1';
	}

	public function get_id(){
		return 'new_badge';
	}

	public function get_name(){
		return 'New Badge';
	}

	public function scheme(){

		return [
			'type'          => 'action',
			'tag'           => 'reycore/loop_inside_thumbnail/bottom-left',
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

		$postdate      = get_the_time( 'Y-m-d' ); // Post date
		$postdatestamp = strtotime( $postdate );  // Timestamped post date
		$newness       = apply_filters('reycore/woocommerce/loop/new_badge/newness', 30); // Newness in days

		if( $custom_newness = get_theme_mod('loop_new_badge_duration', '') ){
			$newness = $custom_newness;
		}

		if ( ( time() - ( 60 * 60 * 24 * $newness ) ) < $postdatestamp ) {

			$text = apply_filters('reycore/woocommerce/loop/new_text', esc_html__( 'NEW', 'rey-core' ) );

			if( $custom_text = get_theme_mod('loop_new_badge_text', '') ){
				$text = $custom_text;
			}

			$css = '';

			if( $custom_css = get_theme_mod('loop_new_badge_css', '') ){
				$css = esc_attr($custom_css);
			}

			printf('<div class="rey-itemBadge rey-new-badge" style="%2$s">%1$s</div>', $text, $css );
		}

	}

}
