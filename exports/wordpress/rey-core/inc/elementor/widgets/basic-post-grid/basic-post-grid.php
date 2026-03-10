<?php
namespace ReyCore\Elementor\Widgets;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class BasicPostGrid extends \ReyCore\Elementor\WidgetsBase {

	public $_query = null;
	public $_settings = [];
	private $posts_archive = null;
	public $slider_components;

	public static function get_rey_config(){
		return [
			'id' => 'basic-post-grid',
			'title' => __( 'Posts', 'rey-core' ),
			'icon' => 'eicon-posts-grid',
			'categories' => [ 'rey-theme' ],
			'keywords' => [ 'post', 'grid', 'blog', 'recent', 'news' ],
			'css' => [
				'assets/style[rtl].css',
			],
			'js' => [
				'assets/script.js',
			],
		];
	}

	protected function register_skins() {

		foreach ([
			'SkinBasic2',
			'SkinInner',
		] as $skin) {
			$skin_class = __CLASS__ . '\\' . $skin;
			$this->add_skin( new $skin_class( $this ) );
		}

	}

	public function get_custom_help_url() {
		return reycore__support_url('kb/rey-elements/#post-grid');
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

		$this->register_controls__section_layout();
		$this->register_controls__section_query();
		$this->register_controls__section_carousel_settings();
		$this->register_controls__section_innerskin_styles();
		$this->register_controls__section_thumb_styles();
		$this->register_controls__section_title_styles();
		$this->register_controls__section_meta_styles();
		$this->register_controls__section_content_styles();
		$this->register_controls__section_footer_styles();
		$this->register_controls__section_posts_misc();

		\ReyCore\Elementor\Helper::widgets_box_styles_controls([
			'element'       => $this,
			'selectors'     => [
				'active' => '{{WRAPPER}} .--box-styler',
				'hover'  => '{{WRAPPER}} .--box-styler:hover',
			],
			'section_title' =>  __( 'Post Box Styles', 'rey-core' ),
		]);

		\ReyCore\Libs\Slider_Components::controls( $this, [
			'arrows' => 'carousel_arrows',
			'dots' => 'carousel_pagination',
		] );

	}

	public function register_controls__section_layout() {


		$this->start_controls_section(
			'section_layout',
			[
				'label' => __( 'Layout', 'rey-core' ),
			]
		);

		$this->add_responsive_control(
			'per_row',
			[
				'label' => __( 'Posts per row', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'prefix_class' => 'reyEl-bPostGrid%s--',
				'min' => 1,
				'max' => 6,
				'devices' => [ 'desktop', 'tablet', 'mobile' ],
				'desktop_default' => 2,
				'tablet_default' => 2,
				'mobile_default' => 1,
				'condition' => [
					'carousel!' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}}' => '--per-row: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'posts_per_page',
			[
				'label' => __( 'Limit', 'rey-core' ),
				'description' => __( 'Select the number of items to load from query.', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 2,
				'min' => 1,
				'max' => 100,
			]
		);

		$this->add_responsive_control(
			'gap',
			[
				'label' => __( 'Gap', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 30,
				'min' => 0,
				'max' => 200,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .reyEl-bPostGrid' => '--bpostgrid-spacing: {{VALUE}}px;',
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
			'lazy_load',
			[
				'label' => esc_html__( 'Lazy Load', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'separator' => 'before',
				'condition' => [
					// '_skin' => [],
					'add_pagination' => '',
				],
			]
		);

		$this->add_responsive_control(
			'lazy_load_placeholders_height',
			[
				'label' => esc_html__( 'Placeholder height', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 1,
				'max' => 1000,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .__placeholder-wrapper' => '--lazy-placeholder-height: {{VALUE}}px',
				],
				'condition' => [
					// '_skin' => [],
					'add_pagination' => '',
					'lazy_load!' => '',
				],
			]
		);

		$this->add_control(
			'lazy_load_placeholders_bg',
			[
				'label' => esc_html__( 'Placeholder color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .__placeholder-wrapper' => '--lazy-placeholder-bg: {{VALUE}}',
				],
				'condition' => [
					// '_skin' => [],
					'add_pagination' => '',
					'lazy_load!' => '',
				],
			]
		);

		$this->add_control(
			'lazy_load_trigger',
			[
				'label' => esc_html__( 'Trigger', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'scroll',
				'options' => [
					'scroll'  => esc_html__( 'On Scroll', 'rey-core' ),
					'click'  => esc_html__( 'On Click', 'rey-core' ),
					'mega-menu'  => esc_html__( 'On Mega Menu display', 'rey-core' ),
				],
				'condition' => [
					// '_skin' => [],
					'add_pagination' => '',
					'lazy_load!' => '',
				],
			]
		);

		$this->add_control(
			'lazy_load_click_trigger',
			[
				'label' => esc_html__( 'Click Selector', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => esc_html__( 'eg: .custom-unique-selector', 'rey-core' ),
				'condition' => [
					// '_skin' => [],
					'add_pagination' => '',
					'lazy_load!' => '',
					'lazy_load_trigger' => 'click',
				],
				'wpml' => false,
			]
		);

		$this->add_control(
			'lazy_load_cache',
			[
				'label' => esc_html__( 'Cache Ajax Response?', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
				'condition' => [
					'add_pagination' => '',
					'lazy_load!' => '',
				],
			]
		);

		$this->end_controls_section();

	}

	public function register_controls__section_query() {

		/**
		 * Query Settings
		 */

		$this->start_controls_section(
			'section_query',
			[
				'label' => __( 'Posts Query', 'rey-core' ),
			]
		);

		$this->add_control(
			'post_type',
			[
				'label' => esc_html__( 'Post Type', 'rey-core' ),
				'default' => 'post',
				'type' => 'rey-ajax-list',
				'query_args' => [
					'request' => 'post_types_list_except_product',
				],
			]
		);

		$this->add_control(
			'query_type',
			[
				'label' => esc_html__('Query Type', 'rey-core'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'recent',
				'options' => [
					'recent'           => esc_html__('Recent', 'rey-core'),
					'manual-selection' => esc_html__('Manual Selection', 'rey-core'),
					'current-query' => esc_html__('Current Query', 'rey-core'),
				],
				// 'condition' => [
				// 	'post_type!' => 'page'
				// ],
			]
		);

		$this->add_control(
			'categories',
			[
				'label' => esc_html__('Categories', 'rey-core'),
				'placeholder' => esc_html__('- Select category -', 'rey-core'),
				'type' => 'rey-query',
				'query_args' => [
					'type' => 'terms',
					'taxonomy' => 'category',
				],
				'label_block' => true,
				'multiple' => true,
				'default'     => [],
				'condition' => [
					'query_type' => ['recent'],
					'post_type' => 'post'
				],
			]
		);

		$this->add_control(
			'all_taxonomies',
			[
				'label' => esc_html__('Taxonomy Term', 'rey-core'),
				'placeholder' => esc_html__('- Select term -', 'rey-core'),
				'type' => 'rey-query',
				'query_args' => [
					'type' => 'terms',
					'taxonomy' => 'all_taxonomies',
				],
				'label_block' => true,
				'multiple' => true,
				'default'     => [],
				'condition' => [
					'query_type' => 'recent',
					'post_type!' => ['', 'page']
				],
			]
		);

		$this->add_control(
			'tags',
			[
				'label' => esc_html__('Tags', 'rey-core'),
				'placeholder' => esc_html__('- Select tags -', 'rey-core'),
				'type' => 'rey-query',
				'query_args' => [
					'type' => 'terms',
					'taxonomy' => 'post_tag',
				],
				'label_block' => true,
				'multiple' => true,
				'default'     => [],
				'condition' => [
					'query_type' => ['recent'],
					'post_type' => 'post'
				],
			]
		);

		// Advanced settings
		$this->add_control(
			'include',
			[
				'label'       => esc_html__( 'Posts', 'rey-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => 'eg: 21, 22',
				'label_block' => true,
				'description' => __( 'Add posts IDs separated by comma.', 'rey-core' ),
				'condition' => [
					'query_type' => 'manual-selection',
				],
			]
		);

		$this->add_control(
			'exclude',
			[
				'label'       => esc_html__( 'Exclude Posts', 'rey-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => 'eg: 21, 22',
				'label_block' => true,
				'description' => __( 'Add posts IDs separated by comma.', 'rey-core' ),
				'condition' => [
					'query_type!' => 'manual-selection',
				],
			]
		);

		$this->add_control(
			'orderby',
			[
				'label' => __( 'Order By', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'post_date',
				'options' => [
					'post_date' => __( 'Date', 'rey-core' ),
					'post_title' => __( 'Title', 'rey-core' ),
					'menu_order' => __( 'Menu Order', 'rey-core' ),
					'rand' => __( 'Random', 'rey-core' ),
				],
				'condition' => [
					'query_type!' => 'manual-selection',
				],
			]
		);

		$this->add_control(
			'order',
				[
				'label' => __( 'Order', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'desc',
				'options' => [
					'asc' => __( 'ASC', 'rey-core' ),
					'desc' => __( 'DESC', 'rey-core' ),
				],
				'condition' => [
					'query_type!' => 'manual-selection',
				],
			]
		);

		$this->add_control(
			'exclude_duplicates',
			[
				'label' => __( 'Exclude Duplicates', 'rey-core' ),
				'description' => __( 'Exclude duplicates that were already loaded in this page', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'add_pagination',
			[
				'label' => __( 'Enable Pagination', 'rey-core' ),
				'description' => __( 'Use element as a posts archive.', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->add_control(
			'query_id',
			[
				'label' => esc_html__( 'Custom Query ID', 'rey-core' ),
				'description' => __( 'Give your Query a custom unique id to allow server side custom hooks. Code <a href="https://d.pr/n/n82S5F" target="_blank">example here</a>.', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => esc_html__( 'eg: unique_id', 'rey-core' ),
				'separator' => 'before',
				'ai' => [
					'active' => false,
				],
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$this->end_controls_section();

	}

	public function register_controls__section_carousel_settings() {

		/* ------------------------------------ CAROUSEL ------------------------------------ */

		$this->start_controls_section(
			'section_carousel_settings',
			[
				'label' => __( 'Carousel Settings', 'rey-core' ),
			]
		);

		$this->add_control(
			'carousel',
			[
				'label' => esc_html__( 'Activate Carousel', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		);


		$slides_to_show = range( 1, 10 );
		$slides_to_show = array_combine( $slides_to_show, $slides_to_show );

		$this->add_responsive_control(
			'slides_to_show',
			[
				'label' => __( 'Slides to Show', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'' => __( 'Default', 'rey-core' ),
				] + $slides_to_show,
				'condition' => [
					'carousel' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}}' => '--slides-to-show: {{VALUE}}; --per-row: {{VALUE}}',
				],
				'devices' => [ 'desktop', 'tablet', 'mobile' ],
				'desktop_default' => 4,
				'tablet_default' => 3,
				'mobile_default' => 2,
			]
		);

		$this->add_control(
			'pause_on_hover',
			[
				'label' => __( 'Pause on Hover', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
				'condition' => [
					'carousel' => 'yes',
				],
			]
		);

		$this->add_control(
			'autoplay',
			[
				'label' => __( 'Autoplay', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
				'condition' => [
					'carousel' => 'yes',
				],
			]
		);

		$this->add_control(
			'autoplay_speed',
			[
				'label' => __( 'Autoplay Speed', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 5000,
				'condition' => [
					'carousel' => 'yes',
				],
			]
		);

		$this->add_control(
			'infinite',
			[
				'label' => __( 'Infinite Loop', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'yes',
				'options' => [
					'yes' => __( 'Yes', 'rey-core' ),
					'no' => __( 'No', 'rey-core' ),
				],
				'condition' => [
					'carousel' => 'yes',
				],
			]
		);

		$this->add_control(
			'effect',
			[
				'label' => __( 'Effect', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'slide',
				'options' => [
					'slide' => __( 'Slide', 'rey-core' ),
					'fade' => __( 'Fade', 'rey-core' ),
				],
				'condition' => [
					'carousel' => 'yes',
				],
			]
		);

		$this->add_control(
			'speed',
			[
				'label' => __( 'Animation Speed', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 500,
				'condition' => [
					'carousel' => 'yes',
				],
			]
		);

		$this->add_control(
			'direction',
			[
				'label' => __( 'Direction', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'ltr',
				'options' => [
					'ltr' => __( 'Left', 'rey-core' ),
					'rtl' => __( 'Right', 'rey-core' ),
				],
				'condition' => [
					'carousel' => 'yes',
				],
			]
		);

		\ReyCore\Libs\Slider_Components::controls_nav( $this, [
			'arrows' => 'carousel_arrows',
			'dots' => 'carousel_pagination',
			'extra' => [
				'condition' => [
					'carousel!' => '',
				],
				'default' => ''
			]
		] );

		$this->add_control(
			'offset_title',
			[
			   'label' => esc_html__( 'Offset', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'carousel_padding',
			[
				'label' => __( 'Horizontal Padding (Offset)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'vw' ],
				'condition' => [
					'carousel!' => '',
					'carousel_viewport_offset_side' => ''
				],
				'allowed_dimensions' => 'horizontal',
				'selectors' => [
					'{{WRAPPER}} .splide__track' => 'padding-left: {{LEFT}}{{UNIT}}; padding-right: {{RIGHT}}{{UNIT}}; ',
				],
			]
		);

		$this->add_control(
			'carousel_viewport_offset_side',
			[
				'label' => esc_html__( 'Side Offset', 'rey-core' ),
				'description' => esc_html__( 'This option will pull the carousel horizontal sides toward the window edges. Applies only on desktop and overrides other settings.', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => esc_html__( '- None -', 'rey-core' ),
					'both'  => esc_html__( 'Both', 'rey-core' ),
					'left'  => esc_html__( 'Left', 'rey-core' ),
					'right'  => esc_html__( 'Right', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'carousel_viewport_offset_side_opa',
			[
				'label' => esc_html__( 'Inactives opacity', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 1,
				'step' => 0.05,
				'condition' => [
					'carousel_viewport_offset_side!' => '',
				],
				'selectors' => [
					'{{WRAPPER}}' => '--side-offset-inactive-opacity: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'carousel_id',
			[
				'label' => __( 'Carousel Unique ID', 'rey-core' ),
				'separator' => 'before',
				'label_block' => true,
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => uniqid('carousel-'),
				'placeholder' => __( 'eg: some-unique-id', 'rey-core' ),
				'description' => sprintf(__( 'Copy the ID above and paste it into the "Toggle Boxes" Widget or "Slider Navigation" widget where specified. No hashtag needed. Read more on <a href="%s" target="_blank">how to connect them</a>.', 'rey-core' ), reycore__support_url('kb/products-grid-element/#adding-custom-navigation') ),
				'condition' => [
					'carousel' => 'yes',
				],
				'style_transfer' => false,
				'wpml' => false,
			]
		);


		$this->add_control(
			'disable_desktop',
			[
				'label' => esc_html__( 'Disable on desktop', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'return_value' => 'desktop',
				'prefix_class' => '--disable-',
				'separator' => 'before',
				'condition' => [
					'carousel' => 'yes',
				],
			]
		);

		$this->add_control(
			'disable_tablet',
			[
				'label' => esc_html__( 'Disable on tablet', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'return_value' => 'tablet',
				'prefix_class' => '--disable-',
				'condition' => [
					'carousel' => 'yes',
				],
			]
		);

		$this->add_control(
			'disable_mobile',
			[
				'label' => esc_html__( 'Disable on mobile', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'return_value' => 'mobile',
				'prefix_class' => '--disable-',
				'condition' => [
					'carousel' => 'yes',
				],
			]
		);

		$this->end_controls_section();


	}

	public function register_controls__section_innerskin_styles() {

		/* ------------------------------------ Inner Skin Options ------------------------------------ */


		$this->start_controls_section(
			'section_innerskin_styles',
			[
				'label' => __( 'Inner Skin Settings', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'_skin' => 'inner',
				],
			]
		);

		$this->add_control(
			'inner_align',
			[
				'label' => esc_html__( 'Vertical Alignment', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'flex-end',
				'options' => [
					'flex-start'  => esc_html__( 'Top', 'rey-core' ),
					'center'  => esc_html__( 'Middle', 'rey-core' ),
					'flex-end'  => esc_html__( 'Bottom', 'rey-core' ),
				],
				'selectors' => [
					'{{WRAPPER}} .reyEl-bPostGrid-inner' => 'justify-content: {{VALUE}}',
				],
				'condition' => [
					'_skin' => 'inner',
				],
			]
		);

		$this->add_responsive_control(
			'inner_spacing',
			[
			   'label' => esc_html__( 'Content Spacing', 'rey-core' ),
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
						'max' => 10.0,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 40,
				],
				'selectors' => [
					'{{WRAPPER}} .reyEl-bPostGrid--inner' => '--posts-spacing: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'_skin' => 'inner',
				],
			]
		);

		$this->add_control(
			'inner_image_stretch',
			[
				'label' => esc_html__( 'Image Stretch', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'inner_hover_effect',
			[
				'label' => esc_html__( 'Hover Effect', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'none',
				'options' => [
					'none'  => esc_html__( 'Default', 'rey-core' ),
					'scale'  => esc_html__( 'Scale', 'rey-core' ),
					'clip'  => esc_html__( 'Clip & Scale', 'rey-core' ),
					'shift'  => esc_html__( 'Subtle Shift', 'rey-core' ),
				],
			]
		);

		$this->start_controls_tabs( 'tabs_inner_styles' );

			$this->start_controls_tab(
				'tab_inner_normal',
				[
					'label' => __( 'Normal', 'rey-core' ),
				]
			);

				$this->add_control(
					'inner_text_color',
					[
						'label' => __( 'Text Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'default' => '',
						'selectors' => [
							'{{WRAPPER}} .reyEl-bPostGrid--inner' => '--posts-text-color: {{VALUE}};',
						],
					]
				);

				$this->add_control(
					'inner_link_color',
					[
						'label' => __( 'Link Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'default' => '',
						'selectors' => [
							'{{WRAPPER}} .reyEl-bPostGrid--inner' => '--posts-link-color: {{VALUE}};',
						],
					]
				);

				$this->add_control(
					'inner_bg_color',
					[
						'label' => __( 'Overlay Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'default' => '',
						'selectors' => [
							'{{WRAPPER}} .reyEl-bPostGrid--inner' => '--posts-inner-bg-color: {{VALUE}};',
						],
					]
				);

			$this->end_controls_tab();

			$this->start_controls_tab(
				'tab_inner_hover',
				[
					'label' => __( 'Hover', 'rey-core' ),
				]
			);

				$this->add_control(
					'inner_link_color_hover',
					[
						'label' => __( 'Link Hover Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'default' => '',
						'selectors' => [
							'{{WRAPPER}} .reyEl-bPostGrid--inner' => '--posts-link-hover-color: {{VALUE}};',
						],
					]
				);

				$this->add_control(
					'inner_bg_color_hover',
					[
						'label' => __( 'Overlay Hover Color', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'default' => '',
						'selectors' => [
							'{{WRAPPER}} .reyEl-bPostGrid--inner' => '--posts-inner-bg-hover-color: {{VALUE}};',
						],
						'condition' => [
							'inner_bg_overlay_gradient' => '',
						],
					]
				);

			$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'inner_bg_overlay_gradient',
			[
				'label' => esc_html__( 'Gradient Overlay?', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$this->end_controls_section();

	}

	public function register_controls__section_thumb_styles() {

		/* ------------------------------------ Start section - Thumbnail ------------------------------------ */

		$this->start_controls_section(
			'section_thumb_styles',
			[
				'label' => __( 'Thumbnail Settings', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'thumb',
			[
				'label' => __( 'Display thumbnail', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'thumb_size',
			[
				'label' => __( 'Thumbnail layout', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'natural',
				'options' => [
					'natural'  => __( 'Natural', 'rey-core' ),
					'custom'  => __( 'Custom Height', 'rey-core' ),
				],
				'prefix_class' => 'reyEl-bpost-thumb--',
				'condition' => [
					'thumb' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'custom_height',
			[
				'label' => __( 'Height', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'default' => [
					'size' => 300,
				],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 800,
					],
				],
				'size_units' => [ 'px' ],
				'selectors' => [
					'{{WRAPPER}}.reyEl-bpost-thumb--custom .reyEl-bpost-thumb' => 'height: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'thumb' => 'yes',
					'thumb_size' => 'custom',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Image_Size::get_type(),
			[
				'name' => 'image', // Usage: `{name}_size` and `{name}_custom_dimension`, in this case `image_size` and `image_custom_dimension`.
				'default' => 'medium_large',
				'separator' => 'before',
				'exclude' => ['custom'],
				// 'condition' => [
				// 	'_skin!' => 'carousel-section',
				// ],
			]
		);

		$this->end_controls_section();

	}

	public function register_controls__section_title_styles() {

		$this->start_controls_section(
			'section_title_styles',
			[
				'label' => __( 'Title Settings', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_control(
				'title',
				[
					'label' => __( 'Display title', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'yes',
					'default' => 'yes',
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'title_typo',
					'global' => [
						'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Typography::TYPOGRAPHY_PRIMARY,
					],
					'selector' => '{{WRAPPER}} .reyEl-bpost-title',
					'condition' => [
						'title' => ['yes'],
					],
				]
			);

			$this->add_control(
				'title_underline_effect',
				[
					'label' => esc_html__( 'Title underline effect', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'prefix_class' => '--title-hover-',
				]
			);

			$this->add_control(
				'title_color',
				[
					'label' => esc_html__( 'Title Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .reyEl-bpost-title a' => 'color: {{VALUE}}',
					],
					'condition' => [
						'_skin!' => 'inner',
					],
				]
			);

			$this->add_control(
				'show_categories',
				[
					'label' => esc_html__( 'Show Categories', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'separator' => 'before',
					'default' => '',
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'cats_typo',
					'global' => [
						'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Typography::TYPOGRAPHY_PRIMARY,
					],
					'selector' => '{{WRAPPER}} .reyEl-bpost-cats',
					'condition' => [
						'show_categories!' => '',
					],
				]
			);

			$this->add_control(
				'cats_color',
				[
					'label' => esc_html__( 'Categories Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .reyEl-bpost-cats a' => 'color: {{VALUE}}',
					],
					'condition' => [
						'show_categories!' => '',
					],
				]
			);

		$this->end_controls_section();



	}

	public function register_controls__section_meta_styles() {

		$this->start_controls_section(
			'section_meta_styles',
			[
				'label' => __( 'Meta Settings', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'meta_author',
			[
				'label' => __( 'Display Author', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'meta_date',
			[
				'label' => __( 'Display Date', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'meta_comments',
			[
				'label' => __( 'Display Comments', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'meta_color',
			[
				'label' => esc_html__( 'Meta Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-postInfo, {{WRAPPER}} .rey-postInfo a' => 'color: {{VALUE}}',
				],
				'condition' => [
					'_skin!' => 'inner',
				],
			]
		);

		$this->add_control(
			'date_color',
			[
				'label' => esc_html__( 'Date Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-entryDate' => 'color: {{VALUE}}',
				],
				'condition' => [
					'_skin!' => 'inner',
				],
			]
		);

		$this->end_controls_section();


	}

	public function register_controls__section_content_styles() {

		$this->start_controls_section(
			'section_content_styles',
			[
				'label' => __( 'Excerpt Settings', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'excerpt',
			[
				'label' => __( 'Display excerpt', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'excerpt_length',
			[
				'label' => __( 'Excerpt Length (Words count)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 20,
				'min' => 0,
				'max' => 200,
				'step' => 0,
			]
		);

		$this->add_control(
			'excerpt_color',
			[
				'label' => esc_html__( 'Excerpt Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .reyEl-bpost-content, {{WRAPPER}} .reyEl-bpost-content a' => 'color: {{VALUE}}',
				],
				'condition' => [
					'_skin!' => 'inner',
				],
			]
		);

		// settings pt image size

		$this->end_controls_section();

	}

	public function register_controls__section_footer_styles() {

		$this->start_controls_section(
			'section_footer_styles',
			[
				'label' => __( 'Post footer settings', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'read_more',
			[
				'label' => __( 'Display Read more button', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'read_more_text',
			[
				'label' => esc_html__( 'Button text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => esc_html__( 'eg: Continue Reading', 'rey-core' ),
				'condition' => [
					'read_more!' => '',
				],
			]
		);

		$this->add_control(
			'read_duration',
			[
				'label' => __( 'Display Read Duration', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default' => 'yes',
				'condition' => [
					'read_more' => ['yes'],
				],
			]
		);

		$this->add_control(
			'footer_color',
			[
				'label' => esc_html__( 'Footer Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .reyEl-bpost-footer, {{WRAPPER}} .reyEl-bpost-footer a' => 'color: {{VALUE}}',
				],
				'condition' => [
					'_skin!' => 'inner',
				],
			]
		);

		$this->end_controls_section();

	}

	public function register_controls__section_posts_misc() {

		$this->start_controls_section(
			'section_posts_misc',
			[
				'label' => __( 'Misc. settings', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'masonry',
			[
				'label' => esc_html__( 'Use Masonry layout?', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
				'condition' => [
					'carousel' => '',
				],
			]
		);

		$this->add_responsive_control(
			'vertical_spacing',
			[
			   'label' => esc_html__( 'Vertical Spacing', 'rey-core' ),
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
					'size' => 30,
				],
				'selectors' => [
					'{{WRAPPER}} .reyEl-bPostGrid' => '--bpostgrid-vspacing: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'entry_animation',
			[
				'label' => __( 'Animate on scroll', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->end_controls_section();

	}

	public function get_query() {
		return $this->_query;
	}

	public function pre_get_posts_query_filter( $query ){
		$query_id = $this->_settings[ 'query_id' ];
		do_action( "reycore/elementor/query/{$query_id}", $query, $this );
	}

	public function query_posts() {

		$query_args = [
			'posts_per_page' => $this->_settings['posts_per_page'] ? $this->_settings['posts_per_page'] : get_option('posts_per_page'),
			'post_type' => $this->_settings['post_type'],
			'post_status' => 'publish',
			'ignore_sticky_posts' => true,
			// 'no_found_rows' => true,
			// 'update_post_meta_cache' => false, //useful when post meta will not be utilized
			'update_post_term_cache' => false, //useful when taxonomy terms will not be utilized
			'orderby' => isset($this->_settings['orderby']) ? $this->_settings['orderby'] : 'date',
			'order' => isset($this->_settings['order']) ? $this->_settings['order'] : 'DESC',
		];

		if( $this->_settings['query_type'] == 'current-query' ){
			$current_query_args = array_filter($GLOBALS['wp_query']->query_vars);
			$query_args = array_merge($current_query_args, $query_args);
		}

		else if( $this->_settings['query_type'] == 'manual-selection' && !empty($this->_settings['include']) ) {
			$query_args['post__in'] = array_map( 'trim', explode( ',', $this->_settings['include'] ) );
		}

		else {

			if( 'post' === $this->_settings['post_type'] && isset($this->_settings['categories']) && $categories = $this->_settings['categories'] ){
				unset($query_args['update_post_term_cache']);
				$query_args['category__in'] = array_map( 'absint', $categories );
			}

			if( 'post' === $this->_settings['post_type'] && isset($this->_settings['tags']) && $tags = $this->_settings['tags'] ){
				unset($query_args['update_post_term_cache']);
				$query_args['tag__in'] = array_map( 'absint', $tags );
			}

			if(
				// 'post' !== $this->_settings['post_type'] &&
				isset($this->_settings['all_taxonomies']) &&
				$all_taxonomies = $this->_settings['all_taxonomies']
			){

				unset($query_args['update_post_term_cache']);

				foreach ( $all_taxonomies as $term_id ) {

					$term = get_term( $term_id );

					if( isset($term->taxonomy) ){
						$query_args['tax_query'][] = [
							'taxonomy' => $term->taxonomy,
							'field' => 'term_id',
							'terms' => absint($term_id),
						];
					}
				}

			}

			if( isset($this->_settings['tags']) && $tags = $this->_settings['tags'] ){
				unset($query_args['update_post_term_cache']);
				$query_args['tag__in'] = array_map( 'absint', $tags );
			}

			if( !empty($this->_settings['exclude']) ) {
				$query_args['post__not_in'] = array_map( 'trim', explode( ',', $this->_settings['exclude'] ) );
			}
		}

		// Exclude duplicates
		if( $this->_settings['exclude_duplicates'] !== '' &&
			isset($GLOBALS["rey_exclude_posts"]) &&
			($to_exclude = $GLOBALS["rey_exclude_posts"]) ){
			$query_args['post__not_in'] = isset($query_args['post__not_in']) ? array_merge( $query_args['post__not_in'], $to_exclude ) : $to_exclude;
		}

		if( $this->_settings['add_pagination'] !== '' ){
			$query_args['paged'] = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
		}

		$query_args = apply_filters( 'reycore/elementor/posts/query_args', $query_args, $this );

		if ( isset($this->_settings['query_id']) && $query_id = $this->_settings['query_id'] ) {

			$query_args = apply_filters( "reycore/elementor/query/{$query_id}/query_args", $query_args, $this );

			add_action( 'pre_get_posts', [ $this, 'pre_get_posts_query_filter' ] );

		}

		// $this->_query = new \WP_Query( $query_args );
		$this->_query = \ReyCore\Helper::get_query( $query_args );

		remove_action( 'pre_get_posts', [ $this, 'pre_get_posts_query_filter' ] );

		do_action( 'reycore/elementor/query/query_results', $this->_query, $this );

	}

	/**
	 * Render thumbnail
	 *
	 * @since 1.0.0
	 **/
	public function render_thumbnail($class = '', $force_wrapper = false)
	{

		if ( 'yes' !== $this->_settings['thumb'] ){
			return;
		}

		$can_show_post_thumb = has_post_thumbnail();

		if( $can_show_post_thumb || $force_wrapper ){
			?>
			<div class="reyEl-bpost-thumb">
				<a class="reyEl-bpost-thumbLink <?php echo esc_attr($class); ?>" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
			<?php
		}

			if ( $can_show_post_thumb ){
				add_filter('post_thumbnail_size', [$this,'thumb_size'], 50);
				the_post_thumbnail();
				remove_filter('post_thumbnail_size', [$this,'thumb_size'], 50);
			}

		if( $can_show_post_thumb || $force_wrapper ){
			?>
				</a>
			</div>
		<?php
		}

	}

	public function thumb_size( $size ){
		return $this->_settings['image_size'] !== 'custom' ? $this->_settings['image_size'] : $size;
	}

	/**
	 * Render meta
	 *
	 * @since 1.0.0
	 **/
	public function render_meta()
	{

		if( 'yes' === $this->_settings['meta_author'] || 'yes' === $this->_settings['meta_date'] || 'yes' === $this->_settings['meta_comments'] ): ?>
			<div class="rey-postInfo">
			<?php
				if( 'yes' === $this->_settings['meta_author'] ){
					if( function_exists('rey__posted_by') ){
						rey__posted_by();
					}
				}
				if( 'yes' === $this->_settings['meta_date'] ){
					if( function_exists('rey__posted_date') ){
						rey__posted_date();
					}
				}
				if( 'yes' === $this->_settings['meta_comments'] ){
					if( function_exists('rey__comment_count') ){
						rey__comment_count();
					}
				}
				if( function_exists('rey__edit_link') ){
					rey__edit_link();
				}
			?>
			</div>
		<?php endif;
	}

	/**
	 * Render title
	 *
	 * @since 1.0.0
	 **/
	public function render_title()
	{
		if( 'yes' === $this->_settings['title'] ){
			the_title( sprintf( '<h3 class="reyEl-bpost-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h3>' );
		}

		if( '' !== $this->_settings['show_categories'] ){
			if ($categories_list = get_the_category_list(', ')) {
				printf(
					'<div class="reyEl-bpost-cats"><span class="screen-reader-text">%s</span>%s</div>',
					esc_html__('Posted in', 'rey-core'),
					$categories_list
				); // WPCS: XSS OK.
			}
		}


	}

	/**
	 * Render excerpt
	 *
	 * @since 1.0.0
	 **/
	public function render_excerpt()
	{
		$settings = $this->_settings;

		if( 'yes' !== $settings['excerpt'] ) {
			return;
		}

		$excerpt = get_the_excerpt();

		if( $settings['excerpt_length'] ){

			$excerpt = explode(' ', $excerpt, $settings['excerpt_length']);

			if ( count( $excerpt ) >= $settings['excerpt_length'] ) {
				array_pop($excerpt);
				$excerpt = implode(" ", $excerpt) . '&hellip;';
			}
			else {
				$excerpt = implode(" ",$excerpt);
			}

			$excerpt = preg_replace('`\[[^\]]*\]`','',$excerpt);
		} ?>

		<div class="reyEl-bpost-content">
			<?php echo apply_filters('reycore/get_the_excerpt', $excerpt); ?>
		</div>

		<?php
	}

	/**
	 * Render footer
	 *
	 * @since 1.0.0
	 **/
	public function render_footer()
	{

		if( 'yes' !== $this->_settings['read_more'] ) {
			return;
		}
		reycore_assets()->add_styles('rey-buttons'); ?>

		<div class="reyEl-bpost-footer">

			<a class="btn btn-line-active" href="<?php echo esc_url( get_permalink() ) ?>">

				<?php if( $custom_text = $this->_settings['read_more_text'] ){
					echo $custom_text;
				}
				else {
					esc_html_e('CONTINUE READING', 'rey-core');
				} ?>

				<span class="screen-reader-text"> <?php echo get_the_title(); ?></span>
			</a>
			<?php
			if( 'yes' === $this->_settings['read_duration'] ){
				if( function_exists('rey__postDuration') ){
					echo rey__postDuration();
				}
			} ?>
		</div>

		<?php
	}


	public function render_start(){

		reycore_assets()->add_styles('rey-blog');

		$skin = $this->_settings['_skin'] ? $this->_settings['_skin'] : 'default';

		$attributes = [];

		$attributes['class'] = [
			'rey-element',
			'reyEl-bPostGrid',
			'reyEl-bPostGrid--' . $skin,
		];

		if( 'inner' === $skin) {

			if( 'yes' === $this->_settings['inner_image_stretch'] ) {
				$attributes['class']['inner_stretch_img'] = '--stretch-image';
			}

			$attributes['class']['inner_effect'] = '--inner-effect-' . $this->_settings['inner_hover_effect'];
		}

		// multiple rows, load masonry
		if(
			absint($this->_settings['per_row']) < absint($this->_settings['posts_per_page']) &&
			$this->_settings['masonry'] !== '' &&
			$this->_settings['carousel'] === '' ){
			$attributes['class']['masonry'] = '--masonry';
			reycore_assets()->add_scripts(['reycore-widget-basic-post-grid-scripts']);
			wp_enqueue_script('masonry');
		}

		if( $this->_settings['carousel'] !== '' ):

			unset($attributes['class']['masonry']);

			$attributes['class'][] = 'splide';

			if( $side_offset = $this->_settings['carousel_viewport_offset_side'] ){
				$attributes['data-side-offset'] = esc_attr($side_offset);
			}

			if($carousel_id = $this->_settings['carousel_id'] ){
				$attributes['class']['carousel_id'] = esc_attr($carousel_id);
				$attributes['data-slider-carousel-id'] = esc_attr($carousel_id);
			}

			$this->slider_components = new \ReyCore\Libs\Slider_Components( $this, [
				'arrows' => 'carousel_arrows',
				'dots' => 'carousel_pagination',
			] );

			reycore_assets()->add_scripts(['reycore-widget-basic-post-grid-scripts', 'splidejs', 'rey-splide']);
			reycore_assets()->add_styles('rey-splide');

			$carousel_config = [
				'slides_to_show' => $this->_settings['slides_to_show'],
				'autoplay' => $this->_settings['autoplay'] !== '',
				'autoplay_speed' => $this->_settings['autoplay_speed'],
				'pause_on_hover' => $this->_settings['pause_on_hover'] !== '',
				'infinite' => $this->_settings['infinite'] !== '',
				'effect' => $this->_settings['effect'],
				'speed' => $this->_settings['speed'],
				'direction' => $this->_settings['direction'],
				'gap' => $this->_settings['gap'],
				'gap_tablet' => isset($this->_settings['gap_tablet']) ? $this->_settings['gap_tablet'] : '',
				'gap_mobile' => isset($this->_settings['gap_mobile']) ? $this->_settings['gap_mobile'] : '',
				'carousel_padding' => $this->_settings['carousel_padding'],
				'carousel_padding_tablet' => isset($this->_settings['carousel_padding_tablet']) ? $this->_settings['carousel_padding_tablet'] : '',
				'carousel_padding_mobile' => isset($this->_settings['carousel_padding_mobile']) ? $this->_settings['carousel_padding_mobile'] : '',
				'pagination' => $this->_settings['carousel_pagination'] !== ''
			];

			if( $this->_settings['carousel_arrows'] !== '' ){
				$carousel_config['createArrows'] = [
					'navSelector' => '--basic'
				];
			}

			if( isset($this->_settings['slides_to_show_tablet']) && $slides_to_show_tablet = $this->_settings['slides_to_show_tablet'] ){
				$carousel_config['slides_to_show_tablet'] = $slides_to_show_tablet;
			}

			if( isset($this->_settings['slides_to_show_mobile']) && $slides_to_show_mobile = $this->_settings['slides_to_show_mobile'] ){
				$carousel_config['slides_to_show_mobile'] = $slides_to_show_mobile;
			}

			$attributes['data-carousel-settings'] = wp_json_encode($carousel_config);

		endif;

		$this->add_render_attribute( 'wrapper', $attributes );

		$GLOBALS["rey_exclude_posts"] = wp_list_pluck( $this->_query->posts, 'ID' );

		?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
		<?php

		if( $this->_settings['carousel'] !== '' ){
			$this->slider_components->render_dots_container();
			echo '<div class="splide__track"><div class="splide__list">';
		}
	}

	public function render_end(){

		if( $this->_settings['carousel'] !== '' ){
			echo '</div></div>';
		}

		echo '</div>';

		$this->render_pagination();


	}

	public function get_classes(){

		$classes = [];

		if( $this->_settings['carousel'] !== '' ){
			$classes[] = 'splide__slide';
		}

		return implode(' ', $classes);
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

		$this->_settings = $this->get_settings_for_display();

		$this->posts_archive = new \ReyCore\Elementor\TagPosts( [
			'el_instance' => $this
		], $this->_settings );

		if( $this->posts_archive && $this->posts_archive->lazy_start() ){
			return;
		}

		reycore_assets()->add_styles($this->get_style_name());

		$this->query_posts();

		if ( ! $this->_query->found_posts ) {
			return;
		}

		$this->render_start();

		while ( $this->_query->have_posts() ) : $this->_query->the_post();
		?>
			<div class="reyEl-bPostGrid-item <?php echo $this->get_classes(); ?>">
				<div class="reyEl-bPostGrid-itemInner --box-styler">
					<?php
						$this->render_thumbnail();
						$this->render_meta();
						$this->render_title();
						$this->render_excerpt();
						$this->render_footer();
					?>
				</div>
			</div>
		<?php endwhile;
		wp_reset_postdata();

		$this->render_end();

		if( $this->posts_archive ){
			$this->posts_archive->lazy_end();
		}

	}

	function render_pagination(){

		if( $this->_settings['add_pagination'] !== '' && function_exists('rey__pagination') ){

			add_filter('rey/blog_pagination_args', function($args){

				$args['base']    = str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) );
				$args['format']  = '?paged=%#%';
				$args['total']  = $this->_query->max_num_pages;
				$args['current']  = max( 1, get_query_var( 'paged' ) );
				$args['prev_text']  = reycore__arrowSvg(false);
				$args['next_text']  = reycore__arrowSvg();

				return $args;
			});

			rey__pagination();
		}

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
