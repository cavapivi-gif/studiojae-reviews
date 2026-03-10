<?php
namespace ReyCore\Compatibility\Dokan;

use WeDevs\Dokan\Abstracts\DokanShortcode;

class TopRatedProduct extends DokanShortcode {
    protected $shortcode = 'dokan-top-rated-product';

    /**
     * Render top rated products via shortcode
     *
     * @param array $atts
     *
     * @return string
     */
    public function render_shortcode( $atts ) {

        /**
         * Filter return the number of top rated product per page.
         *
         * @since 2.2
         *
         * @param array
         */
        $per_page = shortcode_atts(
            apply_filters(
                'dokan_top_rated_product_per_page',
                [
                    'no_of_product' => 8,
                ],
                $atts
            ),
            $atts
        );

        ob_start();

		do_action( 'woocommerce_before_shop_loop' );

        woocommerce_product_loop_start();

            $best_selling_query = dokan_get_top_rated_products();

            while ( $best_selling_query->have_posts() ) {
                $best_selling_query->the_post();

                wc_get_template_part( 'content', 'product' );
            }

			wp_reset_postdata();

		woocommerce_product_loop_end();

		do_action( 'woocommerce_after_shop_loop' );

        return ob_get_clean();
    }
}
