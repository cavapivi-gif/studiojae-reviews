<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package rey
 */

get_header(); ?>

<?php
rey_action__before_site_container(); ?>

	<div class="rey-siteMain-inner">

		<?php
		while ( have_posts() ) :
			the_post();

			get_template_part( 'template-parts/content/content' );

			// If comments are open or we have at least one comment, load up the comment template.
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}

		endwhile; // End of the loop. ?>

	</div>
	<!-- .rey-siteMain-inner -->

<?php
rey_action__after_site_container(); ?>

<?php
get_footer();
