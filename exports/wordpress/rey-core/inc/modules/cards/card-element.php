<?php
namespace ReyCore\Modules\Cards;

use ReyCore\Modules\Cards\Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( Base::instance() ):

class CardElement extends \ReyCore\Elementor\WidgetsBase {

	public $_settings = [];
	public $_items = [];
	public $item_key;
	public $card_key;

	public $selectors = [
		'wrapper'    => '{{WRAPPER}}',
		'card'       => '{{WRAPPER}} .rey-card',
		'card_hover' => '{{WRAPPER}} .rey-card:hover',
		'media_link' => '{{WRAPPER}} .__media-link',
		'media'      => '{{WRAPPER}} .__media',
		'title'      => '{{WRAPPER}} .__captionTitle',
		'title_a'    => '{{WRAPPER}} .__captionTitle, {{WRAPPER}} .__captionTitle a',
	];

	public $supports = [
		'shadow' => false
	];

	public static $more_total = 0;

	public function __construct( $data = [], $args = null ) {
		parent::__construct( $data, $args );
		$this->card_key = Base::CARD_KEY;
	}

	public function element_support( $s ){
		return isset($this->supports[$s]) && $this->supports[$s];
	}

	public function add_element_controls(){}

	public function register_controls() {
		$this->add_element_controls();
	}

	public function get_sources(){
		return Base::instance()->get_sources();
	}

	public function get_source_controls(){
		foreach ( $this->get_sources() as $source_id => $source) {
			$source->controls($this);
		}
	}

	public function controls__content(){

		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Content', 'rey-core' ),
			]
		);

			$sources = [];

			foreach ($this->get_sources() as $source) {
				$sources[ $source->get_id() ] = $source->get_title();
			}

			$this->add_control(
				'source',
				[
					'label' => esc_html__( 'Data Source', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'images',
					'options' => $sources,
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Image_Size::get_type(),
				[
					'name' => 'image', // Usage: `{name}_size` and `{name}_custom_dimension`, in this case `image_size` and `image_custom_dimension`.
					'default' => 'large',
					'condition' => [
						'source!' => 'reviews',
					],
				]
			);

		$this->end_controls_section();


	}

	public function controls__teaser(){

		$this->start_controls_section(
			'section_teaser',
			[
				'label' => __( 'Teaser', 'rey-core' ),
			]
		);

			$this->add_control(
				'teaser_gs',
				[
					'label_block' => true,
					'label'       => __( 'Select Global Section', 'rey-core' ),
					'description' => __( 'Include a global section inside the list, at a specific chosen location.', 'rey-core' ),
					'type'        => 'rey-query',
					'default'     => '',
					'placeholder' => esc_html__('- Select -', 'rey-core'),
					'query_args'  => [
						'type'      => 'posts',
						'post_type' => \ReyCore\Elementor\GlobalSections::POST_TYPE,
						'meta'      => [
							'meta_key'   => 'gs_type',
							'meta_value' => 'generic',
						],
						'edit_link' => true,
					],
				]
			);

			$this->add_control(
				'teaser_position',
				[
					'label' => esc_html__( 'Position', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'last',
					'options' => [
						'first'  => esc_html__( 'First', 'rey-core' ),
						'last'  => esc_html__( 'Last', 'rey-core' ),
						'custom'  => esc_html__( 'Custom', 'rey-core' ),
					],
					'condition' => [
						'teaser_gs!' => '',
					],
				]
			);

			$this->add_control(
				'teaser_pos_custom',
				[
					'label' => esc_html__( 'Position', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 1,
					'min' => 1,
					'max' => 20,
					'step' => 1,
					'condition' => [
						'teaser_gs!' => '',
						'teaser_position' => 'custom',
					],
				]
			);

			$this->add_control(
				'teaser_pos_repeat',
				[
					'label' => esc_html__( 'Repeat every nth', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 2,
					'max' => 100,
					'step' => 1,
					'condition' => [
						'teaser_gs!' => '',
					],
				]
			);

		$this->end_controls_section();

	}

	public function controls__load_more(){

		$this->start_controls_section(
			'section_load_more',
			[
				'label' => __( '"Load More" Settings', 'rey-core' ),
				'condition' => [
					'source' => ['posts', 'category', 'product_cat', 'attributes', 'reviews', 'images', 'custom'],
				],
			]
		);

			$this->add_control(
				'load_more_enable',
				[
					'label' => esc_html__( 'Add "Load More" button', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

			$this->add_control(
				'load_more_text',
				[
					'label' => esc_html__( 'Text', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'placeholder' => esc_html__( 'eg: LOAD MORE', 'rey-core' ),
					'condition' => [
						'load_more_enable!' => '',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'load_more_typo',
					'global' => [
						'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Typography::TYPOGRAPHY_PRIMARY,
					],
					'selector' => '{{WRAPPER}} .btn.rey-grid-loadMore',
					'condition' => [
						'load_more_enable!' => '',
					],
				]
			);

			$this->add_control(
				'load_more_style',
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
					],
					'condition' => [
						'load_more_enable!' => '',
					],
				]
			);

			$this->add_control(
				'load_more_color',
				[
					'label' => __( 'Text Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .btn.rey-grid-loadMore' => 'color: {{VALUE}}',
					],
					'condition' => [
						'load_more_enable!' => '',
					],
				]
			);

			$this->add_control(
				'load_more_hover_color',
				[
					'label' => __( 'Text Hover Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .btn.rey-grid-loadMore:hover' => 'color: {{VALUE}}',
					],
					'condition' => [
						'load_more_enable!' => '',
					],
				]
			);

			$this->add_control(
				'load_more_bg_color',
				[
					'label' => __( 'Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .btn.rey-grid-loadMore' => 'background-color: {{VALUE}}',
					],
					'condition' => [
						'load_more_enable!' => '',
					],
				]
			);

			$this->add_control(
				'load_more_hover_bg_color',
				[
					'label' => __( 'Background Hover Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .btn.rey-grid-loadMore:hover' => 'background-color: {{VALUE}}',
					],
					'condition' => [
						'load_more_enable!' => '',
					],
				]
			);

			$this->add_control(
				'load_more_align',
				[
					'label' => __( 'Alignment', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::CHOOSE,
					'options' => [
						'flex-start' => [
							'title' => __( 'Left', 'rey-core' ),
							'icon' => 'eicon-text-align-left',
						],
						'center' => [
							'title' => __( 'Center', 'rey-core' ),
							'icon' => 'eicon-text-align-center',
						],
						'flex-end' => [
							'title' => __( 'Right', 'rey-core' ),
							'icon' => 'eicon-text-align-right',
						],
					],
					'selectors' => [
						'{{WRAPPER}}' => '--load-more-align: {{VALUE}}',
					],
					'condition' => [
						'load_more_enable!' => '',
					],
				]
			);

			$this->add_control(
				'load_more_radius',
				[
					'label' => esc_html__( 'Border Radius', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .btn.rey-grid-loadMore' => 'border-radius: {{VALUE}}px',
					],
					'condition' => [
						'load_more_enable!' => '',
					],
				]
			);

			$this->add_control(
				'load_more_distance',
				[
					'label' => esc_html__( 'Top Distance', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}}' => '--load-more-distance: {{VALUE}}px',
					],
					'condition' => [
						'load_more_enable!' => '',
					],
				]
			);

		$this->end_controls_section();
	}

	/**
	 * Get CTP list except products
	 *
	 * @since 2.4.5
	 **/
	public static function get_post_types_list_except_product()
	{
		return reycore__get_post_types_list([
			'exclude' => [
				'product'
			]
		]);
	}

	public function controls__content_styles(){

		$this->start_controls_section(
			'section_content_style',
			[
				'label' => __( 'Layout', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$cards_list = Base::instance()->get_cards_list();
			$cards_list_keys = array_keys($cards_list);

			$this->add_control(
				Base::CARD_KEY,
				[
					'label' => esc_html__( 'Select Layout', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'basic',
					'options' => $cards_list,
				]
			);

			$this->add_control(
				'card_align',
				[
					'label' => esc_html__( 'Alignment', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						'left'  => esc_html__( 'Left', 'rey-core' ),
						'center'  => esc_html__( 'Center', 'rey-core' ),
						'right'  => esc_html__( 'Right', 'rey-core' ),
						'justify'  => esc_html__( 'Justified', 'rey-core' ),
						''  => esc_html__( '- Inherit -', 'rey-core' ),
					],
					'selectors' => [
						$this->selectors['card'] => 'text-align: {{VALUE}}; --align: {{VALUE}};',
					],
					'condition' => [
						Base::CARD_KEY => $cards_list_keys,
					],
				]
			);

			$this->add_control(
				'card_valign',
				[
					'label' => esc_html__( 'Vertical Alignment', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'options' => [
						'' => esc_html__( '- Inherit -', 'rey-core' ),
						'flex-start' => esc_html__( 'Start', 'rey-core' ),
						'center' => esc_html__( 'Center', 'rey-core' ),
						'flex-end' => esc_html__( 'End', 'rey-core' ),
					],
					'selectors' => [
						$this->selectors['card'] => 'align-items: {{VALUE}}; -v-align-items: {{VALUE}};',
					],
					'condition' => [
						Base::CARD_KEY => $cards_list_keys,
					],
				]
			);

			$this->add_control(
				'card_radius',
				[
					'label' => esc_html__( 'Corner Radius', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 500,
					'step' => 1,
					'selectors' => [
						$this->selectors['card'] => '--card-radius: {{SIZE}}px; overflow: hidden;',
					],
					'condition' => [
						Base::CARD_KEY => $cards_list_keys,
					],
				]
			);

			$this->add_responsive_control(
				'card_padding',
				[
					'label' => __( 'Padding', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'selectors' => [
						$this->selectors['card'] => '--spacing-top:{{TOP}}px; --spacing-right:{{RIGHT}}px; --spacing-bottom:{{BOTTOM}}px; --spacing-left:{{LEFT}}px;',
					],
					'condition' => [
						Base::CARD_KEY => $cards_list_keys,
					],
				]
			);

			$this->add_responsive_control(
				'card_height',
				[
					'label' => esc_html__( 'Height', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'range' => [
						'px' => [
							'min' => 10,
							'max' => 1200,
							'step' => 1,
						],
					],
					'default' => [
						'unit' => 'px',
						'size' => 490,
					],
					'selectors' => [
						$this->selectors['card'] => '--item-height: {{SIZE}}{{UNIT}};',
					],
					'condition' => [
						Base::CARD_KEY => Base::instance()->get_card_supports('height')
					],
					// 'condition' => [
					// 	Base::CARD_KEY => $cards_list_keys,
					// ],
				]
			);

			$this->start_controls_tabs( 'card_styles_tabs', [
				'condition' => [
					Base::CARD_KEY => $cards_list_keys,
				],
			] );

				$this->start_controls_tab(
					'card_styles_tab',
					[
						'label' => esc_html__( 'Normal', 'rey-core' ),
					]
				);

					$this->add_control(
						'card_color',
						[
							'label' => esc_html__( 'Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								$this->selectors['card'] => 'color: {{VALUE}}; --color: {{VALUE}};',
							],
						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Background::get_type(),
						[
							'name' => 'card_bg_color',
							'types' => [ 'classic', 'gradient' ],
							'selector' => $this->selectors['card'],
							'condition' => [
								Base::CARD_KEY => Base::instance()->get_card_supports('background')
							],
							'fields_options' => [
								'color' => [
									'selectors' => [
										'{{SELECTOR}}' => 'background-color:{{VALUE}}; --bg-color: {{VALUE}};',
									],
								]
							],
						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Border::get_type(),
						[
							'name' => 'card_border',
							'selector' => $this->selectors['card'],
							'fields_options' => [
								'color' => [
									'selectors' => [
										'{{SELECTOR}}' => 'border-color: {{VALUE}}; --border-color: {{VALUE}};',
									],
								]
							],
						]
					);

					if( $this->element_support('shadow') ){

						$this->add_control(
							'card_shadow',
							[
								'label' => esc_html__( 'Shadow', 'rey-core' ),
								'type' => \Elementor\Controls_Manager::SELECT,
								'default' => '',
								'options' => [
									''  => esc_html__( 'None', 'rey-core' ),
									'var(--b-shadow-1)'  => esc_html__( 'Preset #1', 'rey-core' ),
									'var(--b-shadow-2)'  => esc_html__( 'Preset #2', 'rey-core' ),
									'var(--b-shadow-3)'  => esc_html__( 'Preset #3', 'rey-core' ),
									'var(--b-shadow-4)'  => esc_html__( 'Preset #4', 'rey-core' ),
									'var(--b-shadow-5)'  => esc_html__( 'Preset #5', 'rey-core' ),
									'custom'  => esc_html__( 'Custom Box Shadow', 'rey-core' ),
								],
								'selectors' => [
									$this->selectors['card'] => 'box-shadow: {{VALUE}};',
								],
							]
						);

						$this->add_group_control(
							\Elementor\Group_Control_Box_Shadow::get_type(),
							[
								'name' => 'card_custom_shadow',
								'selector' => $this->selectors['card'],
								'condition' => [
									'card_shadow' => 'custom',
								],
							]
						);
					}

				$this->end_controls_tab();

				$this->start_controls_tab(
					'card_styles_hover_tab',
					[
						'label' => esc_html__( 'Hover', 'rey-core' ),
					]
				);

					$this->add_control(
						'card_color_hover',
						[
							'label' => esc_html__( 'Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								$this->selectors['card_hover'] => 'color: {{VALUE}}; --color: {{VALUE}}; --color-hover: {{VALUE}};',
							],
						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Background::get_type(),
						[
							'name' => 'card_bg_color_hover',
							'selector' => $this->selectors['card_hover'],
							'condition' => [
								Base::CARD_KEY => Base::instance()->get_card_supports('background')
							],
							'fields_options' => [
								'color' => [
									'selectors' => [
										'{{SELECTOR}}' => 'background-color:{{VALUE}}; --bg-color: {{VALUE}}; --bg-color-hover: {{VALUE}};',
									],
								]
							],
						]
					);

					$this->add_control(
						'card_border_color_hover',
						[
							'label' => esc_html__( 'Border Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								$this->selectors['card_hover'] => 'border-color: {{VALUE}}; --border-color: {{VALUE}}; --border-color-hover: {{VALUE}};',
							],
						]
					);

					if( $this->element_support('shadow') ){

						$this->add_control(
							'card_shadow_hover',
							[
								'label' => esc_html__( 'Shadow', 'rey-core' ),
								'type' => \Elementor\Controls_Manager::SELECT,
								'default' => '',
								'options' => [
									''  => esc_html__( 'None', 'rey-core' ),
									'var(--b-shadow-1)'  => esc_html__( 'Preset #1', 'rey-core' ),
									'var(--b-shadow-2)'  => esc_html__( 'Preset #2', 'rey-core' ),
									'var(--b-shadow-3)'  => esc_html__( 'Preset #3', 'rey-core' ),
									'var(--b-shadow-4)'  => esc_html__( 'Preset #4', 'rey-core' ),
									'var(--b-shadow-5)'  => esc_html__( 'Preset #5', 'rey-core' ),
									'custom'  => esc_html__( 'Custom Box Shadow', 'rey-core' ),
								],
								'selectors' => [
									$this->selectors['card_hover'] => 'box-shadow: {{VALUE}};',
								],
							]
						);

						$this->add_group_control(
							\Elementor\Group_Control_Box_Shadow::get_type(),
							[
								'name' => 'card_custom_shadow_hover',
								'selector' => $this->selectors['card'],
								'condition' => [
									'card_shadow' => 'custom',
								],
							]
						);

					}

					$this->add_control(
						'card_hover_transition',
						[
							'label' => esc_html__( 'Transition Duration', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::SLIDER,
							'default' => [],
							'range' => [
								'px' => [
									'max' => 3000,
									'step' => 50,
								],
							],
							'render_type' => 'ui',
							'separator' => 'before',
							'selectors' => [
								$this->selectors['card'] => '--transition-duration: {{SIZE}}ms;',
							],
						]
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();


			// effect
			// box snhadow
			// border
			//

			/*
			$this->add_control(
				'clip_effect',
				[
					'label' => esc_html__( 'Clip Effect', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
					'condition' => [
						Base::CARD_KEY => $this->get_card_supports('clip')
					],
				]
			);
			*/

			// Individual card settings
			Base::instance()->add_cards_controls( $this );

		$this->end_controls_section();
	}

	public function controls__media_styles(){

		$this->start_controls_section(
			'section_media_style',
			[
				'label' => __( 'Media Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					Base::CARD_KEY => array_keys(Base::instance()->get_cards_list()),
				],
			]
		);

			$this->add_control(
				'image_show',
				[
					'label' => esc_html__( 'Display Image', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- Inherit -', 'rey-core' ),
						'yes'  => esc_html__( 'Yes', 'rey-core' ),
						'no'  => esc_html__( 'No', 'rey-core' ),
					],
				]
			);

			$this->add_control(
				'media_fit',
				[
					'label' => esc_html__( 'Media Fit', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( 'Natural', 'rey-core' ),
						'cover'  => esc_html__( 'Cover', 'rey-core' ),
						'contain'  => esc_html__( 'Contain', 'rey-core' ),
					],
					'selectors' => [
						$this->selectors['media'] => 'object-fit: {{VALUE}};',
					],
					'condition' => [
						'image_show!' => 'no',
						// supports_stretch! = no
					],
				]
			);

			$this->add_responsive_control(
				'media_width',
				[
					'label' => esc_html__( 'Media Width', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', '%' ],
					'range' => [
						'px' => [
							'min' => 30,
							'max' => 1200,
							'step' => 1,
						],
					],
					'default' => [],
					'selectors' => [
						$this->selectors['media_link'] => '--media-max-width: {{SIZE}}{{UNIT}};',
					],
					'condition' => [
						Base::CARD_KEY => Base::instance()->get_card_supports('media-width'),
						'grid_type' => 'vlist'
					],
				]
			);

			$this->add_responsive_control(
				'media_height',
				[
					'label' => esc_html__( 'Media Height', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'range' => [
						'px' => [
							'min' => 10,
							'max' => 1200,
							'step' => 1,
						],
					],
					'default' => [],
					'selectors' => [
						$this->selectors['media'] => 'height: {{SIZE}}{{UNIT}};',
					],
					'condition' => [
						'media_fit!' => '',
						'image_show!' => 'no',
					],
				]
			);

			$this->add_control(
				'media_radius',
				[
					'label' => esc_html__( 'Corner Radius', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 500,
					'step' => 1,
					'selectors' => [
						$this->selectors['card'] => '--media-radius: {{SIZE}}px; overflow: hidden;',
					],
					'condition' => [
						'image_show!' => 'no',
					],
				]
			);

			$this->add_control(
				'overlay_heading',
				[
				   'label' => esc_html__( 'Overlay', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Background::get_type(),
				[
					'name' => 'overlay_color',
					'types' => [ 'classic', 'gradient' ],
					'selector' => '{{WRAPPER}} .__overlay',
					'exclude' => [ 'image' ],
					'fields_options' => [
						'background' => [
							'label' => esc_html__('Color Type', 'rey-core'),
						],
						'color' => [
							'selectors' => [
								'{{SELECTOR}}' => 'background: {{VALUE}};',
							],
						]
					],
				]
			);

			$this->add_control(
				'overlay_opacity',
				[
				   'label' => esc_html__( 'Opacity', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'max' => 1,
					'step' => 0.05,
					'selectors' => [
						'{{WRAPPER}} .__overlay' => 'opacity:{{VALUE}};',
					]
				]
			);

			$this->add_control(
				'overlay_hover_opacity',
				[
				   'label' => esc_html__( 'Opacity (Hover)', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'max' => 1,
					'step' => 0.05,
					'selectors' => [
						$this->selectors['card_hover'] . ' .__overlay' => 'opacity:{{VALUE}};',
					]
				]
			);


		$this->end_controls_section();

	}

	public function controls__title_styles(){

		$this->start_controls_section(
			'section_title_style',
			[
				'label' => __( 'Title Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					Base::CARD_KEY => array_keys(Base::instance()->get_cards_list()),
				],
			]
		);

			$this->add_control(
				'title_show',
				[
					'label' => esc_html__( 'Display Title', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- Inherit -', 'rey-core' ),
						'yes'  => esc_html__( 'Yes', 'rey-core' ),
						'no'  => esc_html__( 'No', 'rey-core' ),
					],
				]
			);

			$this->add_control(
				'title_color',
				[
					'label' => esc_html__( 'Title Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$this->selectors['title_a'] => 'color: {{VALUE}}',
					],
					'condition' => [
						'title_show!' => 'no',
					],
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
					'selector' => $this->selectors['title'],
					'condition' => [
						'title_show!' => 'no',
					],
				]
			);

			$this->add_control(
				'title_link',
				[
					'label' => esc_html__( 'Wrap in link', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'condition' => [
						'title_show!' => 'no',
					],
				]
			);

			$this->add_responsive_control(
				'title_min_height',
				[
					'label' => __( 'Title Min. Height', 'rey-core' ) . ' (px)',
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 300,
					'selectors' => [
						$this->selectors['title'] => 'min-height: {{VALUE}}px;',
					],
				]
			);

		$this->end_controls_section();

	}

	public function controls__subtitle_styles(){

		$this->start_controls_section(
			'section_subtitle_style',
			[
				'label' => __( 'Subtitle Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					Base::CARD_KEY => array_keys(Base::instance()->get_cards_list()),
				],
			]
		);

			$this->add_control(
				'subtitle_show',
				[
					'label' => esc_html__( 'Show', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- Inherit -', 'rey-core' ),
						'yes'  => esc_html__( 'Yes', 'rey-core' ),
						'no'  => esc_html__( 'No', 'rey-core' ),
					],
				]
			);

			$this->add_control(
				'subtitle_hide_mobile',
				[
					'label' => esc_html__( 'Hide on mobile', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'condition' => [
						'subtitle_show!' => 'no',
					],
					'selectors' => [
						'(mobile){{WRAPPER}} .__captionSubtitle' => 'display:none;',
						'(mobile){{WRAPPER}} .__captionTitle' => 'margin-bottom:0;',
					],
				]
			);

			$this->add_control(
				'subtitle_color',
				[
					'label' => esc_html__( 'Sub-Title Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .__captionSubtitle' => 'color: {{VALUE}}',
					],
					'condition' => [
						'subtitle_show!' => 'no',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'subtitle_typo',
					'label' => esc_html__('Sub-Title Typography', 'rey-core'),
					'selector' => '{{WRAPPER}} .__captionSubtitle',
					'condition' => [
						'subtitle_show!' => 'no',
					],
				]
			);

			$this->add_control(
				'subtitle_length',
				[
					'label' => __( 'Subtitle Length (Words Count)', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 20,
					'min' => 0,
					'max' => 200,
					'step' => 0,
					'condition' => [
						'subtitle_show!' => 'no',
					],
				]
			);


		$this->end_controls_section();

	}

	public function controls__label_styles(){


		$this->start_controls_section(
			'section_label_style',
			[
				'label' => __( 'Label Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'source' => ['custom', 'posts', 'reviews'],
					Base::CARD_KEY => array_keys(Base::instance()->get_cards_list()),
				],
			]
		);

			$this->add_control(
				'label_show',
				[
					'label' => esc_html__( 'Show', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- Inherit -', 'rey-core' ),
						'yes'  => esc_html__( 'Yes', 'rey-core' ),
						'no'  => esc_html__( 'No', 'rey-core' ),
					],
				]
			);

			$this->add_control(
				'label_color',
				[
					'label' => esc_html__( 'Label Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .__captionLabel' => 'color: {{VALUE}}',
					],
					'condition' => [
						'label_show!' => 'no',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'label_typo',
					'label' => esc_html__('Label Typography', 'rey-core'),
					'global' => [
						'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Typography::TYPOGRAPHY_PRIMARY,
					],
					'selector' => '{{WRAPPER}} .__captionLabel',
					'condition' => [
						'label_show!' => 'no',
					],
				]
			);

			$this->add_responsive_control(
				'label_distance',
				[
					'label' => __( 'Label Distance', 'rey-core' ) . ' (px)',
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'selectors' => [
						'{{WRAPPER}} .__captionLabel' => '--distance: {{SIZE}}px;',
					],
					'condition' => [
						'label_show!' => 'no',
					],
				]
			);

		$this->end_controls_section();

	}

	public function controls__button_styles(){

		$this->start_controls_section(
			'section_button_style',
			[
				'label' => __( 'Button Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'source' => ['custom', 'posts', 'product_cat', 'category', 'attributes'],
					Base::CARD_KEY => array_keys(Base::instance()->get_cards_list()),
				],
			]
		);

		$this->add_control(
			'button_show',
			[
				'label' => esc_html__( 'Show', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => esc_html__( '- Inherit -', 'rey-core' ),
					'yes'  => esc_html__( 'Yes', 'rey-core' ),
					'no'  => esc_html__( 'No', 'rey-core' ),
				],
			]
		);

			$this->add_control(
				'button_text',
				[
					'label' => __( 'Button Text', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'dynamic' => [
						'active' => true,
					],
					'default' => __( 'Click here', 'rey-core' ),
					'placeholder' => __( 'eg: SEE MORE', 'rey-core' ),
					'condition' => [
						'source' => ['posts', 'product_cat', 'category', 'attributes'],
						'button_show!' => 'no',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'button_typo',
					'label' => esc_html__('Button Typography', 'rey-core'),
					'selector' => '{{WRAPPER}} .__captionBtn a',
					'condition' => [
						'button_show!' => 'no',
					],
				]
			);

			$this->add_control(
				'button_style',
				[
					'label' => __( 'Button Style', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => __( '- Inherit -', 'rey-core' ),
						'btn-simple'  => __( 'Link', 'rey-core' ),
						'btn-primary'  => __( 'Primary', 'rey-core' ),
						'btn-secondary'  => __( 'Secondary', 'rey-core' ),
						'btn-primary-outline'  => __( 'Primary Outlined', 'rey-core' ),
						'btn-secondary-outline'  => __( 'Secondary Outlined', 'rey-core' ),
						'btn-line-active'  => __( 'Underlined', 'rey-core' ),
						'btn-line'  => __( 'Hover Underlined', 'rey-core' ),
						'btn-primary-outline btn-dash'  => __( 'Primary Outlined & Dash', 'rey-core' ),
						'btn-primary-outline btn-dash btn-rounded'  => __( 'Primary Outlined & Dash & Rounded', 'rey-core' ),
						'btn-dash-line'  => __( 'Dash', 'rey-core' ),
					],
					'condition' => [
						'button_show!' => 'no',
					],
				]
			);

			$this->start_controls_tabs( 'btn_tabs_styles', [
				'condition' => [
					'button_show!' => 'no',
				],
			]);

				$this->start_controls_tab(
					'btn_tab_default',
					[
						'label' => __( 'Default', 'rey-core' ),
					]
				);

					$this->add_control(
						'button_color',
						[
							'label' => esc_html__( 'Primary Color (text)', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .__captionBtn .btn' => 'color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'button_color_bg',
						[
							'label' => esc_html__( 'Primary Color (background)', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .__captionBtn .btn' => 'background-color: {{VALUE}}',
							],
						]
					);

				$this->end_controls_tab();

				$this->start_controls_tab(
					'btn_tab_hover',
					[
						'label' => __( 'Hover', 'rey-core' ),
					]
				);

					$this->add_control(
						'button_color_hover',
						[
							'label' => esc_html__( 'Primary Color (text)', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .__captionBtn .btn:hover' => 'color: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'button_color_bg_hover',
						[
							'label' => esc_html__( 'Primary Color (background)', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .__captionBtn .btn:hover' => 'background-color: {{VALUE}}',
							],
						]
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();

		$this->end_controls_section();
	}

	public function pre_get_posts_query_filter( $query ){

		$query_id = $this->_settings[ 'query_id' ];

		do_action( "reycore/elementor/query/{$query_id}", $query, $this );

	}

	public function parse_item(){

		if( ! (isset($this->_items[$this->item_key]) && ($item = $this->_items[$this->item_key])) ){
			return;
		}

		$args = [];

		if( ($sources = $this->get_sources()) &&  isset($sources[ $this->_settings['source'] ]) ){
			$args = $sources[ $this->_settings['source'] ]->parse_item( $this );
		}

		if( $button_style = $this->_settings['button_style'] ){
			$args['button_style'] = $button_style;
		}

		$args['button_show'] = $this->_settings['button_show'];

		if( $this->_settings['title_show'] === 'no' ){
			$args['title'] = '';
		}

		$args['subtitle_show'] = $this->_settings['subtitle_show'];

		if( $this->_settings['subtitle_show'] === 'no' ){
			$args['subtitle'] = '';
		}

		if( $this->_settings['label_show'] === 'no' ){
			$args['label'] = '';
		}

		if( $this->_settings['image_show'] === 'no' ){
			$args['image'] = [];
		}

		$args['uid'] = sprintf('%s-%d', $this->get_id(), $this->item_key);

		$this->_items[$this->item_key] = $args;

	}

	public function get_items_data(){

		$items = [];

		if( ($sources = $this->get_sources()) &&  isset($sources[ $this->_settings['source'] ]) ){
			$items = $sources[ $this->_settings['source'] ]->query( $this );
		}

		if( is_wp_error($items) ){
			return [];
		}

		return $items;

	}

	public function render_item(){
		Base::instance()->render_card($this);
	}

	private function __load_more_button( $per_page ){

		$offset = absint($per_page) + $this->get_offset();

		echo '<div class="rey-grid-loadMore-wrapper">';

			printf(
				'<a href="#" class="btn %6$s rey-grid-loadMore" data-offset="%2$d" data-el-id="%3$s" data-target="%4$s" data-limit="%5$d">
					<span class="__text">%1$s</span>
					<span class="rey-lineLoader"></span>
				</a>',
				(isset($this->_settings['load_more_text']) && ($ct = $this->_settings['load_more_text'])) ? $ct : esc_html__('Load more', 'rey-core'),
				$offset,
				esc_attr($this->get_id()),
				'.rey-gridEl .__items',
				$per_page,
				(isset($this->_settings['load_more_style']) && ($st = $this->_settings['load_more_style'])) ? $st : 'btn-line-active'
			);

		echo '</div>';

		reycore_assets()->add_styles(Base::ASSET_HANDLE . '-load-more');
		reycore_assets()->add_scripts(Base::ASSET_HANDLE . '-load-more');
	}

	public function get_offset(){

		if( isset($_REQUEST['reycore_grid_offset_id']) && $this->get_id() === $_REQUEST['reycore_grid_offset_id'] ){
			if( isset($_REQUEST['reycore_grid_offset']) && $offset = absint($_REQUEST['reycore_grid_offset']) ){
				return $offset;
			}
		}

		return 0;
	}

	public function render_load_more_button(){

		if( '' === $this->_settings['load_more_enable'] ){
			return;
		}

		$per_page = false;

		if( ($sources = $this->get_sources()) &&  isset($sources[ $this->_settings['source'] ]) ){
			$per_page = $sources[ $this->_settings['source'] ]->load_more_button_per_page( $this );
		}

		if( false !== $per_page ){
			$this->__load_more_button($per_page);
		}

	}

	public function render() {}

	public function content_template() {}
}
endif;
