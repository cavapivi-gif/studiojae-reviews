<?php
namespace ReyCore\Customizer\Options\Woocommerce;

if ( ! defined( 'ABSPATH' ) ) exit;

use \ReyCore\Customizer\Controls;

class Cart extends \ReyCore\Customizer\SectionsBase {

	public static function get_id(){
		return 'woo-cart';
	}

	public function get_title(){
		return esc_html__('Cart Page', 'rey-core');
	}

	public function get_title_before(){
		return esc_html__('PAGES', 'rey-core');
	}

	public function get_priority(){
		return 110;
	}

	public function get_breadcrumbs(){
		return ['WooCommerce', 'Pages'];
	}

	public function get_icon(){
		return 'woo-cart-page';
	}

	public function controls(){

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'cart_checkout_hide_flat_rate_if_free_shipping',
			'label'       => esc_html__( 'Hide "Flat rate" if "Free Shipping"', 'rey-core' ),
			'help' => [
				esc_html__( 'By default, even if Free Shipping is valid, the Flat rate will be shown. This option will hide it.', 'rey-core' )
			],
			'default'     => false,
		] );

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'cart_sticky_button',
			'label'       => esc_html__( 'Enable Sticky "Proceed" button', 'rey-core' ),
			'default'     => false,
			'separator'   => 'before',
			'help' => [
				esc_html__( 'Enable the bottom sticky "Proceed to checkout" button.', 'rey-core' )
			],
		] );

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'cart_checkout_bar_process',
			'label'       => esc_html__( 'Enable Cart/Checkout process steps', 'rey-core' ),
			'default'     => true,
			'separator'   => 'before',
		] );

		$this->start_controls_group( [
			'label'    => esc_html__( 'Extra Options', 'rey-core' ),
			'active_callback' => [
				[
					'setting'  => 'cart_checkout_bar_process',
					'operator' => '==',
					'value'    => true,
				],
			],
		]);

			$this->add_control( [
				'type'        => 'select',
				'settings'    => 'cart_checkout_bar_icons',
				'label'       => esc_html__( 'Icon type', 'rey-core' ),
				'help' => [
					esc_html__( 'Choose what type of symbol to display on the progress block in the Cart & Checkout page.', 'rey-core' )
				],
				'default'     => 'icon',
				'choices'     => [
					'icon' => esc_html__( 'Check Icons', 'rey-core' ),
					'numbers' => esc_html__( 'Numbers', 'rey-core' ),
				],
			] );


			$this->add_control( [
				'type'     => 'text',
				'settings' => 'cart_checkout_bar_text1_t',
				'label'       => esc_html__( 'Step 1 (Title)', 'rey-core' ),
				'default'  => '',
				'input_attrs' => [
					'placeholder' => 'eg: SHOPPING BAG'
				],
			] );

			$this->add_control( [
				'type'     => 'text',
				'settings' => 'cart_checkout_bar_text1_s',
				'label'       => esc_html__( 'Step 1 (SubTitle)', 'rey-core' ),
				'default'  => '',
				'input_attrs' => [
					'placeholder' => 'eg: View your items'
				],
			] );

			$this->add_control( [
				'type'     => 'text',
				'settings' => 'cart_checkout_bar_text2_t',
				'label'       => esc_html__( 'Step 2 (Title)', 'rey-core' ),
				'default'  => '',
				'input_attrs' => [
					'placeholder' => 'eg: SHIPPING AND CHECKOUT'
				],
			] );

			$this->add_control( [
				'type'     => 'text',
				'settings' => 'cart_checkout_bar_text2_s',
				'label'       => esc_html__( 'Step 2 (SubTitle)', 'rey-core' ),
				'default'  => '',
				'input_attrs' => [
					'placeholder' => 'eg: Enter your details'
				],
			] );

			$this->add_control( [
				'type'     => 'text',
				'settings' => 'cart_checkout_bar_text3_t',
				'label'       => esc_html__( 'Step 3 (Title)', 'rey-core' ),
				'default'  => '',
				'input_attrs' => [
					'placeholder' => 'eg: CONFIRMATION'
				],
			] );

			$this->add_control( [
				'type'     => 'text',
				'settings' => 'cart_checkout_bar_text3_s',
				'label'       => esc_html__( 'Step 3 (SubTitle)', 'rey-core' ),
				'default'  => '',
				'input_attrs' => [
					'placeholder' => 'eg: Review your order'
				],
			] );

		$this->end_controls_group();

	}
}
