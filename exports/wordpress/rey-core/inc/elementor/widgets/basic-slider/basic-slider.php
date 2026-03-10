<?php
namespace ReyCore\Elementor\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class BasicSlider extends \ReyCore\Elementor\WidgetsBase {

	public $_settings = [];

	public static $defaults;

	public $slide_tag = [];

	public $_items;
	public $selectors;
	public $slider_components;
	public $gs;
	public $_slide;
	public $_key;

	public static function get_rey_config(){
		return [
			'id' => 'basic-slider',
			'title' => __( 'Slider', 'rey-core' ),
			'icon' => 'eicon-post-slider',
			'categories' => [ 'rey-theme' ],
			'keywords' => [ 'carousel', 'slider', 'slideshow', 'cover', 'gallery', 'basic' ],
			'css' => [
				'assets/style[rtl].css',
			],
			'js' => [
				'assets/script.js',
			],
		];
	}

	public function rey_get_script_depends() {
		return [ 'splidejs', 'rey-splide', 'reycore-widget-basic-slider-scripts' ];
	}

	// public function get_custom_help_url() {
	// 	return reycore__support_url('kb/rey-elements/#slider');
	// }

	protected function controls__slides(){

		$this->start_controls_section(
			'section_slides',
			[
				'label' => esc_html__( 'Slides', 'rey-core' ),
			]
		);

		$this->add_control(
			'source',
			[
				'label' => esc_html__( 'Data Source', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'custom',
				'options' => [
					'images'       => esc_html__( 'Images', 'rey-core' ),
					'custom'       => esc_html__( 'Custom content', 'rey-core' ),
					// 'acf_repeater' => esc_html__( 'ACF Repeater', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'images',
			[
				'label' => esc_html__( 'Add Images', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::GALLERY,
				'default' => [],
				'show_label' => false,
				'dynamic' => [
					'active' => true,
				],
				'condition' => [
					'source' => 'images',
				],
			]
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'gs',
			[
				'label_block' => true,
				'label'       => __( 'Use Global section', 'rey-core' ),
				'type'        => 'rey-query',
				'default'     => '',
				'placeholder' => esc_html__('- Select -', 'rey-core'),
				'query_args'  => [
					'type'      => 'posts',
					'post_type' => \ReyCore\Elementor\GlobalSections::POST_TYPE,
					'meta'      => [
						'meta_key'   => 'gs_type',
						'meta_value' => 'generic',
					],
					'edit_link' => true,
				],
			]
		);

		$repeater->start_controls_tabs( 'slides_repeater', [
			'conditions' => [
				'terms' => [
					[
						'name' => 'gs',
						'operator' => '==',
						'value' => '',
					],
				],
			],
		] );

		$repeater->start_controls_tab( 'background', [ 'label' => esc_html__( 'Background', 'rey-core' ) ] );

		$repeater->add_control(
			'background_color',
			[
				'label' => esc_html__( 'Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#78909c',
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}}' => '--bg-color: {{VALUE}}',
				],
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$repeater->add_responsive_control(
			'image',
			[
				'label' => _x( 'Image', 'Background Control', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::MEDIA,
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$repeater->add_control(
			'fit',
			[
				'label' => _x( 'Size', 'Background Control', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'cover',
				'options' => [
					'cover' => _x( 'Cover', 'Background Control', 'rey-core' ),
					'contain' => _x( 'Contain', 'Background Control', 'rey-core' ),
					'auto' => _x( 'Auto', 'Background Control', 'rey-core' ),
				],
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}}' => '--bg-size: {{VALUE}}',
				],
				'conditions' => [
					'terms' => [
						[
							'name' => 'image[url]',
							'operator' => '!=',
							'value' => '',
						],
					],
				],
			]
		);

		$repeater->add_control(
			'cover_position',
			[
				'label' => _x( 'Cover Position', 'Background Control', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '50% 50%',
				'placeholder' => 'X Y (eg: 50% 50%)',
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}}' => '--bg-size-position: {{VALUE}}',
				],
				'conditions' => [
					'terms' => [
						[
							'name' => 'image[url]',
							'operator' => '!=',
							'value' => '',
						],
						[
							'name' => 'fit',
							'operator' => '==',
							'value' => 'cover',
						],
					],
				],
			]
		);

		$repeater->add_control(
			'ken_burns',
			[
				'separator' => 'before',
				'label' => esc_html__( 'Ken Burns Effect', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					'' => esc_html__( '- Disabled -', 'rey-core' ),
					'in' => esc_html__( 'In', 'rey-core' ),
					'out' => esc_html__( 'Out', 'rey-core' ),
				],
				'conditions' => [
					'terms' => [
						[
							'name' => 'image[url]',
							'operator' => '!=',
							'value' => '',
						],
					],
				],
			]
		);

		$repeater->add_control(
			'overlay',
			[
				'label' => esc_html__( 'Background Overlay', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'separator' => 'before',
				'conditions' => [
					'terms' => [
						[
							'name' => 'image[url]',
							'operator' => '!=',
							'value' => '',
						],
					],
				],
			]
		);

		$repeater->add_control(
			'overlay_color',
			[
				'label' => esc_html__( 'Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => 'rgba(0,0,0,0.5)',
				'conditions' => [
					'terms' => [
						[
							'name' => 'overlay',
							'value' => 'yes',
						],
					],
				],
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} .__overlay' => 'background-color: {{VALUE}}',
				],
			]
		);

		$repeater->add_control(
			'overlay_blend_mode',
			[
				'label' => esc_html__( 'Blend Mode', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'' => esc_html__( 'Normal', 'rey-core' ),
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
				'conditions' => [
					'terms' => [
						[
							'name' => 'overlay',
							'value' => 'yes',
						],
					],
				],
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} .__overlay' => 'mix-blend-mode: {{VALUE}}',
				],
			]
		);

		$repeater->end_controls_tab();

		$repeater->start_controls_tab( 'content', [ 'label' => esc_html__( 'Content', 'rey-core' ) ] );

		$repeater->add_control(
			'title',
			[
				'label' => esc_html__( 'Title', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Slide Heading', 'rey-core' ),
				'label_block' => true,
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$repeater->add_control(
			'subtitle',
			[
				'label' => esc_html__( 'Subtitle', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXTAREA,
				'default' => esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'rey-core' ),
				'show_label' => false,
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$repeater->add_control(
			'button_text',
			[
				'label' => esc_html__( 'Button Text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Click Here', 'rey-core' ),
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$repeater->add_control(
			'link',
			[
				'label' => esc_html__( 'Link', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://your-link.com', 'rey-core' ),
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$repeater->add_control(
			'link_click',
			[
				'label' => esc_html__( 'Apply Link On', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'slide' => esc_html__( 'Whole Slide', 'rey-core' ),
					'button' => esc_html__( 'Button Only', 'rey-core' ),
				],
				'default' => 'slide',
				'conditions' => [
					'terms' => [
						[
							'name' => 'link[url]',
							'operator' => '!=',
							'value' => '',
						],
					],
				],
			]
		);

		$repeater->end_controls_tab();

		$repeater->start_controls_tab( 'style', [ 'label' => esc_html__( 'Style', 'rey-core' ) ] );

		$repeater->add_control(
			'custom_style',
			[
				'label' => esc_html__( 'Custom', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'description' => esc_html__( 'Set custom style that will only affect this specific slide.', 'rey-core' ),
			]
		);

		$repeater->add_control(
			'horizontal_position',
			[
				'label' => esc_html__( 'Horizontal Position', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'flex-start' => [
						'title' => esc_html__( 'Left', 'rey-core' ),
						'icon' => 'eicon-h-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'rey-core' ),
						'icon' => 'eicon-h-align-center',
					],
					'flex-end' => [
						'title' => esc_html__( 'Right', 'rey-core' ),
						'icon' => 'eicon-h-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}}.__slide' => 'justify-content:{{VALUE}}',
				],
				'conditions' => [
					'terms' => [
						[
							'name' => 'custom_style',
							'value' => 'yes',
						],
					],
				],
			]
		);

		$repeater->add_control(
			'vertical_position',
			[
				'label' => esc_html__( 'Vertical Position', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'flex-start' => [
						'title' => esc_html__( 'Top', 'rey-core' ),
						'icon' => 'eicon-v-align-top',
					],
					'center' => [
						'title' => esc_html__( 'Middle', 'rey-core' ),
						'icon' => 'eicon-v-align-middle',
					],
					'flex-end' => [
						'title' => esc_html__( 'Bottom', 'rey-core' ),
						'icon' => 'eicon-v-align-bottom',
					],
				],
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} .__caption' => 'align-self: {{VALUE}}',
				],
				'conditions' => [
					'terms' => [
						[
							'name' => 'custom_style',
							'value' => 'yes',
						],
					],
				],
			]
		);

		$repeater->add_control(
			'text_align',
			[
				'label' => esc_html__( 'Text Align', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'start' => [
						'title' => esc_html__( 'Left', 'rey-core' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'rey-core' ),
						'icon' => 'eicon-text-align-center',
					],
					'end' => [
						'title' => esc_html__( 'Right', 'rey-core' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} .__caption' => 'text-align: {{VALUE}}',
				],
				'conditions' => [
					'terms' => [
						[
							'name' => 'custom_style',
							'value' => 'yes',
						],
					],
				],
			]
		);

		$repeater->add_control(
			'content_color',
			[
				'label' => esc_html__( 'Content Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					// '{{WRAPPER}} {{CURRENT_ITEM}}' => 'color: {{VALUE}}',
					'{{WRAPPER}} {{CURRENT_ITEM}} .__title' => 'color: {{VALUE}}',
					'{{WRAPPER}} {{CURRENT_ITEM}} .__subtitle' => 'color: {{VALUE}}',
					'{{WRAPPER}} {{CURRENT_ITEM}} .__button' => 'color: {{VALUE}}; border-color: {{VALUE}}',
				],
				'conditions' => [
					'terms' => [
						[
							'name' => 'custom_style',
							'value' => 'yes',
						],
					],
				],
			]
		);

		$repeater->add_group_control(
			\Elementor\Group_Control_Text_Shadow::get_type(),
			[
				'name' => 'text_shadow',
				'selector' => '{{WRAPPER}} {{CURRENT_ITEM}} .__caption',
				'conditions' => [
					'terms' => [
						[
							'name' => 'custom_style',
							'value' => 'yes',
						],
					],
				],
			]
		);

		$repeater->add_control(
			'button_style',
			[
				'label' => __( 'Button Style', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => __( '- Inherit -', 'rey-core' ),
					'btn-simple'  => __( 'Link', 'rey-core' ),
					'btn-primary'  => __( 'Primary', 'rey-core' ),
					'btn-secondary'  => __( 'Secondary', 'rey-core' ),
					'btn-primary-outline'  => __( 'Primary Outlined', 'rey-core' ),
					'btn-secondary-outline'  => __( 'Secondary Outlined', 'rey-core' ),
					'btn-line-active'  => __( 'Underlined', 'rey-core' ),
					'btn-line'  => __( 'Hover Underlined', 'rey-core' ),
					'btn-primary-outline btn-dash'  => __( 'Primary Outlined & Dash', 'rey-core' ),
					'btn-primary-outline btn-dash btn-rounded'  => __( 'Primary Outlined & Dash & Rounded', 'rey-core' ),
					'btn-dash-line'  => __( 'Dash', 'rey-core' ),
				],
				'condition' => [
					'button_show!' => 'no',
				],
			]
		);

		$repeater->end_controls_tab();

		$repeater->end_controls_tabs();

		$this->add_control(
			'slides',
			[
				'label' => esc_html__( 'Slides', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'show_label' => true,
				'fields' => $repeater->get_controls(),
				'default' => [
					[
						'title' => esc_html__( 'Slide 1 Heading', 'rey-core' ),
						'subtitle' => esc_html__( 'Lorem ipsum dolor sit amet consectetur adipiscing elit dolor', 'rey-core' ),
						'button_text' => esc_html__( 'Click Here', 'rey-core' ),
						'background_color' => '#78909c',
					],
					[
						'title' => esc_html__( 'Slide 2 Heading', 'rey-core' ),
						'subtitle' => esc_html__( 'Lorem ipsum dolor sit amet consectetur adipiscing elit dolor', 'rey-core' ),
						'button_text' => esc_html__( 'Click Here', 'rey-core' ),
						'background_color' => '#26a69a',
					],
					[
						'title' => esc_html__( 'Slide 3 Heading', 'rey-core' ),
						'subtitle' => esc_html__( 'Lorem ipsum dolor sit amet consectetur adipiscing elit dolor', 'rey-core' ),
						'button_text' => esc_html__( 'Click Here', 'rey-core' ),
						'background_color' => '#42a5f5',
					],
				],
				'title_field' => '{{{ title }}}',
				'condition' => [
					'source' => 'custom',
				],
			]
		);


		$this->add_responsive_control(
			'slides_height',
			[
				'label' => esc_html__( 'Height', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 100,
						'max' => 1000,
					],
					'vh' => [
						'min' => 10,
						'max' => 100,
					],
				],
				'default' => [
					'size' => 400,
				],
				'size_units' => [ 'px', 'vh', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .__slides' => '--height: {{SIZE}}{{UNIT}};',
				],
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Image_Size::get_type(),
			[
				'name' => 'image', // Usage: `{name}_size` and `{name}_custom_dimension`, in this case `image_size` and `image_custom_dimension`.
				'default' => 'large',
			]
		);

		$this->end_controls_section();

	}

	protected function controls__slider(){

		$this->start_controls_section(
			'section_slider_options',
			[
				'label' => esc_html__( 'Slider Options', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SECTION,
			]
		);

		$this->add_control(
			'autoplay',
			[
				'label' => esc_html__( 'Autoplay', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'autoplay_duration',
			[
				'label' => esc_html__( 'Autoplay Speed', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 5000,
				'condition' => [
					'autoplay!' => '',
				],
				'selectors' => [
					'{{WRAPPER}}' => '--autoplay-duration: {{VALUE}}ms',
				],
				'render_type' => 'none',
			]
		);

		$this->add_control(
			'pause_on_hover',
			[
				'label' => esc_html__( 'Pause on Hover', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
				'render_type' => 'none',
				'condition' => [
					'autoplay!' => '',
				],
			]
		);


		$this->add_control(
			'infinite',
			[
				'label' => esc_html__( 'Infinite Loop', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'transition',
			[
				'label' => esc_html__( 'Transition', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'slide',
				'options' => [
					'slide' => esc_html__( 'Slide', 'rey-core' ),
					'fade' => esc_html__( 'Fade', 'rey-core' ),
					'scaler' => esc_html__( 'Scale & Fade', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'direction',
			[
				'label' => esc_html__( 'Direction', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'ltr',
				'options' => [
					'ltr' => esc_html__( 'Left to right', 'rey-core' ),
					'rtl' => esc_html__( 'Right to left', 'rey-core' ),
					'ttb' => esc_html__( 'Top to bottom', 'rey-core' ),
				],
				'condition' => [
					'transition' => 'slide',
				],
			]
		);

		$this->add_control(
			'transition_speed',
			[
				'label' => esc_html__( 'Transition Speed', 'rey-core' ) . ' (ms)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 500,
				'render_type' => 'none',
			]
		);

		$this->add_control(
			'caption_animation',
			[
				'label' => esc_html__( 'Caption Animation', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'top',
				'options' => [
					'' => esc_html__( '- None -', 'rey-core' ),
					'top' => esc_html__( 'Slide from Top', 'rey-core' ),
					'left' => esc_html__( 'Slide from Left', 'rey-core' ),
					'fade' => esc_html__( 'Fade In/Out', 'rey-core' ),
				],
			]
		);

		\ReyCore\Libs\Slider_Components::controls_nav( $this );

		$this->add_control(
			'slider_id',
			[
				'label' => __( 'Slider Unique ID', 'rey-core' ),
				'label_block' => true,
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => uniqid('slider-'),
				'placeholder' => __( 'eg: some-unique-id', 'rey-core' ),
				'description' => sprintf(__( 'Copy the ID above and paste it into the "Toggle Boxes" Widget or "Slider Navigation" widget where specified. No hashtag needed. Read more on <a href="%s" target="_blank">how to connect them</a>.', 'rey-core' ), reycore__support_url('kb/products-grid-element/#adding-custom-navigation') ),
				'render_type' => 'none',
				'separator' => 'before',
				'style_transfer' => false,
				'wpml' => false,
			]
		);

		$this->add_control(
			'target_sync',
			[
				'label' => __( 'Sync. Controller ID', 'rey-core' ),
				'label_block' => true,
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => __( 'eg: unique-id', 'rey-core' ),
				'description' => __( 'By pasting a "Slider" or "Carousel" element\'s "Unique ID", this element will listen for the specified element movement.', 'rey-core' ),
				'render_type' => 'none',
				'separator' => 'before',
				'wpml' => false,
			]
		);

		$this->end_controls_section();

	}

	protected function controls__style_slides() {

		$this->start_controls_section(
			'section_style_slides',
			[
				'label' => esc_html__( 'Slides', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'source' => 'custom',
				],
			]
		);

		$this->add_responsive_control(
			'captions_width',
			[
				'label' => esc_html__( 'Content Width', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1000,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'size_units' => [ '%', 'px' ],
				'default' => [
					'unit' => '%',
				],
				'tablet_default' => [
					'unit' => '%',
				],
				'mobile_default' => [
					'unit' => '%',
				],
				'selectors' => [
					'{{WRAPPER}} .__caption' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'captions_padding',
			[
				'label' => esc_html__( 'Padding', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .__caption' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'horizontal_position',
			[
				'label' => esc_html__( 'Horizontal Position', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'default' => '',
				'options' => [
					'flex-start' => [
						'title' => esc_html__( 'Left', 'rey-core' ),
						'icon' => 'eicon-h-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'rey-core' ),
						'icon' => 'eicon-h-align-center',
					],
					'flex-end' => [
						'title' => esc_html__( 'Right', 'rey-core' ),
						'icon' => 'eicon-h-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .__slide' => 'justify-content:{{VALUE}}',
				],
			]
		);

		$this->add_control(
			'vertical_position',
			[
				'label' => esc_html__( 'Vertical Position', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'default' => '',
				'options' => [
					'flex-start' => [
						'title' => esc_html__( 'Top', 'rey-core' ),
						'icon' => 'eicon-v-align-top',
					],
					'center' => [
						'title' => esc_html__( 'Middle', 'rey-core' ),
						'icon' => 'eicon-v-align-middle',
					],
					'flex-end' => [
						'title' => esc_html__( 'Bottom', 'rey-core' ),
						'icon' => 'eicon-v-align-bottom',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .__caption' => 'align-self:{{VALUE}}',
				],
			]
		);

		$this->add_control(
			'text_align',
			[
				'label' => esc_html__( 'Text Align', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'start' => [
						'title' => esc_html__( 'Left', 'rey-core' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'rey-core' ),
						'icon' => 'eicon-text-align-center',
					],
					'end' => [
						'title' => esc_html__( 'Right', 'rey-core' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .__caption' => 'text-align: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Text_Shadow::get_type(),
			[
				'name' => 'text_shadow',
				'selector' => '{{WRAPPER}} .__caption',
			]
		);

		$this->end_controls_section();

	}

	protected function controls__style_title() {

		$this->start_controls_section(
			'section_style_title',
			[
				'label' => esc_html__( 'Title', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'source' => 'custom',
				],
			]
		);

			$this->add_control(
				'title_spacing',
				[
					'label' => esc_html__( 'Spacing', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 100,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .__title:not(:last-child)' => '--spacing: {{SIZE}}{{UNIT}}',
					],
				]
			);

			$this->add_control(
				'title_color',
				[
					'label' => esc_html__( 'Text Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .__title' => 'color: {{VALUE}}',

					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'title_typography',
					'global' => [
						'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Typography::TYPOGRAPHY_PRIMARY,
					],
					'selector' => '{{WRAPPER}} .__title',
				]
			);

		$this->end_controls_section();

	}

	protected function controls__style_subtitle() {

		$this->start_controls_section(
			'section_style_subtitle',
			[
				'label' => esc_html__( 'Subtitle', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'source' => 'custom',
				],
			]
		);

			$this->add_control(
				'subtitle_spacing',
				[
					'label' => esc_html__( 'Spacing', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 100,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .__subtitle:not(:last-child)' => '--spacing: {{SIZE}}{{UNIT}}',
					],
				]
			);

			$this->add_control(
				'subtitle_color',
				[
					'label' => esc_html__( 'Text Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .__subtitle' => 'color: {{VALUE}}',

					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'subtitle_typography',
					'global' => [
						'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Typography::TYPOGRAPHY_SECONDARY,
					],
					'selector' => '{{WRAPPER}} .__subtitle',
				]
			);

			$this->add_control(
				'subtitle_before_title',
				[
					'label' => esc_html__( 'Display Before Title', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

		$this->end_controls_section();

	}

	protected function controls__style_button() {

		$this->start_controls_section(
			'section_style_button',
			[
				'label' => esc_html__( 'Button', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'source' => 'custom',
				],
			]
		);

			$this->add_control(
				'button_style',
				[
					'label' => __( 'Button Style', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'btn-primary-outline',
					'options' => [
						''  => __( '- Inherit -', 'rey-core' ),
						'btn-simple'  => __( 'Link', 'rey-core' ),
						'btn-primary'  => __( 'Primary', 'rey-core' ),
						'btn-secondary'  => __( 'Secondary', 'rey-core' ),
						'btn-primary-outline'  => __( 'Primary Outlined', 'rey-core' ),
						'btn-secondary-outline'  => __( 'Secondary Outlined', 'rey-core' ),
						'btn-line-active'  => __( 'Underlined', 'rey-core' ),
						'btn-line'  => __( 'Hover Underlined', 'rey-core' ),
						'btn-primary-outline btn-dash'  => __( 'Primary Outlined & Dash', 'rey-core' ),
						'btn-primary-outline btn-dash btn-rounded'  => __( 'Primary Outlined & Dash & Rounded', 'rey-core' ),
						'btn-dash-line'  => __( 'Dash', 'rey-core' ),
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'button_typography',
					'selector' => '{{WRAPPER}} .__button',
				]
			);

			$this->add_control(
				'button_border_width',
				[
					'label' => esc_html__( 'Border Width', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 20,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .__button' => 'border-width: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_control(
				'button_border_radius',
				[
					'label' => esc_html__( 'Border Radius', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 100,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .__button' => 'border-radius: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->start_controls_tabs( 'button_tabs' );

				$this->start_controls_tab( 'normal', [ 'label' => esc_html__( 'Normal', 'rey-core' ) ] );

					$this->add_control(
						'button_text_color',
						[
							'label' => esc_html__( 'Text Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .__button' => 'color: {{VALUE}};',
							],
						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Background::get_type(),
						[
							'name' => 'button_background',
							'types' => [ 'classic', 'gradient' ],
							'exclude' => [ 'image' ],
							'selector' => '{{WRAPPER}} .__button',
							'fields_options' => [
								'background' => [
									'default' => 'classic',
								],
							],
						]
					);

					$this->add_control(
						'button_border_color',
						[
							'label' => esc_html__( 'Border Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .__button' => 'border-color: {{VALUE}};',
							],
						]
					);

				$this->end_controls_tab();

				$this->start_controls_tab( 'hover', [ 'label' => esc_html__( 'Hover', 'rey-core' ) ] );

					$this->add_control(
						'button_hover_text_color',
						[
							'label' => esc_html__( 'Text Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .__button:hover' => 'color: {{VALUE}};',
							],
						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Background::get_type(),
						[
							'name' => 'button_hover_background',
							'types' => [ 'classic', 'gradient' ],
							'exclude' => [ 'image' ],
							'selector' => '{{WRAPPER}} .__button:hover',
							'fields_options' => [
								'background' => [
									'default' => 'classic',
								],
							],
						]
					);

					$this->add_control(
						'button_hover_border_color',
						[
							'label' => esc_html__( 'Border Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .__button:hover' => 'border-color: {{VALUE}};',
							],
						]
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();

		$this->end_controls_section();

	}

	protected function register_controls() {

		$this->selectors['slider'] = '{{WRAPPER}} .rey-bSlider';

		$this->controls__slides();
		$this->controls__slider();
		$this->controls__style_slides();
		$this->controls__style_title();
		$this->controls__style_subtitle();
		$this->controls__style_button();

		\ReyCore\Libs\Slider_Components::controls( $this );

	}

	public function render_start()
	{

		$classes = [
			'splide',
			'rey-bSlider',
			'--source-' . esc_attr( $this->_settings['source'] )
		];

		if( count($this->_items) > 1 ) {

			reycore_assets()->add_scripts( $this->rey_get_script_depends() );

			if( $slider_id = esc_attr( $this->_settings['slider_id'] ) ){
				$classes[] =  $slider_id;
				$this->add_render_attribute( 'wrapper', 'data-slider-carousel-id', esc_attr($slider_id) );
			}

			$classes[] = $this->slider_components::$selectors['wrapper'];

			if( $pause_on_hover = ($this->_settings['autoplay'] !== '' && $this->_settings['pause_on_hover'] !== '') ){
				$classes[] = $this->slider_components::$selectors['pause_hover'];
			}

			if( $target_sync = $this->_settings['target_sync'] ){
				$this->add_render_attribute( 'wrapper', 'data-target-sync', esc_attr($target_sync) );
			}

			$classes['transition'] = 'splide--' . esc_attr( $this->_settings['transition'] );

			$_config = [
				'direction'      => $this->_settings['direction'],
				'infinite'       => $this->_settings['infinite'] !== '',
				'autoplay'       => $this->_settings['autoplay'] !== '',
				'interval'       => ! empty($this->_settings['autoplay_duration']) ? absint($this->_settings['autoplay_duration']) : 5000,
				'pause_on_hover' => $pause_on_hover,
				'transition'     => $this->_settings['transition'],
				'speed'          => absint($this->_settings['transition_speed']),
				'uniqueID'       => $this->_settings['slider_id'],
				'targetSync'     => $this->_settings['target_sync'],
				'customArrows'   => $this->slider_components::$selectors['arrows'],
				'pagination'     => $this->slider_components->is_enabled('dots'),
				// 'arrows' => $this->slider_components->is_enabled('arrows'),
			];

			if( ! $this->slider_components->is_enabled('arrows') ){
				$_config['customArrows'] = false;
			}

			$this->add_render_attribute( 'wrapper', 'data-carousel-settings', wp_json_encode($_config) );

			if( $caption_animation = $this->_settings['caption_animation'] ){
				$this->add_render_attribute( 'wrapper', 'data-c-anim', esc_attr($caption_animation) );
			}
		}

		$this->add_render_attribute( 'wrapper', 'class', $classes ); ?>

		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
		<?php
	}

	public function render_end()
	{
		?></div><?php
	}

	public function render_slider(){

		if( empty($this->_items) ){
			return;
		} ?>

		<div class="splide__track">
			<div class="splide__list __slides">

				<?php
				for ($i=0; $i < count($this->_items); $i++) {
					$this->_slide = $this->_items[$i];
					$this->_key = $i;

					$this->render_item__start();
					$this->render_item();
					$this->render_item__end();

				} ?>

			</div>
		</div>
		<?php
	}

	public function render_item__start(){

		$classes = [
			'splide_slide' => 'splide__slide',
			'item' => '__slide',
		];

		$this->gs = isset($this->_slide['gs']) && ($gs = $this->_slide['gs']) ? $gs : '';

		if( isset($this->_slide['_id']) && $_id = $this->_slide['_id'] ){
			$classes['_id'] = 'elementor-repeater-item-' . $_id;
		}

		$attributes = '';
		$this->slide_tag[$this->_key] = 'div';

		if( ! $this->gs && isset($this->_slide['link_click']) && 'slide' === $this->_slide['link_click'] ){
			$attributes = $this->__link_attributes();

			if( strpos($attributes, 'href=') !== false ){
				$this->slide_tag[$this->_key] = 'a';
			}
		}

		printf('<%s class="%s" %s>', $this->slide_tag[$this->_key], esc_attr(implode(' ', $classes)), $attributes);
	}

	public function render_item__end(){
		printf('</%s>', $this->slide_tag[$this->_key]);
	}

	/**
	 * Prepare a link's attribute
	 *
	 * @param string $class
	 * @return string html attributes
	 */
	public function __link_attributes( $class = '' ){

		if( ! (isset($this->_slide['link']['url']) && $url = $this->_slide['link']['url']) ) {
			return '';
		}

		$attributes['href'] = $url;
		$attributes['class'] = $class;

		if( $this->_slide['link']['is_external'] ){
			$attributes['target'] = '_blank';
		}

		if( $this->_slide['link']['nofollow'] ){
			$attributes['rel'] = 'nofollow';
		}

		if( ($custom_attributes = $this->_slide['link']['custom_attributes']) && is_array($custom_attributes) ){
			foreach ($custom_attributes as $key => $value) {
				if( ! $key ) continue;
				$attributes[$key] = $value;
			}
		}

		return reycore__implode_html_attributes($attributes);
	}

	public function __title(){

		if( ! ($title = $this->_slide['title']) ){
			return;
		}

		printf('<%1$s class="__captionEl __title">%2$s</%1$s>', self::$defaults['title_tag'], $title);
	}

	public function __subtitle(){

		if( ! ($subtitle = $this->_slide['subtitle']) ){
			return;
		}

		printf('<%1$s class="__captionEl __subtitle">%2$s</%1$s>', self::$defaults['subtitle_tag'], reycore__parse_text_editor($subtitle));
	}

	public function __button(){

		if( ! ($button_text = $this->_slide['button_text']) ){
			return;
		}

		reycore_assets()->add_styles('rey-buttons');

		$button_style = $this->_settings['button_style'];

		if( '' !== $this->_slide['custom_style'] && '' !== $this->_slide['button_style'] ){
			$button_style = $this->_slide['button_style'];
		}

		$tag = 'div';
		$attributes = '';

		if( 'button' === $this->_slide['link_click'] ){
			$tag = 'a';
			$attributes = $this->__link_attributes();
		}

		printf('<%1$s class="__captionEl __button %3$s" %4$s>%2$s</%1$s>',
			$tag,
			$button_text,
			'btn ' . $button_style,
			$attributes
		);

	}

	public function render_item(){

		if( $this->gs ){
			reycore_assets()->defer_page_styles('elementor-post-' . $this->gs);
			echo \ReyCore\Elementor\GlobalSections::do_section($this->gs, false, true);
			return;
		}

		$images_config = [
			'desktop' => $this->_slide['image'],
			'tablet'  => ! empty($this->_slide['image_tablet']['id']) ? $this->_slide['image_tablet'] : [],
			'mobile'  => ! empty($this->_slide['image_mobile']['id']) ? $this->_slide['image_mobile'] : [],
			'image_size' => $this->_settings['image_size'],
			'settings' => $this->_settings,
			'class' => '__media',
		];

		if( ! empty($this->_slide['ken_burns']) ){
			$images_config['desktop_attributes']['data-kb'] = esc_attr($this->_slide['ken_burns']);
		}

		echo reycore__get_responsive_attachment_images($images_config);

		if( isset($this->_slide['overlay']) && $this->_slide['overlay'] ){
			echo '<div class="__overlay"></div>';
		}

		if( ! $this->gs && 'custom' === $this->_settings['source'] ){

			echo '<div class="__caption">';

				if( '' !== $this->_settings['subtitle_before_title'] ){
					$this->__subtitle();
				}

				$this->__title();

				if( '' === $this->_settings['subtitle_before_title'] ){
					$this->__subtitle();
				}

				$this->__button();

			echo '</div>';
		}

	}

	protected function render() {

		self::$defaults = apply_filters('reycore/basic-slider/defaults', [
			'title_tag' => 'h2',
			'label_tag' => 'h3',
			'subtitle_tag' => 'div',
		]);

		reycore_assets()->add_styles([$this->get_style_name(), 'rey-splide']);

		$this->_settings = $this->get_settings_for_display();
		$this->_items = [];

		switch( $this->_settings['source'] ){
			case "custom":
				$this->_items = ($slides = $this->_settings['slides']) ? $slides : [];
				break;
			case "images":
				foreach ($this->_settings['images'] as $value) {
					$this->_items[]['image'] = $value;
				}
				break;
			// case "gs":
			// 	$this->_items = ($slides = $this->_settings['gs_slides']) ? $slides : [];
			// 	break;
		}

		$this->slider_components = new \ReyCore\Libs\Slider_Components( $this );

		$this->render_start();

			$this->render_slider();

			$this->slider_components->render();
			$this->slider_components->render_dots_container();

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
