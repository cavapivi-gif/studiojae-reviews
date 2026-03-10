<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<div class="rey-quickviewPanel woocommerce <?php echo esc_attr(implode(' ', $args['classes'])) ?>" id="js-rey-quickviewPanel" data-lazy-hidden>

	<div class="rey-quickview-container" data-openstyle="<?php echo esc_attr($args['panel_style']) ?>"></div>

	<button class="btn rey-quickviewPanel-close js-rey-quickviewPanel-close" aria-label="<?php esc_attr__('CLOSE', 'rey-core') ?>" ><?php echo reycore__get_svg_icon(['id' => 'close']) ?></button>

	<div class="rey-lineLoader"></div>

</div>
