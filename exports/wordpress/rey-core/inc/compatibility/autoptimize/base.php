<?php
namespace ReyCore\Compatibility\Autoptimize;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Compatibility\CompatibilityBase {

	public function __construct() {
		add_filter('rey/main_script_params', [$this, 'filter_main_script_params']);
		add_filter('autoptimize_filter_css_exclude', [$this, 'skip_critical_css']);
		// add_filter('body_class', [$this, 'body_class']);
	}

	function skip_critical_css($css){

		if( is_array($css) ){
			$css[] = 'reycore-critical-css';
		}
		else {
			$css .= ', reycore-critical-css';
		}

		return $css;
	}

	public function is_lazy_load_enabled(){
		// check settings option
		return ($autoptimize_img_settings = get_option('autoptimize_imgopt_settings', [])) &&
			// check if lazy load is enabled
			isset($autoptimize_img_settings['autoptimize_imgopt_checkbox_field_3']) && $autoptimize_img_settings['autoptimize_imgopt_checkbox_field_3'] === '1';
	}

	public function filter_main_script_params($params)
	{
		if( $this->is_lazy_load_enabled() ) {
			$params['lazy_load'] = true;
		}

		return $params;
	}

	public function body_class($classes)
	{
		if( $this->is_lazy_load_enabled() ) {
			// $classes[] = '--lazyload-enabled';
		}

		return $classes;
	}

}
