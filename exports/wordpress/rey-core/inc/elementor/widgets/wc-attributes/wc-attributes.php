<?php
namespace ReyCore\Elementor\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WcAttributes extends \ReyCore\Elementor\WidgetsBase {

	private $letters = [];
	private $_settings = [];
	private $_attribute_terms = [];

	public static function get_rey_config(){
		return [
			'id' => 'wc-attributes',
			'title' => __( 'WooCommerce Attributes', 'rey-core' ),
			'icon' => 'eicon-menu-toggle',
			'categories' => [ 'rey-theme' ],
			'keywords' => [],
			'css' => [
				'assets/style.css',
				'assets/alphabetic.css',
			],
		];
	}

	public function rey_get_script_depends() {
		return [ 'reycore-woocommerce' ];
	}

	public function get_custom_help_url() {
		return reycore__support_url('kb/rey-elements/#woocommerce-attributes');
	}

	public function on_export($element)
    {
        unset(
            $element['settings']['attr_id']
        );

        return $element;
	}

	private $colors = [];

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
			'section_settings',
			[
				'label' => __( 'Settings', 'rey-core' ),
			]
		);

		$terms = function_exists('reycore_wc__get_attributes_list') ? reycore_wc__get_attributes_list() : [];

		$this->add_control(
			'attr_id',
			[
				'label' => __( 'Select Attribute', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => ['' => esc_html__('- Select -', 'rey-core')] + $terms,
			]
		);

		$this->add_control(
			'attr_list',
			[
				'label' => esc_html__( 'Attributes Display', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'all',
				'options' => [
					'all'              => esc_html__( 'List All', 'rey-core' ),
					'limited'          => esc_html__( 'List a Limited number', 'rey-core' ),
					'handpicked'       => esc_html__( 'Manually Handpicked', 'rey-core' ),
					'handpicked_order' => esc_html__( 'Manually Handpicked (Custom order)', 'rey-core' ),
					'dynamic'          => esc_html__( 'Dynamic (from Current Product)', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'attr_limit',
			[
				'label' => esc_html__( 'Display limit', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 1000,
				'step' => 1,
				'condition' => [
					'attr_id!' => '',
					'attr_list' => 'limited',
				],
			]
		);

		foreach($terms as $term => $term_label):

			$this->add_control(
				'attr_custom_' . $term,
				[
					'label' => sprintf( esc_html__( 'Select one or more %s attributes', 'rey-core' ), $term_label ),
					'placeholder' => esc_html__('- Select-', 'rey-core'),
					'type' => 'rey-query',
					'query_args' => [
						'type' => 'terms', // terms, posts
						'taxonomy' => wc_attribute_taxonomy_name( $term ),
					],
					'multiple' => true,
					'default' => [],
					'label_block' => true,
					'condition' => [
						'attr_id' => $term,
						'attr_list' => 'handpicked',
					],
				]
			);


		endforeach;

		// Custom order
		$custom_order_attr = new \Elementor\Repeater();

			$custom_order_attr->add_control(
				'attr',
				[
					'label' => esc_html__( 'Select attribute', 'rey-core' ),
					'placeholder' => esc_html__('- Select-', 'rey-core'),
					'type' => 'rey-query',
					'query_args' => [
						'type' => 'terms', // terms, posts
					],
					'label_block' => true,
					'default' => [],
				]
			);

		$this->add_control(
			'handpicked_order_attrs',
			[
				'label' => __( 'Manually add attributes (with ordering)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $custom_order_attr->get_controls(),
				'default' => [],
				'condition' => [
					'attr_list' => 'handpicked_order',
				],
			]
		);

		$this->add_control(
			'layout',
			[
				'label' => esc_html__( 'Layout', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'default',
				'options' => [
					'default'  => esc_html__( 'Default', 'rey-core' ),
					'alphabetic'  => esc_html__( 'Alphabetic List', 'rey-core' ),
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_sett_alphabetic',
			[
				'label' => __( 'Alphabetic Layout settings', 'rey-core' ),
				'condition' => [
					'layout' => 'alphabetic',
				],
			]
		);

		$this->add_responsive_control(
			'alphabetic_columns',
			[
				'label' => __( 'Letter Blocks Columns', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 1,
				'min' => 1,
				'max' => 4,
				'step' => 1,
				'condition' => [
					'layout' => 'alphabetic',
				],
				'selectors' => [
					'{{WRAPPER}} .reyEl-wcAttr-alphaItem' => '-ms-flex-preferred-size: calc(100% / {{VALUE}}); flex-basis: calc(100% / {{VALUE}});',
				],
				'render_type' => 'template',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'alphabetic_title_typo',
				'label' => esc_html__( 'Alphabetic Titles - Typo', 'rey-core' ),
				'selector' => '{{WRAPPER}} .reyEl-wcAttr-alphaItem > h4',
			]
		);

		$this->add_control(
			'alphabetic_title_color',
			[
				'label' => esc_html__( 'Alphabetic Titles - Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .reyEl-wcAttr-alphaItem > h4' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'alphabetic_menu_title',
			[
			   'label' => esc_html__( 'ALPHABETIC MENU', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'alphabetic_menu',
			[
				'label' => esc_html__( 'Show Alphabetic Menu', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
				'condition' => [
					'layout' => 'alphabetic',
				],
			]
		);

		$this->add_control(
			'alphabetic_menu_align',
			[
				'label' => __( 'Alphabetic Menu - Alignment', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					'' => __( 'Default', 'rey-core' ),
					'flex-start' => __( 'Start', 'rey-core' ),
					'center' => __( 'Center', 'rey-core' ),
					'flex-end' => __( 'End', 'rey-core' ),
					'space-between' => __( 'Space Between', 'rey-core' ),
					'space-around' => __( 'Space Around', 'rey-core' ),
					'space-evenly' => __( 'Space Evenly', 'rey-core' ),
				],
				'selectors' => [
					'{{WRAPPER}} .reyEl-wcAttr-alphMenu' => 'justify-content: {{VALUE}}',
				],
				'condition' => [
					'layout' => 'alphabetic',
					'alphabetic_menu' => 'yes',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'alphabetic_menu_typo',
				'label' => esc_html__( 'Alphabetic Menu - Typo', 'rey-core' ),
				'selector' => '{{WRAPPER}} .reyEl-wcAttr-alphMenu a',
				'condition' => [
					'layout' => 'alphabetic',
					'alphabetic_menu' => 'yes',
				],
			]
		);

		$this->add_control(
			'alphabetic_menu_color',
			[
				'label' => esc_html__( 'Alphabetic Menu - Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .reyEl-wcAttr-alphMenu a' => 'color: {{VALUE}}',
				],
				'condition' => [
					'layout' => 'alphabetic',
					'alphabetic_menu' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'alphabetic_menu_spacing',
			[
			   'label' => esc_html__( 'Alphabetic Menu - Spacing', 'rey-core' ),
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
					'unit' => 'em',
					'size' => 1,
				],
				'selectors' => [
					'{{WRAPPER}} .reyEl-wcAttr-alphMenu a' => 'padding-left: {{SIZE}}{{UNIT}}; padding-right: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'layout' => 'alphabetic',
					'alphabetic_menu' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'alphabetic_menu_margin',
			[
				'label' => __( 'Alphabetic Menu - Margin', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .reyEl-wcAttr-alphMenu' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'layout' => 'alphabetic',
					'alphabetic_menu' => 'yes',
				],
			]
		);


		$this->end_controls_section();

		$this->start_controls_section(
			'section_sett_advanced',
			[
				'label' => __( 'Advanced settings', 'rey-core' ),
			]
		);

		$this->add_control(
			'orderby',
			[
				'label' => __( 'Order By', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'name',
				'options' => [
					'name'  => __( 'Name', 'rey-core' ),
					'menu_order' => __( 'Menu Order', 'rey-core' ),
					'count'  => __( 'Count', 'rey-core' ),
					'rand'  => __( 'Random', 'rey-core' ),
				],
				'condition' => [
					'attr_id!' => '',
				],
			]
		);

		$this->add_control(
			'hide_empty',
			[
				'label' => __( 'Hide Empty', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default' => '',
				'condition' => [
					'attr_id!' => '',
				],
			]
		);

		$this->add_control(
			'hide_empty_invisib',
			[
				'label' => __( 'Hide Invisibles', 'rey-core' ),
				'description' => __( 'When enabled, the outofstock or excluded from catalog will also determine if the term is empty and should hide. Please remember this adds more queries and could slow down the site.', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [
					'attr_id!' => '',
					'attr_list' => ['all', 'limited'],
				],
			]
		);

		$this->add_control(
			'attr_link',
			[
				'label' => esc_html__( 'Attribute Link', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'archive',
				'options' => [
					''  => esc_html__( 'None', 'rey-core' ),
					'archive'  => esc_html__( 'Term link', 'rey-core' ),
					// 'filter'  => esc_html__( 'Filter link', 'rey-core' ),
				],
				'condition' => [
					'attr_id!' => '',
				],
				'separator' => 'before',
			]
		);

		$this->add_control(
			'url',
			[
				'label' => __( 'Custom URL for non-archive terms', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::URL,
				'placeholder' => __( 'eg: https://your-link.com', 'rey-core' ),
				'show_external' => false,
				'default' => [
					'url' => '',
				],
				'description' => __( 'If an Attribute does not have "Archive" enabled, the term link will fallback to the Shop page having a custom parameter (eg: domain.com/shop?attro-brand=99), just like a filter link. Adding a link here will replace the Shop page URL.', 'rey-core' ),
				'condition' => [
					'attr_id!' => '',
					'attr_link' => 'archive',
				],
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$this->add_control(
			'force_url',
			[
				'label' => esc_html__( 'Force the URL', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [
					'url[url]!' => '',
					'attr_id!' => '',
					'attr_link' => 'archive',
				],
				'description' => __( 'Force the URL above to all Attributes, not just the ones without Archive enabled.', 'rey-core' ),
			]
		);

		$this->add_control(
			'see_all_button',
			[
				'label' => esc_html__( '"View All" Button Text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => esc_html__( 'eg: View All', 'rey-core' ),
				'separator' => 'before',
			]
		);


		$this->add_control(
			'see_all_button_url',
			[
				'label' => __( '"View All" Button Link', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::URL,
				'placeholder' => __( 'eg: https://your-link.com', 'rey-core' ),
				'default' => [
					'url' => '',
				],
				'condition' => [
					'see_all_button!' => '',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Style', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'display',
			[
				'label' => __( 'Display as', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'list',
				'options' => [
					'list'  => __( 'List', 'rey-core' ),
					'color'  => __( 'Color', 'rey-core' ),
					'clist'  => __( 'Color List', 'rey-core' ),
					'button'  => __( 'Button', 'rey-core' ),
					'image'  => __( 'Image', 'rey-core' ),
					'ilist'  => __( 'Image List', 'rey-core' ),
				],
				// 'prefix_class' => 'reyEl-wcAttr--'
			]
		);

		$this->add_responsive_control(
			'columns',
			[
				'label' => __( 'Columns', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 1,
				'min' => 1,
				'max' => 4,
				'step' => 1,
				'condition' => [
					'display' => ['list', 'clist', 'ilist'],
				],
				'selectors' => [
					'{{WRAPPER}} .reyEl-wcAttr-list' => '--cols: {{VALUE}};',
				],
				'render_type' => 'template',

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
				'selectors' => [
					'{{WRAPPER}} .reyEl-wcAttr-list a' => 'justify-content: {{VALUE}}; text-align: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'text_color',
			[
				'label' => __( 'Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .reyEl-wcAttr-list a' => 'color: {{VALUE}}',
				],
				'condition' => [
					'display' => ['list', 'button', 'clist', 'ilist'],
				],
				'separator' => 'before',
			]
		);

		$this->add_control(
			'text_color_hover',
			[
				'label' => __( 'Text Hover Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .reyEl-wcAttr-list a:hover' => 'color: {{VALUE}}',
				],
				'condition' => [
					'display' => ['list', 'button', 'clist', 'ilist'],
				],
			]
		);

		$this->add_control(
			'border_color',
			[
				'label' => __( 'Border Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .reyEl-wcAttr-list' => '--sw-border-color: {{VALUE}}',
				],
				'condition' => [
					'display' => ['color', 'clist', 'image', 'ilist', 'button'],
				],
				'separator' => 'before',
			]
		);

		$this->add_control(
			'border_color_hover',
			[
				'label' => __( 'Border Hover Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .reyEl-wcAttr-list' => '--sw-border-hover-color: {{VALUE}}',
				],
				'condition' => [
					'display' => ['color', 'clist', 'image', 'ilist', 'button'],
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'typography',
				'selector' => '{{WRAPPER}} .reyEl-wcAttr-list a',
				'separator' => 'before',
				'condition' => [
					'display!' => ['image', 'color'],
				],
			]
		);


		$this->add_responsive_control(
			'items_spacing',
			[
				'label' => esc_html__( 'Items Spacing', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 200,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .reyEl-wcAttr-list' => '--spacing: {{VALUE}}px',
				],
				'separator' => 'before',
			]
		);

		$this->add_control(
			'items_inner_spacing',
			[
				'label' => esc_html__( 'Inner Spacing', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 200,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .reyEl-wcAttr-list' => '--inner-spacing: {{VALUE}}px',
				],
				'condition' => [
					'display' => ['clist', 'ilist'],
				],
			]
		);

		$this->add_control(
			'width',
			[
				'label' => esc_html__( 'Item Width', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 10,
				'max' => 200,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .reyEl-wcAttr-list' => '--item-width: {{VALUE}}px',
				],
				'condition' => [
					'display!' => ['list'],
				],
			]
		);

		$this->add_control(
			'height',
			[
				'label' => esc_html__( 'Item Height', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 10,
				'max' => 200,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .reyEl-wcAttr-list' => '--item-height: {{VALUE}}px',
				],
				'condition' => [
					'display!' => ['list'],
				],
			]
		);

		$this->add_control(
			'padding',
			[
				'label' => esc_html__( 'Item Padding', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 200,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .reyEl-wcAttr-list' => '--item-padding: {{VALUE}}px',
				],
				'condition' => [
					'display' => ['color', 'clist', 'image', 'ilist', 'button'],
				],
			]
		);

		$this->add_control(
			'radius',
			[
				'label' => esc_html__( 'Item Radius', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 200,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .reyEl-wcAttr-list' => '--item-radius: {{VALUE}}px',
				],
				'condition' => [
					'display' => ['color', 'clist', 'image', 'ilist', 'button'],
				],
			]
		);

		$this->add_control(
			'image_fit',
			[
				'label' => esc_html__( 'Image Fit', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'contain',
				'options' => [
					'contain'  => esc_html__( 'Contain', 'rey-core' ),
					'cover'  => esc_html__( 'Cover (stretch)', 'rey-core' ),
				],
				'condition' => [
					'display' => ['image', 'ilist'],
				],
				'selectors' => [
					'{{WRAPPER}} .reyEl-wcAttr-list' => '--img-fit: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'enable_widget_height',
			[
				'label' => esc_html__( 'Custom widget height', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'prefix_class' => '--wCustomHeight-',
				'separator' => 'before',
				'selectors' => [
					'{{WRAPPER}}.--wCustomHeight-yes .reyEl-wcAttr' => 'overflow: auto;',
				],
			]
		);

		$this->add_control(
			'widget_height',
			[
				'label' => esc_html__( 'Widget Height', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 10,
				'max' => 500,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}}.--wCustomHeight-yes .reyEl-wcAttr' => 'height: {{VALUE}}px',
				],
				'condition' => [
					'enable_widget_height!' => '',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function get_attributes(){

		if ( empty($this->_settings['attr_id']) ) {
			return [];
		}

		$query_args['orderby'] = $this->_settings['orderby'];
		$query_args['taxonomy'] = wc_attribute_taxonomy_name($this->_settings['attr_id'] );
		if( ! in_array($query_args['taxonomy'], get_object_taxonomies( 'product' )) ){
			$query_args['taxonomy'] = $this->_settings['attr_id'];
		}
		$query_args['hide_empty'] = $this->_settings['hide_empty'] === 'yes';
		$query_args['hide_empty_invisib'] = $this->_settings['hide_empty_invisib'] !== '';

		if( $this->_settings['attr_list'] === 'limited' && ($attr_limit = $this->_settings['attr_limit']) ){
			$query_args['number'] = $attr_limit;
		}

		if(
			$this->_settings['attr_list'] === 'handpicked'
			&& isset($this->_settings[ 'attr_custom_' . $this->_settings['attr_id'] ])
			&& ($custom_terms = $this->_settings[ 'attr_custom_' . $this->_settings['attr_id'] ])
		){
			return \ReyCore\Helper::get_terms( [
				'term_taxonomy_id'  => $custom_terms,
			] + $query_args );
		}

		if( $this->_settings['attr_list'] === 'handpicked_order' &&
			isset($this->_settings[ 'handpicked_order_attrs' ]) && ($handpicked_ordered_terms = $this->_settings[ 'handpicked_order_attrs' ]) ){
			$handpicked_ordered_terms__clean  = array_filter( wp_list_pluck($handpicked_ordered_terms, 'attr') );
			$query_args['include']  = $handpicked_ordered_terms__clean;
			$query_args['orderby']  = 'include';
			unset($query_args['taxonomy']);
			return get_terms( $query_args );
		}

		if( $this->_settings['attr_list'] === 'dynamic' ){

			global $product;

			if ( ! $product ) {
				return [];
			}

			return wc_get_product_terms( $product->get_id(), $query_args['taxonomy'], $query_args );
		}

		if( $this->_settings['hide_empty_invisib'] !== '' ){
			if( false !== ($invisibles = $this->hide_invisibles( $query_args )) ){
				return $invisibles;
			}
		}

		$custom_results = apply_filters('reycore/elementor/wc-attributes/custom_results', false, $query_args);

		if( false !== $custom_results && is_array($custom_results) ){
			return $custom_results;
		}

		return \ReyCore\Helper::get_terms( $query_args );
	}

	/**
	 * Filter out terms which are not empty & dont contain out of stock items
	 */
	function hide_invisibles( $args ){

		if( ! $args['hide_empty'] ){
			return false;
		}

		$terms = get_terms($args);
		$terms_to_render = [];

		foreach ($terms as $term) {

			// just bail if not products
			if( ! $term->count ){
				continue;
			}

			$product_args = [
				'posts_per_page' => -1,
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'fields'         => 'ids',
				'tax_query'      => [
					'relation'      => 'AND',
					[
						'taxonomy'      => $args['taxonomy'],
						'field'         => 'term_id',
						'terms'         => $term->term_id,
						'operator'      => 'IN'
					],
				]
			];

			// get outofstock products from WC. datastore
			$product_visibility_terms  = wc_get_product_visibility_term_ids();
			$product_visibility_not_in[] = $product_visibility_terms['exclude-from-catalog'];

			// Hide out of stock products.
			if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
				$product_visibility_not_in[] = $product_visibility_terms['outofstock'];
			}

			if ( ! empty( $product_visibility_not_in ) ) {
				$product_args['tax_query'][] = [
					'taxonomy' => 'product_visibility',
					'field'    => 'term_taxonomy_id',
					'terms'    => $product_visibility_not_in,
					'operator' => 'NOT IN',
				];
			}

			$products = get_posts($product_args);

			// if there are results, means the term is not empty
			if( empty($products) ){
				continue;
			}

			$terms_to_render[] = $term;
		}

		// if there are terms to render, just pass them on
		if( ! empty($terms_to_render) ){
			return $terms_to_render;
		}

		return false;
	}

	protected function get_term_tag( $attr, $type = 'color' ) {
		return apply_filters('reycore/elementor/wc-attributes/get_term_tag', '', $attr, $type );
	}

	public function render_link($attr){

		$settings = $this->get_settings_for_display();
		$link = '';

		if( 'archive' === $settings['attr_link'] ){

			// shop or custom URL
			$url = ! empty($settings['url']['url']) ? $settings['url']['url'] : get_permalink( wc_get_page_id( 'shop' ) );

			// fallback URL if terms are not public => act as filters
			$fallback_url = sprintf('%1$s?attro-%2$s=%3$s', $url, $settings['attr_id'], $attr->term_id);

			if( ! ('' === $settings['force_url'] || is_null($settings['force_url'])) ){
				$link = $fallback_url;
			}
			else {
				$link = \ReyCore\Plugin::instance()->woo::get_term_link( $attr->term_id, wc_attribute_taxonomy_name($settings['attr_id']), $fallback_url );
			}

			$link = sprintf('href="%s"', esc_url($link));
		}
		else if( 'filter' === $settings['attr_link'] ){

			$url = '';

			if( is_product_taxonomy() ){
				$term = get_queried_object(); // this will be a WP_Term
				if ( $term && ! is_wp_error( $term ) ) {
					$url = get_term_link( $term );
				}
			}
			else {
				$url = get_permalink( wc_get_page_id( 'shop' ) );
			}

			if( $url ){
				$link = sprintf('href="%1$s%2$sattro-%3$s=%4$s"', $url, strpos($url, '?') !== false ? '&' : '?', $settings['attr_id'], $attr->term_id);
			}
		}

		$html = '';

		switch( $settings['display'] ):

			case"color":
				$tag = $this->get_term_tag( $attr, 'color' );

				$html = sprintf(
						'<a %s class="__item">%s</a>',
						$link,
						$tag
				);
				break;

			case"clist":
				$tag = $this->get_term_tag( $attr, 'color' );

				$html = sprintf(
						'<a %s class="__item">%s</a>',
						$link,
						$tag . esc_html( $attr->name )
				);

				break;

			case"image":
				$tag = $this->get_term_tag( $attr, 'image' );

				$html = sprintf(
						'<a %s class="__item">%s</a>',
						$link,
						$tag
				);
				break;

			case"ilist":
				$tag = $this->get_term_tag( $attr, 'image' );

				$html = sprintf(
						'<a %s class="__item">%s</a>',
						$link,
						$tag . esc_html( $attr->name )
				);

				break;

			default:
				$html = sprintf( '<a %s class="__item">%s</a>', $link, esc_html( $attr->name ) );

		endswitch;

		return apply_filters('reycore/elementor/wc-attributes/render_link', $html, $settings, $attr, $link );
	}

	function get_letters() {

		if( !(isset($this->_settings['layout']) && $this->_settings['layout'] === 'alphabetic') ){
			return false;
		}

		foreach ($this->_attribute_terms as $key => $term) {
			if( isset($term->name) && strlen($term->name) > 0 ){
				$letter = mb_substr($term->name, 0, 1, 'UTF-8');
				$this->letters[$letter][] = $term;
			}
		}

		return true;
	}

	function render_alphabetic_menu(){

		if( empty( $this->letters ) ){
			return;
		}

		if( !(isset($this->_settings['alphabetic_menu']) && $this->_settings['alphabetic_menu'] === 'yes') ){
			return false;
		}

		$alpha_menu_items = [];

		foreach ($this->letters as $letter => $term) {
			$alpha_menu_items[] .= sprintf( '<a href="#letter-%1$s" class="js-scroll-to">%1$s</a>', $letter );
		}

		if( !empty($alpha_menu_items) ){
			printf( '<div class="reyEl-wcAttr-alphMenu">%s</div>', implode('', $alpha_menu_items) );
		}
	}

	public function render_attributes($attribute_terms = []){

		if( empty($attribute_terms) ) {
			return;
		}

		if( is_wp_error($attribute_terms) ) {
			return;
		}

		echo '<ul class="reyEl-wcAttr-list">';

		foreach( $attribute_terms as $key => $attr ) {
			if( isset($attr->term_id) ){
				printf( '<li>%s</li>', $this->render_link( $attr ) );
			}
		}

		if( $this->_settings['attr_list'] !== 'all' ){
			$this->see_all_link();
		}

		echo '</ul>';
	}

	public function see_all_link(){

		if( ! ( $text = $this->_settings['see_all_button'] )){
			return;
		}

		if( ! ( isset( $this->_settings['see_all_button_url']['url'] ) && $link = $this->_settings['see_all_button_url'] )){
			return;
		}

		if ( ! empty( $link['url'] ) ) {

			$this->add_render_attribute( 'see_all_link', 'href', $link['url'] );

			if ( $link['is_external'] ) {
				$this->add_render_attribute( 'see_all_link', 'target', '_blank' );
			}

			if ( $link['nofollow'] ) {
				$this->add_render_attribute( 'see_all_link', 'rel', 'nofollow' );
			}
		}

		printf( '<li><a class="__view-all" %s>%s</a></li>', $this->get_render_attribute_string( 'see_all_link' ), $text );
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

		reycore_assets()->add_styles([$this->get_style_name(), 'rey-wc-tag-attributes']);
		reycore_assets()->add_scripts( $this->rey_get_script_depends() );

		$this->_settings = $this->get_settings_for_display();
		$this->_attribute_terms = $this->get_attributes();

		$this->get_letters();

		$classes = [
			'rey-element',
			'reyEl-wcAttr',
			'reyEl-wcAttr--' . $this->_settings['display'],
			'rey-filterList',
			'rey-filterList--' . $this->_settings['display']
		];

		if( ! empty( $this->letters ) ){
			$classes[] = 'reyEl-wcAttr--alphabeticList';
		}

		$this->add_render_attribute( 'wrapper', 'class', $classes ); ?>

		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>

			<?php
				if( !empty( $this->letters ) ){

					reycore_assets()->add_styles([$this->get_style_name('alphabetic')]);

					$this->render_alphabetic_menu();

					foreach ($this->letters as $letter => $terms) {
						echo '<div class="reyEl-wcAttr-alphaItem">';
						printf('<h4 id="letter-%1$s">%1$s</h4>', $letter);
						$this->render_attributes($terms);
						echo '</div>';
					}
				}
				else {
					$this->render_attributes( $this->_attribute_terms );
				}
			?>
		</div>
		<?php
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
