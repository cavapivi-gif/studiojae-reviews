<?php
namespace ReyCore\Customizer\Options\Woocommerce;

if ( ! defined( 'ABSPATH' ) ) exit;

use \ReyCore\Customizer\Controls;

class Checkout extends \ReyCore\Customizer\SectionsBase {

	const START_PRIORITY = 850;

	public static function get_id(){
		return 'woo-checkout';
	}

	public function get_title(){
		return esc_html__('Checkout', 'rey-core');
	}

	public function get_priority(){
		return 120;
	}

	public function get_title_after(){
		return esc_html__('MODULES', 'rey-core');
	}

	public function get_icon(){
		return 'woo-checkout';
	}

	public function get_breadcrumbs(){
		return ['WooCommerce', 'Pages'];
	}

	public function customize_register(){

		global $wp_customize;

		$priority = self::START_PRIORITY;

		foreach ([

			// Texts
			'woocommerce_checkout_privacy_policy_text',
			'woocommerce_checkout_terms_and_conditions_checkbox_text',

			// Pages
			'wp_page_for_privacy_policy',
			'woocommerce_terms_page_id',

			// Fields
			'woocommerce_checkout_company_field',
			'woocommerce_checkout_address_2_field',
			'woocommerce_checkout_phone_field',
			'woocommerce_checkout_highlight_required_fields',

		] as $control) {

			if( $the_control = $wp_customize->get_control( $control ) ){
				$the_control->section = self::get_id();
				$the_control->priority = $priority;
				$priority += 10;
			}

		}

	}

	// public function help_link(){
	// 	return '';
	// }

	public function controls(){

		$this->add_section_marker('before_controls');

		$this->add_title( esc_html__('SETTINGS', 'rey-core'), [
			'separator' => false
		]);

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'checkout_distraction_free',
			'label'       => esc_html__( 'Distraction Free Checkout', 'rey-core' ),
			'description' => esc_html__( 'This option disables header for the checkout page, to prevent user distractions.', 'rey-core' ),
			'default'     => false,
		] );

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'checkout_add_thumbs',
			'label'       => esc_html__( 'Add thumbnails (in Classic layout)', 'rey-core' ),
			'description' => esc_html__( 'This option enables thumbnails in the order review block.', 'rey-core' ),
			'default'     => true,

			'separator' => 'before'
		] );

		$this->add_section_marker('before_texts');

		$this->add_title( esc_html__('TEXTS', 'rey-core'), [
			'priority' => (self::START_PRIORITY - 1),
		]);

		$this->add_title( esc_html__('PAGES', 'rey-core'), [
			'priority' => (self::START_PRIORITY + 19),
		]);

		$this->add_title( esc_html__('FORM FIELDS', 'rey-core'), [
			'priority' => (self::START_PRIORITY + 39),
		]);

	}
}
