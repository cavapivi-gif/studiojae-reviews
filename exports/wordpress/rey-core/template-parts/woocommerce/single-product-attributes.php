<?php
/**
 * Product attributes
 *
 * Used by list_attributes() in the products class.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/product-attributes.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.3.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! $product_attributes ) {
	return;
}

$toggle_content = get_theme_mod('product_content_blocks_specs_toggle', false);

if( $toggle_content ){

	reycore_assets()->add_styles(['rey-buttons', 'reycore-text-toggle']);
	reycore_assets()->add_scripts('reycore-text-toggle');

	printf('<div class="rey-prodDescToggle u-toggle-text-next-btn %s">', apply_filters('reycore/productspecs/mobile_only', false) ? '--mobile' : '');

} ?>

<table class="woocommerce-product-attributes shop_attributes">
	<?php foreach ( $product_attributes as $product_attribute_key => $product_attribute ) : ?>
		<tr class="woocommerce-product-attributes-item woocommerce-product-attributes-item--<?php echo esc_attr( $product_attribute_key ); ?>">
			<th class="woocommerce-product-attributes-item__label"><?php echo wp_kses_post( $product_attribute['label'] ); ?></th>
			<td class="woocommerce-product-attributes-item__value"><?php echo wp_kses_post( $product_attribute['value'] ); ?></td>
		</tr>
	<?php endforeach; ?>
</table>

<?php

if( $toggle_content ){
	printf('</div><button class="btn btn-minimal" aria-label="Toggle"><span data-read-more="%s" data-read-less="%s"></span></button>',
		esc_html_x('Read more', 'Toggling the product specs product page layout.', 'rey-core'),
		esc_html_x('Less', 'Toggling the product specs product page layout.', 'rey-core')
	);
}
