<?php
namespace ReyCore\Elementor\Custom;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Icon {

	function __construct(){
		add_action( 'elementor/element/icon/section_icon/before_section_end', [$this, 'add_controls'], 10);
		add_action( 'elementor/element/icon/section_style_icon/before_section_end', [$this, 'add_controls_style'], 10);
	}

	/**
	 * Add custom settings into Elementor's image section
	 *
	 * @since 1.0.0
	 */
	function add_controls( $element )
	{
		$element->add_control(
			'icon_block',
			[
				'label' => esc_html__( 'Force as block', 'rey-core' )  . \ReyCore\Elementor\Helper::rey_badge(),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'prefix_class' => '--ib-',
				'selectors' => [
					'{{WRAPPER}} .elementor-icon-wrapper' => 'display: flex;',
				],
			]
		);
	}

	function add_controls_style( $element )
	{
		$element->add_responsive_control(
			'icon_height',
			[
				'label' => esc_html__( 'Height', 'rey-core' ) . ' (em)'  . \ReyCore\Elementor\Helper::rey_badge(),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 5,
				'step' => 0.05,
				'selectors' => [
					'{{WRAPPER}} .elementor-icon, {{WRAPPER}} .elementor-icon i, {{WRAPPER}} .elementor-icon svg' => 'height: {{VALUE}}em;',
				],
			]
		);
	}

}
