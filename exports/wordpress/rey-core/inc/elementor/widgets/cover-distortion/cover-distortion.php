<?php
namespace ReyCore\Elementor\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class CoverDistortion extends \ReyCore\Elementor\WidgetsBase {

	private $_items = [];
	private $_images = [];
	private $_settings = [];

	public static function get_rey_config(){
		return [
			'id' => 'cover-distortion',
			'title' => __( 'Cover - Distortion Slider', 'rey-core' ),
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

	public function rey_get_script_depends() {
		return [ 'threejs', 'distortion-app', 'animejs', 'reycore-widget-cover-distortion-scripts' ];
	}

	public function get_custom_help_url() {
		return reycore__support_url('kb/rey-elements-covers/#skew-cover');
	}

	public function default_image_size(){
		return [
			'width' => 1280,
			'height' => 720,
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
			'label',
			[
				'label'       => __( 'Label', 'rey-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
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
				'global' => [
					'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Colors::COLOR_ACCENT,
				],
			]
		);

		$items->add_control(
			'secondary_color',
			[
				'label' => __( 'Secondary Color (text)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
			]
		);

		$items->add_control(
			'alignment',
			[
				'label' => __( 'Text align', 'rey-core' ),
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
					'{{WRAPPER}} {{CURRENT_ITEM}}' => 'text-align: {{VALUE}}',
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
				'default' => 'custom',
				'separator' => 'before',
				'fields_options' => [
					'custom_dimension' => [
						'default' => $this->default_image_size(),
					],
				],
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
				'selectors' => [
					'{{WRAPPER}} .cDistortion-navBullets button.--active i' => 'transition-duration: {{VALUE}}ms;',
				],
			]
		);

		$this->add_control(
			'navigation',
			[
				'label' => __( 'Navigation', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->add_control(
			'transition_duration',
			[
				'label' => __( 'Transition Duration (ms)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 1600,
				'min' => 500,
				'max' => 10000,
				'step' => 50,
			]
		);

		$this->add_control(
			'transition_type',
			[
				'label' => esc_html__( 'Transition Effect', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => esc_html__( 'Morph into next slide', 'rey-core' ),
					'random'  => esc_html__( 'Random', 'rey-core' ),
					'0'  => esc_html__( 'Effect #1', 'rey-core' ),
					'1'  => esc_html__( 'Effect #2', 'rey-core' ),
					'2'  => esc_html__( 'Effect #3', 'rey-core' ),
					'3'  => esc_html__( 'Effect #4', 'rey-core' ),
					'4'  => esc_html__( 'Effect #5', 'rey-core' ),
					'5'  => esc_html__( 'Effect #6', 'rey-core' ),
					'6'  => esc_html__( 'Effect #7', 'rey-core' ),
					'7'  => esc_html__( 'Effect #8', 'rey-core' ),
					'8'  => esc_html__( 'Effect #9', 'rey-core' ),
					'9'  => esc_html__( 'Effect #10', 'rey-core' ),
					'10'  => esc_html__( 'Effect #11', 'rey-core' ),
					'11'  => esc_html__( 'Effect #12', 'rey-core' ),
					'12'  => esc_html__( 'Effect #13', 'rey-core' ),
					'13'  => esc_html__( 'Effect #14', 'rey-core' ),
					'14'  => esc_html__( 'Effect #15', 'rey-core' ),
					'15'  => esc_html__( 'Effect #16', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'transition_in_intensity',
			[
				'label' => esc_html__( 'IN Transition Intensity', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 0.2,
				'min' => 0.05,
				'max' => 1,
				'step' => 0.05,
			]
		);

		$this->add_control(
			'transition_out_intensity',
			[
				'label' => esc_html__( 'OUT Transition Intensity', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 0.2,
				'min' => 0.05,
				'max' => 1,
				'step' => 0.05,
			]
		);

		$this->add_control(
			'transition_direction',
			[
				'label' => esc_html__( 'Vertical direction', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);
		$this->add_control(
			'transition_scale',
			[
				'label' => esc_html__( 'Scale effect', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->add_control(
			'transition_easing',
			[
				'label' => esc_html__( 'Transition Easing', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'easeInOutCubic',
				'options' => [
					'easeInQuad' => 'easeInQuad',
					'easeInCubic' => 'easeInCubic',
					'easeInQuart' => 'easeInQuart',
					'easeInQuint' => 'easeInQuint',
					'easeInSine' => 'easeInSine',
					'easeInExpo' => 'easeInExpo',
					'easeInCirc' => 'easeInCirc',
					'easeInOutQuad' => 'easeInOutQuad',
					'easeInOutCubic' => 'easeInOutCubic',
					'easeInOutQuart' => 'easeInOutQuart',
					'easeInOutQuint' => 'easeInOutQuint',
					'easeInOutSine' => 'easeInOutSine',
					'easeInOutExpo' => 'easeInOutExpo',
					'easeInOutCirc' => 'easeInOutCirc',
					'easeOutQuad' => 'easeOutQuad',
					'easeOutCubic' => 'easeOutCubic',
					'easeOutQuart' => 'easeOutQuart',
					'easeOutQuint' => 'easeOutQuint',
					'easeOutSine' => 'easeOutSine',
					'easeOutExpo' => 'easeOutExpo',
					'easeOutCirc' => 'easeOutCirc',
				]
			]
		);

		$this->add_control(
			'delay_until_interaction',
			[
				'label' => esc_html__( 'Delay until interaction', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'replay_first',
			[
				'label' => esc_html__( 'Run animation of first slide', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
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
			'content_width',
			[
				'label' => __( 'Content Width', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 500,
						'max' => 1600,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .cDistortion-captions' => 'max-width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .cDistortion-nav' => 'max-width: {{SIZE}}{{UNIT}};',
				],
				'separator' => 'none',
			]
		);

		$this->add_responsive_control(
			'custom_height',
			[
				'label' => __( 'Height', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'default' => [
					'size' => 100,
					'unit' => 'vh',
				],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1440,
					],
					'vh' => [
						'min' => 0,
						'max' => 100,
					],
					'vw' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'size_units' => [ 'px', 'vh', 'vw' ],
				'selectors' => [
					'{{WRAPPER}} .rey-coverDistortion' => 'min-height: {{SIZE}}{{UNIT}};',
				],
			]
		);


		$this->add_control(
			'overlay_bg',
			[
				'label' => esc_html__( 'Overlay background color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => 'rgba(0,0,0,0.3)',
				'selectors' => [
					'{{WRAPPER}} .cDistortion-slidesBg' => 'background-color: {{VALUE}}',
				],
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
				'selector' => '{{WRAPPER}} .rey-coverDistortion .cDistortion-captions h3',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'label',
				'label' => esc_html__('Label Typography', 'rey-core'),
				'global' => [
					'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}} .rey-coverDistortion .cDistortion-captions .cDistortion-captionLabel',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'button',
				'label' => esc_html__('Button Typography', 'rey-core'),
				'selector' => '{{WRAPPER}} .rey-coverDistortion .cDistortion-captions .btn',
			]
		);

		$this->add_control(
			'button_style',
			[
				'label' => __( 'Button Style', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'btn-primary-outline btn-dash',
				'options' => [
					'btn-simple'  => __( 'Link', 'rey-core' ),
					'btn-primary'  => __( 'Primary', 'rey-core' ),
					'btn-secondary'  => __( 'Secondary', 'rey-core' ),
					'btn-primary-outline'  => __( 'Primary Outlined', 'rey-core' ),
					'btn-secondary-outline'  => __( 'Secondary Outlined', 'rey-core' ),
					'btn-line-active'  => __( 'Underlined', 'rey-core' ),
					'btn-line'  => __( 'Hover Underlined', 'rey-core' ),
					'btn-primary-outline btn-dash'  => __( 'Primary Outlined & Dash', 'rey-core' ),
					'btn-primary-outline btn-dash btn-rounded'  => __( 'Primary Outlined & Dash & Rounded', 'rey-core' ),
				],
			]
		);

		$this->end_controls_section();
	}

	public function render_start(){

		$this->add_render_attribute( 'wrapper', 'class', [
			'rey-coverDistortion',
			'--loading',
			$this->_settings['autoplay'] !== '' ? '--autoplay' : ''
		]);

		if( count($this->_items) > 1 ) {

			$slider_settings = [
				'autoplay' => $this->_settings['autoplay'] !== '',
				'autoplayDuration' => ! empty($this->_settings['autoplay_duration'] ) ? $this->_settings['autoplay_duration'] : 5000,
				'navigation' => $this->_settings['navigation'] !== '',
				'transitionDuration' => $this->_settings['transition_duration'],
				'effect' => $this->_settings['transition_type'],
				'vertical' => $this->_settings['transition_direction'] === 'yes',
				'scaleEffect' => $this->_settings['transition_scale'] === 'yes',
				'easing' => $this->_settings['transition_easing'],
				'delay' => (isset($this->_settings['delay_until_interaction']) && $this->_settings['delay_until_interaction'] === 'yes'),
				'replay_first' => (isset($this->_settings['replay_first']) && $this->_settings['replay_first'] === 'yes'),
			];

			if( ($transition_in_intensity = $this->_settings['transition_in_intensity']) && is_numeric($transition_in_intensity) ){
				$slider_settings['intensity1'] = number_format($transition_in_intensity, 1);
			}

			if( ($transition_out_intensity = $this->_settings['transition_out_intensity']) && is_numeric($transition_out_intensity) ){
				$slider_settings['intensity2'] = number_format($transition_out_intensity, 1);
			}

			$this->add_render_attribute( 'wrapper', 'data-slider-settings', wp_json_encode($slider_settings) );

			if( isset($this->_settings[ 'image_custom_dimension' ]['width']) && 1000 == $this->_settings[ 'image_custom_dimension' ]['width'] ){
				$this->_settings[ 'image_custom_dimension' ] = $this->default_image_size();
			}

			foreach( $this->_items as $item ){

				$this->_images[] = [
					'url' => reycore__get_attachment_image( [
						'image'      => $item['image'],
						'size'       => 'custom',
						'key'        => 'image',
						'settings'   => $this->_settings,
						'return_url' => true
					] ),
					'bg-color' => esc_attr($item['primary_color']),
					'color' => esc_attr($item['secondary_color'])
				];
			}

			$this->add_render_attribute( 'wrapper', 'data-images', wp_json_encode( $this->_images ) );
		}
		?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
		<?php
	}

	public function render_end(){
		?></div><?php
		echo \ReyCore\Elementor\Helper::edit_mode_widget_notice(['full_viewport', 'tabs_modal']);
	}

	public function render_slides(){

		if( ! isset($this->_images[0]) ){
			return;
		}
		?>

		<div class="cDistortion-slides --show-on-loading">
			<?php
				printf('<img src="%s" width="%s" height="%s">', $this->_images[0]['url'][0], $this->_images[0]['url'][1], $this->_images[0]['url'][2]);
			?>
		</div>

		<div class="cDistortion-canvasWrapper"></div>

		<div class="cDistortion-slidesBg --show-on-loading" style="background-color:<?php echo $this->_images[0]['bg-color']; ?>"></div>
		<?php
	}

	public function render_captions(){
		?>
		<div class="cDistortion-captions --show-on-loading">

			<?php
			foreach($this->_items as $key => $item): ?>
			<div class="cDistortion-captionItem elementor-repeater-item-<?php echo $item['_id'] ?>">

				<?php if( $label = $item['label'] ): ?>
					<h5 class="cDistortion-captionEl cDistortion-captionLabel"><?php echo $label ?></h5>
				<?php endif; ?>

				<?php if( $title = $item['title'] ): ?>
					<h3 class="cDistortion-captionEl cDistortion-captionTitle"><?php echo $title ?></h3>
				<?php endif; ?>

				<?php
				if( $button_text = $item['button_text'] ): ?>
					<div class="cDistortion-captionEl cDistortion-captionBtn">
						<?php
						reycore_assets()->add_styles('rey-buttons');
						$this->add_render_attribute( $item['_id'] , 'class', ['btn', $this->_settings['button_style']] );

						if( isset($item['button_url']['url']) && ($url = $item['button_url']['url']) ){
							$this->add_render_attribute( $item['_id'] , 'href', $url );

							if( $item['button_url']['is_external'] ){
								$this->add_render_attribute( $item['_id'] , 'target', '_blank' );
							}

							if( $item['button_url']['nofollow'] ){
								$this->add_render_attribute( $item['_id'] , 'rel', 'nofollow' );
							}
						} ?>
						<a <?php echo  $this->get_render_attribute_string($item['_id']); ?>>
							<?php echo $button_text; ?>
						</a>
					</div>
				<?php
				endif; ?>

			</div>
			<?php
			endforeach; ?>
		</div>
		<?php
	}

	public function render_nav(){

		?>
		<div class="cDistortion-nav">
			<button class="cDistortion-navPrev" aria-label="<?php esc_html_e('Previous', 'rey-core') ?>">
				<i></i>
			</button>
			<div class="cDistortion-navBullets">
			<?php
				foreach($this->_items as $key => $item): ?>
					<button data-index="<?php echo $key; ?>" class="<?php echo $key === 0 ? '--active':''; ?>" aria-label="<?php esc_html_e('Switch', 'rey-core') ?>">
						<span><?php printf("%02d", $key + 1); ?></span>
						<i></i>
					</button>
				<?php
				endforeach; ?>
			</div>
			<button class="cDistortion-navNext" aria-label="<?php esc_html_e('Next', 'rey-core') ?>">
				<i></i>
			</button>
		</div>
		<?php
	}

	protected function render() {

		reycore_assets()->add_styles($this->get_style_name());
		reycore_assets()->add_scripts( $this->rey_get_script_depends() );

		$this->_settings = $this->get_settings_for_display();
		$this->_items = $this->_settings['items'];

		$this->render_start();

		if( !empty($this->_items) ){

			$this->render_slides();
			$this->render_captions();

			if( count($this->_items) > 1 && $this->_settings['navigation'] === 'yes' ){
				$this->render_nav();
			}
		}

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
