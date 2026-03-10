<?php
namespace ReyCore\Modules\ProductLoopGs\ElementorWidgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Thumbnail extends WooBase {

	public function get_name() {
		return 'reycore-woo-grid-thumbnail';
	}

	public function get_title() {
		return __( 'Thumbnail (Product Grid)', 'rey-core' );
	}

	public function get_icon() {
		return $this->get_icon_class();
	}

	public function show_in_panel() {
		return $this->maybe_show_in_panel();
	}

	protected function element_register_controls() {

		$this->start_controls_section(
			'section_title',
			[
				'label' => __( 'Settings', 'rey-core' ),
			]
		);

			$this->add_wrapper_css_class();

			$this->add_group_control(
				\Elementor\Group_Control_Image_Size::get_type(),
				[
					'name' => 'image', // Usage: `{name}_size` and `{name}_custom_dimension`, in this case `image_size` and `image_custom_dimension`.
					'default' => 'woocommerce_thumbnail',
				]
			);

			// image size select
			// extra images
			// thumb padding 0 forced
			// reset current slots
			// Components in Slots

		$this->end_controls_section();

		$this->start_controls_section(
			'section_styles',
			[
				'label' => __( 'Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'show_label' => false,
			]
		);

			$selectors['main'] = '{{WRAPPER}} .rey-productThumbnail';

			// components spacing

		$this->end_controls_section();

	}

	public function render_template() {

		$loop = \ReyCore\Plugin::instance()->woocommerce_loop;

		if( ! ( $component = $loop->get_component('thumbnails') ) ){
			return;
		}

		$loop->thumbnail_wrapper_start();
		$loop->loop_product_link_open();

		$component->render();

		$loop->thumbnail_wrapper_end();
		$loop->loop_product_link_close();

	}

}
