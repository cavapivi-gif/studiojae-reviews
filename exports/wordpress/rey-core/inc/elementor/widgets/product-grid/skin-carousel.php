<?php
namespace ReyCore\Elementor\Widgets\ProductGrid;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class SkinCarousel extends \Elementor\Skin_Base
{

	public $_settings = [];

	public function get_id() {
		return 'carousel';
	}

	public function get_title() {
		return __( 'Carousel', 'rey-core' );
	}

	protected function _register_controls_actions() {
		parent::_register_controls_actions();

		add_action( 'elementor/element/reycore-product-grid/section_layout/after_section_end', [ $this, 'register_carousel_controls' ] );
		add_action( 'elementor/element/reycore-product-grid/section_component_styles/after_section_end', [ $this, 'register_carousel_styles' ] );
	}

	public function register_carousel_controls( $element ){

		$element->start_injection( [
			'of' => 'limit',
		] );

		$element->add_responsive_control(
			'slides_to_show',
			[
				'label' => __( 'Slides to Show', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 1,
				'max' => 10,
				'step' => 1,
				'condition' => [
					'_skin' => 'carousel',
				],
				'selectors' => [
					'{{WRAPPER}} ul.products' => '--woocommerce-grid-columns: {{VALUE}}',
				],
				'render_type' => 'template',
				'default' => 4,
				'tablet_default' => 3,
				'mobile_default' => 2,
			]
		);

		$element->add_control(
			'slides_to_move',
			[
				'label'       => __( 'Slides to Move', 'rey-core' ),
				'description' => __( 'Will switch from moving "pages" to individual slides.', 'rey-core' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'min'         => 1,
				'max'         => 10,
				'step'        => 1,
				'default'     => '',
				'condition'   => [
					'_skin' => 'carousel',
				],
			]
		);

		$element->add_control(
			'disable_desktop',
			[
				'label' => esc_html__( 'Disable on desktop', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'return_value' => 'desktop',
				'separator' => 'before',
				// 'prefix_class' => '--disable-',
				'condition' => [
					'_skin' => 'carousel',
				],
				'render_type' => 'template',
			]
		);

		$element->add_control(
			'disable_tablet',
			[
				'label' => esc_html__( 'Disable on tablet', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'return_value' => 'tablet',
				// 'prefix_class' => '--disable-',
				'condition' => [
					'_skin' => 'carousel',
				],
				'render_type' => 'template',
			]
		);

		$element->add_control(
			'disable_mobile',
			[
				'label' => esc_html__( 'Disable on mobile', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'return_value' => 'mobile',
				// 'prefix_class' => '--disable-',
				'condition' => [
					'_skin' => 'carousel',
				],
				'render_type' => 'template',
			]
		);

		$element->end_injection();


		$element->start_controls_section(
			'section_carousel_settings',
			[
				'label' => __( 'Carousel Settings', 'rey-core' ),
				'condition' => [
					'_skin' => 'carousel',
				],
			]
		);

		$element->add_control(
			'pause_on_hover',
			[
				'label' => __( 'Pause on Hover', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'yes',
				'options' => [
					'yes' => __( 'Yes', 'rey-core' ),
					'no' => __( 'No', 'rey-core' ),
				],
			]
		);

		$element->add_control(
			'autoplay',
			[
				'label' => __( 'Autoplay', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'yes',
				'options' => [
					'yes' => __( 'Yes', 'rey-core' ),
					'no' => __( 'No', 'rey-core' ),
				],
			]
		);

		$element->add_control(
			'autoplay_speed',
			[
				'label' => __( 'Autoplay Speed', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 5000,
				'condition' => [
					'autoplay' => 'yes',
				],
			]
		);

		$element->add_responsive_control(
			'infinite',
			[
				'label' => __( 'Infinite Loop', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'yes',
				'options' => [
					'yes' => __( 'Yes', 'rey-core' ),
					'no' => __( 'No', 'rey-core' ),
				],
			]
		);

		$element->add_control(
			'effect',
			[
				'label' => __( 'Effect', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'slide',
				'options' => [
					'slide' => __( 'Slide', 'rey-core' ),
					'fade' => __( 'Fade', 'rey-core' ),
				],
			]
		);

		$element->add_control(
			'speed',
			[
				'label' => __( 'Animation Speed', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 500,
			]
		);

		$element->add_control(
			'direction',
			[
				'label' => __( 'Direction', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'ltr',
				'options' => [
					'ltr' => __( 'Left', 'rey-core' ),
					'rtl' => __( 'Right', 'rey-core' ),
				],
			]
		);

		$element->add_responsive_control(
			'gap',
			[
				'label' => __( 'Gap', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 200,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .reyEl-productGrid ul.products' => '--woocommerce-products-gutter: {{VALUE}}px',
				],
				'render_type' => 'template',
			]
		);

		$element->add_control(
			'carousel_free_drag',
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

		$element->add_control(
			'delay_init',
			[
				'label' => __( 'Delay Initialization', 'rey-core' ) . ' (ms)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 20000,
				'step' => 50,
			]
		);

		// Navigation
		$element->add_control(
			'carousel_arrows',
			[
				'label' => esc_html__( 'Navigation Arrows', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'separator' => 'before'
			]
		);

		$element->add_control(
			'carousel_nav_notice',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => esc_html__( 'Did you know you can use "Slider Navigation" element to control this carousel, and place it everywhere? Read below on the "Carousel Unique ID" control.', 'rey-core' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
				'condition' => [
					'carousel_arrows!' => '',
				],
			]
		);

		$element->add_control(
			'offset_title',
			[
			   'label' => esc_html__( 'Spacing & Offset', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$element->add_responsive_control(
			'carousel_padding',
			[
				'label' => __( 'Carousel Padding', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'vw' ],
				'selectors' => [
					'{{WRAPPER}} .splide__track' => 'padding-left: {{LEFT}}{{UNIT}}; padding-right: {{RIGHT}}{{UNIT}}; padding-top: {{TOP}}{{UNIT}}; padding-bottom: {{BOTTOM}}{{UNIT}}',
				],
				'render_type' => 'template',
			]
		);

		$element->add_control(
			'carousel_side_offset',
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
				'prefix_class' => '--offset-on-',
			]
		);

		$element->add_control(
			'carousel_side_offset_opa',
			[
				'label' => esc_html__( 'Inactives opacity', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 1,
				'step' => 0.05,
				'condition' => [
					'carousel_side_offset!' => '',
				],
				'selectors' => [
					'{{WRAPPER}}' => '--side-offset-inactive-opacity: {{VALUE}};',
				],
			]
		);

		$element->add_control(
			'carousel_id',
			[
				'label' => __( 'Carousel Unique ID', 'rey-core' ),
				'label_block' => true,
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => uniqid('carousel-'),
				'placeholder' => __( 'eg: some-unique-id', 'rey-core' ),
				'description' => sprintf(__( 'Copy the ID above and paste it into the "Toggle Boxes" Widget or "Slider Navigation" widget where specified. No hashtag needed. Read more on <a href="%s" target="_blank">how to connect them</a>.', 'rey-core' ), reycore__support_url('kb/products-grid-element/#adding-custom-navigation') ),
				'separator' => 'before',
				'style_transfer' => false,
				'wpml' => false,
			]
		);

		$element->add_control(
			'disable_acc_outlines',
			[
				'label' => esc_html__( 'Disable accesibility outlines', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'prefix_class' => '--disable-acc-outlines-',
			]
		);

		$element->end_controls_section();

	}

	function register_carousel_styles( $element ){

		$element->start_controls_section(
			'section_carousel_arrows_styles',
			[
				'label' => __( 'Arrows styles for Carousel', 'rey-core' ),
				'condition' => [
					'_skin' => 'carousel',
					'carousel_arrows!' => '',
				],
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$element->add_control(
			'carousel_arrows_position',
			[
				'label' => esc_html__( 'Position', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'inside',
				'options' => [
					'inside'  => esc_html__( 'Inside (over first/last products)', 'rey-core' ),
					'outside'  => esc_html__( 'Outside', 'rey-core' ),
				],
				'prefix_class' => '--carousel-navPos-'
			]
		);

		$element->add_responsive_control(
			'carousel_arrows_distance',
			[
				'label' => esc_html__( 'Distance from edge', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => -100,
				'max' => 200,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .reyEl-productGrid-carouselNav .rey-arrowSvg' => '--nav-distance: {{VALUE}}px',
				],
			]
		);

		$element->add_control(
			'carousel_arrows_show_on_hover',
			[
				'label' => esc_html__( 'Show on hover only', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
				'prefix_class' => '--show-on-hover-'
			]
		);

		$element->add_control(
			'carousel_arrows_hide_on',
			[
				'label' => esc_html__( 'Hide on', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'default' => [],
				'multiple' => true,
				'options' => [
					'lg'  => esc_html__( 'Desktop', 'rey-core' ),
					'md'  => esc_html__( 'Tablet', 'rey-core' ),
					'sm'  => esc_html__( 'Mobile', 'rey-core' ),
				],
			]
		);

		$element->add_control(
			'carousel_arrows_type',
			[
				'label' => esc_html__( 'Arrows Type', 'rey-core' ),
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
			'carousel_arrows_custom_icon',
			[
				'label' => __( 'Custom Icon (Right)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::ICONS,
				'condition' => [
					'carousel_arrows_type' => 'custom',
				],
			]
		);

		$element->add_control(
			'carousel_arrows_size',
			[
				'label' => esc_html__( 'Size', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 5,
				'max' => 200,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .reyEl-productGrid-carouselNav .rey-arrowSvg' => 'font-size: {{VALUE}}px',
				],
			]
		);

		$element->add_control(
			'carousel_arrows_height',
			[
				'label' => esc_html__( 'Height', 'rey-core' ) . ' (em)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0.1,
				'max' => 1,
				'step' => 0.1,
				'selectors' => [
					'{{WRAPPER}} .reyEl-productGrid-carouselNav .rey-arrowSvg' => '--arrow-height: {{VALUE}}em',
				],
			]
		);

		$element->add_responsive_control(
			'carousel_arrows_padding',
			[
				'label' => __( 'Padding', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .reyEl-productGrid-carouselNav .rey-arrowSvg' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$element->start_controls_tabs( 'tabs_styles');

			$element->start_controls_tab(
				'tab_default',
				[
					'label' => __( 'Default', 'rey-core' ),
				]
			);

				$element->add_control(
					'carousel_arrows_color',
					[
						'label' => esc_html__( 'Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .reyEl-productGrid-carouselNav .rey-arrowSvg' => 'color: {{VALUE}}',
						],
					]
				);

				$element->add_control(
					'carousel_arrows_bg_color',
					[
						'label' => esc_html__( 'Background Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .reyEl-productGrid-carouselNav .rey-arrowSvg' => 'background-color: {{VALUE}}',
						],
					]
				);

				$element->add_group_control(
					\Elementor\Group_Control_Border::get_type(),
					[
						'name' => 'carousel_arrows_border',
						'selector' => '{{WRAPPER}} .reyEl-productGrid-carouselNav .rey-arrowSvg',
						'responsive' => true,
					]
				);

			$element->end_controls_tab();

			$element->start_controls_tab(
				'tab_hover',
				[
					'label' => __( 'Hover', 'rey-core' ),
				]
			);

				$element->add_control(
					'carousel_arrows_color_hover',
					[
						'label' => esc_html__( 'Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .reyEl-productGrid-carouselNav .rey-arrowSvg:hover' => 'color: {{VALUE}}',
						],
					]
				);

				$element->add_control(
					'carousel_arrows_bg_color_hover',
					[
						'label' => esc_html__( 'Background Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .reyEl-productGrid-carouselNav .rey-arrowSvg:hover' => 'background-color: {{VALUE}}',
						],
					]
				);

				$element->add_control(
					'carousel_arrows_border_color_hover',
					[
						'label' => esc_html__( 'Border Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .reyEl-productGrid-carouselNav .rey-arrowSvg:hover' => 'border-color: {{VALUE}}',
						],
					]
				);

			$element->end_controls_tab();

		$element->end_controls_tabs();

		$element->add_control(
			'carousel_arrows_border_radius',
			[
				'label' => __( 'Border Radius', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .reyEl-productGrid-carouselNav .rey-arrowSvg' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$element->end_controls_section();
	}


	public function loop_start($product_archive)
	{

		wc_set_loop_prop( 'name', 'product_grid_element' );
		wc_set_loop_prop( 'loop', 0 );

		do_action('reycore/woocommerce/loop/before_grid');
		do_action('reycore/woocommerce/loop/before_grid/name=product_grid_element');

		$parent_classes = $product_archive->get_css_classes();

		if( $parent_classes['grid_layout'] === 'rey-wcGrid-metro' ){
			$parent_classes[] = '--prevent-metro';
		}

		$c_wrapper_classes = ['reyEl-productGrid-splide'];
		$c_wrapper_attributes = [];

		$c_attributes = [];
		$c_classes = [
			'--prevent-thumbnail-sliders', // make sure it does not have thumbnail slideshow
			'--prevent-scattered', // make sure scattered is not applied
			'--prevent-masonry', // make sure masonry is not applied
			'splide__list',
		];

		if( $carousel_id = esc_attr( $this->_settings['carousel_id'] ) ){
			$c_wrapper_classes[] =  $carousel_id;
			$c_attributes['data-slider-carousel-id'] = $carousel_id;
		}

		foreach (['desktop', 'tablet', 'mobile'] as $device) {
			if( isset($this->_settings['disable_'.$device]) && '' !== $this->_settings['disable_'.$device] ){
				$c_wrapper_classes[] = '--disable-' . $device;
			}
		}

		if( $side_offset = $this->_settings['carousel_side_offset'] ){
			$c_wrapper_attributes['data-side-offset'] = esc_attr($side_offset);
		}

		$pg_skin = $this->_settings['loop_skin'] ? $this->_settings['loop_skin'] : get_theme_mod('loop_skin', 'basic');
		$c_wrapper_attributes['data-skin'] = esc_attr($pg_skin);

		printf('<div class="splide %1$s" %2$s><div class="splide__track"><ul class="products %3$s" %4$s>',
			implode(' ', $c_wrapper_classes ),
			reycore__implode_html_attributes($c_wrapper_attributes),
			reycore__product_grid_classes( array_merge( $c_classes, $parent_classes ) ),
			reycore__product_grid_attributes($c_attributes, $this->_settings)
		);

	}

	public function loop_end(){

		echo '</ul></div>'; // ul.products -> .splide__track

		$this->render_arrows();

		echo '</div>'; // .splide

		do_action('reycore/woocommerce/loop/after_grid');
		do_action('reycore/woocommerce/loop/after_grid/name=product_grid_element');

	}

	public function render_arrows(){

		if( '' === $this->_settings['carousel_arrows'] ){
			return;
		}

		$classes = [
			'__arrows-' . $this->parent->get_id(),
		];

		if( isset($this->_settings['carousel_arrows_hide_on']) && is_array($this->_settings['carousel_arrows_hide_on']) ){
			foreach ( $this->_settings['carousel_arrows_hide_on'] as $value) {
				$classes[] = '--dnone-' . esc_attr($value);
			}
		}

		printf('<div class="reyEl-productGrid-carouselNav %s">', implode(' ', $classes) );

			$custom_svg_icon = '';

			if( 'custom' === $this->_settings['carousel_arrows_type'] &&
				($custom_icon = $this->_settings['carousel_arrows_custom_icon']) && isset($custom_icon['value']) && !empty($custom_icon['value']) ){
				ob_start();
				\Elementor\Icons_Manager::render_icon( $custom_icon, [ 'aria-hidden' => 'true', 'class' => '' ] );
				$custom_svg_icon = ob_get_clean();
			}

			reycore__svg_arrows([
				'type' => $this->_settings['carousel_arrows_type'],
				'custom_icon' => $custom_svg_icon,
				'attributes' => [
					'left' => 'data-dir="<"',
					'right' => 'data-dir=">"',
				]
			]);

		echo '</div>';

	}

	/**
	 * Render widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function render() {

		reycore_assets()->add_scripts( ['reycore-woocommerce', 'reycore-widget-product-grid-scripts'] );

		$this->_settings = $this->parent->get_settings_for_display();

		if( ! class_exists('\ReyCore\WooCommerce\Tags\ProductArchive') ){
			return;
		}

		$pg_skin = $this->_settings['loop_skin'] ? $this->_settings['loop_skin'] : get_theme_mod('loop_skin', 'basic');
		$this->parent->add_render_attribute('_wrapper', 'class', 'pg-skin-' . esc_attr($pg_skin));

		$carousel_config = [
			'type' => $this->_settings['effect'] === 'fade' ? 'fade' : 'slide',
			'slides_to_show' => $this->_settings['slides_to_show'] ? $this->_settings['slides_to_show'] : reycore_wc_get_columns('desktop'),
			'slides_to_show_tablet' => isset($this->_settings['slides_to_show_tablet']) && $this->_settings['slides_to_show_tablet'] ? $this->_settings['slides_to_show_tablet'] : reycore_wc_get_columns('tablet'),
			'slides_to_show_mobile' => isset($this->_settings['slides_to_show_mobile']) && $this->_settings['slides_to_show_mobile'] ? $this->_settings['slides_to_show_mobile'] : reycore_wc_get_columns('mobile'),
			'slides_to_move' => (isset($this->_settings['slides_to_move']) && $per_move = $this->_settings['slides_to_move']) ? $per_move : false,
			'autoplay' => $this->_settings['autoplay'] === 'yes',
			'autoplaySpeed' => $this->_settings['autoplay_speed'],
			'pause_on_hover' => $this->_settings['pause_on_hover'],
			'infinite' => $this->_settings['infinite'] === 'yes',
			'infinite_tablet' => isset($this->_settings['infinite_tablet']) && $this->_settings['infinite_tablet'] === 'yes',
			'infinite_mobile' => isset($this->_settings['infinite_mobile']) && $this->_settings['infinite_mobile'] === 'yes',
			'speed' => $this->_settings['speed'],
			'direction' => $this->_settings['direction'],
			'carousel_padding' => $this->_settings['carousel_padding'],
			'carousel_padding_tablet' => isset($this->_settings['carousel_padding_tablet']) && ($c_padding_tablet = $this->_settings['carousel_padding_tablet']) ? $c_padding_tablet : [],
			'carousel_padding_mobile' => isset($this->_settings['carousel_padding_mobile']) && ($c_padding_mobile = $this->_settings['carousel_padding_mobile']) ? $c_padding_mobile : [],
			'delayInit' => $this->_settings['delay_init'],
			'customArrows' => $this->_settings['carousel_arrows'] !== '' ? '.__arrows-' . $this->parent->get_id() : '',
			'free_drag' => $this->_settings['carousel_free_drag'],
			'side_offset' => $this->_settings['carousel_side_offset'],
		];

		$args = [
			'name'        => 'product_grid_element',
			'filter_name' => 'product_grid',
			'main_class'  => 'reyEl-productGrid',
			'el_instance' => $this->parent,
			'attributes'  => [
				'data-carousel-settings' => wp_json_encode( apply_filters('reycore/elementor/product_grid/carousel_settings', $carousel_config, $this) )
			]
		];

		$product_archive = new \ReyCore\WooCommerce\Tags\ProductArchive( $args, $this->_settings );

		if( $product_archive->lazy_start() ){
			return;
		}

		$styles_to_load = [
			'rey-wc-general',
			'rey-wc-general-deferred',
			$this->parent->get_style_name(),
			$this->parent->get_style_name('carousel'),
			'rey-splide',
		];

		if( isset($this->_settings['carousel_side_offset']) && '' !== $this->_settings['carousel_side_offset'] ){
			$styles_to_load[] = $this->parent->get_style_name('carousel-offset');
		}

		reycore_assets()->add_styles($styles_to_load);
		reycore_assets()->add_scripts( ['splidejs', 'rey-splide'] );

		if ( ($query_results = (array) $product_archive->get_query_results()) &&
			isset($query_results['ids']) && ! empty($query_results['ids']) ) {

			$product_archive->render_start();

				$this->loop_start($product_archive);

					$product_archive->render_products();

				$this->loop_end();

			$product_archive->render_end();
		}

		else {

			$show_template = true;

			if( isset($this->_settings['hide_empty_template']) && '' !== $this->_settings['hide_empty_template'] ){
				$show_template = false;
			}

			if( $show_template ){
				/**
				 * Hook: woocommerce_no_products_found.
				 *
				 * @hooked wc_no_products_found - 10
				 */
				do_action( 'woocommerce_no_products_found' );
			}
		}

		$product_archive->lazy_end();

	}

}
