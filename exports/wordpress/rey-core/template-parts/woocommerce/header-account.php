<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = reycore_wc__get_account_panel_args();

reycore_assets()->add_styles('rey-header-icon');

$aria_label = esc_html__('Open Account details', 'rey-core');
$btn_text = '';

if( in_array($args['button_type'], ['text', 'both_before', 'both_after', 'both_above'], true) ){

	$btn_text = do_shortcode($args['button_text']);

	if( is_user_logged_in() && $args['button_text_logged_in']  ){
		$btn_text = do_shortcode($args['button_text_logged_in']);
	}

	if( $btn_text ){
		$aria_label = $btn_text;
	}

}

$wrapper_classes = [
	'rey-headerAccount',
	'rey-headerIcon',
	reycore__get_option('header_layout_type') === 'default' && get_theme_mod('header_account_mobile', false) ? 'd-md-block d-none' : '',
];

$text_position_map = [
	'both_before' => 'before',
	'both_after' => 'after',
	'both_above' => 'under',
];

$position_class = isset($text_position_map[ $args['button_type'] ]) ? $text_position_map[ $args['button_type'] ] : $args['button_type'];

$button_classes = [
	'btn',
	'rey-headerIcon-btn',
	'js-rey-headerAccount',
	'rey-headerAccount-btn',
	'rey-headerAccount-btn--' . $position_class, // legacy
	'--hit-' . $position_class,
]; ?>

<div class="<?php echo esc_attr(implode(' ', $wrapper_classes)) ?>">
    <button class="<?php echo esc_attr(implode(' ', $button_classes)) ?>">

		<span class="screen-reader-text"><?php echo wp_kses_post($aria_label); ?></span>

		<?php

			$btn_html = '';

			// icons are shown always
			if( $args['icon_type'] === 'reycore-icon-heart' ){
				$btn_icon = reycore__get_svg_icon(['id' => 'heart', 'class' => 'rey-headerAccount-btnIcon']);
			}
			else {
				$btn_icon = reycore__get_svg_icon(['id' => 'user', 'class' => 'rey-headerAccount-btnIcon']);
			}

			$btn_icon = sprintf('<span class="__icon rey-headerIcon-icon" aria-hidden="true">%s</span>', apply_filters('reycore/woocommerce/header/account_icon', $btn_icon ));

			// add counter
			ob_start();
			reycore__get_template_part('template-parts/woocommerce/header-account-wishlist-count');
			$btn_icon .= ob_get_clean();

			if( $btn_text ){
				$btn_html = sprintf('<span class="rey-headerAccount-btnText rey-headerIcon-btnText">%s</span>', $btn_text );
				add_filter('reycore/woocommerce/account_panel/account_title', function() use ($btn_text) {
					return $btn_text;
				});
			}

			echo $btn_html;

			echo $btn_icon;

		?>
    </button>

</div>
<!-- .rey-headerAccount-wrapper -->
