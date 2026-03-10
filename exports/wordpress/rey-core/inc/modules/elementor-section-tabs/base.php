<?php
namespace ReyCore\Modules\ElementorSectionTabs;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	const ASSET_HANDLE = 'reycore-module-elementor-section-tabs';

	const KEY = 'rey_tabs';
	const VALUE = 'tabs-section';

	public function __construct()
	{
		add_action( 'init', [$this, 'init']);
	}

	public function init() {

		if( ! $this->is_enabled() ){
			return;
		}

		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
		add_action( 'elementor/element/section/section_layout/after_section_end', [$this, 'settings'], 10);
		add_action( 'elementor/element/container/section_layout_additional_options/after_section_end', [$this, 'settings'], 10);
		add_action( 'reycore/frontend/section/before_render', [$this, 'before_render'], 10);
		add_action( 'reycore/frontend/container/before_render', [$this, 'before_render'], 10);

	}

	public function register_assets($assets){

		$assets->register_asset('styles', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/style.css',
				'deps'    => ['elementor-frontend'],
				'version'   => REY_CORE_VERSION,
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
			'section_tabs',
			[
				'label' => __( 'Tabs Settings', 'rey-core' ) . \ReyCore\Elementor\Helper::rey_badge(),
				'tab' => \Elementor\Controls_Manager::TAB_LAYOUT
			]
		);

		$element->add_control(
			self::KEY,
			[
				'label' => __( 'Enable Tabs', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => self::VALUE,
				'default' => '',
				'prefix_class' => 'rey-',
				// 'hide_in_inner' => true,
			]
		);


		$element->add_control(
			'rey_tabs_id',
			[
				'label' => __( 'Tabs ID', 'rey-core' ),
				'label_block' => true,
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => uniqid('tabs-'),
				'placeholder' => __( 'eg: some-unique-id', 'rey-core' ),
				'description' => __( 'Copy the ID above and paste it into the "Toggle Boxes" Widget where specified.', 'rey-core' ),
				'condition' => [
					self::KEY . '!' => '',
				],
				'style_transfer' => false,
				'render_type' => 'none',
			]
		);

		$element->add_control(
			'rey_tabs_effect',
			[
				'label' => esc_html__( 'Tabs Effect', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'default',
				'options' => [
					'default'  => esc_html__( 'Fade', 'rey-core' ),
					'slide'  => esc_html__( 'Fade & Slide', 'rey-core' ),
				],
				'condition' => [
					self::KEY . '!' => '',
				],
			]
		);

		$element->add_control(
			'rey_tabs_transition_speed',
			[
				'label' => esc_html__( 'Transition Speed', 'rey-core' ) . ' (ms)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 1000,
				'step' => 1,
				'condition' => [
					self::KEY . '!' => '',
				],
				'selectors' => [
					// v2
					'{{WRAPPER}}.rey-tabs-section > .elementor-container > .elementor-row > .elementor-column' => 'transition-duration: {{VALUE}}ms',
					// v3
					'{{WRAPPER}}.rey-tabs-section > .elementor-container > .elementor-column' => 'transition-duration: {{VALUE}}ms',
					// Container
					'{{WRAPPER}}.rey-tabs-section > .elementor-element' => 'transition-duration: {{VALUE}}ms',
				],
			]
		);

		$element->end_controls_section();

	}

	/**
	* Render before rendering
	*
	* @since 1.0.0
	**/
	public function before_render( $element )
	{

		$settings = $element->get_settings();

		// Modal
		if( ! (isset($settings[self::KEY]) && $settings[self::KEY] === self::VALUE) ){
			return;
		}

		$element->add_render_attribute( '_wrapper', 'data-tabs-id', esc_attr($settings['rey_tabs_id']) );

		$classes[] = '--tabs-effect-' . esc_attr($settings['rey_tabs_effect']);

		if( !empty($classes) ){
			$element->add_render_attribute( '_wrapper', 'class', $classes );
		}

		reycore_assets()->add_styles(self::ASSET_HANDLE);
		reycore_assets()->add_scripts(self::ASSET_HANDLE);

	}

	public function is_enabled() {
		return true;
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Tabs for Elementor Sections & Containers', 'Module name', 'rey-core'),
			'description' => esc_html_x('Create tabs from Section or Container elements. You can use the "Toggle Boxes" element to control them.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['elementor'],
			'keywords'    => ['Elementor', 'Tabs', 'Section', 'Container'],
			'help'        => reycore__support_url('kb/create-tabs-sections/'),
			'video' => true
		];
	}

	public function module_in_use(){

		$results = \ReyCore\Elementor\Helper::scan_content_in_site( 'content', sprintf( '"%s":"%s"', self::KEY, self::VALUE ) );

		return ! empty($results);

	}
}
