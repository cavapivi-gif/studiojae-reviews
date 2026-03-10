<?php
namespace ReyCore\Elementor\Custom;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Section {

	function __construct(){
		add_action( 'elementor/element/section/section_background/before_section_end', [$this, 'bg_settings']);
		add_action( 'elementor/element/section/section_layout/before_section_end', [$this, 'layout_settings']);
		add_action( 'elementor/element/section/section_advanced/before_section_end', [$this, 'section_advanced']);
		add_action( 'elementor/element/section/_section_responsive/after_section_end', [$this, 'custom_css_settings']);
		add_action( 'elementor/frontend/section/before_render', [$this, 'before_render']);
		add_action( 'elementor/frontend/section/after_render', [$this, 'after_render']);
		add_filter( 'elementor/frontend/section/should_render', ['\ReyCore\Elementor\WidgetsOverrides', 'should_render_element_or_widget'], 10, 2 );
	}

	/**
	 * Add background options
	 *
	 * @since 1.0.0
	 */
	function bg_settings( $element )
	{
		$control_manager = \Elementor\Plugin::instance()->controls_manager;

		// extract background args
		// group control is not available, so only get main bg control
		$bg = $control_manager->get_control_from_stack( $element->get_unique_name(), 'background_background' );

		if( $bg && ! is_wp_error($bg) ){
			$bg['prefix_class'] = 'rey-section-bg--';
			$element->update_control( 'background_background', $bg );
		}

		// Add Dynamic switcher
		$element->add_control(
			'rey_dynamic_bg',
			[
				'label' => esc_html__( 'Use Featured Image', 'rey-core' )  . \ReyCore\Elementor\Helper::rey_badge(),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [
					'background_background' => 'classic',
				],
			]
		);

		// adds desktop-only gradient
		// Add Dynamic switcher
		$element->add_control(
			'rey_desktop_gradient',
			[
				'label' => esc_html__( 'Desktop-Only Gradient', 'rey-core' ) .  \ReyCore\Elementor\Helper::rey_badge(),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [
					'background_background' => 'gradient',
				],
				'prefix_class' => 'rey-gradientDesktop-',
			]
		);

		$element->add_control(
			'rey_bg_disable_mobile',
			[
				'label' => esc_html__( 'Disable image on mobiles', 'rey-core' ) .  \ReyCore\Elementor\Helper::rey_badge(),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [
					'background_background' => 'classic',
				],
				'selectors' => [
					'(mobile){{WRAPPER}}' => 'background-image:none !important;',
				],
			]
		);

	}


	/**
	 * Add custom settings into Elementor's Section
	 *
	 * @since 1.0.0
	 */
	function layout_settings( $element )
	{
		// Content width var
		$content_width = \Elementor\Plugin::instance()->controls_manager->get_control_from_stack( $element->get_unique_name(), 'content_width' );

		if( $content_width && ! is_wp_error($content_width) ){
			$content_width['selectors']['{{WRAPPER}} > .elementor-container'] = 'max-width:{{SIZE}}{{UNIT}}; --container-max-width:{{SIZE}}{{UNIT}};';
			$element->update_control( 'content_width', $content_width );
		}

		// remove default stretch
		$element->remove_control( 'stretch_section' );

		$element->start_injection( [
			'of' => '_title',
		] );

		$element->add_control(
			'rey_stretch_section',
			[
				'label' => __( 'Stretch Section', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'return_value' => 'section-stretched',
				'prefix_class' => 'rey-',
				'hide_in_inner' => true,
				'description' => __( 'Stretch the section to the full width of the page using plain CSS.', 'rey-core' ) . sprintf( ' <a href="%1$s" target="_blank">%2$s</a>', 'https://go.elementor.com/stretch-section/', __( 'Learn more.', 'rey-core' ) ),
			]
		);

		$element->end_injection();

		$element->start_injection( [
			'of' => 'html_tag',
		] );

		$element->add_control(
			'rey_inner_section_width',
			[
				'label' => __( 'Inner-Section Width', 'rey-core' ) . \ReyCore\Elementor\Helper::rey_badge(),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'separator' => 'before',
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 2500,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
					'vw' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'size_units' => [ 'px', '%', 'vw' ],
				'selectors' => [
					'{{WRAPPER}}' => 'max-width: {{SIZE}}{{UNIT}};',
				],
				'hide_in_top' => true
			]
		);

		$element->add_control(
			'rey_flex_wrap',
			[
				'label' => __( 'Multi-rows (Flex Wrap)', 'rey-core' ) . \ReyCore\Elementor\Helper::rey_badge(),
				'description' => __( 'Enabling this option will allow columns on separate rows. Note that manual resizing handles are disabled.', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Yes', 'rey-core' ),
				'label_off' => __( 'No', 'rey-core' ),
				'return_value' => 'rey-flexWrap',
				'default' => '',
				'prefix_class' => '',
				'separator' => 'before'
			]
		);

		// Horizontal Mobile Offset
		\ReyCore\Elementor\WidgetsOverrides::horizontal_offset_for_mobile($element);

		$element->end_injection();

		if( \ReyCore\Elementor\Assets::maybe_load_rey_grid() ){
			// Custom Columns Gap
			$gap_columns_custom = \Elementor\Plugin::instance()->controls_manager->get_control_from_stack( $element->get_unique_name(), 'gap_columns_custom' );
			if( $gap_columns_custom && ! is_wp_error($gap_columns_custom) ){
				// Unset current selector
				unset($gap_columns_custom['selectors']['{{WRAPPER}} .elementor-column-gap-custom .elementor-column > .elementor-element-populated']);
				// set the new selector
				$gap_columns_custom['selectors']['{{WRAPPER}} .elementor-column-gap-custom'] = '--half-gutter-size:calc({{SIZE}}{{UNIT}} / 2);';
				// update control
				$element->update_control( 'gap_columns_custom', $gap_columns_custom );
			}
		}

	}


	public function custom_css_settings( $element ){
		\ReyCore\Elementor\WidgetsOverrides::custom_css_controls($element);
	}

	/**
		* Tweak the CSS classes field.
		*/
	function section_advanced( $stack )
	{
		$controls_manager = \Elementor\Plugin::instance()->controls_manager;
		$unique_name = $stack->get_unique_name();

		// Margin
		$margin = $controls_manager->get_control_from_stack( $unique_name, 'margin' );

		if( $margin && ! is_wp_error($margin) && ! empty($margin) ){
			$margin['condition'] = 'all';
			$margin['condition'] = [
				'rey_allow_horizontal_margin' => ''
			];
			$stack->update_control( 'margin', $margin );
		}

		$stack->start_injection( [
			'at' => 'after',
			'of' => 'margin',
		] );

		$stack->add_control(
			'rey_allow_horizontal_margin',
			[
				'label' => __( 'Vertical <strong>and Horizontal</strong> margins', 'rey-core' ) . \ReyCore\Elementor\Helper::rey_badge(),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'return_value' => 'all',
			]
		);

		$container_spacing = get_theme_mod('container_spacing', 15);

		$stack->add_responsive_control(
			'rey_margin_all',
			[
				'label' => __( 'Margin', 'rey-core' ) . \ReyCore\Elementor\Helper::rey_badge(),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}}' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; --section-left-margin:{{LEFT}}{{UNIT}}; --section-right-margin:{{RIGHT}}{{UNIT}}; ',
				],
				'condition' => [
					'rey_allow_horizontal_margin' => 'all'
				],
				'placeholder' => [
					'left' => $container_spacing,
					'right' => $container_spacing,
				],
			]
		);

		$stack->end_injection();

		// Zindex
		$z_index = $controls_manager->get_control_from_stack( $unique_name, 'z_index' );
		if( $z_index && ! is_wp_error($z_index) ){
			$z_index['prefix_class'] = '--zindexed-';
			$stack->update_control( 'z_index', $z_index );
		}

		// CSS CLASS - stretch field
		$css_classes = $controls_manager->get_control_from_stack( $unique_name, 'css_classes' );
		if( $css_classes && ! is_wp_error($css_classes) ) {
			$css_classes['label_block'] = true;
			$stack->update_control( 'css_classes', $css_classes );
		}

		\ReyCore\Elementor\WidgetsOverrides::hide_element_on($stack);

	}

	/**
	* Render some attributes before rendering
	*
	* @since 1.0.0
	**/
	public function before_render( $element )
	{
		$is_inner = $element->get_data( 'isInner' );

		if( ! apply_filters( "elementor/frontend/section/should_render", true, $element, $is_inner ) ){
			return;
		}

		static $css;

		if( ! $css ){
			reycore_assets()->add_styles([
				\ReyCore\Elementor\Assets::get_stylesheet_suffix('section', 'key'),
				\ReyCore\Elementor\Assets::get_stylesheet_suffix('section-deferred', 'key'),
			]);
			$css = true;
		}

		$settings = $element->get_settings();

		do_action('reycore/frontend/section/before_render', $element, $is_inner);

		// Dynamic image background
		if(
			'classic' === $settings['background_background'] &&
			isset($settings['rey_dynamic_bg']) &&
			$settings['rey_dynamic_bg'] === 'yes'
		){
			$thumbnail_data = reycore__get_post_term_thumbnail();

			if( isset($thumbnail_data['url']) && ($thumbnail_url = $thumbnail_data['url']) ){
				$element->add_render_attribute( '_wrapper', 'style', sprintf('background-image:url(%s);', $thumbnail_url) );
			}
		}

		if( isset($settings['rey_mobile_offset']) && '' !== $settings['rey_mobile_offset'] ){
			reycore_assets()->add_styles('reycore-elementor-mobi-offset');
		}

	}


	/**
	* Add HTML after section rendering
	*
	* @since 1.0.0
	**/
	public function after_render( $element )
	{
		$is_inner = $element->get_data( 'isInner' );

		if( ! apply_filters( "elementor/frontend/section/should_render", true, $element, $is_inner ) ){
			return;
		}

		do_action('reycore/frontend/section/after_render', $element, $is_inner);

	}

}
