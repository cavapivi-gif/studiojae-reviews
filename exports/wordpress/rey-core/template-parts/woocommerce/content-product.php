<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to themes/rey-child/rey-core/woocommerce/content-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

do_action('reycore/woocommerce/content_product/before', $product);

if ( ! apply_filters('reycore/woocommerce/content_product/render', true, $product ) ) {
	return;
}

// Ensure visibility.
if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}

$attributes = apply_filters('reycore/woocommerce/content_product/attributes', [
	'class' => implode( ' ', wc_get_product_class( '', $product ) ),
	'data-pid' => absint( $product->get_id() ),
], $product); ?>

<li <?php echo reycore__implode_html_attributes($attributes); ?>>

	<?php

	ob_start();

		/**
		 * Hook: reycore/woocommerce/content_product/custom
		 *
		 * Override the inner contents of the product.
		 */
		do_action('reycore/woocommerce/content_product/custom', $product, $attributes);

	// Render custom markup
	if( $product_markup = ob_get_clean() ){
		echo $product_markup;
	}

	// Render default WooCommerce product hooks
	else {

		/**
		 * Hook: woocommerce_before_shop_loop_item.
		 *
		 * @hooked woocommerce_template_loop_product_link_open - 10
		 */
		do_action( 'woocommerce_before_shop_loop_item' );

		/**
		 * Hook: woocommerce_before_shop_loop_item_title.
		 *
		 * @hooked woocommerce_show_product_loop_sale_flash - 10
		 * @hooked woocommerce_template_loop_product_thumbnail - 10
		 */
		do_action( 'woocommerce_before_shop_loop_item_title' );

		/**
		 * Hook: woocommerce_shop_loop_item_title.
		 *
		 * @hooked woocommerce_template_loop_product_title - 10
		 */
		do_action( 'woocommerce_shop_loop_item_title' );

		/**
		 * Hook: woocommerce_after_shop_loop_item_title.
		 *
		 * @hooked woocommerce_template_loop_rating - 5
		 * @hooked woocommerce_template_loop_price - 10
		 */
		do_action( 'woocommerce_after_shop_loop_item_title' );

		/**
		 * Hook: woocommerce_after_shop_loop_item.
		 *
		 * @hooked woocommerce_template_loop_product_link_close - 5
		 * @hooked woocommerce_template_loop_add_to_cart - 10
		 */
		do_action( 'woocommerce_after_shop_loop_item' );

	} ?>

</li>

<?php
do_action('reycore/woocommerce/content_product/after', $product);
