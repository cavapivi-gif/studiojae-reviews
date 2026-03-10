<?php
namespace ReyCore;

use ReyCore\Helper;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class AssetsHandler
{
	/**
	 * Store Filesystem
	 */
	private static $fs;

	/**
	 * Path where to save files.
	 */
	private static $dir_path;

	/**
	 * WP Uploads Folder
	 *
	 * @var array
	 */
	private static $wp_uploads_dir = [];

	/**
	 * Assets manager
	 *
	 * @var AssetsManager
	 */
	public static $assets_manager;

	/**
	 * Holds registered styles
	 *
	 * @var array
	 */
	private static $registered_styles = [];

	/**
	 * Holds registered scripts
	 *
	 * @var array
	 */
	private static $registered_scripts = [];

	/**
	 * Holds collected scripts
	 *
	 * @var array
	 */
	private $scripts = [];

	/**
	 * Buffer manager instance
	 *
	 * @var BufferManager
	 */
	private static $buffer;

	/**
	 * Handle log times
	 *
	 * @var string
	 */
	private static $current_time;

	/**
	 * Determine what to log
	 *
	 * @var array
	 */
	public static $logs = [
		'time'          => false,
		'styles'        => false,
		'scripts'       => false,
		'print_handles' => true,
	];

	/**
	 * Detects paths in CSS.
	 */
	const ASSETS_REGEX = '/url\s*\(\s*(?!["\']?data:)(?![\'|\"]?[\#|\%|])([^)]+)\s*\)([^;},\s]*)/i';

	public function __construct($manager)
	{

		self::set_filesystem();

		self::$assets_manager = $manager;

		add_action( 'reycore/buffer/assets', [$this, 'handle_assets']);
		add_action( 'init', [$this, 'handle_clear_data']);
		add_action( 'rey/flush_cache_after_updates', [$this, 'clear__basic'], 20);
		add_action( 'reycore/refresh_all_caches', [$this, 'clear__basic'], 10);
		add_filter( 'reycore/admin_bar_menu/nodes', [$this, 'adminbar__add_refresh'], 20);
		add_action( 'wp_ajax__refresh_assets', [$this, 'adminbar__clear_assets']);

	}

	public function handle_assets($buffer)
	{
		self::$buffer = $buffer;

		if( self::$assets_manager->get_settings('disable_assets') ){
			return;
		}

		$this->handle_css();
		$this->handle_js();

	}

	/**
	 * Start finding CSS occurances and handle CSS merging
	 *
	 * @return void
	 */
	public function handle_css(){

		if( ! self::$assets_manager->get_settings('save_css') ){
			return;
		}

		// start timing and logging
		do_action( 'qm/start', 'rey_handle_css' );

		// define excludes from merging
		$excludes = apply_filters('reycore/buffer/css/excluded', [
			REY_CORE_STYLESHEET_HANDLE,
			'reycore-frontend-admin',
		]);

		$output = '';

		self::$registered_styles = self::$assets_manager->get_register_assets('styles');

		foreach (self::$assets_manager->get_styles() as $type => $stylesheets) {

			if( empty($stylesheets) ){
				continue;
			}

			// usually dev. mode
			if( ! self::$assets_manager->get_settings('css_combine') ){
				foreach ($stylesheets as $stylesheet_handle) {
					if( isset(self::$registered_styles[$stylesheet_handle]) && ($stylesheet_data = self::$registered_styles[$stylesheet_handle]) ){
						$output .= self::print_css_tag($type, $stylesheet_data, $stylesheet_handle );
					}
				}
				continue;
			}

			$stylesheets = array_diff($stylesheets, $excludes);

			$hash = self::get_hash( $stylesheets, self::$registered_styles );

			$file = [
				'path' => self::$dir_path . self::get__stylesheet__basename($hash, $type), // server
				'src' => self::get__base_uploads__url() . self::get__stylesheet__basename($hash, $type), // url
			];

			$css_content = '';

			// if it already exists, don't rewrite it, just stop
			if( ! self::$fs->is_file( $file['path'] ) ){
				// write the CSS file
				$css_content = $this->write_css($stylesheets, $type, $file['path']);
			}

			if( self::$assets_manager::NAME_HEAD === $type && 'inline' === self::$assets_manager->get_settings('css_head') ){
				$output .= self::print_css_tag_inline($css_content, $file);
			}
			else {
				$output .= self::print_css_tag($type, $file);
			}

		}

		// update the buffer
		$this->update_buffer( self::$assets_manager::HEAD_PLACEHOLDER, $output );

		do_action( 'qm/stop', 'rey_handle_css' );

	}

	/**
	 * Write stylesheet to the uploads folder.
	 *
	 * @param array $stylesheets
	 * @param string $type
	 * @param string $filepath Target stylesheet
	 * @return void
	 */
	private function write_css( $stylesheets, $type, $filepath){

		$css = [];
		$data_to_log = [];

		// go through stylesheets
		foreach ($stylesheets as $handle ) {

			$stylesheet_data = self::$registered_styles[$handle];

			// check for path and if the file actually exists
			if( ! (isset($stylesheet_data['path']) && self::$fs->is_file( $stylesheet_data['path'] )) ) {
				continue;
			}

			// grabs CSS
			$stylesheet_css = self::$fs->get_contents( $stylesheet_data['path'] );
			// fix urls
			$stylesheet_css = self::fixurls($stylesheet_data['path'], $stylesheet_css);
			// collect css
			$css[$handle] = $stylesheet_css;
			// collect logging handles
			$data_to_log[] = $handle;
		}

		// check if CSS data exists
		if( ! empty($css) ){

			// minify the css data
			$css_contents = self::minify_css($css);

			// log
			self::log( sprintf('Stored %2$s stylesheet in "%3$s", FN: "%1$s".', $filepath, strtoupper($type), reycore__get_page_title() ) );

			// append handles
			$css_contents .= $this->debug_print_handles($data_to_log);

			// actually write the file
			self::$fs->put_contents( $filepath, $css_contents );

			return $css_contents;
		}

		return false;
	}

	/**
	 * Start finding JS occurances and handle JS merging
	 *
	 * @return void
	 */
	public function handle_js(){

		if( ! self::$assets_manager->get_settings('save_js') ){
			return;
		}

		do_action( 'qm/start', 'rey_handle_js' );

		$scripts = self::$assets_manager->get_all_scripts();

		if( ! empty( $scripts ) ){

			// define excludes from merging
			$scripts = array_diff($scripts, apply_filters('reycore/buffer/js/excluded', [
				'reyadminbar',
				'reycore-frontend-admin',
			]));

			self::$registered_scripts = self::$assets_manager->get_register_assets('scripts');

			// create unique hash
			$hash = self::get_hash( $scripts, self::$registered_scripts );

			$file = [
				'path' => self::$dir_path . self::get__scripts__basename($hash), // server
				'url' => self::get__base_uploads__url() . self::get__scripts__basename($hash), // url
			];

			// if it already exists, don't rewrite it, just stop
			if( ! self::$fs->is_file( $file['path'] ) ){
				// write the JS file
				$this->write_js($scripts, $file['path']);
			}

			$search_for = '</body>';

			// update the buffer
			$this->update_buffer(
				$search_for,
				$this->get_script_output( $file['url'], self::get_version($file)) . $search_for
			);

		}

		do_action( 'qm/stop', 'rey_handle_js' );

	}

	public function get_script_output( $path, $version ){

		// delayed
		if( self::$assets_manager->get_settings('delay_js') ){

			// delay JS
			// reyDelay-DOMContentLoaded to be added to delay_forced_js_event
			// exclus lazy bg
			// is logged out?. Just current_user_can('administrator') to not delay JS.
			return sprintf("<script type='text/javascript' id='rey-delay-js' %s>
				(function(){
					let didDelayJS = false;
					const events = [ 'mousemove', 'scroll', 'keydown', 'click', 'touchstart' ];
					events.forEach(event => {
						document.body.addEventListener(event, function(){
							if( ! didDelayJS) {
								new Promise(function(resolve, reject) {
									var script = document.createElement('script');
										const url = '%s?%s';
										script.src = url;
										script.id = 'rey-combined-js';
										script.async = false;
										script.onload = () => {
											// dispatch event
											document.dispatchEvent(new CustomEvent('reyDelay-DOMContentLoaded'));
											resolve(url);
										};
										script.onerror = () => {reject(url)};
									document.body.appendChild(script);
									didDelayJS = true;
									console.log(script);
								});
							}
						}, {once: true});
					});
				})();
			</script>", reycore__js_no_opt_attr(), $path, $version );

		}

		return sprintf('<script defer type="text/javascript" id="rey-combined-js" src="%1$s?ver=%2$s"></script>', $path, $version ) . "\n";
	}

	/**
	 * Write scripts to the uploads folder.
	 *
	 * @param string $filepath Target script
	 * @return void
	 */
	private function write_js($scripts, $filepath){

		$js = [];
		$data_to_log = [];


		// go through scripts
		foreach ($scripts as $handle ) {

			if( ! isset(self::$registered_scripts[$handle]) ){
				continue;
			}

			$script = self::$registered_scripts[$handle];

			// check for path and if the file actually exists
			if( ! (isset($script['path']) && self::$fs->is_file( $script['path'] )) ) {
				continue;
			}

			// collect css
			$js[$handle] = self::$fs->get_contents( $script['path'] );

			// collect logging handles
			$data_to_log[] = $handle;
		}

		// check if CSS data exists
		if( ! empty($js) ){

			// log
			self::log( sprintf('Stored SCRIPT in "%2$s", FN: "%1$s".', $filepath, reycore__get_page_title() ) );

			// append handles
			$js[] = $this->debug_print_handles($data_to_log);

			// actually write the file
			return self::$fs->put_contents( $filepath, implode('', $js) );
		}

		return false;
	}

	/**
	 * Update the buffer by replacing the existing tags.
	 *
	 * @param string $tag
	 * @param string $replace_with
	 * @return void
	 */
	private function update_buffer($tag, $replace_with = '', $first_only = false){

		// get existing buffer content
		$buffer_content = self::$buffer->get_buffer();

		if( $first_only ){
			$new_content = preg_replace('/' . $tag . '/', $replace_with, $buffer_content, 1);
		}
		else {
			// remove existing ones
			$new_content = str_replace($tag, $replace_with, $buffer_content);
		}

		// update buffer content
		self::$buffer->set_buffer($new_content);

	}

	/**
	 * Render the CSS tags
	 *
	 * @param string $type
	 * @param array $file
	 * @param string $defer
	 * @return string
	 */
	public static function print_css_tag($type, $file, $id = '' ){

		$handle = sprintf('rey-%s-css', ($id ? $id : $type));

		$tag = [
			'start' => sprintf('<link id="%1$s" type="text/css" href="%2$s?ver=%3$s" %4$s', $handle, $file['src'], self::get_version($file), reycore__css_no_opt_attr() ),
			'mid' => ' rel="stylesheet" media="all" ',
			'end' => ' />' . "\n",
			'noscript' => sprintf('<noscript><link rel="stylesheet" href="%s" data-no-minify="1"></noscript>', $file['src']) . "\n",
		];

		// if head, can load defer or block
		if( self::$assets_manager::NAME_HEAD === $type ){
			// maybe some day when critical is generated
			if( 'defer' === self::$assets_manager->get_settings('css_head') ){
				return $tag['start'] . self::$assets_manager::get_defer_method() . $tag['end'] . $tag['noscript'];
			}
			else {

				$the_tag = $tag['start'] . $tag['mid'] . $tag['end'];

				// main stylesheet in block mode, should be handled by caching plugins
				if( 'block' === self::$assets_manager->get_settings('css_head') ){
					$the_tag = str_replace(reycore__css_no_opt_attr(), '', $the_tag);
					wp_register_style( $handle, $file['src'] ); // register style in WP
				}

				return $the_tag;
			}
		}

		// DF, deferred or interactive
		// deferred early or late
		else if( self::$assets_manager::NAME_LAZY === $type ){

			$delay_mode = self::$assets_manager->get_settings('css_delay_mode');

			if( $delay_mode && in_array($delay_mode, ['defer', 'defer_late'], true) ){
				// block the deferred stylesheet
				if( defined('REY_DEFER_TEST') && ! current_user_can('administrator') ){
					return '';
				}
				wp_register_style( $handle, $file['src'] ); // register style in WP
				return $tag['start'] . self::$assets_manager::get_defer_method( 'defer' === $delay_mode ) . $tag['end'] . $tag['noscript'];
			}

			else if( 'block' === $delay_mode ){
				$the_tag = $tag['start'] . $tag['mid'] . $tag['end'];
				$the_tag = str_replace(reycore__css_no_opt_attr(), '', $the_tag);
				wp_register_style( $handle, $file['src'] ); // register style in WP
				return $the_tag;
			}

			else {
				return str_replace('href', self::$assets_manager::LAZY_ATTRIBUTE, $tag['start']) . $tag['mid'] . $tag['end'];
			}
		}

	}

	/**
	 * Render the CSS tag inline
	 *
	 * @param string $type
	 * @param array $file
	 * @param string $defer
	 * @return string
	 */
	public static function print_css_tag_inline($css_content, $file){

		if( ! $css_content ){
			$css_content = self::$fs->get_contents( $file['path'] );
		}

		return sprintf('<style type="text/css" id="rey-%2$s-css" %3$s>%1$s</style>', $css_content, self::$assets_manager::NAME_HEAD, reycore__css_no_opt_attr() ) . "\n";
	}


	/**
	 * Returns the current version with or without a suffix
	 *
	 * @param array $file
	 * @return string
	 */
	public static function get_version($file){

		$suffix = '';
		if( isset($file['path']) && self::$fs->is_file( $file['path'] )){
			$suffix = '.' . filemtime( $file['path'] );
		}

		return REY_CORE_VERSION . $suffix;
	}

	/**
	 * Very basic and minimal minification of the CSS.
	 * Should be actually done by caching plugins
	 *
	 * @param array $css
	 * @return string
	 */
	public static function minify_css($css = []){

		$css_content = str_replace(
			[
				': ',
				';  ',
				'; ',
				'  '
			],
			[
				':',
				';',
				';',
				' '
			],
			preg_replace( "/\r|\n/", '', implode('', $css) )
		);

		// // comments
		// $string = preg_replace('!/\*.*?\*/!s','', $string);
		// $string = preg_replace('/\n\s*\n/',"\n", $string);

		// // space
		// $string = preg_replace('/[\n\r \t]/',' ', $string);
		// $string = preg_replace('/ +/',' ', $string);
		// $string = preg_replace('/ ?([,:;{}]) ?/','$1',$string);

		// // trailing;
		// $string = preg_replace('/;}/','}',$string);

		return $css_content;
	}

	public static function get_hash($handles, $registered){

		$items = [];

		foreach ($handles as $handle) {
			$version = isset($registered[$handle]['version']) ? $registered[$handle]['version'] : REY_CORE_VERSION;
			$items[] = $handle . '-' . $version;
		}

		return Helper::hash( $items );
	}

	/**
	 * Set the filesystem app and the upload
	 * folders
	 *
	 * @return void
	 */
	public static function set_filesystem(){

		if( self::$fs ){
			return self::$fs;
		}

		if( !($fs = reycore__wp_filesystem()) ){
			return;
		}

		self::$fs = $fs;

		$dir_path = self::get__base_uploads__dir();

		if ( ! self::$fs->is_dir( $dir_path ) ) {
			self::$fs->mkdir( $dir_path );
		}

		self::$dir_path = trailingslashit( $dir_path );

		return self::$fs;
	}

	/**
	 * Get WordPress Uploads folder absolute path
	 *
	 * @return string
	 */
	private static function get__wp_uploads__dir() {
		global $blog_id;

		if ( empty( self::$wp_uploads_dir[ $blog_id ] ) ) {
			self::$wp_uploads_dir[ $blog_id ] = wp_upload_dir( null, false );
		}

		return self::$wp_uploads_dir[ $blog_id ];
	}

	/**
	 * Get Rey's Uploads folder absolute path
	 *
	 * @return string
	 */
	public static function get__base_uploads__dir() {
		$wp_upload_dir = self::get__wp_uploads__dir();
		return trailingslashit($wp_upload_dir['basedir']) . REY_CORE_THEME_NAME . '/';
	}

	/**
	 * Get Rey's Uploads folder relative site path
	 *
	 * @return string
	 */
	public static function get__base_uploads__url() {
		$wp_upload_dir = self::get__wp_uploads__dir();
		return trailingslashit(set_url_scheme( $wp_upload_dir['baseurl'] )) . REY_CORE_THEME_NAME . '/';
	}

	/**
	 * Get the stylesheets file base name
	 *
	 * @param string $hash
	 * @param string $type
	 * @return string
	 */
	private static function get__stylesheet__basename( $hash, $type = '' ){
		return sprintf('%s-%s%s.css', ( ! $type ? self::$assets_manager::NAME_HEAD : $type), $hash, AssetsManager::rtl());
	}

	/**
	 * Get the combined script file base name
	 *
	 * @param string $hash
	 * @return string
	 */
	private static function get__scripts__basename( $hash ){
		return sprintf('scripts-%s.js', $hash);
	}

	/**
	 * Log messages to console if debug enabled
	 *
	 * @param string $message
	 * @return void
	 */
	public static function log($message){
		do_action( 'qm/debug', '::Assets: ' . $message );
		if( self::is_debug_log_assets() ){
			error_log(var_export( '::Assets: ' . $message ,true));
		}
	}

	public static function start_log_time(){}
	public static function end_log_time($prefix = ''){}

	/**
	 * Adds comments inside the files
	 *
	 * @param array $data_to_log
	 * @return string
	 */
	public function debug_print_handles( $data_to_log ){

		if( self::$logs['print_handles'] && self::is_debug_log_assets() ){
			return "\r\n" . '/** ' . "\r\n" . implode( "\r\n", $data_to_log ) . "\r\n" . reycore__get_page_title() . "\r\n" . '*/';
		}

	}

	/**
	 * Checks if logging is enabled
	 *
	 * @return boolean
	 */
	public static function is_debug_log_assets(){
		return defined('REY_DEBUG_LOG_ASSETS') && REY_DEBUG_LOG_ASSETS;
	}

	/**
	 * Retrieve assets paths
	 *
	 * @param array $assets
	 * @param string $type
	 * @return array
	 */
	public function get_assets_paths( $assets, $type = 'styles' ){ return []; }

	/**
	 * Make sure URL's are absolute iso relative to original CSS location.
	 *
	 * @param string $file filename of optimized CSS-file.
	 * @param string $code CSS-code in which to fix URL's.
	 */
	static function fixurls( $file, $code )
	{
		// Switch all imports to the url() syntax.
		$code = preg_replace( '#@import ("|\')(.+?)\.css.*?("|\')#', '@import url("${2}.css")', $code );


		if ( preg_match_all( self::ASSETS_REGEX, $code, $matches ) ) {

			$wp_content_name = '/' . wp_basename( WP_CONTENT_DIR );
			$wp_root_dir = substr( WP_CONTENT_DIR, 0, strlen( WP_CONTENT_DIR ) - strlen( $wp_content_name ) );
			$wp_root_url = str_replace( $wp_content_name, '', content_url() );

			$file = str_replace( $wp_root_dir, '/', $file );
			$dir = dirname( $file ); // Like /themes/expound/css.

			/**
			 * $dir should not contain backslashes, since it's used to replace
			 * urls, but it can contain them when running on Windows because
			 * fixurls() is sometimes called with `ABSPATH . 'index.php'`
			 */
			$dir = str_replace( '\\', '/', $dir );
			unset( $file ); // not used below at all.

			$replace = array();
			foreach ( $matches[1] as $k => $url ) {
				// Remove quotes.
				$url      = trim( $url, " \t\n\r\0\x0B\"'" );
				$no_q_url = trim( $url, "\"'" );
				if ( $url !== $no_q_url ) {
					$removed_quotes = true;
				} else {
					$removed_quotes = false;
				}

				if ( '' === $no_q_url ) {
					continue;
				}

				$url = $no_q_url;
				if ( '/' === $url[0] || preg_match( '#^(https?://|ftp://|data:)#i', $url ) ) {
					// URL is protocol-relative, host-relative or something we don't touch.
					continue;
				} else { // Relative URL.

					$newurl = preg_replace( '/https?:/', '', str_replace( ' ', '%20', $wp_root_url . str_replace( '//', '/', $dir . '/' . $url ) ) );


					/**
					 * Hash the url + whatever was behind potentially for replacement
					 * We must do this, or different css classes referencing the same bg image (but
					 * different parts of it, say, in sprites and such) loose their stuff...
					 */
					$hash = md5( $url . $matches[2][ $k ] );
					$code = str_replace( $matches[0][ $k ], $hash, $code );

					if ( $removed_quotes ) {
						$replace[ $hash ] = "url('" . $newurl . "')" . $matches[2][ $k ];
					} else {
						$replace[ $hash ] = 'url(' . $newurl . ')' . $matches[2][ $k ];
					}
				}
			}

			$code = self::replace_longest_matches_first( $code, $replace );
		}

		return $code;
	}

	/**
	 * Given an array of key/value pairs to replace in $string,
	 * it does so by replacing the longest-matching strings first.
	 *
	 * @param string $string string in which to replace.
	 * @param array  $replacements to be replaced strings and replacement.
	 *
	 * @return string
	 */
	protected static function replace_longest_matches_first( $string, $replacements = array() )
	{
		if ( ! empty( $replacements ) ) {
			// Sort the replacements array by key length in desc order (so that the longest strings are replaced first).
			$keys = array_map( 'strlen', array_keys( $replacements ) );
			array_multisort( $keys, SORT_DESC, $replacements );
			$string = str_replace( array_keys( $replacements ), array_values( $replacements ), $string );
		}

		return $string;
	}

	// CLEAR DATA

	public function adminbar__add_refresh($nodes){

		if( ! current_user_can('administrator') ){
			return $nodes;
		}

		$assets_settings = self::$assets_manager->get_settings();

		if( ! ($assets_settings['save_css'] || $assets_settings['save_js']) ){
			return $nodes;
		}

		if( isset($nodes['refresh']) ){
			$nodes['refresh']['nodes']['refresh_assets'] = [
				'title'  => esc_html__( 'Assets Cache', 'rey-core' ) . ' (CSS/JS)',
				'href'  => '#',
				'meta_title' => esc_html__( 'Refresh the combined and minified assets.', 'rey-core' ),
				'class' => 'qm-refresh-assets qm-refresher',
			];
		}

		return $nodes;
	}

	/**
	 * Refresh Assets through Ajax
	 *
	 * @since 2.0.0
	 **/
	public function adminbar__clear_assets()
	{
		if( $this->clear_assets() ){
			wp_send_json_success();
		}

		wp_send_json_error();
	}

	private function clear_assets(){

		if( ! current_user_can('administrator') ){
			self::log( 'Incorrect permissions for clearing assets!' );
			return;
		}

		if( ! self::$fs ){
			self::log( 'Filesystem missing for clearing assets!' );
			return;
		}

		$assets_settings = self::$assets_manager->get_settings();

		if( ! ($assets_settings['save_css'] || $assets_settings['save_js']) ){
			return;
		}

		$cleared = [];

		if( is_multisite() ){
			foreach ( get_sites() as $blog ) {
				switch_to_blog( $blog->blog_id );

					$dir_path = self::get__base_uploads__dir();

					if ( self::$fs->rmdir( $dir_path, true ) ) {
						self::$fs->mkdir( $dir_path );
						$cleared[] = true;
					}

				restore_current_blog();
			}
		}
		else {
			if ( self::$fs->rmdir( self::$dir_path, true ) ) {
				self::$fs->mkdir( self::$dir_path );
				$cleared[] = true;
			}
		}

		$status = in_array(true, $cleared, true);

		if( ! $status ){
			self::log( 'Assets not deleted!' );
		}
		else {
			self::log( 'Assets successfully cleaned-up!' );
		}

		do_action('reycore/assets/cleanup', $status);

		return true;
	}

	public function clear__basic() {
		$this->clear_assets();
	}

	public function handle_clear_data(){
		if( isset($_REQUEST['clear_assets']) && absint($_REQUEST['clear_assets']) === 1 ){
			$this->clear_assets();
		}
	}

}
