<?php
defined( 'ABSPATH' ) || exit;

// Compatibility reasons
do_action( 'woocommerce_cart_is_empty' ); ?>

<div class="rey-cartPage rey-emptyCart">

	<div class="rey-emptyCart-icon">
		<?php
		if( ($cart_layout = get_theme_mod('header_cart_layout', 'bag')) && !in_array($cart_layout, ['disabled', 'text'], true)){
			echo reycore__get_svg_icon([ 'id'=> $cart_layout]);
		} ?>
	</div>

	<div class="rey-emptyCart-title">
		<h2><?php echo wp_kses_post( apply_filters( 'wc_empty_cart_message', __( 'Your cart is currently empty.', 'woocommerce' ) ) )  ?></h2>
	</div>

	<div class="rey-emptyCart-content">

		<?php if ( apply_filters('reycore/woocommerce/cart_page/show_return_button', wc_get_page_id( 'shop' ) > 0 ) ) :
			reycore_assets()->add_styles('rey-buttons'); ?>
			<p class="return-to-shop">
				<a class="btn btn-primary wc-backward" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>">
					<?php esc_html_e( 'Return to shop', 'woocommerce' ); ?>
				</a>
			</p>
		<?php endif; ?>

	</div>
</div>
