<?php
if (!defined('ABSPATH')) {
    exit;
}

if(!function_exists('rey__standard_large_size')):
	/**
	 * Standard image size
	 *
	 * @since 2.5.0
	 **/
	function rey__standard_large_size()
	{
		return 'rey-standard-large';
	}
endif;

if(!function_exists('rey__allowed_image_sizes')):
/**
 * Get allowed images sizes
 *
 * @since 2.5.0
 **/
function rey__allowed_image_sizes()
{
	static $sizes;

	if( is_null($sizes) ){
		if( class_exists('\ACF') && ($the_sizes = get_option( REY_THEME_NAME . '_custom_post_thumbnail_size' )) ){
			$sizes = $the_sizes;
		}
		else {
			$sizes = [];
		}
	}

	return $sizes;
}
endif;

if(!function_exists('rey__the_post_thumbnail_size')):
	/**
	 * Get Post thumbnail with size
	 *
	 * @since 2.5.0
	 **/
	function rey__the_post_thumbnail_size()
	{
		static $size;

		if( is_null($size) ){
			if( ($standard_size = rey__standard_large_size()) && in_array( $standard_size, rey__allowed_image_sizes(), true ) ){
				$size = $standard_size;
			}
			else {
				$size = 'large';
			}
		}

		return $size;
	}
endif;


if (!function_exists('rey__post_footer')):
    /**
     * Adds post footer row after single post content.
     *
     * @since 1.0.0
     **/
    function rey__post_footer()
    {
        if (is_singular('post')):
            // Post Footer
            get_template_part('template-parts/post/footer');
        endif;
    }
endif;
add_action('rey/single_post/after_content', 'rey__post_footer', 10);


if (!function_exists('rey__post_author_box')):
    /**
     * Adds author box into Single's Post Footer.
     *
     * @since 1.0.0
     **/
    function rey__post_author_box()
    {
        if (get_theme_mod('post_author_box', true) && is_singular('post')):
            get_template_part('template-parts/post/author');
        endif;
    }
endif;
add_action('rey/single_post/after_content', 'rey__post_author_box', 20);


if (!function_exists('rey__post_navigation')):
    /**
     * Adds page navigation into Single's Post Footer.
     *
     * @since 1.0.0
     **/
    function rey__post_navigation()
    {
        if (!get_theme_mod('post_navigation', true)) {
            return;
        }

        if (is_singular('attachment')) {
            // Parent post navigation.
            the_post_navigation(
                array(
                    /* translators: %s: parent post link */
                    'prev_text' => sprintf(
                        wp_kses( __('<span class="rey-postNav__meta">Published in</span><span class="rey-postNav__title">%s</span>', 'rey'), ['span' => ['class' => []]] ), '%title'),
                )
            );
        } elseif (is_singular('post')) {
            // Previous/next post navigation.
            the_post_navigation(
            array(
                'next_text' => '<span class="rey-postNav__meta" aria-hidden="true">'. esc_html__('Next Post', 'rey').'</span> '.
                '<span class="screen-reader-text">'.esc_html__('Next post:', 'rey').'</span> <br/>'.
                '<span class="rey-postNav__title">%title</span>',
                'prev_text' => '<span class="rey-postNav__meta" aria-hidden="true">'.esc_html__('Previous Post', 'rey').'</span> '.
                '<span class="screen-reader-text">'.esc_html__('Previous post:', 'rey').'</span> <br/>'.
                '<span class="rey-postNav__title">%title</span>',
            )
        );
        }
    }
endif;
add_action('rey/single_post/after_content', 'rey__post_navigation', 30);

if (!function_exists('rey__comments')):
    /**
     * callback for comments template.
     *
     * @param mixed $comment
     * @param mixed $args
     * @param mixed $depth
     *
     * @since 1.0.0
     */
    function rey__comments($comment, $args, $depth)
    {
        ?>
			<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">

				<div id="comment-<?php comment_ID(); ?>" class="rey-comment">

					<header class="rey-comment__header">
						<?php if ($avatar = get_avatar($comment, $args['avatar_size'])): ?>
						<div class="rey-comment__authorAvatar vcard">
							<?php echo wp_kses_post($avatar); ?>
						</div>
						<?php endif; ?>

						<h6 class="rey-comment__author">

						<?php
						echo get_comment_author_link();

						// For comment authors who are the author of the post
						if ($post = get_post(get_the_ID())) {
							if ($comment->user_id === $post->post_author) {
								echo '<span class="rey-comment__byAuthor">[ '.esc_html__('Post Author', 'rey').' ]</span>';
							}
						} ?>
					</h6>
				</header>

				<div class="rey-comment__content">
					<?php if ($comment->comment_approved == '0'): ?>
						<em><?php esc_html_e('Comment awaiting approval', 'rey'); ?></em>
						<br />
					<?php endif; ?>
					<div class="rey-comment__commentText">
						<?php comment_text(); ?>
					</div>
				</div>

				<footer class="rey-comment__footer">
					<span class="rey-comment__date">
						<?php
						printf(
								'<time datetime="%1$s" title="%1$s">%2$s %3$s</time>',
								esc_attr(get_comment_time(DATE_W3C)),
								esc_html(human_time_diff(get_comment_time('U'), current_time('timestamp'))),
								esc_html__(' ago', 'rey')
							); ?>
					</span>
					<div class="rey-comment__reply"><?php comment_reply_link(array_merge($args, array('reply_text' => esc_html__('Reply', 'rey'), 'depth' => $depth, 'max_depth' => $args['max_depth'])), $comment->comment_ID); ?></div>
					<?php edit_comment_link(esc_html__('Edit', 'rey')); ?>
				</footer>
			</div>
		</li>
		<?php
    }
endif;

/*
 * Wrap at start comment form with HTML tag
 * @since 1.0.0
 */
add_action('comment_form_before', function () {
    echo '<div class="rey-commentForm">';
});

/*
 * Wrap at end comment form with HTML tag
 * @since 1.0.0
 */
add_action('comment_form_after', function () {
    echo '</div>';
});


if(!function_exists('rey__post_comments_btn')):
	/**
	 * Add large button before comments
	 *
	 * @since 2.1.1
	 **/
	function rey__post_comments_btn()
	{

		if( ! is_singular('post') ){
			return;
		}

		if( ! get_theme_mod('blog_post__comments_btn', false) ){
			return;
		}

		$text = esc_html__('Join the conversation', 'rey');

		if( $number = get_comments_number() ){
			$text = sprintf('%s (%d)', esc_html__('Show comments', 'rey'), $number);
		}

		$classes[] = 'btn-' . get_theme_mod('blog_post__comments_btn_style', 'secondary-outline');

		if( get_theme_mod('blog_post__comments_expanded', false) ){
			$classes[] = '--toggled';
		}

		rey_assets()->add_styles('rey-buttons');

		printf('<div data-target=".rey-postComments" class="rey-commentsBtn btn %2$s btn--block js-toggle-target" role="button"><span>%1$s</span></div>', $text, implode(' ', $classes) );

	}

	add_action('rey/comments/before', 'rey__post_comments_btn');
endif;


if (!function_exists('rey__humanDate')):
    /**
     * Get HTML for human date.
     *
     * @since 1.0.0
     */
    function rey__humanDate()
    {
        return sprintf(
            '<time datetime="%1$s">%2$s %3$s</time>',
            esc_attr(get_the_date(DATE_W3C)),
            esc_html(human_time_diff(get_the_time('U'), current_time('timestamp'))),
            esc_html__(' ago', 'rey')
        );
    }
endif;

if (!function_exists('rey__posted_on_classic')):
    /**
     * Prints HTML with meta information for the current post-date/time.
     *
     * @since 1.0.0
     */
    function rey__posted_on_classic($link = true)
    {
        $time_string = sprintf(
            '<time datetime="%1$s">%2$s</time>',
            esc_attr(get_the_date(DATE_W3C)),
            esc_html(get_the_date())
        );

        $tag = $link ? '<span class="rey-entryDate"><a href="%1$s" rel="bookmark">%2$s</a></span>' : '<span class="rey-entryDate">%2$s</span>';

        echo apply_filters('rey/post_content/posted_date', sprintf(
            $tag,
            esc_url(get_permalink()),
            $time_string
        ));
    }
endif;

if (!function_exists('rey__posted_on')):
    /**
     * Prints HTML with meta information for the human post-date/time.
     *
     * @since 1.0.0
     */
    function rey__posted_on($link = true)
    {
        $tag = $link ? '<span class="rey-entryDate"><a href="%1$s" rel="bookmark">%2$s</a></span>' : '<span class="rey-entryDate">%2$s</span>';
        echo apply_filters('rey/post_content/posted_date', sprintf(
            $tag,
            esc_url(get_permalink()),
            rey__humanDate()
        ));
    }
endif;

if (!function_exists('rey__posted_date')):
    /**
     * Prints HTML with meta information for the human post-date/time.
     *
     * @since 1.0.0
     */
    function rey__posted_date($link = true)
    {
        if ((!is_singular() && !get_theme_mod('blog_date_visibility', true)) ||
        (is_singular() && !get_theme_mod('post_date_visibility', true))) {
            return false;
        }

        $type = get_theme_mod('blog_date_type', false);

        if ($type) {
            rey__posted_on($link);
        } else {
            rey__posted_on_classic($link);
        }
    }
endif;

if (!function_exists('rey__posted_by')):
    /**
     * Prints HTML with meta information about theme author.
     *
     * @since 1.0.0
     */
    function rey__posted_by()
    {
        if ((!is_singular() && !get_theme_mod('blog_author_visibility', true)) ||
        (is_singular() && !get_theme_mod('post_author_visibility', true))) {
            return false;
        }

        echo apply_filters('rey/post_content/post_author', sprintf(
            '<span class="rey-postAuthor">%1$s %2$s</span>',
            esc_html__('By', 'rey'),
            get_the_author_posts_link()
        ));
    }
endif;

if (!function_exists('rey__comment_count')):
    /**
     * Prints HTML with the comment count for the current post.
     *
     * @since 1.0.0
     */
    function rey__comment_count()
    {
        if ((!is_singular() && !get_theme_mod('blog_comment_visibility', true)) ||
        (is_singular() && !get_theme_mod('post_comment_visibility', true))) {
            return false;
        }

        if (!post_password_required() && (comments_open() || get_comments_number())) {
            $id = get_the_ID();
            echo apply_filters('rey/post_content/comments_count', sprintf(
                wp_kses_post( __('<span class="rey-entryComment"><a href="%1$s">%2$s %3$s</a> <span class="screen-reader-text"> on %4$s</span></span>', 'rey') ),
                esc_url(get_comments_link()),
                rey__get_svg_icon(['id' => 'comments']),
                get_comments_number($id),
                get_the_title()
            ));
        }
    }
endif;

if (!function_exists('rey__tagList')):
    /**
     * Prints HTML with meta information for the tags.
     *
     * @since 1.0.0
     */
    function rey__tagList()
    {
        if (get_theme_mod('post_tags', true) && 'post' === get_post_type()) {
            $tags_list = get_the_tag_list();
            if ($tags_list) {
                echo apply_filters('rey/post_content/post_tags', sprintf(
                    '<div class="rey-postTags"><span class="screen-reader-text">%s </span>%s</div>',
                    esc_html__('Tags:', 'rey'),
                    $tags_list
                )); // WPCS: XSS OK.
            }
        }
    }
endif;

if (!function_exists('rey__categoriesList')):
    /**
     * Prints HTML with meta information for the categories.
     *
     * @since 1.0.0
     */
    function rey__categoriesList()
    {
        if (
        ((!is_singular() && get_theme_mod('blog_categories_visibility', true)) ||
            (is_singular() && get_theme_mod('post_categories_visibility', true))
        ) && 'post' === get_post_type()) {
            /* translators: used between list items, there is a space after the comma. */
            $categories_list = get_the_category_list();
            if ($categories_list) {
                echo apply_filters('rey/post_content/post_categories', sprintf(
                    '<div class="rey-postCategories"><span class="screen-reader-text">%s</span>%s</div>',
                    esc_html__('Posted in', 'rey'),
                    $categories_list
                )); // WPCS: XSS OK.
            }
        }
    }
endif;

if (!function_exists('rey__edit_link')):
    /**
     * Prints HTML with post edit link.
     *
     * @since 1.0.0
     */
    function rey__edit_link($args = [])
    {
		if( ! current_user_can('administrator') ){
			return;
		}

		if ( ! ($post = get_post()) ) {
			return;
		}

		$url = get_edit_post_link( $post->ID );

		if ( ! $url ) {
			return;
		}

		$args = wp_parse_args($args, [
			'text' => sprintf(
				wp_kses(
					/* translators: %s: Name of current post. Only visible to screen readers. */
					__('Edit <span class="screen-reader-text">%s</span>', 'rey'),
					['span' => ['class' => []]]
				),
				get_the_title( $post->ID )
			),
			'class' => 'rey-editLink',
		]);

		printf('<span class="%3$s"><a class="post-edit-link" target="_blank" href="%1$s">%2$s</a></span>', esc_url( $url ), $args['text'],$args['class'] );
    }
endif;

if(!function_exists('rey__postContent_type')):
	/**
	 * Get Post content type
	 *
	 * @since 1.0.0
	 **/
	function rey__postContent_type()
	{
		return get_theme_mod('blog_content_type', 'e');
	}
endif;

if (!function_exists('rey__postContent')):
    /**
     * Prints post content.
     *
     * @since 1.0.0
     */
    function rey__postContent()
    {
        $type = rey__postContent_type();
        if ($type == 'e') {
            the_excerpt();

            rey__excerptFooter_before();
            rey__excerptFooter();
            rey__excerptFooter_after();
        } elseif ($type == 'c') {
            the_content(
            sprintf(
                wp_kses(
                    /* translators: %s: Name of current post. Only visible to screen readers */
                    __('Continue reading<span class="screen-reader-text"> "%s"</span>', 'rey'),
                    ['span' => ['class' => []]]
                ),
                get_the_title()
            )
        );
        }
    }
endif;

if (!function_exists('rey__postDuration')):
    /**
     * Prints HTML with post read duration.
     *
     * @since 1.0.0
     */
    function rey__postDuration( $post = false )
    {
		if( $post ){
			if ( ! (is_single() && get_theme_mod('post_read_visibility', true)) ) {
				return;
			}
		}
		else {
			if ( ! get_theme_mod('blog_read_visibility', true) ) {
				return;
			}
		}

        return apply_filters('rey/post_content/post_duration', sprintf(
            '<span class="rey-postDuration">%s %s</span>',
            rey__estimated_reading_time(),
            esc_html__('min read', 'rey')
        ));
    }
endif;

if (!function_exists('rey__excerptFooter')):
    /**
     * Prints HTML with continue button.
     *
     * @since 1.0.0
     */
    function rey__excerptFooter()
    {

		$class = 'btn btn-line-active';
		$button_content = apply_filters('rey/blog/read_more/text', esc_html__('CONTINUE READING', 'rey'));

		if( apply_filters('rey/blog/read_more/arrow', false) ){
			$button_content = "<span>{$button_content}</span>" . rey__arrowSvg();
			$class = '--arrow';
		}

        printf(
            '<a class="rey-post-moreLink %5$s" href="%1$s">%2$s<span class="screen-reader-text"> %3$s</span></a> %4$s',
            esc_url(get_permalink()),
            $button_content,
            get_the_title(),
            rey__postDuration(),
			$class );
    }
endif;

if (!function_exists('rey__content_more_link')):
    /**
     * Wrap and alter content more link.
     *
     * @since 1.0.0
     */
    function rey__content_more_link($more_link_element)
    {
        return
        rey__excerptFooter_before(false).
        str_replace('more-link', 'rey-post-moreLink', $more_link_element).
        rey__postDuration().
        rey__excerptFooter_after(false);
    }
endif;
add_filter('the_content_more_link', 'rey__content_more_link');

if (!function_exists('rey__excerptFooter_before')):
    /**
     * wrap before more tag.
     *
     * @since 1.0.0
     */
    function rey__excerptFooter_before($echo = true)
    {
        if ($echo) {
            echo '<div class="rey-postContent-more">';
        } else {
            return '<div class="rey-postContent-more">';
        }
    }
endif;

if (!function_exists('rey__excerptFooter_after')):
    /**
     * wrap after more tag.
     *
     * @since 1.0.0
     */
    function rey__excerptFooter_after($echo = true)
    {
        if ($echo) {
            echo '</div>';
        } else {
            return '</div>';
        }
    }
endif;

/*
 * Excerpt ending symbol
 *
 * @since 1.0.0
 */
add_filter('excerpt_more', function ($more) {
    return ' [&hellip;]';
}, 10);


if(!function_exists('rey__allow_post_iframes_filter')):
	/**
	 * Add iFrame to allowed wp_kses_post tags
	 *
	 * @param array  $tags Allowed tags, attributes, and/or entities.
	 * @param string $context Context to judge allowed tags by. Allowed values are 'post'.
	 *
	 * @return array
	 */
	function rey__allow_post_iframes_filter( $tags, $context ) {

		// Only change for users who can publish posts
		if ( !current_user_can( 'publish_posts' ) ) {
			return $tags;
		}

		if ( 'post' === $context ) {
			// Allow iframes and the following attributes
			$tags['iframe'] = array(
				'src'             => true,
				'height'          => true,
				'width'           => true,
				'frameborder'     => true,
				'allowfullscreen' => true,
				'allow'           => true,
				'title'           => true,
			);
		}
		return $tags;
	}
endif;
add_filter( 'wp_kses_allowed_html', 'rey__allow_post_iframes_filter', 10, 2 );
