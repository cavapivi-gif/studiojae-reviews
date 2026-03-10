<?php
/**
 * The template for displaying search results pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package rey
 */

get_header(); ?>

<?php

rey_assets()->add_styles('rey-blog');

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

			?>
			<p><?php esc_html_e( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'rey' ); ?></p>
			<?php
				get_search_form();

		endif;
		?>
	</div>

<?php
rey_action__after_site_container(); ?>

<?php
get_footer();
