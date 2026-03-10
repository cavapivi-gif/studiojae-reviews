<?php
namespace ReyCore\Modules\ElementorSectionSlideshow;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	const ASSET_HANDLE = 'reycore-module-elementor-section-slideshow';

	const KEY = 'rey_slideshow';

	public $settings = [];

	public function __construct()
	{
		add_action( 'init', [$this, 'init']);
	}

	public function init() {

		if( ! $this->is_enabled() ){
			return;
		}

		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);

		add_action( 'elementor/element/section/section_background/before_section_end', [$this, 'append_bg_choice']);
		add_action( 'elementor/element/container/section_background/before_section_end', [$this, 'append_bg_choice']);

		add_action( 'elementor/element/section/section_background/after_section_end', [$this, 'settings']);
		add_action( 'elementor/element/container/section_background/after_section_end', [$this, 'settings']);

		add_action( 'reycore/frontend/section/before_render', [$this, 'before_render']);
		add_action( 'reycore/frontend/container/before_render', [$this, 'before_render']);

		add_action( 'reycore/frontend/section/after_render', [$this, 'after_render']);
		add_action( 'reycore/frontend/container/after_render', [$this, 'after_render']);

		add_filter( 'elementor/section/print_template', [$this, 'print_template'], 10, 2 );
		add_filter( 'elementor/container/print_template', [$this, 'print_template'], 10, 2 );

		add_action( 'elementor/widget/reycore-product-grid/skins_init', [$this, 'product_grid_carousel_section_skin'] );

	}

	public function product_grid_carousel_section_skin( $element )
	{
		$element->add_skin( new \ReyCore\Modules\ElementorSectionSlideshow\PgSkinCarouselSection( $element ) );
	}

	public function register_assets($assets){

		$direction_suffix = is_rtl() ? '-rtl' : '';

		$assets->register_asset('styles', [
			self::ASSET_HANDLE => [
				'src'      => self::get_path( basename( __DIR__ ) ) . '/style.css',
				'deps'     => ['elementor-frontend', 'rey-splide'],
				'version'  => REY_CORE_VERSION,
				'priority' => 'low',
			]
		]);

		$assets->register_asset('scripts', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/script.js',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			]
		]);

		$assets->register_asset('scripts', [
			self::ASSET_HANDLE . '-cs' => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/pg-skin-carousel-section.js',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			]
		]);

	}

	public function append_bg_choice($element){

		$control_manager = \Elementor\Plugin::instance()->controls_manager;

		// extract background args
		// group control is not available, so only get main bg control
		$bg = $control_manager->get_control_from_stack( $element->get_unique_name(), 'background_background' );
		if( $bg && ! is_wp_error($bg) ){
			// add new condition, for REY slideshow background
			$bg['options'][self::KEY] = [
				'title' => sprintf(_x( 'Background Slideshow (%s)', 'Background Control', 'rey-core' ), reycore__get_props('theme_title')),
				'icon' => 'eicon-slides',
			];
			$element->update_control( 'background_background', $bg );
		}

	}

	/**
	 * Add custom settings into Elementor's Section
	 *
	 * @since 1.0.0
	 */
	public function settings( $element )
	{

		$element->start_controls_section(
			'rey_section_slideshow',
			[
				'label' => __( 'Background Slideshow', 'rey-core' ) . \ReyCore\Elementor\Helper::rey_badge(),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'background_background' => self::KEY
				]
			]
		);

		$element->add_control(
			'rey_slideshow_autoplay',
			[
				'label' => __( 'Autoplay', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$element->add_control(
			'rey_slideshow_autoplay_time',
			[
				'label' => __( 'Autoplay Timeout', 'rey-core' ) . ' (ms)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 5000,
				'min' => 100,
				'max' => 30000,
				'step' => 10,
				'placeholder' => 5000,
				'condition' => [
					'rey_slideshow_autoplay' => 'yes',
				],
			]
		);

		$element->add_control(
			'rey_slideshow_speed',
			[
				'label' => __( 'Transition speed', 'rey-core' ) . ' (ms)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 500,
				'min' => 100,
				'max' => 5000,
				'step' => 10,
				'placeholder' => 500,
			]
		);

		$element->add_control(
			'rey_slideshow_effect',
			[
				'label' => __( 'Slideshow Effect', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'slide',
				'options' => [
					'slide'  => __( 'Slide', 'rey-core' ),
					'fade'  => __( 'Fade In/Out', 'rey-core' ),
					'scaler'  => __( 'Scale & Fade', 'rey-core' ),
				],
			]
		);

		$element->add_control(
			'rey_slideshow_nav',
			[
				'label' => __( 'Connect the dots', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'label_block' => true,
				'placeholder' => __( 'eg: #toggle-boxes-gd4fg6', 'rey-core' ),
				'description' => __( 'Use the Toggle Boxes widget and paste its unique id here. If empty, the first Toggler widget found in this section will be used, if any.', 'rey-core' ),
			]
		);

		$slides = new \Elementor\Repeater();

		$slides->add_control(
			'img',
			[
				'label' => __( 'Choose Image', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::MEDIA,
				'default' => [
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				],
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$slides->add_responsive_control(
			'img_position',
			[
				'label' => __( 'Position (X & Y)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '50% 50%',
				'selectors' => [
					// '{{WRAPPER}} {{CURRENT_ITEM}}' => 'background-position: {{VALUE}};',
					'{{WRAPPER}} {{CURRENT_ITEM}} .rey-section-slideshowItem-img' => 'object-position: {{VALUE}};',
				],
			]
		);

		$element->add_control(
			'rey_slideshow_slides',
			[
				'label' => __( 'Slides', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $slides->get_controls(),
			]
		);

		$element->add_control(
			'rey_slideshow_mobile__title',
			[
				'label' => esc_html__( 'MOBILE', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$element->add_control(
			'rey_slideshow_mobile',
			[
				'label' => __( 'Show on mobiles?', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$element->add_control(
			'rey_slideshow_mobile__color',
			[
				'label' => esc_html__( 'Background Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'(mobile){{WRAPPER}} .rey-section-slideshowItem.rey-section-slideshowItem--0' => 'background-color: {{VALUE}}; background-image: none;',
				],
				'condition' => [
					'rey_slideshow_mobile' => '',
				],
			]
		);

		$element->add_control(
			'rey_slideshow_mobile__image',
			[
				'label' => esc_html__( 'Background Image', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::MEDIA,
				'selectors' => [
					'(mobile){{WRAPPER}} .rey-section-slideshowItem.rey-section-slideshowItem--0' => 'background-image: url("{{URL}}");',
				],
				'condition' => [
					'rey_slideshow_mobile' => '',
				],
			]
		);

		$element->add_control(
			'rey_slideshow_misc__title',
			[
				'label' => esc_html__( 'MISC.', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$element->add_control(
			'rey_slideshow_container',
			[
				'label' => __( 'Fit Container', 'rey-core' ),
				'description' => __( 'Useful when using this feature in a Page cover global section.', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
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
		// Element based
		$settings = $element->get_settings();

		if( self::KEY !== $settings['background_background'] ){
			return;
		}

		if( ! isset($settings['rey_slideshow_slides']) ){
			return;
		}

		if( empty($settings['rey_slideshow_slides']) ){
			return;
		}

		$this->settings[ $element->get_id() ] = $settings;

		reycore_assets()->add_scripts(['splidejs', 'rey-splide', self::ASSET_HANDLE]);
		reycore_assets()->add_styles(['rey-splide', self::ASSET_HANDLE]);

		// reycore-elementor-elem-section-slideshow
		// reycore-elementor-section-slideshow

		$slideshow_config = [
			'type' => 'slide'
		];

		if( isset($settings['rey_slideshow_autoplay']) && $settings['rey_slideshow_autoplay'] ){
			$slideshow_config['autoplay'] = $settings['rey_slideshow_autoplay'] !== '';
			$slideshow_config['interval'] = absint( $settings['rey_slideshow_autoplay_time'] );
		}

		$slideshow_config['speed'] = absint( $settings['rey_slideshow_speed'] );

		$classes['slideshow-mobile'] = '--no-mobile-slideshow';

		if( isset($settings['rey_slideshow_mobile']) && $settings['rey_slideshow_mobile'] === 'yes' ){
			$slideshow_config['mobile'] = true;
			$classes['slideshow-mobile'] = '';
		}

		if( isset($settings['rey_slideshow_mobile']) && $settings['rey_slideshow_container'] !== '' ){
			$slideshow_config['class'] = '--slideshow-container --slideshow-container-gap-' . esc_attr( $settings['gap'] );
		}

		if( isset($settings['rey_slideshow_effect']) ){
			if( $settings['rey_slideshow_effect'] === 'scaler' ){
				$slideshow_config['type'] = 'fade';
			}
			elseif( $settings['rey_slideshow_effect'] === 'fade' ){
				$slideshow_config['type'] = 'fade';
			}
		}

		$element->add_render_attribute( 'slideshow_wrapper', 'data-rey-slideshow-settings', wp_json_encode($slideshow_config) );

		if( isset($settings['rey_slideshow_nav']) ){
			$element->add_render_attribute( 'slideshow_wrapper', 'data-rey-slideshow-nav', esc_attr( $settings['rey_slideshow_nav'] ) );
		}

		$element->add_render_attribute( '_wrapper', 'class', $classes );

		// Catch output
		ob_start();

	}

	public function after_render( $element ){

		$element_id = $element->get_id();

		if( ! isset($this->settings[ $element_id ]) ){
			return;
		}

		$element_type = $element->get_type();

		$slideshow_html = sprintf(
			'<div class="elementor-background-slideshow rey-section-slideshow splide--%s splide" data-abs="" %s>',
			esc_attr( $this->settings[ $element_id ]['rey_slideshow_effect'] ),
			$element->get_render_attribute_string( 'slideshow_wrapper' )
		);

			$slideshow_html .= '<div class="splide__track">';
			$slideshow_html .= '<div class="splide__list">';

			foreach ($this->settings[ $element_id ]['rey_slideshow_slides'] as $index => $item) {
				if( isset($item['img']['id']) && $image_id = $item['img']['id'] ){

					$img = wp_get_attachment_image( $image_id, 'full', false, [
						'class' => 'rey-section-slideshowItem-img'
					]);

					$slideshow_html .= sprintf(
						'<div style="--slide-index:%1$d" class="splide__slide rey-section-slideshowItem rey-section-slideshowItem--%1$d elementor-repeater-item-%3$s %4$s" %5$s>%2$s</div>',
						$index,
						$img,
						$item['_id'],
						($index === 0 ? 'is-active' : ''),
						($index !== 0 ? 'data-lazy-hiddenx=""' : '')
					);
				}
			}

			$slideshow_html .= '</div>';
			$slideshow_html .= '</div>';
		$slideshow_html .= '</div>';

		// Collect output
		$content = ob_get_clean();

		$html_tag = $this->settings[ $element_id ]['html_tag'] ?: ($element_type === 'section' ? 'section' : 'div');
		$query = sprintf('//%s[contains( @class, "elementor-element-%s")]', $html_tag, $element_id);

		if( $new_html = \ReyCore\Elementor\Helper::el_inject_html( $content, $slideshow_html, $query) ){
			$content = $new_html;
		}

		echo $content;

	}


	/**
	* Filter Section Print Content
	*
	* @since 1.0.0
	**/
	function print_template( $template, $element )
	{

		$template_new = "

		<# if ( settings.background_background && settings.background_background == 'rey_slideshow' && settings.rey_slideshow_slides ) { #>

			<# var slide_config = JSON.stringify({
				'autoplay': settings.rey_slideshow_autoplay,
				'interval': settings.rey_slideshow_autoplay_time,
				'animationDuration': settings.rey_slideshow_speed,
				'mobile': settings.rey_slideshow_mobile !== ''
			}); #>

			<div class='splide elementor-background-slideshow rey-section-slideshow' data-abs='' data-rey-slideshow-effect='{{settings.rey_slideshow_effect}}' data-rey-slideshow-nav='{{settings.rey_slideshow_nav}}' data-rey-slideshow-settings='{{slide_config}}'>
				<div class='splide__track'>
					<div class='splide__list'>
						<# _.each( settings.rey_slideshow_slides, function( item, index ) { #>
							<div class='splide__slide rey-section-slideshowItem rey-section-slideshowItem--{{index}} elementor-repeater-item-{{item._id}}'>
								<img src='{{item.img.url}}' class='rey-section-slideshowItem-img' />
							</div>
						<# } ); #>
					</div>
				</div>
			</div>

		";

		$template_new .= "<# } #>";

		return $template_new . $template;
	}

	public function is_enabled() {
		return true;
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Elementor Section & Container Slideshow Background', 'Module name', 'rey-core'),
			'description' => esc_html_x('Adds a slideshow as a background for Sections and Container. Unlike the native Elementor choice, this can be Navigatable.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['elementor'],
			'keywords'    => ['Elementor', 'Image', 'Slideshow', 'Section', 'Container'],
			'help'        => reycore__support_url('kb/add-section-image-slideshow-background/'),
			'video' => true
		];
	}

	public function module_in_use(){
		$results = \ReyCore\Elementor\Helper::scan_content_in_site( 'content', sprintf( '"%s"', self::KEY ) );
		return ! empty($results);

	}
}
