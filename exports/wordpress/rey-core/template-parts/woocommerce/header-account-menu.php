<?php
/**
 * My Account navigation
 *
 * This template can be overridden by copying it to themes/rey-child/rey-core/woocommerce/header-account-menu.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.3.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_user = wp_get_current_user();

do_action( 'woocommerce_before_account_navigation' ); ?>

<nav class="woocommerce-MyAccount-navigation">

	<?php if( ! is_account_page() ): ?>
		<?php if( $heading = apply_filters('reycore/woocommerce/account-menu/heading', sprintf( __('Hello %s,', 'woocommerce'), ($current_user->user_firstname ? $current_user->user_firstname : $current_user->user_login) ), $current_user) ): ?>
			<<?php echo reycore_wc__account_heading_tags('hello_title') ?> class="rey-accountPanel-title">
				<?php echo $heading; ?>
			</<?php echo reycore_wc__account_heading_tags('hello_title') ?>>
		<?php endif; ?>
	<?php endif; ?>

	<ul>
		<?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?>
			<li class="<?php echo wc_get_account_menu_item_classes( $endpoint ); ?>">
				<a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>"><?php echo apply_filters('reycore/woocommerce/account-menu/link_label', wp_kses_post($label), $endpoint) ?></a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>

<?php do_action( 'woocommerce_after_account_navigation' );
