<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

reycore_assets()->add_styles(['rey-buttons', 'rey-wc-widgets-filter-button']); ?>

<div class="rey-filterBtn--noRes">

	<?php if( is_tax() || is_shop() ): ?>
		<button class="btn btn-line-active js-rey-filterBtn-open" aria-label="<?php esc_html_e('REFINE FILTERS', 'rey-core') ?>">
			<?php esc_html_e('REFINE FILTERS', 'rey-core') ?>
		</button>
	<?php endif; ?>

	<button class="btn btn-line-active js-rey-filter-reset" data-location="<?php echo reycore_wc__reset_filters_link(); ?>" aria-label="<?php esc_html_e('Reset filters', 'rey-core') ?>">
		<?php esc_html_e('RESET', 'rey-core') ?>
	</button>

</div>
