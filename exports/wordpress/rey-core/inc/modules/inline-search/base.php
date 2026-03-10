<?php
namespace ReyCore\Modules\InlineSearch;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	public static $is_enabled = false;

	const ASSET_HANDLE = 'reycore-inlinesearch';

	public function __construct()
	{

		parent::__construct();

		add_action( 'init', [$this, 'init']);
		add_action( 'wp', [$this, 'wp']);
		add_action( 'reycore/customizer/section=header-search', [ $this, 'customizer_options' ] );
		add_action( 'elementor/element/reycore-header-search/section_settings/before_section_end', [ $this, 'add_elementor_settings' ] );
		add_action( 'elementor/element/reycore-header-search/section_styles/after_section_end', [ $this, 'add_elementor_style_options' ] );
		add_action( 'reycore/elementor/header-search/template', [$this, 'elementor_inline_search_form'], 10, 2);
	}

	function is_enabled(){
		return function_exists('reycore_wc__get_header_search_args') && 'inline' === reycore_wc__get_header_search_args('search_style');
	}

	function init(){

		self::$is_enabled = $this->is_enabled();

		add_action( 'wp', [ $this, 'remove_default_search' ]);
	}

	public function wp(){
		add_action( 'rey/header/row', [$this, 'inline_search_form'], 30);
		add_filter( 'rey/main_script_params', [ $this, 'script_params'], 11 );
		add_filter( 'reycore/elementor/header-search/assets', [$this, 'add_element_assets'], 20, 3);
		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts_in_editor' ] );
	}


	/**
	 * Remove default search button
	 *
	 * @since 1.3.0
	 */
	function remove_default_search() {

		if( !self::$is_enabled ){
			return;
		}

		remove_action('rey/header/row', 'rey__header__search', 30);
	}

	/**
	 * Add markup
	 *
	 * @since 1.3.0
	 **/
	function inline_search_form(){

		if( ! get_theme_mod('header_enable_search', false) ){
			return;
		}

		if( !self::$is_enabled ){
			return;
		}

		$this->load_scripts();

		reycore__get_template_part('inc/modules/inline-search/tpl-search-form-inline');
	}

	/**
	 * Add markup in Elementor
	 *
	 * @since 1.3.0
	 **/
	function elementor_inline_search_form($settings, $search_style){

		// Inline Form
		if( $search_style === 'inline' ){

			$this->load_scripts();

			reycore__get_template_part('inc/modules/inline-search/tpl-search-form-inline');
		}
	}

	/**
	 * Filter main script's params
	 *
	 * @since 1.0.0
	 **/
	public function script_params($params)
	{
		$params['ajax_search_only_title'] = false;
		return $params;
	}

	function add_element_assets( $styles, $search_style, $type ){

		if( 'style' === $type && 'inline' === $search_style ){
			$styles[] = self::ASSET_HANDLE;
			$styles[] = self::ASSET_HANDLE . '-form';
		}

		return $styles;
	}

	public function register_assets($assets){

		$assets->register_asset('styles', [
			self::ASSET_HANDLE => [
				'src'      => self::get_path( basename( __DIR__ ) ) . '/style.css',
				'deps'     => [],
				'version'  => REY_CORE_VERSION,
				'priority' => 'low',
			]
		]);

		$assets->register_asset('styles', [
			self::ASSET_HANDLE . '-form' => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/form.css',
				'deps'    => [],
				'version' => REY_CORE_VERSION,
			]
		]);

		$script_deps = [];

		if( class_exists('\WooCommerce') ){
			$script_deps[] = 'reycore-woocommerce';
		}

		$assets->register_asset('scripts', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/script.js',
				'deps'    => $script_deps,
				'version'   => REY_CORE_VERSION,
			]
		]);

	}

	function enqueue_scripts_in_editor(){
		if( reycore__elementor_edit_mode() ){
			self::load_scripts();
		}
	}

	public static function load_scripts(){
		reycore_assets()->add_styles(['rey-overlay', self::ASSET_HANDLE, self::ASSET_HANDLE . '-form', 'rey-header-icon', 'reycore-header-search-top', 'reycore-header-search']);
		reycore_assets()->add_scripts([self::ASSET_HANDLE, 'reycore-header-search']);
	}

	public function customizer_options( $section ){

		// header_search_style - add choice
		$header_search_style = $section->get_control('header_search_style');
		$header_search_style['choices']['inline'] = esc_html__( 'Inline Form', 'rey-core' );
		$section->update_control( $header_search_style );

		// group options
		$group_options = $section->get_control('group_start__header_search_styles_options_title');
		$group_options['active_callback'][0]['value'][] = 'inline';
		$section->update_control( $group_options );

		// color options
		foreach (['search_bg_color', 'search_text_color'] as $opt_key) {

			$opt = $section->get_control($opt_key);
			$opt['active_callback'] = [
				[
					'setting'  => 'header_search_style',
					'operator' => '!=',
					'value'    => 'inline',
				],
			];
			$section->update_control( $opt );

		}
	}

	public function add_elementor_settings( $element ){

		$search_styles = \Elementor\Plugin::instance()->controls_manager->get_control_from_stack( $element->get_unique_name(), 'search_style' );
		if( ! empty($search_styles['options']) && ! is_wp_error($search_styles) ){
			$search_styles['options'] = array_merge( (array) $search_styles['options'], [ 'inline' => esc_html__( 'Inline Form', 'rey-core' ) ]);
			$element->update_control( 'search_style', $search_styles );
		}

	}

	function add_elementor_style_options( $element ){

		$element->start_controls_section(
			'section_styles_inline',
			[
				'label' => __( 'Inline Form Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'conditions' => [
					'relation' => 'or',
					'terms' => [
						[
							'relation' => 'and',
							'terms' => [
								['name' => 'custom', 'operator' => '!==', 'value' => ''],
								['name' => 'search_style', 'operator' => '===', 'value' => 'inline'],
							],
						],
						[
							'relation' => 'and',
							'terms' => [
								['name' => 'global__search_style', 'operator' => '===', 'value' => 'inline'], // 'inline'
								['name' => 'custom', 'operator' => '===', 'value' => ''], // ''
								// ['name' => 'search_style', 'operator' => 'in', 'value' => ['', 'inline']], // side
							],
						],
					]
				],
			]
		);

		$element->add_control(
			'inline_prevent_overlay',
			[
				'label' => esc_html__( 'Disable Overlay (& Results)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => '--prevent-results-overlay',
				'default' => '',
				'prefix_class' => '',
				'condition' => [
					'inline_layout' => 'ov',
				],
			]
		);

		$element->add_control(
			'inline_layout',
			[
				'label' => esc_html__( 'Layout type', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'ov',
				'options' => [
					'ov'  => esc_html__( 'Animated Overlay', 'rey-core' ),
					'drop'  => esc_html__( 'Dropdown', 'rey-core' ),
				],
				'prefix_class' => '--inline-layout-'
			]
		);

		$element->start_controls_tabs( 'inline_colors' );

			$element->start_controls_tab(
				'inline_colors_normal',
				[
					'label' => __( 'Normal', 'rey-core' ),
				]
			);

				$element->add_control(
					'inline_text_color',
					[
						'label' => __( 'Text Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'.rey-headerSearch--inline form, .rey-headerSearch--inline input[type="search"], .rey-headerSearch--inline .rey-searchForm-list' => 'color: {{VALUE}}',
						],
					]
				);

				$element->add_control(
					'inline_bg_color',
					[
						'label' => __( 'Background Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .rey-headerSearch--inline form' => 'background-color: {{VALUE}}',
							'{{WRAPPER}} .rey-headerSearch--inline form:before' => 'display: none',
						],
					]
				);

			$element->end_controls_tab();

			$element->start_controls_tab(
				'inline_colors_focus',
				[
					'label' => __( 'Focused', 'rey-core' ),
				]
			);

				$element->add_control(
					'inline_text_color_active',
					[
						'label' => __( 'Text Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .rey-headerSearch--inline.--active input[type="search"]' => 'color: {{VALUE}}',
						],
					]
				);

				$element->add_control(
					'inline_bg_color_active',
					[
						'label' => __( 'Background Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .rey-headerSearch--inline.--active form:before' => 'display: block; background-color: {{VALUE}};',
						],
					]
				);

			$element->end_controls_tab();

		$element->end_controls_tabs();


		$element->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'inline_border',
				'selector' => '{{WRAPPER}} .rey-headerSearch--inline form',
				'separator' => 'before',
			]
		);

		$element->add_responsive_control(
			'inline_border_radius',
			[
				'label' => __( 'Border Radius', 'elementor' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}}' => '--hsi-br-tl: {{TOP}}{{UNIT}}; --hsi-br-tr: {{RIGHT}}{{UNIT}}; --hsi-br-br: {{BOTTOM}}{{UNIT}}; --hsi-br-bl: {{LEFT}}{{UNIT}};',
					// '{{WRAPPER}} .rey-headerSearch--inline input[type="search"], {{WRAPPER}} .rey-headerSearch--inline .search-btn, {{WRAPPER}} .rey-headerSearch--inline form' => '--hsi-br-tl: {{TOP}}{{UNIT}}; --hsi-br-tr: {{RIGHT}}{{UNIT}}; --hsi-br-br: {{BOTTOM}}{{UNIT}}; --hsi-br-bl: {{LEFT}}{{UNIT}};',
				],
			]
		);


		$element->add_control(
			'expand_click',
			[
				'label' => esc_html__( 'Expand on click', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'prefix_class' => '--expand-',
				'separator' => 'before'
			]
		);

		$element->add_control(
			'inline_height',
			[
				'label' => esc_html__( 'Bar height', 'rey-core' ) . ' (px)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 20,
				'max' => 1000,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .rey-headerSearch--inline' => '--height:{{VALUE}}px',
				],
			]
		);

		$element->add_control(
			'inline_custom_width',
			[
				'label' => esc_html__( 'Desktop Custom Width', 'rey-core' ) . ' (px)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 100,
				'max' => 1000,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .rey-headerSearch--inline' => '--width:{{VALUE}}px',
				],
			]
		);

		$element->add_control(
			'inline_mobile_layout',
			[
				'label' => esc_html__( 'Mobile layout type', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'separator' => 'before',
				'default' => 'icon',
				'options' => [
					'icon'  => esc_html__( 'Icon', 'rey-core' ),
					'full'  => esc_html__( 'Bar', 'rey-core' ),
				],
				'prefix_class' => '--inline-mobile-'
			]
		);


		$element->end_controls_section();

		$element->start_controls_section(
			'section_styles_inline_icon',
			[
				'label' => __( 'Icon Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$element->add_responsive_control(
			'inline_icon_size',
			[
				'label' => esc_html__( 'Icon Size', 'rey-core' ) . ' (px)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 1,
				'max' => 100,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .rey-headerSearch--inline .icon-search' => 'font-size: {{VALUE}}px',
				],
			]
		);

		$element->add_control(
			'use_button',
			[
				'label' => esc_html__( 'Icon as Button?', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'no',
				'options' => [
					'yes'  => esc_html__( 'Yes', 'rey-core' ),
					'no'  => esc_html__( 'No', 'rey-core' ),
				],
				'separator' => 'before',
				'prefix_class' => '--has-button-'
			]
		);

		$element->start_controls_tabs( 'inline_icon_colors' );

			$element->start_controls_tab(
				'inline_icon_colors_normal',
				[
					'label' => __( 'Normal', 'rey-core' ),
				]
			);

				$element->add_control(
					'inline_button_color',
					[
						'label' => esc_html__( 'Button Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}}.--has-button-yes .rey-headerSearch--inline .search-btn' => 'background-color: {{VALUE}}',
						],
						'condition' => [
							'use_button' => 'yes',
						],
					]
				);

				$element->add_control(
					'inline_icon_color',
					[
						'label' => esc_html__( 'Icon Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .rey-headerSearch--inline .icon-search' => 'color: {{VALUE}}',
						],
					]
				);

				$element->add_control(
					'inline_icon_color_mobile',
					[
						'label' => esc_html__( 'Icon Color [Mobile]', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .rey-headerSearch--inline .rey-headerSearch-toggle .icon-search' => 'color: {{VALUE}}',
						],
					]
				);

			$element->end_controls_tab();

			$element->start_controls_tab(
				'inline_icon_colors_focus',
				[
					'label' => __( 'Focused', 'rey-core' ),
				]
			);

				$element->add_control(
					'inline_button_color_hover',
					[
						'label' => esc_html__( 'Button Hover Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}}.--has-button-yes .rey-headerSearch--inline .search-btn:hover' => 'background-color: {{VALUE}}',
						],
						'condition' => [
							'use_button' => 'yes'
						],
					]
				);

				$element->add_control(
					'inline_icon_color__active',
					[
						'label' => esc_html__( 'Icon Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .rey-headerSearch--inline.--active .icon-search' => 'color: {{VALUE}};',
						],
					]
				);


			$element->end_controls_tab();

		$element->end_controls_tabs();

		$element->end_controls_section();
	}


	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Inline Search Form', 'Module name', 'rey-core'),
			'description' => esc_html_x('Adds a new style regular search bar layout to the Search form, with support for popup results.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['frontend'],
			'keywords'    => [''],
			'help'        => reycore__support_url('kb/customizer-header-settings/#search'),
			'video' => true,
		];
	}

	public function module_in_use(){
		return $this->is_enabled();
	}

}
