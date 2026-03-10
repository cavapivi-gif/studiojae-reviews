<?php
defined( 'ABSPATH' ) || exit;

global $product;

$attribute_keys  = array_keys( $args['attributes'] );

do_action( 'woocommerce_before_variations_form' ); ?>

<?php if ( empty( $args['available_variations'] ) && false !== $args['available_variations'] ) : ?>
	<p class="stock out-of-stock"><?php echo esc_html( apply_filters( 'woocommerce_out_of_stock_message', __( 'This product is currently out of stock and unavailable.', 'woocommerce' ) ) ); ?></p>
<?php else : ?>
	<table class="variations" cellspacing="0">
		<tbody>
			<?php foreach ( $args['attributes'] as $attribute_name => $options ) : ?>
				<tr>
					<td class="label"><label for="<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>"><?php echo wc_attribute_label( $attribute_name ); // WPCS: XSS ok. ?></label></td>
					<td class="value">
						<?php
							wc_dropdown_variation_attribute_options(
								array(
									'options'   => $options,
									'attribute' => $attribute_name,
									'product'   => $product,
								)
							);
							echo end( $attribute_keys ) === $attribute_name ? wp_kses_post( apply_filters( 'woocommerce_reset_variations_link', '<a class="reset_variations" href="#">' . esc_html__( 'Clear', 'woocommerce' ) . '</a>' ) ) : '';
						?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>

<?php do_action( 'woocommerce_after_variations_form' ); ?>
