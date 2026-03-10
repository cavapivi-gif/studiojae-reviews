<?php
namespace ReyCore\Modules\StoreNotice;

if ( ! defined( 'ABSPATH' ) ) exit;

class Customizer extends \ReyCore\Customizer\SectionsBase {

	public static function get_id(){
		return 'woo-store-notice';
	}

	public function get_title(){
		return esc_html__('Store Notice', 'rey-core');
	}

	public function get_priority(){
		return 150;
	}

	public function get_breadcrumbs(){
		return ['WooCommerce', 'Modules'];
	}

	public function get_icon(){
		return 'woo-store-notice';
	}

	public function customize_register(){

		global $wp_customize;

		foreach ([
			'woocommerce_demo_store',
			'woocommerce_demo_store_notice',
		] as $control) {
			$wp_customize->get_control( $control )->section = self::get_id();
		}

	}

	public function controls(){

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'woocommerce_store_notice_close_style',
			'label'       => esc_html__( 'Close Style', 'rey-core' ),
			'default'     => 'default',
			'priority'    => 20,
			'choices'     => [
				'default' => esc_html__( 'Default', 'rey-core' ),
				'icon-inside' => esc_html__( 'Close Icon (Inside)', 'rey-core' ),
				'icon-outside' => esc_html__( 'Close Icon (Outside)', 'rey-core' ),
			],
		] );

		$this->add_control( [
			'type'        => 'typography',
			'settings'    => 'woocommerce_store_notice_typography',
			'label'       => esc_attr__('Typography', 'rey-core'),
			'transport' => 'auto',
			'priority' => 20,
			'default'     => [
				'font-family'      => '',
				'font-size'      => '',
				'line-height'    => '',
				'letter-spacing' => '',
				'text-transform' => '',
				'variant' => '',
				'font-weight' => '',
			],
			'output' => [
				[
					'element' => '.woocommerce-store-notice .woocommerce-store-notice-content',
				],
			],
			'load_choices' => true,
		]);

		$this->add_control( [
			'type'        => 'rey-color',
			'settings'    => 'woocommerce_store_notice_text_color',
			'label'       => esc_html__( 'Text Color', 'rey-core' ),
			'default'     => '',
			'transport' => 'auto',
			'priority' => 20,
			'choices'     => [
				'alpha' => true,
			],
			'output' => [
				[
					'element' => '.woocommerce-store-notice .woocommerce-store-notice-content',
					'property' => 'color',
				],
			],
		] );

		$this->add_control( [
			'type'        => 'rey-color',
			'settings'    => 'woocommerce_store_notice_bg_color',
			'label'       => esc_html__( 'Background Color', 'rey-core' ),
			'default'     => '',
			'transport' => 'auto',
			'priority' => 20,
			'choices'     => [
				'alpha' => true,
			],
			'output' => [
				[
					'element' => '.woocommerce-store-notice',
					'property' => 'background-color',
				],
			],
		] );

		$this->add_control( [
			'type'        => 'slider',
			'settings'    => 'woocommerce_store_notice_height',
			'label'       => esc_html__( 'Height', 'rey-core' ),
			'default'     => 32,
			'priority' => 20,
			'choices'     => [
				'min'  => 0,
				'max'  => 300,
				'step' => 1,
				'suffix' => 'px'
			],
			'transport' => 'auto',
			'output' => [
				[
					'element' => '.woocommerce-store-notice .woocommerce-store-notice-content',
					'property' => 'min-height',
					'units' => 'px',
				],
			],
		] );
	}
}
