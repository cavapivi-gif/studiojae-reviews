<?php
/**
 * Template Name: Rey - Canvas
 *
 * @package rey
 */

get_header('canvas');

	while ( have_posts() ) : the_post();
		the_content();
	endwhile; // End of the loop.

	do_action('rey/rey_canvas/after_content');

get_footer('canvas');
