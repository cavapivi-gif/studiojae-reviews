<?php
namespace ReyCore\Elementor\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Hotspots extends \ReyCore\Elementor\WidgetsBase {

	public $_settings = [];

	public $has_link = false;

	public static function get_rey_config(){
		return [
			'id' => 'hotspots',
			'title' => __( 'HotSpot Tooltip', 'rey-core' ),
			'icon' => 'eicon-image-hotspot',
			'categories' => [ 'rey-theme' ],
			'keywords' => [],
			'css' => [
				'assets/style[rtl].css',
			],
			'js' => [
				'assets/script.js',
			],
		];
	}

	public function rey_get_script_depends() {
		return [ 'reycore-widget-hotspots-scripts' ];
	}

	public function get_custom_help_url() {
		return reycore__support_url('kb/rey-elements/#hotspots');
	}

	public function on_export($element)
	{
		if( isset($element['settings']['product']) ){
			unset( $element['settings']['product'] );
		}
		return $element;
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
			'section_tooltip_layout',
			[
				'label' => __( 'Hotspot', 'rey-core' ),
			]
		);

		$this->add_control(
			'note',
			[
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => __( 'To position the tooltip, please access Advanced tab > Custom Positioning, set Width on <strong>Inline (auto)</strong> and Position on <strong>Absolute</strong>. .', 'rey-core' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-danger',
				'condition' => [
					'_element_width!' => 'auto',
					'_position!' => 'absolute',
				],
			]
		);

		$this->add_control(
			'hotspot_content',
			[
				'label' => esc_html__( 'Hotspot Content', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'icon',
				'options' => [
					'image'  => esc_html__( 'Image', 'rey-core' ),
					'icon'  => esc_html__( 'Icon', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'hotspot_image',
			[
			   'label' => esc_html__( 'Image', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::MEDIA,
				'default' => [
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				],
				'condition' => [
					'hotspot_content' => 'image',
				],
			]
		);

		$this->add_control(
			'hotspot_icon',
			[
				'label' => __( 'Icon', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::ICONS,
				// 'fa4compatibility' => 'icon',
				'default' => [
					'value' => 'fas fa-plus',
					'library' => 'fa-solid',
				],
				'condition' => [
					'hotspot_content' => 'icon',
				],
			]
		);

		$this->add_control(
			'link',
			[
				'label' => __( 'Link', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::URL,
				'dynamic' => [
					'active' => true,
				],
				'placeholder' => __( 'https://your-link.com', 'rey-core' ),
				'default' => [
					'url' => '#',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Panel Content', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'content_type',
			[
				'label' => esc_html__( 'Content type', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'text',
				'options' => [
					'text'  => esc_html__( 'Custom Text', 'rey-core' ),
					'product'  => esc_html__( 'Product', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'text',
			[
				'label' => __( 'Text Content', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::WYSIWYG,
				'default' => __( 'Tooltip Content', 'rey-core' ),
				'show_label' => false,
				'condition' => [
					'content_type' => 'text',
				],
			]
		);

		$this->add_control(
			'product',
			[
				'label' => esc_html__( 'Select Product', 'rey-core' ),
				'default' => '',
				'label_block' => true,
				'type' => 'rey-query',
				'query_args' => [
					'type' => 'posts',
					'post_type' => 'product',
				],
				'condition' => [
					'content_type' => 'product',
				],
			]
		);

		$this->add_control(
			'open_quickview',
			[
				'label' => esc_html__( 'Open Quickview on click', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
				'condition' => [
					'content_type' => 'product',
				],
			]
		);

		$this->add_control(
			'pos',
			[
				'label' => esc_html__( 'Panel Position', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'right',
				'options' => [
					'top' => esc_html__( 'Top', 'rey-core' ),
					'right' => esc_html__( 'Right', 'rey-core' ),
					'bottom' => esc_html__( 'Bottom', 'rey-core' ),
					'left' => esc_html__( 'Left', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'pos_align',
			[
				'label' => esc_html__( 'Panel Align', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'start',
				'options' => [
					'start'  => esc_html__( 'Start', 'rey-core' ),
					'middle'  => esc_html__( 'Middle', 'rey-core' ),
					'end'  => esc_html__( 'End', 'rey-core' ),
				],
			]
		);

		$this->add_control(
			'panel_disable',
			[
				'label' => esc_html__( 'Disable the panel', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
			]
		);


		$this->end_controls_section();


		$this->start_controls_section(
			'section_hotspot_style',
			[
				'label' => __( 'Hotspot Style', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'hotspot_style',
			[
				'label' => esc_html__( 'Hotspot Style', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'default',
				'options' => [
					'default'  => esc_html__( 'Default', 'rey-core' ),
				],
				'condition' => [
					'hotspot_content' => 'icon',
				],
			]
		);

		$this->add_responsive_control(
			'hotspot_size',
			[
			   'label' => esc_html__( 'Hotspot Size', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 9,
						'max' => 180,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .rey-hotspots' => '--ht-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'hotspot_icon_size',
			[
			   'label' => esc_html__( 'Icon Size', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'em' ],
				'range' => [
					'em' => [
						'min' => 0.05,
						'max' => 1.0,
						'step' => 0.01,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .rey-hotspots-type--icon .rey-hotspot i' => 'font-size: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'hotspot_content' => 'icon',
				],
			]
		);

		$this->add_control(
			'hotspot_primary_color',
			[
				'label' => esc_html__( 'Primary Color (Background)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-hotspots' => '--ht-primary-color: {{VALUE}}',
				],
				'condition' => [
					'hotspot_content' => 'icon',
				],
			]
		);

		$this->add_control(
			'hotspot_secondary_color',
			[
				'label' => esc_html__( 'Secondary Color (Text)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-hotspots' => '--ht-secondary-color: {{VALUE}}',
				],
				'condition' => [
					'hotspot_content' => 'icon',
				],
			]
		);

		$this->add_control(
			'animated',
			[
				'label' => esc_html__( 'Animate Tooltip', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [
					'hotspot_content' => 'icon',
				],
			]
		);

		$this->add_control(
			'animation_type',
			[
				'label' => esc_html__( 'Animation type', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'default',
				'options' => [
					'default'  => esc_html__( 'Smooth blink (default)', 'rey-core' ),
					'pulse'  => esc_html__( 'Pulse', 'rey-core' ),
				],
				'condition' => [
					'animated!' => '',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_panel_style',
			[
				'label' => __( 'Panel Style', 'rey-core' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'panel_size',
			[
			   'label' => esc_html__( 'Size', 'rey-core' ) . \ReyCore\Elementor\Helper::px_badge(),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 100,
						'max' => 600,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .rey-hotspots' => '--pn-width: {{SIZE}}{{UNIT}};',
				],

			]
		);

		$this->add_control(
			'panel_primary_color',
			[
				'label' => esc_html__( 'Primary Color (Background)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-hotspots' => '--pn-primary-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'panel_secondary_color',
			[
				'label' => esc_html__( 'Secondary Color (Text)', 'rey-core' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .rey-hotspots' => '--pn-secondary-color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_section();
	}

	public function render_start()
	{
		$classes = [
			'rey-hotspots',
			'rey-hotspots-type--' . $this->_settings['hotspot_content'],
			'rey-hotspots-style--' . $this->_settings['hotspot_style'],
			'rey-hotspots-pos--' . $this->_settings['pos'],
			'rey-hotspots-align--' . $this->_settings['pos_align'],
			'rey-hotspots-ctype--' . $this->_settings['content_type'],
		];

		if( $this->_settings['animated'] === 'yes' ){
			$classes[] = '--animated';
			$classes[] = '--animation-' . $this->_settings['animation_type'];
		}

		$this->add_render_attribute( 'wrapper', 'class', $classes );
		?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
		<?php
	}

	public function render_end()
	{
		?></div><?php
	}

	public function render_link_start() {

		$href = '';

		if( class_exists('\WooCommerce') && $this->_settings['content_type'] === 'product' && ($product_id = $this->_settings['product']) ){

			$this->has_link = true;

			// Quickview enabled
			if( $this->_settings['open_quickview'] === 'yes'  && reycore_wc__get_loop_component_status('quickview') ){

				$this->add_render_attribute( 'link_wrapper' , 'data-id', absint($product_id) );

				$href = '#'; // placeholder

				if( !\Elementor\Plugin::$instance->editor->is_edit_mode() ){
					$this->add_render_attribute( 'link_wrapper' , 'class', 'js-rey-quickviewBtn' );
				}

				// make sure assets are loaded
				if( ($qv = reycore__get_module('quickview')) && method_exists($qv, 'add_assets') ){
					$qv->add_assets();
				}

			}

			// just link the product
			else {
				$href = get_permalink($product_id);
			}
		}

		if( $url = $this->_settings['link']['url'] ){

			$this->has_link = true;

			$href = $url;

			if( $this->_settings['link']['is_external'] ){
				$this->add_render_attribute( 'link_wrapper' , 'target', '_blank' );
			}

			if( $this->_settings['link']['nofollow'] ){
				$this->add_render_attribute( 'link_wrapper' , 'rel', 'nofollow' );
			}
		}

		if( !$this->has_link ){
			return;
		}

		$this->add_render_attribute( 'link_wrapper' , [
			'href' => $href,
			'class' => 'rey-hotspotLink',
			'aria-label' => esc_html__('Hover to preview', 'rey-core'),
			'role' => 'button'
		] );

		?>

		<a <?php echo  $this->get_render_attribute_string('link_wrapper'); ?>><?php
	}

	public function render_link_end()
	{
		if( !$this->has_link ){
			return;
		}

		?></a><?php
	}

	public function render_hotspot(){

		echo '<div class="rey-hotspot">';

		if( $this->_settings['hotspot_content'] === 'icon' && ($icon = $this->_settings['hotspot_icon']) ){
			// helper
			echo '<div class="rey-hotspotHelper"></div>';
			// prints icon
			\Elementor\Icons_Manager::render_icon( $icon, [ 'aria-hidden' => 'true' ] );
		}

		elseif( $this->_settings['hotspot_content'] === 'image' && ($image = $this->_settings['hotspot_image']) ){

			echo reycore__get_attachment_image( [
				'image' => $image,
				'size' => 'medium',
				'attributes' => ['class'=>'rey-hotspotImg']
			] );
		}

		echo '</div>';
	}

	public function render_the_panel_text(){

		if( $this->_settings['content_type'] !== 'text'){
			return;
		}

		if( !($text = $this->_settings['text']) ) {
			return;
		}

		printf('<div class="rey-hotspots-panel"><div class="rey-hotspots-panelInner">%s</div></div>', $text);

	}

	public function render_the_panel_product(){

		if( !class_exists('\WooCommerce') ){
			return;
		}

		if( $this->_settings['content_type'] !== 'product'){
			return;
		}

		if( !(($product_id = $this->_settings['product']) && $product = wc_get_product($product_id) ) ) {
			return;
		}

		echo '<div class="rey-hotspots-panel"><div class="rey-hotspots-panelInner">';

		printf( '<p class="rey-hsProduct-img"><a href="%s">%s</a></p>',
			get_permalink($product_id),
			$product->get_image()
		);
		printf( '<h4 class="rey-hsProduct-title">%s</h4>', $product->get_title() );
		printf( '<p class="rey-hsProduct-price">%s</p>', $product->get_price_html() );

		echo '<p class="rey-hsProduct-atc">';

			reycore_assets()->add_styles('rey-buttons');

			$atc_attributes = [
				'quantity' => 1,
				'class' => implode(' ', array_filter([
					'btn btn-line-active',
					'product_type_' . $product->get_type(),
					$product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
					$product->supports('ajax_add_to_cart') ? 'ajax_add_to_cart' : '',
				])),
				'data-product_id' => $product_id,
				// 'data-product_sku' => $product->get_sku(),
				'aria-label' => strip_tags( $product->add_to_cart_description() ),
				'rel' => 'nofollow',
			];

			add_filter('woocommerce_product_add_to_cart_url', [$this, 'add_to_cart_url'], 20, 2);

			printf( '<a href="%1$s" %2$s>%3$s</a>',
				$product->add_to_cart_url(),
				reycore__implode_html_attributes( $atc_attributes ),
				! get_theme_mod('shop_catalog', false) && $product->is_purchasable() ? $product->single_add_to_cart_text() : $product->add_to_cart_text()
			);

			remove_filter('woocommerce_product_add_to_cart_url', [$this, 'add_to_cart_url'], 20, 2);

		echo '</p>';

		echo '</div></div>';

	}

	public function add_to_cart_url( $url, $product ){

		if( ! $product->is_type('simple') ){
			return $url;
		}

		if( $product->is_purchasable() && $product->is_in_stock() ){
			return remove_query_arg(
				'added-to-cart',
				add_query_arg( 'add-to-cart', $product->get_id(), $product->get_permalink() )
			);
		}

		return $product->get_permalink();
	}


	protected function render() {

		reycore_assets()->add_styles($this->get_style_name());
		reycore_assets()->add_scripts( $this->rey_get_script_depends() );

		$this->_settings = $this->get_settings_for_display();

		$this->render_start();

		$this->render_link_start();
		$this->render_hotspot();
		$this->render_link_end();

		if( '' === $this->_settings['panel_disable'] ){
			$this->render_the_panel_text();
			$this->render_the_panel_product();
		}

		$this->render_end();
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
