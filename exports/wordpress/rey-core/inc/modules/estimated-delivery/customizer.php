<?php
namespace ReyCore\Modules\EstimatedDelivery;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Customizer {

	public function __construct(){
		add_action('reycore/customizer/section=woo-product-page-summary-components/marker=before_meta', [$this, 'add_controls']);
	}

	public function add_controls( $section ){

		if( ! $section ){
			return;
		}

		$section->start_controls_accordion([
			'label'  => esc_html__( 'Estimated delivery', 'rey-core' ),
		]);

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'single_extras__estimated_delivery',
			'label'       => esc_html__( 'Show Estimated Delivery', 'rey-core' ),
			'default'     => false,
		] );

		$section->add_control( [
			'type'        => 'text',
			'settings'    => 'estimated_delivery__prefix',
			'label'       => esc_html__( 'Prefix title', 'rey-core' ),
			'default'     => esc_html__('Estimated delivery:', 'rey-core'),
			'input_attrs'     => [
				'placeholder' => esc_html__('eg: Estimated delivery:', 'rey-core'),
			],
			'active_callback' => [
				[
					'setting'  => 'single_extras__estimated_delivery',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$section->add_control( [
			'type'        => 'rey-number',
			'settings'    => 'estimated_delivery__days',
			'label'       => esc_html__( 'Days', 'rey-core' ),
			'default'     => 3,
			'choices'     => [
				'min'  => 1,
				'max'  => 200,
				'step' => 1,
			],
			'active_callback' => [
				[
					'setting'  => 'single_extras__estimated_delivery',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$section->add_control( [
			'type'        => 'select',
			'settings'    => 'estimated_delivery__display_type',
			'label'       => esc_html__( 'Display type', 'rey-core' ),
			'default'     => 'number',
			'choices'     => [
				'number' => esc_html__( 'Number of days', 'rey-core' ),
				'date' => esc_html__( 'Exact date', 'rey-core' ),
			],
			'active_callback' => [
				[
					'setting'  => 'single_extras__estimated_delivery',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$section->add_control( [
			'type'        => 'select',
			'settings'    => 'estimated_delivery__exclude',
			'label'       => esc_html__('Non-working days', 'rey-core'),
			'help' => [
				__('Exclude certain non-working days from date estimation count.', 'rey-core')
			],
			'default'     => ['Saturday', 'Sunday'],
			'multiple'    => 6,
			'choices'     => [
				'Monday' => esc_html__( 'Monday', 'rey-core' ),
				'Tuesday' => esc_html__( 'Tuesday', 'rey-core' ),
				'Wednesday' => esc_html__( 'Wednesday', 'rey-core' ),
				'Thursday' => esc_html__( 'Thursday', 'rey-core' ),
				'Friday' => esc_html__( 'Friday', 'rey-core' ),
				'Saturday' => esc_html__( 'Saturday', 'rey-core' ),
				'Sunday' => esc_html__( 'Sunday', 'rey-core' ),
			],
			'active_callback' => [
				[
					'setting'  => 'single_extras__estimated_delivery',
					'operator' => '==',
					'value'    => true,
				],
				[
					'setting'  => 'estimated_delivery__display_type',
					'operator' => '==',
					'value'    => 'date',
				],
			],
		] );

		$section->add_control( [
			'type'        => 'select',
			'settings'    => 'estimated_delivery__inventory',
			'label'       => __('Show for', 'rey-core'),
			'default'     => ['instock'],
			'multiple'    => 3,
			'choices'     => [
				'instock' => esc_html__( 'In Stock', 'rey-core' ),
				'outofstock' => esc_html__( 'Out of stock', 'rey-core' ),
				'onbackorder' => esc_html__( 'Available on backorder', 'rey-core' ),
			],
			'active_callback' => [
				[
					'setting'  => 'single_extras__estimated_delivery',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$section->add_control( [
			'type'        => 'rey-number',
			'settings'    => 'estimated_delivery__days_margin',
			'label'       => esc_html__('Days margin', 'rey-core'),
			'help' => [
				__('Eg: from 1 to X days.', 'rey-core')
			],
			'default'     => '',
			'choices'     => [
				'min'  => 0,
				'max'  => 100,
				'step' => 1,
			],
			'active_callback' => [
				[
					'setting'  => 'single_extras__estimated_delivery',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$section->add_control( [
			'type'        => 'rey-color',
			'settings'    => 'estimated_delivery__color',
			'label'       => esc_html__( 'Color', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'alpha' => true,
			],
			'output'          => [
				[
					'element'  		   => '.woocommerce div.product .rey-estimatedDelivery',
					'property' 		   => 'color',
				],
			],
			'active_callback' => [
				[
					'setting'  => 'single_extras__estimated_delivery',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'estimated_delivery__locale',
			'label'       => esc_html__('Use Locale', 'rey-core'),
			'help' => [
				__('This option will display the dates in the local language.', 'rey-core')
			],
			'default'     => false,
			'active_callback' => [
				[
					'setting'  => 'single_extras__estimated_delivery',
					'operator' => '==',
					'value'    => true,
				],
				[
					'setting'  => 'estimated_delivery__display_type',
					'operator' => '==',
					'value'    => 'date',
				],
			],
		] );

		$section->add_control( [
			'type'        => 'text',
			'settings'    => 'estimated_delivery__locale_format',
			'label'       => esc_html__('Date format', 'rey-core'),
			'help' => [
				__('More examples of formats types <a href="https://www.php.net/manual/en/function.strftime.php" target="_blank">on this page</a>.', 'rey-core'),
				'clickable' => true
			],
			'default'     => '%A, %b %d',
			'input_attrs'     => [
				'placeholder' => esc_html__('eg: %A, %b %d', 'rey-core'),
			],
			'active_callback' => [
				[
					'setting'  => 'single_extras__estimated_delivery',
					'operator' => '==',
					'value'    => true,
				],
				[
					'setting'  => 'estimated_delivery__locale',
					'operator' => '==',
					'value'    => true,
				],
				[
					'setting'  => 'estimated_delivery__display_type',
					'operator' => '==',
					'value'    => 'date',
				],
			],
		] );

		$section->add_control( [
			'type'        => 'select',
			'settings'    => 'estimated_delivery__position',
			'label'       => esc_html__( 'Position', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'default' => esc_html__( 'Default (after ATC button)', 'rey-core' ),
				'custom' => esc_html__( 'Custom ', 'rey-core' ),
			],
			'active_callback' => [
				[
					'setting'  => 'single_extras__estimated_delivery',
					'operator' => '==',
					'value'    => true,
				],
			],
			'help' => [
				_x('Select the placement. You can disable the text in favor of using the <code>[rey_estimated_delivery]</code> shortcode somewhere in the page.', 'Customizer control', 'rey-core'),
				'clickable' => true,
			]
		] );


		$section->add_control( [
			'type'        => 'text',
			'settings'    => 'estimated_delivery__text_outofstock',
			'label'       => esc_html__( 'Fallback text when Out of stock', 'rey-core' ),
			'default'     => '',
			'help' => [
				_x('If you want to just override the Days number specifically for Out of stock products, simply just add a number here without any text. Eg: 14.', 'Customizer control', 'rey-core'),
			],
			'input_attrs'     => [
				'data-control-class' => '--text-xl',
			],
			'active_callback' => [
				[
					'setting'  => 'single_extras__estimated_delivery',
					'operator' => '==',
					'value'    => true,
				],
				[
					'setting'  => 'estimated_delivery__inventory',
					'operator' => 'does not contain',
					'value'    => 'outofstock', // value 1
				],
			],
		] );

		$section->add_control( [
			'type'        => 'text',
			'settings'    => 'estimated_delivery__text_onbackorder',
			'label'       => esc_html__( 'Fallback text when on Backorder', 'rey-core' ),
			'default'     => '',
			'help' => [
				_x('If you want to just override the Days number specifically for "Backorder" products, simply just add a number here without any text. Eg: 14.', 'Customizer control', 'rey-core'),
			],
			'input_attrs'     => [
				'data-control-class' => '--text-xl',
			],
			'active_callback' => [
				[
					'setting'  => 'single_extras__estimated_delivery',
					'operator' => '==',
					'value'    => true,
				],
				[
					'setting'  => 'estimated_delivery__inventory',
					'operator' => 'does not contain',
					'value'    => 'onbackorder', // value 1
				],
			],
		] );

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'estimated_delivery__cart_checkout',
			'label'       => esc_html__( 'Show in Cart & Checkout?', 'rey-core' ),
			'help' => [
				__('If enabled, the Estimated delivery range row will be shown in the Cart & Checkout order review table.', 'rey-core'),
			],
			'default'     => true,
			'active_callback' => [
				[
					'setting'  => 'single_extras__estimated_delivery',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'estimated_delivery__variations',
			'label'       => esc_html__( 'Allow overrides per variation', 'rey-core' ),
			'default'     => false,
			'help' => [
				__('Set estimation delivery dates per variation.', 'rey-core'),
			],
			'active_callback' => [
				[
					'setting'  => 'single_extras__estimated_delivery',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );


		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'single_extras__shipping_class',
			'label'       => esc_html__( 'Show Shipping Class', 'rey-core' ),
			'default'     => false,
		] );

		$section->end_controls_accordion();
	}

}
