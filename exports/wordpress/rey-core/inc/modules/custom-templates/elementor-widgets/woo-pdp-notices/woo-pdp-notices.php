<?php
namespace ReyCore\Modules\CustomTemplates;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WooPdpNotices extends WooBase {

	public function get_name() {
		return 'reycore-woo-pdp-notices';
	}

	public function get_title() {
		return __( 'Page Notices (PDP)', 'rey-core' );
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
				'customize_settings_notice',
				[
					'type' => \Elementor\Controls_Manager::RAW_HTML,
					'content_classes' => 'rey-raw-html',
					'raw' => _x( 'This element will output the WooCommerce notices.', 'Elementor control label', 'rey-core' ),
				]
			);

		$this->end_controls_section();

	}

	function render_template() {

		if( ! is_object(WC()->session) ){
			return;
		}

		woocommerce_output_all_notices();

	}

}
