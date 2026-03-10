<?php
/**
 * Single Product Image
 *
 * This template can be overridden by copying it to themes/rey-child/rey-core/woocommerce/single-product-image.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.7.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if( ! apply_filters('reycore/woocommerce/allow_product_page_gallery', true, $product) ){
	return;
}

reycore_assets()->add_scripts(['reycore-wc-product-gallery']);

do_action( 'reycore/woocommerce/product_image/before_gallery' );

$columns           = apply_filters( 'woocommerce_product_thumbnails_columns', 4 );

$post_thumbnail_id = apply_filters( 'reycore/woocommerce/product_image/main_image_id',  $product ? $product->get_image_id() : 0 );

$wrapper_classes   = apply_filters(
	'woocommerce_single_product_image_gallery_classes',
	[
		'woocommerce-product-gallery',
		'woocommerce-product-gallery--' . ( $post_thumbnail_id ? 'with-images' : 'without-images' ),
		'images',
	],
	$product
); ?>

<div
	class="<?php echo esc_attr( implode( ' ', array_map( 'sanitize_html_class', $wrapper_classes ) ) ); ?>"
	data-columns="<?php echo esc_attr( $columns ); ?>"
	data-params='<?php echo wp_json_encode( apply_filters('reycore/woocommerce/product_image/params', []) ); ?>'
>

	<?php
	do_action( 'reycore/woocommerce/product_image/before_gallery_wrapper' ); ?>

	<figure class="woocommerce-product-gallery__wrapper">

		<?php
		do_action( 'reycore/woocommerce_single_product_image/before' );

		if ( $post_thumbnail_id ) {
			$html = wc_get_gallery_image_html( $post_thumbnail_id, true );
		} else {
			$html = sprintf( '<div class="woocommerce-product-gallery__image woocommerce-product-gallery__image--placeholder"><img src="%s" alt="%s" class="wp-post-image" /></div>',
				esc_url( wc_placeholder_img_src( 'woocommerce_single' ) ),
				esc_html__( 'Awaiting product image', 'woocommerce' )
			);
		}

		echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', $html, $post_thumbnail_id ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped

		do_action( 'woocommerce_product_thumbnails' ); ?>

	</figure>

	<?php
	do_action( 'reycore/woocommerce/product_image/after_gallery_wrapper' ); ?>

</div>

<?php
do_action( 'reycore/woocommerce/product_image/after_gallery' ); ?>
