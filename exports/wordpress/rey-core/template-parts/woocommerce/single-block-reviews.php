<?php
/**
 * Single Block for Reviews
 *
 * This template can be overridden by copying it to rey-child/rey-core/woocommerce/single-block-reviews.php.
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
 * @version 4.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( ! wc_review_ratings_enabled() ){
	return;
}

$reviews_count = 0;

if( $product = wc_get_product() ){
	$reviews_count = $product->get_review_count() ;
} ?>

<div class="rey-wcPanels <?php echo implode(' ', array_map('esc_attr', apply_filters('rey/woocommerce/product_panels_classes', []))) ?>">
	<?php

		do_action('reycore/woocommerce/before_block_reviews'); ?>

		<div class="rey-wcPanel rey-wcPanel--reviews">
			<div class="rey-wcPanel-inner">
				<?php

				reycore__get_template_part('template-parts/woocommerce/single-block-reviews-button', false, false, [
					'text' => sprintf( __( 'Reviews (%d)', 'rey-core' ), $reviews_count )
				]);

				comments_template(); ?>
			</div>
		</div>
		<?php

		do_action('reycore/woocommerce/after_block_reviews');
	?>
</div>
