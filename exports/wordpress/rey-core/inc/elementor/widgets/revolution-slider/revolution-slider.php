<?php
namespace ReyCore\Elementor\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( ! class_exists('\RevSliderSlider') ){
	return;
}

class RevolutionSlider extends \ReyCore\Elementor\WidgetsBase {

	public static function get_rey_config(){
		return [
			'id' => 'revolution-slider',
			'title' => __( 'Revolution Slider', 'rey-core' ),
			'icon' => 'rey-font-icon-general-r',
			'categories' => [ 'rey-theme-covers' ],
			'keywords' => ['revolution', 'slider'],
			'css' => [
				'!assets/style[rtl].css',
			],
			'js' => [
				'assets/script.js',
			],
		];
	}

	public function rey_get_script_depends() {
		return [ 'reycore-widget-revolution-slider-scripts' ];
	}

	public function on_export($element)
    {
        unset(
            $element['settings']['slider_id']
        );

        return $element;
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
			'important_note',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => __( 'To use this element you need to install Revolution Slider plugin.', 'rey-core' ),
				'content_classes' => 'elementor-descriptor',
				'condition' => [
					'slider_id' => '',
				],
			]
		);

		$this->add_control(
			'slider_id',
			[
				'label' => __( 'Slider ID', 'rey-core' ),
				'default' => '',
				'type' => 'rey-ajax-list',
				'query_args' => [
					'request' => 'get_rev_sliders',
					'export' => 'rev_id',
				],
			]
		);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Style', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'special_bg',
			[
				'label' => __( 'Special Background Effect', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => __( 'None', 'rey-core' ),
					'stripes'  => __( 'Stripes', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'stripes_loading_bg',
			[
				'label' => __( 'Stripes - "Loading" Background', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .revStripes-bg .revStripes-loading' => 'background-color: {{VALUE}}',
				],
				'condition' => [
					'special_bg' => 'stripes',
				],
			]
		);

		$this->add_control(
			'stripes_stripe_1',
			[
				'label' => __( 'Stripes - Stripe 1 Background', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .revStripes-bg .revStripes-stripe1' => 'background-color: {{VALUE}}',
				],
				'condition' => [
					'special_bg' => 'stripes',
				],
			]
		);

		$this->add_control(
			'stripes_stripe_2',
			[
				'label' => __( 'Stripes - Stripe 2 Background', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .revStripes-bg .revStripes-stripe2' => 'background-color: {{VALUE}}',
				],
				'condition' => [
					'special_bg' => 'stripes',
				],
			]
		);

		$this->add_control(
			'stripes_stripe_3',
			[
				'label' => __( 'Stripes - Stripe 3 Background', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .revStripes-bg .revStripes-stripe3' => 'background-color: {{VALUE}}',
				],
				'condition' => [
					'special_bg' => 'stripes',
				],
			]
		);

		$this->end_controls_section();

	}

	function render_bg($settings){
		if($settings['special_bg'] === 'stripes'): ?>
			<div class="revStripes-bg">
				<div class="revStripes-loading"></div>
				<div class="revStripes-loading"></div>
				<div class="revStripes-loading"></div>
				<div class="revStripes-stripe1"></div>
				<div class="revStripes-stripe2"></div>
				<div class="revStripes-stripe3"></div>
			</div>
		<?php endif;
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
		reycore_assets()->add_scripts( $this->rey_get_script_depends() );

		$this->add_render_attribute( 'wrapper', 'class', 'rey-revSlider' );

		$content = '';

		$settings = $this->get_settings_for_display();
		$slider_alias = $settings['slider_id'];

		global $SR_GLOBALS;

		// Rev Output
		$_slider = new \RevSliderSlider();

		if( $SR_GLOBALS['front_version'] === 6 ){
			$output = new \RevSliderOutput();
		}
		else {
			$output = false;
			if( class_exists('\RevSlider7Output') ){
				$output = new \RevSlider7Output();
			}
		}

		if( $output === false ){
			return;
		}

		if( empty($slider_alias) || ! $_slider->alias_exists( $slider_alias ) ){
			return;
		}

		$_slider->init_by_alias( $slider_alias );

		if( ! \Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode() ){

			// v1 (via set custom settings)
			// $params = $_slider->get_params();
			// $params['general']['slideshow']['waitForInit'] = apply_filters('reycore/elementor/revslider/waitforinit', (reycore__preloader_is_active() || $settings['special_bg'] === 'stripes') );
			// $output->set_custom_settings(wp_json_encode($params));

			// v2 (via set_param)
			// $_slider->set_param(['general','slideshow','waitForInit'], apply_filters('reycore/elementor/revslider/waitforinit', (reycore__preloader_is_active() || $settings['special_bg'] === 'stripes') ) );

			ob_start();
			$slider = $output->add_slider_to_stage($slider_alias);
			$content = ob_get_clean();
		}

		else {
			$height = $_slider->get_param(['size','height','d']);
			$content = '<div class="revSlider-editMode" style="height:'.$height.'px;"><img src="' . RS_PLUGIN_URL_CLEAN . 'admin/assets/images/rs6_logo_2x.png"><div>'. __('Please preview in frontend.', 'rey-core') .'</div></div>';
		}

		$slider_id = $output->get_slider_id();

		$this->add_render_attribute( 'wrapper', 'data-rev-settings', wp_json_encode([
			'slider_id' => $slider_id,
			'wait_for_init' => $_slider->get_param(['general','slideshow','waitForInit']),
		]) ); ?>

		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
			<?php $this->render_bg( $settings ); ?>
			<?php echo $content; ?>
		</div>
		<?php
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
