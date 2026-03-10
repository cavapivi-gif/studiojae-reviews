<?php
if (!defined('ABSPATH')) {
    exit;
}

if(!function_exists('rey__add_content_single_post')):
	/**
	 * Add content into single post
	 *
	 * @since 1.0.0
	 **/
	function rey__add_content_single_post()
	{
		$post_format = get_post_format();
		$function_name = "rey__single_post_content_" . ( (string) sanitize_title($post_format) );

		if( $post_format && function_exists($function_name) && ! is_single() && rey__postContent_type() === 'e'){
			$function_name();
		}
		else {
			rey__single_post_content();
		}
	}
endif;
add_action('rey/single_post/content', 'rey__add_content_single_post');


if(!function_exists('rey__single_post_content')):
	/**
	 * Add content into single post
	 *
	 * @since 1.0.0
	 **/
	function rey__single_post_content()
	{
		if( is_single() ){
			// Big Text
			get_template_part( 'template-parts/post/post-item-category-text' );
			// Post Header
			get_template_part( 'template-parts/post/header' );
			// Post Thumbnail
			get_template_part( 'template-parts/post/thumbnail' );
		}
		else {

			if( is_sticky() ){

				if ( rey__can_show_post_thumbnail() && $image = get_the_post_thumbnail( null, rey__the_post_thumbnail_size() ) ):
					echo '<figure class="rey-postMedia">';
						echo wp_kses_post($image);
					echo '</figure>';
				endif;
			}
			else {
				// Post Thumbnail
				get_template_part( 'template-parts/post/thumbnail' );
			}

			// Post Header
			get_template_part( 'template-parts/post/header' );
		}

		// Post Content
		get_template_part( 'template-parts/post/content' );
	}
endif;


if(!function_exists('rey__single_post_content_audio')):
	/**
	 * Add AUDIO content into single post
	 *
	 * @since 1.0.0
	 **/
	function rey__single_post_content_audio()
	{
		$audio = rey__get_post_format_content();

		// If not a single post, highlight the video file.
		if ( ! empty( $audio ) ) {
			foreach ( $audio as $i => $audio_html ) {
				if( $i > 0 ) continue;
				printf('<div class="rey-postMedia">%s</div>', $audio_html);
			}
		};

		// Post Image
		get_template_part( 'template-parts/post/image' );
		// Post Header
		get_template_part( 'template-parts/post/header' );
		// Post Content
		get_template_part( 'template-parts/post/content' );
	}
endif;


if(!function_exists('rey__single_post_content_video')):
	/**
	 * Add VIDEO content into single post
	 *
	 * @since 1.0.0
	 **/
	function rey__single_post_content_video()
	{
		$video = rey__get_post_format_content();

		// If not a single post, highlight the video file.
		if ( ! empty( $video ) ) {
			rey_assets()->add_styles('rey-embed-responsive');
			foreach ( $video as $i => $video_html ) {
				if( $i > 0 ) continue;
				printf('<div class="rey-postMedia"><div class="embed-responsive embed-responsive-16by9">%s</div></div>', $video_html);
			}
		};

		// Post Image
		get_template_part( 'template-parts/post/image' );
		// Post Header
		get_template_part( 'template-parts/post/header' );
		// Post Content
		get_template_part( 'template-parts/post/content' );
	}
endif;


if(!function_exists('rey__single_post_content_image')):
	/**
	 * Add IMAGE content into single post
	 *
	 * @since 1.0.0
	 **/
	function rey__single_post_content_image()
	{
		$image = rey__get_post_format_content();

		if($image):
			echo '<figure class="rey-postMedia">';
				echo wp_kses_post($image);
				rey__categoriesList();
			echo '</figure>';
		endif;

		// Post Image
		get_template_part( 'template-parts/post/image' );
		// Post Header
		get_template_part( 'template-parts/post/header' );
		// Post Content
		get_template_part( 'template-parts/post/content' );
	}
endif;


if(!function_exists('rey__single_post_content_excerpt')):
	/**
	 * Add EXCERPT content into single post
	 *
	 * @since 1.0.0
	 **/
	function rey__single_post_content_excerpt()
	{
		// Post Thumbnail
		get_template_part( 'template-parts/post/thumbnail' );
		// Post Header
		get_template_part( 'template-parts/post/header' );
		// Post Content
		get_template_part( 'template-parts/post/content-excerpt' );
	}
endif;


if(!function_exists('rey__single_post_content_gallery')):
	/**
	 * Add GALLERY content into single post
	 *
	 * @since 1.0.0
	 **/
	function rey__single_post_content_gallery()
	{
		// Post Gallery
		get_template_part( 'template-parts/post/gallery' );
		// Post Header
		get_template_part( 'template-parts/post/header' );
		// Post Content
		get_template_part( 'template-parts/post/content' );
	}
endif;


if(!function_exists('rey__single_post_content_link')):
	/**
	 * Add link content into single post
	 *
	 * @since 1.0.0
	 **/
	function rey__single_post_content_link()
	{ ?>
		<div class="rey-postItem-inner">
			<div class="rey-postFormat__content-bg"></div>
			<div class="rey-postFormat__content">
				<?php
				echo rey__get_svg_icon(['id' => 'link']); ?>
				<div class="rey-postFormat__content-inner">
					<?php
					the_content(); ?>
				</div>
				<div class="rey-postInfo">
					<?php
						rey__posted_by();
						rey__posted_date(false);
						rey__comment_count();
						rey__edit_link();
					?>
				</div>
			</div>
		</div>
		<?php
	}
endif;


if(!function_exists('rey__single_post_content_quote')):
	/**
	 * Add quote content into single post
	 *
	 * @since 1.0.0
	 **/
	function rey__single_post_content_quote()
	{ ?>
		<div class="rey-postItem-inner">
			<div class="rey-postFormat__content-bg">
				<?php if(rey__can_show_post_thumbnail()): ?>
					<img src="<?php echo esc_url(get_the_post_thumbnail_url( get_the_ID(), rey__the_post_thumbnail_size() )) ?>" alt="<?php the_title(); ?>">
				<?php endif; ?>
			</div>
			<div class="rey-postFormat__content">
				<div class="rey-postFormat__content-inner">
					<?php
					the_content(); ?>
				</div>
				<div class="rey-postInfo">
					<?php
						rey__posted_by();
						rey__posted_date(false);
						rey__comment_count();
						rey__edit_link();
					?>
				</div>
			</div>
		</div>
		<?php
	}
endif;


if(!function_exists('rey__single_post_content_status')):
	/**
	 * Add status content into single post
	 *
	 * @since 1.0.0
	 **/
	function rey__single_post_content_status()
	{ ?>
		<div class="rey-postFormat__content">
			<div class="rey-postFormat__content-inner">
				<?php
				the_content(); ?>
			</div>
			<div class="rey-postInfo">
				<?php
					rey__posted_by();
					rey__posted_date(false);
					rey__comment_count();
					rey__edit_link();
				?>
			</div>
		</div>
		<?php
	}
endif;


if(!function_exists('rey__post_formats_post_classes')):
	/**
	 * Filter post classes to add custom post formats css classes
	 *
	 * @since 1.0.0
	 */
	function rey__post_formats_post_classes($classes)
	{
		if( ! is_single() ){
			$post_format = get_post_format();

			switch($post_format):

				case"image":
					if( rey__get_post_format_content() ){
						$classes[] = 'has-postImage';
					}
					break;

				case"audio":
				case"video":
					if( rey__get_post_format_content() ){
						$classes[] = 'has-postMedia';
					}
					break;

			endswitch;
		}

		return $classes;
	}
endif;
add_filter('post_class', 'rey__post_formats_post_classes');


if(!function_exists('rey__get_post_format_content')):
	/**
	 * Get the post format content
	 *
	 * @since 1.0.0
	 */
	function rey__get_post_format_content()
	{
		$post_format = get_post_format();
		$post_content = apply_filters( 'the_content', get_the_content() );
		$post_format_content = false;

		switch($post_format):

			case"audio":
				// Only get audio from the content if a playlist isn't present.
				if ( false === strpos( $post_content, 'wp-playlist-script' ) ) {
					$post_format_content = get_media_embedded_in_content( $post_content, array( 'audio' ) );
				}
				break;

			case"image":
				if ( rey__can_show_post_thumbnail() ):
					$post_format_content = get_the_post_thumbnail( null, rey__the_post_thumbnail_size() );
				elseif ( $img = rey__get_first_img(get_the_ID()) ):
					$post_format_content = sprintf('<img src="%s" alt="%s"/>', esc_url($img), esc_attr(get_the_title()) );
				endif;
				break;

			case"video":
				// Only get video from the content if a playlist isn't present.
				if ( false === strpos( $post_content, 'wp-playlist-script' ) ) {
					$post_format_content = get_media_embedded_in_content( $post_content, array( 'video', 'object', 'embed', 'iframe' ) );
				}
				break;

		endswitch;

		return $post_format_content;
	}
endif;
