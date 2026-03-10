<?php
namespace ReyCore\Modules\BuyNowButton;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	private $settings = [];

	const ASSET_HANDLE = 'reycore-buy-now-button';

	public static $replace_atc = false;

	public function __construct()
	{

		parent::__construct();

		add_action( 'init', [$this, 'init'] );

		add_action( 'wc_ajax_reycore_buy_now', [ $this, 'ajax__buy_now' ] );
		add_action( 'reycore/acf/fields', [$this, 'add_acf_fields']);
		add_action( 'reycore/customizer/section=woo-product-page-summary-components/marker=atc', [ $this, 'add_customizer_options' ] );
		add_action( 'reycore/templates/register_widgets', [$this, 'register_widgets']);

	}

	public function register_widgets($widgets_manager){
		$widgets_manager->register( new Element );
	}

	public function init()
	{

		// can run independently of the customizer option
		add_action( 'reycore/elementor/pdp-add-to-cart/render', [ $this, 'render_in_custom_templates_atc' ] );
		add_action( 'reycore/elementor/pdp-buy-now/render', [ $this, 'display' ] );
		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);

		if( ! $this->is_enabled() ){
			return;
		}

		add_filter( 'woocommerce_add_to_cart_product_id', [$this, 'cancel_regular_atc'] );

		if( self::$replace_atc = get_theme_mod('buynow_pdp__replace_atc', false) ){
			add_filter('reycore/woocommerce/single_product/add_to_cart_button/simple', '__return_empty_string');
			add_filter('reycore/woocommerce/single_product/add_to_cart_button/variation', '__return_empty_string');
		}

		new CompatStickyAtc();

		$this->hook_into_position();
	}

	public function add_acf_fields( $acf_fields ){
		new AcfFields($acf_fields);
	}

	public function hook_into_position(){

		$position = get_theme_mod('buynow_pdp__position', 'inline');

		$hooks = [
			'before' => [
				'hook' => 'woocommerce_before_add_to_cart_form',
				'priority' => 10
			],
			'before_button' => [
				'hook' => 'woocommerce_before_add_to_cart_button',
				'priority' => 0
			],
			'inline' => [
				'hook' => 'woocommerce_after_add_to_cart_button',
				'priority' => 0
			],
			'after' => [
				'hook' => 'reycore/woocommerce/single/after_add_to_cart_form',
				'priority' => 0
			],
		];

		add_action( $hooks[$position]['hook'], [$this, 'display'], $hooks[$position]['priority'] );

	}

	public function maybe_render(){

		if( ! apply_filters( 'reycore/woocommerce/pdp/render/buy_now', true ) ){
			return;
		}

		return true;
	}

	public function display(){

		if( ! $this->maybe_render() ){
			return;
		}

		$this->settings = apply_filters('reycore/module/buy_now', [
			'exclude_product_types' => [
				'external',
				'grouped',
			]
		]);

		$product = wc_get_product();

		if ( ! ( $product && $id = $product->get_id() ) ) {
			return;
		}

		if( in_array($product->get_type(), $this->settings['exclude_product_types'], true) ){
			return;
		}

		$classes = $text_class = $wrapper_class = [];

		$button_text = esc_html__('BUY NOW', 'rey-core');

		if( $custom_button_text = get_theme_mod('buynow_pdp__btn_text', '') ){
			$button_text = $custom_button_text;
		}

		$button_content = self::get_icon();

		if( ! $button_text ){
			$classes[] = '--no-text';
		}

		if( $product->get_type() === 'variable' ){
			$classes[] = '--disabled';
		}

		if( self::$replace_atc ){
			$classes[] = '--replace-atc';
		}

		$attributes = [];

		if( ($btn_style = get_theme_mod('buynow_pdp__btn_style', 'btn-secondary')) && $btn_style !== 'none' ){
			reycore_assets()->add_styles('rey-buttons');
			$classes['btn_style'] = 'btn ' . $btn_style;
			if( in_array($btn_style, ['btn-line', 'btn-line-active'], true) ){
				$text_class['text_style'] = 'btn ' . $btn_style;
				$classes['btn_style'] = 'btn --btn-text';
			}
		}

		$text_visibility = get_theme_mod('buynow_pdp__btn_text_visibility', 'show_desktop');

		if( get_theme_mod('buynow_pdp__stretch', false)
			&& in_array($btn_style, ['btn-primary', 'btn-primary-outline', 'btn-secondary'])
			&& in_array(get_theme_mod('buynow_pdp__position', 'inline'), ['before', 'before_button', 'after'])
		){
			$wrapper_class['btn_stretch'] = 'btn--full';
		}

		if( $text_visibility && $button_text ){

			if( $text_visibility === 'show_desktop' && $button_content ){
				$text_class[] = '--dnone-sm --dnone-md';
			}

			$button_content .= sprintf('<span class="rey-buyNowBtn-text %s">%s</span>', esc_attr(implode(' ', $text_class)), $button_text);
		}

		$attributes['data-disabled-text'] = esc_html__('Please select some product options before proceeding.', 'rey-core');
		$attributes['data-id'] = $id;

		$button_content .= '<span class="rey-lineLoader"></span>';

		reycore__get_template_part('template-parts/woocommerce/buy-now-button', false, false, [
			'classes'         => $classes,
			'text'            => $button_text,
			'content'         => $button_content,
			'attributes'      => $attributes,
			'wrapper_classes' => $wrapper_class,
		]);

		self::load_scripts();

	}

	public function render_in_custom_templates_atc( $element ){

		if( ! $this->is_enabled() ){
			return;
		}

		$this->hook_into_position();
	}

	public static function get_icon(){

		if( ! ( $icon = get_theme_mod('buynow_pdp__icon_type', 'bolt') ) ){
			return '';
		}

		if(
			($svg = \ReyCore\Plugin::instance()->svg) &&
			$icon === 'custom' &&
			($custom_icon = get_theme_mod('buynow_pdp__icon_custom', '')) &&
			($svg_code = $svg->get_inline_svg( [ 'id' => $custom_icon, 'class' => 'rey-buyNowBtn-icon' ] )) ){
			return $svg_code;
		}

		return reycore__get_svg_icon([
			'id' => $icon,
			'class' => 'rey-buyNowBtn-icon'
		]);
	}

	public static function load_scripts(){
		reycore_assets()->add_scripts(self::ASSET_HANDLE);
		reycore_assets()->add_styles(self::ASSET_HANDLE);
	}

	public function register_assets($assets){

		$assets->register_asset('styles', [
			self::ASSET_HANDLE => [
				'src'      => self::get_path( basename( __DIR__ ) ) . '/style.css',
				'deps'     => [],
				'version'  => REY_CORE_VERSION,
				'priority' => 'low',
			]
		]);

		$assets->register_asset('scripts', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/script.js',
				'deps'    => ['reycore-woocommerce'],
				'version'   => REY_CORE_VERSION,
			]
		]);

	}

	/**
	 * When "Empty cart before redirecting" is disabled, this will prevent the regular ATC from adding the product to cart (double add to cart issue)
	 *
	 * @param int $product_id
	 * @return int
	 */
	function cancel_regular_atc($product_id){
		if( isset($_REQUEST['wc-ajax']) && $_REQUEST['wc-ajax'] === 'reycore_buy_now' ){
			if( ! get_theme_mod('buynow_pdp__empty_cart', true) ){
				return null;
			}
		}
		return $product_id;
	}

	public function ajax__buy_now(){

		if ( ! check_ajax_referer( 'rey_nonce', 'security', false ) ) {
			wp_send_json_error( esc_html__('Invalid security nonce!', 'rey-core') );
		}

		if ( ! (isset( $_REQUEST['product_id'] ) && $product_id = absint( $_REQUEST['product_id'] )) ) {
			wp_send_json_error('No product ID specified.');
		}

		// Return if cart object is not initialized.
		if ( ! is_object( WC()->cart ) ) {
			wp_send_json_error('Cannot retrieve Cart object.');
		}

		if( get_theme_mod('buynow_pdp__empty_cart', true) ){
			WC()->cart->empty_cart();
		}

		$quantity = isset($_REQUEST['quantity']) ? absint( $_REQUEST['quantity'] ) : 1;

		if ( isset( $_REQUEST['variation_id'] ) && $variation_id = absint( $_REQUEST['variation_id'] ) ) {

			$variation = [];

			if ( isset( $_REQUEST['attr'] ) ) {
				foreach ($_REQUEST['attr'] as $attribute) {
					if( isset($_REQUEST[$attribute]) && '' !== $_REQUEST[$attribute] ){
						$variation[$attribute] = reycore__clean( $_REQUEST[$attribute] );
					}
				}
			}

			$added = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation );
		}
		else{
			$added = WC()->cart->add_to_cart( $product_id, $quantity );
		}

		if( ! $added ){

			$notices = '';

			if( isset(WC()->session) && WC()->session && is_object(WC()->session) ){
				ob_start();
				echo '<p>Failed to add to cart.</p>';
				woocommerce_output_all_notices();
				$notices = ob_get_clean();
			}

			wp_send_json_error($notices);
		}

		wp_send_json_success([
			'checkout_url' => wc_get_checkout_url()
		]);

	}

	public function add_customizer_options($section){

		$section->start_controls_accordion([
			'label'  => esc_html__( 'Buy Now Button', 'rey-core' ),
		]);

		$section->add_title( '', [
			'description' => esc_html__('Settings for buy now button.', 'rey-core'),
			'separator' => 'none',
		]);

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'buynow_pdp__enable',
			'label'       => esc_html__( 'Enable button', 'rey-core' ),
			'default'     => false,
		] );

		$section->add_control( [
			'type'        => 'text',
			'settings'    => 'buynow_pdp__btn_text',
			'label'       => esc_html__( 'Button text', 'rey-core' ),
			'default'     => '',
			'input_attrs'     => [
				'placeholder' => esc_html__('BUY NOW', 'rey-core'),
			],
			'active_callback' => [
				[
					'setting'  => 'buynow_pdp__enable',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$section->add_control( [
			'type'        => 'select',
			'settings'    => 'buynow_pdp__btn_text_visibility',
			'label'       => esc_html__( 'Text visibility', 'rey-core' ),
			'default'     => 'show_desktop',
			'choices' => [
				'' => esc_html__('Hide', 'rey-core'),
				'show' => esc_html__('Show', 'rey-core'),
				'show_desktop' => esc_html__('Show text on desktop only', 'rey-core'),
			],
			'active_callback' => [
				[
					'setting'  => 'buynow_pdp__enable',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$section->add_control( [
			'type'        => 'select',
			'settings'    => 'buynow_pdp__position',
			'label'       => esc_html__( 'Button Position', 'rey-core' ),
			'default'     => 'inline',
			'choices'     => [
				'inline' => esc_html__( 'Inline with ATC. button', 'rey-core' ),
				'before' => esc_html__( 'Before ATC. block', 'rey-core' ),
				'before_button' => esc_html__( 'Before ATC. button', 'rey-core' ),
				'after' => esc_html__( 'After ATC. button', 'rey-core' ),
			],
			'active_callback' => [
				[
					'setting'  => 'buynow_pdp__enable',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$section->add_control( [
			'type'        => 'select',
			'settings'    => 'buynow_pdp__btn_style',
			'label'       => esc_html__( 'Button Style', 'rey-core' ),
			'default'     => 'btn-secondary',
			'choices'     => [
				'none' => esc_html__( 'None', 'rey-core' ),
				'btn-line' => esc_html__( 'Underlined on hover', 'rey-core' ),
				'btn-line-active' => esc_html__( 'Underlined', 'rey-core' ),
				'btn-primary' => esc_html__( 'Regular', 'rey-core' ),
				'btn-primary-outline' => esc_html__( 'Regular outline', 'rey-core' ),
				'btn-secondary' => esc_html__( 'Secondary', 'rey-core' ),
				'btn-secondary-outline' => esc_html__( 'Secondary Outline', 'rey-core' ),
			],
			'active_callback' => [
				[
					'setting'  => 'buynow_pdp__enable',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'buynow_pdp__stretch',
			'label'       => esc_html__( 'Full-Stretch Button', 'rey-core' ),
			'default'     => false,
			'active_callback' => [
				[
					'setting'  => 'buynow_pdp__enable',
					'operator' => '==',
					'value'    => true,
				],
				[
					'setting'  => 'buynow_pdp__position',
					'operator' => 'in',
					'value'    => ['before', 'before_button', 'after'],
				],
				[
					'setting'  => 'buynow_pdp__btn_style',
					'operator' => 'in',
					'value'    => ['btn-primary', 'btn-primary-outline', 'btn-secondary'],
				],
			],
		] );

		$section->add_control( [
			'type'        => 'rey-color',
			'settings'    => 'buynow_pdp__color_bg',
			'label'       => esc_html__( 'Button Background Color', 'rey-core' ),
			'default'     => '',
			'transport'   		=> 'auto',
			'choices'     => [
				'alpha' => true,
			],
			'output'      		=> [
				[
					'element'  		=> '.woocommerce .rey-buyNowBtn-wrapper',
					'property' 		=> '--accent-color',
				]
			],
			'active_callback' => [
				[
					'setting'  => 'buynow_pdp__enable',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$section->add_control( [
			'type'        => 'rey-color',
			'settings'    => 'buynow_pdp__color_text',
			'label'       => esc_html__( 'Button Text Color', 'rey-core' ),
			'default'     => '',
			'transport'   		=> 'auto',
			'choices'     => [
				'alpha' => true,
			],
			'output'      		=> [
				[
					'element'  		=> '.woocommerce .rey-buyNowBtn-wrapper',
					'property' 		=> '--accent-text-color',
				]
			],
			'active_callback' => [
				[
					'setting'  => 'buynow_pdp__enable',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$section->add_control( [
			'type'        => 'rey-color',
			'settings'    => 'buynow_pdp__color_text_hover',
			'label'       => esc_html__( 'Button Hover Text Color', 'rey-core' ),
			'default'     => '',
			'transport'   		=> 'auto',
			'choices'     => [
				'alpha' => true,
			],
			'output'      		=> [
				[
					'element'  		=> '.woocommerce .rey-buyNowBtn-wrapper .btn:hover',
					'property' 		=> 'color',
				]
			],
			'active_callback' => [
				[
					'setting'  => 'buynow_pdp__enable',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$section->add_control( [
			'type'        => 'rey-color',
			'settings'    => 'buynow_pdp__color_bg_hover',
			'label'       => esc_html__( 'Button Hover Background Color', 'rey-core' ),
			'default'     => '',
			'transport'   		=> 'auto',
			'choices'     => [
				'alpha' => true,
			],
			'output'      		=> [
				[
					'element'  		=> '.woocommerce .rey-buyNowBtn-wrapper .btn:hover',
					'property' 		=> 'background-color',
				]
			],
			'active_callback' => [
				[
					'setting'  => 'buynow_pdp__enable',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$section->add_control( [
			'type'        => 'select',
			'settings'    => 'buynow_pdp__icon_type',
			'label'       => esc_html__( 'Choose icon', 'rey-core' ),
			'default'     => 'bolt',
			'choices'     => [
				'' => esc_html__( 'Disabled', 'rey-core' ),
				'bolt' => esc_html__( 'Bolt', 'rey-core' ),
				'custom' => esc_html__( 'Custom', 'rey-core' ),
			],
			'active_callback' => [
				[
					'setting'  => 'buynow_pdp__enable',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$section->add_control( [
			'type'        => 'image',
			'settings'    => 'buynow_pdp__icon_custom',
			'label'       => esc_html__( 'Custom Icon (svg)', 'rey-core' ),
			'default'     => '',
			'choices'     => [
				'save_as' => 'id',
			],
			'active_callback' => [
				[
					'setting'  => 'buynow_pdp__icon_type',
					'operator' => '==',
					'value'    => 'custom',
				],
				[
					'setting'  => 'buynow_pdp__enable',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'buynow_pdp__empty_cart',
			'label'       => esc_html__( 'Empty cart before redirecting', 'rey-core' ),
			'default'     => true,
			'active_callback' => [
				[
					'setting'  => 'buynow_pdp__enable',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'buynow_pdp__replace_atc',
			'label'       => esc_html__( 'Replace Add To Cart button', 'rey-core' ),
			'default'     => false,
			'active_callback' => [
				[
					'setting'  => 'buynow_pdp__enable',
					'operator' => '==',
					'value'    => true,
				],
			],
		] );

		$section->end_controls_accordion();

	}

	public function is_enabled(){
		return reycore__get_option('buynow_pdp__enable', false) && ! reycore_wc__is_catalog();
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Buy Now Button', 'Module name', 'rey-core'),
			'description' => esc_html_x('Adds a button in product page, capable to directly redirect to Checkout, instead of just adding to cart.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => ['Product page'],
			'help'        => reycore__support_url('kb/buy-now-button/'),
			'video' => true,
		];
	}

	public function module_in_use(){
		return $this->is_enabled();
	}

}
