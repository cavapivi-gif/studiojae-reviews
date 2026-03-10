<?php
namespace ReyCore\Elementor\Custom;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Text {

	function __construct(){

		add_action( 'elementor/element/text-editor/section_style/before_section_end', [$this, 'layout_settings'], 10);
		add_action( 'elementor/element/text-editor/section_editor/before_section_end', [$this, 'editor_controls'], 10);
		add_action( 'elementor/element/reycore-acf-text/section_style/before_section_end', [$this, 'layout_settings'], 10);
		add_action( 'elementor/widget/text-editor/skins_init', [$this, 'load_skins'] );
		add_filter( 'widget_text', [$this, 'widget_text'], 10, 2 );

	}

	function load_skins( $element )
	{
		$element->add_skin( new \ReyCore\Elementor\Custom\TextDynamic( $element ) );
	}

	function editor_controls( $element )
	{
		$editor = \Elementor\Plugin::instance()->controls_manager->get_control_from_stack( $element->get_unique_name(), 'editor' );
		if( $editor && ! is_wp_error($editor) ){
			$editor['condition']['_skin'] = [''];
			$element->update_control( 'editor', $editor );
		}

		$element->start_injection( [
			'of' => 'editor',
		] );

		\ReyCore\Elementor\Helper::render_dynamic_controls($element, [
			'source_key' => 'rey_dynamic_source',
			'condition_skin' => 'dynamic_text',
		]);

		$element->end_injection();
	}

	/**
	 * Add custom settings into Elementor's Section
	 *
	 * @since 1.0.0
	 */
	function layout_settings( $element )
	{

		$element->add_control(
			'rey_links_styles',
			[
				'label' => esc_html__( 'Links Style', 'rey-core' ). \ReyCore\Elementor\Helper::rey_badge(),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'separator' => 'before',
				'options' => [
					''  => esc_html__( '- None -', 'rey-core' ),
					'anim-ul'  => esc_html__( 'Thickness Animated Underline', 'rey-core' ),
					'ltr-ul'  => esc_html__( 'Left to right underline', 'rey-core' ),
					'altr-ul'  => esc_html__( 'Active left to right underline', 'rey-core' ),
					'exp-ul'  => esc_html__( 'Expanding underline', 'rey-core' ),
					'simple-ul'  => esc_html__( 'Simple underline', 'rey-core' ),
				],
				'prefix_class' => 'u-links-'
			]
		);

		$element->add_control(
			'rey_links_deco_color',
			[
				'label' => esc_html__( 'Decoration Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => '--deco-color: {{VALUE}}',
				],
				'condition' => [
					'rey_links_styles!' => '',
				],
			]
		);

		$element->add_control(
			'remove_last_p',
			[
				'label' => __( 'Remove last bottom-margin', 'rey-core' ) . \ReyCore\Elementor\Helper::rey_badge(),
				'description' => __( 'Remove the last paragraph bottom margin.', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'u-last-p-margin',
				'default' => '',
				'prefix_class' => '',
				'separator' => 'before',
				'selectors' => [
					'{{WRAPPER}} p:last-of-type' => 'margin-bottom:0',
				],
			]
		);

		$element->add_control(
			'rey_toggle_text',
			[
				'label' => __( 'Toggle Text', 'rey-core' ) . \ReyCore\Elementor\Helper::rey_badge(),
				'description' => __( 'Make the text toggable. Needs live preview.', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'separator' => 'before'
			]
		);

		$element->add_control(
			'rey_toggle_text_tags',
			[
				'label' => __( 'Strip tags', 'rey-core' ) . \ReyCore\Elementor\Helper::rey_badge(),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [
					'rey_toggle_text!' => '',
				],
			]
		);

		$element->add_control(
			'rey_toggle_text_more',
			[
				'label' => esc_html__( 'More text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => esc_html__( 'eg: More', 'rey-core' ),
				'condition' => [
					'rey_toggle_text!' => '',
				],
			]
		);

		$element->add_control(
			'rey_toggle_text_less',
			[
				'label' => esc_html__( 'Less text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => esc_html__( 'eg: Less', 'rey-core' ),
				'condition' => [
					'rey_toggle_text!' => '',
				],
			]
		);

		$element->add_responsive_control(
			'rey_toggle_text_height',
			[
				'label' => esc_html__( 'Text Height', 'rey-core' ) . ' (px)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 1000,
				'step' => 1,
				'condition' => [
					'rey_toggle_text!' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .u-toggle-text-next-btn' => '--height: {{VALUE}}px',
				],
			]
		);
	}

	function widget_text($content, $settings){

		if( isset($settings['rey_links_styles']) && '' !== $settings['rey_links_styles'] ){
			reycore_assets()->add_styles('reycore-elementor-text-links');
		}

		if( isset($settings['rey_toggle_text']) && $settings['rey_toggle_text'] !== '' ){
			return \ReyCore\Elementor\Helper::common_toggle_text($content, $settings);
		}

		return $content;
	}

}
