<?php
namespace ReyCore\Elementor\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( ! class_exists('\WooCommerce') ){
	return;
}

class HeaderWishlist extends \ReyCore\Elementor\WidgetsBase {

	public $_settings = [];

	public static function get_rey_config(){
		return [
			'id' => 'header-wishlist',
			'title' => __( 'Wishlist (Header)', 'rey-core' ),
			'icon' => 'eicon-heart-o',
			'categories' => [ 'rey-header' ],
			'keywords' => [],
		];
	}

	public function rey_get_script_depends() {
		return [ 'reycore-woocommerce', 'reycore-wc-header-wishlist', 'reycore-wishlist', 'reycore-elementor-elem-header-wishlist', 'rey-tmpl' ];
	}

	// public function get_custom_help_url() {
	// 	return reycore__support_url('kb/rey-elements-header/#wishlist');
	// }

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
				'text',
				[
					'label' => esc_html__( 'Text', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'placeholder' => esc_html__( 'eg: WISHLIST', 'rey-core' ),
				]
			);

			$this->add_control(
				'icon',
				[
					'label' => esc_html__( 'Icon', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => get_theme_mod('wishlist__icon_type', 'heart'),
					'options' => [
						''  => esc_html__( 'None', 'rey-core' ),
						'heart'  => esc_html__( 'Heart', 'rey-core' ),
						'favorites'  => esc_html__( 'Ribbon', 'rey-core' ),
						'custom'  => esc_html__( '- Custom Icon -', 'rey-core' ),
					],
				]
			);

			$this->add_control(
				'custom_icon',
				[
					'label' => __( 'Custom Icon', 'elementor' ),
					'type' => \Elementor\Controls_Manager::ICONS,
					'condition' => [
						'icon' => 'custom',
					],

				]
			);

			$this->add_control(
				'counter_layout',
				[
					'label' => esc_html__( 'Counter layout', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'minimal',
					'options' => [
						''  => esc_html__( 'Hide', 'rey-core' ),
						'minimal'  => esc_html__( 'Minimal', 'rey-core' ),
						'bubble'  => esc_html__( 'Bubble', 'rey-core' ),
						'out'  => esc_html__( 'Outline', 'rey-core' ),
						'text'  => esc_html__( 'Text', 'rey-core' ),
						// 'icon'  => esc_html__( 'In Icon', 'rey-core' ),
					],
				]
			);


		$this->end_controls_section();

		$this->start_controls_section(
			'section_styles',
			[
				'label' => __( 'Style', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE
			]
		);

			$this->add_responsive_control(
				'icon_size',
				[
					'label' => esc_html__( 'Icon Size', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 1,
					'max' => 200,
					'step' => 1,
					'condition' => [
						'icon!' => '',
					],
					'selectors' => [
						'{{WRAPPER}}' => '--icon-size: {{VALUE}}px',
					],
				]
			);

			$this->add_control(
				'color',
				[
					'label' => esc_html__( 'Icon Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .btn' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'hover_color',
				[
					'label' => esc_html__( 'Icon Color on Hover', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .btn:hover' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'counter_color',
				[
					'label' => esc_html__( 'Counter background color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .--bubble' => 'background-color: {{VALUE}}',
					],
					'condition' => [
						'counter_layout' => 'bubble',
					],
				]
			);

			$this->add_control(
				'counter_text_color',
				[
					'label' => esc_html__( 'Counter text color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .--bubble' => 'color: {{VALUE}}',
					],
					'condition' => [
						'counter_layout' => 'bubble',
					],
				]
			);

			$this->add_control(
				'text_position',
				[
					'label' => __( 'Text Position', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'after',
					'options' => [
						'before' => esc_html__( 'Before', 'rey-core' ),
						'after' => esc_html__( 'After', 'rey-core' ),
						'under' => esc_html__( 'Under', 'rey-core' ),
					],
					'condition' => [
						'text!' => '',
					],
					'separator' => 'before'
				]
			);

			$this->add_control(
				'text_distance',
				[
					'label' => esc_html__( 'Text Distance', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
					'type' => \Elementor\Controls_Manager::NUMBER,
					'default' => '',
					'min' => 0,
					'max' => 1000,
					'step' => 1,
					'selectors' => [
						'{{WRAPPER}}' => '--text-distance: {{VALUE}}px',
					],
					'condition' => [
						'text!' => '',
					],
				]
			);


			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'label' => esc_html__('Text Typography', 'rey-core'),
					'name' => 'text_typo',
					'selector' => '{{WRAPPER}} .rey-headerIcon-btnText',
					'condition' => [
						'text!' => '',
					],
				]
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_panel_styles',
			[
				'label' => __( 'Panel Styles', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE
			]
		);

			$this->add_control(
				'products_layout',
				[
					'label' => esc_html__( 'Products layout', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'grid',
					'options' => [
						'grid'  => esc_html__( 'Grid', 'rey-core' ),
						'list'  => esc_html__( 'List', 'rey-core' ),
					],
				]
			);

			$this->add_control(
				'panel_color',
				[
					'label' => esc_html__( 'Text Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-elWishlist-content' => '--color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'panel_bg_color',
				[
					'label' => esc_html__( 'Background Color', 'rey-core' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .rey-elWishlist-content' => '--background-color: {{VALUE}}',
					],
				]
			);

		$this->end_controls_section();

	}

	public function render_start()
	{
		reycore_assets()->add_styles(['rey-header-drop-panel', 'rey-wc-header-wishlist', 'rey-header-icon']);
		reycore_assets()->add_scripts('rey-drop-panel');

		$this->add_render_attribute( 'wrapper', [
			'class' => [
				'rey-elWishlist',
				'rey-headerIcon',
				'rey-header-dropPanel',
			],
			'data-droppanel' => wp_json_encode([
				'mobileStretch' => true
			]),
			'data-layout' => 'drop',
		] ); ?>

		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
		<?php
	}

	public function render_end()
	{
		?></div><?php
	}

	function counter_html(){
		return class_exists('\ReyCore\WooCommerce\Tags\Wishlist') ? \ReyCore\WooCommerce\Tags\Wishlist::get_wishlist_counter_html() : '';
	}

	function get_url(){
		return class_exists('\ReyCore\WooCommerce\Tags\Wishlist') ? \ReyCore\WooCommerce\Tags\Wishlist::get_wishlist_url() : '';
	}

	public function render_button(){

		$text_html = $icon_html = $counter_layout_html = "";

		if( $text = $this->_settings['text'] ){
			$text_html = "<span class=\"rey-headerIcon-btnText\">{$text}</span>";
		}

		if( $icon = $this->_settings['icon'] ){

			if( $icon === 'custom' ){
				if( ($custom_icon = $this->_settings['custom_icon']) && isset($custom_icon['value']) && !empty($custom_icon['value']) ){
					$icon_html = \ReyCore\Elementor\Helper::render_icon( $custom_icon, [ 'aria-hidden' => 'true', 'class' => 'rey-headerWishlist-customIcon rey-icon' ] );
			}
			}
			else {
				$icon_html = reycore__get_svg_icon([
					'id' => $icon,
					'class' => 'rey-elWishlist-btnIcon'
				]);
			}
		}

		$counter_layout_html = '';
		if( $this->_settings['counter_layout'] !== '' ){
			$counter_layout_html = sprintf('<span class="rey-headerAccount-count rey-headerIcon-counter --hidden --%3$s">%1$s%2$s</span>',
				(class_exists('\ReyCore\WooCommerce\Tags\Wishlist') ? \ReyCore\WooCommerce\Tags\Wishlist::get_wishlist_counter_html() : ''),
				reycore__get_svg_icon(['id' => 'close', 'class' => '__close-icon', 'attributes' => ['data-transparent' => '', 'data-abs' => '']]),
				esc_attr($this->_settings['counter_layout'])
			);
		}

		$classes = [
			'--itype-' . $this->_settings['icon'],
			'--tp-' . $this->_settings['text_position'], // legacy
			'--hit-' . $this->_settings['text_position'],
		];

		printf('<button class="btn rey-headerIcon-btn rey-header-dropPanel-btn %s" aria-label="%s">', esc_attr( implode(' ', $classes)), esc_html__('Open', 'rey-core'));
			echo $text_html;
			echo sprintf('<span class="__icon rey-headerIcon-icon">%s</span>', $icon_html);
			echo $counter_layout_html;
		echo '</button>';
	}

	public function render_panel() {

		echo '<div class="rey-header-dropPanel-content rey-elWishlist-content" data-lazy-hidden>';

			$title_tag = apply_filters('reycore/elementor/header-wishlist/title_tag', 'div');

			printf('<%s class="rey-wishlistPanel-title">', $title_tag);

				$title = '';

				if( class_exists('\ReyCore\WooCommerce\Tags\Wishlist') ){
					$title = \ReyCore\WooCommerce\Tags\Wishlist::title();
				}

				if( $wishlist_url = $this->get_url() ){
					printf( '<a href="%s">%s</a>', esc_url( $wishlist_url ), $title );
				}
				else {
					echo $title;
				}

				echo $this->counter_html();

			printf('</%s>', $title_tag);

			printf('<div class="rey-wishlistPanel-container" data-type="%s">', esc_attr($this->_settings['products_layout']) );
				echo '<div class="rey-elWishlist-panel rey-wishlistPanel"></div>';
				echo '<div class="rey-lineLoader"></div>';
			echo '</div>';
		echo '</div>';

		if( $wishlist_tag = \ReyCore\Plugin::instance()->woocommerce_tags[ 'wishlist' ] ){
			$wishlist_tag->load_dependencies();
		}

	}

	protected function render() {

		add_filter('theme_mod_header_account_wishlist', '__return_true');

		reycore_assets()->add_styles(['rey-wc-header-account-panel-top', 'rey-wc-header-wishlist-element']);
		reycore_assets()->add_scripts( $this->rey_get_script_depends() );

		$this->_settings = $this->get_settings_for_display();
		$this->render_start();
		$this->render_button();
		$this->render_panel();
		$this->render_end();

		remove_filter('theme_mod_header_account_wishlist', '__return_true');

	}

	protected function content_template() {}
}
