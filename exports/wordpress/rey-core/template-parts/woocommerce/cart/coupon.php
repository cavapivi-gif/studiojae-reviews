<?php
defined( 'ABSPATH' ) || exit; ?>

<?php if ( wc_coupons_enabled() ) { ?>
	<div class="coupon">
		<?php do_action( 'reycore/woocommerce/before_woocommerce_cart_coupon' ); ?>
		<label for="coupon_code"><?php esc_html_e( 'Coupon:', 'woocommerce' ); ?></label>
		<input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_html_e( 'Coupon code', 'woocommerce' ); ?>" /> <button type="submit" class="button" name="apply_coupon" value="<?php esc_html_e( 'Apply coupon', 'woocommerce' ); ?>" aria-label="<?php esc_html_e('Apply coupon', 'woocommerce') ?>"><?php esc_html_e( 'Apply', 'woocommerce' ); ?></button>
		<?php do_action( 'woocommerce_cart_coupon' ); ?>
	</div>
<?php } ?>
