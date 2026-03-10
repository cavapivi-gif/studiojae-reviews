<?php
namespace ReyCore\Modules\PdpBenefits;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Customizer
{
	public function __construct()
	{
		add_action('reycore/customizer/section=woo-product-page-summary-components/marker=before_meta', [$this, 'add_pdp_controls'], 20);
		add_action( 'customize_controls_print_scripts', [$this, 'customizer_scripts'] );
		add_action( 'reycore/ajax/register_actions', [ $this, 'register_actions' ] );
	}

	public function add_pdp_controls( $section ){

		$section->start_controls_accordion([
			'label'  => esc_html__( 'Product Benefits List', 'rey-core' ),
		]);

		$section->add_title( '', [
			'description' => _x('Display a list of benefits inside the product summary.', 'Customizer control text', 'rey-core'),
			'separator' => 'none',
		]);

		$section->add_control( [
			'type'        => 'select',
			'settings'    => 'pdp_benefits_gs',
			'label'       => esc_html__( 'Select Global Section', 'rey-core' ),
			'default'     => 'no',
			'choices'     => [
				'no'  => esc_attr__( '- Disabled -', 'rey-core' )
			],
			'ajax_choices' => 'get_global_sections',
			'edit_preview' => true,
		] );

		$section->add_control( [
			'type'        => 'hidden',
			'settings'    => 'pdp_benefits_gs_is_created',
			'default'     => '',
		] );

		$section->add_control( [
			'type'        => 'rey-button',
			'settings'    => 'pdp_benefits_create',
			'label'       => __('Create & Select Global Section', 'rey-core'),
			'description' => __('Click to create a generic global section with a list of styled product benefits, and automatically select it.', 'rey-core'),
			'default'     => '',
			'choices'     => [
				'text'   => esc_html__('Create', 'rey-core'),
				'action' => 'pdp_benefits_create_gs',
			],
			'active_callback' => [
				[
					'setting'  => 'pdp_benefits_gs_is_created',
					'operator' => '==',
					'value'    => '',
				],
			],
		] );

		$section->add_control( [
			'type'        => 'select',
			'settings'    => 'pdp_benefits_pos',
			'label'       => esc_html_x( 'Position', 'Customizer control text', 'rey-core' ),
			'default'     => 'before_meta',
			'choices'     => [
				'after_atc' => esc_html_x( 'After Add to cart', 'Customizer control text', 'rey-core' ),
				'before_meta' => esc_html_x( 'Before product meta', 'Customizer control text', 'rey-core' ),
				'after_meta' => esc_html_x( 'After product meta', 'Customizer control text', 'rey-core' ),
			],
			'active_callback' => [
				[
					'setting'  => 'pdp_benefits_gs',
					'operator' => '!=',
					'value'    => 'no',
				],
			],
		] );

		$section->end_controls_accordion();

	}

	public function customizer_scripts(){

		\wp_enqueue_script(
			Base::ASSET_HANDLE . '-admin',
			Base::get_path( basename( __DIR__ ) ) . '/admin.js',
			['jquery'],
			REY_CORE_VERSION,
			true
		);

	}

	public function register_actions($ajax_manager){
		$ajax_manager->register_ajax_action( 'pdp_benefits_create_gs', [$this, 'ajax__create_gs'], [
			'auth' => 1,
			'capability' => 'manage_woocommerce',
		] );
		$ajax_manager->register_ajax_action( 'pdp_benefits_check_gs', [$this, 'ajax__check_gs'], [
			'auth' => 1,
			'capability' => 'manage_woocommerce',
		] );
	}

	public function ajax__create_gs(){

		if( ! class_exists('\ReyCore\Elementor\GlobalSections') ){
			return [
				'errors' => [ 'Elementor is inactive.' ]
			];
		}

		add_filter('elementor/files/allow_unfiltered_upload', '__return_true');

		$import = \ReyCore\Elementor\Helper::importTemplateFromFile( 'https://rey-theme.s3.us-west-2.amazonaws.com/public/templates/product-benefits-list.json', true );

		if( ! is_array($import) ){
			return [
				'errors' => [ $import ]
			];
		}

		if( is_wp_error($import) ){
			return [
				'errors' => [ 'Something went wrong importing the template.' ]
			];
		}

		if( empty($import) ){
			return [
				'errors' => [ 'Something went wrong importing the template, and the list is empty.' ]
			];
		}

		$imported_template = $import[0];

		if( !isset($imported_template['template_id']) ){
			return [
				'errors' => [ 'Template ID is missing.' ]
			];
		}

		$post_data = [
			'ID' => $imported_template['template_id'],
			'post_type' => \ReyCore\Elementor\GlobalSections::POST_TYPE,
		];

		// internal
		$post_data['meta_input']['rey_pdp_benefits_list'] = true;
		// Global Section Type
		$post_data['meta_input']['gs_type'] = 'generic';
		$post_data['meta_input']['_gs_type'] = 'field_5c4c18fb06515';
		// Elementor Data
		$post_data['meta_input']['_elementor_template_type'] = 'wp-post';

		$update_post = wp_update_post( $post_data );

		if( is_wp_error($update_post) ){
			return [
				'errors' => [ 'Something went wrong importing the template.' ]
			];
		}

		// update is created flag
		set_theme_mod('pdp_benefits_gs_is_created', $imported_template['template_id']);

		return [
			'title' => $imported_template['title'],
			'id' => $imported_template['template_id']
		];

	}

	public function ajax__check_gs(){

		if( ! class_exists('\ReyCore\Elementor\GlobalSections') ){
			return;
		}

		$post_ids = get_posts([
			'post_type'   => \ReyCore\Elementor\GlobalSections::POST_TYPE,
			'numberposts' => -1,
			'post_status' => 'publish',
			'fields'      => 'ids',
			'meta_query'  => [
				[
					'key' => 'rey_pdp_benefits_list',
					'value'   => true,
					'compare' => '=='
				],
			]
		]);

		return ! empty($post_ids);

	}
}
