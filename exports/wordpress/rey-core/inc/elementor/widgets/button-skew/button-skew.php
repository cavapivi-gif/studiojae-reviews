<?php
namespace ReyCore\Elementor\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ButtonSkew extends \ReyCore\Elementor\WidgetsBase {

	public static function get_rey_config(){
		return [
			'id' => 'button-skew',
			'title' => __( 'Button - Skew', 'rey-core' ),
			'icon' => 'eicon-button',
			'categories' => [ 'rey-theme' ],
			'keywords' => [],
			'css' => [
				'assets/style[rtl].css',
			],
			'js' => [
				'assets/script.js',
			],
		];
	}

	public function get_custom_help_url() {
		return reycore__support_url('kb/rey-elements/#button-skew');
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
			'style',
			[
				'label' => __( 'Button Style', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'filled',
				'options' => [
					'filled'  => __( 'Filled', 'rey-core' ),
					'filled-left'  => __( 'Filled Left', 'rey-core' ),
					'filled-right'  => __( 'Filled Right', 'rey-core' ),
					'outline'  => __( 'Outline', 'rey-core' ),
					'outline-left'  => __( 'Outline Left', 'rey-core' ),
					'outline-right'  => __( 'Outline Right', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'text',
			[
				'label' => __( 'Text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true,
				],
				'default' => __( 'Click here', 'rey-core' ),
				'placeholder' => __( 'Click here', 'rey-core' ),
			]
		);

		$this->add_control(
			'link',
			[
				'label' => __( 'Link', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::URL,
				'dynamic' => [
					'active' => true,
				],
				'placeholder' => __( 'https://your-link.com', 'rey-core' ),
				'default' => [
					'url' => '#',
				],
			]
		);

		$this->add_responsive_control(
			'align',
			[
				'label' => __( 'Alignment', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'left'    => [
						'title' => __( 'Left', 'rey-core' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'rey-core' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => __( 'Right', 'rey-core' ),
						'icon' => 'eicon-text-align-right',
					],
					'justify' => [
						'title' => __( 'Justified', 'rey-core' ),
						'icon' => 'eicon-text-align-justify',
					],
				],
				'prefix_class' => 'elementor%s-align-',
				'default' => '',
			]
		);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Button', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'typography',
				'selector' => '{{WRAPPER}} a.rey-buttonSkew',
			]
		);

		$this->start_controls_tabs( 'tabs_button_style' );

		$this->start_controls_tab(
			'tab_button_normal',
			[
				'label' => __( 'Normal', 'rey-core' ),
			]
		);

		$this->add_control(
			'button_text_color',
			[
				'label' => __( 'Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .buttonSkew-center span' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'background_color',
			[
				'label' => __( 'Background Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'global' => [
					'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Colors::COLOR_ACCENT,
				],
				'selectors' => [
					'{{WRAPPER}} a.rey-buttonSkew' => 'color: {{VALUE}};',
				],
				'condition' => [
					'style' => ['filled', 'filled-left', 'filled-right'],
				],
			]
		);

		$this->add_control(
			'border_color',
			[
				'label' => __( 'Border Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'global' => [
					'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Colors::COLOR_ACCENT,
				],
				'selectors' => [
					'{{WRAPPER}} a.rey-buttonSkew' => 'color: {{VALUE}};',
				],
				'condition' => [
					'style' => ['outline', 'outline-left', 'outline-right'],
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_button_hover',
			[
				'label' => __( 'Hover', 'rey-core' ),
			]
		);

		$this->add_control(
			'hover_color',
			[
				'label' => __( 'Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} a.rey-buttonSkew:hover .buttonSkew-center span, {{WRAPPER}} a.rey-buttonSkew:focus .buttonSkew-center span' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_background_hover_color',
			[
				'label' => __( 'Background Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} a.rey-buttonSkew:hover, {{WRAPPER}} a.rey-buttonSkew:focus' => 'color: {{VALUE}};',
				],
				'condition' => [
					'style' => ['filled', 'filled-left', 'filled-right'],
				],
			]
		);

		$this->add_control(
			'border_color_hover',
			[
				'label' => __( 'Border Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'global' => [
					'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Colors::COLOR_ACCENT,
				],
				'selectors' => [
					'{{WRAPPER}} a.rey-buttonSkew:hover' => 'color: {{VALUE}};',
				],
				'condition' => [
					'style' => ['outline', 'outline-left', 'outline-right'],
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'border_width',
			[
				'label' => __( 'Border Width', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} a.rey-buttonSkew.rey-buttonSkew--outline .buttonSkew-left:before, {{WRAPPER}} a.rey-buttonSkew.rey-buttonSkew--outline-left .buttonSkew-left:before, {{WRAPPER}} a.rey-buttonSkew.rey-buttonSkew--outline-right .buttonSkew-left:before, {{WRAPPER}} a.rey-buttonSkew.rey-buttonSkew--outline .buttonSkew-right:before, {{WRAPPER}} a.rey-buttonSkew.rey-buttonSkew--outline-left .buttonSkew-right:before, {{WRAPPER}} a.rey-buttonSkew.rey-buttonSkew--outline-right .buttonSkew-right:before' => 'border-width: {{VALUE}}px;',
					'{{WRAPPER}} a.rey-buttonSkew.rey-buttonSkew--outline .buttonSkew-center, {{WRAPPER}} a.rey-buttonSkew.rey-buttonSkew--outline-left .buttonSkew-center, {{WRAPPER}} a.rey-buttonSkew.rey-buttonSkew--outline-right .buttonSkew-center' => 'border-top-width: {{VALUE}}px; border-bottom-width: {{VALUE}}px;',
				],
				'condition' => [
					'style' => ['outline', 'outline-left', 'outline-right'],
				],
			]
		);

		$this->add_control(
			'border_radius',
			[
				'label' => __( 'Border Radius', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} a.rey-buttonSkew .buttonSkew-left:before' => 'border-top-left-radius: {{VALUE}}px; border-bottom-left-radius: {{VALUE}}px;',
					'{{WRAPPER}} a.rey-buttonSkew .buttonSkew-right:before' => 'border-top-right-radius: {{VALUE}}px; border-bottom-right-radius: {{VALUE}}px;',
				],
			]
		);

		$this->add_responsive_control(
			'text_padding',
			[
				'label' => __( 'Padding', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} a.rey-buttonSkew .buttonSkew-center' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'separator' => 'before',
			]
		);

		$this->end_controls_section();
	}


	public function render_start(){

		$settings = $this->get_settings_for_display();

		$this->add_render_attribute( 'wrapper', 'class', 'rey-buttonSkew' );
		$this->add_render_attribute( 'wrapper', 'class', 'rey-buttonSkew--' . $settings['style'] );

		if ( ! empty( $settings['link']['url'] ) ) {
			$this->add_render_attribute( 'wrapper', 'href', $settings['link']['url'] );

			if ( $settings['link']['is_external'] ) {
				$this->add_render_attribute( 'wrapper', 'target', '_blank' );
			}

			if ( $settings['link']['nofollow'] ) {
				$this->add_render_attribute( 'wrapper', 'rel', 'nofollow' );
			}

			if ( ! empty( $settings['link']['custom_attributes'] ) ) {
				$custom_attributes = \Elementor\Utils::parse_custom_attributes( $settings['link']['custom_attributes'] );
				$this->add_render_attribute( 'wrapper', $custom_attributes );
			}

		}

		?>
		<a <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
		<?php
	}

	public function render_end(){
		?>
		</a>
		<?php
	}

	protected function render() {

		reycore_assets()->add_styles($this->get_style_name());

		$this->render_start();
		$text = $this->get_settings_for_display('text');
		?>

		<span class="buttonSkew-left"></span>
		<span class="buttonSkew-center">
			<span><?php echo $text; ?></span>
		</span>
		<span class="buttonSkew-right"></span>

		<?php
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
