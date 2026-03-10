<?php
namespace ReyCore\Modules\Wishlist;

if ( ! defined( 'ABSPATH' ) ) exit;

class Customizer extends \ReyCore\Customizer\SectionsBase {

	public static function get_id(){
		return 'wishlist';
	}

	public function get_title(){
		return esc_html__('Wishlist', 'rey-core');
	}

	public function get_priority(){
		return 150;
	}

	public function get_icon(){
		return 'wishlist';
	}

	public function get_breadcrumbs(){
		return ['WooCommerce', 'Modules'];
	}

	public function controls(){

		$can_show_options = !class_exists('TInvWL_Public_AddToWishlist');

		if( $can_show_options ):
			$this->add_control( [
				'type'        => 'toggle',
				'settings'    => 'wishlist__enable',
				'label'       => esc_html__( 'Enable Wishlist', 'rey-core' ),
				'default'     => true,
			] );
		endif;

		$cond__wishlist_enable = [
			'setting'  => 'wishlist__enable',
			'operator' => '==',
			'value'    => true,
		];

		if( $can_show_options ):
			$this->add_control( [
				'settings'    => 'wishlist__default_url',
				'label'       => esc_html__( 'Wishlist page', 'rey-core' ),
				'default'     => '',
				'active_callback' => [
					$cond__wishlist_enable,
				],
				'type'        => 'select',
				'ajax_choices' => 'get_pages',
				'new_page' => [
					'placeholder' => esc_attr__('New page', 'rey-core'),
					'button_text' => esc_attr__('Add', 'rey-core'),
					'new_link' => esc_attr__('+ Add new page', 'rey-core'),
				],
			] );

			$this->add_control( [
				'type'        => 'select',
				'settings'    => 'wishlist__inj_type',
				'label'       => esc_html__('Page content inject', 'rey-core'),
				'help' => [
					__('Select how you want to inject the product grid into the page. If you\'re choosing Shortcode, please use <code>[rey_wishlist_page hide_title="no"]</code>.', 'rey-core'),
					'clickable' => true
				],
				'default'     => 'override',
				'choices'     => [
					'override' => esc_html__( 'Override page', 'rey-core' ),
					'append' => esc_html__( 'Append to end of page', 'rey-core' ),
					'custom' => esc_html__( 'Add custom shortcode', 'rey-core' ),
				],
				'active_callback' => [
					$cond__wishlist_enable,
				],
			] );

		endif;

		if( $can_show_options ):
			$this->add_control( [
				'type'        => 'select',
				'settings'    => 'wishlist__icon_type',
				'label'       => esc_html__( 'Icon Type', 'rey-core' ),
				'default'     => 'heart',
				'choices'     => [
					'heart' => esc_html__( 'Heart', 'rey-core' ),
					'favorites' => esc_html__( 'Ribbon', 'rey-core' ),
				],
				'active_callback' => [
					$cond__wishlist_enable,
				],
			] );
		endif;

		if( $can_show_options ):
			$this->add_control( [
				'type'        => 'select',
				'settings'    => 'wishlist__after_add',
				'label'       => esc_html__( 'After add to list', 'rey-core' ),
				'default'     => 'notice',
				'choices'     => [
					'' => esc_html__( 'Do nothing', 'rey-core' ),
					'notice' => esc_html__( 'Show Notice', 'rey-core' ),
					// 'modal' => esc_html__( 'Show Modal with products', 'rey-core' ),
				],
				'active_callback' => [
					$cond__wishlist_enable,
				],
			] );
		endif;

		$this->add_title( esc_html__('Catalog', 'rey-core'), [
			'active_callback' => [
				$cond__wishlist_enable
			],
		]);


		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'loop_wishlist_enable',
			'label'       => esc_html__( 'Enable button', 'rey-core' ),
			'default'     => true,
			'active_callback' => [
				$cond__wishlist_enable
			],
		] );

		$this->start_controls_group( [
			'label'    => esc_html__( 'Wishlist options', 'rey-core' ),
			'active_callback' => [
				$cond__wishlist_enable,
				[
					'setting'  => 'loop_wishlist_enable',
					'operator' => '==',
					'value'    => true,
				],
			],
		]);


			$this->add_control( [
				'type'        => 'select',
				'settings'    => 'loop_wishlist_position',
				'label'       => esc_html__( 'Button Position', 'rey-core' ),
				'default'     => '',
				'choices'     => [
					'' => esc_html__( '- Inherit (from skin) -', 'rey-core' ),
					'bottom' => esc_html__( 'Bottom', 'rey-core' ),
					'topright' => esc_html__( 'Thumb. top right', 'rey-core' ),
					'bottomright' => esc_html__( 'Thumb. bottom right', 'rey-core' ),
				],
			]);

			$this->add_control( [
				'type'        => 'select',
				'settings'    => 'wishlist_loop__icon_style',
				'label'       => esc_html__( 'Wishlist Icon Style', 'rey-core' ),
				'default'     => '',
				'choices'     => [
					'' => esc_html__( '- Inherit (from skin) -', 'rey-core' ),
					'minimal' => esc_html__( 'Minimal', 'rey-core' ),
					'boxed' => esc_html__( 'Boxed', 'rey-core' ),
					'rounded' => esc_html__( 'Boxed Rounded', 'rey-core' ),
				],
				'active_callback' => [
					$cond__wishlist_enable,
					[
						'setting'  => 'loop_wishlist_enable',
						'operator' => '==',
						'value'    => true,
					],
					[
						'setting'  => 'loop_wishlist_position',
						'operator' => 'in',
						'value'    => ['topright', 'bottomright'],
					],
				],
			] );

			$this->add_control( [
				'type'        => 'toggle',
				'settings'    => 'wishlist_loop__mobile',
				'label'       => esc_html__( 'Enable button on mobile', 'rey-core' ),
				'default'     => false,
				'active_callback' => [
					$cond__wishlist_enable,
					[
						'setting'  => 'loop_wishlist_enable',
						'operator' => '==',
						'value'    => true,
					],
				],
			] );

			$this->add_control( [
				'type'        => 'toggle',
				'settings'    => 'wishlist_loop__tooltip',
				'label'       => esc_html__( 'Show tooltip', 'rey-core' ),
				'default'     => false,
				'active_callback' => [
					$cond__wishlist_enable,
					[
						'setting'  => 'loop_wishlist_enable',
						'operator' => '==',
						'value'    => true,
					],
				],
			] );

		$this->end_controls_group();

		if( $can_show_options ):
			$this->add_title( esc_html__('Product Page', 'rey-core'), [
				'active_callback' => [
					$cond__wishlist_enable,
				],
			]);
		endif;

		if( $can_show_options ):
			$this->add_control( [
				'type'        => 'toggle',
				'settings'    => 'wishlist_pdp__enable',
				'label'       => esc_html__( 'Enable button', 'rey-core' ),
				'default'     => true,
				'active_callback' => [
					$cond__wishlist_enable,
				],
			] );
		endif;

		if( $can_show_options ):
			$this->add_control( [
				'type'        => 'select',
				'settings'    => 'wishlist_pdp__wtext',
				'label'       => esc_html__( 'Text visibility', 'rey-core' ),
				'default'     => 'show_desktop',
				'choices' => [
					'' => esc_html__('Hide', 'rey-core'),
					'show' => esc_html__('Show', 'rey-core'),
					'show_desktop' => esc_html__('Show text on desktop only', 'rey-core'),
				],
				'active_callback' => [
					$cond__wishlist_enable,
					[
						'setting'  => 'wishlist_pdp__enable',
						'operator' => '==',
						'value'    => true,
					],
				],
			] );

			$this->add_control( [
				'type'        => 'toggle',
				'settings'    => 'wishlist_pdp__tooltip',
				'label'       => esc_html__( 'Show tooltip', 'rey-core' ),
				'default'     => false,
				'active_callback' => [
					$cond__wishlist_enable,
					[
						'setting'  => 'wishlist_pdp__enable',
						'operator' => '==',
						'value'    => true,
					],
					[
						'setting'  => 'wishlist_pdp__wtext',
						'operator' => '==',
						'value'    => '',
					],
				]
			] );
		endif;

		if( $can_show_options ):
			$this->add_control( [
				'type'        => 'select',
				'settings'    => 'wishlist_pdp__position',
				'label'       => esc_html__( 'Button Position', 'rey-core' ),
				'default'     => 'inline',
				'choices'     => [
					'inline' => esc_html__( 'Inline with ATC. button', 'rey-core' ),
					'before' => esc_html__( 'Before ATC. button', 'rey-core' ),
					'after' => esc_html__( 'After ATC. button', 'rey-core' ),
				],
				'active_callback' => [
					$cond__wishlist_enable,
					[
						'setting'  => 'wishlist_pdp__enable',
						'operator' => '==',
						'value'    => true,
					],
				],
			] );
		endif;

		if( $can_show_options ):
			$this->add_control( [
				'type'        => 'select',
				'settings'    => 'wishlist_pdp__btn_style',
				'label'       => esc_html__( 'Button Style', 'rey-core' ),
				'default'     => 'btn-line',
				'choices'     => [
					'none' => esc_html__( 'None', 'rey-core' ),
					'btn-line' => esc_html__( 'Underlined on hover', 'rey-core' ),
					'btn-line-active' => esc_html__( 'Underlined', 'rey-core' ),
					'btn-primary' => esc_html__( 'Regular', 'rey-core' ),
					'btn-primary btn--block' => esc_html__( 'Regular & Full width', 'rey-core' ),
					'btn-primary-outline' => esc_html__( 'Regular outline', 'rey-core' ),
					'btn-primary-outline btn--block' => esc_html__( 'Regular outline & Full width', 'rey-core' ),
					'btn-secondary' => esc_html__( 'Secondary', 'rey-core' ),
					'btn-secondary btn--block' => esc_html__( 'Secondary & Full width', 'rey-core' ),
				],
				'active_callback' => [
					$cond__wishlist_enable,
					[
						'setting'  => 'wishlist_pdp__enable',
						'operator' => '==',
						'value'    => true,
					],
				],
			] );
		endif;

		if( $can_show_options ):

			$this->add_title( esc_html__('Share', 'rey-core'), [
				'active_callback' => [
					$cond__wishlist_enable,
				],
			]);

			$this->add_control( [
				'type'        => 'toggle',
				'settings'    => 'wishlist__share_enable',
				'label'       => esc_html__( 'Enable Sharing', 'rey-core' ),
				'default'     => true,
				'active_callback' => [
					$cond__wishlist_enable,
				],
			] );

			$cond__wishlist_share_enable = [
				'setting'  => 'wishlist__share_enable',
				'operator' => '==',
				'value'    => true,
			];

			$this->add_control( [
				'type'        => 'repeater',
				'settings'    => 'wishlist__share_icons',
				'label'       => esc_html__('Social Sharing Icons', 'rey-core'),
				'row_label' => [
					'type' => 'text',
					'value' => esc_html__('Social Icon', 'rey-core'),
					'field' => 'social_icon',
				],
				'button_label' => esc_html__('New Social Icon', 'rey-core'),
				'default'      => [
					[
						'social_icon' => 'twitter'
					],
					[
						'social_icon' => 'facebook-f'
					],
					[
						'social_icon' => 'linkedin'
					],
					[
						'social_icon' => 'pinterest-p'
					],
					[
						'social_icon' => 'mail'
					],
					[
						'social_icon' => 'copy'
					],
				],
				'fields' => [
					'social_icon' => [
						'type'        => 'select',
						'label'       => esc_html__('Social Icon', 'rey-core'),
						'choices'     => reycore__social_icons_list_select2('share'),
					],
				],
				'active_callback' => [
					$cond__wishlist_enable,
					$cond__wishlist_share_enable,
				],
			] );

			$this->add_control( [
				'type'        => 'toggle',
				'settings'    => 'wishlist__share_icons_colored',
				'label'       => esc_html__( 'Colored icons', 'rey-core' ),
				'help' => [
					esc_html__( 'Enable coloring the icons', 'rey-core' )
				],
				'default'     => false,
				'active_callback' => [
					$cond__wishlist_enable,
					$cond__wishlist_share_enable,
				],
			] );

			$this->add_control( [
				'type'        => 'text',
				'settings'    => 'wishlist__share_title',
				'label'       => esc_html__( 'Share title', 'rey-core' ),
				'default'     => '',
				'input_attrs'     => [
					'placeholder' => esc_html__('eg: Share On', 'rey-core'),
				],
				'active_callback' => [
					$cond__wishlist_enable,
					$cond__wishlist_share_enable,
				],
			] );

			$this->add_control( [
				'type'        => 'text',
				'settings'    => 'wishlist__share_text',
				'label'       => esc_html__( 'Share text', 'rey-core' ),
				'help' => [
					esc_html__('Text which is shown when sharing to a social platform.', 'rey-core'),
				],
				'default'     => '',
				'active_callback' => [
					$cond__wishlist_enable,
					$cond__wishlist_share_enable,
				],
			] );

		endif;

		if( $can_show_options ):
			$this->add_title( esc_html__('Texts', 'rey-core'), [
				'active_callback' => [
					$cond__wishlist_enable,
				],
			]);
		endif;

		if( $can_show_options ):
			$this->add_control( [
				'type'        => 'text',
				'settings'    => 'wishlist__text',
				'label'       => esc_html__( 'Wishlist title', 'rey-core' ),
				'default'     => '',
				'input_attrs'     => [
					'placeholder' => esc_html_x('Wishlist', 'Placeholder in Customizer control.', 'rey-core'),
				],
				'active_callback' => [
					$cond__wishlist_enable,
				],
			] );
		endif;

		if( $can_show_options ):
			$this->add_control( [
				'type'        => 'text',
				'settings'    => 'wishlist__texts_add',
				'label'       => esc_html__( 'Add text', 'rey-core' ),
				'default'     => '',
				'input_attrs'     => [
					'placeholder' => esc_html__('Add to wishlist', 'rey-core'),
				],
				'active_callback' => [
					$cond__wishlist_enable,
				],
			] );
		endif;

		if( $can_show_options ):
			$this->add_control( [
				'type'        => 'text',
				'settings'    => 'wishlist__texts_rm',
				'label'       => esc_html__( 'Remove text', 'rey-core' ),
				'default'     => '',
				'input_attrs'     => [
					'placeholder' => esc_html__('Remove from wishlist', 'rey-core'),
				],
				'active_callback' => [
					$cond__wishlist_enable,
				],
			] );
		endif;

		if( $can_show_options ):
			$this->add_control( [
				'type'        => 'text',
				'settings'    => 'wishlist__texts_added',
				'label'       => esc_html__( '"Added" Notice - text', 'rey-core' ),
				'default'     => '',
				'sanitize_callback' => 'wp_kses_post',
				'input_attrs'     => [
					'placeholder' => esc_html__('Added to wishlist!', 'rey-core'),
				],
				'active_callback' => [
					$cond__wishlist_enable,
					[
						'setting'  => 'wishlist__after_add',
						'operator' => '==',
						'value'    => 'notice',
					],
				],
			] );
		endif;

		if( $can_show_options ):
			$this->add_control( [
				'type'        => 'text',
				'settings'    => 'wishlist__texts_btn',
				'label'       => esc_html__( '"Added" Notice - button text', 'rey-core' ),
				'default'     => '',
				'sanitize_callback' => 'wp_kses_post',
				'input_attrs'     => [
					'placeholder' => esc_html__('VIEW WISHLIST', 'rey-core'),
				],
				'active_callback' => [
					$cond__wishlist_enable,
					[
						'setting'  => 'wishlist__after_add',
						'operator' => '==',
						'value'    => 'notice',
					],
				],
			] );
		endif;

		if( $can_show_options ):
			$this->add_control( [
				'type'        => 'text',
				'settings'    => 'wishlist__texts_page_title',
				'label'       => esc_html__( 'Empty page - title', 'rey-core' ),
				'default'     => '',
				'sanitize_callback' => 'wp_kses_post',
				'input_attrs'     => [
					'placeholder' => __('Wishlist is empty.', 'rey-core'),
				],
				'active_callback' => [
					$cond__wishlist_enable,
				],
			] );
		endif;

		if( $can_show_options ):
			$this->add_control( [
				'type'        => 'text',
				'settings'    => 'wishlist__texts_page_text',
				'label'       => esc_html__( 'Empty page - text', 'rey-core' ),
				'default'     => '',
				'sanitize_callback' => 'wp_kses_post',
				'input_attrs'     => [
					'placeholder' => __('You don\'t have any products added in your wishlist. Search and save items to your liking!', 'rey-core'),
				],
				'active_callback' => [
					$cond__wishlist_enable,
				],
			] );
		endif;

		if( $can_show_options ):
			$this->add_control( [
				'type'        => 'text',
				'settings'    => 'wishlist__texts_page_btn_text',
				'label'       => esc_html__( 'Empty page - button text', 'rey-core' ),
				'default'     => '',
				'sanitize_callback' => 'wp_kses_post',
				'input_attrs'     => [
					'placeholder' => __('SHOP NOW', 'rey-core'),
				],
				'active_callback' => [
					$cond__wishlist_enable,
				],
			] );
		endif;


		if( $can_show_options ):

			$this->add_title( esc_html__('EMPTY PAGE', 'rey-core'), [
				'active_callback' => [
					$cond__wishlist_enable,
				],
			]);

			$this->add_control( [
				'type'        => 'select',
				'settings'    => 'wishlist__empty_gs',
				'label'       => esc_html__( 'Show Global Section', 'rey-core' ),
				'default'     => '',
				'choices'     => [
					'' => '- None -'
				],
				'ajax_choices' => 'get_global_sections',
				'edit_preview' => true,
				'active_callback' => [
					$cond__wishlist_enable,
				],
			] );

			$this->add_control( [
				'type'        => 'select',
				'settings'    => 'wishlist__empty_mode',
				'label'       => esc_html__( 'Mode', 'rey-core' ),
				'default'     => 'overwrite',
				'choices'     => [
					'overwrite' => esc_html__( 'Overwrite Content', 'rey-core' ),
					'before' => esc_html__( 'Add Before', 'rey-core' ),
					'after' => esc_html__( 'Add After', 'rey-core' ),
				],
				'active_callback' => [
					$cond__wishlist_enable,
					[
						'setting'  => 'wishlist__empty_gs',
						'operator' => '!=',
						'value'    => '',
					],
				],
			] );

		endif;

		$this->add_title( esc_html__('ANALYTICS', 'rey-core'), [
			'active_callback' => [
				$cond__wishlist_enable
			],
		]);

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'wishlist__analytics',
			'label'       => esc_html__( 'Enable Analytics', 'rey-core' ),
			'description' => esc_html__('The analytics "Most wishlisted" products chart can be found in the backend WooCommerce > Analytics page > Leaderboards section. Please know that for stores with 10000s of users, it might have an impact on the performance.', 'rey-core'),
			'default'     => false,
			'active_callback' => [
				$cond__wishlist_enable
			],
		] );

		$this->add_control( [
			'type'        => 'rey-button',
			'settings'    => 'rey_wishlist_rescan',
			'label'       => esc_html__( 'Force Scan for Most wishlisted', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'text' => esc_html__('Scan', 'rey-core'),
				'ajax_action' => 'rey_wishlist_rescan',
			],
			'active_callback' => [
				$cond__wishlist_enable,
				[
					'setting'  => 'wishlist__analytics',
					'operator' => '==',
					'value'    => true,
				],
			],
			'help' => [
				esc_html__('The top wishlisted chart is automatically updated through weekly background scans. If you need to refresh the chart immediately, please click the "Scan" button. Be aware that updating the index may take up to 10 minutes.', 'rey-core'),
			],
		] );

		$this->add_title( esc_html__('TOP FAVORITE LABEL', 'rey-core'), [
			'active_callback' => [
				$cond__wishlist_enable,
				[
					'setting'  => 'wishlist__analytics',
					'operator' => '==',
					'value'    => true,
				],
			],
		]);

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'wishlist__top_label',
			'label'       => esc_html__( 'Enable Label', 'rey-core' ),
			'default'     => false,
			'active_callback' => [
				$cond__wishlist_enable,
				[
					'setting'  => 'wishlist__analytics',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );


		$this->add_control( [
			'type'        => 'text',
			'settings'    => 'wishlist__top_label_text',
			'label'       => esc_html__( 'Text', 'rey-core' ),
			'default'     => esc_html__('Top Favorite', 'rey-core'),
			'input_attrs'     => [
				'placeholder' => esc_html__('eg: Top Favorite', 'rey-core'),
			],
			'active_callback' => [
				$cond__wishlist_enable,
				[
					'setting'  => 'wishlist__top_label',
					'operator' => '==',
					'value'    => true,
				],
				[
					'setting'  => 'wishlist__analytics',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

	}
}
