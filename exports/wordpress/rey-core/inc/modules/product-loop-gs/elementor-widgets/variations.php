<?php
namespace ReyCore\Modules\ProductLoopGs\ElementorWidgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Variations extends WooBase {

	public function get_name() {
		return 'reycore-woo-grid-variations';
	}

	public function get_title() {
		return __( 'Product Variations (Grid)', 'rey-core' );
	}

	public function get_icon() {
		return $this->get_icon_class();
	}

	public function show_in_panel() {
		return $this->maybe_show_in_panel();
	}

	public static function get_list(){

		$options = reycore_wc__get_attributes_list();
		$options[''] = esc_html__('- Inherit -', 'rey-core');
		$options['disabled'] = esc_html__('- Disabled -', 'rey-core');

		return apply_filters('reycore/woocommerce/loop/attributes_list', $options);
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
			'section_title',
			[
				'label' => __( 'Settings', 'rey-core' ),
			]
		);

			$this->add_wrapper_css_class();

			$this->add_control(
				'edit_link',
				[
					'type' => \Elementor\Controls_Manager::RAW_HTML,
					'raw' => sprintf( __( 'Product Variation options can be modified into the <a href="%1$s" target="_blank">Product Variations</a> settings in Customizer > WooCommerce > Product Catalog > Product Layout', 'rey-core' ),
						esc_url( add_query_arg( ['autofocus[control]' => 'accordion_start__catalog_product_variations'], admin_url( 'customize.php' ) ) ),
					),
					'content_classes' => 'rey-raw-html',
				]
			);

			// $this->add_control(
			// 	'attributes_id',
			// 	[
			// 		'label'      => esc_html__( 'Select Attribute', 'rey-core' ),
			// 		'description'      => __('Display product variation swatches into product items, by selecting which attributes should be displayed.', 'rey-core'),
			// 		'default'    => '',
			// 		'type'       => 'rey-ajax-list',
			// 		'query_args' => [
			// 			'request' => [__CLASS__, 'get_list'],
			// 		],
			// 		'options' => [
			// 			'disabled' => __('- Disabled -', 'rey-core'),
			// 		],
			// 	]
			// );

		$this->end_controls_section();

		// $this->start_controls_section(
		// 	'section_styles',
		// 	[
		// 		'label' => __( 'Styles', 'rey-core' ),
		// 		'tab' => \Elementor\Controls_Manager::TAB_STYLE,
		// 		'show_label' => false,
		// 	]
		// );

		// 	$selectors['main'] = '{{WRAPPER}} .rey-loopPrice';

		// $this->end_controls_section();

	}

	public function render_template() {

		// if( '' !== $this->_settings['attributes_id'] ){
		// 	add_filter('theme_mod_woocommerce_loop_variation', [$this, '__type']);
		// }

		$component = \ReyCore\Plugin::instance()->woocommerce_loop->get_component('variations');

		if( $component ){
			$component->render();
		}

		// if( '' !== $this->_settings['attributes_id'] ){
		// 	remove_filter('theme_mod_woocommerce_loop_variation', [$this, '__type']);
		// }

	}

	public function __type($mod){
		return $this->_settings['attributes_id'];
	}

}
