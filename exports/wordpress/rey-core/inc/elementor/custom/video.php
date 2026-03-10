<?php
namespace ReyCore\Elementor\Custom;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Video {

	function __construct(){

		add_action( 'elementor/element/video/section_video_style/before_section_end', [$this, 'icon_settings'], 10);
		add_action( 'elementor/frontend/widget/before_render', [$this, 'before_render'], 10);

	}

	/**
	* Render some attributes before rendering
	*
	* @since 1.0.0
	**/
	function before_render( $element )
	{

		if( 'video' !== $element->get_unique_name() ){
			return;
		}

		$settings = $element->get_data('settings');

		if( isset($settings['play_icon_icon']) && $settings['play_icon_icon'] !== '' ){
			reycore_assets()->add_styles('reycore-elementor-el-video');
		}

	}

	/**
	 * Add custom settings into Elementor's Section
	 *
	 * @since 1.0.0
	 */
	function icon_settings( $element )
	{
		$element->start_injection( [
			'of' => 'play_icon_color',
		] );

		$element->add_control(
			'play_icon_icon',
			[
				'label' => __( 'Play Icon Type', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => __( 'Default (play circle)', 'rey-core' ),
					'caret'  => __( 'Caret right', 'rey-core' ),
					'chevron'  => __( 'Chevron right', 'rey-core' ),
					'play'  => __( 'Play icon', 'rey-core' ),
					'yt'  => __( 'Youtube Play icon', 'rey-core' ),
				],
				'prefix_class' => 'rey-video-iconType-'
			]
		);

		$element->end_injection();


	}

}
