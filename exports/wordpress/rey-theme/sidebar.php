<?php
/**
 * The sidebar containing the main widget area
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package rey
 */

$sidebar = rey__get_sidebar_name();

if ( ! is_active_sidebar( $sidebar ) ) {
	return;
}

rey_assets()->add_styles('rey-sidebar');

$sidebar_class =  implode( ' ', array_map( 'sanitize_html_class', apply_filters('rey/content/sidebar_class', [
	sanitize_title($sidebar),
	( rey__is_blog_list() && get_theme_mod('blog_sidebar_boxed', true) ? '--boxed-sidebar' : '' )
], $sidebar) ) );
?>

<aside class="rey-sidebar widget-area <?php echo esc_attr($sidebar_class); ?>" <?php rey__render_attributes('sidebar', [
	'role' => 'complementary',
	'aria-label' => __( 'Sidebar', 'rey' )
]); ?>>

	<div class="rey-sidebarInner">
		<?php dynamic_sidebar( $sidebar ); ?>
	</div>
</aside>
