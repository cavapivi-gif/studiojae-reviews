<?php
namespace ReyCore\Elementor\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class TriggerV2 extends \ReyCore\Elementor\WidgetsBase {

	public static function get_rey_config(){
		return [
			'id' => 'trigger-v2',
			'title' => __( 'Trigger', 'rey-core' ),
			'icon' => 'eicon-button',
			'categories' => [ 'rey-header', 'rey-theme' ],
			'keywords' => [],
			'css' => [
				'assets/button.css',
				'assets/popover.css',
			],
			'js' => [
				'assets/script.js',
			],
		];
	}

	// public function on_demo_export(){
	// }

	// public function get_custom_help_url() {
	// 	return reycore__support_url('kb/rey-elements-header/#fullscreen-navigation');
	// }

	function controls__settings(){

		$this->start_controls_section(
			'section_settings',
			[
				'label' => __( 'Settings', 'rey-core' ),
			]
		);

			$this->add_control(
				'trigger',
				[
					'label' => esc_html__( 'Trigger type', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'click',
					'options' => [
						'click'  => esc_html__( 'On Click', 'rey-core' ),
						'hover'  => esc_html__( 'On Hover', 'rey-core' ),
					],
				]
			);

			$this->add_control(
				'action',
				[
					'label' => esc_html__( 'Action', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- Select -', 'rey-core' ),
						'offcanvas'  => esc_html__( 'Open Off-Canvas Panel', 'rey-core' ),
						'toggle'  => esc_html__( 'Toggle Content Visibility', 'rey-core' ),
						'popover'  => esc_html__( 'Open Popover', 'rey-core' ),
						'modal'  => esc_html__( 'Open Modal', 'rey-core' ),
						'slide'  => esc_html__( 'Go To Slide & Carousel Item', 'rey-core' ),
					],
				]
			);

			$this->add_control(
				'trigger_title_attr',
				[
					'label' => esc_html__( 'Title Attribute', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'placeholder' => esc_html__( 'eg: Button description', 'rey-core' ),
				]
			);


			$this->add_control(
				'layout',
				[
					'label' => esc_html__( 'Layout', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'hamburger',
					'options' => [
						'hamburger'  => esc_html__( 'Hamburger Icon', 'rey-core' ),
						'button'  => esc_html__( 'Button', 'rey-core' ),
						'image'  => esc_html__( 'Image', 'rey-core' ),
						'circle'  => esc_html__( 'Circle', 'rey-core' ),
						// 'lottie'  => esc_html__( 'Lottie animation', 'rey-core' ),
					],
				]
			);

			$this->add_responsive_control(
				'align',
				[
					'label' => __( 'Alignment', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::CHOOSE,
					'options' => [
						'flex-start' => [
							'title' => __( 'Left', 'rey-core' ),
							'icon' => 'eicon-text-align-left',
						],
						'center' => [
							'title' => __( 'Center', 'rey-core' ),
							'icon' => 'eicon-text-align-center',
						],
						'flex-end' => [
							'title' => __( 'Right', 'rey-core' ),
							'icon' => 'eicon-text-align-right',
						],
					],
					'selectors' => [
						'{{WRAPPER}}  .elementor-widget-container' => 'display: flex; justify-content: {{VALUE}}',
					],
 				]
			);


		$this->end_controls_section();

		$this->start_controls_section(
			'section_settings_offcanvas',
			[
				'label' => __( 'Off-canvas Settings', 'rey-core' ),
				'condition' => [
					'action' => 'offcanvas',
				],
			]
		);

			$this->add_control(
				'offcanvas_panel',
				[
					'label_block' => true,
					'label' => __( 'Off-Canvas Global Sections', 'rey-core' ),
					'type' => 'rey-query',
					'placeholder' => esc_html__('- Select -', 'rey-core'),
					'default' => '',
					'query_args' => [
						'type' => 'posts',
						'post_type' => \ReyCore\Elementor\GlobalSections::POST_TYPE,
						'meta' => [
							'meta_key' => 'gs_type',
							'meta_value' => 'offcanvas',
						],
						'edit_link' => true,
					],
				]
			);


		$this->end_controls_section();

		$this->start_controls_section(
			'section_settings_toggle',
			[
				'label' => __( 'Toggle Content Settings', 'rey-core' ),
				'condition' => [
					'action' => 'toggle',
				],
			]
		);

			$this->add_control(
				'toggle_css_selector',
				[
					'label' => esc_html__( 'CSS Selector', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'placeholder' => esc_html_x( 'eg: .my-custom-class', 'Elementor control label', 'rey-core' ),
					'description' => esc_html_x( 'Add this custom class or id, in any section, column or element you want to show or hide.', 'Elementor control label', 'rey-core' ),
					'label_block' => true,
				]
			);

			$this->add_control(
				'toggle_start_opened',
				[
					'label' => esc_html__( 'Start Opened', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

			$this->add_control(
				'toggle_animated',
				[
					'label' => esc_html__( 'Toggle Animated?', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'description' => esc_html_x( 'If enabled, the toggle will be made with slide effect.', 'Elementor control label', 'rey-core' ),
					'default' => '',
				]
			);


		$this->end_controls_section();

		$this->start_controls_section(
			'section_settings_popover',
			[
				'label' => __( 'Popover Settings', 'rey-core' ),
				'condition' => [
					'action' => 'popover',
				],
			]
		);

			$this->add_control(
				'popover_type',
				[
					'label' => esc_html__( 'Content Type', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'text',
					'options' => [
						'text'  => esc_html__( 'Custom Text', 'rey-core' ),
						'gs'  => esc_html__( 'Global Section', 'rey-core' ),
					],
				]
			);

			$this->add_control(
				'popover_text',
				[
					'label' => __( 'Custom Content', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::WYSIWYG,
					'default' => '',
					'placeholder' => __( 'Type your content here', 'rey-core' ),
					'condition' => [
						'popover_type' => 'text',
					],
				]
			);

			$this->add_control(
				'popover_gs',
				[
					'label_block' => true,
					'label' => __( 'Global Sections', 'rey-core' ),
					'type' => 'rey-query',
					'default' => '',
					'placeholder' => esc_html__('- Select -', 'rey-core'),
					'query_args' => [
						'type' => 'posts',
						'post_type' => \ReyCore\Elementor\GlobalSections::POST_TYPE,
						'meta' => [
							'meta_key' => 'gs_type',
							'meta_value' => 'generic',
						],
						'edit_link' => true,
					],
					'condition' => [
						'popover_type' => 'gs',
					],
				]
			);


		$this->end_controls_section();

		$this->start_controls_section(
			'section_settings_modal',
			[
				'label' => __( 'Modal Settings', 'rey-core' ),
				'condition' => [
					'action' => 'modal',
				],
			]
		);

			$this->add_control(
				'modal_id',
				[
					'label' => esc_html__( 'Modal Unique ID', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'placeholder' => esc_html_x( 'eg: #modal-61c0eef', 'Elementor control label', 'rey-core' ),
					'description' => sprintf( _x( 'Add the Modal section unique ID in this field. Learn <a href="%s" target="_blank">how to create modals</a>.', 'Elementor control description', 'rey-core' ), reycore__support_url('kb/create-modal-sections/') ),
					'label_block' => true,
					'wpml' => false,
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_settings_slide',
			[
				'label' => __( 'Slider/Carousel Settings', 'rey-core' ),
				'condition' => [
					'action' => 'slide',
				],
			]
		);

			$this->add_control(
				'slider_id',
				[
					'label' => esc_html__( 'Slider/Carousel Unique ID', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'placeholder' => esc_html_x( 'eg: #carousel-6455dcc', 'Elementor control label', 'rey-core' ),
					'description' => sprintf( _x( 'Add the Slider or Carousel unique ID in this field.', 'Elementor control description', 'rey-core' ), reycore__support_url('kb/create-modal-sections/') ),
					'label_block' => true,
					'wpml' => false,
				]
			);

			$this->add_control(
				'slide_number',
				[
					'label' => esc_html__( 'Slide number', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 1,
					'max' => 1000,
					'step' => 1,
				]
			);

		$this->end_controls_section();
	}

	function controls__styles() {

		// $this->start_controls_section(
		// 	'section_styles',
		// 	[
		// 		'label' => __( 'Styles', 'rey-core' ),
		// 		'tab' => \Elementor\Controls_Manager::TAB_STYLE
		// 	]
		// );

		// $this->end_controls_section();
	}

	function controls__hamburger_styles() {

		$this->start_controls_section(
			'section_styles_hamburger',
			[
				'label' => __( 'Hamburger Icon', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'layout' => 'hamburger',
				],
			]
		);

			$this->add_control(
				'hamburger_style',
				[
					'label' => esc_html__( 'Style', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( 'Default - 3 bars', 'rey-core' ),
						'--25b'  => esc_html__( '2.5 bars', 'rey-core' ),
						'--2b'  => esc_html__( '2 bars', 'rey-core' ),
						'--2bh'  => esc_html__( '2 bars + hover', 'rey-core' ),
						'--2b2'  => esc_html__( '2 bars v2', 'rey-core' ),
					],
				]
			);

			$this->add_responsive_control(
				'hamburger_style_width',
				[
					'label' => esc_html__( 'Bars Width', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'min' => 1,
					'max' => 100,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .rey-triggerBtn' => '--hbg-bars-width: {{VALUE}}px',
					],
				]
			);

			$this->add_responsive_control(
				'hamburger_style_bars_thick',
				[
					'label' => esc_html__( 'Bars Thickness', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'min' => 1,
					'max' => 15,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .rey-triggerBtn' => '--hbg-bars-thick: {{VALUE}}px',
					],
				]
			);

			$this->add_responsive_control(
				'hamburger_style_bars_distance',
				[
					'label' => esc_html__( 'Bars Distance', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'min' => 1,
					'max' => 15,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .rey-triggerBtn' => '--hbg-bars-distance: {{VALUE}}px',
					],
				]
			);

			$this->add_responsive_control(
				'hamburger_style_bars_round',
				[
					'label' => esc_html__( 'Bars Roundness', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 2,
					'min' => 0,
					'max' => 15,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .rey-triggerBtn' => '--hbg-bars-roundness: {{VALUE}}px',
					],
				]
			);

			$this->add_responsive_control(
				'hamburger_color',
				[
					'label' => esc_html__( 'Icon Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-triggerBtn' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'hamburger_text',
				[
					'label' => esc_html__( 'Custom Text', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'separator' => 'before',
					'selectors' => [
						'{{WRAPPER}} .__hamburger:after' => 'content: "{{VALUE}}"',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'hamburger_text_styles',
					'selector' => '{{WRAPPER}} .__hamburger:after',
					'condition' => [
						'hamburger_text!' => '',
					],
				]
			);

			$this->add_control(
				'hamburger_text_reverse',
				[
					'label' => esc_html__( 'Flip Text Position', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'selectors' => [
						'{{WRAPPER}} .__hamburger' => '--hbg-a-o: -1',
					],
					'condition' => [
						'hamburger_text!' => '',
					],
				]
			);

			$this->add_control(
				'hamburger_text_distance',
				[
					'label' => esc_html__( 'Text distance', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .__hamburger' => '--text-distance: {{VALUE}}px',
					],
					'condition' => [
						'hamburger_text!' => '',
					],
				]
			);

			$this->add_control(
				'hamburger_text_color',
				[
					'label' => esc_html__( 'Text color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .__hamburger:after' => 'color: {{VALUE}}',
					],
					'condition' => [
						'hamburger_text!' => '',
					],
				]
			);

			$this->add_control(
				'hamburger_text_mobile',
				[
					'label' => esc_html__( 'Hide text on mobiles/tablet', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
					'prefix_class' => '--text-mobile-',
				]
			);

		$this->end_controls_section();
	}

	function controls__button_styles() {

		$this->start_controls_section(
			'section_btn_style',
			[
				'label' => __( 'Button Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'layout' => 'button',
				],
			]
		);

			$this->add_control(
				'btn_text',
				[
					'label' => esc_html__( 'Button text', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => esc_html__( 'Click here', 'rey-core' ),
					'placeholder' => esc_html__( 'eg: click here', 'rey-core' ),
				]
			);

			$this->add_control(
				'btn_style',
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
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'btn_typo',
					'selector' => '{{WRAPPER}} .rey-triggerBtn.--button2',
				]
			);

			$this->start_controls_tabs( 'tabs_items_styles' );

				$this->start_controls_tab(
					'tabs_btn_normal',
					[
						'label' => esc_html__( 'Normal', 'rey-core' ),
					]
				);

					$this->add_control(
						'btn_color',
						[
							'label' => __( 'Text Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}}  .rey-triggerBtn.--button2' => 'color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'btn_bg_color',
						[
							'label' => __( 'Background Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}}  .rey-triggerBtn.--button2' => 'background-color: {{VALUE}}',
							],
						]
					);

					$this->add_responsive_control(
						'btn_border_width',
						[
							'label' => __( 'Border Width', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::DIMENSIONS,
							'size_units' => [ 'px', 'em', '%' ],
							'selectors' => [
								'{{WRAPPER}}  .rey-triggerBtn.--button2' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'btn_border_color',
						[
							'label' => __( 'Border Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}}  .rey-triggerBtn.--button2' => 'border-color: {{VALUE}};',
							],
						]
					);

				$this->end_controls_tab();

				$this->start_controls_tab(
					'tabs_btn_hover',
					[
						'label' => esc_html__( 'Hover', 'rey-core' ),
					]
				);

					$this->add_control(
						'btn_color_active',
						[
							'label' => __( 'Text Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .rey-triggerBtn.--button2:hover' => 'color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'btn_bg_color_active',
						[
							'label' => __( 'Background Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .rey-triggerBtn.--button2:hover' => 'background-color: {{VALUE}}',
							],
						]
					);

					$this->add_responsive_control(
						'btn_border_width_active',
						[
							'label' => __( 'Border Width', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::DIMENSIONS,
							'size_units' => [ 'px', 'em', '%' ],
							'selectors' => [
								'{{WRAPPER}} .rey-triggerBtn.--button2:hover' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'btn_border_color_active',
						[
							'label' => __( 'Border Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .rey-triggerBtn.--button2:hover' => 'border-color: {{VALUE}};',
							],
						]
					);

				$this->end_controls_tab();

				$this->start_controls_tab(
					'tabs_btn_active',
					[
						'label' => esc_html__( 'Active', 'rey-core' ),
						'condition' => [
							'action' => ['toggle', 'popover'],
						],
					]
				);

					$this->add_control(
						'btn_color_active_active',
						[
							'label' => __( 'Text Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .rey-triggerBtn.--button2.--is-active' => 'color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'btn_bg_color_active_active',
						[
							'label' => __( 'Background Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .rey-triggerBtn.--button2.--is-active' => 'background-color: {{VALUE}}',
							],
						]
					);

					$this->add_responsive_control(
						'btn_border_width_active_active',
						[
							'label' => __( 'Border Width', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::DIMENSIONS,
							'size_units' => [ 'px', 'em', '%' ],
							'selectors' => [
								'{{WRAPPER}} .rey-triggerBtn.--button2.--is-active' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'btn_border_color_active_active',
						[
							'label' => __( 'Border Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .rey-triggerBtn.--button2.--is-active' => 'border-color: {{VALUE}};',
							],
						]
					);

				$this->end_controls_tab();
			$this->end_controls_tabs();

			$this->add_responsive_control(
				'btn_border_radius',
				[
					'label' => __( 'Border Radius', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em' ],
					'selectors' => [
						'{{WRAPPER}} .rey-triggerBtn.--button2' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'btn_padding',
				[
					'label' => __( 'Padding', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em' ],
					'selectors' => [
						'{{WRAPPER}} .rey-triggerBtn.--button2' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);


		$this->end_controls_section();

		$this->start_controls_section(
			'section_btn_icon_style',
			[
				'label' => __( 'Button Icon Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'layout' => 'button',
				],
			]
		);

			$this->add_control(
				'icon',
				[
					'label' => __( 'Icon', 'elementor' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'default' => [
						'value' => 'fas fa-plus',
						'library' => 'fa-solid',
					],
				]
			);

			$this->add_responsive_control(
				'icon_size',
				[
					'label' => esc_html__( 'Icon Size', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'min' => 1,
					'max' => 300,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .rey-triggerBtn.--button2' => '--icon-size: {{VALUE}}px',
					],
				]
			);

			$this->add_responsive_control(
				'icon_distance',
				[
					'label' => esc_html__( 'Icon Distance', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'min' => 0,
					'max' => 300,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .rey-triggerBtn.--button2' => '--icon-distance: {{VALUE}}px',
					],
				]
			);

			$this->add_responsive_control(
				'icon_color',
				[
					'label' => esc_html__( 'Icon Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-triggerBtn.--button2' => '--icon-color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'icon_reverse',
				[
					'label' => esc_html__( 'Move icon to left', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

			$this->add_control(
				'stretch_button',
				[
					'label' => esc_html__( 'Stretch button', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

			$this->add_control(
				'active_icon',
				[
					'label' => __( 'Active Icon', 'elementor' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'fa4compatibility' => 'icon_active',
					'default' => [
						'value' => 'fas fa-minus',
						'library' => 'fa-solid',
					],
					'recommended' => [
						'fa-solid' => [
							'chevron-up',
							'angle-up',
							'angle-double-up',
							'caret-up',
							'caret-square-up',
						],
						'fa-regular' => [
							'caret-square-up',
						],
					],
					'separator' => 'before',
					'condition' => [
						'action' => ['toggle', 'popover'],
					],
				]
			);

		$this->end_controls_section();
	}

	function controls__image_styles(){

		$this->start_controls_section(
			'section_image_style',
			[
				'label' => __( 'Image Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'layout' => 'image',
				],
			]
		);

			$this->add_control(
				'the_image',
				[
				   'label' => esc_html__( 'Select Image', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::MEDIA,
					'default' => [
						'url' => \Elementor\Utils::get_placeholder_image_src(),
					],
					'dynamic' => [
						'active' => true,
					],
				]
			);

			$this->add_control(
				'image_size_css',
				[
				   'label' => esc_html__( 'Custom Image Size', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', 'em', '%' ],
					'range' => [
						'px' => [
							'min' => 8,
							'max' => 1280,
							'step' => 1,
						],
						'em' => [
							'min' => 0,
							'max' => 5.0,
						],
					],
					'default' => [
						'unit' => 'px',
						'size' => 90,
					],
					'selectors' => [
						'{{WRAPPER}} .rey-triggerImg' => 'width: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Image_Size::get_type(),
				[
					'name' => 'image', // Usage: `{name}_size` and `{name}_custom_dimension`, in this case `image_size` and `image_custom_dimension`.
					'default' => 'medium',
					'exclude' => ['custom'],
					'label' => esc_html__('Physical image size', 'rey-core')
				]
			);

		$this->end_controls_section();

	}

	function controls__circle_styles(){

		$this->start_controls_section(
			'section_circle_style',
			[
				'label' => __( 'Circle Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'layout' => 'circle',
				],
			]
		);

			$this->add_responsive_control(
				'circle_size',
				[
					'label' => esc_html__( 'Size', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 2,
					'max' => 100,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}}' => '--circle-size: {{VALUE}}px',
					],
				]
			);

			$this->add_responsive_control(
				'circle_size_hover',
				[
					'label' => esc_html__( 'Hover Scale', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 1.4,
					'min' => 1,
					'max' => 4,
					'step' => 0.05,
					'selectors' => [
						'{{WRAPPER}} .btn:hover' => '--i-tr:scale({{VALUE}})',
					],
				]
			);

			$this->add_control(
				'circle_color',
				[
					'label' => esc_html__( 'Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}}' => '--circle-color: {{VALUE}}',
					],
				]
			);

		$this->end_controls_section();

	}

	function controls__lottie_styles(){

		$this->start_controls_section(
			'section_lottie_style',
			[
				'label' => __( 'Lottie Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'layout' => 'lottie',
				],
			]
		);

			$this->add_control(
				'lottie_source',
				[
					'label' => __( 'Source', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'media_file',
					'options' => [
						'media_file' => __( 'Media File', 'rey-core' ),
						'external_url' => __( 'External URL', 'rey-core' ),
					],
					'frontend_available' => true,
				]
			);

			$this->add_control(
				'lottie_source_external_url',
				[
					'label' => __( 'External URL', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::URL,
					'condition' => [
						'lottie_source' => 'external_url',
					],
					'dynamic' => [
						'active' => true,
					],
					'placeholder' => __( 'Enter your URL', 'rey-core' ),
					'frontend_available' => true,
				]
			);

			$this->add_control(
				'lottie_source_json',
				[
					'label' => __( 'Upload JSON File', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::MEDIA,
					'media_type' => 'application/json',
					'frontend_available' => true,
					'condition' => [
						'lottie_source' => 'media_file',
					],
				]
			);

			$this->add_control(
				'lottie_size_css',
				[
				   'label' => esc_html__( 'Custom Image Size', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', 'em', '%' ],
					'range' => [
						'px' => [
							'min' => 8,
							'max' => 1280,
							'step' => 1,
						],
						'em' => [
							'min' => 0,
							'max' => 5.0,
						],
					],
					'default' => [
						'unit' => 'px',
						'size' => 90,
					],
					'selectors' => [
						'{{WRAPPER}} .rey-triggerLottie' => 'width: {{SIZE}}{{UNIT}};',
					],
				]
			);


		$this->end_controls_section();

	}

	function controls__popover_styles(){

		$this->start_controls_section(
			'section_popover_style',
			[
				'label' => __( 'Popover Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'action' => 'popover',
				],
			]
		);

			$this->add_responsive_control(
				'popover_width',
				[
				   'label' => esc_html__( 'Width', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', 'vw' ],
					'range' => [
						'px' => [
							'min' => 80,
							'max' => 1280,
							'step' => 1,
						],
					],
					'default' => [
						'unit' => 'px',
						'size' => 390,
					],
					'selectors' => [
						'{{WRAPPER}} .rey-header-dropPanel-content' => 'width: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'popover_height',
				[
				   'label' => esc_html__( 'Height', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', 'vh' ],
					'range' => [
						'px' => [
							'min' => 80,
							'max' => 1280,
							'step' => 1,
						],
					],
					'default' => [],
					'selectors' => [
						'{{WRAPPER}} .rey-header-dropPanel-content' => 'height: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'popover_padding',
				[
					'label' => __( 'Padding', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'selectors' => [
						'{{WRAPPER}} .rey-header-dropPanel-content' => 'padding-top: {{TOP}}px; padding-left: {{LEFT}}px; padding-right: {{RIGHT}}px; --drop-padding-bottom: {{BOTTOM}}px;',
					],
				]
			);

			$this->add_control(
				'popover_offset',
				[
					'label' => esc_html__( 'Horizontal Offset', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'range' => [
						'px' => [
							'min' => -300,
							'max' => 300,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .rey-header-dropPanel-content' => '--submenu-panels-offset: {{VALUE}}px;',
					],
				]
			);

			$this->add_control(
				'popover_top_offset',
				[
					'label' => esc_html__( 'Top Offset', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'range' => [
						'px' => [
							'min' => -100,
							'max' => 200,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .rey-header-dropPanel-content' => '--submenu-panels-distance: {{VALUE}}px;',
					],
				]
			);

			$this->add_control(
				'popover_overlay',
				[
					'label' => esc_html__( 'Enable Overlay', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
				]
			);

			$this->add_control(
				'popover_scrollbar',
				[
					'label' => esc_html__( 'Enable Scrollbar', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

		$this->end_controls_section();

	}

	protected function register_controls() {

		$this->controls__settings();
		$this->controls__styles();
		$this->controls__hamburger_styles();
		$this->controls__button_styles();
		$this->controls__image_styles();
		$this->controls__circle_styles();
		$this->controls__popover_styles();
		// $this->controls__lottie_styles();

	}


	/**
	 * Render widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render()
	{

		$settings = $this->get_settings_for_display();

		// bail if no action is set
		if( $settings['action'] === '' ){
			if( current_user_can('administrator') ){
				echo esc_html__('Please select an Action type.', 'rey-core');
			}
			return;
		}

		$content_before = $content_after = '';

		$active_icon = (
			in_array( $settings['action'], ['toggle', 'popover'], true ) &&
			isset($settings['active_icon']['value']) &&
			! empty($settings['active_icon']['value'])
		 ) ? $settings['active_icon'] : '';

		$classes = [
			'btn',
			'rey-triggerBtn',
			'js-triggerBtn',
			'--' . $settings['layout'] . '2',
			'active_icon' => ($active_icon ? '--active-icon' : '')
		];

		$attributes[] = sprintf('data-trigger="%s"', esc_attr($settings['trigger']));
		$attributes[] = sprintf('data-action="%s"', esc_attr($settings['action']));

		if( isset($settings['trigger_title_attr']) && ($title_attr = $settings['trigger_title_attr']) ){
			$attributes[] = sprintf('title="%s"', esc_attr($title_attr));
		}

		// offcanvas
		if ($settings['action'] === 'offcanvas' && ($gs_offcanvas = $settings['offcanvas_panel'])){
			if( reycore__is_multilanguage() ){
				$gs_offcanvas = apply_filters('reycore/translate_ids', $gs_offcanvas, \ReyCore\Elementor\GlobalSections::POST_TYPE);
			}
			$attributes[] = sprintf('data-offcanvas-id="%s"', esc_attr($gs_offcanvas));
			add_filter("reycore/module/offcanvas_panels/load_panel={$gs_offcanvas}", '__return_true');
		}

		// Toggle
		else if ($settings['action'] === 'toggle' && ($toggle_selector = $settings['toggle_css_selector'])){

			// force a dot if missing
			if( ($selector_array = str_split($toggle_selector, 1)) && ! in_array($selector_array[0], ['.', '#'], true) ){
				$toggle_selector = '.' . $toggle_selector;
			}

			$attributes[] = sprintf('data-selector="%s"', esc_attr($toggle_selector));

			// start hidden
			if( $settings['toggle_start_opened'] === '' ){

				if( ! \Elementor\Plugin::$instance->editor->is_edit_mode() ){
					printf('<style id="rey-toggle-css-%1$s">
						.elementor-section%2$s > .elementor-container,
						.elementor-column%2$s > .elementor-widget-wrap,
						.elementor-element%2$s > .elementor-widget-container { display:none; }
					</style>', esc_attr($this->get_id()), esc_attr($toggle_selector));
				}

			}
			else {
				$classes[] = '--is-active';
			}

			if( $settings['toggle_animated'] !== '' ){
				$classes[] = '--toggle-animated';
			}

			reycore_assets()->add_scripts(['reycore-widget-trigger-v2-scripts']);

		}

		// Popover
		else if ($settings['action'] === 'popover' && ($popover_type = $settings['popover_type'])){

			reycore_assets()->add_deferred_styles($this->get_style_name('popover'));

			$popover_content = '';

			if( 'text' === $popover_type ){
				if( $popover_text = reycore__parse_text_editor($settings['popover_text']) ){
					$popover_content = $popover_text;
				}
			}
			else if( 'gs' === $popover_type ){
				if( $popover_gs = $settings['popover_gs'] ){
					reycore_assets()->defer_page_styles('elementor-post-' . $popover_gs, true);
					$popover_content = \ReyCore\Elementor\GlobalSections::do_section( $popover_gs, false, true );
				}
			}

			if( $popover_content ){

				$popover_content_attributes = '';
				$popover_wrapper_classes[] = '';

				if( '' === $settings['popover_overlay'] ){
					$classes[] = '--no-overlay';
				}

				if( \Elementor\Plugin::$instance->editor->is_edit_mode() ){
					$classes[] = '--no-overlay';
					$popover_wrapper_classes[] = '--left';
				}

				if( 'hover' === $settings['trigger'] ){
					$content_before .= '<div class="--dp-hover">';
				}

				if( '' !== $settings['popover_scrollbar'] ){
					reycore_assets()->add_scripts('rey-simple-scrollbar');
					reycore_assets()->add_styles('rey-simple-scrollbar');
					$popover_content_attributes = 'data-ss-container';
				}

				$content_before .= sprintf('<div class="rey-trigger-popover rey-header-dropPanel %s">', implode(' ', $popover_wrapper_classes) );
				$content_after .= sprintf('<div class="rey-header-dropPanel-content" data-lazy-hidden><div class="__content" %2$s>%1$s</div></div></div>', $popover_content, $popover_content_attributes);

				if( 'hover' === $settings['trigger'] ){
					$content_after .= '</div>';
				}

				reycore_assets()->add_styles('rey-header-drop-panel');
				reycore_assets()->add_scripts('rey-drop-panel');

				$classes[] = 'rey-header-dropPanel-btn';

			}
		}

		// Modal
		else if ($settings['action'] === 'modal' && ($modal_id = $settings['modal_id'])){

			$attributes[] = sprintf('data-rey-section-modal="%s"', esc_attr(wp_json_encode([
				'content' => sprintf('[data-rey-modal-id="%s"]', $modal_id),
				'id' => str_replace(['#', '.'], ['', ''], $modal_id),
			])));

		}

		// Slider
		else if ($settings['action'] === 'slide' && ($slider_id = $settings['slider_id'])){
			$attributes[] = sprintf('data-slider="%s"', esc_attr(wp_json_encode([
				'id' => str_replace(['#', '.'], ['', ''], $slider_id),
				'no' => absint($settings['slide_number']),
			])));
			reycore_assets()->add_scripts(['reycore-widget-trigger-v2-scripts']);
		}

		if( $settings['layout'] === 'button') {
			reycore_assets()->add_styles(['rey-buttons', $this->get_style_name('button')]);
			if( $btn_text = $settings['btn_text'] ){
				$classes[] = $settings['btn_style'];
				$classes[] = $settings['icon_reverse'] !== '' ? '--reverse-icon' : '';
				$classes[] = $settings['stretch_button'] !== '' ? '--stretch-btn' : '';
			}
		}
		elseif( $settings['layout'] === 'image') {
			// not much
		}
		elseif( $settings['layout'] === 'hamburger') {

			$classes[] = 'rey-headerIcon';
			$classes[] = '__hamburger';

			reycore_assets()->add_styles( ['rey-hbg', 'rey-header-icon'] );

			if( $settings['hamburger_style'] !== '' ){
				$map     =  [
					'--25b' => '25bars',
					'--2b'  => '2bars',
					'--2bh' => 'hover2bars',
					'--2b2' => 'hover2bars2',
				];

				if( isset($map[$settings['hamburger_style']]) ){
					$classes[] = '--hs-' . $map[$settings['hamburger_style']];
				}

				reycore_assets()->add_styles( 'reycore-hbg-styles' );
			}
		}

		$attributes[] = sprintf('aria-label="%s"', esc_html__('Open', 'rey-core'));

		echo $content_before;

		printf('<button class="%s" %s>', esc_attr(implode(' ', $classes)), implode(' ', $attributes));

			if( $settings['layout'] === 'button'  ){

				if( $btn_text = $settings['btn_text'] ){
					printf( '<span class="">%s</span>', do_shortcode($btn_text) );
				}

				if( ($icon = $settings['icon']) ){
					echo \ReyCore\Elementor\Helper::render_icon( $icon, [ 'aria-hidden' => 'false', 'class' => '__default rey-icon' ] );
				}

				if( $active_icon ){
					echo \ReyCore\Elementor\Helper::render_icon( $active_icon, [ 'aria-hidden' => 'true', 'class' => '__active rey-icon' ] );
				}

			}

			elseif( $settings['layout'] === 'image' ){
				if( $image = $settings['the_image'] ) {
					echo reycore__get_attachment_image( [
						'image' => $image,
						'size' => $settings['image_size'],
						'attributes' => ['class'=>'rey-triggerImg']
					] );
				}
			}

			elseif( $settings['layout'] === 'hamburger' ){

				if( ! empty($settings['hamburger_text']) ){
					reycore_assets()->add_styles( 'reycore-hbg-text' );
				}

				echo apply_filters('reycore/hamburger_icon/markup', '<div class="__bars"><span class="__bar"></span><span class="__bar"></span><span class="__bar"></span></div>');
				echo reycore__get_svg_icon(['id' => 'close']);
			}
			elseif( $settings['layout'] === 'circle') {
				echo '<svg class="rey-icon" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" style="color: var(--circle-color, currentColor); --i-fz: var(--circle-size, 16px)"><circle cx="50" cy="50" r="50"></circle></svg><style>.rey-triggerBtn {--i-trs:transform .25s ease}</style>';
			}
		echo '</button>';

		echo $content_after;

		do_action('reycore/elementor/btn_trigger');

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
