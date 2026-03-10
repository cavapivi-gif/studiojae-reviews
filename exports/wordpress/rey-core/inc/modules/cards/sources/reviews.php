<?php
namespace ReyCore\Modules\Cards\Sources;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Reviews extends Base {

	public function get_id(){
		return 'reviews';
	}

	public function get_title(){
		return esc_html__( 'Product Reviews', 'rey-core' );
	}

	public function controls($element){

		$element->start_controls_section(
			'section_product_reviews',
			[
				'label' => __( 'Product Reviews Settings', 'rey-core' ),
				'condition' => [
					'source' => 'reviews',
				],
			]
		);

			$element->add_control(
				'reviews_type',
				[
					'label' => esc_html__( 'Query Type', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'latest',
					'options' => [
						'latest'  => esc_html__( 'Latest reviews', 'rey-core' ),
						'product'  => esc_html__( 'Specific Products reviews', 'rey-core' ),
					],
				]
			);

			$element->add_control(
				'reviews_limit',
				[
					'label' => __( 'Limit', 'rey-core' ),
					'description' => __( 'Select the number of items to load from query.', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 8,
					'min' => 1,
					'max' => 100,
				]
			);

			$element->add_control(
				'reviews_min',
				[
					'label' => esc_html__( 'Minimum rating', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 4,
					'options' => [
						'5'  => esc_html__( '5 Stars', 'rey-core' ),
						'4'  => esc_html__( '4 Stars', 'rey-core' ),
						'3'  => esc_html__( '3 Stars', 'rey-core' ),
						'2'  => esc_html__( '2 Stars', 'rey-core' ),
						'1'  => esc_html__( '1 Stars', 'rey-core' ),
					],
				]
			);

			$element->add_control(
				'reviews_products',
				[
					'label'       => __( 'Select Products', 'rey-core' ),
					'type'        => 'rey-query',
					'default'     => '',
					'label_block' => true,
					'multiple'    => true,
					'placeholder' => esc_html__('- Select -', 'rey-core'),
					'query_args'  => [
						'type'      => 'posts',
						'post_type' => 'product',
					],
					'condition' => [
						'reviews_type' => 'product',
					],
				]
			);

			$element->add_control(
				'reviews_color',
				[
					'label' => esc_html__( 'Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}}' => '--star-rating-color: {{VALUE}}',
					],
				]
			);

			$element->add_control(
				'reviews_size',
				[
					'label' => esc_html__( 'Size', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}}' => '--star-rating-size: {{VALUE}}px',
					],
				]
			);

			$element->add_control(
				'reviews_spacing',
				[
					'label' => esc_html__( 'Spacing', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .star-rating' => '--star-rating-spacing: {{VALUE}}px',
					],
				]
			);

		$element->end_controls_section();

	}

	public function query($element){

		$args = [
			'status'  => 'approve',
			'type'    => 'review',
			'parent'  => 0,
			'number'  => $element->_settings['reviews_limit'],
			// 'fields'  => 'ids',
			// 'orderby' => '',
			// 'order'   => 'DESC',
		];

		if( isset($element->_settings['load_more_enable']) && '' !== $element->_settings['load_more_enable'] ){
			if( $offset = $element->get_offset() ){
				$args['offset'] = $offset;
			}
		}

		if( 'product' === $element->_settings['reviews_type'] && ($product_ids = $element->_settings['reviews_products']) ){

			// $args['post_id'] = $product_id;
			$args['post__in'] = array_map('absint', $product_ids);
		}

		if( $min_rating = $element->_settings['reviews_min'] ){
			$args['meta_query']['relation'] = 'AND';
			$args['meta_query'][] = [
				'key'     => 'rating',
				'type'    => 'NUMERIC',
				'compare' => '>=',
				'value'   => absint($min_rating),
			];
		}

		$comments_query = new \WP_Comment_Query;
		$comments = $comments_query->query( $args );

		return $comments;
	}

	public function parse_item($element){

		if( ! (isset($element->_items[$element->item_key]) && ($item = $element->_items[$element->item_key])) ){
			return [];
		}

		$args = [
			'image'        => [],
			'_id'          => 'posts-' . $item->comment_ID,
			'post_id'      => $item->comment_ID,
			'item_classes' => [
				'post-' . $item->comment_ID,
				'review',
			],
			'button_url'   => [],
			'button_text'  => '',
			'button_show'  => 'no',
			'captions'     => 'yes',
			'title'        => $item->comment_author,
			'subtitle'     => $item->comment_content,
		];

		$element->_settings['button_show'] = 'no';

		if( $rating = intval( get_comment_meta( $item->comment_ID, 'rating', true ) ) ){
			$args['before_title'] = sprintf('<div class="star-rtng-wrapper">%s</div>', wc_get_rating_html( $rating ));
		}

		return $args;
	}

	public function load_more_button_per_page($element){
		return isset($element->_settings['reviews_limit']) && ($limit = $element->_settings['reviews_limit']) ? $limit : false;
	}
}
