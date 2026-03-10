<?php
namespace ReyCore\Modules\CustomTemplates;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WooPdpShare extends WooBase {

	public $_settings;

	public function get_name() {
		return 'reycore-woo-pdp-share';
	}

	public function get_title() {
		return __( 'Share (PDP)', 'rey-core' );
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
				'title_enable',
				[
					'label' => esc_html__( 'Enable title', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => 'yes',
				]
			);

			$this->add_control(
				'title',
				[
					'label' => esc_html__( 'Title', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'placeholder' => esc_html__( 'eg: Share', 'rey-core' ),
					'condition' => [
						'title_enable!' => '',
					],
				]
			);

			$share_icons = new \Elementor\Repeater();

			$share_icons->add_control(
				'icon',
				[
					'label' => __( 'Select icon', 'rey-core' ),
					'label_block' => true,
					'default' => '',
					'type' => 'rey-ajax-list',
					'query_args' => [
						'request' => 'get_social_share_icons',
					],
				]
			);

			$this->add_control(
				'icons',
				[
					'label' => __( 'Custom Icons', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::REPEATER,
					'fields' => $share_icons->get_controls(),
					'default' => [],
					'prevent_empty' => false,
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
				'inner' => '{{WRAPPER}} .rey-productShare-inner',
				'svg' => '{{WRAPPER}} a svg',
				'title' => '{{WRAPPER}} .rey-productShare h5',
				'links' => '{{WRAPPER}} .rey-postSocialShare a',
				'links_hover' => '{{WRAPPER}} .rey-postSocialShare a:hover',
			];

			$this->add_responsive_control(
				'alignment',
				[
					'label' => __( 'Alignment', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::CHOOSE,
					'options' => [
						'flex-start'           => [
							'title'         => __( 'Left', 'rey-core' ),
							'icon'          => 'eicon-text-align-left',
						],
						'center'        => [
							'title'         => __( 'Center', 'rey-core' ),
							'icon'          => 'eicon-text-align-center',
						],
						'flex-end'          => [
							'title'         => __( 'Right', 'rey-core' ),
							'icon'          => 'eicon-text-align-right',
						],
					],
					'default' => '',
					'selectors' => [
						$selectors['inner'] => 'justify-content: {{VALUE}};',
					],
				]
			);

			// -----

			$this->add_control(
				'icon_heading',
				[
				   'label' => esc_html__( 'Icons', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_control(
				'colored_icons',
				[
					'label' => esc_html__( 'Colored icons', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

			$this->add_control(
				'icon_color',
				[
					'label' => esc_html__( 'Icons Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selectors['links'] => 'color: {{VALUE}}',
					],
					'condition' => [
						'colored_icons' => '',
					],
				]
			);

			$this->add_control(
				'icon_color_hover',
				[
					'label' => esc_html__( 'Icons Hover Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selectors['links_hover'] => 'color: {{VALUE}}',
					],
					'condition' => [
						'colored_icons' => '',
					],
				]
			);

			$this->add_responsive_control(
				'icons_size',
				[
					'label' => esc_html__( 'Icon Size', 'rey-core' ) . ' (px)',
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 1,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						$selectors['svg'] => 'font-size: {{VALUE}}px',
					],
				]
			);

			$this->add_responsive_control(
				'icons_spacing',
				[
					'label' => esc_html__( 'Icon Spacing', 'rey-core' ) . ' (px)',
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 1,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						$selectors['inner'] => '--icons-spacing: {{VALUE}}px',
					],
				]
			);

			$this->add_control(
				'vertical',
				[
					'label' => esc_html__( 'Display vertically', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

			// -----

			$this->add_control(
				'title_title',
				[
				   'label' => esc_html__( 'Title', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_control(
				'color',
				[
					'label' => esc_html__( 'Title Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selectors['title'] => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'label' => esc_html__( 'Title Typo', 'rey-core' ),
					'name' => 'typo',
					'selector' => $selectors['title'],
				]
			);

		$this->end_controls_section();

	}

	function enable_product_share(){
		return '1';
	}

	function render_template() {

		add_filter('theme_mod_product_share', [$this, 'enable_product_share']);
		add_filter('theme_mod_product_share_icons', [$this, 'icons']);
		add_filter('theme_mod_product_share_icons_colored', [$this, 'colored_icons']);

		$this->_settings = $this->get_settings_for_display();
		$args = [];

		if( $this->_settings['title_enable'] === 'yes' ){

			if( $custom_title = $this->_settings['title'] ){
				$args['title'] = $custom_title;
			}

		}
		else {
			$args['title'] ='';
		}

		if( $this->_settings['vertical'] !== '' ){
			$args['custom_classes'][] = '--vertical';
		}

		if( ($pdp = \ReyCore\Plugin::instance()->woocommerce_pdp) && ($c = $pdp->get_component('share')) ){
			$c->output($args);
		}

		remove_filter('theme_mod_product_share', [$this, 'enable_product_share']);
		remove_filter('theme_mod_product_share_icons', [$this, 'icons']);
		remove_filter('theme_mod_product_share_icons_colored', [$this, 'colored_icons']);

	}

	function colored_icons($mod){

		if( $this->_settings['colored_icons'] !== '' ){
			return true;
		}

		return $mod;
	}

	function icons($mod){

		if( $custom_icons = $this->_settings['icons'] ){

			$mod = [];

			foreach ($custom_icons as $key => $value) {
				$mod[]['social_icon'] = $value['icon'];
			}

		}

		return $mod;
	}

}
