<?php
namespace ReyCore\Elementor\Custom;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Image {

	function __construct(){
		add_action( 'elementor/widget/image/skins_init', [$this, 'image_skins'] );
		add_action( 'elementor/element/image/section_image/before_section_end', [$this, 'image_image_controls'], 10);
		add_action( 'elementor/element/image/section_style_image/before_section_end', [$this, 'image_style_controls'], 10);
	}

	/**
	 * Add custom skins into Elementor's Image widget
	 *
	 * @since 1.0.0
	 */
	function image_skins( $element )
	{
		$element->add_skin( new \ReyCore\Elementor\Custom\ImageDynamic( $element ) );
	}

	/**
	 * Add custom settings into Elementor's image section
	 *
	 * @since 1.0.0
	 */
	function image_image_controls( $element )
	{
		$image_control = \Elementor\Plugin::instance()->controls_manager->get_control_from_stack( $element->get_unique_name(), 'image' );
		if( $image_control && ! is_wp_error($image_control) ){
			$image_control['condition']['_skin'] = [''];
			$element->update_control( 'image', $image_control );
		}

		$element->start_injection( [
			'of' => 'image',
		] );

		$element->add_control(
			'source',
			[
				'label' => __( 'Image Source', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'featured_image',
				'options' => [
					'featured_image'  => __( 'Featured Image', 'rey-core' ),
				],
				'condition' => [
					'_skin' => ['dynamic_image'],
				],
			]
		);

		$element->end_injection();
	}


	/**
	 * Add custom settings into Elementor's style section
	 *
	 * @since 1.0.0
	 */
	function image_style_controls( $element )
	{

		$element->start_injection( [
			'of' => 'image_box_shadow',
		] );

		$element->add_control(
			'rey_custom_height__notice',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => '<em>'. __( 'These options are deprecated because Elementor added Custom height options natively. Please use those options instead.', 'rey-core' ) .'</em>',
				'content_classes' => 'rey-raw-html',
				'separator' => 'before',
			]
		);


		$element->add_control(
			'rey_custom_height',
			[
				'label' => __( 'Custom Height', 'rey-core' ) . \ReyCore\Elementor\Helper::rey_badge(),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'ch',
				'prefix_class' => 'elementor-image--',
				'default' => '',
			]
		);


		$element->add_responsive_control(
			'rey_height',
			[
			'label' => __( 'Height', 'rey-core' ) . \ReyCore\Elementor\Helper::rey_badge(),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 10,
						'max' => 1000,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => 'px',
					// 'size' => ,
				],
				'tablet_default' => [
					'unit' => 'px',
				],
				'mobile_default' => [
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}}.elementor-image--ch img' => 'height: {{SIZE}}{{UNIT}}; object-fit: cover;',
				],
				'condition' => [
					'rey_custom_height' => 'ch',
				],
			]
		);

		$element->add_responsive_control(
			'rey_custom_height_contain',
			[
				'label' => __( 'Contain image?', 'rey-core' ) . \ReyCore\Elementor\Helper::rey_badge(),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'contain',
				'default' => '',
				'selectors' => [
					'{{WRAPPER}}.elementor-image--ch img' => 'object-fit: {{VALUE}};',
				],
				'condition' => [
					'rey_custom_height' => 'ch',
				],
			]
		);

		$element->end_injection();
	}

}
