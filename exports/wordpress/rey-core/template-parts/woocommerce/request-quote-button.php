<?php
defined( 'ABSPATH' ) || exit;

$btn_classes = array_merge([
	'btn',
	'rey-requestQuote-btn',
	esc_attr( get_theme_mod('request_quote__btn_style', 'btn-line-active') ),
], $args['classes']); ?>

<div class="rey-requestQuote-wrapper">

	<a href="<?php echo get_permalink(); ?>" class=" <?php echo implode(' ', $btn_classes) ?> js-requestQuote" data-id="<?php echo get_the_ID() ?>" data-title="<?php echo $args['product_data']['title'] ?>" data-sku="<?php echo $args['product_data']['sku'] ?>">
		<?php echo $args['button_text']; ?>
	</a>

	<?php if( $after_text = get_theme_mod('request_quote__btn_text_after', '') ): ?>
		<div class="rey-requestQuote-text"><?php echo reycore__parse_text_editor($after_text); ?></div>
	<?php endif; ?>

</div>
