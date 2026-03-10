<?php
namespace ReyCore\Modules\ElementorAnimations;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	const ASSET_HANDLE = 'reycore-module-elementor-animations';

	public function __construct()
	{
		add_action( 'init', [$this, 'init']);
		add_action( 'elementor/editor/init', [ $this, 'animations_hide_native_anim_options' ] );
	}

	public function init() {

		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
		add_action( 'elementor/element/column/section_effects/before_section_end', [$this, 'column_section_effects'], 10);
		add_action( 'elementor/frontend/column/before_render', [$this, 'column_before_render'], 10);
		add_action( 'elementor/element/common/section_effects/before_section_end', [$this, 'common_section_effects'], 10, 2);
		add_action( 'elementor/frontend/before_render', [$this, 'common_before_render'], 10);
		add_action( 'elementor/element/section/section_effects/before_section_end', [$this, 'section_effects_settings'], 10);
		add_action( 'elementor/element/after_add_attributes', [$this, 'section_after_add_attributes'], 10);
		add_action( 'elementor/frontend/section/before_render', [$this, 'section_before_render'], 10);

	}

	/**
	 * Hides Elementor's native entrance animation options
	 * if Rey's animations are enabled.
	 * @since 1.0.0
	 */
	public function animations_hide_native_anim_options(){
		add_action('wp_head', function(){?>
			<style>
				body.elementor-device-tablet .elementor-control-rey_entrance_title,
				body.elementor-device-tablet .elementor-control-rey_animation_type,
				body.elementor-device-tablet .elementor-control-rey_animation_activation_trigger,
				body.elementor-device-tablet .elementor-control-rey_animation_subject,
				body.elementor-device-tablet .elementor-control-rey_animation_duration,
				body.elementor-device-tablet .elementor-control-rey_animation_delay,
				body.elementor-device-tablet .elementor-control-rey_reveal_title,
				body.elementor-device-tablet .elementor-control-rey_animation_type_reveal_direction,
				body.elementor-device-tablet .elementor-control-rey_animation_type__reveal_zoom,
				body.elementor-device-tablet .elementor-control-rey_animation_type__reveal_bg_color,
				body.elementor-device-tablet .elementor-control-rey_animation_overrides,
				body.elementor-device-mobile .elementor-control-rey_entrance_title,
				body.elementor-device-mobile .elementor-control-rey_animation_type,
				body.elementor-device-mobile .elementor-control-rey_animation_activation_trigger,
				body.elementor-device-mobile .elementor-control-rey_animation_subject,
				body.elementor-device-mobile .elementor-control-rey_animation_duration,
				body.elementor-device-mobile .elementor-control-rey_animation_delay,
				body.elementor-device-mobile .elementor-control-rey_reveal_title,
				body.elementor-device-mobile .elementor-control-rey_animation_type_reveal_direction,
				body.elementor-device-mobile .elementor-control-rey_animation_type__reveal_zoom,
				body.elementor-device-mobile .elementor-control-rey_animation_type__reveal_bg_color,
				body.elementor-device-mobile .elementor-control-rey_animation_overrides
				{
					display: none !important;
				}
			</style>
		<?php });
	}

	public function enqueue_scripts(){
		reycore_assets()->add_scripts(self::ASSET_HANDLE);
		reycore_assets()->add_styles(self::ASSET_HANDLE);
	}

	public function register_assets($assets){

		$assets->register_asset('styles', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/style'. $assets::rtl() . '.css',
				'deps'    => ['elementor-frontend'],
				'version'   => REY_CORE_VERSION,
				'priority' => 'high'
			]
		]);

		$assets->register_asset('scripts', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/script.js',
				'deps'    => ['elementor-frontend', 'reycore-elementor-frontend', 'animejs'],
				'version'   => REY_CORE_VERSION,
			]
		]);

	}

	public function column_section_effects( $element ){

		$animation = \Elementor\Plugin::instance()->controls_manager->get_control_from_stack( $element->get_unique_name(), 'animation' );
		if( $animation && ! is_wp_error($animation) ){
			$animation['condition']['rey_animation_type'] = '';
			$element->update_control( 'animation', $animation );
		}

		$element->add_control(
			'rey_entrance_title',
			[
			'label' => __( 'ENTRANCE SETTINGS', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'rey_animation_type!' => '',
					'animation' => ['', 'none'],
				],
			]
		);

		$element->add_control(
			'rey_animation_type',
			[
				'label' => __( 'Entrance Effect', 'rey-core' ) . \ReyCore\Elementor\Helper::rey_badge(),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => __( '- Select -', 'rey-core' ),
					'reveal'  => __( 'Reveal', 'rey-core' ),
					'fade-in'  => __( 'Fade In', 'rey-core' ),
					'fade-slide'  => __( 'Fade In From Bottom', 'rey-core' ),
					'slide-hidden'  => __( 'Slide Hidden From Bottom', 'rey-core' ),
				],
				'prefix_class' => 'rey-animate-el rey-anim--',
				'condition' => [
					'animation' => ['', 'none'],
				],
			]
		);

		$element->add_control(
			'rey_animation_activation_trigger',
			[
				'label' => __( 'Activation Trigger', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'viewport',
				'options' => [
					'viewport'  => __( 'In viewport', 'rey-core' ),
					'parent'  => __( 'Parent section has animated', 'rey-core' ),
				],
				'condition' => [
					'rey_animation_type!' => '',
					'animation' => ['', 'none'],
				],
				'render_type' => 'none',
				'prefix_class' => 'rey-anim--',
			]
		);

		$element->add_control(
			'rey_animation_subject',
			[
				'label' => __( 'Animation Subject', 'rey-core' ),
				'description' => esc_html__('Select the animation subject, either this column itself, or the widgets inside, sequentially.' , 'rey-core'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'column',
				'options' => [
					'column'  => __( 'Column itself', 'rey-core' ),
					'widgets'  => __( 'Widgets in this column', 'rey-core' ),
				],
				'condition' => [
					'rey_animation_type!' => '',
					'animation' => ['', 'none'],
				],
				'prefix_class' => 'rey-anim--subject-',
				'render_type' => 'none',
			]
		);


		$element->add_control(
			'rey_animation_duration',
			[
				'label' => __( 'Animation Duration', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					'slow' => __( 'Slow', 'rey-core' ),
					'' => __( 'Normal', 'rey-core' ),
					'fast' => __( 'Fast', 'rey-core' ),
				],
				'condition' => [
					'rey_animation_type!' => '',
					'animation' => ['', 'none'],
				],
				'render_type' => 'none',
			]
		);

		$element->add_control(
			'rey_animation_delay',
			[
				'label' => __( 'Animation Delay', 'rey-core' ) . ' (ms)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'step' => 100,
				'condition' => [
					'rey_animation_type!' => '',
					'animation' => ['', 'none'],
				],
				'render_type' => 'none',
			]
		);

		$element->add_control(
			'rey_reveal_title',
			[
			'label' => __( 'REVEAL SETTINGS', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'rey_animation_type' => ['reveal'],
					'animation' => ['', 'none'],
				],
			]
		);

		$element->add_control(
			'rey_animation_type_reveal_direction',
			[
				'label' => __( 'Reveal Direction', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'left',
				'options' => [
					'left'  => __( 'Left', 'rey-core' ),
					'top'  => __( 'Top', 'rey-core' ),
				],
				'condition' => [
					'rey_animation_type' => ['reveal'],
					'animation' => ['', 'none'],
				],
				'render_type' => 'none',
			]
		);

		$element->add_control(
			'rey_animation_type__reveal_zoom',
			[
				'label' => __( 'Reveal Zoom Animation', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Yes', 'rey-core' ),
				'label_off' => __( 'No', 'rey-core' ),
				'return_value' => 'yes',
				'default' => 'yes',
				'condition' => [
					'rey_animation_type' => ['reveal'],
					'animation' => ['', 'none'],
				],
				'render_type' => 'none',
			]
		);

		$element->add_control(
			'rey_animation_type__reveal_bg_color',
			[
				'label' => __( 'Reveal Background Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'condition' => [
					'rey_animation_type' => ['reveal'],
					'animation' => ['', 'none'],
				],
				'render_type' => 'none',
			]
		);

	}

	public function column_before_render($element) {

		if( ! $this->is_enabled() ){
			return;
		}

		$settings = $element->get_settings_for_display();

		if( $settings['rey_animation_type'] === '' ) {
			return;
		}

		$el_id = $element->get_id();

		$wrapper_attribute_string = ! \ReyCore\Elementor\Helper::is_optimized_dom() ? '_inner_wrapper' : '_widget_wrapper';

		$config = [
			'id'                 => $el_id,
			'element_type'       => 'column',
			'animation_type'     => esc_attr( $settings['rey_animation_type'] ),
			'reveal_direction'   => esc_attr( $settings['rey_animation_type_reveal_direction']),
			'reveal_zoom'        => esc_attr( $settings['rey_animation_type__reveal_zoom'] ),
			'reveal_bg'          => esc_attr( $settings['rey_animation_type__reveal_bg_color']),
			'activation_trigger' => esc_attr( $settings['rey_animation_activation_trigger']),
			'subject'            => esc_attr( $settings['rey_animation_subject'] ),
		];

		if( $settings['rey_animation_delay'] ) {
			$config['delay']= esc_attr( $settings['rey_animation_delay'] );
		}

		if( $settings['rey_animation_duration'] ) {
			$config['duration']= esc_attr( $settings['rey_animation_duration'] );
		}

		$element->add_render_attribute( '_wrapper', 'data-rey-anim-config', wp_json_encode($config) );

		$this->enqueue_scripts();

		if( $settings['rey_animation_subject'] == 'column' ) {
			$element->add_render_attribute( $wrapper_attribute_string, 'class', ['rey-animator-inner', 'rey-animator-inner--' . $config['id'] ] );
		}

	}


	/**
	 * Add custom settings into Elementor's "Motion Effects" Section
	 *
	 * @since 1.0.0
	 */
	function common_section_effects( $element, $args )
	{

		$animation = \Elementor\Plugin::instance()->controls_manager->get_control_from_stack( $element->get_unique_name(), '_animation' );
		if( $animation && ! is_wp_error($animation) ){
			$animation['condition']['rey_animation_overrides'] = '';
			$element->update_control( '_animation', $animation );
		}

		$element->add_control(
			'rey_animation_overrides',
			[
				'label' => __( 'Animation Overrides', 'rey-core' ) . \ReyCore\Elementor\Helper::rey_badge(),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'separator' => 'before',
				'condition' => [
					'_animation' => ['', 'none'],
				],
			]
		);

		$element->add_control(
			'rey_animation_duration',
			[
				'label' => __( 'Animation Duration', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'inherit',
				'options' => [
					'inherit' => __( 'Inherit', 'rey-core' ),
					'slow' => __( 'Slow', 'rey-core' ),
					'' => __( 'Normal', 'rey-core' ),
					'fast' => __( 'Fast', 'rey-core' ),
				],
				'render_type' => 'none',
				'condition' => [
					'rey_animation_overrides!' => '',
					'_animation' => ['', 'none'],
				],
			]
		);

		$element->add_control(
			'rey_animation_delay',
			[
				'label' => __( 'Animation Delay', 'rey-core' ) . ' (ms)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'step' => 100,
				'condition' => [
					'rey_animation_overrides!' => '',
					'_animation' => ['', 'none'],
				],
				'render_type' => 'none',
			]
		);

		$element->add_control(
			'rey_reveal_title',
			[
			'label' => __( 'REVEAL SETTINGS', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					// 'rey_animation_type' => ['reveal'],
					'rey_animation_overrides!' => '',
					'_animation' => ['', 'none'],
				],
			]
		);

		$element->add_control(
			'rey_animation_type_reveal_direction',
			[
				'label' => __( 'Reveal Direction', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => __( 'Inherit', 'rey-core' ),
					'left'  => __( 'Left', 'rey-core' ),
					'top'  => __( 'Top', 'rey-core' ),
				],
				'condition' => [
					// 'rey_animation_type' => ['reveal'],
					'rey_animation_overrides!' => '',
					'_animation' => ['', 'none'],
				],
				'render_type' => 'none',
			]
		);

		$element->add_control(
			'rey_animation_type__reveal_bg_color',
			[
				'label' => __( 'Reveal Background Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'condition' => [
					// 'rey_animation_type' => ['reveal'],
					'rey_animation_overrides!' => '',
					'_animation' => ['', 'none'],
				],
				'render_type' => 'none',
			]
		);

	}

	/**
	 * Render some attributes before rendering.
	 *
	 * @since 1.0.0
	 **/
	function common_before_render( $element )
	{

		if( ! $this->is_enabled() ){
			return;
		}

		if( $element->get_type() !== 'widget' ){
			return;
		}

		$el_id = $element->get_id();

		if( ! (
				($settings = $element->get_data('settings')) &&
				isset($settings['rey_animation_overrides']) &&
				$settings['rey_animation_overrides'] !== ''
			)
		){
			return;
		}

		$config = [];

		if( isset($settings['rey_animation_type_reveal_direction']) && !empty($settings['rey_animation_type_reveal_direction']) ) {
			$config['reveal_direction'] = esc_attr( $settings['rey_animation_type_reveal_direction']);
		}

		if( isset($settings['rey_animation_type__reveal_bg_color']) && !empty($settings['rey_animation_type__reveal_bg_color']) ) {
			$config['reveal_bg'] = esc_attr( $settings['rey_animation_type__reveal_bg_color']);
		}

		if( isset($settings['rey_animation_delay']) && !empty($settings['rey_animation_delay']) ) {
			$config['delay'] = esc_attr( $settings['rey_animation_delay'] );
		}

		if( isset($settings['rey_animation_duration']) && $settings['rey_animation_duration'] != 'inherit' ) {
			$config['duration'] = esc_attr( $settings['rey_animation_duration'] );
		}

		$element->add_render_attribute( '_wrapper', 'data-rey-anim-config', wp_json_encode($config) );

		$this->enqueue_scripts();

	}

		/**
	 * Add custom settings into Elementor's Section
	 *
	 * @since 1.0.0
	 */
	function section_effects_settings( $element )
	{

		$animation = \Elementor\Plugin::instance()->controls_manager->get_control_from_stack( $element->get_unique_name(), 'animation' );
		if( $animation && ! is_wp_error($animation) ){
			$animation['condition']['rey_animation_type'] = '';
			$element->update_control( 'animation', $animation );
		}

		$element->add_control(
			'rey_entrance_title',
			[
			'label' => __( 'ENTRANCE SETTINGS', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'rey_animation_type!' => '',
					'animation' => ['', 'none'],
				],
			]
		);

		$element->add_control(
			'rey_animation_type',
			[
				'label' => __( 'Entrace Effect', 'rey-core' ) . \ReyCore\Elementor\Helper::rey_badge(),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => __( '- Select -', 'rey-core' ),
					'reveal'  => __( 'Reveal', 'rey-core' ),
					'fade-in'  => __( 'Fade In', 'rey-core' ),
					'fade-slide'  => __( 'Fade In From Bottom', 'rey-core' ),
				],
				'render_type' => 'none',
				'condition' => [
					'animation' => ['', 'none'],
				],
			]
		);

		$element->add_control(
			'rey_animation_duration',
			[
				'label' => __( 'Animation Duration', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					'slow' => __( 'Slow', 'rey-core' ),
					'' => __( 'Normal', 'rey-core' ),
					'fast' => __( 'Fast', 'rey-core' ),
				],
				'condition' => [
					'rey_animation_type!' => '',
					'animation' => ['', 'none'],
				],
				'render_type' => 'none',
			]
		);

		$element->add_control(
			'rey_animation_delay',
			[
				'label' => __( 'Animation Delay', 'rey-core' ) . ' (ms)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'step' => 100,
				'condition' => [
					'rey_animation_type!' => '',
					'animation' => ['', 'none'],
				],
				'render_type' => 'none',
			]
		);

		$element->add_control(
			'rey_reveal_title',
			[
			'label' => __( 'REVEAL SETTINGS', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'rey_animation_type' => ['reveal'],
					'animation' => ['', 'none'],
				],
			]
		);

		$element->add_control(
			'rey_animation_type_reveal_direction',
			[
				'label' => __( 'Reveal Direction', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'left',
				'options' => [
					'left'  => __( 'Left', 'rey-core' ),
					'top'  => __( 'Top', 'rey-core' ),
				],
				'condition' => [
					'rey_animation_type' => ['reveal'],
					'animation' => ['', 'none'],
				],
				'render_type' => 'none',
			]
		);

		$element->add_control(
			'rey_animation_type__reveal_bg_color',
			[
				'label' => __( 'Reveal Background Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				// 'selectors' => [
				// 	'{{WRAPPER}}.rey-anim--reveal-bg .rey-anim--reveal-bgHolder' => 'background-color: {{VALUE}}',
				// ],
				'condition' => [
					'rey_animation_type' => ['reveal'],
					'animation' => ['', 'none'],
				],
				'render_type' => 'none',
			]
		);

	}

	public function section_after_add_attributes($element){

		if( 'section' !== $element->get_unique_name() ){
			return;
		}

		$settings = $element->get_settings_for_display();

		if( $settings['rey_animation_type'] === '' ){
			return;
		}

		/**
		* Hack to delay loading (lazy load) the video background if section is animated
		*/

		// Check if background enabled
		if( $settings['background_background'] === 'video' && $settings['background_video_link'] ){

			// add a temporary custom attribute
			$element->add_render_attribute( '_wrapper', 'data-rey-video-link', esc_attr($settings['background_video_link']) );

			// unset video link to remove it from data-settings attribute
			$frontend_settings = $element->get_render_attributes('_wrapper', 'data-settings');

			if( $frontend_settings && isset($frontend_settings[0]) && $frontend_settings_dec = json_decode($frontend_settings[0], true) ){
				unset($frontend_settings_dec['background_video_link']);
				$element->add_render_attribute( '_wrapper', 'data-settings', wp_json_encode( $frontend_settings_dec ), true );
			}

			reycore_assets()->add_scripts('reycore-elementor-elem-section-video');

		}

	}


	/**
	* Render some attributes before rendering
	*
	* @since 1.0.0
	**/
	function section_before_render( $element )
	{

		if( ! $this->is_enabled() ){
			return;
		}

		if( ! apply_filters( "elementor/frontend/section/should_render", true, $element ) ){
			return;
		}

		$settings = $element->get_settings();

		$classes = [];

		if( $settings['rey_animation_type'] === '' ){
			return;
		}

		$classes[] = 'rey-animate-el';
		$classes[] = 'rey-anim--' . esc_attr( $settings['rey_animation_type'] );
		$classes[] = 'rey-anim--viewport';

		$element->add_render_attribute( '_wrapper', 'class', $classes );

		$config = [
			'id'               => $element->get_id(),
			'element_type'     => 'section',
			'animation_type'   => esc_attr( $settings['rey_animation_type'] ),
			'reveal_direction' => esc_attr( $settings['rey_animation_type_reveal_direction']),
			'reveal_bg'        => esc_attr( $settings['rey_animation_type__reveal_bg_color']),
		];

		if( $settings['rey_animation_delay'] ) {
			$config['delay']= esc_attr( $settings['rey_animation_delay'] );
		}

		if( $settings['rey_animation_duration'] ) {
			$config['duration']= esc_attr( $settings['rey_animation_duration'] );
		}

		$element->add_render_attribute( '_wrapper', 'data-rey-anim-config', wp_json_encode($config) );

		$this->enqueue_scripts();

	}

	public function is_enabled() {

		if( reycore_assets()->mobile ){
			return;
		}

		return true;
	}

	public static function __config(){
		return [
			'id'          => basename(__DIR__),
			'title'       => esc_html_x('Elementor Animations', 'Module name', 'rey-core'),
			'description' => __('Adds extra entrace animations support for sections, columns and elements inside them.', 'rey-core'),
			'icon'        => '',
			'categories'  => ['elementor'],
			'keywords'    => [''],
			'help'        => reycore__support_url('kb/how-to-use-animated-entrance-effects/'),
			'video'       => true
		];
	}

	public function module_in_use(){

		if( ! $this->is_enabled() ){
			return;
		}

		$results = \ReyCore\Elementor\Helper::scan_content_in_site( 'content', 'rey_animation_type' );

		return ! empty($results);
	}
}
