<?php
namespace ReyCore\Modules\RelatedProducts;

if ( ! defined( 'ABSPATH' ) ) exit;

class Customizer {

	public function __construct(){
		add_action('reycore/customizer/section=woo-product-page-components', [$this, 'add_controls']);
	}

	public function add_controls( $section ){

		$section->add_title( esc_html__('Related products', 'rey-core'), [
				'description' => esc_html__( 'These options will extend the product page\'s built-in Related products block.', 'rey-core' )
			]
		);

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'single_product_page_related',
			'label'       => esc_html__( 'Display section', 'rey-core' ),
			'default'     => true,
		] );

		$section->add_control( [
			'type'     => 'text',
			'settings' => 'single_product_page_related_title',
			'label'    => esc_html__('Title', 'rey-core'),
			'default'  => '',
			'active_callback' => [
				[
					'setting'  => 'single_product_page_related',
					'operator' => '==',
					'value'    => true,
				],
			],
			'input_attrs' => [
				'placeholder' => esc_html__('eg: Related products', 'rey-core')
			]
		] );

		$section->add_control( [
			'type'        => 'rey-number',
			'settings'    => 'single_product_page_related_columns',
			'label'       => esc_html__('Products per row', 'rey-core'),
			'default'     => '',
			'responsive' => true,
			'choices'     => [
				'min'  => 1,
				'max'  => 6,
				'step' => 1,
			],
			'active_callback' => [
				[
					'setting'  => 'single_product_page_related',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$section->add_control( [
			'type'        => 'rey-number',
			'settings'    => 'single_product_page_related_per_page',
			'label'       => esc_html__('Limit', 'rey-core'),
			'help' => [
				__('Limit the number of products to show.', 'rey-core')
			],
			'default'     => '',
			'choices'     => [
				// 'min'  => 1,
				'max'  => 20,
				'step' => 1,
			],
			'active_callback' => [
				[
					'setting'  => 'single_product_page_related',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'single_product_page_related_carousel',
			'label'       => esc_html__('Enable Carousel Mode', 'rey-core'),
			'help' => [
				__('Show related products as a carousel.', 'rey-core')
			],
			'default'     => false,
			'active_callback' => [
				[
					'setting'  => 'single_product_page_related',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'single_product_page_related_custom',
			'label'       => esc_html__('Enable Manual Products Selection', 'rey-core'),
			'help' => [
				__('Enabling this option will add a custom input into the products pages in admin, in the Linked products tab, to select custom products.', 'rey-core')
			],
			'default'     => false,
			'active_callback' => [
				[
					'setting'  => 'single_product_page_related',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'single_product_page_related_custom_replace',
			'label'       => esc_html__('Replace Default Products', 'rey-core'),
			'help' => [
				__('Enable if you want to replace the default Related products with the ones you manually pick in the Linked products tab.', 'rey-core')
			],
			'default'     => true,
			'active_callback' => [
				[
					'setting'  => 'single_product_page_related',
					'operator' => '==',
					'value'    => true,
				],
				[
					'setting'  => 'single_product_page_related_custom',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'single_product_page_related_same_category',
			'label'       => esc_html__('From Same Categories', 'rey-core'),
			'help' => [
				__('This option will modify the Related products query to only show products from the same categories as the main product. If "Manually Selection" is enabled, it will fallback to these products.', 'rey-core')
			],
			'default'     => false,
			'active_callback' => [
				[
					'setting'  => 'single_product_page_related',
					'operator' => '==',
					'value'    => true,
				],
				// [
				// 	'setting'  => 'single_product_page_related_custom',
				// 	'operator' => '!=',
				// 	'value'    => true,
				// ],
			],
		] );

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'single_product_page_related_hide_outofstock',
			'label'       => esc_html__('Hide Out of stock', 'rey-core'),
			'help' => [
				__('This option will prevent showing out of stock products in the Related Products.', 'rey-core')
			],
			'default'     => false,
			'active_callback' => [
				[
					'setting'  => 'single_product_page_related',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'single_product_page_related_lazy',
			'label'       => esc_html__('Lazy load', 'rey-core'),
			'help' => [
				__('This option will make the Related products load dynamically when scrolling into view. Better for page performance.', 'rey-core')
			],
			'default'     => true,
			'active_callback' => [
				[
					'setting'  => 'single_product_page_related',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'single_product_page_related_upsells',
			'label'       => esc_html__('Same settings for Up-Sells block', 'rey-core'),
			'help' => [
				__('Enable this option to copy the same settings for Up-Sells (eg: carousel, products per row, etc.).', 'rey-core')
			],
			'default'     => true,
			'active_callback' => [
				[
					'setting'  => 'single_product_page_related',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

	}
}
