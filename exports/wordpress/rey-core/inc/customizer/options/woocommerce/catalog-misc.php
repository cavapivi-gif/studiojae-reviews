<?php
namespace ReyCore\Customizer\Options\Woocommerce;

if ( ! defined( 'ABSPATH' ) ) exit;

use \ReyCore\Customizer\Controls;

class CatalogMisc extends \ReyCore\Customizer\SectionsBase {

	public static function get_id(){
		return 'woo-catalog-misc';
	}

	public function get_title(){
		return esc_html__('Misc. options', 'rey-core');
	}

	public function get_priority(){
		return 50;
	}

	public function get_icon(){
		return 'woo-catalog-misc';
	}

	public function get_breadcrumbs(){
		return ['WooCommerce', 'Catalog Settings'];
	}

	public function help_link(){
		return reycore__support_url('kb/customizer-woocommerce/#product-catalog-miscellaneous');
	}

	public function controls(){

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'shop_catalog_page_exclude',
			'label'       => __('Exclude categories from Shop Page', 'rey-core'),
			'help' => [
				__('Choose to exclude products of specific categories, from the Shop page.', 'rey-core')
			],
			'default'     => '',
			// 'priority'    => 5,
			'multiple'    => 100,
			'query_args' => [
				'type' => 'terms',
				'taxonomy' => 'product_cat',
			],
			// 'separator' => 'before',
			'css_class' => '--block-label',
		] );

		$this->add_separator();

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'fix_variable_product_prices',
			'label'       => esc_html__( 'Disable range on Sale Variations', 'rey-core' ),
			'help' => [
				esc_html__( 'If a Variation is on sale, and its price is different from the others, this option will disable the range showing the lowest price.', 'rey-core' )
			],
			'default'     => false,
		] );

		$this->add_separator();

		$this->add_control( [
			'type'        => 'text',
			'settings'    => 'custom_price_range',
			'label'       => esc_html__( 'Variable prices range', 'rey-core' ),
			'help' => [
				esc_html__( 'If you want to have a custom format for the variable products price range, you can add a prefix or suffix, or use {{min} or {{max}} placeholders.', 'rey-core' )
			],
			'default'     => '',
			'input_attrs'     => [
				'placeholder' => esc_html__('eg: {{min} - {{max}}', 'rey-core'),
			],
		] );

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'archive__title_back',
			'label'       => __('Enable back arrow in Archives', 'rey-core'),
			'help' => [
				__('If enabled, a back arrow will be displayed in the left side of the product archive.', 'rey-core')
			],
			'default'     => false,
			// 'priority'    => 5,
			'separator' => 'before',
		] );

		$this->start_controls_group( [
			'label'    => esc_html__( 'Back button options', 'rey-core' ),
			'active_callback' => [
				[
					'setting'  => 'archive__title_back',
					'operator' => '==',
					'value'    => true,
					],
			],
		]);

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'archive__back_behaviour',
			'label'       => esc_html__( 'Behaviour', 'rey-core' ),
			'default'     => 'parent',
			'choices'     => [
				'parent' => esc_html__( 'Back to parent', 'rey-core' ),
				'shop' => esc_html__( 'Back to shop page', 'rey-core' ),
				'page' => esc_html__( 'Back to previous page', 'rey-core' ),
			],
			// 'priority'    => 5,
		] );

		$this->end_controls_group();

	}
}
