<?php
namespace ReyCore\Customizer\Options\Woocommerce;

if ( ! defined( 'ABSPATH' ) ) exit;

class CatalogGridComponents extends \ReyCore\Customizer\SectionsBase {

	public static function get_id(){
		return 'woo-catalog-grid-components';
	}

	public function get_title(){
		return esc_html__('Grid Components', 'rey-core');
	}

	public function get_priority(){
		return 20;
	}

	public function get_icon(){
		return 'woo-grid-components';
	}

	public function get_breadcrumbs(){
		return ['WooCommerce', 'Catalog Settings'];
	}

	public function controls(){

		/**
		 * GRID TOP TEXT
		 */

		$this->start_controls_accordion([
			'label'  => esc_html__( 'Top Text', 'rey-core' ),
			'open' => true,
		]);

			$this->add_title( '', [
				'description' => esc_html__('Control the display of the short text above the product grid.', 'rey-core'),
				'separator' => 'none',
			]);

			$this->add_control( [
				'type' => 'toggle',
				'settings' => 'loop_product_count',
				'label' => esc_html__('Enable product count text', 'rey-core'),
				'default' => true,
			]);

			$this->start_controls_group( [
				'active_callback' => [
					[
						'setting' => 'loop_product_count',
						'operator' => '==',
						'value' => true,
					],
				],
			]);

				$this->add_control( [
					'type' => 'text',
					'settings' => 'loop_product_count__text',
					'label'       => esc_html__('Custom product count text', 'rey-core'),
					'help' => [
						__('You can use variables such as {{FIRST}} , {{LAST}}, {{TOTAL}}.', 'rey-core')
					],
					'default' => '',
					'css_class' => '--block-label',
					'input_attrs' => [
						'placeholder' => esc_html__('eg: Showing {{FIRST}}–{{LAST}} of {{TOTAL}} results', 'rey-core'),
					]
				]);

			$this->end_controls_group();

		$this->end_controls_accordion();


		/**
		 * PAGINATION
		 */

		$this->start_controls_accordion([
			'label'  => esc_html__( 'Pagination', 'rey-core' ),
		]);

			$this->add_control( [
				'type'        => 'select',
				'settings'    => 'loop_pagination',
				'rey_preset' => 'catalog',
				'label'       => esc_html__( 'Pagination type', 'rey-core' ),
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

			$this->start_controls_group( [
				'label'    => esc_html__( 'Ajax Pagination Options', 'rey-core' ),
				'active_callback' => [
					[
						'setting'  => 'loop_pagination',
						'operator' => 'in',
						'value'    => ['load-more', 'infinite'],
					],
				],
			]);

			$this->add_control( [
				'type'        => 'toggle',
				'settings'    => 'loop_pagination_ajax_counter',
				'rey_preset' => 'catalog',
				'label'       => esc_html__( 'Add counter to button.', 'rey-core' ),
				'default'     => false,
			] );

			$this->add_control( [
				'type'     => 'text',
				'settings' => 'loop_pagination_ajax_text',
				'label'    => esc_html__( 'Button Text', 'rey-core' ),
				'input_attrs' => [
					'placeholder'  => esc_html__( 'eg: SHOW MORE', 'rey-core' ),
				],
				'default' => '',
				'active_callback' => [
					[
						'setting'  => 'loop_pagination',
						'operator' => 'in',
						'value'    => ['load-more', 'infinite'],
					],
				],
			] );

			$this->add_control( [
				'type'     => 'text',
				'settings' => 'loop_pagination_ajax_end_text',
				'label'    => esc_html__( 'Button End Text', 'rey-core' ),
				'input_attrs' => [
					'placeholder'  => esc_html__( 'eg: END', 'rey-core' ),
				],
				'default' => '',
				'active_callback' => [
					[
						'setting'  => 'loop_pagination',
						'operator' => 'in',
						'value'    => ['load-more', 'infinite'],
					],
				],
			] );

			$this->add_control( [
				'type'        => 'select',
				'settings'    => 'loop_pagination_btn_style',
				'label'       => esc_html__( 'Button Style', 'rey-core' ),
				'default'     => 'btn-line-active',
				'choices'     => [
					'btn-line' => esc_html__( 'Underlined on hover', 'rey-core' ),
					'btn-line-active' => esc_html__( 'Underlined', 'rey-core' ),
					'btn-primary' => esc_html__( 'Regular', 'rey-core' ),
					'btn-primary-outline' => esc_html__( 'Regular outline', 'rey-core' ),
					'btn-secondary' => esc_html__( 'Secondary', 'rey-core' ),
					'btn-secondary-outline' => esc_html__( 'Secondary Outline', 'rey-core' ),
				],
				'active_callback' => [
					[
						'setting'  => 'loop_pagination',
						'operator' => 'in',
						'value'    => ['load-more', 'infinite'],
					],
				],
			] );

			$this->add_control( [
				'type'        => 'toggle',
				'settings'    => 'loop_pagination_ajax_history',
				'label'       => esc_html__( 'Add history marker for URLs.', 'rey-core' ),
				'help' => [
					__('If enabled, each time a new batch of products is loaded, the URL will change like in pagination mode.', 'rey-core')
				],
				'default'     => true,
				'active_callback' => [
					[
						'setting'  => 'loop_pagination',
						'operator' => 'in',
						'value'    => ['load-more', 'infinite'],
					],
				],
			] );

			$this->add_control( [
				'type'        => 'toggle',
				'settings'    => 'loop_pagination_cache_products',
				'label'       => esc_html__( 'Cache loaded products', 'rey-core' ),
				'help' => [
					__('If enabled, the loaded products will be cached in the session so that each time when accessing a product page and returning, all the previous products will be loaded back. Useful when going back to grid from a product page. It will keep the scroll position.', 'rey-core')
				],
				'default'     => true,
				'active_callback' => [
					[
						'setting'  => 'loop_pagination',
						'operator' => 'in',
						'value'    => ['load-more', 'infinite'],
					],
				],
			] );

			$this->end_controls_group();

		$this->end_controls_accordion();

		$this->add_section_marker('components');

		/**
		 * SIDEBAR
		 */

		$this->start_controls_accordion([
			'label'  => esc_html__( 'Sidebar', 'rey-core' ),
			'group_id' => 'shop_sidebar_acc'
		]);

			$this->add_control( [
				'type'        => 'select',
				'settings'    => 'catalog_sidebar_position',
				'label'       => esc_html__( 'Sidebar Position', 'rey-core' ),
				'help' => [
					__('Select the placement of the Shop Sidebar or disable it. Default is right.', 'rey-core')
				],
				'default'     => 'right',
				'choices'     => [
					'right' => esc_html__( 'Right', 'rey-core' ),
					'left' => esc_html__( 'Left', 'rey-core' ),
					'disabled' => esc_html__( 'Disabled', 'rey-core' ),
				],
			] );

			$this->add_control( [
				'type'        => 'rey-number',
				'settings'    => 'shop_sidebar_size',
				'label'       => esc_html__( 'Sidebar Size', 'rey-core' ) . ' (%)',
				'default'     => 16,
				'choices'     => [
					'min'  => 10,
					'max'  => 60,
					'step' => 1,
				],
				'transport'   => 'auto',
				'output'      		=> [
					[
						'element'  		=> ':root',
						'property' 		=> '--woocommerce-sidebar-size',
						'units'    		=> '%',
					],
				],
			] );

			$this->add_control( [
				'type'        => 'rey-number',
				'settings'    => 'shop_sidebar_spacing',
				'label'       => esc_html__( 'Widget Spacing', 'rey-core' ) . ' (px)',
				'default'     => '',
				'choices'     => [
					// 'min'  => 0,
					'max'  => 150,
					'step' => 1,
				],
				'transport'   => 'auto',
				'output'      		=> [
					[
						'element'  		=> ':root',
						'property' 		=> '--woocommerce-sidebar-widget-spacing',
						'units'    		=> 'px',
					],
				],
			] );

			$this->add_control( [
				'type'        => 'toggle',
				'settings'    => 'sidebar_shop__sticky',
				'label'       => esc_html__( 'Sticky on Scroll', 'rey-core' ),
				'help' => [
					__('Enable to have the sidebar stick to top when scrolling the page.', 'rey-core')
				],
				'default'     => false,
			] );

			$this->add_control( [
				'type'        => 'toggle',
				'settings'    => 'sidebar_shop__toggle__enable',
				'label'       => esc_html__( 'Toggable Widgets', 'rey-core' ),
				'help' => [
					__('This will make the widgets inside the sidebars toggable.', 'rey-core')
				],
				'default'     => false,
			] );

			$this->start_controls_group( [
				'label'    => esc_html__( 'Toggable widgets options', 'rey-core' ),
				'active_callback' => [
					[
						'setting'  => 'sidebar_shop__toggle__enable',
						'operator' => '==',
						'value'    => true,
					],
				],
			]);

			$this->add_control( [
				'type'        => 'select',
				'settings'    => 'sidebar_shop__toggle__status',
				'label'       => esc_html__( 'Status', 'rey-core' ),
				'default'     => 'all',
				'choices'     => [
					'all' => esc_html_x('All closed', 'Customizer option choice', 'rey-core'),
					'except_first' => esc_html_x('All closed except first', 'Customizer option choice', 'rey-core')
				],
			] );

			$this->add_control( [
				'type'        => 'select',
				'settings'    => 'sidebar_shop__toggle__indicator',
				'label'       => esc_html__( 'Indicator type', 'rey-core' ),
				'default'     => 'plusminus',
				'choices'     => [
					'plusminus' => esc_html__( 'Plus Minus', 'rey-core' ),
					'arrow' => esc_html__( 'Arrow', 'rey-core' ),
				],
				'active_callback' => [
					[
						'setting'  => 'sidebar_shop__toggle__enable',
						'operator' => '==',
						'value'    => true,
					],
				],
			] );

			$this->add_control( [
				'type'        => 'text',
				'settings'    => 'sidebar_shop__toggle__exclude',
				'label'       => esc_html__( 'Exclude IDs', 'rey-core' ),
				'help' => [
					__('Add a list of css widget ids, separated by comma.', 'rey-core')
				],
				'default'     => '',
				'input_attrs'     => [
					'placeholder' => esc_html__('eg: #some_widget', 'rey-core'),
				],
				'active_callback' => [
					[
						'setting'  => 'sidebar_shop__toggle__enable',
						'operator' => '==',
						'value'    => true,
					],
				],
			] );

			$this->end_controls_group();

			$this->add_control( [
				'type'        => 'toggle',
				'settings'    => 'sidebar_title_styles',
				'label'       => esc_html__( 'Custom Titles Styles', 'rey-core' ),
				'default'     => false,
			] );

			$this->start_controls_group( [
				'label'    => esc_html__( 'Sidebar title options', 'rey-core' ),
				'active_callback' => [
					[
						'setting'  => 'sidebar_title_styles',
						'operator' => '==',
						'value'    => true,
					],
				],
			]);

			$this->add_control( [
				'type'        => 'typography',
				'settings'    => 'sidebar_title_typo',
				'label'       => esc_attr__('Sidebar title', 'rey-core'),
				'default'     => [
					'font-family'      => '',
					'font-size'      => '',
					'line-height'    => '',
					'letter-spacing' => '',
					'font-weight' => '',
					'text-transform' => '',
					'variant' => '',
					'color' => '',
				],
				'output' => [
					[
						'element' => '.rey-ecommSidebar .widget-title',
					],
				],
				'load_choices' => true,
				'transport' => 'auto',
				'responsive' => true,
			]);

			$this->add_control( [
				'type'        => 'select',
				'settings'    => 'sidebar_title_layouts',
				'label'       => esc_html__( 'Sidebar Title styles', 'rey-core' ),
				'default'     => '',
				'choices'     => [
					'' => esc_html__( 'Default', 'rey-core' ),
					'bline' => esc_html__( 'Bottom Lined', 'rey-core' ),
					'sline' => esc_html__( 'Side Line', 'rey-core' ),
				],
				'active_callback' => [
					[
						'setting'  => 'sidebar_title_styles',
						'operator' => '==',
						'value'    => true,
					],
				],
			] );

			$this->end_controls_group();


		$this->end_controls_accordion();

		$this->add_section_marker('sidebar');


	}

}
