<?php
/**
 * The template for displaying Comments.
 *
 * The area of the page that contains both current comments
 * and the comment form. The actual display of comments is
 * handled by a callback to generate_comment() which is
 * located in the inc/template-tags.php file.
 *
 * @package rey
 */

if ( post_password_required() ) {
	return;
}

do_action('rey/comments/before'); ?>

<section id="comments" class="rey-postComments post-comments">

	<?php if ( have_comments() ) { ?>
		<h3 class="rey-postComments__title">
			<?php
				$comments_number = get_comments_number();
				if ( '1' === $comments_number ) {
					/* translators: %s: post title */
					printf( esc_html( _x( 'One Reply to &ldquo;%s&rdquo;', 'comments title', 'rey' ) ), get_the_title() );
				} else {
					printf(
						/* translators: 1: number of comments, 2: post title */
						esc_html( _nx(
							'%1$s Reply to &ldquo;%2$s&rdquo;',
							'%1$s Replies to &ldquo;%2$s&rdquo;',
							$comments_number,
							'comments title',
							'rey'
						) ),
						intval( number_format_i18n( $comments_number ) ),
						get_the_title()
					);
				}
			?>
		</h3>

		<ol class="rey-postComments__commentList">
			<?php
			wp_list_comments(array(
				'style'			=> 'ol',
				'avatar_size'	=> 32,
				'max_depth'		=> 4,
				'short_ping'    => true,
				'callback'		=> 'rey__comments',
				'type'			=> 'all'
			));
			?>
		</ol><!-- .rey-postComments__commentList -->

		<?php the_comments_navigation(); ?>

	<?php } // End if(). ?>

	<?php
		// If comments are closed and there are comments, let's leave a little note, shall we?
		if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) {
		?>
			<p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'rey' ); ?></p>
		<?php } ?>

		<?php
		$commenter = wp_get_current_commenter();

			$custom_comment_field = '<div class="rey-commentForm__comment form-row"><div class="col"><textarea class="form-control" id="comment" name="comment" cols="45" rows="8" aria-required="true" placeholder="'. esc_attr__('Add your comment ..', 'rey') .'"></textarea></div></div>';
			$aria_req = " required='required'";
			$req_symbol = esc_attr__(' *', 'rey');
			$cmnt_fields =  array(

				'author' =>
				'<div class="form-row"><div class="col rey-commentForm__author"><input class="form-control" id="author" placeholder="'. esc_attr__('Name', 'rey') . $req_symbol . '" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) .'" size="30"' . $aria_req . ' /></div>',

				'email' =>
				'<div class="col rey-commentForm__email"><input class="form-control" id="email" placeholder="'. esc_attr__('Email', 'rey') . $req_symbol . '" name="email" type="text" value="' . esc_attr(  $commenter['comment_author_email'] ) .'" size="30"' . $aria_req . ' /></div></div>',

				'url' =>
				'<div class="form-row"><div class="col rey-commentForm__url"><input class="form-control" id="url" placeholder="'. esc_attr__('Website', 'rey') .'" name="url" type="text" value="' . esc_attr( $commenter['comment_author_url'] ) .'" size="30" /></div></div>',
			);

			rey_assets()->add_styles(['rey-buttons', 'rey-form-row']);

			comment_form( apply_filters('rey/comment_form/args', [
				'comment_field'			=> $custom_comment_field,
				'comment_notes_after'	=> '',
				'logged_in_as' 			=> '',
				'comment_notes_before' 	=> '',
				'title_reply'			=> esc_html__('Join the conversation', 'rey'),
				'cancel_reply_link'		=> esc_html__('Cancel reply', 'rey'),
				'label_submit'			=> esc_html__('Post Comment', 'rey'),
				'class_form'           => 'comment-form rey-commentForm__form',
				'class_submit'         => 'btn btn-primary rey-commentForm__submit',
				'title_reply_before'   => '<h3 id="reply-title" class="comment-reply-title rey-commentForm__replyTitle">',
				'fields' => $cmnt_fields,
			]));
		?>

</section><!-- .comments-area -->

<?php do_action('rey/comments/after'); ?>
