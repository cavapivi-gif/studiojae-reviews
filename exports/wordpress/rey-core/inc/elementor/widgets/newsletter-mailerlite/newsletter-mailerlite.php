<?php
namespace ReyCore\Elementor\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class NewsletterMailerlite extends \ReyCore\Elementor\WidgetsBase {

	private $form_id;

	public $_settings = [];

	public static $selectors = [
		'input' => '{{WRAPPER}} .mailerlite-form-field input[type="text"], {{WRAPPER}} .mailerlite-form-field input[type="email"]',
		'button' => '{{WRAPPER}} .mailerlite-subscribe-button-container input[type="submit"]',
		'button_hover' => '{{WRAPPER}} .mailerlite-subscribe-button-container input[type="submit"]:hover',
		'label' => '{{WRAPPER}} .mailerlite-form-field label',
	];

	public static function get_rey_config(){
		return [
			'id' => 'newsletter-mailerlite',
			'title' => __( 'Newsletter for Mailerlite', 'rey-core' ),
			'icon' => 'eicon-email-field',
			'categories' => [ 'rey-theme' ],
			'keywords' => ['newsletter', 'mailing list', 'mailerlite'],
			'css' => [
				'assets/style[rtl].css',
			],
		];
	}

	public function get_custom_help_url() {
		return reycore__support_url('kb/rey-elements/#newsletter-form');
	}

	public function on_export($element)
    {
        unset(
            $element['settings']['form_id']
        );

        return $element;
    }

	function controls__settings(){

		$this->start_controls_section(
			'section_settings',
			[
				'label' => __( 'Settings', 'rey-core' ),
			]
		);

			// form id
			$this->add_control(
				'form_id',
				[
					'label' => __( 'Form ID', 'rey-core' ),
					'default' => '',
					'type' => 'rey-ajax-list',
					'query_args' => [
						'request' => 'get_mailerlite_forms',
						'export' => 'id',
						'placeholder' => '- Select -'
					],
					'placeholder' => '- Select -'
				]
			);

			// $this->add_control(
			// 	'override_form',
			// 	[
			// 		'label' => esc_html__( 'Override Form HTML', 'rey-core' ),
			// 		'type' => \Elementor\Controls_Manager::SWITCHER,
			// 		'label_on' => esc_html__( 'Yes', 'rey-core' ),
			// 		'label_off' => esc_html__( 'No', 'rey-core' ),
			// 		'default' => '',
			// 	]
			// );

			$this->add_control(
				'override_form',
				[
					'label' => esc_html__( 'Override Form HTML', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::HIDDEN,
					'default' => '',
				]
			);

			$this->add_control(
				'email_placeholder',
				[
					'label' => esc_html__( 'Email field placeholder', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'placeholder' => esc_html__( 'eg: Your email address', 'rey-core' ),
					'condition' => [
						'override_form!' => '',
					],
				]
			);

			$this->add_control(
				'btn_text',
				[
					'label' => esc_html__( 'Button text', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => esc_html__( 'JOIN', 'rey-core' ),
					'placeholder' => esc_html__( 'eg: JOIN', 'rey-core' ),
					'condition' => [
						'override_form!' => '',
					],
				]
			);

			$this->add_control(
				'btn_icon',
				[
					'label' => __( 'Button Icon', 'elementor' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'default' => [],
					'condition' => [
						'override_form!' => '',
					],
				]
			);

			$this->add_control(
				'override_form_name',
				[
					'label' => esc_html__( 'Add Name', 'rey-core' ),
					'description' => esc_html__( 'Make sure there\'s also a name field added in the plugin\'s form markup.', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'condition' => [
						'override_form!' => '',
					],
				]
			);

			$this->add_control(
				'name_placeholder',
				[
					'label' => esc_html__( 'Name field placeholder', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'placeholder' => esc_html__( 'eg: Name', 'rey-core' ),
					'condition' => [
						'override_form!' => '',
						'override_form_name!' => '',
					],
				]
			);


		$this->end_controls_section();
	}


	function controls__layout(){

		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Style', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_control(
				'form_style',
				[
					'label' => __( 'Form Style', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						'' => '- Select -',
						'inline-basic' => esc_html__('Inline', 'rey-core'),
						'rows-basic' => esc_html__('Rows', 'rey-core'),
					],
				]
			);

			$this->add_control(
				'form_gaps',
				[
					'label' => esc_html__( 'Gap', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 200,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}}' => '--form-gap: {{VALUE}}px;',
					],
					'condition' => [
						'form_style!' => '',
					],
				]
			);


		$this->end_controls_section();
	}

	function controls__input_styles(){

		$this->start_controls_section(
			'section_rows_styles',
			[
				'label' => __( 'Email Input Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_control(
				'show_label',
				[
					'label' => esc_html__( 'Display labels', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
					'prefix_class' => '--labels-',
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'label_typo',
					'selector' => self::$selectors['label'],
					'condition' => [
						'show_label!' => '',
					],
				]
			);

			$this->add_control(
				'label_color',
				[
					'label' => esc_html__( 'Labels color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						self::$selectors['label'] => 'color: {{VALUE}}',
					],
					'condition' => [
						'show_label!' => '',
					],
				]
			);

			$this->add_control(
				'rows_input_bg_color',
				[
					'label' => __( 'Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						self::$selectors['input'] => 'background-color: {{VALUE}}',
					],
					'separator' => 'before',
				]
			);

			$this->add_control(
				'rows_input_text_color',
				[
					'label' => __( 'Text Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						self::$selectors['input'] => 'color: {{VALUE}}',
					]
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'input_typo',
					'label' => __( 'Input typography', 'rey-core' ),
					'selector' => self::$selectors['input'],
				]
			);

			$this->add_responsive_control(
				'rows_input_border_width',
				[
					'label' => __( 'Border Width', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em', '%' ],
					'selectors' => [
						self::$selectors['input'] => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_control(
				'rows_input_border_color',
				[
					'label' => __( 'Border Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						self::$selectors['input'] => 'border-color: {{VALUE}};',
					],
				]
			);

			$this->add_control(
				'input_height',
				[
					'label' => esc_html__( 'Height', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 1,
					'max' => 200,
					'step' => 1,
					'selectors' => [
						self::$selectors['input'] => 'height: {{VALUE}}px;',
					],
				]
			);

			$this->add_responsive_control(
				'input_text_align',
				[
					'label' => __( 'Text Alignment', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::CHOOSE,
					'options' => [
						'left' => [
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
					],
					'selectors' => [
						self::$selectors['input'] => 'text-align: {{VALUE}};',
					],
				]
			);

			$this->add_responsive_control(
				'rows_input_border_radius',
				[
					'label' => __( 'Border Radius', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em' ],
					'selectors' => [
						self::$selectors['input'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

		$this->end_controls_section();

	}

	function controls__btn_styles(){

		$this->start_controls_section(
			'section_btn_styles',
			[
				'label' => __( 'Button Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
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
								self::$selectors['button'] => 'color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'btn_bg_color',
						[
							'label' => __( 'Background Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								self::$selectors['button'] => 'background-color: {{VALUE}}',
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
								self::$selectors['button'] => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'btn_border_color',
						[
							'label' => __( 'Border Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								self::$selectors['button'] => 'border-color: {{VALUE}};',
							],
						]
					);

				$this->end_controls_tab();

				$this->start_controls_tab(
					'tabs_btn_hover',
					[
						'label' => esc_html__( 'Active', 'rey-core' ),
					]
				);

					$this->add_control(
						'btn_color_active',
						[
							'label' => __( 'Text Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								self::$selectors['button_hover'] => 'color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'btn_bg_color_active',
						[
							'label' => __( 'Background Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								self::$selectors['button_hover'] => 'background-color: {{VALUE}}',
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
								self::$selectors['button_hover'] => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'btn_border_color_active',
						[
							'label' => __( 'Border Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								self::$selectors['button_hover'] => 'border-color: {{VALUE}};',
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
						self::$selectors['button'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_control(
				'btn_height',
				[
					'label' => esc_html__( 'Button Height', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 1,
					'max' => 200,
					'step' => 1,
					'selectors' => [
						self::$selectors['button'] => 'height: {{VALUE}}px;',
					],
				]
			);

			$this->add_responsive_control(
				'input_width',
				[
					'label' => esc_html__( 'Button Width', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 1,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						self::$selectors['button'] => 'width: {{VALUE}}px;',
					],
					'condition' => [
						'form_style' => 'inline-basic',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'btn_typo',
					'label' => __( 'Button typography', 'rey-core' ),
					'selector' => self::$selectors['button'],
				]
			);

			$this->add_control(
				'btn_style',
				[
					'type' => \Elementor\Controls_Manager::SELECT,
					'label'       => esc_html__( 'Button Style', 'rey-core' ),
					'default'     => '',
					'options'     => [
						'' => esc_html__( 'None', 'rey-core' ),
						'btn-line' => esc_html__( 'Underlined on hover', 'rey-core' ),
						'btn-line-active' => esc_html__( 'Underlined', 'rey-core' ),
						'btn-primary' => esc_html__( 'Regular', 'rey-core' ),
						'btn-primary-outline' => esc_html__( 'Regular outline', 'rey-core' ),
						'btn-secondary' => esc_html__( 'Secondary', 'rey-core' ),
					],
					'condition' => [
						'override_form!' => '',
					],
				]
			);

			$this->add_control(
				'btn_block',
				[
					'label' => esc_html__( 'Button block', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
					'prefix_class' => '--btn-block-'
				]
			);

			$this->add_control(
				'btn_width',
				[
					'label' => esc_html__( 'Button Width', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 1,
					'max' => 2000,
					'step' => 1,
					'selectors' => [
						self::$selectors['button'] => 'width: {{VALUE}}px;',
					],
					'condition' => [
						'btn_block!' => 'yes',
					],
				]
			);

			$this->add_control(
				'btn_icon_distance',
				[
					'label' => __( 'Icon Distance', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => [],
					'condition' => [
						'btn_icon!' => '',
						'override_form!' => '',
					],
					'selectors' => [
						self::$selectors['button'] => '--icon-distance: {{VALUE}}px;',
					],
				]
			);

		$this->end_controls_section();

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

		$this->controls__settings();
		$this->controls__layout();
		$this->controls__input_styles();
		$this->controls__btn_styles();
	}

	public function custom_form(){

	}

	public function render_form(){

		if( ! class_exists('\MailerLiteForms\Modules\Form') ){
			return;
		}

		if( ! $this->form_id ){
			return;
		}

		if( $this->_settings['override_form'] !== '' ){
			$this->custom_form();
			return;
		}

		try {

			ob_start();

			( new \MailerLiteForms\Modules\Form() )->load_mailerlite_form( $this->form_id );

			$form = ob_get_clean();

		} catch (\Exception $e) {
			return false;
		}

		echo $form;

	}

	/**
	 * Render widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {

		reycore_assets()->add_styles($this->get_style_name());

		$this->_settings = $this->get_settings_for_display();

		$this->add_render_attribute( 'wrapper', 'class', [
			'rey-element',
			'rey-newsletterForm-ml',
			'rey-mlForm--' . $this->_settings['form_style']
		] );

		?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
			<?php if( $this->form_id = $this->_settings['form_id'] ){
				$this->render_form();
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
