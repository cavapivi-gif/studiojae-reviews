<?php
namespace ReyCore\Modules\CustomTemplates;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WooPdpTabsBlocks extends WooBase {

	public $_settings = [];

	public function get_name() {
		return 'reycore-woo-pdp-tabs-blocks';
	}

	public function get_title() {
		return __( 'Tabs/Blocks (PDP)', 'rey-core' );
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

			// $this->add_control(
			// 	'layout',
			// 	[
			// 	   'label' => esc_html__( 'Layout', 'rey-core' ),
			// 		'type' => \Elementor\Controls_Manager::HIDDEN,
			// 		'default' => get_theme_mod('product_content_layout', 'blocks'),
			// 	]
			// );

			$this->add_control(
				'layout',
				[
					'label' => esc_html__( 'Layout', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- Inherit -', 'rey-core' ),
						'blocks'  => esc_html__( 'Blocks', 'rey-core' ),
						'tabs'  => esc_html__( 'Tabs', 'rey-core' ),
					],
				]
			);

			$this->add_control(
				'tabs_disable_titles',
				[
					'label' => esc_html__( 'Disable titles inside Tabs', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- Inherit -', 'rey-core' ),
						'yes'  => esc_html__( 'Yes', 'rey-core' ),
						'no'  => esc_html__( 'No', 'rey-core' ),
					],
					'condition' => [
						'layout' => 'tabs',
					],
				]
			);

			$this->add_control(
				'reviews_tab_as_block',
				[
					'label' => esc_html__( 'Make Reviews tab as block', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- Inherit -', 'rey-core' ),
						'yes'  => esc_html__( 'Yes', 'rey-core' ),
						'no'  => esc_html__( 'No', 'rey-core' ),
					],
					'condition' => [
						'layout' => 'tabs',
					],
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
					'separator' => 'before'
				]
			);


			$this->add_control(
				'customize_settings_notice',
				[
					'type' => \Elementor\Controls_Manager::RAW_HTML,
					'content_classes' => 'rey-raw-html',
					'raw' => sprintf( _x( '<a href="%s" target="_blank" class="__title-link">Customize tabs/blocks<i class="eicon-editor-external-link"></i></a><br>Access Customizer > WooCommerce > Product Tabs/Blocks to switch layouts, disable tabs/blocks, create new ones and change other settings.', 'Elementor control label', 'rey-core' ), add_query_arg( ['autofocus[control]' => 'single_product_atc__price'], admin_url( 'customize.php' ) ) ),
					'separator' => 'before'
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_styles',
			[
				'label' => __( 'Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				// 'show_label' => false,
			]
		);

		$this->add_control(
			'stretch_desc',
			[
				'label' => esc_html__( 'Stretch Description', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => esc_html__( '- Inherit -', 'rey-core' ),
					'yes'  => esc_html__( 'Yes', 'rey-core' ),
					'no'  => esc_html__( 'No', 'rey-core' ),
				],
				'condition' => [
					'layout' => 'blocks',
				],
			]
		);

		$this->add_control(
			'columns',
			[
				'label' => esc_html__( 'Columns per row', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '',
				'min' => 0,
				'max' => 10,
				'step' => 0,
				'selectors' => [
					'{{WRAPPER}} .rey-wcPanels' => '--panel-size: calc(100% / {{VALUE}})',
				],
				'condition' => [
					'layout' => 'blocks',
				],
			]
		);

		$this->add_control(
			'titles_title',
			[
			   'label' => esc_html__( 'HEADINGS', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'layout' => 'blocks',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'titles',
				'selector' => '{{WRAPPER}} .rey-wcPanel-inner > h2',
				'condition' => [
					'layout' => 'blocks',
				],
			]
		);

		$this->add_control(
			'titles_color',
			[
				'label' => esc_html__( 'Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-wcPanel-inner > h2' => 'color: {{VALUE}}',
				],
				'condition' => [
					'layout' => 'blocks',
				],
			]
		);

		$this->end_controls_section();

	}

	function render_template() {

		$this->_settings = $this->get_settings_for_display();

		add_filter('theme_mod_product_content_layout', [$this, 'change_layout'] );
		add_filter('theme_mod_product_tab_description', '__return_true');
		add_filter('theme_mod_single__accordion_items', '__return_empty_array');
		add_filter('theme_mod_single_tabs__reviews_outside', [$this, 'reviews_tab_as_block']);
		add_filter('theme_mod_product_content_tabs_disable_titles', [$this, 'tabs_disable_titles']);
		add_filter('theme_mod_product_content_blocks_desc_stretch', [$this, 'blocks_stretch']);
		add_filter('woocommerce_product_tabs', [$this, 'overrides'], 100);

		woocommerce_output_product_data_tabs();

		if( function_exists('reycore_wc__get_tag') && ($tabs_tag = reycore_wc__get_tag('tabs')) ){
			$tabs_tag->move_reviews_tab_outside();
		}

		remove_filter('theme_mod_product_tab_description', '__return_true');
		remove_filter('theme_mod_single__accordion_items', '__return_empty_array');
		remove_filter('theme_mod_single_tabs__reviews_outside', [$this, 'reviews_tab_as_block']);
		remove_filter('theme_mod_product_content_tabs_disable_titles', [$this, 'tabs_disable_titles']);
		remove_filter('theme_mod_product_content_blocks_desc_stretch', [$this, 'blocks_stretch']);
		remove_filter('woocommerce_product_tabs', [$this, 'overrides'], 100);
		remove_filter('theme_mod_product_content_layout', [$this, 'change_layout'] );

	}

	function change_layout($mod){

		if( ! ($element_layout = $this->_settings['layout']) ){
			return $mod;
		}

		return $element_layout;

	}

	function blocks_stretch($mod){

		if( $stretch = $this->_settings['stretch_desc'] ){
			return $stretch === 'yes';
		}

		return $mod;
	}

	function tabs_disable_titles($mod){

		if( $tabs_disable_titles = $this->_settings['tabs_disable_titles'] ){
			return $tabs_disable_titles === 'yes';
		}

		return $mod;
	}

	function reviews_tab_as_block($mod){

		if( $reviews_tab_as_block = $this->_settings['reviews_tab_as_block'] ){
			return $reviews_tab_as_block === 'yes';
		}

		return $mod;
	}

	function overrides( $tabs ){

		if( ! ($overrides = $this->_settings['overrides']) ){
			return $tabs;
		}

		$new_tabs = [];

		foreach ($overrides as $key => $tab) {

			if( ! isset($tabs[$tab['item']]) ){
				continue;
			}

			$old_tab = $tabs[$tab['item']];

			$old_tab['priority'] = $key;

			if( isset($tab['title']) && ! empty($tab['title']) ){
				$old_tab['title'] = $tab['title'];
			}

			$new_tabs[$tab['item']] = $old_tab;

		}

		return $new_tabs;
	}

}
