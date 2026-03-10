<?php
namespace ReyCore\Modules\Cards\Sources;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Posts extends Base {

	public function get_id(){
		return 'posts';
	}

	public function get_title(){
		return esc_html__( 'Posts', 'rey-core' );
	}

	public function controls($element){

		$element->start_controls_section(
			'section_post_query',
			[
				'label' => __( 'Posts query', 'rey-core' ),
				'condition' => [
					'source' => 'posts',
				],
			]
		);

		$element->add_control(
			'posts_per_page',
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
			'post_type',
			[
				'label' => esc_html__( 'Post Type', 'rey-core' ),
				'default' => 'post',
				'type' => 'rey-ajax-list',
				'query_args' => [
					'request' => 'get_post_types_list',
				],
			]
		);

		$element->add_control(
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
				'condition' => [
					'post_type!' => 'page'
				],
			]
		);

		$element->add_control(
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

		// Advanced settings
		$element->add_control(
			'include',
			[
				'label'       => esc_html__( 'Manual include', 'rey-core' ),
				'description' => __( 'Add posts IDs separated by comma.', 'rey-core' ),
				'label_block' => true,
				'type' => 'rey-query',
				'multiple' => true,
				'query_args' => [
					'type' => 'posts',
					'post_type' => '{post_type}',
				],
				'condition' => [
					'query_type' => 'manual-selection',
				],
			]
		);

		$element->add_control(
			'exclude',
			[
				'label'       => esc_html__( 'Exclude', 'rey-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'type' => 'rey-query',
				'multiple' => true,
				'query_args' => [
					'type' => 'posts',
					'post_type' => '{post_type}',
				],
				'condition' => [
					'query_type!' => 'manual-selection',
				],
			]
		);

		/* wip
		$element->add_control(
			'posts_meta_query',
			[
				'label' => esc_html__( 'Use Meta Query', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		); */

		$element->add_control(
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
				// 'condition' => [
				// 	'query_type!' => 'manual-selection',
				// ],
			]
		);

		$element->add_control(
			'order',
				[
				'label' => __( 'Order', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'desc',
				'options' => [
					'asc' => __( 'ASC', 'rey-core' ),
					'desc' => __( 'DESC', 'rey-core' ),
				],
				// 'condition' => [
				// 	'query_type!' => 'manual-selection',
				// ],
			]
		);

		$element->add_control(
			'exclude_duplicates',
			[
				'label' => __( 'Exclude Duplicates', 'rey-core' ),
				'description' => __( 'Exclude duplicates that were already loaded in this page', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$element->add_control(
			'exclude_without_image',
			[
				'label' => __( 'Exclude posts without image', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		);

		$element->add_control(
			'posts_map_label',
			[
				'label' => esc_html__( 'Use "Label" as', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => esc_html__( '- None -', 'rey-core' ),
					'date'  => esc_html__( 'Date', 'rey-core' ),
					'category'  => esc_html__( 'Category', 'rey-core' ),
				],
			]
		);

		$element->add_control(
			'query_id',
			[
				'label' => esc_html__( 'Custom Query ID', 'rey-core' ),
				'description' => esc_html__( 'Give your Query a custom unique id to allow server side modifications.', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => esc_html__( 'eg: my_custom_action', 'rey-core' ),
				'separator' => 'before',
			]
		);

		$element->end_controls_section();

		/* wip

		$element->start_controls_section(
			'section_meta_query',
			[
				'label' => __( 'Meta Query', 'rey-core' ),
				'condition' => [
					'posts_meta_query! ' => '',
				],
			]
		);

			$meta_co = new \Elementor\Repeater();

			$element->add_control(
				'meta_key',
				[
					'label' => esc_html__( 'Meta Key', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
				]
			);

			$element->add_control(
				'meta_value',
				[
					'label' => esc_html__( 'Meta Value', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
				]
			);

			$element->add_control(
				'meta_compare',
				[
					'label' => esc_html__( 'Compare operator', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '=',
					'options' => [
						'true'  => 'True',
						'false'  => 'False',
						'null'  => 'Is Null',
						'not_null'  => 'Not Null',
						'==' => esc_html__('Is equal to', 'rey-core'),
						'!=' => esc_html__('Is not equal to', 'rey-core'),
						'>' => esc_html__('Is greater than', 'rey-core'),
						'<' => esc_html__('Is less than', 'rey-core'),
						'!=empty' => esc_html__('Is not empty', 'rey-core'),
						'==empty' => esc_html__('Is empty', 'rey-core'),
					],
				]
			);

			$element->add_control(
				'meta_conditions',
				[
					'label' => __( 'Meta Conditions', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::REPEATER,
					'fields' => $meta_co->get_controls(),
					'default' => [],
					'title_field' => '{{{ meta_key }}}',
				]
			);

		$element->end_controls_section();

		*/
	}

	public function query($element){

		$query_args = [
			'posts_per_page' => $element->_settings['posts_per_page'] ? $element->_settings['posts_per_page'] : get_option('posts_per_page'),
			'post_type' => $element->_settings['post_type'],
			'post_status' => 'publish',
			'ignore_sticky_posts' => true,
			'update_post_term_cache' => false, //useful when taxonomy terms will not be utilized
			'orderby' => isset($element->_settings['orderby']) ? $element->_settings['orderby'] : 'date',
			'order' => isset($element->_settings['order']) ? $element->_settings['order'] : 'DESC',
			'fields' => 'ids',
		];

		if( isset($element->_settings['load_more_enable']) && '' !== $element->_settings['load_more_enable'] ){
			if( $offset = $element->get_offset() ){
				$query_args['offset'] = $offset;
			}
		}

		if( $element->_settings['query_type'] == 'current-query' ){
			$current_query_args = array_filter($GLOBALS['wp_query']->query_vars);
			$query_args = array_merge($current_query_args, $query_args);
		}
		else if( $element->_settings['query_type'] == 'manual-selection' && !empty($element->_settings['include']) ) {
			$query_args['post__in'] = array_map( 'absint', $element->_settings['include'] );
			// $query_args['orderby'] = 'post__in';
		}
		else {

			if(
				// 'post' !== $element->_settings['post_type'] &&
				isset($element->_settings['all_taxonomies']) &&
				$all_taxonomies = $element->_settings['all_taxonomies']
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

			if( ! empty($element->_settings['exclude']) ) {
				$query_args['post__not_in'] = array_map( 'absint', $element->_settings['exclude'] );
			}

		}

		// Exclude duplicates
		if( $element->_settings['exclude_duplicates'] !== '' ){
			if(
				isset($GLOBALS["rey_exclude_posts"])
				&& ($to_exclude = $GLOBALS["rey_exclude_posts"]) ) {
				$query_args['post__not_in'] = isset($query_args['post__not_in']) ? array_merge( $query_args['post__not_in'], $to_exclude ) : $to_exclude;
			}
		}

		// get_the_ID() sometimes returns odd results
		$current_id = get_the_ID();

		if ( is_singular() ) {
			$current_id = get_queried_object_id();
		}

		$current_id = apply_filters( 'reycore/elementor/card/exclude_current_id', $current_id, $element, $this);

		if( isset($query_args['post__not_in']) ){
			$query_args['post__not_in'][] = $current_id;
		}
		else {
			$query_args['post__not_in'] = [$current_id];
		}

		if( $element->_settings['exclude_without_image'] !== '' ){
			$query_args['meta_query'] = [
				[
					'key' => '_thumbnail_id'
				]
			];
		}

		// Deprecated
		$query_args = apply_filters_deprecated( 'reycore/elementor/carousel/query_args', [$query_args, $element], '2.4.4', 'reycore/elementor/card/query_args' );

		$query_args = apply_filters( 'reycore/elementor/card/query_args', $query_args, $element );

		if ( isset($element->_settings['query_id']) && !empty($element->_settings['query_id']) ) {
			add_action( 'pre_get_posts', [ $element, 'pre_get_posts_query_filter' ] );
		}

		$query = \ReyCore\Helper::get_query( $query_args );

		remove_action( 'pre_get_posts', [ $element, 'pre_get_posts_query_filter' ] );

		do_action( 'reycore/elementor/query/query_results', $query, $element );

		$post_ids = $query->get_posts();

		// create the global exclusion array
		$GLOBALS["rey_exclude_posts"] = isset($GLOBALS["rey_exclude_posts"]) ? array_merge($GLOBALS["rey_exclude_posts"], $post_ids) : $post_ids;

		if( isset($element->_settings['load_more_enable']) && '' !== $element->_settings['load_more_enable'] ){
			$element::$more_total = count($post_ids);
		}

		return $post_ids;

	}

	public function parse_item($element){

		if( ! (isset($element->_items[$element->item_key]) && ($item = $element->_items[$element->item_key])) ){
			return [];
		}

		$args = [
			'image'        => [],
			'_id'          => 'posts-' . $item,
			'post_id'      => $item,
			// 'item_classes' => get_post_class('', $item),
			'item_classes' => [
				'post-' . $item,
				'type-' . esc_attr($element->_settings['post_type'])
			],
		];

		if( in_array($element->_settings[$this->base::CARD_KEY], array_keys($this->base->get_cards_list()), true) ):

			if( 'no' !== $element->_settings['image_show'] ){
				$args['image'] = [
					'id' => get_post_thumbnail_id($item),
				];
			}

			$args['button_url'] = [
				'url' => get_permalink($item)
			];
			$args['button_text'] = $element->_settings['button_text'];
			$args['captions'] = 'yes';

			$args['title'] = get_the_title($item);
			$args['subtitle'] = get_the_excerpt( $item );

			if( $map_label = $element->_settings['posts_map_label'] ){
				if( 'date' === $map_label ){
					$args['label'] = get_the_date( '', $item );
				}
				else if( 'category' === $map_label ){
					$post_cats = array_column(get_the_category( $item ), 'name');
					$args['label'] = implode(', ', $post_cats);
				}
			}

		endif;

		return $args;
	}

	public function load_more_button_per_page($element){
		return $element->_settings['posts_per_page'] ? $element->_settings['posts_per_page'] : get_option('posts_per_page');
	}
}
