<?php
namespace ReyCore\Elementor\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
exit; // Exit if accessed directly.
}

class CarouselUno extends \ReyCore\Elementor\WidgetsBase {

	private $_settings = [];
	private $_items = [];

	public static function get_rey_config(){
		return [
			'id' => 'carousel-uno',
			'title' => __( 'Slider', 'rey-core' ),
			'icon' => 'eicon-post-slider',
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
		return [ 'rey-videos', 'splidejs', 'rey-splide', 'reycore-widget-carousel-uno-scripts' ];
	}

	public function show_in_panel() {
		return false;
	}

	public function get_custom_help_url() {
		return reycore__support_url('kb/rey-elements/#carousel-uno');
	}

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
				'label' => __( 'Content', 'rey-core' ),
			]
		);

		$this->add_control(
			'notice',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => __( 'This element is deprecated. Please use <strong>Basic Slider</strong> or <strong>Carousel</strong> elements instead.', 'rey-core' ),
				'content_classes' => 'rey-raw-html --deprecated',
			]
		);

		$items = new \Elementor\Repeater();

		$items->add_control(
			'bg_type',
			[
				'label' => __( 'Content Type', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'default' => 'image',
				'options' => [
					'image' => [
						'title' => _x( 'Image', 'Background Control', 'rey-core' ),
						'icon' => 'eicon-image',
					],
					'video' => [
						'title' => _x( 'Self Hosted Video', 'Background Control', 'rey-core' ),
						'icon' => 'eicon-video-camera',
					],
					'youtube' => [
						'title' => _x( 'YouTube Video', 'Background Control', 'rey-core' ),
						'icon' => 'eicon-youtube',
					],
				],
			]
		);

		$items->add_control(
			'html_video',
			[
				'label' => __( 'Self Hosted Video URL', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'label_block' => true,
				'description' => __( 'Link to video file (mp4 is recommended).', 'rey-core' ),
				'conditions' => [
					'terms' => [
						[
							'name' => 'bg_type',
							'operator' => '==',
							'value' => 'video',
						],
					],
				],
				'wpml' => false,
				'ai' => [
					'active' => false,
				],
			]
		);

		$items->add_control(
			'yt_video',
			[
				'label' => __( 'YouTube URL', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'label_block' => true,
				// 'description' => __( 'Link to Youtube.', 'rey-core' ),
				'conditions' => [
					'terms' => [
						[
							'name' => 'bg_type',
							'operator' => '==',
							'value' => 'youtube',
						],
					],
				],
				'wpml' => false,
				'ai' => [
					'active' => false,
				],
			]
		);

		$items->add_control(
			'image',
			[
			'label' => __( 'Image', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::MEDIA,
				'default' => [
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				],
				'conditions' => [
					'terms' => [
						[
							'name' => 'bg_type',
							'operator' => '==',
							'value' => 'image',
						],
					],
				],
			]
		);

		$items->add_control(
			'image_as_video_fallback',
			[
				// 'label' => __( 'Important Note', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => __( 'Use the image as fallback for videos on mobile.', 'rey-core' ),
				'content_classes' => 'elementor-descriptor',
				'conditions' => [
					'terms' => [
						[
							'name' => 'bg_type',
							'operator' => '!=',
							'value' => 'image',
						],
					],
				],
			]
		);


		$items->add_control(
			'video_image',
			[
			'label' => __( 'Background Fallback', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::MEDIA,
				'default' => [
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				],
				'description' => __( 'This cover image will replace the background video on mobile and tablet devices. ', 'rey-core' ),
				'conditions' => [
					'terms' => [
						[
							'name' => 'bg_type',
							'operator' => '!=',
							'value' => 'image',
						],
					],
				],
			]
		);

		$items->add_control(
			'overlay_color',
			[
				'label' => __( 'Overlay Background Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} .cUno-slideOverlay' => 'background-color: {{VALUE}}',
				],
			]
		);

		$items->add_control(
			'captions',
			[
				'label' => __( 'Enable Captions', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$items->add_control(
			'title',
			[
				'label'       => __( 'Title', 'rey-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'conditions' => [
					'terms' => [
						[
							'name' => 'captions',
							'operator' => '!=',
							'value' => '',
						],
					],
				],
			]
		);

		$items->add_control(
			'subtitle',
			[
				'label'       => __( 'Subtitle Text', 'rey-core' ),
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'label_block' => true,
				'conditions' => [
					'terms' => [
						[
							'name' => 'captions',
							'operator' => '!=',
							'value' => '',
						],
					],
				],
			]
		);

		$items->add_control(
			'label',
			[
				'label'       => __( 'Label Text', 'rey-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'conditions' => [
					'terms' => [
						[
							'name' => 'captions',
							'operator' => '!=',
							'value' => '',
						],
					],
				],
			]
		);

		$items->add_control(
			'text_color',
			[
				'label' => __( 'Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} .cUno-caption' => 'color: {{VALUE}}',
				],
				'conditions' => [
					'terms' => [
						[
							'name' => 'captions',
							'operator' => '!=',
							'value' => '',
						],
					],
				],
				'separator' => 'after'
			]
		);

		$items->add_control(
			'button_text',
			[
				'label' => __( 'Button Text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true,
				],
				'default' => __( 'Click here', 'rey-core' ),
				'placeholder' => __( 'eg: SHOP NOW', 'rey-core' ),
				'conditions' => [
					'terms' => [
						[
							'name' => 'captions',
							'operator' => '!=',
							'value' => '',
						],
					],
				],
			]
		);

		$items->add_control(
			'button_url',
			[
				'label' => __( 'Link', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::URL,
				'dynamic' => [
					'active' => true,
				],
				'placeholder' => __( 'https://your-link.com', 'rey-core' ),
				'default' => [
					'url' => '#',
				],
				// 'conditions' => [
				// 	'terms' => [
				// 		[
				// 			'name' => 'captions',
				// 			'operator' => '!=',
				// 			'value' => '',
				// 		],
				// 	],
				// ],
				'separator' => 'after'
			]
		);

		// No 2nd button because they would need too many options, style, color, hover color, per button

		$this->add_control(
			'items',
			[
				'label' => __( 'Items', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $items->get_controls(),
				'default' => [
					[
						'image' => [
							'url' => \Elementor\Utils::get_placeholder_image_src(),
						],
						'captions' => 'yes',
						'title' => esc_html_x('Some title', 'Placeholder title', 'rey-core'),
						'subtitle' => esc_html_x('Phosfluorescently predominate pandemic applications for real-time customer service', 'Placeholder text', 'rey-core'),
						'button_text' => esc_html_x('Click here', 'Placeholder button text', 'rey-core'),
						'button_url' => [
							'url' => '#',
						],
					],
					[
						'image' => [
							'url' => \Elementor\Utils::get_placeholder_image_src(),
						],
						'captions' => 'yes',
						'title' => esc_html_x('Some title', 'Placeholder title', 'rey-core'),
						'subtitle' => esc_html_x('Phosfluorescently predominate pandemic applications for real-time customer service', 'Placeholder text', 'rey-core'),
						'button_text' => esc_html_x('Click here', 'Placeholder button text', 'rey-core'),
						'button_url' => [
							'url' => '#',
						],
					],
				]
			]
		);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_content_settings',
			[
				'label' => __( 'Content Settings', 'rey-core' ),
			]
		);

		$this->add_control(
			'stretch_content',
			[
				'label' => __( 'Content Inside', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'return_value' => 'stretch-content',
			]
		);

		$this->add_responsive_control(
			'content_inside_valign',
			[
				'label' => __( 'Caption Vertical Align', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'flex-start' => [
						'title' => __( 'Start', 'rey-core' ),
						'icon' => 'eicon-v-align-top',
					],
					'center' => [
						'title' => __( 'Middle', 'rey-core' ),
						'icon' => 'eicon-v-align-middle',
					],
					'flex-end' => [
						'title' => __( 'End', 'rey-core' ),
						'icon' => 'eicon-v-align-bottom',
					],
				],
				'default' => 'flex-start',
				'condition' => [
					'stretch_content!' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .cUno--stretch-content .cUno-slide' => 'justify-content: {{VALUE}};',
					'{{WRAPPER}} .cUno--stretch-content .cUno-captionStretched' => 'justify-content: {{VALUE}};', // when image fully stretches
				],
				'prefix-class' => '--caption-posy-'
			]
		);

		$this->add_responsive_control(
			'content_inside_halign',
			[
				'label' => __( 'Caption Horizontal Align', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'left'           => [
						'title'         => __( 'Left', 'rey-core' ),
						'icon'          => 'eicon-h-align-left',
					],
					'center'        => [
						'title'         => __( 'Center', 'rey-core' ),
						'icon'          => 'eicon-h-align-stretch',
					],
					'right'          => [
						'title'         => __( 'Right', 'rey-core' ),
						'icon'          => 'eicon-h-align-right',
					],
				],
				'default' => 'left',
				'condition' => [
					'stretch_content!' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .cUno--stretch-content .cUno-slide' => 'text-align: {{VALUE}};',
				],
				'prefix-class' => '--caption-posx-'
			]
		);

		$this->add_responsive_control(
			'content_spacing',
			[
				'label' => esc_html__( 'Content Spacing', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 200,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .rey-carouselUno' => '--content-spacing: {{SIZE}}px;',
				],
				'condition' => [
					'stretch_content!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'content_inside_spacing',
			[
				'label' => esc_html__( 'Nav. Elements Spacing', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 200,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .rey-carouselUno' => '--side-spacing: {{SIZE}}px;',
				],
				'condition' => [
					'stretch_content!' => '',
				],
			]
		);

		$this->add_control(
			'image_content_title',
			[
				'label' => esc_html__( 'IMAGE', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
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

		$this->add_control(
			'stretch_image',
			[
				'label' => esc_html__( 'Natural Image Size', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [
					'stretch_content!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'stretch_image_height',
			[
			'label' => __( 'Slider Height', 'rey-core' ),
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
				],
				'tablet_default' => [
					'unit' => 'px',
				],
				'mobile_default' => [
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .cUno--stretch-content .cUno-slide' => 'height: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'stretch_content!' => '',
					'stretch_image' => '',
				],
			]
		);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_slider',
			[
				'label' => __( 'Slider Settings', 'rey-core' ),
			]
		);

		$this->add_control(
			'fade',
			[
				'label' => __( 'Enable Fade Transition', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->add_control(
			'autoplay',
			[
				'label' => __( 'Autoplay', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default' => '',
				'condition' => [
					'sync' => '',
				],
			]
		);

		$this->add_control(
			'autoplay_duration',
			[
				'label' => __( 'Autoplay Duration (ms)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 9000,
				'min' => 3500,
				'max' => 20000,
				'step' => 50,
				'condition' => [
					'autoplay' => 'yes',
					'sync' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .rey-carouselUno' => '--autoplay-duration: {{SIZE}}ms;',
				],
			]
		);

		$this->add_control(
			'autoplay_pause_hover',
			[
				'label' => esc_html__( 'Pause on hover', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
				'condition' => [
					'autoplay!' => '',
					'sync' => '',
				],
			]
		);

		$this->add_control(
			'arrows_title',
			[
				'label' => esc_html__( 'ARROWS', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'arrows',
			[
				'label' => __( 'Arrows Navigation', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
				'condition' => [
					'sync' => '',
				],
			]
		);

		$this->add_control(
			'arrows_on_hover',
			[
				'label' => __( 'Show Arrows On Hover', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [
					'sync' => '',
					'arrows' => 'yes',
				],
			]
		);

		$this->add_control(
			'arrows_position',
			[
				'label' => esc_html__( 'Arrows Vertical Position', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'range' => [
					'%' => [
						'min' => 0,
						'max' => 100,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .rey-arrowSvg' => 'top: calc({{SIZE}}% - (var(--arrow-size)/2));',
				],
				'condition' => [
					'sync' => '',
					'arrows' => 'yes',
				],
			]
		);

		$this->add_control(
			'dots_title',
			[
				'label' => esc_html__( 'DOTS', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'dots',
			[
				'label' => __( 'Dots Navigation', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
				'condition' => [
					'sync' => '',
				],
			]
		);

		$this->add_control(
			'dots_style',
			[
				'label' => esc_html__( 'Dots Style', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'circles',
				'options' => [
					'circles'  => esc_html__( 'Circles', 'rey-core' ),
					'bars'  => esc_html__( 'Bars', 'rey-core' ),
				],
				'condition' => [
					'dots!' => '',
				],
			]
		);

		$this->add_control(
			'dots_position',
			[
				'label' => esc_html__( 'Dots Position', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'top-left',
				'options' => [
					'top-left'  => esc_html__( 'Top Left', 'rey-core' ),
					'top-center'  => esc_html__( 'Top Center', 'rey-core' ),
					'top-right'  => esc_html__( 'Top Right', 'rey-core' ),
					'bottom-left'  => esc_html__( 'Bottom Left', 'rey-core' ),
					'bottom-center'  => esc_html__( 'Bottom Center', 'rey-core' ),
					'bottom-right'  => esc_html__( 'Bottom Right', 'rey-core' ),
				],
				'condition' => [
					'stretch_content!' => '',
					'dots!' => '',
				],
			]
		);

		$this->add_control(
			'sync_title',
			[
				'label' => esc_html__( 'SYNC', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'sync_id',
			[
				'label' => __( 'Sync another carousel (as controller)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'default' => '',
				'placeholder' => __( 'eg: carousel-unique-id', 'rey-core' ),
				'description' => esc_html__('Sync this slider with another one. Access the other carousel Slider settings and enable Sync option, to generate a unique id which you\'ll have to paste here.', 'rey-core'),
				'condition' => [
					'sync' => '',
				],
			]
		);

		$this->add_control(
			'sync',
			[
				'label' => __( 'Enable Sync (as listener)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->add_control(
			'target_sync_id',
			[
				'label' => __( 'Sync ID', 'rey-core' ),
				'label_block' => true,
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => uniqid('carousel-'),
				'placeholder' => __( 'eg: carousel-unique-id', 'rey-core' ),
				'description' => __( 'Copy this ID above and paste it into the "Carousel Uno" widget "Sync another carousel".', 'rey-core' ),
				'condition' => [
					'sync!' => '',
				],
				'render_type' => 'none',
				'wpml' => false,
			]
		);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Slider Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'title_typo',
				'label' => esc_html__('Title Typography', 'rey-core'),
				'global' => [
					'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}} .cUno-captionTitle',
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => esc_html__( 'Title Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cUno-captionTitle' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'subtitle_typo',
				'label' => esc_html__('Sub-Title Typography', 'rey-core'),
				'selector' => '{{WRAPPER}} .cUno-captionSubtitle',
			]
		);

		$this->add_control(
			'subtitle_color',
			[
				'label' => esc_html__( 'Sub-Title Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cUno-captionSubtitle' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'label_typo',
				'label' => esc_html__('Label Typography', 'rey-core'),
				'global' => [
					'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}} .cUno-captionLabel',
			]
		);

		$this->add_control(
			'label_color',
			[
				'label' => esc_html__( 'Label Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cUno-captionLabel' => 'color: {{VALUE}}',
				],
			]
		);


		$this->add_responsive_control(
			'label_distance',
			[
				'label' => __( 'Label Distance', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .cUno-captionLabel' => '--distance: {{SIZE}}px;',
				],
				'separator' => 'after'
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'button_typo',
				'label' => esc_html__('Button Typography', 'rey-core'),
				'selector' => '{{WRAPPER}} .cUno-captionBtn a',
			]
		);

		$this->add_control(
			'button_style',
			[
				'label' => __( 'Button Style', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'btn-line-active',
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

		$this->start_controls_tabs( 'tabs_styles');

			$this->start_controls_tab(
				'tab_default',
				[
					'label' => __( 'Default', 'rey-core' ),
				]
			);

				$this->add_control(
					'button_color',
					[
						'label' => esc_html__( 'Primary Color (text)', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .cUno-captionBtn .btn' => 'color: {{VALUE}}',
						],
					]
				);

				$this->add_control(
					'button_color_bg',
					[
						'label' => esc_html__( 'Primary Color (background)', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .cUno-captionBtn .btn' => 'background-color: {{VALUE}}',
						],
					]
				);

			$this->end_controls_tab();

			$this->start_controls_tab(
				'tab_hover',
				[
					'label' => __( 'Hover', 'rey-core' ),
				]
			);

				$this->add_control(
					'button_color_hover',
					[
						'label' => esc_html__( 'Primary Color (text)', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .cUno-captionBtn .btn:hover' => 'color: {{VALUE}}',
						],
					]
				);

				$this->add_control(
					'button_color_bg_hover',
					[
						'label' => esc_html__( 'Primary Color (background)', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .cUno-captionBtn .btn:hover' => 'background-color: {{VALUE}}',
						],
					]
				);

			$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'arrows_color',
			[
				'label' => esc_html__( 'Arrows Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cUno-arrows' => 'color: {{VALUE}}',
				],
				'separator' => 'before',
			]
		);

		$this->add_control(
			'arrows_color_diff',
			[
				'label' => esc_html__( 'Enable difference color?', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'difference',
				'return_value' => 'difference',
				'selectors' => [
					'{{WRAPPER}} .cUno-arrows' => 'mix-blend-mode: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'dots_color',
			[
				'label' => esc_html__( 'Dots Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cUno-nav' => 'color: {{VALUE}}',
				],
				'separator' => 'before',
			]
		);

		$this->add_control(
			'dots_color_diff',
			[
				'label' => esc_html__( 'Enable difference color?', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'difference',
				'return_value' => 'difference',
				'selectors' => [
					'{{WRAPPER}} .cUno-nav' => 'mix-blend-mode: {{VALUE}}',
				],
			]
		);

		$this->end_controls_section();
	}

	function can_stretch_image(){
		return $this->_settings['stretch_content'] !== '' && $this->_settings['stretch_image'] !== '';
	}

	public function render_start(){

		$classes = ['rey-carouselUno'];

		if( $this->_settings['stretch_content'] !== '' ){
			$classes[] = 'cUno--stretch-content';
		}

		if( $this->can_stretch_image() ){
			$classes[] = 'cUno-imgnatural-yes';
		}

		if( $this->_settings['autoplay'] !== '' && $this->_settings['autoplay_pause_hover'] !== '' ){
			$classes[] = '--pause-hover';
		}

		$this->add_render_attribute( 'wrapper', 'class', $classes );

		if( count($this->_items) > 1 ) {

			$slider_settings = [
				'type' => $this->_settings['fade'] !== '' ? 'fade' : 'slide',
				'autoplay' => $this->_settings['autoplay'] !== '',
				'pauseOnHover' => $this->_settings['autoplay'] !== '' && $this->_settings['autoplay_pause_hover'] !== '',
				'interval' => absint($this->_settings['autoplay_duration']),
				'sync' => $this->_settings['sync'] !== '',
				'syncId' => esc_attr($this->_settings['sync_id']),
				'targetSyncId' => esc_attr($this->_settings['target_sync_id']),
				'contentPosition' => 'inside',
				'customArrows' => '.__arrows-' . $this->get_id(),
				'customPagination' => '.__pagination-' . $this->get_id(),
				'speed' => 700,
				'animateHeight' => $this->can_stretch_image(),
			];

			if( ! $slider_settings['interval'] ){
				$slider_settings['autoplay'] = false;
			}

			$this->add_render_attribute( 'wrapper', 'data-slider-settings', wp_json_encode($slider_settings) );
		}
		?><div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>><?php
	}

	public function render_end(){
		?></div><?php
	}

	public function render_slides(){
		?>
		<div class="splide <?php echo $this->_settings['sync'] !== '' ? esc_attr($this->_settings['target_sync_id']) : '' ?>">
			<div class="splide__track">
				<div class="splide__list cUno-slides">
				<?php
					if( !empty($this->_items) ):
						foreach($this->_items as $key => $item): ?>

						<div class="splide__slide cUno-slide --<?php echo esc_attr($item['bg_type']) ?> elementor-repeater-item-<?php echo esc_attr($item['_id']) ?>">

							<?php
							$slide_content = '';

							$slide_image = reycore__get_attachment_image( [
								'image' => $item['image'],
								'size' => $this->_settings['image_size'],
								'attributes' => ['class'=>'cUno-slideContent'],
								'settings' => $this->_settings,
							] );

							if( $item['bg_type'] === 'video' )
							{
								$slide_content = reycore__get_video_html([
									'video_url' => $item['html_video'],
									'class' => 'cUno-slideContent',
								]);
							}

							elseif( $item['bg_type'] === 'youtube' )
							{
								$slide_content = reycore__get_youtube_iframe_html([
									'video_url' => $item['yt_video'],
									'class' => 'cUno-slideContent',
									'html_id' => 'yt' . $item['_id'],
									'add_preview_image' => empty($slide_image),
									'preview_inside' => true,
								]);
							}

							echo $slide_content;

							$url_key = 'url' . $key;
							$link_start = $link_end = '';

							if( $item['bg_type'] === 'image' && isset($item['button_url']['url']) && $url = $item['button_url']['url'] )
							{
								$this->add_render_attribute( $url_key , 'href', $url );

								if( $item['button_url']['is_external'] ){
									$this->add_render_attribute( $url_key , 'target', '_blank' );
								}

								if( $item['button_url']['nofollow'] ){
									$this->add_render_attribute( $url_key , 'rel', 'nofollow' );
								}

								$link_start = sprintf('<a %s>', $this->get_render_attribute_string($url_key) );
								$link_end = '</a>';
							}

							echo $link_start . $slide_image . $link_end;
							?>

							<div class="cUno-slideOverlay "></div>

							<?php $this->render_nav(); ?>
							<?php $this->render_caption($item, $key . '_slide'); ?>
						</div>
						<?php
						endforeach;
					endif; ?>
				</div>
			</div>
		</div>
		<?php
	}

	public function render_captions() {
		?>
		<div class="cUno-captions">
		<?php
			if( !empty($this->_items) ):
				foreach($this->_items as $key => $item):
					$this->render_caption($item, $key);
				endforeach;
			endif; ?>
		</div>
		<?php
	}

	public function render_caption( $slide, $key )
	{
		if( $slide['captions'] !== '' ):

			if( $this->can_stretch_image() ){
				echo '<div class="cUno-captionStretched">';
			} ?>

			<div class="cUno-caption">

				<?php if( $label = $slide['label'] ): ?>
					<h3 class="cUno-captionEl cUno-captionLabel"><?php echo $label ?></h3>
				<?php endif; ?>

				<?php if( $title = $slide['title'] ): ?>
					<h2 class="cUno-captionEl cUno-captionTitle"><?php echo $title ?></h2>
				<?php endif; ?>

				<?php if( $subtitle = $slide['subtitle'] ): ?>
					<div class="cUno-captionEl cUno-captionSubtitle"><?php echo reycore__parse_text_editor($subtitle) ?></div>
				<?php endif; ?>

				<?php if( $button_text = $slide['button_text'] ): ?>
					<div class="cUno-captionEl cUno-captionBtn">

						<?php
						$url_key = 'url'.$key;
						reycore_assets()->add_styles('rey-buttons');
						$this->add_render_attribute( $url_key , 'class', 'btn ' . $this->_settings['button_style'] );

						if( isset($slide['button_url']['url']) && $url = $slide['button_url']['url'] ){
							$this->add_render_attribute( $url_key , 'href', $url );

							if( $slide['button_url']['is_external'] ){
								$this->add_render_attribute( $url_key , 'target', '_blank' );
							}

							if( $slide['button_url']['nofollow'] ){
								$this->add_render_attribute( $url_key , 'rel', 'nofollow' );
							}
						} ?>
						<a <?php echo  $this->get_render_attribute_string($url_key); ?>>
							<?php echo $button_text; ?>
						</a>
					</div>
					<!-- .cUno-btn -->
				<?php endif; ?>

			</div><?php

			if( $this->can_stretch_image() ){
				echo '</div>';
			}
		endif;
	}

	public function render_arrows()
	{
		if( $this->_settings['arrows'] === 'yes' ):

			$classes[] = '__arrows-' . $this->get_id();
			$classes[] = $this->_settings['arrows_on_hover'] === 'yes' ? '--hide-on-idle' : '';

			?>
			<div class="cUno-arrows <?php echo implode(' ', $classes) ?>">
				<?php
					echo reycore__arrowSvg([
						'right'      => false,
						'attributes' => 'data-dir="<"',
					]);
					echo reycore__arrowSvg([
						'right'      => true,
						'attributes' => 'data-dir=">"',
					]);
				?>
			</div>
		<?php endif;
	}

	public function render_nav(){
		if( $this->_settings['dots'] === 'yes' && !empty($this->_items) ){

			$classes[] = '__pagination-' . $this->get_id();
			$classes[] = $this->_settings['stretch_content'] !== '' ? '--dots-pos--' . $this->_settings['dots_position'] : '';
			$classes[] = '--dots-style--' . $this->_settings['dots_style'];

			if( $this->_settings['autoplay'] !== '' ){
				$classes[] = '--autoplay';
			}

			?>
			<div class="cUno-nav <?php  echo implode(' ', $classes); ?>">
				<?php
				foreach($this->_items as $key => $item):
					printf('<button data-go="%d" class="%s"></button>', esc_attr($key), $key === 0 ? 'is-active' : '');
				endforeach; ?>
			</div>
		<?php }
	}

	protected function render() {

		reycore_assets()->add_styles([$this->get_style_name(), 'rey-splide']);
		reycore_assets()->add_scripts( $this->rey_get_script_depends() );

		$this->_settings = $this->get_settings_for_display();
		$this->_items = $this->_settings['items'];

		$this->render_start();
		$this->render_slides();
		// $this->render_nav();
		// $this->render_captions();
		$this->render_arrows();
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
