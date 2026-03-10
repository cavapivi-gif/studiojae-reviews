<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

reycore_assets()->register_assets();

$uses_cover = false;

// cover published
if( function_exists('rey_action__after_header_outside') && class_exists('\ReyCore\Elementor\TagCover') && \ReyCore\Elementor\TagCover::instance()->get_cover_id() ){
	rey_action__after_header_outside();
	$uses_cover = true;
}

if( function_exists('rey__main_content_wrapper_start') ){
	rey__main_content_wrapper_start();
}

if( ! $uses_cover){

	reycore__woocommerce_breadcrumb();

	?>
	<header class="woocommerce-products-header">

		<?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
			<h1 class="woocommerce-products-header__title page-title"><?php woocommerce_page_title(); ?></h1>
		<?php endif; ?>

		<?php
		/**
		 * Hook: woocommerce_archive_description.
		 *
		 * @hooked woocommerce_taxonomy_archive_description - 10
		 * @hooked woocommerce_product_archive_description - 10
		 */
		do_action( 'woocommerce_archive_description' );
		?>

	</header><?php
}

if ( woocommerce_product_loop() ) {

	reycore_assets()->collect_start('woocommerce_product_loop_start');

	/**
	 * Hook: woocommerce_before_shop_loop.
	 *
	 * @hooked woocommerce_output_all_notices - 10
	 * @hooked woocommerce_result_count - 20
	 * @hooked woocommerce_catalog_ordering - 30
	 */
	do_action( 'woocommerce_before_shop_loop' );

	woocommerce_product_loop_start();

	if ( wc_get_loop_prop( 'total' ) ) {
		while ( have_posts() ) {
			the_post();

			/**
			 * Hook: woocommerce_shop_loop.
			 */
			do_action( 'woocommerce_shop_loop' );

			wc_get_template_part( 'content', 'product' );
		}
	}

	$collected = reycore_assets()->collect_end('woocommerce_product_loop_start', true);

	if( ! empty($collected) ){
		printf('<div data-loop-assets=\'%s\' class="--hidden"></div>', wp_json_encode($collected));
	}

	woocommerce_product_loop_end();

	/**
	 * Hook: woocommerce_after_shop_loop.
	 *
	 * @hooked woocommerce_pagination - 10
	 */
	do_action( 'woocommerce_after_shop_loop' );


} else {
	/**
	 * Hook: woocommerce_no_products_found.
	 *
	 * @hooked wc_no_products_found - 10
	 */
	do_action( 'woocommerce_no_products_found' );
}

do_action( 'woocommerce_after_main_content' );

if( function_exists('rey__main_content_wrapper_end') ){
	rey__main_content_wrapper_end();
}

if( function_exists('reycore_wc__check_filter_panel') && reycore_wc__check_filter_panel() ) {
	reycore__get_template_part('template-parts/woocommerce/filter-panel-sidebar');
}
