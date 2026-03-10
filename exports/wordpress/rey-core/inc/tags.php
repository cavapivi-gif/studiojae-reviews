<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if(!function_exists('reycore__texts')):
	/**
	 * Text strings
	 *
	 * @since 1.6.6
	 **/
	function reycore__texts( $text = '' )
	{
		$texts = apply_filters('reycore/texts', [
			'qty' => esc_attr_x( 'Qty', 'Product quantity input tooltip', 'rey-core' ),
			'cannot_update_cart' => esc_html__('Couldn\'t update cart!', 'rey-core'),
			'added_to_cart_text' => esc_html__('ADDED TO CART', 'rey-core'),
		]);

		if( !empty($text) && isset($texts[$text]) ){
			return $texts[$text];
		}
	}
endif;


if(!function_exists('reycore__social_sharing_icons_list')):
	/**
	 * Social Icons List
	 *
	 * @helper https://gist.github.com/HoldOffHunger/1998b92acb80bc83547baeaff68aaaf4
	 *
	 * @since 1.3.0
	 **/
	function reycore__social_sharing_icons_list()
	{
		$list = [
			'digg' => [
				'title' => esc_html__('Digg', 'rey-core'),
				'url' => 'http://digg.com/submit?url={url}',
				'icon' => 'digg',
				'color' => '005be2'
			],
			'facebook' => [
				'title' => esc_html__('FaceBook', 'rey-core'),
				'url' => 'https://www.facebook.com/sharer/sharer.php?u={url}',
				'icon' => 'facebook',
				'url_attributes' => sprintf('data-share-props=\'%s\'', wp_json_encode([
					'name' => 'facebook-share',
					'size' => 'width=580,height=296'
				])),
				'color' => '#1877f2'
			],
			'facebook-f' => [
				'title' => esc_html__('Facebook', 'rey-core'),
				'url' => 'https://www.facebook.com/sharer/sharer.php?u={url}',
				'icon' => 'facebook-f',
				'url_attributes' => sprintf('data-share-props=\'%s\'', wp_json_encode([
					'name' => 'facebook-share',
					'size' => 'width=580,height=296'
				])),
				'color' => '#1877f2'
			],
			'linkedin' => [
				'title' => esc_html__('LinkedIn', 'rey-core'),
				'url' => 'http://www.linkedin.com/shareArticle?mini=true&url={url}&title={title}',
				'icon' => 'linkedin',
				'url_attributes' => sprintf('data-share-props=\'%s\'', wp_json_encode([
					'name' => 'linkedin-share',
					'size' => 'width=930,height=720'
				])),
				'color' => '#007bb5'
			],
			'pinterest' => [
				'title' => esc_html__('Pinterest', 'rey-core'),
				'url' => 'http://pinterest.com/pin/create/button/?url={url}&description={title}',
				'icon' => 'pinterest',
				'url_attributes' => sprintf('data-share-props=\'%s\'', wp_json_encode([
					'name' => 'pinterest-share',
					'size' => 'width=490,height=530'
				])),
				'color' => '#e82b2d'
			],
			'pinterest-p' => [
				'title' => esc_html__('Pinterest', 'rey-core'),
				'url' => 'http://pinterest.com/pin/create/button/?url={url}&description={title}',
				'icon' => 'pinterest-p',
				'url_attributes' => sprintf('data-share-props=\'%s\'', wp_json_encode([
					'name' => 'pinterest-share',
					'size' => 'width=490,height=530'
				])),
				'color' => '#e82b2d'
			],
			'reddit' => [
				'title' => esc_html__('Reddit', 'rey-core'),
				'url' => 'https://reddit.com/submit?url={url}&title={title}',
				'icon' => 'reddit',
				'color' => '#ff4500'
			],
			'skype' => [
				'title' => esc_html__('Skype', 'rey-core'),
				'url' => 'https://web.skype.com/share?url={url}&text={text}',
				'icon' => 'skype',
				'color' => '#00aff0'
			],
			'tumblr' => [
				'title' => esc_html__('Tumblr', 'rey-core'),
				'url' => 'https://www.tumblr.com/widgets/share/tool?canonicalUrl={url}&title={title}',
				'icon' => 'tumblr',
				'color' => '#35465d'
			],
			'twitter' => [
				'title' => esc_html__('X (Twitter)', 'rey-core'),
				'url' => 'http://twitter.com/share?text={title}&url={url}',
				'icon' => 'twitter',
				'url_attributes' => sprintf('data-share-props=\'%s\'', wp_json_encode([
					'name' => 'twitter-share',
					'size' => 'width=550,height=235'
				])),
				'color' => '#1da1f2'
			],
			'vk' => [
				'title' => esc_html__('VK', 'rey-core'),
				'url' => 'http://vk.com/share.php?url={url}&title={title}',
				'icon' => 'vk',
				'color' => '#4a76a8'
			],
			'weibo' => [
				'title' => esc_html__('Weibo', 'rey-core'),
				'url' => 'http://service.weibo.com/share/share.php?url={url}&appkey=&title={title}&pic=&ralateUid=',
				'icon' => 'weibo',
				'color' => '#df2029'
			],
			'whatsapp' => [
				'title' => esc_html__('WhatsApp', 'rey-core'),
				'url' => 'https://wa.me/?text={title}+{url}',
				'icon' => 'whatsapp',
				'color' => '#25d366'
			],
			'xing' => [
				'title' => esc_html__('Xing', 'rey-core'),
				'url' => 'https://www.xing.com/spi/shares/new?url={url}',
				'icon' => 'xing',
				'color' => '#026466'
			],
			'mail' => [
				'title' => esc_html__('Mail', 'rey-core'),
				'url' => 'mailto:?body={url}',
				'icon' => 'envelope',
				// 'color' => ''
			],
			'copy' => [
				'title' => esc_html__('Copy URL', 'rey-core'),
				'url' => '#',
				'icon' => 'link',
				'url_attributes' => 'data-url="{url}" class="js-copy-url u-copy-url" onclick=\'(function(e){ const temp = document.createElement("input");document.body.appendChild(temp);temp.value = e.currentTarget.getAttribute("data-url");temp.select();document.execCommand("copy");temp.remove();e.currentTarget.style.opacity = "0.5";})(arguments[0]);return false;\' ',
				'color' => '#a3a7ab'
			],
			'print' => [
				'title' => esc_html__('Print URL', 'rey-core'),
				'url' => '#',
				'icon' => 'print',
				'url_attributes' => 'class="js-print-url" onclick="window.print();return false;"',
				'color' => '#a3a7ab'
			],
		];

		$list['x'] = $list['twitter'];

		return apply_filters('reycore/social_sharing', $list);
	}
endif;


if ( ! function_exists( 'reycore__socialShare' ) ) :
	/**
	 * Prints HTML with social sharing.
	 * @since 1.0.0
	 */
	function reycore__socialShare( $args = [])
	{

		$defaults = [
			'class'   => '',
			'colored' => false,
			'url'     => esc_url( get_the_permalink() ),
			'before'  => '',
			'after'   => '',
			'title'   => urlencode( html_entity_decode( get_the_title(), ENT_COMPAT, 'UTF-8') ),
		];

		$defaults['share_items'] = apply_filters('reycore/post/social_share', [ 'twitter', 'facebook', 'linkedin', 'pinterest', 'mail' ], $defaults, false);

		$args = wp_parse_args( $args, $defaults );

		$classes = esc_attr($args['class']);

		if( $args['colored'] ){
			$classes .= ' --colored';
		}

		if( is_array($args['share_items']) && !empty($args['share_items']) ):

			echo $args['before']; ?>

			<ul class="rey-postSocialShare <?php echo $classes; ?>">
				<?php

				$all_icons = reycore__social_sharing_icons_list();

				foreach($args['share_items'] as $item):
					echo '<li class="rey-shareItem--'. $item .'">';

					if( isset($all_icons[$item]) ){

						$cleanup = function($string) use ($args) {
							$cleaned_up = str_replace('{url}', $args['url'], $string);
							$cleaned_up = str_replace('{title}', $args['title'], $cleaned_up);
							return $cleaned_up;
						};

						$attributes = isset($all_icons[$item]['url_attributes']) ? $cleanup($all_icons[$item]['url_attributes']) : '';

						if( $args['colored'] && isset($all_icons[$item]['color']) ){
							$attributes .= sprintf(' style="background-color: %s;"', $all_icons[$item]['color']);
						}

						$title_prefix = ! in_array($item, ['mail', 'copy', 'print'], true) ? esc_html__('Share on ', 'rey-core') : '';

						$tooltip = $title_prefix . $all_icons[$item]['title'];

						reycore_assets()->add_styles('reycore-tooltips');

						printf( '<a href="%1$s" %2$s data-tooltip-text="%3$s" rel="noreferrer" target="%5$s" aria-label="%3$s">%4$s</a>',
							$cleanup( $all_icons[$item]['url'] ),
							$attributes,
							esc_attr($tooltip),
							reycore__get_svg_social_icon( ['id' => $all_icons[$item]['icon']] ),
							apply_filters('reycore/social_sharing/target', '_blank', $item)
						);
					}

					echo '</li>';
				endforeach;
				?>
			</ul>
			<!-- .rey-postSocialShare -->

			<?php
			echo $args['after'];

		reycore_assets()->add_styles('reycore-post-social-share');

		endif;
	}
endif;


if(!function_exists('reycore__social_icons_list')):
	/**
	 * Social Icons List
	 *
	 * @since 1.0.0
	 **/
	function reycore__social_icons_list()
	{
		return [
			'android',
			'apple',
			'behance',
			'bitbucket',
			'codepen',
			'delicious',
			'deviantart',
			'digg',
			'discord',
			'dribbble',
			'envelope',
			'facebook',
			'facebook-f',
			'flickr',
			'foursquare',
			'free-code-camp',
			'github',
			'gitlab',
			'globe',
			'google-plus',
			'houzz',
			'instagram',
			'jsfiddle',
			'link',
			'linkedin',
			'medium',
			'meetup',
			'mixcloud',
			'odnoklassniki',
			'patreon',
			'pinterest',
			'pinterest-p',
			'product-hunt',
			'reddit',
			'rss',
			'shopping-cart',
			'skype',
			'slideshare',
			'snapchat',
			'soundcloud',
			'spotify',
			'stack-overflow',
			'steam',
			'stumbleupon',
			'telegram',
			'thumb-tack',
			'tiktok',
			'tripadvisor',
			'tumblr',
			'twitch',
			'twitter',
			'viber',
			'vimeo',
			'vimeo-v',
			'vk',
			'weibo',
			'weixin',
			'whatsapp',
			'wordpress',
			'xing',
			'x',
			'yelp',
			'youtube',
			'500px',
		];
	}
endif;


if(!function_exists('reycore__social_icons_list_select2')):
	/**
	 * Social Icons List for a select list
	 *
	 * @since 1.0.0
	 **/
	function reycore__social_icons_list_select2( $type = 'social' )
	{
		$new_list = [];

		if( $type === 'social' ){
			$list = reycore__social_icons_list();

			foreach( $list as $v ){
				$new_list[$v] = ucwords(str_replace('-',' ', $v));
			}
		}
		elseif( $type === 'share' ){
			$list = reycore__social_sharing_icons_list();

			foreach( $list as $k => $v ){
				$new_list[$k] = $v['title'];
			}
		}

		return $new_list;
	}
endif;


if(!function_exists('reycore__get_page_title')):
	/**
	 * Get the page title
	 *
	 * @since 1.0.0
	 */
	function reycore__get_page_title() {
		$title = '';

		if ( class_exists('\WooCommerce') && is_shop() && ! is_search() ) {

			$shop_page_id = wc_get_page_id( 'shop' );
			$page_title   = get_the_title( $shop_page_id );
			$title = apply_filters( 'woocommerce_page_title', $page_title );
		}
		elseif ( is_home() ) {
			$title = get_the_title( get_option( 'page_for_posts' ) );
		}
		elseif ( is_singular() ) {
			$title = get_the_title();
		} elseif ( is_search() ) {
			/* translators: %s: Search term. */
			$title = sprintf( __( 'Search Results for: %s', 'rey-core' ), get_search_query() );
			// show page
			if ( get_query_var( 'paged' ) ) {
				/* translators: %s is the page number. */
				$title .= sprintf( __( '&nbsp;&ndash; Page %s', 'rey-core' ), get_query_var( 'paged' ) );
			}
		} elseif ( is_category() ) {
			$title = single_cat_title( '', false );
		} elseif ( is_tag() ) {
			$title = single_tag_title( '', false );
		} elseif ( is_author() ) {
			$title = '<span class="vcard">' . get_the_author() . '</span>';
		} elseif ( is_year() ) {
			$title = get_the_date( _x( 'Y', 'yearly archives date format', 'rey-core' ) );
		} elseif ( is_month() ) {
			$title = get_the_date( _x( 'F Y', 'monthly archives date format', 'rey-core' ) );
		} elseif ( is_day() ) {
			$title = get_the_date( _x( 'F j, Y', 'daily archives date format', 'rey-core' ) );
		} elseif ( is_tax( 'post_format' ) ) {
			if ( is_tax( 'post_format', 'post-format-aside' ) ) {
				$title = _x( 'Asides', 'post format archive title', 'rey-core' );
			} elseif ( is_tax( 'post_format', 'post-format-gallery' ) ) {
				$title = _x( 'Galleries', 'post format archive title', 'rey-core' );
			} elseif ( is_tax( 'post_format', 'post-format-image' ) ) {
				$title = _x( 'Images', 'post format archive title', 'rey-core' );
			} elseif ( is_tax( 'post_format', 'post-format-video' ) ) {
				$title = _x( 'Videos', 'post format archive title', 'rey-core' );
			} elseif ( is_tax( 'post_format', 'post-format-quote' ) ) {
				$title = _x( 'Quotes', 'post format archive title', 'rey-core' );
			} elseif ( is_tax( 'post_format', 'post-format-link' ) ) {
				$title = _x( 'Links', 'post format archive title', 'rey-core' );
			} elseif ( is_tax( 'post_format', 'post-format-status' ) ) {
				$title = _x( 'Statuses', 'post format archive title', 'rey-core' );
			} elseif ( is_tax( 'post_format', 'post-format-audio' ) ) {
				$title = _x( 'Audio', 'post format archive title', 'rey-core' );
			} elseif ( is_tax( 'post_format', 'post-format-chat' ) ) {
				$title = _x( 'Chats', 'post format archive title', 'rey-core' );
			}
		} elseif ( is_post_type_archive() ) {
			$title = post_type_archive_title( '', false );
		} elseif ( is_tax() ) {
			$title = single_term_title( '', false );
		} elseif ( is_404() ) {
			$title = __( 'Page Not Found', 'rey-core' );
		}

		$title = apply_filters( 'reycore/tags/get_the_title', $title );

		return $title;
	}
endif;



if(!function_exists('reycore__get_video_html')):
	/**
	 * Get HTML5 video markup
	 *
	 * @since 1.0.0
	 */
	function reycore__get_video_html( $args = [] ){

		$defaults = [
			'video_url' => '',
			'class' => '',
			'params' => [
				'class'=>'rey-hostedVideo-inner elementor-background-video-hosted elementor-html5-video',
				'loop' => 'loop',
				'muted'=>'muted',
				'autoplay'=>'autoplay',
				'style' => 'width:100%;height:100%;'
				// 'preload'=>'metadata',
			],
			'start' => 0,
			'end' => 0,
			'mobile' => false,
		];

		$args = reycore__wp_parse_args( $args, $defaults );

		if( empty($args['video_url']) ){
			return;
		}

		$args['params']['src'] = esc_attr($args['video_url']);

		if( $args['start'] || $args['end'] ){
			$args['params']['src'] = sprintf( '%s#t=%s%s',
				$args['params']['src'],
				$args['start'] ? $args['start'] : 0,
				$args['end'] ? ',' . $args['end'] : ''
			);
		}

		if( !$args['mobile'] ){
			$args['class'] .= ' elementor-hidden-mobile';
		}
		else {
			$args['params']['playsinline'] = 'playsinline';
		}

		return sprintf(
			'<div class="rey-hostedVideo %s" data-video-params=\'%s\'></div>',
				esc_attr($args['class']),
				wp_json_encode($args['params'])
		);
	}
endif;

if(!function_exists('reycore__get_youtube_iframe_html')):
	/**
	 * Get YouTube video iframe HTML
	 *
	 * @since 1.0.0
	 */
	function reycore__get_youtube_iframe_html( $args = [] ){

		$defaults            =  [
			'video_id'          => '',
			'video_url'         => '',
			'class'             => '',
			'html_id'           => '',
			'add_preview_image' => false,
			'preview_inside' 	=> false,
			'mobile'            => false,
			'params'            => [
				'enablejsapi'      => 1,
				'rel'              => 0,
				'showinfo'         => 0,
				'controls'         => 0,
				'autoplay'         => 1,
				'disablekb'        => 1,
				'mute'             => 1,
				'fs'               => 0,
				'iv_load_policy'   => 3,
				'loop'             => 1,
				'modestbranding'   => 1,
				'start'            => 0,
				'end'              => 0,
			]
		];

		$args = reycore__wp_parse_args( $args, $defaults );

		if( empty($args['video_id']) && !empty($args['video_url']) ){
			$args['video_id'] = reycore__extract_youtube_id( $args['video_url'] );
			$args['params']['start'] = reycore__extract_youtube_start( $args['video_url'] );
		}

		if( empty($args['video_id']) ){
			return false;
		}

		$preview = '';

		if( $args['add_preview_image'] ){
			$preview = reycore__get_youtube_preview_image_html([
				'video_id' => $args['video_id'],
				'class' => $args['class'],
			]);
			reycore_assets()->add_styles('reycore-videos');
		}

		if( !$args['mobile'] ){
			$args['class'] .= ' elementor-hidden-mobile';
		}
		else {
			$args['params']['playsinline'] = 1;
		}

		$preview_inside = '';
		$preview_outside = $preview;

		if( $args['preview_inside'] ){
			$preview_inside = $preview;
			$preview_outside = '';
			$args['class'] .= ' --preview-inside';
		}

		return sprintf(
			'<div class="rey-youtubeVideo %1$s" style="width:100%%;height:100%%;" data-video-params=\'%2$s\' data-video-id="%3$s"><div class="rey-youtubeVideo-inner elementor-background-video-embed" id="%4$s" ></div>%5$s</div>%6$s',
				esc_attr($args['class']),
				wp_json_encode($args['params']),
				esc_attr($args['video_id']),
				esc_attr($args['html_id']),
				$preview_inside,
				$preview_outside
		);
	}
endif;

if(!function_exists('reycore__get_youtube_preview_image_html')):
	/**
	 * Get YouTube video preview image HTML
	 *
	 * @since 1.0.0
	 */
	function reycore__get_youtube_preview_image_html( $args = [] ){

		$defaults = [
			'video_id' => '',
			'class' => '',
		];

		$args = reycore__wp_parse_args( $args, $defaults );

		if( empty($args['video_id']) ){
			return;
		}

		return sprintf(
			'<div class="rey-youtubePreview %2$s"><img src="//img.youtube.com/vi/%1$s/maxresdefault.jpg" data-default-src="//img.youtube.com/vi/%1$s/hqdefault.jpg" alt="" /></div>',
			esc_attr($args['video_id']),
			esc_attr($args['class'])
		);
	}
endif;


if(!function_exists('reycore__extract_youtube_id')):
	/**
	 * Extract Youtube ID from URL
	 *
	 * @since 1.0.0
	 **/
	function reycore__extract_youtube_id( $url )
	{
		// Here is a sample of the URLs this regex matches: (there can be more content after the given URL that will be ignored)
		// http://youtu.be/dQw4w9WgXcQ
		// http://www.youtube.com/embed/dQw4w9WgXcQ
		// http://www.youtube.com/watch?v=dQw4w9WgXcQ
		// http://www.youtube.com/?v=dQw4w9WgXcQ
		// http://www.youtube.com/v/dQw4w9WgXcQ
		// http://www.youtube.com/e/dQw4w9WgXcQ
		// http://www.youtube.com/user/username#p/u/11/dQw4w9WgXcQ
		// http://www.youtube.com/sandalsResorts#p/c/54B8C800269D7C1B/0/dQw4w9WgXcQ
		// http://www.youtube.com/watch?feature=player_embedded&v=dQw4w9WgXcQ
		// http://www.youtube.com/?feature=player_embedded&v=dQw4w9WgXcQ
		// It also works on the youtube-nocookie.com URL with the same above options.
		// It will also pull the ID from the URL in an embed code (both iframe and object tags)
		preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);

		if( isset($match[1]) && $youtube_id = $match[1] ){
			return $youtube_id;
		}

		return false;
	}
endif;


if(!function_exists('reycore__extract_youtube_start')):
	/**
	 * Extract Youtube start
	 *
	 * @since 1.0.0
	 **/
	function reycore__extract_youtube_start( $url )
	{
		parse_str($url, $query);

		if( isset($query['t']) && $start = absint($query['t']) ){
			return $start;
		}

		return 0;
	}
endif;


if(!function_exists('reycore__get_next_posts_url')):
	/**
	 * Retrieves the next posts page link.
	 * based on `get_next_posts_link`
	 *
	 * @since 1.0.0
	 *
	 * @global int      $paged
	 * @global WP_Query $wp_query
	 *
	 * @param int    $max_page Optional. Max pages. Default 0.
	 * @return string|void next posts url.
	 */
	function reycore__get_next_posts_url( $max_page = 0 ) {
		global $paged, $wp_query;

		if ( ! $max_page ) {
			$max_page = $wp_query->max_num_pages;
		}

		if ( ! $paged ) {
			$paged = 1;
		}


		$nextpage = intval( $paged ) + 1;

		if ( ! is_single() && ( $nextpage <= $max_page ) ) {
			return str_replace( '%2C', ',', next_posts( $max_page, false ));
		}
	}
endif;


if(!function_exists('reycore__ajax_load_more_pagination')):
	/**
	 * Show ajax load more pagination markup
	 *
	 * @since 1.0.0
	 **/
	function reycore__ajax_load_more_pagination( $args = [] )
	{
		reycore_assets()->add_scripts(['reycore-load-more', 'reycore-wc-loop-count-loadmore']);
		reycore_assets()->add_styles(['rey-buttons', 'reycore-ajax-load-more']);

		$pagination_args = apply_filters('reycore/load_more_pagination_args', wp_parse_args( $args, [
			'url'          => reycore__get_next_posts_url(),
			'class'        => 'btn ' . get_theme_mod('loop_pagination_btn_style', 'btn-line-active'),
			'post_type'    => get_post_type(),
			'target'       => 'ul.products',
			'items'        => 'li.product, .rey-postItem',
			'text'         => esc_html__('SHOW MORE', 'rey-core'),
			'end_text'     => esc_html__('END', 'rey-core'),
			'ajax_counter' => get_theme_mod('loop_pagination_ajax_counter', false),
		]));

		if( $pagination_args['url'] ){

			$attributes = [];

			$attributes['data-post-type'] = esc_attr( $pagination_args['post_type'] );
			$attributes['data-target'] = esc_attr( $pagination_args['target'] );
			$attributes['data-items'] = esc_attr( $pagination_args['items'] );
			$attributes['data-text'] = _x($pagination_args['text'], 'Ajax load more posts or products button text.', 'rey-core');
			$attributes['aria-label'] = _x($pagination_args['text'], 'Ajax load more posts or products button text.', 'rey-core');
			$attributes['data-end-text'] = _x($pagination_args['end_text'], 'Ajax load more end text.', 'rey-core');

			$attributes['href'] = esc_url( $pagination_args['url']);
			$attributes['class'] = 'rey-ajaxLoadMore-btn ' . esc_attr( $pagination_args['class']);

			if(
				is_post_type_archive('product') ||
				is_tax( get_object_taxonomies('product') ) ||
				apply_filters('reycore/load_more_pagination/product', false)
			){

				if( $pagination_args['ajax_counter'] ){

					$total    = wc_get_loop_prop( 'total' );
					$per_page = wc_get_loop_prop( 'per_page' );
					$paged    = wc_get_loop_prop( 'current_page' );

					$from     = min( $total, $per_page * $paged );
					$to       = $total;

					$counter_current_page = true;

					if( isset($pagination_args['counter_current_page']) && ! $pagination_args['counter_current_page'] ){
						$counter_current_page = false;
					}

					if( $counter_current_page ){
						$from = $paged;
						if( $total ){
							$to = ceil( $total / $per_page );
						}
					}

					$from = absint($from);
					$to = absint($to);

					if( ($from + 1) === $to ){
						$attributes['data-end-count'] = sprintf('(%s / %s)', $from + 1, $to);
					}

					$attributes['data-count'] = sprintf('(%s / %s)', $from, $to);
				}

				$attributes['data-history'] = get_theme_mod('loop_pagination_ajax_history', true) ? '1' : '0';

				if( $btn_text = get_theme_mod('loop_pagination_ajax_text', '') ){
					$attributes['data-text'] = $btn_text;
					$attributes['aria-label'] = $btn_text;
				}

				if( $btn_end_text = get_theme_mod('loop_pagination_ajax_end_text', '') ){
					$attributes['data-end-text'] = $btn_end_text;
				}

				$attributes['href'] = str_replace('?reynotemplate=1', '', $attributes['href']);
				$attributes['href'] = str_replace('&reynotemplate=1', '', $attributes['href']);
				$attributes['href'] = str_replace('&#038;reynotemplate=1', '', $attributes['href']);
			}

			printf( '<nav class="rey-ajaxLoadMore --invisible"><a %s></a><div class="rey-lineLoader"></div></nav>',
				reycore__implode_html_attributes( apply_filters('reycore/load_more_pagination/output_attributes', $attributes, $pagination_args) )
			);
		}
	}
endif;

if(!function_exists('reycore__remove_paged_pagination')):
	/**
	 * Remove default pagination in blog
	 *
	 * @since 1.0.0
	 */
	function reycore__remove_paged_pagination() {
		if( get_theme_mod('blog_pagination', 'paged') !== 'paged' ){
			remove_action('rey/post_list', 'rey__pagination', 50);
		}
	}
endif;
add_action('wp', 'reycore__remove_paged_pagination');


if(!function_exists('reycore__pagination')):
	/**
	 * Wrapper for wp pagination
	 *
	 * @since 1.0.0
	 */
	function reycore__pagination() {
		if( ($blog_pagination = get_theme_mod('blog_pagination', 'paged')) && $blog_pagination !== 'paged' ){
			reycore__get_template_part( 'template-parts/misc/pagination-' . $blog_pagination );
		}
	}
endif;
add_action('rey/post_list', 'reycore__pagination', 50);


if(!function_exists('reycore__get_post_term_thumbnail')):
/**
 * Extract Thumbnail ID & URL from Post or WooCOmmerce Term
 *
 * @since 1.3.0
 **/
function reycore__get_post_term_thumbnail()
{
	if( class_exists('\WooCommerce') && is_tax() ){
		$term = get_queried_object();
		$thumb_id = get_term_meta( $term->term_id, 'thumbnail_id', true );
		return [
			'id' => $thumb_id,
			'url' => wp_get_attachment_url(  $thumb_id )
		];
	}
	elseif( is_singular() ){
		return [
			'id' => get_post_thumbnail_id(),
			'url' => get_the_post_thumbnail_url()
		];
	}
}
endif;

if(!function_exists('reycore__single_post_add_share_buttons')):
	/**
	 * Adds social sharing icons in single post footer
	 *
	 * @since 1.0.0
	 */
	function reycore__single_post_add_share_buttons(){

		if( ! get_theme_mod('post_share', true) ) {
			return;
		}

		$classes = ['text-center', 'text-sm-right'];

		$style = get_theme_mod('post_share_style', '');
		$is_colored = $style === '' || $style === 'round_c';

		if( $style ){
			$classes[] = '--' . $style;
		}

		reycore__socialShare([
			'class' => implode(' ', $classes),
			'colored' => $is_colored,
			'share_items' => get_theme_mod('post_share_icons_list', ['facebook-f', 'twitter', 'linkedin', 'pinterest-p', 'mail'])
		]);

	}
endif;
add_action('rey/single_post/footer', 'reycore__single_post_add_share_buttons' );

if(!function_exists('reycore__limit_text')):
	/**
	 * Limit words in a string
	 *
	 * @since 1.3.7
	 **/
	function reycore__limit_text($text, $limit)
	{
		if (str_word_count($text, 0) > $limit) {
			$words = str_word_count($text, 2);
			$pos = array_keys($words);
			$text = substr($text, 0, $pos[$limit]) . '...';
		}
		return $text;
	}
endif;

if(!function_exists('reycore__remove_404_page')):
	/**
	 * Remove default 404 page
	 *
	 * @since 1.5.0
	 */
	function reycore__remove_404_page() {
		if( get_theme_mod('404_gs', '') !== '' ){
			remove_action('rey/404page', 'rey__404page', 10);
		}
	}
endif;
add_action('wp', 'reycore__remove_404_page');


if(!function_exists('reycore__404page')):
	/**
	 * Add global section 404 page content
	 *
	 * @since 1.5.0
	 */
	function reycore__404page() {
		if( $gs = get_theme_mod('404_gs', '') ){
			echo \ReyCore\Elementor\GlobalSections::do_section( $gs );
		}
	}
endif;
add_action('rey/404page', 'reycore__404page');

add_filter('rey/404page/container_classes', function($class){

	if( $gs = get_theme_mod('404_gs', '') && get_theme_mod('404_gs_stretch', false) ){
		$class .= ' --stretch';
	}

	return $class;
});

if(!function_exists('reycore__scripts_params')):
	/**
	 * Filter rey script params
	 *
	 * @since 1.5.0
	 **/
	function reycore__scripts_params($params) {
		return array_merge($params, [ 'check_for_empty' => ['.--check-empty', '.rey-mobileNav-footer', '.rey-postFooter'] ]);
	}
	add_filter('rey/main_script_params', 'reycore__scripts_params', 10, 3);
endif;

if(!function_exists('reycore__filter_nav_classes')):
	/**
	 * Filter nav classes
	 *
	 * @since 1.9.0
	 **/
	function reycore__filter_nav_classes($classes, $args, $screen) {

		if( 'desktop' === $screen && ! get_theme_mod('header_nav_hover_delays', true) ){
			$classes[] = '--prevent-delays';
		}

		return $classes;
	}
	add_filter('rey/header/nav_classes', 'reycore__filter_nav_classes', 10, 3);
endif;


if(!function_exists('reycore__svg_arrows')):
	/**
	 * Print Arrow Icons
	 *
	 * @since 1.6.10
	 **/
	function reycore__svg_arrows( $args = [] )
	{

		$args = wp_parse_args($args, [
			'type'        => '',
			'class'       => '',
			'echo'        => true,
			'single'      => '',
			'custom_icon' => '',
			'tag'         => 'div',
			'name'        => '',
			'attributes' => [
				'left'  => '',
				'right' => '',
			]
		]);

		$arrows_scheme = [
			'left' => false,
			'right' => true,
		];

		if( $args['single'] ){
			unset($arrows_scheme[ $args['single'] ]);
		}

		$arrowsSvg = $markup = '';

		if( $args['type'] === 'custom' && ($custom_icon = $args['custom_icon']) ){
			$markup = $custom_icon;
		}

		foreach ($arrows_scheme as $key => $is_right) {
			$arrowsSvg .= reycore__arrowSvg( [
				'right'      => $is_right,
				'attributes' => $args['attributes'][$key],
				'class'      => $args['class'],
				'type'       => $args['type'],
				'markup'     => $markup,
				'tag'        => $args['tag'],
				'name'       => $args['name'],
			] );
		}

		if( $args['echo'] ){
			echo $arrowsSvg;
		}

		else {
			return $arrowsSvg;
		}

	}
endif;



if(!function_exists('reycore__get_current_url')):
/**
 * Get Current URL
 *
 * @since 1.7.0
 **/
function reycore__get_current_url( $alt = false )
{
	if( $alt ){
		return ( is_ssl() ? 'https' : 'http' ) . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}

	global $wp;
	return home_url( add_query_arg( array(), $wp->request ) );
}
endif;

if(!function_exists('reycore__html_class_attr')):
	/**
	 * Adds class attribute to html tag
	 *
	 * @since 1.9.6
	 **/
	function reycore__html_class_attr($output)
	{
		$classes = esc_attr( implode(' ', array_filter(apply_filters('reycore/html_class_attr', []))));

		if( !empty($classes) ){

			// check if already has class attribute
			if( strpos($output, 'class="') !== false ){
				$output = str_replace('class="', 'class="' . $classes . ' ', $output);
			}
			else {
				$output .= sprintf(' class="%s"', $classes);
			}
		}

		return $output;
	}
	add_filter( 'language_attributes', 'reycore__html_class_attr', 100 );
endif;


if(!function_exists('reycore_wc__modal_template')):

	function reycore_wc__modal_template(){

		if( ! apply_filters('reycore/modals/always_load', false) ){
			return;
		}

		reycore_assets()->add_styles('reycore-modals');
		reycore_assets()->add_scripts(['reycore-elementor-frontend', 'reycore-modals']);

	}
endif;
add_action('admin_footer', 'reycore_wc__modal_template', 5);
add_action('wp_footer', 'reycore_wc__modal_template', 5);

/**
 * Preload Assets
 * https://developer.mozilla.org/en-US/docs/Web/HTML/Preloading_content
 *
 * @since 2.0.0
 */
add_action('wp_head', function(){

	foreach (get_theme_mod('perf__preload_assets', []) as $key => $asset) {

		$attributes = [];

		// eg: image, font, video
		if( $type = $asset['type'] ){
			$attributes['as'] = $type;
		}

		// eg: image/jpeg, image/svg+xml, font/woff2, video/mp4
		if( $mime = $asset['mime'] ){
			$attributes['type'] = $mime;
		}

		// eg: (max-width: 600px)
		if( $media = $asset['media'] ){
			$attributes['media'] = $media;
		}

		if( $path = $asset['path'] ){
			$attributes['href'] = $path;
		}

		// External
		if( $asset['crossorigin'] === 'yes' ){
			$attributes['crossorigin'] = '';
		}

		if( ! empty($attributes) ){
			printf(
				'<link rel="preload" %s/>',
				reycore__implode_html_attributes($attributes)
			) . "\n";
		}
	}

}, 5);


if(!function_exists('reycore__get_picture')):
	/**
	 * Add picture tag.
	 *
	 * @param array $args
	 * @return string
	 */
	function reycore__get_picture($args = []){

		$args = wp_parse_args($args, [
			'id' => 0,
			'size' => 'medium',
			'class' => '',
			'disable_mobile' => false,
			'lazy_attribute' => false
		]);

		if( ! $args['id'] ){
			return;
		}

		$image_size = $args['size'];
		$image_html = wp_get_attachment_image( $args['id'], $image_size, false, [ 'class' => $args['class']] );

		if( $args['disable_mobile'] ){

			$image_srcset = wp_get_attachment_image_srcset($args['id'], $image_size);

			if( ! $image_srcset && $image_src = wp_get_attachment_image_src( $args['id'], $image_size ) ){
				$image_srcset = $image_src[0];
				$image_srcset .= $args['disable_mobile'] && isset($image_src[1]) ? sprintf(' %dw', $image_src[1]) : '';
			}

			$media = '(min-width: 768px)';
			$pixel = '<source media="(max-width: 767px)" sizes="1px" srcset="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7 1w"/>';

			return sprintf('<picture class="%5$s">%3$s<source media="%4$s" srcset="%2$s"/>%1$s</picture>',
				$image_html,
				$image_srcset,
				$pixel,
				$media,
				$args['class']
			);
		}

		else {
			if( $args['lazy_attribute'] ){
				$image_html = str_replace(' src=', sprintf(' %s=', $args['lazy_attribute']), $image_html);
			}
		}

		return $image_html;
	}
endif;

if(!function_exists('reycore__post_thumbnail_size')):
	function reycore__post_thumbnail_size($size)
	{
		if( $custom_size = get_theme_mod('post_thumbnail_image_size', '') ){
			return $custom_size;
		}
		return $size;
	}
	add_filter('post_thumbnail_size', 'reycore__post_thumbnail_size');
endif;


if(!function_exists('reycore__css_no_opt_attr')):
	/**
	 * Attributes to be added on stylesheet tags to prevent optimisations.
	 *
	 * @since 2.5.0
	 **/
	function reycore__css_no_opt_attr( $inline = false )
	{
		static $attributes;

		if( is_null($attributes) ){

			$_attr =  [
				'data-noptimize'          => '',
				'data-no-optimize'        => '1',
				'data-pagespeed-no-defer' => '',
				'data-pagespeed-no-transform' => '',
			];

			// if it's not inline,
			// minifying is allowed
			if( ! $inline ){
				$_attr['data-minify'] = '1';
			}

			$attributes = apply_filters('reycore/css/no_optimize_attrbutes', reycore__implode_html_attributes($_attr));
		}

		return $attributes;
	}
endif;


if(!function_exists('reycore__js_no_opt_attr')):
	/**
	 * Attributes to be added on script tags to prevent optimisations.
	 *
	 * @since 2.5.0
	 **/
	function reycore__js_no_opt_attr()
	{
		static $attributes;

		if( is_null($attributes) ){

			$_attr =  [
				'data-noptimize'          => '',
				'data-no-optimize'        => '1',
				'data-no-defer'           => '1',
				'data-pagespeed-no-defer' => '',
			];

			$attributes = apply_filters('reycore/js/no_optimize_attrbutes', reycore__implode_html_attributes($_attr));
		}

		return $attributes;
	}
endif;

if(!function_exists('reycore__js_is_delayed')):
	/**
	 * Mark JS delayed
	 *
	 * @since 2.1.2
	 **/
	function reycore__js_is_delayed()
	{
		return apply_filters('reycore/delay_js', false);
	}
endif;

if(!function_exists('reycore__js_delayed_exclusions')):
	/**
	 * JS delayed exclusions
	 *
	 * @since 2.1.2
	 **/
	function reycore__js_delayed_exclusions()
	{

		$excludes['reyParams'] = 'reyParams';
		$excludes['reycore-critical-css-js'] = 'reycore-critical-css-js';
		$excludes['rey-instant-js'] = 'rey-instant-js';
		$excludes['rey-no-js'] = 'rey-no-js';

		return array_unique(apply_filters('reycore/delay_js/exclusions', $excludes));
	}
endif;

if(!function_exists('reycore__close_button')):
	/**
	 * Close button
	 *
	 * @since 2.4.0
	 **/
	function reycore__close_button($args = [])
	{

		$args = wp_parse_args($args, [
			'class' => '',
			'aria-label' => esc_html__('Close', 'rey-core'),
			'arrow' => false,
			'icon' => reycore__get_svg_icon(['id' => 'close']),
			'before_icon' => '',
			'text' => '',
		]);

		if( $args['before_icon'] ){
			$args['icon'] = $args['before_icon'] . $args['icon'];
		}

		$args['icon'] .= reycore__get_svg_icon(['id' => 'arrow-classic']);

		reycore_assets()->add_styles('reycore-close-arrow');

		printf('<button class="__arrClose %s" aria-label="%s">%s<span class="__icons">%s</span></button>',
			$args['class'],
			$args['aria-label'],
			$args['text'] ? sprintf('<span class="__close-text">%s</span>', $args['text']) : '',
			$args['icon']
		);
	}
endif;

if(!function_exists('reycore__mobile_menu_close_button')):
	/**
	 * Close button
	 *
	 * @since 2.4.0
	 **/
	function reycore__mobile_menu_close_button()
	{
		ob_start();
		reycore__close_button([
			'class' => 'btn rey-mobileMenu-close js-rey-mobileMenu-close',
			'aria-label' => esc_html__('Close menu', 'rey-core'),
		]);
		return ob_get_clean();
	}
	add_filter('rey/mobile_nav/close_button', 'reycore__mobile_menu_close_button');
endif;


if(!function_exists('reycore__lazy_placeholders')):
	/**
	 * Lazy placeholders for grid
	 *
	 * @since 2.4.0
	 **/
	function reycore__lazy_placeholders($args = [])
	{

		$args = wp_parse_args($args, [
			'class'              => 'placeholder_products',
			'placeholders_class' => '',
			'filter_title'       => 'placeholder_products',
			'blocktitle'         => true,
			'desktop'            => 4,
			'tablet'             => 3,
			'mobile'             => 2,
			'limit'              => 4,
			'nowrap'             => false, // keep in one line (carousels)
		]);

		$args = apply_filters("reycore/woocommerce/{$args['filter_title']}/placeholder_params", $args);

		reycore_assets()->add_styles('reycore-placeholders');

		$output = sprintf('<div class="__placeholder-wrapper %s">', esc_attr($args['class']));

			if( $args['blocktitle'] ){
				$output .= '<div class="__placeholders-blockTitle"></div>';
			}

			$pclass = $args['placeholders_class'];

			if( $args['nowrap'] ){
				$pclass .= ' --nowrap';
			}

			$output .= sprintf('<div class="__placeholders %4$s" style="--cols: %1$d; --cols-tablet: %2$d; --cols-mobile: %3$d;">',
				$args['desktop'],
				$args['tablet'],
				$args['mobile'],
				$pclass
			);

				for( $i = 0; $i < absint($args['limit']); $i++ ){
					$output .= '<div class="__placeholder-item"><div class="__placeholder-thumb"></div><div class="__placeholder-title"></div><div class="__placeholder-subtitle"></div></div>';
				}

			$output .= '</div>';

		$output .= '</div>';

		return $output;
	}
endif;


if(!function_exists('reycore__popover')):
/**
 * Popover
 *
 * @since 2.4.0
 **/
function reycore__popover($args = [])
{
	$args = wp_parse_args($args, [
		'content' => '',
		'admin'   => false,
		'class'   => '',
		'arrow'   => 'bottom-right', // bottom-left / top-right / top-left
	]);

	if( ! $args['content'] ){
		return;
	}

	if( $args['admin'] && ! current_user_can('administrator') ){
		return;
	}

	$args['class'] .= ' --arr-' . $args['arrow'];

	$content = sprintf('<div class="rey-simplePopover %s">', $args['class']);

	$content .= $args['content'];

	if( $args['admin'] ){
		$content .= '<p><small>This notice shows only for Administrators.</small></p>';
	}

	$content .= '</div>';

	return $content;
}
endif;

if(!function_exists('reycore__disable_admin_bar')):
	/**
	 * Disable Admin Bar
	 *
	 * @since 2.4.0
	 **/
	function reycore__disable_admin_bar($status) {

		if( isset($_REQUEST['admin_bar']) && absint($_REQUEST['admin_bar']) === 0 && current_user_can('administrator') ){
			return false;
		}

		return $status;
	}
	add_filter( 'show_admin_bar' , 'reycore__disable_admin_bar' );
endif;


if(!function_exists('reycore__input_has_value')):
/**
 * Adds attribute
 *
 * @since 2.6.0
 **/
function reycore__input_has_value()
{
	return 'onInput="(function(e){e.target.closest(\'.rey-form-row\').classList.toggle(\'--has-value\',e.target.value)})(arguments[0]);"';
}
endif;
