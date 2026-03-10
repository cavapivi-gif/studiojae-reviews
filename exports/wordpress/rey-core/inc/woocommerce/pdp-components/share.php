<?php
namespace ReyCore\WooCommerce\PdpComponents;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Share extends Component {

	public function init(){
		add_action( 'woocommerce_share', [$this, 'output']);
	}

	public function get_id(){
		return 'share';
	}

	public function get_name(){
		return 'Share';
	}

	public function output( $args = [] ){

		if( ! $this->maybe_render() ){
			return;
		}

		if( ! function_exists('reycore__socialShare') ){
			return;
		}

		$args = wp_parse_args($args, [
			'title' => esc_html__('SHARE', 'rey-core'),
			'custom_classes' => []
		]);

		$classes = apply_filters('reycore/woocommerce/product_page/share/classes', $args['custom_classes']);

		// Sharing
		if( get_theme_mod('product_share', '1') != '1' ){
			return;
		}

		reycore_assets()->add_styles('rey-wc-product-share');

		printf('<div class="rey-productShare %s" data-lazy-hidden>', esc_attr(implode(' ', $classes)));

			echo '<div class="rey-productShare-inner">';

			if( $title = $args['title'] ){
				$title_tag = apply_filters('reycore/woocommerce/product_page/share/title_tag', 'div');
				printf('<%1$s class="rey-productShare-title">%2$s</%1$s>', $title_tag, $title);
			}

			$share_icons = get_theme_mod('product_share_icons', [
				[
					'social_icon' => 'twitter',
				],
				[
					'social_icon' => 'facebook-f',
				],
				[
					'social_icon' => 'linkedin',
				],
				[
					'social_icon' => 'pinterest-p',
				],
				[
					'social_icon' => 'mail',
				],
				[
					'social_icon' => 'copy',
				],
			]);

			reycore__socialShare([
				'share_items' => wp_list_pluck($share_icons, 'social_icon'),
				'colored' => get_theme_mod('product_share_icons_colored', false)
			]);

			echo '</div>';
		echo '</div>';

	}
}
