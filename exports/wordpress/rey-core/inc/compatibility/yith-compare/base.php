<?php
namespace ReyCore\Compatibility\YithCompare;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{
	private $settings = [];

	const ASSET_HANDLE = 'reycore-yithcompare-styles';

	public function __construct()
	{
		add_action( 'init', [ $this, 'init' ] );
		add_action( 'reycore/assets/register_scripts', [ $this, 'register_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'plugins_loaded', [ $this, 'reposition_buttons' ], 11 );
		add_filter( 'body_class', [ $this, 'add_body_class' ] );
		add_filter( 'yith_woocompare_compare_added_label', [ $this, 'added_text' ] );
	}

	public function init(){
		$this->settings = apply_filters('reycore/yith_compare/params', [
			'bg' => true,
			'hover' => true,
			'tooltip' => true,
			'icon' => reycore__get_svg_icon(['id'=>'compare']),
			'page_btn_class' => '',
		]);
	}

	function added_text(){
		return __( 'Added to Compare', 'rey-core' );
	}

	public function add_body_class( $classes ){

		if( is_product() ){
			$classes[] = 'yith-compare-enabled';
		}

		return $classes;
	}

	public function is_added( $obj, $product_id ){
		return isset($obj->products_list) && is_array($obj->products_list) && in_array($product_id, $obj->products_list);
	}

	public function reposition_buttons(){

		if( !class_exists('\YITH_Woocompare_Frontend') ){
			return;
		}

		global $yith_woocompare;

		remove_action('woocommerce_single_product_summary', [$yith_woocompare->obj, 'add_compare_link'], 35);
		remove_action('woocommerce_after_shop_loop_item', [$yith_woocompare->obj, 'add_compare_link'], 20);

		if ( get_option( 'yith_woocompare_compare_button_in_product_page', 'yes' ) == 'yes' )  {
			add_action('woocommerce_single_product_summary', [$this, 'rey_compare_link_page'], 35);
		}

		if ( get_option( 'yith_woocompare_compare_button_in_products_list', 'no' ) == 'yes' ) {
			add_action('reycore/loop_inside_thumbnail/top-right', [$this, 'rey_compare_link_loop']);
		}
	}

	public function rey_compare_link_loop(){

		$product = wc_get_product();

		if( !($product && $product_id = $product->get_id()) ){
			return;
		}

		global $yith_woocompare;

		$classes = [
			'rey-yithCompare',
			'compare',
			'bg' => $this->settings['bg'] ? '--with-bg' : '',
			'hover' => $this->settings['hover'] ? '--on-hover' : '',
			// 'added' => $this->is_added($yith_woocompare->obj, $product_id) ? 'added' : '',
		];

		if( $this->settings['tooltip'] ){
			reycore_assets()->add_styles('reycore-tooltips');
		}

		printf( '<div class="rey-yithCompare-wrapper"><a href="%1$s" class="%2$s" data-product_id="%3$d" %4$s rel="nofollow">%5$s</a>%5$s</div>',
			$yith_woocompare->obj->add_product_url( $product_id ),
			implode(' ', $classes),
			$product_id,
			$this->settings['tooltip'] ? 'data-tooltip-text="'. $this->get_button_text() .'"' : '',
			$this->settings['icon']
		);
	}

	public function rey_compare_link_page(){

		$product = wc_get_product();

		if( !($product && $product_id = $product->get_id()) ){
			return;
		}

		global $yith_woocompare;

		$classes = [
			'compare',
			$this->settings['page_btn_class']
		];

		$button_text = $this->get_button_text();

		if( $this->is_added($yith_woocompare->obj, $product_id) ){
			$button_text = apply_filters( 'yith_woocompare_compare_added_label', __( 'Added to Compare', 'rey-core' ) );
		}

		printf( '<div class="rey-yithCompare-pageBtn">%s<a href="%s" class="%s" data-product_id="%d" rel="nofollow">%s</a></div>',
			$this->settings['icon'],
			$yith_woocompare->obj->add_product_url( $product_id ),
			implode(' ', $classes),
			$product_id,
			$button_text
		);
	}

	public function get_button_text(){
		// button text
		$button_text = get_option( 'yith_woocompare_button_text', __( 'Compare', 'yith-woocommerce-compare' ) );
		do_action ( 'wpml_register_single_string', 'Plugins', 'plugin_yit_compare_button_text', $button_text );
		return apply_filters( 'wpml_translate_single_string', $button_text, 'Plugins', 'plugin_yit_compare_button_text' );
	}

	public function enqueue_scripts(){
		reycore_assets()->add_styles(self::ASSET_HANDLE);
	}

	public function register_scripts($assets){

		$assets->register_asset('styles', [
			self::ASSET_HANDLE => [
				'src'     => self::get_path( basename( __DIR__ ) ) . '/style.css',
				'deps'    => [],
				'version'   => REY_CORE_VERSION,
			]
		]);

	}
}
