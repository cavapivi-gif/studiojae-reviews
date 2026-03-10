<?php
/**
 * Template part for displaying posts list.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package rey
 */ ?>

<div class="rey-postList">

	<?php
	// Load posts loop.
	while ( have_posts() ) {
		the_post();
		get_template_part( 'template-parts/content/content' );
	} ?>

</div>
<!-- .rey-postList -->
