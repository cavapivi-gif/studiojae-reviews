<?php
namespace ReyCore\Modules\ProductLoopGs\ElementorWidgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Rating extends WooBase {

	public function get_name() {
		return 'reycore-woo-grid-rating';
	}

	public function get_title() {
		return __( 'Rating (Product Grid)', 'rey-core' );
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

			$this->add_control(
				'layout',
				[
					'label' => esc_html__( 'Layout', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => [
						''  => esc_html__( '- Inherit -', 'rey-core' ),
						'default'  => esc_html__( 'Default', 'rey-core' ),
						'extended'  => esc_html__( 'Extended', 'rey-core' ),
					],
				]
			);

			$this->add_control(
				'show_empty',
				[
					'label' => esc_html__( 'Show if 0 reviews', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'default' => '',
				]
			);

			$this->add_wrapper_css_class();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_styles',
			[
				'label' => __( 'Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				'show_label' => false,
			]
		);

			$selectors['main'] = '{{WRAPPER}} .star-rating';


			$this->add_control(
				'color',
				[
					'label' => esc_html__( 'Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						$selectors['main'] => '--star-rating-color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'size',
				[
					'label' => esc_html__( 'Size', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						$selectors['main'] => '--star-rating-size: {{VALUE}}px',
					],
				]
			);

			$this->add_control(
				'spacing',
				[
					'label' => esc_html__( 'Spacing', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						$selectors['main'] => '--star-rating-spacing: {{VALUE}}px',
					],
				]
			);

			$this->add_responsive_control(
				'alignment',
				[
					'label' => __( 'Alignment', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::CHOOSE,
					'options' => [
						'flex-start' => [
							'title'     => __( 'Left', 'rey-core' ),
							'icon'      => 'eicon-text-align-left',
						],
						'center'     => [
							'title'     => __( 'Center', 'rey-core' ),
							'icon'      => 'eicon-text-align-center',
						],
						'flex-end'   => [
							'title'     => __( 'Right', 'rey-core' ),
							'icon'      => 'eicon-text-align-right',
						],
					],
					'default' => '',
					'selectors' => [
						'{{WRAPPER}} .__ratings' => 'justify-content: {{VALUE}};',
					],
				]
			);

		$this->end_controls_section();

	}

	public function render_template() {

		if( ! ( $component = \ReyCore\Plugin::instance()->woocommerce_loop->get_component('ratings') ) ){
			return;
		}

		echo '<div class="__ratings">';

		add_filter('theme_mod_loop_ratings', [$this, '__enable']);

		if( '' !== $this->_settings['show_empty'] ){
			add_filter('woocommerce_product_get_rating_html', [$this, '__show_empty'], 5, 3);
		}

		if( 'extended' === $this->_settings['layout'] ){
			add_filter('theme_mod_loop_ratings_extend', '__return_true');
			add_filter('woocommerce_product_get_rating_html', [$component, 'extend_rating_display'], 10, 3);
		}

			woocommerce_template_loop_rating();

		if( 'extended' === $this->_settings['layout'] ){
			remove_filter('woocommerce_product_get_rating_html', [$component, 'extend_rating_display'], 10, 3);
			remove_filter('theme_mod_loop_ratings_extend', '__return_true');
		}

		if( '' !== $this->_settings['show_empty'] ){
			add_filter('woocommerce_product_get_rating_html', [$this, '__show_empty'], 5, 3);
		}

		remove_filter('theme_mod_loop_ratings', [$this, '__enable']);

		echo '</div>';

	}

	public function __enable(){
		return '1';
	}

	public function __show_empty($html, $rating, $count){

		if ( 0 == $rating ) {
			/* translators: %s: rating */
			$label = sprintf( __( 'Rated %s out of 5', 'woocommerce' ), $rating );
			$html  = '<div class="star-rating" role="img" aria-label="' . esc_attr( $label ) . '">' . wc_get_star_rating_html( $rating, $count ) . '</div>';
		}

		return $html;

	}
}
