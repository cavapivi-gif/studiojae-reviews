<?php
namespace ReyCore\Elementor\Custom;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use \ReyCore\Elementor\Helper;

class Common
{

	public function __construct(){

		add_action( 'elementor/element/common/_section_style/before_section_end', [$this, 'common_advanced']);
		add_action( 'elementor/element/common/_section_position/before_section_end', [$this, 'common_position']);
		// add_action( 'elementor/element/common/_section_responsive/after_section_end', [$this, 'common_popover']);
		add_action( 'elementor/element/common/_section_responsive/after_section_end', [$this, 'custom_css_settings']);
		add_filter( 'elementor/frontend/widget/should_render', ['\ReyCore\Elementor\WidgetsOverrides', 'should_render_element_or_widget'], 10, 2 );

	}

	public static function is_elementor36(){
		return defined('ELEMENTOR_VERSION') ? version_compare(ELEMENTOR_VERSION, '3.6', '>=') : false;
	}

	/**
	 * Tweak the CSS classes field.
	 */
	public function common_advanced( $stack ) {

		$controls_manager = \Elementor\Plugin::instance()->controls_manager;
		$unique_name = $stack->get_unique_name();

		// Zindex
		$z_index = $controls_manager->get_control_from_stack( $unique_name, '_z_index' );
		if( $z_index && ! is_wp_error($z_index) ){
			$z_index['prefix_class'] = '--zindexed-';
			if( self::is_elementor36() ){
				// Add a separator before the z-index to separate from Position.
				$z_index['separator'] = 'before';
			}
			$stack->update_control( '_z_index', $z_index );
		}

		// Transform CSS Classes control's text input, into a block
		$css_classes = $controls_manager->get_control_from_stack( $unique_name, '_css_classes' );
		if( $css_classes && ! is_wp_error($css_classes) ) {
			$css_classes['label_block'] = true;
			$stack->update_control( '_css_classes', $css_classes );
		}

		$stack->add_responsive_control(
			'rey_el_order',
			[
				'label' => __( 'Order', 'rey-core' ) . \ReyCore\Elementor\Helper::rey_badge(),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => esc_html__( '- Default -', 'rey-core' ),
					'-1'  => esc_html__( 'First', 'rey-core' ),
					'1'  => esc_html__( '1', 'rey-core' ),
					'2'  => esc_html__( '2', 'rey-core' ),
					'3'  => esc_html__( '3', 'rey-core' ),
					'4'  => esc_html__( '4', 'rey-core' ),
					'5'  => esc_html__( '5', 'rey-core' ),
					'6'  => esc_html__( '6', 'rey-core' ),
					'7'  => esc_html__( '7', 'rey-core' ),
					'8'  => esc_html__( '8', 'rey-core' ),
					'9'  => esc_html__( '9', 'rey-core' ),
					'10'  => esc_html__( '10', 'rey-core' ),
					'999'  => esc_html__( 'Last', 'rey-core' ),
				],
				'selectors' => [
					'{{WRAPPER}}' => 'order: {{VALUE}};',
				],
			]
		);

		$stack->add_control(
			'rey_utility_classes',
			[
				'label' => esc_html__( 'Utility Classes', 'rey-core' ) . \ReyCore\Elementor\Helper::rey_badge(),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => esc_html__( '- None -', 'rey-core' ),
					'm-auto--top'  => esc_html__( 'Margin Top - Auto', 'rey-core' ),
					'm-auto--right'  => esc_html__( 'Margin Right - Auto', 'rey-core' ),
					'm-auto--bottom'  => esc_html__( 'Margin Bottom - Auto', 'rey-core' ),
					'm-auto--left'  => esc_html__( 'Margin Left - Auto', 'rey-core' ),
					'u-ov-hidden'  => esc_html__( 'Overflow Hidden', 'rey-core' ),
				],
				'prefix_class' => ''
			]
		);

		\ReyCore\Elementor\WidgetsOverrides::hide_element_on($stack);

		// in Elementor 3.6+
		// move custom controls in Layout tab
		if( self::is_elementor36() ){
			$this->add_inline_controls($stack);
			$this->add_position_controls($stack);
		}

	}

	/**
	 * Add custom settings into Elementor's "Custom Position" Section
	 *
	 * @since 1.0.0
	 */
	public function common_position( $stack )
	{
		// Things changed in Elementor 3.6+
		// so the controls no longer need to be added
		if( ! self::is_elementor36() ){
			$this->add_inline_controls($stack, '_element_vertical_align_mobile');
			$this->add_position_controls($stack);
		}
	}

	/**
	 * Adds custom controls for Position options
	 *
	 * @param object $stack
	 * @return void
	 */
	public function add_position_controls( $stack ){

		$stack->start_injection( [
			'at' => 'after',
			'of' => '_position',
		] );

		$stack->add_control(
			'rey_position_mobile',
			[
				'label' => esc_html__( 'Static position on mobiles', 'rey-core' ) . \ReyCore\Elementor\Helper::rey_badge(),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [
					'_position!' => '',
				],
				'prefix_class' => 'rey-default-position-',
			]
		);

		$stack->end_injection();

	}

	/**
	 * Adds custom controls for Width Flex Layout options
	 *
	 * @param object $stack
	 * @return void
	 */
	public function add_inline_controls( $stack, $after = '_element_vertical_align' ){

		$stack->start_injection( [
			'at' => 'after',
			'of' => $after,
		] );

		$stack->add_responsive_control(
			'rey_inline_horizontal_align',
			[
				'label'           => __( 'Horizontal Align', 'rey-core' ) . \ReyCore\Elementor\Helper::rey_badge(),
				'type'            => \Elementor\Controls_Manager::CHOOSE,
				'toggle'          => true,
				'default'         => '',
				'options'         => [
					'left'           => [
						'title'         => __( 'Left', 'rey-core' ),
						'icon'          => 'eicon-h-align-left',
					],
					'stretch'        => [
						'title'         => __( 'Stretch', 'rey-core' ),
						'icon'          => 'eicon-h-align-stretch',
					],
					'right'          => [
						'title'         => __( 'Right', 'rey-core' ),
						'icon'          => 'eicon-h-align-right',
					],
				],
				'devices'         => [ 'desktop', 'tablet', 'mobile' ],
				'condition'       => [
					'_element_width' => ['auto', 'initial'],
					'_position'      => '',
				],
				'prefix_class'    => '--il-%s-',
			]
		);

		$stack->add_responsive_control(
			'rey_inline_flex_grow',
			[
				'label'           => __( 'Flex Grow', 'rey-core' ) . \ReyCore\Elementor\Helper::rey_badge(),
				'type'            => \Elementor\Controls_Manager::SELECT,
				'label_block'     => false,
				'default'         => '',
				'options'         => [
					'' => __( 'Unset', 'rey-core' ),
					'1' => __( 'Yes', 'rey-core' ),
					'0' => __( 'No', 'rey-core' ),
				],
				'devices'         => [ 'desktop', 'tablet', 'mobile' ],
				'condition'       => [
					'_element_width' => ['auto', 'initial'],
					'_position'      => '',
				],
				'selectors' => [
					'{{WRAPPER}}' => 'flex-grow: {{VALUE}}',
				],
				// 'description' => esc_html__('Specifies how much the item will grow relative to the rest of the flexible items inside the same container.', 'rey-core'),
			]
		);

		$stack->add_responsive_control(
			'rey_inline_flex_shrink',
			[
				'label'           => __( 'Flex Shrink', 'rey-core' ) . \ReyCore\Elementor\Helper::rey_badge(),
				'type'            => \Elementor\Controls_Manager::SELECT,
				'label_block'     => false,
				'default'         => '',
				'options'         => [
					'' => __( 'Unset', 'rey-core' ),
					'1' => __( 'Yes', 'rey-core' ),
					'0' => __( 'No', 'rey-core' ),
				],
				'devices'         => [ 'desktop', 'tablet', 'mobile' ],
				'condition'       => [
					'_element_width' => ['auto', 'initial'],
					'_position'      => '',
				],
				'selectors' => [
					'{{WRAPPER}}' => 'flex-shrink: {{VALUE}}',
				],
				// 'description' => esc_html__('Specifies how the item will shrink relative to the rest of the flexible items inside the same container.', 'rey-core'),
			]
		);

		$stack->end_injection();

	}

	public function common_popover( $stack )
	{

		$stack->start_controls_section(
			'section_rey_tooltip',
			[
				'label' => esc_html__('Hover Tooltip', 'rey-core') . \ReyCore\Elementor\Helper::rey_badge(),
				'tab' => \Elementor\Controls_Manager::TAB_ADVANCED,
			]
		);

		$stack->add_control(
			'rey_tooltip',
			[
				'label' => esc_html__( 'Enable Content Tooltip', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				// 'return_value' => 'yes',
				'default' => '',
			]
		);

		$stack->end_controls_section();

	}

	public function custom_css_settings( $element ){
		\ReyCore\Elementor\WidgetsOverrides::custom_css_controls($element);
	}

}
