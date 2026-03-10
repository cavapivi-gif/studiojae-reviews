<?php
namespace ReyCore\Elementor\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WcCheckout extends \ReyCore\Elementor\WidgetsBase {

	const DEFAULT_LAYOUT = 'custom';

	public $_settings = [];
	public $_options = [];

	public static function get_rey_config(){
		return [
			'id' => 'wc-checkout',
			'title' => __( 'WooCommerce Checkout Page', 'rey-core' ),
			'icon' => 'rey-el-icon--checkout',
			'categories' => [ 'rey-woocommerce' ],
			'keywords' => [],
			'css' => [
				'assets/style[rtl].css',
			],
			'js' => [
				'assets/script.js',
			],
		];
	}

	public function rey_get_script_depends() {
		return [ 'reycore-woocommerce', 'reycore-widget-wc-checkout-scripts', 'reycore-tooltips' ];
	}

	public function get_custom_help_url() {
		return reycore__support_url('kb/how-to-create-a-custom-cart-checkout-layout/');
	}


	function get_fields( $fields_type = 'shipping' ){

		$fields = [
			'first_name' => 'First name',
			'last_name' => 'Last name',
			'company' => 'Company name',
			'country' => 'Country / Region',
			'address_1' => 'Street address',
			'address_2' => 'Address 2',
			'city' => 'Town / City',
			'state' => 'County',
			'postcode' => 'Postcode / ZIP',
			'phone' => 'Phone',
			'email' => 'Email address',
		];

		$return = [
			'' => esc_html__('- Select -', 'rey-core')
		];

		foreach ($fields as $key => $label) {
			if( $fields_type === 'shipping' && $key === 'email'){
				continue;
			}
			// must use billing_phone
			if( $key === 'phone' ){
				$return['billing_' . $key] = $label;
			}
			else {
				$return[$fields_type . '_' . $key] = $label;
			}
		}

		return $return;
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_settings',
			[
				'label' => __( 'Settings', 'rey-core' ),
			]
		);

			$this->add_control(
				'layout',
				[
					'label' => esc_html__( 'Layout', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => self::DEFAULT_LAYOUT,
					'options' => [
						'classic'  => esc_html__( 'Classic', 'rey-core' ),
						'custom'  => esc_html__( 'Custom', 'rey-core' ),
					],
				]
			);

			$this->add_control(
				'quick_tips',
				[
					'label' => __( 'Quick Tip:', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::RAW_HTML,
					'raw' => __( 'To have a more focused Checkout, disable the Header & Footer for this page. To do it, access this page in the backend and look for the options eg: <a href="https://d.pr/v/eurVKF" target="_blank">https://d.pr/v/eurVKF</a>.', 'rey-core' ),
					'content_classes' => 'elementor-descriptor',
				]
			);

			$this->add_control(
				'use_steps',
				[
					'label' => esc_html__( 'Display Steps', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
					'condition' => [
						'layout' => 'custom',
					],
				]
			);

			// $this->add_control(
			// 	'debug_mode',
			// 	[
			// 		'label' => esc_html__( 'Debug Mode', 'rey-core' ),
			// 		'type' => \Elementor\Controls_Manager::SWITCHER,
			// 		'default' => '',
			// 		'condition' => [
			// 			'layout' => 'custom',
			// 		],
			// 	]
			// );

		$this->end_controls_section();

		/* ------------------------------------ Information ------------------------------------ */

		$this->start_controls_section(
			'section_info_settings',
			[
				'label' => __( 'Information', 'rey-core' ),
			]
		);

			$this->add_control(
				'show_billing_first',
				[
					'label' => esc_html__( 'Show Billing First?', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'condition' => [
						'layout' => 'custom',
					],
				]
			);

			$this->add_control(
				'info_shipping_fields_notice_title',
				[
				   'label' => esc_html_x( 'CUSTOM FIELDS', 'Title in Elementor control.', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_control(
				'info_shipping_fields_notice',
				[
					'type' => \Elementor\Controls_Manager::RAW_HTML,
					'raw' => sprintf( __( 'Want to create new billing and shipping fields? Please enable <strong>WooCommerce Custom Fields</strong> plugin and read <a href="%s" target="_blank">this article</a>.', 'rey-core' ), '#' ),
					'content_classes' => 'rey-raw-html',
				]
			);

			$this->add_control(
				'custom_shipping_fields__title',
				[
				   'label' => esc_html_x( 'BUILT-IN FIELDS', 'Title in Elementor control.', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$custom_shipping_fields = new \Elementor\Repeater();

			$custom_shipping_fields->add_control(
				'field',
				[
					'label' => esc_html__( 'Select Field', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => $this->get_fields('shipping'),
				]
			);

			// $custom_shipping_fields->add_control(
			// 	'remove',
			// 	[
			// 		'label' => esc_html__( 'Remove Field', 'rey-core' ),
			// 		'type' => \Elementor\Controls_Manager::SWITCHER,
			// 		'default' => '',
			// 		'conditions' => [
			// 			'terms' => [
			// 				[
			// 					'name' => 'field',
			// 					'operator' => '!=',
			// 					'value' => '',
			// 				],
			// 			],
			// 		],
			// 	]
			// );

			$custom_shipping_fields->add_control(
				'required',
				[
					'label' => esc_html__( 'Required', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( 'Unchanged', 'rey-core' ),
						'yes'  => esc_html__( 'Yes', 'rey-core' ),
						'no'  => esc_html__( 'No', 'rey-core' ),
					],
					'conditions' => [
						'terms' => [
							[
								'name' => 'field',
								'operator' => '!=',
								'value' => '',
							],
							// [
							// 	'name' => 'remove',
							// 	'operator' => '==',
							// 	'value' => '',
							// ],
						],
					],
				]
			);

			$custom_shipping_fields->add_control(
				'label',
				[
					'label' => esc_html__( 'Label', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'conditions' => [
						'terms' => [
							[
								'name' => 'field',
								'operator' => '!=',
								'value' => '',
							],
							// [
							// 	'name' => 'remove',
							// 	'operator' => '==',
							// 	'value' => '',
							// ],
						],
					],
				]
			);

			$custom_shipping_fields->add_control(
				'description',
				[
					'label' => esc_html__( 'Tooltip', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'label_block' => true,
					'conditions' => [
						'terms' => [
							[
								'name' => 'field',
								'operator' => '!=',
								'value' => '',
							],
							// [
							// 	'name' => 'remove',
							// 	'operator' => '==',
							// 	'value' => '',
							// ],
						],
					],
				]
			);

			$custom_shipping_fields->add_control(
				'priority',
				[
					'label' => esc_html__( 'Priority (Order)', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 200,
					'step' => 1,
					'conditions' => [
						'terms' => [
							[
								'name' => 'field',
								'operator' => '!=',
								'value' => '',
							],
							// [
							// 	'name' => 'remove',
							// 	'operator' => '==',
							// 	'value' => '',
							// ],
						],
					],
				]
			);

			/*

			See in code below why disabled.

			$custom_shipping_fields->add_control(
				'size',
				[
					'label' => esc_html__( 'Size', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( 'Unchanged', 'rey-core' ),
						'full'  => esc_html__( 'Full', 'rey-core' ),
						'half'  => esc_html__( 'Half', 'rey-core' ),
						'third'  => esc_html__( 'One Third', 'rey-core' ),
					],
					'conditions' => [
						'terms' => [
							[
								'name' => 'field',
								'operator' => '!=',
								'value' => '',
							],
							// [
							// 	'name' => 'remove',
							// 	'operator' => '==',
							// 	'value' => '',
							// ],
						],
					],
				]
			);
			*/

			$this->add_control(
				'custom_shipping_fields',
				[
					'label' => __( 'Customize Default <strong>Shipping</strong> Fields', 'rey-core' ),
					'description' => __( 'You can customize the shipping fields by changing some of their attributes.', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::REPEATER,
					'fields' => $custom_shipping_fields->get_controls(),
					'default' => [],
					'prevent_empty' => false,
					// 'title_field' => '{{{ field }}}'
				]
			);

			/**
			 * BILLING
			 */

			$custom_billing_fields = new \Elementor\Repeater();

			$custom_billing_fields->add_control(
				'field',
				[
					'label' => esc_html__( 'Select Field', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => $this->get_fields('billing'),
				]
			);

			// $custom_billing_fields->add_control(
			// 	'remove',
			// 	[
			// 		'label' => esc_html__( 'Remove Field', 'rey-core' ),
			// 		'type' => \Elementor\Controls_Manager::SWITCHER,
			// 		'default' => '',
			// 		'conditions' => [
			// 			'terms' => [
			// 				[
			// 					'name' => 'field',
			// 					'operator' => '!=',
			// 					'value' => '',
			// 				],
			// 			],
			// 		],
			// 	]
			// );

			$custom_billing_fields->add_control(
				'required',
				[
					'label' => esc_html__( 'Required', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( 'Unchanged', 'rey-core' ),
						'yes'  => esc_html__( 'Yes', 'rey-core' ),
						'no'  => esc_html__( 'No', 'rey-core' ),
					],
					'conditions' => [
						'terms' => [
							[
								'name' => 'field',
								'operator' => '!=',
								'value' => '',
							],
							// [
							// 	'name' => 'remove',
							// 	'operator' => '==',
							// 	'value' => '',
							// ],
						],
					],
				]
			);

			$custom_billing_fields->add_control(
				'label',
				[
					'label' => esc_html__( 'Label', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'conditions' => [
						'terms' => [
							[
								'name' => 'field',
								'operator' => '!=',
								'value' => '',
							],
							// [
							// 	'name' => 'remove',
							// 	'operator' => '==',
							// 	'value' => '',
							// ],
						],
					],
				]
			);

			$custom_billing_fields->add_control(
				'description',
				[
					'label' => esc_html__( 'Tooltip', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'label_block' => true,
					'conditions' => [
						'terms' => [
							[
								'name' => 'field',
								'operator' => '!=',
								'value' => '',
							],
							// [
							// 	'name' => 'remove',
							// 	'operator' => '==',
							// 	'value' => '',
							// ],
						],
					],
				]
			);

			$custom_billing_fields->add_control(
				'priority',
				[
					'label' => esc_html__( 'Priority (Order)', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 200,
					'step' => 1,
					'conditions' => [
						'terms' => [
							[
								'name' => 'field',
								'operator' => '!=',
								'value' => '',
							],
							// [
							// 	'name' => 'remove',
							// 	'operator' => '==',
							// 	'value' => '',
							// ],
						],
					],
				]
			);

			$this->add_control(
				'custom_billing_fields',
				[
					'label' => __( 'Customize Default <strong>Billing</strong> Fields', 'rey-core' ),
					'description' => __( 'You can customize the billing fields by changing some of their attributes.', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::REPEATER,
					'fields' => $custom_billing_fields->get_controls(),
					'default' => [],
					'prevent_empty' => false,
					// 'title_field' => '{{{ field }}}'
				]
			);

			$this->add_control(
				'rearrange_country_state_zip',
				[
					'label' => esc_html__( 'Rearrange country, state & zip order?', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
				]
			);



		$this->end_controls_section();

		/* ------------------------------------ Shipping ------------------------------------ */

		$this->start_controls_section(
			'section_shipping_settings',
			[
				'label' => __( 'Shipping', 'rey-core' ),
				'condition' => [
					'layout' => 'custom',
				],
			]
		);

			$this->add_control(
				'show_estimated_delivery',
				[
					'label' => esc_html__( 'Show Estimated Delivery Text', 'rey-core' ),
					// 'description' => esc_html__( 'Must be enabled in Customizer > WooCommerce > Product page - Content > Extras .', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

				 $this->add_control(
					'estimated_delivery_text',
					[
						'label' => esc_html__( 'Estimated Delivery Custom Text', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'default' => '',
						'label_block' => true,
						'condition' => [
							'show_estimated_delivery!' => '',
						],
					]
				 );

			$this->add_control(
				'disable_shipping_step',
				[
					'label' => esc_html__( 'Disable Shipping Step', 'rey-core' ),
					'description' => esc_html__( 'Useful if there\'s no shipping method added.', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

		$this->end_controls_section();

		/* ------------------------------------ Payment ------------------------------------ */

		// $this->start_controls_section(
		// 	'section_payment_settings',
		// 	[
		// 		'label' => __( 'Payment', 'rey-core' ),
		// 	]
		// );



		// $this->end_controls_section();

		/* ------------------------------------ Reviews ------------------------------------ */

		$this->start_controls_section(
			'section_review_settings',
			[
				'label' => __( 'Order Review', 'rey-core' ),
				'condition' => [
					'layout' => 'custom',
				],
			]
		);

			$this->add_control(
				'order_shipping_total',
				[
					'label' => esc_html__( 'Display Shipping Total', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
					'condition' => [
						'layout' => 'custom',
					],
				]
			);

			$this->add_control(
				'disable_shipping_in_information',
				[
					'label' => esc_html__( 'Hide Shipping in Information step?', 'rey-core' ),
					'description' => esc_html__( 'This helps to avoid confusion if there are multiple shipping choices.', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
					'condition' => [
						'layout' => 'custom',
						'order_shipping_total' => 'yes',
					],
				]
			);

			$this->add_control(
				'review_coupon_enable',
				[
					'label' => esc_html__( 'Display Coupon Form', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
					'separator' => 'before',
				]
			);

				$this->add_control(
					'review_coupon_toggle',
					[
						'label' => esc_html__( 'Toggle Coupon Link', 'rey-core' ),
						'type' => \Elementor\Controls_Manager::SWITCHER,
						'default' => '',
						'condition' => [
							'review_coupon_enable!' => '',
						],
					]
				);

			$this->add_control(
				'review_custom_heading',
				[
				   'label' => esc_html__( 'Custom Content', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_control(
				'custom_text',
				[
					'label' => esc_html__( 'Custom Text', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::WYSIWYG,
					'default' => '',
					'placeholder' => __( 'Type your content here', 'rey-core' ),
				]
			);

		$this->end_controls_section();


		/* ------------------------------------ Thank you page ------------------------------------ */

		$this->start_controls_section(
			'section_thankyou',
			[
				'label' => __( 'Order confirmation', 'rey-core' ),
				'condition' => [
					'layout' => 'custom',
				],
			]
		);

			// $this->add_control(
			// 	'thankyou_preview',
			// 	[
			// 		'label' => esc_html__( 'Preview page', 'rey-core' ),
			// 		'type' => \Elementor\Controls_Manager::SELECT,
			// 		'default' => '',
			// 		'options' => [
			// 			''  => esc_html__( 'No', 'rey-core' ),
			// 			'success'  => esc_html__( 'Successful Order', 'rey-core' ),
			// 			'failed'  => esc_html__( 'Failed Order', 'rey-core' ),
			// 		],
			// 	]
			// );

			// custom text
			$this->add_control(
				'thankyou_text',
				[
					'label' => esc_html__( 'Custom Text', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::WYSIWYG,
					'default' => esc_html__('Thank you {{name}}! Your order has been received.', 'rey-core'),
				]
			);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_misc',
			[
				'label' => __( 'Misc. Settings', 'rey-core' ),
				'condition' => [
					'layout' => 'custom',
				],
			]
		);

			// $this->add_control(
			// 	'custom_fields_address_to_title',
			// 	[
			// 		'label' => esc_html__( 'Bill To / Ship To Custom fields', 'rey-core' ),
			// 		'type' => \Elementor\Controls_Manager::HEADING,
			// 		'separator' => 'before',
			// 	]
			// );

			// $this->add_control(
			// 	'custom_fields_address_to_desc',
			// 	[
			// 		'type' => \Elementor\Controls_Manager::RAW_HTML,
			// 		'raw' => esc_html__( 'In case you\'re using a plugin that adds custom Billing or Shipping fields, and want to include it into the "Bill To" or "Ship To" address, you can add here their field names separated by comma.', 'rey-core' ),
			// 		'content_classes' => 'rey-raw-html',
			// 	]
			// );

			$this->add_control(
				'custom_billing_fields_bill_to',
				[
					'label' => esc_html__( 'Billing fields for "Bill To"', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::HIDDEN,
					'default' => '',
					'placeholder' => esc_html__( 'billing_x, billing_y', 'rey-core' ),
					'label_block' => true,
				]
			);

			$this->add_control(
				'custom_shipping_fields_ship_to',
				[
					'label' => esc_html__( 'Shipping fields for "Ship To"', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::HIDDEN,
					'default' => '',
					'placeholder' => esc_html__( 'shipping_x, shipping_y', 'rey-core' ),
					'label_block' => true,
				]
			);


		$this->end_controls_section();


		/* ------------------------------------ Style ------------------------------------ */


		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Style', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'layout' => 'custom',
				],
			]
		);

			$this->add_control(
				'form_size',
				[
					'label' => esc_html__( 'Form Size', 'rey-core' ) . ' (%)',
					'type' => \Elementor\Controls_Manager::SLIDER,
					'range' => [
						'%' => [
							'min' => 30,
							'max' => 85,
							'step' => 1,
						],
					],
					'size_units' => [ '%' ],
					'selectors' => [
						'{{WRAPPER}} .rey-checkoutPage.--layout-custom' => '--checkout-form-size: {{SIZE}}%',
					],
					'render_type' => 'template',
				]
			);

			$this->add_control(
				'accent_color',
				[
					'label' => esc_html__( 'Accent Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-checkoutPage' => '--accent-color: {{VALUE}}',
					],
					'separator' => 'before'
				]
			);

			$this->add_control(
				'accent_hover_color',
				[
					'label' => esc_html__( 'Accent Hover Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-checkoutPage' => '--accent-hover-color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'accent_text_color',
				[
					'label' => esc_html__( 'Accent Text Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-checkoutPage' => '--accent-text-color: {{VALUE}}',
					]
				]
			);

			$this->add_control(
				'separator_color',
				[
					'label' => esc_html__( 'Separator Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-checkoutPage-form:after' => 'background-color: {{VALUE}}',
					],
					'separator' => 'before'
				]
			);

			$this->add_control(
				'titles_color',
				[
					'label' => esc_html__( 'Title Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-checkoutPage-title' => 'color: {{VALUE}}',
					],
					'separator' => 'before'
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'titles_style',
					'label' => __( 'Titles Typo', 'rey-core' ),
					'selector' => '{{WRAPPER}} .rey-checkoutPage-title',
				]
			);

			$this->add_control(
				'text_color',
				[
					'label' => esc_html__( 'Text Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-checkoutPage' => '--checkout-text-colors: {{VALUE}}',
					],
					'separator' => 'before'
				]
			);

			$this->add_control(
				'bg_color',
				[
					'label' => esc_html__( 'Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-checkoutPage' => '--checkout-bg-colors: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'bg_color_secondary',
				[
					'label' => esc_html__( 'Background Color (Secondary)', 'rey-core' ),
					'description' => esc_html__( 'Used inside toggles.', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-checkoutPage' => '--checkout-bg-colors-secondary: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'border_color',
				[
					'label' => esc_html__( 'Borders Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-checkoutPage' => '--checkout-border-color: {{VALUE}}',
					],
				]
			);

			$this->add_responsive_control(
				'border_size',
				[
					'label' => esc_html__( 'Borders Size', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 10,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .rey-checkoutPage' => '--checkout-border-size: {{VALUE}}px',
					],
				]
			);

			$this->add_responsive_control(
				'border_radius',
				[
					'label' => esc_html__( 'Borders Radius', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 100,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .rey-checkoutPage' => '--checkout-border-radius: {{VALUE}}px',
					],
				]
			);

			$this->add_responsive_control(
				'cell_padding',
				[
					'label' => esc_html__( 'Cells Padding', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 100,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .rey-checkoutPage' => '--checkout-box-padding: {{VALUE}}px',
					],
				]
			);

		$this->end_controls_section();

		/* ------------------------------------ Fiels styles ------------------------------------ */

		$this->start_controls_section(
			'section_fields_style',
			[
				'label' => __( 'Fields styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->start_controls_tabs( 'tabs_fields_styles' );

				$this->start_controls_tab(
					'tabs_field_normal',
					array(
						'label' => esc_html__( 'Normal', 'rey-core' ),
					)
				);

					$this->add_control(
						'field_color',
						[
							'label' => __( 'Text Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}}' => '--checkout-fields-text: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'field_bg_color',
						[
							'label' => __( 'Background Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}}' => '--checkout-fields-bg: {{VALUE}}',
							],
						]
					);

					$this->add_responsive_control(
						'field_border_width',
						[
							'label' => __( 'Border Width', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::DIMENSIONS,
							'size_units' => [ 'px', 'em', '%' ],
							'selectors' => [
								'{{WRAPPER}}' => '--checkout-fields-border-size: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'field_border_color',
						[
							'label' => __( 'Border Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}}' => '--checkout-fields-border-color: {{VALUE}};',
							],
						]
					);

					$this->add_responsive_control(
						'field_height',
						[
							'label' => esc_html__( 'Fields Height', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
							'type' => \Elementor\Controls_Manager::NUMBER,
							'default' => '',
							'min' => 20,
							'max' => 1000,
							'step' => 0,
							'selectors' => [
								'{{WRAPPER}}' => '--checkout-fields-height: {{VALUE}}px;',
							],
						]
					);

				$this->end_controls_tab();

				$this->start_controls_tab(
					'tabs_field_focus',
					array(
						'label' => esc_html__( 'FOCUS', 'rey-core' ),
					)
				);

					$this->add_control(
						'field_color_focus',
						[
							'label' => __( 'Text Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}}' => '--checkout-fields-focus-text: {{VALUE}}',
							],
						]
					);

					$this->add_control(
						'field_bg_color_focus',
						[
							'label' => __( 'Background Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}}' => '--checkout-fields-focus-bg: {{VALUE}}',
							],
						]
					);

					$this->add_responsive_control(
						'field_border_width_focus',
						[
							'label' => __( 'Border Width', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::DIMENSIONS,
							'size_units' => [ 'px', 'em', '%' ],
							'selectors' => [
								'{{WRAPPER}}' => '--checkout-fields-focus-border-size: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
						]
					);

					$this->add_control(
						'field_border_color_focus',
						[
							'label' => __( 'Border Color', 'rey-core' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}}' => '--checkout-fields-focus-border-color: {{VALUE}};',
							],
						]
					);

				$this->end_controls_tab();
			$this->end_controls_tabs();

			$this->add_control(
				'label_color',
				[
					'label' => esc_html__( 'Labels Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .form-row label, {{WRAPPER}} .wccf_field_container label' => 'color: {{VALUE}}',
					],
					'separator' => 'before'
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'labels_typo',
					'selector' => '{{WRAPPER}} .form-row label, {{WRAPPER}} .wccf_field_container label',
				]
			);

		$this->end_controls_section();

		/* ------------------------------------ Order review ------------------------------------ */

		$this->start_controls_section(
			'section_review_style',
			[
				'label' => __( 'Order review', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'layout' => 'custom',
				],
			]
		);

			$this->add_control(
				'review_sticky',
				[
					'label' => esc_html__( 'Sticky', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
					'render_type' => 'template',
				]
			);

			$this->add_control(
				'review_sticky_offset',
				[
					'label' => esc_html__( 'Sticky offset', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 1,
					'condition' => [
						'review_sticky!' => '',
					],
					'selectors' => [
						'{{WRAPPER}}' => '--sticky-offset: {{VALUE}}px;'
					]
				]
			);

			$this->add_control(
				'review_color',
				[
					'label' => __( 'Text Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}}' => '--checkout-review-text: {{VALUE}}',
					],
					'separator' => 'before'
				]
			);

			$this->add_control(
				'review_bg_color',
				[
					'label' => __( 'Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}}' => '--checkout-review-bg: {{VALUE}}',
					],
				]
			);

			$this->add_responsive_control(
				'review_border_width',
				[
					'label' => __( 'Border Width', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em', '%' ],
					'selectors' => [
						'{{WRAPPER}}' => '--checkout-review-border-size: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_control(
				'review_border_color',
				[
					'label' => __( 'Border Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}}' => '--checkout-review-border-color: {{VALUE}};',
					],
				]
			);

			$this->add_responsive_control(
				'review_border_radius',
				[
					'label' => esc_html__( 'Borders Radius', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 100,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .rey-checkoutPage' => '--checkout-review-border-radius: {{VALUE}}px',
					],
				]
			);

			$this->add_responsive_control(
				'review_padding',
				[
					'label' => esc_html__( 'Cells Padding', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 100,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}} .rey-checkoutPage' => '--checkout-review-box-padding: {{VALUE}}px',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'review_title_style',
					'label' => __( 'Title Typo', 'rey-core' ),
					'selector' => '{{WRAPPER}} #order_review_heading',
					'separator' => 'before'
				]
			);

			$this->add_control(
				'review_title_color',
				[
					'label' => esc_html__( 'Title Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} #order_review_heading' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_responsive_control(
				'image_size',
				[
					'label' => __( 'Image Size', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'range' => [
						'px' => [
							'max' => 300,
							'min' => 20,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .rey-checkoutPage-review .woocommerce-checkout-review-order-table .rey-reviewOrder-img' => 'width: {{SIZE}}px;',
					],
					'separator' => 'before'
				]
			);

			$this->add_control(
				'coupon_btn_color',
				[
					'label' => esc_html__( 'Coupon Button Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .woocommerce-form-coupon .button' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'coupon_btn_bg_color',
				[
					'label' => esc_html__( 'Coupon Button Bg. Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .woocommerce-form-coupon .button' => 'background-color: {{VALUE}}',
					],
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_crumbs_style',
			[
				'label' => __( 'Breadcrumbs Nav. Style', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'layout' => 'custom',
				],
			]
		);

			$this->add_control(
				'crumbs_style',
				[
					'label' => esc_html__( 'Style', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'default',
					'options' => [
						'default'  => esc_html__( 'Default', 'rey-core' ),
						'extended'  => esc_html__( 'Extended', 'rey-core' ),
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'crumbs_typo',
					'selector' => '{{WRAPPER}} .rey-checkoutPage-crumbs',
				]
			);

			$this->add_control(
				'crumbs_color',
				[
					'label' => esc_html__( 'Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-checkoutPage-crumbs, {{WRAPPER}} .rey-checkoutPage-crumbs a' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'crumbs_color_active',
				[
					'label' => esc_html__( 'Active Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-checkoutPage-crumbs a.--active' => 'color: {{VALUE}}',
					],
				]
			);

		$this->end_controls_section();

	}

	public function __unset_email_billing(){
		add_filter('woocommerce_form_field_email', '__return_empty_string', 20);
	}

	public function __customize_fields( $fields ){

		$size_classes = [
			'full' => 'form-row-wide',
			'half' => 'form-row-half',
			'third' => 'form-row-third',
		];

		// Rearrange Country order
		if( $this->_options['rearrange_country_state_zip'] ){

			if( isset($fields['billing']['billing_country']) ){
				$fields['billing']['billing_country']['priority'] = 65;
			}

			if( isset($fields['shipping']['shipping_country']) ){
				$fields['shipping']['shipping_country']['priority'] = 65;
			}
		}

		// Shipping
		foreach ($this->_settings['custom_shipping_fields'] as $shipping_field) {

			if( ! isset($fields['shipping'][ $shipping_field['field'] ]) ){
				continue;
			}

			if( isset($shipping_field['remove']) && $shipping_field['remove'] === 'yes' ){
				unset($fields['shipping'][ $shipping_field['field'] ]);
				continue;
			}

			if( $shipping_field['required'] ){
				$fields['shipping'][ $shipping_field['field'] ]['required'] = $shipping_field['required'] === 'yes';
			}

			if( $shipping_field['label'] ){
				$fields['shipping'][ $shipping_field['field'] ]['label'] = $shipping_field['label'];
			}

			if( $shipping_field['description'] ){
				$fields['shipping'][ $shipping_field['field'] ]['description'] = $shipping_field['description'];
			}

			if( $shipping_field['priority'] ){
				$fields['shipping'][ $shipping_field['field'] ]['priority'] = $shipping_field['priority'];
			}

			/*

			Can't have a size, bc WooCommerce selectively adds back the form-row-wide class.
			The only solution is to make the size css based, using the #id css selector.

			if( $shipping_field['size'] ){

				$classes = $fields['shipping'][ $shipping_field['field'] ]['class'];

				// cleanup first
				if (($key = array_search('form-row-wide', $classes)) !== false) {
					unset($classes[$key]);
				}

				$classes[] = $size_classes[ $shipping_field['size'] ];

				$fields['shipping'][ $shipping_field['field'] ]['class'] = $classes;
			}
			*/

		}

		// Billing
		foreach ($this->_settings['custom_billing_fields'] as $billing_field) {

			if( ! isset($fields['billing'][ $billing_field['field'] ]) ){
				continue;
			}

			if( isset($billing_field['remove']) && $billing_field['remove'] === 'yes' ){
				unset($fields['billing'][ $billing_field['field'] ]);
				continue;
			}

			if( $billing_field['required'] ){
				$fields['billing'][ $billing_field['field'] ]['required'] = $billing_field['required'] === 'yes';
			}

			if( $billing_field['label'] ){
				$fields['billing'][ $billing_field['field'] ]['label'] = $billing_field['label'];
			}

			if( $billing_field['description'] ){
				$fields['billing'][ $billing_field['field'] ]['description'] = $billing_field['description'];
			}

			if( $billing_field['priority'] ){
				$fields['billing'][ $billing_field['field'] ]['priority'] = $billing_field['priority'];
			}
		}

		return $fields;
	}

	function render_review_custom_content(){

		/**
		 * Custom text
		 */
		if( $custom_text = $this->_settings['custom_text'] ){
			printf('<div class="rey-review-customText">%s</div>', do_shortcode($custom_text));
		}

	}

	function render_thankyou_heading(){

		// it's Elementor WC. Checkout (Custom)
		if( $this->_settings['layout'] !== 'custom' ){
			return;
		}

		echo '<div class="rey-ordRecPage-header">';
			echo reycore__get_svg_icon(['id' => 'check', 'class' => 'rey-ordRecPage-icon']);
			printf('<h2 class="rey-ordRecPage-title">%s</h2>', WC()->query->get_endpoint_title('order-received'));
		echo '</div>';

	}

	function __show_estimation_delivery(){

		if( $this->_settings['show_estimated_delivery'] === '' ){
			return;
		}

		if( $text = $this->_settings['estimated_delivery_text'] ){
			echo $text;
		}

	}

	function render_thankyou_text( $text, $order ){

		if( $order && $custom_text = $this->_settings['thankyou_text'] ){
			return str_replace('{{name}}', $order->get_billing_first_name(), $custom_text);
		}

		return $text;
	}

	function __tweak_thankyou_addresses($address){
		return str_replace('<br/>', ', ', $address);
	}

	public function __render_thankyou_footer(){

		reycore_assets()->add_styles('rey-buttons'); ?>

		<p class="woocommerce-order-overview-actions">
			<a class="btn btn-primary wc-backward" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>">
				<?php esc_html_e( 'Continue shopping', 'woocommerce' ); ?>
			</a>
		</p>
		<?php
	}

	public function display_custom_layout(){

		if( ! ( function_exists('reycore_wc__get_tag') && ($checkout_tag = reycore_wc__get_tag('checkout')) ) ){
			return;
		}

		add_filter('woocommerce_is_checkout', '__return_true');

		// Load scripts for Login/Register modal
		reycore_assets()->add_styles('rey-wc-header-account-panel');

		add_filter( 'reycore/woocommerce/wc_get_template', [ $checkout_tag, 'add_templates' ], 20);

		// disable process
		add_filter('theme_mod_cart_checkout_bar_process', '__return_false', 20);

		// disable shipping cost display in checkout
		if($this->_settings['order_shipping_total'] === '' || $this->_settings['disable_shipping_step'] === 'yes'){
			add_filter('reycore/woocommerce/cart_checkout/show_shipping', '__return_false', 20);
		}

		/**
		 * Rearrange fields
		 */
		add_action('woocommerce_checkout_billing', [$this, '__unset_email_billing'], 0);
		add_filter('woocommerce_checkout_fields', [$this, '__customize_fields'], 20);

		/**
		 * Make Login as Modal
		 */
		remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10 );

		add_action( 'woocommerce_after_checkout_form', function(){

			if( is_user_logged_in() ){
				return;
			}

			add_filter( 'reycore/modal_template/show', '__return_true' ); ?>

				<div class="rey-checkoutLogin-form --hidden">
					<?php
					$shortcode = sprintf('[rey_ajax_login_form %s]', reycore__implode_html_attributes([
						'redirect_type' => 'url',
						'redirect_url' => wc_get_checkout_url(),
					]));
					echo do_shortcode($shortcode); ?>
				</div>
			<?php
		}, 10 );

		/**
		 * Add estimated delivery
		 */
		add_action('woocommerce_review_order_after_shipping', [$this, '__show_estimation_delivery'], 10);

		/**
		 * Move Coupons
		 */
		remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );

		/**
		 * Move payments in step 3
		 */
		remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );
		add_action( 'reycore/woocommerce/checkout/end', 'woocommerce_checkout_payment', 10 );

		/**
		 * Add custom content in review
		 */
		add_action('woocommerce_checkout_after_order_review', [$this, 'render_review_custom_content']);

		/**
		 * Thank you page
		 */
		add_action('woocommerce_before_thankyou', [$this, 'render_thankyou_heading']);
		add_filter('woocommerce_thankyou_order_received_text', [$this, 'render_thankyou_text'], 20, 2);
		add_filter('woocommerce_order_get_formatted_shipping_address', [$this, '__tweak_thankyou_addresses'], 20);
		add_filter('woocommerce_order_get_formatted_billing_address', [$this, '__tweak_thankyou_addresses'], 20);
		// add_action('woocommerce_thankyou', [$this, '__render_thankyou_footer'], 30);

	}

	public function render_main(){

		wc_load_cart();

		// Show notice to change the page.
		if( current_user_can( 'administrator' ) && get_the_ID() !== wc_get_page_id( 'checkout' ) ){
			printf( __('<h4>WooCommerce Checkout is not set to be this page, which causes this element to behave improperly. Please access <a href="%s" target="_blank"><u>WooCommerce > Settings > Advanced</u></a> and pick this page and save.</h4>', 'rey-core') , admin_url('admin.php?page=wc-settings&tab=advanced') );
			return;
		}

		$edit_mode = reycore__elementor_edit_mode();

		if( WC()->cart->is_empty()){
			if( $edit_mode ){
				printf('<h4>%s</h4>', __('No products in cart. To be able to edit the Checkout form layout, please add at least one product in cart.', 'rey-core'));
				return;
			}
		}

		// load modal scripts
		add_filter( 'reycore/modals/always_load', '__return_true');

		reycore_assets()->add_styles(['rey-wc-cart', 'rey-wc-checkout', 'reycore-tooltips']);

		if( $this->_settings['layout'] === 'custom'){
			$this->display_custom_layout();
		}

		// Preview ThankYou page
		if (
			$edit_mode &&
			current_user_can( 'administrator' ) &&
			isset($this->_settings['thankyou_preview']) &&
			'' !== $this->_settings['thankyou_preview']
		) {

			$user_id = get_current_user_id(); // The current user ID

			// Get the WC_Customer instance Object for the current user
			$customer = new \WC_Customer( $user_id );

			if( 'failed' === $this->_settings['thankyou_preview'] ){
				add_filter( 'woocommerce_order_has_status', function($value, $order, $status){
					if( 'failed' === $status ){
						return true;
					}
					return $value;
				}, 10, 3 );
			}

			// Get the last WC_Order Object instance from current customer
			$order = $customer->get_last_order();

			if( ! $order ){
				printf('<h4>%s</h4>', __('Please make a sample order for this preview to work.', 'rey-core'));
				return;
			}

			wc_get_template( 'checkout/thankyou.php', ['order' => $order] );
			return;
		}

		// render the shortcode
		$this->shortcode_output();
	}

	public function shortcode_output(){

		if( class_exists('\WC_Shortcode_Checkout') ){
			\WC_Shortcode_Checkout::output([]);
		}

	}

	public function render_start(){

		$this->_settings = $this->get_settings_for_display();

		// these settings will be used in edit mode
		$GLOBALS['reycore_checkout_settings'] = $this->_settings;

		$this->_options = apply_filters('reycore/woocommerce/checkout/options', [
			'gallery_columns' => 4,
			'gallery_thumbnail_size' => 'thumbnail',
			'gallery_random' => false,
			'rearrange_country_state_zip' => $this->_settings['rearrange_country_state_zip'] !== ''
		]);

		$classes = [
			'woocommerce',
			'rey-checkoutPage',
			'--layout-' . $this->_settings['layout'],
			'--crumbs-' . $this->_settings['crumbs_style'],
			$this->_options['rearrange_country_state_zip'] ? '--rearr-csz' : '',
			$this->_settings['show_billing_first'] === 'yes' ? '--bfirst' : '',
			$this->_settings['disable_shipping_step'] === 'yes' ? '--nosh' : '',
			$this->_settings['disable_shipping_in_information'] === 'yes' ? '--nosh-info' : '',
			// $this->_settings['debug_mode'] === 'yes' && current_user_can( 'administrator' ) ? '--debug' : '',
			$this->_settings['use_steps'] === '' ? '--no-steps' : '--steps',
		];

		$this->add_render_attribute( 'wrapper', 'data-steps', $this->_settings['use_steps'] !== '' ? 'yes' : 'no' );

		if($this->_settings['review_sticky'] === 'yes' ){
			$classes[] = '--sticky-review';

			if( ! empty($this->_settings['review_sticky_offset']) ){
				$this->add_render_attribute( 'wrapper', 'data-sticky-offset', esc_attr($this->_settings['review_sticky_offset']) );
			}

			reycore_assets()->add_scripts('reycore-sticky');
		}

		$this->add_render_attribute( 'wrapper', 'class', $classes );
		$this->add_render_attribute( 'wrapper', 'data-active-step', 'info' );
		?>

		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>> <?php
	}

	public function render_end(){
		?>
		</div><?php
	}

	protected function render() {

		if( defined('REYCORE_CHECKOUT_WIDGET_RENDER') && REYCORE_CHECKOUT_WIDGET_RENDER ){
			if( current_user_can('administrator') ){
				echo '<div class="woocommerce"><small>Checkout Widget already added.</small></div>';
			}
			return;
		}

		// prevent double render
		define('REYCORE_CHECKOUT_WIDGET_RENDER', true);

		if( ! apply_filters('reycore/woocommerce/checkout/supports_element', ! (defined('REYCORE_DISABLE_CHECKOUT') && REYCORE_DISABLE_CHECKOUT) ) ){
			echo '<div class="woocommerce">';
			$this->shortcode_output();
			echo '</div>';
			return;
		}

		reycore_assets()->add_styles($this->get_style_name());
		reycore_assets()->add_scripts( $this->rey_get_script_depends() );

		$this->render_start();
		$this->render_main();
		$this->render_end();

		\ReyCore\Plugin::instance()->js_icons->include_icons( 'help' );
	}

	protected function content_template() {}
}
