<?php
namespace ReyCore\Customizer\Options\Header;

if ( ! defined( 'ABSPATH' ) ) exit;

use \ReyCore\Customizer\Controls;

class Account extends \ReyCore\Customizer\SectionsBase {

	public static function get_id(){
		return 'header-account';
	}

	public function get_title(){
		return esc_html__('Account (Button & Panel)', 'rey-core');
	}

	public function get_priority(){
		return 50;
	}

	public function can_load(){
		return class_exists('\WooCommerce');
	}

	public function get_icon(){
		return 'header-account';
	}

	public function help_link(){
		return reycore__support_url('kb/customizer-header-settings/#account');
	}

	public function controls(){

		// Setting available only for the Default Header
		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'header_enable_account',
			'label'       => esc_html__( 'Enable My Account panel?', 'rey-core' ),
			'default'     => false,
			'active_callback' => [
				[
					'setting'  => 'header_layout_type',
					'operator' => '==',
					'value'    => 'default',
				],
			],
		] );

		$this->add_title( esc_html__('Button settings', 'rey-core'), [
			'separator' => 'none',
		]);

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'header_account_type',
			'label'       => esc_html__( 'Type of button', 'rey-core' ),
			'default'     => 'text',
			'choices'     => [
				'text' => esc_html__( 'Text', 'rey-core' ),
				'icon' => esc_html__( 'Icon', 'rey-core' ),
				'both_before' => esc_html__( 'Text & Icon Before', 'rey-core' ),
				'both_after' => esc_html__( 'Text & Icon After', 'rey-core' ),
				'both_above' => esc_html__( 'Text Under', 'rey-core' ),
			],
		] );

		$this->add_control( [
			'type'     => 'text',
			'settings' => 'header_account_text',
			'label'    => esc_html__( 'Button text label', 'rey-core' ),
			'default'  => esc_html__( 'ACCOUNT', 'rey-core' ),
			'active_callback' => [
				[
					'setting'  => 'header_account_type',
					'operator' => 'in',
					'value'    => ['text', 'both_before', 'both_after', 'both_above'],
				],
			],
		] );

		$this->add_control( [
			'type'     => 'text',
			'settings' => 'header_account_text_logged_in',
			'label'       => __('Button text label (Logged in)', 'rey-core'),
			'help' => [
				__('Optional. Text to display when user is logged in.', 'rey-core')
			],
			'default'  => '',
			'active_callback' => [
				[
					'setting'  => 'header_account_type',
					'operator' => 'in',
					'value'    => ['text', 'both_before', 'both_after', 'both_above'],
				],
			],
		] );

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'header_account_icon_type',
			'label'       => esc_html__( 'Icon Type', 'rey-core' ),
			'default'     => 'rey-icon-user',
			'choices'     => [
				'rey-icon-user' => esc_html__( 'User Icon', 'rey-core' ),
				'reycore-icon-heart' => esc_html__( 'Heart Icon (Requires Rey-Core plugin)', 'rey-core' ),
			],
			'active_callback' => [
				[
					'setting'  => 'header_account_type',
					'operator' => 'in',
					'value'    => ['icon', 'both_before', 'both_after', 'both_above'],
				],
			],
		] );

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'header_account_mobile',
			'label'       => esc_html__( 'Hide on Mobiles?', 'rey-core' ),
			'default'     => false,
			'active_callback' => [
				[
					'setting'  => 'header_layout_type',
					'operator' => '==',
					'value'    => 'default',
				],
			],
		] );

		$this->add_title( esc_html__('Drop panel settings', 'rey-core') );

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'header_account__panel_display',
			'label'       => esc_html__( 'Display as', 'rey-core' ),
			'default'     => 'drop',
			'choices'     => [
				'drop' => esc_html__( 'Drop Down', 'rey-core' ),
				'offcanvas' => esc_html__( 'Off-canvas panel', 'rey-core' ),
			],
		] );

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'header_account__enable_forms',
			'label'       => esc_html__( 'Enable Forms/Account menu', 'rey-core' ),
			'default'     => true,
		] );

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'header_account__enable_ajax',
			'label'       => esc_html__( 'Enable Forms Ajax', 'rey-core' ),
			'default'     => apply_filters('reycore/header/account/ajax_forms', true),
			'active_callback' => [
				[
					'setting'  => 'header_account__enable_forms',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$this->add_control( [
			'type'        => 'select',
			'settings'    => 'header_account_redirect_type',
			'label'       => esc_html__( 'Login/Register action on success', 'rey-core' ),
			'default'     => 'load_menu',
			'choices'     => [
				'load_menu' => esc_html__( 'Show "My Account Menu"', 'rey-core' ),
				'refresh' => esc_html__( 'Refresh same page', 'rey-core' ),
				'myaccount' => esc_html__( 'Default (My Account)', 'rey-core' ),
				'url' => esc_html__( 'Go to custom URL', 'rey-core' ),
			],
			'active_callback' => [
				[
					'setting'  => 'header_account__enable_forms',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$this->add_control( [
			'type'     => 'text',
			'settings' => 'header_account_redirect_url',
			'label'       => __('Redirect URL', 'rey-core'),
			'help' => [
				__('Add here the URL where you want the visitor to redirect after login or register.', 'rey-core')
			],
			'default'  => '',
			'active_callback' => [
				[
					'setting'  => 'header_account_redirect_type',
					'operator' => '==',
					'value'    => 'url',
				],
				[
					'setting'  => 'header_account__enable_forms',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'header_account__close_on_scroll',
			'label'       => __('Close on scroll', 'rey-core'),
			'help' => [
				__('Will close the drop panel when scrolling outside the panel, in the page.', 'rey-core')
			],
			'default'     => false,
			'active_callback' => [
				[
					'setting'  => 'header_account__panel_display',
					'operator' => '==',
					'value'    => 'drop',
				],
			],
		] );

		$this->add_separator();


		$this->add_control( [
			'type'        => 'repeater',
			'settings'    => 'header_account_menu_items',
			'label'       => esc_html__('Menu - Extra Items', 'rey-core'),
			'description' => sprintf(__('Add extra menu items to the My Account menu. To hide default menu items, please access <a href="%s" target="_blank">WooCommerce Account endpoints</a> and empty the fields you don\'t want to display', 'rey-core'), admin_url('admin.php?page=wc-settings&tab=advanced') ),
			'row_label' => [
				'value' => esc_html__('Menu item', 'rey-core'),
				'type'  => 'field',
				'field' => 'text',
			],
			'button_label' => esc_html__('New menu item', 'rey-core'),
			'default'      => [],
			'fields' => [
				'text' => [
					'type'        => 'text',
					'label'       => esc_html__('Text', 'rey-core'),
				],
				'url' => [
					'type'        => 'text',
					'label'       => esc_html__('URL', 'rey-core'),
				],
			],
			'active_callback' => [
				[
					'setting'  => 'header_account__enable_forms',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$this->add_separator();


		$this->add_control( [
			'type'        => 'toggle',
			'settings'    => 'header_account_wishlist',
			'label'       => esc_html__( 'Enable Wishlist', 'rey-core' ),
			'default'     => true,
		] );

		$this->start_controls_group( [
			'label'    => esc_html__( 'Wishlist settings', 'rey-core' ),
			'active_callback' => [
				[
					'setting'  => 'header_account_wishlist',
					'operator' => '==',
					'value'    => true,
				],
			],
		]);

			$this->add_control( [
				'type'        => 'toggle',
				'settings'    => 'header_account_wishlist_counter',
				'label'       => esc_html__( 'Wishlist Counter', 'rey-core' ),
				'default'     => true,
			] );

		$this->end_controls_group();


		$this->add_notice([
			'default'     => __('In case these options doesn\'t seem to work, please check if you\'re using a Header Global Section and make sure the "Header - Account" element doesn\'t have the Override settings option enabled eg: <a href="https://d.pr/i/rbTmOb" target="_blank">https://d.pr/i/rbTmOb</a>.', 'rey-core'),
			'active_callback' => [
				[
					'setting'  => 'header_layout_type',
					'operator' => '!=',
					'value'    => 'default',
				],
			],
		] );

	}
}
