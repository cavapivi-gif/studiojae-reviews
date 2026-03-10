<?php
namespace ReyCore\Elementor\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class HeaderLogo extends \ReyCore\Elementor\WidgetsBase {

	public static function get_rey_config(){
		return [
			'id' => 'header-logo',
			'title' => __( 'Logo', 'rey-core' ),
			'icon' => 'eicon-logo',
			'categories' => [ 'rey-header' ],
			'keywords' => [],
		];
	}

	public function get_custom_help_url() {
		return reycore__support_url('kb/rey-elements-header/#logo');
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

		$cst_link = add_query_arg([
			'autofocus[section]' => \ReyCore\Customizer\Options\Header\Logo::get_id()
			], admin_url( 'customize.php' )
		);

		$this->add_control(
			'edit_link',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => sprintf( __( 'Logo Image can be changed in <a href="%1$s" target="_blank">Customizer > Header > Logo</a>.', 'rey-core' ), $cst_link ),
				'content_classes' => 'rey-raw-html',
				'condition' => [
					'custom' => [''],
				],
			]
		);

		$this->add_control(
			'custom',
			[
				'label' => __( 'Override global settings', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->add_control(
			'blog_name',
			[
				'label' => __( 'Site Name', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => get_bloginfo( 'name' ),
				'description' => __( 'Disabled if image is used.', 'rey-core' ),
				'condition' => [
					'custom!' => [''],
				],
			]
		);

		$this->add_control(
			'blog_description',
			[
				'label' => __( 'Site Description', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => get_bloginfo( 'description', 'display' ),
				'description' => __( 'Disabled if image is used.', 'rey-core' ),
				'condition' => [
					'custom!' => [''],
				],
			]
		);

		$this->add_control(
			'logo',
			[
			   'label' => __( 'Choose Logo', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::MEDIA,
				'default' => [],
				'condition' => [
					'custom!' => [''],
				],
			]
		);

		$this->add_control(
			'logo_mobile',
			[
			   'label' => __( 'Choose Logo for Mobile view', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::MEDIA,
				'default' => [],
				'condition' => [
					'custom!' => [''],
				],
			]
		);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_styles',
			[
				'label' => __( 'Logo styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE
			]
		);

		$this->add_responsive_control(
			'width',
			[
				'label' => __( 'Width', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'default' => [
					'unit' => 'px',
				],
				'tablet_default' => [
					'unit' => 'px',
				],
				'mobile_default' => [
					'unit' => 'px',
				],
				'size_units' => ['px', '%', 'vw' ],
				'range' => [
					'%' => [
						'min' => 1,
						'max' => 100,
					],
					'px' => [
						'min' => 1,
						'max' => 1000,
					],
					'vw' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .rey-siteLogo img, {{WRAPPER}} .rey-siteLogo .custom-logo' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'height',
			[
				'label' => __( 'Height', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 1000,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .rey-siteLogo img, {{WRAPPER}} .rey-siteLogo .custom-logo' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'space',
			[
				'label' => __( 'Max Width', 'rey-core' ) . ' (%)',
				'type' => \Elementor\Controls_Manager::SLIDER,
				'default' => [
					'unit' => '%',
				],
				'tablet_default' => [
					'unit' => '%',
				],
				'mobile_default' => [
					'unit' => '%',
				],
				'size_units' => [ '%' ],
				'range' => [
					'%' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .rey-siteLogo img, {{WRAPPER}} .rey-siteLogo .custom-logo' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'max_height',
			[
				'label' => __( 'Max Height', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 300,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .rey-siteLogo img, {{WRAPPER}} .rey-siteLogo .custom-logo' => 'max-height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'align',
			[
				'label' => __( 'Alignment', 'rey-core' ),
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
				'prefix_class' => 'elementor%s-align-',
			]
		);

		$this->add_control(
			'object_fit',
			[
				'label' => esc_html__( 'Object Fit', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => esc_html__( 'Auto', 'rey-core' ),
					'contain'  => esc_html__( 'Contain', 'rey-core' ),
					'cover'  => esc_html__( 'Cover', 'rey-core' ),
				],
				'selectors' => [
					'{{WRAPPER}} .rey-siteLogo .custom-logo' => 'object-fit: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'text_typography',
				'label' => __( 'Text Fallback Typography', 'rey-core' ),
				'selector' => '{{WRAPPER}} .rey-logoTitle',
			]
		);

		$this->end_controls_section();


	}

	function set_logo_options( $args ){

		$settings = $this->get_settings_for_display();

		// if it's sticky, inherit the initial logo settings
		if( isset($GLOBALS['rey__is_sticky']) && $GLOBALS['rey__is_sticky'] ){
			$args['logo'] = get_theme_mod('custom_logo', '');
			$args['logo_mobile'] = get_theme_mod('logo_mobile', '');
		}

		if( isset($settings['custom']) && $settings['custom'] ){

			$args['blog_name'] = $settings['blog_name'];
			$args['blog_description'] = $settings['blog_description'];

			if( isset($settings['logo']['id']) ){
				$args['logo'] = $settings['logo']['id'];
			}

			if( isset($settings['logo_mobile']['id']) ){
				$args['logo_mobile'] = $settings['logo_mobile']['id'];
			}
		}

		return $args;
	}

	function logo_attributes( $attributes ){

		$settings = $this->get_settings_for_display();

		if( isset($settings['width']['size']) && $size = $settings['width']['size'] ){
			if( isset($settings['width']['unit']) && $settings['width']['unit'] === 'px' ){
				$attributes['width'] = $size;
			}
		}

		if( isset($settings['height']['size']) && $size = $settings['height']['size'] ){
			if( isset($settings['height']['unit']) && $settings['height']['unit'] === 'px' ){
				$attributes['height'] = $size;
			}
		}

		if( isset($settings['custom']) && '' !== $settings['custom'] ){

			$overrides = [];

			if( ! empty($settings['logo']['id']) ){
				$overrides[] = 'logo';
			}

			if( ! empty($settings['logo_mobile']['id']) ){
				$overrides[] = 'logo_mobile';
			}

			if( ! empty($overrides) ){
				$attributes['data-el-overrides'] = implode(',', $overrides);
			}

		}

		return $attributes;
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

		add_filter('rey/header/logo_params', [$this, 'set_logo_options'], 10);
		add_filter('rey/logo/attributes', [$this, 'logo_attributes'], 10);

		reycore__get_template_part('template-parts/header/logo');

		remove_filter('rey/header/logo_params', [$this, 'set_logo_options'], 10);
		remove_filter('rey/logo/attributes', [$this, 'logo_attributes'], 10);

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
