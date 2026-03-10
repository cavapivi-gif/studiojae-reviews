<?php
namespace ReyCore;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Helper {


	const STATIC_TRANSIENTS = [];

	const QUERY = '_rey_query_';
	const PRODUCTS = '_rey_products_';
	const TERMS = '_rey_terms_';
	const MENU = '_rey_menu_';
	const CACHE_QUERIES = false;

	public function __construct()
	{
		add_action( 'init', [ $this, 'init' ]);

		foreach (self::caching_plugins() as $plugin) {
			if( $plugin['enabled'] && isset($plugin['flush_hook']) ){
				foreach ((array) $plugin['flush_hook'] as $hook) {
					add_action( $hook, [$this, 'run_caching_flush'] );
				}
			}
		}

		add_action('reycore/assets/cleanup', [$this, 'flush_plugin_cache']);

	}

	public function init(){

	}

	public static function get_transient( $transient_name, $cb, $expiration = false ){

		if( ! $transient_name ){
			return;
		}

		if( false !== ($content = get_transient($transient_name)) ){
			return $content;
		}

		if( ! $expiration && isset(self::STATIC_TRANSIENTS[$transient_name]) ){
			$expiration = self::STATIC_TRANSIENTS[$transient_name];
		}

		$content = $cb();

		set_transient($transient_name, $content, $expiration);

		return $content;
	}

	public function clean_transient($post_id){
		if ($post_id === REY_CORE_THEME_NAME) {

			reycore__maybe_disable_obj_cache();

			foreach (self::STATIC_TRANSIENTS as $k => $v){
				delete_transient($k);
			}
		}
	}

	public static function get_terms($args = []){

		if( empty($args) ){
			return [];
		}

		if( ! self::CACHE_QUERIES ){
			return get_terms($args);
		}

		$tax = '';

		if( isset($args['taxonomy']) ){
			$tax = $args['taxonomy'];
		}

		$name = self::TERMS . $tax . '_' . md5(wp_json_encode($args));

		return self::get_transient( $name, function() use ($args){
			return get_terms($args);
		}, WEEK_IN_SECONDS);
	}

	public static function get_products_query($args = []){

		if( empty($args) ){
			return [];
		}

		if( ! self::CACHE_QUERIES ){
			return new \WP_Query( $args );
		}

		$name = self::PRODUCTS . md5(wp_json_encode($args));

		// make sure to force
		$args['post_type'] = 'product';

		return self::get_transient( $name, function() use ($args){
			return new \WP_Query( $args );
		}, WEEK_IN_SECONDS);
	}

	public static function get_query($args = []){

		if( empty($args) ){
			return [];
		}

		if( ! self::CACHE_QUERIES ){
			return new \WP_Query( $args );
		}

		$pt = '';

		if( isset($args['post_type']) ){
			$pt = $args['post_type'];
		}

		$name = self::QUERY . $pt . '_' . md5(wp_json_encode($args));

		return self::get_transient( $name, function() use ($args){
			return new \WP_Query( $args );
		}, WEEK_IN_SECONDS);
	}

	public static function wp_nav_menu($args = []){
		return wp_nav_menu($args);
	}

	public static function get_all_image_sizes( $add_default = true ) {
		global $_wp_additional_image_sizes;

		$default_image_sizes = [ 'thumbnail', 'medium', 'medium_large', 'large' ];

		$wp_image_sizes = [];

		foreach ( $default_image_sizes as $size ) {
			$wp_image_sizes[ $size ] = [
				'width' => (int) get_option( $size . '_size_w' ),
				'height' => (int) get_option( $size . '_size_h' ),
				'crop' => (bool) get_option( $size . '_crop' ),
			];
		}

		if ( $_wp_additional_image_sizes ) {
			$wp_image_sizes = array_merge( $wp_image_sizes, $_wp_additional_image_sizes );
		}

		/** This filter is documented in wp-admin/includes/media.php */
		$wp_image_sizes = apply_filters( 'image_size_names_choose', $wp_image_sizes );

		$image_sizes = [];

		if( $add_default ){
			$image_sizes[''] = esc_html__( 'Default', 'rey-core' );
		}

		foreach ( $wp_image_sizes as $size_key => $size_attributes ) {

			$control_title = ucwords( str_replace( '_', ' ', $size_key ) );

			if ( is_array( $size_attributes ) ) {
				$control_title .= sprintf( ' - %d x %d', $size_attributes['width'], $size_attributes['height'] );
			}

			$image_sizes[ $size_key ] = $control_title;
		}

		$image_sizes['full'] = _x( 'Full', 'Image Size Control', 'rey-core' );

		return $image_sizes;
	}

	/**
	 * @param int $number
	 * @return string
	 */
	public static function numberToRoman($number) {
		$map = array('M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1);
		$returnValue = '';
		while ($number > 0) {
			foreach ($map as $roman => $int) {
				if($number >= $int) {
					$number -= $int;
					$returnValue .= $roman;
					break;
				}
			}
		}
		return $returnValue;
	}

	public static function get_video_properties( $video_url ) {

		$provider_match_masks = [
			'youtube' => '/^.*(?:youtu\.be\/|youtube(?:-nocookie)?\.com\/(?:(?:watch)?\?(?:.*&)?vi?=|(?:embed|v|vi|user)\/))([^\?&\"\'>]+)/',
			'vimeo' => '/^.*vimeo\.com\/(?:[a-z]*\/)*([‌​0-9]{6,11})[?]?.*/',
		];

		foreach ( $provider_match_masks as $provider => $match_mask ) {

			preg_match( $match_mask, $video_url, $matches );

			if ( $matches ) {

				$query = [];
				$parts = parse_url($video_url);

				if( isset($parts['query']) && !empty($parts['query']) ){
					parse_str($parts['query'], $query);
					unset($query['v']);
				}

				return [
					'provider' => $provider,
					'video_id' => $matches[1],
					'query' => $query,
				];
			}
		}

		return null;
	}

	public static function get_embed_video( $args = [] ) {

		$args = reycore__wp_parse_args($args, [
			'url'         => '',
			'style'       => '',
			'class'       => '',
			'class_video' => '',
			'autoplay'    => true,
			'id'          => '',
			'lazy'        => false,
			'attribute'   => 'data-video',
			'poster'      => '',
			'poster_html' => '',
			'params' => [
				'loop'              => 1,
				'controls'          => 1,
				'start'             => false,
				'end'               => false,
				'muted'             => null,
				// YT
				'yt_showinfo'       => 0,
				'yt_modestbranding' => 1,
				'yt_privacy'        => 0,
				'yt_rel'            => 0,
				'enablejsapi'       => 0,
				// VIMEO
				'vimeo_color'       => '',
				'vimeo_title'       => 0,
				'vimeo_portrait'    => 0,
				'vimeo_byline'      => 0,
				// hosted
				'poster'            => '',
			]
		]);

		if( ! $args['url'] ){
			return;
		}

		if( $args['lazy'] ){
			$args['attribute'] = 'data-lazy-video';
		}

		$autoplay = (int) $args['autoplay'];
		$video_properties = self::get_video_properties( $args['url'] );
		$poster = $args['params']['poster'] && ! $args['poster'] ? $args['params']['poster'] : $args['poster'];

		if( isset($video_properties['provider']) ) {

			$src = '';

			if( 'youtube' === $video_properties['provider'] ){

				$params = [
					'autoplay'       => $autoplay,
					'mute'           => ! is_null($args['params']['muted']) ? $args['params']['muted'] : $autoplay,
					'playlist'       => $video_properties['video_id'],
					'loop'           => $args['params']['loop'],
					'controls'       => $args['params']['controls'],
					'rel'            => $args['params']['yt_rel'],
					'showinfo'       => $args['params']['yt_showinfo'],
					'modestbranding' => $args['params']['yt_modestbranding'],
					'yt_privacy'     => $args['params']['yt_privacy'],
					'enablejsapi'    => $args['params']['enablejsapi'],
				];

				if( $args['params']['start'] ){
					$params['start'] = $args['params']['start'];
				}
				if( $args['params']['end'] ){
					$params['end'] = $args['params']['end'];
				}

				$query_args = array_merge($params, $video_properties['query']);

				$src = sprintf('//www.youtube.com/embed/%s?%s', $video_properties['video_id'], http_build_query($query_args));
			}

			else if( 'vimeo' === $video_properties['provider'] ){

				$params = [
					'autoplay' => $autoplay,
					'muted'    => ! is_null($args['params']['muted']) ? $args['params']['muted'] : $autoplay,
					'loop'     => $args['params']['loop'],
					'controls' => $args['params']['controls'],
					'color'    => str_replace('#', '', $args['params']['vimeo_color']),
					'title'    => $args['params']['vimeo_title'],
					'portrait' => $args['params']['vimeo_portrait'],
					'byline'   => $args['params']['vimeo_byline'],
				];

				$start = $args['params']['start'] ? sprintf('#t=%d', $args['params']['start']) : '';

				$query_args = array_merge($params, $video_properties['query']);

				$src = sprintf('//player.vimeo.com/video/%s?%s%s', $video_properties['video_id'], http_build_query($query_args), $start);
			}

			$video = sprintf('<iframe allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" %2$s="%1$s" class="%3$s" frameborder="0" allowfullscreen allow="autoplay"></iframe>', $src, $args['attribute'], esc_attr($args['class_video']));

		}

		// self hosted
		else {

			if( $autoplay ){

				if( ! is_null($args['params']['muted']) ){
					if( false !== $args['params']['muted'] ){
						$attrs['muted'] = $args['params']['muted'];
					}
				}
				else {
					$attrs['muted'] = 'muted';
				}

				$attrs['autoplay'] = 'autoplay';
			}

			if( $args['params']['controls'] ){
				$attrs['controls'] = 'controls';
			}

			if( $args['params']['loop'] ){
				$attrs['loop'] = 'loop';
			}

			if( $args['class_video'] ){
				$attrs['class'] = $args['class_video'];
			}

			if( $poster ){
				$attrs['poster'] = $poster;
			}

			$time = [];

			if( $args['params']['start'] ){
				$time['start'] = $args['params']['start'];
			}

			if( $args['params']['end'] ){
				$time['end'] = $args['params']['end'];
				if( ! isset($time['start']) ){
					$time['start'] = 0;
				}
			}

			$time_attribute = ! empty($time) ? sprintf('#t=%s', implode(',', $time)) : '';

			$video = sprintf('<video %3$s playsinline="playsinline" %2$s="%1$s%4$s"></video>',
				$args['url'],
				$args['attribute'],
				reycore__implode_html_attributes($attrs),
				$time_attribute
			);
		}

		if( $video ){
			reycore_assets()->add_styles('rey-embed-responsive');

			if( ! empty($args['poster_html']) ){
				$video .= $args['poster_html'];
			}

			return sprintf('<div class="embed-responsive embed-responsive-%4$s %2$s" style="%3$s">%1$s</div>',
				$video,
				$args['class'],
				$args['style'],
				apply_filters('reycore/embed_video_ratio', '16by9', $args)
			);
		}

	}

	/**
	 * Get File Path
	 *
	 * Will return the file path starting with the plugin directory for the given path
	 *
	 * @param string $path The path that will be appended to the plugin path
	 *
	 * @return string
	 */
	public static function get_file_path( $path = '' ) {
		return REY_CORE_DIR . $path;
	}

	// Normalize class name
	public static function fix_class_name( $name, $prefix = '', $suffix = '' ){
		$class_name = str_replace( '/', '\\', $name );
		$class_prefix = $prefix ? $prefix . '\\' : '';
		$class_suffix = $suffix ? '\\' . $suffix : '';
		return __NAMESPACE__ . '\\' . $class_prefix . $class_name . $class_suffix;
	}

	public static function is_rest_api_request() {
		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			// Probably a CLI request
			return false;
		}

		$rest_prefix         = trailingslashit( rest_get_url_prefix() );
		$is_rest_api_request = strpos( $_SERVER['REQUEST_URI'], $rest_prefix ) !== false;

		return apply_filters( 'is_rest_api_request', $is_rest_api_request );
	}

	/**
	 * Clean DB option by wildcard string.
	 *
	 * @param string $option_name
	 * @return array Query results.
	 */
	public static function clean_db_option( $option_name ){

		if ( ! $option_name ){
			return;
		}

		global $wpdb;

		$like = '%' . $wpdb->esc_like( $option_name ) . '%';

		return $wpdb->query(
			$wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s ", $like )
		);

	}

	/**
	 * Clean DB transients by wildcard string.
	 *
	 * @param string $transient_name
	 * @return array Query results.
	 */
	public static function clean_db_transient( $transient_name = '' ){

		if ( ! $transient_name ){
			return;
		}

		global $wpdb;

		$like_main = '%transient_' . $wpdb->esc_like( $transient_name ) . '%';
		$like_timeout = '%transient_timeout_' . $wpdb->esc_like( $transient_name ) . '%';

		$query = $wpdb->query(
			$wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s ", $like_main, $like_timeout )
		);

		do_action('reycore/clean_db_transient', $transient_name, $query);

		return $query;
	}

	/**
	 * Check for transients by wildcard string.
	 *
	 * @param string $transient_name
	 * @return array Query results.
	 */
	public static function check_db_transients( $transient_name = '' ){

		if ( ! $transient_name ){
			return;
		}

		global $wpdb;

		$like = '%transient_' . $wpdb->esc_like( $transient_name ) . '%';

		return $wpdb->query(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s", $like )
		);
	}


	/**
	 * Generate a hash
	 *
	 * @param array $data
	 * @param integer $limit
	 * @return string
	 */
	public static function hash( $data = [], $limit = 10 ){

		if( empty($data) ){
			return '';
		}

		return substr( md5( wp_json_encode( $data ) ), 0, $limit );
	}

	/**
	 * Insert an attachment from a URL address.
	 *
	 * @param  string   $url            The URL address.
	 * @param  int|null $parent_post_id The parent post ID (Optional).
	 * @return int|false                The attachment ID on success. False on failure.
	 */
	public static function insert_attachment_from_url( $url, $parent_post_id = null ){

		if ( ! class_exists( '\WP_Http' ) ) {
			require_once ABSPATH . WPINC . '/class-http.php';
		}

		$http     = new \WP_Http();
		$response = $http->request( $url );
		if ( 200 !== $response['response']['code'] ) {
			return false;
		}

		$upload = wp_upload_bits( basename( $url ), null, $response['body'] );
		if ( ! empty( $upload['error'] ) ) {
			return false;
		}

		$file_path        = $upload['file'];
		$file_name        = basename( $file_path );
		$file_type        = wp_check_filetype( $file_name, null );
		$attachment_title = sanitize_file_name( pathinfo( $file_name, PATHINFO_FILENAME ) );
		$wp_upload_dir    = wp_upload_dir();

		$post_info = array(
			'guid'           => $wp_upload_dir['url'] . '/' . $file_name,
			'post_mime_type' => $file_type['type'],
			'post_title'     => $attachment_title,
			'post_content'   => '',
			'post_status'    => 'inherit',
		);

		// Create the attachment.
		$attach_id = wp_insert_attachment( $post_info, $file_path, $parent_post_id );

		// Include files
		require_once ABSPATH . '/wp-admin/includes/media.php'; // video functions
		require_once ABSPATH . '/wp-admin/includes/file.php';
		require_once ABSPATH . '/wp-admin/includes/image.php';

		// Generate the attachment metadata.
		$attach_data = wp_generate_attachment_metadata( $attach_id, $file_path );

		// Assign metadata to attachment.
		wp_update_attachment_metadata( $attach_id, $attach_data );

		return $attach_id;
	}

	public static function caching_plugins(){
		return [
			// https://wordpress.org/plugins/wp-super-cache/
			'wp_super_cache' => [
				'enabled' => function_exists('wp_super_cache_init_action'),
				'flush_hook' => 'wp_cache_cleared',
				'flush_method' => [__CLASS__, '__flush__wp_super_cache']
			],
			// https://wp-rocket.me/
			'wprocket' => [
				'enabled' => defined('WP_ROCKET_VERSION'),
				'flush_hook' => 'after_rocket_clean_domain',
				'flush_method' => 'rocket_clean_minify'
			],
			// https://wordpress.org/plugins/w3-total-cache/
			'w3_total_cache' => [
				'enabled' => defined( 'W3TC' ) && W3TC,
				'flush_hook' => 'w3tc_flush_all',
				'flush_method' => 'w3tc_flush_all',
			],
			// https://wordpress.org/plugins/autoptimize/
			'autoptimize' => [
				'enabled' => function_exists( 'autoptimize' ),
				'flush_hook' => 'autoptimize_action_cachepurged',
				'flush_method' => ['autoptimizeCache', 'clearall'],
			],
			// https://wordpress.org/plugins/wp-fastest-cache/
			'wp_fastest_cache' => [
				'enabled' => class_exists('\WpFastestCache'),
				'flush_hook' => 'wpfc_clear_all_cache',
				'flush_method' => ['WpFastestCache', 'deleteCache'],
			],
			// https://wordpress.org/plugins/litespeed-cache/
			'litespeed' => [
				'enabled' => function_exists( 'run_litespeed_cache' ),
				'flush_hook' => 'litespeed_cache_api_purge',
				'flush_method' => [__CLASS__, '__flush__litspeed'],
			],
			// https://swiftperformance.io/
			// https://wordpress.org/plugins/swift-performance-lite/
			'swift_performance_lite' => [
				'enabled' => class_exists( '\Swift_Performance_Lite' ) || class_exists( '\Swift_Performance' ),
				'flush_hook' => 'swift_performance_after_clear_all_cache',
				'flush_method' => ['Swift_Performance_Cache', 'clear_all_cache'],
			],
			// https://wordpress.org/plugins/sg-cachepress/
			'sg_optimizer' => [
				'enabled' => class_exists( '\SiteGround_Optimizer\Options\Options' ),
				'flush_hook' => 'siteground_optimizer_flush_cache',
				'flush_method' => 'sg_cachepress_purge_cache',
			],
			// https://wordpress.org/plugins/breeze/
			'breeze' => [
				'enabled' => function_exists( 'breeze_get_option' ),
				'flush_hook' => ['breeze_clear_varnish', 'breeze_clear_all_cache'],
				'flush_method' => ['Breeze_PurgeCache', 'breeze_cache_flush'],
			],
			// https://wordpress.org/plugins/wp-optimize/
			'wp_optimize' => [
				'enabled' => class_exists( '\WP_Optimize' ),
				'flush_hook' => 'wpo_cache_flush',
				'flush_method' => ['WP_Optimize_Minify_Cache_Functions', 'reset'],
			],
			// https://wordpress.org/plugins/hummingbird-performance/
			'hummingbird' => [
				'enabled' => class_exists( '\\Hummingbird\\WP_Hummingbird' ),
				'flush_hook' => 'wphb_clear_page_cache',
				'flush_method' => 'wphb_flush_cache',
			],
			// https://wordpress.org/plugins/nitropack/
			'nitropack' => [
				'enabled' => defined( 'NITROPACK_VERSION' ),
				'flush_hook' => 'nitropack_integration_purge_all',
			],
			// https://wordpress.org/plugins/cloudflare/
			'cloudflare' => [
				'enabled' => class_exists( '\CF' ),
				// 'flush_hook' => '',
				'flush_method' => [__CLASS__, '__flush__cloudflare'],
			],
			// https://perfmatters.io/
			'perfmatters' => [
				'enabled' => defined( 'PERFMATTERS_VERSION' ),
				// 'flush_hook' => '',
				// 'flush_method' => '',
			],
			// https://wordpress.org/plugins/clearfy/
			'clearify' => [
				'enabled' => defined( 'WCL_PLUGIN_DIR' ),
				// 'flush_hook' => '',
				// 'flush_method' => '',
			],
			// https://flying-press.com/
			'flyingpress' => [
				'enabled' => defined( 'FLYING_PRESS_VERSION' ),
				// 'flush_hook' => '',
				// 'flush_method' => '',
			],
			// https://wordpress.org/plugins/page-optimize/
			'page_optimize' => [
				'enabled' => defined( 'PAGE_OPTIMIZE_CACHE_DIR' ),
				// 'flush_hook' => '',
				// 'flush_method' => '',
			],
		];
	}

	/**
	 * This hook will be executed when a caching plugin is flushing.
	 *
	 * @return void
	 */
	public function run_caching_flush(){
		do_action('reycore/caching_plugins/flush');
	}

	public static function __flush__wp_super_cache(){
		if( function_exists('wp_cache_clean_cache')) {
			global $file_prefix;
			if( ! empty($file_prefix) ){
				wp_cache_clean_cache( $file_prefix, true );
			}
		}
	}

	public static function __flush__litspeed(){
		do_action( 'litespeed_purge_all' );
	}

	public static function __flush__cloudflare(){
		$cloudflare = new \CF\WordPress\Hooks();
		$cloudflare->purgeCacheEverything();
	}

	/**
	 * Triggers the active caching plugin's flush
	 *
	 * @return void
	 */
	public function flush_plugin_cache(){

		foreach (self::caching_plugins() as $plugin) {
			if( $plugin['enabled'] && isset($plugin['flush_method']) && is_callable($plugin['flush_method']) ){
				call_user_func( $plugin['flush_method'] );
				error_log(var_export( $plugin['flush_method'], true));
			}
		}

	}

	public static function get_post_id_by_slug( $slug, $post_type = 'post' ){

		$args = [
			'name'           => $slug,
			'post_type'      => $post_type,
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids'
		];

		$posts = get_posts( $args );

		if( ! empty($posts) ) {
			return $posts[ 0 ];
		}

		return false;
	}

	public static function get_title_allowed_tags() {
		return [
			'h1' => 'H1',
			'h2' => 'H2',
			'h3' => 'H3',
			'h4' => 'H4',
			'h5' => 'H5',
			'h6' => 'H6',
			'p' => 'P',
			'span' => 'SPAN',
			'div' => 'DIV',
		];
	}

	public static function get_valid_title_tag( $tag, $default = 'h3' ) {
		$tag = strtolower( $tag );
		$allowed_tags = self::get_title_allowed_tags();
		return isset( $allowed_tags[ $tag ] ) ? $tag : $default;
	}

	/**
	 * Deprecated
	 *
	 * @return array
	 */
	public static function get_all_menus(){
		return [];
	}
}
