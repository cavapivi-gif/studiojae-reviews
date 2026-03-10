<?php
namespace ReyCore\WooCommerce\LoopComponents;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class AddToCart extends Component {

	public function init(){

		remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);

		add_filter( 'woocommerce_loop_add_to_cart_link', [$this, 'add_to_cart_link'], 5, 3);
		add_filter( 'woocommerce_product_add_to_cart_text', [$this, 'loop_add_to_cart_text'], 10, 2);

		$single_atc__disable = get_theme_mod('single_atc__disable', false);
		foreach( ['simple', 'grouped', 'variable', 'external'] as $type ) {
			if( $single_atc__disable ){
				remove_action( 'woocommerce_'. $type .'_add_to_cart', 'woocommerce_'. $type .'_add_to_cart', 30 );
			}
		}

	}

	public function status(){
		return get_theme_mod('loop_add_to_cart', true);
	}

	public function get_id(){
		return 'add_to_cart';
	}

	public function get_name(){
		return 'Add to cart button';
	}

	public function scheme(){

		return [
			'type'          => 'action',
			'tag'           => 'woocommerce_after_shop_loop_item',
			'priority'      => 10,
		];

	}

	public function render(){

		if( ! $this->maybe_render() ){
			return;
		}

		woocommerce_template_loop_add_to_cart();
	}


	public static function add_to_cart_classes( $args ){

		$classes['base'] = esc_attr( isset( $args['class'] ) ? $args['class'] : 'button' );

		if( $btn_style = reycore_wc__get_setting('loop_add_to_cart_style') ){

			$btn_style_class = esc_attr($btn_style);

			if( reycore_wc__is_product() && $product = wc_get_product( get_queried_object_id() ) ){
				if( 'grouped' === $product->get_type() ){
					$btn_style_class = 'primary';
				}
			}

			$classes['style'] = 'rey-btn--' . $btn_style_class;
		}

		if( get_theme_mod('loop_add_to_cart_mobile', false) ){
			$classes['mobile'] = '--mobile-on';
		}

		$classes = apply_filters('reycore/woocommerce/loop/add_to_cart/classes', $classes );

		return implode(' ', $classes);
	}

	/**
	 * Some plugins filter but don't get attributes back
	 */
	function add_to_cart_link( $html, $product, $args ){

		if( ($text = $product->add_to_cart_text()) ){
			$text = sprintf('<span class="__text">%s</span>', $text);
		}

		$quantity = isset( $args['quantity'] ) ? $args['quantity'] : 1;

		if( $min = apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ) ){
			if( $min > $quantity ){
				$quantity = $min;
			}
		}

		return sprintf(
			'%6$s<a href="%1$s" data-quantity="%2$s" class="%3$s" %4$s>%5$s</a>%7$s',
			esc_url( $product->add_to_cart_url() ),
			esc_attr( $quantity ) ,
			self::add_to_cart_classes($args),
			isset( $args['attributes'] ) ? reycore__implode_html_attributes( $args['attributes'] ) : '',
			apply_filters('reycore/woocommerce/loop/add_to_cart/content', $text, $product),
			apply_filters('reycore/woocommerce/loop/add_to_cart/before', '', $product, $args),
			apply_filters('reycore/woocommerce/loop/add_to_cart/after', '', $product, $args)
		);

	}

	function loop_add_to_cart_text( $text, $product ){

		if(
			// $product->get_type() === 'simple' &&
			// $product->is_purchasable() &&
			// $product->is_in_stock() &&
			get_theme_mod('loop_atc__text', '') !== '' ){

				$custom_text = get_theme_mod('loop_atc__text', '');

				if( $custom_text === '0' ){
					return '';
				}

			return $custom_text;
		}

		return $text;
	}

}
