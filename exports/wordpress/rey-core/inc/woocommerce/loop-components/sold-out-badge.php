<?php
namespace ReyCore\WooCommerce\LoopComponents;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class SoldOutBadge extends Component {

	public function status() {
		return self::stock_display() !== 'hide';
	}

	public function get_id() {
		return 'sold_out_badge';
	}

	public function get_name() {
		return 'Sold Out Badge';
	}

	public function scheme() {
		if ( in_array( self::stock_display(), [ 'badge_so', 'badge_is' ], true ) ) {
			return [
				'type'     => 'action',
				'tag'      => 'reycore/loop_inside_thumbnail/top-right',
				'priority' => 10,
			];
		}
		else if ( 'text' === self::stock_display() ) {
			return [
				'type'     => 'action',
				'tag'      => 'woocommerce_shop_loop_item_title',
				'priority' => 60,
			];
		}
	}

	public static function stock_display() {
		return get_theme_mod( 'loop_stock_display', 'badge_so' );
	}

	/**
	 * Item Component - SOLD OUT / IN STOCK badge.
	 * Uses WooCommerce query layer for variations (no per-variation objects).
	 *
	 * @since 1.0.0
	 */
	public function render() {

		if ( ! $this->maybe_render() ) {
			return;
		}

		// Reuse current loop product.
		if ( ! ( $product = reycore_wc__get_product() ) ) {
			return;
		}

		if ( $product->get_type() === 'variable' ) {

			// If parent is out of stock, render once for the parent.
			if ( ! $product->is_in_stock() ) {
				self::__render( $product );
				return;
			}

			// Parent is in stock -> render per-variation without building objects.
			$by_status    = self::get_variation_ids_by_status( $product->get_id() );
			$hide_status  = get_theme_mod( 'loop_stock_hide_statuses', [] );

			foreach ( $by_status as $status => $ids ) {
				if ( empty( $ids ) ) {
					continue;
				}
				if ( $hide_status && in_array( $status, $hide_status, true ) ) {
					continue;
				}
				foreach ( $ids as $vid ) {
					self::__render_from_status( $vid, $status );
				}
			}
			return;
		}

		// Simple / other product types.
		self::__render( $product );
	}

	/**
	 * Group variation IDs by stock status using WooCommerce's product query API.
	 *
	 * @param int $parent_id
	 * @return array{instock: int[], onbackorder: int[], outofstock: int[]}
	 */
	protected static function get_variation_ids_by_status( $parent_id ) {
		static $cache = [];

		$parent_id = (int) $parent_id;

		if ( isset( $cache[ $parent_id ] ) ) {
			return $cache[ $parent_id ];
		}

		$base = [
			'type'   => 'variation',
			'parent' => $parent_id,
			'limit'  => -1,
			'return' => 'ids', // critical: avoid constructing WC_Product_Variation objects
		];

		$out = [
			'instock'     => wc_get_products( $base + [ 'stock_status' => 'instock' ] ),
			'onbackorder' => wc_get_products( $base + [ 'stock_status' => 'onbackorder' ] ),
			'outofstock'  => wc_get_products( $base + [ 'stock_status' => 'outofstock' ] ),
		];

		// Normalize arrays to ints.
		foreach ( $out as $k => $ids ) {
			$out[ $k ] = array_map( 'intval', (array) $ids );
		}

		return $cache[ $parent_id ] = $out;
	}

	/**
	 * Render when we only know (variation_id, stock_status) via WooCommerce queries.
	 * Avoids instantiating WC_Product_Variation.
	 *
	 * @param int    $variation_id
	 * @param string $status 'instock'|'outofstock'|'onbackorder'
	 */
	protected static function __render_from_status( $variation_id, $status ) {

		if ( in_array( self::stock_display(), [ 'badge_so', 'badge_is' ], true ) ) {
			$badge     = '';
			$css_class = '';
			$custom    = get_theme_mod( 'loop_sold_out_badge_text', '' );
			$text      = $custom ? $custom : '';

			if ( 'outofstock' !== $status ) {
				if ( 'onbackorder' === $status && apply_filters( 'reycore/woocommerce/loop/stock/onbackorder', false ) ) {
					$badge     = apply_filters( 'reycore/woocommerce/loop/in_stock_text', esc_html__( 'ON BACKORDER', 'rey-core' ) );
					$css_class = 'rey-backorder-badge';
				}
				else if ( self::stock_display() === 'badge_is' ) {
					$badge     = $text ? $text : apply_filters( 'reycore/woocommerce/loop/in_stock_text', esc_html__( 'IN STOCK', 'rey-core' ) );
					$css_class = 'rey-instock-badge';
				}
			} else {
				if ( self::stock_display() === 'badge_so' ) {
					$badge     = $text ? $text : apply_filters( 'reycore/woocommerce/loop/sold_out_text', esc_html__( 'SOLD OUT', 'rey-core' ) );
					$css_class = 'rey-soldout-badge';
				}
			}

			if ( $badge ) {
				$attributes = [
					'style'             => self::get_css(),
					'data-status'       => $status,
					'data-variation-id' => (int) $variation_id,
				];

				printf(
					'<div class="rey-itemBadge rey-stock-badge %2$s" %3$s>%1$s</div>',
					$badge,
					esc_attr( $css_class ),
					reycore__implode_html_attributes( $attributes )
				);
			}

			return;
		}

		if ( 'text' === self::stock_display() ) {
			// Fallback strings consistent with Woo semantics (no product object here).
			switch ( $status ) {
				case 'instock':
					$text = esc_html__( 'In stock', 'rey-core' );
					$cls  = 'in-stock';
					break;
				case 'onbackorder':
					$text = esc_html__( 'On Backorder', 'rey-core' );
					$cls  = 'onbackorder';
					break;
				default:
					$text = esc_html__( 'Out of stock', 'rey-core' );
					$cls  = 'out-of-stock';
					break;
			}

			$attributes = [
				'class'             => 'rey-loopStock ' . $cls,
				'style'             => self::get_css(),
				'data-status'       => $status,
				'data-variation-id' => (int) $variation_id,
			];

			printf( '<div %2$s>%1$s</div>', $text, reycore__implode_html_attributes( $attributes ) );
		}
	}

	public static function __render( $product, $is_variation = false ) {

		if ( in_array( self::stock_display(), [ 'badge_so', 'badge_is' ], true ) ) {
			self::render_badge( $product, $is_variation );
		}
		else if ( 'text' === self::stock_display() ) {
			self::render_text( $product, $is_variation );
		}
	}

	public static function render_text( $product, $is_variation = false ) {

		if ( ! $product ) {
			return;
		}

		$status = $product->get_stock_status();

		if ( ( $hide_statuses = get_theme_mod( 'loop_stock_hide_statuses', [] ) ) && in_array( $status, $hide_statuses, true ) ) {
			return;
		}

		$availability = $product->get_availability();
		$text         = '';
		$css_class    = $availability['class'];

		switch ( $status ) {
			case 'instock':
				$text = $availability['availability'] ? $availability['availability'] : esc_html__( 'In stock', 'rey-core' );
				break;
			case 'outofstock':
				$text = $availability['availability'] ? $availability['availability'] : esc_html__( 'Out of stock', 'rey-core' );
				break;
			case 'onbackorder':
				$text = $availability['availability'] ? $availability['availability'] : esc_html__( 'On Backorder', 'rey-core' );
				break;
		}

		$attributes = [
			'class'       => 'rey-loopStock ' . $css_class,
			'style'       => self::get_css(),
			'data-status' => $status,
		];

		if ( $is_variation ) {
			$attributes['data-variation-id'] = $product->get_id();
		}

		printf( '<div %2$s>%1$s</div>', $text, reycore__implode_html_attributes( $attributes ) );
	}

	public static function render_badge( $product, $is_variation = false ) {

		if ( ! $product ) {
			return;
		}

		$status = $product->get_stock_status();
		$badge  = '';
		$text   = '';

		if ( $custom_text = get_theme_mod( 'loop_sold_out_badge_text', '' ) ) {
			$text = $custom_text;
		}

		if ( $product->is_in_stock() ) {
			if ( 'onbackorder' === $status && apply_filters( 'reycore/woocommerce/loop/stock/onbackorder', false ) ) {
				$badge     = apply_filters( 'reycore/woocommerce/loop/in_stock_text', esc_html__( 'ON BACKORDER', 'rey-core' ) );
				$css_class = 'rey-backorder-badge';
			}
			else if ( self::stock_display() === 'badge_is' ) {
				$badge     = $text ? $text : apply_filters( 'reycore/woocommerce/loop/in_stock_text', esc_html__( 'IN STOCK', 'rey-core' ) );
				$css_class = 'rey-instock-badge';
			}
		}
		else {
			if ( self::stock_display() === 'badge_so' ) {
				$badge     = $text ? $text : apply_filters( 'reycore/woocommerce/loop/sold_out_text', esc_html__( 'SOLD OUT', 'rey-core' ) );
				$css_class = 'rey-soldout-badge';
			}
		}

		if ( empty( $badge ) ) {
			return;
		}

		$attributes = [
			'style'       => self::get_css(),
			'data-status' => $status,
		];

		if ( $is_variation ) {
			$attributes['data-variation-id'] = $product->get_id();
		}

		printf(
			'<div class="rey-itemBadge rey-stock-badge %2$s" %3$s>%1$s</div>',
			$badge,
			$css_class,
			reycore__implode_html_attributes( $attributes )
		);
	}

	public static function get_css() {
		if ( $custom_css = get_theme_mod( 'loop_sold_out_badge_css', '' ) ) {
			return esc_attr( $custom_css );
		}
		return '';
	}
}
