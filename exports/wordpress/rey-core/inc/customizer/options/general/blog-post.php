<?php
namespace ReyCore\Customizer\Options\General;

if ( ! defined( 'ABSPATH' ) ) exit;

use \ReyCore\Customizer\Controls;

class BlogPost extends \ReyCore\Customizer\SectionsBase {

	public static function get_id(){
		return 'blog-post';
	}

	public function get_title(){
		return esc_html__('Blog Posts', 'rey-core');
	}

	public function get_priority(){
		return 60;
	}

	public function get_icon(){
		return 'blog-posts';
	}

	public function help_link(){
		return reycore__support_url('kb/customizer-blog-settings/#blog-post');
	}

	public function controls(){

		$this->add_title( esc_html__('Layout', 'rey-core'), [
			'separator' => 'none',
		]);

		$this->add_control( array(
			'type'        => 'select',
			'settings'    => 'post_width',
			'label'       => __('Post width', 'rey-core'),
			'help' => [
				__('Select the post width style.', 'rey-core')
			],
			'default'     => 'c',
			'choices'     => array(
				'c' => esc_attr__('Compact', 'rey-core'),
				'e' => esc_attr__('Expanded', 'rey-core')
			),
		));

		$this->start_controls_group( [
			'label'    => esc_html__( 'Extra settings', 'rey-core' ),
			'active_callback' => [
				[
					'setting'  => 'post_width',
					'operator' => '==',
					'value'    => 'c',
				],
			],
		]);

			$this->add_control( [
				'type'        => 'rey-number',
				'settings'    => 'custom_post_width',
				'label'       => esc_html__( 'Post Width', 'rey-core' ),
				'default'     => '',
				'choices'     => [
					// 'min'  => 100,
					'max'  => 1920,
					'step' => 1,
				],
				'transport'   => 'auto',
				'output'      		=> [
					[
						'element'  => ':root',
						'property' => '--post-width',
						'units' 	=> 'px',
					],
				],
			] );

			$this->add_control( [
				'type'        => 'rey-number',
				'settings'    => 'wide_size',
				'label'       => esc_html__( '"Wide" alignment Size', 'rey-core' ) . ' (vw)',
				'default'     => 25,
				'choices'     => [
					'min'  => 0,
					'max'  => 80,
					'step' => 1,
				],
				'transport'   => 'auto',
				'output'      => [
					[
						'element'  => ':root',
						'property' => '--post-align-wide-size',
						'units' 	=> 'vw',
					],
				]
			] );

		$this->end_controls_group();


		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'blog_post__comments_btn',
			'label'       => __('Comments - Show large button', 'rey-core'),
			'help' => [
				__('If enabled, the comments section will be hidden and a large outline button will be shown instead to toggle open the comments or join the conversation.', 'rey-core')
			],
			'default'     => false,
		] );

		$this->start_controls_group( [
			'label'    => esc_html__( 'Extra settings', 'rey-core' ),
			'active_callback' => [
				[
					'setting'  => 'blog_post__comments_btn',
					'operator' => '==',
					'value'    => true,
				],
			],
		]);

			$this->add_control( [
				'type'        => 'toggle',
				'settings'    => 'blog_post__comments_expanded',
				'label'       => esc_html__( 'Start expanded', 'rey-core' ),
				'default'     => false,
			] );

			$this->add_control( [
				'type'        => 'select',
				'settings'    => 'blog_post__comments_btn_style',
				'label'       => esc_html__( 'Button style', 'rey-core' ),
				'default'     => 'secondary-outline',
				'choices'     => [
					'secondary-outline' => esc_html__( 'Outline', 'rey-core' ),
					'primary' => esc_html__( 'Filled', 'rey-core' ),
				],
			] );

		$this->end_controls_group();

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'blog_post__links',
			'label'       => esc_html__( 'Links style', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'' => esc_html__( 'Underline', 'rey-core' ),
				'clean' => esc_html__( 'Clean', 'rey-core' ),
			],
		] );

		$this->start_controls_group( [
			'label'    => esc_html__( 'Extra settings', 'rey-core' ),
		]);

			$this->add_control( [
				'type'        => 'rey-color',
				'settings'    => 'blog_post__links_color',
				'label'       => esc_html__( 'Links Color', 'rey-core' ),
				'default'     => '',
				'choices'     => [
					'alpha' => true,
				],
				'output' => [
					[
						'element'  => ':root',
						'property' => '--post-content-links-color',
					]
				],
			] );

			$this->add_control( [
				'type'        => 'rey-color',
				'settings'    => 'blog_post__links_color_hover',
				'label'       => esc_html__( 'Links Hover Color', 'rey-core' ),
				'default'     => '',
				'choices'     => [
					'alpha' => true,
				],
				'output' => [
					[
						'element'  => ':root',
						'property' => '--post-content-links-hover-color',
					]
				]
			] );

		$this->end_controls_group();

		// Meta
		$this->add_title( esc_html__('Meta', 'rey-core'), []);

		$this->add_control( array(
			'type'        => 'toggle',
			'settings'    => 'post_date_visibility',
			'label'       => __('Date', 'rey-core'),
			'help' => [
				__('Display the post date?', 'rey-core')
			],
			'default'     => '1',
		));

		$this->add_control( array(
			'type'        => 'toggle',
			'settings'    => 'post_comment_visibility',
			'label'       => __('Comments', 'rey-core'),
			'help' => [
				__('Enable comments number?', 'rey-core')
			],
			'default'     => '1',
		));

		$this->add_control( array(
			'type'        => 'toggle',
			'settings'    => 'post_categories_visibility',
			'label'       => __('Categories', 'rey-core'),
			'help' => [
				__('Enable categories?', 'rey-core')
			],
			'default'     => '1',
		));

		$this->add_control( array(
			'type'        => 'toggle',
			'settings'    => 'post_author_visibility',
			'label'       => __('Author', 'rey-core'),
			'help' => [
				__('Enable author?', 'rey-core')
			],
			'default'     => '1',
		));

		$this->add_control( array(
			'type'        => 'toggle',
			'settings'    => 'post_read_visibility',
			'label'       => __('Read duration', 'rey-core'),
			'help' => [
				__('Enable read duration?', 'rey-core')
			],
			'default'     => '1',
		));
		$this->add_control( array(
			'type'        => 'toggle',
			'settings'    => 'post_author_box',
			'label'       => __('Author Box', 'rey-core'),
			'help' => [
				__('Enable author box?', 'rey-core')
			],
			'default'     => '1',
		));

		$this->add_control( array(
			'type'        => 'toggle',
			'settings'    => 'post_tags',
			'label'       => __('Tags', 'rey-core'),
			'help' => [
				__('Enable post tags?', 'rey-core')
			],
			'default'     => '1',
		));

		$this->add_control( array(
			'type'        => 'toggle',
			'settings'    => 'post_navigation',
			'label'       => __('Navigation', 'rey-core'),
			'help' => [
				__('Enable post navigation?', 'rey-core')
			],
			'default'     => '1',
		));

		$this->add_control( array(
			'type'        => 'toggle',
			'settings'    => 'post_thumbnail_visibility',
			'label'       => __('Display Featured Image', 'rey-core'),
			'help' => [
				__('Enable thumbnail or other media?', 'rey-core')
			],
			'default'     => '1',
		));

		$this->start_controls_group( [
			'label'    => esc_html__( 'Extra settings', 'rey-core' ),
			'active_callback' => [
				[
					'setting'  => 'post_thumbnail_visibility',
					'operator' => '!=',
					'value'    => '',
				],
			],
		]);

			$this->add_control( [
				'type'        => 'select',
				'settings'    => 'post_thumbnail_image_size',
				'label'       => __('Thumb Size', 'rey-core'),
				'help' => [
					__('Select the featured image intrinsic size.', 'rey-core')
				],
				'default'     => '',
				'choices'     => \ReyCore\Helper::get_all_image_sizes(),
			] );

		$this->end_controls_group();

		$this->add_control( array(
			'type'        => 'toggle',
			'settings'    => 'post_cat_text_visibility',
			'label'       => __('Large Category Text', 'rey-core'),
			'help' => [
				__('Enable the big category text behind the post title?', 'rey-core')
			],
			'default'     => '1',
		));


		$this->add_control( array(
			'type'        => 'toggle',
			'settings'    => 'post_share',
			'label'       => __('Social Sharing links', 'rey-core'),
			'help' => [
				__('Enable Sharing links in the post footer?', 'rey-core')
			],
			'default'     => '1',
		));

		$this->start_controls_group( [
			'label'    => esc_html__( 'Extra settings', 'rey-core' ),
			'active_callback' => [
				[
					'setting'  => 'post_share',
					'operator' => '==',
					'value'    => true,
				],
			],
		]);

			$this->add_control( [
				'type'        => 'select',
				'settings'    => 'post_share_style',
				'label'       => esc_html__( 'Sharing Icons style', 'rey-core' ),
				'default'     => '',
				'choices'     => [
					'' => esc_html__( 'Default (Colored)', 'rey-core' ),
					'round_c' => esc_html__( 'Colored Rounded', 'rey-core' ),
					'minimal' => esc_html__( 'Minimal', 'rey-core' ),
					'round_m' => esc_html__( 'Minimal Rounded', 'rey-core' ),
				],
			] );

			$this->add_control( [
				'type'        => 'select',
				'settings'    => 'post_share_icons_list',
				'label'       => esc_html__( 'Social Sharing Items', 'rey-core' ),
				'default'     => ['facebook-f', 'twitter', 'linkedin', 'pinterest-p', 'mail'],
				'multiple'    => 15,
				'choices'     => reycore__social_icons_list_select2('share'),
				'css_class' => '--block-label',

			] );

		$this->end_controls_group();

		$this->add_title( esc_html__('Typography', 'rey-core'), [ ]);

		$this->add_control( array(
			'type'        => 'typography',
			'settings'    => 'typography_blog_post_title',
			'label'       => esc_attr__('Post Title', 'rey-core'),
			'show_variants' => true,
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
					'element' => '.single-post .rey-postTitle',
				),
			),
			'load_choices' => true,
			'responsive' => true,

		));


		$this->add_control( array(
			'type'        => 'typography',
			'settings'    => 'typography_blog_post_content',
			'label'       => esc_attr__('Post Content', 'rey-core'),
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
					'element' => '.single-post .rey-postContent, .single-post .rey-postContent a',
				),
			),
			'load_choices' => true,
			'responsive' => true,
		));

		$this->add_title( esc_html__('Misc.', 'rey-core'), [ ]);

		$this->add_control( array(
			'type'        => 'select',
			'settings'    => 'blog_post_sidebar',
			'label'       => __('Sidebar', 'rey-core'),
			'help' => [
				__('Select the placement of sidebar or disable it. Default is right.', 'rey-core')
			],
			'default'     => 'disabled',
			'choices'     => [
				'inherit' => esc_attr__('Inherit', 'rey-core'),
				'left' => esc_attr__('Left', 'rey-core'),
				'right' => esc_attr__('Right', 'rey-core'),
				'disabled' => esc_attr__('Disabled', 'rey-core'),
			],
		));


	}
}
