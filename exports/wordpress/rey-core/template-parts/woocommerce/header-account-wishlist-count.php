<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = reycore_wc__get_account_panel_args();
$style = ! empty($args['counter_layout']) ? ' --' . esc_attr($args['counter_layout']) : '--minimal'; ?>

<span class="rey-headerAccount-count rey-headerIcon-counter --hidden <?php echo $style; ?>">

	<?php
	if( class_exists('\ReyCore\WooCommerce\Tags\Wishlist') && $args['wishlist'] && $args['counter'] != '' ){
		echo \ReyCore\WooCommerce\Tags\Wishlist::get_wishlist_counter_html();
	}

	echo reycore__get_svg_icon(['id' => 'close', 'class' => '__close-icon', 'attributes' => ['data-transparent' => '', 'data-abs' => '']]) ?>

</span>
