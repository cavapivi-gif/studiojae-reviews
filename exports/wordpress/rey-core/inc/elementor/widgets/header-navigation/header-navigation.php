<?php
namespace ReyCore\Elementor\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class HeaderNavigation extends \ReyCore\Elementor\WidgetsBase {

	private $_settings = [];

	public static function get_rey_config(){
		return [
			'id' => 'header-navigation',
			'title' => __( 'Header Navigation', 'rey-core' ),
			'icon' => 'eicon-nav-menu',
			'categories' => [ 'rey-header' ],
			'keywords' => [ 'mega', 'menu' ],
			'css' => [
				'assets/text.css',
				'assets/social.css',
			],
		];
	}

	public function show_in_panel(){
		if( ! (bool) reycore__get_purchase_code() ){
			return false;
		}
		return apply_filters('reycore/elementor/header_nav/show_in_panel', true);
	}

	public function __construct( $data = [], $args = null ) {

		if( class_exists('\WooCommerce') ){
			if ( $data && isset($data['settings']) && $settings = $data['settings'] ) {
				if( isset($settings['mobile_panel_disable_footer']) && 'yes' === $settings['mobile_panel_disable_footer'] ){
					remove_action('rey/mobile_nav/footer', 'reycore_wc__add_mobile_nav_link', 5);
				}
			}
		}

		parent::__construct( $data, $args );
	}

	public function get_custom_help_url() {
		return reycore__support_url('kb/rey-elements-header/#navigation');
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
			'section_settings',
			[
				'label' => __( 'Settings', 'rey-core' ),
			]
		);

		$this->add_control(
			'edit_notice',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => esc_html__( 'If you don\'t want to show this element, simply remove it from its section.', 'rey-core' ),
				'content_classes' => 'rey-raw-html --notice',
			]
		);

		$cst_link_query['autofocus[section]'] = \ReyCore\Customizer\Options\Header\Navigation::get_id();
		$cst_link = add_query_arg( $cst_link_query, admin_url( 'customize.php' ) );

		$this->add_control(
			'edit_link',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => sprintf( __( 'Navigation options can be edited into the <a href="%1$s" target="_blank">Customizer Panel > Header > Navigation</a>, but you can also override those settings below.', 'rey-core' ), $cst_link ),
				'content_classes' => 'rey-raw-html',
				'condition' => [
					'custom' => [''],
				],
			]
		);

		$this->add_control(
			'custom',
			[
				'label' => __( 'Override global settings', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->add_control(
			'menu_source',
			[
				'label' => __( 'Menu Source', 'rey-core' ),
				'type' => 'rey-ajax-list',
				'query_args' => [
					'request'   => 'get_nav_menus_options',
					'edit_link' => true,
				],
				'default' => 'main-menu',
				'condition' => [
					'custom!' => [''],
				],
			]
		);

		$this->add_control(
			'enable_mobile',
			[
				'label' => esc_html__( 'Enable Mobile Menu', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
				'condition' => [
					'custom!' => '',
				],
			]
		);

		$this->add_control(
			'mobile_menu_source_type',
			[
				'label_block' => true,
				'label' => __( 'Mobile Menu Source Type', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'' => esc_html__('Default', 'rey-core'),
					'offcanvas' => esc_html__('Off-Canvas Global Section', 'rey-core'),
				],
				'default' => '',
				'condition' => [
					'custom!' => '',
					'enable_mobile!' => '',
				],
			]
		);

		$this->add_control(
			'mobile_menu_source',
			[
				'label' => __( 'Mobile Menu Source', 'rey-core' ),
				'type' => 'rey-ajax-list',
				'query_args' => [
					'request'   => 'get_nav_menus_options',
					'edit_link' => true,
				],
				'default' => 'main-menu',
				'condition' => [
					'custom!' => '',
					'enable_mobile!' => '',
					'mobile_menu_source_type' => '',
				],
			]
		);

		$this->add_control(
			'offcanvas_panel',
			[
				'label_block' => true,
				'label'       => __( 'Choose Off-Canvas Panel', 'rey-core' ),
				'type'        => 'rey-query',
				'default'     => '',
				'placeholder' => esc_html__('- Select -', 'rey-core'),
				'query_args'  => [
					'type'      => 'posts',
					'post_type' => \ReyCore\Elementor\GlobalSections::POST_TYPE,
					'meta'      => [
						'meta_key'   => 'gs_type',
						'meta_value' => 'offcanvas',
					],
					'edit_link' => true,
				],
				'condition' => [
					'custom!' => '',
					'enable_mobile!' => '',
					'mobile_menu_source_type' => 'offcanvas',
				],
			]
		);

		$this->add_control(
			'breakpoint',
			[
				'label'       => __( 'Breakpoint for mobile navigation', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
				'description' => __( 'This will control at which window size to switch the menu into mobile navigation.', 'rey-core' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'min'       => 768,
				'step'      => 1,
				'default'     => 1025,
				'condition' => [
					'custom!' => '',
					'enable_mobile!' => '',
				],
				'placeholder' => 1025
			]
		);

		$this->end_controls_section();


		$this->controls__first_level();
		$this->controls__submenus();
		$this->controls__submenu_items();
		$this->controls__mobile_panel();
		$this->controls__mobile_panel_footer();
		$this->controls__hamburger_icon();

	}

	public function controls__first_level(){

		$this->start_controls_section(
			'section_styles',
			[
				'label' => __( 'Menu Items (1st level)', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE
			]
		);

			$this->add_control(
				'horizontal_spacing',
				[
				'label' => esc_html__( 'Horizontal Item Spacing', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', 'rem' ],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 180,
							'step' => 1,
						],
						'rem' => [
							'min' => 0,
							'max' => 5.0,
						],
					],
					'default' => [
						'unit' => 'rem',
						'size' => 1,
					],
					'selectors' => [
						'{{WRAPPER}}' => '--header-nav-x-spacing: {{SIZE}}{{UNIT}};',
					],
					'separator' => 'before'
				]
			);

			$this->add_responsive_control(
				'horizontal_alignment',
				[
					'label' => esc_html__( 'Horizontal Alignment', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						'' => __( 'Default', 'rey-core' ),
						'flex-start' => __( 'Start', 'rey-core' ),
						'center' => __( 'Center', 'rey-core' ),
						'flex-end' => __( 'End', 'rey-core' ),
						'space-between' => __( 'Space Between', 'rey-core' ),
						'space-around' => __( 'Space Around', 'rey-core' ),
						'space-evenly' => __( 'Space Evenly', 'rey-core' ),
					],
					'selectors' => [
						'{{WRAPPER}} .rey-mainMenu--desktop' => 'justify-content: {{VALUE}}; width: 100%;',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name'      => 'typography',
					'selector'  => '{{WRAPPER}} .rey-mainMenu--desktop > .menu-item > a',
				]
			);

			$this->add_control(
				'style',
				[
					'label' => __( 'Menu Items Style', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => __( 'Default', 'rey-core' ),
						'simple'  => __( 'Simple', 'rey-core' ),
						'ulr'  => __( 'Left to Right Underline', 'rey-core' ),
						'ulr --thinner'  => __( 'Left to Right Underline (thinner)', 'rey-core' ),
						'ut'  => __( 'Left to Right Thick Underline', 'rey-core' ),
						'ut2'  => __( 'Left to Right Background', 'rey-core' ),
						'ub'  => __( 'Bottom Underline', 'rey-core' ),
						'sc'  => __( 'Scale on hover', 'rey-core' ),
					],
				]
			);

			$this->add_control(
				'deco_color',
				[
					'label' => esc_html__( 'Menu deco. color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-mainMenu--desktop > .menu-item > a:after' => 'color: {{VALUE}}',
					],
					'condition' => [
						'style!' => ['', 'simple'],
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
						'text_color',
						[
							'label' => __( 'Text Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .rey-mainMenu--desktop > .menu-item > a' => 'color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'bg_color',
						[
							'label' => __( 'Background Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .rey-mainMenu--desktop > .menu-item > a' => 'background-color: {{VALUE}}',
							],
						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Border::get_type(),
						[
							'name' => 'border',
							'selector' => '{{WRAPPER}} .rey-mainMenu--desktop > .menu-item > a',
							// 'separator' => 'before',
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
						'hover_text_color',
						[
							'label' => __( 'Hover Text Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .rey-mainMenu--desktop > .menu-item:hover > a, {{WRAPPER}} .rey-mainMenu--desktop > .menu-item.--hover > a, {{WRAPPER}} .rey-mainMenu--desktop > .menu-item > a:hover, {{WRAPPER}} .rey-mainMenu--desktop > .menu-item.current-menu-item > a' => 'color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'hover_bg_color',
						[
							'label' => __( 'Background Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .rey-mainMenu--desktop > .menu-item:hover > a, {{WRAPPER}} .rey-mainMenu--desktop > .menu-item.--hover > a, {{WRAPPER}} .rey-mainMenu--desktop > .menu-item > a:hover, {{WRAPPER}} .rey-mainMenu--desktop > .menu-item.current-menu-item > a' => 'background-color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'hover_border_color',
						[
							'label' => __( 'Border Color', 'elementor' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'condition' => [
								'border_border!' => '',
							],
							'selectors' => [
								'{{WRAPPER}} .rey-mainMenu--desktop > .menu-item:hover > a, {{WRAPPER}} .rey-mainMenu--desktop > .menu-item.--hover > a, {{WRAPPER}} .rey-mainMenu--desktop > .menu-item > a:hover, {{WRAPPER}} .rey-mainMenu--desktop > .menu-item.current-menu-item > a' => 'border-color: {{VALUE}};',
								'{{WRAPPER}} .rey-mainMenu--desktop > .menu-item:focus > a, {{WRAPPER}} .rey-mainMenu--desktop > .menu-item > a:focus' => 'border-color: {{VALUE}};',
							],
						]
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();

			$this->add_responsive_control(
				'items_padding',
				[
					'label' => __( 'Padding', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em', 'rem' ],
					'selectors' => [
						'{{WRAPPER}} .rey-mainMenu--desktop > .menu-item > a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						'{{WRAPPER}}' => '--indicator-padding: calc({{RIGHT}}{{UNIT}} * var(--padding-factor, 1.5));', // extra for the indicator size
						'.rtl {{WRAPPER}}' => '--indicator-padding: calc({{LEFT}}{{UNIT}} * var(--padding-factor, 1.5));',
					],
				]
			);

			$this->add_control(
				'border_radius',
				[
					'label' => __( 'Border Radius', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%' ],
					'selectors' => [
						'{{WRAPPER}} .rey-mainMenu--desktop > .menu-item > a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_control(
				'icons_visibility',
				[
					'label' => esc_html__( 'Show Icon/Image', 'rey-core' ),
					'description' => sprintf( _x( 'First make sure to add an icon or image to the menu item in Appearance > Menus (<a href="%s" target="_blank">example</a>).', 'Elementor control description', 'rey-core' ), 'https://d.pr/i/nVDcLX' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
					'separator' => 'before'
				]
			);

			$this->add_responsive_control(
				'icons_size',
				[
					'label' => esc_html__( 'Icon/Image Size', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 5,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} a[data-has-icon]' => '--icon-size: {{VALUE}}px',
					],
					'condition' => [
						'icons_visibility' => 'yes',
					],
				]
			);

			$this->add_control(
				'icons_distance',
				[
					'label' => esc_html__( 'Icon/Image Distance', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} a[data-has-icon]' => '--icon-distance: {{VALUE}}px',
					],
					'condition' => [
						'icons_visibility' => 'yes',
					],
				]
			);

			$this->add_control(
				'icons_position',
				[
					'label' => esc_html__( 'Icon/Image Position', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'start',
					'options' => [
						'start' => __( 'Start', 'rey-core' ),
						'end' => __( 'End', 'rey-core' ),
						'above' => __( 'Above', 'rey-core' ),
					],
					'prefix_class' => '--icons-',
					'condition' => [
						'icons_visibility' => 'yes',
					],
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_parent_items_styles',
			[
				'label' => __( 'Parent Menu Items', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE
			]
		);

			$this->add_control(
				'nav_indicator_title',
				[
					'label' => esc_html__( 'PARENT ITEMS INDICATOR', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_control(
				'nav_indicator',
				[
					'label' => esc_html__( 'Select Indicator', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'circle',
					'options' => [
						'none'  => esc_html__( 'None', 'rey-core' ),
						'circle'  => esc_html__( 'Circle', 'rey-core' ),
						'arrow'  => esc_html__( 'Arrow', 'rey-core' ),
						'arrow2'  => esc_html__( 'Arrow v2', 'rey-core' ),
						'dash'  => esc_html__( 'Dash', 'rey-core' ),
						'plus'  => esc_html__( 'Plus', 'rey-core' ),
					],
				]
			);

			$this->add_control(
				'nav_indicator_color',
				[
					'label' => esc_html__( 'Indicator Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-mainMenu .menu-item-has-children .--submenu-indicator' => 'color: {{VALUE}}',
					],
					'condition' => [
						'nav_indicator!' => 'none',
					],
				]
			);

			$this->add_control(
				'nav_indicator_size',
				[
					'label' => esc_html__( 'Indicator Size', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'range' => [
						'px' => [
							'min' => 1,
							'max' => 40,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .rey-mainMenu .menu-item-has-children .--submenu-indicator' => 'font-size: {{VALUE}}px',
					],
					'condition' => [
						'nav_indicator!' => 'none',
					],
				]
			);

		$this->end_controls_section();
	}

	public function controls__submenus(){

		$this->start_controls_section(
			'section_styles_submenu_panels',
			[
				'label' => __( 'Sub-Menu Panels', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE
			]
		);

			$this->add_control(
				'hover_overlay',
				[
					'label' => esc_html__( 'Hover Overlay', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''                => esc_html__( '- Inherit -', 'rey-core' ),
						'show'            => esc_html__( 'Show', 'rey-core' ),
						'show_header_top' => esc_html__( 'Show & Header Over', 'rey-core' ),
						'hide'            => esc_html__( 'Disabled', 'rey-core' ),
					],
					// 'frontend_available' => true,
				]
			);


			$this->add_control(
				'submenu_width',
				[
				'label' => esc_html__( 'Sub-menu Width', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'range' => [
						'px' => [
							'min' => 120,
							'max' => 400,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .rey-mainMenu--desktop .--is-regular .sub-menu > .menu-item > a' => 'min-width: {{SIZE}}{{UNIT}};',
					],
					'separator' => 'before',
				]
			);

			$this->add_control(
				'submenu_bg_color',
				[
					'label' => esc_html__( 'Sub-menu Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-mainMenu--desktop .--is-regular' => '--body-bg-color: {{VALUE}}',
						// '{{WRAPPER}} .rey-mainMenu--desktop .--is-mega .rey-mega-gs:before' => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'submenus_padding_y',
				[
					'label' => esc_html__( 'Padding Vertical', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}}' => '--submenus-padding:{{VALUE}}px',
					],
					'separator' => 'before'
				]
			);

			$this->add_control(
				'submenus_padding_x',
				[
					'label' => esc_html__( 'Padding Horizontal', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}}' => '--submenus-padding-x:{{VALUE}}px',
					],
				]
			);

			$this->add_control(
				'submenus_radius',
				[
					'label' => esc_html__( 'Border Radius', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}}' => '--submenus-radius:{{VALUE}}px',
					],
				]
			);

			$this->add_control(
				'submenus_distance',
				[
					'label' => esc_html__( 'Top Distance', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}}' => '--submenu-panels-distance:{{VALUE}}px',
					],
					'separator' => 'before'
				]
			);

			$this->add_control(
				'submenu_top_indicator',
				[
					'label' => esc_html__( 'Sub-menus Top Pointer', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

			$this->add_control(
				'mobile_submenu_title',
				[
				'label' => esc_html__( 'MOBILE', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_control(
				'mobile_panel_disable_mega_gs',
				[
					'label' => esc_html__( 'Disable Mega Menus', 'rey-core' ),
					// 'description' => esc_html__( 'Disable mega menu global sections in mobile panel.', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'prefix_class' => '',
				]
			);

			$this->add_control(
				'mobile_submenu_items_state',
				[
					'label' => esc_html__( 'Sub-menus Display', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'collapsed',
					'options' => [
						'collapsed'  => esc_html__( 'Collapsed', 'rey-core' ),
						'expanded'  => esc_html__( 'Expanded', 'rey-core' ),
					],
					'prefix_class' => '--submenu-display-'
				]
			);

			$this->add_control(
				'offcanvas_tap_behavior',
				[
					'label' => esc_html__( 'Click behaviour on Mobiles', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					// 'label_block' => true,
					'default' => 'open',
					'options' => [
						'open'  => esc_html__( 'Open Sub-menus', 'rey-core' ),
						'link'  => esc_html__( 'Go to Link', 'rey-core' ),
					],
					'condition' => [
						'nav_indicator!' => 'none',
					],
					'prefix_class' => '--tap-'
				]
			);

		$this->end_controls_section();

	}

	public function controls__submenu_items(){

		$this->start_controls_section(
			'section_styles_submenus',
			[
				'label' => __( 'Sub-Menu Items', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE
			]
		);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name'      => 'typography_sub',
					'selector'  => '{{WRAPPER}} .rey-mainMenu--desktop .--is-regular .sub-menu .menu-item > a, {{WRAPPER}} .rey-mainMenu--desktop .--is-mega-cols .sub-menu .menu-item > a',
				]
			);


			$this->start_controls_tabs( 'tabs_styles_submenus');

				$this->start_controls_tab(
					'tab_default_submenus',
					[
						'label' => __( 'Default', 'rey-core' ),
					]
				);

					$this->add_control(
						'text_color_submenus',
						[
							'label' => __( 'Item Text Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .rey-mainMenu--desktop .--is-regular .sub-menu .menu-item > a, {{WRAPPER}} .rey-mainMenu--desktop .--is-mega-cols .sub-menu .menu-item > a' => 'color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'bg_color_submenus',
						[
							'label' => __( 'Item Background Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .rey-mainMenu--desktop .--is-regular .sub-menu .menu-item > a, {{WRAPPER}} .rey-mainMenu--desktop .--is-mega-cols .sub-menu .menu-item > a' => 'background-color: {{VALUE}}',
							],
						]
					);

				$this->end_controls_tab();

				$this->start_controls_tab(
					'tab_hover_submenus',
					[
						'label' => __( 'Hover', 'rey-core' ),
					]
				);

					$this->add_control(
						'hover_text_color_submenus',
						[
							'label' => __( 'Hover Text Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .rey-mainMenu--desktop .--is-regular .sub-menu .menu-item:hover > a, {{WRAPPER}} .rey-mainMenu--desktop .--is-regular .sub-menu .menu-item > a:hover, {{WRAPPER}} .rey-mainMenu--desktop .--is-regular .sub-menu .menu-item.current-menu-item > a,
								{{WRAPPER}} .rey-mainMenu--desktop .--is-mega-cols .sub-menu .menu-item:hover > a, {{WRAPPER}} .rey-mainMenu--desktop .--is-mega-cols .sub-menu .menu-item > a:hover, {{WRAPPER}} .rey-mainMenu--desktop .--is-mega-cols .sub-menu .menu-item.current-menu-item > a' => 'color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'hover_bg_color_submenus',
						[
							'label' => __( 'Background Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .rey-mainMenu--desktop .--is-regular .sub-menu .menu-item:hover > a, {{WRAPPER}} .rey-mainMenu--desktop .--is-regular .sub-menu .menu-item > a:hover, {{WRAPPER}} .rey-mainMenu--desktop .--is-regular .sub-menu .menu-item.current-menu-item > a,
								{{WRAPPER}} .rey-mainMenu--desktop .--is-mega-cols .sub-menu .menu-item:hover > a, {{WRAPPER}} .rey-mainMenu--desktop .--is-mega-cols .sub-menu .menu-item > a:hover, {{WRAPPER}} .rey-mainMenu--desktop .--is-mega-cols .sub-menu .menu-item.current-menu-item > a' => 'background-color: {{VALUE}}',
							],
						]
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();

		$this->end_controls_section();

	}

	public function controls__hamburger_icon(){

		$this->start_controls_section(
			'section_styles_mobile_hamburger',
			[
				'label' => __( 'Hamburger Icon', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'enable_mobile!' => '',
				],
			]
		);

		$this->add_control(
			'hamburger_style',
			[
				'label' => esc_html__( 'Icon Bars', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => esc_html__( 'Default - 3 bars', 'rey-core' ),
					'--25bars'  => esc_html__( '2.5 bars', 'rey-core' ),
					'--2bars'  => esc_html__( '2 bars', 'rey-core' ),
					'--hover2bars'  => esc_html__( '2 bars + hover', 'rey-core' ),
					'--hover2bars2'  => esc_html__( '2 bars Alternative', 'rey-core' ),
				],
				// 'prefix_class' => '--hs-'
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
				'default' => 20,
				'selectors' => [
					'{{WRAPPER}} .rey-mainNavigation-mobileBtn' => '--hbg-bars-width: {{VALUE}}px',
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
				'default' => 2,
				'selectors' => [
					'{{WRAPPER}} .rey-mainNavigation-mobileBtn' => '--hbg-bars-thick: {{VALUE}}px',
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
				'default' => 4,
				'selectors' => [
					'{{WRAPPER}} .rey-mainNavigation-mobileBtn' => '--hbg-bars-distance: {{VALUE}}px',
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
				'default' => 2,
				'selectors' => [
					'{{WRAPPER}} .rey-mainNavigation-mobileBtn' => '--hbg-bars-roundness: {{VALUE}}px',
				],
			]
		);

		$this->add_responsive_control(
			'hamburger_color',
			[
				'label' => esc_html__( 'Icon Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-mainNavigation-mobileBtn' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'hamburger_trigger_hover',
			[
				'label' => esc_html__( 'Trigger on hover?', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'prefix_class' => '--hbg-hover-'
			]
		);

		$this->add_control(
			'hamburger_trigger_hover_close',
			[
				'label' => esc_html__( 'Close on mouseleave?', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'prefix_class' => '--hbg-hover-close-',
				'condition' => [
					'hamburger_trigger_hover!' => '',
				],
			]
		);

		$this->add_control(
			'hamburger_text',
			[
				'label' => esc_html__( 'Custom Text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'selectors' => [
					'{{WRAPPER}} .__hamburger:after' => 'content: "{{VALUE}}"',
				],
				'separator' => 'before'
			]
		);

		$this->add_control(
			'hamburger_text_position',
			[
				'label' => esc_html__( 'Flip Position', 'rey-core' ),
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
			'hamburger_text_mobile',
			[
				'label' => esc_html__( 'Hide text on mobiles/tablet', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
				'prefix_class' => '--text-mobile-',
				'condition' => [
					'hamburger_text!' => '',
				],
			]
		);


		$this->end_controls_section();
	}

	public function controls__mobile_panel(){

		$this->start_controls_section(
			'section_styles_mobile',
			[
				'label' => __( 'Mobile Panel', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'enable_mobile!' => '',
				],
			]
		);

		$this->add_control(
			'rey_hide_panel_toggle_visibility',
			[
				'label' => __( 'Toggle Visibility', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::BUTTON,
				'button_type' => 'default',
				'text' => __( 'Toggle', 'rey-core' ),
				'event' => 'rey:header_nav_visibility',
			]
		);

		$this->add_control(
			'panel_slide_direction',
			[
				'label' => esc_html__( 'Slide Direction', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'left',
				'options' => [
					'left'  => esc_html__( 'Left', 'rey-core' ),
					'right'  => esc_html__( 'Right', 'rey-core' ),
				],
				'prefix_class' => '--panel-dir--'
			]
		);

		$this->add_control(
			'desktop_panel_width',
			[
			'label' => esc_html__( 'Panel width', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'vw' ],
				'range' => [
					'px' => [
						'min' => 200,
						'max' => 1920,
						'step' => 1,
					],
					'vw' => [
						'min' => 0,
						'max' => 100,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .rey-mainNavigation.rey-mainNavigation--mobile' => 'max-width: {{SIZE}}{{UNIT}};',
				]
			]
		);

		$this->add_control(
			'mobile_panel_width',
			[
			'label' => esc_html__( 'Panel width (Mobile, VW only)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'range' => [
					'vw' => [
						'min' => 0,
						'max' => 100,
						'step' => 1,
					],
				],
				'selectors' => [
					'(mobile){{WRAPPER}} .rey-mainNavigation.rey-mainNavigation--mobile' => 'max-width: {{SIZE}}vw;',
				]
			]
		);

		$this->add_control(
			'mobile_close_size',
			[
				'label' => esc_html__( 'Close button size', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 8,
				'max' => 1000,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .rey-mobileMenu-close' => 'font-size: {{VALUE}}px',
				],
			]
		);

		$this->add_control(
			'mobile_panel_logo_max_width',
			[
			'label' => esc_html__( 'Logo Max-Width', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'range' => [
					'px' => [
						'min' => 10,
						'max' => 480,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .rey-mobileNav-header .rey-siteLogo img, {{WRAPPER}} .rey-mobileNav-header .rey-siteLogo .custom-logo' => 'max-width: {{SIZE}}px; width: 100%;',
				],
				'separator' => 'before'
			]
		);

		$this->add_control(
			'mobile_panel_logo_max_height',
			[
			'label' => esc_html__( 'Logo Max-Height', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'range' => [
					'px' => [
						'min' => 10,
						'max' => 480,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .rey-mobileNav-header .rey-siteLogo img, {{WRAPPER}} .rey-mobileNav-header .rey-siteLogo .custom-logo' => 'max-height: {{SIZE}}px;',
				],

			]
		);

		$this->add_control(
			'mobile_panel_logo_img',
			[
			'label' => esc_html__( 'Custom Logo', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::MEDIA,
				'default' => [],
				'separator' => 'after',
				'media_type' => [],
			]
		);

		$this->add_control(
			'text_color_mobile',
			[
				'label' => __( 'Text & Links Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-mainNavigation--mobile, {{WRAPPER}} .rey-mainNavigation--mobile a' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'hover_text_color_mobile',
			[
				'label' => __( 'Links hover color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-mainNavigation--mobile .menu-item:hover > a, {{WRAPPER}} .rey-mainNavigation--mobile a:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'bg_color_mobile',
			[
				'label' => __( 'Panel Background Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-mainNavigation--mobile' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'typography_mobile',
				// includes: level 0 links and non GS items
				'selector' => '{{WRAPPER}} .rey-mainMenu-mobile > .menu-item > a, {{WRAPPER}} .rey-mainMenu-mobile > .menu-item.--is-mega-cols .menu-item > a, {{WRAPPER}} .rey-mainMenu-mobile > .menu-item.--is-regular .menu-item > a',
			]
		);

		$this->end_controls_section();

	}

	public function controls__mobile_panel_footer(){

		$this->start_controls_section(
			'section_styles_mobile_footer',
			[
				'label' => __( 'Mobile Panel Footer', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'enable_mobile!' => '',
				],
			]
		);

			$this->add_control(
				'mobile_footer_gs',
				[
					'label' => esc_html__( 'Add Global Section?', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( 'No', 'rey-core' ),
						'before'  => esc_html__( 'Add Before', 'rey-core' ),
						'after'  => esc_html__( 'Add After', 'rey-core' ),
						'nav'  => esc_html__( 'Add After Navigation', 'rey-core' ),
					],
				]
			);

			$this->add_control(
				'mobile_footer_gs_item',
				[
					'label_block' => true,
					'label'       => __( 'Choose Global section', 'rey-core' ),
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
					'condition' => [
						'mobile_footer_gs!' => '',
					],
				]
			);

			$this->add_control(
				'mobile_panel_disable_footer',
				[
					'label' => esc_html__( 'Disable Footer Menu', 'rey-core' ),
					'description' => esc_html__( 'Disable the Login & Logout link items.', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'separator' => 'before',
					'condition' => [
						'mobile_footer_gs!' => 'replace',
					],
				]
			);

			$social_icons = new \Elementor\Repeater();

				$social_icons->add_control(
					'social',
					[
						'label' => __( 'Social Icon', 'rey-core' ),
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
						'title_field' => '{{{ social.replace( \'-\', \' \' ).replace( /\b\w/g, function( letter ){ return letter.toUpperCase() } ) }}}',
						'separator' => 'before',
						'prevent_empty' => false,
						'condition' => [
							'mobile_footer_gs!' => 'replace',
						],
					]
				);

				$this->add_control(
					'social_text',
					[
						'label' => __( '"Follow" Text', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'default' => __( 'FOLLOW US', 'rey-core' ),
						'placeholder' => __( 'eg: FOLLOW US', 'rey-core' ),
						'condition' => [
							'mobile_footer_gs!' => 'replace',
						],
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' => 'social_typo',
						'label' => esc_html__( 'Icons Typo', 'rey-core' ),
						'selector' => '{{WRAPPER}} .rey-mobileNav-socialIcons-link',
						'condition' => [
							'mobile_footer_gs!' => 'replace',
						],
					]
				);

				$this->add_control(
					'social_color',
					[
						'label' => esc_html__( 'Icons Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .rey-mobileNav-socialIcons-link' => 'color: {{VALUE}}',
						],
						'condition' => [
							'mobile_footer_gs!' => 'replace',
						],
					]
				);

				$this->add_control(
					'mobile_panel_footer_separator_color',
					[
						'label' => esc_html__( 'Separator Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}}' => '--mobile-nav-footer-border-color: {{VALUE}}',
						],
						'separator' => 'before'
					]
				);

				$this->add_control(
					'mobile_panel_footer_separator_spacing',
					[
						'label' => esc_html__( 'Separator Spacing', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
						'type' => \Elementor\Controls_Manager::NUMBER,
						'default' => '',
						'min' => 0,
						'max' => 100,
						'step' => 1,
						'selectors' => [
							'{{WRAPPER}}' => '--mobile-nav-footer-top-spacing: {{VALUE}}px',
						],
					]
				);

		$this->end_controls_section();
	}

	/**
	 * Generate breakpoint CSS
	 */
	public function get_breakpoint_css()
	{
		$css = '<style>';
		$css .= '.elementor-element-%1$s, .rey-mobileNav--%1$s{ --nav-breakpoint-desktop: none; --nav-breakpoint-mobile: flex; }';
		$css .= '@media (min-width: %2$spx) { .elementor-element-%1$s, .rey-mobileNav--%1$s { --nav-breakpoint-desktop: flex; --nav-breakpoint-mobile: none; } }';
		$css .= '</style>';
		printf( $css, $this->get_id(), $this->_settings['breakpoint'] );
	}

	public function set_nav_options( $args ){

		$nav_styles[] = $this->_settings['submenu_top_indicator'] !== '' ? '--submenu-top' : '';

		if( $this->_settings['style'] !== '' ){

			$nav_styles[] = 'rey-navEl';
			$nav_styles[] = '--menuHover-' . $this->_settings['style'];

			$args['nav_style'] = '';

			reycore_assets()->add_styles( 'reycore-elementor-nav-styles' );

		}

		$args['nav_ul_style'] = implode(' ', $nav_styles);

		if( isset($this->_settings['custom']) && $this->_settings['custom'] ){

			$args['mobile_menu'] = $this->_settings['enable_mobile'] !== '' ? $this->_settings['mobile_menu_source'] : false;
			$args['menu'] = $this->_settings['menu_source'];
			$args['override'] = true;

			$mobile_nav_custom_attributes = $mobile_nav_custom_classes = [];

			if ($this->_settings['mobile_menu_source_type'] === 'offcanvas' && ($gs_offcanvas = $this->_settings['offcanvas_panel'])){

				if( reycore__is_multilanguage() ){
					$gs_offcanvas = apply_filters('reycore/translate_ids', $gs_offcanvas, \ReyCore\Elementor\GlobalSections::POST_TYPE);
				}

				$args['load_hamburger'] = [
					'attributes' => [
						'data-offcanvas-id' => $gs_offcanvas,
						'data-trigger' => 'click',
					],
					'classes' => [
						'js-triggerBtn',
						'--prevent-main-mobile-nav',
					],
				];
				add_filter("reycore/module/offcanvas_panels/load_panel={$gs_offcanvas}", '__return_true');
			}
		}

		if( $hamburger_style = $this->_settings['hamburger_style'] ){

			$hamburger_style_map =  [
				'--25bars'      => '25bars',
				'--2bars'       => '2bars',
				'--hover2bars'  => 'hover2bars',
				'--hover2bars2' => 'hover2bars2',
			];

			if( isset($hamburger_style_map[$hamburger_style]) ){
				$args['load_hamburger']['classes'][] = '--hs-' . $hamburger_style_map[$hamburger_style];
			}
		}

		$args['nav_id'] = '-' . $this->get_id();

		if( isset($GLOBALS['rey__is_sticky']) && $GLOBALS['rey__is_sticky'] ){
			$args['nav_id'] .= '-sticky';
		}

		if( $this->_settings['offcanvas_tap_behavior'] === 'link' ){
			if( in_array($this->_settings['nav_indicator'], ['circle', 'dash']) ){
				// TODO find better patch
				// $this->_settings['nav_indicator'] = 'plus';
			}
		}

		$args['nav_indicator'] = $this->_settings['nav_indicator'];

		return $args;
	}

	function set_logo_options( $args ){

		if( ( $logo = $this->get_settings_for_display('mobile_panel_logo_img') ) && isset($logo['id']) && !empty($logo['id']) ) {
			$args['mobile_panel_logo'] = $logo['id'];
		}

		return $args;
	}

	function set_mega_menu_support( $args ){

		// Set mega menu support
		$args['element_type'] = $this->get_name();
		$args['walker'] = new \ReyCore\Libs\Nav_Walker;

		// set if menu should be cached
		if( ! isset($args['cache_menu']) ){
			$args['cache_menu'] = get_theme_mod('header_nav_cache', false);
		}

		return $args;
	}

	public function mega_panel_classes( $classes ){

		if( '' !== $this->_settings['mobile_panel_disable_mega_gs'] ){
			$classes['disabled'] = '--disable-mega-gs-mobile';
		}

		return $classes;
	}

	/**
	 * Render widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {

		$this->_settings = $this->get_settings_for_display();

		// Social Icons
		add_action('rey/mobile_nav/footer', [$this, 'before_nav_footer'], 0);
		add_action('rey/mobile_nav/footer', [$this, 'after_nav_footer'], 200);
		add_action('rey/mobile_nav/main_end', [$this, 'after_main_nav'], 20);

		add_filter('rey/header/nav_params', [$this, 'set_nav_options'], 10);
		add_filter('rey/header/logo_params', [$this, 'set_logo_options'], 10);
		add_filter('rey/logo/attributes', [$this, 'set_logo_attributes'], 10);
		add_filter('wp_nav_menu_args', [$this, 'set_mega_menu_support'], 9);
		add_filter('reycore/megamenu_panel/classes', [$this, 'mega_panel_classes']);

			if( isset($this->_settings['custom']) && $this->_settings['custom'] && $this->_settings['enable_mobile'] !== '' && $this->_settings['breakpoint'] ){
				$this->get_breakpoint_css();
			}

			if( $hamburger_style = $this->_settings['hamburger_style'] ){
				reycore_assets()->add_styles( 'reycore-hbg-styles' );
			}

			if( ! empty($this->_settings['hamburger_text']) ){
				reycore_assets()->add_styles( 'reycore-hbg-text' );
			}

			if( $overlay = $this->_settings['hover_overlay']){
				$this->add_render_attribute('_wrapper', 'data-hoverlay', $overlay);
				if( reycore__elementor_edit_mode() ){
					printf('<div class="__editmode" data-hoverlay="%s"></div>', $overlay);
				}
			}

			if( $this->_settings['icons_visibility'] !== 'yes') {
				add_filter('reycore/menu_nav/support_icons', '__return_false');
			}

			reycore__get_template_part('template-parts/header/navigation');

		remove_filter('reycore/megamenu_panel/classes', [$this, 'mega_panel_classes']);
		remove_filter('wp_nav_menu_args', [$this, 'set_mega_menu_support'], 9);
		remove_filter('rey/header/nav_params', [$this, 'set_nav_options'], 10);
		remove_filter('rey/header/logo_params', [$this, 'set_logo_options'], 10);
		remove_filter('rey/logo/attributes', [$this, 'set_logo_attributes'], 10);
		remove_filter('reycore/menu_nav/support_icons', '__return_false');
		remove_filter('reycore/menu_nav/support_icons', '__return_false');
		remove_action('rey/mobile_nav/footer', [$this, 'before_nav_footer'], 0);
		remove_action('rey/mobile_nav/footer', [$this, 'after_nav_footer'], 200);
		remove_action('rey/mobile_nav/main_end', [$this, 'after_main_nav'], 20);

		reycore_assets()->add_styles(['rey-header-menu', 'rey-header-menu-submenus', 'reycore-main-menu']);
		reycore_assets()->add_scripts(['rey-mobile-menu-trigger', 'rey-main-menu', 'reycore-elementor-elem-header-navigation']);

		if (isset($this->_settings['custom']) && $this->_settings['custom'] && $this->_settings['mobile_menu_source_type'] === 'offcanvas' && ($gs_offcanvas = $this->_settings['offcanvas_panel'])){
			do_action('reycore/elementor/header_nav/offcanvas');
		}
	}

	public function set_logo_attributes( $attributes ){

		$attributes['width'] = 60;
		$attributes['height'] = 40;

		return $attributes;
	}

	public function add_gs(){

		if( ! ($item_id = $this->_settings['mobile_footer_gs_item']) ){
			return;
		}

		reycore_assets()->defer_page_styles('elementor-post-' . $item_id, true);

		printf('<div class="__gs">%s</div>', \ReyCore\Elementor\GlobalSections::do_section($item_id, false, true));

	}

	public function before_nav_footer(){

		if( 'before' === $this->_settings['mobile_footer_gs']){
			$this->add_gs();
		}

		$this->render_social();
	}


	public function after_nav_footer(){

		if( 'after' === $this->_settings['mobile_footer_gs']){
			$this->add_gs();
		}

	}

	public function after_main_nav(){

		if( 'nav' === $this->_settings['mobile_footer_gs']){
			$this->add_gs();
		}

	}

	public function render_social(){

		if( $social_icon_list = $this->_settings['social_icon_list'] ):

			reycore_assets()->add_styles( $this->get_style_name('social') ); ?>

			<div class="rey-mobileNav--footerItem rey-mobileNav-social">

				<div class="rey-mobileNav-socialIcons">

					<?php if($social_text = $this->_settings['social_text']): ?>
						<div class="rey-mobileNav-socialText"><?php echo $social_text ?></div>
					<?php endif; ?>

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
						<a class="rey-mobileNav-socialIcons-link" rel="noreferrer" <?php echo $this->get_render_attribute_string( $link_key ); ?>>
							<?php echo reycore__get_svg_social_icon([ 'id'=> $item['social'] ]); ?>
						</a>
					<?php endforeach; ?>
				</div>

			</div>
			<!-- .rey-mobileNav-social -->
			<?php endif;
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
