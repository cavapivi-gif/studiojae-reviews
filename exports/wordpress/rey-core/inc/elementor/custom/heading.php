<?php
namespace ReyCore\Elementor\Custom;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Heading {

	function __construct(){
		add_action( 'elementor/widget/heading/skins_init', [$this,'heading_skins'] );
		add_action( 'elementor/element/heading/section_title/before_section_end', [$this,'heading_controls'], 10);
		add_action( 'elementor/element/heading/section_title_style/after_section_end', [$this,'heading_controls_styles'], 10);
		add_action( 'elementor/element/reycore-acf-heading/section_title_style/after_section_end', [$this,'heading_controls_styles'], 10);
		add_action( 'elementor/frontend/widget/before_render', [$this, 'before_render'], 10);
	}

	/**
	 * Add custom skins into Elementor's Heading widget
	 *
	 * @since 1.0.0
	 */
	function heading_skins( $element )
	{
		$element->add_skin( new \ReyCore\Elementor\Custom\HeadingDynamic( $element ) );
	}


	/**
	 * Add custom settings into Elementor's title section
	 *
	 * @since 1.0.0
	 */
	function heading_controls( $element )
	{
		$heading_title = \Elementor\Plugin::instance()->controls_manager->get_control_from_stack( $element->get_unique_name(), 'title' );
		if( $heading_title && ! is_wp_error($heading_title) ){
			$heading_title['condition']['_skin'] = [''];
			$element->update_control( 'title', $heading_title );
		}

		$element->start_injection( [
			'of' => 'title',
		] );

		\ReyCore\Elementor\Helper::render_dynamic_controls($element, [
			'source_key' => 'source',
			'condition_skin' => 'dynamic_title',
		]);

		$element->end_injection();
	}


	/**
	 * Add custom settings into Elementor's title style section
	 *
	 * @since 1.0.0
	 */
	function heading_controls_styles( $element )
	{

		$element->start_controls_section(
			'section_special_styles',
			[
				'label' => __( 'Special Styles', 'rey-core' ) . \ReyCore\Elementor\Helper::rey_badge(),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$element->add_control(
			'rey_text_stroke',
			[
				'label' => __( 'Text Outline', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'return_value' => 'stroke',
				'prefix_class' => 'elementor-heading--',
				'selectors' => [
					'{{WRAPPER}} .elementor-heading-title' => '-webkit-text-fill-color: transparent; -webkit-text-stroke-color: currentColor; -webkit-text-stroke-width: var(--heading-stroke-size, 2px);',
				],
			]
		);

		$element->add_control(
			'rey_text_stroke_size',
			[
				'label' => __( 'Stroke size', 'rey-core' ) . ' (px)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}}' => '--heading-stroke-size: {{VALUE}}px',
				],
				'condition' => [
					'rey_text_stroke!' => '',
				],
			]
		);

		$element->add_responsive_control(
			'rey_vertical_text',
			[
				'label' => __( 'Vertical Text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'return_value' => 'vertical',
				'prefix_class' => 'elementor-heading-%s-',
			]
		);

		$element->add_control(
			'rey_vertical_text_reversed',
			[
				'label' => __( 'Vertical Text - Reversed', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'return_value' => 'yes',
				'prefix_class' => '--rev-',
				'condition' => [
					'rey_vertical_text!' => '',
				],
				'selectors' => [
					'{{WRAPPER}}' => '--heading-vertical-dir: 0deg;',
				],
			]
		);

		$element->add_control(
			'rey_parent_hover',
			[
				'label' => __( 'Animate on Parent Hover', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => __( 'None', 'rey-core' ),
					'underline'  => __( 'Underline animation', 'rey-core' ),
					'show'  => __( 'Visible In', 'rey-core' ),
					'hide'  => __( 'Visible Out', 'rey-core' ),
					'slide_in'  => __( 'Slide In', 'rey-core' ),
					'slide_out'  => __( 'Slide Out', 'rey-core' ),
				],
				'prefix_class' => 'p-ani--'
			]
		);

		$element->add_control(
			'rey_parent_hover_slide_direction',
			[
				'label' => __( 'Slide direction', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'bottom',
				'options' => [
					'top'  => __( 'Top', 'rey-core' ),
					'right'  => __( 'Right', 'rey-core' ),
					'bottom'  => __( 'Bottom', 'rey-core' ),
					'left'  => __( 'Left', 'rey-core' ),
				],
				'prefix_class' => '--slide-',
				'condition' => [
					'rey_parent_hover' => ['slide_in', 'slide_out'],
				],
			]
		);

		$element->add_control(
			'rey_parent_hover_slide_blur',
			[
				'label' => esc_html__( 'Slide with blur', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'return_value' => 'ry',
				'prefix_class' => '--blur',
				'condition' => [
					'rey_parent_hover' => ['show', 'hide', 'slide_in', 'slide_out'],
				],
			]
		);

		$element->add_control(
			'rey_parent_hover_delay',
			[
				'label' => esc_html__( 'Transition delay', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 5000,
				'step' => 50,
				'selectors' => [
					'{{WRAPPER}} .elementor-heading-title' => 'transition-delay: {{VALUE}}ms',
				],
				'condition' => [
					'rey_parent_hover' => ['show', 'hide', 'slide_in', 'slide_out'],
				],
			]
		);

		$element->add_control(
			'rey_parent_hover_trigger',
			[
				'label' => __( 'Parent Hover Trigger', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'column',
				'options' => [
					'column'  => __( 'Parent Column', 'rey-core' ),
					'section'  => __( 'Parent Section', 'rey-core' ),
					'container'  => __( 'Parent Container', 'rey-core' ),
					'top-container'  => __( 'Parent Top Container', 'rey-core' ),
				],
				'condition' => [
					'rey_parent_hover!' => '',
				],
				'prefix_class' => 'p-trg--'
			]
		);

		// parent hover
		// hover trigger - parent section / parent column
		// hover effect - underline / animate in

		$element->add_control(
			'rey_special_text_heading',
			[
				'label' => esc_html__( 'WORD STYLES', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);
		$element->add_control(
			'rey_special_text_styles',
			[
				'label' => esc_html__( 'Styles', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => esc_html__( 'None', 'rey-core' ),
					'block'  => esc_html__( 'Simple word as block', 'rey-core' ),
					'grd'  => esc_html__( 'Gradient word', 'rey-core' ),
					'out'  => esc_html__( 'Outline word', 'rey-core' ),
					'hf'  => esc_html__( 'Highlight full', 'rey-core' ),
					'hp'  => esc_html__( 'Highlight partial', 'rey-core' ),
					'hp2'  => esc_html__( 'Highlight partial (multi-lines)', 'rey-core' ),
					'cv'  => esc_html__( 'Curvy underline', 'rey-core' ),
				],
				'prefix_class' => 'el-mark--',
			]
		);

		$element->add_control(
			'rey_special_text_styles__notice',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => __('Wrap the word into a <strong>&lt;mark&gt;...&lt;/mark&gt;</strong> tag.', 'rey-core'),
				'content_classes' => 'elementor-descriptor',
				'condition' => [
					'rey_special_text_styles!' => '',
				],
			]
		);

		$element->add_control(
			'rey_special_text_styles__stroke_size',
			[
				'label' => __( 'Stroke size', 'rey-core' ) . ' (px)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 1,
				'selectors' => [
					'{{WRAPPER}} mark' => '--mark-stroke-size: {{VALUE}}px',
				],
				'condition' => [
					'rey_special_text_styles' => 'out',
				],
			]
		);

		$element->add_control(
			'rey_special_text_styles__height',
			[
				'label' => __( 'Height', 'rey-core' ) . ' (%)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 33,
				'selectors' => [
					'{{WRAPPER}} mark' => '--hp-height:{{VALUE}}%;',
				],
				'condition' => [
					'rey_special_text_styles' => ['hp', 'hp2'],
				],
			]
		);

		$element->add_control(
			'rey_special_text_styles__color',
			[
				'label' => esc_html__( 'Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} mark' => '--mark-color: {{VALUE}}',
				],
				'condition' => [
					'rey_special_text_styles!' => '',
				],
			]
		);

		$element->add_control(
			'rey_special_text_styles__grcolor',
			[
				'label' => esc_html__( '2nd Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} mark' => '--mark-gradient-color: {{VALUE}}',
				],
				'condition' => [
					'rey_special_text_styles' => ['grd', 'hp2'],
				],
			]
		);

		$element->add_control(
			'rey_special_text_styles__grangle',
			[
				'label' => esc_html__( 'Gradient Angle', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'deg' ],
				'default' => [
					'unit' => 'deg',
					'size' => 180,
				],
				'range' => [
					'deg' => [
						'step' => 10,
					],
				],
				'selectors' => [
					'{{WRAPPER}} mark' => '--mark-gradient-angle: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'rey_special_text_styles' => 'grd',
				],
			]
		);

		$element->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name' => 'rey_special_text_styles__bg',
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} mark, {{WRAPPER}} mark:before',
				'condition' => [
					'rey_special_text_styles!' => ['', 'grd', 'hp2'],
				],
			]
		);

		$element->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'rey_special_text_styles__typo',
				'selector' => '{{WRAPPER}} mark',
				'condition' => [
					'rey_special_text_styles!' => '',
				],
			]
		);

		$element->add_responsive_control(
			'rey_special_text_styles__padding',
			[
				'label' => __( 'Padding', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors' => [
					'{{WRAPPER}} mark, {{WRAPPER}} mark:before' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'rey_special_text_styles!' => '',
				],
			]
		);

		$element->add_control(
			'rey_special_text_styles__radius',
			[
				'label' => __( 'Border Radius', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} mark, {{WRAPPER}} mark:before' => 'border-radius: {{VALUE}}px',
				],
				'condition' => [
					'rey_special_text_styles!' => ['', 'grd'],
				],
			]
		);

		$element->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'rey_special_text_styles__shadow',
				'selector' => '{{WRAPPER}} mark, {{WRAPPER}} mark:before',
				'condition' => [
					'rey_special_text_styles!' => ['', 'grd'],
				],
			]
		);

		$element->add_group_control(
			\Elementor\Group_Control_Text_Shadow::get_type(),
			[
				'name' => 'rey_special_text_styles__tshadow',
				'selector' => '{{WRAPPER}} mark',
				'condition' => [
					'rey_special_text_styles!' => '',
				],
			]
		);

		$element->add_control(
			'rey_special_text_styles__blend_mode',
			[
				'label' => __( 'Blend Mode', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'' => __( 'Normal', 'rey-core' ),
					'multiply' => 'Multiply',
					'screen' => 'Screen',
					'overlay' => 'Overlay',
					'darken' => 'Darken',
					'lighten' => 'Lighten',
					'color-dodge' => 'Color Dodge',
					'saturation' => 'Saturation',
					'color' => 'Color',
					'difference' => 'Difference',
					'exclusion' => 'Exclusion',
					'hue' => 'Hue',
					'luminosity' => 'Luminosity',
				],
				'selectors' => [
					'{{WRAPPER}} mark' => 'mix-blend-mode: {{VALUE}}',
				],
				'condition' => [
					'rey_special_text_styles!' => '',
				],
			]
		);

		$element->add_responsive_control(
			'rey_special_text_styles__margin',
			[
				'label' => __( 'Margin', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors' => [
					'{{WRAPPER}} mark' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'rey_special_text_styles' => 'block',
				],
			]
		);

		$element->end_controls_section();
	}

	function before_render( $element )
	{

		if( ! in_array($element->get_unique_name(), ['heading', 'reycore-acf-heading'], true) ){
			return;
		}

		$settings = $element->get_data('settings');

		if( isset($settings['rey_parent_hover']) && ($hover_type = $settings['rey_parent_hover']) ){

			$styles_map = [
				'underline' => 'u',
				'show'      => 'v',
				'hide'      => 'v',
				'slide_in'  => 's',
				'slide_out' => 's',
			];

			$anim_heading_target = ! empty( $settings['link']['url'] ) ? 'url' : 'title';
			$element->add_render_attribute( $anim_heading_target, 'class', 'h-ani' );

			reycore_assets()->add_styles('reycore-elementor-heading-animation-' . $styles_map[$hover_type] );
			reycore_assets()->add_scripts('reycore-elementor-elem-heading');
		}

		if( (isset($settings['rey_vertical_text']) && $settings['rey_vertical_text'] )
			|| (isset($settings['rey_vertical_text_tablet']) && $settings['rey_vertical_text_tablet'] )
			|| (isset($settings['rey_vertical_text_mobile']) && $settings['rey_vertical_text_mobile'] )
		){
			reycore_assets()->add_styles('reycore-elementor-heading-vertical');
		}

		if( isset($settings['rey_special_text_styles']) && $settings['rey_special_text_styles'] !== '' ){
			reycore_assets()->add_styles('reycore-elementor-heading-highlight');
		}

		if( isset($settings['_css_classes']) && $css_classes = $settings['_css_classes'] ){
			if( strpos($css_classes, 'u-title-dashes') !== false ){
				reycore_assets()->add_styles('reycore-elementor-el-heading-dashes');
			}
		}
	}

}
