<?php
namespace ReyCore\Libs;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class JsIcons {

	public $icons = [];

	public function __construct(){
		add_action('wp_footer', [$this, 'render_icons'], 20);
	}

	public function include_icons( $icons ){
		foreach ( (array) $icons as $icon ) {
			$this->icons[] = $icon;
		}
	}

	public function render_icons(){

		if( empty($this->icons) ){
			return;
		}

		$output = '';

		foreach ( array_unique($this->icons) as $icon ) {
			$output .= reycore__get_svg_icon([
				'id' => $icon,
				'attributes' => [
					'data-icon-id' => $icon
				]
			]);
		}

		printf('<div style="display:none !important" id="rey-svg-holder">%s</div>', $output);
	}

}
