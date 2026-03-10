<?php
namespace ReyCore\Customizer\Options\Header;

if ( ! defined( 'ABSPATH' ) ) exit;

use \ReyCore\Customizer\Controls;

class Cta extends \ReyCore\Customizer\SectionsBase {

	public static function get_id(){
		return 'cta';
	}

	public function get_title(){
		return esc_html__('Call to action', 'rey-core');
	}

	public function get_priority(){
		return 80;
	}

	public function get_icon(){
		return 'header-cta';
	}

	public function controls(){

		$this->add_notice([
			'default'     => __('These options are only available for the Default Header layout.', 'rey-core'),
			'active_callback' => [
				[
					'setting'  => 'header_layout_type',
					'operator' => '!=',
					'value'    => 'default',
				],
			],
		] );

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'header_cta',
			'label'       => esc_html__( 'Enable Button?', 'rey-core' ),
			'default'     => false,
			'active_callback' => [
				[
					'setting'  => 'header_layout_type',
					'operator' => '==',
					'value'    => 'default',
				],
			],
		] );

		$condition = [
			[
				'setting'  => 'header_layout_type',
				'operator' => '==',
				'value'    => 'default',
			],
			[
				'setting'  => 'header_cta',
				'operator' => '==',
				'value'    => true,
			],
		];

		$this->add_control( [
			'type'        => 'text',
			'settings'    => 'header_cta__text',
			'label'       => esc_html__( 'Text', 'rey-core' ),
			'default'     => '',
			'input_attrs' => [
				'placeholder' => esc_html__('eg: Click here', 'rey-core'),
			],
			'active_callback' => $condition,
			'sanitize_callback' => 'wp_kses_post',
		] );

		$this->add_control( [
			'type'        => 'text',
			'settings'    => 'header_cta__url',
			'label'       => esc_html__( 'URL', 'rey-core' ),
			'default'     => '',
			'input_attrs' => [
				'placeholder' => esc_html__('eg: http://www.website.com/', 'rey-core'),
			],
			'active_callback' => $condition,
		] );

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'header_cta__style',
			'label'       => esc_html__( 'Style', 'rey-core' ),
			'default'     => 'btn-primary',
			'choices'     => [
				'btn-simple'                   => __( 'Link', 'rey-core' ),
				'btn-primary'                  => __( 'Primary', 'rey-core' ),
				'btn-secondary'                => __( 'Secondary', 'rey-core' ),
				'btn-primary-outline'          => __( 'Primary Outlined', 'rey-core' ),
				'btn-secondary-outline'        => __( 'Secondary Outlined', 'rey-core' ),
				'btn-line-active'              => __( 'Underlined', 'rey-core' ),
				'btn-line'                     => __( 'Hover Underlined', 'rey-core' ),
				'btn-primary-outline btn-dash' => __( 'Primary Outlined & Dash', 'rey-core' ),
				'btn-dash-line'                => __( 'Dash', 'rey-core' ),
			],
			'active_callback' => $condition,
		] );

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'header_cta__size',
			'label'       => esc_html__( 'Size', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				''   => __( 'Default', 'rey-core' ),
				'xs' => __( 'Small', 'rey-core' ),
				'md' => __( 'Medium', 'rey-core' ),
				'lg' => __( 'Large', 'rey-core' ),
				'xl' => __( 'Extra large', 'rey-core' ),
			],
			'active_callback' => $condition,
		] );

		$this->add_control( [
			'type'      => 'rey-number',
			'settings'  => 'header_cta__radius',
			'label'     => esc_html__( 'Corner Radius', 'rey-core' ) . ' (px)',
			'default'   => '',
			'choices'   => [
				'min'      => 0,
				'max'      => 150,
				'step'     => 1,
			],
			'transport' => 'auto',
			'output'    => [
				[
					'element'  => '.btn.rey-headerCta',
					'property' => '--btn-br',
					'units'    => 'px',
				],
			],
			'active_callback' => $condition,
		] );

		$this->add_control( [
			'type'        => 'rey-color',
			'settings'    => 'header_cta__color',
			'label'       => esc_html__( 'Text Color', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'alpha' => true,
			],
			'transport'   => 'auto',
			'output'      => [
				[
					'element'  		=> '.btn.rey-headerCta',
					'property' 		=> '--btn-color',
				],
			],
			'active_callback' => $condition,
		] );

		$this->add_control( [
			'type'        => 'rey-color',
			'settings'    => 'header_cta__bg_color',
			'label'       => esc_html__( 'Background Color', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'alpha' => true,
			],
			'transport'   => 'auto',
			'output'      => [
				[
					'element'  => '.btn.rey-headerCta',
					'property' => '--btn-bg-color',
				],
			],
			'active_callback' => $condition,
		] );


		$this->add_control( [
			'type'            => 'toggle',
			'settings'        => 'header_cta__mobile',
			'label'           => esc_html_x( 'Show on mobile', 'Customizer control title', 'rey-core' ),
			'default'         => false,
			'active_callback' => $condition,
		] );

	}
}
