<?php
namespace ReyCore\Elementor\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
exit; // Exit if accessed directly.
}

class Cf7 extends \ReyCore\Elementor\WidgetsBase {

	public static function get_rey_config(){
		return [
			'id' => 'cf7',
			'title' => __( 'Contact Form', 'rey-core' ),
			'icon' => 'eicon-mail',
			'categories' => [ 'rey-theme' ],
			'keywords' => ['mail', 'contact', 'form'],
			'css' => [
				'assets/style[rtl].css',
			],
		];
	}

	public function get_custom_help_url() {
		return reycore__support_url('kb/rey-elements/#contact-form');
	}

	public function on_export($element)
	{
		unset(
			$element['settings']['form_id']
		);

		return $element;
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

		$this->add_control(
			'important_note',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => sprintf(__( 'Create contact forms in <a href="%s" target="_blank">Contact Form 7</a> plugin. Here\'s <a href="%s" target="_blank">an article</a> where you can find generic HTML code to add into the Contact Form, to style it', 'rey-core' ), admin_url('admin.php?page=wpcf7'), reycore__support_url('kb/how-to-style-contact-form-7-forms-html/')),
				'content_classes' => 'elementor-descriptor',
				'condition' => [
					'form_id' => '',
				],
			]
		);

		// form id
		$this->add_control(
			'form_id',
			[
				'label' => __( 'Select form', 'rey-core' ),
				'default' => '',
				'type' => 'rey-ajax-list',
				'query_args' => [
					'request' => 'get_cf7_forms',
					'export' => 'id',
				],
			]
		);

		$this->end_controls_section();


		// $this->start_controls_section(
		// 	'section_style',
		// 	[
		// 		'label' => __( 'Style', 'rey-core' ),
		// 		'tab' => \Elementor\Controls_Manager::TAB_STYLE,
		// 	]
		// );

		// $this->end_controls_section();

		$this->start_controls_section(
			'section_input_style',
			[
				'label' => __( 'Input Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_control(
				'form_style',
				[
					'label' => __( 'Form Style', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::HIDDEN,
					'default' => 'basic',
				]
			);

			$selectors = [
				'rows' => '{{WRAPPER}} .wpcf7-form > p, {{WRAPPER}} .wpcf7-form > div',
				'label' => '{{WRAPPER}} .wpcf7-form label',
				'submit' => '{{WRAPPER}} .wpcf7-form .wpcf7-submit',
				'submit_hover' => '{{WRAPPER}} .wpcf7-form .wpcf7-submit:hover',
			];

			$styles = [
				'input[type="text"]',
				'input[type="number"]',
				'input[type="tel"]',
				'input[type="email"]',
				'input[type="password"]',
				'input[type="search"]',
				'select',
				'textarea'
			];

			foreach ([
				'default' => '',
				'hover' => ':hover',
				'focus' => ':focus'
			] as $key => $state) {

				$_s = [];

				foreach ($styles as $value) {
					$_s[] = '{{WRAPPER}} ' . $value . $state;
				}

				$selectors['input_' . $key] = implode(',', $_s);
			}

			$this->start_controls_tabs( 'tabs_input_styles' );

				$this->start_controls_tab(
					'tab_input_normal',
					[
						'label' => __( 'Normal', 'rey-core' ),
					]
				);

					$this->add_control(
						'input_text_color',
						[
							'label' => __( 'Text Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'default' => '',
							'selectors' => [
								$selectors['input_default'] => 'color: {{VALUE}};',
							],
						]
					);

					$this->add_control(
						'input_bg_color',
						[
							'label' => __( 'Background Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								$selectors['input_default'] => 'background-color: {{VALUE}}',
							],
						]
					);

					$this->add_responsive_control(
						'input_border_width',
						[
							'label' => __( 'Border Width', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::DIMENSIONS,
							'size_units' => [ 'px', 'em', '%' ],
							'selectors' => [
								$selectors['input_default'] => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'input_border_color',
						[
							'label' => __( 'Border Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								$selectors['input_default'] => 'border-color: {{VALUE}};',
							],
						]
					);

				$this->end_controls_tab();

				$this->start_controls_tab(
					'tab_input_hover',
					[
						'label' => __( 'Hover', 'rey-core' ),
					]
				);

					$this->add_control(
						'input_color_hover',
						[
							'label' => __( 'Text Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								$selectors['input_hover'] => 'color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'input_bg_color_hover',
						[
							'label' => __( 'Background Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								$selectors['input_hover'] => 'background-color: {{VALUE}}',
							],
						]
					);

					$this->add_responsive_control(
						'input_border_width_hover',
						[
							'label' => __( 'Border Width', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::DIMENSIONS,
							'size_units' => [ 'px', 'em', '%' ],
							'selectors' => [
								$selectors['input_hover'] => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'input_border_color_hover',
						[
							'label' => __( 'Border Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								$selectors['input_hover'] => 'border-color: {{VALUE}};',
							],
						]
					);

				$this->end_controls_tab();

				$this->start_controls_tab(
					'tab_input_focus',
					[
						'label' => __( 'Focus', 'rey-core' ),
					]
				);

					$this->add_control(
						'input_color_focus',
						[
							'label' => __( 'Text Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								$selectors['input_focus'] => 'color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'input_bg_color_focus',
						[
							'label' => __( 'Background Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								$selectors['input_focus'] => 'background-color: {{VALUE}}',
							],
						]
					);

					$this->add_responsive_control(
						'input_border_width_focus',
						[
							'label' => __( 'Border Width', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::DIMENSIONS,
							'size_units' => [ 'px', 'em', '%' ],
							'selectors' => [
								$selectors['input_focus'] => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'input_border_color_focus',
						[
							'label' => __( 'Border Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								$selectors['input_focus'] => 'border-color: {{VALUE}};',
							],
						]
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'input_typo',
					'selector' => $selectors['input_default'],
				]
			);

			$this->add_control(
				'input_radius',
				[
					'label' => esc_html__( 'Border Radius', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						$selectors['input_default'] => 'border-radius: {{VALUE}}px;',
					],
				]
			);

			$this->add_control(
				'input_spacing',
				[
					'label' => esc_html__( 'Vertical rows distance', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						$selectors['rows'] => 'margin-bottom: {{VALUE}}px;',
					],
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_labels_style',
			[
				'label' => __( 'Labels Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'label_typo',
					'selector' => $selectors['label'],
				]
			);

			$this->add_control(
				'label_color',
				[
					'label' => esc_html__( 'Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selectors['label'] => 'color: {{VALUE}}',
					],
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_submit_style',
			[
				'label' => __( 'Submit Button Style', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'btn_typo',
					'selector' => $selectors['submit'],
				]
			);

			$this->start_controls_tabs( 'tabs_btn_styles' );

				$this->start_controls_tab(
					'tabs_btn_normal',
					[
						'label' => esc_html__( 'Normal', 'rey-core' ),
					]
				);

					$this->add_control(
						'btn_color',
						[
							'label' => __( 'Text Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								$selectors['submit'] => 'color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'btn_bg_color',
						[
							'label' => __( 'Background Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								$selectors['submit'] => 'background-color: {{VALUE}}',
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
								$selectors['submit'] => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'btn_border_color',
						[
							'label' => __( 'Border Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								$selectors['submit'] => 'border-color: {{VALUE}};',
							],
						]
					);

				$this->end_controls_tab();

				$this->start_controls_tab(
					'tabs_btn_hover',
					[
						'label' => esc_html__( 'Hover', 'rey-core' ),
					]
				);

					$this->add_control(
						'btn_color_hover',
						[
							'label' => __( 'Text Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								$selectors['submit_hover'] => 'color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'btn_bg_color_hover',
						[
							'label' => __( 'Background Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								$selectors['submit_hover'] => 'background-color: {{VALUE}}',
							],
						]
					);

					$this->add_responsive_control(
						'btn_border_width_hover',
						[
							'label' => __( 'Border Width', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::DIMENSIONS,
							'size_units' => [ 'px', 'em', '%' ],
							'selectors' => [
								$selectors['submit_hover'] => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'btn_border_color_hover',
						[
							'label' => __( 'Border Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								$selectors['submit_hover'] => 'border-color: {{VALUE}};',
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
						$selectors['submit'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'btn_padding',
				[
					'label' => __( 'Padding', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em' ],
					'selectors' => [
						$selectors['submit'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_control(
				'btn_stretch',
				[
					'label' => esc_html__( 'Stretch button', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => '100',
					'default' => '',
					'selectors' => [
						$selectors['submit'] => 'width: {{VALUE}}%;',
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

		$this->add_render_attribute( 'wrapper', 'class', 'rey-element' );
		$this->add_render_attribute( 'wrapper', 'class', 'rey-cf7' );

		?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
			<?php
			if( function_exists('wpcf7_contact_form') ){
				if( $form_id = $settings['form_id'] ){
					if ( $contact_form = wpcf7_contact_form($form_id) ) {

						reycore_assets()->add_styles($this->get_style_name());

						if ( function_exists( 'wpcf7_enqueue_scripts' ) ) {
							wpcf7_enqueue_scripts();
						}

						if ( function_exists( 'wpcf7_enqueue_styles' ) ) {
							wpcf7_enqueue_styles();
						}

						echo $contact_form->form_html([
							'html_class' => 'rey-cf7--' . $settings['form_style']
						]);
					}
				}
			}
			else {
				if( current_user_can('install_plugins') ){
					printf('<div class="rey-setupPlugin"><strong>%1$s</strong> %2$s <a href="#" class="rey-genericBtn" data-setup-plugin="contact-form-7"><u>%3$s</u></a></div>', 'Contact Form 7', esc_html__(' is not installed or active.', 'rey-core'), esc_html__('Install & setup a form now.', 'rey-core'));
				}
			} ?>
		</div>
		<?php
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
