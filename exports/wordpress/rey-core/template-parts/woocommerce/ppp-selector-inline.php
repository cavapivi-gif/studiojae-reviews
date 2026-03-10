<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

if( ! ($opts = (array)$args['options']) ){
	return;
} ?>

<div class="rey-pppSelector rey-loopInlineList">

	<span class="rey-loopInlineList__label"><?php echo $args['label'] ?></span>

	<ul class="rey-loopInlineList-list">
	<?php
	foreach ($opts as $key => $value) {
		printf( '<li data-count="%1$s" class="%2$s" tabindex="0">%1$s</li>',
			$value,
			$value === $args['selected'] ? 'is-active': ''
		);
	}
	?>
	</ul>

	<div class="__loop-separator --dnone-md --dnone-sm"></div>

</div>
