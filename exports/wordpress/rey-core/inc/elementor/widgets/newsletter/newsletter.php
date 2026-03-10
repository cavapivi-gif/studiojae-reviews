<?php
namespace ReyCore\Elementor\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Newsletter extends \ReyCore\Elementor\WidgetsBase {

	private $form_id;

	public $_settings = [];

	public static function get_rey_config(){
		return [
			'id' => 'newsletter',
			'title' => __( 'Newsletter for Mailchimp', 'rey-core' ),
			'icon' => 'eicon-mailchimp',
			'categories' => [ 'rey-theme' ],
			'keywords' => ['newsletter', 'mailing list', 'mailchimp'],
			'css' => [
				'assets/inline-basic.css',
				'assets/rows-basic.css',
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

			$this->add_control(
				'important_note',
				[
					'type' => \Elementor\Controls_Manager::RAW_HTML,
					'raw' => __( 'To use this element you need to install <a href="https://wordpress.org/plugins/mailchimp-for-wp/" target="_blank">Mailchimp for WordPress</a>.', 'rey-core' ),
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
					'label' => __( 'Form ID', 'rey-core' ),
					'default' => '',
					'type' => 'rey-ajax-list',
					'query_args' => [
						'request' => 'get_mc4wp_forms',
						'export' => 'id',
					],
				]
			);

			if( ! defined( 'MC4WP_PREMIUM_VERSION' ) ){
				$this->add_control(
					'use_ajax',
					[
						'label' => esc_html__( 'Use Ajax (stay on page)', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::SWITCHER,
						'default' => 'yes',
					]
				);
			}

			$this->add_control(
				'override_form',
				[
					'label' => esc_html__( 'Override Form HTML', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'label_on' => esc_html__( 'Yes', 'rey-core' ),
					'label_off' => esc_html__( 'No', 'rey-core' ),
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

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'input_typo',
					'label' => __( 'Input typography', 'rey-core' ),
					'selector' => '{{WRAPPER}} .rey-newsletterForm input[type="email"]',
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'btn_typo',
					'label' => __( 'Button typography', 'rey-core' ),
					'selector' => '{{WRAPPER}} .rey-newsletterForm [type="submit"]',
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

		$this->end_controls_section();
	}

	function controls__form_styles(){

		$this->start_controls_section(
			'section_other_styles',
			[
				'label' => __( 'Form Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_control(
				'primary_color',
				[
					'label' => __( 'Primary Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .mc4wp-form-fields' => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'secondary_color',
				[
					'label' => __( 'Secondary Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}}, {{WRAPPER}} input' => 'color: {{VALUE}}',
					],
				]
			);

			$this->start_controls_tabs( 'el_tabs_border' );

				$this->start_controls_tab(
					'el_tab_border_normal',
					[
						'label' => __( 'Normal', 'rey-core' ),
					]
				);

					$this->add_group_control(
						\Elementor\Group_Control_Border::get_type(),
						[
							'name' => 'el_border',
							'selector' => '{{WRAPPER}} .mc4wp-form-fields',
						]
					);

					$this->add_responsive_control(
						'el_border_radius',
						[
							'label' => __( 'Border Radius', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::DIMENSIONS,
							'size_units' => [ 'px', '%' ],
							'selectors' => [
								'{{WRAPPER}} .mc4wp-form-fields' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Box_Shadow::get_type(),
						[
							'name' => 'el_box_shadow',
							'selector' => '{{WRAPPER}} .mc4wp-form-fields',
						]
					);

				$this->end_controls_tab();

				$this->start_controls_tab(
					'el_tab_border_hover',
					[
						'label' => __( 'Hover', 'rey-core' ),
					]
				);

					$this->add_group_control(
						\Elementor\Group_Control_Border::get_type(),
						[
							'name' => 'el_border_hover',
							'selector' => '{{WRAPPER}}:hover .mc4wp-form-fields',
						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Box_Shadow::get_type(),
						[
							'name' => 'el_box_shadow_hover',
							'selector' => '{{WRAPPER}}:hover .mc4wp-form-fields',
						]
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();

			$this->add_responsive_control(
				'el_padding',
				[
					'label' => __( 'Padding', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em', '%' ],
					'selectors' => [
						'{{WRAPPER}} .mc4wp-form-fields' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
					'condition' => [
						'form_style' => 'inline-basic',
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
				'rows_input_bg_color',
				[
					'label' => __( 'Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} input[type="text"], {{WRAPPER}} input[type="email"]' => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'rows_input_text_color',
				[
					'label' => __( 'Text Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} input[type="text"], {{WRAPPER}} input[type="email"]' => 'color: {{VALUE}}',
					]
				]
			);

			$this->add_responsive_control(
				'rows_input_border_width',
				[
					'label' => __( 'Border Width', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em', '%' ],
					'selectors' => [
						'{{WRAPPER}} .rey-newsletterForm input[type="text"], {{WRAPPER}} .rey-newsletterForm input[type="email"]' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_control(
				'rows_input_border_color',
				[
					'label' => __( 'Border Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}}  .rey-newsletterForm input[type="email"]' => 'border-color: {{VALUE}};',
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
						'{{WRAPPER}} .rey-newsletterForm input[type="text"], {{WRAPPER}} .rey-newsletterForm input[type="email"]' => 'height: {{VALUE}}px;',
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
						'{{WRAPPER}} .rey-newsletterForm input[type="text"], {{WRAPPER}} .rey-newsletterForm input[type="email"]' => 'text-align: {{VALUE}};',
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
						'{{WRAPPER}} .rey-newsletterForm input[type="text"], {{WRAPPER}} .rey-newsletterForm input[type="email"]' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
								'{{WRAPPER}}  .rey-newsletterForm [type="submit"]' => 'color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'btn_bg_color',
						[
							'label' => __( 'Background Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}}  .rey-newsletterForm [type="submit"]' => 'background-color: {{VALUE}}',
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
								'{{WRAPPER}}  .rey-newsletterForm [type="submit"]' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'btn_border_color',
						[
							'label' => __( 'Border Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}}  .rey-newsletterForm [type="submit"]' => 'border-color: {{VALUE}};',
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
								'{{WRAPPER}} .rey-newsletterForm [type="submit"]:hover' => 'color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'btn_bg_color_active',
						[
							'label' => __( 'Background Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .rey-newsletterForm [type="submit"]:hover' => 'background-color: {{VALUE}}',
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
								'{{WRAPPER}} .rey-newsletterForm [type="submit"]:hover' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'btn_border_color_active',
						[
							'label' => __( 'Border Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .rey-newsletterForm [type="submit"]:hover' => 'border-color: {{VALUE}};',
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
						'{{WRAPPER}} .rey-newsletterForm [type="submit"]' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
						'{{WRAPPER}}  .rey-newsletterForm [type="submit"]' => 'height: {{VALUE}}px;',
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
						'{{WRAPPER}}  .rey-newsletterForm [type="submit"]' => 'width: {{VALUE}}px;',
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
						'{{WRAPPER}}  .rey-newsletterForm [type="submit"]' => '--icon-distance: {{VALUE}}px;',
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
		$this->controls__form_styles();
		$this->controls__input_styles();
		$this->controls__btn_styles();
	}

	function override_form_fields($content, $form, $element){

		if( $this->_settings['override_form'] === '' ){
			return $content;
		}

		$email_text = esc_html__('Your email address', 'rey-core');
		// $button_text = esc_html__('JOIN', 'rey-core');
		$button_text = '';
		$button_content = '';

		if( $custom_email_text = $this->_settings['email_placeholder'] ){
			$email_text = $custom_email_text;
		}

		if( $custom_button_text = $this->_settings['btn_text'] ){
			$button_text .= $custom_button_text;
		}

		if( $btn_icon = $this->_settings['btn_icon'] ){
			ob_start();
			\Elementor\Icons_Manager::render_icon( $btn_icon, [ 'aria-hidden' => 'true' ] );
			$button_text .= ob_get_clean();
		}

		if( $button_text ){
			$button_content = sprintf('<span>%s</span>', $button_text );
		}

		$btn_classes = ['btn'];

		reycore_assets()->add_styles('rey-buttons');

		if( $btn_style = $this->_settings['btn_style'] ){
			$btn_classes[] = $btn_style;
		}

		$custom_content = sprintf('<p><input type="email" name="EMAIL" placeholder="%s" required autocomplete /></p>', $email_text);
		$custom_content .= sprintf('<p><button class="%s" type="submit">%s</button></p>', esc_attr(implode(' ', $btn_classes)), $button_content);

		return $custom_content;
	}

	public function render_form(){

		if( ! function_exists('mc4wp_get_form') ){

			if( current_user_can('install_plugins') ){
				printf('<div class="rey-setupPlugin"><strong>%1$s</strong> %2$s <a href="#" class="rey-genericBtn" data-setup-plugin="mailchimp-for-wp"><u>%3$s</u></a></div>', 'MailChimp For WordPress', esc_html__(' is not installed or active.', 'rey-core'), esc_html__('Install & setup a form now.', 'rey-core'));
			}

			return;
		}

		if( ! $this->form_id ){
			return;
		}

		add_filter('mc4wp_form_content', [$this, 'override_form_fields'], 10, 3);

		try {
			$form = mc4wp_get_form($this->form_id);
		} catch (\Exception $e) {
			return false;
		}

		echo $form;

		remove_filter('mc4wp_form_content', [$this, 'override_form_fields'], 10, 3);

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

		$this->_settings = $this->get_settings_for_display();

		if( ! ($this->form_id = $this->_settings['form_id']) ) {
			return;
		}

		reycore_assets()->add_styles($this->get_style_name($this->_settings['form_style']));

		if( ! defined( 'MC4WP_PREMIUM_VERSION' ) && '' !== $this->_settings['use_ajax'] ){
			reycore_assets()->add_scripts(['reycore-mc4wp']);
		}

		$this->add_render_attribute( 'wrapper', 'class', [
			'rey-element',
			'rey-newsletterForm',
			'rey-nlForm--' . $this->_settings['form_style']
		] ); ?>

		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
			<?php $this->render_form(); ?>
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
