<?php
namespace ReyCore\Modules\CustomTemplates;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WooPdpNav extends WooBase {

	public $_settings;

	public function get_name() {
		return 'reycore-woo-pdp-nav';
	}

	public function get_title() {
		return __( 'Navigation (PDP)', 'rey-core' );
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

		$selectors = [
			'main' => '{{WRAPPER}} .rey-postNav',
			'nav_links' => '{{WRAPPER}} .rey-postNav .nav-links',
			'arrow' => '{{WRAPPER}} .rey-arrowSvg',
			'arrow_hover' => '{{WRAPPER}} .rey-arrowSvg:hover',
			'arrow_svg' => '{{WRAPPER}} .rey-arrowSvg svg',
		];

		$this->start_controls_section(
			'section_settings',
			[
				'label' => __( 'Settings', 'rey-core' ),
			]
		);

		$this->add_responsive_control(
			'alignment',
			[
				'label' => __( 'Alignment', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					'' => __( 'Default', 'rey-core' ),
					'flex-start' => __( 'Start', 'rey-core' ),
					'center' => __( 'Center', 'rey-core' ),
					'flex-end' => __( 'End', 'rey-core' ),
					'space-between' => __( 'Space Between', 'rey-core' ),
					'space-around' => __( 'Space Around', 'rey-core' ),
					'space-evenly' => __( 'Space Evenly', 'rey-core' ),
					'stretch' => __( 'Space Evenly', 'rey-core' ),
				],
				'selectors' => [
					$selectors['nav_links'] => 'display:flex; justify-content: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'type',
			[
				'label' => esc_html__( 'Hover type', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					'' => esc_html__('- Inherit -', 'rey-core'),
					'1' => esc_html__('Compact', 'rey-core'),
					'extended' => esc_html__('Extended', 'rey-core'),
					'full' => esc_html__('Full', 'rey-core'),
				],
			]
		);

		$this->add_control(
			'in_same_term',
			[
				'label' => esc_html__( 'Navigate only the same category', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => esc_html__( '- Inherit -', 'rey-core' ),
					'yes'  => esc_html__( 'Yes', 'rey-core' ),
					'no'  => esc_html__( 'No', 'rey-core' ),
				],
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

			$this->add_control(
				'arrow_color',
				[
					'label' => esc_html__( 'Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selectors['arrow'] => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'arrow_color_hover',
				[
					'label' => esc_html__( 'Hover Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selectors['arrow_hover'] => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'arrow_size',
				[
					'label' => esc_html__( 'Arrows size', 'rey-core' ) . ' (px)',
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 1,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						$selectors['arrow_svg'] => 'font-size: {{VALUE}}px',
					],
				]
			);


		// $this->end_controls_section();

		// $this->start_controls_section(
		// 	'section_arrows_styles',
		// 	[
		// 		'label' => __( 'Arrows Styles', 'rey-core' ),
		// 		'condition' => [
		// 			'_skin' => '',
		// 		],
		// 		'tab' => \Elementor\Controls_Manager::TAB_STYLE,
		// 	]
		// );

		$this->add_control(
			'arrows_type',
			[
				'label' => esc_html__( 'Arrows Type', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => esc_html__( 'Default', 'rey-core' ),
					'chevron'  => esc_html__( 'Chevron', 'rey-core' ),
					'custom'  => esc_html__( 'Custom Icon', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'arrows_custom_icon',
			[
				'label' => __( 'Custom Arrow Icon (Right)', 'elementor' ),
				'type' => \Elementor\Controls_Manager::ICONS,
				'condition' => [
					'arrows_type' => 'custom',
				],
			]
		);

		// $this->add_control(
		// 	'arrows_size',
		// 	[
		// 		'label' => esc_html__( 'Arrows size', 'rey-core' ),
		// 		'type' => \Elementor\Controls_Manager::NUMBER,
		// 		'default' => '',
		// 		'min' => 5,
		// 		'max' => 200,
		// 		'step' => 1,
		// 		'selectors' => [
		// 			'{{WRAPPER}}' => '--arrow-size: {{VALUE}}px',
		// 		],
		// 	]
		// );

		$this->end_controls_section();

	}

	function render_template() {

		$this->_settings = $this->get_settings_for_display();

		add_filter('theme_mod_product_navigation', [$this, 'type']);
		add_filter('theme_mod_product_navigation_same_term', [$this, 'in_same_term']);
		add_filter('rey/svg_arrow_markup', [$this, 'custom_icon']);

		if( ($pdp = \ReyCore\Plugin::instance()->woocommerce_pdp) && ($c = $pdp->get_component('product_nav')) ){
			$c->render();
		}

		remove_filter('theme_mod_product_navigation', [$this, 'type']);
		remove_filter('theme_mod_product_navigation_same_term', [$this, 'in_same_term']);
		remove_filter('rey/svg_arrow_markup', [$this, 'custom_icon']);

	}

	function custom_icon( $html ){

		$custom_svg_icon = '';

		if( 'custom' === $this->_settings['arrows_type'] &&
			($custom_icon = $this->_settings['arrows_custom_icon']) && isset($custom_icon['value']) && !empty($custom_icon['value']) ){
			ob_start();
			\Elementor\Icons_Manager::render_icon( $custom_icon, [ 'aria-hidden' => 'true', 'class' => '' ] );
			return ob_get_clean();
		}
		else if( 'chevron' === $this->_settings['arrows_type'] ){
			return '<svg viewBox="0 0 40 64" xmlns="http://www.w3.org/2000/svg"><polygon fill="currentColor" points="39.5 32 6.83 64 0.5 57.38 26.76 32 0.5 6.62 6.83 0"></polygon></svg>';
		}

		return $html;
	}

	function type($mod){
		if( $type = $this->_settings['type'] ){
			return $type;
		}
		return $mod;
	}

	function in_same_term($mod){

		if( ! ($in = $this->_settings['in_same_term']) ){
			return $mod;
		}

		return 'yes' === $in;
	}

}
