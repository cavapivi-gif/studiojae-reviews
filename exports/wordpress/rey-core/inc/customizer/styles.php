<?php
namespace ReyCore\Customizer;

if ( ! defined( 'ABSPATH' ) ) exit;

class Styles {

	const CSS_OPTION_NAME = 'rey__custom_css_option';

	public function __construct(){

		add_filter( 'rey/allow_enqueue_custom_styles', '__return_false'); // prevent enqueue rey styles, instead to hook into kirki's css

		add_action( 'wp_ajax_refresh_dynamic_css', [$this, 'refresh_dynamic_css_ajax']);
		add_action( 'acf/save_post', [$this, 'theme_settings_save'], 20);
		add_action( 'customize_save_after', [$this, 'regenerate_css'], 20);
		add_action( 'rey/flush_cache_after_updates', [$this, 'regenerate_css'], 20);
		add_action( 'rey/customizer/regenerate_css', [$this, 'regenerate_css'], 20);
		add_action( 'reycore/refresh_all_caches', [$this, 'regenerate_css']);

		if( apply_filters('reycore/inline_styles/position/head', true) ){
			add_action( 'wp_head', [$this, 'print_styles'], 998 );
		}
		else {
			add_action( 'wp_footer', [$this, 'print_styles'], 50 );
		}

		add_action( 'admin_head', [$this, 'admin_print_styles'], 998 );
		add_filter( 'rocket_usedcss_content', [ $this, 'rocket_usedcss_content' ] );

	}

	/**
	 * Minify CSS
	 *
	 * @since 1.6.2
	 */
	public static function get_minified_css(){
		if( function_exists('rey__custom_styles') && ($styles_output = rey__custom_styles()) && is_array($styles_output) ){
			$styles_output = implode(' ', $styles_output);
			$styles_output = str_replace(array("\r\n", "\r", "\n"), '', $styles_output);
			return $styles_output;
		}
	}

	public function get_option_name(){
		return sprintf('%s_%s', self::CSS_OPTION_NAME , reycore__versions_hash() );
	}

	/**
	 * Create the CSS and store it
	 *
	 * @since 1.6.2
	 */
	public function create_dynamic_css( $update_option = true ){

		// Collect styles
		ob_start();
		do_action('reycore/customizer/make_dynamic_css', $this);
		echo self::get_minified_css();
		$styles = ob_get_clean();

		if( $update_option ){
			// cleanup first
			\ReyCore\Helper::clean_db_option( self::CSS_OPTION_NAME );
			// cache CSS
			update_option( self::get_option_name(), $styles );
		}

		self::log( 'Frontend, created CSS. Update Option: ' . ( $update_option ? 'Yes' : 'No') );

		return $styles;
	}

	/**
	 * Print styles, both global and per page.
	 * By default it's placed into `wp_head`, however in some cases
	 * when caching plugins mix the CSS, it might be needed to load these styles
	 * later, in `wp_footer`.
	 *
	 * To do so, please use this css snippet:
	 *    add_filter('reycore/inline_styles/position/head', '__return_false');
	 *
	 * @return void
	 */
	public function print_styles(){
		$this->print_inline_style();
		$this->print_page_inline_style();
	}

	/**
	 * Print styles in block editor, in admin
	 *
	 * @return void
	 */
	public function admin_print_styles(){
		if( reycore__wp_is_block_editor() ){
			$this->print_inline_style();
		}
	}

	/**
	 * Print Inline Styles
	 *
	 * @since 1.5.4
	 **/
	public function print_inline_style()
	{
		$markup = '<style id="reycore-inline-styles" %2$s>%1$s</style>';

		// when in customizer, just print the CSS,
		// but don't store it.
		if( is_customize_preview() ){
			printf($markup, $this->create_dynamic_css(false), reycore__css_no_opt_attr());
			return;
		}

		// check if cache CSS exists
		if( ! ($css = get_option( self::get_option_name() )) ) {

			// regenerate if not
			$css = $this->create_dynamic_css();

			// log
			self::log('Frontend, missing Customizer saved CSS, regenerating.');
		}

		printf($markup, $css, reycore__css_no_opt_attr()) . "\n";
	}

	public function rocket_usedcss_content($css){

		// check if cache CSS exists
		if( ! ($dynamic_css = get_option( self::get_option_name() )) ) {
			// regenerate if not
			$dynamic_css = $this->create_dynamic_css();
		}

		return $dynamic_css . $css;
	}

	/**
	 * Print Inline Styles for pages
	 *
	 * @since 1.5.4
	 **/
	public function print_page_inline_style()
	{
		if( $css = apply_filters('reycore/page_css_styles', []) ) {
			printf('<style id="reycore-page-inline-styles" %2$s>%1$s</style>', implode( '', $css ), reycore__css_no_opt_attr()) . "\n";
		}
	}

	/**
	 * Clearn Custom CSS Option
	 *
	 * @since 1.5.4
	 * @deprecated 2.4.6
	 **/
	public function custom_css_option_delete() {}

	/**
	 * Clearn Custom CSS Option
	 *
	 * @since 1.5.4
	 **/
	public function regenerate_css() {
		return $this->create_dynamic_css();
	}

	/**
	 * Clearn Custom CSS Transients
	 *
	 * @since 1.5.4
	 **/
	public function theme_settings_save( $post_id ) {
		if ( $post_id === REY_CORE_THEME_NAME ) {
			$this->regenerate_css();
		}
	}

	/**
	 * Refresh Dynamic CSS through Ajax
	 *
	 * @since 1.6.0
	 **/
	public function refresh_dynamic_css_ajax()
	{
		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error();
		}

		if( delete_option( self::get_option_name() ) ){
			do_action('reycore/refresh_dynamic_css_ajax');
			wp_send_json_success();
		}

	}

	/**
	 * Log messages to console if debug enabled
	 *
	 * @param string $message
	 * @return void
	 */
	public static function log($message){

		do_action( 'qm/debug', '::Customizer: ' . $message );

		if( defined('REY_DEBUG_LOG_CUSTOMIZER_CSS') && REY_DEBUG_LOG_CUSTOMIZER_CSS ){
			error_log(var_export( '::Customizer: ' . $message , true));
		}
	}
}
