<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

if( ! ($opts = (array)$args['options']) ){
	return;
} ?>

<div class="rey-pppSelector rey-loopSelectList">
	<label class="btn btn-line" for="catalog-ppp-selector" tabindex="0">
		<span><?php echo $args['label'] ?> <?php echo $args['selected']; ?></span>
		<select aria-label="<?php esc_html__('Products per page', 'rey-core') ?>" id="catalog-ppp-selector" tabindex="-1">
			<?php
			foreach ( $opts as $key => $value) {
				printf( '<option value="%1$s" %2$s>%1$s</option>',
					$value,
					$value === $args['selected'] ? 'selected="selected"': ''
				);
			}
			?>
		</select>
	</label>
</div>
