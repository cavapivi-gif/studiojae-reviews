<?php
namespace ReyCore\Elementor\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
exit; // Exit if accessed directly.
}

class CoverPanels extends \ReyCore\Elementor\WidgetsBase {

	private $_items = [];

	public static function get_rey_config(){
		return [
			'id' => 'cover-panels',
			'title' => __( 'Cover - Hover Panels', 'rey-core' ),
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

		// if( ! empty($data) ){
		// 	\ReyCore\Plugin::instance()->elementor->frontend->add_delay_js_scripts('cover-panels', ['rey-script']);
		// }

		parent::__construct( $data, $args );
	}

	public function rey_get_script_depends() {
		return [ 'reycore-widget-cover-panels-scripts' ];
	}

	public function get_custom_help_url() {
		return reycore__support_url('kb/rey-elements-covers/#hover-panels');
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

		$items->start_controls_tabs( 'items_repeater' );

		$items->start_controls_tab( 'content', [ 'label' => __( 'Content', 'rey-core' ) ] );

		$items->add_control(
			'title',
			[
				'label'       => __( 'Title', 'rey-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
			]
		);

		$items->add_control(
			'desc',
			[
				'label'       => __( 'Description', 'rey-core' ),
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
			]
		);

		$items->add_control(
			'menu_id',
			[
				'label' => __( 'Select Menu (on hover)', 'rey-core' ),
				'label_block' => true,
				'type' => 'rey-ajax-list',
				'query_args' => [
					'request'   => 'get_nav_menus_options',
					'edit_link' => true,
				],
				'default' => '',
				'conditions' => [
					'terms' => [
						[
							'name' => 'link_panel',
							'operator' => '==',
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
				'placeholder' => __( 'Click here', 'rey-core' ),
			]
		);

		$items->add_control(
			'button_url',
			[
				'label' => __( 'Button Link', 'rey-core' ),
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
			'link_panel',
			[
				'label' => esc_html__( 'Link entire block', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$items->end_controls_tab();

		$items->start_controls_tab( 'active', [ 'label' => __( 'ACTIVE', 'rey-core' ) ] );

		$items->add_control(
			'active_title',
			[
				'label' => __( 'DEFAULT STATE', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before'
			]
		);

		$items->add_control(
			'active_text_color',
			[
				'label' => __( 'Text color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} .cPanels-active' => 'color: {{VALUE}}',
				],
			]
		);

		$items->add_control(
			'active_image',
			[
			'label' => __( 'Background Image', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::MEDIA,
				'default' => [],
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} .cPanels-activeBg' => 'background-image: url("{{URL}}")',
				],
			]
		);

		$items->add_control(
			'active_background_size',
			[
				'label' => _x( 'Size', 'Background Control', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'cover',
				'options' => [
					'cover' => _x( 'Cover', 'Background Control', 'rey-core' ),
					'contain' => _x( 'Contain', 'Background Control', 'rey-core' ),
					'auto' => _x( 'Auto', 'Background Control', 'rey-core' ),
				],
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} .cPanels-activeBg' => 'background-size: {{VALUE}}',
				],
				'conditions' => [
					'terms' => [
						[
							'name' => 'active_image[url]',
							'operator' => '!=',
							'value' => '',
						],
					],
				],
			]
		);

		$items->add_control(
			'active_background_pos',
			[
				'label' => _x( 'Position', 'Background Control', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					'' => _x( 'Default', 'Background Control', 'rey-core' ),
					'top left' => _x( 'Top Left', 'Background Control', 'rey-core' ),
					'top center' => _x( 'Top Center', 'Background Control', 'rey-core' ),
					'top right' => _x( 'Top Right', 'Background Control', 'rey-core' ),
					'center left' => _x( 'Center Left', 'Background Control', 'rey-core' ),
					'center center' => _x( 'Center Center', 'Background Control', 'rey-core' ),
					'center right' => _x( 'Center Right', 'Background Control', 'rey-core' ),
					'bottom left' => _x( 'Bottom Left', 'Background Control', 'rey-core' ),
					'bottom center' => _x( 'Bottom Center', 'Background Control', 'rey-core' ),
					'bottom right' => _x( 'Bottom Right', 'Background Control', 'rey-core' ),
					'custom' => _x( 'Custom', 'Background Control', 'rey-core' ),

				],
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} .cPanels-activeBg' => 'background-position: {{VALUE}}',
				],
				'conditions' => [
					'terms' => [
						[
							'name' => 'active_image[url]',
							'operator' => '!=',
							'value' => '',
						],
					],
				],
			]
		);

		$items->add_responsive_control(
			'active_background_pos_x',
				[
				'label' => _x( 'X Position', 'Background Control', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'default' => [
					'unit' => '%',
					'size' => 0,
				],
				'range' => [
					'px' => [
						'min' => -800,
						'max' => 800,
					],
					'%' => [
						'min' => -100,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} .cPanels-activeBg' => 'background-position-x: {{SIZE}}{{UNIT}}',
				],
				'conditions' => [
					'terms' => [
						[
							'name' => 'active_image[url]',
							'operator' => '!=',
							'value' => '',
						],
						[
							'name' => 'active_background_pos',
							'operator' => '==',
							'value' => 'custom',
						],
					],
				],
				'required' => true,
			]
		);

		$items->add_responsive_control(
			'active_background_pos_y',
				[
				'label' => _x( 'Y Position', 'Background Control', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'default' => [
					'unit' => '%',
					'size' => 0,
				],
				'range' => [
					'px' => [
						'min' => -800,
						'max' => 800,
					],
					'%' => [
						'min' => -100,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} .cPanels-activeBg' => 'background-position-y: {{SIZE}}{{UNIT}}',
				],
				'conditions' => [
					'terms' => [
						[
							'name' => 'active_image[url]',
							'operator' => '!=',
							'value' => '',
						],
						[
							'name' => 'active_background_pos',
							'operator' => '==',
							'value' => 'custom',
						],
					],
				],
				'required' => true,
			]
		);

		$items->add_control(
			'active_background_repeat',
			[
				'label' => _x( 'Repeat', 'Background Control', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					'' => _x( 'Default', 'Background Control', 'rey-core' ),
					'no-repeat' => _x( 'No-repeat', 'Background Control', 'rey-core' ),
					'repeat' => _x( 'Repeat', 'Background Control', 'rey-core' ),
					'repeat-x' => _x( 'Repeat-x', 'Background Control', 'rey-core' ),
					'repeat-y' => _x( 'Repeat-y', 'Background Control', 'rey-core' ),
				],
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} .cPanels-activeBg' => 'background-repeat: {{VALUE}}',
				],
				'conditions' => [
					'terms' => [
						[
							'name' => 'active_image[url]',
							'operator' => '!=',
							'value' => '',
						],
					],
				],
			]
		);

		$items->add_control(
			'active_overlay_bg',
			[
				'label' => __( 'Overlay Background Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} .cPanels-activeBg:after' => 'background-color: {{VALUE}}',
				],
			]
		);

		$items->end_controls_tab();

		$items->start_controls_tab( 'hover', [ 'label' => __( 'Hover', 'rey-core' ) ] );

		$items->add_control(
			'hover_title',
			[
				'label' => __( 'HOVER STATE', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				// 'separator' => 'before'
			]
		);

		$items->add_control(
			'image',
			[
			'label' => __( 'Background Image', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::MEDIA,
				'default' => [
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				],
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}}.cPanels-slideBg' => 'background-image: url("{{URL}}")',
				],
			]
		);

		$items->add_control(
			'hover_bg_color',
			[
				'label' => __( 'Background Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} .cPanels-hoverBg' => 'background-color: {{VALUE}}',
				],
			]
		);

		$items->add_control(
			'hover_text_color',
			[
				'label' => __( 'Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} .cPanels-hover' => 'color: {{VALUE}}',
				],
			]
		);

		$items->end_controls_tab();

		$items->end_controls_tabs();

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
						'title' => __( 'Title Text #2', 'rey-core' ),
						'button_text' => __( 'Button Text #2', 'rey-core' ),
						'button_url' => [
							'url' => '#',
						],
					],
					[
						'image' => [
							'url' => \Elementor\Utils::get_placeholder_image_src(),
						],
						'title' => __( 'Title Text #3', 'rey-core' ),
						'button_text' => __( 'Button Text #3', 'rey-core' ),
						'button_url' => [
							'url' => '#',
						],
					],
				]
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
				'name' => 'title_typo',
				'label' => esc_html__('Title Typography', 'rey-core'),
				'global' => [
					'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}} .cPanels-title',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'menu_typo',
				'label' => esc_html__('Menu Items Typography', 'rey-core'),
				'selector' => '{{WRAPPER}} .cPanels-menu .menu-item a',
			]
		);

		$this->add_control(
			'hover_bg_overlay',
			[
				'label' => __( 'Blend Mode', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'' => __( 'Normal', 'rey-core' ),
					'multiply' => 'Multiply',
					'screen' => 'Screen',
					'overlay' => 'Overlay',
					'darken' => 'Darken',
					'lighten' => 'Lighten',
					'color-dodge' => 'Color Dodge',
					'color-burn' => 'Color Burn',
					'hue' => 'Hue',
					'saturation' => 'Saturation',
					'color' => 'Color',
					'exclusion' => 'Exclusion',
					'luminosity' => 'Luminosity',
				],
				'default' => 'multiply',
				'selectors' => [
					'{{WRAPPER}} .cPanels-hoverBg.cPanels-hoverBg--1' => 'mix-blend-mode: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'bottom_distance',
			[
				'label' => esc_html__( 'Bottom Spacing', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => -200,
				'max' => 500,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .cPanels-slide' => '--bottom-distance: {{VALUE}}px;',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_tablets',
			[
				'label' => __( 'Styles for Tablets', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'tablet_height',
			[
				'label' => esc_html__( 'Height on tablets', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'vh' ],
				'range' => [
					'px' => [
						'min' => 100,
						'max' => 1000,
						'step' => 1,
					],
					'vh' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .rey-coverPanels' => '--tablet-height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_mobiles',
			[
				'label' => __( 'Styles for Mobiles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'mobile_images',
			[
				'label' => esc_html__( 'Show images', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->add_control(
			'mobile_custom_image_height',
			[
				'label' => esc_html__( 'Custom Image Height', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->add_control(
			'mobile_image_height',
			[
				'label' => esc_html__( 'Image Height', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 90,
						'max' => 600,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .cPanels-mobileImg .--custom-height' => 'height: {{SIZE}}px;',
				],
				'condition' => [
					'mobile_images' => 'yes',
					'mobile_custom_image_height' => 'yes',
				],
			]
		);

		$this->add_control(
			'mobile_vertical_spacing',
			[
				'label' => esc_html__( 'Vertical Spacing', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}}' => '--mobile-slides-spacing: {{SIZE}}px;',
				],
			]
		);

		$this->end_controls_section();
	}


	public function render_start($settings){

		$classes = [
			'rey-coverPanels',
			'--no-bg',
		];

		if( ! reycore__js_is_delayed() ){
			$classes[] = '--loading';
		}

		$this->add_render_attribute( 'wrapper', 'class', $classes );

		?>

		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>

			<div class="cPanels-loadingBg cPanels-abs"></div>
			<?php
	}

	public function render_end(){
		?>
		</div><?php
	}

	function render_backgrounds()
	{
		if( !empty($this->_items) ):
			?>
			<div class="cPanels-slideBgWrapper cPanels-abs">
				<?php
				foreach ($this->_items as $key => $item) {
					echo '<div class="cPanels-slideBg cPanels-abs elementor-repeater-item-' . $item['_id'] . '"></div>';
				}
				?>
			</div>
			<?php
		endif;
	}

	function render_title($item)
	{
		if( $title = $item['title'] ){
			echo '<h2 class="cPanels-title">'. $title .'</h2>';
		}
	}

	function render_desc($item)
	{
		if( $desc = $item['desc'] ){
			echo '<div class="cPanels-desc">'. reycore__parse_text_editor($desc) .'</div>';
		}
	}

	function render_link($item, $key, $button_text = ''){

		$url_key = 'url' . $key;

		if( !empty($button_text) ){
			reycore_assets()->add_styles('rey-buttons');
			$this->add_render_attribute( $url_key , 'class', 'btn btn-line-active' );
		}

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
		<?php
	}

	function render_btn($item, $key)
	{
		if( ($button_text = $item['button_text']) ): ?>
			<div class="cPanels-btn">
				<?php $this->render_link($item, $key, $button_text); ?>
			</div><?php
		endif;
	}

	function render_slides( $settings )
	{
		if( !empty($this->_items) ):

		foreach ($this->_items as $key => $item) {

			$classes = [];

			$classes[] = $item['link_panel'] === '' ? '' : '--link';
			$classes[] = 'elementor-repeater-item-' . $item['_id'];
			$classes[] = 'cPanels-slide--' . ($key + 1);
			?>

			<div class="cPanels-slide <?php echo implode(' ', $classes) ?>" data-index="<?php echo $key ?>">

				<?php if( isset($settings['mobile_images']) && $settings['mobile_images'] === 'yes' ): ?>
					<div class="cPanels-mobileImg">
						<?php echo reycore__get_attachment_image( [
							'image'      => $item['image'],
							'size'       => 'medium',
							'attributes' => [
								'class'=> $settings['mobile_custom_image_height'] === 'yes' ? '--custom-height' : ''
							]
						] ); ?>
					</div>
				<?php endif; ?>

				<div class="cPanels-hoverBg cPanels-hoverBg--1"></div>
				<div class="cPanels-hoverBg cPanels-hoverBg--2"></div>

				<div class="cPanels-active cPanels-abs">
					<div class="cPanels-activeBg cPanels-abs"></div>
					<?php
						$this->render_title($item);
						$this->render_btn($item, $key);
					?>
				</div>

				<div class="cPanels-hover cPanels-abs">

					<?php
						if( $item['link_panel'] !== '' ){
							$this->render_link($item, $key . '_full');
						}
						else {

							if( isset($item['menu_id']) && $item['menu_id'] ):
								wp_nav_menu([
									'menu'        => $item['menu_id'],
									'container'   => '',
									'menu_class'   => 'cPanels-menu',
									'items_wrap'  => '<ul id="%1$s" class="%2$s">%3$s</ul>',
									'link_before' => '<span>',
									'link_after'  => '</span>',
									'depth' => 0,
									'cache_menu'  => true,
								]);
							endif;
						}

						$this->render_title($item);
						$this->render_desc($item);
						$this->render_btn($item, $key . '_hover');
					?>

				</div>
			</div>
			<!-- .cPanels-slide -->
			<?php
		}
		endif;
	}

	protected function render() {

		$settings = $this->get_settings_for_display();

		// assign items
		$this->_items = $settings['items'];

		$this->render_start($settings);
		$this->render_backgrounds();
		$this->render_slides( $settings );
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
