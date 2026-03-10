<?php
namespace ReyCore\Compatibility\WooAdvancedProductSizeChart;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Customizer extends \ReyCore\Customizer\SectionsBase {

	public static function get_id(){
		return 'wapsc-settings';
	}

	public function get_title(){
		return esc_html__('Plugin: Size Charts', 'rey-core');
	}

	public function get_priority(){
		return 250;
	}

	public function controls(){

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'wapsc_button_position',
			'label'       => esc_html__( 'Button Position', 'rey-core' ),
			'default'     => 'before_atc',
			'choices'     => [
				'before_atc' => esc_html__('Before Add to cart button', 'rey-core'),
				'after_atc' => esc_html__('After Add to cart button', 'rey-core'),
				'inline_atc' => esc_html__('Inline with Add to cart button', 'rey-core'),
				'inline_attribute' => esc_html__('Inline with Attributes', 'rey-core'),
			],
		] );

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'wapsc_button_attribute',
			'label'       => esc_html__( 'Select Attribute', 'rey-core' ),
			'default'     => '',
			'active_callback' => [
				[
					'setting'  => 'wapsc_button_position',
					'operator' => '==',
					'value'    => 'inline_attribute',
				],
			],
			'choices'      => [
				'' => esc_html__('- Select -', 'rey-core')
			],
			'ajax_choices' => 'get_woo_attributes_list',
		] );

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'wapsc_button_style',
			'label'       => esc_html__( 'Button Style', 'rey-core' ),
			'default'     => 'line-active',
			'choices'     => [
				'primary' => 'Primary',
				'secondary' => 'Secondary',
				'line-active' => 'Underlined',
				'line' => 'Underlined on hover',
			],
			'active_callback' => [
				[
					'setting'  => 'wapsc_button_position',
					'operator' => '!=',
					'value'    => 'inline_attribute',
				],
			],
		] );
	}
}
