<?php
defined( 'ABSPATH' ) || exit;


$checkout = WC()->checkout();
$fields = $checkout->get_checkout_fields();

if( isset($fields['billing']['billing_email']) ){

	woocommerce_form_field( 'billing_email', $fields['billing']['billing_email'], $checkout->get_value( 'billing_email' ) );

	if( ! is_user_logged_in() && 'yes' === get_option( 'woocommerce_enable_checkout_login_reminder' ) ){

		printf('<p class="form-row form-row-wide rey-checkoutLogin-btn">%1$s <a href="#" class="" data-reymodal=\'%3$s\'>%2$s</a></p>',
			esc_html__('Already have an account?', 'rey-core'),
			esc_html__('Log in', 'rey-core'),
			wp_json_encode([
				'content' => '.rey-checkoutLogin-form',
				'width' => 700,
				'id' => 'rey-checkout-login-modal'
			])
		);

		reycore_assets()->add_styles(['rey-wc-header-account-panel']);

		add_filter( 'reycore/modals/always_load', '__return_true');

	}
}
