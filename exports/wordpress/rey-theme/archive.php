<?php
/**
 * The template for displaying archive pages
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package rey
 */

get_header(); ?>

<?php
rey_action__before_site_container(); ?>

	<div class="rey-siteMain-inner">
		<?php
		/**
		 * Displays title
		 */
		do_action('rey/content/title'); ?>

		<?php
		if ( have_posts() ) :

			rey_action__post_list();

		else :

			// If no content, include the "No posts found" template.
			get_template_part( 'template-parts/content/content', 'none' );

		endif;
		?>
	</div>

<?php
rey_action__after_site_container(); ?>

<?php
get_footer();
