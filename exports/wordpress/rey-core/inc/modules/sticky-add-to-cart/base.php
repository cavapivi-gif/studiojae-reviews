<?php
namespace ReyCore\Modules\StickyAddToCart;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	private $settings = [];

	private $product;

	const ASSET_HANDLE = 'reycore-satc-popup';

	public function __construct()
	{
		add_action('reycore/customizer/section=woo-product-page-components', [$this, 'add_customizer_options'] );
		add_action( 'wp', [$this, 'init'] );
	}

	public function init()
	{
		if( ! $this->is_enabled() ){
			return;
		}

		if( ! is_product() ){
			return;
		}

		$this->product = wc_get_product();

		if( ! $this->product ){
			return;
		}

		if( ! $this->product->is_purchasable() ){
			return;
		}

		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_filter( 'rey/main_script_params', [ $this, 'script_params'] );
		add_action( 'rey/after_site_wrapper', [$this, 'markup']);
		add_action( 'reycore/module/satc/before_markup', [$this, 'rearrange']);
		add_action( 'reycore/module/satc/after_components', [ $this, 'get_navigation' ], 10);
		add_filter('reycore/woocommerce/pdp/render/product_nav', [$this, 'show_nav_in_sticky_bar']);

		$this->settings = apply_filters('reycore/module/sticky_add_to_cart_settings', [
			'price_args'        => [],
			'start_point'       => '',
			'finish_point'      => '',
			'mobile-from-top'   => true,
			'simple_use_scroll' => false,
		]);
	}

	function rearrange(){

		remove_all_actions('woocommerce_after_add_to_cart_button');

		add_filter( 'reycore/woocommerce/pdp/render/before_add_to_cart', '__return_false');
		add_filter( 'reycore/woocommerce/pdp/render/after_add_to_cart', '__return_false');

		add_filter( 'reycore/woocommerce/wrap_quantity', '__return_true');
		add_filter( 'reycore/woocommerce/add_quantity_controls', '__return_true');

		do_action('reycore/woocommerce/quantity/add_to_cart_button_wrap');
	}

	function get_navigation(){
		if( ($pdp = \ReyCore\Plugin::instance()->woocommerce_pdp) && ($c = $pdp->get_component('product_nav')) ){
			$c->render();
		}
	}

	function markup(){

		if( !is_single() ){
			return;
		}

		set_query_var('product_page_sticky_bar', true);

		do_action('reycore/module/satc/before_markup');

		$attributes = [];

		if( $this->settings['finish_point'] !== '' ){
			$attributes['data-finish-point'] = esc_attr($this->settings['finish_point']);
		}

		if( $this->settings['start_point'] !== '' ){
			$attributes['data-start-point'] = esc_attr($this->settings['start_point']);
		}

		if( $this->settings['mobile-from-top'] ){
			$attributes['data-mobile-from-top'] = true;
		}

		add_filter('reycore/module/price_in_atc/should_print_price', '__return_false'); ?>

		<div class="rey-stickyAtc-wrapper" data-lazy-hidden>
			<div class="rey-stickyAtc" <?php echo reycore__implode_html_attributes($attributes); ?>>
				<div <?php wc_product_class( '', $this->product ); ?>>
					<h4 class="rey-stickyAtc-title"><?php echo $this->product->get_title() ?></h4>
					<div class="rey-stickyAtc-price"><?php echo $this->product->get_price_html() ?></div>
					<div class="rey-stickyAtc-cart --<?php echo $this->product->get_type() ?> js-cartForm-wrapper">
						<?php $this->get_add_to_cart(); ?>
					</div>
					<?php do_action('reycore/module/satc/after_components'); ?>
				</div>
			</div>
		</div>
		<?php

		remove_filter('reycore/module/price_in_atc/should_print_price', '__return_false');

		set_query_var('product_page_sticky_bar', false);

	}

	function get_add_to_cart(){

		$post = get_post( $this->product->get_id() );

		setup_postdata( $post );

		switch($this->product->get_type()):
			default:
			case "simple":
				if( $this->settings['simple_use_scroll'] ){
					$this->get_product_variable();
				}
				else {
					woocommerce_simple_add_to_cart();
				}
				break;
			case "variable":
				$this->get_product_variable();
				break;
			case "external":
				woocommerce_external_add_to_cart();
				break;
			case "grouped":
				$this->get_product_grouped();
				break;
		endswitch;

		do_action('reycore/module/satc/after_atc');

		wp_reset_postdata();

	}

	function get_price(){

		if( 'variable' === $this->product->get_type() ){
			$_price = $this->product->get_variation_price( 'min' );
		}
		else {
			$_price = wc_get_price_to_display( $this->product );
		}

		return wc_price( $_price, $this->settings['price_args'] );
	}

	function get_open_button( $label ){
		reycore_assets()->add_styles('rey-buttons');
		return sprintf('<button class="rey-satc-openBtn btn btn-primary"><span>%1$s</span><span class="satc-price"><span>%4$s</span>%3$s</span> %2$s</button>',
			$label,
			reycore__get_svg_icon(['id'=>'arrow']),
			$this->get_price(),
			_x('From', 'From text in the Sticky add to cart bar, for price in button.', 'rey-core')
		);
	}

	function get_product_variable(){
		echo $this->get_open_button( _x('SELECT OPTIONS', 'Select Options text in the Sticky add to cart bar, for variable products.', 'rey-core') );
	}

	function load_variations_form_template(){

		if( ! apply_filters('reycore/module/satc/default_template', true) ){
			return;
		}

		// Enqueue variation scripts.
		reycore_assets()->add_scripts(['wc-add-to-cart-variation']);

		// Get Available variations?
		$get_variations = count( $this->product->get_children() ) <= apply_filters( 'woocommerce_ajax_variation_threshold', 30, $this->product );

		// Load the template.
		wc_get_template(
			'single-product/add-to-cart/variable.php',
			array(
				'available_variations' => $get_variations ? $this->product->get_available_variations() : false,
				'attributes'           => $this->product->get_variation_attributes(),
				'selected_attributes'  => $this->product->get_default_attributes(),
			)
		);

	}

	function get_product_grouped(){
		echo $this->get_open_button( _x('SELECT PRODUCTS', 'Select Products text in the Sticky add to cart bar, for grouped products.', 'rey-core') );
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

	public function enqueue_scripts(){
		reycore_assets()->add_styles(['rey-overlay', self::ASSET_HANDLE]);
		reycore_assets()->add_scripts([self::ASSET_HANDLE]);
	}

	public function script_params($params)
	{
		if( $this->product->is_type('simple') ){
			$params['sticky_add_to_cart__btn_text'] = sprintf('<span class="satc-price">%s</span>', $this->get_price() );
		}

		return $params;
	}

	function add_customizer_options( $section ){

		$section->add_title( esc_html__('Sticky - Add to cart bar', 'rey-core'), [
				'description' => esc_html__( 'This will show a sticky bar at the bottom of the page, which is shown when scrolling past the summary\'s Add to cart button.', 'rey-core' )
			]
		);

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'product_page_sticky_add_to_cart',
			'label'       => esc_html__( 'Enable Sticky Bar', 'rey-core' ),
			'default'     => false,
		] );

		$section->start_controls_group( [
			'group_id' => 'sticky_bar_group',
			'label'    => esc_html__( 'Extra Options', 'rey-core' ),
			'active_callback' => [
				[
					'setting'  => 'product_page_sticky_add_to_cart',
					'operator' => '==',
					'value'    => true,
				],
			],
		]);

			$section->add_control( [
				'type'        => 'toggle',
				'settings'    => 'product_page_sticky_add_to_cart__arrows',
				'label'       => esc_html__( 'Display Navigation Arrows', 'rey-core' ),
				'default'     => true,
			] );

			$section->add_section_marker('sticky_atc_components');


		$section->end_controls_group();

	}

	public function show_nav_in_sticky_bar( $status ){

		if( ! get_theme_mod('product_page_sticky_add_to_cart__arrows', true) ){
			return;
		}

		return $status;
	}

	public function is_enabled() {
		return get_theme_mod('product_page_sticky_add_to_cart', false);
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Sticky Add to cart bar in product page', 'Module name', 'rey-core'),
			'description' => esc_html_x('Always maintain an easy and reachable way for the customer to add product to the cart.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => ['product page'],
			'video' => true,
		];
	}

	public function module_in_use(){
		return $this->is_enabled();
	}
}
