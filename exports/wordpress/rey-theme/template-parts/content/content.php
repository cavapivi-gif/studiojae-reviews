<?php
/**
 * Template part for displaying posts.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package rey
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('rey-postItem'); ?>>
	<?php
		/**
		 * Prints content into single post
		 * @hook rey/single_post/content
		 */
		rey_action__single_post_content();
	?>
</article><!-- #post-${ID} -->

<?php
/**
 * Prints content after single post
 * @hook rey/single_post/after_content
 */
rey_action__single_post_after_content();
