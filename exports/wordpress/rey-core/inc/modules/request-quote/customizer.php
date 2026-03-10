<?php
namespace ReyCore\Modules\RequestQuote;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Customizer
{
	public function __construct()
	{
		add_action('reycore/customizer/section=woo-product-page-summary-components/marker=before_meta', [$this, 'add_controls'], 20);
		add_action( 'elementor/element/reycore-product-grid/section_layout_components/before_section_end', [ $this, 'elementor__add_pg_control' ], 30 );
		add_action( 'elementor/element/reycore-woo-loop-products/section_layout_components/before_section_end', [ $this, 'elementor__add_pg_control' ], 30 );
	}

	public function add_controls( $section ){

		$section->start_controls_accordion([
			'label'  => esc_html__( 'Request a Quote (Send enquiry)', 'rey-core' ),
		]);

		$section->add_title( '', [
			'description' => esc_html__('Add a button in product pages that opens a modal containing a contact form.', 'rey-core'),
			'separator' => 'none',
		]);

		$section->add_control( [
			'type'        => 'select',
			'settings'    => 'request_quote__type',
			'label'       => esc_html__( 'Select which products', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'' => esc_html__( 'None', 'rey-core' ),
				'all' => esc_html__( 'All products', 'rey-core' ),
				'products' => esc_html__( 'Specific products', 'rey-core' ),
				'categories' => esc_html__( 'Specific category products', 'rey-core' ),
			],
		] );

		$section->add_control( [
			'type'        => 'text',
			'settings'    => 'request_quote__products',
			'label'       => esc_html__( 'Product IDs (Comma separated)', 'rey-core' ),
			'placeholder' => esc_html__( 'eg: 100, 101, 102', 'rey-core' ),
			'default'     => '',
			'active_callback' => [
				[
					'setting'  => 'request_quote__type',
					'operator' => '==',
					'value'    => 'products',
				],
			],
		] );

		$section->add_control( [
			'type'        => 'select',
			'settings'    => 'request_quote__categories',
			'label'       => esc_html__( 'Select categories', 'rey-core' ),
			'default'     => '',
			'multiple'    => 100,
			'query_args' => [
				'type' => 'terms',
				'taxonomy' => 'product_cat',
			],
			'active_callback' => [
				[
					'setting'  => 'request_quote__type',
					'operator' => '==',
					'value'    => 'categories',
				],
			],
		] );

		$section->add_control( [
			'type'        => 'select',
			'settings'    => 'request_quote__form_type',
			'label'       => esc_html__( 'Select Form Type', 'rey-core' ),
			'default'     => 'cf7',
			'choices'     => [
				'cf7' => esc_html__( 'Contact Form 7', 'rey-core' ) . (! class_exists('\WPCF7') ? esc_html__(' (Not installed)', 'rey-core') : ''),
				'wpforms' => esc_html__( 'WP Forms', 'rey-core' ) . (! function_exists('wpforms') ? esc_html__(' (Not installed)', 'rey-core') : ''),
			],
			'active_callback' => [
				[
					'setting'  => 'request_quote__type',
					'operator' => '!=',
					'value'    => '',
				],
			],
		] );

		$section->add_control( [
			'type'        => 'select',
			'settings'    => 'request_quote__cf7',
			'label'       => esc_html__( 'Select Contact Form', 'rey-core' ),
			'description' => apply_filters('reycore/cf7/control_description', ''),
			'default'     => '',
			'choices'     => apply_filters('reycore/cf7/forms', []),
			'active_callback' => [
				[
					'setting'  => 'request_quote__type',
					'operator' => '!=',
					'value'    => '',
				],
				[
					'setting'  => 'request_quote__form_type',
					'operator' => '==',
					'value'    => 'cf7',
				],
			],
		] );

		$section->add_control( [
			'type'        => 'select',
			'settings'    => 'request_quote__wpforms',
			'label'       => esc_html__( 'Select Contact Form', 'rey-core' ),
			'description' => apply_filters('reycore/wpforms/control_description', ''),
			'default'     => '',
			'choices'     => apply_filters('reycore/wpforms/forms', []),
			'active_callback' => [
				[
					'setting'  => 'request_quote__type',
					'operator' => '!=',
					'value'    => '',
				],
				[
					'setting'  => 'request_quote__form_type',
					'operator' => '==',
					'value'    => 'wpforms',
				],
			],
		] );



		$section->add_control( [
			'type'        => 'text',
			'settings'    => 'request_quote__btn_text',
			'label'       => esc_html__( 'Button Text', 'rey-core' ),
			'placeholder' => esc_html__( 'eg: Request a quote', 'rey-core' ),
			'default'     => esc_html__( 'Request a Quote', 'rey-core' ),
			'active_callback' => [
				[
					'setting'  => 'request_quote__type',
					'operator' => '!=',
					'value'    => '',
				],
			],
		] );

		$section->add_control( [
			'type'        => 'select',
			'settings'    => 'request_quote__btn_style',
			'label'       => esc_html__( 'Button Style', 'rey-core' ),
			'default'     => 'btn-line-active',
			'choices'     => [
				'btn-line-active' => esc_html__( 'Underlined', 'rey-core' ),
				'btn-primary' => esc_html__( 'Regular', 'rey-core' ),
				'btn-primary btn--block' => esc_html__( 'Regular & Full width', 'rey-core' ),
				'btn-primary-outline' => esc_html__( 'Regular outline', 'rey-core' ),
				'btn-primary-outline btn--block' => esc_html__( 'Regular outline & Full width', 'rey-core' ),
				'btn-secondary' => esc_html__( 'Secondary', 'rey-core' ),
				'btn-secondary btn--block' => esc_html__( 'Secondary & Full width', 'rey-core' ),
			],
			'active_callback' => [
				[
					'setting'  => 'request_quote__type',
					'operator' => '!=',
					'value'    => '',
				],
			],
		] );

		$section->add_control( [
			'type'        => 'textarea',
			'settings'    => 'request_quote__btn_text_after',
			'label'       => esc_html__( 'Text after button', 'rey-core' ),
			'default'     => '',
			'active_callback' => [
				[
					'setting'  => 'request_quote__type',
					'operator' => '!=',
					'value'    => '',
				],
			],
		] );

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'request_quote__var_aware',
			'label'       => _x('Variation aware', 'Customizer Control Label', 'rey-core'),
			'help' => [
				_x('If enabled, the button will only be clickable when a variation is selected.', 'Customizer Control Label', 'rey-core')
			],
			'default'     => false,
			'active_callback' => [
				[
					'setting'  => 'request_quote__type',
					'operator' => '!=',
					'value'    => '',
				],
			],
		] );

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'request_quote__catalog',
			'label'       => _x('Show in Catalog products', 'Customizer Control Label', 'rey-core'),
			'help' => [
				_x('If enabled, the button be displayed in the catalog too.', 'Customizer Control Label', 'rey-core')
			],
			'default'     => false,
			'active_callback' => [
				[
					'setting'  => 'request_quote__type',
					'operator' => '!=',
					'value'    => '',
				],
			],
		] );

		$section->end_controls_accordion();

	}

	public function elementor__add_pg_control( $stack ){

		$stack->start_injection( [
			'of' => 'hide_new_badge',
		] );

		$stack->add_control(
			'hide_request_quote',
			[
				'label' => esc_html__( 'Request a quote button', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => esc_html__( '- Inherit -', 'rey-core' ),
					'no'  => esc_html__( 'Show', 'rey-core' ),
					'yes'  => esc_html__( 'Hide', 'rey-core' ),
				],
				'condition' => [
					'loop_skin!' => 'template',
				],
			]
		);

		$stack->end_injection();

	}
}
