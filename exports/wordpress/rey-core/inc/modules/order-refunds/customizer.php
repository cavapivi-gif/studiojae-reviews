<?php
namespace ReyCore\Modules\OrderRefunds;

if ( ! defined( 'ABSPATH' ) ) exit;

class Customizer extends \ReyCore\Customizer\SectionsBase {

	public static function get_id(){
		return 'refunds';
	}

	public function get_title(){
		return esc_html__('Refunds form', 'rey-core');
	}

	public function get_priority(){
		return 150;
	}

	public function get_breadcrumbs(){
		return ['WooCommerce', 'Modules'];
	}

	public function get_icon(){
		return 'woo-refunds-form';
	}

	public function controls(){

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'refunds__enable',
			'label'       => esc_html__( 'Enable Refunds', 'rey-core' ),
			'description'       => esc_html__( 'Once enabled, a new page in your Account dashboard should be visible. This page contains a contact form which will help customers choose a product from a specific order, to ask for a refund. ', 'rey-core' ) . sprintf(__('<br><strong>Note:</strong> <a href="%s" target="_blank">Resave Permalinks</a> or it will give 404 error.', 'rey-core'), admin_url('options-permalink.php')),
			'default'     => false,
		] );

		$this->add_control( [
			'type'        => 'text',
			'settings'    => 'refunds__menu_text',
			'label'       => esc_html__('Menu text', 'rey-core'),
			'help' => [
				__('This text will show up in the My Account dashboard menu.', 'rey-core')
			],
			'default'     => esc_html__('Request Return', 'rey-core'),
			'active_callback' => [
				[
					'setting'  => 'refunds__enable',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$this->add_control( [
			'type'        => 'text',
			'settings'    => 'refunds__page_title',
			'label'       => esc_html__('Page Title', 'rey-core'),
			'help' => [
				__('This page title show up in the My Account dashboard menu - Returns page.', 'rey-core')
			],
			'default'     => esc_html__('Request Return', 'rey-core'),
			'active_callback' => [
				[
					'setting'  => 'refunds__enable',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );


		$this->add_control( [
			'type'        => 'editor',
			'settings'    => 'refunds__content',
			'label'       => esc_html__( 'Content before form', 'rey-core' ),
			'default'     => '',
			'active_callback' => [
				[
					'setting'  => 'refunds__enable',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

	}
}
