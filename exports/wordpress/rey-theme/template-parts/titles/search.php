<header class="rey-pageHeader">
	<?php if ( have_posts() ) : ?>
		<h1 class="rey-pageTitle"><?php printf( wp_kses( __( 'Search Results for: %s', 'rey' ),['span' => ['class' => []]]), '<span>' . get_search_query() . '</span>' ); ?></h1>
	<?php else : ?>
		<h1 class="rey-pageTitle"><?php esc_html_e( 'Nothing Found', 'rey' ); ?></h1>
	<?php endif; ?>
</header><!-- .page-header -->
