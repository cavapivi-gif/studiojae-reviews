<?php
namespace ReyCore\Elementor\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
exit; // Exit if accessed directly.
}

class CoverNest extends \ReyCore\Elementor\WidgetsBase {

	private $_items = [];

	private $_settings = [];

	public static function get_rey_config(){
		return [
			'id' => 'cover-nest',
			'title' => __( 'Cover - Nest Slider', 'rey-core' ),
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

		do_action('reycore/elementor/widget/construct', $data);

		if( ! empty($data) ):
			\ReyCore\Plugin::instance()->elementor->frontend->add_delay_js_scripts('cover-nest', ['rey-script', 'animejs', 'rey-splide', 'splidejs']);
		endif;

		parent::__construct( $data, $args );
	}

	public function rey_get_script_depends() {
		return [ 'animejs', 'splidejs', 'rey-splide', 'reycore-widget-cover-nest-scripts' ];
	}

	public function get_custom_help_url() {
		return reycore__support_url('kb/rey-elements-covers/#nest-slider');
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
			'overlay_color',
			[
				'label' => __( 'Overlay Background Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}}.cNest-slide:after' => 'background-color: {{VALUE}}',
				],
			]
		);

		$items->add_control(
			'captions',
			[
				'label' => __( 'Enable Captions', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$items->add_control(
			'label',
			[
				'label'       => __( 'Label Text', 'rey-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'conditions' => [
					'terms' => [
						[
							'name' => 'captions',
							'operator' => '!=',
							'value' => '',
						],
					],
				],
			]
		);

		$items->add_control(
			'title',
			[
				'label'       => __( 'Title', 'rey-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'conditions' => [
					'terms' => [
						[
							'name' => 'captions',
							'operator' => '!=',
							'value' => '',
						],
					],
				],
			]
		);

		$items->add_control(
			'subtitle',
			[
				'label'       => __( 'Subtitle Text', 'rey-core' ),
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'label_block' => true,
				'conditions' => [
					'terms' => [
						[
							'name' => 'captions',
							'operator' => '!=',
							'value' => '',
						],
					],
				],
			]
		);

		$items->add_control(
			'button_text',
			[
				'label' => __( 'Button Text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true,
				],
				'default' => __( 'Click here', 'rey-core' ),
				'placeholder' => __( 'eg: SHOP NOW', 'rey-core' ),
				'conditions' => [
					'terms' => [
						[
							'name' => 'captions',
							'operator' => '!=',
							'value' => '',
						],
					],
				],
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
				'conditions' => [
					'terms' => [
						[
							'name' => 'captions',
							'operator' => '!=',
							'value' => '',
						],
					],
				],
			]
		);

		$items->add_control(
			'text_color',
			[
				'label' => __( 'Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}}.cNest-caption' => 'color: {{VALUE}}',
				],
				'conditions' => [
					'terms' => [
						[
							'name' => 'captions',
							'operator' => '!=',
							'value' => '',
						],
					],
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
						'label' => __( 'Label Text #1', 'rey-core' ),
						'title' => __( 'Title Text #1', 'rey-core' ),
						'subtitle' => __( 'Subtitle Text #1', 'rey-core' ),
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
						'label' => __( 'Label Text #2', 'rey-core' ),
						'title' => __( 'Title Text #2', 'rey-core' ),
						'subtitle' => __( 'Subtitle Text #2', 'rey-core' ),
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

		/**
		 * Social Icons
		 */

		$this->start_controls_section(
			'section_social',
			[
				'label' => __( 'Social Icons', 'rey-core' ),
			]
		);

		$social_icons = new \Elementor\Repeater();

		$social_icons->add_control(
			'social',
			[
				'label' => __( 'Select icon', 'rey-core' ),
				'label_block' => true,
				'default' => 'wordpress',
				'type' => 'rey-ajax-list',
				'query_args' => [
					'request' => 'get_social_icons',
				],
			]
		);

		$social_icons->add_control(
			'link',
			[
				'label' => __( 'Link', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::URL,
				'label_block' => true,
				'default' => [
					'is_external' => 'true',
				],
				'placeholder' => __( 'https://your-link.com', 'rey-core' ),
			]
		);

		$this->add_control(
			'social_icon_list',
			[
				'label' => __( 'Social Icons', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $social_icons->get_controls(),
				'default' => [
					[
						'social' => 'facebook',
					],
					[
						'social' => 'twitter',
					],
					[
						'social' => 'google-plus',
					],
				],
				'title_field' => '{{{ social.replace( \'-\', \' \' ).replace( /\b\w/g, function( letter ){ return letter.toUpperCase() } ) }}}',
				'prevent_empty' => false,
			]
		);

		$this->add_control(
			'social_text',
			[
				'label' => __( '"Follow" Text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'FOLLOW US', 'rey-core' ),
				'placeholder' => __( 'eg: FOLLOW US', 'rey-core' ),
			]
		);

		$this->end_controls_section();

	/**
	 * Bottom Content
	 */

		$this->start_controls_section(
			'section_bottom_text',
			[
				'label' => __( 'Bottom Text', 'rey-core' ),
			]
		);

		$this->add_control(
			'bottom_text',
			[
				'label' => __( 'Content', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::WYSIWYG,
				'default' => '',
				'placeholder' => __( 'Type your content here', 'rey-core' ),
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'bottom_text_typo',
				'selector' => '{{WRAPPER}} .cNest-contact',
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
			]
		);

		$this->add_control(
			'bars_nav',
			[
				'label' => __( 'Bars Navigation', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->add_control(
			'arrows',
			[
				'label' => __( 'Arrows Navigation', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->add_control(
			'effect',
			[
				'label' => __( 'Transition Effect', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'scaler',
				'options' => [
					'slide'  => __( 'Slide', 'rey-core' ),
					'fade'  => __( 'Fade In/Out', 'rey-core' ),
					'scaler'  => __( 'Scale & Fade', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'entrance_animation',
			[
				'label' => esc_html__( 'Entrance Animation', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_general_styles',
			[
				'label' => __( 'General Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'bg_color',
			[
				'label' => __( 'Background Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cNest-nestLines svg' => 'stroke: {{VALUE}}',
					'{{WRAPPER}} .cNest-borders span' => 'background-color: {{VALUE}}',
					'{{WRAPPER}} .cNest-loadingBg--1' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'text_color',
			[
				'label' => __( 'Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,

				'selectors' => [
					'{{WRAPPER}} .cNest-footer' => 'color: {{VALUE}}',
					':root' => '--header-text-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'lines',
			[
				'label' => __( 'Enable Decoration Lines', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'lines_width',
			[
				'label' => __( 'Lines Width', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 20,
				'min' => 5,
				'max' => 50,
				'step' => 1,
				'condition' => [
					'lines' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}}' => '--cover-nest-lines-size: {{VALUE}}px',
				],
			]
		);


		$this->add_control(
			'mobile_height',
			[
				'label' => esc_html__( 'Custom Height - Mobile', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 50,
				'max' => 1000,
				'step' => 1,
				'selectors' => [
					'(mobile){{WRAPPER}} .rey-coverNest-mbHeight .cNest-slideContent' => 'height: {{VALUE}}px;',
				],
				'render_type' => 'template',
			]
		);


		$this->end_controls_section();


		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Slider Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);


		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'label_typo',
				'label' => esc_html__('Label Typography', 'rey-core'),
				'selector' => '{{WRAPPER}} .cNest-captionLabel',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'title_typo',
				'label' => esc_html__('Title Typography', 'rey-core'),
				'global' => [
					'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}} .cNest-captionTitle',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'subtitle_typo',
				'label' => esc_html__('Sub-Title Typography', 'rey-core'),
				'selector' => '{{WRAPPER}} .cNest-captionSubtitle',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'button_typo',
				'label' => esc_html__('Button Typography', 'rey-core'),
				'selector' => '{{WRAPPER}} .cNest-captionBtn a',
			]
		);

		$this->add_control(
			'button_style',
			[
				'label' => __( 'Button Style', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'btn-line-active',
				'options' => [
					'btn-simple'  => __( 'Link', 'rey-core' ),
					'btn-primary'  => __( 'Primary', 'rey-core' ),
					'btn-secondary'  => __( 'Secondary', 'rey-core' ),
					'btn-primary-outline'  => __( 'Primary Outlined', 'rey-core' ),
					'btn-secondary-outline'  => __( 'Secondary Outlined', 'rey-core' ),
					'btn-line-active'  => __( 'Underlined', 'rey-core' ),
					'btn-line'  => __( 'Hover Underlined', 'rey-core' ),
					'btn-primary-outline btn-dash'  => __( 'Primary Outlined & Dash', 'rey-core' ),
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_misc_styles',
			[
				'label' => __( 'Misc. Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

		$this->add_control(
			'loading_bg_color_1',
			[
				'label' => __( 'Loader Curtain Color (1st)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cNest-loadingBg--1' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'loading_bg_color_2',
			[
				'label' => __( 'Loader Curtain Color (2nd)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cNest-loadingBg--2' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_section();
	}

	public function render_start(){

		$classes = [
			'rey-coverNest'
		];

		$this->add_render_attribute( 'wrapper', 'class', '' );

		if( ! reycore__preloader_is_active() ){
			$classes[] = '--loading';
		}

		if( $this->_settings['mobile_height'] !== '' ){
			$classes[] = 'rey-coverNest-mbHeight';
		}
		if( $this->_settings['entrance_animation'] === 'yes' ){
			$classes[] = '--animated-entrance';
		}
		else {
			$classes[] = '--no-entrance';
		}

		if( empty($this->_settings['social_icon_list']) ){
			$classes[] = '--no-social';
		}

		$this->add_render_attribute( 'wrapper', 'class', $classes );

		if( count($this->_items) > 1 ) {

			$slide_settings = [
				'effect' => $this->_settings['effect'],
				'autoplay' => $this->_settings['autoplay'] !== '',
				'interval' => $this->_settings['autoplay'] !== '' && $this->_settings['autoplay_duration'] ? $this->_settings['autoplay_duration'] : 0,
				'delayInit' => 500,
				'customArrows' => $this->_settings['arrows'] !== '' ? '.__arrows-' . $this->get_id() : '',
				'customPagination' => ($this->_settings['bars_nav'] !== '' && $this->_items > 1) ? '.__pagination-' . $this->get_id() : '',
			];

			$this->add_render_attribute( 'wrapper', 'data-slider-settings', wp_json_encode($slide_settings) );
		}
		?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
			<div class="cNest-loadingBg cNest-loadingBg--1 cNest--abs"></div>
			<div class="cNest-loadingBg cNest-loadingBg--2 cNest--abs"></div>
				<?php
	}

	public function render_end(){
		?>
		</div>
		<?php
		echo \ReyCore\Elementor\Helper::edit_mode_widget_notice(['full_viewport', 'tabs_modal']);
	}

	public function render_slides(){
		?>
		<div class="splide <?php echo 'splide--' . $this->_settings['effect'] ?>">
			<div class="splide__track">
				<div class="cNest-slider splide__list">
					<?php
					if( !empty($this->_items) ):
						foreach($this->_items as $key => $item):

						$slide_classes = [
							'splide__slide',
							'cNest-slide',
							'cNest-slide--' . $key,
							'elementor-repeater-item-' . $item['_id'],
							// ($key === 0 ? '--slide-active':'')
							($key === 0 ? 'is-active':'')
						];
						?>
						<div class="<?php echo implode(' ', array_map('esc_attr', $slide_classes)); ?>">
							<?php
							echo reycore__get_attachment_image( [
								'image' => $item['image'],
								'size' => $this->_settings['image_size'],
								'attributes' => ['class'=>'cNest-slideContent cNest--abs']
							] ); ?>
						</div>
						<?php
						endforeach;
					endif; ?>
				</div>
			</div>
			<?php $this->render_arrows(); ?>
		</div>
		<?php
	}

	public function render_arrows()
	{
		if( $this->_settings['arrows'] !== '' ): ?>
			<div class="cNest-arrows __arrows-<?php echo $this->get_id(); ?>">
				<?php
					reycore__svg_arrows([
						'class' => 'cNest-arrow',
						'attributes' => [
							'left' => 'data-dir="<"',
							'right' => 'data-dir=">"',
						]
					]);
				?>
			</div>
		<?php endif;
	}

	public function render_captions()
	{
		if( !empty($this->_items) ): ?>
			<div class="cNest-captions" >
			<?php
				foreach($this->_items as $key => $item):
					if( $item['captions'] !== '' ): ?>
					<div class="cNest-caption elementor-repeater-item-<?php echo $item['_id'] ?>">
						<?php if( $label = $item['label'] ): ?>
						<div class="cNest-captionEl cNest-captionLabel"><?php echo $label ?></div>
						<?php endif; ?>

						<?php if( $title = $item['title'] ):
							$tag = apply_filters('reycore/elementor/cover_nest/title_tag', 'h2'); ?>
							<<?php echo esc_attr($tag) ?> class="cNest-captionEl cNest-captionTitle"><?php echo $title ?></<?php echo esc_attr($tag) ?>>
						<?php endif; ?>

						<?php if( $subtitle = $item['subtitle'] ): ?>
						<div class="cNest-captionEl cNest-captionSubtitle"><?php echo $subtitle ?></div>
						<?php endif; ?>

						<?php if( $button_text = $item['button_text'] ): ?>
							<div class="cNest-captionEl cNest-captionBtn">

								<?php
								$url_key = 'url'.$key;

								reycore_assets()->add_styles('rey-buttons');
								$this->add_render_attribute( $url_key , 'class', 'btn ' . $this->_settings['button_style'] );

								if( isset($item['button_url']['url']) && $url = $item['button_url']['url'] ){
									$this->add_render_attribute( $url_key , 'href', $url );

									if( $item['button_url']['is_external'] ){
										$this->add_render_attribute( $url_key , 'target', '_blank' );
									}

									if( $item['button_url']['nofollow'] ){
										$this->add_render_attribute( $url_key , 'rel', 'nofollow' );
									}
								} ?>
								<a <?php echo  $this->get_render_attribute_string($url_key); ?>>
									<?php echo $button_text; ?>
								</a>
							</div>
							<!-- .cNest-btn -->
						<?php endif; ?>

					</div><?php
					endif;
				endforeach; ?>
			</div>
			<?php
		endif;
	}

	public function render_decorations()
	{
		?>
		<div class="cNest-decorations cNest--abs">
			<?php
				$this->render_borders();
				$this->render_lines();
			?>
		</div>
		<?php
	}

	public function render_lines(){

		if( $this->_settings['lines'] === '' ){
			return;
		} ?>

		<div class="cNest-nestLines" data-stroke="<?php echo ! empty($this->_settings['lines_width']) ? esc_attr( $this->_settings['lines_width'] ) : '' ?>">
			<svg viewBox="0 0 1920 1080" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" preserveAspectRatio="xMidYMid slice">
				<g fill="none" fill-rule="evenodd">
					<path d="M0,856.513433 L1920,201.5"></path>
					<path d="M519.5,679.48665 L56.4884224,0"></path>
					<path d="M519.4875,679.48665 L792,1079.51335"></path>
					<path d="M1190.51242,449.514039 L759.487585,0.485961123"></path>
					<path d="M1631.5125,300.486284 L1281.4875,1079.51372"></path>
					<path d="M1565.48764,447.486807 L1920,815.513193"></path>
				</g>
			</svg>
		</div>
		<?php
	}

	public function render_borders(){
		?>
		<div class="cNest-borders">
			<span class="__top"></span>
			<span></span>
			<span></span>
			<span></span>
		</div>
		<?php
	}

	public function render_contact(){
		if( $text = $this->_settings['bottom_text'] ): ?>
			<div class="cNest-contact elementor-text-editor">
				<?php echo $text ?>
			</div>
		<?php
		endif;
	}

	public function render_nav()
	{
		if( !empty($this->_items) ){

			$bullets = '';

			for( $i = 0; $i < count($this->_items); $i++ ){
				$bullets .= sprintf( '<button data-go="%1$d" aria-label="%2$s %1$d"></button>', $i, esc_html__('Go to slide ', 'rey-core') );
			}

			printf('<div class="cNest-nav __pagination-%s">%s</div>', $this->get_id(), $bullets );
		}
	}

	public function render_social(){

		if( $social_icon_list = $this->_settings['social_icon_list'] ): ?>

			<div class="cNest-social">

				<?php if($social_text = $this->_settings['social_text']): ?>
					<div class="cNest-socialText"><?php echo $social_text ?></div>
				<?php endif; ?>

				<div class="cNest-socialIcons">
					<?php
					foreach ( $social_icon_list as $index => $item ):

						$link_key = 'link_' . $index;

						$this->add_render_attribute( $link_key, 'href', $item['link']['url'] );

						if ( $item['link']['is_external'] ) {
							$this->add_render_attribute( $link_key, 'target', '_blank' );
						}

						if ( $item['link']['nofollow'] ) {
							$this->add_render_attribute( $link_key, 'rel', 'nofollow' );
						}
						?>
						<a class="cNest-socialIcons-link" rel="noreferrer" <?php echo $this->get_render_attribute_string( $link_key ); ?>>
							<?php echo reycore__get_svg_social_icon([ 'id'=>$item['social'] ]); ?>
						</a>
					<?php endforeach; ?>
				</div>

			</div>
			<!-- .cNest-social -->
		<?php endif;
	}

	public function render_footer(){
		?>
		<div class="cNest-footer">
			<div class="cNest-footerInner">
			<?php

				$this->render_contact();

				if( $this->_settings['bars_nav'] !== '' && $this->_items > 1 ){
					$this->render_nav();
				}

				$this->render_social();
			?>
			</div>
		</div>
		<?php
	}

	public function run_early_script(){

		$scripts = '';

		$scripts .= 'document.body.classList.add("el-reycore-cover-nest");';

		if( '' === $this->_settings['entrance_animation'] ){
			$scripts .= 'document.body.classList.add("--cNest-active");';
		}

		$scripts .= 'var splitHeaderHelper = document.getElementById("rey-siteHeader-helper"); splitHeaderHelper ? splitHeaderHelper.classList.add("--dnone-lg"):"";';

		if( $scripts ){
			printf('<script type="text/javascript" data-rey-instant-js %s>%s</script>', reycore__js_no_opt_attr(), $scripts);
		}
	}

	protected function render() {

		$this->_settings = $this->get_settings_for_display();
		$this->_items = $this->_settings['items'];

		$this->run_early_script();
		$this->render_start();
		$this->render_slides();
		$this->render_captions();
		$this->render_decorations();
		$this->render_footer();
		$this->render_end();

		reycore_assets()->add_styles([$this->get_style_name(), 'rey-splide']);
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
