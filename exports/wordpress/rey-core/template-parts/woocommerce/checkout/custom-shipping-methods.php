<?php
defined( 'ABSPATH' ) || exit; ?>

<div class="rey-checkout-shipping">

	<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>

		<h3 class="rey-checkoutPage-title"><?php echo esc_html_x('Shipping Method', 'Title in checkout form, shipping step.', 'rey-core') ?></h3>

		<?php do_action( 'woocommerce_review_order_before_shipping' ); ?>

		<table class="rey-checkout-shippingMethods"><?php wc_cart_totals_shipping_html(); ?></table>

		<?php do_action( 'woocommerce_review_order_after_shipping' ); ?>

	<?php endif; ?>

</div>
