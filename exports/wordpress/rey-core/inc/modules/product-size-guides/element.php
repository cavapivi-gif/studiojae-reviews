<?php
namespace ReyCore\Modules\ProductSizeGuides;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Element extends \ReyCore\Modules\CustomTemplates\WooBase {

	public $_settings = [];

	public function get_name() {
		return 'reycore-woo-pdp-size-guide';
	}

	public function get_title() {
		return __( 'Size Guide Button (PDP)', 'rey-core' );
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
				'size_guide',
				[
					'label' => esc_html__( 'Size Guide', 'rey-core' ),
					'description' => esc_html__( 'Leave empty to automatically be assigned.', 'rey-core' ),
					'label_block' => true,
					'type'        => 'rey-query',
					'placeholder' => esc_html__('- Select -', 'rey-core'),
					'default' => '',
					'query_args'  => [
						'type'      => 'posts',
						'post_type' => Base::POST_TYPE,
						'edit_link' => true,
					],
				]
			);

			$this->add_control(
				'button_text',
				[
					'label' => esc_html__( 'Button Text', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'placeholder' => esc_html__( 'eg: Size Guide', 'rey-core' ),
				]
			);

			$this->add_control(
				'modal_width',
				[
					'label' => esc_html__( 'Modal Width', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 2000,
					'step' => 0,
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
				'btn' => '{{WRAPPER}} .rey-sizeGuide-btn',
				'btn_hover' => '{{WRAPPER}} .rey-sizeGuide-btn:hover',
			];

			$this->add_control(
				'btn_style',
				[
					'type' => \Elementor\Controls_Manager::SELECT,
					'label'       => esc_html__( 'Button Style', 'rey-core' ),
					'default'     => '',
					'options'     => [
						''     => '- Inherit -',
						'primary'     => 'Primary',
						'secondary'   => 'Secondary',
						'line-active' => 'Underlined',
						'line'        => 'Underlined on hover',
						'simple'      => 'Simple',
						'minimal'     => 'Minimal',
					],
				]
			);

			$this->add_control(
				'color',
				[
					'label' => esc_html__( 'Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selectors['btn'] => 'color: {{VALUE}};',
					],
				]
			);

			$this->add_control(
				'bg_color',
				[
					'label' => esc_html__( 'Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selectors['btn'] => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'color_hover',
				[
					'label' => esc_html__( 'Hover Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selectors['btn_hover'] => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'bg_color_hover',
				[
					'label' => esc_html__( 'Hover Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selectors['btn_hover'] => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'typo',
					'selector' => $selectors['btn'],
				]
			);

			$this->add_control(
				'use_icon',
				[
					'label' => esc_html__( 'Icon', 'rey-core' ),
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
				'custom_icon',
				[
					'label' => __( 'Custom Icon', 'elementor' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'condition' => [
						'use_icon' => 'yes',
					],
				]
			);

			$this->add_control(
				'icon_after',
				[
					'label' => esc_html__( 'Icon Position', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- Inherit -', 'rey-core' ),
						'before'  => esc_html__( 'Before', 'rey-core' ),
						'after'  => esc_html__( 'After', 'rey-core' ),
					],
					'condition' => [
						'use_icon' => 'yes',
					],
				]
			);

		$this->end_controls_section();

	}

	function render_template() {

		$this->_settings = $this->get_settings_for_display();

		add_filter('theme_mod_pdp_size_guides_button_style', [$this, 'btn_style']);
		add_filter('theme_mod_pdp_size_guides_button_text', [$this, 'btn_text']);
		add_filter('theme_mod_pdp_size_guides_button_icon', [$this, 'btn_icon']);
		add_filter('reycore/size_guides/settings', [$this, 'override_settings']);

		if( $mod = reycore__get_module('product-size-guides') ){
			$mod->render_button();
		}

		remove_filter('theme_mod_pdp_size_guides_button_style', [$this, 'btn_style']);
		remove_filter('theme_mod_pdp_size_guides_button_text', [$this, 'btn_text']);
		remove_filter('theme_mod_pdp_size_guides_button_icon', [$this, 'btn_icon']);
		remove_filter('reycore/size_guides/settings', [$this, 'override_settings']);
	}

	function override_settings($settings){

		if( 'yes' === $this->_settings['use_icon'] ){
			if( ! empty($this->_settings['custom_icon']) && ($custom_icon = $this->_settings['custom_icon']) && isset($custom_icon['value']) && !empty($custom_icon['value']) ){
				$settings['icon'] = \ReyCore\Elementor\Helper::render_icon( $custom_icon, [ 'aria-hidden' => 'true', 'class' => 'rey-icon' ] );
			}
			if( 'after' === $this->_settings['icon_after'] ){
				$settings['icon_after'] = true;
			}
			elseif( 'before' === $this->_settings['icon_after'] ){
				$settings['icon_after'] = false;
			}
		}

		if( $modal_width = $this->_settings['modal_width'] ){
			$settings['modal_width'] = $modal_width;
		}

		return $settings;
	}

	function btn_icon($mod){

		if( '' !== $this->_settings['use_icon'] ){
			return 'no' !== $this->_settings['use_icon'];
		}

		return $mod;
	}

	function btn_text($mod){

		if( $text = $this->_settings['button_text'] ){
			return $text;
		}

		return $mod;
	}

	function btn_style($mod){

		if( $style = $this->_settings['btn_style'] ){
			return $style;
		}

		return $mod;
	}

}
