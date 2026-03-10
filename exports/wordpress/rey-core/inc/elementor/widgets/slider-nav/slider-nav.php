<?php
namespace ReyCore\Elementor\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class SliderNav extends \ReyCore\Elementor\WidgetsBase {

	public $_settings = [];

	public static function get_rey_config(){
		return [
			'id' => 'slider-nav',
			'title' => __( 'Slider Navigation', 'rey-core' ),
			'icon' => 'eicon-post-navigation',
			'categories' => [ 'rey-theme' ],
			'keywords' => [],
			'css' => [
				'assets/style.css',
			],
			'js' => [
				'assets/script.js',
			],
		];
	}

	public function rey_get_script_depends() {
		return [ 'reycore-widget-slider-nav-scripts' ];
	}

	public function get_custom_help_url() {
		return reycore__support_url('kb/rey-elements/#slider-navigation');
	}

	protected function register_skins() {
		foreach ([
			'SkinBullets',
		] as $skin) {
			$skin_class = __CLASS__ . '\\' . $skin;
			$this->add_skin( new $skin_class( $this ) );
		}
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
			'section_layout',
			[
				'label' => __( 'Layout', 'rey-core' ),
			]
		);

		$this->add_control(
			'slider_source',
			[
				'label' => esc_html__( 'Source', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'pg',
				'options' => [
					'pg'  => esc_html__( 'Product Grid - Carousel', 'rey-core' ),
					'bp'  => esc_html__( 'Blog Posts - Carousel', 'rey-core' ),
					'carousel'  => esc_html__( 'Carousel', 'rey-core' ),
					'slider'  => esc_html__( 'Slider', 'rey-core' ),
					'tabs'  => esc_html__( 'Tabs', 'rey-core' ),
					'parent'  => esc_html__( 'Parent Section Slideshow', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'slider_id',
			[
				'label' => __( 'Slider Unique ID', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => __( '', 'rey-core' ),
				'description' => __( 'Supported widgets: Carousel, Product Grid - Carousel .', 'rey-core' ),
				'placeholder' => __( 'eg: carousel-a6596db', 'rey-core' ),
				'label_block' => true,
				'condition' => [
					'slider_source' => ['pg', 'carousel', 'slider'],
				],
				'wpml' => false,
			]
		);

		$this->add_control(
			'slider_id__bp',
			[
				'label' => __( 'Slider Unique ID', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => __( '', 'rey-core' ),
				'description' => __( 'Supported widgets: Posts.', 'rey-core' ),
				'placeholder' => __( 'eg: .carousel-5e8448d138e77', 'rey-core' ),
				'label_block' => true,
				'condition' => [
					'slider_source' => 'bp',
				],
				'wpml' => false,
			]
		);

		$this->add_control(
			'tabs_id',
			[
				'label' => __( 'Tabs Unique ID', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => __( '', 'rey-core' ),
				'description' => __( 'Supported widgets: Section as Tab wrapper.', 'rey-core' ),
				'placeholder' => __( 'eg: .tabs-5e8448d138e77', 'rey-core' ),
				'label_block' => true,
				'condition' => [
					'slider_source' => 'tabs',
				],
				'wpml' => false,
			]
		);

		$this->add_control(
			'show_counter',
			[
				'label' => __( 'Show Counter', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
				'condition' => [
					'_skin' => '',
				],
			]
		);

		$this->add_control(
			'nav_lines',
			[
				'label' => esc_html__( 'Navigation Line', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => esc_html__( '- Disabled -', 'rey-core' ),
					'left'  => esc_html__( 'Show - Left', 'rey-core' ),
					'right'  => esc_html__( 'Show - Right', 'rey-core' ),
				],
				'condition' => [
					'_skin' => '',
				],
			]
		);

		// $this->add_control(
		// 	'hide_empty',
		// 	[
		// 		'label' => __( 'Hide when no results', 'rey-core' ),
		// 		'type' => \Elementor\Controls_Manager::SWITCHER,
		// 		'default' => '',
		// 		'condition' => [
		// 			'_skin' => '',
		// 		],
		// 	]
		// );

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Style', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'color',
			[
				'label' => __( 'Primary Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-sliderNav' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'color_hover',
			[
				'label' => __( 'Primary Hover Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-arrowSvg:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'align',
			[
				'label' => __( 'Horizontal Align', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					'' => __( 'Default', 'rey-core' ),
					'flex-start' => __( 'Start', 'rey-core' ),
					'center' => __( 'Center', 'rey-core' ),
					'flex-end' => __( 'End', 'rey-core' ),
					'space-between' => __( 'Space Between', 'rey-core' ),
					'space-around' => __( 'Space Around', 'rey-core' ),
					'space-evenly' => __( 'Space Evenly', 'rey-core' ),
				],
				'selectors' => [
					'{{WRAPPER}} .rey-sliderNav' => 'justify-content: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'bullets_style',
			[
				'label' => __( 'Bullets Style', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'lines',
				'options' => [
					'lines'  => __( 'Lines', 'rey-core' ),
					'dots'  => __( 'Dots', 'rey-core' ),
					'circles-ext'  => __( 'Dots & Circles', 'rey-core' ),
				],
				'condition' => [
					'_skin' => 'bullets',
				],
			]
		);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_arrows_styles',
			[
				'label' => __( 'Arrows Styles', 'rey-core' ),
				'condition' => [
					'_skin' => '',
				],
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'arrows_type',
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

		$this->add_control(
			'arrows_custom_icon',
			[
				'label' => __( 'Custom Arrow Icon (Right)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::ICONS,
				'condition' => [
					'arrows_type' => 'custom',
				],
			]
		);

		$this->add_responsive_control(
			'arrows_size',
			[
				'label' => esc_html__( 'Arrows size', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 5,
				'max' => 200,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}}' => '--arrow-size: {{VALUE}}px',
				],
			]
		);

		$this->add_control(
			'arrows_height',
			[
				'label' => esc_html__( 'Arrows height', 'rey-core' ) . ' (em)',
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0.1,
				'max' => 1,
				'step' => 0.1,
				'selectors' => [
					'{{WRAPPER}}' => '--arrow-height: {{VALUE}}em',
				],
			]
		);

		$this->add_responsive_control(
			'arrows_padding',
			[
				'label' => __( 'Arrows Padding', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .rey-arrowSvg' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_styles');

			$this->start_controls_tab(
				'tab_default',
				[
					'label' => __( 'Default', 'rey-core' ),
				]
			);

				$this->add_control(
					'arrows_color',
					[
						'label' => esc_html__( 'Arrows Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .rey-arrowSvg' => 'color: {{VALUE}}',
						],
					]
				);

				$this->add_control(
					'arrows_bg_color',
					[
						'label' => esc_html__( 'Arrows Background Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .rey-arrowSvg' => 'background-color: {{VALUE}}',
						],
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Border::get_type(),
					[
						'name' => 'arrows_border',
						'selector' => '{{WRAPPER}} .rey-arrowSvg',
						'responsive' => true,
					]
				);

			$this->end_controls_tab();

			$this->start_controls_tab(
				'tab_hover',
				[
					'label' => __( 'Hover', 'rey-core' ),
				]
			);

				$this->add_control(
					'arrows_color_hover',
					[
						'label' => esc_html__( 'Arrows Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .rey-arrowSvg:hover' => 'color: {{VALUE}}',
						],
					]
				);

				$this->add_control(
					'arrows_bg_color_hover',
					[
						'label' => esc_html__( 'Arrows Background Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .rey-arrowSvg:hover' => 'background-color: {{VALUE}}',
						],
					]
				);

				$this->add_control(
					'arrows_border_color_hover',
					[
						'label' => esc_html__( 'Arrows Border Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .rey-arrowSvg:hover' => 'border-color: {{VALUE}}',
						],
					]
				);

			$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'arrows_border_radius',
			[
				'label' => __( 'Border Radius', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .rey-arrowSvg' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	public function render_start(){

		$attributes['class'] = [
			'rey-sliderNav',
		];

		$attributes['data-type'] = esc_attr($this->_settings['_skin'] == 'bullets' ? 'bullets' : 'arrows');

		if( $this->_settings['_skin'] == 'bullets' ) {

			$attributes['class'][] = 'splide__pagination';

			$map = [
				'dots'  => 'circles',
				'lines'  => 'bars',
			];

			$attributes['data-style'] = isset($map[ $this->_settings['bullets_style'] ]) ? $map[ $this->_settings['bullets_style'] ] : $this->_settings['bullets_style'];

		}

		if( ! reycore__elementor_edit_mode() ){
			$attributes['class'][] = '--hidden';
		}

		$attributes['data-slider-source'] = esc_attr($this->_settings['slider_source']);

		if( $this->_settings['slider_source'] !== 'parent' ){

			$source_id = $this->_settings['slider_id'];

			if( $this->_settings['slider_source'] === 'bp' ){
				$source_id = $this->_settings['slider_id__bp'];
			}

			if( $this->_settings['slider_source'] === 'tabs' ){
				$source_id = $this->_settings['tabs_id'];
			}

			$attributes['data-slider-id'] = esc_attr($source_id);
		}

		$this->add_render_attribute( 'wrapper', $attributes );

		?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>

		<?php
		add_filter('rey/svg_arrow_markup', [$this, 'custom_icon']);

	}

	function custom_icon( $html ){

		$custom_svg_icon = '';

		if( 'custom' === $this->_settings['arrows_type'] &&
			($custom_icon = $this->_settings['arrows_custom_icon']) && isset($custom_icon['value']) && !empty($custom_icon['value']) ){
			ob_start();
			\Elementor\Icons_Manager::render_icon( $custom_icon, [ 'aria-hidden' => 'true', 'class' => '' ] );
			return ob_get_clean();
		}
		else if( 'chevron' === $this->_settings['arrows_type'] ){
			return '<svg viewBox="0 0 40 64" xmlns="http://www.w3.org/2000/svg"><polygon fill="currentColor" points="39.5 32 6.83 64 0.5 57.38 26.76 32 0.5 6.62 6.83 0"></polygon></svg>';
		}

		return $html;
	}

	public function render_end(){

		if( '' === $this->_settings['_skin'] && '' !== $this->_settings['nav_lines'] ){
			$this->render_nav_lines();
		}

		?></div><?php
		remove_filter('rey/svg_arrow_markup', [$this, 'custom_icon']);
	}

	public function render_counter(){
		if( $this->_settings['show_counter'] === 'yes' ): ?>
			<div class="rey-sliderNav-counter">
				<span class="rey-sliderNav-counterCurrent"></span>
				<span class="rey-sliderNav-counterSeparator">&mdash;</span>
				<span class="rey-sliderNav-counterTotal"></span>
			</div>
		<?php endif;
	}

	public function render_nav_lines(){
		?><div class="rey-sliderNav-lines --<?php echo esc_attr($this->_settings['nav_lines']) ?>"></div><?php
	}

	protected function render() {

		reycore_assets()->add_deferred_styles($this->get_style_name());

		$this->_settings = $this->get_settings_for_display();

		$this->render_start();

		echo reycore__arrowSvg(false);

		$this->render_counter();

		echo reycore__arrowSvg();

		$this->render_end();

		reycore_assets()->add_scripts( $this->rey_get_script_depends() );

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
