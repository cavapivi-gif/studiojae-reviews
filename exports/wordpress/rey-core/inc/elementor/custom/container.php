<?php
namespace ReyCore\Elementor\Custom;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use Elementor\Core\Breakpoints\Manager as Breakpoints_Manager;

class Container {

	private static $__count = 0;

	public $should_render = [];


	function __construct(){
		add_action( 'elementor/frontend/container/before_render', [$this, 'before_render']);
		add_action( 'elementor/frontend/container/after_render', [$this, 'after_render']);
		add_action( 'elementor/element/container/_section_responsive/after_section_end', [$this, 'custom_css_settings']);
		add_action( 'elementor/element/container/section_layout_additional_options/before_section_end', [$this, 'offset_for_mobile']);
		add_action( 'elementor/element/container/section_layout/before_section_end', [$this, 'section_layout_settings']);
		add_action( 'elementor/element/container/section_layout_container/before_section_end', [$this, 'section_layout_container_settings']);
	}


	/**
	* Render some attributes before rendering
	*
	* @since 1.0.0
	**/
	function before_render( $element )
	{

		if( \ReyCore\Elementor\WidgetsOverrides::maybe_hide_element( $element ) ){
			add_filter( 'elementor/frontend/container/should_render', '__return_false' );
			return;
		}

		$is_inner = ! (self::$__count === 0);
		self::$__count++;

		$element_id = $element->get_id();

		do_action('reycore/frontend/container/before_render', $element, $is_inner);

		if( ! $is_inner ){
			$element->add_render_attribute('_wrapper', 'class', 'e-con-top');
		}

		$this->should_render[$element_id] = true;

		static $assets;

		if( ! $assets ){
			reycore_assets()->add_styles( \ReyCore\Elementor\Assets::get_stylesheet_suffix('container', 'key') );
			$assets = true;
		}

		$settings = $element->get_settings();

		if( isset($settings['rey_mobile_offset']) && '' !== $settings['rey_mobile_offset'] ){
			reycore_assets()->add_styles('reycore-elementor-mobi-offset');
		}
	}

	/**
	* Add HTML after container rendering
	*
	* @since 1.0.0
	**/
	function after_render( $element )
	{

		if( \ReyCore\Elementor\WidgetsOverrides::maybe_hide_element( $element ) ){
			remove_filter( 'elementor/frontend/container/should_render', '__return_false' );
			return;
		}

		self::$__count--;
		$is_inner = ! (self::$__count === 0);

		$element_id = $element->get_id();

		if( ! isset($this->should_render[$element_id]) ){
			return;
		}

		if( ! $this->should_render[$element_id] ){
			return;
		}

		do_action('reycore/frontend/container/after_render', $element, $is_inner);

	}

	public function custom_css_settings( $element ){
		\ReyCore\Elementor\WidgetsOverrides::custom_css_controls($element);
	}

	public function section_layout_settings( $element ){

		$controls_manager = \Elementor\Plugin::instance()->controls_manager;
		$unique_name = $element->get_unique_name();

		// Zindex
		if( ($z_index = $controls_manager->get_control_from_stack( $unique_name, 'z_index' )) && ! is_wp_error($z_index) ){
			$z_index['prefix_class'] = '--zindexed-';
			$element->update_control( 'z_index', $z_index );
		}

		// "Hide On" Selector
		\ReyCore\Elementor\WidgetsOverrides::hide_element_on($element);
	}

	public function offset_for_mobile( $element ){
		// Horizontal Mobile Offset
		\ReyCore\Elementor\WidgetsOverrides::horizontal_offset_for_mobile(
			$element ,
			esc_html__('Only works with an inner Container, set as Row.', 'rey-core')
		);
	}

	public function section_layout_container_settings( $element ){

		$element->start_injection( [
			'of' => 'content_width',
			'at' => 'before',
		] );

		$element->add_control(
			'rey_stretch_section',
			[
				'label' => __( 'Stretch Container', 'rey-core' ) . \ReyCore\Elementor\Helper::rey_badge(),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'return_value' => 'section-stretched',
				'prefix_class' => 'rey-',
				'hide_in_inner' => true,
				'description' => __( 'Stretch the container to the full width of the page using plain CSS.', 'rey-core' ),
			]
		);

		$element->end_injection();

		$element->add_responsive_control(
			'rey_cols',
			[
				'label' => esc_html__( 'Fixed Columns [beta]', 'rey-core' ) . \ReyCore\Elementor\Helper::rey_badge(),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 1,
				'max' => 20,
				'step' => 1,
				'condition' => [
					'flex_direction' => ['row', 'row-reverse'],
					// 'flex_wrap' => 'wrap',
				],
				'prefix_class' => 'r-con-cols',
				'selectors' => [
					'{{WRAPPER}}:not(.e-con-boxed)' => '--display: grid; grid-template-columns: repeat({{VALUE}}, 1fr);',
					'{{WRAPPER}}.e-con-boxed > .e-con-inner' => '--display: grid; grid-template-columns: repeat({{VALUE}}, 1fr);',
				],
			]
		);


	}
}
