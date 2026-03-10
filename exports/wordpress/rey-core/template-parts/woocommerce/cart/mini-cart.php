<?php
/**
 * Mini-cart
 *
 * Contains the markup for the mini-cart, used by the cart widget.
 *
 * This template can be overridden by copying it to themes/rey-child/rey-core/woocommerce/cart/mini-cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 10.0.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_mini_cart' ); ?>

<?php if ( ! WC()->cart->is_empty() ) :

	if( ! empty(WC()->cart->get_coupons()) ){
		$args['list_class'] .= ' has-coupons';
	} ?>

	<ul class="woocommerce-mini-cart cart_list product_list_widget <?php echo esc_attr( $args['list_class'] ); ?>">
		<?php
		do_action( 'woocommerce_before_mini_cart_contents' );

		$the_cart_items = WC()->cart->get_cart();

		if( apply_filters('reycore/woocommerce/mini_cart/reverse', true) ){
			$the_cart_items = array_reverse($the_cart_items);
		}

		foreach ( $the_cart_items as $cart_item_key => $cart_item ) {
			$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
			$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

			if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
				$product_name      = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
				$thumbnail         = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image('woocommerce_thumbnail'), $cart_item, $cart_item_key );
				$price_html = \ReyCore\WooCommerce\Tags\MiniCart::get_product_price( $cart_item );
				$product_price     = apply_filters( 'woocommerce_cart_item_price', $price_html, $cart_item, $cart_item_key );
				$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );

				$classes = ['mini_cart_item'];

				?>
				<li class="woocommerce-mini-cart-item <?php echo esc_attr( apply_filters( 'woocommerce_mini_cart_item_class', implode(' ', $classes), $cart_item, $cart_item_key ) ); ?>">
					<?php
					echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						'woocommerce_cart_item_remove_link',
						sprintf(
							'<a href="%s" class="remove remove_from_cart_button rey-removeBtn" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s">%s</a>',
							esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
							/* translators: %s is the product name */
							esc_attr( sprintf( __( 'Remove %s from cart', 'woocommerce' ), wp_strip_all_tags( $product_name ) ) ),
							esc_attr( $product_id ),
							esc_attr( $cart_item_key ),
							esc_attr( $_product->get_sku() ),
							reycore__get_svg_icon(['id' => 'close'])
						),
						$cart_item_key
					);
					?>
					<?php if ( empty( $product_permalink ) ) : ?>
						<?php echo '<div class="rey-cartImg">'.$thumbnail.'</div>' . wp_kses_post( $product_name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php else : ?>
						<a href="<?php echo esc_url( $product_permalink ); ?>" class="woocommerce-mini-cart-thumbTitle">
							<?php echo '<div class="rey-cartImg">'.$thumbnail.'</div>' . wp_kses_post( $product_name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</a>
					<?php endif; ?>
					<?php do_action( 'reycore/minicart/after_woocommerce_cart_item_name', $cart_item, $cart_item_key ); ?>
					<?php echo wc_get_formatted_cart_item_data( $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php echo apply_filters( 'woocommerce_widget_cart_item_quantity', '<span class="quantity">' . sprintf( '%s &times; %s', $cart_item['quantity'], sprintf('<span class="woocommerce-mini-cart-price">%s</span>', $product_price) ) . '</span>', $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</li>
				<?php
			}
		}

		do_action( 'woocommerce_mini_cart_contents' );
		?>
	</ul>

	<?php do_action( 'reycore/woocommerce/minicart/before_totals' ); ?>

	<div class="woocommerce-mini-cart__total total">

		<?php do_action( 'woocommerce_widget_shopping_cart_total' ); ?>

		<?php
		if ( !is_cart() && !is_checkout() ){

			foreach ( WC()->cart->get_fees() as $fee ) : ?>

				<div class="minicart-total-row minicart-total-row--fees fees">
					<div class="minicart-total-row-head"><?php echo esc_html( $fee->name ); ?></div>
					<div class="minicart-total-row-content"><?php wc_cart_totals_fee_html( $fee ); ?></div>
				</div>

			<?php endforeach;

			if ( wc_coupons_enabled() && get_theme_mod('header_cart_coupon', true) ) {

				$coupons = WC()->cart ? WC()->cart->get_coupons() : [];
				$show_form = true;

				if( ! empty($coupons) && get_theme_mod('header_cart_coupon_hide_if_applied', true) ){
					$show_form = false;
				}

				if( ! empty($coupons) ):
					foreach ( $coupons as $code => $coupon ) : ?>
					<div class="minicart-total-row minicart-total-row--coupon coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
						<div class="minicart-total-row-head"><?php wc_cart_totals_coupon_label( $coupon ); ?></div>
						<div class="minicart-total-row-content"><?php wc_cart_totals_coupon_html( $coupon ); ?></div>
					</div>
				<?php endforeach;
				endif;

				if( $show_form ):
					reycore_assets()->add_styles('rey-wc-coupon-toggle'); ?>
					<div class="rey-toggleCoupon">
						<button class="rey-toggleCoupon-btn"><span><?php esc_html_e('Have a Coupon?', 'rey-core') ?></span></button>
						<div class="rey-toggleCoupon-content">
							<?php reycore__get_template_part('template-parts/woocommerce/cart/coupon'); ?>
						</div>
					</div>
				<?php endif; ?>

				<div class="rey-toggleCoupon-response"></div>

				<?php reycore_assets()->add_styles(['rey-wc-notices']); ?>

			<?php
			}
		} ?>

		<?php do_action( 'woocommerce_widget_shopping_cart_total_after' ); ?>

	</div>

	<?php do_action( 'woocommerce_widget_shopping_cart_before_buttons' ); ?>

	<p class="woocommerce-mini-cart__buttons buttons"><?php do_action( 'woocommerce_widget_shopping_cart_buttons' ); ?></p>

	<?php do_action( 'woocommerce_widget_shopping_cart_after_buttons' ); ?>

<?php else : ?>

	<p class="woocommerce-mini-cart__empty-message"><?php esc_html_e( 'No products in the cart.', 'woocommerce' ); ?></p>

<?php endif; ?>

<?php do_action( 'woocommerce_after_mini_cart' ); ?>
