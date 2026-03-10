<?php
namespace ReyCore\Elementor\Widgets\Menu;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class SkinAccountMenu extends \Elementor\Skin_Base
{

	public function get_id() {
		return 'account-menu';
	}

	public function get_title() {
		return __( 'Account Menu (WooCommerce)', 'rey-core' );
	}

	protected function _register_controls_actions() {
		parent::_register_controls_actions();

		add_action( 'elementor/element/reycore-menu/section_settings/before_section_end', [ $this, 'register_items_controls' ] );
	}

	public function register_items_controls( $element ){

		$element->add_control(
			'show_logged_out_menu',
			[
				'label' => esc_html__( 'Show Login/Register?', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
				'condition' => [
					'_skin' => 'account-menu',
				],
			]
		);

		$element->add_control(
			'singin_text',
			[
				'label' => esc_html__( 'Login Text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'LOGIN', 'rey-core' ),
				'placeholder' => esc_html__( 'eg: Login', 'rey-core' ),
				'condition' => [
					'_skin' => 'account-menu',
					'show_logged_out_menu' => 'yes',
				],
			]
		);

		$element->add_control(
			'singin_custom_url',
			[
				'label' => __( 'Custom "Login" page URL', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::URL,
				'dynamic' => [
					'active' => true,
				],
				'default' => [],
				'condition' => [
					'_skin' => 'account-menu',
					'show_logged_out_menu' => 'yes',
				],
			]
		);

		$element->add_control(
			'singup_text',
			[
				'label' => esc_html__( 'Register Text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'REGISTER', 'rey-core' ),
				'placeholder' => esc_html__( 'eg: Register', 'rey-core' ),
				'condition' => [
					'_skin' => 'account-menu',
					'show_logged_out_menu' => 'yes',
				],
			]
		);

		$element->add_control(
			'singup_custom_url',
			[
				'label' => __( 'Custom "Register" page URL', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::URL,
				'dynamic' => [
					'active' => true,
				],
				'default' => [],
				'condition' => [
					'_skin' => 'account-menu',
					'show_logged_out_menu' => 'yes',
				],
			]
		);
	}

	public function render_menu($settings)
	{
		if( ! \ReyCore\Plugin::instance()->woo ){
			return;
		}

		$items = wc_get_account_menu_items();

		if( !empty($items) ){

			echo '<div class="reyEl-menu-navWrapper">';

				printf('<ul class="reyEl-menu-nav rey-navEl --menuHover-%s">', $settings['hover_style']);

				foreach ($items as $endpoint => $label) {

					$url = esc_url( wc_get_account_endpoint_url( $endpoint ) );
					$is_active = reycore__current_url() === $url;

					printf(
						'<li class="menu-item %3$s"><a href="%2$s"><span>%1$s</span></a></li>',
						apply_filters('reycore/woocommerce/account-menu/link_label', wp_kses_post($label), $endpoint),
						$url,
						($is_active ? 'current-menu-item' : '')
					);
				}

				echo '</ul>';
			echo '</div>';
		}
	}

	public function render() {

		if( ! class_exists('\WooCommerce') ){
			return;
		}

		reycore_assets()->add_styles( [
			$this->parent->get_style_name('style'),
			$this->parent->get_style_name('account'),
		] );

		$settings = $this->parent->get_settings_for_display();

		$this->parent->render_start();

		if( is_user_logged_in() ){
			$this->parent->render_title();
			$this->render_menu($settings);
		}

		else {
			if( $settings['show_logged_out_menu'] === 'yes' ){

				$login_url = $register_url = $account_url = esc_url( get_permalink( wc_get_page_id('myaccount') ) );

				echo '<div class="reyEl-menu-acc">';

				if ( ! empty( $settings['singin_custom_url']['url'] ) ) {
					$this->parent->add_render_attribute( 'login_url', 'href', $settings['singin_custom_url']['url'] );
					if ( $settings['singin_custom_url']['is_external'] ) {
						$this->parent->add_render_attribute( 'login_url', 'target', '_blank' );
					}
					if ( ! empty( $settings['singin_custom_url']['nofollow'] ) ) {
						$this->parent->add_render_attribute( 'login_url', 'rel', 'nofollow' );
					}
				}
				else {
					$this->parent->add_render_attribute( 'login_url', 'href', $login_url );
				}

				reycore_assets()->add_styles('rey-buttons');

				if( $login = $settings['singin_text'] ){
					printf( '<a class="btn btn-secondary reyEl-menu--accLogin" %2$s><span>%1$s</span></a>', $login, $this->parent->get_render_attribute_string( 'login_url' ) );
				}


				if ( ! empty( $settings['singup_custom_url']['url'] ) ) {
					$this->parent->add_render_attribute( 'register_url', 'href', $settings['singup_custom_url']['url'] );
					if ( $settings['singup_custom_url']['is_external'] ) {
						$this->parent->add_render_attribute( 'register_url', 'target', '_blank' );
					}
					if ( ! empty( $settings['singup_custom_url']['nofollow'] ) ) {
						$this->parent->add_render_attribute( 'register_url', 'rel', 'nofollow' );
					}
				}
				else {
					$this->parent->add_render_attribute( 'register_url', 'href', $register_url );
				}

				if( $register = $settings['singup_text'] ){
					printf( '<a class="btn btn-primary reyEl-menu--accReg" %2$s><span>%1$s</span></a>', $register, $this->parent->get_render_attribute_string( 'register_url' ) );
				}

				echo '</div>';
			}
		}

		$this->parent->render_end();
	}
}
