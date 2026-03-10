<?php
namespace ReyCore\Elementor\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


class SocialSharing extends \ReyCore\Elementor\WidgetsBase {

	public static function get_rey_config(){
		return [
			'id' => 'social-sharing',
			'title' => __( 'Social Sharing', 'rey-core' ),
			'icon' => 'eicon-share',
			'categories' => [ 'rey-theme' ],
			'keywords' => ['share', 'facebook', 'twitter', 'instagram'],
		];
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

			$share_icons = new \Elementor\Repeater();

			$share_icons->add_control(
				'icon',
				[
					'label' => __( 'Select icon', 'rey-core' ),
					'label_block' => true,
					'default' => '',
					'type' => 'rey-ajax-list',
					'query_args' => [
						'request' => 'get_social_share_icons',
					],
				]
			);

			$this->add_control(
				'icons',
				[
					'label' => __( 'Items', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::REPEATER,
					'fields' => $share_icons->get_controls(),
					'default' => [
						[
							'icon' => 'facebook-f'
						],
						[
							'icon' => 'twitter'
						],
						[
							'icon' => 'linkedin'
						],
						[
							'icon' => 'pinterest-p'
						],
						[
							'icon' => 'mail'
						],
					],
					'prevent_empty' => true,
				]
			);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$selectors = [
				'main' => '{{WRAPPER}} .rey-postSocialShare',
				'item' => '{{WRAPPER}} .rey-postSocialShare a',
			];

			$this->add_control(
				'layout',
				[
					'label' => esc_html__( 'Layout', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						'' => esc_html__( 'Default (Colored)', 'rey-core' ),
						'round_c' => esc_html__( 'Colored Rounded', 'rey-core' ),
						'minimal' => esc_html__( 'Minimal', 'rey-core' ),
						'round_m' => esc_html__( 'Minimal Rounded', 'rey-core' ),
					],
				]
			);

			$this->add_control(
				'size',
				[
					'label' => esc_html__( 'Icon size', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 0,
					'selectors' => [
						$selectors['item'] => 'font-size: {{VALUE}}px;',
					],
				]
			);

			$this->add_control(
				'spacing',
				[
					'label' => esc_html__( 'Spacing', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 0,
					'selectors' => [
						$selectors['main'] => '--icons-spacing: {{VALUE}}px;',
					],
				]
			);

			$this->add_control(
				'radius',
				[
					'label' => esc_html__( 'Border Radius', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 0,
					'selectors' => [
						$selectors['item'] => 'border-radius: {{VALUE}}px;',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Border::get_type(),
				[
					'name' => 'border',
					'selector' => $selectors['item'],
				]
			);

			$this->add_responsive_control(
				'padding',
				[
					'label' => __( 'Padding', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em', '%' ],
					'selectors' => [
						$selectors['item'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_control(
				'color',
				[
					'label' => esc_html__( 'Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selectors['item'] => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'bg_color',
				[
					'label' => esc_html__( 'Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selectors['item'] => 'background-color: {{VALUE}}',
					],
				]
			);

		$this->end_controls_section();
	}

	/**
	 * Render form widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {

		$settings = $this->get_settings_for_display();

		$this->add_render_attribute( 'wrapper', 'class', [
			'rey-element',
			'rey-elSharing'
		] );

		?>

		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>

		<?php

		$classes = [];

		$style = $settings['layout'];
		$is_colored = $style === '' || $style === 'round_c';

		if( $style ){
			$classes[] = '--' . $style;
		}

		reycore__socialShare([
			'class' => implode(' ', $classes),
			'colored' => $is_colored,
			'share_items' => wp_list_pluck($settings['icons'], 'icon')
		]);

		?></div><?php
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
