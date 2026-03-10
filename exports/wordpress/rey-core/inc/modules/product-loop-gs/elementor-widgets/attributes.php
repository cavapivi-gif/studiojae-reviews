<?php
namespace ReyCore\Modules\ProductLoopGs\ElementorWidgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Attributes extends WooBase {

	public $attr_id;

	public function get_name() {
		return 'reycore-woo-grid-attributes';
	}

	public function get_title() {
		return __( 'Attributes (Product Grid)', 'rey-core' );
	}

	public function get_icon() {
		return $this->get_icon_class();
	}

	public function show_in_panel() {
		return $this->maybe_show_in_panel();
	}

	/**
	 * Register widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function element_register_controls() {

		$this->start_controls_section(
			'section_title',
			[
				'label' => __( 'Settings', 'rey-core' ),
			]
		);

			$this->add_wrapper_css_class();

			$this->add_control(
				'title',
				[
					'label' => esc_html__( 'Title', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'placeholder' => esc_html__( 'ex: Title:', 'rey-core' ),
				]
			);

			$this->add_control(
				'attributes_id',
				[
					'label'      => esc_html__( 'Attribute', 'rey-core' ),
					'default'    => '',
					'type'       => 'rey-ajax-list',
					'query_args' => [
						'request' => 'get_attributes_list',
					],
				]
			);

			$this->add_control(
				'attributes_query_type',
				[
					'label' => esc_html__( 'Query Type', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'all',
					'options' => [
						'all'  => esc_html__( 'All', 'rey-core' ),
						'manual'  => esc_html__( 'Manual selection', 'rey-core' ),
					],
					'condition' => [
						'attributes_id!' => '',
					],
				]
			);

			$this->add_control(
				'attributes_limit',
				[
					'label' => __( 'Limit', 'rey-core' ),
					'description' => __( 'Select the number of items to load from query.', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 6,
					'min' => 1,
					'max' => 100,
					'condition' => [
						'attributes_id!' => '',
						'attributes_query_type!' => 'manual',
					],
				]
			);

			$this->add_control(
				'attributes_exclude',
				[
					'label'       => esc_html__( 'Exclude', 'rey-core' ),
					'type'        => \Elementor\Controls_Manager::TEXT,
					'label_block' => true,
					'type' => 'rey-query',
					'multiple' => true,
					'query_args' => [
						'type' => 'terms',
						'taxonomy' => '{attributes_id}',
					],
					'condition' => [
						'attributes_id!' => '',
						'attributes_query_type!' => 'manual',
					],
				]
			);

			$this->add_control(
				'attributes_orderby',
				[
					'label' => __( 'Order By', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'term_order',
					'options' => [
						'name' => __( 'Name', 'rey-core' ),
						'term_id' => __( 'Term ID', 'rey-core' ),
						'menu_order' => __( 'Menu Order', 'rey-core' ),
						'count' => __( 'Count', 'rey-core' ),
						'term_order' => __( 'Term Order (Needs Objects IDs)', 'rey-core' ),
					],
					'condition' => [
						'attributes_id!' => '',
						'attributes_query_type!' => 'manual',
					],
				]
			);

			$this->add_control(
				'attributes_order',
					[
					'label' => __( 'Order', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'desc',
					'options' => [
						'asc' => __( 'ASC', 'rey-core' ),
						'desc' => __( 'DESC', 'rey-core' ),
					],
					'condition' => [
						'attributes_id!' => '',
						'attributes_query_type!' => 'manual',
					],
				]
			);

			$manual = new \Elementor\Repeater();

			$manual->add_control(
				'term',
				[
					'label' => esc_html__('Terms', 'rey-core'),
					'placeholder' => esc_html__('- Select term -', 'rey-core'),
					'type' => 'rey-query',
					'query_args' => [
						'type' => 'terms',
						'taxonomy' => '{attributes_id}',
					],
					'label_block' => true,
					'default'     => '',
				]
			);

			$this->add_control(
				'attributes_manual',
				[
					'label' => __( 'Select Attribute Terms', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::REPEATER,
					'fields' => $manual->get_controls(),
					'default' => [],
					'condition' => [
						'attributes_id!' => '',
						'attributes_query_type' => 'manual',
					],
					'prevent_empty' => false,
				]
			);

			$this->add_control(
				'attribute_show_count',
				[
					'label' => esc_html__( 'Show counters', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'condition' => [
						'attributes_id!' => '',
					],
				]
			);

			$this->add_control(
				'attributes_thumb_key',
				[
					'label' => esc_html__( 'Thumbnail ACF Key', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'placeholder' => esc_html__( 'eg: rey_brand_image', 'rey-core' ),
				]
			);

			$this->add_control(
				'hide_text',
				[
					'label' => esc_html__( 'Hide text if Image exists', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

			$this->add_control(
				'attributes_separator',
				[
					'label' => esc_html__( 'Attributes Separator', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => ', ',
					'placeholder' => esc_html__( 'eg: ,', 'rey-core' ),
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_styles',
			[
				'label' => __( 'Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'show_label' => false,
			]
		);

			$selectors['main'] = '{{WRAPPER}} .elementor-widget-container';
			$selectors['title'] = '{{WRAPPER}} .__attrs-title';
			$selectors['items'] = '{{WRAPPER}} .__attrs-items';
			$selectors['item'] = '{{WRAPPER}} .__attrs-item';

			$this->add_control(
				'color',
				[
					'label' => esc_html__( 'Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selectors['main'] => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'typo',
					'selector' => $selectors['main'],
				]
			);

			$this->add_responsive_control(
				'alignment',
				[
					'label' => __( 'Alignment', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::CHOOSE,
					'options' => [
						'left'           => [
							'title'         => __( 'Left', 'rey-core' ),
							'icon'          => 'eicon-text-align-left',
						],
						'center'        => [
							'title'         => __( 'Center', 'rey-core' ),
							'icon'          => 'eicon-text-align-center',
						],
						'right'          => [
							'title'         => __( 'Right', 'rey-core' ),
							'icon'          => 'eicon-text-align-right',
						],
					],
					'default' => '',
					'selectors' => [
						$selectors['main'] => 'text-align: {{VALUE}};',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Image_Size::get_type(),
				[
					'name' => 'image', // Usage: `{name}_size` and `{name}_custom_dimension`, in this case `image_size` and `image_custom_dimension`.
					'default' => 'thumbnail',
					'condition' => [
						'attributes_thumb_key!' => '',
					],
					'exclude' => ['custom'],
				]
			);

			$this->add_control(
				'image_width',
				[
					'label' => esc_html__( 'Image width', 'rey-core' ) . ' [px]',
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 0,
					'selectors' => [
						$selectors['main'] => '--img-size: {{VALUE}}px;',
					],
				]
			);

		$this->end_controls_section();

	}

	public function render_template() {

		if( ! ($terms = $this->get_terms()) ){
			return;
		}

		$output = [];

		foreach ($terms as $term_id) {

			$args = $this->parse_item($term_id);

			$tag = 'span';
			$attrs['class'] = '__attrs-item';

			if( ! empty($args['button_url']) ){
				$tag = 'a';
				$attrs['href'] = $args['button_url'];
			}

			$thumb = '';
			if( isset($args['image']) && ! empty($args['image']['id']) ){
				$thumb = wp_get_attachment_image($args['image']['id'], $this->_settings['image_size'], false, ['class' => '__img']);
			}

			$item_title = sprintf('<span>%s</span>', $args['title']);

			if( $this->_settings['hide_text'] && $thumb ){
				$item_title = '';
			}

			$output[] = sprintf('<%1$s %2$s >%4$s<span>%3$s</span></%1$s>', $tag, reycore__implode_html_attributes($attrs), $item_title, $thumb);
		}

		if( $title = $this->_settings['title'] ){
			printf('<div class="__attrs-title">%s</div>', $title);
		}

		$separator = ($sep = $this->_settings['attributes_separator']) ? sprintf('<span class="__sep">%s</span>', $sep) : '';

		echo implode($separator, $output);

	}

	public function get_terms() {

		$settings = $this->_settings;

		if( ! (isset($settings['attributes_id']) && ($this->attr_id = $settings['attributes_id'])) ){
			return [];
		}

		$terms_args = [
			'hide_empty' => true,
			'orderby'    => $settings['attributes_orderby'],
			'order'      => $settings['attributes_order'],
		];

		if( 'manual' === $settings['attributes_query_type'] && ( $attributes_manual = $settings['attributes_manual'] ) ){
			$terms_args['orderby'] = 'include';
			$terms_args['order'] = 'ASC';
			$terms_args['include'] = array_column($attributes_manual, 'term');
		}

		else {

			if( $settings['attributes_limit'] ){
				$terms_args['number'] = $settings['attributes_limit'];
			}

			if( $excludes = $settings['attributes_exclude'] ){
				$terms_args['exclude'] = $excludes;
			}

		}

		// may be overridden
		$terms_args['taxonomy'] = $this->attr_id;
		$terms_args = array_merge(
			apply_filters("reycore/elementor/product_loop/attributes/{$this->attr_id}_args", $terms_args, $this),
			['fields' => 'ids']
		);

		$get_terms = wp_get_post_terms($this->_product->get_id(), $this->attr_id, $terms_args);

		if( is_wp_error($get_terms) ){
			return [];
		}

		return $get_terms;

	}

	public function parse_item($item){

		if( ! (($term = get_term( $item )) && isset($term->name)) ){
			return [];
		}

		$args = [];

		if( $this->_settings['attributes_thumb_key'] && ($thumbnail_id = get_term_meta( $item, $this->_settings['attributes_thumb_key'], true )) ){
			$args['image']['id'] = $thumbnail_id;
		}

		if( ($link = get_term_link($item, $this->attr_id)) && ! is_wp_error($link) ){
			$args['button_url'] = $link;
		}

		$args['title'] = $term->name;
		$args['subtitle'] = $term->description;

		if( '' !== $this->_settings['attribute_show_count'] ){
			$args['title'] .= sprintf(' <sup>%d</sup>', $term->count);
		}

		return $args;
	}
}
