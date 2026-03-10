<?php
namespace ReyCore\Elementor\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Trigger extends \ReyCore\Elementor\WidgetsBase {

	public static function get_rey_config(){
		return [
			'id' => 'trigger',
			'title' => __( 'Navigation Trigger', 'rey-core' ),
			'icon' => 'eicon-menu-bar',
			'categories' => [ 'rey-header' ],
			'keywords' => [],
			'css' => [
				'assets/style[rtl].css',
			],
			'js' => [
				'assets/script.js',
			],
		];
	}

	public function show_in_panel() {
		return false;
	}

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
			'edit_notice',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => __( 'This element is deprecated. Please use <strong>Trigger</strong> element instead.', 'rey-core' ),
				'content_classes' => 'rey-raw-html --deprecated',
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
					''  => esc_html__( 'Select', 'rey-core' ),
					'offcanvas'  => esc_html__( 'Open Off-Canvas Panel (Global Section)', 'rey-core' ),
					// open modal?
					// dropdpwn

				],
			]
		);

		$this->add_control(
			'offcanvas_panel',
			[
				'label_block' => true,
				'label' => __( 'Off-Canvas Panel Sections', 'rey-core' ),
				'default' => '',
				'type' => 'rey-ajax-list',
				'query_args' => [
					'request' => 'global_sections_list',
					'type' => 'offcanvas',
					'edit_link' => true,
					'export' => 'id',
				],
				'condition' => [
					'action' => 'offcanvas',
				],
			]
		);

		$this->end_controls_section();
	}

	function controls__styles() {

		$this->start_controls_section(
			'section_styles',
			[
				'label' => __( 'Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE
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
					],
				]
			);


		$this->end_controls_section();
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
					'selectors' => [
						'{{WRAPPER}} .rey-triggerBtn:after' => 'content: "{{VALUE}}"',
					],
					'separator' => 'before'
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'hamburger_text_styles',
					'selector' => '{{WRAPPER}} .rey-triggerBtn:after',
				]
			);

			$this->add_control(
				'hamburger_text_mobile',
				[
					'label' => esc_html__( 'Hide text on mobiles/tablet', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
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
								'{{WRAPPER}}  .rey-triggerBtn.--button' => 'color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'btn_bg_color',
						[
							'label' => __( 'Background Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}}  .rey-triggerBtn.--button' => 'background-color: {{VALUE}}',
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
								'{{WRAPPER}}  .rey-triggerBtn.--button' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'btn_border_color',
						[
							'label' => __( 'Border Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}}  .rey-triggerBtn.--button' => 'border-color: {{VALUE}};',
							],
						]
					);

				$this->end_controls_tab();

				$this->start_controls_tab(
					'tabs_btn_hover',
					[
						'label' => esc_html__( 'Active', 'rey-core' ),
					]
				);

					$this->add_control(
						'btn_color_active',
						[
							'label' => __( 'Text Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .rey-triggerBtn.--button:hover' => 'color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'btn_bg_color_active',
						[
							'label' => __( 'Background Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .rey-triggerBtn.--button:hover' => 'background-color: {{VALUE}}',
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
								'{{WRAPPER}} .rey-triggerBtn.--button:hover' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'btn_border_color_active',
						[
							'label' => __( 'Border Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .rey-triggerBtn.--button:hover' => 'border-color: {{VALUE}};',
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
						'{{WRAPPER}} .rey-triggerBtn.--button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'btn_typo',
					'selector' => '{{WRAPPER}} .rey-triggerBtn.--button',
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
					'separator' => 'before'
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
						'{{WRAPPER}} .rey-triggerBtn.--button' => '--icon-size: {{VALUE}}px',
					],
				]
			);

			$this->add_responsive_control(
				'icon_distance',
				[
					'label' => esc_html__( 'Icon Distance', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'min' => 1,
					'max' => 300,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .rey-triggerBtn.--button' => '--icon-distance: {{VALUE}}px',
					],
				]
			);

			$this->add_responsive_control(
				'icon_color',
				[
					'label' => esc_html__( 'Icon Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-triggerBtn.--button' => '--icon-color: {{VALUE}}',
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

		$this->end_controls_section();
	}

	function controls__offcanvas_styles() {

		$this->start_controls_section(
			'section_offcanvas_settings',
			[
				'label' => __( 'Off-canvas Settings', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'action' => 'offcanvas',
					'offcanvas_panel!' => '',
				],
			]
		);

			$panel_selector = sprintf('div.rey-offcanvas-wrapper[data-id="%s"]', $this->get_id());

			$this->add_control(
				'offcanvas_size',
				[
				   'label' => esc_html__( 'Panel size', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', 'vw', 'vh' ],
					'range' => [
						'px' => [
							'min' => 200,
							'max' => 3000,
							'step' => 1,
						],
						'vw' => [
							'min' => 10,
							'max' => 100,
						],
						'vh' => [
							'min' => 5,
							'max' => 100,
						],
					],
					'default' => [],
					'selectors' => [
						$panel_selector => '--panel-width:{{SIZE}}{{UNIT}}',
					],
				]
			);

			$this->add_control(
				'offcanvas_size_tablet',
				[
				   'label' => esc_html__( 'Panel size (Tablet)', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', 'vw', 'vh' ],
					'range' => [
						'px' => [
							'min' => 200,
							'max' => 3000,
							'step' => 1,
						],
						'vw' => [
							'min' => 10,
							'max' => 100,
						],
						'vh' => [
							'min' => 5,
							'max' => 100,
						],
					],
					'default' => [],
					'selectors' => [
						$panel_selector => '--panel-width-tablet:{{SIZE}}{{UNIT}}',
					],
				]
			);

			$this->add_control(
				'offcanvas_size_mobile',
				[
				   'label' => esc_html__( 'Panel size (Mobile)', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', 'vw', 'vh' ],
					'range' => [
						'px' => [
							'min' => 200,
							'max' => 3000,
							'step' => 1,
						],
						'vw' => [
							'min' => 10,
							'max' => 100,
						],
						'vh' => [
							'min' => 5,
							'max' => 100,
						],
					],
					'default' => [],
					'selectors' => [
						$panel_selector => '--panel-width-mobile:{{SIZE}}{{UNIT}}',
					],
				]
			);

			$this->add_control(
				'offcanvas_bgcolor',
				[
					'label' => esc_html__( 'Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$panel_selector => '--panel-color:{{VALUE}}',
					],
					'separator' => 'before'
				]
			);

			$this->add_control(
				'offcanvas_position',
				[
					'label' => esc_html__( 'Position', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'left',
					'options' => [
						'left'  => esc_html__( 'Left', 'rey-core' ),
						'right'  => esc_html__( 'Right', 'rey-core' ),
						'top'  => esc_html__( 'Top', 'rey-core' ),
						'bottom'  => esc_html__( 'Bottom', 'rey-core' ),
					],
				]
			);

			// -----

			$this->add_control(
				'offcanvas_close_position',
				[
					'label' => esc_html__( 'Close Position', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'inside',
					'options' => [
						'inside'  => esc_html__( 'Inside', 'rey-core' ),
						'outside'  => esc_html__( 'Outside', 'rey-core' ),
					],
					'separator' => 'before'
				]
			);

			$this->add_control(
				'offcanvas_close_text',
				[
					'label' => esc_html__( 'Close text', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'placeholder' => esc_html__( 'eg: Close', 'rey-core' ),
					'selectors' => [
						$panel_selector => '--close-text: "{{VALUE}}";',
					],
				]
			);

			$this->add_control(
				'offcanvas_close_outside_rotate',
				[
					'label' => esc_html__( 'Rotate Button', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'condition' => [
						'offcanvas_position!' => ['left', 'right'],
						'offcanvas_close_position' => 'outside',
						'offcanvas_close_text!' => '',
					]
				]
			);

			$this->add_control(
				'offcanvas_close_size',
				[
					'label' => esc_html__( 'Close Size', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 8,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						$panel_selector => '--close-size: {{VALUE}}px',
					],
				]
			);

			// -----

			$this->add_control(
				'offcanvas_transition',
				[
					'label' => esc_html__( 'Transition', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( 'Default (Slide)', 'rey-core' ),
						'slideskew'  => esc_html__( 'Slide Skew', 'rey-core' ),
						'curtain'  => esc_html__( 'Curtain', 'rey-core' ),
					],
					'separator' => 'before'
				]
			);

			$this->add_control(
				'offcanvas_transition_duration',
				[
					'label' => esc_html__( 'Transition Duration', 'rey-core' ) . ' (ms)',
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 700,
					'min' => 0,
					'max' => 2000,
					'step' => 10,
				]
			);

			$this->add_control(
				'offcanvas_animate_cols',
				[
					'label' => esc_html__( 'Animate Inside', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
				]
			);

			$this->add_control(
				'offcanvas_shift_site',
				[
					'label' => esc_html__( 'Shift Site Content', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
					'condition' => [
						'offcanvas_position' => ['left', 'right'],
					],
				]
			);

			$this->add_control(
				'offcanvas_curtain__m1_color',
				[
					'type' => \Elementor\Controls_Manager::COLOR,
					'label' => esc_html__( 'Curtain - Mask #1 Color', 'rey-core' ),
					'selectors' => [
						$panel_selector . ' .rey-offcanvas-mask.--m1' => 'background-color: {{VALUE}}',
					],
					'condition' => [
						'offcanvas_transition' => 'curtain',
					],
				]
			);

			$this->add_control(
				'offcanvas_curtain__m2_color',
				[
					'type' => \Elementor\Controls_Manager::COLOR,
					'label' => esc_html__( 'Curtain - Mask #2 Color', 'rey-core' ),
					'selectors' => [
						$panel_selector . ' .rey-offcanvas-mask.--m2' => 'background-color: {{VALUE}}',
					],
					'condition' => [
						'offcanvas_transition' => 'curtain',
					],
				]
			);

		$this->end_controls_section();
	}

	protected function register_controls() {

		$this->controls__settings();
		$this->controls__styles();
		$this->controls__hamburger_styles();
		$this->controls__button_styles();
		$this->controls__offcanvas_styles();

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
		reycore_assets()->add_styles($this->get_style_name());
		$settings = $this->get_settings_for_display();

		// bail if no action is set
		if( $settings['action'] === '' ){
			return;
		}

		$attributes = [];

		$attributes[] = sprintf('data-trigger="%s"', esc_attr($settings['trigger']));


		// offcanvas
		if ($settings['action'] === 'offcanvas' && ($gs_offcanvas = $settings['offcanvas_panel'])){

			if( reycore__is_multilanguage() ){
				$gs_offcanvas = apply_filters('reycore/translate_ids', $gs_offcanvas, \ReyCore\Elementor\GlobalSections::POST_TYPE);
			}

			$offcanvas_settings = [
				'id'                  => $this->get_id(),
				'gs'                  => $gs_offcanvas,
				'trigger'             => $settings['trigger'],
				'shift'               => $settings['offcanvas_shift_site'] === 'yes',
				'position'            => $settings['offcanvas_position'],
				'transition'          => $settings['offcanvas_transition'],
				'transition_duration' => $settings['offcanvas_transition_duration'] !== '' ? $settings['offcanvas_transition_duration'] : 700,
				'animate_inside'      => $settings['offcanvas_animate_cols'] === 'yes',
				'close_position'      => $settings['offcanvas_close_position'],
				'close_text'          => $settings['offcanvas_close_text'],
				'close_rotate'        => $settings['offcanvas_close_outside_rotate'] !== '' &&
											$settings['offcanvas_close_position'] === 'outside' &&
											in_array($settings['offcanvas_position'], ['left', 'right'], true),
			];

			$attributes[] = sprintf('data-offcanvas-settings="%s"', esc_attr(wp_json_encode($offcanvas_settings)));

			add_filter("reycore/module/offcanvas_panels/load_panel={$gs_offcanvas}", '__return_true');

		}

		$classes = [
			'btn',
			'rey-triggerBtn',
			'js-triggerBtn',
			'--' . $settings['layout'],
			'--pos-' . $settings['offcanvas_position'],
		];

		reycore_assets()->add_styles(['rey-buttons','rey-header-icon']);

		if( $settings['layout'] === 'button' && $btn_text = $settings['btn_text'] ){
			$classes[] = $settings['btn_style'];
			$classes[] = $settings['icon_reverse'] === 'yes' ? '--reverse-icon' : '';
		}
		else {
			$classes[] = 'rey-headerIcon';

			if( $settings['hamburger_style'] !== '' ){
				$classes[] = '--hamburger' . $settings['hamburger_style'];
			}

			if( $settings['hamburger_text_mobile'] === 'yes' ){
				$classes[] = '--hbg-text-mobile';
			}

		}

		printf('<button class="%s" %s>', esc_attr(implode(' ', $classes)), implode(' ', $attributes));

			if( $settings['layout'] === 'button'  ){

				if( $btn_text = $settings['btn_text'] ){
					printf( '<span>%s</span>', do_shortcode($settings['btn_text']) );
				}

				if( ($icon = $settings['icon']) ){
					\Elementor\Icons_Manager::render_icon( $icon, [ 'aria-hidden' => 'true', 'class' => '' ] );
				}

			}

			elseif( $settings['layout'] === 'hamburger' ){
				echo '<span></span><span></span><span></span>';
			}

		echo '</button>';

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
