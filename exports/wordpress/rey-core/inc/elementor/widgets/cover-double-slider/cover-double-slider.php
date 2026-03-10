<?php
namespace ReyCore\Elementor\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class CoverDoubleSlider extends \ReyCore\Elementor\WidgetsBase {

	private $_items = [];
	private $_images = [];
	private $_settings = [];
	private $__link_props = [];

	public static function get_rey_config(){
		return [
			'id' => 'cover-double-slider',
			'title' => __( 'Cover - Double Slider', 'rey-core' ),
			'icon' => 'rey-font-icon-general-r',
			'categories' => [ 'rey-theme-covers' ],
			'keywords' => [],
			'css' => [
				'!assets/style[rtl].css',
			],
			'js' => [
				'assets/script.js',
			],
		];
	}

	public function __construct( $data = [], $args = null ) {

		if( ! empty($data) ):
			\ReyCore\Plugin::instance()->elementor->frontend->add_delay_js_scripts('cover-double-slider', ['rey-script', 'rey-videos', 'animejs']);
		endif;

		parent::__construct( $data, $args );
	}

	public function rey_get_script_depends() {
		return [ 'animejs', 'reycore-widget-cover-double-slider-scripts' ];
	}

	public function get_custom_help_url() {
		return reycore__support_url('kb/rey-elements-covers/#double-slider-cover');
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
			'slide_type',
			[
				'label' => __( 'Media Type', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'default' => 'image',
				'options' => [
					'image' => [
						'title' => _x( 'Image', 'Background Control', 'rey-core' ),
						'icon' => 'eicon-image',
					],
					'video' => [
						'title' => _x( 'Self Hosted Video', 'Background Control', 'rey-core' ),
						'icon' => 'eicon-video-camera',
					],
					'youtube' => [
						'title' => _x( 'YouTube Video', 'Background Control', 'rey-core' ),
						'icon' => 'eicon-youtube',
					],
				],
			]
		);

		$items->add_control(
			'html_video',
			[
				'label' => __( 'Self Hosted Video URL', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'label_block' => true,
				'description' => __( 'Link to video file (mp4 is recommended).', 'rey-core' ),
				'conditions' => [
					'terms' => [
						[
							'name' => 'slide_type',
							'operator' => '==',
							'value' => 'video',
						],
					],
				],
				'wpml' => false,
				'ai' => [
					'active' => false,
				],
			]
		);

		$items->add_control(
			'yt_video',
			[
				'label' => __( 'YouTube URL', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'label_block' => true,
				'conditions' => [
					'terms' => [
						[
							'name' => 'slide_type',
							'operator' => '==',
							'value' => 'youtube',
						],
					],
				],
				'wpml' => false,
				'ai' => [
					'active' => false,
				],
			]
		);

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
			'image_as_video_fallback',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => __( 'Use the image as fallback for videos on mobile.', 'rey-core' ),
				'content_classes' => 'elementor-descriptor',
				'conditions' => [
					'terms' => [
						[
							'name' => 'slide_type',
							'operator' => '!=',
							'value' => 'image',
						],
					],
				],
			]
		);

		$items->add_control(
			'title_1',
			[
				'label'       => __( 'Title line #1', 'rey-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
			]
		);

		$items->add_control(
			'title_2',
			[
				'label'       => __( 'Title line #2', 'rey-core' ),
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
					'{{WRAPPER}} {{CURRENT_ITEM}}' => '--primary-color: {{VALUE}}',
				],
			]
		);

		$items->add_control(
			'secondary_color',
			[
				'label' => __( 'Secondary Color (text)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}}' => '--secondary-color: {{VALUE}}',
				],
			]
		);

		$items->add_control(
			'v_align',
			[
				'label' => esc_html__( 'Caption - Vertical Alignment', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'flex-end',
				'label_block' => true,
				'options' => [
					'flex-start'  => esc_html__( 'Top', 'rey-core' ),
					'center'  => esc_html__( 'Middle', 'rey-core' ),
					'flex-end'  => esc_html__( 'Bottom', 'rey-core' ),
				],
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}}' => 'align-items: {{VALUE}}',
				],
			]
		);

		$items->add_control(
			'h_align',
			[
				'label' => esc_html__( 'Caption - Horizontal Alignment', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'start',
				'label_block' => true,
				'options' => [
					'start'  => esc_html__( 'Left', 'rey-core' ),
					'center'  => esc_html__( 'Center', 'rey-core' ),
					'end'  => esc_html__( 'Right', 'rey-core' ),
				],
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}}' => 'justify-content: {{VALUE}}; text-align: {{VALUE}};',
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
						'title_1' => __( 'Title Text #1', 'rey-core' ),
						'button_text' => __( 'Button Text #1', 'rey-core' ),
						'button_url' => [
							'url' => '#',
						],
					],
					[
						'image' => [
							'url' => \Elementor\Utils::get_placeholder_image_src(),
						],
						'title_1' => __( 'Title Text #2', 'rey-core' ),
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
				'separator' => 'before',
			]
		);

		$this->add_control(
			'wrap_in_link',
			[
				'label' => esc_html__( 'Wrap slides in link', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
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
					'{{WRAPPER}} .cDbSlider-navBullets button.--active i' => 'transition-duration: {{VALUE}}ms;',
				],
			]
		);

		$this->add_control(
			'navigation',
			[
				'label' => __( 'Arrows Navigation', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'bullets',
			[
				'label' => __( 'Bullets Navigation', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
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
			'auto_height',
			[
				'label' => esc_html__( 'Auto Height (desktops)', 'rey-core' ),
				'description' => esc_html__( 'This option will make the height automatic, decreasing he header height and a bottom margin.', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'auto',
				'return_value' => 'auto',
				'prefix_class' => 'cDbSlider--ah-',
			]
		);

		$this->add_responsive_control(
			'height',
			[
				'label' => __( 'Height', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'desktop_default' => [
					'size' => 780,
					'unit' => 'px',
				],
				'tablet_default' => [
					'size' => 380,
					'unit' => 'px',
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
					'{{WRAPPER}} .cDbSlider-slides' => 'min-height: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'auto_height!' => 'auto',
				],
			]
		);


		$this->add_responsive_control(
			'middle_distance',
			[
			   'label' => esc_html__( 'Middle Gap Size', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 220,
						'step' => 1,
					],
					'em' => [
						'min' => 0,
						'max' => 5.0,
					],
				],
				'default' => [],
				'selectors' => [
					'{{WRAPPER}} .rey-coverDoubleSlider' => '--middle-distance:{{SIZE}}{{UNIT}};',
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
				'selector' => '{{WRAPPER}} .rey-coverDoubleSlider .cDbSlider-caption h2',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'button',
				'label' => esc_html__('Button Typography', 'rey-core'),
				'selector' => '{{WRAPPER}} .rey-coverDoubleSlider .cDbSlider-caption .rey-buttonSkew',
			]
		);

		$this->add_control(
			'prev_arrow_color',
			[
				'label' => esc_html__( 'Prev. Arrow Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cDbSlider-navPrev .rey-arrowSvg' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'prev_arrow_bg_color',
			[
				'label' => esc_html__( 'Prev. Arrow Background Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cDbSlider-navPrev .rey-arrowSvg' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'next_arrow_color',
			[
				'label' => esc_html__( 'Next Arrow Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cDbSlider-navNext .rey-arrowSvg' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'next_arrow_bg_color',
			[
				'label' => esc_html__( 'Next Arrow Background Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cDbSlider-navNext .rey-arrowSvg' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_section();
	}

	public function render_start(){

		$this->add_render_attribute( 'wrapper', 'class', [
			'rey-coverDoubleSlider',
			'--loading',
			$this->_settings['autoplay'] !== '' ? '--autoplay' : '',
			count($this->_items) % 2 === 0 ? '--even' : '--odd'
		]);

		if( count($this->_items) > 2 ) {
			$this->add_render_attribute( 'wrapper', 'data-slider-settings', wp_json_encode([
				'autoplay' => $this->_settings['autoplay'] !== '',
				'autoplayDuration' => ! empty($this->_settings['autoplay_duration']) ? $this->_settings['autoplay_duration'] : 5000,
				'navigation' => $this->_settings['navigation'] !== '',
			]) );
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

		if( count($this->_items) < 2 ){
			printf('<div class="cDbSlider-empty">%s</div>', esc_html__('Please add more than 1 slide.', 'rey-core'));
			return;
		}

		?>
		<div class="cDbSlider-slides">
			<?php
			$wrap_in_link = $this->_settings['wrap_in_link'] !== '';

			foreach( $this->_items as $key => $item ):

				if( isset($item['button_url']['url']) && ($url = $item['button_url']['url']) ){

					$this->add_render_attribute( $item['_id'] , 'href', $url );

					if( $item['button_url']['is_external'] ){
						$this->add_render_attribute( $item['_id'] , 'target', '_blank' );
					}

					if( $item['button_url']['nofollow'] ){
						$this->add_render_attribute( $item['_id'] , 'rel', 'nofollow' );
					}

				}

				$this->__link_props = [
					'slide_tag' => 'div',
					'slide_attr' => '',
					'anchor_tag' => 'a',
					'anchor_attr' => $this->get_render_attribute_string($item['_id']),
				];

				if( $wrap_in_link ){

					$this->__link_props = [
						'slide_tag' => 'a',
						'slide_attr' => $this->get_render_attribute_string($item['_id']),
						'anchor_tag' => 'div',
						'anchor_attr' => '',
					];

				}

				printf('<%s %s class="cDbSlider-slideItem elementor-repeater-item-%s %s">',
					$this->__link_props['slide_tag'],
					$this->__link_props['slide_attr'],
					esc_attr($item['_id']),
					$key === (count($this->_items) - 1) && $key % 2 === 0 ? '--single' : ''
				);

					$slide_content = '';

					$slide_image = reycore__get_attachment_image( [
						'image' => $item['image'],
						'size' => $this->_settings['image_size'],
						'key' => 'image',
						'settings' => $this->_settings,
						'attributes' => ['class'=>'cDbSlider-slideContent']
					] );

					if( $item['slide_type'] === 'video' )
					{
						reycore_assets()->add_scripts( 'rey-videos' );

						if( $slide_image ){
							reycore_assets()->add_styles('reycore-videos');
						}

						$slide_content = reycore__get_video_html([
							'video_url' => $item['html_video'],
							'class' => 'cDbSlider-slideContent',
							'mobile' => true,
						]);
					}

					elseif( $item['slide_type'] === 'youtube' )
					{
						reycore_assets()->add_scripts( 'rey-videos' );

						if( $slide_image ){
							reycore_assets()->add_styles('reycore-videos');
						}

						$slide_content = reycore__get_youtube_iframe_html([
							'video_url' => $item['yt_video'],
							'class' => 'cDbSlider-slideContent',
							'html_id' => 'yt' . $item['_id'],
							// only show video preview if image not provided
							'add_preview_image' => empty($slide_image),
							'mobile' => true,
						]);
					}

					echo $slide_content;
					echo $slide_image;
					?>

					<?php $this->render_caption($item); ?>

					<div class="cDbSlider-slideItem-bgCurtain"></div>
				<?php
				printf('</%s>', $this->__link_props['slide_tag']);
			endforeach; ?>
		</div>
		<?php
	}

	public function render_caption( $item ){

		$url_start = sprintf('<%s %s class="cDbSlider-caption-link">', $this->__link_props['anchor_tag'], $this->__link_props['anchor_attr']);
		$url_end = sprintf('</%s>', $this->__link_props['anchor_tag']);
		?>

		<div class="cDbSlider-caption">

			<?php if( $url_start ) {
				echo $url_start;
			} ?>

			<?php if( $title1 = $item['title_1'] ): ?>
				<div class="cDbSlider-captionEl cDbSlider-captionTitle cDbSlider-captionTitle--1">
					<h2><?php echo $title1 ?></h2>
				</div>
			<?php endif; ?>

			<?php if( $title2 = $item['title_2'] ): ?>
				<div class="cDbSlider-captionEl cDbSlider-captionTitle cDbSlider-captionTitle--2">
					<h2><?php echo $title2 ?></h2>
				</div>
			<?php endif; ?>

			<?php
			if( $button_text = $item['button_text'] ): ?>
				<div class="cDbSlider-captionEl">
					<div class="cDbSlider-captionBtn">
						<?php if($button_text): ?>
							<span><?php echo $button_text; ?></span>
						<?php endif; ?>
						<?php echo reycore__arrowSvg(); ?>
					</div>
				</div>
			<?php
			endif; ?>

			<?php if( $url_end ) {
				echo $url_end;
			} ?>

		</div>
		<?php
	}

	public function render_nav(){
		?>
		<div class="cDbSlider-nav">
			<button class="cDbSlider-navPrev" aria-label="<?php esc_html_e('Previous', 'rey-core') ?>">
				<?php echo reycore__arrowSvg(false); ?>
			</button>
			<button class="cDbSlider-navNext" aria-label="<?php esc_html_e('Next', 'rey-core') ?>">
				<?php echo reycore__arrowSvg(); ?>
			</button>
		</div>
		<?php
	}

	public function render_bullets(){
		?>
		<div class="cDbSlider-navBullets">
			<?php
			foreach($this->_items as $key => $item):?>
				<button data-index="<?php echo $key; ?>" class="<?php echo $key === 0 ? '--active':''; ?>" aria-label="<?php esc_html_e('Switch', 'rey-core') ?>">
					<i></i>
				</button>
			<?php
			endforeach; ?>
		</div>
		<?php
	}


	protected function render() {

		$this->_settings = $this->get_settings_for_display();
		$this->_items = $this->_settings['items'];

		$this->render_start();

		if( !empty($this->_items) ){

			$this->render_slides();

			if( count($this->_items) > 2 ){
				if( $this->_settings['navigation'] === 'yes' ){
					$this->render_nav();
				}
				if( $this->_settings['bullets'] === 'yes' ){
					$this->render_bullets();
				}
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
