<?php
namespace ReyCore\Modules\CustomTemplates;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WooLoopTitle extends WooBase {

	public $_settings;

	public function get_name() {
		return 'reycore-woo-loop-title';
	}

	public function get_title() {
		return __( 'Product Archive Title', 'rey-core' );
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
			'section_styles',
			[
				'label' => __( 'Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'show_label' => false,
			]
		);

			$selectors['main'] = '{{WRAPPER}} .woocommerce-products-header__title';


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
						$selectors['main'] => 'text-align: {{VALUE}};',
					],
				]
			);

			$this->add_control(
				'tag',
				[
					'label' => esc_html__( 'HTML Tag', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'h1',
					'options' => [
						'h1'  => esc_html__( 'H1', 'rey-core' ),
						'h2'  => esc_html__( 'H2', 'rey-core' ),
						'h3'  => esc_html__( 'H3', 'rey-core' ),
						'h4'  => esc_html__( 'H4', 'rey-core' ),
						'h5'  => esc_html__( 'H5', 'rey-core' ),
						'h6'  => esc_html__( 'H6', 'rey-core' ),
						'div'  => esc_html__( 'Div', 'rey-core' ),
						'span'  => esc_html__( 'Span', 'rey-core' ),
						'p'  => esc_html__( 'P', 'rey-core' ),
					],
				]
			);

			$this->add_control(
				'show_back',
				[
					'label' => esc_html__( 'Show back button', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- Select -', 'rey-core' ),
						'yes'  => esc_html__( 'Yes', 'rey-core' ),
						'no'  => esc_html__( 'No', 'rey-core' ),
					],
				]
			);

		$this->end_controls_section();

	}

	function render_template() {

		$this->_settings = $this->get_settings_for_display();

		add_filter('theme_mod_archive__title_back', [$this, 'back_button']);
		$tag = \ReyCore\Helper::get_valid_title_tag($this->_settings['tag'], 'h1');
		?>
		<<?php echo $tag ?> class="woocommerce-products-header__title page-title">
			<?php woocommerce_page_title(); ?>
		</<?php echo $tag ?>>
		<?php

		remove_filter('theme_mod_archive__title_back', [$this, 'back_button']);

	}

	function back_button($mod){

		if( $show_back = $this->_settings['show_back'] ){
			return $show_back === 'yes';
		}

		return $mod;
	}

}
