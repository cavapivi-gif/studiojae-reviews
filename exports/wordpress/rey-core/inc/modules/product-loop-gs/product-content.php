<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
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

// Ensure visibility.
if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}

$attributes = apply_filters('reycore/woocommerce/content_product/attributes', [
	'class' => implode( ' ', wc_get_product_class( 'rey-pLoop-item', $product ) ),
	'data-pid' => absint( $product->get_id() ),
], $product); ?>

<li <?php echo reycore__implode_html_attributes($attributes); ?>>

	<?php
		/**
		 * Hook: reycore/woocommerce/content_product/custom
		 *
		 * Override the inner contents of the product.
		 */
		do_action('reycore/woocommerce/content_product/custom', $product, $attributes);
	?>

</li>

<?php
do_action('reycore/woocommerce/content_product/after', $product);
