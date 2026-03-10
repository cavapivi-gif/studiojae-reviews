<?php
/**
 * Result Count
 *
 * Shows text: Showing x - x of x results.
 *
 * This template can be overridden by copying it to themes/rey-child/rey-core/woocommerce/loop-result-count.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     10.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="woocommerce-result-count">
	<?php

	$first = $last = $the_text = '';

	if ( 1 === intval( $total ) ) {
		$text = __( 'Showing the single result', 'woocommerce' );
	}
	elseif ( $total <= $per_page || -1 === $per_page ) {

		$first = ( $per_page * $current ) - $per_page + 1;
		$last  = min( $total, $per_page * $current );

		/* translators: %d: total results */
		$text = sprintf( _n( 'Showing all %d result', 'Showing all %d results', $total, 'woocommerce' ), $total );
	}
	else {

		$first = ( $per_page * $current ) - $per_page + 1;
		$last  = min( $total, $per_page * $current );

		/* translators: 1: first result 2: last result 3: total results */
		$text = sprintf( _nx( 'Showing %1$d&ndash;%2$d of %3$d result', 'Showing %1$d&ndash;%2$d of %3$d results', $total, 'with first and last result', 'woocommerce' ), $first, $last, $total );
	}

	if( $custom_text = get_theme_mod('loop_product_count__text', '') ){

		if( 1 < intval( $total ) ){

			// if( strpos($custom_text, '{{FIRST}}') !== false && empty($first) ){
			// 	$result_count_custom_text = sprintf('<span class="total-count">%d</span> / <span class="total-count">%d</span>', $total, $total);
			// }

			// else {

				$result_count_custom_text = $custom_text;

				foreach ([
					[
						'placeholder' => '{{TOTAL}}',
						'replacement' => $total,
						'css_class' => 'total-count',
					],
					[
						'placeholder' => '{{FIRST}}',
						'replacement' => $first,
						'css_class' => 'first-count',
					],
					[
						'placeholder' => '{{LAST}}',
						'replacement' => $last,
						'css_class' => 'last-count',
					],
				] as $item) {
					$result_count_custom_text = str_replace($item['placeholder'], sprintf('<span class="%s">%s</span>', $item['css_class'], $item['replacement']), $result_count_custom_text);
				}

			// }

			$the_text = apply_filters('reycore/woocommerce/loop/result_count_text', $result_count_custom_text, $custom_text, $total, $first, $last);
		}

	}
	else {
		$the_text = $text;
	}

	if( $the_text ){
		// printf('<span>%s</span>', $the_text);
		printf('<span>%s</span>', $the_text);
	} ?>
</div>
