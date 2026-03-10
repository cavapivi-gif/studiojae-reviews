<?php
namespace ReyCore\Modules\ProductLoopGs\ElementorWidgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Categories extends WooBase {

	public function get_name() {
		return 'reycore-woo-grid-categories';
	}

	public function get_title() {
		return __( 'Categories (Product Grid)', 'rey-core' );
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

			$this->add_control(
				'exclude_parents',
				[
					'label' => esc_html__( 'Exclude Parent Categories', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- Inherit -', 'rey-core' ),
						'yes'  => esc_html__( 'Yes', 'rey-core' ),
						'no'  => esc_html__( 'No', 'rey-core' ),
					],
				]
			);

			$this->add_control(
				'sep',
				[
					'label' => esc_html__( 'Separator', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => esc_html__( '', 'rey-core' ),
					'placeholder' => 'ex: ,',
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_styles',
			[
				'label' => __( 'Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'show_label' => false,
			]
		);

			$selectors['main'] = '{{WRAPPER}} .rey-productCategories';

			$this->add_control(
				'color',
				[
					'label' => esc_html__( 'Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selectors['main'] => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'hover_color',
				[
					'label' => esc_html__( 'Hover Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selectors['main'] . ' a:hover' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'typo',
					'selector' => $selectors['main'],
				]
			);

			$this->add_responsive_control(
				'alignment',
				[
					'label' => __( 'Alignment', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::CHOOSE,
					'options' => [
						'left'           => [
							'title'         => __( 'Left', 'rey-core' ),
							'icon'          => 'eicon-text-align-left',
						],
						'center'        => [
							'title'         => __( 'Center', 'rey-core' ),
							'icon'          => 'eicon-text-align-center',
						],
						'right'          => [
							'title'         => __( 'Right', 'rey-core' ),
							'icon'          => 'eicon-text-align-right',
						],
					],
					'default' => '',
					'selectors' => [
						$selectors['main'] => '--components-align: {{VALUE}};',
					],
				]
			);

		$this->end_controls_section();

	}

	public function render_template() {

		if( ! ( $component = \ReyCore\Plugin::instance()->woocommerce_loop->get_component('category') ) ){
			return;
		}

		add_filter('theme_mod_loop_show_categories', [$this, '__enable']);
		add_filter('theme_mod_loop_categories__exclude_parents', [$this, '__exc_parents']);
		add_filter('reycore/woocommerce/loop/categories_sep', [$this, '__sep']);

			$component->render();

		remove_filter('theme_mod_loop_show_categories', [$this, '__enable']);
		remove_filter('theme_mod_loop_categories__exclude_parents', [$this, '__exc_parents']);
		remove_filter('reycore/woocommerce/loop/categories_sep', [$this, '__sep']);

	}

	public function __sep( $val ){

		if( $sep = $this->_settings['sep'] ) {
			return $sep;
		}

		return $val;
	}

	public function __exc_parents( $status ){

		if( $exclude_parents = $this->_settings['exclude_parents'] ) {
			return 'yes' === $exclude_parents;
		}

		return $status;
	}

	public function __enable(){
		return '1';
	}

}
