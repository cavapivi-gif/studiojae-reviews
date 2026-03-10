<?php
namespace ReyCore\Modules\MiniCartExtraProducts;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Customizer
{
	public function __construct()
	{
		add_action( 'reycore/customizer/section=header-mini-cart/marker=after_crosssells', [$this, 'add_controls'], 20);
	}

	function add_controls( $section ){

		$section->start_controls_accordion([
			'label'  => esc_html__( 'Extra Products Vertical List', 'rey-core' ),
		]);

			$section->add_control( [
				'type'        => 'toggle',
				'settings'    => 'header_cart__extra_products',
				'label'       => esc_html__( 'Show products', 'rey-core' ),
				'help' => [
					esc_html__( 'This will show up a vertical list of products on the side of the Cart panel.', 'rey-core')
				],
				'default'     => false,
			] );

			$section->add_control( [
				'type'        => 'text',
				'settings'    => 'header_cart__extra_products_title',
				'label'       => esc_html__( 'Title', 'rey-core' ),
				'default'     => '',
				'input_attrs'     => [
					'placeholder' => esc_html__('ex: You might like..', 'rey-core'),
				],
				'active_callback' => [
					[
						'setting'  => 'header_cart__extra_products',
						'operator' => '==',
						'value'    => true,
					],
				],
			] );

			$section->add_control( [
				'type'        => 'rey-number',
				'settings'    => 'header_cart__extra_products_limit',
				'label'       => esc_html__( 'Number of products', 'rey-core' ),
				'default'     => 12,
				'choices'     => [
					'min'  => 1,
					'max'  => 20,
					'step' => 1,
				],
				'active_callback' => [
					[
						'setting'  => 'header_cart__extra_products',
						'operator' => '==',
						'value'    => true,
					],
				],
			] );

			$section->add_control( [
				'type'        => 'select',
				'settings'    => 'header_cart__extra_products_type',
				'label'       => esc_html__( 'Type of products', 'rey-core' ),
				'default'     => 'latest',
				'choices'     => [
					'latest' => esc_html__( 'Latest', 'rey-core' ),
					'bestsellers' => esc_html__( 'Bestsellers', 'rey-core' ),
					'sales' => esc_html__( 'Sales', 'rey-core' ),
					'wishlist' => esc_html__( 'Wishlist', 'rey-core' ),
					'cross-sells' => esc_html__( 'Cross-sells', 'rey-core' ),
					'manual' => esc_html__( 'Manual Selection', 'rey-core' ),
				],
				'active_callback' => [
					[
						'setting'  => 'header_cart__extra_products',
						'operator' => '==',
						'value'    => true,
					],
				],
			] );

			$section->add_control( [
				'type'        => 'select',
				'settings'    => 'header_cart__extra_products_manual',
				'label'       => esc_html__( 'Pick products', 'rey-core' ),
				'default'     => [],
				'multiple'    => 20,
				'query_args' => [
					'type' => 'posts',
					'post_type' => 'product',
				],
				'active_callback' => [
					[
						'setting'  => 'header_cart__extra_products',
						'operator' => '==',
						'value'    => true,
					],
					[
						'setting'  => 'header_cart__extra_products_type',
						'operator' => '==',
						'value'    => 'manual',
					],
				],
			] );

		$section->end_controls_accordion();

	}

}
