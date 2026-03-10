<?php
namespace ReyCore;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Frontend {

	protected $body_classes = [];

	public function __construct(){
		add_action( 'wp_head', [$this, 'wp_head'], 100);
		add_filter( 'body_class', [$this, 'filter_body_class'], 20 );
		add_filter( 'rey/body/tag_attributes', [$this, 'add_post_id_tag']);
		add_action( 'wp_body_open', [$this, 'instant_js'], 0 );
		add_action( 'init', [$this, 'disable_emoji']);
	}

	public function wp_head(){
		$this->set_default_body_classes();
		do_action('reycore/frontend/wp_head', $this);
	}

	public function add_post_id_tag($attr){

		$id = get_queried_object_id();

		if( function_exists('is_shop') && is_shop() ){
			$id = wc_get_page_id( 'shop' );
		}

		$attr['data-id'] = $id;

		return $attr;
	}

	/**
	 * Filter the body class
	 *
	 * @param array $classes
	 * @return array
	 */
	public function filter_body_class($classes)
	{

		unset($classes['search_style']);

		return array_merge($classes, $this->body_classes);
	}

	/**
	 * Public method to append body classes
	 *
	 * @param array|string $classes
	 * @return void
	 */
	public function add_body_class( $classes ){

		foreach ((array) $classes as $key => $class) {
			$this->body_classes[$key] = $class;
		}

	}

	/**
	 * Public method to remove body classes
	 *
	 * @param string Class name or key
	 * @param string Use key or not
	 * @return void
	 */
	public function remove_body_class( $data, $key = true ){

		foreach ((array) $data as $item) {
			if( $key ){
				unset($this->body_classes[ $item ]);
			}
			else {
				$the_key = array_search($item, $this->body_classes, true);
				unset($this->body_classes[$the_key]);
			}
		}

	}

	/**
	 * Add the default body classes
	 *
	 * @return void
	 */
	private function set_default_body_classes(){

		$classes = [];

		/**
		 * Add custom class for container width
		 *
		 * @since 2.1.2
		 **/
		if( $custom_container_width = get_theme_mod('custom_container_width', 'default') ){
			$classes['container_width'] = 'rey-cwidth--' . esc_attr($custom_container_width);
		}

		/**
		 * Mark JS delayed
		 *
		 * @since 2.1.2
		 **/
		if ( ! is_user_logged_in() && reycore__js_is_delayed() ) {
			// $classes['rey_wpr'] = '--not-ready';
		}

		/**
		 * Adds custom class defined in page options
		 */
		if( $rey_body_class = reycore__get_option('rey_body_class', '') ){
			$classes[] = esc_attr($rey_body_class);
		}

		/**
		 * Hide button focuses
		 */
		if( get_theme_mod('accessibility__hide_btn_focus', false) ){
			$classes['acc_focus'] = '--no-acc-focus';
		}

		$this->body_classes = $classes;

	}

	public function instant_js(){
		?>
		<script type="text/javascript" id="rey-instant-js" <?php echo reycore__js_no_opt_attr(); ?>>
			(function(){
				if( ! window.matchMedia("(max-width: 1024px)").matches && ("IntersectionObserver" in window) ){
					var io = new IntersectionObserver(entries => {
						window.reyScrollbarWidth = window.innerWidth - entries[0].boundingClientRect.width;
						document.documentElement.style.setProperty('--scrollbar-width', window.reyScrollbarWidth + "px");
						io.disconnect();
					});
					io.observe(document.documentElement);
				}
				let cw = parseInt(document.documentElement.getAttribute('data-container') || 1440);
				const sxl = function () {
					let xl;
					if ( window.matchMedia('(min-width: 1025px) and (max-width: ' + cw + 'px)').matches ) xl = 1; // 1440px - 1025px
					else if ( window.matchMedia('(min-width: ' + (cw + 1) + 'px)').matches ) xl = 2; // +1440px
					document.documentElement.setAttribute('data-xl', xl || 0);
				};
				sxl(); window.addEventListener('resize', sxl);
			})();
		</script>
		<?php
	}

	/**
	 * Remove Emoji Script
	 *
	 * @since 2.3.6
	 **/
	function disable_emoji()
	{

		if ( ! get_theme_mod('perf__disable_emoji', true) ) {
			return;
		}

		remove_action('wp_head', 'print_emoji_detection_script', 7);
		remove_action('wp_print_styles', 'print_emoji_styles');
		remove_action('admin_print_styles', 'print_emoji_styles');
		remove_action('admin_print_scripts', 'print_emoji_detection_script');

		remove_filter('the_content_feed', 'wp_staticize_emoji');
		remove_filter('comment_text_rss', 'wp_staticize_emoji');
		remove_filter('wp_mail', 'wp_staticize_emoji_for_email');

		add_filter('tiny_mce_plugins', function ($plugins) {
			if (is_array($plugins)) {
				return array_diff($plugins, ['wpemoji']);
			} else {
				return [];
			}
		});

		add_filter('wp_resource_hints', function ($urls, $relation_type) {
			if ('dns-prefetch' === $relation_type) {
				$emoji_svg_url = apply_filters('emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/'); /** This filter is documented in wp-includes/formatting.php */
				$urls = array_diff($urls, [$emoji_svg_url]);
			}
			return $urls;
		}, 10, 2);

	}

}
