<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pagination_args = apply_filters( 'rey/blog_pagination_args', [
	'base'            => esc_url_raw( str_replace( 999999999, '%#%', get_pagenum_link( 999999999, false ) ) ),
	'show_all'        => false,
	'end_size'        => 1,
	'prev_next'       => true,
	'prev_text'       => rey__arrowSvg(['right' => false, 'attributes' => sprintf('title="%s"', __( '&laquo; Previous' )), 'title' => false ]),
	'next_text'       => rey__arrowSvg(['attributes' => sprintf('title="%s"', __( 'Next &raquo;' )), 'title' => false ]),
	'type'            => 'plain',
	'add_args'        => false,
	'add_fragment'    => ''
] );

if ( $paginate_links = paginate_links($pagination_args) ) {
	rey_assets()->add_styles('rey-pagination');
	printf( '<nav class="rey-pagination">%s</nav>', $paginate_links);
}
