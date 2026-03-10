<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

rey_assets()->add_styles(['rey-hbg', 'rey-header-icon']);

$classes = [
	'btn',
	'rey-mainNavigation-mobileBtn',
	'rey-headerIcon',
	'__hamburger',
];

$attributes = [
	'aria-label' => esc_html__('Open menu', 'rey'),
];

$args = rey__header_nav_params();

if(  ! empty($args['load_hamburger']) ){
	if( isset($args['load_hamburger']['attributes']) && ($custom_attributes = $args['load_hamburger']['attributes']) ){
		$attributes = array_merge($attributes, $custom_attributes);
	}
	if( isset($args['load_hamburger']['classes']) && ($custom_classes = $args['load_hamburger']['classes']) ){
		$classes = array_merge($classes, $custom_classes);
	}
} ?>

<button class="<?php echo esc_attr( implode(' ', $classes) ) ?>" <?php echo rey__implode_html_attributes( $attributes ) ?>>
	<div class="__bars">
		<span class="__bar"></span>
		<span class="__bar"></span>
		<span class="__bar"></span>
	</div>
	<?php echo rey__get_svg_icon(['id' => 'close']); ?>
</button>
<!-- .rey-mainNavigation-mobileBtn -->
