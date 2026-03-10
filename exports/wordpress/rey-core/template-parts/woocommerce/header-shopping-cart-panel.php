<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists('\WooCommerce') ) {
    return;
}

reycore_assets()->add_styles('reycore-close-arrow');

wp_enqueue_script( 'wc-cart-fragments' );

$panel_class = 'rey-cartPanel';

if( $args['inline_buttons'] ){
	$panel_class .= ' --btns-inline';
} ?>

<div class="rey-cartPanel-wrapper rey-sidePanel js-rey-cartPanel woocommerce" data-lazy-hidden>

	<?php do_action('reycore/woocommerce/mini-cart/before', $args); ?>

	<div class="<?php echo esc_attr($panel_class); ?>">

		<div class="rey-cartPanel-header">

			<div class="__tabs">

				<div class="__tab --active" data-item="cart">
					<?php $title_tag = reycore__header_cart_params('title_tag'); ?>
					<<?php echo \ReyCore\Helper::get_valid_title_tag($title_tag, 'div') ?> class="rey-cartPanel-title">
						<?php echo $args['cart_title'] ?>
						<?php \ReyCore\WooCommerce\Tags\MiniCart::get_cart_count(); ?>
					</<?php echo \ReyCore\Helper::get_valid_title_tag($title_tag, 'div') ?>>
				</div>

				<?php do_action('reycore/woocommerce/mini-cart/tabs', $args); ?>

			</div>

		</div>

		<div class="__tab-content --active" data-item="cart">

			<div class="widget woocommerce widget_shopping_cart">
				<div class="widget_shopping_cart_content"></div>
			</div>

		</div>

		<?php do_action('reycore/woocommerce/mini-cart/after_content', $args); ?>
	</div>

	<?php do_action('reycore/woocommerce/mini-cart/after', $args); ?>

</div>
