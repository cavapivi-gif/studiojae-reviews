<?php
namespace ReyCore\Elementor\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class BeforeAfter extends \ReyCore\Elementor\WidgetsBase {

	public $_settings = [];

	public static function get_rey_config(){
		return [
			'id' => 'before-after',
			'title' => __( 'Before/After [rey]', 'rey-core' ),
			'icon' => 'eicon-image-before-after',
			'categories' => [ 'rey-theme' ],
			'keywords' => ['compare', 'images'],
			'css' => [
				'assets/style.css',
			],
			'js' => [
				'assets/script.js',
			],
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
			'section_content',
			[
				'label' => __( 'Content', 'rey-core' ),
			]
		);

			$this->add_control(
				'before_img',
				[
				   'label' => esc_html__( 'Before Image', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::MEDIA,
					'default' => [
						'url' => \Elementor\Utils::get_placeholder_image_src(),
					],
				]
			);

			$this->add_control(
				'after_img',
				[
				   'label' => esc_html__( 'After Image', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::MEDIA,
					'default' => [
						'url' => \Elementor\Utils::get_placeholder_image_src(),
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Image_Size::get_type(),
				[
					'name' => 'image', // Usage: `{name}_size` and `{name}_custom_dimension`, in this case `image_size` and `image_custom_dimension`.
					'default' => 'large',
					'exclude' => ['custom'],
				]
			);

			$this->add_control(
				'aspect_ratio',
				[
					'label' => esc_html__( 'Aspect Ratio', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0.2,
					'max' => 10,
					'step' => 0.05,
					'placeholder' => 2.25,
					'selectors' => [
						'{{WRAPPER}}' => '--ba-aspect-ratio: {{VALUE}};',
					],
				]
			);

			$this->add_control(
				'stretch',
				[
					'label' => esc_html__( 'Stretch Images', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

			$this->add_control(
				'orientation',
				[
					'label' => esc_html__( 'Orientation', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'h',
					'options' => [
						'h'  => esc_html__( 'Horizontal', 'rey-core' ),
						'v'  => esc_html__( 'Vertical', 'rey-core' ),
					],
				]
			);

			$this->add_control(
				'start',
				[
					'label' => esc_html__( 'Start', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 50,
					'min' => 0,
					'max' => 100,
					'step' => 1,
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

			$this->add_control(
				'cursor',
				[
					'label' => esc_html__( 'Cursor', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
				]
			);

			$this->add_control(
				'sep_color',
				[
					'label' => esc_html__( 'Separator Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}}' => '--ba-separator-color: {{VALUE}}',
					],
					'condition' => [
						'cursor!' => '',
					],
				]
			);

			$this->add_control(
				'sep_size',
				[
					'label' => esc_html__( 'Separator Size', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 1,
					'max' => 100,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}}' => '--ba-separator-size: {{VALUE}}px',
					],
					'condition' => [
						'cursor!' => '',
					],
				]
			);

			$this->add_control(
				'center_indicator',
				[
					'label' => esc_html__( 'Center Indicator', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'condition' => [
						'cursor!' => '',
					],
				]
			);

			$this->add_control(
				'center_indicator_size',
				[
					'label' => esc_html__( 'Indicator Size', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 50,
					'min' => 20,
					'max' => 400,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}}' => '--pointer-size: {{VALUE}}px',
					],
					'condition' => [
						'cursor!' => '',
						'center_indicator!' => '',
					],
				]
			);

			$this->add_control(
				'mouse_cursor',
				[
					'label' => esc_html__( 'Show Mouse Cursor', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
					'separator' => 'before',
				]
			);


		$this->end_controls_section();

	}

	protected function render() {

		$this->_settings = $this->get_settings_for_display();

		if( empty($this->_settings['before_img']['id']) && empty($this->_settings['after_img']['id']) ){
			echo esc_html__('Please select the Before and After images.', 'rey-core');
			return;
		}

		$config = [
			'cursor'      => $this->_settings['cursor']           === 'yes',
			'start'       => absint($this->_settings['start']),
			'orientation' => $this->get_orientation(),
			'indicator'   => $this->_settings['center_indicator'] === 'yes',
		];

		$attrs['data-skip-lazy'] = 1;
		$attrs['data-no-lazy'] = 1;
		$attrs['loading'] = 'eager';
		$attrs['class'] = 'no-lazy';

		$el_classes = [
			$this->_settings['mouse_cursor'] === '' ? '--no-mouse' : '',
			$this->_settings['stretch'] !== '' ? '--stretch' : '',
		];

		printf('<div class="rey-beforeAfter %5$s" data-o="%4$s" data-config=\'%3$s\'>%1$s%2$s</div>',
			wp_get_attachment_image($this->_settings['before_img']['id'], $this->_settings['image_size'], false, $attrs),
			wp_get_attachment_image($this->_settings['after_img']['id'], $this->_settings['image_size'], false, $attrs),
			wp_json_encode($config),
			$this->get_orientation(),
			implode(' ', $el_classes)
		);

		reycore_assets()->add_styles($this->get_style_name());
		reycore_assets()->add_scripts($this->get_script_name('scripts'));

	}

	public function get_orientation(){

		$or = [
			'h'  => 'horizontal',
			'v'  => 'vertical',
		];

		return esc_attr($or[$this->_settings['orientation']]);
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
