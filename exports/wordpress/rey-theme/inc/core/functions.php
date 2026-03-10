<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if(!function_exists('rey__get_theme_url')):
	/**
	 * Get Rey Theme URL
	 *
	 * @since 1.0.0
	 **/
	function rey__get_theme_url()
	{
		return esc_url( wp_get_theme( REY_THEME_NAME )->get('ThemeURI') );
	}
endif;


if(!function_exists('rey__get_core_path')):
	/**
	 * Get Core Plugin Path
	 *
	 * @since 1.0.0
	 **/
	function rey__get_core_path()
	{
		return sprintf('%s/%s.php', REY_THEME_CORE_SLUG, REY_THEME_CORE_SLUG);
	}
endif;

/**
 * Retrieve Rey's properties
 *
 * @since 2.3.3
 **/
function rey__get_props( $prop = '' )
{

	if( ! class_exists('ReyTheme_Setup') ){
		return;
	}

	$props = ReyTheme_Setup::$props;

	if( ! empty($prop) && isset($props[$prop]) ){

		// checks user ID
		if( isset($props[$prop]['user_id']) ){
			global $current_user;

			if( is_array($props[$prop]['user_id']) ){
				return $current_user && isset($current_user->ID) && in_array($current_user->ID, $props[$prop]['user_id'], true);
			}
			else {
				return $current_user && isset($current_user->ID) && $props[$prop]['user_id'] === $current_user->ID;
			}

		}

		// checks capability
		elseif( isset($props[$prop]['capability']) ){
			return current_user_can( rey__clean($props[$prop]['capability']) );
		}

		return $props[$prop];
	}

	return $props;
}


if(!function_exists('rey__config')):
	/**
	 * Internal configuration settings
	 *
	 * @param $setting The setting to pull
	 * @return array
	 *
	 * @since 1.0.0
	 */
	function rey__config( $setting = '' ) {

		$settings = apply_filters('rey/config_settings', [
			'animate_blog_items' => true,
		]);

		if( isset($settings[$setting]) ) {
			return $settings[$setting];
		}

		return $settings;
	}
endif;

if(!function_exists('rey__support_url')):
	/**
	 * Get Support URL
	 *
	 * @since 2.3.3
	 **/
	function rey__support_url( $url = '' )
	{
		if( ! rey__get_props('kb_links') ){
			return '#';
		}

		return rey__get_props('support_url') . $url;
	}
endif;

if(!function_exists('rey_assets')):
	/**
	 * Get Rey Assets
	 *
	 * @since 2.5.0
	 **/
	function rey_assets(){
		return ReyTheme_Assets::getInstance();
	}
endif;

if(!function_exists('reyAssets')):
	/**
	 * Get Rey Assets backwards compatibility.
	 * Don't remove.
	 *
	 * @since 2.5.0
	 **/
	function reyAssets(){
		return rey_assets();
	}
endif;

if(!function_exists('rey__get_post_id')):
	/**
	 * Wrapper for queried object
	 *
	 * @since 1.0.0
	 */
	function rey__get_post_id() {

		if( class_exists('WooCommerce') && is_shop() ){
			return wc_get_page_id('shop');
		}
		elseif( is_home() ){
			return absint( get_option('page_for_posts') );
		}
		elseif( is_tax() || is_archive() || is_author() || is_category() || is_tag() ){
			if( apply_filters('rey/get_queried_object_id', false, 'rey' ) ){
				return get_queried_object_id();
			}
			else {
				return get_queried_object();
			}
		}
		elseif( isset($_GET['preview_id']) && isset($_GET['preview_nonce']) && ($pid = $_GET['preview_id']) ){
			return absint( $pid );
		}

		return false;
	}
endif;


if(!function_exists('rey__acf_get_field')):
	/**
	 * Get ACF Field - wrapper for get_field
	 *
	 * @since 1.0.0
	 **/
	function rey__acf_get_field( $name, $pid = false, $return = false )
	{

		if( !$pid ) {
			$pid = rey__get_post_id();
		}

		// check for ACF and get the field
		if( class_exists('ACF') && $opt = apply_filters("rey_acf_option_{$name}", get_field( $name, $pid ), $pid ) )  {
			return $opt;
		}

		return $return;
	}
endif;


if(!function_exists('rey__get_option')):
	/**
	 * Get Option - wrapper for get_theme_mod and get_field
	 * overrides rey__get_option from theme
	 *
	 * @since 1.0.0
	 **/
	function rey__get_option( $name, $default = false )
	{

		// check for ACF and get the field
		if( $opt = rey__acf_get_field( $name ) ) {
			return $opt;
		}

		return get_theme_mod( $name, $default);
	}
endif;


if(!function_exists('rey__is_blog_list')):
	/**
	 * Check if it's a blog listing
	 *
	 * @since 1.0.0
	 **/
	function rey__is_blog_list()
	{
		return apply_filters('rey/is_blog_list', ( is_archive() || is_author() || is_category() || is_home() || is_single() || is_tag()) && ( 'post' == get_post_type() || rey__ctp_supports_blog() ));
	}
endif;


if(!function_exists('rey__has_blocks')):
	/**
	 * Check if it's a blog listing
	 *
	 * @since 1.0.0
	 **/
	function rey__has_blocks()
	{
		global $post;

		if( ! isset($post->ID) ){
			return;
		}

		// just load for posts
		if( is_singular('post') ){
			return true;
		}

		if( ! has_blocks($post->ID) ){
			return false;
		}

		$contain_others = [];

		$ignore_list = [
			'core/paragraph',
			'core/heading',
			'core/image',
			'core/html',
		];

		foreach (array_filter(parse_blocks($post->post_content)) as $block) {

			if( ! isset($block['blockName']) ){
				continue;
			}

			if( is_null($block['blockName']) ){
				continue;
			}

			if( ! in_array($block['blockName'], $ignore_list, true) ){
				$contain_others[] = $block['blockName'];
				break;
			}

		}

		if( empty($contain_others) ){
			return false;
		}

		return true;
	}
endif;


if(!function_exists('rey__wp_parse_args')):
	/**
	 * Recursive wp_parse_args WordPress function which handles multidimensional arrays
	 * @url http://mekshq.com/recursive-wp-parse-args-wordpress-function/
	 * @param  array &$a Args
	 * @param  array $b Defaults
	 * @since: 1.0.0
	 */
	function rey__wp_parse_args( &$a, $b )
	{
		$a = (array)$a;
		$b = (array)$b;
		$result = $b;
		foreach ( $a as $k => &$v )
		{
			if ( is_array( $v ) && isset( $result[ $k ] ) )
			{
				$result[ $k ] = rey__wp_parse_args( $v, $result[ $k ] );
			}
			else
			{
				$result[ $k ] = $v;
			}
		}
		return $result;
	}
endif;


if(!function_exists('rey__can_show_post_thumbnail')):
	/**
	 * Determines if post thumbnail can be displayed.
	 * @since 1.0.0
	 */
	function rey__can_show_post_thumbnail() {

		$can = ! post_password_required() && ! is_attachment() && has_post_thumbnail();

		if(
			(!is_singular() && !get_theme_mod('blog_thumbnail_visibility', true)) ||
			(is_singular() && !get_theme_mod('post_thumbnail_visibility', true))
		){
			$can = false;
		}

		return apply_filters( 'rey/content/post_thumbnail', $can );
	}
endif;


if(!function_exists('rey__estimated_reading_time')):
	/**
	 * Get estimated read time (minutes) for current post in loop.
	 * @return int Estimated time in minutes to read post
	 * @since 1.0.0
	 */
	function rey__estimated_reading_time( $avg = 200 ){
		$post = get_post();
		$words = str_word_count(strip_tags($post->post_content));
		$average_reading_rate = apply_filters('rey/estimated_reading_time/count', $avg); // The average reading rate is actually 238, according to studies, but 200 is a nice compromise and is easier to remember.
		$minutes = floor($words / $average_reading_rate);
		if($minutes == 0) $minutes = 1;
		return $minutes;
	}
endif;


if(!function_exists('rey__words_limit')):
	/**
	 * Truncate a string based on provided word count and include terminator
	 * @param       string $string      String to be truncated
	 * @param       int $length         Number of characters to allow before split
	 * @param       string $terminator  (Optional) String terminator to be used
	 * @return      string              Truncated string with add terminator
	 * @since 1.0.0
	 */
	function rey__words_limit($string, $length, $terminator = ""){

		if(mb_strlen($string) <= $length){
			$string = $string;
		}
		else{
			$string = preg_replace('/\s+?(\S+)?$/', '', mb_substr($string, 0, $length)) . $terminator;
		}

		return $string;
	}
endif;


if(!function_exists('rey__unique_id')):
	/**
	 * Get unique ID.
	 *
	 * This is a PHP implementation of Underscore's uniqueId method. A static variable
	 * contains an integer that is incremented with each call. This number is returned
	 * with the optional prefix. As such the returned value is not universally unique,
	 * but it is unique across the life of the PHP process.
	 * @staticvar int $id_counter
	 *
	 * @param string $prefix Prefix for the returned ID.
	 * @return string Unique ID.
	 * @since 1.0.0
	 */
	function rey__unique_id( $prefix = '' ) {
		static $id_counter = 0;
		if ( function_exists( 'wp_unique_id' ) ) {
			return wp_unique_id( $prefix );
		}
		return $prefix . (string) ++$id_counter;
	}
endif;


if(!function_exists('rey__get_first_img')):
	/**
	 * Get first image in post
	 *
	 * @param post - POST ID
	 * @param attachment_id bool . Return id instead of URL.
     * @return string
	 * @since 1.0.0
	 */
	function rey__get_first_img($post = 0, $attachment_id = false) {

		if ( ! $post = get_post( $post ) )
			return array();

		$img = '';
		$output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);

		if( isset($matches[1][0]) ){
			$img = $matches[1][0];
		}

		if( !empty($img) ) {

			// Return Img ID
			if( $attachment_id ) {
				// cleanup path from url query's
				$img_url = parse_url($img);
				$img = sprintf('%s://%s%s', $img_url['scheme'], $img_url['host'], $img_url['path']);
				return attachment_url_to_postid( $img );
			}
			else {
				return $img;
			}
		}
		return false;
	}
endif;


if(!function_exists('rey__log_error')):
	/**
	 * Log Errors
	 *
	 * @since 1.0.0
	 **/
	function rey__log_error( $source, $error )
	{
		if( ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ){
			error_log(var_export( [$source, $error] ,1));
		}
	}
endif;

if(!function_exists('rey__clean')):
	/**
	 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
	 * Non-scalar values are ignored.
	 *
	 * @param string|array $var Data to sanitize.
	 * @return string|array|null
	 */
	function rey__clean( $var, $unslash = true ) {

		if ( is_array( $var ) ) {
			return array_map( function($item) use ($unslash) {
				return rey__clean($item, $unslash);
			}, $var );
		}

		else {
			if( is_bool($var) ){
				return filter_var($var, FILTER_VALIDATE_BOOLEAN);
			}
			else {

				if( ! is_scalar( $var ) ){
					return;
				}

				if( ! $unslash ){
					return sanitize_text_field( $var );
				}

				return sanitize_text_field( wp_unslash($var) );
			}
		}
	}
endif;


if(!function_exists('rey__is_godaddy')):
	/**
	 * This function will search in various places for any of the default GoDaddy files,
	 * and if any is found then we assume this is a GoDaddy hosting
	 * @return bool
	 * @since 1.0.0
	 */
	function rey__is_godaddy()
	{
		$root       = trailingslashit(ABSPATH);
		$pluginsDir = (defined('WP_CONTENT_DIR') ? trailingslashit(WP_CONTENT_DIR) . 'mu-plugins/' : $root . 'wp-content/mu-plugins/');

		if ( is_file( $root . 'gd-config.php' )) {
			return true;
		}
		elseif ( is_dir($pluginsDir . 'gd-system-plugin') || is_file($pluginsDir . 'gd-system-plugin.php') ) {
			return true;
		}
		elseif ( class_exists('\WPaaS\Plugin') ) {
			return true;
		}
		return false;
	}
endif;


if(!function_exists('rey__maybe_disable_obj_cache')):
	/**
	 * This function will temporarily disable WordPress object cache
	 * Useful for hosts such as GoDaddy that causes WP transients API not working properly
	 * @return bool
	 * @since 1.0.0
	 */
	function rey__maybe_disable_obj_cache()
	{

		if( ! (defined('REY_DISABLE_OBJECT_CACHE') && REY_DISABLE_OBJECT_CACHE) ){
			return;
		}

		$status = false;

		if( rey__is_godaddy() ){
			$status = true;
		}

		if( apply_filters('rey/temporarily_disable_obj_cache', $status) ){
			wp_using_ext_object_cache( false );
		}
	}
endif;


if(!function_exists('rey__wp_filesystem')):
	/**
	 * Retrieve the reference to the instance of the WP file system
	 * @return $wp_filesystem
	 * @since 1.0.0
	 */
	function rey__wp_filesystem()
	{
		//#! Set the permission constants if not already set.
		if ( ! defined( 'FS_CHMOD_DIR' ) ) {
			define( 'FS_CHMOD_DIR', ( fileperms( ABSPATH ) & 0777 | 0755 ) );
		}
		if ( ! defined( 'FS_CHMOD_FILE' ) ) {
			define( 'FS_CHMOD_FILE', ( fileperms( ABSPATH . 'index.php' ) & 0777 | 0644 ) );
		}

		//#! Setup a new instance of WP_Filesystem_Direct and use it
		global $wp_filesystem;

		if ( ! ( $wp_filesystem instanceof \WP_Filesystem_Base ) ) {
			if ( ! class_exists( 'WP_Filesystem_Direct' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php' );
				require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php' );
			}
			$wp_filesystem = new \WP_Filesystem_Direct( [] );
		}
		return $wp_filesystem;
	}
endif;


if(!function_exists('rey__wp_kses')):
	/**
	 * Allow basic tags
	 *
	 * @since 1.0.0
	 **/
	function rey__wp_kses($string = '')
	{
		return wp_kses($string, [
			'a' => [
				'class' => [],
				'href' => [],
				'target' => []
			],
			'code' => [
				'class' => []
			],
			'strong' => [],
			'br' => [],
			'em' => [],
			'p' => [
				'class' => []
			],
			'span' => [
				'class' => []
			],
		]);
	}
endif;


if(!function_exists('rey__implode_html_attributes')):
	/**
	 * Implode and escape HTML attributes for output.
	 *
	 * @since 1.9.4
	 * @param array $raw_attributes Attribute name value pairs.
	 * @return string
	 */
	function rey__implode_html_attributes( $raw_attributes ) {

		$rendered_attributes = [];

		foreach ( $raw_attributes as $attribute_key => $attribute_values ) {
			if ( is_array( $attribute_values ) ) {
				$attribute_values = implode( ' ', $attribute_values );
			}

			$rendered_attributes[] = sprintf( '%1$s="%2$s"', $attribute_key, esc_attr( $attribute_values ) );
		}

		return implode( ' ', $rendered_attributes );
	}
endif;


if(!function_exists('rey__valid_url')):
	/**
	 * Checks for valid 200 response code
	 *
	 * @since 2.4.0
	 **/
	function rey__valid_url($url, $return_code = false)
	{

		$response = wp_safe_remote_get( $url );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$code = wp_remote_retrieve_response_code( $response );

		if( $return_code ){
			return $code;
		}

		return 200 === $code;
	}
endif;

if(!function_exists('rey__core_assets')):
	/**
	 * Get ReyCore Assets
	 *
	 * @since 2.5.0
	 **/
	function rey__core_assets()
	{
		if( function_exists('reycore_assets') ){
			return reycore_assets();
		}
		elseif( function_exists('reyCoreAssets') ){
			return reyCoreAssets();
		}
	}
endif;
