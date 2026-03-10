<?php
/**
 * Template Name: Rey - Multi-columns Page
 *
 * @package rey
 */

get_header();

	rey_assets()->add_styles('rey-tpl-page');

	rey_action__before_site_container();

		/**
		 * Displays title
		 */
		do_action('rey/content/title');

		while ( have_posts() ) : the_post();
		?>
		<div class="rey-pageContent"><?php the_content(); ?></div><?php
		endwhile; // End of the loop.

	rey_action__after_site_container();

get_footer();
