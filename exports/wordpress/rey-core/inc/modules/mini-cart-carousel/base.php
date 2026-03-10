<?php
namespace ReyCore\Modules\MiniCartCarousel;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	const ASSET_HANDLE = 'reycore-module-minicart-carousel';

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
		add_action( 'reycore/woocommerce/minicart/cart_panel', [$this, 'render_markup'], 10);
		add_filter( 'woocommerce_add_to_cart_fragments', [ $this, 'cross_sells_data_fragment' ] );
		add_action( 'woocommerce_before_mini_cart', [$this, 'add_cart_assets']);
		add_filter( 'reycore/woocommerce/minicarts/supports_products', '__return_true' );
	}

	public function set_settings(){

		$this->settings = apply_filters('reycore/woocommerce/cartpanel/cross_sells_carousel', [
			'enable' => $this->is_enabled(),
			'limit' => get_theme_mod('header_cart__cross_sells_carousel_limit', 10),
			'title' => ($crs_title = get_theme_mod('header_cart__cross_sells_carousel_title', '')) ? $crs_title : __( 'You may also like&hellip;', 'woocommerce' ),
			'mobile' => get_theme_mod('header_cart__cross_sells_carousel_mobile', true),
			'autoplay' => false,
			'autoplay_duration' => 3000,
			'type' => get_theme_mod('header_cart__cross_sells_carousel_type', 'cs'),
			'related_limit' => 2,
			'shuffle' => true,
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

	public function add_cart_assets(){

		if( WC()->cart->is_empty() && ! wp_doing_ajax() ){
			return;
		}

		reycore_assets()->add_styles( [ 'rey-wc-general', 'rey-wc-general-deferred', 'rey-splide', self::ASSET_HANDLE] );
		reycore_assets()->add_scripts( [ 'rey-tmpl', 'splidejs', 'rey-splide', self::ASSET_HANDLE ] );

	}

	public function get_related_products(){

		$related_products = [];

		foreach ( WC()->cart->get_cart_contents() as $cart_item ) {
			$related_products = array_merge($related_products, wc_get_related_products( $cart_item['product_id'], $this->settings['related_limit'] ));
		}

		return array_unique($related_products);
	}

	/**
	 * Append special Cross-sells fragment into the Cart's Fragments
	 *
	 * @param array $fragments
	 * @return array
	 */
	public function cross_sells_data_fragment( $fragments ){

		if( ! $this->settings ){
			$this->set_settings();
		}

		if( ! ($minicart_tag = reycore_wc__get_tag('minicart')) ){
			return $fragments;
		}

		$_ids = [];

		switch($this->settings['type']):

			case "cs":
				$_ids = WC()->cart->get_cross_sells();
			break;

			case "combined":
				$_ids = array_unique(array_merge( WC()->cart->get_cross_sells(), $this->get_related_products() ));
			break;

			case "empty_combined":
				if( $_products = WC()->cart->get_cross_sells() ){
					$_ids = $_products;
				}
				else {
					$_ids = array_unique(array_merge( $_ids, $this->get_related_products() ));
				}
			break;
		endswitch;

		if( empty($_ids) ){
			return $fragments;
		}

		if( ! is_array($_ids) ){
			return $fragments;
		}

		if( $this->settings['shuffle'] ){
			shuffle($_ids);
		}

		$_ids = array_unique( $this->settings['limit'] > 0 ? array_slice( $_ids, 0, $this->settings['limit'] ) : $_ids );

		$cs_fragment = $minicart_tag->prepare_products_data_fragment($_ids);

		if( ! empty($cs_fragment) ){
			$fragments['_crosssells_'] = $cs_fragment;
		}

		return $fragments;
	}

	public function render_markup(){

		if( ! $this->settings ){
			$this->set_settings();
		}

		if( ! $this->settings['enable'] ){
			return;
		}

		if( ! ($minicart_tag = reycore_wc__get_tag('minicart')) ){
			return;
		}

		$class = 'splide rey-crossSells-carousel --loading';

		if( $this->settings['mobile'] ){
			$class .= ' --dnone-lg --dnone-md';
		}

		$slider_config = wp_json_encode([
			'autoplay' => $this->settings['autoplay'],
			'autoplaySpeed' => $this->settings['autoplay_duration'],
		]); ?>

		<script type="text/html" id="tmpl-reyCrossSellsCarousel">

		<# var items = data.items; #>
		<# if( items.length ){ #>
		<?php
			printf('<div class="%1$s" data-slider-config=\'%2$s\'>',
				$class,
				$slider_config
			);
		?>

			<?php if( $title = $this->settings['title'] ): ?>
			<h3 class="rey-crossSells-carousel-title">
				<span class="__text"><?php echo $title ?></span>
				<!-- <span class="__hidebtn --dnone-lg --dnone-md">
					<?php echo reycore__get_svg_icon(['id'=>'arrow']) ?>
				</span> -->
			</h3>
			<?php endif; ?>

			<div class="splide__track">
				<div class="rey-crossSells-itemsWrapper splide__list">
					<?php $minicart_tag->render_cross_sells(['class' => 'splide__slide']) ?>
				</div>
			</div>

			</div><!-- end -->
		<# } #>

		</script><?php

	}

	public function add_customizer_options( $section ){

		$section->add_title( esc_html__('CAROUSEL', 'rey-core') );

		$section->add_control( [
			'type'        => 'toggle',
			'settings'    => 'header_cart__cross_sells_carousel',
			'label'       => esc_html__( 'Enable Carousel', 'rey-core' ),
			'description' => esc_html__( 'This will display a carousel containing all the cross-sells products linked to the products in the cart.', 'rey-core' ),
			'default'     => true,
		] );

		$section->start_controls_group( [
			'label'    => esc_html__( 'Options', 'rey-core' ),
			'active_callback' => [
				[
					'setting'  => 'header_cart__cross_sells_carousel',
					'operator' => '==',
					'value'    => true,
				],
			],
		]);

			$section->add_control( [
				'type'        => 'text',
				'settings'    => 'header_cart__cross_sells_carousel_title',
				'label'       => esc_html__( 'Title', 'rey-core' ),
				'default'     => '',
				'input_attrs'     => [
					'placeholder' => __( 'You may also like&hellip;', 'woocommerce' ),
				],
				'active_callback' => [
					[
						'setting'  => 'header_cart__cross_sells_carousel',
						'operator' => '==',
						'value'    => true,
					],
				],
			] );

			$section->add_control( [
				'type'        => 'rey-number',
				'settings'    => 'header_cart__cross_sells_carousel_limit',
				'label'       => esc_html__( 'Products Limit', 'rey-core' ),
				'default'     => 10,
				'choices'     => [
					'min'  => 1,
					'max'  => 20,
					'step' => 1,
				],
				'active_callback' => [
					[
						'setting'  => 'header_cart__cross_sells_carousel',
						'operator' => '==',
						'value'    => true,
					],
				],
			] );

			$section->add_control( [
				'type'        => 'select',
				'settings'    => 'header_cart__cross_sells_carousel_type',
				'label'       => esc_html__( 'Product types', 'rey-core' ),
				'default'     => 'cs',
				'choices' => [
					'cs' => esc_html__('Cross-sells', 'rey-core'),
					'combined' => esc_html__('Cross-sells & Related products', 'rey-core'),
					'empty_combined' => esc_html__('Related products if empty Cross-sells', 'rey-core'),
				],
				'active_callback' => [
					[
						'setting'  => 'header_cart__cross_sells_carousel',
						'operator' => '==',
						'value'    => true,
					],
				],
			] );

			$section->add_control( [
				'type'        => 'toggle',
				'settings'    => 'header_cart__cross_sells_carousel_mobile',
				'label'       => esc_html__( 'Show only on Mobile', 'rey-core' ),
				'default'     => true,
				'active_callback' => [
					[
						'setting'  => 'header_cart__cross_sells_carousel',
						'operator' => '==',
						'value'    => true,
					],
				],
			] );

		$section->end_controls_group();

	}

	public function is_enabled() {
		return get_theme_mod('header_cart__cross_sells_carousel', true);
	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Mini-Cart Carousel with Cross-Sells', 'Module name', 'rey-core'),
			'description' => esc_html_x('Shows a carousel of products inside the mini-cart, based on added to cart product\'s cross-sell products.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => [''],
			'help'        => reycore__support_url('kb/shopping-cart-popup-side-panel/#display-a-carousel-of-cross-sells-products'),
			'video' => true,
		];
	}

	public function module_in_use(){
		return $this->is_enabled();
	}
}
