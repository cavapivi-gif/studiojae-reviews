<?php
defined( 'ABSPATH' ) || exit;
?>

<form class="checkout_coupon woocommerce-form-coupon" method="post" >
	<input type="text" name="coupon_code" class="input-text" placeholder="<?php echo esc_attr__( 'Coupon code', 'woocommerce' ); ?>" id="coupon_code" value="" />
	<button type="submit" class="button" name="apply_coupon" value="<?php echo esc_attr__( 'Apply', 'woocommerce' ); ?>"><?php esc_html_e( 'Apply', 'woocommerce' ); ?></button>
</form>
