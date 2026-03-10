<?php
namespace ReyCore\Modules\Cards\Sources;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class CatBase extends Base {

	public function controls_tax( $element ){

		$tax_type = static::get_id();
		$name = static::get_title();

		$element->start_controls_section(
			"section_{$tax_type}_query",
			[
				'label' => $name,
				'condition' => [
					'source' => $tax_type,
				],
			]
		);

			$element->add_control(
				"{$tax_type}_type",
				[
					'label' => esc_html__( 'Query Type', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'all',
					'options' => [
						'all'  => esc_html__( 'All', 'rey-core' ),
						'manual'  => esc_html__( 'Manual selection', 'rey-core' ),
						'top-parents'  => esc_html__( 'All parents', 'rey-core' ),
						'siblings'  => esc_html__( 'Sibling Categories (of current)', 'rey-core' ),
						'subcategories'  => esc_html__( 'Sub-Categories (of current)', 'rey-core' ),
						// 'parents'  => esc_html__( 'Parent & Siblings (of current)', 'rey-core' ),
					],
				]
			);

			if( $tax_type === 'category' ){
				$element->add_control(
					'category_taxonomy',
					[
						'label' => esc_html__( 'Taxonomy', 'rey-core' ),
						'default' => 'category',
						'type' => 'rey-ajax-list',
						'query_args' => [
							'request' => 'get_taxonomies_list',
						],
					]
				);
			}

			$element->add_control(
				"{$tax_type}_limit",
				[
					'label' => __( 'Limit', 'rey-core' ),
					'description' => __( 'Select the number of items to load from query.', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => 6,
					'min' => 1,
					'max' => 100,
					'condition' => [
						"{$tax_type}_type!" => 'manual',
					],
				]
			);

			$element->add_control(
				"{$tax_type}_exclude",
				[
					'label'       => esc_html__( 'Exclude', 'rey-core' ),
					'type'        => \Elementor\Controls_Manager::TEXT,
					'label_block' => true,
					'type' => 'rey-query',
					'multiple' => true,
					'query_args' => [
						'type' => 'terms',
						'taxonomy' => $tax_type,
					],
					'condition' => [
						"{$tax_type}_type!" => 'manual',
					],
				]
			);

			$element->add_control(
				"{$tax_type}_orderby",
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
						"{$tax_type}_type!" => 'manual',
					],
				]
			);

			$element->add_control(
				"{$tax_type}_order",
					[
					'label' => __( 'Order', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'desc',
					'options' => [
						'asc' => __( 'ASC', 'rey-core' ),
						'desc' => __( 'DESC', 'rey-core' ),
					],
					'condition' => [
						"{$tax_type}_type!" => 'manual',
					],
				]
			);

			$__cats = new \Elementor\Repeater();

			$cat_query_args = [
				'type' => 'terms',
				'taxonomy' => $tax_type,
			];

			if( $tax_type === 'category' ){
				$cat_query_args['taxonomy'] = '{category_taxonomy}';
			}

			$__cats->add_control(
				'cat',
				[
					'label' => esc_html__('Category', 'rey-core'),
					'placeholder' => esc_html__('- Select category -', 'rey-core'),
					'type' => 'rey-query',
					'query_args' => $cat_query_args,
					'label_block' => true,
					'default'     => '',
				]
			);

			$element->add_control(
				"{$tax_type}s",
				[
					'label' => __( 'Select Categories', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::REPEATER,
					'fields' => $__cats->get_controls(),
					'default' => [],
					'condition' => [
						"{$tax_type}_type" => 'manual',
					],
					'prevent_empty' => false,
				]
			);

			$element->add_control(
				"{$tax_type}_show_count",
				[
					'label' => esc_html__( 'Show counters', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

		$element->end_controls_section();
	}

	public function query_tax( $element ){

		$tax_type = static::get_id();

		$cat_type = $element->_settings["{$tax_type}_type"];

		$terms_args = [
			'query_source' => "card_element_{$tax_type}",
			'hide_empty' => true,
			'orderby'    => $element->_settings["{$tax_type}_orderby"],
			'order'      => $element->_settings["{$tax_type}_order"],
		];

		if( isset($element->_settings['load_more_enable']) && '' !== $element->_settings['load_more_enable'] ){
			if( $offset = $element->get_offset() ){
				$terms_args['offset'] = $offset;
			}
		}

		if( 'manual' === $cat_type && ( $cats = $element->_settings["{$tax_type}s"] ) ){
			$terms_args['orderby'] = 'include';
			$terms_args['order'] = 'ASC';
			$terms_args['include'] = array_column($cats, 'cat');
		}

		else {

			if( $element->_settings["{$tax_type}_limit"] ){
				$terms_args['number'] = $element->_settings["{$tax_type}_limit"];
			}

			$excludes = [];

			if( 'product_cat' === $tax_type && ($uncategorized = get_option( 'default_product_cat' )) ){
				$excludes = (array) $uncategorized;
			}

			if( $custom_excludes = $element->_settings["{$tax_type}_exclude"] ){
				$excludes = array_merge($excludes, $custom_excludes);
			}

			if( ! empty($excludes) ){
				$terms_args['exclude'] = $excludes;
			}

			if( 'top-parents' === $cat_type ){
				$terms_args['parent'] = 0;
			}

			elseif( 'siblings' === $cat_type ){

				if( is_tax($tax_type) ){
					$current_cat = get_queried_object();
					$terms_args['parent'] = $current_cat->parent;
					$terms_args['exclude'] = $current_cat->term_id;
				}

				// show parents on Shop and Attributes
				elseif ( is_shop() || is_product_taxonomy() ) {
					$terms_args['parent'] = 0;
				}

			}

			elseif( 'subcategories' === $cat_type ){

				if( is_tax($tax_type) ){
					$current_cat = get_queried_object();
					$terms_args['parent'] = $current_cat->term_id;
				}

				// show parents on Shop and Attributes
				elseif ( is_shop() || is_product_taxonomy() ) {
					$terms_args['parent'] = 0;
				}

			}
		}

		// if( is_tax($tax_type) && ( $current_cat = get_queried_object() ) ){
		// 	if( isset($terms_args['exclude']) ){
		// 		$terms_args['exclude'][] = $current_cat->term_id;
		// 	}
		// 	else {
		// 		$terms_args['exclude'] = [$current_cat->term_id];
		// 	}
		// }

		// may be overridden
		$terms_args['taxonomy'] = $tax_type;

		if( isset($element->_settings['category_taxonomy']) && ($category_taxonomy = $element->_settings['category_taxonomy']) ){
			$terms_args['taxonomy'] = $category_taxonomy;
		}

		$terms_args = apply_filters("reycore/elementor/card_element/{$tax_type}_args", $terms_args, $element);

		$terms_args['fields'] = 'ids';

		$get_terms = get_terms( $terms_args );

		if( is_wp_error($get_terms) ){
			return [];
		}

		return $get_terms;
	}
}
