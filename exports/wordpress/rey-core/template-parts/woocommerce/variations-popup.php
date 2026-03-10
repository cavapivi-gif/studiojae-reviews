<?php
/**
 * Variations Popup
 *
 * This template can be overridden by copying it to themes/rey-child/rey-core/woocommerce/variations-popup.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<div class="rey-productLoop-variationsForm woocommerce" data-id="<?php echo absint($args['pid']) ?>">
	<div class="product">
		<span class="rey-productLoop-variationsForm-pointer"></span>
		<span class="rey-productLoop-variationsForm-close"><?php echo reycore__get_svg_icon(['id' => 'close']) ?></span>
		<?php woocommerce_variable_add_to_cart(); ?>
	</div>
</div>
