<?php
namespace ReyCore\Modules\AtcButtonIcon;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Base extends \ReyCore\Modules\ModuleBase {

	private $settings = [];

	public function __construct()
	{
		parent::__construct();

		add_action( 'init', [$this, 'init'] );
	}

	public function init()
	{
		add_filter('reycore/woocommerce/loop/add_to_cart/content', [$this, 'add_icon__catalog'], 10, 2);
		add_filter('reycore/woocommerce/single_product/add_to_cart_button/variation', [$this,'add_icon__product_page'], 20, 3);
		add_filter('reycore/woocommerce/single_product/add_to_cart_button/simple', [$this, 'add_icon__product_page'], 20, 3);
		add_action( 'reycore/customizer/control=loop_atc__text', [ $this, 'add_customizer_loop_options' ], 10, 2 );
		add_action( 'reycore/customizer/control=single_atc__text', [ $this, 'add_customizer_pdp_options' ], 10, 2 );
	}

	public function add_icon__catalog($html, $product) {
		$icon = ($cart_icon = get_theme_mod('loop_atc__icon', '')) ? reycore__get_svg_icon([ 'id'=> $cart_icon ]) : '';

		if( ! $icon ){
			return $html;
		}

		return apply_filters('reycore/woocommerce/loop/add_to_cart/icon', $icon) . $html;
	}

	public function add_icon__product_page($html, $product, $text) {

		$icon = ($cart_icon = get_theme_mod('single_atc__icon', '')) ? reycore__get_svg_icon([ 'id'=> $cart_icon ]) : '';

		if( ! $icon ){
			return $html;
		}

		$icon = apply_filters('reycore/woocommerce/pdp/add_to_cart/icon', $icon);

		$search = '<span class="single_add_to_cart_button-text">';
		return str_replace($search, $icon . $search, $html);
	}

	public static function icon_choices(){
		return [
			''        => esc_html__( 'No Icon', 'rey-core' ),
			'bag'     => esc_html__( 'Shopping Bag', 'rey-core' ),
			'bag2'    => esc_html__( 'Shopping Bag 2', 'rey-core' ),
			'bag3'    => esc_html__( 'Shopping Bag 3', 'rey-core' ),
			'basket'  => esc_html__( 'Shopping Basket', 'rey-core' ),
			'basket2' => esc_html__( 'Shopping Basket 2', 'rey-core' ),
			'cart'    => esc_html__( 'Shopping Cart', 'rey-core' ),
			'cart2'   => esc_html__( 'Shopping Cart 2', 'rey-core' ),
			'cart3'   => esc_html__( 'Shopping Cart 3', 'rey-core' ),
			'plus'    => esc_html__( 'Plus Icon', 'rey-core' ),
		];
	}

	public function add_customizer_pdp_options($control_args, $section){

		$section->add_control( [
			'type'     => 'select',
			'settings' => 'single_atc__icon',
			'label'    => esc_html_x( 'Button Icon', 'Customizer control label', 'rey-core' ),
			'default'  => '',
			'choices'  => self::icon_choices(),
		] );

	}

	public function add_customizer_loop_options($control_args, $section){

		$section->add_control( [
			'type' => 'select',
			'settings' => 'loop_atc__icon',
			'label' => esc_html_x('Button Icon', 'Customizer control label', 'rey-core'),
			'default' => '',
			'choices'  => self::icon_choices(),
		]);

	}

	public static function __config(){
		return [
			'id' => basename(__DIR__),
			'title' => esc_html_x('Icon in Add To Cart Button', 'Module name', 'rey-core'),
			'description' => esc_html_x('Adds new controls for Add to Cart buttons to display an icon inside them.', 'Module description', 'rey-core'),
			'icon'        => '',
			'categories'  => ['woocommerce'],
			'keywords'    => ['Product Catalog', 'Product Page'],
			'video' => true,
		];
	}

	public function module_in_use(){
		return (bool) (get_theme_mod('loop_atc__icon', '') || get_theme_mod('single_atc__icon', ''));
	}

}
