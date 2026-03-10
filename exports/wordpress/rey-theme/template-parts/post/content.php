<div class="<?php echo esc_attr(implode(' ', apply_filters('rey/post_content/classes', [
	'rey-postContent',
	($links_style = get_theme_mod('blog_post__links', '')) ? '--links-' . $links_style : '',
]))) ?>">
	<?php
		if( !is_singular() ):
			rey__postContent();
		else:
			the_content(
				sprintf(
					wp_kses(
						/* translators: %s: Name of current post. Only visible to screen readers */
						__( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'rey' ),
						[ 'span' => [ 'class' => [] ]]
					),
					get_the_title()
				)
			);
			wp_link_pages(
				array(
					'before' => '<div class="rey-pageLinks">' . esc_html__( 'Pages:', 'rey' ),
					'after'  => '</div>',
					'link_before'      => '<span>',
					'link_after'       => '</span>',
				)
			);
		endif;
	?>
</div><!-- .rey-postContent -->
