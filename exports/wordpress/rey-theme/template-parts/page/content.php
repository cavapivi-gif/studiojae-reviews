<?php
/**
 * Template part for displaying page content in page.php
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Rey
 */

?>

<article id="page-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="rey-pageContent">
		<?php
			the_content();

			wp_link_pages(
				array(
					'before' => '<div class="rey-pageLinks">' . esc_html__( 'Pages:', 'rey' ),
					'after'  => '</div>',
					'link_before'      => '<span>',
					'link_after'       => '</span>',
				)
			);
		?>
	</div><!-- .rey-pageContent -->
</article><!-- #page-## -->
