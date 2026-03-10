<?php
namespace ReyCore\Elementor\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
exit; // Exit if accessed directly.
}

class CoverBlurry extends \ReyCore\Elementor\WidgetsBase {

	private $_settings = [];
	private $_items = [];
	private $no_animated_entry;

	public static function get_rey_config(){
		return [
			'id' => 'cover-blurry',
			'title' => __( 'Cover - Blurry Slider', 'rey-core' ),
			'icon' => 'rey-font-icon-general-r',
			'categories' => [ 'rey-theme-covers' ],
			'keywords' => [],
			'css' => [
				'!assets/style[rtl].css',
			],
			'js' => [
				'assets/script.js',
			],
		];
	}

	public function __construct( $data = [], $args = null ) {

		do_action('reycore/elementor/widget/construct', $data);

		if( ! empty($data) ){
			\ReyCore\Plugin::instance()->elementor->frontend->add_delay_js_scripts('cover-blurry', ['rey-script', 'rey-splide', 'splidejs', 'rey-videos']);
		}

		parent::__construct( $data, $args );
	}

	public function rey_get_script_depends() {
		return [ 'rey-splide', 'splidejs', 'reycore-widget-cover-blurry-scripts' ];
	}

	public function get_custom_help_url() {
		return reycore__support_url('kb/rey-elements-covers/#blurry-slider');
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

		$selectors = [
			'title' => '',
		];

		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Content', 'rey-core' ),
			]
		);

		$items = new \Elementor\Repeater();

		$items->start_controls_tabs( 'slides_repeater' );

			$items->start_controls_tab( 'media', [ 'label' => esc_html__( 'Media', 'rey-core' ) ] );

				$items->add_control(
					'bg_type',
					[
						'label' => __( 'Background Type', 'rey-core' ),
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
					'video_play_on_mobile',
					[
						'label' => esc_html__( 'Play video on mobile', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::SWITCHER,
						'default' => '',
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
					'image',
					[
					'label' => __( 'Image', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::MEDIA,
						'default' => [
							'url' => \Elementor\Utils::get_placeholder_image_src(),
						],
						'dynamic' => [
							'active' => true,
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
					'overlay_color',
					[
						'label' => __( 'Overlay Background-Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} {{CURRENT_ITEM}} .cBlurry-slideOverlay' => 'background-color: {{VALUE}}',
						],
						'dynamic' => [
							'active' => true,
						],
					]
				);

				$items->add_control(
					'blur_overlay_color',
					[
						'label' => __( 'Blur Background-Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} {{CURRENT_ITEM}} .cBlurry-slideBlur .cBlurry-slideOverlay' => 'background-color: {{VALUE}}',
						],
						'dynamic' => [
							'active' => true,
						],
					]
				);

			$items->end_controls_tab();

			$items->start_controls_tab( 'caption', [ 'label' => esc_html__( 'Captions', 'rey-core' ) ] );

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
						'dynamic' => [
							'active' => true,
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
						'dynamic' => [
							'active' => true,
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
						'dynamic' => [
							'active' => true,
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

			$items->end_controls_tab();

			$items->start_controls_tab( 'style', [ 'label' => esc_html__( 'Styles', 'rey-core' ) ] );

				$items->add_control(
					'align',
					[
						'label' => esc_html__( 'Horizontal Alignment', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::SELECT,
						'default' => '',
						'options' => [
							''  => esc_html__( '- Default -', 'rey-core' ),
							'start'  => esc_html__( 'Start', 'rey-core' ),
							'center'  => esc_html__( 'Center', 'rey-core' ),
							'end'  => esc_html__( 'End', 'rey-core' ),
						],
						'dynamic' => [
							'active' => true,
						],
					]
				);

				$items->add_control(
					'text_color',
					[
						'label' => __( 'Text Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} {{CURRENT_ITEM}} .cBlurry-caption' => 'color: {{VALUE}}',
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
						'dynamic' => [
							'active' => true,
						],
					]
				);

			$items->end_controls_tab();

		$items->end_controls_tabs();

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
				],
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
				'label' => __( 'Show Elements', 'rey-core' ),
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

		$this->add_control(
			'social_color',
			[
				'label' => esc_html__( 'Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cBlurry-social' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'social_icon_size',
			[
				'label' => esc_html__( 'Icon Size', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .cBlurry-socialIcons-link' => 'font-size: {{VALUE}}px;',
				],
			]
		);

		$this->end_controls_section();

		/**
		 * Scroll Button
		 */

		$this->start_controls_section(
			'section_scroll',
			[
				'label' => __( 'Scroll Button', 'rey-core' ),
			]
		);

		$this->add_control(
			'scroll_decoration',
			[
				'label' => __( 'Show Scroll Decoration', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'scroll_text',
			[
				'label' => __( '"Scroll" Text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'SCROLL', 'rey-core' ),
				'placeholder' => __( 'eg: SCROLL', 'rey-core' ),
				'condition' => [
					'scroll_decoration' => 'yes',
				],
			]
		);

		$this->add_control(
			'scroll_color',
			[
				'label' => esc_html__( 'Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cBlurry-scroll' => 'color: {{VALUE}}',
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
				'default' => 9000,
				'min' => 3500,
				'max' => 20000,
				'step' => 50,
				'condition' => [
					'autoplay!' => '',
				],
			]
		);

		$this->add_control(
			'pause_on_hover',
			[
				'label' => __( 'Pause on hover', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
				'condition' => [
					'autoplay!' => '',
				],
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
			'bars_nav',
			[
				'label' => __( 'Bars Navigation', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'transition_factor',
			[
				'label' => _x( 'Transition Factor', 'Elementor control title', 'rey-core' ),
				'description' => _x( 'If you\'ll increase the transition factor, the transition speed will be slower.', 'Elementor control description', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 1,
				'min' => 0.5,
				'max' => 3,
				'step' => 0.1,
				'selectors' => [
					'{{WRAPPER}} .rey-coverBlurry' => '--transition-factor: {{VALUE}}',
				],
			]
		);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_general_style',
			[
				'label' => __( 'General Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'animated_entry',
			[
				'label' => esc_html__( 'Animated Entry', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
				'separator' => 'after'
			]
		);

		$this->add_control(
			'bg_color',
			[
				'label' => esc_html__( 'Background Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-coverBlurry' => '--bg-color: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'custom_height',
			[
				'label' => __( 'Height', 'rey-core' ),
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
					'{{WRAPPER}} .cBlurry-slides' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);


		$this->add_control(
			'blur',
			[
				'label' => esc_html__( 'Enable Blur', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'blur_backdrop',
			[
				'label' => esc_html__( 'Blur with backdrop filter', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HIDDEN,
				'default' => '',
			]
		);

		$this->add_responsive_control(
			'blur_width',
			[
				'label' => __( 'Blur Overlay Width', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 3,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .cBlurry-slide.is-active .cBlurry-slideBlur.--classic' => 'width: {{SIZE}}%;',
					'{{WRAPPER}} .cBlurry-slide.is-active .cBlurry-slideBlur.--backdrop' => 'transform: scaleX( calc( {{SIZE}} / 100 ) );'
				],
				'condition' => [
					'blur' => 'yes',
				],
			]
		);

		$this->add_control(
			'blur_align',
			[
				'label' => esc_html__( 'Right-aligned Blur Overlay', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [
					'blur' => 'yes',
				],
			]
		);

		$this->add_control(
			'content_title',
			[
				'label' => esc_html__( 'Content', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'content_width',
			[
				'label' => __( 'Content Width', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1440,
					],
					'vw' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'size_units' => [ 'px', 'vh', 'vw' ],
				'selectors' => [
					'{{WRAPPER}} .rey-coverBlurry' => '--c-max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'x_align',
			[
				'label' => esc_html__( 'Horizontal Alignment', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'start',
				'options' => [
					'start'  => esc_html__( 'Start', 'rey-core' ),
					'center'  => esc_html__( 'Center', 'rey-core' ),
					'end'  => esc_html__( 'End', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'y_align',
			[
				'label' => esc_html__( 'Vertical Alignment', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'center',
				'options' => [
					'start'  => esc_html__( 'Start', 'rey-core' ),
					'center'  => esc_html__( 'Center', 'rey-core' ),
					'end'  => esc_html__( 'End', 'rey-core' ),
				],
			]
		);


		$this->add_responsive_control(
			'footer_distance',
			[
				'label' => __( 'Footer bottom-distance', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'selectors' => [
					'{{WRAPPER}} .rey-coverBlurry' => '--bottom-distance: {{VALUE}}px;',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_slides_style',
			[
				'label' => __( 'Slides Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'label_typo',
				'label' => esc_html__('Label Typography', 'rey-core'),
				'selector' => '{{WRAPPER}} .cBlurry-captionLabel',
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
				'selector' => '{{WRAPPER}} .cBlurry-captionTitle',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'subtitle_typo',
				'label' => esc_html__('Sub-Title Typography', 'rey-core'),
				'selector' => '{{WRAPPER}} .cBlurry-captionSubtitle',
			]
		);

		$this->add_control(
			'button_heading',
			[
			   'label' => esc_html__( 'Button Styles', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'button_style',
			[
				'label' => __( 'Button Style', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'btn-primary-outline btn-dash btn-rounded',
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

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'button_typo',
				'label' => esc_html__('Button Typography', 'rey-core'),
				'selector' => '{{WRAPPER}} .cBlurry-captionBtn .btn',
			]
		);

		$this->add_control(
			'btn_color',
			[
				'label' => esc_html__( 'Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cBlurry-captionBtn .btn' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'btn_bg_color',
			[
				'label' => esc_html__( 'Background Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cBlurry-captionBtn .btn' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'btn_color_hover',
			[
				'label' => esc_html__( 'Hover Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cBlurry-captionBtn .btn:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'btn_bg_color_hover',
			[
				'label' => esc_html__( 'Hover Background Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cBlurry-captionBtn .btn:hover' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'btn_border_radius',
			[
				'label' => esc_html__( 'Border radius', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 1000,
				'step' => 0,
				'selectors' => [
					'{{WRAPPER}} .cBlurry-captionBtn .btn' => 'border-radius: {{VALUE}}px',
				],
			]
		);

		$this->end_controls_section();

		/* ------------------------------------ Arrows ------------------------------------ */

		$this->start_controls_section(
			'section_arrow_styles',
			[
				'label' => __( 'Arrow Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'arrows!' => '',
				],
			]
		);

		$this->add_control(
			'arrows_layout',
			[
				'label' => esc_html__( 'Arrows Layout', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'edges',
				'options' => [
					'edges'  => esc_html__( 'Screen edges', 'rey-core' ),
					'side'  => esc_html__( 'Stacked on a side', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'arrows_type',
			[
				'label' => esc_html__( 'Arrows Type', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => esc_html__( 'Default', 'rey-core' ),
					'chevron'  => esc_html__( 'Chevron', 'rey-core' ),
					'custom'  => esc_html__( 'Custom Icon', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'arrows_custom_icon',
			[
				'label' => __( 'Custom Icon (Right)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::ICONS,
				'condition' => [
					'arrows_type' => 'custom',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_arrows_styles' );

			$this->start_controls_tab(
				'tabs_arrows_styles_normal',
				[
					'label' => esc_html__( 'Normal', 'rey-core' ),
				]
			);

				$this->add_control(
					'arrow_text_color',
					[
						'label' => __( 'Text Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .cBlurry-arrow' => 'color: {{VALUE}}',
						],
					]
				);

				$this->add_control(
					'arrow_bg_color',
					[
						'label' => __( 'Background Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .cBlurry-arrow' => 'background-color: {{VALUE}}',
						],
					]
				);

				$this->add_responsive_control(
					'arrow_border_width',
					[
						'label' => __( 'Border Width', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', 'em', '%' ],
						'selectors' => [
							'{{WRAPPER}} .cBlurry-arrow' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_control(
					'arrow_border_color',
					[
						'label' => __( 'Border Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .cBlurry-arrow' => 'border-color: {{VALUE}};',
						],
					]
				);

			$this->end_controls_tab();

			$this->start_controls_tab(
				'tabs_arrows_styles_hover',
				[
					'label' => esc_html__( 'Active', 'rey-core' ),
				]
			);

				$this->add_control(
					'arrow_text_color_active',
					[
						'label' => __( 'Text Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .cBlurry-arrow:hover' => 'color: {{VALUE}}',
						],
					]
				);

				$this->add_control(
					'arrow_bg_color_active',
					[
						'label' => __( 'Background Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .cBlurry-arrow:hover' => 'background-color: {{VALUE}}',
						],
					]
				);

				$this->add_responsive_control(
					'arrow_border_width_active',
					[
						'label' => __( 'Border Width', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', 'em', '%' ],
						'selectors' => [
							'{{WRAPPER}} .cBlurry-arrow:hover' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_control(
					'arrow_border_color_active',
					[
						'label' => __( 'Border Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .cBlurry-arrow:hover' => 'border-color: {{VALUE}};',
						],
					]
				);

			$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_responsive_control(
			'arrow_border_radius',
			[
				'label' => __( 'Border Radius', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .cBlurry-arrow' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'arrow_width',
			[
				'label' => esc_html__( 'Arrow Width', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 1000,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .cBlurry-arrow' => '--arrow-width: {{VALUE}}px;',
				],
				'separator' => 'before'
			]
		);

		$this->add_responsive_control(
			'arrow_height',
			[
				'label' => esc_html__( 'Arrow Height', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 1000,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .cBlurry-arrow' => '--arrow-height: {{VALUE}}px;',
				],
			]
		);

		$this->add_responsive_control(
			'arrow_size',
			[
				'label' => esc_html__( 'Arrow Size', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 1000,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .cBlurry-arrow' => 'font-size: {{VALUE}}px;',
				],
			]
		);

		$this->add_control(
			'arrow_on_hover',
			[
				'label' => esc_html__( 'Show arrows on hover', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->end_controls_section();

		/* ------------------------------------ Bars ------------------------------------ */

		$this->start_controls_section(
			'section_bars_styles',
			[
				'label' => __( 'Bars Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'bars_nav!' => '',
				],
			]
		);

		$this->add_control(
			'bars_color',
			[
				'label' => __( 'Bars Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cBlurry-nav button' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'bars_width',
			[
				'label' => esc_html__( 'Bars Width', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 1000,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .cBlurry-nav' => '--bars-width: {{VALUE}}px;',
				],
			]
		);

		$this->add_responsive_control(
			'bars_height',
			[
				'label' => esc_html__( 'Bars Height', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 1000,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .cBlurry-nav' => '--bars-height: {{VALUE}}px;',
				],
			]
		);

		$this->end_controls_section();

	}

	public function render_start(){

		$classes = [
			'rey-coverBlurry',
		];

		if( $this->_settings['blur'] === 'yes' ){
			$classes[] = '--hasBlur';
			if( $this->_settings['blur_align'] === 'yes' ){
				$classes[] = '--blurAlign-right';
			}
		}

		if( $this->_settings['arrow_on_hover'] !== '' ){
			$classes[] = '--arr-hover';
		}

		$classes['start'] = '--loading';

		$this->no_animated_entry = $this->_settings['animated_entry'] === '' || reycore__js_is_delayed() || reycore__preloader_is_active();

		if( $this->no_animated_entry ){
			$classes['start'] = '--init';
		}

		$this->add_render_attribute( 'wrapper', 'class', $classes );

		if( count($this->_items) > 1 ) {
			$this->add_render_attribute( 'wrapper', 'data-slider-settings', wp_json_encode([
				'autoplay' => $this->_settings['autoplay'] !== '',
				'autoplaySpeed' => $this->_settings['autoplay_duration'],
				'pauseOnHover' => $this->_settings['pause_on_hover'] !== '',
				'delayInit' => 500,
				'customArrows' => '.__arrows-' . $this->get_id(),
				'customPagination' => '.__pagination-' . $this->get_id(),
			]) );
		}
		?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
			<div class="cBlurry-loadingBg cBlurry--abs"></div>
		<?php
	}

	public function render_end(){
		?>
		</div>
		<?php
		echo \ReyCore\Elementor\Helper::edit_mode_widget_notice(['full_viewport', 'tabs_modal']);
	}

	public function render_slides(){
		?>
		<div class="cBlurry-wrapper splide">
		<div class="splide__track">
		<div class="cBlurry-slides splide__list">
		<?php
			if( !empty($this->_items) ):
				foreach($this->_items as $key => $item):

					$classes = [
						'splide__slide',
						'cBlurry-slide',
						'--' . esc_attr($item['bg_type']),
						'elementor-repeater-item-' . esc_attr($item['_id']),
						'align' => '--content-x-' . $this->_settings['x_align'],
						'--content-y-' . $this->_settings['y_align'],
					];

					if( $this->no_animated_entry && 0 === $key ){
						$classes[] = 'is-active';
					}

					if( isset($item['align']) && $item['align'] !== '' ){
						$classes['align'] = '--content-x-' . $item['align'];
					}

					$slide_content = '';

					$slide_image = reycore__get_attachment_image( [
						'image' => $item['image'],
						'size' => $this->_settings['image_size'],
						'attributes' => ['class'=>'cBlurry-slideContent cBlurry--abs']
					] );

					if( $item['bg_type'] === 'video' ) {

						reycore_assets()->add_scripts( 'rey-videos' );

						if( $slide_image ){
							reycore_assets()->add_styles('reycore-videos');
						}

						$slide_content = reycore__get_video_html([
							'video_url' => $item['html_video'],
							'class' => 'cBlurry-slideContent cBlurry--abs',
							'mobile' => isset($item['video_play_on_mobile']) && $item['video_play_on_mobile'] === 'yes',
						]);
					}

					elseif( $item['bg_type'] === 'youtube' ) {

						reycore_assets()->add_scripts( 'rey-videos' );

						if( $slide_image ){
							reycore_assets()->add_styles('reycore-videos');
						}

						$slide_content = reycore__get_youtube_iframe_html([
							'video_url' => $item['yt_video'],
							'class' => 'cBlurry-slideContent cBlurry--abs ',
							'html_id' => 'yt' . $item['_id'],
							// only show video preview if image not provided
							'add_preview_image' => empty($slide_image),
							'mobile' => isset($item['video_play_on_mobile']) && $item['video_play_on_mobile'] === 'yes',
						]);
					}

					if( $slide_content || $slide_image ){ ?>

						<div class="<?php echo implode(' ', $classes); ?>">

							<div class="cBlurry-slideContent-wrapper cBlurry--abs">
								<?php
									echo $slide_content;
									echo $slide_image;
								?>
								<div class="cBlurry-slideOverlay cBlurry--abs"></div>
							</div>

							<?php

							$use_backdrop = $this->_settings['blur_backdrop'] !== '';

							if( $this->_settings['blur'] === 'yes' ): ?>

								<div class="cBlurry-slideBlur cBlurry--abs <?php echo $use_backdrop || ($item['bg_type'] === 'youtube') ? '--backdrop' : '--classic' ?>">
									<?php echo $item['bg_type'] == 'image' && !$use_backdrop ? $slide_image : ''; ?>
									<div class="cBlurry-slideOverlay cBlurry--abs"></div>
								</div>

							<?php endif; ?>

							<div class="cBlurry-slideBg cBlurry--abs"></div>

							<?php $this->render_caption($item, $key); ?>

						</div>

					<?php } ?>

				<?php
				endforeach;
			endif; ?>
		</div>
		</div>

		<?php $this->render_arrows(); ?>

		</div>
		<?php
	}


	public function render_caption( $slide, $key )
	{
		if( $slide['captions'] !== '' ): ?>

			<div class="cBlurry-caption">

				<?php if( $label = $slide['label'] ): ?>
				<div class="cBlurry-captionEl cBlurry-captionLabel"><?php echo $label ?></div>
				<?php endif; ?>

				<?php if( $title = $slide['title'] ): ?>
					<h2 class="cBlurry-captionEl cBlurry-captionTitle"><?php echo $title ?></h2>
				<?php endif; ?>

				<?php if( $subtitle = $slide['subtitle'] ): ?>
					<div class="cBlurry-captionEl cBlurry-captionSubtitle"><?php echo $subtitle ?></div>
				<?php endif; ?>

				<?php if( $button_text = $slide['button_text'] ): ?>
					<div class="cBlurry-captionEl cBlurry-captionBtn">

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
					<!-- .cBlurry-btn -->
				<?php endif; ?>

			</div><?php
		endif;
	}

	public function render_arrows()
	{
		if( $this->_settings['arrows'] !== 'yes' ){
			return;
		} ?>

		<div class="cBlurry-arrows __arrows-<?php echo $this->get_id() ?> --l-<?php echo esc_attr($this->_settings['arrows_layout']) ?>">

			<?php
			$custom_svg_icon = '';

			if( 'custom' === $this->_settings['arrows_type'] &&
				($custom_icon = $this->_settings['arrows_custom_icon']) && isset($custom_icon['value']) && !empty($custom_icon['value']) ){
				ob_start();
				\Elementor\Icons_Manager::render_icon( $custom_icon, [ 'aria-hidden' => 'true', 'class' => '' ] );
				$custom_svg_icon = ob_get_clean();
			}

			reycore__svg_arrows([
				'type' => $this->_settings['arrows_type'],
				'custom_icon' => $custom_svg_icon,
				'class' => 'cBlurry-arrow',
				'attributes' => [
					'left' => 'data-dir="<"',
					'right' => 'data-dir=">"',
				]
			]); ?>
		</div>
		<?php
	}

	public function render_footer(){

		$nav = $this->_settings['bars_nav'] === 'yes';
		$social = !empty($this->_settings['social_icon_list']);
		$scroll = $this->_settings['scroll_decoration'] === 'yes';

		$classes = !$social ? '--no-social' : '';
		$classes .= !$scroll ? '--no-scroll' : '';

		?>
		<div class="cBlurry-footer">
			<div class="cBlurry-footerInner <?php echo esc_attr($classes) ?>">
			<?php
				if( $social ){
					$this->render_social();
				}
				if( $nav ){
					$this->render_nav();
				}
				if( $scroll ){
					$this->render_scroll();
				}
			?>
			</div>
		</div>
		<?php
	}

	public function render_scroll()
	{ ?>
		<div class="cBlurry-scroll">
			<a href="#" class="rey-scrollDeco rey-scrollDeco--default" data-target="next" data-lazy-hidden>
				<span class="rey-scrollDeco-text"><?php echo $this->_settings['scroll_text'] ?></span>
				<span class="rey-scrollDeco-line"></span>
			</a>
		</div><?php
		reycore_assets()->add_styles('reycore-elementor-scroll-deco');
		reycore_assets()->add_scripts('reycore-elementor-scroll-deco');
	}

	public function render_nav()
	{
		if( !empty($this->_items) ){

			$bullets = '';

			for( $i = 0; $i < count($this->_items); $i++ ){
				$bullets .= sprintf( '<button data-go="%1$d" aria-label="%2$s %1$d"></button>', $i, esc_html__('Go to slide ', 'rey-core') );
			}

			printf('<div class="cBlurry-nav __pagination-%s">'. $bullets .'</div>', $this->get_id());
		}
	}

	public function render_social(){

		if( $social_icon_list = $this->_settings['social_icon_list'] ): ?>

			<div class="cBlurry-social">

				<?php if($social_text = $this->_settings['social_text']): ?>
					<div class="cBlurry-socialText"><?php echo $social_text ?></div>
				<?php endif; ?>

				<div class="cBlurry-socialIcons">
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
						<a class="cBlurry-socialIcons-link" rel="noreferrer" <?php echo $this->get_render_attribute_string( $link_key ); ?>>
							<?php echo reycore__get_svg_social_icon([ 'id'=>$item['social'] ]); ?>
						</a>
					<?php endforeach; ?>
				</div>

			</div>
			<!-- .cBlurry-social -->
		<?php endif;
	}

	protected function render() {

		$this->_settings = $this->get_settings_for_display();
		$this->_items = apply_filters('reycore/elementor/blurry_slider/items', $this->_settings['items'], $this->get_id());

		$this->render_start();
		$this->render_slides();

		$this->render_footer();
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
