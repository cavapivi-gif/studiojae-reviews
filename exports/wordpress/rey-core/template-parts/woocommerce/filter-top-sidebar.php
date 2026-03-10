<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly


$sidebar_class =  implode( ' ', array_map( 'sanitize_html_class', apply_filters('rey/content/sidebar_class', [], 'filters-top-sidebar') ) );
?>

<aside class="filters-top-sidebar widget-area <?php echo esc_attr($sidebar_class); ?>" <?php function_exists('rey__render_attributes') ? rey__render_attributes('sidebar', [
		'role' => 'complementary',
		'aria-label' => __( 'Sidebar', 'rey-core' )
	]) : ''; ?> >

	<div class="rey-topSidebarInner">

		<?php if( apply_filters('reycore/woocommerce/filters-top/show_text', get_theme_mod('ajaxfilter_topbar_buttons', true)) ): ?>
			<div class="rey-filterTop-head">
				<?php
				do_action('reycore/woocommerce/filters/top_bar__button'); ?>
				<span><?php esc_html_e('FILTERS:', 'rey-core') ?></span>
			</div>
		<?php endif; ?>

		<?php dynamic_sidebar( 'filters-top-sidebar' ); ?>

	</div>
</aside>
