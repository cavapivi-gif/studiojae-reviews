<?php
/**
 * Single Product Meta
 *
 * This template can be overridden by copying it to themes/rey-child/rey-core/woocommerce/single-meta.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     9.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;
?>

<?php do_action( 'reycore/woocommerce_product_meta/outside/before' ); ?>

<?php if( get_theme_mod('single_product_meta_v2', true) ): ?>

	<?php do_action( 'reycore/woocommerce_product_meta/before' ); ?>

	<div class="product_meta rey-pdp-meta">

		<?php do_action( 'woocommerce_product_meta_start' ); ?>

		<?php if ( wc_product_sku_enabled() && ( ($sku = $product->get_sku()) || $product->is_type( 'variable' ) ) ) : ?>
			<span class="sku_wrapper" style="<?php echo ! $sku ? 'display:none;' : '' ?>" data-o-sku="<?php echo $sku; ?>">
				<span class="sku" data-label="<?php esc_html_e( 'SKU:', 'woocommerce' ); ?>"><?php echo $sku; ?></span>
			</span>
		<?php endif; ?>

		<?php
		if( get_theme_mod('single__product_categories', true) ):
			echo wc_get_product_category_list( $product->get_id(), ', ', '<span class="posted_in">' . _n( 'Category:', 'Categories:', count( $product->get_category_ids() ), 'woocommerce' ) . ' ', '</span>' );
		endif; ?>

		<?php
		if( get_theme_mod('single__product_tags', true) ):
			echo wc_get_product_tag_list( $product->get_id(), ', ', '<span class="tagged_as">' . _n( 'Tag:', 'Tags:', count( $product->get_tag_ids() ), 'woocommerce' ) . ' ', '</span>' );
		endif; ?>

		<?php do_action( 'woocommerce_product_meta_end' ); ?>

	</div>

<?php do_action( 'reycore/woocommerce_product_meta/after' ); ?>

<?php endif; ?>

<?php do_action( 'reycore/woocommerce_product_meta/outside/after' ); ?>
