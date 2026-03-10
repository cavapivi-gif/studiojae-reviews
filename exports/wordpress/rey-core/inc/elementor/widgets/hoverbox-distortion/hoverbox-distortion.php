<?php
namespace ReyCore\Elementor\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class HoverboxDistortion extends \ReyCore\Elementor\WidgetsBase {

	private $_settings = [];
	private $_images = [];

	public $is_css = false;

	public $link_type = '';
	public $wrapper_tag = 'div';
	public $button_tag = 'a';

	public static function get_rey_config(){
		return [
			'id' => 'hoverbox-distortion',
			'title' => __( 'HoverBox', 'rey-core' ),
			'icon' => 'eicon-image-rollover',
			'categories' => [ 'rey-theme' ],
			'keywords' => [],
			'css' => [
				'assets/style[rtl].css',
			],
			'js' => [
				'assets/script.js',
			],
		];
	}

	public function rey_get_script_depends() {
		return [ 'threejs', 'distortion-app', 'animejs', 'reycore-widget-hoverbox-distortion-scripts' ];
	}

	// public function get_custom_help_url() {
	// 	return reycore__support_url('kb/rey-elements-covers/#skew-cover');
	// }

	/**
	 * Register widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Image', 'rey-core' ),
			]
		);

			$this->add_control(
				'active_image',
				[
				'label' => esc_html__( 'Active Image', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::MEDIA,
					'default' => [
						'url' => \Elementor\Utils::get_placeholder_image_src(),
					],
				]
			);


			$this->add_control(
				'active_overlay_color',
				[
					'label' => esc_html__( 'Overlay Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'default' => 'rgba(0,0,0,0.3)',
					'selectors' => [
						'{{WRAPPER}} .__imagesBg' => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'blend_overlay',
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
						'color-burn' => 'Color Burn',
						'hue' => 'Hue',
						'saturation' => 'Saturation',
						'color' => 'Color',
						'exclusion' => 'Exclusion',
						'luminosity' => 'Luminosity',
					],
					'default' => '',
					'selectors' => [
						'{{WRAPPER}} .__imagesBg' => 'mix-blend-mode: {{VALUE}}',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Image_Size::get_type(),
				[
					'name' => 'image', // Usage: `{name}_size` and `{name}_custom_dimension`, in this case `image_size` and `image_custom_dimension`.
					'default' => 'large',
					'separator' => 'before',
				]
			);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_content_hover',
			[
				'label' => __( 'Hover Image', 'rey-core' ),
			]
		);

			$this->add_control(
				'hover_image',
				[
				'label' => esc_html__( 'Hover Image (Optional)', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::MEDIA,
					'default' => [
						'url' => \Elementor\Utils::get_placeholder_image_src(),
					],
				]
			);

			$this->add_control(
				'hover_overlay_color',
				[
					'label' => esc_html__( 'Overlay Color on Hover', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'default' => '',
					'selectors' => [
						'{{WRAPPER}}:hover .__imagesBg' => 'background-color: {{VALUE}}',
					],
				]
			);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_content_main',
			[
				'label' => __( 'Content', 'rey-core' ),
			]
		);

			$this->add_control(
				'top_caption_settings_title',
				[
				   'label' => esc_html__( 'TOP CAPTION', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_control(
				'top_text_visibility',
				[
					'label' => esc_html__( 'Top Caption Visibility', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'always',
					'options' => [
						'always'  => esc_html__( 'Always', 'rey-core' ),
						'show'  => esc_html__( 'Show On Hover', 'rey-core' ),
						'hide'  => esc_html__( 'Hide On Hover', 'rey-core' ),
					],
				]
			);

			$this->add_control(
				'top_text',
				[
					'label' => esc_html__( 'Top Caption', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXTAREA,
					'label_block' => true,
					'default' => '',
					'placeholder' => esc_html__( 'eg: Some text', 'rey-core' ),
				]
			);


			$this->add_control(
				'caption_settings_title',
				[
				   'label' => esc_html__( 'CAPTIONS', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_control(
				'captions_visibility',
				[
					'label' => esc_html__( 'Captions Visibility', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'show',
					'options' => [
						'always'  => esc_html__( 'Always', 'rey-core' ),
						'show'  => esc_html__( 'Show On Hover', 'rey-core' ),
						'hide'  => esc_html__( 'Hide On Hover', 'rey-core' ),
					],
				]
			);

			$this->add_control(
				'text_1',
				[
					'label' => esc_html__( 'Caption Title', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'label_block' => true,
					'default' => '',
					'placeholder' => esc_html__( 'eg: Cool title', 'rey-core' ),
				]
			);

			$this->add_control(
				'text_2',
				[
					'label' => esc_html__( 'Caption Subtitle', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'label_block' => true,
					'default' => '',
					'placeholder' => esc_html__( 'eg: Just a subtitle text.', 'rey-core' ),
				]
			);

			$this->add_control(
				'button_settings_title',
				[
				   'label' => esc_html__( 'BUTTON', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_control(
				'link_type',
				[
					'label' => esc_html__( 'Link type', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'btn',
					'options' => [
						'btn'  => esc_html__( 'Show button', 'rey-core' ),
						'wrap'  => esc_html__( 'Wrap element in link', 'rey-core' ),
						'wrap-btn'  => esc_html__( 'Wrap element in link & Show button', 'rey-core' ),
					],
				]
			);

			$this->add_control(
				'button_text',
				[
					'label' => esc_html__( 'Button Text', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => esc_html__( 'CLICK HERE', 'rey-core' ),
					'placeholder' => esc_html__( 'some placeolder', 'rey-core' ),
					'condition' => [
						'link_type!' => 'wrap',
					],
				]
			);

			$this->add_control(
				'button_url',
				[
					'label' => __( 'Button URL', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::URL,
					'dynamic' => [
						'active' => true,
					],
					'placeholder' => __( 'https://your-link.com', 'rey-core' ),
					'default' => [
						'url' => '#',
					],
				]
			);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_transition_settings',
			[
				'label' => __( 'Transition Settings', 'rey-core' ),
			]
		);

		$this->add_control(
			'transition_group',
			[
				'label' => esc_html__( 'Transition Types', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'css',
				'options' => [
					'css'  => esc_html__( 'CSS Transition', 'rey-core' ),
					'js'  => esc_html__( 'JS Transitions', 'rey-core' ),
				],
			]
		);

		// CSS

		$this->add_control(
			'css_transition_type',
			[
				'label' => esc_html__( 'Transition Effect', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => esc_html__( 'None', 'rey-core' ),
					'fade-in'  => esc_html__( 'Fade In', 'rey-core' ),
					'fade-out'  => esc_html__( 'Fade Out', 'rey-core' ),
					'scale-in'  => esc_html__( 'Scale In', 'rey-core' ),
					'scale-out'  => esc_html__( 'Scale Out', 'rey-core' ),
					'clip-in'  => esc_html__( 'Clip In', 'rey-core' ),
					'clip-out'  => esc_html__( 'Clip Out', 'rey-core' ),
					// 'curtain'  => esc_html__( 'Curtain', 'rey-core' ),
				],
				'condition' => [
					'transition_group' => 'css',
				],
			]
		);

		$this->add_control(
			'css_transition_duration',
			[
				'label' => __( 'Transition Duration (ms)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 500,
				'max' => 3000,
				'step' => 50,
				'placeholder' => 500,
				'selectors' => [
					'{{WRAPPER}} .rey-hoverBox' => '--css-transition-duration: {{VALUE}}ms',
				],
				'condition' => [
					'transition_group' => 'css',
				],
			]
		);

		$this->add_control(
			'css_transition_blur',
			[
				'label' => __( 'Blur in Transitions', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 10,
				'step' => 1,
				'placeholder' => 5,
				'selectors' => [
					'{{WRAPPER}} .rey-hoverBox' => '--d-blur: {{VALUE}}px',
				],
				'condition' => [
					'transition_group' => 'css',
				],
			]
		);

		// JS

		$this->add_control(
			'transition_js_notice',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => '<em>'. __( 'JS Transitions are costly in terms of performance and may result in Google Page Speed Insights errors (ThreeJS/WebGL incompatibilities).', 'rey-core' ) .'</em>',
				'content_classes' => 'rey-raw-html --warning',
				'condition' => [
					'transition_group' => 'js',
				],
			]
		);

		$this->add_control(
			'transition_duration',
			[
				'label' => __( 'Transition Duration (ms)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 1600,
				'min' => 500,
				'max' => 10000,
				'step' => 50,
				'selectors' => [
					'{{WRAPPER}} .__imagesBg' => 'transition-duration: {{VALUE}}ms',
				],
				'condition' => [
					'transition_group' => 'js',
				],
			]
		);

		$this->add_control(
			'transition_type',
			[
				'label' => esc_html__( 'Transition Effect', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => esc_html__( 'Morph into next slide', 'rey-core' ),
					'random'  => esc_html__( 'Random', 'rey-core' ),
					'0'  => esc_html__( 'Effect #1', 'rey-core' ),
					'1'  => esc_html__( 'Effect #2', 'rey-core' ),
					'2'  => esc_html__( 'Effect #3', 'rey-core' ),
					'3'  => esc_html__( 'Effect #4', 'rey-core' ),
					'4'  => esc_html__( 'Effect #5', 'rey-core' ),
					'5'  => esc_html__( 'Effect #6', 'rey-core' ),
					'6'  => esc_html__( 'Effect #7', 'rey-core' ),
					'7'  => esc_html__( 'Effect #8', 'rey-core' ),
					'8'  => esc_html__( 'Effect #9', 'rey-core' ),
					'9'  => esc_html__( 'Effect #10', 'rey-core' ),
					'10'  => esc_html__( 'Effect #11', 'rey-core' ),
					'11'  => esc_html__( 'Effect #12', 'rey-core' ),
					'12'  => esc_html__( 'Effect #13', 'rey-core' ),
					'13'  => esc_html__( 'Effect #14', 'rey-core' ),
					'14'  => esc_html__( 'Effect #15', 'rey-core' ),
					'15'  => esc_html__( 'Effect #16', 'rey-core' ),
				],
				'condition' => [
					'transition_group' => 'js',
				],
			]
		);

		$this->add_control(
			'transition_intensity',
			[
				'label' => esc_html__( 'Transition Intensity', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 0.2,
				'min' => -1,
				'max' => 1,
				'step' => 0.05,
				'condition' => [
					'transition_group' => 'js',
				],
			]
		);

		$this->add_control(
			'transition_out_intensity',
			[
				'label' => esc_html__( 'OUT Transition Intensity', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => -1,
				'max' => 1,
				'step' => 0.05,
				'condition' => [
					'transition_group' => 'js',
				],
			]
		);

		$this->add_control(
			'transition_direction',
			[
				'label' => esc_html__( 'Vertical direction', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [
					'transition_group' => 'js',
				],
			]
		);

		$this->add_control(
			'transition_easing',
			[
				'label' => esc_html__( 'Transition Easing', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'easeInOutCubic',
				'options' => [
					'easeInQuad' => 'easeInQuad',
					'easeInCubic' => 'easeInCubic',
					'easeInQuart' => 'easeInQuart',
					'easeInQuint' => 'easeInQuint',
					'easeInSine' => 'easeInSine',
					'easeInExpo' => 'easeInExpo',
					'easeInCirc' => 'easeInCirc',
					'easeInOutQuad' => 'easeInOutQuad',
					'easeInOutCubic' => 'easeInOutCubic',
					'easeInOutQuart' => 'easeInOutQuart',
					'easeInOutQuint' => 'easeInOutQuint',
					'easeInOutSine' => 'easeInOutSine',
					'easeInOutExpo' => 'easeInOutExpo',
					'easeInOutCirc' => 'easeInOutCirc',
					'easeOutQuad' => 'easeOutQuad',
					'easeOutCubic' => 'easeOutCubic',
					'easeOutQuart' => 'easeOutQuart',
					'easeOutQuint' => 'easeOutQuint',
					'easeOutSine' => 'easeOutSine',
					'easeOutExpo' => 'easeOutExpo',
					'easeOutCirc' => 'easeOutCirc',
				],
				'condition' => [
					'transition_group' => 'js',
				],
			]
		);


		$this->end_controls_section();


		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);


			$this->add_responsive_control(
				'custom_height',
				[
					'label' => __( 'Element Height', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 1440,
						],
						'vh' => [
							'min' => 0,
							'max' => 100,
						],
						'vw' => [
							'min' => 0,
							'max' => 100,
						],
					],
					'size_units' => [ 'px', 'vh', 'vw' ],
					'selectors' => [
						'{{WRAPPER}} .rey-hoverBox' => '--height: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'inner_padding',
				[
					'label' => __( 'Inner Padding', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%' ],
					'selectors' => [
						'{{WRAPPER}} .__text' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_control(
				'predominant_color',
				[
					'label' => esc_html__( 'Predominant Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}}' => '--predominant-color: {{VALUE}}',
					],
					'separator' => 'before',
				]
			);

			$this->add_control(
				'predominant_hover_color',
				[
					'label' => esc_html__( 'Predominant Color on Hover', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}}' => '--predominant-hover-color: {{VALUE}}',
					],
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_top_caption',
			[
				'label' => __( 'Top Caption', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'top_text_typo',
					'label' => esc_html__('Typography', 'rey-core'),
					'selector' => '{{WRAPPER}} .__textTop',
				]
			);

			$this->add_control(
				'top_text_color',
				[
					'label' => esc_html__( 'Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .__textTop' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'top_move',
				[
					'label' => esc_html__( 'Move to bottom', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

			$this->add_control(
				'top_hide_on_mobile',
				[
					'label' => esc_html__( 'Hide on Mobile', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'label_on' => __( 'Hide', 'elementor' ),
					'label_off' => __( 'Show', 'elementor' ),
					'default' => '',
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_title_caption',
			[
				'label' => __( 'Title Caption', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'text1_typo',
					'label' => esc_html__('Typography', 'rey-core'),
					'selector' => '{{WRAPPER}} .__text1',
				]
			);

			$this->add_control(
				'title_text_color',
				[
					'label' => esc_html__( 'Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .__text1' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'title_hide_on_mobile',
				[
					'label' => esc_html__( 'Hide on Mobile', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'label_on' => __( 'Hide', 'elementor' ),
					'label_off' => __( 'Show', 'elementor' ),
					'default' => '',
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_subtitle_caption',
			[
				'label' => __( 'Subtitle Caption', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'text2_typo',
					'label' => esc_html__('Typography', 'rey-core'),
					'selector' => '{{WRAPPER}} .__text2',
				]
			);

			$this->add_control(
				'subtitle_text_color',
				[
					'label' => esc_html__( 'Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .__text2' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'subtitle_distance',
				[
					'label' => esc_html__( 'Subtitle Top Spacing', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 0,
					'selectors' => [
						'{{WRAPPER}} .__text2' => '--top-distance: {{VALUE}}px',
					],
				]
			);

			$this->add_control(
				'subtitle_hide_on_mobile',
				[
					'label' => esc_html__( 'Hide on Mobile', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'label_on' => __( 'Hide', 'elementor' ),
					'label_off' => __( 'Show', 'elementor' ),
					'default' => '',
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_button_caption',
			[
				'label' => __( 'Button', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'button',
					'label' => esc_html__('Button Typography', 'rey-core'),
					'selector' => '{{WRAPPER}} .rey-coverDistortion .__captions .rey-buttonSkew',
				]
			);

			$this->add_control(
				'button_style',
				[
					'label' => __( 'Button Style', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'btn-primary-outline btn-dash',
					'options' => [
						'btn-simple'  => __( 'Link', 'rey-core' ),
						'btn-primary'  => __( 'Primary', 'rey-core' ),
						'btn-secondary'  => __( 'Secondary', 'rey-core' ),
						'btn-primary-outline'  => __( 'Primary Outlined', 'rey-core' ),
						'btn-secondary-outline'  => __( 'Secondary Outlined', 'rey-core' ),
						'btn-line-active'  => __( 'Underlined', 'rey-core' ),
						'btn-line'  => __( 'Hover Underlined', 'rey-core' ),
						'btn-primary-outline btn-dash'  => __( 'Primary Outlined & Dash', 'rey-core' ),
						'btn-primary-outline btn-dash btn-rounded'  => __( 'Primary Outlined & Dash & Rounded', 'rey-core' ),
					],
				]
			);

			$this->add_control(
				'button_primary_color',
				[
					'label' => esc_html__( 'Primary Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .btn' => '--accent-text-color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'button_secondary_color',
				[
					'label' => esc_html__( 'Secondary Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .btn' => '--accent-color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'button_primary_hover_color',
				[
					'label' => esc_html__( 'Primary Hover Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .btn:hover' => '--accent-text-color: {{VALUE}}',
						'{{WRAPPER}} .btn' => '--accent-text-hover-color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'button_secondary_hover_color',
				[
					'label' => esc_html__( 'Secondary Hover Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .btn:hover' => '--accent-color: {{VALUE}}',
						'{{WRAPPER}} .btn' => '--accent-hover-color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'button_distance',
				[
					'label' => esc_html__( 'Button Top Spacing', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 0,
					'selectors' => [
						'{{WRAPPER}} .__btn' => '--top-distance: {{VALUE}}px',
					],
				]
			);

			$this->add_control(
				'button_hide_on_mobile',
				[
					'label' => esc_html__( 'Hide on Mobile', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'label_on' => __( 'Hide', 'elementor' ),
					'label_off' => __( 'Show', 'elementor' ),
					'default' => '',
				]
			);

		$this->end_controls_section();
	}


	public function render_start__css(){

		if( 'css' !== $this->_settings['transition_group'] ){
			return;
		}

		$this->is_css = true;

		$this->add_render_attribute( 'wrapper', 'data-css-tr', $this->_settings['css_transition_type']);
	}

	public function render_start__js(){

		if( 'js' !== $this->_settings['transition_group'] ){
			return;
		}

		reycore_assets()->add_scripts( $this->rey_get_script_depends() );

		$this->add_render_attribute( 'wrapper', 'data-box-settings', wp_json_encode([
			'transitionDuration' => ! empty($this->_settings['transition_duration']) ? $this->_settings['transition_duration'] : 1600,
			'effect'             => $this->_settings['transition_type'],
			'intensity1'         => $this->_settings['transition_intensity'],
			'intensity2'         => $this->_settings['transition_out_intensity'],
			'vertical'           => $this->_settings['transition_direction'] === 'yes',
			'easing'             => $this->_settings['transition_easing'],
		]) );

		$this->add_render_attribute( 'wrapper', 'data-images', wp_json_encode( $this->_images ) );

	}

	public function render_start(){

		if( isset($this->_settings['active_image']['id']) && $this->_settings['active_image']['id'] ){
			$this->_images[] = [
				'url' => reycore__get_attachment_image( [
					'image'      => $this->_settings['active_image'],
					'size'       => $this->_settings['image_size'],
					'key'        => 'image',
					'settings'   => $this->_settings,
					'return_url' => true
				] ),
				'id' => $this->_settings['active_image']['id'],
			];
		}

		if( isset($this->_settings['hover_image']['id']) && $this->_settings['hover_image']['id'] ){
			$this->_images[] = [
				'url' => reycore__get_attachment_image( [
					'image'      => $this->_settings['hover_image'],
					'size'       => $this->_settings['image_size'],
					'key'        => 'image',
					'settings'   => $this->_settings,
					'return_url' => true
				] ),
				'id' => $this->_settings['hover_image']['id'],
			];
		}

		$count = count($this->_images);

		$state = 'none';

		if( $count === 1 ){
			$state = 'one';
		}
		else if( $count === 2 ){
			$state = 'two';
		}

		$this->add_render_attribute( 'wrapper', [
			'class' => [
				'rey-hoverBox',
				$this->_settings['top_move'] !== '' ? '--top-move' : ''
			],
			'data-state' => $state
		]);

		$this->get_link_attributes();
		$this->render_start__css();
		$this->render_start__js();

		printf('<%s %s>', $this->wrapper_tag, $this->get_render_attribute_string( 'wrapper' ));
	}

	public function render_end(){
		printf('</%s>', $this->wrapper_tag);
	}

	public function render_images(){

		if( ! isset($this->_images[0]) ){
			return;
		} ?>

		<div class="__images">

			<?php
				printf(
					'<img src="%s" width="%s" height="%s" class="__img __img-active" alt="%s">',
					$this->_images[0]['url'][0],
					$this->_images[0]['url'][1],
					$this->_images[0]['url'][2],
					get_post_meta($this->_images[0]['id'], '_wp_attachment_image_alt', true)
				);

				if( isset($this->_images[1]) && $this->is_css ){
					printf(
						'<img src="%s" width="%s" height="%s" class="__img __img-hover" alt="%s">',
						$this->_images[1]['url'][0],
						$this->_images[1]['url'][1],
						$this->_images[1]['url'][2],
						get_post_meta($this->_images[0]['id'], '_wp_attachment_image_alt', true)
					);
				}
			?>
		</div>

		<div class="__imagesBg"></div>
		<?php
	}

	function render_text(){

		$captions_class = 'captions--' . $this->_settings['captions_visibility']; ?>

		<div class="__text">

			<?php if( $top_text = $this->_settings['top_text'] ):
				$top_classes[] = 'top-caption--' . $this->_settings['top_text_visibility'];
				$top_classes[] = $this->_settings['top_hide_on_mobile'] !== '' ? '--dnone-sm' : '';
				$top_tag = apply_filters('reycore/elementor/hoverbox/top_tag', 'h3');
				printf('<%3$s class="__textTop __hoverItem %s">%s</%3$s>', esc_attr(implode(' ', $top_classes)), $top_text, esc_html($top_tag));
			endif;

			if( $text_1 = $this->_settings['text_1'] ):
				$title_classes[] = $captions_class;
				$title_classes[] = $this->_settings['title_hide_on_mobile'] !== '' ? '--dnone-sm' : '';
				printf('<p class="__text1 __hoverItem %s">%s</p>', esc_attr(implode(' ', $title_classes)), $text_1 );
			endif;

			if( $text_2 = $this->_settings['text_2'] ):
				$subtitle_classes[] = $captions_class;
				$subtitle_classes[] = $this->_settings['subtitle_hide_on_mobile'] !== '' ? '--dnone-sm' : '';
				printf('<p class="__text2 __hoverItem %s">%s</p>', esc_attr(implode(' ', $subtitle_classes)), $text_2 );
			endif;

			if( ($button_text = $this->_settings['button_text']) && 'wrap' !== $this->link_type ):

				$button_classes[] = $captions_class;
				$button_classes[] = $this->_settings['button_hide_on_mobile'] !== '' ? '--dnone-sm' : '';

				printf('<div class="__btn __hoverItem %s">', esc_attr(implode(' ', $button_classes)) );

					reycore_assets()->add_styles('rey-buttons');

					$this->add_render_attribute( 'button', 'class', [ 'btn', $this->_settings['button_style'] ] );

					printf( '<%1$s %2$s>%3$s</%1$s>',
						$this->button_tag,
						$this->get_render_attribute_string('button'),
						$button_text ); ?>

				</div>
			<?php endif; ?>

		</div>
		<?php
	}

	public function get_link_attributes(){

		$this->link_type = $this->_settings['link_type'];

		if( 'btn' === $this->link_type ){
			$link_element = 'button';
		}
		else {
			$link_element = 'wrapper';
			$this->wrapper_tag = 'a';
			$this->button_tag = 'div';
		}

		// href, target & rel
		if( isset($this->_settings['button_url']['url']) && ($url = $this->_settings['button_url']['url']) ){
			$this->add_render_attribute( $link_element, 'href', $url );

			if( $this->_settings['button_url']['is_external'] ){
				$this->add_render_attribute( $link_element, 'target', '_blank' );
			}

			if( $this->_settings['button_url']['nofollow'] ){
				$this->add_render_attribute( $link_element, 'rel', 'nofollow' );
			}
		}
	}

	protected function render() {

		reycore_assets()->add_styles($this->get_style_name());

		$this->_settings = $this->get_settings_for_display();

		$this->render_start();
		$this->render_images();
		$this->render_text();
		$this->render_end();
	}

	/**
	 * Render widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function content_template() {}
}
