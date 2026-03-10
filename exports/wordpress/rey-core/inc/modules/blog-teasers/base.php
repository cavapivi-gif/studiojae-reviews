<?php
namespace ReyCore\Modules\BlogTeasers;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	public function __construct()
	{

		parent::__construct();

		add_action('wp', [$this, 'init']);
		add_action( 'reycore/customizer/control=blog_post_sidebar', [ $this, 'add_customizer_options' ], 10, 2 );

	}

	public function init(){

		if( is_admin() ){
			return;
		}

		if( ! is_singular('post') ){
			return;
		}

		if( ! $this->is_enabled() ){
			return;
		}

		$this->settings = apply_filters('reycore/module/blog-teasers/settings', [
			'min_limit_of_blocks' => 6,
			'title_tag' => 'h4'
		]);

		add_filter('the_content', [$this, 'the_content']);

	}

	public function get_related_posts( $args = [] ){

		$args['content'] = [
			'blockName' => 'acf/reycore-posts-v1',
			'attrs' => [
				'id' => uniqid('block_'),
				'name' => 'acf/reycore-posts-v1',
				'data' => [
					'columns'            => 1,
					'limit'              => isset($args['related_limit']) && !empty($args['related_limit']) ? $args['related_limit'] : 3,
					'gap_size'           => 30,
					'vertical_separator' => true,
					'numerotation'       => 'roman',
					'query_type'         => 'related',
					'order_by'           => 'date',
					'order_direction'    => 'asc',
					'show_image'         => false,
					'show_date'          => false,
					'show_categories'    => false,
					'show_excerpt'       => false,
					'title_size'         => 'xs',
				],
				'align' => '',
				'mode' => 'preview',
			],
		];

		$output = render_block( $this->get_container_data($args) );

		// check if it's empty
		if( strpos($output, '__no-posts') !== false ){
			return;
		}

		return $output;
	}

	public function get_single_post( $args = [] ){

		$args['content'] = [
			'blockName' => 'acf/reycore-posts-v1',
			'attrs' => [
				'id' => uniqid('block_'),
				'name' => 'acf/reycore-posts-v1',
				'data' => wp_parse_args($args, [
					'columns'            => 1,
					'limit'              => 1,
					'gap_size'           => 30,
					'vertical_separator' => false,
					'numerotation'       => false,
					'query_type'         => 'manual',
					'order_by'           => 'date',
					'order_direction'    => 'asc',
					'image_alignment'    => 'top',
					'show_image'         => true,
					'show_date'          => false,
					'show_categories'    => true,
					'show_excerpt'       => false,
					'title_size'         => 'xs',
				]),
				'align' => '',
				'mode' => 'preview',
			],
		];

		$output = render_block( $this->get_container_data($args) );

		// check if it's empty
		if( strpos($output, '__no-posts') !== false ){
			return;
		}

		return $output;

	}

	public function get_global_section( $args = [] ){

		if( ! (isset($args['global_section']) && $gs = $args['global_section']) ){
			return;
		}

		$args['content'] = [
			'blockName' => 'acf/reycore-elementor-global-sections',
			'attrs' => [
				'id' => uniqid('block_'),
				'name' => 'acf/reycore-elementor-global-sections',
				'data' => [
					'global_section' => $gs,
				],
				'align' => '',
				'mode' => 'preview',
			],
		];

		$output = render_block( $this->get_container_data($args) );

		return $output;

	}

	public function get_container_data( $args = [] ){

		$args = wp_parse_args($args, [
			'heading'      => '',
			'heading_tag'  => $this->settings['title_tag'],
			'offset_align' => 'semi',
			'align'        => 'right',
			'width'        => 325,
			'content'      => '',
		]);

		if( ! $args['content'] ){
			return;
		}

		$offset = $style = '';

		if( in_array($args['align'], ['left', 'right'], true) ){
			$offset = "--offsetAlign-" . $args['offset_align'];
			if( $max_width = $args['width'] ){
				$style .= "--max-width:{$max_width}px;";
			}
			$style .= "width:100%";
		}

		if( 'center' === $args['align'] ){
			$style .= "max-width:100%";
		}

		$data = [
			'blockName' => 'reycore/container-v1',
			'innerBlocks' => [],
			'innerContent' => [
				"<div class='wp-block-reycore-container-v1 align{$args['align']} reyBlock-container-v1 {$offset}' data-align='{$args['align']}' style='{$style}'><div class='reyBlock-containerInner'>",
				NULL,
			],
		];

		if( $heading = $args['heading'] ) {

			$heading_html = sprintf('<%1$s>%2$s</%1$s>', $args['heading_tag'], $heading);
			$data['innerBlocks'][] = [
				'blockName' => 'core/heading',
				'innerContent' => [ $heading_html ],
			];
			// placeholder for inner content
			$data['innerContent'][] = null;

		}

		$data['innerBlocks'][] = $args['content'];
		$data['innerContent'][] = '</div></div>';

		return $data;
	}

	public function add_inner_content( $args = [] ){

		$args = wp_parse_args($args, [
			'data' => [],
			'has_position_checks' => true,
		]);

		$output = '';

		global $post;

		if( ! (isset($post->post_content) && ($post_content = $post->post_content)) ){
			return $output;
		}

		if( ! ($teasers = $args['data']) ){
			return $output;
		}

		$blocks = parse_blocks( $post_content );

		if( count( array_filter(wp_list_pluck($blocks, 'blockName')) ) < $this->settings['min_limit_of_blocks'] ){
			return $output;
		}

		$_skip = $_rendered = [];

		foreach ($blocks as $block_key => $block) {

			$rendered_block = render_block($block);

			foreach ([
				't' => 0.25,
				'm' => 0.5,
				'b' => 0.75
			] as $pos => $percent) {

				if( ! (isset($teasers[$pos]) && $teasers_options = $teasers[$pos]) ){
					continue;
				}

				foreach ($teasers_options as $key => $t_options) {

					if( isset($_rendered[$pos][$key]) && $_rendered[$pos][$key] ){
						continue;
					}

					if( ( (isset($_skip[$pos][$key]) && $_skip[$pos][$key]) || ( $block_key === absint( count($blocks) * $percent ) ) ) ){

						if( $args['has_position_checks'] ){

							// if it's full or wide, just skip, nothing can be done about them
							if( $t_options['align'] !== 'center' && isset($block['attrs']['align']) && in_array($block['attrs']['align'], ['wide', 'full'], true) ){
								$_skip[$pos][$key] = true;
								continue;
							}

						}

						// if it's something else, not a paragraph, but aligned semi (bc full is ok), just set it full
						if( ! is_null($block['blockName'] ) && $block['blockName'] !== 'core/paragraph' && $t_options['offset_align'] === 'semi' ){
							$t_options['offset_align'] = 'full';
						}

						if( $teaser_content = $this->get_content($t_options) ){
							$output .= $teaser_content;
						}

						$_rendered[$pos][$key] = true;
					}
				}
			}

			$output .= $rendered_block;
		}

		return $output;
	}

	public function get_content( $option ){

		if( 'single' === $option['type'] ){
			return $this->get_single_post( $option );
		}

		else if( 'related' === $option['type'] ){
			return $this->get_related_posts( $option );
		}

		else if( 'global_section' === $option['type'] ){
			return $this->get_global_section( $option );
		}

	}

	public function the_content($content){

		if( get_queried_object_id() !== get_the_ID() ){
			return $content;
		}

		$blog_teasers = self::option();

		if( empty($blog_teasers) ){
			return $content;
		}

		$top_content = $bottom_content = '';

		$__inner_content = [
			't' => [],
			'm' => [],
			'b' => [],
		];

		foreach ($blog_teasers as $option) {

			if( '0%' === $option['position'] ){
				$top_content .= $this->get_content($option);
			}
			elseif( '25%' === $option['position'] ){
				$__inner_content['t'][] = $option;
			}
			elseif( '50%' === $option['position'] ){
				$__inner_content['m'][] = $option;
			}
			elseif( '75%' === $option['position'] ){
				$__inner_content['b'][] = $option;
			}
			elseif( '100%' === $option['position'] ){
				$bottom_content .= $this->get_content($option);
			}

		}

		if( !empty($__inner_content['t']) || !empty($__inner_content['m']) || !empty($__inner_content['b']) ){

			$inner_content = $this->add_inner_content([
				'data'                => $__inner_content,
				'has_position_checks' => true,
			]);

			if( $inner_content ){
				$content = $inner_content;
			}

		}

		return $top_content . $content . $bottom_content;

	}

	public function add_customizer_options($control_args, $section){

		$section->add_control( [
			'type'        => 'repeater',
			'settings'    => 'blog_teasers',
			'label'       => esc_html__('Blog Teasers', 'rey-core'),
			'description' => __('Assign blocks into posts.', 'rey-core'),
			'row_label' => [
				'value' => esc_html__('Blog teaser', 'rey-core'),
				'type'  => 'field',
				'field' => 'type',
			],
			'button_label' => esc_html__('New teaser', 'rey-core'),
			'default'      => [],
			'fields'       => [

				'position' => [
					'type'        => 'select',
					'label'       => esc_html__('Position in page', 'rey-core'),
					'choices'     => [
						'' => esc_html__('- Select -', 'rey-core'),
						'0%' => esc_html__('Top', 'rey-core'),
						'25%' => esc_html__('In content - 25%', 'rey-core'),
						'50%' => esc_html__('In content - 50%', 'rey-core'),
						'75%' => esc_html__('In content - 75%', 'rey-core'),
						'100%' => esc_html__('Bottom', 'rey-core'),
					],
				],

				'align' => [
					'type'        => 'select',
					'label'       => esc_html__('Align', 'rey-core'),
					'choices'     => [
						'' => esc_html__('- Select -', 'rey-core'),
						'left' => esc_html__('Left', 'rey-core'),
						'center' => esc_html__('Center', 'rey-core'),
						'right' => esc_html__('Right', 'rey-core'),
					],
				],

				'offset_align' => [
					'type'        => 'select',
					'label'       => esc_html__('Offset Align', 'rey-core'),
					'choices'     => [
						'' => esc_html__('- Select -', 'rey-core'),
						'semi' => esc_html__('Semi-offset', 'rey-core'),
						'full' => esc_html__('Full-offset', 'rey-core'),
					],
					'condition' => [
						[
							'setting'  => 'align',
							'operator' => '!=',
							'value'    => 'center',
						],
					],
				],

				'heading' => [
					'type'        => 'text',
					'label'       => esc_html__('Heading text', 'rey-core'),
				],

				'width' => [
					'type'        => 'number',
					'label'       => esc_html__('Block Width', 'rey-core'),
					'condition' => [
						[
							'setting'  => 'align',
							'operator' => '!=',
							'value'    => 'center',
						],
					],
				],

				'type' => [
					'type'        => 'select',
					'label'       => esc_html__('Block Type', 'rey-core'),
					'choices'     => [
						'' => esc_html__('- Select -', 'rey-core'),
						'related' => esc_html__('Related posts', 'rey-core'),
						'single' => esc_html__('Single post', 'rey-core'),
						'global_section' => esc_html__('Global section', 'rey-core'),
					],
				],

				'global_section' => [
					'type'        => 'select',
					'label'       => esc_html__('Select Global Section', 'rey-core'),
					'choices'     => \ReyCore\Customizer\Helper::global_sections('generic', ['' => '- Select -']),
					'export' => 'post_id',
					'condition' => [
						[
							'setting'  => 'type',
							'operator' => '==',
							'value'    => 'global_section',
						],
					],
				],

				'manual_posts' => [
					'type'        => 'select',
					'label'       => esc_html__('Choose Post', 'rey-core'),
					'query_args' => [
						'type' => 'posts',
						'post_type' => 'post',
					],
					'condition' => [
						[
							'setting'  => 'type',
							'operator' => '==',
							'value'    => 'single',
						],
					],
				],

				'related_limit' => [
					'type'    => 'number',
					'label'   => esc_html__('Posts Limit', 'rey-core'),
					'default' => 3,
					'condition' => [
						[
							'setting'  => 'type',
							'operator' => '==',
							'value'    => 'related',
						],
					],
				],

				'show_image' => [
					'type'    => 'select',
					'label'   => esc_html__('Show Image', 'rey-core'),
					'default' => '',
					'condition' => [
						[
							'setting'  => 'type',
							'operator' => 'in',
							'value'    => ['related', 'single'],
						],
					],
					'choices'     => [
						'' => esc_html__('- Default -', 'rey-core'),
						'yes' => esc_html__('Yes', 'rey-core'),
						'no' => esc_html__('No', 'rey-core'),
					],
				],

				'image_alignment' => [
					'type'    => 'select',
					'label'   => esc_html__('Image alignment', 'rey-core'),
					'default' => '',
					'condition' => [
						[
							'setting'  => 'type',
							'operator' => 'in',
							'value'    => ['related', 'single'],
						],
						[
							'setting'  => 'show_image',
							'operator' => '!=',
							'value'    => 'no',
						],
					],
					'choices'     => [
						'' => esc_html__('- Default -', 'rey-core'),
						'top' => esc_html__('Top', 'rey-core'),
						'left' => esc_html__('Left', 'rey-core'),
						'right' => esc_html__('Right', 'rey-core'),
					],
				],

				'show_date' => [
					'type'    => 'select',
					'label'   => esc_html__('Show date', 'rey-core'),
					'default' => '',
					'condition' => [
						[
							'setting'  => 'type',
							'operator' => 'in',
							'value'    => ['related', 'single'],
						],
					],
					'choices'     => [
						'' => esc_html__('- Default -', 'rey-core'),
						'yes' => esc_html__('Yes', 'rey-core'),
						'no' => esc_html__('No', 'rey-core'),
					],
				],

				'show_categories' => [
					'type'    => 'select',
					'label'   => esc_html__('Show categories', 'rey-core'),
					'default' => '',
					'condition' => [
						[
							'setting'  => 'type',
							'operator' => 'in',
							'value'    => ['related', 'single'],
						],
					],
					'choices'     => [
						'' => esc_html__('- Default -', 'rey-core'),
						'yes' => esc_html__('Yes', 'rey-core'),
						'no' => esc_html__('No', 'rey-core'),
					],
				],

				'numerotation' => [
					'type'    => 'select',
					'label'   => esc_html__('Show numerotation', 'rey-core'),
					'default' => '',
					'condition' => [
						[
							'setting'  => 'type',
							'operator' => 'in',
							'value'    => ['related', 'single'],
						],
					],
					'choices'     => [
						'' => esc_html__('- Default -', 'rey-core'),
						'yes' => esc_html__('Yes', 'rey-core'),
						'no' => esc_html__('No', 'rey-core'),
					],
				],

				'columns' => [
					'type'    => 'number',
					'label'   => esc_html__('Columns', 'rey-core'),
					'default' => '',
					'choices' => [
						'min' => 1,
						'max' => 5,
						'step' => 1,
					],
					'condition' => [
						[
							'setting'  => 'type',
							'operator' => '==',
							'value'    => 'related',
						],
					],
				],

				'title_size' => [
					'type'    => 'select',
					'label'   => esc_html__('Title size', 'rey-core'),
					'default' => '',
					'condition' => [
						[
							'setting'  => 'type',
							'operator' => 'in',
							'value'    => ['related', 'single'],
						],
					],
					'choices'     => [
						'' => esc_html__('- Default -', 'rey-core'),
						'xxs' => esc_html__('XXS', 'rey-core'),
						'xs' => esc_html__('XS', 'rey-core'),
						'sm' => esc_html__('SM', 'rey-core'),
						'md' => esc_html__('MD', 'rey-core'),
						'lg' => esc_html__('LG', 'rey-core'),
					],
				],

			],
		] );
	}

	public function option(){
		return get_theme_mod('blog_teasers', []);
	}

	public function is_enabled(){
		return !empty( self::option() );
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Blog Posts Teasers', 'Module name', 'rey-core'),
			'description' => esc_html_x('Adds the ability to display various types of content inside blog posts, at specific points of the article.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['frontend'],
			'keywords'    => ['Blog'],
			'video' => true,
		];
	}

	public function module_in_use(){
		return $this->is_enabled();
	}

}
