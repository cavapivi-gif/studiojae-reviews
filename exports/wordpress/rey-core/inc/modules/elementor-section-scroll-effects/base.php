<?php
namespace ReyCore\Modules\ElementorSectionScrollEffects;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	const ASSET_HANDLE = 'reycore-module-elementor-section-elementor-section-scroll-effects';

	const KEY = 'rey_scroll_effects';
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

		add_action( 'elementor/element/section/section_effects/before_section_end', [$this, 'settings']);
		add_action( 'reycore/frontend/section/before_render', [$this, 'before_render'], 10, 2);

		add_action( 'elementor/element/container/section_effects/before_section_end', [$this, 'settings']);
		add_action( 'reycore/frontend/container/before_render', [$this, 'before_render'], 10, 2);

	}

	public function register_assets($assets){

		$direction_suffix = is_rtl() ? '-rtl' : '';

		$assets->register_asset('styles', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/style'. $direction_suffix . '.css',
				'deps'    => ['elementor-frontend'],
				'version'   => REY_CORE_VERSION,
				// 'priority' => 'low'
			]
		]);

		$assets->register_asset('scripts', [
			self::ASSET_HANDLE . '-sticky' => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/sticky.js',
				'deps'    => [ ],
				'version'   => REY_CORE_VERSION,
			]
		]);

		$assets->register_asset('scripts', [
			self::ASSET_HANDLE . '-colorize' => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/colorize.js',
				'deps'    => [ ],
				'version'   => REY_CORE_VERSION,
			]
		]);

		$assets->register_asset('scripts', [
			self::ASSET_HANDLE . '-clip' => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/clip.js',
				'deps'    => [ ],
				'version'   => REY_CORE_VERSION,
			]
		]);

	}


	/**
	 * Add custom settings into Elementor's Section
	 *
	 * @since 1.0.0
	 */
	public function settings( $element )
	{

		$element->add_control(
			self::KEY,
			[
				'label' => __( 'Scroll Effects', 'rey-core' ) . \ReyCore\Elementor\Helper::rey_badge(),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => __( 'None', 'rey-core' ),
					'clip-in'  => __( 'Clip In', 'rey-core' ),
					'clip-out'  => __( 'Clip Out', 'rey-core' ),
					'sticky'  => __( 'Sticky', 'rey-core' ),
					'colorize'  => __( 'Colorize Site', 'rey-core' ),
				],
				// 'hide_in_inner' => true,
				'prefix_class' => 'rey-sectionScroll rey-sectionScroll--',
				'separator' => 'before',
			]
		);

		$element->add_control(
			'rey_clip_mobile',
			[
				'label' => esc_html__( 'Add effect on mobile', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [
					self::KEY => ['clip-in', 'clip-out'],
				],
				'prefix_class' => '--clip-mobile-',
			]
		);

		$element->add_responsive_control(
			'rey_clip_offset',
			[
				'label' => __( 'Clip Depth', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 5,
				'max' => 300,
				'step' => 1,
				'condition' => [
					self::KEY => ['clip-in', 'clip-out'],
				],
				'selectors' => [
					'{{WRAPPER}}.rey-sectionScroll' => '--clip-offset: {{VALUE}}px; ',
				]
			]
		);

		$element->add_control(
			'rey_clip_threshold',
			[
				'label' => __( 'Scroll Threshold', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 0.5,
				'min' => 0,
				'max' => 1,
				'step' => 0.1,
				'condition' => [
					self::KEY => ['clip-in', 'clip-out', 'colorize'],
				],
				'selectors' => [
					'{{WRAPPER}}.rey-sectionScroll' => '--clip-threshold: {{VALUE}} ',
				],
				'frontend_available' => true
			]
		);

		$element->add_control(
			'rey_clip_transition',
			[
				'label' => __( 'Transition Duration', 'rey-core' ) . ' (ms)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 4000,
				'step' => 10,
				'placeholder' => 400,
				'condition' => [
					self::KEY => ['clip-in', 'clip-out'],
				],
				'selectors' => [
					'{{WRAPPER}}.rey-sectionScroll' => '--clip-transition-duration:{{VALUE}}ms;',
				]
			]
		);


		$element->add_control(
			'rey_sticky_offset',
			[
				'label' => __( 'Sticky Offset', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 1,
				'max' => 300,
				'step' => 1,
				'condition' => [
					self::KEY => 'sticky',
				],
				'selectors' => [
					'{{WRAPPER}}.rey-sectionScroll' => '--sticky-offset: {{VALUE}}px; ',
				]
			]
		);


		$element->add_control(
			'rey_sticky_breakpoints',
			[
				'label' => __( 'Sticky Breakpoints', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'multiple' => true,
				'label_block' => true,
				'default' => ['desktop'],
				'options' => [
					'desktop'  => __( 'Desktop', 'rey-core' ),
					'tablet'  => __( 'Tablet', 'rey-core' ),
					'mobile'  => __( 'Mobile', 'rey-core' ),
				],
				'condition' => [
					self::KEY => 'sticky',
				],
			]
		);

		$element->add_control(
			'rey_colorize_bg_color',
			[
				'label' => esc_html__( 'Background Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'condition' => [
					self::KEY => 'colorize',
				],
			]
		);

		$element->add_control(
			'rey_colorize_text_color',
			[
				'label' => esc_html__( 'Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'condition' => [
					self::KEY => 'colorize',
				],
			]
		);

		$element->add_control(
			'rey_colorize_link_color',
			[
				'label' => esc_html__( 'Links Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'condition' => [
					self::KEY => 'colorize',
				],
			]
		);

		$element->add_control(
			'rey_colorize_link_hover_color',
			[
				'label' => esc_html__( 'Links Hover Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'condition' => [
					self::KEY => 'colorize',
				],
			]
		);

		$element->add_control(
			'rey_scroll_effects_notice',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => __( 'Please preview in public mode.', 'rey-core' ),
				'content_classes' => 'rey-raw-html',
				'condition' => [
					self::KEY . '!' => '',
				],
			]
		);

	}

	/**
	* Render before rendering
	*
	* @since 1.0.0
	**/
	public function before_render( $element, $is_inner )
	{

		$settings = $element->get_settings();

		if( ! isset($settings[self::KEY]) ){
			return;
		}

		if( ! ($type = $settings[self::KEY]) ){
			return;
		}

		reycore_assets()->add_styles(self::ASSET_HANDLE);

		// Sticky
		if( 'sticky' === $type ){

			$sticky_config = [];

			if( ! empty($settings['rey_sticky_offset']) ){
				$sticky_config['offset'] = esc_attr($settings['rey_sticky_offset']);
			}

			if( $sticky_breakpoints = $settings['rey_sticky_breakpoints'] ){
				$sticky_config['breakpoints'] = array_map('esc_attr', $sticky_breakpoints);
			}

			if( ! $is_inner ){

				if( !empty($sticky_config) ){
					$element->add_render_attribute( '_wrapper', 'data-sticky-config', wp_json_encode($sticky_config) );
				}

				reycore_assets()->add_scripts(['reycore-sticky', self::ASSET_HANDLE . '-sticky']);
			}

			// inner containers
			else {
				if( ! empty($sticky_config['breakpoints']) ){
					$element->add_render_attribute( '_wrapper', 'data-sticky-bp', implode(',', $sticky_config['breakpoints']) );
				}
			}

		}

		// Colorize
		else if( 'colorize' === $type ){

			$colorize_config = [];

			if( $colorize__bg = $settings['rey_colorize_bg_color'] ){
				$colorize_config['bg'] = esc_attr($colorize__bg);
			}

			if( $colorize__text = $settings['rey_colorize_text_color'] ){
				$colorize_config['text'] = esc_attr($colorize__text);
			}

			if( $colorize__link = $settings['rey_colorize_link_color'] ){
				$colorize_config['link'] = esc_attr($colorize__link);
			}

			if( $colorize__link_hover = $settings['rey_colorize_link_hover_color'] ){
				$colorize_config['link_hover'] = esc_attr($colorize__link_hover);
			}

			if( '' !== $settings['rey_clip_threshold'] ){
				$colorize_config['threshold'] = $settings['rey_clip_threshold'];
			}

			if( !empty($colorize_config) ){
				$element->add_render_attribute( '_wrapper', 'data-colorize-config', wp_json_encode( apply_filters('reycore/elementor/colorize_config', $colorize_config, $element) ) );
			}
		}

		// scripts
		if( in_array($type, ['clip-in', 'clip-out', 'colorize'], true) ){

			if( $type === 'colorize' ){
				reycore_assets()->add_scripts(self::ASSET_HANDLE . '-colorize');
			}
			else {
				reycore_assets()->add_scripts(self::ASSET_HANDLE . '-clip');
			}

		}

	}

	public function is_enabled() {
		return true;
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Scroll Effects for Elementor Sections & Containers', 'Module name', 'rey-core'),
			'description' => esc_html_x('Add fancy effects on page scrolling to sections and containers.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['elementor'],
			'keywords'    => ['Elementor', 'Scroll', 'Section', 'Container'],
			'help'        => reycore__support_url('kb/rey-theme-custom-elementor-features/#scroll-effects-clip-in-clip-out'),
			'video' => true
		];
	}

	public function module_in_use(){

		$results = \ReyCore\Elementor\Helper::scan_content_in_site( 'content', sprintf( '"%s"', self::KEY ) );

		return ! empty($results);

	}
}
