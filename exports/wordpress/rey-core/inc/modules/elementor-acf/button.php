<?php
namespace ReyCore\Modules\ElementorAcf;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Button extends \Elementor\Widget_Base {

	public function get_name() {
		return 'reycore-acf-button';
	}

	public function get_title() {
		return __( 'Button', 'rey-core' );
	}

	public function get_icon() {
		return 'rey-editor-icons --acf-button';
	}

	public function get_categories() {
		return [ 'rey-acf' ];
	}

	public function show_in_panel() {
		return class_exists('\ACF') && (bool) reycore__get_purchase_code();
	}

	public function get_keywords() {
		return [ 'acf', 'button', 'link' ];
	}
	// public function get_custom_help_url() {
	// 	return '';
	// }

	protected function register_controls() {

		$this->start_controls_section(
			'section_button',
			[
				'label' => __( 'Settings', 'rey-core' ),
			]
		);

			$this->add_control(
				'button_type',
				[
					'label' => __( 'Type', 'elementor' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						'' => __( 'Default', 'elementor' ),
						'info' => __( 'Info', 'elementor' ),
						'success' => __( 'Success', 'elementor' ),
						'warning' => __( 'Warning', 'elementor' ),
						'danger' => __( 'Danger', 'elementor' ),
					],
					'prefix_class' => 'elementor-button-',
				]
			);

			$this->add_control(
				'acf_field',
				[
					'label' => esc_html__( 'Select Text Field', 'rey-core' ),
					'description' => esc_html__( 'Choose the text field to pull.', 'rey-core' ),
					'default' => '',
					'label_block' => true,
					'type' => 'rey-query',
					'query_args' => [
						'type' => 'acf',
						'format' => '%key%:%name%',
						'field_types' => [
							'text',
							'textarea',
							'number',
							'email',
							'url',
						],
					],
				]
			);

			$this->add_control(
				'fallback',
				[
					'label' => esc_html__( 'Add fallback?', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

			$this->add_control(
				'fallback_text',
				[
					'label' => 'Fallback text',
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => 'Lorem ipsum dolor sit amet',
					'label_block' => true,
					'condition' => [
						'fallback!' => '',
					],
				]
			);

			$this->add_control(
				'link',
				[
					'label' => __( 'Link', 'elementor' ),
					'type' => \Elementor\Controls_Manager::URL,
					'dynamic' => [
						'active' => true,
					],
					'default' => [
						'url' => '',
					],
					'separator' => 'before',
				]
			);

			$this->add_control(
				'link_acf_field',
				[
					'label' => esc_html__( 'ACF Link', 'rey-core' ),
					'description' => esc_html__( 'Choose the link field to pull.', 'rey-core' ),
					'default' => '',
					'label_block' => true,
					'type' => 'rey-query',
					'query_args' => [
						'type' => 'acf',
						'format' => '%key%:%name%',
						'field_types' => [
							'text',
							'url',
						],
					],
				]
			);

			$this->add_control(
				'size',
				[
					'label' => __( 'Size', 'elementor' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'sm',
					'options' => [
						'xs' => __( 'Extra Small', 'elementor' ),
						'sm' => __( 'Small', 'elementor' ),
						'md' => __( 'Medium', 'elementor' ),
						'lg' => __( 'Large', 'elementor' ),
						'xl' => __( 'Extra Large', 'elementor' ),
					],
					'style_transfer' => true,
				]
			);

			$this->add_control(
				'selected_icon',
				[
					'label' => __( 'Icon', 'elementor' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'fa4compatibility' => 'icon',
					'skin' => 'inline',
					'label_block' => false,
				]
			);

			$this->add_control(
				'icon_align',
				[
					'label' => __( 'Icon Position', 'elementor' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'left',
					'options' => [
						'left' => __( 'Before', 'elementor' ),
						'right' => __( 'After', 'elementor' ),
					],
					'condition' => [
						'selected_icon[value]!' => '',
					],
				]
			);

			$this->add_control(
				'icon_indent',
				[
					'label' => __( 'Icon Spacing', 'elementor' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'range' => [
						'px' => [
							'max' => 50,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .elementor-button .elementor-align-icon-right' => 'margin-left: {{SIZE}}{{UNIT}};',
						'{{WRAPPER}} .elementor-button .elementor-align-icon-left' => 'margin-right: {{SIZE}}{{UNIT}};',
					],
				]
			);


			$this->add_responsive_control(
				'align',
				[
					'label' => __( 'Alignment', 'elementor' ),
					'type' => \Elementor\Controls_Manager::CHOOSE,
					'options' => [
						'left'    => [
							'title' => __( 'Left', 'elementor' ),
							'icon' => 'eicon-text-align-left',
						],
						'center' => [
							'title' => __( 'Center', 'elementor' ),
							'icon' => 'eicon-text-align-center',
						],
						'right' => [
							'title' => __( 'Right', 'elementor' ),
							'icon' => 'eicon-text-align-right',
						],
						'justify' => [
							'title' => __( 'Justified', 'elementor' ),
							'icon' => 'eicon-text-align-justify',
						],
					],
					'prefix_class' => 'elementor%s-align-',
					'default' => '',
				]
			);

			$this->add_control(
				'button_css_id',
				[
					'label' => __( 'Button ID', 'elementor' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'dynamic' => [
						'active' => true,
					],
					'default' => '',
					'title' => __( 'Add your custom id WITHOUT the Pound key. e.g: my-id', 'elementor' ),
					'description' => __( 'Please make sure the ID is unique and not used elsewhere on the page this form is displayed. This field allows <code>A-z 0-9</code> & underscore chars without spaces.', 'elementor' ),
					'separator' => 'before',
					'wpml' => false,
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Styles', 'elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'typography',
					'global' => [
						'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Typography::TYPOGRAPHY_ACCENT,
					],
					'selector' => '{{WRAPPER}} .elementor-button',
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Text_Shadow::get_type(),
				[
					'name' => 'text_shadow',
					'selector' => '{{WRAPPER}} .elementor-button',
				]
			);

			$this->start_controls_tabs( 'tabs_button_style' );

			$this->start_controls_tab(
				'tab_button_normal',
				[
					'label' => __( 'Normal', 'elementor' ),
				]
			);

			$this->add_control(
				'button_text_color',
				[
					'label' => __( 'Text Color', 'elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'default' => '',
					'selectors' => [
						'{{WRAPPER}} .elementor-button' => 'fill: {{VALUE}}; color: {{VALUE}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Background::get_type(),
				[
					'name' => 'background',
					'label' => __( 'Background', 'elementor' ),
					'types' => [ 'classic', 'gradient' ],
					'exclude' => [ 'image' ],
					'selector' => '{{WRAPPER}} .elementor-button',
					'fields_options' => [
						'background' => [
							'default' => 'classic',
						],
						'color' => [
							'global' => [
								'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Colors::COLOR_ACCENT,
							],
						],
					],
				]
			);

			$this->end_controls_tab();

			$this->start_controls_tab(
				'tab_button_hover',
				[
					'label' => __( 'Hover', 'elementor' ),
				]
			);

			$this->add_control(
				'hover_color',
				[
					'label' => __( 'Text Color', 'elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .elementor-button:hover, {{WRAPPER}} .elementor-button:focus' => 'color: {{VALUE}};',
						'{{WRAPPER}} .elementor-button:hover svg, {{WRAPPER}} .elementor-button:focus svg' => 'fill: {{VALUE}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Background::get_type(),
				[
					'name' => 'button_background_hover',
					'label' => __( 'Background', 'elementor' ),
					'types' => [ 'classic', 'gradient' ],
					'exclude' => [ 'image' ],
					'selector' => '{{WRAPPER}} .elementor-button:hover, {{WRAPPER}} .elementor-button:focus',
					'fields_options' => [
						'background' => [
							'default' => 'classic',
						],
					],
				]
			);

			$this->add_control(
				'button_hover_border_color',
				[
					'label' => __( 'Border Color', 'elementor' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'condition' => [
						'border_border!' => '',
					],
					'selectors' => [
						'{{WRAPPER}} .elementor-button:hover, {{WRAPPER}} .elementor-button:focus' => 'border-color: {{VALUE}};',
					],
				]
			);

			$this->add_control(
				'hover_animation',
				[
					'label' => __( 'Hover Animation', 'elementor' ),
					'type' => \Elementor\Controls_Manager::HOVER_ANIMATION,
				]
			);

			$this->end_controls_tab();

			$this->end_controls_tabs();

			$this->add_group_control(
				\Elementor\Group_Control_Border::get_type(),
				[
					'name' => 'border',
					'selector' => '{{WRAPPER}} .elementor-button',
					'separator' => 'before',
				]
			);

			$this->add_control(
				'border_radius',
				[
					'label' => __( 'Border Radius', 'elementor' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em' ],
					'selectors' => [
						'{{WRAPPER}} .elementor-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Box_Shadow::get_type(),
				[
					'name' => 'button_box_shadow',
					'selector' => '{{WRAPPER}} .elementor-button',
				]
			);

			$this->add_responsive_control(
				'text_padding',
				[
					'label' => __( 'Padding', 'elementor' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em', '%' ],
					'selectors' => [
						'{{WRAPPER}} .elementor-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
					'separator' => 'before',
				]
			);

		$this->end_controls_section();

	}

	function render_text($settings, $text){

		$this->add_render_attribute( [
			'content-wrapper' => [
				'class' => 'elementor-button-content-wrapper',
			],
			'icon-align' => [
				'class' => [
					'elementor-button-icon',
					'elementor-align-icon-' . $settings['icon_align'],
				],
			],
			'text' => [
				'class' => 'elementor-button-text',
			],
		] );

		?>
		<span <?php echo $this->get_render_attribute_string( 'content-wrapper' ); ?>>
			<?php if ( ! empty( $settings['icon'] ) || ! empty( $settings['selected_icon']['value'] ) ) : ?>
			<span <?php echo $this->get_render_attribute_string( 'icon-align' ); ?>>
				<?php
				\Elementor\Icons_Manager::render_icon( $settings['selected_icon'], [ 'aria-hidden' => 'true' ] ); ?>
			</span>
			<?php endif; ?>
			<span <?php echo $this->get_render_attribute_string( 'text' ); ?>><?php echo $text; ?></span>
		</span>
		<?php
	}

	protected function render() {

		$settings = $this->get_settings_for_display();

		$text = '';

		if( $settings['fallback'] !== '' ){
			$text = $settings['fallback_text'];
		}

		if( $acf_field = $settings['acf_field'] ){
			$text = Base::get_field($acf_field);
		}

		if( ! $text ){
			return;
		}

		$this->add_render_attribute( 'wrapper', 'class', 'elementor-button-wrapper' );

		if( $link_acf_field = $settings['link_acf_field'] ){
			$settings['link']['url'] = Base::get_field($link_acf_field);
		}

		if ( ! empty( $settings['link']['url'] ) ) {
			$this->add_link_attributes( 'button', $settings['link'] );
			$this->add_render_attribute( 'button', 'class', 'elementor-button-link' );
		}

		$this->add_render_attribute( 'button', 'class', 'elementor-button' );
		$this->add_render_attribute( 'button', 'role', 'button' );

		if ( ! empty( $settings['button_css_id'] ) ) {
			$this->add_render_attribute( 'button', 'id', $settings['button_css_id'] );
		}

		if ( ! empty( $settings['size'] ) ) {
			$this->add_render_attribute( 'button', 'class', 'elementor-size-' . $settings['size'] );
		}

		if ( $settings['hover_animation'] ) {
			$this->add_render_attribute( 'button', 'class', 'elementor-animation-' . $settings['hover_animation'] );
		}
		?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
			<a <?php echo $this->get_render_attribute_string( 'button' ); ?>>
				<?php $this->render_text($settings, $text); ?>
			</a>
		</div>
		<?php
	}

}
