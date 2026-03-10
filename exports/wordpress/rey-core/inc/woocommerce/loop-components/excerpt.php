<?php
namespace ReyCore\WooCommerce\LoopComponents;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Excerpt extends Component {

	public function status(){
		return get_theme_mod('loop_short_desc', '2') == '1';
	}

	public function get_id(){
		return 'excerpt';
	}

	public function get_name(){
		return 'Excerpt';
	}

	public function scheme(){


		$position = get_theme_mod('loop_short_desc_position', 'before');

		$hooks = [
			'before' => [
				'hook'     => 'woocommerce_shop_loop_item_title',
				'priority' => 10,
			],
			'after' => [
				'hook'     => 'woocommerce_after_shop_loop_item_title',
				'priority' => 0,
			],
			'after_price' => [
				'hook'     => 'woocommerce_after_shop_loop_item_title',
				'priority' => 20,
			],
			'last' => [
				'hook'     => 'woocommerce_after_shop_loop_item',
				'priority' => 905,
			],
		];

		if( ! isset($hooks[ $position ]) ){
			return [];
		}

		return [
			'type'          => 'action',
			'tag'           => $hooks[ $position ]['hook'],
			'priority'      => $hooks[ $position ]['priority'],
		];
	}

	public function render(){

		if( ! $this->maybe_render() ){
			return;
		}

		global $post;

		$post_excerpt = $post->post_excerpt;

		// Compatibility with Single Variations in Catalog
		if( 'product_variation' === get_post_type() ){

			$product = wc_get_product( $post->ID );

			if( $variation_desc = $product->get_description() ){
				$post_excerpt = $variation_desc;
			}

			else {
				$post_excerpt = get_the_excerpt( wp_get_post_parent_id() );
			}

		}

		if ( ! ($excerpt = apply_filters( 'woocommerce_short_description', $post_excerpt )) ) {
			return;
		}

		$limit = absint( get_theme_mod('loop_short_desc_limit', '8') );
		$tag = 'div';
		$attributes = '';

		if( $limit ){
			$excerpt = wp_trim_words($excerpt, $limit);
			$tag = 'a';
			$attributes = sprintf('href="%s"', esc_url( get_permalink() ));
		}

		$class = '';

		if( get_theme_mod('loop_short_desc_mobile', false) ){
			$class .= ' --show-mobile';
		}

		?>
		<<?php echo $tag ?> <?php echo $attributes ?> class="woocommerce-product-details__short-description <?php echo esc_attr($class)  ?>">
			<?php echo $excerpt; // WPCS: XSS ok. ?>
		</<?php echo $tag ?>>
		<?php

	}

}
