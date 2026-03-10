<?php
namespace ReyCore;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Shortcodes {

	public function __construct(){

		add_shortcode('site_info', [$this, 'site_info']);
		add_shortcode('user_name', [$this, 'username']);
		add_shortcode('current_year', [$this, 'current_year']);
		add_shortcode('inline_image', [$this, 'render_inline_image']);
		add_shortcode('enqueue_asset', [$this, 'enqueue_asset']);

	}

	/**
	 * Display site info through shortcodes.
	 * show = name / email / url
	 *
	 * @since 1.0.0
	 **/
	public function site_info($atts) {

		$content = '';
		if( isset($atts['show']) && $show = $atts['show'] ){
			switch ($show):
				case"name":
					$content = get_bloginfo( 'name' );
					break;
				case"email":
					$content = get_bloginfo( 'admin_email' );
					break;
				case"url":
					$content = sprintf('<a href="%1$s">%1%s</a>', get_bloginfo( 'url' ));
					break;
			endswitch;
		}
		return $content;
	}

	/**
	 * Enqueue a script or style;
	 *
	 * @since 1.9.7
	 **/
	public function enqueue_asset($atts)
	{
		$content = '';

		if( isset($atts['type']) && ($type = $atts['type']) && isset($atts['name']) && ($name = $atts['name']) ){

			if( $type === 'style' ){
				wp_enqueue_style($name);
			}
			else if( $type === 'script' ){
				wp_enqueue_script($name);
			}

		}

		return $content;
	}

	public function username($atts){

		if( ! ($current_user = wp_get_current_user()) ){
			return;
		}
		return $current_user->user_firstname ?? $current_user->user_login;

	}

	public function current_year(){
		return date('Y');
	}

	/**
	 * Render inline image shortcode.
	 *
	 * Example: [inline_image id="123" width="100" height="100" css="border-radius:50%;" x="center" y="center" mobile="yes"]
	 *
	 * @param array $atts
	 * @return string
	 */
	function render_inline_image($atts) {

		// Extract shortcode attributes with defaults
		$atts = shortcode_atts(
			[
				'id'     => '',
				'css'    => '',
				'width'  => '',
				'height' => '',
				'size'   => 'thumbnail',
				'mobile' => 'no',
			],
			$atts,
			'inline_image'
		);

		// Return if no ID is provided
		if (empty($atts['id'])) {
			return 'Image ID is required.';
		}

		$attr = [
			'class' => 'rey-inlineImage'
		];

		if ('no' === $atts['mobile']) {
			$attr['class'] .= ' --dnone-sm';
		}

		$styles = [
			'object-fit: cover;',
			'width: 1em;',
			'height: 1em;',
			'border-radius: 0.2em;',
			'display: inline-block;',
			'vertical-align: middle;'
		];

		if (!empty($atts['width'])) {
			$styles[] = 'width:' . esc_attr($atts['width']) . ';';
		}
		if (!empty($atts['height'])) {
			$styles[] = 'height:' . esc_attr($atts['height']) . ';';
		}
		if ( !empty($atts['x']) || !empty($atts['y']) ) {

			$pos = '';

			if (!empty($atts['x'])) {
				$pos .= esc_attr($atts['x']);
			}

			if (!empty($atts['y'])) {
				$pos .= esc_attr($atts['y']);
			}

			if( $pos ){
				$styles[] = 'object-position:' . $pos . ';';
			}
		}

		$styles[] = esc_attr($atts['css']);

		if( ! empty($styles) ){
			$attr['style'] = implode('', $styles);
		}

		// Use wp_get_attachment_image to fetch and render the image
		return wp_get_attachment_image($atts['id'], 'thumbnail', false, $attr);

	}

}
