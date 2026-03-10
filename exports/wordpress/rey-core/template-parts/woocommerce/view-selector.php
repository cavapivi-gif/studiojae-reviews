<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Get variations
$options = array_filter( array_map( 'absint', explode(',', trim( get_theme_mod('loop_view_switcher_options', '2, 3, 4') ) ) ) );
$classes = [
	'rey-viewSelector',
	'rey-loopInlineList'
];
if( $mobile_selector = get_theme_mod('loop_view_switcher_mobile', false) ){
	$classes[] = '--mobile-enabled';
}
if( isset($_REQUEST['orderby']) ){
	$classes[] = '--is-visible';
} ?>

<div class=" <?php echo implode(' ', $classes) ?>">

	<span class="rey-loopInlineList__label"><?php esc_html_e('VIEW', 'rey-core') ?></span>

	<ul class="rey-loopInlineList-list rey-viewSelector-list-dk">
		<?php
			foreach ($options as $key => $value) {
				if( $value > 1 && $value <= 6 ){
					printf( '<li data-count="%1$s" tabindex="0">%1$s</li>', $value );
				}
			}
		?>
	</ul>

	<?php if( $mobile_selector ): ?>
	<ul class="rey-viewSelector-list-mb">
		<li data-count="1"><span></span></li>
		<li data-count="2"><span></span><span></span><span></span><span></span></li>
	</ul>
	<?php endif; ?>

	<div class="__loop-separator --dnone-md --dnone-sm"></div>

</div>
