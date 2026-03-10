<?php
/**
 * Footer Navigation
 */ ?>

<?php if ( has_nav_menu( 'footer-menu' ) ) : ?>
	<nav id="footer-navigation" class="rey-footerNav" aria-label="<?php esc_attr_e( 'Footer Menu', 'rey' ); ?>">
		<?php
		wp_nav_menu([
			'theme_location' => 'footer-menu',
			'menu_class'     => 'rey-footerMenu',
			'container'      => '',
			'depth'          => '1',
			'items_wrap'     => '<ul id="%1$s" class="%2$s">%3$s</ul>',
		]);
		?>
	</nav><!-- .rey-footerNav -->
<?php else : ?>
	<?php if( current_user_can('administrator') ): ?>
	<p>
		<?php echo sprintf( wp_kses( __('You have to create a menu then select Primary Menu location using&nbsp;<a href="%s">Menu Builder</a>', 'rey'), ['a' => ['href' => []]] ), esc_url(admin_url('nav-menus.php')) ) ?>
	</p>
	<?php endif; ?>
<?php endif; ?>
