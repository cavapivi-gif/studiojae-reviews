<?php
namespace ReyCore\Modules\CustomTemplates;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class WooBase extends \Elementor\Widget_Base {

	public function render_template(){}
	public function get_name(){}

	/**
	 * Render widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {

		Elementor::__before_render($this);

		if( ! Elementor::__should_render($this) ){
			return;
		}

		$this->render_template();

		Elementor::__after_render($this);

	}

	public function add_preview_controls(){

		if( strpos($this->get_name(), 'reycore-woo-pdp') === false ){
			return;
		}

		$this->start_controls_section(
			'section_product_id',
			[
				'label' => __( 'Product Preview', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

			$this->add_control(
				'_product_id',
				[
					'label' => esc_html_x( 'Set Custom Product', 'Elementor control label', 'rey-core' ),
					'description' => esc_html_x( 'Setting a product will make this element inherit its properties. Leaving empty will just pull the product page\'s ID.', 'Elementor control label', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'label_block' => true,
					'type' => 'rey-query',
					'query_args' => [
						'type' => 'posts',
						'post_type' => 'product',
					],
				]
			);

		$this->end_controls_section();

	}

	protected function register_controls(){

		$this->element_register_controls();
		$this->add_preview_controls();

	}

	protected function element_register_controls(){}


	/**
	 * Render widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function content_template() {}

	public function get_icon_class(){

		$class = 'general';
		$name = $this->get_name();

		if( strpos($name, 'reycore-woo-') === 0 ){
			$class = str_replace('reycore-woo-', '', $name);
		}

		return sprintf('rey-editor-icons --%s', $class);
	}

	public function maybe_show_in_panel(){
		return (bool) reycore__get_purchase_code();
	}

}
