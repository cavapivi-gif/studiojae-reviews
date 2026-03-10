<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = reycore_wc__get_header_search_args(); ?>

<?php if ( $args['search_menu_source'] ) : ?>

	<nav class="rey-searchPanel__qlinks" aria-label="<?php echo esc_attr__( 'Search Menu', 'rey-core' ); ?>" data-lazy-hidden>
		<?php
		printf('<%2$s class="rey-searchPanel__qlinksTitle">%1$s</%2$s>', esc_html( ucwords(str_replace('-', ' ', $args['search_menu_source'])) ), esc_html( $args['menu_title_tag'] ) );

		wp_nav_menu([
			'menu'       => $args['search_menu_source'],
			'menu_class' => 'rey-searchMenu list-unstyled',
			'container'  => '',
			'depth'      => 1,
			'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
			'fallback_cb' 	=> '__return_empty_string',
			'cache_menu'  => true,
		]);
		?>
	</nav><!-- .rey-searchPanel__qlinks -->
<?php endif; ?>
