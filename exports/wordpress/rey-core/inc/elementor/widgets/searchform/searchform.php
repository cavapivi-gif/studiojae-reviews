<?php
namespace ReyCore\Elementor\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Searchform extends \ReyCore\Elementor\WidgetsBase {

	public $_settings = [];

	public static function get_rey_config(){
		return [
			'id' => 'searchform',
			'title' => __( 'Search Form', 'rey-core' ),
			'icon' => 'eicon-site-search',
			'categories' => [ 'rey-theme' ],
			'keywords' => [],
			'css' => [
				'assets/style[rtl].css',
			],
		];
	}

	// public function get_custom_help_url() {
	// 	return reycore__support_url('kb/rey-elements/#stamp');
	// }

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
				'post_type',
				[
					'label' => esc_html__( 'Post Type to search', 'rey-core' ),
					'default' => class_exists('\WooCommerce') ? 'product' : 'post',
					'type' => 'rey-ajax-list',
					'query_args' => [
						'request' => 'get_post_types_list',
					],
				]
			);

			$this->add_control(
				'placeholder',
				[
					'label' => esc_html__( 'Placeholder', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => esc_html__( 'Search &hellip;', 'rey-core' ),
					'placeholder' => esc_html__( 'eg: Search ..', 'rey-core' ),
				]
			);

			$this->add_control(
				'button',
				[
					'label' => esc_html__( 'Add Button', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
				]
			);

			$this->add_control(
				'button_click',
				[
					'label' => esc_html__( 'Disable Button Click', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'prefix_class' => '--btn-noclick-',
					'condition' => [
						'button!' => '',
					],
				]
			);

			$this->add_control(
				'btn_position',
				[
					'label' => esc_html__( 'Button Position', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'right',
					'options' => [
						'left'  => esc_html__( 'Left', 'rey-core' ),
						'right'  => esc_html__( 'Right', 'rey-core' ),
					],
					'prefix_class' => '--btn-pos-',
					'condition' => [
						'button!' => '',
					],
				]
			);

			$this->add_control(
				'btn_icon',
				[
					'label' => esc_html__( 'Button as icon?', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
					'condition' => [
						'button!' => '',
					],
				]
			);

			$this->add_control(
				'btn_text',
				[
					'label' => esc_html__( 'Button Text', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => esc_html__( 'Search', 'rey-core' ),
					'placeholder' => esc_html__( 'eg: Search', 'rey-core' ),
					'condition' => [
						'button!' => '',
						'btn_icon' => '',
					],
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_form_style',
			[
				'label' => __( 'Form Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_group_control(
				\Elementor\Group_Control_Border::get_type(),
				[
					'name' => 'form_border',
					'selector' => '{{WRAPPER}} .rey-elSearchForm-form',
					'responsive' => true,
				]
			);

			$this->add_responsive_control(
				'form_border_radius',
				[
					'label' => __( 'Border Radius', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em' ],
					'selectors' => [
						'{{WRAPPER}} .rey-elSearchForm-form' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Input Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_control(
				'input_color',
				[
					'label' => esc_html__( 'Input Text Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-elSearchForm-form input[type="search"]' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'input_bg_color',
				[
					'label' => esc_html__( 'Input Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-elSearchForm-form input[type="search"]' => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Border::get_type(),
				[
					'name' => 'input_border',
					'selector' => '{{WRAPPER}} .rey-elSearchForm-form input[type="search"]',
					'responsive' => true,
				]
			);

			$this->add_responsive_control(
				'input_border_radius',
				[
					'label' => __( 'Border Radius', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em' ],
					'selectors' => [
						'{{WRAPPER}} .rey-elSearchForm-form input[type="search"]' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_btn_style',
			[
				'label' => __( 'Button Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'button!' => '',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_items_styles' );

			$this->start_controls_tab(
				'tabs_btn_normal',
				array(
					'label' => esc_html__( 'Normal', 'rey-core' ),
				)
			);

				$this->add_control(
					'btn_color',
					[
						'label' => __( 'Text Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}}  .rey-elSearchForm-form .btn' => 'color: {{VALUE}}',
						],
					]
				);

				$this->add_control(
					'btn_bg_color',
					[
						'label' => __( 'Background Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}}  .rey-elSearchForm-form .btn' => 'background-color: {{VALUE}}',
						],
					]
				);

				$this->add_responsive_control(
					'btn_border_width',
					[
						'label' => __( 'Border Width', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', 'em', '%' ],
						'selectors' => [
							'{{WRAPPER}}  .rey-elSearchForm-form .btn' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_control(
					'btn_border_color',
					[
						'label' => __( 'Border Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}}  .rey-elSearchForm-form .btn' => 'border-color: {{VALUE}};',
						],
					]
				);

			$this->end_controls_tab();

			$this->start_controls_tab(
				'tabs_btn_hover',
				array(
					'label' => esc_html__( 'Active', 'rey-core' ),
				)
			);

				$this->add_control(
					'btn_color_active',
					[
						'label' => __( 'Text Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .rey-elSearchForm-form .btn:hover' => 'color: {{VALUE}}',
						],
					]
				);

				$this->add_control(
					'btn_bg_color_active',
					[
						'label' => __( 'Background Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .rey-elSearchForm-form .btn:hover' => 'background-color: {{VALUE}}',
						],
					]
				);

				$this->add_responsive_control(
					'btn_border_width_active',
					[
						'label' => __( 'Border Width', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', 'em', '%' ],
						'selectors' => [
							'{{WRAPPER}} .rey-elSearchForm-form .btn:hover' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_control(
					'btn_border_color_active',
					[
						'label' => __( 'Border Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .rey-elSearchForm-form .btn:hover' => 'border-color: {{VALUE}};',
						],
					]
				);

			$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_responsive_control(
			'btn_border_radius',
			[
				'label' => __( 'Border Radius', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .rey-elSearchForm-form .btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'btn_width',
			[
				'label' => esc_html__( 'Button Width', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 20,
				'max' => 1000,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .rey-elSearchForm-form .btn' => 'width: {{VALUE}}px',
				],
			]
		);

		$this->add_responsive_control(
			'btn_fz',
			[
				'label' => esc_html__( 'Button Font Size', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 6,
				'max' => 1000,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .rey-elSearchForm-form .btn' => 'font-size: {{VALUE}}px',
				],
			]
		);

		$this->add_responsive_control(
			'btn_distance',
			[
				'label' => esc_html__( 'Button Distance', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 1000,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .rey-elSearchForm-form .btn' => '--btn-margin: {{VALUE}}px',
				],
			]
		);

		$this->end_controls_section();

	}

	public function render_start()
	{
		$classes = [
			'rey-elSearchForm',
		];

		$this->add_render_attribute( 'wrapper', 'class', $classes );
		?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
		<?php
	}

	public function render_end()
	{
		?></div><?php
	}

	function form_settings( $options ){

		$options['button'] = $this->_settings['button'] === 'yes';
		$options['class'] = 'rey-elSearchForm-form';
		$options['placeholder'] = $this->_settings['placeholder'];
		$options['button_icon'] = $this->_settings['btn_icon'] === 'yes';
		$options['button_text'] = $this->_settings['btn_text'];

		if( $post_type = $this->_settings['post_type'] ){
			$options['post_type'] = $post_type;
		}

		return $options;
	}

	protected function render() {

		reycore_assets()->add_styles($this->get_style_name());

		$this->_settings = $this->get_settings_for_display();

		$this->render_start();

			add_filter('rey/search_form/settings', [$this, 'form_settings']);
			get_search_form();
			remove_filter('rey/search_form/settings', [$this, 'form_settings']);

		$this->render_end();
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
