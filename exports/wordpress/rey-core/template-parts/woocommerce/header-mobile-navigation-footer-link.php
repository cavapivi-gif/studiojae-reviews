<?php
if ( ! defined( 'ABSPATH' ) ) {
exit;
} ?>

<?php if( apply_filters('reycore/woocommerce/mobile_menu/show_connect_link', true) ): ?>
<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'dashboard' ) ); ?>" class="rey-mobileNav--footerItem">
	<?php
		if(! is_user_logged_in() ){
			esc_html_e('Connect to your account', 'rey-core');
		}
		else {
			esc_html_e('Your account', 'rey-core');
		}
	?>
	<?php echo reycore__get_svg_icon(['id' => 'user']) ?>
</a>
<?php endif; ?>

<?php if( is_user_logged_in() ): ?>
	<a href="<?php echo esc_url( wp_logout_url( reycore__get_current_url() ) ); ?>" class="rey-mobileNav--footerItem">
		<?php
			esc_html_e('Log Out', 'rey-core');
		?>
	</a>
<?php endif; ?>
