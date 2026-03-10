<?php
namespace ReyCore\Modules\ProductPageAfterContent;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Customizer {

	public function __construct(){
		add_action('reycore/customizer/section=woo-product-page-components', [$this, 'controls'], 30);
	}

	public function controls( $section ){

		$section->add_title( esc_html__('Global Sections', 'rey-core'), [
			'description' => sprintf(__('Add global sections into product page. Read more about <a href="%s" target="_blank">Global Sections</a>.', 'rey-core'), reycore__support_url('kb/what-exactly-are-global-sections/') ),
		]);

		$section->add_control( [
			'type'        => 'select',
			'settings'    => 'product_content_after_summary',
			'label'       => esc_html__( 'After product summary section', 'rey-core' ),
			'description' => __( 'Select a global section to append <strong>after product summary</strong> section which will be shown in all product pages.', 'rey-core' ),
			'default'     => 'none',
			'choices'     => [
				'none' => '- None -'
			],
			'ajax_choices' => 'get_global_sections',
			'edit_preview' => true,
		] );

		$section->add_control( [
			'type'        => 'select',
			'settings'    => 'product_content_after_content',
			'label'       => esc_html__( 'After content', 'rey-core' ),
			'description' => __( 'Select a global section to append <strong>after content end</strong> (after reviews) which will be shown in all product pages.', 'rey-core' ),
			'default'     => 'none',
			'choices'     => [
				'none' => '- None -'
			],
			'ajax_choices' => 'get_global_sections',
			'edit_preview' => true,
		] );

		$section->add_control( [
			'type'        => 'repeater',
			'settings'    => 'product_content_after_content_per_category',
			'label'       => esc_html__('"After" Content per Category / Attribute', 'rey-core'),
			'description' => __('Select generic global sections to be assigned in products that belong to a certain product category or attribute.', 'rey-core'),
			'row_label' => [
				'value' => esc_html__('Global Section', 'rey-core'),
				'type'  => 'field',
				'field' => 'categories',
			],
			'button_label' => esc_html__('New global section per category / attribute', 'rey-core'),
			'default'      => [],
			'fields' => [
				'gs' => [
					'type'        => 'select',
					'label'       => esc_html__('Select Global Section', 'rey-core'),
					'choices'     => \ReyCore\Customizer\Helper::global_sections('generic', ['' => esc_html__('- Select -', 'rey-core')]),
					'export' => 'post_id'
				],
				'position' => [
					'type'        => 'select',
					'label'       => esc_html__('Select Position', 'rey-core'),
					'choices'     => [
						'' => esc_html__('- Select -', 'rey-core'),
						'summary' => esc_html__('After Product Summary', 'rey-core'),
						'content' => esc_html__('After Product Content (reviews block)', 'rey-core')
					],
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
				'attributes' => [
					'type'        => 'select',
					'label'       => esc_html__('Attributes', 'rey-core'),
					'query_args' => [
						'type' => 'terms',
						'taxonomy' => 'all_attributes',
					],
					'multiple' => 100
				],
			],
		] );

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'product_content_after_content__before_reviews',
			'label'       => esc_html__( 'Place global section before Reviews block', 'rey-core' ),
			'default'     => false,
			'active_callback' => [
				[
					'setting'  => 'product_content_layout',
					'operator' => '==',
					'value'    => 'blocks',
				],
			],
		] );


	}
}
