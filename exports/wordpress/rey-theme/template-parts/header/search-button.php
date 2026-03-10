<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

rey_assets()->add_styles(['rey-header-icon']); ?>

<div class="rey-headerSearch rey-headerSearch--form rey-headerIcon">

	<button class="btn rey-headerIcon-btn js-rey-headerSearch-form" aria-label="<?php esc_html_e('Search', 'rey') ?>">
		<?php echo rey__get_svg_icon(['id' => 'search', 'class' => 'icon-search']) ?>
		<?php echo rey__get_svg_icon(['id' => 'close', 'class' => 'icon-close']) ?>
	</button>

	<?php get_search_form() ?>
</div>
