<?php
/**
 * Template Name: Rey - Full Width
 *
 * @package rey
 */

get_header();

	rey_action__before_site_container();

		while ( have_posts() ) : the_post();
			the_content();
		endwhile; // End of the loop.

	rey_action__after_site_container();

get_footer();
