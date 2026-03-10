<?php
namespace ReyCore\Elementor\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( ! class_exists('\ReyCore\Modules\Cards\CardElement') ){
	return;
}

class Carousel extends \ReyCore\Modules\Cards\CardElement {

	public $_settings = [];

	public $_items = [];

	public $slider_components;

	public static $items_to_show_defaults = [
		'desktop' => 4,
		'tablet' => 3,
		'mobile' => 2,
	];

	public static function get_rey_config(){
		return [
			'id' => 'carousel',
			'title' => __( 'Carousel', 'rey-core' ),
			'icon' => 'eicon-posts-carousel',
			'categories' => [ 'rey-theme' ],
			'keywords' => ['carousel', 'slider', 'posts', 'gallery', 'categories'],
			'css' => [
				'assets/style[rtl].css',
			],
			'js' => [
				'assets/script.js',
			],
		];
	}

	public function rey_get_script_depends() {
		return [ 'elementor-frontend', 'reycore-elementor-frontend', 'splidejs', 'rey-splide', 'reycore-widget-carousel-scripts' ];
	}

	public function get_custom_help_url() {
		return reycore__support_url('kb/rey-elements/#carousel');
	}

	public function add_element_controls() {

		$this->selectors['carousel'] = '{{WRAPPER}} .rey-carouselEl';

		$this->controls__content();
		$this->get_source_controls();
		$this->controls__carousel_settings();
		$this->controls__teaser();
		$this->controls__content_styles();
		$this->controls__media_styles();
		$this->controls__title_styles();
		$this->controls__subtitle_styles();
		$this->controls__label_styles();
		$this->controls__button_styles();

		\ReyCore\Libs\Slider_Components::controls( $this );

	}

	public function controls__carousel_settings(){

		$this->start_controls_section(
			'section_carousel_settings',
			[
				'label' => __( 'Carousel Settings', 'rey-core' ),
			]
		);

			$this->add_control(
				'direction',
				[
					'label' => esc_html__( 'Direction', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'ltr',
					'options' => [
						'ltr'  => esc_html__( 'Horizontal', 'rey-core' ),
						'rtl'  => esc_html__( 'Horizontal Reverse', 'rey-core' ),
						'ttb'  => esc_html__( 'Vertical', 'rey-core' ),
					],
				]
			);

			$items_to_show = range( 1, 10 );
			$items_to_show = array_combine( $items_to_show, $items_to_show );

			$this->add_responsive_control(
				'items_to_show',
				[
					'label' => __( 'Items to Show', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'options' => [
						'' => __( 'Default', 'rey-core' ),
					] + $items_to_show,
					'selectors' => [
						$this->selectors['carousel'] => '--per-row: {{VALUE}}',
					],
					'default' => self::$items_to_show_defaults['desktop'],
				]
			);

			$this->add_responsive_control(
				'gap',
				[
					'label' => __( 'Gap', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 200,
					'step' => 1,
					'selectors' => [
						$this->selectors['carousel'] => '--gap: {{VALUE}}px;',
					],
				]
			);

			$this->add_control(
				'infinite',
				[
					'label' => __( 'Infinite Loop', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
				]
			);

			$this->add_control(
				'autoplay',
				[
					'label' => __( 'Autoplay', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'yes',
					'default' => '',
				]
			);

			$this->add_control(
				'autoplay_duration',
				[
					'label' => __( 'Autoplay Duration (ms)', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 9000,
					'min' => 3500,
					'max' => 20000,
					'step' => 50,
					'condition' => [
						'autoplay' => 'yes',
					],
					'selectors' => [
						$this->selectors['carousel'] => '--autoplay-duration: {{SIZE}}ms;',
					],
				]
			);

			$this->add_control(
				'autoplay_pause_hover',
				[
					'label' => esc_html__( 'Pause on hover', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
					'condition' => [
						'autoplay!' => '',
					],
				]
			);

			$this->add_control(
				'lazy_load_img',
				[
					'label' => esc_html__( 'Lazy Images', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

			$this->add_control(
				'c_free_drag',
				[
					'label' => esc_html__( 'Free Drag', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT2,
					'default' => [],
					'multiple' => true,
					'options' => [
						'desktop'  => esc_html__( 'Desktop', 'rey-core' ),
						'tablet'  => esc_html__( 'Tablet', 'rey-core' ),
						'mobile'  => esc_html__( 'Mobile', 'rey-core' ),
					],
				]
			);

			$this->add_control(
				'autoscroll',
				[
					'label' => esc_html__( 'Auto-Scroll', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => -2,
					'max' => 5,
					'step' => 1,
				]
			);

			$this->add_control(
				'autoscroll_rewind',
				[
					'label' => esc_html__( 'Auto-Scroll Rewind', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
					'condition' => [
						'autoscroll!' => ['', 0],
						'infinite' => '',
					],
				]
			);

			$this->add_control(
				'autoscroll_pause_hover',
				[
					'label' => esc_html__( 'Auto-Scroll Pause on Hover', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'condition' => [
						'autoscroll!' => ['', 0],
					],
				]
			);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_carousel_spacing',
			[
				'label' => __( 'Carousel Spacing', 'rey-core' ),
			]
		);

			$this->add_responsive_control(
				'c_padding',
				[
					'label' => __( 'Padding', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'description' => 'Adding left or right padding will cutoff the next slides.',
					'selectors' => [
						$this->selectors['wrapper'] . ' .splide__track' => 'padding-top: {{TOP}}px; padding-left: {{LEFT}}px; padding-right: {{RIGHT}}px; padding-bottom: {{BOTTOM}}px;',
					],
					'render_type' => 'template',
				]
			);

			$this->add_control(
				'c_side_offset',
				[
					'label' => esc_html__( 'Side Offset', 'rey-core' ),
					'description' => esc_html__( 'This option will pull the carousel horizontal sides toward the window edges. Applies only on desktop and overrides other settings.', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- None -', 'rey-core' ),
						'both'  => esc_html__( 'Both', 'rey-core' ),
						'left'  => esc_html__( 'Left', 'rey-core' ),
						'right'  => esc_html__( 'Right', 'rey-core' ),
					],
				]
			);

			$this->add_control(
				'c_side_offset_opa',
				[
					'label' => esc_html__( 'Inactives opacity', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1,
					'step' => 0.05,
					'condition' => [
						'c_side_offset!' => '',
					],
					'selectors' => [
						'{{WRAPPER}}' => '--side-offset-inactive-opacity: {{VALUE}};',
					],
				]
			);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_carousel_navigation',
			[
				'label' => __( 'Carousel Navigation', 'rey-core' ),
			]
		);

			\ReyCore\Libs\Slider_Components::controls_nav( $this );

			$this->add_control(
				'carousel_id',
				[
					'label' => __( 'Carousel Unique ID', 'rey-core' ),
					'label_block' => true,
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => uniqid('carousel-'),
					'placeholder' => __( 'eg: some-unique-id', 'rey-core' ),
					'description' => sprintf(__( 'Copy the ID above and paste it into the "Toggle Boxes" Widget or "Slider Navigation" widget where specified. No hashtag needed. Read more on <a href="%s" target="_blank">how to connect them</a>.', 'rey-core' ), reycore__support_url('kb/products-grid-element/#adding-custom-navigation')),
					'separator' => 'before',
					'style_transfer' => false,
					'wpml' => false,
				]
			);

			$this->add_control(
				'target_sync',
				[
					'label' => __( 'Sync. Controller', 'rey-core' ),
					'label_block' => true,
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'placeholder' => __( 'eg: unique-id', 'rey-core' ),
					'description' => __( 'By pasting a "Slider" or "Carousel" element\'s "Unique ID", this element will listen for the specified element movement.', 'rey-core' ),
					'render_type' => 'none',
					'separator' => 'before',
					'wpml' => false,
				]
			);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_carousel_vertical_settings',
			[
				'label' => __( 'Vertical Carousel Settings', 'rey-core' ),
				'condition' => [
					'direction' => 'ttb',
				],
			]
		);

			$this->add_responsive_control(
				'height',
				[
				'label' => esc_html__( 'Height', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', 'vh' ],
					'range' => [
						'px' => [
							'min' => 50,
							'max' => 1100,
							'step' => 1,
						],
						'vh' => [
							'min' => 5,
							'max' => 100,
						],
					],
					'default' => [],
					'selectors' => [
						'{{WRAPPER}} .splide__track' => 'height: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_control(
				'ttb_autoheight',
				[
					'label' => esc_html__( 'Force Items Auto Height', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
				]
			);

			$this->add_control(
				'ttb_mask',
				[
					'label' => esc_html__( 'Vertical Fade Mask', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

			$this->add_control(
				'ttb_rotate_arrows',
				[
					'label' => esc_html__( 'Rotate Arrows', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
					'condition' => [
						'arrows!' => '',
					],
				]
			);

		$this->end_controls_section();

	}

	public function render_start(){

		$attributes['data-layout'] = $this->_settings[$this->card_key];

		$direction = $this->_settings['direction'] === 'ltr' && is_rtl() ? 'rtl' : $this->_settings['direction'];

		$attributes['class'] = [
			'rey-carouselEl',
			'--' . $this->_settings['source'],
			'--direction-' . $direction,
		];

		if( $this->_settings['autoplay'] !== '' && $this->_settings['autoplay_pause_hover'] !== '' ){
			$attributes['class'][] = $this->slider_components::$selectors['pause_hover'];
		}

		$attributes['class'][] = $this->slider_components::$selectors['wrapper'];

		if( $target_sync = $this->_settings['target_sync'] ){
			$attributes['data-target-sync'] = esc_attr($target_sync);
		}

		if( count($this->_items) > 1 ) {

			$carousel_config = [
				'type'             => 'slide',
				'direction'        => $direction,
				'items_to_show'    => ! empty($this->_settings['items_to_show']) ? $this->_settings['items_to_show'] : self::$items_to_show_defaults['desktop'],
				'infinite'         => $this->_settings['infinite'] !== '',
				'autoplay'         => $this->_settings['autoplay'] !== '',
				'pauseOnHover'     => $this->_settings['autoplay'] !== '' && $this->_settings['autoplay_pause_hover'] !== '',
				'interval'         => ! empty($this->_settings['autoplay_duration']) ? absint($this->_settings['autoplay_duration']) : 9000,
				'customArrows'     => $this->slider_components::$selectors['arrows'],
				// 'customPagination' => $this->slider_components::$selectors['dots'],
				'pagination'       => $this->_settings['dots'] !== '',
				'speed'            => 700,
				'c_padding'        => $this->_settings['c_padding'],
				'uniqueID'         => $this->_settings['carousel_id'],
				'targetSync'       => $this->_settings['target_sync'],
				'free_drag'        => $this->_settings['c_free_drag'],
				'lazy_load'        => $this->_settings['lazy_load_img'] !== '',
				'autoscroll'       => $this->_settings['autoscroll'],
				'autoscroll_rewind'       => $this->_settings['autoscroll_rewind'] === 'yes',
				'autoscroll_pause_hover'  => $this->_settings['autoscroll_pause_hover'] === 'yes',
				'bp_devices'       => [
					'perPage' => 'items_to_show',
					'padding' => 'c_padding'
				],
			];




			if( 'ttb' === $this->_settings['direction'] ){

				if( $this->_settings['ttb_mask'] !== '' ){
					$attributes['class'][] = '--ttb-mask';
				}

				if( $this->_settings['ttb_rotate_arrows'] !== '' ){
					$attributes['class'][] = '--ttb-rotate-arrows';
				}

				if( ($height = $this->_settings['height']) && isset($height['size'], $height['unit']) ){

					$carousel_config['height'] = (($size = $height['size']) ? $size : 200) . $height['unit'];
					$carousel_config['autoheight'] = $this->_settings['ttb_autoheight'] !== '';

					if( ! $carousel_config['autoheight'] ){
						$attributes['class'][] = '--ttb-no-autoheight';
					}

				}
				else {
					unset($carousel_config['direction']);
				}
			}

			foreach ( $carousel_config['bp_devices'] as $control ) {
				foreach ( \ReyCore\Elementor\Helper::get_breakpoints() as $device ) {

					if( isset($this->_settings[ $control . $device ]) ){
						$carousel_config[ $control . $device ] = $this->_settings[ $control . $device ];
					}
					else {

						if( 'items_to_show' === $control ){

							$clean_device_name = substr( $device, 1 );

							if( isset( self::$items_to_show_defaults[ $clean_device_name ] ) ){

								$value = self::$items_to_show_defaults[$clean_device_name];

								// check desktop if smaller
								if( isset($this->_settings[ $control ]) && $this->_settings[ $control ] < $value ){
									$value = $this->_settings[ $control ];
								}

								$carousel_config[ $control . $device ] = $value;
							}

						}

					}
				}
			}

			$attributes['data-carousel-settings'] = wp_json_encode($carousel_config);
		}

		$this->add_render_attribute( 'wrapper', $attributes );

		?><div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>><?php
	}

	public function render_end(){
		?></div><?php
	}

	public function render_carousel(){

		if( empty($this->_items) ){
			return;
		}

		$attributes['class'] = [
			'splide',
		];

		if( $side_offset = $this->_settings['c_side_offset'] ){
			$attributes['data-side-offset'] = esc_attr($side_offset);
		}

		if( $carousel_id = esc_attr( $this->_settings['carousel_id'] ) ){
			$attributes['class'][] = $carousel_id;
			$attributes['data-slider-carousel-id'] = esc_attr($carousel_id);
		}

		if( ! empty($attributes) ){
			$this->add_render_attribute( 'carousel_wrapper', $attributes );
		} ?>

		<div <?php echo $this->get_render_attribute_string( 'carousel_wrapper' ); ?>>
			<div class="splide__track">
				<div class="splide__list __slides">

					<?php
					for ($i=0; $i < count($this->_items); $i++) {
						$this->item_key = $i;

						$this->parse_item();
						$this->render_item__start();
						$this->render_item();
						$this->render_item__end();

					} ?>

				</div>
			</div>

			<?php $this->slider_components->render_dots_container(); ?>

		</div>
		<?php
	}

	public static function default_item_classes(){
		return [
			'splide_slide' => 'splide__slide',
			'item' => '__slide',
		];
	}

	public function render_item__start(){

		$classes = self::default_item_classes();

		if( isset($this->_items[$this->item_key]['_id']) && $_id = $this->_items[$this->item_key]['_id'] ){
			$classes['_id'] = 'elementor-repeater-item-' . $_id;
		}

		if( isset($this->_items[$this->item_key]['item_classes']) ){
			$classes = array_merge($classes, $this->_items[$this->item_key]['item_classes']);
		}

		do_action('reycore/elementor/card/before_item', $this, 'carousel');

		?><div class="<?php echo esc_attr(implode(' ', $classes)) ?>"><?php
	}

	public function render_item__end(){
		?></div><?php

		do_action('reycore/elementor/card/after_item', $this, 'carousel');
	}

	public function render() {

		reycore_assets()->add_styles([$this->get_style_name(), 'rey-splide']);
		reycore_assets()->add_scripts( $this->rey_get_script_depends() );

		$this->_settings = $this->get_settings_for_display();

		if( $this->_settings['autoscroll'] ){
			reycore_assets()->add_scripts( 'splidejs-autoscroll' );
		}

		if( ! ($this->_items = $this->get_items_data()) ){
			return;
		}

		$this->slider_components = new \ReyCore\Libs\Slider_Components( $this );

		$this->render_start();
		$this->render_carousel();
		$this->slider_components->render();
		$this->render_end();
	}

}
