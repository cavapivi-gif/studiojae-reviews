<?php
namespace ReyCore\Elementor\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Marquee extends \ReyCore\Elementor\WidgetsBase {

	public $_settings = [];

	public static function get_rey_config(){
		return [
			'id' => 'marquee',
			'title' => __( 'Scrolling Text', 'rey-core' ),
			'icon' => 'eicon-animation-text',
			'categories' => [ 'rey-theme' ],
			'keywords' => ['marquee', 'sliding text', 'scroller'],
			'css' => [
				'assets/style[rtl].css',
			],
			'js' => [
				'assets/script.js',
			],
		];
	}

	public function get_custom_help_url() {
		return reycore__support_url('kb/rey-elements/#marquee');
	}

	public function rey_get_script_depends() {
		return [ 'reycore-widget-marquee-scripts' ];
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
			'text',
			[
				'label' => esc_html__( 'Text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXTAREA,
				'default' => esc_html__( 'TEXT EXAMPLE', 'rey-core' ),
				'description' => esc_html__( 'You can also add multiple words per line.', 'rey-core' ),
			]
		);

		$this->add_control(
			'separator',
			[
				'label' => esc_html__( 'Separator', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => esc_html__( 'None', 'rey-core' ),
					'dot'  => esc_html__( 'Dot', 'rey-core' ),
					'line'  => esc_html__( 'Line', 'rey-core' ),
					'slash'  => esc_html__( 'Slash', 'rey-core' ),
					'backslash'  => esc_html__( 'Backslash', 'rey-core' ),
					'custom'  => esc_html__( 'Custom', 'rey-core' ),
				],
				'render_type' => 'template',
			]
		);

		$this->add_control(
			'separator_custom',
			[
				'label' => esc_html__( 'Custom Separator', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => esc_html__( 'ex: &curren;', 'rey-core' ),
				'condition' => [
					'separator' => 'custom',
				],
			]
		);

		$this->add_control(
			'direction',
			[
				'label' => esc_html__( 'Direction', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'left',
				'options' => [
					'left'  => esc_html__( 'To Left', 'rey-core' ),
					'right'  => esc_html__( 'To Right', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'speed',
			[
				'label' => esc_html__( 'Speed (seconds)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 20,
				'min' => 1,
				'max' => 500,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .rey-marquee' => '--duration: {{VALUE}}s',
				],
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
				'default' => [],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Marquee Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'typo',
				'selector' => '{{WRAPPER}}',
				'render_type' => 'template',
			]
		);

		$this->add_control(
			'color',
			[
				'label' => esc_html__( 'Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'distance',
			[
			   'label' => esc_html__( 'Distance', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'range' => [
					'em' => [
						'min' => 0.1,
						'max' => 3.0,
					],
				],
				'default' => [
					'unit' => 'em',
					'size' => .2,
				],
				'selectors' => [
					'{{WRAPPER}} .rey-marquee' => '--distance: {{SIZE}}{{UNIT}};',
				],
				'render_type' => 'template',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_separator_style',
			[
				'label' => __( 'Separator Style', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'separator_size',
			[
			   'label' => esc_html__( 'Separator size', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'range' => [
					'em' => [
						'min' => 0.1,
						'max' => 3.0,
					],
				],
				'default' => [
					'unit' => 'em',
					'size' => .8,
				],
				'selectors' => [
					'{{WRAPPER}} .rey-marquee' => '--sep-size: {{SIZE}}{{UNIT}};',
				],
				'render_type' => 'template',
			]
		);

		$this->add_control(
			'separator_color',
			[
				'label' => esc_html__( 'Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-marquee' => '--sep-color: {{VALUE}};',
				],
			]
		);


		$this->end_controls_section();
	}

	public function render_start()
	{
		$classes = [
			'rey-marquee',
			'rey-marquee--default',
			'--dir-' . $this->_settings['direction'],
		];

		$this->add_render_attribute( 'wrapper', [
			'class' => $classes,
		] );
		?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
		<?php
	}

	public function render_end()
	{
		?></div><?php
	}

	public function render_the_content(){

		if( '' === $this->_settings['text'] ){
			return;
		}

		$content = $separator = '';

		if( $_sp = $this->_settings['separator'] ){

			$_sp_map = [
				'dot' => '&bull;',
				'line' => '&mdash;',
				'slash' => '/',
				'backslash' => '\\',
			];

			if( $_sp === 'custom' ){
				$_sp_symbol = $this->_settings['separator_custom'];
			}
			else {
				$_sp_symbol = $_sp_map[$_sp];
			}

			$separator = sprintf('<span class="__sep --%s"><span>%s</span></span>', esc_attr($_sp), $_sp_symbol);
		}

		$texts = preg_split('/\r\n|\r|\n/', $this->_settings['text']);

		if( count($texts) > 1 ){
			foreach ($texts as $text) {
				$content .= sprintf('<div class="rey-marqueeInnerChunk"><span>%s</span>%s</div>', $text, $separator);
			}
		}
		else {
			$content = sprintf('<span>%s</span>%s', $texts[0], $separator);
		}

		$link['start'] = $link['end'] = '';

		if ( ! empty( $this->_settings['link']['url'] ) ) {
			$this->add_link_attributes( 'href', $this->_settings['link'] );
			$link['start'] = sprintf('<a %s>', $this->get_render_attribute_string( 'href' ));
			$link['end'] = '</a>';
		} ?>

		<?php echo $link['start']; ?>
		<div class="rey-marqueeSlider">
			<div class="rey-marqueeChunk"><?php echo $content; ?></div>
		</div>
		<?php echo $link['end']; ?>

		<?php
	}



	protected function render() {

		reycore_assets()->add_styles($this->get_style_name());
		reycore_assets()->add_scripts( $this->rey_get_script_depends() );

		$this->_settings = $this->get_settings_for_display();

		$this->render_start();
		$this->render_the_content();
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
