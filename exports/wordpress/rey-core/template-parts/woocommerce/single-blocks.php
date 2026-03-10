<?php
/**
 * Single Product tabs
 *
 * This template can be overridden by copying it to themes/rey-child/rey-core/woocommerce/single-blocks.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 9.8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action('reycore/woocommerce/product/tabs/before', 'blocks');

/**
 * Filter tabs and allow third parties to add their own.
 *
 * Each tab is an array containing title, callback and priority.
 * @see woocommerce_default_product_tabs()
 */
$blocks = apply_filters( 'woocommerce_product_tabs', [] );

// Remove blocks that should go into Accordion/Tabs
if( $accordion_tabs = get_theme_mod('single__accordion_items', []) ){
	foreach ($accordion_tabs as $key => $value) {
		unset( $blocks[ $value['item'] ] );
	}
}

if ( ! empty( $blocks ) ) :

	foreach ( $blocks as $key => $tab ){
		if( ! empty($tab['force_acc']) ){
			unset($blocks[$key]);
		}
	}

	reycore_assets()->add_styles('rey-wc-product-blocks');

	$blocks_classes = [];

	$blocks_count = $blocks;
	unset($blocks_count['reviews']);

	// Stretch description
	if( get_theme_mod('product_content_blocks_desc_stretch', false) ){
		$blocks_classes[] = '--stretch-desc';
		unset($blocks_count['description']);
	}

	if( ! isset($blocks['description']['callback']) ){
		$blocks_classes[] = '--no-description';
		unset($blocks_count['description']);
	}

	$count_blocks_css = sprintf('--blocks-count:%d;', count($blocks_count));

	?>
	<div class="rey-wcPanels <?php echo implode(' ', array_map('esc_attr', apply_filters('rey/woocommerce/product_panels_classes', $blocks_classes))) ?>" style="<?php echo esc_attr($count_blocks_css) ?>">
		<?php

		$i = 1;

		foreach ( $blocks as $key => $tab ):

			if( ! empty($tab['force_acc']) ){
				continue;
			}

			$content = '';

			if ( isset( $tab['callback'] ) ) {

				ob_start();
				call_user_func( $tab['callback'], $key, $tab );
				$the_content = ob_get_clean();

				if( ! $the_content ){
					continue;
				}

				if( $key == 'reviews' && wc_review_ratings_enabled() && isset( $tab['title'] ) ) {

					ob_start();
					reycore__get_template_part('template-parts/woocommerce/single-block-reviews-button', false, false, [
						'text' => $tab['title']
					]);
					$content .= ob_get_clean();

				}

				$content .= '<div class="rey-wcPanel-inner">';

					if ( isset($tab['type']) && ($tab['type'] === 'custom') && $tab['title'] ) {
						if( apply_filters( 'reycore/woocommerce/blocks/headings', true, $tab ) ){
							$content .= sprintf('<h2>%s</h2>', esc_html( $tab['title'] ));
						}
					}

					$content .= $the_content;
				$content .= '</div>';
			}

			if( $content ):

				do_action('reycore/woocommerce/before_block_' . $key); ?>

				<div class="rey-wcPanel rey-wcPanel--<?php echo esc_attr( $key ); ?> rey-wcPanel--ord-<?php echo $i ?>">
					<?php echo $content; ?>
				</div>

				<?php
				do_action('reycore/woocommerce/after_block_' . $key);
			endif;

			$i++;

		endforeach; ?>
	</div>

	<?php
	// deprecated
	do_action('reycore/woocommerce/before_blocks_review'); ?>

<?php endif;

do_action('reycore/woocommerce/product/tabs/after', 'blocks');
