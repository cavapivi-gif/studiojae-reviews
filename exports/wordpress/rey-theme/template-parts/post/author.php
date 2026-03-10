<?php
/**
 * The template for displaying Author info
 * @since 1.0.0
 */

if ( (bool) get_the_author_meta( 'description' ) ) : ?>
<div class="rey-postAuthor">
	<div class="rey-postAuthor__avatar">
		<?php
		echo get_avatar( get_the_author_meta( 'ID' ) );  ?>
	</div>
	<div class="rey-postAuthor__content">

	<span class="rey-postAuthor__by">
			<?php
			esc_html_e( 'Written by:', 'rey' );
			?>
		</span>

		<h2 class="rey-postAuthor__title">
			<?php
				if( (bool) get_the_author_meta('first_name') || (bool) get_the_author_meta('last_name') ):
					echo esc_html( get_the_author_meta('first_name') . ' ' . get_the_author_meta('last_name') );
				else:
					echo esc_html( get_the_author() );
				endif;
			?>
		</h2>

		<p class="rey-postAuthor__description">
			<?php the_author_meta( 'description' ); ?>
		</p><!-- .rey-postAuthor__description -->

		<a class="rey-postAuthor__more" href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>" rel="author">
			<?php esc_html_e( 'View more posts', 'rey' ); ?>
		</a>

		<?php
		if ( (bool) get_the_author_meta( 'user_url' ) ) : ?>
			<a class="rey-postAuthor__url" href="<?php echo esc_url( get_the_author_meta( 'user_url' ) ); ?>">
			<?php esc_html_e( 'My Website', 'rey' ); ?>
			</a>
		<?php
		endif; ?>
	</div>
</div><!-- .rey-postAuthor -->
<?php endif; ?>
