<header class="rey-postHeader">
	<?php
	rey__categoriesList();

	if ( is_singular() ) :
		/**
		 * Displays title
		 */
		do_action('rey/content/title');
	else :
		$heading_tag = apply_filters('rey/loop/post/title_tag', 'h2');
		the_title(
			sprintf( '<%3$s class="rey-postTitle %1$s"><a href="%2$s" rel="bookmark"><span class="rey-hvLine">',
				get_theme_mod('blog_title_animation', true) ? 'rey-hvLine-parent' : '',
				esc_url( get_permalink() ),
				$heading_tag
			),
			sprintf('</span></a></%s>', $heading_tag)
		);
	endif;
	?>

	<div class="rey-postInfo">
		<?php
			rey__posted_by();
			rey__posted_date();
			rey__comment_count();
			rey__edit_link();
			echo rey__postDuration(true);
		?>
	</div>

</header><!-- .rey-postHeader -->
