<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

reycore_assets()->add_scripts('reycore-ajaxfilter-droppanel');
reycore_assets()->add_styles(['reycore-ajaxfilter-droppanel']); ?>

<div class="reyajfilter-dp">

	<?php
	if( isset($args['active_count']) && ($active_count = absint($args['active_count'])) ){
		$args['button'] .= sprintf('<span class="reyajfilter-dpText-count">%d</span>', $active_count);
	}

	printf(
		'<button class="reyajfilter-dp-btn %3$s" data-keep-active="%4$s"><span class="reyajfilter-dpText">%1$s</span>%2$s</button>',
		$args['button'],
		reycore__get_svg_icon(['id'=>'arrow']),
		$args['selection'] ? '--selection' : '',
		$args['keep_active'] ? 1 : 0
	); ?>

	<div class="reyajfilter-dp-drop" aria-hidden="true">

		<?php echo $args['html']; ?>

		<?php
		if( $args['selection'] && $args['key'] ){
			$key = is_array($args['key']) ? implode(',', $args['key']) : $args['key'];
			printf('<button class="reyajfilter-dp-clear" data-key="%2$s">%1$s</button>', $args['clear_text'], esc_attr( $key ));
		} ?>

	</div><!-- .reyajfilter-dp-drop -->

</div><!-- .reyajfilter-dp -->
