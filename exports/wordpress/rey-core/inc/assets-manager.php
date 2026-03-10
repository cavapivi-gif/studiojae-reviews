<?php
namespace ReyCore;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

final class AssetsManager
{

	public static $assets_handler;

	public $debug = false;
	public $settings = [];

	/**
	 * All registered styles and scripts.
	 */
	private $registered_styles = [];
	private $registered_scripts = [];

	private static $styles_to_remove = [];
	private static $scripts_to_remove = [
		'wp-util'
	];

	private static $deferred_page_scripts = [];
	private static $deferred_page_styles = [];
	private static $deferred_page_styles__late = [];
	private static $deferred_styles = [];

	private static $__sorted_styles;

	private static $excluded_styles;
	private static $excluded_scripts;

	/**
	 * Styles and scripts that has been added throughout the page load
	 */
	private $styles = [];
	private $scripts = [];

	private $collected_scripts = [];
	private $collected_styles = [];

	/**
	 * Should cache separately for mobiles.
	 * Causes issues invalidating cache, and regenerates data.
	 */
	public $mobile = false;

	/**
	 * Html attribute used for the lazy stylesheets
	 */
	const LAZY_ATTRIBUTE = 'data-lazy-stylesheet';

	/**
	 * Name of lazy style
	 *
	 * @var string
	 */
	const NAME_LAZY = 'ds';

	/**
	 * Name of top priority style
	 *
	 * @var string
	 */
	const NAME_HEAD = 'hs';

	/**
	 * Determines if the placeholder is added
	 *
	 * @var string
	 */
	private static $__placeholder;

	/**
	 * Head stylesheet placeholder
	 */
	const HEAD_PLACEHOLDER = '<!-- REY_CSS -->';

	const CHILD_THEME_SLUG = 'rey-wp-style-child';

	/**
	 * Actuall only rey-script (rey.js) is mandatory,
	 * however RC scrpt also contains various important scripts.
	 *
	 * @var array
	 */
	public static $mandatory_scripts = ['rey-script', 'reycore-script'];

	public function __construct()
	{
		add_action( 'init', [$this, 'init']);
		add_action( 'admin_enqueue_scripts', [ $this, 'register_assets' ], 5 );
		add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ], 5 );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_mandatory']);
		add_filter( 'style_loader_tag', [$this, 'style_loader_tag'], 10, 2);
		add_filter( 'script_loader_tag', [$this, 'defer_script_tags'], 10, 2);
		add_filter( 'rey/main_script_params', [$this, 'script_params']);
		add_action( 'wp_footer', [$this, 'enqueue_footer'], 15 );
		add_action( 'wp_head', [$this, 'add_head_stylesheet_placeholder'], 100 ); // 100, before `wp_custom_css_cb`

		new BufferManager();

		self::$assets_handler  = new AssetsHandler($this);

	}

	public function init(){

		$this->settings = [
			'disable_assets'  => false, // will disable Rey's styles and scripts
			'save_css'        => REY_CORE_ASSETS_CSS, // combines and minifies CSS
			'css_combine'     => REY_CORE_ASSETS_CSS_COMBINE, // combines and minifies CSS
			'css_head'        => REY_CORE_ASSETS_CSS_HEAD, // 'block', 'inline'
			'css_delay_mode'  => REY_CORE_ASSETS_CSS_DELAY_MODE, // 'interaction', 'defer', 'defer_late'
			'save_js'         => REY_CORE_ASSETS_JS, // combines and minifies JS
			'defer_js'        => true,
			'delay_js'        => false,
			'mobile'          => false, // should behave differently in mobile (Work in progress),
			'mid_position'    => self::NAME_LAZY,
			'deferred_styles' => [
				'child_css' => self::CHILD_THEME_SLUG, // should be deferred if no caching plugin installed
				'cf7' => 'contact-form-7',
				'classic_theme_styles' => 'classic-theme-styles'
			],
			'deferred_scripts' => [
				'mc4wp' => 'mc4wp-forms-api',
			],
		];

		// disable in debug mode or if theme/core unsynced
		if( (defined('REY_OUTDATED') && REY_OUTDATED) || is_404() || strpos($_SERVER['REQUEST_URI'], 'wc-api') !== false || $this->settings['disable_assets'] ){
			$this->settings['save_css'] = false;
			$this->settings['save_js'] = false;
		}

		if( reycore__js_is_delayed() || is_customize_preview() ){
			$this->settings['save_js'] = false;
		}

		// if late deferring, mid-prioritized styles
		// must go to head
		if( $this->settings['save_css'] && 'defer_late' === $this->settings['css_delay_mode'] ){
			$this->settings['mid_position'] = self::NAME_HEAD;
		}

		$this->settings = apply_filters('reycore/assets/settings', $this->settings, $this);

		$this->mobile = $this->settings['mobile'] && reycore__is_mobile();

		self::$deferred_page_styles = $this->settings['deferred_styles'];

		$this->set_combine_excludes();
		$this->set_excluded_styles();
		$this->set_excluded_scripts();

	}

	public function get_settings($setting = ''){

		if( isset($this->settings[$setting]) ){
			return $this->settings[$setting];
		}

		return $this->settings;
	}

	public function set_settings($settings, $value = ''){

		if( is_array($settings) ){
			foreach ($settings as $setting => $value) {
				if( isset($this->settings[$setting]) ){
					$this->settings[$setting] = $value;
				}
			}
		}
		else {
			if( isset($this->settings[$settings]) ){
				$this->settings[$settings] = $value;
			}
		}

	}

	public function is_edit_mode(){

		static $__edit_mode;

		if( is_null($__edit_mode) ){
			$__edit_mode = reycore__elementor_edit_mode();
		}

		return $__edit_mode;
	}

	/**
	 * Register an asset
	 *
	 * @param string $type
	 * @param array $assets
	 * @return void
	 */
	public function register_asset( $type, $assets ){
		if( $type === 'styles' ){
			$this->registered_styles = array_merge($this->registered_styles, (array) $assets);
		}
		elseif( $type === 'scripts' ){
			$this->registered_scripts = array_merge($this->registered_scripts, (array) $assets);
		}
	}

	/**
	 * Register an asset
	 *
	 * @param string $type
	 * @param array $assets
	 * @return void
	 */
	public function update_asset( $type, $handle, $params = [] ){
		if( $type === 'styles' && isset($this->registered_styles[$handle]) ){
			foreach ($params as $key => $value) {
				if( isset($this->registered_styles[$handle][$key]) ){
					$this->registered_styles[$handle][$key] = $value;
				}
			}
		}
		else if( $type === 'scripts' && isset($this->registered_scripts[$handle]) ){
			foreach ($params as $key => $value) {
				if( isset($this->registered_scripts[$handle][$key]) ){
					$this->registered_scripts[$handle][$key] = $value;
				}
			}
		}
	}

	/**
	 * De-Register an asset
	 *
	 * @param string $type
	 * @param array $assets
	 * @return void
	 */
	public function deregister_asset( $type, $assets ){
		if( $type === 'styles' ){
			foreach ((array) $assets as $asset) {
				unset($this->registered_styles[$asset]);
			}
		}
		elseif( $type === 'scripts' ){
			foreach ((array) $assets as $asset) {
				unset($this->registered_scripts[$asset]);
			}
		}
	}

	/**
	 * Register and collect assets
	 *
	 * @return void
	 */
	public function register_assets(){

		/**
		 * Hook to register Rey styles.
		 * @since 2.0.0
		 */
		do_action('reycore/assets/register_scripts', $this);

		$is_admin = is_admin() && ! wp_doing_ajax();

		foreach( $this->registered_styles as $handle => $style ){
			if( $is_admin ){
				if( ! (isset($style['admin']) && $style['admin']) ){
					continue;
				}
			}
			if( ! isset($this->registered_styles[$handle]['path']) && strpos($style['src'], REY_CORE_URI) !== false ){
				$this->registered_styles[$handle]['path'] = str_replace(REY_CORE_URI, REY_CORE_DIR, $style['src']);
			}
			wp_register_style($handle, $style['src'], $style['deps'], $style['version']);
		}

		foreach( $this->registered_scripts as $handle => $script ){
			if( $is_admin ){
				if( ! (isset($script['admin']) && $script['admin']) ){
					continue;
				}
			}
			if( isset($script['src']) ){
				if( ! isset($this->registered_scripts[$handle]['path']) && strpos($script['src'], REY_CORE_URI) !== false ){
					$this->registered_scripts[$handle]['path'] = str_replace(REY_CORE_URI, REY_CORE_DIR, $script['src']);
				}
				wp_register_script(
					$handle,
					$script['src'],
					isset($script['deps']) ? $script['deps'] : [],
					isset($script['version']) ? $script['version'] : REY_CORE_VERSION,
					isset($script['in_footer']) ? $script['in_footer'] : true
				);
				if( isset($script['localize']) && is_array($script['localize']['params']) ){
					wp_localize_script($handle, $script['localize']['name'], $script['localize']['params']);
				}
			}
		}

		do_action('qm/debug', sprintf('Registered %s Styles and %s Scripts', count(array_keys($this->registered_styles)), count(array_keys($this->registered_scripts)) ) );

		// error_log(var_export( array_keys($this->registered_styles), true));
		// error_log(var_export( array_keys($this->registered_scripts), true));
	}

	public function get_register_assets( $type = 'styles' ){
		if( 'styles' === $type ){
			return $this->registered_styles;
		}
		else if( 'scripts' === $type ){
			return $this->registered_scripts;
		}
	}

	/**
	 * Add style but deferred. To be enqueued later.
	 *
	 * @param array $handlers
	 * @return void
	 */
	public function add_deferred_styles($handlers){

		if( $this->settings['disable_assets'] ){
			return;
		}

		foreach ((array) $handlers as $handler) {

			if( in_array($handler, self::$styles_to_remove, true) ){
				continue;
			}

			if( ! $this->style_is($handler) ){
				continue;
			}

			// maybe it was requested before, so don't defer it
			if( ! in_array($handler, $this->styles, true) ){
				self::$deferred_styles[ $handler ] = $this->registered_styles[ $handler ];
			}

			$this->styles[] = $handler;

			if( ! $this->settings['save_css'] ){
				wp_enqueue_style($handler);
			}
		}
	}

	/**
	 * Enqueue and add styles to enqueue collection
	 *
	 * @param array $handlers
	 * @return void
	 */
	public function add_styles( $handlers ){

		if( $this->settings['disable_assets'] ){
			return;
		}

		$is_admin = is_admin() && ! wp_doing_ajax();

		foreach ((array) $handlers as $key => $handler) {

			if( in_array($handler, self::$styles_to_remove, true) ){
				continue;
			}

			// already enqueued
			if( in_array($handler, $this->styles, true) ){
				if(
					// don't stop in case it's an ajax filter / pagination, because `$this->styles`
					// holds several styles which are not loaded during collection.
					! ( isset($_REQUEST['reynotemplate']) && absint( $_REQUEST['reynotemplate'] ) === 1)
				){
					continue;
				}
			}

			// add dependencies as styles
			if($deps = $this->style_is($handler, 'deps')){
				foreach ( (array) $deps as $dep_handler) {

					// not a rey style
					if( ! $this->style_is($dep_handler) ){
						continue;
					}

					// include in low priority styles
					if( $this->is_low_priority($dep_handler) ){
						self::$deferred_styles[ $dep_handler ] = $this->registered_styles[$dep_handler];
					}

					$this->styles[] = $dep_handler;

					if( $is_admin ){
						wp_enqueue_style($dep_handler);
					}
				}
			}

			if( $is_admin ){
				wp_enqueue_style($handler);
				continue;
			}

			// debug
			// if( in_array($handler, [ '' ]) ){
			// 	error_log(var_export( wp_debug_backtrace_summary(), true));
			// }

			foreach ($this->collected_styles as $key => $value) {
				$this->collected_styles[$key][] = $handler;
			}

			// non Rey styles, check if already enqueued
			if( ! $this->style_is($handler) ){
				if( wp_style_is($handler, 'enqueued') ){
					continue;
				}
			}

			if( $this->is_low_priority($handler) ){
				self::$deferred_styles[ $handler ] = $this->registered_styles[$handler];
			}

			$this->styles[] = $handler;

			if( ! $this->settings['save_css'] ){
				wp_enqueue_style($handler);
			}
		}
	}

	public function is_low_priority($handler){
		return 'low' === $this->style_is($handler, 'priority');
	}

	public function remove_styles($handlers){
		foreach ((array) $handlers as $handler) {
			self::$styles_to_remove[$handler] = $handler;
		}
	}

	public function remove_scripts($handlers){
		foreach ((array) $handlers as $handler) {
			self::$scripts_to_remove[$handler] = $handler;
		}
	}

	/**
	 * Determine if an asset should be enqueued without demand.
	 *
	 * @param array $asset
	 * @param string $handle
	 * @return bool
	 */
	public function maybe_enqueue_mandatory( $asset, $handle = '' ){

		$enqueue = false;

		// always enqueue
		if( isset($asset['enqueue']) && $asset['enqueue'] ){

			$enqueue = true;

			if( in_array($handle, \ReyCore\Assets::get_excludes(), true) ){
				$enqueue = false;
			}

		}

		else {
			// check callback
			if( isset($asset['callback']) ){
				if( is_callable($asset['callback']) && call_user_func($asset['callback']) ){
					$enqueue = true;
				}
			}
		}

		return $enqueue;
	}

	/**
	 * Enqueue Mandatory scripts and styles
	 *
	 * @return void
	 */
	public function enqueue_mandatory(){

		if( $this->settings['disable_assets'] ){
			return;
		}

		do_action('reycore/assets/enqueue', $this);

		// Just load everything in elementor mode
		if( $this->is_edit_mode() ){
			foreach (array_keys($this->registered_styles) as $style) {
				wp_enqueue_style($style);
			}
			return;
		}

		foreach( $this->registered_styles as $handle => $style ){

			if( in_array($handle, self::$styles_to_remove, true) ){
				continue;
			}

			if( $this->maybe_enqueue_mandatory( $style, $handle ) ){
				$this->add_styles($handle);
			}
		}

		foreach( $this->registered_scripts as $handle => $script ){

			if( in_array($handle, self::$scripts_to_remove, true) ){
				continue;
			}

			if( $this->maybe_enqueue_mandatory( $script, $handle ) ){
				$this->add_scripts($handle);
			}
		}

	}

	/**
	 * Make a page stylesheet load deferred
	 * Used for late GSs.
	 *
	 * @param array $handlers
	 * @return void
	 */
	public function defer_page_styles($handlers, $late = false)
	{
		foreach ( (array) $handlers as $handle) {
			self::$deferred_page_styles[$handle] = $handle;
			if( $late ){
				self::$deferred_page_styles__late[$handle] = $handle;
			}
		}
	}

	/**
	 * Unload a deferred stylesheet
	 *
	 * @param string $handle
	 * @return void
	 */
	public function undefer_page_styles($handle)
	{
		unset(self::$deferred_page_styles[$handle]);
		unset(self::$deferred_page_styles__late[$handle]);
	}

	/**
	 * Make a page stylesheet load deferred
	 * Used for late GSs.
	 *
	 * @param array $handlers
	 * @return void
	 */
	public function defer_page_scripts($handlers)
	{
		foreach ( (array) $handlers as $handle) {
			self::$deferred_page_scripts[$handle] = $handle;
		}
	}

	public static function get_defer_method( $early = true ){
		if( $early ){
			return ' rel="preload" as="style" onload="this.onload=null;this.rel=\'stylesheet\';" media="all" ';
		}
		return ' rel="stylesheet" onload="this.onload=null;this.media=\'all\';" media="print" ';
	}

	public function replace_style_attribute($tag, $handle = ''){

		$no_opt = reycore__css_no_opt_attr();

		if( $handle === self::CHILD_THEME_SLUG ){
			$no_opt = '';
		}

		// defer the stylesheet
		if( in_array($this->settings['css_delay_mode'], ['defer', 'defer_late'], true) ){

			$tag = str_replace( ' media=', ' data-media=', $tag);
			$no_script_tag = sprintf('<noscript>%s</noscript>', str_replace( [' id=', ' type=', self::HEAD_PLACEHOLDER], [ ' ' . $no_opt . ' data-id=', ' data-type=', ''], $tag));

			$search_for = ' rel=';
			$replace_with = implode(' ', [
				self::get_defer_method( ! in_array($handle, self::$deferred_page_styles__late, true) ),
				$no_opt,
				'data-no-rel=',
			]);

			return str_replace( $search_for, $replace_with, $tag) . $no_script_tag;
		}

		// set a lazy attribute
		return str_replace(' href=', sprintf(' %s %s=', $no_opt, self::LAZY_ATTRIBUTE), $tag);

	}

	/**
	 * Override style tag output
	 *
	 * @param string $tag
	 * @param string $handle
	 * @return string
	 */
	public function style_loader_tag($tag, $handle){

		if( ! $this->can_run_loader_tag() ){
			return $tag;
		}

		if( self::$excluded_styles ){
			if(
				in_array($handle, self::$excluded_styles, true)
				|| (($words = implode('|', self::$excluded_styles)) && preg_match("~\b({$words})\b~i", $tag, $tag_matches))
			){
				if( isset($tag_matches) && ! empty($tag_matches) ){
					unset(self::$excluded_styles[$tag_matches[0]]);
				}
				unset(self::$excluded_styles[$handle]);
				return '';
			}
		}

		if( $this->can_add_placeholder() && 'elementor-frontend' === $handle ){
			$this->added_placeholder();
			$tag .= self::HEAD_PLACEHOLDER;
		}

		// Remove woocommerce general request. It's empty.
		// Just need the handle as dependency target
		if( in_array($handle, ['woocommerce-general'], true) ){
			return '';
		}

		if( in_array($handle, self::$styles_to_remove, true) ){
			return '';
		}

		if(
			// must have lazy enabled
			in_array($this->settings['css_delay_mode'], ['defer', 'defer_late'], true)
			// it's a low priority stylesheet
			&& isset( self::$deferred_styles[$handle] )
			// purposely deferring a stylesheet (not necesarily Rey)
			|| in_array($handle, self::$deferred_page_styles, true)
		){
			return $this->replace_style_attribute($tag, $handle);
		}

		return $tag;
	}

	/**
	 * Sort the css stylesheets by priorities
	 *
	 * @param array $data
	 * @return array
	 */
	public function sort_css_priorities($data){

		$high = $mid = $low = $others = [];

		foreach($data as $key => $handle){

			if( ! isset( $this->registered_styles[ $handle ] ) ){
				continue;
			}

			$style = $this->registered_styles[$handle];

			if ( isset(self::$deferred_styles[$handle]) ) {
				$low[] = $handle;
			}

			else if( isset($style['priority']) )
			{
				switch($style['priority']):
					case"high":
						$high[] = $handle;
					break;
					case"mid":
						$mid[] = $handle;
					break;
					case"low":
						$low[] = $handle;
					break;
				endswitch;
			}
			// whatever this may be
			else{
				$others[] = $handle;
			}
		}

		if( self::NAME_LAZY === $this->settings['mid_position'] ){
			$low = array_merge($low, $mid);
		}

		else if( self::NAME_HEAD === $this->settings['mid_position'] ){
			$high = array_merge($high, $mid);
		}

		return [
			self::NAME_HEAD => array_merge($high, $others),
			self::NAME_LAZY => $low,
		];
	}

	/**
	 * Get the page styles. Used for collecting enqueued styles.
	 *
	 * @return array
	 */
	public function get_styles(){

		if( self::$__sorted_styles ){
			return self::$__sorted_styles;
		}

		self::$__sorted_styles = $this->sort_css_priorities( array_unique($this->styles) );

		return self::$__sorted_styles;
	}

	/**
	 * Get the deferred page styles.
	 *
	 * @return array
	 */
	public static function get_deferred_styles(){
		return array_unique(array_keys(self::$deferred_styles));
	}

	/**
	 * Create an object with the styles which have been added to the page.
	 *
	 * @param array $styles
	 * @return void
	 */
	public function output_inserted_styles($styles = []){

		if( empty($styles) ){
			$styles = $this->get_styles();
		}

		printf("<script type='text/javascript' id='reystyles-loaded'>\n window.reyStyles=%s; \n</script>", wp_json_encode(array_values($styles)));
	}

	/**
	 * Retrive scripts that have been requested in the page
	 *
	 * @return array
	 */
	public function get_scripts(){
		return array_unique($this->scripts);
	}

	/**
	 * Retrive scripts that have been requested in the page
	 *
	 * @return array
	 */
	public function get_all_scripts(){
		if( empty($this->get_scripts()) ){
			return [];
		}
		return array_merge(self::$mandatory_scripts, $this->get_scripts());
	}

	/**
	 * Add script to collection of to be enqueued
	 *
	 * @param array $handlers
	 * @return void
	 */
	public function add_scripts( $handlers ){

		if( $this->settings['disable_assets'] ){
			return;
		}

		$is_admin = is_admin() && ! wp_doing_ajax();

		foreach ((array) $handlers as $key => $handler) {

			if( in_array($handler, self::$scripts_to_remove, true) ){
				continue;
			}

			$collectable_handlers = [];

			// add dependencies as scripts
			if( $deps = $this->script_is($handler, 'deps') ){

				foreach ( (array) $deps as $dep_handler)
				{
					// not a rey script (eg jQuery)
					if( ! $this->script_is($dep_handler) ){
						// but make sure it's loaded
						wp_enqueue_script($dep_handler);
						continue;
					}

					// it's an external script, so should just load
					if( $this->script_is($dep_handler, 'external') ){
						wp_enqueue_script($dep_handler);
						continue;
					}

					// make sure it's collected
					$collectable_handlers[] = $dep_handler;

					if( $is_admin ){
						wp_enqueue_script($dep_handler);
					}

					// It's a JS plugin script.
					// To play well with other wp extensions if they use these librraries,
					// they should load outside of the bundle
					if( $this->settings['save_js'] && $this->script_is($dep_handler, 'plugin') ){
						wp_enqueue_script($dep_handler);
						continue;
					}

					$this->scripts[] = $dep_handler;
				}
			}

			if( $is_admin ){
				wp_enqueue_script($handler);
				continue;
			}

			$collectable_handlers[] = $handler;

			foreach ($this->collected_scripts as $key => $value) {
				foreach ($collectable_handlers as $item) {
					$this->collected_scripts[$key][] = $item;
				}
			}

			// It's a JS plugin script.
			// To play well with other wp extensions if they use these librraries,
			// they should load outside of the bundle
			if( $this->settings['save_js'] && $this->script_is($handler, 'plugin') ){
				wp_enqueue_script($handler);
				continue;
			}

			$this->scripts[] = $handler;

		}
	}

	public function script_is($handler, $type = ''){
		if( ! $type ){
			return isset($this->registered_scripts[$handler]);
		}
		return isset($this->registered_scripts[$handler]) && isset($this->registered_scripts[$handler][$type]) ? $this->registered_scripts[$handler][$type] : '';
	}

	public function style_is($handler, $type = ''){
		if( ! $type ){
			return isset($this->registered_styles[$handler]);
		}
		return isset($this->registered_styles[$handler]) && isset($this->registered_styles[$handler][$type]) ? $this->registered_styles[$handler][$type] : '';
	}

	/**
	 * Run late footer scripts
	 *
	 * @return void
	 */
	public function enqueue_footer(){

		if( $this->settings['disable_assets'] ){
			return;
		}

		do_action('reycore/assets/enqueue_footer', $this);

		$this->output_inserted_styles();

		$this->enqueue_js();

		do_action('qm/debug', sprintf('Enqueued %s Styles and %s Scripts', count(array_unique($this->styles)), count($this->get_all_scripts()) ));

	}

	/**
	 * Render a JS global object containing all scripts that have been added to the page
	 *
	 * @param array $scripts
	 * @return void
	 */
	public function output_inserted_scripts($scripts){
		printf("<script type='text/javascript' id='reyscripts-loaded'>\n window.reyScripts=%s; \n</script>", wp_json_encode(array_values($scripts)));
	}

	/**
	 * Enqueue JS scripts
	 *
	 * @return void
	 */
	public function enqueue_js(){

		if( $this->is_edit_mode() ){
			if( $registered_scripts = array_keys($this->registered_scripts) ){
				foreach (array_merge($registered_scripts, self::$mandatory_scripts) as $script) {
					wp_enqueue_script($script);
				}
			}
			return;
		}

		if( ! ($scripts = $this->get_scripts()) ){
			return;
		}

		$scripts = array_merge(self::$mandatory_scripts, $scripts);

		$this->output_inserted_scripts($scripts);

		// error_log(var_export( $scripts, true));

		// when bundle is enabled
		// scripts having localized data need to be added
		// but cleaned up afterwards
		if( $this->settings['save_js'] ){
			global $wp_scripts;
			foreach ($scripts as $handle) {

				if( 'rey-script' === $handle && defined('REY_SCRIPT_LOCALIZED') && REY_SCRIPT_LOCALIZED && is_user_logged_in() && ! is_admin() ){
					continue;
				}

				if( $script_data = $wp_scripts->get_data( $handle, 'data' ) ){
					printf('<script type="text/javascript" id="%1$s-js-extra" %3$s>%2$s</script>', $handle, $script_data, reycore__js_no_opt_attr()) . "\n";
				}

				$this->load_icons($handle);
			}
		}
		// just enqueue scripts
		else {
			foreach ($scripts as $script) {
				wp_enqueue_script($script);
				$this->load_icons($script);
			}
		}

	}

	public function load_icons( $handle ){
		if( isset($this->registered_scripts[$handle]['icons']) ){
			\ReyCore\Plugin::instance()->js_icons->include_icons( (array) $this->registered_scripts[$handle]['icons'] );
		}
	}

	public function can_run_loader_tag(){

		if( $this->settings['disable_assets'] ){
			return false;
		}

		static $wont_run;

		if( is_null($wont_run) ){
			$wont_run = is_admin() || $this->is_edit_mode();
		}

		return ! $wont_run;
	}

	/**
	 * Filter scripts output to append a defer tag
	 *
	 * @param string $tag
	 * @param string $handle
	 * @return string
	 */
	public function defer_script_tags($tag, $handle){

		if( $this->settings['disable_assets'] ){
			return $tag;
		}

		if( ! $this->can_run_loader_tag() ){
			return $tag;
		}

		if( self::$excluded_scripts ){
			if(
				in_array($handle, self::$excluded_scripts, true)
				|| (($words = implode('|', self::$excluded_scripts)) && preg_match("~\b({$words})\b~i", $tag, $tag_matches))
			){
				if( isset($tag_matches) && ! empty($tag_matches) ){
					unset(self::$excluded_scripts[$tag_matches[0]]);
				}
				unset(self::$excluded_scripts[$handle]);
				return '';
			}
		}

		if( in_array($handle, self::$scripts_to_remove, true) ){
			return $tag;
		}

		// Stop if no deferring
		if( ! $this->settings['defer_js'] ){
			return $tag;
		}

		// already deferred
		if( strpos($tag, ' defer') !== false ){
			return $tag;
		}

		if(
			// pre-defined deferred scripts list
			in_array($handle, $this->settings['deferred_scripts'], true)
			// manually deferred page scripts
			|| in_array($handle, self::$deferred_page_scripts, true)
			// Specified scripts
			|| $this->script_is($handle, 'defer')
			// Rey JS plugins
			|| $this->script_is($handle, 'plugin')
		){
			return str_replace( ' src', ' defer src', $tag );
		}

		// no need to continue going through all scripts
		// if bundle is enabled.
		if( $this->settings['save_js'] ){

			// Rey Script already loaded in bundle
			if( 'rey-script' === $handle
				&& ! is_admin()
				&& current_user_can('administrator')
				&& ! is_customize_preview()
				&& ! $this->is_edit_mode()
			){
				return '';
			}

			return $tag;
		}

		if( is_admin() || isset($_REQUEST['elementor-preview']) && ! empty($_REQUEST['elementor-preview']) ){
			return $tag;
		}

		if( in_array($handle, $this->get_all_scripts(), true) ){
			return str_replace( ' src', ' defer src', $tag );
		}

		return $tag;
	}

	/**
	 * Adds a placeholder in the HEAD tag
	 * to be replaced by the main stylesheet's tag
	 *
	 * @return void
	 */
	public function add_head_stylesheet_placeholder()
	{
		if( ! $this->can_run_loader_tag() ){
			return;
		}
		if( $this->can_add_placeholder() ){
			echo self::HEAD_PLACEHOLDER;
			$this->added_placeholder();
		}
	}

	public function added_placeholder(){
		self::$__placeholder = true;
	}

	public function can_add_placeholder(){
		return is_null(self::$__placeholder);
	}


	/**
	 * Retrieve RTL stylesheet suffix
	 *
	 * @return string
	 */
	public static function rtl(){
		return is_rtl() ? '-rtl' : '';
	}

	public function collect_start( $name ){
		$this->collected_styles[$name] = [];
		$this->collected_scripts[$name] = [];
	}

	public function collect_end($name, $src = false, $type = '' ){

		$collected = [
			'scripts' => [],
			'styles' => [],
		];

		if( isset($this->collected_scripts[$name]) && ! empty($this->collected_scripts[$name]) ){
			$collected['scripts'] = array_unique( $this->collected_scripts[$name] );
		}
		if( isset($this->collected_styles[$name]) && ! empty($this->collected_styles[$name]) ){
			$collected['styles'] = array_unique( $this->collected_styles[$name] );
		}

		do_action('qm/debug', sprintf('Collected for `%s` %s Styles and %s Scripts', $name, count($collected['styles']), count($collected['scripts']) ) );

		unset($this->collected_styles[$name]);
		unset($this->collected_scripts[$name]);

		if( $src ){
			return [
				'scripts' => $this->get_assets_uri($collected, 'scripts'),
				'styles' => $this->get_assets_uri($collected, 'styles'),
			];
		}

		if( $type && isset($collected[$type]) ){
			return $collected[$type];
		}

		return $collected;
	}

	public function downgrade_styles_priority($name){
		if( ! isset($this->collected_styles[$name]) ){
			return;
		}
		foreach ( $this->collect_end($name, false, 'styles') as $handler) {
			self::$deferred_styles[$handler] = $handler;
		}
	}

	public function get_assets_uri( $assets, $type = 'styles' ){

		$assets_to_return = [];

		$single_asset = ! isset( $assets['styles'] ) && ! isset( $assets['scripts'] );

		if( ! isset($assets[$type]) && ! $single_asset ){
			return $assets_to_return;
		}

		$wp_assets = $type === 'styles' ? wp_styles() : wp_scripts();

		$the_assets = $single_asset ? (array) $assets : $assets[$type];

		foreach ($the_assets as $key => $handler) {

			if( ! (isset($wp_assets->registered[ $handler ]) && ($script = $wp_assets->registered[ $handler ])) ){
				continue;
			}
			if( ! (isset($script->src) && ($src = $script->src)) ){
				continue;
			}

			$site_url = get_site_url();

			if ( 0 === strpos( $src, $site_url ) || 0 === strpos( $src, 'http' ) ) {
				$src_ = $src;
			} else {
				$src_ = $site_url . $src;
			}

			if( $single_asset ){
				$assets_to_return = $src_;
			}
			else {
				$assets_to_return[$handler] = $src_;
			}
		}

		return $assets_to_return;
	}

	public function script_params($params){

		foreach ($this->registered_styles as $handle => $style) {
			if( isset($style['lazy_assets']) && ($selector = $style['lazy_assets']) ){
				// add initial style
				$params['lazy_assets'][$selector]['styles'][$handle] = $style['src'];
				// check dependencies
				if( isset($style['deps']) ){
					foreach ($style['deps'] as $dep) {
						if( isset($this->registered_styles[$dep]) ){
							$params['lazy_assets'][$selector]['styles'][$dep] = $this->registered_styles[$dep]['src'];
						}
					}
				}
			}
		}

		foreach ($this->registered_scripts as $handle => $script) {
			if( isset($script['lazy_assets']) && ($selector = $script['lazy_assets']) ){
				// add initial script
				$params['lazy_assets'][$selector]['scripts'][$handle] = $script['src'];
				// check dependencies
				if( isset($script['deps']) ){
					foreach ($script['deps'] as $dep) {
						if( isset($this->registered_scripts[$dep]) ){
							$params['lazy_assets'][$selector]['scripts'][$dep] = $this->registered_scripts[$dep]['src'];
						}
					}
				}
			}
		}

		return $params;
	}

	public function combine_excludes_filter($val){

		$excludes[] = sprintf('rey-%s-css', self::NAME_LAZY);

		if( 'defer' === $this->settings['css_head'] ){
			$excludes[] = sprintf('rey-%s-css', self::NAME_HEAD);
		}

		unset(self::$deferred_page_styles['child_css']);

		return array_merge($val, $excludes, array_keys(self::$deferred_styles), self::$deferred_page_styles);
	}

	public function set_combine_excludes(){

		if( ! $this->settings['save_css'] ){
			return;
		}

		foreach ([
			'sgo_css_combine_exclude', // Siteground Optimizer
			'wp-optimize-minify-default-exclusions', // WP Optimize
			// 'breeze_filter_css_exclude', // Breeze
		] as $filter_id) {
			add_filter($filter_id, [$this, 'combine_excludes_filter']);
		}
	}

	public function set_excluded_styles(){

		if( is_null(self::$excluded_styles) ){
			self::$excluded_styles = [];
			if( $opt = get_theme_mod('perf__exclude_styles', '') ){
				$split = preg_split("/\r\n|\n|\r/", $opt);
				if( ! empty($split) ){
					self::$excluded_styles = array_combine($split, $split);
				}
			}
		}

		return self::$excluded_styles;
	}

	public function set_excluded_scripts(){

		if( is_null(self::$excluded_scripts) ){
			self::$excluded_scripts = [];
			if( $opt = get_theme_mod('perf__exclude_scripts', '') ){
				$split = preg_split("/\r\n|\n|\r/", $opt);
				if( ! empty($split) ){
					self::$excluded_scripts = array_combine($split, $split);
				}
			}
		}

		return self::$excluded_scripts;
	}
}
