<?php
namespace ReyCore\Modules\ElementorSectionModals;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	const ASSET_HANDLE = 'reycore-module-elementor-section-modals';

	const KEY = 'rey_modal';
	const VALUE = 'modal-section';

	private $supported;

	public function __construct()
	{
		add_action( 'init', [$this, 'init']);
	}

	public function init() {

		if( ! $this->is_enabled() ){
			return;
		}

		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
		add_action( 'elementor/element/section/section_layout/after_section_end', [$this, 'modal_settings'], 10);
		add_action( 'reycore/frontend/section/before_render', [$this, 'before_render'], 10, 2);
		add_action( 'reycore/frontend/section/after_render', [$this, 'after_render'], 10);
		add_action( 'elementor/element/container/section_layout_additional_options/after_section_end', [$this, 'modal_settings'], 10);
		add_action( 'reycore/frontend/container/before_render', [$this, 'before_render'], 10);
		add_action( 'reycore/frontend/container/after_render', [$this, 'after_render'], 10);

	}

	public function register_assets($assets){

		$direction_suffix = is_rtl() ? '-rtl' : '';

		$assets->register_asset('styles', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/style'. $direction_suffix . '.css',
				'deps'    => ['elementor-frontend'],
				'version'   => REY_CORE_VERSION,
				'priority' => 'low'
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
	function modal_settings( $element )
	{

		$element->start_controls_section(
			'section_modal',
			[
				'label' => __( 'Modal Settings', 'rey-core' ) . \ReyCore\Elementor\Helper::rey_badge(),
				'tab' => \Elementor\Controls_Manager::TAB_LAYOUT
			]
		);

		$element->add_control(
			self::KEY,
			[
				'label' => __( 'Enable Modal', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => self::VALUE,
				'default' => '',
				'prefix_class' => 'rey-',
				'hide_in_inner' => true,
			]
		);

		$element->add_responsive_control(
			'rey_modal_width',
			[
			'label' => __( 'Width', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'vw' ],
				'range' => [
					'px' => [
						'min' => 320,
						'max' => 2560,
						'step' => 1,
					],
					'vw' => [
						'min' => 10,
						'max' => 100,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => 'vw',
					'size' => 80,
				],
				'selectors' => [
					'{{WRAPPER}}' => 'max-width: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					self::KEY . '!' => '',
				],
			]
		);

		$element->add_responsive_control(
			'rey_modal_height',
			[
			'label' => __( 'Max-Height', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'vw' ],
				'range' => [
					'px' => [
						'min' => 150,
						'max' => 1000,
						'step' => 1,
					],
					'vw' => [
						'min' => 10,
						'max' => 100,
						'step' => 1,
					],
				],
				'default' => [],
				'selectors' => [
					'{{WRAPPER}}.elementor-element-edit-mode > .elementor-container' => 'max-height: {{SIZE}}{{UNIT}}; overflow-y: auto !important; overflow-x: hidden !important;',
					'{{WRAPPER}} > .elementor-container' => 'max-height: {{SIZE}}{{UNIT}}; overflow: auto;',
				],
				'condition' => [
					self::KEY . '!' => '',
				],
			]
		);

		$element->add_control(
			'rey_modal_close_pos',
			[
				'label' => __( 'Close Position', 'rey-core' ),
				'description' => __( 'Button can be previewed in normal frontend (not edit mode).', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => __( 'Default', 'rey-core' ),
					'inside'  => __( 'Inside', 'rey-core' ),
					'outside'  => __( 'Outside', 'rey-core' ),
				],
				'render_type' => 'none',
				'condition' => [
					self::KEY . '!' => '',
				],
				'separator' => 'before',
			]
		);

		$element->add_control(
			'rey_modal_close_color',
			[
				'label' => esc_html__( 'Close Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'condition' => [
					self::KEY . '!' => '',
				],
			]
		);

		$element->add_control(
			'rey_modal_splash',
			[
				'label' => __( 'Auto pop-up?', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => __( 'Disabled', 'rey-core' ),
					'scroll'  => __( 'On Page Scroll', 'rey-core' ),
					'time'  => __( 'On Page Load', 'rey-core' ),
					'exit'  => __( 'On Exit Intent', 'rey-core' ),
				],
				'render_type' => 'none',
				'separator' => 'before',
				'condition' => [
					self::KEY . '!' => '',
				],
			]
		);

		$element->add_control(
			'rey_modal_splash_scroll_distance',
			[
				'label' => esc_html__( 'Scroll Distance (%)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 50,
				'min' => 0,
				'max' => 100,
				'step' => 1,
				'condition' => [
					self::KEY . '!' => '',
					'rey_modal_splash' => 'scroll',
				],
			]
		);

		$element->add_control(
			'rey_modal_splash_timeframe',
			[
				'label' => esc_html__( 'Timeframe (Seconds)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 4,
				'min' => 0,
				'max' => 500,
				'step' => 1,
				'condition' => [
					self::KEY . '!' => '',
					'rey_modal_splash' => 'time',
				],
			]
		);

		$element->add_control(
			'rey_modal_splash_nag',
			[
				'label' => esc_html__( 'Prevent re-opening?', 'rey-core' ),
				'description' => esc_html__( 'When the visitor closes the splash popup, he won\'t be nagged again.', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'yes',
				'options' => [
					''  => esc_html__( 'No', 'rey-core' ),
					'yes'  => esc_html__( 'Yes - for 1 day', 'rey-core' ),
					'week'  => esc_html__( 'Yes - for 1 week', 'rey-core' ),
					'month'  => esc_html__( 'Yes - for 1 month', 'rey-core' ),
					'forever'  => esc_html__( 'Yes - Forever', 'rey-core' ),
				],
				'condition' => [
					self::KEY . '!' => '',
					'rey_modal_splash!' => '',
				],
			]
		);

		$element->add_control(
			'rey_modal_splash_nth_open',
			[
				'label' => esc_html__( 'Run on "nth" page load', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 20,
				'step' => 1,
				'condition' => [
					self::KEY . '!' => '',
				],
			]
		);

		$element->add_control(
			'rey_modal_id',
			[
				'label' => __( 'Modal ID', 'rey-core' ),
				'label_block' => true,
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => uniqid('#modal-'),
				'placeholder' => __( 'eg: #some-unique-id', 'rey-core' ),
				'description' => __( 'Copy the ID above and paste it into the link text-fields, where specified.', 'rey-core' ),
				'separator' => 'before',
				'condition' => [
					self::KEY . '!' => '',
				],
				'style_transfer' => false,
				'render_type' => 'none',
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

		// Modal
		if( ! (isset($settings[self::KEY]) && $settings[self::KEY] === self::VALUE) ){
			return;
		}

		$modal_attributes['data-rey-modal-id'] = $settings['rey_modal_id'];

		if( isset($settings['rey_modal_splash']) && $splash = $settings['rey_modal_splash'] ){
			$modal_attributes['data-rey-modal-splash'] = wp_json_encode( apply_filters('reycore/elementor/frontend/modal_splash/params', [
				'type' => esc_attr($splash),
				'time' => esc_attr($settings['rey_modal_splash_timeframe']),
				'distance' => esc_attr($settings['rey_modal_splash_scroll_distance']),
				'nag' => $settings['rey_modal_splash_nag'] === 'yes' ? 'day' : $settings['rey_modal_splash_nag'],
				'nth' => esc_attr($settings['rey_modal_splash_nth_open']),
				'elements_to_prevent' => '.mc4wp-form.mc4wp-form-success',
			]) );
		}

		$modal_data = [
			'wrapperClass' => '--section'
		];

		if( $modal_close_btn_color = $settings['rey_modal_close_color'] ){
			$modal_data['closeColor'] = esc_attr($modal_close_btn_color);
		}

		if( $modal_close_position = $settings['rey_modal_close_pos'] ){
			$modal_data['closePosition'] = esc_attr($modal_close_position);
		}

		$modal_data['closeParent'] = '.rey-modal-section';

		$modal_attributes['data-modal-extra'] = wp_json_encode($modal_data);

		$modal_attributes['class'] = ['reymodal__section', '--hidden'];

		$elementor_post_id = (isset($GLOBALS['global_section_ids']) && ($gs_ids = $GLOBALS['global_section_ids'])) ? end($gs_ids) : get_queried_object_id();

		$modal_attributes['data-elementor-class'] = sprintf('elementor elementor-%d', $elementor_post_id);

		// Wrap section & add overlay
		printf( '<div %s>', reycore__implode_html_attributes($modal_attributes) );

		reycore_assets()->add_styles(self::ASSET_HANDLE);
		reycore_assets()->add_scripts(self::ASSET_HANDLE);

		add_filter( 'reycore/modals/always_load', '__return_true');

		$this->supported = true;
	}

	/**
	* Render after rendering
	*
	* @since 1.0.0
	**/
	public function after_render( $element )
	{

		if( ! ( ! is_null($this->supported) && $this->supported )){
			return;
		}

		echo '</div>';

		// unset flag
		$this->supported = null;

	}

	public function is_enabled() {
		return true;
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Modals for Elementor Sections & Containers', 'Module name', 'rey-core'),
			'description' => esc_html_x('Create modal popups from Section or Container elements. You can link to them or automatically open.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['elementor'],
			'keywords'    => ['Elementor', 'Modals', 'Popup', 'Section', 'Container'],
			'help'        => reycore__support_url('kb/create-modal-sections/'),
			'video' => true
		];
	}

	public function module_in_use(){

		$results = \ReyCore\Elementor\Helper::scan_content_in_site( 'content', sprintf( '"%s":"%s"', self::KEY, self::VALUE ) );

		return ! empty($results);

	}
}
