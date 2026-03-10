<?php
/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package rey
 */

get_header(); ?>

<?php
rey_action__before_site_container(); ?>

	<?php
	while ( have_posts() ) : the_post(); ?>
		<?php
		/**
		 * Displays title
		 */
		do_action('rey/content/title'); ?>

		<?php
		// Post Thumbnail
		get_template_part( 'template-parts/post/thumbnail' ); ?>

		<?php
		get_template_part( 'template-parts/page/content', 'page' );

		// If comments are open or we have at least one comment, load up the comment template.
		if ( comments_open() || get_comments_number() ) :
			comments_template();
		endif;

	endwhile; // End of the loop.
	?>

<?php
rey_action__after_site_container(); ?>

<?php get_footer();
