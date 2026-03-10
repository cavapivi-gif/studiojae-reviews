<?php
namespace ReyCore\Customizer\Options\Cover;

if ( ! defined( 'ABSPATH' ) ) exit;

use \ReyCore\Customizer\Controls;

class Shop extends \ReyCore\Customizer\SectionsBase {

	public static function get_id(){
		return 'cover-shop';
	}

	public function get_title(){
		return esc_html__('WooCommerce (Shop)', 'rey-core');
	}

	public function get_priority(){
		return 10;
	}

	public function get_icon(){
		return 'cover-woocommerce';
	}

	public function can_load(){
		return class_exists('\WooCommerce');
	}

	public function help_link(){
		return reycore__support_url('kb/customizer-page-cover/#shop');
	}

	public function controls(){

		$global_sections = \ReyCore\Customizer\Helper::global_sections('cover', ['' => '- Select -']);

		$this->add_control( [
			'type'        => 'custom',
			'settings'    => 'cover_title__shop',
			'default'     => \ReyCore\Customizer\Options\Cover::get_main_desc(),
		] );

		// Shop Categories
		$this->add_title( esc_html__('Categories', 'rey-core'), [
			'description' => esc_html__('Select a page cover to display in product categories. You can always disable or change the Page Cover of a specific category, in its options.', 'rey-core'),
		]);

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'cover__shop_cat',
			'label'       => esc_html__( 'Select a Page Cover', 'rey-core' ),
			'default'     => 'no',
			'choices'     => [
				'no' => 'Disabled',
			],
			'ajax_choices' => [
				'action' => 'get_global_sections',
				'params' => [
					'type' => 'cover',
				]
			],
			'edit_preview' => true,
		] );

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'cover__shop_cat_inherit',
			'label'       => esc_html__( 'Subcategories inherit parent', 'rey-core' ),
			'help' => [
				esc_html__( 'If enabled, subcategories will inherit parent category cover.', 'rey-core' )
			],
			'default'     => false,
		] );

		$this->add_control( [
			'type'        => 'repeater',
			'settings'    => 'cover__shop_cat_custom',
			'label'       => esc_html__('Page Cover per Category', 'rey-core'),
			'description' => __('Assign Page Cover global sections per product categories.', 'rey-core'),
			'row_label' => [
				'value' => esc_html__('Page Cover', 'rey-core'),
				'type'  => 'field',
				'field' => 'categories',
			],
			'button_label' => esc_html__('New cover per category', 'rey-core'),
			'default'      => [],
			'fields' => [
				'gs' => [
					'type'        => 'select',
					'label'       => esc_html__('Select Global Section', 'rey-core'),
					'choices'     => $global_sections,
					'export' => 'post_id'
				],
				'categories' => [
					'type'        => 'select',
					'label'       => esc_html__('Categories', 'rey-core'),
					'query_args' => [
						'type' => 'terms',
						'taxonomy' => 'product_cat',
					],
					'multiple' => 100
				],
			],
		] );


		// Product Page
		$this->add_title( esc_html__('Product Page', 'rey-core'), [
			'description' => esc_html__('Select a page cover to display in product pages.', 'rey-core'),
		]);

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'cover__product_page',
			'label'       => esc_html__( 'Select a Page Cover', 'rey-core' ),
			'default'     => 'no',
			'choices'     => [
				'no' => 'Disabled',
			],
			'ajax_choices' => [
				'action' => 'get_global_sections',
				'params' => [
					'type' => 'cover',
				]
			],
			'edit_preview' => true,
		] );

		$this->add_control( [
			'type'        => 'repeater',
			'settings'    => 'cover__product_page_custom',
			'label'       => esc_html__('Product Page Cover based on Condition', 'rey-core'),
			'description' => __('Assign Page Cover global sections on product pages, based on their categories or tags.', 'rey-core'),
			'row_label' => [
				'value' => esc_html__('Page Cover', 'rey-core'),
				'type'  => 'text',
			],
			'button_label' => esc_html__('New cover per condition', 'rey-core'),
			'default'      => [],
			'fields' => [
				'gs' => [
					'type'        => 'select',
					'label'       => esc_html__('Select Global Section', 'rey-core'),
					'choices'     => $global_sections,
					'export' => 'post_id'
				],
				'categories' => [
					'type'        => 'select',
					'label'       => esc_html__('Categories', 'rey-core'),
					'query_args' => [
						'type' => 'terms',
						'taxonomy' => 'product_cat',
					],
					'multiple' => 100
				],
				'tags' => [
					'type'        => 'select',
					'label'       => esc_html__('Tags', 'rey-core'),
					'query_args' => [
						'type' => 'terms',
						'taxonomy' => 'product_tag',
					],
					'multiple' => 100
				],
			],
		] );


		// Shop Page
		$this->add_title( esc_html__('Shop Page', 'rey-core'), [
			'description' => esc_html__('These settings will apply on the default Shop page (Assigned in WooCommerce > Settings > Products > General).', 'rey-core'),
		]);

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'cover__shop_page',
			'label'       => esc_html__( 'Select a Page Cover', 'rey-core' ),
			'default'     => 'no',
			'choices'     => [
				'no' => 'Disabled',
			],
			'ajax_choices' => [
				'action' => 'get_global_sections',
				'params' => [
					'type' => 'cover',
				]
			],
			'edit_preview' => true,
		] );


		// Shop Tags
		$this->add_title( esc_html__('Tags', 'rey-core'), [
			'description' => esc_html__('Select a page cover to display in product tags. You can always disable or change the Page Cover of a specific tag, in its options.', 'rey-core'),
		]);

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'cover__shop_tag',
			'label'       => esc_html__( 'Select a Page Cover', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'' => '- Inherit -',
				'no' => 'Disabled',
			],
			'ajax_choices' => [
				'action' => 'get_global_sections',
				'params' => [
					'type' => 'cover',
				]
			],
			'edit_preview' => true,
		] );


	}
}
