<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly


// Check if filters are active
$target = reycore_wc__check_filter_btn();

$btn_classes =  implode( ' ', apply_filters('reycore/filters/btn_class', [
	'rey-filterBtn--pos-' . get_theme_mod('ajaxfilter_panel_btn_pos', 'right'),
	$target !== 'filters-sidebar' ? '--dnone-lg' : ''
]) );

reycore_assets()->add_styles(['rey-buttons', 'rey-wc-widgets-filter-button']); ?>

<div class="rey-filterBtn <?php echo esc_attr($btn_classes); ?> " data-target="<?php echo esc_attr($target) ?>">

	<div class="__loop-separator --dnone-md --dnone-sm"></div>

	<button class="btn btn-line rey-filterBtn__label js-rey-filterBtn-open" aria-label="<?php esc_html_e('Open filters', 'rey-core') ?>">

		<?php echo reycore__get_svg_icon(['id' => 'sliders']) ?>

		<span><?php esc_html_e('FILTER', 'rey-core') ?></span>

		<?php
		do_action('reycore/woocommerce/filters/panel__button'); ?>

	</button>

	<?php
	do_action('reycore/woocommerce/filters/panel__close_button'); ?>

</div>
