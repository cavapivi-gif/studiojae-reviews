<?php
namespace ReyCore\Compatibility\Litespeed;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{

	const EVENT = 'DOMContentLiteSpeedLoaded';

	public function __construct()
	{
		add_filter( 'rey/main_script_params', [ $this, 'set_delay_event' ] );
		add_filter( 'theme_mod_site_preloader', [ $this, 'disable_site_preloader' ] );
		add_filter( 'reycore/assets/settings', [ $this, 'disable_defer_js' ] );
		add_filter( 'litespeed_optm_js_defer_exc', [ $this, 'exclude_delay_js' ] );
		add_filter( 'litespeed_optm_js_defer_exc', [ $this, 'exclude_delay_js_force_rey_scripts' ], 1000 );
	}

	public function is_javascript_delayed(){
		if( ! class_exists('\LiteSpeed\Conf') ){
			return false;
		}
		return \LiteSpeed\Conf::cls()->conf( \LiteSpeed\Base::O_OPTM_JS_DEFER ) == 2 && ! is_user_logged_in();
	}

	public function set_delay_event($params){

		if ( $this->is_javascript_delayed() ) {
			$params['delay_forced_js_event'] = 'DOMContentLiteSpeedLoaded';
			$params['delay_final_js_event'] = 'DOMContentLiteSpeedLoaded';
			$params['delay_js_dom_event'] = 'DOMContentLiteSpeedLoaded';
		}

		return $params;
	}

	function disable_site_preloader($mod){

		if ( $this->is_javascript_delayed() ) {
			return false;
		}

		return $mod;
	}

	public function disable_defer_js($settings){

		if ( $this->is_javascript_delayed() ) {
			$settings['defer_js'] = false;
			$settings['save_js'] = false;
		}

		return $settings;
	}

	/**
	 * Exclude scripts from delaying/deferring .
	 * By default everything is excluded.
	 * If any rey script needs to be excluded, you must include `rey.js`.
	 *
	 * @param array $scripts
	 * @return array
	 */
	function exclude_delay_js( $scripts ) {
		return array_merge($scripts, reycore__js_delayed_exclusions(), [
			// 'rey.js',
		]);
	}

	function exclude_delay_js_force_rey_scripts( $scripts ) {

		$default_excludes = reycore__js_delayed_exclusions();

		foreach ($scripts as $script) {
			// don't search in default excludes
			if( in_array($script, $default_excludes, true) ){
				continue;
			}
			// if any rey script is added
			// it must force rey-script to load
			if( strpos($script, 'rey') === 0 || strpos($script, '/rey') !== false ){
				$scripts[] = 'rey.js';
			}
		}
		return $scripts;
	}
}
