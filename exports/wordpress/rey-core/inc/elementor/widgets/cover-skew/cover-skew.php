<?php
namespace ReyCore\Elementor\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class CoverSkew extends \ReyCore\Elementor\WidgetsBase {

	private $_items = [];

	public static function get_rey_config(){
		return [
			'id' => 'cover-skew',
			'title' => __( 'Cover - Skew', 'rey-core' ),
			'icon' => 'rey-font-icon-general-r',
			'categories' => [ 'rey-theme-covers' ],
			'keywords' => [],
			'css' => [
				'assets/style[rtl].css',
			],
			'js' => [
				'assets/script.js',
			],
		];
	}

	public function __construct( $data = [], $args = null ) {

		do_action('reycore/elementor/widget/construct', $data);

		if( ! empty($data) ){
			\ReyCore\Plugin::instance()->elementor->frontend->add_delay_js_scripts('cover-skew', ['rey-script']);
		}

		parent::__construct( $data, $args );
	}

	public function rey_get_script_depends() {
		return [ 'reycore-widget-cover-skew-scripts' ];
	}

	public function get_custom_help_url() {
		return reycore__support_url('kb/rey-elements-covers/#skew-cover');
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

		$items = new \Elementor\Repeater();

		$items->add_control(
			'image',
			[
			   'label' => __( 'Image', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::MEDIA,
				'default' => [
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				],
			]
		);

		$items->add_control(
			'title',
			[
				'label'       => __( 'Title', 'rey-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
			]
		);

		$items->add_control(
			'button_text',
			[
				'label' => __( 'Text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true,
				],
				'default' => __( 'Click here', 'rey-core' ),
				'placeholder' => __( 'Click here', 'rey-core' ),
			]
		);

		$items->add_control(
			'button_url',
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

		$items->add_control(
			'primary_color',
			[
				'label' => __( 'Primary Color (backgrounds)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} h3:before' => 'background-color: {{VALUE}}',
					'{{WRAPPER}} {{CURRENT_ITEM}} .coverSkew-slideBg' => 'background-color: {{VALUE}}',
				],
			]
		);

		$items->add_control(
			'secondary_color',
			[
				'label' => __( 'Secondary Color (text)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} h3' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'items',
			[
				'label' => __( 'Items', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $items->get_controls(),
				'default' => [
					[
						'image' => [
							'url' => \Elementor\Utils::get_placeholder_image_src(),
						],
						'captions' => 'yes',
						'title' => __( 'Title Text #1', 'rey-core' ),
						'button_text' => __( 'Button Text #1', 'rey-core' ),
						'button_url' => [
							'url' => '#',
						],
					],
					[
						'image' => [
							'url' => \Elementor\Utils::get_placeholder_image_src(),
						],
						'captions' => 'yes',
						'title' => __( 'Title Text #2', 'rey-core' ),
						'button_text' => __( 'Button Text #2', 'rey-core' ),
						'button_url' => [
							'url' => '#',
						],
					],
				]
			]
		);


		$this->add_group_control(
			\Elementor\Group_Control_Image_Size::get_type(),
			[
				'name' => 'image', // Usage: `{name}_size` and `{name}_custom_dimension`, in this case `image_size` and `image_custom_dimension`.
				'default' => 'large',
				// 'separator' => 'before',
				'exclude' => ['custom'],
				// TODO: add support for custom size thumbnails #40
			]
		);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_slider',
			[
				'label' => __( 'Slider Settings', 'rey-core' ),
			]
		);

		$this->add_control(
			'animate_entry',
			[
				'label' => __( 'Animate Entry', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'autoplay',
			[
				'label' => __( 'Autoplay', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->add_control(
			'autoplay_duration',
			[
				'label' => __( 'Autoplay Duration (ms)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 5000,
				'min' => 2000,
				'max' => 20000,
				'step' => 50,
				'condition' => [
					'autoplay!' => '',
				],
			]
		);

		$this->add_control(
			'dots',
			[
				'label' => __( 'Dots Navigation', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->add_control(
			'dots_style',
			[
				'label' => __( 'Dots Style', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'lines',
				'options' => [
					'lines'  => __( 'Lines', 'rey-core' ),
					'numbers'  => __( 'Numbers', 'rey-core' ),
				],
				'condition' => [
					'dots!' => '',
				],
			]
		);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_layout',
			[
				'label' => __( 'Layout', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'next_style',
			[
				'label' => __( 'Next Container Style', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'default',
				'options' => [
					'default'  => __( 'Default', 'rey-core' ),
					'off-slider'  => __( 'Off-slider', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'slide_bar',
			[
				'label' => __( 'Slide Colored Bar', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'bar_opacity',
			[
				'label' => __( 'Bar Opacity', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'max' => 1,
						'min' => 0.10,
						'step' => 0.01,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .rey-coverSkew .coverSkew-slideBg' => 'opacity: {{SIZE}};',
				],
				'condition' => [
					'slide_bar' => 'yes',
				],
			]
		);

		$this->add_control(
			'mask_transition',
			[
				'label' => esc_html__( 'Masking transition', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'mask_1_color',
			[
				'label' => esc_html__( 'Mask #1 background color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-coverSkew .coverSkew-slideMask--1' => 'background-color: {{VALUE}}',
				],
				'condition' => [
					'mask_transition!' => '',
				],
			]
		);

		$this->add_control(
			'mask_2_color',
			[
				'label' => esc_html__( 'Mask #2 background color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-coverSkew .coverSkew-slideMask--2' => 'background-color: {{VALUE}}',
				],
				'condition' => [
					'mask_transition!' => '',
				],
			]
		);

		$this->add_control(
			'ovhidden',
			[
				'label' => esc_html__( 'Page Overflow Hidden', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'hidden',
				'default' => '',
				'selectors' => [
					'body.elementor-page' => 'overflow-x: {{VALUE}}',
				],
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

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'title',
				'label' => esc_html__('Title Typography', 'rey-core'),
				'global' => [
					'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}} .rey-coverSkew .coverSkew-captions h3',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'button',
				'label' => esc_html__('Button Typography', 'rey-core'),
				'selector' => '{{WRAPPER}} .rey-coverSkew .coverSkew-captions .rey-buttonSkew',
			]
		);

		$this->add_control(
			'btn_text_color',
			[
				'label' => esc_html__( 'Button Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-coverSkew .coverSkew-captions .buttonSkew-center span' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'btn_text_color_hover',
			[
				'label' => esc_html__( 'Button Text Hover Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-coverSkew .coverSkew-captions .buttonSkew-center span:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'btn_color',
			[
				'label' => esc_html__( 'Button Background Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-coverSkew .coverSkew-captions .rey-buttonSkew' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'btn_color_hover',
			[
				'label' => esc_html__( 'Button Background Hover Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-coverSkew .coverSkew-captions .rey-buttonSkew:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_section();
	}


	public function render_start($settings){

		$this->add_render_attribute( 'wrapper', 'class', [
			'rey-coverSkew',
			'--loading',
			'rey-coverSkew-next--' . $settings['next_style'],
			$settings['mask_transition'] !== '' ? '--mask-transition' : '',
			$settings['animate_entry'] !== '' ? '--animated' : '--non-animated',
		] );

		if( count($this->_items) == 1 || 'yes' !== $settings['slide_bar'] ){
			$this->add_render_attribute( 'wrapper', 'class', 'rey-coverSkew--noBar' );
		}

		if( count($this->_items) > 1 ) {
			$this->add_render_attribute( 'wrapper', 'data-slider-settings', wp_json_encode([
				'autoplay' => $settings['autoplay'] !== '',
				'autoplayDuration' => $settings['autoplay_duration'],
				'dots' => $settings['dots'] !== '',
			]) );
		}

		?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
		<?php
	}

	public function render_end(){
		?>
		</div>
		<?php
		echo \ReyCore\Elementor\Helper::edit_mode_widget_notice(['full_viewport', 'tabs_modal']);
	}

	public function render_slides($settings){

		if( $settings['animate_entry'] !== '' ){
			echo '<div class="coverSkew-loader"></div>';
		} ?>

		<div class="coverSkew-bg"></div>

		<div class="coverSkew-slides">
			<div class="coverSkew-slidesInner">
				<?php
				foreach($this->_items as $item): ?>
				<div class="coverSkew-slide elementor-repeater-item-<?php echo $item['_id'] ?>">
					<div class="coverSkew-slideInner">
						<?php if( $settings['mask_transition'] === 'yes' ): ?>
							<div class="coverSkew-slideMask coverSkew-slideMask--1">
								<div class="coverSkew-slideMaskInner"></div>
							</div>
							<div class="coverSkew-slideMask coverSkew-slideMask--2">
								<div class="coverSkew-slideMaskInner"></div>
							</div>
						<?php endif; ?>
						<div class="coverSkew-slideBg"></div>
						<?php
						echo reycore__get_attachment_image( [
							'image' => $item['image'],
							'size' => $settings['image_size'],
						] );
						?>
					</div>
				</div>
				<?php
				endforeach; ?>
			</div>
		</div>
		<?php
	}

	public function render_captions(){
		?>
		<div class="coverSkew-captions">
			<?php
			foreach($this->_items as $item): ?>
			<div class="coverSkew-captionItem elementor-repeater-item-<?php echo $item['_id'] ?>">

				<?php if( $title = $item['title'] ): ?>
					<h3><span><?php echo $title ?></span></h3>
				<?php endif; ?>

				<?php
				if( $button_text = $item['button_text'] ):
					$button = \Elementor\Plugin::instance()->elements_manager->create_element_instance(
						[
							'elType' => 'widget',
							'widgetType' => 'reycore-button-skew',
							'id' => 'reycore-cover-skew-btn-' . $item['_id'],
							'settings' => [
								'text' => $button_text,
								'link' => $item['button_url'],
								'style' => 'filled',
								'align' => 'center'
							],
						]
					);
					$button->print_element();
				endif; ?>

			</div>
			<?php
			endforeach; ?>
		</div>
		<?php
	}


	public function render_next_section( $settings ){
		?>
		<div class="coverSkew-next">
			<div class="coverSkew-nextInner">

				<?php
				foreach($this->_items as $item): ?>
				<div class="coverSkew-nextItem elementor-repeater-item-<?php echo $item['_id'] ?>">
					<?php
					echo reycore__get_attachment_image( [
						'image' => $item['image'],
						'size' => $settings['image_size'],
					] );
					?>
				</div>
				<?php
				endforeach; ?>

				<?php echo reycore__arrowSvg(); ?>

			</div>
		</div>
		<?php
	}

	public function render_nav( $style ){
		?>
		<div class="coverSkew-nav coverSkew-nav--<?php echo $style; ?>">
			<?php
			foreach($this->_items as $key => $item): ?>
				<span data-index="<?php echo $key; ?>"><?php printf("%02d", $key + 1); ?></span>
			<?php
			endforeach; ?>
		</div>
		<?php
	}

	protected function render() {

		$settings = $this->get_settings_for_display();

		$this->_items = $settings['items'];

		$this->render_start($settings);

		if( !empty($this->_items) ){

			$this->render_slides($settings);
			$this->render_captions();
			if( count($this->_items) > 1 ){
				$this->render_next_section($settings);
			}

			if( count($this->_items) > 1 && $settings['dots'] == 'yes' ){
				$this->render_nav( $settings['dots_style'] );
			}
		}

		$this->render_end();

		reycore_assets()->add_styles($this->get_style_name());
		reycore_assets()->add_scripts( $this->rey_get_script_depends() );

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
