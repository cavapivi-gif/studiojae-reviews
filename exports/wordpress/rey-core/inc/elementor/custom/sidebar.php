<?php
namespace ReyCore\Elementor\Custom;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Sidebar {

	function __construct(){
		add_action( 'elementor/frontend/widget/before_render', [$this, 'before_render'], 10);
	}

	/**
	 * Add custom settings into Elementor's Global Settings
	 *
	 * @since 1.0.0
	 */
	function before_render( $element )
	{
		if( $element->get_unique_name() !== 'sidebar' ){
			return;
		}

		$settings = $element->get_settings();

		if( ! (isset($settings['sidebar']) && ($sidebar = $settings['sidebar'])) ){
			return;
		}

		$sidebar_class[] = $sidebar;
		$sidebar_class[] = 'widget-area';

		$element->add_render_attribute( '_wrapper', 'class', implode( ' ', array_map( 'sanitize_html_class', apply_filters('rey/content/sidebar_class', $sidebar_class, $sidebar) ) ) );

	}
}
