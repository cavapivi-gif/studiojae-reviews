<?php
namespace ReyCore\Modules\CustomTemplates;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WooPdpTabBlock extends WooBase {

	public $_settings = [];

	public function get_name() {
		return 'reycore-woo-pdp-tab-block';
	}

	public function get_title() {
		return __( 'Single Tab/Block (PDP)', 'rey-core' );
	}

	public function get_icon() {
		return $this->get_icon_class();
	}

	public function get_categories() {
		return [ 'rey-woocommerce-pdp' ];
	}

	public function show_in_panel() {
		return $this->maybe_show_in_panel();
	}

	// public function get_custom_help_url() {
	// 	return '';
	// }

	/**
	 * Register widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function element_register_controls() {

		$this->start_controls_section(
			'section_settings',
			[
				'label' => __( 'Settings', 'rey-core' ),
			]
		);

			$this->add_control(
				'block',
				[
					'label' => esc_html__( 'Tab / Block', 'rey-core' ),
					'default' => '',
					'type' => 'rey-ajax-list',
					'query_args' => [
						'request' => 'get_tabs'
					],
				]
			);

			$this->add_control(
				'customize_settings_notice',
				[
					'type' => \Elementor\Controls_Manager::RAW_HTML,
					'content_classes' => 'rey-raw-html',
					'raw' => sprintf( _x( '<a href="%s" target="_blank" class="__title-link">Customize tabs/blocks<i class="eicon-editor-external-link"></i></a><br>Access Customizer > WooCommerce > Product Tabs/Blocks to switch layouts, disable tabs/blocks, create new ones and change other settings.', 'Elementor control label', 'rey-core' ), add_query_arg( ['autofocus[control]' => 'single_product_atc__price'], admin_url( 'customize.php' ) ) ),
				]
			);

		$this->end_controls_section();

	}

	function render_template() {

		$this->_settings = $this->get_settings_for_display();

		if( ! $this->_settings['block'] ){
			return;
		}

		$tabs_blocks = apply_filters('woocommerce_product_tabs', []);

		if( isset( $tabs_blocks[ $this->_settings['block'] ]['callback'] ) && $cb = $tabs_blocks[ $this->_settings['block'] ]['callback'] ){
			call_user_func($cb);
		}

	}


}
