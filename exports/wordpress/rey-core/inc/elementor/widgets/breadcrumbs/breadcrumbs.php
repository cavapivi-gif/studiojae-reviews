<?php
namespace ReyCore\Elementor\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
exit; // Exit if accessed directly.
}

class Breadcrumbs extends \ReyCore\Elementor\WidgetsBase {

	public $custom_home_url;
	public $args;

	public static function get_rey_config(){
		return [
			'id' => 'breadcrumbs',
			'title' => __( 'Breadcrumbs', 'rey-core' ),
			'icon' => 'eicon-product-breadcrumbs',
			'categories' => [ 'rey-theme' ],
			'keywords' => [],
		];
	}

	public function get_custom_help_url() {
		return reycore__support_url('kb/rey-elements/#breadcrumbs');
	}

	/**
	 * Register widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'section_settings',
			[
				'label' => __( 'Settings', 'rey-core' ),
			]
		);

		$this->add_control(
			'delimiter',
			[
				'label' => __( 'Custom Delimiter', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => __( '', 'rey-core' ),
				'placeholder' => __( 'eg: /', 'rey-core' ),
			]
		);

		$this->add_control(
			'add_home',
			[
				'label' => __( 'Add Home?', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'home_url',
			[
				'label' => esc_html__( 'Home URL', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'condition' => [
					'add_home!' => '',
				],
			]
		);

		$this->add_control(
			'home',
			[
				'label' => __( 'Home Text', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Home', 'rey-core' ),
				'condition' => [
					'add_home' => ['yes'],
				],
			]
		);

		$this->add_responsive_control(
			'align',
			[
				'label' => __( 'Alignment', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => __( 'Left', 'rey-core' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'rey-core' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => __( 'Right', 'rey-core' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'prefix_class' => 'elementor%s-align-',
			]
		);


		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Style', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'typography',
				'selector' => '{{WRAPPER}} .rey-breadcrumbs',
			]
		);

		$this->add_control(
			'text_color',
			[
				'label' => __( 'Text Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}}' => 'color: {{VALUE}};',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_breadcrumbs_style' );

		$this->start_controls_tab(
			'tab_color_normal',
			[
				'label' => __( 'Normal', 'rey-core' ),
			]
		);

		$this->add_control(
			'link_color',
			[
				'label' => __( 'Link Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_color_hover',
			[
				'label' => __( 'Hover', 'rey-core' ),
			]
		);

		$this->add_control(
			'link_hover_color',
			[
				'label' => __( 'Color', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} a:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Render widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {

		ob_start();

		/**
		 * Override Breadcrumbs by hooking into `rey/breadcrumbs/override`.
		 * This will shortcircuit  the code and display whatever is hooked.
		 */
		do_action('rey/breadcrumbs/override', $this);

		$override = ob_get_clean();

		if( $override ){
			echo $override;
			return;
		}

		$settings = $this->get_settings_for_display();

		$this->add_render_attribute( 'wrapper', 'class', ['rey-element', 'reyEl-breadcrumbs'] ); ?>

		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>

			<?php

			reycore_assets()->add_styles('reycore-breadcrumbs');

			/**
			 * WooCommerce Breadcrumbs
			 * @from woocommerce/includes/class-wc-breadcrumb.php
			 */

			$delimiter = '&#8250;';

			if( $settings['delimiter'] != '' ){
				$delimiter = $settings['delimiter'];
			}

			$this->args = [
				'delimiter'   => '<span class="rey-breadcrumbs-del">'. $delimiter .'</span>',
				'wrap_before' => reycore__breadcrumbs_nav_tag(),
				'wrap_after'  => '</nav>',
				'before'      => '<div class="rey-breadcrumbs-item">',
				'after'       => '</div>',
				'home'       => 'yes' == $settings['add_home'] ? $settings['home'] : '',
			];

			$home_url = home_url();
			$this->custom_home_url = '';

			if( 'yes' == $settings['add_home'] && ($this->custom_home_url = $settings['home_url']) ){
				$home_url = $this->custom_home_url;
			}

			/**
			 * WooCommerce is installed and active.
			 */
			if( function_exists('woocommerce_breadcrumb') ) {

				add_filter('woocommerce_breadcrumb_home_url', [$this, 'add_home_url']);
				add_filter('woocommerce_breadcrumb_defaults', [$this, 'add_defaults']);

					woocommerce_breadcrumb();

				remove_filter('woocommerce_breadcrumb_home_url', [$this, 'add_home_url']);
				remove_filter('woocommerce_breadcrumb_defaults', [$this, 'add_defaults']);

			}

			/**
			 * When WooCommerce is NOT installed and active.
			 * Fallbacks to ReyCore Breadcrumb which is a near clone of WooCommerce Breadcrumb.
			 */
			else if( class_exists('\ReyCore\Libs\Breadcrumb') ) {

				$breadcrumbs = new \ReyCore\Libs\Breadcrumb();

				if ( $this->args['home'] ) {
					$breadcrumbs->add_crumb( $this->args['home'], apply_filters( 'woocommerce_breadcrumb_home_url', $home_url ) );
				}

				$this->args['breadcrumb'] = $breadcrumbs->generate();

				/**
				 * WooCommerce Breadcrumb hook
				 *
				 * @hooked WC_Structured_Data::generate_breadcrumblist_data() - 10
				 */
				do_action( 'woocommerce_breadcrumb', $breadcrumbs, $this->args );

				if ( ! empty( $this->args['breadcrumb'] ) ) {

					echo $this->args['wrap_before'];

					foreach ( $this->args['breadcrumb'] as $key => $crumb ) {

						echo $this->args['before'];

						if ( ! empty( $crumb[1] ) && sizeof( $this->args['breadcrumb'] ) !== $key + 1 ) {
							echo '<a href="' . esc_url( $crumb[1] ) . '">' . esc_html( $crumb[0] ) . '</a>';
						} else {
							echo esc_html( $crumb[0] );
						}

						echo $this->args['after'];

						if ( sizeof( $this->args['breadcrumb'] ) !== $key + 1 ) {
							echo $this->args['delimiter'];
						}
					}

					echo $this->args['wrap_after'];
				}
			}
			?>
		</div>
		<!-- .reyEl-breadcrumbs -->
		<?php
	}

	public function add_home_url($val){
		if( $this->custom_home_url ){
			return $this->custom_home_url;
		}
		return $val;
	}

	public function add_defaults(){
		return $this->args;
	}

	/**
	 * Render widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function content_template() {}
}
