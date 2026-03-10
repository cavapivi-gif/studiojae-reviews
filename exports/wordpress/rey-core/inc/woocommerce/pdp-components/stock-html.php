<?php
namespace ReyCore\WooCommerce\PdpComponents;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class StockHtml extends Component {

	public function init(){
		add_filter( 'woocommerce_get_stock_html', [ $this, 'adjust_stock_html' ], 9, 2);
		add_filter( 'woocommerce_get_availability_class', [ $this, 'availability_class' ], 20, 2);
	}

	public function get_id(){
		return 'stock_html';
	}

	public function get_name(){
		return 'Stock Html';
	}

	public function adjust_stock_html($html, $product){

        if ( ! apply_filters( 'reycore/woocommerce/pdp/render/stock_html', $this->maybe_render() ) ) {
            return $html;
        }

        if ( ! apply_filters_deprecated( 'reycore/woocommerce/stock_display', [true], '2.3.0', 'reycore/woocommerce/pdp/render/stock_html' ) ) {
            return $html;
        }

		if( ! is_product() ){
			return $html;
		}

		$stock_status = $product->get_stock_status();
		$display = get_theme_mod('product_page__stock_display', 'show');

		if( 'hide' === $display && $stock_status !== 'onbackorder'){
			return '';
		}

		if( ($hide_statuses = get_theme_mod('product_page__stock_hide_statuses', [])) && in_array($stock_status, $hide_statuses, true) ){
			return '';
		}

		$availability = $product->get_availability();

		$icons = [
			'instock' => '',
			'outofstock' => '',
		];

		if( 'icontext' === get_theme_mod('product_page__stock_layout', 'icontext') ){
			$icons = [
				'instock' => apply_filters('reycore/woocommerce/stock/icon/in_stock', reycore__get_svg_icon(['id' => 'check'])),
				'outofstock' => apply_filters('reycore/woocommerce/stock/icon/out_of_stock', reycore__get_svg_icon(['id' => 'close'])),
			];
		}

		switch( $stock_status ):
			case "onbackorder":
				return apply_filters('reycore/woocommerce/stock/onbackorder', false) ? sprintf('<p class="stock %s"><span>%s</span></p>',
					esc_attr( $availability['class'] ),
					$availability['availability'] ? $availability['availability'] : __( 'Available on backorder', 'woocommerce' )
				) : $html;
				break;
			case "instock":
				return sprintf('<p class="stock %s">%s <span>%s</span></p>',
					esc_attr( $availability['class'] ),
					$icons['instock'],
					$availability['availability'] ? $availability['availability'] : esc_html__( 'In stock', 'rey-core' )
				);
				break;
			case "outofstock":
				return sprintf('<p class="stock %s">%s <span>%s</span></p>',
					esc_attr( $availability['class'] ),
					$icons['outofstock'],
					$availability['availability'] ? $availability['availability'] : esc_html__( 'Out of stock', 'rey-core' )
				);
				break;
		endswitch;

		return $html;
	}

	public function availability_class( $class, $product ){

		if ( $product->is_in_stock() && $product->managing_stock() ) {
			if ( $product->get_stock_quantity() <= wc_get_low_stock_amount( $product ) ) {
				$class .= ' low-stock';
			}
		}

		return $class;
	}

}
