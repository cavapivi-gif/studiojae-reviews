<?php
namespace ReyCore\Modules\Cards\Sources;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Attributes {

	public function get_id(){
		return 'attributes';
	}

	public function get_title(){
		return esc_html__( 'Product Attributes', 'rey-core' );
	}

	public function controls($element){

		$element->start_controls_section(
			'section_attributes_query',
			[
				'label' => sprintf( '%s Settings', $this->get_title() ),
				'condition' => [
					'source' => $this->get_id(),
				],
			]
		);

			$element->add_control(
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

			$element->add_control(
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

			$element->add_control(
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

			$element->add_control(
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

			$element->add_control(
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

			$element->add_control(
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

			$element->add_control(
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

			$element->add_control(
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

			$element->add_control(
				'attributes_thumb_key',
				[
					'label' => esc_html__( 'Thumbnail Meta Key', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'placeholder' => esc_html__( 'eg: custom_field_key', 'rey-core' ),
				]
			);

		$element->end_controls_section();

	}

	public function query($element){

		$settings = $element->_settings;

		if( ! (isset($settings['attributes_id']) && ($attr_id = $settings['attributes_id'])) ){
			return [];
		}

		$terms_args = [
			'hide_empty' => true,
			'orderby'    => $settings['attributes_orderby'],
			'order'      => $settings['attributes_order'],
		];

		if( isset($settings['load_more_enable']) && '' !== $settings['load_more_enable'] ){
			if( $offset = $element->get_offset() ){
				$terms_args['offset'] = $offset;
			}
		}

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
		$terms_args['taxonomy'] = $attr_id;

		$terms_args = apply_filters("reycore/elementor/card_element/{$attr_id}_args", $terms_args, $element);

		$terms_args['fields'] = 'ids';

		$get_terms = get_terms( $terms_args );

		if( is_wp_error($get_terms) ){
			return [];
		}

		return $get_terms;
	}

	public function load_more_button_per_page($element){
		return isset($element->_settings['attributes_limit']) && ($limit = $element->_settings['attributes_limit']) ? $limit : false;
	}

	public function parse_item($element){

		if( ! (isset($element->_items[$element->item_key]) && ($item = $element->_items[$element->item_key])) ){
			return [];
		}

		if( ! (isset($element->_settings['attributes_id']) && ($taxonomy = $element->_settings['attributes_id'])) ){
			return [];
		}

		if( ! (($term = get_term( $item )) && isset($term->name)) ){
			return [];
		}

		$args = [
			'captions'    => 'yes',
			'_id'         => 'prod-attr-' . $item,
			'term' => $term,
		];

		$default_thumb_key = (isset($element->_settings['attributes_thumb_key']) && ($th_key = $element->_settings['attributes_thumb_key'])) ? $th_key : 'thumbnail_id';

		static $thumb_key;

		if( is_null($thumb_key) ){
			$thumb_key = apply_filters('reycore/cards/attribute_source/thumb_key', $default_thumb_key, $taxonomy);
		}

		if( $thumbnail_id = get_term_meta( $item, $thumb_key, true ) ){
			$args['image']['id'] = $thumbnail_id;
		}

		if( ($link = get_term_link($item, $taxonomy)) && ! is_wp_error($link) ){
			$args['button_url']['url'] = $link;
			$args['button_text'] = $element->_settings['button_text'];
		}

		$args['title'] = $term->name;
		$args['subtitle'] = $term->description;

		if( '' !== $element->_settings['attribute_show_count'] ){
			$args['title'] .= sprintf(' <sup>%d</sup>', $term->count);
		}

		return $args;
	}

}
