<?php
defined( 'ABSPATH' ) || exit;


$checkout = WC()->checkout();
$show_account_fields = ! is_user_logged_in() && $checkout->is_registration_enabled(); ?>

<div class="woocommerce-shipping-fields <?php echo $show_account_fields ? '--account-fields' : ''; ?>">

	<?php if ( true === WC()->cart->needs_shipping_address() ){ ?>

		<h3 class="rey-checkoutPage-title"><?php echo esc_html__('Shipping address', 'rey-core') ?></h3>

		<?php if( reycore_wc__get_tag('checkout')->checkout_custom_billing_first() ): ?>
		<div class="rey-checkoutPage-shpAsBilling" id="ship-to-different-address">
			<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
				<input
					id="<?php echo apply_filters('woocommerce_ship_to_different_address_input_id', 'ship-to-different-address-checkbox'); ?>"
					class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox"
					<?php checked( apply_filters( 'woocommerce_ship_to_different_address_checked', 'shipping' === get_option( 'woocommerce_ship_to_destination' ) ? 1 : 0 ), 1 ); ?>
					type="checkbox"
					name="ship_to_different_address"
					value="1"
					/>
				<span><?php esc_html_e( 'Ship to a different address?', 'woocommerce' ); ?></span>
			</label>
		</div>
		<?php endif; ?>

		<?php do_action( 'woocommerce_before_checkout_shipping_form', $checkout ); ?>

		<div class="woocommerce-shipping-fields__field-wrapper <?php echo apply_filters( 'woocommerce_ship_to_different_address_checked', 'shipping' === get_option( 'woocommerce_ship_to_destination' ) ) ? '--visible' : ''; ?>">
			<?php
			$fields = $checkout->get_checkout_fields( 'shipping' );

			foreach ( $fields as $key => $field ) {
				woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
			}
			?>
		</div>

		<?php do_action( 'woocommerce_after_checkout_shipping_form', $checkout ); ?>

	<?php
	} else {
		if( current_user_can('administrator') ){
			printf('<p>%s<br><small><em>%s</em></small></p>', esc_html__('No shipping method added.', 'rey-core'), esc_html__('This message shows only for the site Administrator.', 'rey-core'));
		}
	} ?>
</div>
