<?php
namespace ReyCore\Compatibility\WpStoreLocator;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Customizer extends \ReyCore\Customizer\SectionsBase {

	public static function get_id(){
		return 'wpsl-settings';
	}

	public function get_title(){
		return esc_html__('Plugin: WP Store Locator', 'rey-core');
	}

	public function get_priority(){
		return 250;
	}

	public function controls(){

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'wpsl_enable_button',
			'label'       => esc_html__( 'Enable Button in Product Page', 'rey-core' ),
			'description' => esc_html__( 'Enable or disable a "Find Dealer" button in the Product page.', 'rey-core' ),
			'default'     => class_exists('\ACF') && get_field('wpsl_enable_button', REY_CORE_THEME_NAME),
		] );

		$this->add_control( [
			'type'        => 'text',
			'settings'    => 'wpsl_button_text',
			'label'       => esc_html__( 'Button Text', 'rey-core' ),
			'default'     => class_exists('\ACF') ? get_field('wpsl_button_text', REY_CORE_THEME_NAME) : '',
			'input_attrs'     => [
				'placeholder' => esc_html__('eg: FIND A DEALER', 'rey-core'),
			],
			'active_callback' => [
				[
					'setting'  => 'wpsl_enable_button',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$btn_url = '';
		if( ($acf_btn_url = get_field('wpsl_button_url', REY_CORE_THEME_NAME)) && isset($acf_btn_url['url']) ){
			$btn_url = $acf_btn_url['url'];
		}

		$this->add_control( [
			'type'        => 'text',
			'settings'    => 'wpsl_button_url',
			'label'       => esc_html__( 'Button URL', 'rey-core' ),
			'default'     => $btn_url,
			'active_callback' => [
				[
					'setting'  => 'wpsl_enable_button',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );
	}
}
