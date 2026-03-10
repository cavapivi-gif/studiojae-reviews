<?php
namespace ReyCore\Modules\AjaxFilters;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Customizer extends \ReyCore\Customizer\SectionsBase {

	public static function get_id(){
		return 'ajax-filters';
	}

	public function get_title(){
		return esc_html__('Filters Settings', 'rey-core');
	}

	public function get_priority(){
		return 150;
	}

	public function get_icon(){
		return 'woo-filter-settings';
	}

	public function get_breadcrumbs(){
		return ['WooCommerce', 'Modules'];
	}

	public function controls(){

		$this->add_title( '', [
			'description' => sprintf(__('To enable filter panels, simply drag and drop any Rey Ajax Filter widget into the <a href="%s" target="_blank">Widgets</a> page, in any of the Shop Sidebar / Filter Panel / Filter Top Bar areas. Read <a href="%s" target="_blank">more here</a>.', 'rey-core'), admin_url('widgets.php') , reycore__support_url('kb/add-filters-in-woocommerce/')) . '<br>',
			'separator' => 'none',
		]);

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'ajaxfilter_animation_type',
			'label'       => esc_html__( 'Filtering Animation', 'rey-core' ),
			'default'     => 'default',
			'choices'     => [
				'default' => esc_html__( 'Fade & Scroll to top', 'rey-core' ),
				'subtle' => esc_html__( 'Suble fade', 'rey-core' ),
				'' => esc_html__( 'None', 'rey-core' ),
			],
		] );

		$this->start_controls_group( [
			'label'    => esc_html__( 'Fade / Scroll options', 'rey-core' ),
			'active_callback' => [
				[
					'setting'  => 'ajaxfilter_animation_type',
					'operator' => '==',
					'value'    => 'default',
				],
			],
		]);

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'ajaxfilter_scroll_to_top',
			'label'       => esc_html__( 'Scroll to top', 'rey-core' ),
			'description' => esc_html__( 'Enable if to enable scroll to top after updating shop loop.', 'rey-core' ),
			'default'     => true,
		] );

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'ajaxfilter_scroll_to_top_from',
			'label'       => esc_html__( 'Scroll to top target', 'rey-core' ),
			'help' => [
				esc_html__( 'Offset when scrolling to top.', 'rey-core' )
			],
			'default'     => 'grid',
			'choices'     => [
				'grid'  => esc_html_x('Products Grid', 'Customizer control label', 'rey-core'),
				'top'  => esc_html_x('Page top', 'Customizer control label', 'rey-core'),
			],
			'active_callback' => [
				[
					'setting'  => 'ajaxfilter_animation_type',
					'operator' => '==',
					'value'    => 'default',
				],
				[
					'setting'  => 'ajaxfilter_scroll_to_top',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$this->add_control( [
			'type'        => 'rey-number',
			'settings'    => 'ajaxfilter_scroll_to_top_offset',
			'label'       => esc_html__( 'Scroll to top offset', 'rey-core' ),
			'help' => [
				esc_html__( 'Offset when scrolling to top.', 'rey-core' )
			],
			'default'     => 100,
			'choices'     => [
				'min'  => 0,
				'max'  => 200,
				'step' => 1,
			],
			'active_callback' => [
				[
					'setting'  => 'ajaxfilter_animation_type',
					'operator' => '==',
					'value'    => 'default',
				],
				[
					'setting'  => 'ajaxfilter_scroll_to_top',
					'operator' => '==',
					'value'    => true,
				],
				[
					'setting'  => 'ajaxfilter_scroll_to_top_from',
					'operator' => '==',
					'value'    => 'grid',
				],
			],
		] );

		$this->end_controls_group();

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'ajaxfilter_active_position',
			'label'       => esc_html__( 'Active filters position', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'' => esc_html__( 'None (manually added)', 'rey-core' ),
				'above_grid' => esc_html__( 'Above product grid', 'rey-core' ),
				'above_header' => esc_html__( 'Above grid header', 'rey-core' ),
			],
		] );

		$this->start_controls_group( [
			'label'    => esc_html__( 'Extra options', 'rey-core' ),
			'active_callback' => [
				[
					'setting'  => 'ajaxfilter_active_position',
					'operator' => '!=',
					'value'    => '',
				],
			],
		]);

		$this->add_control( [
			'type'     => 'text',
			'settings' => 'ajaxfilter_active_clear_text',
			'label'       => esc_html__( 'Reset text', 'rey-core' ),
			'default'  => esc_html__('Clear all', 'rey-core'),
			'input_attrs' => [
				'placeholder' => 'eg: Clear all'
			],
		] );

		$this->end_controls_group();

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'ajaxfilter_apply_filter',
			'label'       => esc_html__( '"Apply Filters" button', 'rey-core' ),
			'description' => esc_html__( 'Enable if you want to append an Apply Filter button to submit the filters.', 'rey-core' ),
			'default'     => false,
		] );

		$this->start_controls_group( [
			'label'    => esc_html__( 'Button options', 'rey-core' ),
			'active_callback' => [
				[
					'setting'  => 'ajaxfilter_apply_filter',
					'operator' => '==',
					'value'    => true,
				],
			],
		]);

		$this->add_control( [
			'type'        => 'text',
			'settings'    => 'ajaxfilter_apply_filter_text',
			'label'       => esc_html__( 'Button Text', 'rey-core' ),
			'default'     => esc_html__('Apply filters', 'rey-core'),
		] );

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'ajaxfilter_apply_filter_live',
			'label'       => esc_html__( 'Show live results', 'rey-core' ),
			'help' => [
				esc_html__( 'Will show products results count inside the button.', 'rey-core' )
			],
			'default'     => false,
			'active_callback' => [
				[
					'setting'  => 'ajaxfilter_apply_filter',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$this->end_controls_group();

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'ajaxfilter_product_sorting',
			'label'       => esc_html__( 'Ajax Sorting', 'rey-core' ),
			'description'       => esc_html__( 'Enable if you want to sort your products via ajax.', 'rey-core' ),
			'default'     => true,
		] );

		// $this->add_control( [
		// 	'type'        => 'toggle',
		// 	'settings'    => 'ajaxfilter_transients',
		// 	'label'       => esc_html__( 'Enable Transients', 'rey-core' ),
		// 	'description' => esc_html__( 'Transients improve query performance by temporarily caching them.', 'rey-core' ),
		// 	'default'     => true,
		// ] );

		$this->add_control( [
			'type'        => 'rey-button',
			'settings'    => 'ajaxfilter_clear_transient',
			'label'       => esc_html__( 'Clear Transients', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'text' => esc_html__('Clear', 'rey-core'),
				'ajax_action' => 'ajaxfilter_clear_transient',
			],
			'active_callback' => [
				[
					'setting'  => 'ajaxfilter_transients',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$this->add_title( esc_html__('SIDEBARS', 'rey-core') );

		$available_sidebars = [
			'' => esc_html__('- Select -', 'rey-core')
		];

		if( reycore_wc__check_filter_panel() ){
			$available_sidebars['filters-sidebar'] = esc_html__( 'Filters Offcanvas Panel', 'rey-core' );
		}

		if( reycore_wc__check_shop_sidebar() ){
			$available_sidebars['shop-sidebar'] = esc_html__( 'Shop Sidebar', 'rey-core' );
		}

		if( reycore_wc__check_filter_sidebar_top() ){
			$available_sidebars['filters-top-sidebar'] = esc_html__( 'Top Filters Bar', 'rey-core' );
		}

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'ajaxfilter_mobile_button_opens',
			'label'       => esc_html__( 'Mobile Filter opens:', 'rey-core' ),
			'default'     => '',
			'choices'     => $available_sidebars,
		] );

		$this->add_title( esc_html__('SIDEBAR: SHOP', 'rey-core'), [
			'description' => __('Customize the shop sidebar. Find more Shop sidebar options in <a href="#autofocus-control" data-control="accordion_start__shop_sidebar_acc">Grid Components > Sidebar</a> .', 'rey-core'),
		]);

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'ajaxfilter_shop_sidebar_mobile_offcanvas',
			'label'       => esc_html__( '"Filters" button on mobile', 'rey-core' ),
			'help' => [
				esc_html__( 'On mobiles, this option will transform the shop sidebar into an off-canvas panel and add a Filter button. The option won\'t work though if the Filter Panel sidebar has widgets inside. Make sure to select the "Mobile Filter opens" option.', 'rey-core' )
			],
			'default'     => true,
		] );

		$this->add_title( esc_html__('SIDEBAR: FILTER PANEL (OFFCANVAS)', 'rey-core'), [
			'description' => esc_html__('Customize the off-canvas Filter panel.', 'rey-core'),
		]);

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'ajaxfilter_panel_btn_pos',
			'label'       => esc_html__( 'Button Position', 'rey-core' ),
			'default'     => 'right',
			'choices'     => [
				'left' => esc_html__( 'Left', 'rey-core' ),
				'right' => esc_html__( 'Right', 'rey-core' ),
			],
		] );

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'ajaxfilter_panel_pos',
			'label'       => esc_html__( 'Panel Position', 'rey-core' ),
			'default'     => 'right',
			'choices'     => [
				'left' => esc_html__( 'Left', 'rey-core' ),
				'right' => esc_html__( 'Right', 'rey-core' ),
			],
		] );

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'ajaxfilter_panel_keep_open',
			'label'       => esc_html__( 'Keep panel open when filtering', 'rey-core' ),
			'default'     => false,
		] );

		$this->add_control( [
			'type'        => 'slider',
			'settings'    => 'ajaxfilter_panel_size',
			'label'       => esc_html__( 'Filter Panel width on mobile (%)', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'min'  => 40,
				'max'  => 100,
				'step' => 1,
			],
			'output' => [
				[
					'media_query'	=> '@media (max-width: 1024px)',
					'element' => '.rey-filterPanel-wrapper.rey-sidePanel',
					'property' => 'width',
					'units' => '%',
				],
			],
		] );

		$this->add_title( esc_html__('SIDEBAR: TOP BAR', 'rey-core'), [
			'description' => esc_html__('Customize the top bar filter sidebar.', 'rey-core'),
		]);

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'ajaxfilter_topbar_buttons',
			'label'       => esc_html__( 'Enable "Filters" label/button', 'rey-core' ),
			'default'     => true,
		] );

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'ajaxfilter_topbar_sticky_2',
			'label'       => esc_html__( 'Sticky on scroll', 'rey-core' ),
			'default'     => get_theme_mod('ajaxfilter_topbar_sticky') === true ? 't' : '',
			'choices'     => [
				'' => esc_html__( '- Disabled -', 'rey-core' ),
				't' => esc_html__( 'Stick to Top', 'rey-core' ),
				'b' => esc_html__( 'Stick to Bottom', 'rey-core' ),
			],
		] );

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'ajaxfilter_topbar_position',
			'label'       => esc_html__( 'Top bar position', 'rey-core' ),
			'default'     => 'before',
			'choices'     => [
				'before' => esc_html__( 'Before title', 'rey-core' ),
				'after' => esc_html__( 'After title', 'rey-core' ),
			],
		] );

		$this->add_title( esc_html__('Typography', 'rey-core') );

		$this->add_control( [
			'type'        => 'typography',
			'settings'    => 'ajaxfilter_nav_typo',
			'label'       => esc_attr__('Nav. items typography', 'rey-core'),
			'default'     => [
				'font-family'      => '',
				'font-size'      => '',
				'line-height'    => '',
				'letter-spacing' => '',
				'font-weight' => '',
				'text-transform' => '',
				'variant' => ''
			],
			'output' => [
				[
					'element' => '.reyajfilter-layered-nav li a',
				],
			],
			'load_choices' => true,
			'transport' => 'auto',
			'responsive' => true,
		]);

		$this->add_control( [
			'type'        => 'rey-color',
			'settings'    => 'ajaxfilter_nav_item_color',
			'label'       => esc_html__( 'Nav. items color', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'alpha' => true,
			],
			'output' => [
				[
					'element' => '.reyajfilter-layered-nav li a',
					'property' => 'color',
				],
			],
		] );

		$this->add_control( [
			'type'        => 'rey-color',
			'settings'    => 'ajaxfilter_nav_item_hover_color',
			'label'       => esc_html__( 'Nav. items hover color', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'alpha' => true,
			],
			'output' => [
				[
					'element'  => '.reyajfilter-layered-nav li a:hover, .reyajfilter-layered-nav li.chosen a',
					'property' => 'color',
				],
			],
		] );


		// Advanced
		$this->add_title( esc_html__('Advanced settings', 'rey-core') );

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'ajaxfilters__multiple_all',
			'label'       => esc_html__( 'Multiple filters all?', 'rey-core' ),
			'help' => [
				esc_html__( 'When enabling multiple filters, by default only current category siblings are available to be selected. Enabling this option will allow selecting any item from any category or subcategory.', 'rey-core' )
			],
			'default'     => false,
		] );

		$this->add_title( esc_html__('Taxonomies', 'rey-core') );

		$this->add_control( [
			'type'        => 'repeater',
			'settings'    => 'ajaxfilters_taxonomies',
			'label'       => esc_html__('Register taxonomies', 'rey-core'),
			'description' => __('Register taxonomies to use the Ajax Filter Taxonomies Widget in case they are not already listed there.', 'rey-core'),
			'row_label' => [
				'type' => 'text',
				'value' => esc_html__('Taxonomy', 'rey-core'),
			],
			'button_label' => esc_html__('New Taxonomy', 'rey-core'),
			'default'      => [],
			'fields' => [
				'id' => [
					'type'        => 'text',
					'label'       => esc_html__('Taxonomy', 'rey-core'),
					'default' => esc_html__('product_some_tax', 'rey-core'),
				],
				'name' => [
					'type'        => 'text',
					'label'       => esc_html__('Taxonomy Name', 'rey-core'),
					'default' => esc_html__('Some Tax', 'rey-core'),
				],
			],
		] );

		$this->add_title( esc_html__('Meta Queries (Custom Fields)', 'rey-core') );

		$this->add_control( [
			'type'        => 'repeater',
			'settings'    => 'ajaxfilters_meta_queries',
			'label'       => esc_html__('Register Meta Query', 'rey-core'),
			'description' => sprintf(__('Register a meta query to use with the Filter - Meta Widget. <a href="%s" target="_blank">Learn more</a> about this.', 'rey-core'), reycore__support_url('kb/ajax-filter-widgets/#how-to-work-with-filter-by-custom-fields-meta-data') ),
			'row_label' => [
				'value' => esc_html__('Meta query', 'rey-core'),
				'type'  => 'field',
				'field' => 'title',
			],
			'button_label' => esc_html__('New Meta Query', 'rey-core'),
			'default'      => [],
			'fields' => [
				'title' => [
					'type'        => 'text',
					'label'       => esc_html__('Label', 'rey-core'),
					'default' => '',
				],
				'key' => [
					'type'        => 'text',
					'label'       => esc_html__('Key', 'rey-core'),
					'default' => '',
				],
				'operator' => [
					'type'        => 'select',
					'label'       => esc_html__('Operator', 'rey-core'),
					'default' => '==',
					'choices' => reycore__get_operators()
				],
				'value' => [
					'type'        => 'text',
					'label'       => esc_html__('Value', 'rey-core'),
					'default' => '',
				],
			],
		] );

	}
}
