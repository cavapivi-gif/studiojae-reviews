<?php
namespace ReyCore\Modules\PdpTabsAccordion;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Element extends \ReyCore\Modules\CustomTemplates\WooBase {

	public $_settings = [];

	public function get_name() {
		return 'reycore-woo-pdp-summary-tabs-acc';
	}

	public function get_title() {
		return __( 'Summary Tabs/Accordion (PDP)', 'rey-core' );
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

			$overrides = new \Elementor\Repeater();

			$overrides->add_control(
				'item',
				[
					'label' => __( 'Select Tab/Block', 'rey-core' ),
					'label_block' => true,
					'default' => '',
					'type' => 'rey-ajax-list',
					'query_args' => [
						'request' => 'get_tabs'
					],
				]
			);

			$overrides->add_control(
				'title',
				[
					'label' => esc_html__( 'Title', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
				]
			);

			$this->add_control(
				'overrides',
				[
					'label' => __( 'Override list', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::REPEATER,
					'fields' => $overrides->get_controls(),
					'default' => [],
					'prevent_empty' => false,
				]
			);

			$this->add_control(
				'customize_settings_notice',
				[
					'type' => \Elementor\Controls_Manager::RAW_HTML,
					'content_classes' => 'rey-raw-html',
					'raw' => sprintf( _x( '<a href="%s" target="_blank" class="__title-link">Customize tabs/accordions<i class="eicon-editor-external-link"></i></a><br>Access Customizer > WooCommerce > Product Tabs/Blocks to customize its settings.', 'Elementor control label', 'rey-core' ), add_query_arg( ['autofocus[control]' => 'single__accordion_items'], admin_url( 'customize.php' ) ) ),
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
				'title' => '{{WRAPPER}} .rey-summaryAcc .rey-summaryAcc-accItem'
			];

			$this->add_control(
				'titles_settings',
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


		$this->end_controls_section();
	}

	function render_template() {

		$this->_settings = $this->get_settings_for_display();

		add_filter('theme_mod_single__accordion_items', [$this, 'overrides'], 20);

		Base::instance()->display_summary_accordion_tabs();

		remove_filter('theme_mod_single__accordion_items', [$this, 'overrides'], 20);

	}

	function overrides($mod){

		if( $overrides = $this->_settings['overrides'] ){

			$mod = [];

			foreach ($overrides as $value) {
				$mod[] = $value;
			}

		}

		return $mod;
	}

}
