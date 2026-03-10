<?php
namespace ReyCore\Elementor\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


class SaleBadge extends \ReyCore\Elementor\WidgetsBase {

	public $_settings = [];

	public static function get_rey_config(){
		return [
			'id' => 'sale-badge',
			'title' => __( 'Sale Badge', 'rey-core' ),
			'icon' => 'eicon-product-description',
			'categories' => [ 'rey-theme' ],
			'keywords' => [],
			'css' => [
				'assets/style[rtl].css',
			],
		];
	}

	public function get_custom_help_url() {
		return reycore__support_url('kb/rey-elements/#sale-badge');
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
			'sale_text',
			[
				'label' => esc_html__( 'Sale Text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( '-35%', 'rey-core' ),
				'placeholder' => esc_html__( 'eg: -35%', 'rey-core' ),
			]
		);

		$this->add_control(
			'title',
			[
				'label' => esc_html__( 'Title', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXTAREA,
				'default' => esc_html__( 'Title here', 'rey-core' ),
			]
		);

		$this->add_control(
			'sub_title',
			[
				'label' => esc_html__( 'Sub-title', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXTAREA,
				'default' => esc_html__( 'The sub-title right here.', 'rey-core' ),
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

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Style', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'sale_typo',
				'label' => __( 'Sale text typography', 'rey-core' ),
				'selector' => '{{WRAPPER}} .rey-saleBadge-top',
			]
		);

		$this->add_control(
			'sale_color',
			[
				'label' => esc_html__( 'Sale Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-saleBadge-top' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'sale_bg_color',
			[
				'label' => esc_html__( 'Sale Background Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-saleBadge-top:before' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'animate_sale_bg',
			[
				'label' => esc_html__( 'Animate Sale Background', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
				'separator' => 'after',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'title_typo',
				'label' => __( 'Title Typography', 'rey-core' ),
				'selector' => '{{WRAPPER}} .rey-saleBadge-title',
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => esc_html__( 'Title Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-saleBadge-title' => 'color: {{VALUE}}',
				]
			]
		);

		$this->add_control(
			'title_bg_color',
			[
				'label' => esc_html__( 'Title Background Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-saleBadge-main' => 'background-color: {{VALUE}}',
				],
				'separator' => 'after',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'subtitle_typo',
				'label' => __( 'Subtitle Typography', 'rey-core' ),
				'selector' => '{{WRAPPER}} .rey-saleBadge-subtitle',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'subtitle_color',
			[
				'label' => esc_html__( 'Subtitle Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-saleBadge-subtitle' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'inner_padding',
			[
			   'label' => esc_html__( 'Inner Padding', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range' => [
					'px' => [
						'min' => 9,
						'max' => 180,
						'step' => 1,
					],
					'em' => [
						'min' => 0,
						'max' => 5.0,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 15,
				],
				'separator' => 'before',
				'selectors' => [
					'{{WRAPPER}}' => '--inner-padding: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	public function render_start()
	{
		$classes = [
			'rey-saleBadge',
			'rey-saleBadge--default',
			$this->_settings['animate_sale_bg'] === 'yes' ? '--animate-bg' : '',
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

	public function render_link_start() {

		if( ! ($url = $this->_settings['link']['url']) ){
			return;
		}

		$this->add_render_attribute( 'link_wrapper' , 'class', 'rey-saleBadge-link' );

		$this->add_render_attribute( 'link_wrapper' , 'href', $url );

		if( $this->_settings['link']['is_external'] ){
			$this->add_render_attribute( 'link_wrapper' , 'target', '_blank' );
		}

		if( $this->_settings['link']['nofollow'] ){
			$this->add_render_attribute( 'link_wrapper' , 'rel', 'nofollow' );
		}
		?>

		<a <?php echo  $this->get_render_attribute_string('link_wrapper'); ?>><?php
	}

	public function render_link_end()
	{
		if( ! $this->_settings['link']['url'] ){
			return;
		}

		?></a><?php
	}

	protected function render() {

		reycore_assets()->add_styles($this->get_style_name());

		$this->_settings = $this->get_settings_for_display();

		$this->render_start();
		$this->render_link_start();

		if( $sale_text = $this->_settings['sale_text'] ){
			printf('<div class="rey-saleBadge-top"><span>%s</span></div>', $sale_text);
		}
		$title = $this->_settings['title'];
		$sub_title = $this->_settings['sub_title'];

		if( $title || $sub_title ){

			echo '<div class="rey-saleBadge-main">';

			if( $title ){
				printf('<h3 class="rey-saleBadge-title">%s</h3>', $title);
			}

			if( $sub_title ){
				printf('<div class="rey-saleBadge-subtitle">%s</div>', $sub_title);
			}

			echo '</div>';

		}

		$this->render_link_end();
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
