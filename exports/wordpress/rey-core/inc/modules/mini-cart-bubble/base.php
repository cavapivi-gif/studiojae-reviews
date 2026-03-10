<?php
namespace ReyCore\Modules\MiniCartBubble;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	const ASSET_HANDLE = 'reycore-module-minicart-bubble';

	public $settings;

	public function __construct()
	{
		add_action( 'reycore/woocommerce/init', [$this, 'init']);
	}

	public function init() {

		add_action( 'reycore/customizer/section=header-mini-cart/marker=before_crosssells_end', [$this, 'add_customizer_options']);

		if( ! $this->is_enabled() ){
			return;
		}

		add_action( 'reycore/assets/register_scripts', [$this, 'register_assets']);
		add_filter( 'theme_mod_header_cart__cross_sells_bubble', [ $this, 'disable_header_cart__cross_sells_bubble' ] );
		add_action( 'reycore/woocommerce/minicart/cart_panel', [$this, 'render_markup'], 10);
		add_filter( 'woocommerce_add_to_cart_fragments', [ $this, 'cross_sells_data_fragment' ] );
		add_action( 'woocommerce_before_mini_cart', [$this, 'add_cart_assets']);
		add_filter( 'reycore/woocommerce/minicarts/supports_products', '__return_true' );
	}

	public function set_settings(){

		$this->settings = apply_filters('reycore/woocommerce/cartpanel/cross_sells_bubble', [
			'enable' => $this->is_enabled(),
			'limit' => get_theme_mod('header_cart__cross_sells_bubble_limit', 3),
			'rating' => false,
			'title' => ($bubble_title = get_theme_mod('header_cart__cross_sells_bubble_title', '')) ? $bubble_title : __( 'You may also like&hellip;', 'woocommerce' ),
			'add_to_cart' => esc_html__( 'Continue shopping', 'woocommerce' ),
			'type' => get_theme_mod('header_cart__cross_sells_bubble_type', 'cs'),
		]);

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
				'deps'    => ['reycore-woocommerce', 'rey-tmpl'],
				'version'   => REY_CORE_VERSION,
			]
		]);

	}

	public function can_add(){

		if( isset($_REQUEST['wc-ajax']) && 'add_to_cart' === reycore__clean($_REQUEST['wc-ajax']) ){
			if( ! (isset($_REQUEST['product_id']) && $product_id = absint($_REQUEST['product_id'])) ) {
				return false;
			}
		}

		else if( ! (isset($_REQUEST['add-to-cart']) && $product_id = absint($_REQUEST['add-to-cart'])) ){
			return false;
		}

		if( ! $this->settings ){
			$this->set_settings();
		}

		return $product_id;
	}

	/**
	 * Allow adding to cross-sells fragments
	 *
	 * @param bool $status
	 * @return bool
	 */
	public function cross_sells_data_fragment($fragments){

		if( ! ($product_id = $this->can_add()) ){
			return $fragments;
		}

		if( ! ($product = wc_get_product($product_id)) ){
			return $fragments;
		}

		$_ids = [];

		if( $type = $this->settings['type'] ){

			switch($type):

				case "cs":
					$_ids = $product->get_cross_sell_ids();
				break;

				case "related":
					$_ids = wc_get_related_products( $product_id, $this->settings['limit'] );
				break;

				case "combined":

					if( $_products = $product->get_cross_sell_ids() ){
						$_ids = $_products;
					}
					else {
						$_ids = wc_get_related_products( $product_id, $this->settings['limit'] );
					}

				break;

			endswitch;

		}

		if( empty($_ids) ){
			return $fragments;
		}

		if( ! is_array($_ids) ){
			return $fragments;
		}

		$exclude_items = [];

		foreach ( WC()->cart->get_cart() as $item ) {
			if ( $item['data'] && $pid = $item['data']->get_id() ) {
				$exclude_items[] = $pid;
			}
		}

		$_ids = array_diff($_ids, $exclude_items);
		$_ids = array_unique( $this->settings['limit'] > 0 ? array_slice( $_ids, 0, $this->settings['limit'] ) : $_ids );

		if( ! ($minicart_tag = reycore_wc__get_tag('minicart')) ){
			return $fragments;
		}

		$cs_fragment = $minicart_tag->prepare_products_data_fragment($_ids);

		if( ! empty($cs_fragment) ){
			$fragments['_crosssells_bubble_'] = $cs_fragment;
		}

		return $fragments;
	}

	public function add_cart_assets(){

		if( WC()->cart->is_empty() && ! wp_doing_ajax() ){
			return;
		}

		reycore_assets()->add_styles( self::ASSET_HANDLE );
		reycore_assets()->add_scripts( [ 'rey-tmpl', self::ASSET_HANDLE] );

	}

	public function render_markup(){

		if( ! $this->settings ){
			$this->set_settings();
		}

		if( ! ($minicart_tag = reycore_wc__get_tag('minicart')) ){
			return;
		}

		reycore_assets()->add_styles('rey-buttons'); ?>

		<script type="text/html" id="tmpl-reyCrossSellsBubble">

		<# var items = data.items; #>
		<# if( items.length ){ #>
		<div class="rey-crossSells-bubble --loading">

			<?php if( $title = $this->settings['title'] ): ?>
			<div class="rey-crossSells-bubble-title"><?php echo $title ?></div>
			<?php endif; ?>

			<?php $minicart_tag->render_cross_sells() ?>

			<div><a class="rey-crossSells-bubble-close btn btn-primary-outline btn--block" href="#"><?php echo $this->settings['add_to_cart'] ?></a></div>

		</div>
		<# } #>

		</script><?php

	}


	/**
	 * This feature is exclusive to desktops.
	 * No reason to load it on mobiles.
	 */
	function disable_header_cart__cross_sells_bubble($mod){

		if( reycore__is_mobile() && reycore__supports_mobile_caching() ){
			return false;
		}

		return $mod;
	}

	public function add_customizer_options( $section ){

		$section->add_title(  esc_html__('SIDE BUBBLE', 'rey-core') );

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'header_cart__cross_sells_bubble',
			'label'       => esc_html__( 'Enable', 'rey-core' ),
			'description' => esc_html__( 'After a product has been added to cart, a bubble with recommended products items will be displayed.', 'rey-core' ),
			'default'     => true,
		] );

		$section->start_controls_group( [
			'label'    => esc_html__( 'Options', 'rey-core' ),
			'active_callback' => [
				[
					'setting'  => 'header_cart__cross_sells_bubble',
					'operator' => '==',
					'value'    => true,
				],
			],
		]);

			$section->add_control( [
				'type'        => 'select',
				'settings'    => 'header_cart__cross_sells_bubble_type',
				'label'       => esc_html__( 'Product types', 'rey-core' ),
				'default'     => 'cs',
				'choices' => [
					'cs' => esc_html__('Cross-sells', 'rey-core'),
					'related' => esc_html__('Related Products', 'rey-core'),
					'combined' => esc_html__('Related Products if empty Cross-sells', 'rey-core'),
				],
				'active_callback' => [
					[
						'setting'  => 'header_cart__cross_sells_bubble',
						'operator' => '==',
						'value'    => true,
					],
				],
			] );

			$section->add_control( [
				'type'        => 'text',
				'settings'    => 'header_cart__cross_sells_bubble_title',
				'label'       => esc_html__( 'Title', 'rey-core' ),
				'default'     => '',
				'input_attrs'     => [
					'placeholder' => __( 'You may also like&hellip;', 'woocommerce' ),
				],
				'active_callback' => [
					[
						'setting'  => 'header_cart__cross_sells_bubble',
						'operator' => '==',
						'value'    => true,
					],
				],
			] );

			$section->add_control( [
				'type'        => 'rey-number',
				'settings'    => 'header_cart__cross_sells_bubble_limit',
				'label'       => esc_html__( 'Products Limit', 'rey-core' ),
				'default'     => 3,
				'choices'     => [
					'min'  => 1,
					'max'  => 20,
					'step' => 1,
				],
				'active_callback' => [
					[
						'setting'  => 'header_cart__cross_sells_bubble',
						'operator' => '==',
						'value'    => true,
					],
				],
			] );

		$section->end_controls_group();
	}

	public function is_enabled() {
		return get_theme_mod('header_cart__cross_sells_bubble', true);
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Mini-Cart Bubble popup with Cross-Sells', 'Module name', 'rey-core'),
			'description' => esc_html_x('Shows a popup filled with Cross-sell products, right next to the mini-cart, after a product was added to cart.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => [''],
			'help'        => reycore__support_url('kb/shopping-cart-popup-side-panel/#display-a-side-popover-with-cross-sells-products'),
			'video' => true,
		];
	}

	public function module_in_use(){
		return $this->is_enabled();
	}
}
