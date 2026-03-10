<?php
namespace ReyCore\Customizer\Options\Woocommerce;

if ( ! defined( 'ABSPATH' ) ) exit;

use \ReyCore\Customizer\Controls;

class CatalogGrid extends \ReyCore\Customizer\SectionsBase {

	public static function get_id(){
		return 'woo-catalog-grid';
	}

	public function get_title(){
		return esc_html__('Grid Settings', 'rey-core');
	}

	public function get_title_before(){
		return esc_html__('CATALOG SETTINGS', 'rey-core');
	}

	public function get_priority(){
		return 10;
	}

	public function get_icon(){
		return 'woo-grid-settings';
	}

	public function get_breadcrumbs(){
		return ['WooCommerce', 'Catalog Settings'];
	}

	public function help_link(){
		return reycore__support_url('kb/customizer-woocommerce/#product-catalog-layout');
	}

	public function customize_register(){

		global $wp_customize;

		if( $woocommerce_catalog_columns = $wp_customize->get_control( 'woocommerce_catalog_columns' ) ){
			$woocommerce_catalog_columns->priority = 11;
			$woocommerce_catalog_columns->section = self::get_id();
		}

		if( $woocommerce_catalog_rows = $wp_customize->get_control( 'woocommerce_catalog_rows' ) ){
			$woocommerce_catalog_rows->priority = 12;
			$woocommerce_catalog_rows->section = self::get_id();
		}

		// Ordering
		if( $woocommerce_default_catalog_orderby = $wp_customize->get_control( 'woocommerce_default_catalog_orderby' ) ){
			$woocommerce_default_catalog_orderby->priority = 16;
			$woocommerce_default_catalog_orderby->section = self::get_id();
			$woocommerce_default_catalog_orderby->label = '';
			$woocommerce_default_catalog_orderby->description = '';
		}

		// Catalog display
		if( $woocommerce_shop_page_display = $wp_customize->get_control( 'woocommerce_shop_page_display' ) ){
			$woocommerce_shop_page_display->priority = 18;
			$woocommerce_shop_page_display->section = self::get_id();
		}

		if( $woocommerce_category_archive_display = $wp_customize->get_control( 'woocommerce_category_archive_display' ) ){
			$woocommerce_category_archive_display->priority = 18;
			$woocommerce_category_archive_display->section = self::get_id();
		}

	}

	public function controls(){

		/* ------------------------------------ Grid options ------------------------------------ */

		$this->add_title( esc_html__('Grid settings', 'rey-core'), [
			'priority'  => 5,
			'separator' => 'none',
		]);

		$this->add_control( [
			'type'       => 'select',
			'settings'   => 'loop_grid_layout',
			'rey_preset' => 'catalog',
			'label'      => esc_html__( 'Grid Layout', 'rey-core' ),
			'default'    => 'default',
			'choices'    => [
				'default' => esc_html__( 'Default', 'rey-core' ),
				'masonry' => esc_html__( 'Masonry', 'rey-core' ),
				'masonry2' => esc_html__( 'Masonry Zig-Zag', 'rey-core' ),
				'scattered' => esc_html__( 'Scattered', 'rey-core' ),
				'scattered2' => esc_html__( 'Scattered Mixed & Random', 'rey-core' ),
				'metro' => esc_html__( 'Squares (metro)', 'rey-core' ),
			],
			'priority'    => 5,
		] );

		$this->add_control( [
			'type'       => 'rey-number',
			'settings'   => 'loop_gap_size_v2',
			'rey_preset' => 'catalog',
			'label'      => esc_html__( 'Gap size', 'rey-core' ) . ' (px)',
			'choices'     => [
				'min'  => 0,
				'max'  => 200,
				'step' => 1,
			],
			'default'    => 30,
			'default_tablet' => 10,
			'default_mobile' => 10,
			'responsive' => true,
			'priority'   => 5,
			'output'     => [
				[
					'element'  => ':root',
					'property' => '--woocommerce-products-gutter',
					'units'    => 'px',
				],
			],
		] );

		$this->add_title( esc_html__('Columns', 'rey-core'), [
			'priority' => 10,
		]);

		$this->add_control( [
			'type'        => 'rey-number',
			'settings'    => 'woocommerce_catalog_columns_tablet',
			'label'       => esc_html__('Products per row (tablet)', 'rey-core'),
			'default'     => 2,
			'priority'    => 13,
			'choices'     => [
				'min'  => 1,
				'max'  => 4,
				'step' => 1,
			],
		] );

		$this->add_control( [
			'type'        => 'rey-number',
			'settings'    => 'woocommerce_catalog_columns_mobile',
			'label'       => esc_html__('Products per row (mobile)', 'rey-core'),
			'default'     => 2,
			'priority'    => 13,
			'choices'     => [
				'min'  => 1,
				'max'  => 2,
				'step' => 1,
			],
		] );

		$this->add_notice([
			'default'     => __('"Products per row (mobile)" control will not work because the "Wrapped" product catalog skin only supports a single (1) column.', 'rey-core'),
			'priority'        => 13,
			'active_callback' => [
				[
					'setting'  => 'loop_skin',
					'operator' => '==',
					'value'    => 'wrapped',
				],
				[
					'setting'  => 'woocommerce_catalog_columns_mobile',
					'operator' => '!=',
					'value'    => '1',
				],
			],
		] );

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'woocommerce_catalog_mobile_listview',
			'rey_preset' => 'catalog',
			'label'       => esc_html__( 'Enable list view on mobiles', 'rey-core' ),
			'help' => [
				__('This will make the mobile view more compact. The thumbnails and content will stay side by side.', 'rey-core')
			],
			'default'     => false,
			'priority'    => 13,
			'active_callback' => [
				[
					'setting'  => 'woocommerce_catalog_columns_mobile',
					'operator' => '==',
					'value'    => '1',
				],
			],
		] );


		$this->add_title( esc_html__('Product Sorting', 'rey-core'), [
			'priority' => 15,
			'description' => esc_html__('How should products be sorted in the catalog by default?', 'rey-core'),
		]);

		// Ordering Control (16)

		/* ------------------------------------ DISPLAY SETTINGS ------------------------------------ */

		$this->add_title( esc_html__('Display Settings', 'rey-core'), [
			'priority' => 17,
		]);

		// Display controls (18)

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'shop_display_categories__enable',
			'label'       => __('Enable Titles before/after Categories', 'rey-core'),
			'help' => [
				__('Only available if both "Categories & Products" is selected. This will display heading titles before and after the categories.', 'rey-core')
			],
			'default'     => false,
			'priority'    => 19,
		] );

		$this->add_control( [
			'type'        => 'text',
			'settings'    => 'shop_display_categories__title_cat',
			'label'       => esc_html__( 'Category list title', 'rey-core' ),
			'default'     => esc_html__('Shop by Category', 'rey-core'),
			'active_callback' => [
				[
					'setting'  => 'shop_display_categories__enable',
					'operator' => '==',
					'value'    => true,
				],
			],
			'priority'    => 19,
		] );

		$this->add_control( [
			'type'        => 'text',
			'settings'    => 'shop_display_categories__title_prod',
			'label'       => esc_html__( 'Product list title', 'rey-core' ),
			'default'     => esc_html__('Shop All %s', 'rey-core'),
			'active_callback' => [
				[
					'setting'  => 'shop_display_categories__enable',
					'operator' => '==',
					'value'    => true,
				],
			],
			'priority'    => 19,
		] );


		/* ------------------------------------  ------------------------------------ */

		$this->add_title( esc_html__('EMPTY PRODUCTS LIST', 'rey-core'), [
			'description' => esc_html__('Control the contents of the product list when it\'s empty.', 'rey-core'),
		]);

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'loop_empty__gs',
			'label'       => esc_html__( 'Show Global Section', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'' => '- None -'
			],
			'ajax_choices' => 'get_global_sections',
			'edit_preview' => true,
		] );

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'loop_empty__mode',
			'label'       => esc_html__( 'Mode', 'rey-core' ),
			'default'     => 'overwrite',
			'choices'     => [
				'overwrite' => esc_html__( 'Overwrite Content', 'rey-core' ),
				'before' => esc_html__( 'Add Before', 'rey-core' ),
				'after' => esc_html__( 'Add After', 'rey-core' ),
			],
			'active_callback' => [
				[
					'setting'  => 'loop_empty__gs',
					'operator' => '!=',
					'value'    => '',
				],
			],
		] );

		/*

		** Wait for automatic sync demo control presets.

		$this->add_title( esc_html__('MISC.', 'rey-core') );


		$demos = \ReyCore\Customizer\Helper::demo_presets();
		$choices = [ '' => esc_html__( 'Default', 'rey-core' ) ];
		$presets = [];

		foreach ($demos as $key => $demo) {
			$choices[$key] = $demo['title'];
			$presets[$key]['settings'] = $demo['settings']['catalog'];
		}

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'wc_catalog_layout_presets',
			'label'       => esc_html__( 'Layout Presets', 'rey-core' ),
			'description' => esc_html__( 'These are product catalog layout presets from each demo.', 'rey-core' ),
			'default'     => '',
			'choices'     => $choices,
			'preset' => $presets,
		] );

		*/


	}

}
