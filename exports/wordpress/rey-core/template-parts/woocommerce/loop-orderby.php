<?php
/**
 * Show options for ordering
 *
 * This template can be overridden by copying it to themes/rey-child/rey-core/woocommerce/loop-orderby.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     9.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$catalog_orderby_options = apply_filters('reycore/woocommerce/orderby_options', $catalog_orderby_options);

reycore_assets()->add_styles('rey-buttons'); ?>

<form class="woocommerce-ordering rey-loopSelectList" method="get" >

	<label class="btn btn-line" for="catalog-orderby-list" tabindex="0">
		<span><?php echo esc_html( $catalog_orderby_options[$orderby] ); ?></span>
		<select name="orderby" class="orderby" aria-label="<?php echo esc_attr__( 'Shop order', 'rey-core' ); ?>" id="catalog-orderby-list" tabindex="-1">
			<?php foreach ( $catalog_orderby_options as $id => $name ) : ?>
				<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $orderby, $id ); ?>><?php echo esc_html( $name ); ?></option>
			<?php endforeach; ?>
		</select>
	</label>

	<input type="hidden" name="paged" value="1" />
	<?php wc_query_string_form_fields( null, array( 'orderby', 'submit', 'paged', 'product-page' ) ); ?>
</form>
