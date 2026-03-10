<?php
/**
 * The template for displaying 404 pages (not found).
 *
 * @see https://codex.wordpress.org/Creating_an_Error_404_Page
 */
get_header(); ?>

<div class="rey-page404 <?php echo apply_filters('rey/404page/container_classes', '') ?>">
	<div class="container">
		<?php do_action('rey/404page'); ?>
	</div>
</div>

<?php
get_footer();
