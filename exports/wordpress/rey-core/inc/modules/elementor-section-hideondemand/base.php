<?php
namespace ReyCore\Modules\ElementorSectionHideOnDemand;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	const ASSET_HANDLE = 'reycore-module-elementor-section-hideondemand';

	const KEY = 'rey_hod__enable';
	const VALUE = '';

	public function __construct()
	{
		add_action( 'init', [$this, 'init']);
	}

	public function init() {

		if( ! $this->is_enabled() ){
			return;
		}

		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);

		add_action( 'elementor/element/section/section_custom_css_pro/after_section_end', [$this, 'settings'], 10);
		add_action( 'reycore/frontend/section/before_render', [$this, 'before_render'], 10, 2);

		add_action( 'elementor/element/container/section_custom_css_pro/after_section_end', [$this, 'settings'], 10);
		add_action( 'reycore/frontend/container/before_render', [$this, 'before_render'], 10);

	}

	public function register_assets($assets){

		$direction_suffix = is_rtl() ? '-rtl' : '';

		$assets->register_asset('styles', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/style'. $direction_suffix . '.css',
				'deps'    => ['elementor-frontend'],
				'version'   => REY_CORE_VERSION,
				'priority' => 'high'
			]
		]);

		$assets->register_asset('scripts', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/script.js',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			]
		]);

	}


	/**
	 * Add custom settings into Elementor's Section
	 *
	 * @since 1.0.0
	 */
	function settings( $element )
	{

		$element->start_controls_section(
			'section_rey_hide_on_demand',
			[
				'label' => esc_html__('Hide on demand', 'rey-core') . \ReyCore\Elementor\Helper::rey_badge(),
				'tab' => \Elementor\Controls_Manager::TAB_ADVANCED,
				'hide_in_inner' => true,
			]
		);

		$element->add_control(
			'rey_hod__notice',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => __('This feature should mostly be used for promotion banners or content that is dismissable.', 'rey-core'),
				'content_classes' => 'elementor-descriptor',
			]
		);

		$element->add_control(
			'rey_hod__enable',
			[
				'label' => esc_html__( 'Enable hiding on demand', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'prefix_class' => '--rey-hod-'
			]
		);

		$element->add_control(
			'rey_hod__hide_type',
			[
				'label' => esc_html__( 'Hiding Type', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'icon',
				'options' => [
					'icon'  => esc_html__( 'Close Icon', 'rey-core' ),
					'custom'  => esc_html__( 'Custom link', 'rey-core' ),
				],
				'condition' => [
					'rey_hod__enable!' => '',
				],
				'frontend_available' => true
			]
		);

		$element->add_control(
			'rey_hod__notice_custom',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => __('Please use the URL <strong>#close-section</strong> inside.', 'rey-core'),
				'content_classes' => 'elementor-descriptor',
				'condition' => [
					'rey_hod__enable!' => '',
					'rey_hod__hide_type' => 'custom',
				],
			]
		);

		$element->add_control(
			'rey_hod__close_color',
			[
				'label' => esc_html__( 'Close Icon Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'condition' => [
					'rey_hod__enable!' => '',
					'rey_hod__hide_type' => 'icon',
				],
				'selectors' => [
					'{{WRAPPER}} .rey-hod-close' => 'color: {{VALUE}}',
				]
			]
		);

		$element->add_control(
			'rey_hod__close_position',
			[
				'label' => esc_html__( 'Close Icon Position', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'right',
				'options' => [
					'left'  => esc_html__( 'Left', 'rey-core' ),
					'right'  => esc_html__( 'Right', 'rey-core' ),
				],
				'condition' => [
					'rey_hod__enable!' => '',
					'rey_hod__hide_type' => 'icon',
				],
				'frontend_available' => true,
			]
		);


		$element->add_control(
			'rey_hod__close_size',
			[
				'label' => esc_html__( 'Close Icon Size', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 1,
				'max' => 1000,
				'step' => 1,
				'condition' => [
					'rey_hod__enable!' => '',
					'rey_hod__hide_type' => 'icon',
				],
				'selectors' => [
					'{{WRAPPER}} .rey-hod-close' => 'font-size: {{VALUE}}px',
				]
			]
		);

		$element->add_control(
			'rey_hod__close_thickness',
			[
				'label' => esc_html__( 'Close Icon Thickness', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 12,
				'min' => 1,
				'max' => 1000,
				'step' => 1,
				'condition' => [
					'rey_hod__enable!' => '',
					'rey_hod__hide_type' => 'icon',
				],
				'selectors' => [
					'{{WRAPPER}} .rey-hod-close svg' => '--stroke-width: {{VALUE}}px',
				]
			]
		);

		$element->add_control(
			'rey_hod__close_distance',
			[
				'label' => esc_html__( 'Close Icon - Side distance', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 1,
				'max' => 1000,
				'step' => 1,
				'condition' => [
					'rey_hod__enable!' => '',
					'rey_hod__hide_type' => 'icon',
				],
				'selectors' => [
					'{{WRAPPER}} .rey-hod-close' => '--hod-distance: {{VALUE}}px',
				]
			]
		);

		$element->add_control(
			'rey_hod__store_state',
			[
				'label' => esc_html__( 'Hidden state duration', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'day',
				'options' => [
					'none'  => esc_html__( 'None (show on refresh)', 'rey-core' ),
					'day'  => esc_html__( '1 Day', 'rey-core' ),
					'week'  => esc_html__( '1 Week', 'rey-core' ),
					'month'  => esc_html__( '1 Month', 'rey-core' ),
				],
				'condition' => [
					'rey_hod__enable!' => '',
				],
				'frontend_available' => true,
			]
		);

		$element->add_control(
			'rey_hod__timeout',
			[
				'label'       => esc_html__( 'Delay appearance', 'rey-core' ) . ' (ms)',
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'default'     => '',
				'step'        => 50,
				'placeholder' => 1000,
				'condition'   => [
					'rey_hod__enable!' => '',
				],
				'frontend_available' => true,
			]
		);

		$element->end_controls_section();
	}

	/**
	* Render before rendering
	*
	* @since 1.0.0
	**/
	public function before_render( $element, $element_type = '' )
	{

		// inner section
		if( true === $element_type ){
			return;
		}

		$settings = $element->get_settings();

 		if( ! isset($settings[self::KEY]) ){
			return;
		}

		if( $settings[self::KEY] === '' ){
			return;
		}

		$element->add_render_attribute( '_wrapper', [
			'style' => ! (reycore__elementor_edit_mode()) ? 'display:none' : '',
		] );

		reycore_assets()->add_styles(self::ASSET_HANDLE);
		reycore_assets()->add_scripts(self::ASSET_HANDLE);

		\ReyCore\Plugin::instance()->js_icons->include_icons( 'close' );

	}

	public function is_enabled() {
		return true;
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Hide on Demand Elementor Sections & Containers', 'Module name', 'rey-core'),
			'description' => esc_html_x('Add a close button to a section/container to hide it.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['elementor'],
			'keywords'    => ['Elementor', 'Hide', 'Section', 'Container'],
			'help'        => reycore__support_url('kb/rey-theme-custom-elementor-features/#hide-on-demand'),
			'video' => true
		];
	}

	public function module_in_use(){

		$results = \ReyCore\Elementor\Helper::scan_content_in_site( 'content', sprintf( '"%s"', self::KEY ) );

		return ! empty($results);

	}
}
