<?php
namespace ReyCore\Elementor\Custom;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use \ReyCore\Elementor\Helper;
use Elementor\Core\Breakpoints\Manager as Breakpoints_Manager;

class Column
{

	function __construct(){
		add_action( 'elementor/element/column/layout/after_section_start', [$this,'breakpoints_settings'], 10);
		add_action( 'elementor/element/column/layout/before_section_end', [$this,'layout_settings'], 10);
		add_action( 'elementor/element/column/section_style/before_section_end', [$this,'background_settings'], 10);
		add_action( 'elementor/element/column/section_background_overlay/before_section_end', [$this,'video_bg_overlay_settings'], 10);
		add_action( 'elementor/element/column/section_effects/before_section_end', [$this,'effects_settings'], 10);
		add_action( 'elementor/element/column/section_advanced/before_section_end', [$this,'section_advanced'], 10);
		add_action( 'elementor/frontend/column/before_render', [$this,'before_render'], 10);
		add_action( 'elementor/frontend/column/after_render', [$this,'after_render'], 10);
		add_filter( 'elementor/column/print_template', [$this,'print_template'], 10 );
		add_action( 'elementor/element/column/_section_responsive/after_section_end', [$this, 'custom_css_settings']);
	}

	/**
	 * Add custom settings into Elementor's Columns
	 *
	 * @since 1.0.0
	 */
	function breakpoints_settings( $element ){

		if( ! isset(\Elementor\Plugin::$instance->breakpoints) ){
			return;
		}

		$element->add_control(
			'section_rey_flex_wrap',
			[
				'label' => __( 'Parent Section Flex Wrap', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HIDDEN,
				'default' => '',
			]
		);

		$active_breakpoint_keys = array_reverse( array_keys( \Elementor\Plugin::$instance->breakpoints->get_active_breakpoints() ) );
		$inline_size_device_args = [
			Breakpoints_Manager::BREAKPOINT_KEY_MOBILE => [ 'placeholder' => 100 ],
		];

		foreach ( $active_breakpoint_keys as $breakpoint_key ) {
			if ( ! isset( $inline_size_device_args[ $breakpoint_key ] ) ) {
				$inline_size_device_args[ $breakpoint_key ] = [];
			}

			$inline_size_device_args[ $breakpoint_key ] = array_merge_recursive(
				$inline_size_device_args[ $breakpoint_key ],
				[
					'max' => 100,
					'required' => false,
				]
			);
		}

		if ( in_array( Breakpoints_Manager::BREAKPOINT_KEY_MOBILE_EXTRA, $active_breakpoint_keys, true ) ) {
			$min_affected_device_value = Breakpoints_Manager::BREAKPOINT_KEY_MOBILE_EXTRA;
		} else {
			$min_affected_device_value = Breakpoints_Manager::BREAKPOINT_KEY_TABLET;
		}

		$element->add_responsive_control(
			'rey_flex_wrap_inline_size',
			[
				'label' => __( 'Column Width', 'rey-core' ) . ' (%)' ,
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 2,
				'max' => 100,
				'step' => 1,
				// 'required' => true,
				'device_args' => $inline_size_device_args,
				'min_affected_device' => [
					Breakpoints_Manager::BREAKPOINT_KEY_DESKTOP => $min_affected_device_value,
					Breakpoints_Manager::BREAKPOINT_KEY_LAPTOP => $min_affected_device_value,
					Breakpoints_Manager::BREAKPOINT_KEY_TABLET_EXTRA => $min_affected_device_value,
					Breakpoints_Manager::BREAKPOINT_KEY_TABLET => $min_affected_device_value,
					Breakpoints_Manager::BREAKPOINT_KEY_MOBILE_EXTRA => $min_affected_device_value,
				],
				'selectors' => [
					'{{WRAPPER}}' => 'width: {{VALUE}}%',
				],
				'description' => __( 'This option will force a custom column size. Unlike the native option, this doesn\'t get recalculated, allowing columns to display like rows.', 'rey-core' ),
				'condition' => [
					'section_rey_flex_wrap!' => '',
				],
			]
		);


	}

	function layout_settings( $element )
	{

		$control_manager = \Elementor\Plugin::$instance->controls_manager;

		foreach ( \ReyCore\Elementor\Helper::get_breakpoints() as $key => $bp) {
			$item = [];
			$item[$key] = $control_manager->get_control_from_stack( $element->get_unique_name(), '_inline_size' . $bp );
			if( ! is_wp_error($item[$key]) && is_array($item[$key]) ){
				$item[$key]['condition']['section_rey_flex_wrap'] = '';
				$element->update_control( '_inline_size' . $bp, $item[$key] );
			}
		}

		$element->add_responsive_control(
			'rey_custom_height',
			[
				'label' => __( 'Minimum Height', 'rey-core' ) . \ReyCore\Elementor\Helper::rey_badge(),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'default' => [
					'size' => '',
				],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1440,
					],
					'vh' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'size_units' => [ 'px', 'vh' ],
				'selectors' => [
					// v2
					'{{WRAPPER}} > .elementor-column-wrap' => 'min-height: {{SIZE}}{{UNIT}};',
					// v3
					'{{WRAPPER}} > .elementor-widget-wrap' => 'min-height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$element->add_responsive_control(
			'rey_custom_width',
			[
				'label' => __( 'Custom Width', 'rey-core' ) . \ReyCore\Elementor\Helper::rey_badge(),
				'description' => __( 'Customize the width as you want to, it\'ll overwrite the default percent value. Use pixel value, calc(), auto or whatever.', 'rey-core' ),
				'placeholder' => __( 'eg: calc(100% - 300px)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'selectors' => [
					'(desktop+){{WRAPPER}}' => 'width: {{VALUE}};',
				],
				'device_args' => [
					\Elementor\Controls_Stack::RESPONSIVE_TABLET => [
						'selectors' => [
							'{{WRAPPER}}' => 'width: {{VALUE}};',
						],
					],
					\Elementor\Controls_Stack::RESPONSIVE_MOBILE => [
						'selectors' => [
							'{{WRAPPER}}' => 'width: {{VALUE}};',
						],
					],
				],
			]
		);

		$element->add_responsive_control(
			'rey_col_order',
			[
				'label' => __( 'Column Order', 'rey-core' ) . \ReyCore\Elementor\Helper::rey_badge(),
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

		$element->add_control(
			'rey_link',
			[
				'label'       => __( 'Column Link', 'rey-core' ) . \ReyCore\Elementor\Helper::rey_badge(),
				'type'        => \Elementor\Controls_Manager::URL,
				'dynamic'     => [
					'active' => true,
				],
				'placeholder' => __( 'https://your-link.com', 'rey-core' ),
			]
		);

	}


	/**
	 * Add video BG
	 *
	 * @since 1.0.0
	 */
	function background_settings( $element )
	{
		$control_manager = \Elementor\Plugin::instance()->controls_manager;

		if( \ReyCore\Elementor\Helper::get_compatibilities('column_video') )
		{
			// extract background args
			// group control is not available, so only get main bg control
			$bg = $control_manager->get_control_from_stack( $element->get_unique_name(), 'background_background' );
			if( $bg && ! is_wp_error($bg) ){
				// add new condition, for REY video background
				$bg['options']['video'] = [
					'title' => _x( 'Background Video', 'Background Control', 'rey-core' ),
					'icon' => 'eicon-video-playlist',
				];
				$bg['prefix_class'] = 'rey-colbg--';
				$element->update_control( 'background_background', $bg );
			}

			/*
			BG hover color var.
			Maybe needed in the futuere
			$bg_hover_color = $control_manager->get_control_from_stack( $element->get_unique_name(), 'background_hover_color' );
			$bg_hover_color['selectors']['{{WRAPPER}}'] = '--col-hover-bg-color: {{VALUE}}';
			$element->update_control( 'background_hover_color', $bg_hover_color );
			*/

			// remove options
			if( ! \ReyCore\Elementor\Helper::get_compatibilities('video_bg_play_on_mobile') ){
				$element->remove_control('background_play_on_mobile');
			}

			$element->start_injection( [
				'of' => 'background_play_on_mobile',
			] );

			$element->add_control(
				'rey_bg_video_lazy',
				[
					'label' => esc_html__( 'Lazy load video', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'condition' => [
						'background_background' => 'video',
					],
				]
			);

			$element->end_injection();
		}

		$element->add_control(
			'rey_bg_disable_mobile',
			[
				'label' => esc_html__( 'Disable image on mobiles', 'rey-core' ) .  \ReyCore\Elementor\Helper::rey_badge(),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [
					'background_background' => 'classic',
				],
				'prefix_class' => '--no-mobile-bg-'
			]
		);

	}


	/**
	 * Update the conditions of the background overlay section,
	 * to apply for rey_video as well.
	 */
	function video_bg_overlay_settings( $stack )
	{
		if( ! \ReyCore\Elementor\Helper::get_compatibilities('column_video') ) {
			return;
		}

		// Skip if optimized control loading is active because it's not working properly
		// It affects containers, various background etc.
		// @since 3.0
		if( \ReyCore\Elementor\Helper::is_experiment_active('e_optimized_control_loading') ){
			return;
		}

		$section = \Elementor\Plugin::instance()->controls_manager->get_control_from_stack( $stack->get_unique_name(), 'section_background_overlay' );

		if( is_wp_error($section) ){
			return;
		}

		// pass custom condition
		$section['condition']['background_background'][] = 'video';

		// update section
		// $stack->update_control( 'section_background_overlay', $section );

	}


	/**
	 * Add custom settings into Elementor's Column
	 *
	 * @since 1.0.0
	 */
	function effects_settings( $element )
	{


		$element->add_control(
			'rey_sticky',
			[
				'label' => __( 'Sticky', 'rey-core' ) . \ReyCore\Elementor\Helper::rey_badge(),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default' => '',
				'render_type' => 'none',
				'separator' => 'before',
			]
		);

		$element->add_control(
			'rey_sticky_offset',
			[
				'label' => __( 'Sticky Offset', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 1000,
				'step' => 1,
				'condition' => [
					'rey_sticky' => ['yes'],
				],
				'selectors' => [
					'{{WRAPPER}}' => '--sticky-offset: {{VALUE}}px;'
				]
			]
		);

		do_action('reycore/elementor/column/controls/after_effects', $element);

	}

	/**
	 * Tweak the CSS classes field.
	 */
	function section_advanced( $stack )
	{
		$controls_manager = \Elementor\Plugin::instance()->controls_manager;
		$unique_name = $stack->get_unique_name();

		// Zindex
		$z_index = $controls_manager->get_control_from_stack( $unique_name, 'z_index' );
		if( $z_index && ! is_wp_error($z_index) ){
			$z_index['prefix_class'] = '--zindexed-';
			$stack->update_control( 'z_index', $z_index );
		}

		// get args
		$css_classes = $controls_manager->get_control_from_stack( $unique_name, 'css_classes' );
		if( $css_classes && ! is_wp_error($css_classes) ){
			$css_classes['label_block'] = true;
			$stack->update_control( 'css_classes', $css_classes );
		}

		$stack->add_control(
			'rey_utility_classes',
			[
				'label' => esc_html__( 'Utility Classes', 'rey-core' ) . \ReyCore\Elementor\Helper::rey_badge(),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => esc_html__( '- None -', 'rey-core' ),
					'column-flex-dir--vertical'  => esc_html__( 'Flex direction - Column', 'rey-core' ),
					'column-stretch-right'  => esc_html__( 'Stretch column to right window edge', 'rey-core' ),
					'column-stretch-left'  => esc_html__( 'Stretch column to left window edge', 'rey-core' ),
					'u-topDeco-splitLine'  => esc_html__( 'Border decoration', 'rey-core' ),
					'u-ov-hidden'  => esc_html__( 'Overflow Hidden', 'rey-core' ),
					'm-auto--top'  => esc_html__( 'Margin Top - Auto', 'rey-core' ),
					'm-auto--bottom'  => esc_html__( 'Margin Bottom - Auto', 'rey-core' ),
				],
				'prefix_class' => ''
			]
		);
	}


	/**
	 * Render some attributes before rendering
	 *
	 * @since 1.0.0
	 **/
	function before_render( $element )
	{
		$settings = $element->get_settings_for_display();
		$el_id = $element->get_id();

		$wrapper_attribute_string = !\ReyCore\Elementor\Helper::is_optimized_dom() ? '_inner_wrapper' : '_widget_wrapper';

		$element->add_render_attribute( $wrapper_attribute_string, 'class', ['elementor-column-wrap--' . $el_id ] );

		static $css;

		if( 'yes' === $settings['rey_sticky'] ):

			$classes = ['--sticky-col'];

			reycore_assets()->add_styles('reycore-elementor-column-sticky');

			if( $sticky_col = apply_filters('reycore/elementor/sticky/css_first', true) ){
				$classes[] = '--css-first';
			}

			else {

				$classes[] = '--js-first';

				if( $settings['rey_sticky_offset'] ){
					$element->add_render_attribute( '_wrapper', 'data-top-offset', $settings['rey_sticky_offset'] );
				}

				reycore_assets()->add_scripts(['reycore-sticky', 'reycore-elementor-elem-column-sticky']);
			}

			$element->add_render_attribute( '_wrapper', 'class', $classes );

		endif;

		if ( isset( $settings['rey_link']['url'] ) && ! empty( $settings['rey_link']['url'] ) ) {

			$column_link['url'] = esc_url($settings['rey_link']['url']);
			$column_link['target'] = $settings['rey_link']['is_external'] ? '_blank' : '_self';

			$element->add_render_attribute( '_wrapper', 'data-column-link', wp_json_encode($column_link) );

			reycore_assets()->add_scripts('reycore-elementor-elem-column-click');
		}

		if( isset($settings['css_classes']) && $css_classes = $settings['css_classes'] ){
			if( strpos($css_classes, 'u-topDeco-splitLine') !== false ){
				reycore_assets()->add_styles('reycore-elementor-column-topdeco');
			}
		}

		// Video
		if( \ReyCore\Elementor\Helper::get_compatibilities('column_video') && 'video' === $settings['background_background'] && $video_link = $settings['background_video_link'] ):

			reycore_assets()->add_scripts(['rey-videos', 'reycore-elementor-elem-column-video']);

			// Catch output
			ob_start();

		endif;


	}


	/**
	 * Inject Video HTML Markup
	 *
	 * @since 1.0.0
	 **/
	function after_render( $element )
	{
		$settings = $element->get_settings_for_display();

		if ( \ReyCore\Elementor\Helper::get_compatibilities('column_video') &&
			'video' === $settings['background_background'] && $video_link = $settings['background_video_link'] ) :

			$video_properties = \Elementor\Embed::get_video_properties( $video_link );

			$video_html = '';

			$css_classes = [
				'rey-background-video-container'
			];

			reycore_assets()->add_styles('reycore-elementor-bg-video-container');

			if( $settings['rey_bg_video_lazy'] !== '' ){
				$css_classes[] = '--lazy-video';
			}

			if( isset($video_properties['provider']) && 'youtube' === $video_properties['provider'] ){
				$video_html = reycore__get_youtube_iframe_html([
					'class' => implode(' ', $css_classes),
					'video_id' => $video_properties['video_id'],
					'html_id' => 'yt' . $element->get_id(),
					'add_preview_image' => false,
					'mobile' => isset($settings['background_play_on_mobile']) && $settings['background_play_on_mobile'] === 'yes',
					'params' => [
						'start' => $settings['background_video_start'],
						'end' => $settings['background_video_end'],
						'loop' => $settings['background_play_once'] === '' ? 1 : 0,
					],
				]);
			}
			else {
				$video_html = reycore__get_video_html([
					'class' => implode(' ', $css_classes),
					'video_url' => $video_link,
					'start' => $settings['background_video_start'],
					'end' => $settings['background_video_end'],
					'mobile' => isset($settings['background_play_on_mobile']) && $settings['background_play_on_mobile'] === 'yes',
					'params' => [
						'loop' => $settings['background_play_once'] === '' ? 'loop' : '',
					],
				]);
			}

			// Collect output
			$content = ob_get_clean();

			$query = '//div[contains(@class,"elementor-column-wrap--'. $element->get_id() .'")]';

			if( $new_html = \ReyCore\Elementor\Helper::el_inject_html( $content, $video_html,  $query) ){
				$content = $new_html;
			}

			echo $content;

			reycore_assets()->add_scripts('rey-videos');

		endif;

	}


	/**
		* Filter Columns Print Content
		*
		* @since 1.0.0
		**/
	function print_template( $template )
	{
		if( \ReyCore\Elementor\Helper::get_compatibilities('column_video') ){

			reycore_assets()->add_scripts('rey-videos');

			$old_template = '<div class="elementor-background-overlay"></div>';

			$new_template = '
			<# if ( settings.background_video_link ) {
				var model = view.getEditModel();
				var play_once_yt = settings.background_play_once === "" ? 1 : 0;
				var play_once_hosted = settings.background_play_once === "" ? "loop" : ""; #>';

				$new_template .= reycore__get_youtube_iframe_html([
					'video_id' => '{{{ settings.background_video_link }}}',
					'class' => 'rey-background-video-container',
					'html_id' => 'yt{{{model.id}}}',
					'params' => [
						'start' => '{{{settings.background_video_start}}}',
						'end' => '{{{settings.background_video_end}}}',
						'loop' => '{{{play_once_yt}}}',
					],
				]);

				$new_template .= reycore__get_video_html([
					'video_url' => '{{{ settings.background_video_link }}}',
					'class' => 'rey-background-video-container',
					'params' => [
						'loop' => '{{{play_once_hosted}}}',
					],
					'start' => '{{{settings.background_video_start}}}',
					'end' => '{{{settings.background_video_end}}}',
				]);

			$new_template .= '<# } #>';

			$new_template .= '<div class="elementor-background-overlay"></div>';

			return str_replace( $old_template, $new_template, $template );
		}
		return $template;
	}

	public function custom_css_settings( $element ){
		\ReyCore\Elementor\WidgetsOverrides::custom_css_controls($element);
	}

}
