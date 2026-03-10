<?php
namespace ReyCore\WooCommerce\LoopComponents;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Ratings extends Component {

	public function init(){
		remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
		add_action('woocommerce_before_shop_loop_item', [$this, 'add_extended'], -1);
		add_action('woocommerce_after_shop_loop_item', [$this, 'remove_extended'], 1000);
	}

	public function status(){
		return get_theme_mod('loop_ratings', '2') != '2';
	}

	public function get_id(){
		return 'ratings';
	}

	public function get_name(){
		return 'Ratings';
	}

	public function scheme(){

		$status = get_theme_mod('loop_ratings', '2');

		$positions = [
			'1' => [
				'tag'      => 'woocommerce_shop_loop_item_title',
				'priority' => 3,
			],
			'after' => [
				'tag'      => 'woocommerce_after_shop_loop_item_title',
				'priority' => 0,
			],
		];

		if( ! isset($positions[$status]) ){
			return [
				'type'          => 'action',
				'tag'           => 'none',
				'priority'      => 0,
			];
		}

		return [
			'type'          => 'action',
			'tag'           => $positions[$status]['tag'],
			'priority'      => $positions[$status]['priority'],
		];
	}

	public function render(){

		if( ! $this->maybe_render() ){
			return;
		}

		woocommerce_template_loop_rating();
	}

	function add_extended(){
		add_filter('woocommerce_product_get_rating_html', [$this, 'extend_rating_display'], 10, 3);
	}

	function remove_extended(){
		remove_filter('woocommerce_product_get_rating_html', [$this, 'extend_rating_display'], 10, 3);
	}

	public function extend_rating_display($html, $rating, $count){

		if( ! $this->status() ){
			return $html;
		}

		$empty_html = false;
		$wrapper_html = '<div class="star-rtng-wrapper rey-loopRating">%s</div>';

		if( apply_filters('reycore/woocommerce/catalog/review_show_empty', get_theme_mod('loop_ratings_empty', false)) ){
			$empty_html  = '<div class="star-rating" role="img" aria-label="' . esc_attr(  sprintf( __( 'Rated %s out of 5', 'woocommerce' ), $rating )  ) . '">' . wc_get_star_rating_html( $rating, $count ) . '</div>';
		}

		// if not extended display, just stop here
		if( ! get_theme_mod('loop_ratings_extend', false) ){

			// if rating is 0 and empty html is set, display it
			if ( 0 == $rating && $empty_html ) {
				return sprintf( $wrapper_html, $empty_html );
			}

			return sprintf( $wrapper_html, $html );
		}

		if( ! ($product = wc_get_product()) ){
			return $html;
		}

		if( $product->get_id() === get_queried_object_id() ){
			return $html;
		}

		if ( 0 == $rating ) {

			if( $empty_html ){
				return sprintf( $wrapper_html, $empty_html );
			}

			return $html;
		}

		$count = $product->get_review_count();

		$text = apply_filters('reycore/woocommerce/catalog/review_link_text', sprintf('<small>%1$d %2$s</small>', $count, esc_html( _n( 'review', 'reviews', $count, 'rey-core' ) ) ), $product );

		if( apply_filters('reycore/woocommerce/catalog/review_link', false) ){
			return sprintf('<div class="star-rtng-wrapper rey-loopRating">%1$s<a href="%3$s#reviews" rel="nofollow">%2$s</a></div>',
				$html,
				$text,
				$product->get_permalink()
			);
		}

		return sprintf('<div class="star-rtng-wrapper rey-loopRating">%s%s</div>', $html, $text );

	}


}
