<?php
/**
 * The template for displaying the footer
 * Contains the closing of the #content div and all content after.
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 * @package rey
 */ ?>

	</div>
	<!-- .rey-siteContent -->

</div>
<!-- .rey-siteWrapper -->

<?php
/**
 * Prints content after site wrapper
 * @hook rey/after_site_wrapper
 */
rey_action__after_site_wrapper(); ?>

<?php wp_footer(); ?>
</body>
</html>
