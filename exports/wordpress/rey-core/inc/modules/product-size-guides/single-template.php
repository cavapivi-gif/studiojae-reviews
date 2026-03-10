<?php get_header(); ?>

<div class="rey-sgTemplate">
	<?php
	while ( have_posts() ) : the_post();
		reycore__get_template_part( 'template-parts/page/content' );
	endwhile;
	?>
</div>

<?php
get_footer();
