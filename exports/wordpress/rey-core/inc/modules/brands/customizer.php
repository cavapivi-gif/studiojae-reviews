<?php
namespace ReyCore\Modules\Brands;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Customizer extends \ReyCore\Customizer\SectionsBase {

	public static function get_id(){
		return 'brands';
	}

	public function get_title(){
		return esc_html__('Brands', 'rey-core');
	}

	public function get_priority(){
		return 150;
	}

	public function get_icon(){
		return 'woo-brands';
	}

	public function get_breadcrumbs(){
		return ['WooCommerce', 'Modules'];
	}

	public function help_link(){
		return reycore__support_url('kb/how-to-create-product-brands/');
	}

	public function controls(){

		$brand = Base::instance()->get_brand_attribute();

		$brands_choices = \ReyCore\Customizer\Helper::wc_taxonomies([
			'exclude' => [
				'product_cat',
				'product_tag'
			],
			'label_formatting' => '%s (ID: %s)'
		]);

		$brands_choices[''] = esc_html__('None', 'rey-core');

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'brand_taxonomy',
			'label'       => esc_html__( 'Brand Attribute', 'rey-core' ),
			'help' => [
				__('Please select the Product Attribute you want to assign for Brands.', 'rey-core')
			],
			'default'     => $brand,
			'choices'     => $brands_choices,
		] );

		$this->add_title( '', [
			'description' => sprintf(__('Please select one of the existing Attributes or create <a href="%s" target="_blank">new one here</a>, and head back to select it afterwards in the list above.', 'rey-core'), admin_url('edit.php?post_type=product&page=product_attributes') ),
			'active_callback' => [
				[
					'setting'  => 'brand_taxonomy',
					'operator' => '==',
					'value'    => '',
				]
			],
		]);

		$conditions = [
			'setting'  => 'brand_taxonomy',
			'operator' => '!=',
			'value'    => '',
		];

		$this->add_title( esc_html__('Catalog display', 'rey-core'), [
			'active_callback' => [
				$conditions
			],
		]);

			$this->add_control( [
				'type'        => 'toggle',
				'settings'    => 'loop_show_brads',
				'label'       => esc_html__( 'Enable Link', 'rey-core' ),
				'default'     => '1',
				'active_callback' => [
					$conditions
				],
			] );


		$this->add_title( esc_html__('Product Page', 'rey-core'), [
			'active_callback' => [
				$conditions
			],
		] );

			$this->add_control( [
				'type'        => 'select',
				'settings'    => 'brands__pdp',
				'label'       => esc_html__( 'Brand display as', 'rey-core' ),
				'default'     => 'link',
				'choices'     => [
					'none' => esc_html_x('Disabled', 'Customizer control choice', 'rey-core'),
					'link' => esc_html_x('Link', 'Customizer control choice', 'rey-core'),
					'image' => esc_html_x('Image', 'Customizer control choice', 'rey-core'),
					'both' => esc_html_x('Both Image and Link', 'Customizer control choice', 'rey-core')
				],
				'active_callback' => [
					$conditions
				],
			] );

			$this->add_control( [
				'type'        => 'rey-number',
				'settings'    => 'brands__pdp_image_size',
				'label'       => esc_html__( 'Brand image size', 'rey-core' ) . ' (px)',
				'default'     => 80,
				'choices'     => [
					'min'  => 5,
					'max'  => 400,
					'step' => 1,
				],
				'active_callback' => [
					$conditions,
					[
						'setting'  => 'brands__pdp',
						'operator' => 'in',
						'value'    => ['image', 'both'],
					],
				],
				'transport'   => 'auto',
				'output'      		=> [
					[
						'element'  		=> ':root',
						'property' 		=> '--pdp-brand-image-size',
						'units'    		=> 'px',
						'media_query' => '@media (min-width: 992px)'
					],
				],
			] );

			$this->add_control( [
				'type'        => 'select',
				'settings'    => 'brands__pdp_pos',
				'label'       => esc_html__( 'Position', 'rey-core' ),
				'default'     => 'after',
				'choices'     => [
					'before' => esc_html_x('Before title', 'Customizer control choice', 'rey-core'),
					'after' => esc_html_x('After title', 'Customizer control choice', 'rey-core'),
				],
				'active_callback' => [
					$conditions,
					[
						'setting'  => 'brands__pdp',
						'operator' => '!=',
						'value'    => 'none',
					],
				],
			] );

		$this->add_title( esc_html__('Misc.', 'rey-core'), [
			'active_callback' => [
				$conditions
			],
		]  );

			$this->add_control( [
				'type'        => 'toggle',
				'settings'    => 'show_brads_cart',
				'label'       => esc_html__( 'Show in Cart page', 'rey-core' ),
				'default'     => false,
				'active_callback' => [
					$conditions
				],
			] );

			$this->add_control( [
				'type'        => 'toggle',
				'settings'    => 'show_brads_cart_panel',
				'label'       => esc_html__( 'Show in Cart panel', 'rey-core' ),
				'default'     => false,
				'active_callback' => [
					$conditions
				],
			] );

			$this->add_control( [
				'type'        => 'toggle',
				'settings'    => 'show_brads_checkout',
				'label'       => esc_html__( 'Show in Checkout', 'rey-core' ),
				'default'     => false,
				'active_callback' => [
					$conditions
				],
			] );

			$this->add_control( [
				'type'        => 'toggle',
				'settings'    => 'show_brads_order',
				'label'       => esc_html__( 'Show in Order', 'rey-core' ),
				'default'     => false,
				'active_callback' => [
					$conditions
				],
			] );

	}

}
