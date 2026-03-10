<?php
/**
 * Filter Panel Markup
 *
 * This template can be overridden by copying it to rey-child/rey-core/woocommerce/filter-panel-sidebar.php.
 *
 * HOWEVER, on occasion Rey will need to update template files and you
 * (the Rey theme user) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	https://support.reytheme.com/kb/override-template-files/
 * @version 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<div class="rey-filterPanel-wrapper rey-sidePanel" data-lazy-hidden data-align="<?php echo esc_attr(get_theme_mod('ajaxfilter_panel_pos', 'right')) ?>" id="js-filterPanel">

	<?php do_action('reycore/filters_sidebar/before_panel'); ?>

	<div class="rey-filterPanel">

		<header class="rey-filterPanel__header">
			<?php do_action('reycore/filters_sidebar/panel_header'); ?>
		</header>

		<div class="rey-filterPanel-content-wrapper">
			<div class="rey-filterPanel-content" >
				<?php do_action('reycore/filters_sidebar/before_widgets'); ?>
				<?php dynamic_sidebar( 'filters-sidebar' ); ?>
				<?php do_action('reycore/filters_sidebar/after_widgets'); ?>
			</div>
		</div>

	</div>
	<?php do_action('reycore/filters_sidebar/after_panel'); ?>
</div>
