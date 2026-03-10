<?php
namespace ReyCore\Libs;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Slider_Components
{

	protected $_settings = [];

	protected static $_settings_keys = [];

	const ARROWS_KEY = 'arrows';
	const DOTS_KEY = 'dots';

	protected static $_name = '';

	protected $_element;

	public $_items;

	const SELECTORS_WRAPPER = 'rey-sliderComp-nav';
	const SELECTORS_ARROWS  = 'rey-sliderArrows';
	const SELECTORS_DOTS    = 'rey-sliderDots';

	public static $controls_selectors = [];
	public static $selectors = [];

	public function __construct( $element, $setting_keys = [] )
	{
		if( ! $element ){
			return;
		}

		$this->_element = $element;

		if( isset($element->_items) ){
			$this->_items = $element->_items;
		}

		$this->_settings = $element->_settings;

		$post_id = ($_pid = get_the_ID()) ? $_pid : get_queried_object_id();

		self::$selectors = [
			'wrapper'     => self::SELECTORS_WRAPPER,
			'arrows'      => sprintf('arrows-%s-%s', $this->_element->get_id(), $post_id),
			'dots'        => sprintf('pagination-%s-%s', $this->_element->get_id(), $post_id),
			'pause_hover' => '--pause-hover',
		];

		self::$_name = $element->get_unique_name();
		self::set_settings_key($setting_keys);

	}

	protected static function set_settings_key($custom_keys){

		if( empty($custom_keys) ){
			self::$_settings_keys[self::$_name][ self::ARROWS_KEY ] = self::ARROWS_KEY;
			self::$_settings_keys[self::$_name][ self::DOTS_KEY ] = self::DOTS_KEY;
		}
		else {
			foreach ($custom_keys as $key => $value) {
				self::$_settings_keys[self::$_name][ $key ] = $value;
			}
		}

	}

	public function render(){

		// $this->render_dots();
		$this->render_arrows();

	}

	public function is_enabled( $component = 'arrows' ){

		if( ! $this->_element ){
			return false;
		}

		return isset($this->_settings[ self::$_settings_keys[self::$_name][ $component ] ]) && '' !== $this->_settings[ self::$_settings_keys[self::$_name][ $component ] ];
	}

	public function render_arrows() {

		if( ! $this->_element ){
			return;
		}

		if( ! isset($this->_settings[self::$_settings_keys[self::$_name][ 'arrows' ]]) ){
			return;
		}

		if( '' === $this->_settings[self::$_settings_keys[self::$_name][ 'arrows' ]] ){
			return;
		}

		if( isset($this->_items) && count($this->_items) < 2 ){
			return;
		}

		$classes[] = self::SELECTORS_ARROWS;
		$classes[] = self::$selectors['arrows'];
		$classes[] = $this->_settings['arrows_on_hover'] === 'yes' ? '--hide-on-idle' : '';

		?>
		<div class="<?php echo implode(' ', $classes) ?>" data-lazy-hidden>
			<?php
			$custom_svg_icon = '';

			if( 'custom' === $this->_settings['arrows_type'] &&
				($custom_icon = $this->_settings['arrows_custom_icon']) &&
				isset($custom_icon['value']) && !empty($custom_icon['value'])
			){
				ob_start();
				\Elementor\Icons_Manager::render_icon( $custom_icon, [ 'aria-hidden' => 'true', 'class' => '' ] );
				$custom_svg_icon = ob_get_clean();
			}

			reycore__svg_arrows([
				'type'        => $this->_settings['arrows_type'],
				'custom_icon' => $custom_svg_icon,
				'single'      => $this->_settings['arrow_to_hide'],
				'attributes'  => [
					'left'  => 'data-dir="<"',
					'right' => 'data-dir=">"',
				]
			]); ?>
		</div>

		<?php

		reycore_assets()->add_styles('reycore-slider-components');

	}


	public function render_dots_container(){

		if( ! $this->_element ){
			return;
		}

		if( ! isset($this->_settings[self::$_settings_keys[self::$_name][ 'dots' ]]) ){
			return;
		}

		// check if enabled
		if( '' === $this->_settings[ self::$_settings_keys[self::$_name][ 'dots' ] ] ){
			return;
		}

		if( isset($this->_items) && count($this->_items) < 2 ){
			return;
		}

		$classes[] = 'splide__pagination';
		$classes[] = self::SELECTORS_DOTS;
		$classes[] = self::$selectors['dots'];

		if( $this->_settings['autoplay'] !== '' ){
			$classes[] = '--autoplay';
		}

		$attributes = [
			'class' => $classes,
		];

		if( $this->_settings['dots_position'] && $dots_position = explode('-', esc_attr($this->_settings['dots_position'])) ){
			$attributes['data-position-y'] = $dots_position[0];
			$attributes['data-position-x'] = $dots_position[1];
		}

		$attributes['data-arrange'] = esc_attr($this->_settings['dots_arrange']);
		$attributes['data-style'] = esc_attr($this->_settings['dots_style']);
		$attributes['data-lazy-hidden'] = '';

		$this->_element->add_render_attribute( 'dots', $attributes );

		printf('<ul %s ></ul>', $this->_element->get_render_attribute_string( 'dots' ));

		reycore_assets()->add_styles('reycore-slider-components', '' !== $this->_settings['load_assets_early']);
	}

	public function render_dots(){

		if( ! $this->_element ){
			return;
		}

		if( ! isset($this->_settings[self::$_settings_keys[self::$_name][ 'dots' ]]) ){
			return;
		}

		if( '' === $this->_settings[self::$_settings_keys[self::$_name][ 'dots' ]] ){
			return;
		}

		if( isset($this->_items) && count($this->_items) < 2 ){
			return;
		}

		$classes[] = self::SELECTORS_DOTS;
		$classes[] = self::$selectors['dots'];

		if( $this->_settings['autoplay'] !== '' ){
			$classes[] = '--autoplay';
		}

		$this->_element->add_render_attribute( 'dots', 'class', $classes );

		if( $this->_settings['dots_position'] && $dots_position = explode('-', esc_attr($this->_settings['dots_position'])) ){
			$this->_element->add_render_attribute( 'dots', 'data-position-y', $dots_position[0] );
			$this->_element->add_render_attribute( 'dots', 'data-position-x', $dots_position[1] );
		}

		$this->_element->add_render_attribute( 'dots', 'data-arrange', esc_attr($this->_settings['dots_arrange']) );
		$this->_element->add_render_attribute( 'dots', 'data-style', esc_attr($this->_settings['dots_style']) );

		?>
		<div <?php echo $this->_element->get_render_attribute_string( 'dots' ); ?> >
			<?php
			if( isset($this->_items) ):
				foreach($this->_items as $key => $item):
					printf('<button data-go="%1$d" aria-label="%2$s %1$d" class="%3$s"></button>', $key, esc_html__('Go to ', 'rey-core'), $key === 0 ? 'is-active' : '');
				endforeach;
			endif; ?>
		</div>
		<?php

		reycore_assets()->add_styles('reycore-slider-components', '' !== $this->_settings['load_assets_early']);
	}

	public static function controls( $element, $setting_keys = [] ){

		self::$controls_selectors = [
			'main' => '{{WRAPPER}} .' . self::SELECTORS_WRAPPER,
			'arrows' => '{{WRAPPER}} .' . self::SELECTORS_ARROWS,
			'arrow' => '{{WRAPPER}} .rey-arrowSvg',
			'dots' => '{{WRAPPER}} .' . self::SELECTORS_DOTS,
		];

		self::$_name = $element->get_unique_name();
		self::set_settings_key($setting_keys);
		self::controls_arrows($element);
		self::controls_dots($element);
	}

	public static function controls_arrows( $element ){

		$element->start_controls_section(
			'section_arrow_style',
			[
				'label' => __( 'Arrows Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					self::$_settings_keys[self::$_name][ 'arrows' ] . '!' => '',
				],
			]
		);


		$element->add_control(
			'arrows_type',
			[
				'label' => esc_html__( 'Type', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => esc_html__( 'Default', 'rey-core' ),
					'chevron'  => esc_html__( 'Chevron', 'rey-core' ),
					'custom'  => esc_html__( 'Custom Icon', 'rey-core' ),
				],
			]
		);

		$element->add_control(
			'arrows_custom_icon',
			[
				'label' => __( 'Arrow Icon (Right)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::ICONS,
				'skin' => 'inline',
				'condition' => [
					'arrows_type' => 'custom',
				],
			]
		);

		$element->add_control(
			'arrows_on_hover',
			[
				'label' => __( 'Show On Hover', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$element->add_control(
			'arrow_to_hide',
			[
				'label' => esc_html__( 'Hide Arrow', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => esc_html__( '- No -', 'rey-core' ),
					'left'  => esc_html__( 'Left', 'rey-core' ),
					'right'  => esc_html__( 'Right', 'rey-core' ),
				],
			]
		);

		$element->add_control(
			'arrows_position',
			[
			   'label' => esc_html__( 'Vertical position', 'rey-core' ) . ' (%)',
				'type' => \Elementor\Controls_Manager::SLIDER,
				'range' => [
					'%' => [
						'min' => 0,
						'max' => 100,
						'step' => 1,
					],
				],
				'separator' => 'before',
				'selectors' => [
					self::$controls_selectors['main'] => '--arrows-v-pos: {{SIZE}}%;',
				],
			]
		);

		$element->add_control(
			'arrows_size',
			[
				'label' => esc_html__( 'Size', 'rey-core' ) . ' (px)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 5,
				'max' => 200,
				'step' => 1,
				'selectors' => [
					self::$controls_selectors['main'] => '--arrow-size: {{VALUE}}px',
				],
			]
		);

		$element->add_responsive_control(
			'arrows_spacing',
			[
				'label' => esc_html__( 'Spacing', 'rey-core' ) . ' (px)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => -100,
				'max' => 1000,
				'step' => 1,
				'selectors' => [
					self::$controls_selectors['main'] => '--arrow-side-spacing:{{VALUE}}px;',
				],
			]
		);

		$element->add_control(
			'arrows_height',
			[
				'label' => esc_html__( 'Height', 'rey-core' ) . ' (em)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0.1,
				'max' => 1,
				'step' => 0.1,
				'selectors' => [
					self::$controls_selectors['arrow'] => '--arrow-height: {{VALUE}}em',
				],
			]
		);

		$element->add_responsive_control(
			'arrows_padding',
			[
				'label' => __( 'Padding', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					self::$controls_selectors['arrow'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$element->start_controls_tabs( 'arrows_tabs_styles');

			$element->start_controls_tab(
				'arrows_tab_default',
				[
					'label' => __( 'Default', 'rey-core' ),
				]
			);

				$element->add_control(
					'arrows_color_diff',
					[
						'label' => esc_html__( 'Enable difference color?', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::SWITCHER,
						'default' => 'difference',
						'return_value' => 'difference',
						'selectors' => [
							self::$controls_selectors['arrows'] => 'color: #fff; mix-blend-mode: {{VALUE}}',
						],
					]
				);

				$element->add_control(
					'arrows_color',
					[
						'label' => esc_html__( 'Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							self::$controls_selectors['arrow'] => 'color: {{VALUE}}',
						],
					]
				);

				$element->add_control(
					'arrows_bg_color',
					[
						'label' => esc_html__( 'Background Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							self::$controls_selectors['arrow'] => 'background-color: {{VALUE}}',
						],
					]
				);

				$element->add_group_control(
					\Elementor\Group_Control_Border::get_type(),
					[
						'name' => 'arrows_border',
						'selector' => self::$controls_selectors['arrow'],
						'responsive' => true,
					]
				);

			$element->end_controls_tab();

			$element->start_controls_tab(
				'arrows_tab_hover',
				[
					'label' => __( 'Hover', 'rey-core' ),
				]
			);

				$element->add_control(
					'arrows_color_hover',
					[
						'label' => esc_html__( 'Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							self::$controls_selectors['arrow'] . ':hover' => 'color: {{VALUE}}',
						],
					]
				);

				$element->add_control(
					'arrows_bg_color_hover',
					[
						'label' => esc_html__( 'Background Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							self::$controls_selectors['arrow'] . ':hover' => 'background-color: {{VALUE}}',
						],
					]
				);

				$element->add_control(
					'arrows_border_color_hover',
					[
						'label' => esc_html__( 'Border Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							self::$controls_selectors['arrow'] . ':hover' => 'border-color: {{VALUE}}',
						],
					]
				);

			$element->end_controls_tab();

		$element->end_controls_tabs();

		$element->add_control(
			'arrows_border_radius',
			[
				'label' => __( 'Border Radius', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					self::$controls_selectors['arrow'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$element->end_controls_section();
	}

	public static function controls_dots( $element ){

		$element->start_controls_section(
			'section_dots_style',
			[
				'label' => __( 'Dots Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					self::$_settings_keys[self::$_name][ 'dots' ] . '!' => '',
				],
			]
		);

			$element->add_control(
				'dots_color',
				[
					'label' => esc_html__( 'Dots Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						self::$controls_selectors['dots'] => 'color: {{VALUE}}',
					],
				]
			);

			$element->add_control(
				'dots_color_diff',
				[
					'label' => esc_html__( 'Enable difference color?', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'difference',
					'return_value' => 'difference',
					'selectors' => [
						self::$controls_selectors['dots'] => 'color: #fff; mix-blend-mode: {{VALUE}}',
					],
				]
			);

			$element->add_control(
				'dots_style',
				[
					'label' => esc_html__( 'Dots Style', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'circles',
					'options' => [
						'circles'  => esc_html__( 'Circles', 'rey-core' ),
						'circles-ext'  => esc_html__( 'Dots & Circle', 'rey-core' ),
						'bars'  => esc_html__( 'Bars', 'rey-core' ),
					],
				]
			);

			$element->add_control(
				'dots_position',
				[
					'label' => esc_html__( 'Position', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'bottom-center',
					'options' => [
						'top-left'  => esc_html__( 'Top Left', 'rey-core' ),
						'top-center'  => esc_html__( 'Top Center', 'rey-core' ),
						'top-right'  => esc_html__( 'Top Right', 'rey-core' ),
						'center-right'  => esc_html__( 'Middle Right', 'rey-core' ),
						'center-left'  => esc_html__( 'Middle Left', 'rey-core' ),
						'bottom-left'  => esc_html__( 'Bottom Left', 'rey-core' ),
						'bottom-center'  => esc_html__( 'Bottom Center', 'rey-core' ),
						'bottom-right'  => esc_html__( 'Bottom Right', 'rey-core' ),
					],
				]
			);

			$element->add_control(
				'dots_arrange',
				[
					'label' => esc_html__( 'Arrangement', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'inside',
					'options' => [
						'inside'  => esc_html__( 'Inside', 'rey-core' ),
						'outside'  => esc_html__( 'Outside', 'rey-core' ),
					],
				]
			);

			$element->add_responsive_control(
				'dots_spacing',
				[
					'label' => esc_html__( 'Spacing', 'rey-core' ) . ' (px)',
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 500,
					'step' => 1,
					'selectors' => [
						self::$controls_selectors['main'] => '--dots-side-spacing:{{VALUE}}px;',
					],
				]
			);

			$element->add_control(
				'load_assets_early',
				[
					'label' => esc_html__( 'Load Assets Early', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

		$element->end_controls_section();
	}

	public static function controls_nav( $element, $setting_keys = [] ){

		self::$_name = $element->get_unique_name();
		self::set_settings_key($setting_keys);

		$extra_controls_config = [];

		if( isset($setting_keys['extra']) ){
			$extra_controls_config = $setting_keys['extra'];
		}

		// Nav title
		$nav_title_control_config = array_merge([
			'label' => esc_html__( 'Navigation', 'rey-core' ),
			'type' => \Elementor\Controls_Manager::HEADING,
			'separator' => 'before',
		], $extra_controls_config);

		$element->add_control( 'heading_navigation', $nav_title_control_config );

		// Arrows
		$arrows_control_config = array_merge([
			'label' => esc_html__( 'Arrows', 'rey-core' ),
			'type' => \Elementor\Controls_Manager::SWITCHER,
			'default' => 'yes',
		], $extra_controls_config);

		$element->add_control( self::$_settings_keys[self::$_name][ 'arrows' ], $arrows_control_config );

		// Dots
		$dots_control_config = array_merge([
			'label' => esc_html__( 'Dots', 'rey-core' ),
			'type' => \Elementor\Controls_Manager::SWITCHER,
			'default' => 'yes',
		], $extra_controls_config);

		$element->add_control( self::$_settings_keys[self::$_name][ 'dots' ], $dots_control_config );

	}
}
