<?php
namespace ReyCore\Compatibility\LumiseProductDesigner;

if ( ! defined( 'ABSPATH' ) ) exit;

class Base extends \ReyCore\Compatibility\CompatibilityBase
{
	public $lpd;

	public $settings = [];

	public function __construct()
	{

		global $lumise_woo;
		$this->lpd = $lumise_woo;

		add_action( 'init', [ $this, 'init' ] );
		add_action( 'reycore/woocommerce/loop/after_skin_init', [$this, 'maybe_wrap_link']);
	}

	public function init(){

		$this->set_settings();

		add_action( 'wp_enqueue_scripts', [ $this, 'load_styles' ] );
		remove_filter('woocommerce_loop_add_to_cart_link', [$this->lpd, 'woo_customize_link_list'], 999);
		add_filter( 'woocommerce_loop_add_to_cart_link', [$this, 'button_html'], 999);
		remove_filter('woocommerce_cart_item_thumbnail', [$this->lpd, 'woo_cart_design_thumbnails'], 10);
		add_filter('woocommerce_cart_item_thumbnail', [$this, 'cart_thumbnails'], 10, 2);
	}

	public function set_settings(){
		$this->settings = apply_filters('reycore/compatibility/lumise/settings', [
			'show_cart_first_thumb' => false
		]);
	}

	public function button_html( $html ){

		$product = wc_get_product();

		if( ! $product ){
			global $product;
		}

		if( !$product ){
			return;
		}

		$config = get_option('lumise_config', array());

		$pid = $product->get_id();

		$product_base = get_post_meta($pid, 'lumise_product_base', true);
		$lumise_customize = get_post_meta($pid, 'lumise_customize', true);

		if(
			!empty($product_base) &&
			$lumise_customize == 'yes'
		){

			$link_design = str_replace('?&', '?', $this->lpd->tool_url . '&product_base='.$product_base.'&product_cms=' . $pid );
			$link_design = apply_filters( 'lumise_customize_link', $link_design );

			$html = ($this->atc_is_disabled( $product ) ? '' : $html);
			$btn = sprintf('<a class="button lumise-button" href="%s">%s</a>',
				esc_url($link_design ),
				(isset($config['btn_text'])? $config['btn_text'] : __('Customize', 'rey-core'))
			);
			$html .= apply_filters('reycore/compatibility/lumise/btn_html', $btn, $html);
		}

		return $html;
	}

	public function cart_thumbnails( $product_image, $cart_item ) {

		$design_thumb = '';

		global $lumise;

		if (
			function_exists('is_cart') &&
			is_cart() &&
			isset($cart_item['lumise_data'])
		) {

			$cart_item_data = $lumise->lib->get_cart_data( $cart_item['lumise_data'] );
			$color = $lumise->lib->get_color($cart_item_data['attributes']);

			if(
				isset($cart_item_data['screenshots'])
				&& is_array($cart_item_data['screenshots'])
			){
				$allowed_tags = wp_kses_allowed_html( 'post' );
				$uniq = uniqid();
				$design_thumb = '<div class="rey-lumise-cart-thumbnails lumise-cart-thumbnails lumise-cart-thumbnails-'.$uniq.'">';
				foreach ($cart_item_data['screenshots'] as $screenshot) {
					$design_thumb .= '<img style="background:'.$color.';padding: 0px;" class="lumise-cart-thumbnail" src="'.$lumise->cfg->upload_url.$screenshot.'" />';
				}
				$design_thumb .= '</div>';
			}
		}

		if( $this->settings['show_cart_first_thumb'] ){
			$product_image = $product_image . $design_thumb;
		}

		return $design_thumb ? $design_thumb : $product_image;
	}

	public function maybe_wrap_link( $loop ){

		if( !method_exists($loop, 'wrap_add_to_cart_button') ){
			return;
		}

		add_filter( 'reycore/compatibility/lumise/btn_html', function($btn) use ($loop){
			return $loop->wrap_add_to_cart_button( $btn );
		});
	}

	public function atc_is_disabled( $product ){

		$disable_add_cart = get_post_meta($product->get_id(), 'lumise_disable_add_cart', true);
		$price = $product->get_price();

		if (empty($price)) {
			$disable_add_cart = 'yes';
		}

		return $disable_add_cart === 'yes';
	}

	public function load_styles(){
		// wp_enqueue_style( 'reycore-lpd-styles', self::get_path( basename( __DIR__ ) ) . '/style.css', [], REY_CORE_VERSION );
	}

}
