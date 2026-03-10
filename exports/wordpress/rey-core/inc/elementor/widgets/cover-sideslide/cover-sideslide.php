<?php
namespace ReyCore\Elementor\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class CoverSideslide extends \ReyCore\Elementor\WidgetsBase {

	private $_items = [];

	private $_settings = [];

	public static function get_rey_config(){
		return [
			'id' => 'cover-sideslide',
			'title' => __( 'Cover - Side Slider', 'rey-core' ),
			'icon' => 'rey-font-icon-general-r',
			'categories' => [ 'rey-theme-covers' ],
			'keywords' => [],
			'css' => [
				'assets/style[rtl].css',
			],
			'js' => [
				'assets/script.js',
			],
		];
	}

	public function __construct( $data = [], $args = null ) {

		if ( $data && isset($data['settings']) && $settings = $data['settings'] ) {

			$is_animated = true;

			if( isset($settings['intro_animation']) && '' === $settings['intro_animation'] ){
				$is_animated = false;
			}

			if( $is_animated ){
				do_action('reycore/elementor/widget/construct', $data);
			}

			\ReyCore\Plugin::instance()->elementor->frontend->add_delay_js_scripts('cover-sideslide', ['rey-script', 'rey-videos', 'rey-splide', 'splidejs']);

		}

		parent::__construct( $data, $args );
	}

	public function rey_get_script_depends() {
		return [ 'splidejs', 'rey-splide', 'reycore-widget-cover-sideslide-scripts' ];
	}

	public function get_custom_help_url() {
		return reycore__support_url('kb/rey-elements-covers/#side-slider');
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

		$items = new \Elementor\Repeater();

		$items->add_control(
			'slide_type',
			[
				'label' => __( 'Media Type', 'rey-core' ),
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
							'name' => 'slide_type',
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
							'name' => 'slide_type',
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
			'video_play_on_mobile',
			[
				'label' => esc_html__( 'Play video on mobile', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'conditions' => [
					'terms' => [
						[
							'name' => 'slide_type',
							'operator' => '!=',
							'value' => 'image',
						],
					],
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
							'name' => 'slide_type',
							'operator' => '!=',
							'value' => 'image',
						],
					],
				],
			]
		);


		$items->add_responsive_control(
			'overlay_color',
			[
				'label' => __( 'Overlay Background Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}}.cSslide-slide:after' => 'background-color: {{VALUE}}',
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
					'{{WRAPPER}} {{CURRENT_ITEM}}.cSslide-caption' => 'color: {{VALUE}}',
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
			]
		);

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
						'label' => __( 'Label Text #1', 'rey-core' ),
						'title' => __( 'Title Text #1', 'rey-core' ),
						'subtitle' => __( 'Subtitle Text #1', 'rey-core' ),
						'button_text' => __( 'Button Text #1', 'rey-core' ),
						'button_url' => [
							'url' => '#',
						],
					],
					[
						'image' => [
							'url' => \Elementor\Utils::get_placeholder_image_src(),
						],
						'captions' => 'yes',
						'label' => __( 'Label Text #2', 'rey-core' ),
						'title' => __( 'Title Text #2', 'rey-core' ),
						'subtitle' => __( 'Subtitle Text #2', 'rey-core' ),
						'button_text' => __( 'Button Text #2', 'rey-core' ),
						'button_url' => [
							'url' => '#',
						],
					],
				]
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Image_Size::get_type(),
			[
				'name' => 'image', // Usage: `{name}_size` and `{name}_custom_dimension`, in this case `image_size` and `image_custom_dimension`.
				'default' => 'large',
				// 'separator' => 'before',
				'exclude' => ['custom'],
				// TODO: add support for custom size thumbnails #40
			]
		);

		$this->end_controls_section();

		/**
		 * Intro settings
		 */

		$this->start_controls_section(
			'section_intro_settings',
			[
				'label' => __( 'Intro Settings', 'rey-core' ),
			]
		);

		$this->add_control(
			'intro_animation',
			[
				'label' => esc_html__( 'Intro Animation', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'intro_duration',
			[
				'label' => __( 'Intro Duration', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 1000,
				'min' => 100,
				'max' => 5000,
				'step' => 50,
				'selectors' => [
					':root' => '--cover-sideslide-active-transition-delay: {{VALUE}}ms',
				],
				'condition' => [
					'intro_animation!' => '',
				],
			]
		);

		$this->add_control(
			'intro_logo_enable',
			[
				'label' => __( 'Enable Intro Logo', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [
					'intro_animation!' => '',
				],
			]
		);

		$this->add_control(
			'intro_logo',
			[
			   'label' => __( 'Intro Logo', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::MEDIA,
				'default' => [],
				'condition' => [
					'intro_logo_enable!' => '',
				],
			]
		);

		$this->add_control(
			'intro_logo_position_x',
			[
			   'label' => __( 'Intro Logo Position - Horizontal', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ '%' ],
				'range' => [
					'%' => [
						'min' => 0,
						'max' => 100,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => '%',
					'size' => 50,
				],
				'selectors' => [
					'{{WRAPPER}} .cSlide-logo' => 'left: {{SIZE}}{{UNIT}};',
					'.rtl {{WRAPPER}} .cSlide-logo' => 'right: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'intro_logo_enable!' => '',
				],
			]
		);

		$this->add_control(
			'intro_logo_position_y',
			[
			   'label' => __( 'Intro Logo Position - Vertical', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ '%' ],
				'range' => [
					'%' => [
						'min' => 0,
						'max' => 100,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => '%',
					'size' => 50,
				],
				'selectors' => [
					'{{WRAPPER}} .cSlide-logo' => 'top: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'intro_logo_enable!' => '',
				],
			]
		);

		$is_animated_action = ($body_classes = (string) get_field('rey_body_class')) && strpos($body_classes, 'rey-animated-header') !== false ? 'disable' : 'enable';

		$this->add_control(
			'animated_header',
			[
				'label' => __( 'Animated Header', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::BUTTON,
				'button_type' => 'default',
				'text' => ucfirst($is_animated_action),
				'event' => 'rey:editor:animated-header',
				'action' => $is_animated_action,
			]
		);

		$this->end_controls_section();

		/**
		 * Social Icons
		 */

		$this->start_controls_section(
			'section_social',
			[
				'label' => __( 'Social Icons', 'rey-core' ),
			]
		);

		$social_icons = new \Elementor\Repeater();

		$social_icons->add_control(
			'social',
			[
				'label' => __( 'Select icon', 'rey-core' ),
				'label_block' => true,
				'default' => 'wordpress',
				'type' => 'rey-ajax-list',
				'query_args' => [
					'request' => 'get_social_icons',
				],
			]
		);

		$social_icons->add_control(
			'link',
			[
				'label' => __( 'Link', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::URL,
				'label_block' => true,
				'default' => [
					'is_external' => 'true',
				],
				'placeholder' => __( 'https://your-link.com', 'rey-core' ),
			]
		);

		$this->add_control(
			'social_icon_list',
			[
				'label' => __( 'Social Icons', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $social_icons->get_controls(),
				'default' => [
					[
						'social' => 'facebook',
					],
					[
						'social' => 'twitter',
					],
					[
						'social' => 'google-plus',
					],
				],
				'title_field' => '{{{ social.replace( \'-\', \' \' ).replace( /\b\w/g, function( letter ){ return letter.toUpperCase() } ) }}}',
				'prevent_empty' => false,
			]
		);

		$this->add_control(
			'social_text',
			[
				'label' => __( '"Follow" Text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'FOLLOW US', 'rey-core' ),
				'placeholder' => __( 'eg: FOLLOW US', 'rey-core' ),
			]
		);

		$this->end_controls_section();

		/**
		 * Slider settings
		 */

		$this->start_controls_section(
			'section_slider',
			[
				'label' => __( 'Slider Settings', 'rey-core' ),
			]
		);

		$this->add_control(
			'autoplay',
			[
				'label' => __( 'Autoplay', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->add_control(
			'autoplay_duration',
			[
				'label' => __( 'Autoplay Duration (ms)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 5000,
				'min' => 2000,
				'max' => 20000,
				'step' => 50,
				'condition' => [
					'autoplay!' => '',
				],
			]
		);

		$this->add_control(
			'counter',
			[
				'label' => __( 'Slides Counter', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'arrows',
			[
				'label' => __( 'Arrows Navigation', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'effect',
			[
				'label' => __( 'Transition Effect', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'curtains',
				'options' => [
					'slide'  => __( 'Slide', 'rey-core' ),
					'fade'  => __( 'Fade', 'rey-core' ),
					'curtains'  => __( 'Curtains', 'rey-core' ),
				],
			]
		);

		// $this->add_control(
		// 	'direction',
		// 	[
		// 		'label' => __( 'Slide direction', 'rey-core' ),
		// 		'type' => \Elementor\Controls_Manager::SELECT,
		// 		'default' => 'vertical',
		// 		'options' => [
		// 			'horizontal'  => __( 'Horizontal', 'rey-core' ),
		// 			'vertical'  => __( 'Vertical', 'rey-core' ),
		// 		],
		// 		'condition' => [
		// 			'effect' => 'slide',
		// 		],
		// 	]
		// );

		$this->end_controls_section();

	/**
	 * Slider settings
	 */

		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Caption Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);


		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'label_typo',
				'label' => esc_html__('Label Typography', 'rey-core'),
				'selector' => '{{WRAPPER}} .cSslide-captionLabel',
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
				'selector' => '{{WRAPPER}} .cSslide-captionTitle',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'subtitle_typo',
				'label' => esc_html__('Sub-Title Typography', 'rey-core'),
				'selector' => '{{WRAPPER}} .cSslide-captionSubtitle',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'button_typo',
				'label' => esc_html__('Button Typography', 'rey-core'),
				'selector' => '{{WRAPPER}} .cSslide-captionBtn a',
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
				],
			]
		);

		$this->add_control(
			'button_bg_color',
			[
				'label' => esc_html__( 'Button Background Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cSslide-captionBtn .btn' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'button_bg_hover_color',
			[
				'label' => esc_html__( 'Button Background Hover Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cSslide-captionBtn .btn:hover' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'button_text_color',
			[
				'label' => esc_html__( 'Button Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cSslide-captionBtn .btn' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'button_text_hover_color',
			[
				'label' => esc_html__( 'Button Text Hover Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cSslide-captionBtn .btn:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_section();

	/**
	 * Misc. styles
	 */

		$this->start_controls_section(
			'section_misc_styles',
			[
				'label' => __( 'Slider Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

		$this->add_responsive_control(
			'slider__height',
			[
			   'label' => esc_html__( 'Slider Height', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'vh' ],
				'range' => [
					'px' => [
						'min' => 100,
						'max' => 1200,
						'step' => 1,
					],
					'vh' => [
						'min' => 10,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .rey-coverSideSlide' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'slider__left_margin',
			[
				'label' => esc_html__( 'Left edge size', 'rey-core' ) . ' (vw)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 23,
				'min' => 0,
				'max' => 1000,
				'step' => 0,
				'selectors' => [
					'{{WRAPPER}} .rey-coverSideSlide' => '--cover-sideslide-wrapper-left-margin: {{VALUE}}vw',
				],
				'render_type' => 'template',
			]
		);

		$this->add_responsive_control(
			'slider__margin_size',
			[
				'label' => esc_html__( 'Bottom / Right edge size', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 80,
				'min' => 0,
				'max' => 1000,
				'step' => 0,
				'selectors' => [
					'{{WRAPPER}} .rey-coverSideSlide' => '--cover-sideslide-margin-size: {{VALUE}}px',
				],
				'render_type' => 'template',
			]
		);

		$this->add_responsive_control(
			'slider__top_margin',
			[
				'label' => esc_html__( 'Top edge size', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 1000,
				'step' => 0,
				'selectors' => [
					'{{WRAPPER}} .rey-coverSideSlide' => '--cover-sideslide-top: {{VALUE}}px',
				],
				'render_type' => 'template',
				'separator' => 'after'
			]
		);

		$this->add_control(
			'loading_bg_color_1',
			[
				'label' => __( 'Loader Curtain Color (1st)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cSslide-effectBg--1' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'loading_bg_color_2',
			[
				'label' => __( 'Loader Curtain Color (2nd)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cSslide-effectBg--2' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'mobile_heading',
			[
			   'label' => esc_html__( 'MOBILES', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'mobile_align',
			[
				'label' => __( 'Alignment', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'default' => 'left',
				'options' => [
					'left' => [
						'title' => __( 'Left', 'rey-core' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'rey-core' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => __( 'Right', 'rey-core' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'selectors' => [
					'(mobile){{WRAPPER}} .cSslide-caption' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'mobile_arrows',
			[
				'label' => esc_html__( 'Show arrows', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'return_value' => '--mob-arrows',
				'prefix_class' => ''
			]
		);

		$this->end_controls_section();
	}

	public function render_start(){

		$classes = [
			'rey-coverSideSlide',
			'--effect-' . $this->_settings['effect'],
			$this->_settings['intro_animation'] === '' ? '--no-anim' : '',

		];

		if( ! reycore__preloader_is_active() ){
			$classes[] = '--loading';
		}

		$this->add_render_attribute( 'wrapper', 'class', $classes );

		if( count($this->_items) > 1 ) {

			$slide_settings = [
				'type' => $this->_settings['effect'] === 'slide' ? 'slide' : 'fade',
				'autoplay' => $this->_settings['autoplay'] !== '',
				'interval' => $this->_settings['autoplay_duration'],
				'effect' => $this->_settings['effect'],
				'vertical' => false,
				// 'vertical' => $this->_settings['direction'] === 'vertical',
				// 'verticalSwiping' => $this->_settings['direction'] === 'vertical',
				'customArrows' => $this->_settings['arrows'] !== '' ? '.__arrows-' . $this->get_id() : '',
				'intro' => $this->_settings['intro_animation'] !== '',
			];

			$this->add_render_attribute( 'wrapper', 'data-slider-settings', wp_json_encode($slide_settings) );
		}
		?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
			<div class="cSslide-effectBg cSslide-effectBg--1 cSslide--abs"></div>
			<div class="cSslide-effectBg cSslide-effectBg--2 cSslide--abs"></div>
				<?php
	}

	public function render_end(){
		?></div><?php
		echo \ReyCore\Elementor\Helper::edit_mode_widget_notice(['full_viewport', 'tabs_modal']);
	}

	public function render_slides()
	{
		?>
		<div class="cSslide-sliderWrapper">
			<div class="splide">
				<div class="cSslide-effectBg-slide cSslide-effectBg-slide--1 cSslide--abs"></div>
				<div class="cSslide-effectBg-slide cSslide-effectBg-slide--2 cSslide--abs"></div>
				<div class="splide__track">
					<div class="splide__list cSslide-slider">
						<?php
						if( !empty($this->_items) ):
							foreach($this->_items as $key => $item): ?>
							<div class="splide__slide cSslide-slide elementor-repeater-item-<?php echo $item['_id'] ?> <?php echo ($key === 0 ? 'is-active':'') ?>">
								<?php
								$slide_content = '';

								$slide_image = reycore__get_attachment_image( [
									'image' => $item['image'],
									'size' => $this->_settings['image_size'],
									'attributes' => ['class'=>'cSslide-slideContent cSslide--abs']
								] );

								if( $item['slide_type'] === 'video' )
								{
									reycore_assets()->add_scripts('rey-videos');

									if( $slide_image ){
										reycore_assets()->add_styles('reycore-videos');
									}

									$slide_content = reycore__get_video_html([
										'video_url' => $item['html_video'],
										'class' => 'cSslide-slideContent cSslide--abs',
										'mobile' => isset($item['video_play_on_mobile']) && $item['video_play_on_mobile'] === 'yes',
									]);
								}

								elseif( $item['slide_type'] === 'youtube' )
								{
									reycore_assets()->add_scripts('rey-videos');

									if( $slide_image ){
										reycore_assets()->add_styles('reycore-videos');
									}

									$slide_content = reycore__get_youtube_iframe_html([
										'video_url' => $item['yt_video'],
										'class' => 'cSslide-slideContent cSslide--abs',
										'html_id' => 'yt' . $item['_id'],
										// only show video preview if image not provided
										'add_preview_image' => empty($slide_image),
										'mobile' => isset($item['video_play_on_mobile']) && $item['video_play_on_mobile'] === 'yes',
									]);

								}

								echo $slide_content;
								echo $slide_image;
								?>
							</div>
							<?php
							endforeach;
						endif; ?>
					</div>

					<?php
					if( isset($this->_settings['intro_logo']['id']) ) {
						printf( '<div class="cSlide-logo"><div class="cSlide-logoInner ">%s</div></div>', wp_get_attachment_image($this->_settings['intro_logo']['id'], 'full', false) );
					} ?>
				</div>

				<?php $this->render_arrows(); ?>

			</div>
		</div>
		<?php
	}

	public function render_counter( $item )
	{
		if( $this->_settings['counter'] === 'yes' && $this->_items > 1 ): ?>
			<div class="cSslide-counter cSslide-captionEl">
				<span class="cSslide-counterCurrent"><?php printf("%02d", $item + 1) ?></span>
				<span class="cSslide-counterTotal"><?php printf("%02d", count($this->_items) ) ?></span>
			</div>
		<?php endif;
	}

	public function render_captions()
	{
		if( empty($this->_items) ) {
			return;
		} ?>

		<div class="cSslide-captions" >
			<?php
			foreach($this->_items as $key => $item): ?>
				<div class="cSslide-caption elementor-repeater-item-<?php echo $item['_id'] ?>">

					<?php if( $item['captions'] !== '' ): ?>

						<?php $this->render_counter($key); ?>

						<?php if( $label = $item['label'] ): ?>
						<div class="cSslide-captionEl cSslide-captionLabel"><?php echo $label ?></div>
						<?php endif; ?>

						<?php if( $title = $item['title'] ): ?>
						<h2 class="cSslide-captionEl cSslide-captionTitle"><?php echo $title ?></h2>
						<?php endif; ?>

						<?php if( $subtitle = $item['subtitle'] ): ?>
						<div class="cSslide-captionEl cSslide-captionSubtitle"><?php echo $subtitle ?></div>
						<?php endif; ?>

						<?php if( $button_text = $item['button_text'] ): ?>
							<div class="cSslide-captionEl cSslide-captionBtn">

								<?php
								$url_key = 'url'.$key;

								reycore_assets()->add_styles('rey-buttons');
								$this->add_render_attribute( $url_key , 'class', 'btn ' . $this->_settings['button_style'] );

								if( isset($item['button_url']['url']) && $url = $item['button_url']['url'] ){
									$this->add_render_attribute( $url_key , 'href', $url );

									if( $item['button_url']['is_external'] ){
										$this->add_render_attribute( $url_key , 'target', '_blank' );
									}

									if( $item['button_url']['nofollow'] ){
										$this->add_render_attribute( $url_key , 'rel', 'nofollow' );
									}
								} ?>
								<a <?php echo  $this->get_render_attribute_string($url_key); ?>>
									<?php echo $button_text; ?>
								</a>
							</div>
							<!-- .cSslide-btn -->
						<?php endif; ?>

					<?php endif; ?>

				</div><?php
			endforeach; ?>
		</div>
		<?php
	}

	public function render_nav(){
		if( !empty($this->_items) ){

			$bullets = '';

			for( $i = 0; $i < count($this->_items); $i++ ){
				$bullets .= sprintf( '<button data-go="%1$d" aria-label="%2$s %1$d"></button>', $i, esc_html__('Go to slide ', 'rey-core') );
			}

			printf('<div class="cSslide-nav __pagination-%s">%s</div>', $this->get_id(), $bullets );
		}
	}

	public function render_arrows()
	{
		if( $this->_settings['arrows'] === 'yes' ): ?>
			<div class="cSslide-arrows __arrows-<?php echo $this->get_id() ?>">
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

	public function render_social(){

		if( $social_icon_list = $this->_settings['social_icon_list'] ): ?>

			<div class="cSslide-social">

				<?php if($social_text = $this->_settings['social_text']): ?>
					<div class="cSslide-socialText"><?php echo $social_text ?></div>
				<?php endif; ?>

				<div class="cSslide-socialIcons">
					<?php
					foreach ( $social_icon_list as $index => $item ):

						$link_key = 'link_' . $index;

						$this->add_render_attribute( $link_key, 'href', $item['link']['url'] );

						if ( $item['link']['is_external'] ) {
							$this->add_render_attribute( $link_key, 'target', '_blank' );
						}

						if ( $item['link']['nofollow'] ) {
							$this->add_render_attribute( $link_key, 'rel', 'nofollow' );
						}
						?>
						<a class="cSslide-socialIcons-link" rel="noreferrer" <?php echo $this->get_render_attribute_string( $link_key ); ?>>
							<?php echo reycore__get_svg_social_icon([ 'id'=>$item['social'] ]); ?>
						</a>
					<?php endforeach; ?>
				</div>

			</div>
			<!-- .cSslide-social -->
		<?php endif;
	}


	protected function render() {

		$this->_settings = $this->get_settings_for_display();
		$this->_items = $this->_settings['items'];

		$this->render_start();
		$this->render_slides();
		$this->render_captions();
		$this->render_social();
		$this->render_end();

		reycore_assets()->add_styles([$this->get_style_name(), 'rey-splide']);
		reycore_assets()->add_scripts( $this->rey_get_script_depends() );

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
