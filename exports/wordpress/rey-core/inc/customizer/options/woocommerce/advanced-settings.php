<?php
namespace ReyCore\Customizer\Options\Woocommerce;

if ( ! defined( 'ABSPATH' ) ) exit;

use \ReyCore\Customizer\Controls;

class AdvancedSettings extends \ReyCore\Customizer\SectionsBase {

	public static function get_id(){
		return 'woo-advanced-settings';
	}

	public function get_title(){
		return esc_html__('Advanced Options', 'rey-core');
	}

	public function get_priority(){
		return 300;
	}

	public function get_icon(){
		return 'woo-advanced-options';
	}

	public function get_breadcrumbs(){
		return ['WooCommerce', 'Other Settings'];
	}

	public function get_title_before(){
		return esc_html__('OTHER SETTINGS', 'rey-core');
	}

	public function controls(){


		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'shop_catalog',
			'label'       => esc_html__( 'Enable Catalog Mode', 'rey-core' ),
			'default'     => false,
			// 'priority'    => 5,
			'description' => __( 'Enabling catalog mode will disable adding to cart functionality, including hiding the Cart widget.', 'rey-core' ),
		] );

		$this->start_controls_group( [
			'label'    => esc_html__( 'Extra options', 'rey-core' ),
			'active_callback' => [
				[
					'setting'  => 'shop_catalog',
					'operator' => '==',
					'value'    => true,
					],
			],
		]);

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'shop_catalog__variable',
			'label'       => esc_html__( 'Variable products', 'rey-core' ),
			'help' => [
				esc_html__( 'Choose how the Add To Cart & Variation choices should be handled in Catalog Mode.', 'rey-core' )
			],
			'default'     => 'hide',
			// 'priority'    => 5,
			'choices'     => [
				'hide' => esc_html__( 'Hide the entire ATC. form', 'rey-core' ),
				'hide_just_atc' => esc_html__( 'Hide only the ATC. button', 'rey-core' ),
				'show' => esc_html__( 'Show form, but prevent purchasing', 'rey-core' ),
			],
		] );

		$this->end_controls_group();


		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'shop_hide_prices_logged_out',
			'label'       => __('Hide prices when logged out', 'rey-core'),
			'help' => [
				__('If enabled, product prices will be hidden when the visitor is not logged in.', 'rey-core')
			],
			'default'     => false,
			'separator' => 'before',
		] );

		$this->add_control( [
			'type'        => 'text',
			'settings'    => 'shop_hide_prices_logged_out_text',
			'label'       => __('Show custom text', 'rey-core'),
			'help' => [
				__('Add a custom text to display instead of the prices.', 'rey-core')
			],
			'default'     => '',
			// 'priority'    => 5,
			'input_attrs'     => [
				'placeholder' => esc_html__('eg: Login to see prices.', 'rey-core'),
			],
			'active_callback' => [
				[
					'setting'  => 'shop_hide_prices_logged_out',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$this->add_control( [
			'type'     => 'toggle',
			'settings' => 'woo_notices',
			'label'    => __('Notice Banners - Neutral Colors', 'rey-core'),
			'help'     => [
				__('Starting with Woo 8.4 the notice banners were refactored and the styling was changed. Rey uses a neutral color scheme, however you can choose to fallback to Woo\'s colored scheme by disabling this option.', 'rey-core')
			],
			'default'   => true,
			'separator' => 'before',
		] );


	}
}
