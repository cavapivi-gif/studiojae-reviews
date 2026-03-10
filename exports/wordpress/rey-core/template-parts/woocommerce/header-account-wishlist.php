<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = reycore_wc__get_account_panel_args();
$wishlist_url = $wishlist_counter = $wishlist_title = '';

if( class_exists('\ReyCore\WooCommerce\Tags\Wishlist') ){
	$wishlist_url = \ReyCore\WooCommerce\Tags\Wishlist::get_wishlist_url();
	$wishlist_counter = \ReyCore\WooCommerce\Tags\Wishlist::get_wishlist_counter_html();
	$wishlist_title = \ReyCore\WooCommerce\Tags\Wishlist::title();
}

if( ! $args['wishlist'] ) {
	return;
} ?>

<div class="rey-accountWishlist-wrapper " data-account-tab="wishlist">
	<<?php echo reycore_wc__account_heading_tags('wishlist') ?> class="rey-accountPanel-title">
		<?php
		if( $wishlist_url ){
			printf( '<a href="%s">', esc_url( $wishlist_url ) );
		}
			echo $wishlist_title;

		if( $wishlist_url ){
			echo '</a>';
		}

		if( $args['wishlist'] && $args['counter'] != '' ){
			echo $wishlist_counter;
		} ?>

	</<?php echo reycore_wc__account_heading_tags('wishlist') ?>>
	<div class="rey-wishlistPanel-container" data-type="<?php echo esc_attr($args['wishlist_prod_layout']) ?>">
		<div class="rey-accountWishlist rey-wishlistPanel"></div>
		<div class="rey-lineLoader"></div>
	</div>
</div>
