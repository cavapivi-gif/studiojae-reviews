<?php
namespace ReyCore\Elementor\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class CoverSplit extends \ReyCore\Elementor\WidgetsBase {

	private $_items = [];

	public $_settings = [];

	public static function get_rey_config(){
		return [
			'id' => 'cover-split',
			'title' => __( 'Cover - Split Slider', 'rey-core' ),
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

		do_action('reycore/elementor/widget/construct', $data);

		if( ! empty($data) ):

			\ReyCore\Plugin::instance()->elementor->frontend->add_delay_js_scripts('cover-split', ['rey-script', 'rey-videos']);

		endif;

		parent::__construct( $data, $args );
	}

	public function rey_get_script_depends() {
		return ['reycore-widget-cover-split-scripts' ];
	}

	public function get_custom_help_url() {
		return reycore__support_url('kb/rey-elements-covers/#split-slider');
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
	/**
	 * Main Slide
	 */

		$this->start_controls_section(
			'section_main_content',
			[
				'label' => __( 'Main Slide', 'rey-core' ),
			]
		);

		$this->add_control(
			'main_slide',
			[
				'label' => esc_html__( 'Enable Main Slide', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->start_controls_tabs( 'main_slide_tabs', [
			'condition' => [
				'main_slide' => 'yes',
			],
		] );

			$this->start_controls_tab( 'media', [ 'label' => esc_html__( 'Media', 'rey-core' ) ] );

				$this->add_group_control(
					\Elementor\Group_Control_Background::get_type(),
					[
						'label' => esc_html__('Background', 'rey-core'),
						'name' => 'main_background',
						'types' => [ 'classic', 'gradient', 'video' ],
						'selector' => '{{WRAPPER}} .cSplit-slide--mainBg',
						'prefix_class' => 'cSplit-slide--mainBg--',
						'frontend_available' => false,
						'condition' => [
							'main_slide' => 'yes',
						],
						'exclude' => [
							'attachment',
							'repeat',
							'size',
							'bg_width',
						],
						'fields_options' => [
							'image' => [
								'selectors' => [
									'{{SELECTOR}}' => 'background-image: none;',
								],
							],
							'position' => [
								'selectors' => [
									'{{SELECTOR}}' => '--ob-pos: {{VALUE}};',
								],
								'separator' => 'none'
							],
							'xpos' => [
								'selectors' => [
									'{{SELECTOR}}' => '--ob-pos: {{SIZE}}{{UNIT}} {{ypos.SIZE}}{{ypos.UNIT}};',
								],
							],
							'ypos' => [
								'selectors' => [
									'{{SELECTOR}}' => '--ob-pos: {{xpos.SIZE}}{{xpos.UNIT}} {{SIZE}}{{UNIT}};',
								],
							],
						]
					]
				);

			$this->end_controls_tab();

			$this->start_controls_tab( 'caption', [ 'label' => esc_html__( 'Captions', 'rey-core' ) ] );

				$this->add_control(
					'main_label',
					[
						'label' => __( 'Label Text', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'default' => '',
						'placeholder' => __( 'eg: NEW ARRIVAL', 'rey-core' ),
						'label_block' => true,
						'condition' => [
							'main_slide' => 'yes',
						],
					]
				);

				$this->add_control(
					'main_title',
					[
						'label' => __( 'Title', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'default' => '',
						'label_block' => true,
						'condition' => [
							'main_slide' => 'yes',
						],
					]
				);

				$this->add_control(
					'main_subtitle',
					[
						'label' => __( 'Sub-Title', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::TEXTAREA,
						'default' => '',
						'label_block' => true,
						'condition' => [
							'main_slide' => 'yes',
						],
					]
				);

				$this->add_control(
					'main_button_text',
					[
						'label' => __( 'Button Text', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'default' => '',
						'placeholder' => __( 'eg: SHOP NOW', 'rey-core' ),
						'label_block' => true,
						'condition' => [
							'main_slide' => 'yes',
						],
					]
				);

				$this->add_control(
					'main_button_url',
					[
						'label' => __( 'Button Link', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::URL,
						'dynamic' => [
							'active' => true,
						],
						'placeholder' => __( 'https://your-link.com', 'rey-core' ),
						'default' => [
							'url' => '#',
						],
						'condition' => [
							'main_slide' => 'yes',
						],
					]
				);

			$this->end_controls_tab();

			$this->start_controls_tab( 'style', [ 'label' => esc_html__( 'Styles', 'rey-core' ) ] );

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' => 'label_typo',
						'label' => esc_html__('Label Typography', 'rey-core'),
						'selector' => '{{WRAPPER}} .cSplit-slide.cSplit-slide--main .cSplit-label',
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
						'selector' => '{{WRAPPER}} .cSplit-slide.cSplit-slide--main .cSplit-title',
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' => 'subtitle_typo',
						'label' => esc_html__('Sub-Title Typography', 'rey-core'),
						'selector' => '{{WRAPPER}} .cSplit-slide.cSplit-slide--main .cSplit-subtitle',
					]
				);

				$this->add_control(
					'main_text_color',
					[
						'label' => __( 'Text Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .cSplit-slide.cSplit-slide--main .cSplit-halfLeft' => 'color: {{VALUE}}',
						],
					]
				);

				$this->add_control(
					'main_bg_color',
					[
						'label' => __( 'Left Half - Background Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'alpha' => false,
						'default' => '#212529',
						'selectors' => [
							'{{WRAPPER}} .cSplit-slide.cSplit-slide--main .cSplit-halfLeft:before' => 'background-color: {{VALUE}}',
							'{{WRAPPER}} .cSplit-slide.cSplit-slide--main .cSplit-halfLeft-inner:before' => 'color: {{VALUE}}',
						],
					]
				);

				$this->add_responsive_control(
					'main_bg_color_opacity',
					[
						'label' => __( 'Opacity', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::SLIDER,
						'default' => [
							'size' => .5,
						],
						'range' => [
							'px' => [
								'max' => 1,
								'min' => 0.10,
								'step' => 0.01,
							],
						],
						'tablet_default' => [
							'size' => 0.5,
							'unit' => 'px',
						],
						'mobile_default' => [
							'size' => 1,
							'unit' => 'px',
						],
						'selectors' => [
							'{{WRAPPER}} .cSplit-slide.cSplit-slide--main .cSplit-halfLeft:before' => 'opacity: {{SIZE}};',
						],
					]
				);

			$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

	/**
	 * Slides Content
	 */

		$this->start_controls_section(
			'section_slides_content',
			[
				'label' => __( 'Slides', 'rey-core' ),
			]
		);

		$items = new \Elementor\Repeater();

		$items->start_controls_tabs( 'slides_repeater' );

			$items->start_controls_tab( 'media', [ 'label' => esc_html__( 'Media', 'rey-core' ) ] );

				$items->add_control(
					'image',
					[
					'label' => __( 'Image', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::MEDIA,
						'default' => [
							'url' => \Elementor\Utils::get_placeholder_image_src(),
						],
						'selectors' => [
							// '{{WRAPPER}} {{CURRENT_ITEM}}.cSplit-slide .cSplit-halfRight' => 'background-image: url({{URL}})',
						],
					]
				);

			$items->end_controls_tab();

			$items->start_controls_tab( 'caption', [ 'label' => esc_html__( 'Captions', 'rey-core' ) ] );

				$items->add_control(
					'label',
					[
						'label'       => __( 'Label Text', 'rey-core' ),
						'type'        => \Elementor\Controls_Manager::TEXT,
						'label_block' => true,
					]
				);

				$items->add_control(
					'title',
					[
						'label'       => __( 'Title', 'rey-core' ),
						'type'        => \Elementor\Controls_Manager::TEXT,
						'label_block' => true,
					]
				);

				$items->add_control(
					'subtitle',
					[
						'label'       => __( 'Subtitle Text', 'rey-core' ),
						'type'        => \Elementor\Controls_Manager::TEXTAREA,
						'label_block' => true,
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
					]
				);

			$items->end_controls_tab();

			$items->start_controls_tab( 'style', [ 'label' => esc_html__( 'Styles', 'rey-core' ) ] );

				$items->add_control(
					'bg_color',
					[
						'label' => __( 'Background Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} {{CURRENT_ITEM}}.cSplit-slide .cSplit-halfLeft' => 'background-color: {{VALUE}}',
							'{{WRAPPER}} {{CURRENT_ITEM}}.cSplit-slide .cSplit-halfLeft-inner:before' => 'color: {{VALUE}}',
						],
					]
				);

				$items->add_control(
					'text_color',
					[
						'label' => __( 'Text Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} {{CURRENT_ITEM}}.cSplit-slide .cSplit-halfLeft' => 'color: {{VALUE}}',
						],
					]
				);

				$items->add_responsive_control(
					'img_position',
					[
					'label' => __( 'Image Position (x & y)', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'default' => '50% 50%',
						'selectors' => [
							// '{{WRAPPER}} {{CURRENT_ITEM}}.cSplit-slide .cSplit-halfRight' => 'background-position: {{VALUE}};',
							'{{WRAPPER}} {{CURRENT_ITEM}}.cSplit-slide .cSplit-halfRight-img' => 'object-position: {{VALUE}};',
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

		$this->end_controls_section();

	/**
	 * Bottom Buttons
	 */

		$this->start_controls_section(
			'section_bottom_links',
			[
				'label' => __( 'Bottom Links', 'rey-core' ),
			]
		);

		$bottom_links = new \Elementor\Repeater();

		$bottom_links->add_control(
			'button_text',
			[
				'label' => __( 'Button Text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true,
				],
				'default' => __( 'Click here', 'rey-core' ),
				'placeholder' => __( 'eg: SHOP NOW', 'rey-core' ),
			]
		);

		$bottom_links->add_control(
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
			]
		);


		$this->add_control(
			'bottom_links',
			[
				'label' => __( 'Bottom Links', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $bottom_links->get_controls(),
				'prevent_empty' => false,
			]
		);

		$this->end_controls_section();

	/**
	 * Scroll Button
	 */

		$this->start_controls_section(
			'section_scroll',
			[
				'label' => __( 'Scroll Indicator', 'rey-core' ),
			]
		);

			$this->add_control(
				'scroll_decoration',
				[
					'label' => __( 'Enable', 'rey-core' ),
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

		$this->end_controls_section();


		$this->start_controls_section(
			'section_advanced_sett',
			[
				'label' => __( 'Advanced Settings', 'rey-core' ),
			]
		);

			$this->add_control(
				'scroll_disable',
				[
					'label' => esc_html__( 'Disable Scroll', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

			$this->add_control(
				'scroll_sensitivity_v2',
				[
					'label' => esc_html__( 'Scroll Sensitivity', 'rey-core' ),
					'description' => esc_html__( 'Control the sensitivity of the scroll. The larger the value, the slower will scroll to the next slide.', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 3,
					'min' => 1.5,
					'max' => 6,
					'step' => 0.1,
					'condition' => [
						'scroll_disable' => '',
					],
				]
			);

			$this->add_control(
				'arrows',
				[
					'label' => esc_html__( 'Display Arrows?', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

			$this->add_control(
				'nav_count',
				[
					'label' => esc_html__( 'Show Navigation Count', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
				]
			);

			$this->add_control(
				'autoplay',
				[
					'label' => esc_html__( 'Autoplay?', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'condition' => [
						'scroll_disable!' => '',
					],
				]
			);

			$this->add_control(
				'autoplay_duration',
				[
					'label' => esc_html__( 'Autoplay duration', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 3000,
					'max' => 20000,
					'step' => 50,
					'condition' => [
						'autoplay!' => '',
						'scroll_disable!' => '',
					],
				]
			);

		$this->end_controls_section();

	/**
	 * Styles
	 */

		$this->start_controls_section(
			'section_styles_general',
			[
				'label' => __( 'General Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_control(
				'flip_content',
				[
					'label' => esc_html__( 'Reverse Caption & Image', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'prefix_class' => '--flip-',
				]
			);

			$this->add_control(
				'social_icons_color',
				[
					'label' => __( 'Social Icons - Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'default' => '',
					'selectors' => [
						'{{WRAPPER}} .cSplit-social' => 'color: {{VALUE}}',
					],
					'condition' => [
						'edges!' => 'yes',
					],
				]
			);

			$this->add_control(
				'nav_color',
				[
					'label' => __( 'Navigation Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'default' => '',
					'selectors' => [
						'{{WRAPPER}} .cSplit-nav' => 'color: {{VALUE}}',
					],
					'condition' => [
						'edges!' => 'yes',
					],
				]
			);

			$this->add_control(
				'bottom_links_color',
				[
					'label' => __( 'Bottom Links - Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'default' => '',
					'selectors' => [
						'{{WRAPPER}} .cSplit-bottomLinks' => 'color: {{VALUE}}',
					],
					'condition' => [
						'edges!' => 'yes',
					],
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_styles_header',
			[
				'label' => __( 'Site Header Style', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_control(
				'header_style_desc',
				[
					'type' => \Elementor\Controls_Manager::RAW_HTML,
					'raw' => __( 'These styles apply specifically to the Site Header in relation to this Slider, allowing the Site Header to overlap the Slider and achieve better color contrast. These options are intended for use when the Slider serves as a "Hero" element (rather than being placed within the page). If you plan to use the Slider within the page, it is recommended to disable this option to prevent unnecessary code execution.', 'rey-core' ),
					'content_classes' => 'elementor-descriptor',
				]
			);

			$this->add_control(
				'header_tweaks',
				[
					'label' => esc_html__( 'Enable Site Header Adjustments', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
				]
			);

			$this->add_control(
				'header_color',
				[
					'label' => esc_html__( 'Header Text Color (At Top)', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'default' => '#ffffff',
					'selectors' => [
						'body.csplit-light-header:not(.search-panel--active) .rey-siteHeader--custom:not(.--shrank)' => '--header-text-color: {{VALUE}}',
					],
					'condition' => [
						'header_tweaks!' => '',
						'main_slide!' => '',
					],
				]
			);

			$this->add_control(
				'header_gradient',
				[
					'label' => __( 'Add subtle gradient behind Header', 'rey-core' ),
					'description' => __( 'Enabling this option will add a subtle gradient at the top of the widget, falling behind the header, to help increase its contrast.', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'condition' => [
						'header_tweaks!' => '',
						'main_slide!' => '',
					],
				]
			);

			$this->add_control(
				'header_invert_logo',
				[
					'label' => __( 'Invert Logo Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'selectors' => [
						'body.csplit-light-header:not(.search-panel--active) .rey-siteHeader--custom:not(.--shrank) .rey-siteLogo' => '-webkit-filter: invert(100%); filter: invert(100%);',
					],
					'condition' => [
						'header_tweaks!' => '',
						'main_slide!' => '',
					],
				]
			);

			$this->add_control(
				'header_custom_logo',
				[
				'label' => esc_html__( 'Custom Header Logo Image', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::MEDIA,
					'default' => [],
					'condition' => [
						'header_tweaks!' => '',
						'main_slide!' => '',
						'header_invert_logo!' => 'yes',
					],
				]
			);

			$this->add_control(
				'header_custom_logo_mobile',
				[
					'label' => __( 'Enable on mobile', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'condition' => [
						'header_tweaks!' => '',
						'main_slide!' => '',
						'header_invert_logo!' => 'yes',
					],
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_styles_edges',
			[
				'label' => __( 'Surrounding Frame', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_control(
				'edges',
				[
					'label' => esc_html__( 'Enable the Surrounding Frame', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
					'description' => esc_html__( 'This option will surround the slider with a white frame (except for the main slide).', 'rey-core' ),
				]
			);

			$this->add_control(
				'widget_bg_color',
				[
					'label' => __( 'Frame Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'default' => '',
					'selectors' => [
						'{{WRAPPER}} .rey-coverSplit' => '--edges-bg-color: {{VALUE}}',
					],
					'condition' => [
						'edges' => 'yes',
					],
				]
			);

			$this->add_control(
				'widget_text_color',
				[
					'label' => __( 'Text Color inside Frame', 'rey-core' ),
					'description' => __( 'This will change the color of the components inside the Frame (header text, social icons, nav, bottom links etc.)', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'default' => '',
					'selectors' => [
						'{{WRAPPER}} .cSplit-social' => 'color: {{VALUE}}',
						'{{WRAPPER}} .cSplit-nav' => 'color: {{VALUE}}',
						'{{WRAPPER}} .cSplit-bottomLinks' => 'color: {{VALUE}}',
						'body:not(.csplit-light-header) .rey-siteHeader--custom:not(.--shrank)' => '--header-text-color: {{VALUE}}',
					],
					'condition' => [
						'edges' => 'yes',
					],
				]
			);

			$this->add_control(
				'prevent_image_cutting',
				[
					'label' => esc_html__( 'Prevent Image Cutting', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'condition' => [
						'main_slide' => '',
					],
					'prefix_class' => '--prevent-img-',
					'condition' => [
						'edges!' => '',
					],
					'description' => esc_html__( 'Images are overlapped by the Surrounding Frame. Enabling this option will force images to be fully visible.', 'rey-core' ),
				]
			);

			$this->add_responsive_control(
				'slider__top_margin',
				[
					'label' => esc_html__( 'Top Edge Size', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 0,
					'selectors' => [
						'{{WRAPPER}} .rey-coverSplit' => '--top-edge: {{VALUE}}px',
					],
					'render_type' => 'template',
					'condition' => [
						'edges!' => '',
					],
					'description' => esc_html__( 'The top edge uses the header\'s height, however you can adjust it as you want.', 'rey-core' ),
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_styles_slides',
			[
				'label' => __( 'Captions', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);


			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'slides_label_typo',
					'label' => esc_html__('Label Typography', 'rey-core'),
					'selector' => '{{WRAPPER}} .cSplit-slide:not(.cSplit-slide--main) .cSplit-label',
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'slides_title_typo',
					'label' => esc_html__('Title Typography', 'rey-core'),
					'global' => [
						'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Typography::TYPOGRAPHY_PRIMARY,
					],
					'selector' => '{{WRAPPER}} .cSplit-slide:not(.cSplit-slide--main) .cSplit-title',
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'slides_subtitle_typo',
					'label' => esc_html__('Sub-Title Typography', 'rey-core'),
					'selector' => '{{WRAPPER}} .cSplit-slide:not(.cSplit-slide--main) .cSplit-subtitle',
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

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'button_typo',
					'label' => esc_html__('Button Typography', 'rey-core'),
					'selector' => '{{WRAPPER}} .cSplit-slideBtn .btn',
				]
			);

			$this->add_control(
				'btn_color',
				[
					'label' => esc_html__( 'Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .cSplit-slideBtn .btn' => 'color: {{VALUE}};',
					],
				]
			);

			$this->add_control(
				'btn_bg_color',
				[
					'label' => esc_html__( 'Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .cSplit-slideBtn .btn' => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'btn_color_hover',
				[
					'label' => esc_html__( 'Hover Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .cSplit-slideBtn .btn:hover' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'btn_bg_color_hover',
				[
					'label' => esc_html__( 'Hover Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .cSplit-slideBtn .btn:hover' => 'background-color: {{VALUE}}',
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
						'{{WRAPPER}} .cSplit-slideBtn .btn' => 'border-radius: {{VALUE}}px',
					],
				]
			);

		$this->end_controls_section();

	}

	public function render_start()
	{

		$classes = [
			'rey-coverSplit',
			'--loading',
			$this->_settings['edges'] === 'yes' ? '--edges' : ''
		];

		if( $this->_settings['main_slide'] === 'yes' ){
			$classes[] = '--mainSlide';
			$classes[] = '--mainSlide-active';
		}

		$scroll_is_enabled = $this->_settings['scroll_disable'] === '';

		$slide_settings = [
			'scroll' => $scroll_is_enabled
		];

		if( $this->_settings['autoplay'] !== '' && ! $scroll_is_enabled ){
			$slide_settings['autoplay'] = true;
			if( $this->_settings['autoplay_duration'] ){
				$slide_settings['autoplayDuration'] = $this->_settings['autoplay_duration'];
			}
		}

		if(
			empty($this->_settings['header_invert_logo']) &&
			($header_custom_logo = $this->get_settings('header_custom_logo')) &&
			! empty($header_custom_logo['url']) &&
			($logo_swap = esc_url($header_custom_logo['url']))
		){
			$slide_settings['logo'] = $logo_swap;
			if( ! empty($this->_settings['header_custom_logo_mobile']) ){
				$slide_settings['logo_mobile'] = true;
			}
		}

		$this->add_render_attribute( 'wrapper', [
			'data-slider-settings' => wp_json_encode($slide_settings),
			'class' => $classes,
		] );

		$wrapper_attributes = [
			'class' => [
				'rey-coverSplit-wrapper',
				$scroll_is_enabled ? 'rey-coverSplit-scrollWrapper' : ''
			],
		];

		$count_slides = count($this->_settings['items']);

		if( $this->_settings['main_slide'] === 'yes' ){
			$count_slides += 1;
		}

		// if we have less than 3 slides, we need to adjust the area
		if( $count_slides < 3 ){
			$wrapper_attributes['style'] = sprintf('--cover-split-area: %d;', $count_slides);
		}

		else {

			if(
				$scroll_is_enabled
				&& ! empty($this->_settings['scroll_sensitivity_v2'])
				&& ($sensitivity = floatval($this->_settings['scroll_sensitivity_v2']))
				&& $sensitivity != 3
			){
				$wrapper_attributes['style'] = sprintf('--cover-split-area: %s;', $sensitivity);
			}
		}

		$this->add_render_attribute( 'scrollWrapper', $wrapper_attributes ); ?>

		<div <?php echo $this->get_render_attribute_string( 'scrollWrapper' ); ?>>

			<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>

			<?php do_action('reycore/elementor/cover-split/after_slider_start', $this); ?>

				<div class="rey-coverSplit-inner"><?php
	}

	public function render_end(){ ?>

				</div>
				<!-- .rey-coverSplit-inner -->

			<?php do_action('reycore/elementor/cover-split/before_slider_end', $this); ?>

			</div>
			<!-- .rey-coverSplit -->
		</div>
		<!-- .rey-coverSplit-wrapper -->
		<?php
		echo \ReyCore\Elementor\Helper::edit_mode_widget_notice(['full_viewport', 'tabs_modal']);
	}

	public function render_scroll($settings, $key)
	{
		if( $settings['scroll_decoration'] !== 'yes' ) return; ?>

		<div class="cSplit-scroll">
			<a href="#" class="rey-scrollDeco rey-scrollDeco--default" data-target="next" data-lazy-hidden>
				<?php if($stext = $settings['scroll_text']): ?>
					<span class="rey-scrollDeco-text"><?php echo $stext ?></span>
				<?php endif; ?>
				<?php
				$css_class = 'rey-scrollDeco-bar';
				if( ($settings['main_slide'] === 'yes' && $key === 'main') || ($settings['main_slide'] !== 'yes' && $key === 0) ){
					$css_class .= ' --start';
				} ?>
				<span class="<?php echo $css_class ?>"></span>
			</a>
		</div>

		<?php
		reycore_assets()->add_styles('reycore-elementor-scroll-deco');
	}

	public function render_main_image($settings){

		$main_background_image = ! empty($settings['main_background_image']) ? $settings['main_background_image'] : [];
		$image_size = isset($main_background_image['size']) ? $main_background_image['size'] : 'medium';

		$images_config = [
			'desktop'    => ! empty($settings['main_background_image']['id']) ? $settings['main_background_image'] : [],
			'tablet'     => ! empty($settings['main_background_image_tablet']['id']) ? $settings['main_background_image_tablet'] : [],
			'mobile'     => ! empty($settings['main_background_image_mobile']['id']) ? $settings['main_background_image_mobile'] : [],
			'image_size' => $image_size,
			'settings'   => $settings,
			'class'      => 'cSplit-slide--mainImg',
		];

		echo reycore__get_responsive_attachment_images($images_config);
	}

	public function render_slides($settings){

		if( $settings['header_gradient'] !== ''){
			echo '<div class="cSplit-headerGradient u-transparent-gradient"></div>';
		}

		$type_css_class = '';

		ob_start();

		if( 'classic' === $settings['main_background_background'] ){
			$this->render_main_image($settings);
			$type_css_class = '--classic';
		}
		else if( 'video' === $settings['main_background_background'] && $video_link = $settings['main_background_video_link'] ){

			$type_css_class = '--video';

			reycore_assets()->add_scripts( ['rey-videos'] );
			reycore_assets()->add_styles('reycore-elementor-bg-video-container');

			$video_properties = \Elementor\Embed::get_video_properties( $video_link );

			if( isset($video_properties['provider']) && 'youtube' === $video_properties['provider'] ){
				$type_css_class .= ' --yt';

				echo reycore__get_youtube_iframe_html([
					'class' => 'rey-background-video-container',
					'video_id' => $video_properties['video_id'],
					'html_id' => 'yt' . $this->get_id(),
					'add_preview_image' => false,
					'mobile' => isset($settings['main_background_play_on_mobile']) && $settings['main_background_play_on_mobile'] === 'yes',
					'params' => [
						'start' => absint( $settings['main_background_video_start'] ),
						'end' => absint( $settings['main_background_video_end'] ),
						'loop' => $settings['main_background_play_once'] === '' ? 1 : 0,
					],
				]);
			}
			else {
				$type_css_class .= ' --hosted';
				echo reycore__get_video_html([
					'class' => 'rey-background-video-container',
					'video_url' => $video_link,
					'start' => absint( $settings['main_background_video_start'] ),
					'end' => absint( $settings['main_background_video_end'] ),
					'mobile' => isset($settings['main_background_play_on_mobile']) && $settings['main_background_play_on_mobile'] === 'yes',
					'params' => [
						'loop' => $settings['main_background_play_once'] === '' ? 'loop' : '',
					],
				]);
			}
		}

		printf('<div class="cSplit-slide--mainBg cSplit--abs %2$s">%1$s</div>', ob_get_clean(), $type_css_class); ?>

		<div class="cSplit-slides cSplit--abs">

			<?php
			foreach($this->_items as $key => $item): ?>
			<div class="cSplit-slide cSplit--abs elementor-repeater-item-<?php echo $item['_id'] ?>">

				<div class="cSplit-halfLeft cSplit--abs">
					<div class="cSplit-halfLeft-inner">

						<?php if( $label = $item['label'] ): ?>
						<div class="cSplit-label"><?php echo $label ?></div>
						<?php endif; ?>

						<?php if( $title = $item['title'] ): ?>
						<h2 class="cSplit-title"><?php echo $title ?></h2>
						<?php endif; ?>

						<?php if( $subtitle = $item['subtitle'] ): ?>
						<div class="cSplit-subtitle"><?php echo $subtitle ?></div>
						<?php endif; ?>

						<?php if( $button_text = $item['button_text'] ): ?>
							<div class="cSplit-btn <?php echo ('main' !== $key ? 'cSplit-slideBtn' : ''); ?>">

								<?php
								$url_key = 'url'.$key;

								$this->add_render_attribute( $url_key , 'class', 'btn' );

								if( isset($item['button_url']['url']) && $url = $item['button_url']['url'] ){
									$this->add_render_attribute( $url_key , 'href', $url );

									if( $item['button_url']['is_external'] ){
										$this->add_render_attribute( $url_key , 'target', '_blank' );
									}

									if( $item['button_url']['nofollow'] ){
										$this->add_render_attribute( $url_key , 'rel', 'nofollow' );
									}
								}

								reycore_assets()->add_styles('rey-buttons');

								if( $key === 'main' ){
									$this->add_render_attribute( $url_key , 'class', 'btn-line-active' );
								}
								else {
									$this->add_render_attribute( $url_key , 'class', $settings['button_style'] );
								}

								$btn_html = sprintf('<a %s>%s</a>', $this->get_render_attribute_string($url_key), $button_text);
								echo apply_filters('reycore/elementor/cover-split/btn_html', $btn_html, $item, $url_key, $key);
							?>
							</div>
							<!-- .cSplit-btn -->
						<?php endif; ?>

					</div>

					<?php echo $this->render_scroll($settings, $key); ?>
				</div>
				<!-- .cSplit-halfLeft -->

				<div class="cSplit-halfRight cSplit--abs">
					<?php

						do_action('reycore/elementor/cover-split/slide/right', $item, $key);

						if( isset($item['image']) && isset($item['image']['id']) && $image_id = $item['image']['id'] ){
							echo reycore__get_attachment_image( apply_filters('reycore/elementor/cover-split/image_args', [
								'image' => $item['image'],
								'size' => $settings['image_size'],
								'attributes' => ['class'=> 'cSplit-halfRight-img cSplit--abs ' ]
							] ) );
						}
					?>
				</div>
				<!-- .cSplit-halfRight -->

			</div>
			<?php
			endforeach; ?>
		</div>
		<?php
	}

	public function render_social($settings){

		if( $social_icon_list = $settings['social_icon_list'] ): ?>

			<div class="cSplit-social">
				<div class="cSplit-socialIcons">
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
						<a class="cSplit-socialIcons-link" rel="noreferrer" <?php echo $this->get_render_attribute_string( $link_key ); ?>>
							<?php echo reycore__get_svg_social_icon([ 'id'=>$item['social'] ]); ?>
						</a>
					<?php endforeach; ?>
				</div>

				<?php if($social_text = $settings['social_text']): ?>
					<div class="cSplit-socialText"><?php echo $social_text ?></div>
				<?php endif; ?>

			</div>
			<!-- .cSplit-social -->
		<?php endif;
	}

	public function render_borders(){
		?>
		<div class="cSplit-borders">
			<span class="__top"></span>
			<span></span>
			<span></span>
			<span></span>
		</div>
		<?php
	}

	public function render_arrows( $settings ){

		if( $settings['arrows'] === '' ){
			return;
		}

		?>
		<div class="cSplit-arrows">
			<?php
				reycore__svg_arrows([
					'attributes' => [
						'left' => 'data-dir="<"',
						'right' => 'data-dir=">"',
					]
				]);
			?>
		</div>
		<?php
	}

	public function render_nav( $settings ){
		?>
		<div class="cSplit-nav">
			<?php
			foreach($this->_items as $key => $item ):
				if( is_numeric($key) ){

					$index = $settings['main_slide'] === 'yes' ? $key + 1 : $key;

					printf(
						'<div class="cSplit-navItem" data-index="%s"><span>%s</span></div>',
						$index,
						sprintf("%02d", $key + 1)
					);
				}
			endforeach; ?>
		</div>
		<?php
	}

	public function render_bottom_links($settings){

		if( $bottom_links = $settings['bottom_links'] ): ?>
			<div class="cSplit-bottomLinks">
				<?php
				foreach ( $bottom_links as $index => $item ):

					$link_key = 'blink_' . $index;

					$this->add_render_attribute( $link_key, 'href', $item['button_url']['url'] );

					if ( $item['button_url']['is_external'] ) {
						$this->add_render_attribute( $link_key, 'target', '_blank' );
					}

					if ( $item['button_url']['nofollow'] ) {
						$this->add_render_attribute( $link_key, 'rel', 'nofollow' );
					}

					reycore_assets()->add_styles('rey-buttons'); ?>

					<a class="btn btn-line-active" <?php echo $this->get_render_attribute_string( $link_key ); ?>>
						<?php echo $item['button_text'] ?>
					</a>
				<?php endforeach; ?>
			</div>
			<!-- .cSplit-bottomLinks -->
		<?php endif;
	}

	/**
	 * Handle fast tweaks before the widget is rendered.
	 * The tweaks are related to the widget's relation with the header.
	 *
	 * @return void
	 */
	public function run_early_script()
	{
		if( '' === $this->_settings['header_tweaks'] ){
			return;
		}
		$scripts = '';
		// if the main slide is enabled, make the header text color white
		if( '' !== $this->_settings['main_slide'] ){
			$scripts .= 'document.body.classList.add("csplit-light-header");';
		}
		// only desktop & tablet
		$scripts .= 'if( window.matchMedia("(max-width: 767px)").matches ) return;';
		// get the top section/container
		// check if is first and stop running the script
		$scripts .= sprintf('const scriptTag = document.getElementById("reycore-cover-split-before-%s");', $this->get_id());
		$scripts .= 'if( ! scriptTag ) return;';
		$scripts .= 'const container = scriptTag.closest(".elementor-section.elementor-top-section, .e-con-top");';
		$scripts .= 'if( ! container ) return;';
		$scripts .= 'if( container.previousElementSibling !== null ) return;';
		// deal with the fixed or absolute header's helper to ensure overlapping
		$scripts .= 'document.getElementById("rey-siteHeader-helper")?.classList.add("--dnone-lg");';
		// If the header is Absolute or Relative, we need to make it Fixed
		// and ensure it gets toggled on scroll, when exiting.
		$scripts .= 'const header = (document.querySelector(".rey-siteHeader.header-pos--absolute") || document.querySelector(".rey-siteHeader.header-pos--rel"));';
		$scripts .= 'if( !header ) return;';
		$scripts .= 'document.body.classList.add("csplit-toggle-fixed");';
		$scripts .= 'Object.assign( header.style, { position: "absolute", top: 0, zIndex: 990, width: "100%", insetInlineStart: 0 });';
		$scripts .= 'document.documentElement.style.setProperty("--header-default--height", header.offsetHeight + "px");';

		if( $scripts ){
			printf('<script type="text/javascript" id="reycore-cover-split-before-%s" data-rey-instant-js %s>(function(){%s})();</script>', $this->get_id(), reycore__js_no_opt_attr(), $scripts);
		}
	}

	public function run_late_script()
	{
		if( '' === $this->_settings['header_tweaks'] ){
			return;
		}
		// only desktop & tablet
		$scripts = 'if( window.matchMedia("(max-width: 767px)").matches ) return;';
		// get the scope
		$scripts .= sprintf('const _s = document.currentScript.closest(".elementor-widget-%s");', $this->get_unique_name());
		// check if is first and stop running the script
		$scripts .= 'if( ! _s ) return;';
		$scripts .= 'if( _s.parentElement.children.length === 1 ) return;';
		// grab the siblings
		$scripts .= 'let sibling = _s.previousElementSibling;';
		$scripts .= 'const siblingsToMove = [];';
		$scripts .= 'while (sibling) {';
			$scripts .= 'sibling.style.marginBottom = 0;';
			$scripts .= 'siblingsToMove.push(sibling);';
			$scripts .= 'sibling = sibling.previousElementSibling;';
		$scripts .= '}';
		$scripts .= 'siblingsToMove.reverse();';
		// append the siblings into slider
		$scripts .= '_s.querySelector(".rey-coverSplit")?.prepend(...siblingsToMove);';

		if( $scripts ){
			printf('<script type="text/javascript" data-rey-instant-js %s>(function(){%s})();</script>', reycore__js_no_opt_attr(), $scripts);
		}

	}

	protected function render() {

		$this->_settings = $this->get_settings_for_display();

		$this->run_early_script();
		$this->render_start();

		$slides = [];

		if( $this->_settings['main_slide'] === 'yes' ){
			$slides = [
				'main' => [
					'label' => $this->_settings['main_label'],
					'title' => $this->_settings['main_title'],
					'subtitle' => $this->_settings['main_subtitle'],
					'button_text' => $this->_settings['main_button_text'],
					'button_url' => $this->_settings['main_button_url'],
					'_id' => ' cSplit-slide--main',
				]
			];
		}

		$this->_items = array_merge($slides, $this->_settings['items']);

		$this->render_slides($this->_settings);

		if( $this->_settings['edges'] === 'yes'){
			$this->render_borders();
		}

		$this->render_arrows($this->_settings);

		if( $this->_settings['nav_count'] === 'yes'){
			$this->render_nav($this->_settings);
		}

		$this->render_social($this->_settings);
		$this->render_bottom_links($this->_settings);
		$this->render_end();

		reycore_assets()->add_styles($this->get_style_name());
		reycore_assets()->add_scripts( $this->rey_get_script_depends() );

		$this->run_late_script();
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
