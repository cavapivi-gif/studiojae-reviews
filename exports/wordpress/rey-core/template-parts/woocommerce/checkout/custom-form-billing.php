<?php
/**
 * Checkout billing information form
 *
 * This template can be overridden by copying it to themes/rey-child/rey-core/woocommerce/checkout/custom-form-billing.php.
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
 * @global WC_Checkout $checkout
 */

defined( 'ABSPATH' ) || exit;

$billing_first = reycore_wc__get_tag('checkout')->checkout_custom_billing_first(); ?>

<?php if ( wc_ship_to_billing_address_only() && WC()->cart->needs_shipping() ) : ?>
	<h3 class="rey-checkoutPage-title"><?php esc_html_e( 'Billing &amp; Shipping', 'woocommerce' ); ?></h3>
<?php else : ?>
	<h3 class="rey-checkoutPage-title"><?php esc_html_e( 'Billing details', 'woocommerce' ); ?></h3>
<?php endif; ?>

<?php if( ! $billing_first ): ?>
<div class="rey-checkoutChoose rey-checkoutBilling-choose">

	<div class="rey-checkoutChoose-item rey-checkoutBilling-chooseItem">
		<div class="rey-form-radio">
			<input class="input-radio" type="radio" value="same" checked="checked" id="checkout_same" name="rey_billing_type">
			<label for="checkout_same"><?php echo esc_html__('Same as shipping address', 'rey-core') ?></label>
		</div>
	</div>

	<div class="rey-checkoutChoose-item rey-checkoutBilling-chooseItem">
		<div class="rey-form-radio">
			<input class="input-radio" type="radio" value="different" id="checkout_different" name="rey_billing_type">
			<label for="checkout_different"><?php echo esc_html__('Use a different billing address', 'rey-core') ?></label>
		</div>
	</div>
<?php endif; ?>

	<div class="woocommerce-billing-fields">

		<?php do_action( 'woocommerce_before_checkout_billing_form', $checkout ); ?>

		<div class="woocommerce-billing-fields__field-wrapper clearfix">
			<?php
			$fields = $checkout->get_checkout_fields( 'billing' );

			foreach ( $fields as $key => $field ) {
				woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
				do_action('reycore/woocommerce/checkout/after_billing_field/key=' . $key, $field);
			} ?>

			<?php if( ! $billing_first ): ?>
			<div id="ship-to-different-address">
				<input id="<?php echo apply_filters('woocommerce_ship_to_different_address_input_id', 'ship-to-different-address-checkbox'); ?>" type="checkbox" name="ship_to_different_address" value="1" checked class="--hidden"/>
			</div>
			<?php endif; ?>
		</div>

		<?php do_action( 'woocommerce_after_checkout_billing_form', $checkout ); ?>
	</div>

<?php if( ! $billing_first ): ?>
</div>
<?php endif; ?>
