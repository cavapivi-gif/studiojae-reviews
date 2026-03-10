<?php
namespace ReyCore\Modules\CustomTemplates;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WooPdpRelated extends WooBase {

	public $_settings = [];

	public function get_name() {
		return 'reycore-woo-pdp-related';
	}

	public function get_title() {
		return __( 'Related products (PDP)', 'rey-core' );
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
				'limit',
				[
					'label' => esc_html__( 'Product limit', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 1,
					'max' => 20,
					'step' => 1,
				]
			);

			$this->add_responsive_control(
				'columns',
				[
					'label' => esc_html__( 'Columns', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 1,
					'max' => 6,
					'step' => 1,
				]
			);

			$this->add_control(
				'customize_settings_notice',
				[
					'type' => \Elementor\Controls_Manager::RAW_HTML,
					'content_classes' => 'rey-raw-html',
					'raw' => sprintf( _x( '<a href="%s" target="_blank" class="__title-link">Customize related section<i class="eicon-editor-external-link"></i></a><br>Access Customizer > WooCommerce > Product Components to customize the display of this section.', 'Elementor control label', 'rey-core' ), add_query_arg( ['autofocus[control]' => 'single_product_page_related'], admin_url( 'customize.php' ) ) ),
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

			$selectors = [
				'title' => '{{WRAPPER}} .related.products > h2'
			];

			$this->add_control(
				'title_settings',
				[
				   'label' => esc_html__( 'Title styles', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::HEADING,
				]
			);

			$this->add_control(
				'title_color',
				[
					'label' => esc_html__( 'Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selectors['title'] => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'title_typo',
					'selector' => $selectors['title'],
				]
			);

			$this->add_responsive_control(
				'title_alignment',
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
						$selectors['title'] => 'text-align: {{VALUE}};',
					],
				]
			);

		$this->end_controls_section();
	}

	function render_template() {

		$this->_settings = $this->get_settings_for_display();

		add_filter('theme_mod_single_product_page_related', '__return_true');
		add_filter('woocommerce_output_related_products_args', [$this, 'make_args'], 20);
		add_filter('theme_mod_single_product_page_related_columns_tablet', [$this, 'columns_tablet']);
		add_filter('theme_mod_single_product_page_related_columns_mobile', [$this, 'columns_mobile']);

		woocommerce_output_related_products();

		remove_filter('theme_mod_single_product_page_related', '__return_true');
		remove_filter('woocommerce_output_related_products_args', [$this, 'make_args'], 20);
		remove_filter('theme_mod_single_product_page_related_columns_tablet', [$this, 'columns_tablet']);
		remove_filter('theme_mod_single_product_page_related_columns_mobile', [$this, 'columns_mobile']);
	}

	function make_args( $args ){

		if( $limit = $this->_settings['limit'] ){
			$args['posts_per_page'] = $limit;
		}

		if( $columns = $this->_settings['columns'] ){
			$args['columns'] = $columns;
		}

		return $args;
	}

	function columns_tablet( $mod ){

		if( isset($this->_settings['columns_tablet']) && $columns = $this->_settings['columns_tablet'] ){
			return $columns;
		}

		return $mod;
	}

	function columns_mobile( $mod ){

		if( isset($this->_settings['columns_mobile']) && $columns = $this->_settings['columns_mobile'] ){
			return $columns;
		}

		return $mod;
	}

}
