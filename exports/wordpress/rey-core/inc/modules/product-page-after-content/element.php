<?php
namespace ReyCore\Modules\ProductPageAfterContent;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Element extends \ReyCore\Modules\CustomTemplates\WooBase {

	public function get_name() {
		return 'reycore-woo-pdp-after-summary-gs';
	}

	public function get_title() {
		return __( 'After Summary Global Section (PDP)', 'rey-core' );
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
				'mode',
				[
					'label' => esc_html__( 'Mode', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'after_summary',
					'options' => [
						'after_summary'  => esc_html__( 'After Summary', 'rey-core' ),
						'after_content'  => esc_html__( 'After Content', 'rey-core' ),
					],
					'description' => sprintf( _x( '<a href="%s" target="_blank" class="__title-link">Customize and add new "after summary / content" global sections <i class="eicon-editor-external-link"></i></a> in Customizer > WooCommerce > Product page - Content.', 'Elementor control label', 'rey-core' ), add_query_arg( ['autofocus[control]' => 'product_content_after_summary'], admin_url( 'customize.php' ) ) ),
				]
			);

		$this->end_controls_section();

	}

	function render_template() {

		$mode = $this->get_settings_for_display('mode');

		if( 'after_summary' === $mode ){
			Base::instance()->global_section_after_product_summary();
		}
		elseif( 'after_content' === $mode ){
			Base::instance()->global_section_after_content();
		}
	}

}
