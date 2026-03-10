<?php
namespace ReyCore\Compatibility\Perfmatters;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{

	public function __construct()
	{
		add_filter( 'perfmatters_delayed_scripts', [$this, 'force_include_rey_script']);
		add_filter( 'perfmatters_delay_js_exclusions', [$this, 'exclude_js']);
		add_filter( 'perfmatters_delay_js_exclusions', [ $this, 'exclude_delay_js_force_rey_scripts' ], 1000 );
		add_filter( 'rey/main_script_params', [ $this, 'set_delay_event' ] );
		add_filter( 'theme_mod_site_preloader', [ $this, 'disable_site_preloader' ] );
	}

	public function is_javascript_delayed(){

		if( !($options = get_option('perfmatters_options')) ){
			return;
		}

		/**
		 * Delay JS is skipped on Checkout & Cart pages.
		 */
		if( class_exists('\WooCommerce') && (is_checkout() || is_cart()) ){
			return;
		}

		if ( ! (isset($options['assets']['delay_js']) && $options['assets']['delay_js']) ) {
			return;
		}

		return isset($options['assets']['delay_js_behavior']) && ! empty($options['assets']['delay_js_behavior']);
	}

	public function set_delay_event($params){

		if ( ! $this->is_javascript_delayed() ) {
			return $params;
		}

		$params['delay_final_js_event'] = 'perfmatters-load';
		$params['delay_js_dom_event'] = 'perfmatters-DOMContentLoaded';
		return $params;
	}

	function disable_site_preloader($mod){

		if ( $this->is_javascript_delayed() ) {
			return false;
		}

		return $mod;
	}

	public function exclude_js($scripts){
		return array_merge($scripts, reycore__js_delayed_exclusions(), [
			// 'rey-script',
		]);
	}

	public function exclude_delay_js_force_rey_scripts( $scripts ) {

		$default_excludes = reycore__js_delayed_exclusions();

		foreach ($scripts as $script) {
			// don't search in default excludes
			if( in_array($script, $default_excludes, true) ){
				continue;
			}
			// if any rey script is added
			// it must force rey-script to load
			if( strpos($script, 'rey') === 0 || strpos($script, '/rey') !== false ){
				$scripts[] = 'rey-script';
			}
		}
		return $scripts;
	}

	/**
	 * Force include rey.js because it's essential
	 *
	 * @param array $scripts
	 * @return array
	 */
	public function force_include_rey_script($scripts){

		$includes[] = 'rey.js';
		$includes[] = 'rey-script';

		foreach ($includes as $exclude) {
			if(($key = array_search($exclude, $scripts)) !== false) {
				unset($scripts[$key]);
			}
		}

		return $scripts;
	}


}
