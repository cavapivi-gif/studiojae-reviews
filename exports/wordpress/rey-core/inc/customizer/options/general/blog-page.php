<?php
namespace ReyCore\Customizer\Options\General;

if ( ! defined( 'ABSPATH' ) ) exit;

class BlogPage extends \ReyCore\Customizer\SectionsBase {

	public static function get_id(){
		return 'blog-page';
	}

	public function get_title(){
		return esc_html__('Blog Page', 'rey-core');
	}

	public function get_priority(){
		return 55;
	}

	public function get_icon(){
		return 'blog-page';
	}

	public function help_link(){
		return reycore__support_url('kb/customizer-blog-settings/#blog-page');
	}

	public function controls(){

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'blog_columns',
			'label'       => __('Posts per row', 'rey-core'),
			'help' => [
				__('Select the number of posts per row.', 'rey-core'),
			],
			'default'     => '1',
			'choices'     => [
				'1' => esc_attr__('1 per row', 'rey-core'),
				'2' => esc_attr__('2 per row', 'rey-core'),
				'3' => esc_attr__('3 per row', 'rey-core'),
				'4' => esc_attr__('4 per row', 'rey-core')
			],
			'responsive' => true,
			'output'     => [
				[
					'element'  		=> ':root',
					'property' 		=> '--blog-columns',
				],
			],
		]);

		$this->add_control( [
			'type'        => 'rey-number',
			'settings'    => 'blog_single_col_width',
			'label'       => esc_html__('Blog list width', 'rey-core') . ' (px)',
			'default'     => '',
			'choices'     => [
				'min'  => 500,
				'max'  => 2000,
				'step' => 1,
			],
			'active_callback' => [
				[
					'setting'  => 'blog_columns',
					'operator' => '==',
					'value'    => '1',
				],
			],
			'output'     => [
				[
					'element'  		=> ':root',
					'property' 		=> '--blog-single-width',
					'units'    		=> 'px',
				],
			],
		] );

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'blog_pagination',
			'label'       => __('Pagination type', 'rey-core'),
			'help' => [
				__('Select the type of pagination you want to be displayed after the products.', 'rey-core')
			],
			'default'     => 'paged',
				'choices'     => [
				'paged' => esc_html__( 'Paged', 'rey-core' ),
				'load-more' => esc_html__( 'Load More Button (via Ajax)', 'rey-core' ),
				'infinite' => esc_html__( 'Infinite loading (via Ajax)', 'rey-core' ),
			],
		] );


		// Meta
		$this->add_title( esc_html__('Meta', 'rey-core') );


		$this->add_control( array(
			'type'        => 'toggle',
			'settings'    => 'blog_date_visibility',
			'label'       => __('Date', 'rey-core'),
			'help' => [
				__('Enable date?', 'rey-core')
			],
			'default'     => '1',
		));
		$this->add_control( array(
			'type'        => 'toggle',
			'settings'    => 'blog_date_type',
			'label'       => __('Human Date', 'rey-core'),
			'help' => [
				__('Enable human readable date?', 'rey-core')
			],
			'default'     => '1',
			'active_callback' => [
				[
					'setting'  => 'blog_date_visibility',
					'operator' => '==',
					'value'    => true,
				],
			],
		));
		$this->add_control( array(
			'type'        => 'toggle',
			'settings'    => 'blog_comment_visibility',
			'label'       => __('Comments', 'rey-core'),
			'help' => [
				__('Enable comments number?', 'rey-core')
			],
			'default'     => '1',
		));
		$this->add_control( array(
			'type'        => 'toggle',
			'settings'    => 'blog_categories_visibility',
			'label'       => __('Categories', 'rey-core'),
			'help' => [
				__('Enable categories?', 'rey-core')
			],
			'default'     => '1',
		));
		$this->add_control( array(
			'type'        => 'toggle',
			'settings'    => 'blog_author_visibility',
			'label'       => __('Author', 'rey-core'),
			'help' => [
				__('Enable author?', 'rey-core')
			],
			'default'     => '1',
		));
		$this->add_control( array(
			'type'        => 'toggle',
			'settings'    => 'blog_read_visibility',
			'label'       => __('Read duration', 'rey-core'),
			'help' => [
				__('Enable read duration?', 'rey-core')
			],
			'default'     => '1',
		));

		$this->add_control( array(
			'type'        => 'toggle',
			'settings'    => 'blog_thumbnail_visibility',
			'label'       => __('Thumbnail (Media)', 'rey-core'),
			'help' => [
				__('Enable thumbnail or other media?', 'rey-core')
			],
			'default'     => '1',
		));

		$this->start_controls_group( [
			'label'    => esc_html__( 'Extra settings', 'rey-core' ),
			'active_callback' => [
				[
					'setting'  => 'blog_thumbnail_visibility',
					'operator' => '==',
					'value'    => true,
				],
			],
		]);

			$this->add_control( array(
				'type'        => 'toggle',
				'settings'    => 'blog_thumbnail_expand',
				'label'       => __('Expand Thumbnail', 'rey-core'),
				'help' => [
					__('Enable expanded thumbnail in the posts?', 'rey-core')
				],
				'default'     => '1',
			));

			$this->add_control( array(
				'type'        => 'toggle',
				'settings'    => 'blog_thumbnail_animation',
				'label'       => __('Expand With Animation', 'rey-core'),
				'help' => [
					__('Enable expanded thumbnail with hover animation?', 'rey-core')
				],
				'default'     => '1',
				'active_callback' => [
					[
						'setting'  => 'blog_thumbnail_expand',
						'operator' => '==',
						'value'    => true,
					],
				],
			));

		$this->end_controls_group();

		$this->add_control( array(
			'type'        => 'toggle',
			'settings'    => 'blog_title_animation',
			'label'       => __('Title Animation', 'rey-core'),
			'help' => [
				__('Select the title\'s animation on post hover.', 'rey-core')
			],
			'default'     => '1',
		));

		$this->add_control( array(
			'type'        => 'select',
			'settings'    => 'blog_content_type',
			'label'       => __('Content', 'rey-core'),
			'help' => [
				__('Select the post\' content type.', 'rey-core')
			],

			'default'     => 'e',
			'choices'     => array(
				'e' => esc_attr__('Excerpt', 'rey-core'),
				'c' => esc_attr__('Default Content', 'rey-core'),
				'none' => esc_attr__('None', 'rey-core'),
			),
		));

		$this->add_control( [
			'type'        => 'rey-number',
			'settings'    => 'blog_excerpt_length',
			'label'       => esc_html__('Excerpt length', 'rey-core'),
			'default'     => 55,
			'choices'     => [
				'min'  => 5,
				'max'  => 200,
				'step' => 1,
			],
			'active_callback' => [
				[
					'setting'  => 'blog_content_type',
					'operator' => '==',
					'value'    => 'e',
				],
			],
		] );


		$this->add_title( esc_html__('Sidebar', 'rey-core') );

		$this->add_control( array(
			'type'        => 'select',
			'settings'    => 'blog_sidebar',
			'label'       => __('Sidebar Placement', 'rey-core'),
			'help' => [
				__('Select the placement of sidebar or disable it. Default is right.', 'rey-core')
			],
			'default'     => 'right',
			'choices'     => array(
				'left' => esc_attr__('Left', 'rey-core'),
				'right' => esc_attr__('Right', 'rey-core'),
				'disabled' => esc_attr__('Disabled', 'rey-core'),
			)
		));

		$this->add_control( [
			'type'        => 'slider',
			'settings'    => 'blog_sidebar_size',
			'label'       => esc_html__( 'Sidebar Size', 'rey-core' ),
			'default'     => 27,
			'choices'     => [
				'min'  => 15,
				'max'  => 60,
				'step' => 1,
			],
			'transport'   => 'auto',
			'output'      		=> [
				[
					'element'  		=> ':root',
					'property' 		=> '--sidebar-size',
					'units'    		=> '%',
				],
			],
			'active_callback' => [
				[
					'setting'  => 'blog_sidebar',
					'operator' => '!=',
					'value'    => 'disabled',
				],
			],
		] );

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'blog_sidebar_boxed',
			'label'       => esc_html__( 'Enable boxed sidebar', 'rey-core' ),
			'default'     => true,
			'active_callback' => [
				[
					'setting'  => 'blog_sidebar',
					'operator' => '!=',
					'value'    => 'disabled',
				],
			],
			'help' => [
				__('Wraps the sidebar into a background colored box.', 'rey-core')
			],
		] );


		/**
		 * Blog
		 */
		$this->add_title( esc_html__('Typography', 'rey-core') );

		$this->add_control( array(
			'type'        => 'typography',
			'settings'    => 'typography_blog_title',
			'label'       => esc_attr__('Blog Post Title', 'rey-core'),
			'default'     => array(
				'font-family'      => '',
				'font-size'      => '',
				'line-height'    => '',
				'letter-spacing' => '',
				'text-transform' => '',
				'font-weight' => '',
				'variant' => '',
				'color' => '',
			),
			'output' => array(
				array(
					'element' => '.rey-postList .rey-postTitle > a',
				),
			),
			'load_choices' => true,
			'responsive' => true,

		));

		$this->add_control( array(
			'type'        => 'typography',
			'settings'    => 'typography_blog_content',
			'label'       => esc_attr__('Blog Post Content', 'rey-core'),
			'default'     => array(
				'font-family'      => '',
				'font-size'      => '',
				'line-height'    => '',
				'letter-spacing' => '',
				'font-weight' => '',
				'variant' => '',
				'color' => '',
			),
			'output' => array(
				array(
					'element' => '.rey-postList .rey-postContent, .rey-postList .rey-postContent a',
				),
			),
			'load_choices' => true,
			'responsive' => true,

		));


	}
}
