<?php
namespace ReyCore\Elementor\Custom;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class IconBox {

	function __construct(){
		add_action( 'elementor/element/icon-box/section_style_icon/before_section_end', [$this, 'add_controls_style'], 10);
		add_action( 'elementor/widget/icon-box/skins_init', [$this,'custom_skins'] );
		add_action( 'elementor/frontend/widget/before_render', [$this, 'before_render'], 10);


	}

	/**
	* Render some attributes before rendering
	*
	* @since 1.0.0
	**/
	function before_render( $element )
	{
		if( ! in_array($element->get_unique_name(), ['icon-box'], true) ){
			return;
		}

		static $assets;

		if( ! $assets ){
			reycore_assets()->add_styles('reycore-elementor-el-iconbox');
			$assets = true;
		}
	}


	function add_controls_style( $element )
	{

		$element->add_control(
			'rey_container_icon_width',
			[
			   'label' => esc_html__( 'Icon wrapper width', 'rey-core' ) . \ReyCore\Elementor\Helper::rey_badge(),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range' => [
					'px' => [
						'min' => 9,
						'max' => 1000,
						'step' => 1,
					],
					'em' => [
						'min' => 0,
						'max' => 10.0,
					],
				],
				'default' => [],
				'selectors' => [
					'{{WRAPPER}}' => '--icon-wrapper-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$hover_primary_color = \Elementor\Plugin::instance()->controls_manager->get_control_from_stack( $element->get_unique_name(), 'hover_primary_color' );
		if( $hover_primary_color && ! is_wp_error($hover_primary_color) ){
			$hover_primary_color['selectors']['{{WRAPPER}}'] = '--hover-primary-color: {{VALUE}};';
			$element->update_control( 'hover_primary_color', $hover_primary_color );
		}

		$element->add_responsive_control(
			'rey_box_padding',
			[
				'label' => esc_html__( 'Padding', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .elementor-icon-box-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
	}

	/**
	 * Add custom skins into Elementor's Image Carousel widget
	 *
	 * @since 1.6.0
	 */
	function custom_skins( $element )
	{
		$element->add_skin( new \ReyCore\Elementor\Custom\IconBoxLink( $element ) );
	}

}
