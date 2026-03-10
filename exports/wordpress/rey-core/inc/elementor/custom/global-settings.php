<?php
namespace ReyCore\Elementor\Custom;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class GlobalSettings {

	function __construct(){
		add_action( 'elementor/element/global-settings/style/before_section_end', [$this, 'global_settings'], 10);
	}

	/**
	 * Add custom settings into Elementor's Global Settings
	 *
	 * @since 1.0.0
	 */
	function global_settings( $element )
	{
		$element->remove_control( 'elementor_container_width' );
	}
}
