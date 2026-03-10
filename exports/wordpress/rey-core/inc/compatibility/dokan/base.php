<?php
namespace ReyCore\Compatibility\Dokan;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Compatibility\CompatibilityBase {

	public function __construct() {

		$this->includes();

		add_filter( 'dokan_shortcodes', [$this, 'override_shortcodes']);
		add_filter( 'body_class', [ $this, 'add_wc_class' ] );
		add_filter( 'woocommerce_product_tabs', [$this, 'override_tabs'], 20 );
		add_action( 'woocommerce_after_single_product_summary', [$this, 'dokan_get_more_products_from_seller'], 10);

	}

	function includes(){
		require_once __DIR__ . '/BestSellingProduct.php';
		require_once __DIR__ . '/TopRatedProduct.php';
	}

	function override_shortcodes( $shortcodes ){

		$shortcodes['dokan-best-selling-product'] = new BestSellingProduct();
		$shortcodes['dokan-top-rated-product'] = new TopRatedProduct();

		return $shortcodes;
	}

	public function add_wc_class( $classes ) {

		if ( dokan_is_store_page() || dokan_is_store_listing() ) {
			if ( ! in_array( 'woocommerce', $classes ) ) {
				$classes[] = 'woocommerce';
			}
		}

		return $classes;
	}

	function override_tabs( $tabs ) {

		unset($tabs['more_seller_product']);

		if( isset($tabs['seller']) ){
			$tabs['seller']['priority'] = 25;
		}

		return $tabs;
	}


	function dokan_get_more_products_from_seller() {

		if ( ! check_more_seller_product_tab() ) {
			return;
		}

		add_filter('reycore/woocommerce/loop_components/disable_grid_components', '__return_true');

		global $product, $post;

		$posts_per_page = apply_filters( 'dokan_get_more_products_per_page', 6 );

		$args = [
			'post_type'      => 'product',
			'posts_per_page' => $posts_per_page,
			'orderby'        => 'rand',
			'post__not_in'   => [ $post->ID ],
			'author'         => $post->post_author,
		];

		$products = new \WP_Query( $args );

		if ( $products->have_posts() ) {

			echo '<section class="rey-extra-products dokan-more-products">';

			echo sprintf('<h2>%s</h2>', __( 'More Products', 'dokan-lite' ));

			do_action( 'woocommerce_before_shop_loop' );

			woocommerce_product_loop_start();

			while ( $products->have_posts() ) {
				$products->the_post();
				wc_get_template_part( 'content', 'product' );
			}

			woocommerce_product_loop_end();

			do_action( 'woocommerce_after_shop_loop' );

			echo '</section>';

		} else {
			esc_html_e( 'No product has been found!', 'dokan-lite' );
		}

		wp_reset_postdata();

		remove_filter('reycore/woocommerce/loop_components/disable_grid_components', '__return_true');

	}

}
