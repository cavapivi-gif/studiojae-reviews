<?php
/**
 * Pagination - Show numbered pagination for catalog pages
 *
 * This template can be overridden by copying it to themes/rey-child/rey-core/woocommerce/loop-pagination-paged.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 9.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$total   = isset( $total ) ? $total : wc_get_loop_prop( 'total_pages' );
$current = isset( $current ) ? $current : wc_get_loop_prop( 'current_page' );
$base    = isset( $base ) ? $base : esc_url_raw( str_replace( 999999999, '%#%', remove_query_arg( 'add-to-cart', get_pagenum_link( 999999999, false ) ) ) );
$format  = isset( $format ) ? $format : '';

if ( $total <= 1 ) {
	return;
}

reycore_assets()->add_styles('rey-pagination');

echo '<nav class="woocommerce-pagination rey-pagination">';
	echo paginate_links( apply_filters( 'woocommerce_pagination_args', [
		'base'         => $base,
		'format'       => $format,
		'add_args'     => false,
		'current'      => max( 1, $current ),
		'total'        => $total,
		'mid_size'     => 3,
		'show_all'     => false,
		'end_size'     => 1,
		'prev_next'    => true,
		'prev_text'    => reycore__arrowSvg(['right' => false, 'attributes' => sprintf('title="%s"', __( '&laquo; Previous' )), 'title' => false ]),
		'next_text'    => reycore__arrowSvg(['attributes' => sprintf('title="%s"', __( 'Next &raquo;' )), 'title' => false ]),
		'type'         => 'plain',
		'add_fragment' => ''
	] ) );
echo '</nav>';
