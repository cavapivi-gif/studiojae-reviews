<?php
/**
 * Checkout Form
 *
 * This template can be overridden by copying it to themes/rey-child/rey-core/woocommerce/checkout/custom-form-checkout.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 4.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// check if shipping methods are added
$shipping_needed = WC()->cart->needs_shipping();

// check for c
if( wc_ship_to_billing_address_only() ){
	$shipping_needed = false;
}

$checkout_instance = reycore_wc__get_tag('checkout');
$shipping_disabled = $checkout_instance->checkout_custom_shipping_disabled();

if( $shipping_disabled ){
	$shipping_needed = false;
}

$billing_first = $checkout_instance->checkout_custom_billing_first();

$next_arrow = apply_filters('reycore/woocommerce/checkout/custom_layout/next_arrow', '&nbsp;&nbsp;' . reycore__get_svg_icon(['id'=> 'arrow', 'class' => (is_rtl() ? '--to-left' : '--to-right')]));

reycore_assets()->add_styles('rey-buttons');

do_action( 'woocommerce_before_checkout_form', $checkout );

// If checkout registration is disabled and not logged in, the user cannot checkout.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
} ?>

<div class="rey-checkoutPage-inner">

	<form name="checkout" method="post" class="checkout rey-checkoutPage-form woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">

		<?php if( $checkout_instance->checkout_custom_use_steps() ):
			$arrow_html = reycore__get_svg_icon(['id'=>'arrow', 'class' => (is_rtl() ? '--to-left' : '--to-right')]); ?>
			<div class="rey-checkoutPage-crumbs">
				<ul>
					<li><a href="<?php echo wc_get_cart_url(); ?>" data-step="cart" data-number="1"><?php esc_html_e('Cart', 'rey-core') ?></a></li>
					<li><?php echo $arrow_html ?></li>
					<li class="--active"><a href="#" data-step="info" data-number="2"><?php esc_html_e('Information', 'rey-core') ?></a></li>
					<?php if(!$shipping_disabled): ?>
						<li><?php echo $arrow_html ?></li>
						<li><a href="#" data-step="shipping" data-number="3"><?php esc_html_e('Shipping', 'rey-core') ?></a></li>
					<?php endif; ?>
					<li><?php echo $arrow_html ?></li>
					<li><a href="#" data-step="payment" data-number="4"><?php esc_html_e('Payment', 'rey-core') ?></a></li>
				</ul>
			</div>
		<?php else: ?>
			<div class="rey-checkoutPage-back">
				<a href="<?php echo wc_get_cart_url(); ?>" class="btn btn-line">
					<?php echo reycore__get_svg_icon(['id'=>'arrow', 'class' => '--to-left']); ?>
					<span><?php echo esc_html__('Return to Cart', 'rey-core') ?></span>
				</a>
			</div>
		<?php endif; ?>

		<?php
		if ( $checkout->get_checkout_fields() ) : ?>

			<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

			<div class="__step" data-step="info">

				<?php do_action( 'reycore/woocommerce/checkout/before_information' ); ?>

				<h3 class="rey-checkoutPage-title"><?php echo esc_html__('Information', 'rey-core') ?></h3>

				<div class="__step-main">

					<div class="form-row-wrapper __email">

						<?php reycore__get_template_part('template-parts/woocommerce/checkout/custom-email-helper'); ?>

						<?php do_action( 'reycore/woocommerce_checkout_form/after_email' ); ?>

					</div>

					<?php
					if( $billing_first ){ ?>
						<div class="rey-checkoutDetails-billing">
							<?php do_action( 'woocommerce_checkout_billing' ); ?>

							<?php
							if( $shipping_disabled ){
								$add_account_fields = true;
								reycore__get_template_part('template-parts/woocommerce/checkout/custom-form-account-fields');
							} ?>

						</div><?php
					}
					// Show shipping fields
					else {
						reycore__get_template_part('template-parts/woocommerce/checkout/custom-form-shipping-fields');
					}

					do_action( 'woocommerce_checkout_after_customer_details' );

					if( ! (isset($add_account_fields) && $add_account_fields) ){
						reycore__get_template_part('template-parts/woocommerce/checkout/custom-form-account-fields');
					} ?>
				</div>

				<?php if( $checkout_instance->checkout_custom_use_steps() ): ?>
				<div class="__step-footer">
					<a href="<?php echo wc_get_cart_url(); ?>" class="btn btn-line __step-back">
						<?php echo reycore__get_svg_icon(['id'=>'arrow', 'class' => '--to-left']); ?>
						<span><?php echo esc_html__('Return to Cart', 'rey-core') ?></span>
					</a>
					<?php if(!$shipping_disabled): ?>
						<a href="#" class="btn btn-primary __step-fwd">
							<?php echo esc_html__('Continue to shipping', 'rey-core') ?>
							<?php echo $next_arrow; ?>
						</a>
					<?php else: ?>
						<a href="#" class="btn btn-primary __step-fwd">
							<?php echo esc_html__('Continue to payment', 'rey-core') ?>
							<?php echo $next_arrow; ?>
						</a>
					<?php endif; ?>
				</div>
				<?php endif; ?>

			</div>
			<!-- End Information -->

			<?php if(!$shipping_disabled): ?>
			<div class="__step" data-step="shipping">

				<?php if( $checkout_instance->checkout_custom_use_steps() ): ?>
					<div class="rey-formReview">
						<?php
							$checkout_instance::review_form_block(esc_html_x('Contact', 'Title in checkout steps form review.', 'rey-core'), 'email', 'info');

							if( ! $billing_first ){
								$checkout_instance::review_form_block(esc_html_x('Ship to', 'Title in checkout steps form review.', 'rey-core'), 'address_ship', 'info');
							}
							else {
								$checkout_instance::review_form_block(esc_html_x('Bill to', 'Title in checkout steps form review.', 'rey-core'), 'address_bill', 'info');
							}

							do_action('reycore/woocommerce/checkout/review_form', $checkout_instance, 'shipping');
						?>
					</div>
				<?php endif; ?>

				<div class="__step-main">

					<?php
					if( $billing_first ){
						reycore__get_template_part('template-parts/woocommerce/checkout/custom-form-shipping-fields');
					} ?>

					<?php do_action( 'woocommerce_checkout_shipping' ); ?>
				</div>

				<?php if( $checkout_instance->checkout_custom_use_steps() ): ?>
				<div class="__step-footer">
					<a href="#" class="btn btn-line __step-back">
						<?php echo reycore__get_svg_icon(['id'=>'arrow', 'class' => '--to-left']); ?>
						<span><?php echo esc_html__('Return to Information', 'rey-core') ?></span>
					</a>
					<a href="#" class="btn btn-primary __step-fwd">
						<?php echo esc_html__('Continue to payment', 'rey-core') ?>
						<?php echo $next_arrow; ?>
					</a>
				</div>
				<?php endif; ?>

			</div>
			<!-- End Shipping ( & Additional fields) -->
			<?php endif; ?>

		<?php endif; ?>

		<div class="rey-customerDetails-payment __step" data-step="payment">

			<?php if( $checkout_instance->checkout_custom_use_steps() ): ?>
			<div class="rey-formReview">
				<?php
					$checkout_instance::review_form_block(esc_html_x('Contact', 'Title in checkout steps form review.', 'rey-core'), 'email', 'info');
					if( $billing_first ){
						$checkout_instance::review_form_block(esc_html_x('Bill to', 'Title in checkout steps form review.', 'rey-core'), 'address_bill', 'info');
						if( $shipping_needed ){
							$checkout_instance::review_form_block(esc_html_x('Ship to', 'Title in checkout steps form review.', 'rey-core'), 'address_ship', 'shipping');
						}
					}
					else {
						if( $shipping_needed ){
							$checkout_instance::review_form_block(esc_html_x('Ship to', 'Title in checkout steps form review.', 'rey-core'), 'address_ship', 'info');
						}
					}

					if( $shipping_needed ){
						$checkout_instance::review_form_block(esc_html_x('Method', 'Title in checkout steps form review.', 'rey-core'), 'method', 'shipping');
					}

					do_action('reycore/woocommerce/checkout/review_form', $checkout_instance, 'payment');

				?>
			</div>
			<?php endif; ?>

			<?php
			if( ! $billing_first ){ ?>
				<div class="rey-checkoutDetails-billing">
					<?php do_action( 'woocommerce_checkout_billing' ); ?>
				</div>
			<?php } ?>

			<h3 class="rey-checkoutPage-title --has-caption"><?php echo esc_html_x('Payment', 'Title in checkout form, payment step.', 'rey-core') ?></h3>
			<?php echo apply_filters('reycore/woocommerce/checkout/custom/caption_text', wpautop( esc_html__('All transactions are secure and encrypted.', 'rey-core')) );  ?>
			<?php do_action( 'reycore/woocommerce/checkout/end' ); ?>
		</div>
		<!-- End Payment -->

	</form>

	<div class="rey-checkoutPage-review <?php echo apply_filters('reycore/woocommerce/checkout/custom_layout/mobile_active_order_review', false) ? '--mob-active' : ''; ?>">

		<div class="rey-checkoutPage-review-toggle">
			<span class="__title">
				<?php
				if( ($cart_layout = get_theme_mod('header_cart_layout', 'bag')) ){
					$cart_layout = ! in_array($cart_layout, ['disabled', 'text'], true) ? $cart_layout : 'bag';
					echo reycore__get_svg_icon([ 'id'=> $cart_layout, 'class' => '__title-cart-icon']);
				} ?>
				<span class="__title-text"><?php esc_html_e('Show order summary', 'rey-core') ?></span>
				<?php echo reycore__get_svg_icon(['id'=>'arrow', 'class' => '__title-arrow-icon']) ?>
			</span>
			<span id="rey-checkoutPage-review-toggle__total" class="__total"><?php wc_cart_totals_order_total_html(); ?></span>
		</div>

		<div class="rey-checkoutPage-review-inner">

			<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>

			<h3 id="order_review_heading"><?php esc_html_e( 'Your order', 'woocommerce' ); ?></h3>

			<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

			<div id="order_review" class="woocommerce-checkout-review-order">
				<?php do_action( 'woocommerce_checkout_order_review' ); ?>
			</div>

			<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>

		</div>

	</div>

	<?php
	if ( wc_coupons_enabled() ): ?>
		<div class="rey-checkoutPage-couponMobile">
		<h4 class="rey-checkoutPage-title"><?php esc_html_e('Have a Coupon?', 'rey-core') ?></h4>
		<?php reycore__get_template_part('template-parts/woocommerce/checkout/custom-form-coupon'); ?>
		</div>
	<?php endif; ?>

</div>
<!-- .rey-checkout-wrapper -->

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
