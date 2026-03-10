<?php
namespace ReyCore\Elementor\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


class Stamp extends \ReyCore\Elementor\WidgetsBase {

	public $_settings = [];

	public static function get_rey_config(){
		return [
			'id' => 'stamp',
			'title' => __( 'Stamp', 'rey-core' ),
			'icon' => 'eicon-dot-circle-o',
			'categories' => [ 'rey-theme' ],
			'keywords' => [],
			'css' => [
				'assets/style[rtl].css',
			],
		];
	}

	public function get_custom_help_url() {
		return reycore__support_url('kb/rey-elements/#stamp');
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
			'stamp_content',
			[
				'label' => esc_html__( 'Stamp Content', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'image',
				'options' => [
					'image'  => esc_html__( 'Image', 'rey-core' ),
					'icon'  => esc_html__( 'Icon', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'image',
			[
			   'label' => esc_html__( 'Image', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::MEDIA,
				'default' => [
					'url' => reycore__get_rey_logo(),
				],
				'condition' => [
					'stamp_content' => 'image',
				],
			]
		);

		$this->add_control(
			'icon',
			[
				'label' => __( 'Icon', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::ICONS,
				// 'fa4compatibility' => 'icon',
				'default' => [
					'value' => 'fas fa-plus',
					'library' => 'fa-solid',
				],
				'condition' => [
					'stamp_content' => 'icon',
				],
			]
		);

		$this->add_control(
			'text',
			[
				'label' => esc_html__( 'Circle text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXTAREA,
				'default' => esc_html__( 'Just a text surrounding the icon.', 'rey-core' ),
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

		$this->end_controls_section();


		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Stamp Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'animate',
			[
				'label' => esc_html__( 'Rotate animation', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_responsive_control(
			'size',
			[
			   'label' => esc_html__( 'Stamp Size', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				// 'size_units' => [ 'px', 'em' ],
				'range' => [
					'px' => [
						'min' => 9,
						'max' => 600,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .rey-stamp' => '--size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'color',
			[
				'label' => esc_html__( 'Stamp color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-stamp' => '--primary-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'rotate',
			[
			   'label' => esc_html__( 'Rotate stamp', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => -360,
						'max' => 360,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .rey-stamp' => 'transform: rotate({{SIZE}}deg);',
				],
			]
		);

		$this->add_control(
			'border_size',
			[
				'label' => esc_html__( 'Stamp border', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 30,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .rey-stamp' => '--border-size: {{VALUE}}px;',
				],
			]
		);

		$this->add_control(
			'border_color',
			[
				'label' => esc_html__( 'Border color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-stamp' => '--border-color: {{VALUE}}',
				],
				'condition' => [
					'border_size!' => '',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_content_style',
			[
				'label' => __( 'Content Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'text_typo',
				'label' => esc_html__( 'Text Typography', 'rey-core' ),
				'selector' => '{{WRAPPER}} .rey-stampText-svgText',
				'exclude' => [ 'letter_spacing', 'line_height' ],
			]
		);

		$this->add_responsive_control(
			'letter-spacing',
			[
			   'label' => esc_html__( 'Text Letter-spacing', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 180,
						'step' => 1,
					],
					'em' => [
						'min' => 0,
						'max' => 5.0,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .rey-stamp' => '--letter-spacing: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'inner_size',
			[
			   'label' => esc_html__( 'Inner Size', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', '%' ],
				'range' => [
					'px' => [
						'min' => 9,
						'max' => 600,
						'step' => 1,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
						'step' => 1,
					],
					'em' => [
						'min' => 0,
						'max' => 5.0,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .rey-stamp' => '--inner-size: {{SIZE}}{{UNIT}};',
				],
				'separator' => 'before'
			]
		);

		$this->add_control(
			'icon_color',
			[
				'label' => esc_html__( 'Icon Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-stamp' => '--icon-color: {{VALUE}}',
				],
				'condition' => [
					'stamp_content' => 'icon',
				],
			]
		);

		$this->end_controls_section();
	}

	public function render_start()
	{
		$classes = [
			'rey-stamp',
			'rey-stamp--default',
			$this->_settings['animate'] === 'yes' ? '--animate' : '',
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

		$attributes = [
			'class' => 'rey-stampLink',
			'href' => $url,
		];

		if( $this->_settings['link']['is_external'] ){
			$attributes['target'] = '_blank';
		}

		if( $this->_settings['link']['nofollow'] ){
			$attributes['rel'] = 'nofollow';
		}

		$this->add_render_attribute( 'link_wrapper' , $attributes );
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

	public function render_the_content(){ ?>

		<div class="rey-stampContent">
			<?php

				if( $this->_settings['stamp_content'] === 'icon' && ($icon = $this->_settings['icon']) ){
					\Elementor\Icons_Manager::render_icon( $icon, [ 'aria-hidden' => 'true' ] );
				}

				elseif( $this->_settings['stamp_content'] === 'image' && ($image = $this->_settings['image']) ){

					echo reycore__get_attachment_image( [
						'image' => $image,
						'size' => 'full',
					] );
				}
			?>
		</div>
		<?php
	}

	public function render_svg(){

		if( !($text = $this->_settings['text']) ){
			return;
		}

		?>
		<svg class="rey-stampText-svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="70 70 350 350">
			<title><?php echo esc_html($text) ?></title>
			<defs>
				<path d="M243.2, 382.4c-74.8,
				0-135.5-60.7-135.5-135.5s60.7-135.5,135.5-135.5s135.5, 60.7, 135.5,
				135.5 S318, 382.4, 243.2, 382.4z" id="textcircle-<?php echo $this->get_id() ?>" />
			</defs>
			<text dy="0" class="rey-stampText-svgText">
				<textPath xlink:href="#textcircle-<?php echo $this->get_id() ?>">
					<?php echo esc_html($text) ?>
				</textPath>
			</text>
		</svg>
		<?php
	}

	protected function render() {

		reycore_assets()->add_styles($this->get_style_name());

		$this->_settings = $this->get_settings_for_display();

		$this->render_start();
		$this->render_link_start();

		$this->render_svg();
		$this->render_the_content();


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
