<?php
namespace ReyCore\Modules\CustomTemplates;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WooLoopProducts extends WooBase {

	private $product_archive;
	public $_settings;

	public function get_name() {
		return 'reycore-woo-loop-products';
	}

	public function get_title() {
		return __( 'Product Archive', 'rey-core' );
	}

	public function get_icon() {
		return $this->get_icon_class();
	}

	public function get_categories() {
		return [ 'rey-woocommerce-loop' ];
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
			'section_layout',
			[
				'label' => __( 'Layout settings', 'rey-core' ),
			]
		);

			$this->add_control(
				'query_type',
				[
					'label' => esc_html__( 'Current Query Type', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'current_query',
					'options' => [
						'current_query'  => esc_html__( 'Extended', 'rey-core' ),
						'current_query_original'  => esc_html__( 'Original', 'rey-core' ),
					],
					'description' => esc_html__('The "Original" Current query will just inherit the main query, whereas the "Extended" one may accept other parameters (for example custom ordering).', 'rey-core')
				]
			);

			$this->add_control(
				'_skin',
				[
					'label' => esc_html__('Skin', 'rey-core'),
					'type' => \Elementor\Controls_Manager::HIDDEN,
					'default' => '',
				]
			);

			$this->add_responsive_control(
				'per_row',
				[
					'label' => __( 'Products per row', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'min' => 1,
					'max' => 6,
					'selectors' => [
						'{{WRAPPER}} ul.products' => '--woocommerce-grid-columns: {{VALUE}}',
					],
					'render_type' => 'template',
					'default' => reycore_wc_get_columns('desktop'),
					'tablet_default' => reycore_wc_get_columns('tablet'),
					'mobile_default' => reycore_wc_get_columns('mobile'),
				]
			);

			$this->add_control(
				'rows_per_page',
				[
					'label' => __( 'Rows per page', 'rey-core' ),
					'description' => __( 'Changing this value may affect the pagination total number of pages, because this Widget cannot override the Current query\'s Rows per page value, defined in Customizer WooCommerce Grid settings.', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'min' => 1,
					'max' => 6,
					'default' => wc_get_default_product_rows_per_page(),
					'condition' => [
						'query_type' => 'current_query',
					],
				]
			);

			$this->add_control(
				'paginate',
				[
					'label' => __( 'Pagination', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
				]
			);

			$this->add_control(
				'show_header',
				[
					'label' => __( 'Show Header', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
					'condition' => [
						'paginate' => 'yes',
					],
				]
			);

			$this->add_control(
				'show_count',
				[
					'label' => __( 'Show Product Count', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
					'condition' => [
						'paginate!' => '',
						'show_header!' => '',
					],
				]
			);

			$this->add_control(
				'show_sorting',
				[
					'label' => __( 'Show Sorting', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
					'condition' => [
						'paginate!' => '',
						'show_header!' => '',
					],
				]
			);

			$orderby_choices = array_merge([
					'' => __( '- Inherit -', 'rey-core' )
				],
				apply_filters( 'woocommerce_default_catalog_orderby_options', [
					'menu_order' => __( 'Default sorting (custom ordering + name)', 'woocommerce' ),
					'popularity' => __( 'Popularity (sales)', 'woocommerce' ),
					'rating'     => __( 'Average rating', 'woocommerce' ),
					'date'       => __( 'Sort by most recent', 'woocommerce' ),
					'price'      => __( 'Sort by price (asc)', 'woocommerce' ),
					'price-desc' => __( 'Sort by price (desc)', 'woocommerce' ),
				] )
			);

			// default order
			$this->add_control(
				'default_catalog_orderby',
				[
					'label' => esc_html__( 'Default sorting', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => $orderby_choices,
					'condition' => [
						'query_type' => 'current_query',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Image_Size::get_type(),
				[
					'name' => 'image', // Usage: `{name}_size` and `{name}_custom_dimension`, in this case `image_size` and `image_custom_dimension`.
					'default' => 'woocommerce_thumbnail',
					'separator' => 'before',
				]
			);

		$this->end_controls_section();

		// -------

		if( class_exists('\ReyCore\WooCommerce\Tags\ProductArchive') ){
			\ReyCore\WooCommerce\Tags\ProductArchive::add_component_display_controls( $this );
			\ReyCore\WooCommerce\Tags\ProductArchive::add_extra_data_controls( $this );
		}

		// -------

		$selectors = [
			'header' => '{{WRAPPER}} .rey-loopHeader',
			'pagination' => [
				'all' => '{{WRAPPER}} .rey-pagination, {{WRAPPER}} .rey-ajaxLoadMore-btn',
				'main' => '{{WRAPPER}} .rey-pagination',
				'active' => '{{WRAPPER}} .rey-pagination .page-numbers:hover, {{WRAPPER}} .rey-pagination .page-numbers.current',
				'border' => '{{WRAPPER}} .rey-pagination .page-numbers.current, {{WRAPPER}} .rey-pagination .prev, {{WRAPPER}} .rey-pagination .next',
				'btn' => '{{WRAPPER}} .rey-ajaxLoadMore-btn',
			],
		];

		// -------

		$this->start_controls_section(
			'section_pagination_styles',
			[
				'label' => __( 'Pagination Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'show_label' => false,
				'condition' => [
					'paginate!' => '',
				],
			]
		);

			$this->add_control(
				'pagination_type',
				[
					'label' => esc_html__( 'Pagination Type', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::HIDDEN,
					'default' => get_theme_mod('loop_pagination', 'paged'),
				]
			);

			$this->add_control(
				'pagination_color',
				[
					'label' => esc_html__( 'Pagination Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selectors['pagination']['all'] => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'pagination_typo',
					'selector' => $selectors['pagination']['all'],
				]
			);

			$this->add_control(
				'pagination_color_active',
				[
					'label' => esc_html__( 'Pagination Color Active', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selectors['pagination']['active'] => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'pagination_color_border',
				[
					'label' => esc_html__( 'Pagination Border Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selectors['pagination']['border'] => 'border-color: {{VALUE}}',
					],
				]
			);

		$this->end_controls_section();

		// -------

		if( class_exists('\ReyCore\WooCommerce\Tags\ProductArchive') ){
			\ReyCore\WooCommerce\Tags\ProductArchive::add_common_styles_controls( $this );
		}

	}

	function default_sorting($opt){

		if( $custom = $this->_settings['default_catalog_orderby'] ){
			return $custom;
		}
		return $opt;
	}

	function before(){
		add_filter( 'woocommerce_default_catalog_orderby', [$this, 'default_sorting']);
	}

	function after(){
		remove_filter( 'woocommerce_default_catalog_orderby', [$this, 'default_sorting']);
	}

	function render_template() {

		if( ! class_exists('\ReyCore\WooCommerce\Tags\ProductArchive') ){
			return;
		}

		$this->_settings = $this->get_settings_for_display();

		$args = [
			'name'          => 'product_archive_element',
			'filter_name'   => 'product_archive',
			'main_class'    => 'reyEl-productArchive',
			'filter_button' => $this->_settings['paginate'] !== '' && $this->_settings['show_header'] !== '',
			'el_instance' => $this,
		];

		$this->product_archive = new \ReyCore\WooCommerce\Tags\ProductArchive( $args, $this->_settings );

		if ( ($query_results = (array) $this->product_archive->get_query_results()) &&
				isset($query_results['ids']) && ! empty($query_results['ids']) ) {

			$this->before();

			$this->product_archive->render_start();
				$this->product_archive->loop_start();

					$this->product_archive->render_products();

				$this->product_archive->loop_end();
			$this->product_archive->render_end();

			$this->after();

		}
		else {
			/**
			 * Hook: woocommerce_no_products_found.
			 *
			 * @hooked wc_no_products_found - 10
			 */
			do_action( 'woocommerce_no_products_found' );
		}


	}


}
