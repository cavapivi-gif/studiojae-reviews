<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = reycore_wc__get_header_search_args();

reycore_assets()->add_styles('rey-header-icon');

$aria_label = esc_html__('Search open', 'rey-core');
$text = '';

// legacy
if( get_theme_mod('header_search_text_enable', false) ){
	$text = esc_html__('Search', 'rey-core');
	$aria_label = $text;
}

if( $content_before = $args['search__before_content'] ){
	$text = $content_before;
	$aria_label = $content_before;
}

$classes = [];

if( isset($args['classes']) ){
	$classes[] = $args['classes'];
} ?>

<div class="rey-headerSearch rey-headerIcon js-rey-headerSearch <?php echo implode(' ', $classes); ?>">

	<button class="btn rey-headerIcon-btn rey-headerSearch-toggle js-rey-headerSearch-toggle">

		<?php

		if(!empty($text)){
			echo '<span class="rey-headerSearch-text rey-headerIcon-btnText">' . $text . '</span>';
		} ?>

		<?php
		if( $icon = apply_filters('reycore/woocommerce/header/search_icon', reycore__get_svg_icon([ 'id'=> 'search', 'class' => 'icon-search' ]) ) ) {
			printf('<span class="__icon rey-headerIcon-icon" aria-hidden="true">%s %s</span>', $icon, reycore__get_svg_icon(['id' => 'close', 'class' => 'icon-close', 'attributes' => ['data-abs'=>'', 'data-transparent'=>'']]));
		} ?>

		<span class="screen-reader-text"><?php echo wp_kses_post($aria_label); ?></span>

	</button>
	<!-- .rey-headerSearch-toggle -->

</div>
